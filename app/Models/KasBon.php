<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasBon extends Model
{
    use HasFactory;

    protected $table = 'kas_bon';
    protected $primaryKey = 'id_kas_bon';

    protected $fillable = [
        'id_teknisi',
        'nama_pekerja',
        'jumlah',
        'tanggal',
        'keterangan',
        'status',
    ];

    public function teknisi()
    {
        return $this->belongsTo(Teknisi::class, 'id_teknisi');
    }

    public function getWorkerNameAttribute()
    {
        if ($this->teknisi) {
            return $this->teknisi->nama_teknisi;
        }
        return $this->nama_pekerja ?: 'Pekerja Tidak Dikenal';
    }
}
