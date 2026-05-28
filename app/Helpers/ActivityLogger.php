<?php

namespace App\Helpers;

use App\Models\AktivitasUser;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log($aktivitas, $tipe = 'system')
    {
        $user = auth()->user();
        
        AktivitasUser::create([
            'id_user' => $user ? $user->id : null,
            'nama_user' => $user ? $user->name : 'Guest',
            'role' => $user && $user->role ? $user->role->name : 'Guest',
            'aktivitas' => $aktivitas,
            'tipe' => $tipe,
            'ip_address' => Request::ip(),
        ]);
    }
}
