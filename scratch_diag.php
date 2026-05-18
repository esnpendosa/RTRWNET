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
echo " DIAGNOSIS MIKROTIK KTR01 (Lantai 3)\n";
echo "==================================================\n";
echo "IP Pelanggan  : " . $pelanggan->ip_address . "\n";
echo "Status di DB  : " . ($pelanggan->is_active ? "AKTIF" : "TERISOLIR") . "\n";
echo "Router Target : " . $router->nama_router . " (" . $router->ip_host . ")\n\n";

try {
    echo "1. Menghubungkan ke MikroTik...\n";
    $service = new MikrotikService();
    $client = $service->getConnection($router, 1, true);
    if (!$client) {
        die("❌ Gagal terhubung ke MikroTik API!\n");
    }
    echo "✅ Terhubung ke MikroTik API!\n\n";

    $ip = $pelanggan->ip_address;
    $username = $pelanggan->kode_pelanggan;

    // --- A. Kueri Simple Queue ---
    echo "2. Memeriksa Simple Queue...\n";
    $q = $service->findSimpleQueue($client, $username, $ip, $router);
    if ($q) {
        echo "   -> Ketemu Queue: " . ($q['name'] ?? 'null') . "\n";
        echo "   -> ID          : " . ($q['.id'] ?? 'null') . "\n";
        echo "   -> Target      : " . ($q['target'] ?? 'null') . "\n";
        echo "   -> Max Limit   : " . ($q['max-limit'] ?? 'null') . "\n";
        echo "   -> Disabled    : " . ($q['disabled'] ?? 'null') . "\n";
        echo "   -> Comment     : " . ($q['comment'] ?? 'null') . "\n";
    } else {
        echo "   ❌ Simple Queue TIDAK DITEMUKAN di MikroTik!\n";
    }
    echo "\n";

    // --- B. Kueri Address List ---
    echo "3. Memeriksa Firewall Address List...\n";
    $addrQuery = new Query('/ip/firewall/address-list/print');
    if ($ip) {
        $addrQuery->equal('address', $ip);
    }
    $addrResp = $client->query($addrQuery)->read();
    if (!empty($addrResp)) {
        foreach ($addrResp as $addr) {
            echo "   -> Ketemu di List: " . ($addr['list'] ?? 'null') . "\n";
            echo "   -> ID            : " . ($addr['.id'] ?? 'null') . "\n";
            echo "   -> Address       : " . ($addr['address'] ?? 'null') . "\n";
            echo "   -> Comment       : " . ($addr['comment'] ?? 'null') . "\n";
        }
    } else {
        echo "   ✅ Bersih! IP tidak terdaftar di Address List manapun.\n";
    }
    echo "\n";

    // --- C. Kueri Filter Rule ---
    echo "4. Memeriksa Firewall Filter Rules (Drop Rules)...\n";
    $filterQuery = new Query('/ip/firewall/filter/print');
    $filterResp = $client->query($filterQuery)->read();
    $foundRules = false;
    if (!empty($filterResp)) {
        foreach ($filterResp as $filter) {
            $comment = $filter['comment'] ?? '';
            $srcAddr = $filter['src-address'] ?? '';
            $dstAddr = $filter['dst-address'] ?? '';
            if (str_contains($comment, $username) || 
                ($ip && (str_contains($srcAddr, $ip) || str_contains($dstAddr, $ip)))) {
                echo "   -> Rule ID  : " . ($filter['.id'] ?? 'null') . "\n";
                echo "   -> Chain    : " . ($filter['chain'] ?? 'null') . "\n";
                echo "   -> Action   : " . ($filter['action'] ?? 'null') . "\n";
                echo "   -> Src Addr : " . ($srcAddr ?: 'Any') . "\n";
                echo "   -> Dst Addr : " . ($dstAddr ?: 'Any') . "\n";
                echo "   -> Disabled : " . ($filter['disabled'] ?? 'null') . "\n";
                echo "   -> Comment  : " . ($comment ?: 'null') . "\n";
                $foundRules = true;
            }
        }
    }
    if (!$foundRules) {
        echo "   ✅ Bersih! Tidak ada filter rules yang memblokir IP/Username ini.\n";
    }
    echo "\n";

    // --- D. Ping Test dari MikroTik ---
    echo "5. Menguji Ping langsung dari MikroTik ke IP Pelanggan ($ip)...\n";
    $pingQuery = (new Query('/ping'))
        ->equal('address', $ip)
        ->equal('count', 3);
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
        echo "   ✅ PING BERHASIL! Perangkat pelanggan aktif dan terhubung ke MikroTik!\n";
    } else {
        echo "   ❌ PING GAGAL! Perangkat pelanggan tidak merespons (Offline / LOSS secara fisik).\n";
    }

} catch (\Exception $e) {
    echo "❌ Terjadi kesalahan saat diagnosa: " . $e->getMessage() . "\n";
}
echo "==================================================\n";
