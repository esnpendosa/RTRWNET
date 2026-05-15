<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    public function index()
    {
        return view('content.system.logs');
    }

    public function fetch(Request $request)
    {
        $logPath = storage_path('logs/laravel.log');
        
        if (!File::exists($logPath)) {
            return response()->json(['logs' => 'Log file not found.']);
        }

        // Read last 100 lines
        $lines = 100;
        $data = file($logPath);
        $lastLines = array_slice($data, -$lines);
        
        return response()->json([
            'logs' => implode('', $lastLines),
            'time' => now()->format('H:i:s')
        ]);
    }

    public function clear()
    {
        $logPath = storage_path('logs/laravel.log');
        if (File::exists($logPath)) {
            File::put($logPath, '');
        }
        return back()->with('success', 'Logs cleared successfully.');
    }
}
