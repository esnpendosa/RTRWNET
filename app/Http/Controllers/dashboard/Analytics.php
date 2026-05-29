<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Analytics extends Controller
{
  public function index()
  {
    $totalInventoryValue = \App\Models\InventoryItem::all()->sum(function($item) {
        return ($item->harga_beli ?? 0) * ($item->stok ?? 1);
    });
    $totalInventoryItems = \App\Models\InventoryItem::count();

    return view('content.dashboard.dashboards-analytics', compact('totalInventoryValue', 'totalInventoryItems'));
  }
}
