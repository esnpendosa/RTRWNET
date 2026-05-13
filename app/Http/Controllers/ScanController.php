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
        $code = $request->code;
        // Logic to handle scanned code (Pelanggan ID or Inventory ID)
        if (strpos($code, 'AD') === 0) {
            return redirect()->route('payment.by-id', ['kode_pelanggan' => $code]);
        }

        // Check Inventory
        $inventory = \App\Models\InventoryItem::where('serial_number', $code)
            ->orWhere('id_inventory', $code)
            ->first();

        if ($inventory) {
            return redirect()->route('inventory.show', $inventory->id_inventory);
        }
        
        return back()->with('error', 'Kode tidak dikenali: ' . $code);
    }
}
