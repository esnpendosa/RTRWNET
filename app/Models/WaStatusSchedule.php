<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaStatusSchedule extends Model
{
    use HasFactory;

    protected $table = 'wa_status_schedules';

    protected $fillable = [
        'content',
        'media',
        'scheduled_at',
        'status',
        'error_message'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];
}
