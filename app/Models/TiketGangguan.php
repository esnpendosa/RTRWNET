<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiketGangguan extends Model
{
    use HasFactory;

    protected $table = 'tiket_gangguan';
    protected $primaryKey = 'id_tiket';

    protected $fillable = [
        'kode_tiket',
        'id_pelanggan',
        'prioritas',
        'status',
        'keluhan',
        'id_teknisi',
        'closed_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function teknisi()
    {
        return $this->belongsTo(Teknisi::class, 'id_teknisi', 'id_teknisi');
    }
}
