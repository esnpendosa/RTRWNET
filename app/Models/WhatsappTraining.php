<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTraining extends Model
{
    use HasFactory;

    protected $fillable = [
        'remote_jid',
        'sender_name',
        'message',
        'type',
        'timestamp',
        'is_from_me'
    ];
}
