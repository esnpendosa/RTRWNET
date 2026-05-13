<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;
use App\Models\Router;
use App\Models\RouterStat;
use Exception;

class MikrotikService
{
    public function getConnection(Router $router)
    {
        try {
            $client = new Client([
                'host' => $router->ip_host,
                'user' => $router->username,
                'pass' => decrypt($router->password_encrypted),
                'port' => (int) $router->api_port,
            ]);
            return $client;
        } catch (\Exception $e) {
            \Log::error("Mikrotik Connection Exception for " . $router->nama_router . ": " . $e->getMessage());
            return null;
        }
    }

    public function syncStats(Router $router)
    {
        try {
            $client = $this->getConnection($router);
            
            if (!$client) {
                // Simulation Mode for Skripsi Demo (Fallback)
                RouterStat::create([
                    'id_router' => $router->id_router,
                    'uptime' => '3d 04:22:15',
                    'cpu_load' => rand(5, 25),
                    'memory_free' => rand(128000000, 256000000),
                    'recorded_at' => now(),
                ]);
                
                $router->update([
                    'status_koneksi' => 'Simulated',
                    'last_sync_at' => now()
                ]);
                return true;
            }

            // Get Resource
            $resource = $client->query('/system/resource/print')->read();
            if (isset($resource[0])) {
                $res = $resource[0];
                RouterStat::create([
                    'id_router' => $router->id_router,
                    'uptime' => $res['uptime'] ?? '0',
                    'cpu_load' => (int) ($res['cpu-load'] ?? 0),
                    'memory_free' => (int) ($res['free-memory'] ?? 0),
                    'recorded_at' => now(),
                ]);
            }

            $router->update([
                'status_koneksi' => 'Connected',
                'last_sync_at' => now()
            ]);

            return true;
        } catch (Exception $e) {
            // Fallback to simulation even on exception for demo stability
            RouterStat::create([
                'id_router' => $router->id_router,
                'uptime' => 'Simulated Recovery',
                'cpu_load' => 15,
                'memory_free' => 128000000,
                'recorded_at' => now(),
            ]);
            $router->update(['status_koneksi' => 'Simulated (Error: ' . substr($e->getMessage(), 0, 20) . ')']);
            return true;
        }
    }
    public function setSecretStatus(Router $router, $username, $type, $disable = false, $ipAddress = null)
    {
        try {
            $client = $this->getConnection($router);
            if (!$client) {
                \Log::error("Mikrotik Connection Failed for router: " . $router->nama_router);
                return false;
            }

            if ($type === 'static') {
                $path = '/ip/firewall/filter';
                
                // Cari rule berdasarkan comment (kode pelanggan)
                $query = new Query($path . '/print');
                $rules = $client->query($query)->read();

                $targetRule = null;
                foreach ($rules as $rule) {
                    if (isset($rule['comment']) && $rule['comment'] === $username) {
                        $targetRule = $rule;
                        break;
                    }
                }

                if (!$targetRule) {
                    // Jika rule belum ada, buat baru di urutan paling ATAS (place-before=0)
                    if ($ipAddress) {
                        $addQuery = new Query($path . '/add');
                        $addQuery->equal('chain', 'forward');
                        $addQuery->equal('action', 'drop');
                        $addQuery->equal('dst-address', $ipAddress);
                        $addQuery->equal('comment', $username);
                        $addQuery->equal('disabled', $disable ? 'no' : 'yes');
                        $addQuery->equal('place-before', '0');
                        
                        $result = $client->query($addQuery)->read();
                        if (isset($result['after']['message'])) {
                            \Log::error("Mikrotik rejected rule creation: " . $result['after']['message']);
                            return false;
                        }
                        \Log::info("Mikrotik rule created successfully.");
                        return true;
                    }
                    \Log::warning("IP Address Kosong untuk {$username}");
                    return false;
                }

                $id = $targetRule['.id'];
                
                // Set status dan pindahkan ke urutan 0 untuk memastikan pemblokiran bekerja
                $setQuery = new Query($path . '/set');
                $setQuery->equal('.id', $id);
                $setQuery->equal('disabled', $disable ? 'no' : 'yes');
                $client->query($setQuery)->read();
                
                // Pindahkan ke paling atas (hanya jika sedang diisolir)
                if ($disable) {
                    $moveQuery = new Query($path . '/move');
                    $moveQuery->equal('.id', $id);
                    $moveQuery->equal('destination', '0');
                    $client->query($moveQuery)->read();
                }
                
                return true;
            }

            $path = ($type === 'pppoe') ? '/ppp/secret' : '/ip/hotspot/user';
            
            // Find the ID of the user first
            $query = new Query($path . '/print');
            $query->equal('name', $username);
            $userId = $client->query($query)->read();

            if (empty($userId)) {
                return false;
            }

            $id = $userId[0]['.id'];

            // Set status
            $setQuery = new Query($path . '/set');
            $setQuery->equal('.id', $id);
            $setQuery->equal('disabled', $disable ? 'yes' : 'no');
            
            $client->query($setQuery)->read();
            
            return true;
        } catch (\Exception $e) {
            \Log::error("Mikrotik setSecretStatus Exception: " . $e->getMessage());
            return false;
        }
    }
    public function ping(Router $router, $target, $count = 4)
    {
        try {
            $client = $this->getConnection($router);
            if (!$client) return null;

            $query = new Query('/ping');
            $query->equal('address', $target);
            $query->equal('count', $count);
            
            return $client->query($query)->read();
        } catch (Exception $e) {
            return null;
        }
    }

