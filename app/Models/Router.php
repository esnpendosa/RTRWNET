<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Router extends Model
{
    use HasFactory;
    protected $table = 'mikrotik_router';
    protected $primaryKey = 'id_router';
    protected $fillable = [
        'nama_router', 'ip_host', 'api_port', 'username',
        'password_encrypted', 'is_active', 'status_koneksi', 'last_sync_at'
    ];

    public function stats()
    {
        return $this->hasMany(RouterStat::class, 'id_router', 'id_router');
    }

    public function interfaces()
    {
        return $this->hasMany(RouterInterface::class, 'id_router', 'id_router');
    }
}
