<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $activitiesQuery = \App\Models\AktivitasUser::with('user')->latest();

        if ($request->filled('type')) {
            $activitiesQuery->where('tipe', $request->input('type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $activitiesQuery->where(function($q) use ($search) {
                $q->where('nama_user', 'like', "%{$search}%")
                  ->orWhere('aktivitas', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        $activities = $activitiesQuery->paginate(30);

        return view('content.system.logs', compact('activities'));
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
