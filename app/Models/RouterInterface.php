<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouterInterface extends Model
{
    use HasFactory;
    protected $table = 'mikrotik_interface';
    protected $primaryKey = 'id_interface';
    protected $fillable = [
        'id_router', 'nama_interface', 'status',
        'rx_bps', 'tx_bps', 'recorded_at'
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class, 'id_router', 'id_router');
    }
}
