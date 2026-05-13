<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'keyword',
        'menu_label',
        'response',
        'is_exact_match',
        'is_active',
        'is_menu',
        'parent_id',
        'sort_order',
        'group_enabled'
    ];

    public function children()
    {
        return $this->hasMany(BotResponse::class, 'parent_id')->orderBy('sort_order');
    }

    public function parent()
    {
        return $this->belongsTo(BotResponse::class, 'parent_id');
    }
}
