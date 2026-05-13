<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teknisi extends Model
{
    use HasFactory;

    protected $table = 'teknisi';
    protected $primaryKey = 'id_teknisi';

    protected $fillable = [
        'id_user',
        'nama_teknisi',
        'no_hp',
        'base_latitude',
        'base_longitude',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function tiket()
    {
        return $this->hasMany(TiketGangguan::class, 'id_teknisi', 'id_teknisi');
    }

    public function rute()
    {
        return $this->hasMany(Rute::class, 'id_teknisi', 'id_teknisi');
    }
}
