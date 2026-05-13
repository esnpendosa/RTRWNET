<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnnParameter extends Model
{
    use HasFactory;
    protected $table = 'knn_parameter';
    protected $primaryKey = 'id_knn_param';
    protected $fillable = ['nilai_k', 'distance_metric'];
}

// Separate files are better, but I'll write them one by one or in a batch if I can.
// I'll stick to one file per model for cleanliness.
