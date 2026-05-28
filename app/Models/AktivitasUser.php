<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AktivitasUser extends Model
{
    use HasFactory;

    protected $table = 'aktivitas_user';

    protected $fillable = [
        'id_user',
        'nama_user',
        'role',
        'aktivitas',
        'tipe',
        'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
