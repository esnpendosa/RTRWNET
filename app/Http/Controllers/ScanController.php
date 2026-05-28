<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function index()
    {
        return view('content.scan.index');
    }

    public function process(Request $request)
    {
        $code = trim($request->code);

        // 1. Ekstrak kode pencarian jika input berupa URL lengkap (misal: http://.../billing?search=KTR01)
        if (filter_var($code, FILTER_VALIDATE_URL)) {
            $parsedUrl = parse_url($code);
            if (isset($parsedUrl['query'])) {
                parse_str($parsedUrl['query'], $queryParams);
                if (isset($queryParams['search'])) {
                    $code = trim($queryParams['search']);
                }
            } else {
                // Ambil segmen terakhir URL sebagai fallback
                $pathSegments = explode('/', trim($parsedUrl['path'], '/'));
                $code = end($pathSegments);
            }
        }

        // 2. Cek apakah kode cocok dengan pelanggan terdaftar
        $pelanggan = \App\Models\Pelanggan::where('kode_pelanggan', $code)
            ->orWhere('id_pelanggan', $code)
            ->orWhere('mikrotik_username', $code)
            ->first();

        if ($pelanggan) {
            return redirect()->route('billing.index', ['search' => $pelanggan->kode_pelanggan]);
        }

        // 3. Fallback jika kode diawali AD (backward compatibility)
        if (strpos($code, 'AD') === 0) {
            return redirect()->route('billing.index', ['search' => $code]);
        }

        // 4. Cek apakah cocok dengan inventaris
        $inventory = \App\Models\InventoryItem::where('serial_number', $code)
            ->orWhere('id_inventory', $code)
            ->first();

        if ($inventory) {
            return redirect()->route('inventory.show', $inventory->id_inventory);
        }
        
        return back()->with('error', 'Kode tidak dikenali: ' . $code);
    }
}
