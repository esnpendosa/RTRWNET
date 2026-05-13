<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnnDetailTetangga extends Model
{
    use HasFactory;
    protected $table = 'knn_detail_tetangga';
    protected $primaryKey = 'id_knn_detail';
    protected $fillable = ['id_knn_hasil', 'urutan', 'id_pelanggan_tetangga', 'jarak_euclidean', 'label_tetangga'];

    public function tetangga()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan_tetangga', 'id_pelanggan');
    }
}
