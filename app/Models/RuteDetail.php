<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuteDetail extends Model
{
    use HasFactory;
    protected $table = 'rute_detail';
    protected $primaryKey = 'id_rute_detail';
    protected $fillable = [
        'id_rute', 'urutan', 'id_pelanggan', 'jarak_dari_sebelumnya_km',
        'estimasi_waktu_menit', 'status_kunjungan', 'catatan_teknisi', 'selesai_at'
    ];

    protected $casts = [
        'selesai_at' => 'datetime',
    ];

    public function rute()
    {
        return $this->belongsTo(Rute::class, 'id_rute', 'id_rute');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }
}
