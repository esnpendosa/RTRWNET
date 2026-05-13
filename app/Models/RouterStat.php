<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouterStat extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'mikrotik_stat';
    protected $primaryKey = 'id_stat';
    protected $fillable = [
        'id_router', 'uptime', 'cpu_load', 'memory_free',
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
