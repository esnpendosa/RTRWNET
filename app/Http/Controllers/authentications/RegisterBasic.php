<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegisterBasic extends Controller
{
  public function index()
  {
    return view('content.authentications.auth-register-basic');
  }

  public function store(\Illuminate\Http\Request $request)
  {
    $request->validate([
      'username' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:8',
    ]);

    $role = \App\Models\Role::where('name', 'Pelanggan')->first();
    if (!$role) {
        $role = \App\Models\Role::create(['name' => 'Pelanggan', 'description' => 'Customer role']);
    }

    \DB::transaction(function() use ($request, $role) {
        $user = \App\Models\User::create([
            'name' => $request->username,
            'email' => $request->email,
            'password' => \Hash::make($request->password),
            'id_role' => $role->id_role,
            'username_email' => $request->username,
            'is_active' => true,
        ]);

        // Auto-create Pelanggan record with default values
        \App\Models\Pelanggan::create([
            'kode_pelanggan' => 'CUST-' . strtoupper(substr(uniqid(), 7)),
            'nama_pelanggan' => $request->username,
            'alamat' => 'Alamat belum diatur',
            'latitude' => -7.1593, // Default Gresik
            'longitude' => 112.6519,
            'usage_gb' => 0,
            'jumlah_device' => 0,
            'prioritas_label' => 'Unclassified',
        ]);
        
        auth()->login($user);
    });

    return redirect()->route('dashboard')->with('success', 'Registrasi berhasil. Selamat datang di Rozitech!');
  }
}
