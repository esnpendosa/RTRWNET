<?php

namespace App\Http\Controllers;

use App\Models\Router;
use App\Models\RouterStat;
use App\Services\MikrotikService;
use Illuminate\Http\Request;

class MikrotikController extends Controller
{
    protected $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    public function index()
    {
        $routers = Router::all();
        return view('content.mikrotik.index', compact('routers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_router' => 'required',
            'ip_host' => 'required',
            'username' => 'required',
            'password' => 'required',
        ]);

        Router::create([
            'nama_router' => $request->nama_router,
            'ip_host' => $request->ip_host,
            'api_port' => $request->api_port ?? 8728,
            'username' => $request->username,
            'password_encrypted' => encrypt($request->password),
        ]);

        return redirect()->route('mikrotik.index')->with('success', 'Router berhasil ditambahkan');
    }

    public function edit(Router $router)
    {
        return view('content.mikrotik.edit', compact('router'));
    }

    public function update(Request $request, Router $router)
    {
        $request->validate([
            'nama_router' => 'required',
            'ip_host' => 'required',
            'username' => 'required',
        ]);

        $data = [
            'nama_router' => $request->nama_router,
            'ip_host' => $request->ip_host,
            'api_port' => $request->api_port ?? 8728,
            'username' => $request->username,
        ];

        if ($request->filled('password')) {
            $data['password_encrypted'] = encrypt($request->password);
        }

        $router->update($data);

        return redirect()->route('mikrotik.index')->with('success', 'Router berhasil diperbarui');
    }

    public function destroy(Router $router)
    {
        $router->delete();
        return redirect()->route('mikrotik.index')->with('success', 'Router berhasil dihapus');
    }

    public function sync(Router $router)
    {
        $success = $this->mikrotikService->syncStats($router);
        if ($success) {
            return back()->with('success', 'Sync stats berhasil');
        }
        return back()->with('error', 'Gagal sync stats: ' . $router->status_koneksi);
    }

    public function stats(Router $router)
    {
        $stats = RouterStat::where('id_router', $router->id_router)->latest('recorded_at')->take(20)->get();
        return view('content.mikrotik.stats', compact('router', 'stats'));
    }
}
