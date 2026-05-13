<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rute extends Model
{
    use HasFactory;
    protected $table = 'rute';
    protected $primaryKey = 'id_rute';
    protected $fillable = [
        'id_teknisi', 'tanggal_kunjungan', 'titik_awal_lat', 'titik_awal_lng',
        'metode', 'total_jarak_km', 'status'
    ];

    public function teknisi()
    {
        return $this->belongsTo(Teknisi::class, 'id_teknisi', 'id_teknisi');
    }

    public function details()
    {
        return $this->hasMany(RuteDetail::class, 'id_rute', 'id_rute')->orderBy('urutan');
    }
}