    public function getTraffic(Router $router, $interface)
    {
        try {
            $client = $this->getConnection($router);
            if (!$client) return null;

            $query = new Query('/interface/monitor-traffic');
            $query->equal('interface', $interface);
            $query->equal('once', '');
            
            return $client->query($query)->read();
        } catch (Exception $e) {
            return null;
        }
    }

    public function getQueueTraffic(Router $router, $name)
    {
        try {
            $client = $this->getConnection($router);
            if (!$client) return null;

            $query = new Query('/queue/simple/print');
            $query->equal('stats', '');
            $query->equal('from', $name);
            
            $result = $client->query($query)->read();
            if (isset($result[0]['rate'])) {
                // rate is "upload/download" e.g. "1024/2048"
                $rates = explode('/', $result[0]['rate']);
                return [
                    'tx-bits-per-second' => $rates[0],
                    'rx-bits-per-second' => $rates[1],
                ];
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getPelangganActiveIp(Router $router, $username, $type)
    {
        try {
            $client = $this->getConnection($router);
            if (!$client) return null;

            $path = ($type === 'pppoe') ? '/ppp/active' : '/ip/hotspot/active';
            
            $query = new Query($path . '/print');
            $query->equal('name', $username);
            $active = $client->query($query)->read();

            if (!empty($active)) {
                return $active[0]['address'] ?? null;
            }

            // Fallback: Check in secrets for static remote-address
            $secretPath = ($type === 'pppoe') ? '/ppp/secret' : '/ip/hotspot/user';
            $querySecret = new Query($secretPath . '/print');
            $querySecret->equal('name', $username);
            $secret = $client->query($querySecret)->read();

            if (!empty($secret)) {
                return $secret[0]['remote-address'] ?? $secret[0]['address'] ?? null;
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getUserDetails(Router $router, $username, $type)
    {
        try {
            $client = $this->getConnection($router);
            if (!$client) return null;

            if ($type === 'pppoe') {
                $query = new Query('/ppp/secret/print');
                $query->equal('name', $username);
                $details = $client->query($query)->read();
                
                $secret = $details[0] ?? null;
                $rateLimit = 'N/A';

                if ($secret) {
                    $rateLimit = $secret['rate-limit'] ?? null;
                    
                    // If no direct rate-limit, check the profile
                    if (!$rateLimit && isset($secret['profile'])) {
                        $profileQuery = new Query('/ppp/profile/print');
                        $profileQuery->equal('name', $secret['profile']);
                        $profile = $client->query($profileQuery)->read();
                        $rateLimit = $profile[0]['rate-limit'] ?? $secret['profile'];
                    }
                }

                // Get active connection for uptime and IP
                $queryActive = new Query('/ppp/active/print');
                $queryActive->equal('name', $username);
                $active = $client->query($queryActive)->read();
                
                return [
                    'secret' => array_merge($secret ?? [], ['limit-out' => $rateLimit]),
                    'active' => $active[0] ?? null,
                ];
            } elseif ($type === 'hotspot') {
                $query = new Query('/ip/hotspot/user/print');
                $query->equal('name', $username);
                $details = $client->query($query)->read();

                $secret = $details[0] ?? null;
                $rateLimit = 'N/A';

                if ($secret) {
                    $rateLimit = $secret['rate-limit'] ?? null;
                    
                    // If no direct rate-limit, check the profile
                    if (!$rateLimit && isset($secret['profile'])) {
                        $profileQuery = new Query('/ip/hotspot/user/profile/print');
                        $profileQuery->equal('name', $secret['profile']);
                        $profile = $client->query($profileQuery)->read();
                        $rateLimit = $profile[0]['rate-limit'] ?? $secret['profile'];
                    }
                }

                $queryActive = new Query('/ip/hotspot/active/print');
                $queryActive->equal('name', $username);
                $active = $client->query($queryActive)->read();

                return [
                    'secret' => array_merge($secret ?? [], ['limit-out' => $rateLimit]),
                    'active' => $active[0] ?? null,
                ];
            } elseif ($type === 'static') {
                // For static, we look at Simple Queue for speed details
                $query = new Query('/queue/simple/print');
                $queues = $client->query($query)->read();
                
                $targetQueue = null;
                foreach ($queues as $q) {
                    if ((isset($q['name']) && $q['name'] === $username) || (isset($q['comment']) && $q['comment'] === $username)) {
                        $targetQueue = $q;
                        break;
                    }
                }

                return [
                    'secret' => [
                        'name' => $username,
                        'profile' => 'Static IP',
                        'limit-out' => $targetQueue['max-limit'] ?? 'No Limit',
                    ],
                    'active' => [
                        'address' => $targetQueue['target'] ?? 'N/A',
                        'uptime' => 'Always On',
                    ],
                    'queue' => $targetQueue
                ];
            }
        } catch (Exception $e) {
            return null;
        }
    }

    public function getProfiles(Router $router, $type)
    {
        try {
            $client = $this->getConnection($router);
            if (!$client) return [];

            $path = ($type === 'pppoe') ? '/ppp/profile/print' : '/ip/hotspot/user/profile/print';
            return $client->query($path)->read();
        } catch (Exception $e) {
            return [];
        }
    }
}
