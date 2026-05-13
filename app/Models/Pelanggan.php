<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';
    protected $primaryKey = 'id_pelanggan';

    protected $fillable = [
        'id_user',
        'id_router',
        'kode_pelanggan',
        'nama_pelanggan',
        'email',
        'no_wa',
        'mikrotik_username',
        'mikrotik_type',
        'alamat',
        'latitude',
        'longitude',
        'usage_gb',
        'jumlah_device',
        'harga_layanan',
        'is_active',
        'prioritas_label',
        'ip_address',
        'last_online_status',
        'last_ping_at',
        'billing_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function router()
    {
        return $this->belongsTo(Router::class, 'id_router', 'id_router');
    }

    public function tagihan()
    {
        return $this->hasMany(Tagihan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function tiket()
    {
        return $this->hasMany(TiketGangguan::class, 'id_pelanggan', 'id_pelanggan');
    }
}
