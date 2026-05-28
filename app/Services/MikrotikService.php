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
            $searchKey = trim($username);

            // 1. Coba cari langsung berdasarkan Name (Paling Cepat & Akurat!)
            $query = new Query('/queue/simple/print');
            $query->equal('.proplist', '.id,name,target,comment,max-limit');
            $query->where('name', $searchKey);
            $queues = $client->query($query)->read();
            if (!empty($queues) && !isset($queues['after'])) {
                if (isset($queues[0])) return $queues[0];
            }

            // 2. Coba cari langsung berdasarkan Comment
            $query = new Query('/queue/simple/print');
            $query->equal('.proplist', '.id,name,target,comment,max-limit');
            $query->where('comment', $searchKey);
            $queues = $client->query($query)->read();
            if (!empty($queues) && !isset($queues['after'])) {
                if (isset($queues[0])) return $queues[0];
            }

            // 3. Coba cari langsung berdasarkan Target IP
            if ($ipAddress) {
                $cleanIp = trim($ipAddress);
                
                // Format IP/32 (MikroTik biasanya mencatat target statis sebagai IP/32)
                $query = new Query('/queue/simple/print');
                $query->equal('.proplist', '.id,name,target,comment,max-limit');
                $query->where('target', $cleanIp . '/32');
                $queues = $client->query($query)->read();
                if (!empty($queues) && !isset($queues['after'])) {
                    if (isset($queues[0])) return $queues[0];
                }

                // Format IP biasa
                $query = new Query('/queue/simple/print');
                $query->equal('.proplist', '.id,name,target,comment,max-limit');
                $query->where('target', $cleanIp);
                $queues = $client->query($query)->read();
                if (!empty($queues) && !isset($queues['after'])) {
                    if (isset($queues[0])) return $queues[0];
                }
            }

            // 4. Fallback Terakhir: Lakukan scan parsial jika kueri langsung tidak cocok
            $query = new Query('/queue/simple/print');
            $query->equal('.proplist', '.id,name,target,comment,max-limit');
            $allQueues = $client->query($query)->read();

            if (!empty($allQueues)) {
                $searchLower = strtolower($searchKey);
                $customerIp = $ipAddress ? trim($ipAddress) : null;

                foreach ($allQueues as $q) {
                    $qName = strtolower($q['name'] ?? '');
                    $qComment = strtolower($q['comment'] ?? '');
                    $qTarget = $q['target'] ?? '';
                    
                    if (str_contains($qName, $searchLower) || 
                        str_contains($qComment, $searchLower) ||
                        ($customerIp && str_contains($qTarget, $customerIp))) {
                        return $q;
                    }
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

            if ($type === 'pppoe') {
                $path = '/ppp/active';
                $query = new Query($path . '/print');
                $query->equal('name', $username);
                $active = $client->query($query)->read();

                if (!empty($active)) {
                    return $active[0]['address'] ?? null;
                }

                $querySecret = new Query('/ppp/secret/print');
                $querySecret->equal('name', $username);
                $secret = $client->query($querySecret)->read();

                if (!empty($secret)) {
                    return $secret[0]['remote-address'] ?? null;
                }
            } elseif ($type === 'hotspot') {
                $path = '/ip/hotspot/active';
                $query = new Query($path . '/print');
                $query->equal('name', $username);
                $active = $client->query($query)->read();

                if (!empty($active)) {
                    return $active[0]['address'] ?? null;
                }

                $querySecret = new Query('/ip/hotspot/user/print');
                $querySecret->equal('name', $username);
                $secret = $client->query($querySecret)->read();

                if (!empty($secret)) {
                    return $secret[0]['address'] ?? null;
                }
            } elseif ($type === 'static') {
                // For static type, we check if the simple queue exists on Mikrotik!
                $targetQueue = $this->findSimpleQueue($client, $username, null, $router);
                if (!empty($targetQueue)) {
                    $address = $targetQueue['target'] ?? '';
                    $address = str_replace('/32', '', $address);
                    if (str_contains($address, '/')) {
                        $address = explode('/', $address)[0];
                    }
                    return $address ?: 'STATIC_ONLINE';
                }
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

    public function getAllActiveUsers(Router $router)
    {
        $activeUsers = [];
        try {
            $client = $this->getConnection($router);
            if (!$client) return [];

            // Fetch PPPoE active
            $pppoeQuery = new Query('/ppp/active/print');
            $pppoeQuery->equal('.proplist', '.id,name,address,uptime,caller-id');
            $pppoeActive = $client->query($pppoeQuery)->read();
            
            if (is_array($pppoeActive)) {
                foreach ($pppoeActive as $p) {
                    if (isset($p['name'])) {
                        $activeUsers[] = [
                            'username' => $p['name'],
                            'service' => 'PPPoE',
                            'address' => $p['address'] ?? 'N/A',
                            'uptime' => $p['uptime'] ?? 'N/A',
                            'caller_id' => $p['caller-id'] ?? 'N/A',
                        ];
                    }
                }
            }

            // Fetch Hotspot active
            $hotspotQuery = new Query('/ip/hotspot/active/print');
            $hotspotQuery->equal('.proplist', '.id,user,address,uptime,mac-address');
            $hotspotActive = $client->query($hotspotQuery)->read();
            
            if (is_array($hotspotActive)) {
                foreach ($hotspotActive as $h) {
                    if (isset($h['user'])) {
                        $activeUsers[] = [
                            'username' => $h['user'],
                            'service' => 'Hotspot',
                            'address' => $h['address'] ?? 'N/A',
                            'uptime' => $h['uptime'] ?? 'N/A',
                            'caller_id' => $h['mac-address'] ?? 'N/A',
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error("Mikrotik getAllActiveUsers error: " . $e->getMessage());
        }
        return $activeUsers;
    }

    public function setSecretStatus(Router $router, $username, $type, $disable, $ip = null, $profileName = null)
    {
        try {
            $client = $this->getConnection($router, 1, true);
            if (!$client) {
                \Log::error("Mikrotik setSecretStatus: Connection failed to router {$router->nama_router}");
                return false;
            }

            $found = false;

            // 1. Update status secret (Enable/Disable) & Profile
            if ($type === 'pppoe') {
                $query = new Query('/ppp/secret/print');
                $query->equal('name', $username);
                $resp = $client->query($query)->read();
                if (!empty($resp)) {
                    $id = $resp[0]['.id'];
                    $setQuery = (new Query('/ppp/secret/set'))
                        ->equal('.id', $id)
                        ->equal('disabled', $disable ? 'yes' : 'no');
                    if ($profileName && $profileName !== 'custom') {
                        $setQuery->equal('profile', $profileName);
                    }
                    if ($ip) {
                        $setQuery->equal('remote-address', $ip);
                    }
                    $client->query($setQuery)->read();
                    
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
                } else {
                    // Auto-create PPPoE secret
                    $addQuery = (new Query('/ppp/secret/add'))
                        ->equal('name', $username)
                        ->equal('password', '12345678')
                        ->equal('service', 'pppoe')
                        ->equal('profile', ($profileName && $profileName !== 'custom') ? $profileName : 'default')
                        ->equal('disabled', $disable ? 'yes' : 'no');
                    if ($ip) {
                        $addQuery->equal('remote-address', $ip);
                    }
                    $client->query($addQuery)->read();
                    $found = true;
                }
            } elseif ($type === 'hotspot') {
                $query = new Query('/ip/hotspot/user/print');
                $query->equal('name', $username);
                $resp = $client->query($query)->read();
                if (!empty($resp)) {
                    $id = $resp[0]['.id'];
                    $setQuery = (new Query('/ip/hotspot/user/set'))
                        ->equal('.id', $id)
                        ->equal('disabled', $disable ? 'yes' : 'no');
                    if ($profileName && $profileName !== 'custom') {
                        $setQuery->equal('profile', $profileName);
                    }
                    $client->query($setQuery)->read();
                    
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
                } else {
                    // Auto-create Hotspot user
                    $addQuery = (new Query('/ip/hotspot/user/add'))
                        ->equal('name', $username)
                        ->equal('password', '12345678')
                        ->equal('profile', ($profileName && $profileName !== 'custom') ? $profileName : 'default')
                        ->equal('disabled', $disable ? 'yes' : 'no');
                    $client->query($addQuery)->read();
                    $found = true;
                }
            } elseif ($type === 'static') {
                // 1. Update Simple Queue (Limit Speed)
                $targetQueue = $this->findSimpleQueue($client, $username, $ip, $router);
                
                if ($targetQueue) {
                    $id = $targetQueue['.id'];
                    $setQuery = (new Query('/queue/simple/set'))
                        ->equal('.id', $id)
                        ->equal('disabled', $disable ? 'yes' : 'no');
                    if ($profileName && $profileName !== 'custom') {
                        $limit = null;
                        if (preg_match('/(\d+)\s*(mb|m)/i', $profileName, $matches)) {
                            $mb = $matches[1];
                            $limit = "{$mb}M/{$mb}M";
                        } elseif (preg_match('/(\d+)k/i', $profileName, $matches)) {
                            $k = $matches[1];
                            $limit = "{$k}k/{$k}k";
                        }
                        if ($limit) {
                            $setQuery->equal('max-limit', $limit);
                        }
                    }
                    $client->query($setQuery)->read();
                    $found = true;
                } else {
                    // Auto-create Simple Queue if IP is provided
                    if ($ip) {
                        $limit = '10M/10M';
                        if ($profileName && $profileName !== 'custom') {
                            if (preg_match('/(\d+)\s*(mb|m)/i', $profileName, $matches)) {
                                $mb = $matches[1];
                                $limit = "{$mb}M/{$mb}M";
                            } elseif (preg_match('/(\d+)k/i', $profileName, $matches)) {
                                $k = $matches[1];
                                $limit = "{$k}k/{$k}k";
                            }
                        }
                        $addQuery = (new Query('/queue/simple/add'))
                            ->equal('name', $username)
                            ->equal('target', $ip)
                            ->equal('max-limit', $limit)
                            ->equal('disabled', $disable ? 'yes' : 'no');
                        $client->query($addQuery)->read();
                        $found = true;
                    }
                }

                // 2. Update Address List (Block Traffic via Firewall)
                if ($ip) {
                    $listName = 'ISOLIR';
                    
                    // Tarik semua entri di address-list untuk difilter secara andal di PHP
                    $addrQuery = new Query('/ip/firewall/address-list/print');
                    $allAddrs = $client->query($addrQuery)->read();
                    
                    $matchedAddrs = [];
                    if (is_array($allAddrs)) {
                        foreach ($allAddrs as $addr) {
                            $addrIp = $addr['address'] ?? '';
                            $addrListName = $addr['list'] ?? '';
                            $addrComment = $addr['comment'] ?? '';
                            
                            if ($addrListName === $listName) {
                                // Pencocokan berdasarkan kesamaan IP (bisa dengan subnet /32) ATAU comment yang mengandung username
                                $ipMatch = ($addrIp === $ip || $addrIp === $ip . '/32');
                                $commentMatch = (strpos(strtolower($addrComment), strtolower($username)) !== false);
                                
                                if ($ipMatch || $commentMatch) {
                                    $matchedAddrs[] = $addr;
                                }
                            }
                        }
                    }
                    
                    if ($disable) {
                        // Jika isolir (block) and belum ada di list, buat baru
                        if (empty($matchedAddrs)) {
                            $addQuery = (new Query('/ip/firewall/address-list/add'))
                                ->equal('address', $ip)
                                ->equal('list', $listName)
                                ->equal('comment', 'ISOLIR OTOMATIS: ' . $username);
                            $client->query($addQuery)->read();
                        }
                    } else {
                        // Jika aktif (unblock), hapus SEMUA yang cocok dari list
                        if (!empty($matchedAddrs)) {
                            foreach ($matchedAddrs as $addr) {
                                if (isset($addr['.id'])) {
                                    $remQuery = (new Query('/ip/firewall/address-list/remove'))
                                        ->equal('.id', $addr['.id']);
                                    $client->query($remQuery)->read();
                                }
                            }
                        }
                    }
                    $found = true; // Tandai ditemukan jika berhasil update address-list
                }

                // 3. Update Firewall Filter Rule (Custom method requested by user)
                // Mencari rule secara cerdas: mencakup pencocokan komentar secara case-insensitive ATAU pencocokan IP tujuan
                $filterPath = '/ip/firewall/filter';
                $filterQuery = new Query($filterPath . '/print');
                $filterResp = $client->query($filterQuery)->read();
                
                $targetRule = null;
                if (is_array($filterResp)) {
                    foreach ($filterResp as $filter) {
                        $fChain = $filter['chain'] ?? '';
                        $fAction = $filter['action'] ?? '';
                        $fComment = $filter['comment'] ?? '';
                        $fDstAddr = $filter['dst-address'] ?? '';
                        
                        if ($fChain === 'forward' && $fAction === 'drop') {
                            $commentMatch = (strtolower(trim($fComment)) === strtolower(trim($username)));
                            $ipMatch = ($ip && strpos($fDstAddr, $ip) !== false);
                            
                            if ($commentMatch || $ipMatch) {
                                $targetRule = $filter;
                                break;
                            }
                        }
                    }
                }
                
                if (!$targetRule) {
                    // Jika rule belum ada, buat baru di urutan paling ATAS (place-before=0)
                    if ($ip) {
                        $addFilterQuery = (new Query($filterPath . '/add'))
                            ->equal('chain', 'forward')
                            ->equal('action', 'drop')
                            ->equal('dst-address', $ip)
                            ->equal('comment', $username)
                            ->equal('disabled', $disable ? 'no' : 'yes')
                            ->equal('place-before', '0'); // Taruh di paling atas
                        $client->query($addFilterQuery)->read();
                    }
                } else {
                    $id = $targetRule['.id'];
                    
                    // Set status dan pindahkan ke urutan 0 untuk memastikan pemblokiran bekerja
                    $setQuery = (new Query($filterPath . '/set'))
                        ->equal('.id', $id)
                        ->equal('dst-address', $ip) // Pastikan IP sesuai
                        ->equal('disabled', $disable ? 'no' : 'yes');
                    $client->query($setQuery)->read();
                    
                    // Pindahkan ke paling atas (hanya jika sedang diisolir)
                    if ($disable) {
                        $moveQuery = (new Query($filterPath . '/move'))
                            ->equal('.id', $id)
                            ->equal('destination', '0');
                        $client->query($moveQuery)->read();
                    }
                }
                $found = true;

                // Clear active connections and ARP table if un-isolating
                if (!$disable && $ip) {
                    // 1. Clear Active Connections from MikroTik Connection Tracking
                    try {
                        $conSrc = (new Query('/ip/firewall/connection/print'))->add('?src-address~' . $ip);
                        $conSrcResp = $client->query($conSrc)->read();
                        
                        $conDst = (new Query('/ip/firewall/connection/print'))->add('?dst-address~' . $ip);
                        $conDstResp = $client->query($conDst)->read();
                        
                        $connections = [];
                        if (is_array($conSrcResp)) {
                            foreach ($conSrcResp as $c) {
                                if (isset($c['.id'])) {
                                    $connections[$c['.id']] = $c;
                                }
                            }
                        }
                        if (is_array($conDstResp)) {
                            foreach ($conDstResp as $c) {
                                if (isset($c['.id'])) {
                                    $connections[$c['.id']] = $c;
                                }
                            }
                        }
                        
                        foreach ($connections as $connId => $conn) {
                            $client->query((new Query('/ip/firewall/connection/remove'))
                                ->equal('.id', $connId))->read();
                        }
                        \Log::info("MikrotikService: Cleared " . count($connections) . " Conntrack connections for IP: " . $ip);
                    } catch (\Exception $e) {
                        \Log::warning("MikrotikService: Failed to clear Conntrack for IP {$ip}: " . $e->getMessage());
                    }

                    // 2. Clear Stale ARP Entry from MikroTik ARP Table
                    try {
                        $arpQuery = (new Query('/ip/arp/print'))->where('address', $ip);
                        $arpResp = $client->query($arpQuery)->read();
                        
                        if (is_array($arpResp)) {
                            foreach ($arpResp as $arp) {
                                if (isset($arp['.id'])) {
                                    $client->query((new Query('/ip/arp/remove'))
                                        ->equal('.id', $arp['.id']))->read();
                                }
                            }
                            \Log::info("MikrotikService: Flushed ARP entry for IP: " . $ip);
                        }
                    } catch (\Exception $e) {
                        \Log::warning("MikrotikService: Failed to clear ARP entry for IP {$ip}: " . $e->getMessage());
                    }
                }
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
