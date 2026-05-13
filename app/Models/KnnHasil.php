<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnnHasil extends Model
{
    use HasFactory;
    protected $table = 'knn_hasil';
    protected $primaryKey = 'id_knn_hasil';
    protected $fillable = ['id_pelanggan', 'id_knn_param', 'jarak_min', 'label_hasil'];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function details()
    {
        return $this->hasMany(KnnDetailTetangga::class, 'id_knn_hasil', 'id_knn_hasil');
    }

    public function parameter()
    {
        return $this->belongsTo(KnnParameter::class, 'id_knn_param', 'id_knn_param');
    }
}
