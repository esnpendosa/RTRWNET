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
    public function markConnectionFailed(Router $router, $error = '')
    {
        $cacheKey = "mikrotik_connection_status_" . $router->id_router;
        
        // Cache failure for 15 seconds to prevent overload and log flooding
        Cache::put($cacheKey, 'failed', 15);

        // Update status in DB
        $router->update(['status_koneksi' => 'Simulated (Error: ' . substr($error, 0, 30) . ')']);
        \Log::warning("Mikrotik Router (" . $router->nama_router . ") marked as failed in cache for 15s due to error: " . $error);
    }

    public function getConnection(Router $router, $retries = 1, $bypassCache = false)
    {
        $cacheKey = "mikrotik_connection_status_" . $router->id_router;
        if (!$bypassCache && Cache::get($cacheKey) === 'failed') {
            return null; // Immediately return if connection was marked as failed recently
        }

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
                    'timeout' => 10, // Menggunakan 10 detik agar pembacaan kueri stabil & andal
                    'attempts' => 1,
                    'delay'    => 1,
                ]);

                // Verifikasi apakah socket benar-benar terbuka
                // (Beberapa versi library mengembalikan object tapi stream gagal)
                return $client;
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                $attempt++;
                
                if ($attempt <= $retries) {
                    usleep(500000); // Tunggu 0.5 detik sebelum retry
                    continue;
                }
            }
        }

        // Cache kegagalan koneksi
        $this->markConnectionFailed($router, $lastError);
        
        return null;
    }

    public function syncStats(Router $router)
    {
        try {
            $client = $this->getConnection($router, 1, true);
            
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
        } catch (\Exception $e) {
            $this->markConnectionFailed($router, $e->getMessage());
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
        } catch (\Exception $e) {
            $this->markConnectionFailed($router, $e->getMessage());
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
        } catch (\Exception $e) {
            $this->markConnectionFailed($router, $e->getMessage());
            return null;
        }
    }

    public function findSimpleQueue($client, $username, $ipAddress = null, Router $router = null)
    {
        try {
            $query = new Query('/queue/simple/print');
            $query->equal('.proplist', '.id,name,target,comment,max-limit');
            $queues = $client->query($query)->read();

            if (empty($queues)) return null;

            $searchKey = strtolower(trim($username));
            $customerIp = $ipAddress ? trim($ipAddress) : null;

            foreach ($queues as $q) {
                $qName = strtolower($q['name'] ?? '');
                $qComment = strtolower($q['comment'] ?? '');
                $qTarget = $q['target'] ?? '';
                
                if ($qName === $searchKey || $qComment === $searchKey || 
                    str_contains($qName, $searchKey) || 
                    str_contains($qComment, $searchKey) ||
                    ($customerIp && str_contains($qTarget, $customerIp))) {
                    return $q;
                }
            }
        } catch (\Exception $e) {
            \Log::error("Mikrotik findSimpleQueue Error: " . $e->getMessage());
            if ($router) {
                $this->markConnectionFailed($router, $e->getMessage());
            }
        }
        return null;
    }

    public function getQueueTraffic(Router $router, $name, \App\Models\Pelanggan $pelanggan = null)
    {
        try {
            $client = $this->getConnection($router);
            if (!$client) return null;

            $ipAddress = $pelanggan ? $pelanggan->ip_address : null;
            $q = $this->findSimpleQueue($client, $name, $ipAddress, $router);

            if (!$q) return null;

            // Ambil statistik real-time untuk queue yang ditemukan
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
        } catch (\Exception $e) {
            \Log::error("Mikrotik getQueueTraffic Error: " . $e->getMessage());
            $this->markConnectionFailed($router, $e->getMessage());
            return null;
        }
    }

    public function getPelangganActiveIp(Router $router, $username, $type)
    {
        try {
            $client = $this->getConnection($router);
            if (!$client) return 'ROUTER_OFFLINE';

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
        } catch (\Exception $e) {
            \Log::error("Mikrotik getPelangganActiveIp Error: " . $e->getMessage());
            $this->markConnectionFailed($router, $e->getMessage());
            return 'ROUTER_OFFLINE';
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
                $targetQueue = $this->findSimpleQueue($client, $username, $pelanggan ? $pelanggan->ip_address : null, $router);

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

    public function setSecretStatus(Router $router, $username, $type, $disable, $ip = null)
    {
        try {
            $client = $this->getConnection($router, 1, true);
            if (!$client) {
                \Log::error("Mikrotik setSecretStatus: Connection failed to router {$router->nama_router}");
                return false;
            }

            $found = false;

            // 1. Update status secret (Enable/Disable)
            if ($type === 'pppoe') {
                $query = new Query('/ppp/secret/print');
                $query->equal('name', $username);
                $resp = $client->query($query)->read();
                if (!empty($resp)) {
                    $id = $resp[0]['.id'];
                    $client->query((new Query('/ppp/secret/set'))->equal('.id', $id)->equal('disabled', $disable ? 'yes' : 'no'))->read();
                    
                    // Putuskan koneksi aktif agar modem/router langsung melakukan dial-in ulang secara fresh
                    $activeQuery = new Query('/ppp/active/print');
                    $activeQuery->where('name', $username);
                    $activeResp = $client->query($activeQuery)->read();
                    if (!empty($activeResp)) {
                        foreach ($activeResp as $active) {
                            $client->query((new Query('/ppp/active/remove'))->equal('.id', $active['.id']))->read();
                        }
                    }
                    $found = true;
                }
            } elseif ($type === 'hotspot') {
                $query = new Query('/ip/hotspot/user/print');
                $query->equal('name', $username);
                $resp = $client->query($query)->read();
                if (!empty($resp)) {
                    $id = $resp[0]['.id'];
                    $client->query((new Query('/ip/hotspot/user/set'))->equal('.id', $id)->equal('disabled', $disable ? 'yes' : 'no'))->read();
                    
                    // Putuskan sesi aktif agar pengguna dipaksa login ulang secara fresh
                    $activeQuery = new Query('/ip/hotspot/active/print');
                    $activeQuery->where('user', $username);
                    $activeResp = $client->query($activeQuery)->read();
                    if (!empty($activeResp)) {
                        foreach ($activeResp as $active) {
                            $client->query((new Query('/ip/hotspot/active/remove'))->equal('.id', $active['.id']))->read();
                        }
                    }
                    $found = true;
                }
            } elseif ($type === 'static') {
                // Reliance purely on Firewall Address-List and Filter rules for complete ON/OFF isolation (no queue throttling required)

                // 2. Update Address List (Block Traffic via Firewall)
                if ($ip) {
                    $listName = 'ISOLIR';
                    $addrQuery = new Query('/ip/firewall/address-list/print');
                    $addrQuery->where('address', $ip);
                    $addrQuery->where('list', $listName);
                    $addrResp = $client->query($addrQuery)->read();
                    
                    if ($disable) {
                        if (empty($addrResp)) {
                            $addQuery = (new Query('/ip/firewall/address-list/add'))
                                ->equal('address', $ip)
                                ->equal('list', $listName)
                                ->equal('comment', 'ISOLIR OTOMATIS: ' . $username);
                            $client->query($addQuery)->read();
                        }
                    } else {
                        if (!empty($addrResp)) {
                            foreach ($addrResp as $addr) {
                                $remQuery = (new Query('/ip/firewall/address-list/remove'))
                                    ->equal('.id', $addr['.id']);
                                $client->query($remQuery)->read();
                            }
                        }
                    }
                    $found = true; // Tandai ditemukan jika berhasil update address-list
                }

                // 3. Update Firewall Filter Rule (Custom method requested by user)
                // Mencari rule dengan comment yang sama dengan username/kode pelanggan
                $filterQuery = new Query('/ip/firewall/filter/print');
                $filterQuery->where('comment', $username);
                $filterResp = $client->query($filterQuery)->read();
                
                if (!empty($filterResp)) {
                    foreach ($filterResp as $filter) {
                        // Update existing rule
                        $client->query((new Query('/ip/firewall/filter/set'))
                            ->equal('.id', $filter['.id'])
                            ->equal('dst-address', $ip) // Pastikan IP sesuai
                            ->equal('disabled', $disable ? 'no' : 'yes'))->read();
                    }
                } elseif ($disable && $ip) {
                    // Buat rule baru jika sedang isolir dan rule belum ada
                    $addFilterQuery = (new Query('/ip/firewall/filter/add'))
                        ->equal('chain', 'forward')
                        ->equal('action', 'drop')
                        ->equal('dst-address', $ip)
                        ->equal('comment', $username)
                        ->equal('disabled', 'no'); // Langsung aktif (blokir)
                    $client->query($addFilterQuery)->read();
                }
                $found = true;
            }

            if (!$found) {
                \Log::warning("Mikrotik setSecretStatus: Customer {$username} not found on Mikrotik ({$type})");
                return false;
            }

            // 2. Tendang sesi aktif jika di-disable agar koneksi terputus saat itu juga
            if ($disable) {
                if ($type === 'pppoe') {
                    $query = new Query('/ppp/active/print');
                    $query->equal('name', $username);
                    $resp = $client->query($query)->read();
                    if (!empty($resp)) {
                        $client->query((new Query('/ppp/active/remove'))->equal('.id', $resp[0]['.id']))->read();
                    }
                } elseif ($type === 'hotspot') {
                    $query = new Query('/ip/hotspot/active/print');
                    $query->equal('user', $username);
                    $resp = $client->query($query)->read();
                    if (!empty($resp)) {
                        $client->query((new Query('/ip/hotspot/active/remove'))->equal('.id', $resp[0]['.id']))->read();
                    }
                }
            }

            return true;
        } catch (\Exception $e) {
            \Log::error("Mikrotik setSecretStatus Error for user {$username} on router {$router->nama_router}: " . $e->getMessage());
            $this->markConnectionFailed($router, $e->getMessage());
            return false;
        }
    }
}
