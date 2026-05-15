<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;
use App\Models\Router;
use App\Models\RouterStat;
use Exception;
use Illuminate\Support\Facades\Cache;

class MikrotikService
{
    public function getConnection(Router $router, $retries = 2)
    {
        $attempt = 0;
        $lastError = '';

        while ($attempt <= $retries) {
            try {
                // Konfigurasi Client Mikrotik
                $client = new Client([
                    'host'    => $router->ip_host,
                    'user'    => $router->username,
                    'pass'    => decrypt($router->password_encrypted),
                    'port'    => (int) ($router->api_port ?? 8728),
                    'timeout' => 15, // Ditingkatkan ke 15 detik untuk stabilitas
                    'attempts' => 2,
                    'delay'    => 1,
                ]);

                // Verifikasi apakah socket benar-benar terbuka
                // (Beberapa versi library mengembalikan object tapi stream gagal)
                return $client;
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                $attempt++;
                
                if ($attempt <= $retries) {
                    usleep(1000000 * $attempt); // Wait 1s, 2s... before retry
                    continue;
                }
            }
        }

        // Jika semua percobaan gagal, tandai router sebagai Simulated/Error
        $router->update(['status_koneksi' => 'Simulated (Error: ' . substr($lastError, 0, 30) . ')']);
        \Log::error("Mikrotik Connection Final Failure (" . $router->nama_router . "): " . $lastError);
        
        return null;
    }

    public function syncStats(Router $router)
    {
        try {
            $client = $this->getConnection($router);
            
            if (!$client) {
                // Status sudah diupdate oleh getConnection jika gagal
                return false;
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
            // Hanya update jika belum ditandai Simulated oleh getConnection
            if (!str_contains($router->status_koneksi, 'Simulated')) {
                $router->update(['status_koneksi' => 'Error: ' . substr($e->getMessage(), 0, 50)]);
            }
            return false;
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

    public function getQueueTraffic(Router $router, $name, \App\Models\Pelanggan $pelanggan = null)
    {
        try {
            $client = $this->getConnection($router);
            if (!$client) return null;

            // Cari berdasarkan Nama (Paling Cepat)
            $query = new Query('/queue/simple/print');
            $query->where('name', $name);
            $queues = $client->query($query)->read();

            // Jika tidak ketemu, cari berdasarkan IP (Jika ada)
            if (empty($queues) && $pelanggan && $pelanggan->ip_address) {
                $query = new Query('/queue/simple/print');
                $query->where('target', $pelanggan->ip_address . '/32');
                $queues = $client->query($query)->read();
            }

            if (empty($queues)) return null;

            // Ambil statistik real-time untuk queue yang ditemukan
            $q = $queues[0];
            $statQuery = new Query('/queue/simple/print');
            $statQuery->equal('.id', $q['.id']);
            $statQuery->equal('stats', '');
            $stats = $client->query($statQuery)->read();
            
            if (!empty($stats) && isset($stats[0]['rate'])) {
                $rates = explode('/', $stats[0]['rate']);
                return [
                    'tx-bits-per-second' => (int)($rates[0] ?? 0), 
                    'rx-bits-per-second' => (int)($rates[1] ?? 0),
                ];
            }
            return null;
        } catch (Exception $e) {
            \Log::error("Mikrotik getQueueTraffic Error: " . $e->getMessage());
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

    public function getUserDetails(Router $router, $username, $type, \App\Models\Pelanggan $pelanggan = null)
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
                $query = new Query('/queue/simple/print');
                $query->equal('stats', '');
                $queues = $client->query($query)->read();
                
                $targetQueue = null;
                $searchKey = strtolower(trim($username));
                $customerIp = $pelanggan ? trim($pelanggan->ip_address) : null;

                foreach ($queues as $q) {
                    $qName = strtolower($q['name'] ?? '');
                    $qComment = strtolower($q['comment'] ?? '');
                    $qTarget = $q['target'] ?? '';
                    
                    if ($qName === $searchKey || $qComment === $searchKey || 
                        str_contains($qName, $searchKey) || 
                        str_contains($qComment, $searchKey) ||
                        ($customerIp && str_contains($qTarget, $customerIp))) {
                        $targetQueue = $q;
                        break;
                    }
                }

                $address = $targetQueue['target'] ?? ($pelanggan ? $pelanggan->ip_address : 'N/A');
                $address = str_replace('/32', '', $address);
                if ($address === 'N/A' || empty($address)) $address = $username;
                
                $limit = $targetQueue['max-limit'] ?? 'No Limit';
                if ($limit === '0/0' || $limit === '0' || empty($limit)) {
                    $limit = 'No Limit';
                } else {
                    $limits = explode('/', $limit);
                    if (count($limits) === 2) {
                        $up = round((int)$limits[0] / 1000000, 1);
                        $down = round((int)$limits[1] / 1000000, 1);
                        $limit = ($up > 0 || $down > 0) ? "{$up}M / {$down}M" : "No Limit";
                    }
                }

                return [
                    'secret' => [
                        'name' => $targetQueue['name'] ?? $username,
                        'profile' => 'Static IP',
                        'limit-out' => $limit,
                    ],
                    'active' => [
                        'address' => $address,
                        'uptime' => ($targetQueue) ? 'Connected' : 'Offline',
                    ],
                    'queue' => $targetQueue
                ];
            }
        } catch (Exception $e) {
            // If connection fails, return a "Cached/Fallback" response to keep UI stable
            return [
                'secret' => [
                    'limit-out' => $pelanggan->paket_layanan ?? 'N/A',
                ],
                'active' => [
                    'address' => $pelanggan->ip_address ?? 'N/A',
                    'uptime' => $pelanggan->last_online_status ? 'Connected (Last Seen)' : 'Offline',
                ],
                'is_fallback' => true
            ];
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
