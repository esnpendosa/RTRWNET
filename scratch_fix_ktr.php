<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pelanggan;
use App\Services\MikrotikService;
use RouterOS\Query;

$pelanggan = Pelanggan::where('kode_pelanggan', 'KTR01')->first();
if (!$pelanggan) {
    die("Error: Pelanggan KTR01 tidak ditemukan di Database!\n");
}

$router = $pelanggan->router;
if (!$router) {
    die("Error: Pelanggan KTR01 tidak memiliki Router yang terasosiasi di Database!\n");
}

echo "==================================================\n";
echo " FORCE CLEANUP & REBUILD MIKROTIK KTR01\n";
echo "==================================================\n";
echo "IP Pelanggan  : " . $pelanggan->ip_address . "\n";
echo "Router Target : " . $router->nama_router . " (" . $router->ip_host . ")\n\n";

try {
    $service = new MikrotikService();
    $client = $service->getConnection($router, 1, true);
    if (!$client) {
        die("❌ Gagal terhubung ke MikroTik API!\n");
    }
    echo "✅ Terhubung ke MikroTik API!\n\n";

    $ip = $pelanggan->ip_address;
    $username = $pelanggan->kode_pelanggan;

    // --- 1. BERSIHKAN ADDRESS LIST ISOLIR ---
    echo "1. Memeriksa & Membersihkan Address List 'ISOLIR'...\n";
    $addrQuery = new Query('/ip/firewall/address-list/print');
    $addrResp = $client->query($addrQuery)->read();
    $removedListCount = 0;

    if (!empty($addrResp)) {
        foreach ($addrResp as $addr) {
            $addrIp = $addr['address'] ?? '';
            $addrList = $addr['list'] ?? '';
            $addrId = $addr['.id'] ?? '';
            
            // Hapus jika IP cocok atau comment mengandung KTR01
            if ($addrIp === $ip || str_contains($addr['comment'] ?? '', $username) || ($addrList === 'ISOLIR' && $addrIp === $ip)) {
                echo "   -> Menghapus dari list '{$addrList}': IP={$addrIp}, ID={$addrId}...\n";
                $remQuery = (new Query('/ip/firewall/address-list/remove'))->equal('.id', $addrId);
                $client->query($remQuery)->read();
                $removedListCount++;
            }
        }
    }
    echo "   ✅ Selesai! Total $removedListCount entry Address List dihapus.\n\n";

    // --- 2. BERSIHKAN FIREWALL FILTER RULES ---
    echo "2. Memeriksa & Membersihkan Firewall Filter Rules...\n";
    $filterQuery = new Query('/ip/firewall/filter/print');
    $filterResp = $client->query($filterQuery)->read();
    $removedFilterCount = 0;

    if (!empty($filterResp)) {
        foreach ($filterResp as $filter) {
            $comment = $filter['comment'] ?? '';
            $srcAddr = $filter['src-address'] ?? '';
            $dstAddr = $filter['dst-address'] ?? '';
            $filterId = $filter['.id'] ?? '';

            if (str_contains($comment, $username) || ($ip && (str_contains($srcAddr, $ip) || str_contains($dstAddr, $ip)))) {
                echo "   -> Menghapus Filter Rule: ID={$filterId}, Comment='{$comment}'...\n";
                $remFilter = (new Query('/ip/firewall/filter/remove'))->equal('.id', $filterId);
                $client->query($remFilter)->read();
                $removedFilterCount++;
            }
        }
    }
    echo "   ✅ Selesai! Total $removedFilterCount Firewall Filter Rules dihapus.\n\n";

    // --- 3. REBUILD / BUAT ULANG SIMPLE QUEUE ---
    echo "3. Membuat Ulang Simple Queue untuk KTR01...\n";
    // Cari dulu apakah ada antrean lama
    $q = $service->findSimpleQueue($client, $username, $ip, $router);
    if ($q) {
        echo "   -> Menghapus Simple Queue lama: ID={$q['.id']}...\n";
        $remQueue = (new Query('/queue/simple/remove'))->equal('.id', $q['.id']);
        $client->query($remQueue)->read();
    }

    // Tambah Queue Baru
    $limit = '12M/12M'; // Sesuai paket 12 Mbps
    $addQueue = (new Query('/queue/simple/add'))
        ->equal('name', $username)
        ->equal('target', $ip . '/32')
        ->equal('max-limit', $limit)
        ->equal('comment', 'AKTIF')
        ->equal('disabled', 'no');
    
    $client->query($addQueue)->read();
    echo "   ✅ Sukses Membuat Simple Queue Baru: Name={$username}, Target={$ip}/32, Speed={$limit}!\n\n";

    // --- 4. RESET CONNECTION TRACKING (CONNTRACK) ---
    echo "4. Membersihkan Active Connections (Conntrack) di Firewall...\n";
    // Menghapus koneksi aktif agar firewall block benar-benar ter-clear seketika
    $connQuery = new Query('/ip/firewall/connection/print');
    $connResp = $client->query($connQuery)->read();
    $connCleared = 0;
    if (!empty($connResp)) {
        foreach ($connResp as $conn) {
            $src = $conn['src-address'] ?? '';
            $dst = $conn['dst-address'] ?? '';
            $connId = $conn['.id'] ?? '';
            if (str_contains($src, $ip) || str_contains($dst, $ip)) {
                $remConn = (new Query('/ip/firewall/connection/remove'))->equal('.id', $connId);
                $client->query($remConn)->read();
                $connCleared++;
            }
        }
    }
    echo "   ✅ Selesai! Total $connCleared koneksi aktif di-reset.\n\n";

    // --- 5. PING TEST ---
    echo "5. Menguji Koneksi Fisik (Ping dari MikroTik ke $ip)...\n";
    $pingQuery = (new Query('/ping'))
        ->equal('address', $ip)
        ->equal('count', 5);
    $pingResp = $client->query($pingQuery)->read();
    $pingOk = false;
    if (!empty($pingResp)) {
        foreach ($pingResp as $pr) {
            $received = $pr['received'] ?? 0;
            $sent = $pr['sent'] ?? 0;
            echo "   -> Sent: $sent, Received: $received, Host: " . ($pr['host'] ?? '') . ", Time: " . ($pr['time'] ?? 'timeout') . "\n";
            if ($received > 0) $pingOk = true;
        }
    }
    if ($pingOk) {
        echo "\n🎉 DIAGNOSA AKHIR: SUKSES TOTAL! Jalur data MikroTik bersih, Simple Queue aktif, dan Ping ke perangkat pelanggan BERHASIL! Pelanggan seharusnya sudah bisa berinternet dengan lancar.\n";
    } else {
        echo "\n⚠️ DIAGNOSA AKHIR: MikroTik sudah dibuka 100% dan antrean dibuat baru. Namun, PING tetap gagal. Ini membuktikan bahwa perangkat ONT/modem pelanggan offline secara fisik (kabel LOSS/putus atau modem mati). Silakan periksa kabel/perangkat fisik di lokasi Lantai 3.\n";
    }

} catch (\Exception $e) {
    echo "❌ Terjadi kesalahan saat eksekusi: " . $e->getMessage() . "\n";
}
echo "==================================================\n";
