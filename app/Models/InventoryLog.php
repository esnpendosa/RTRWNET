<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    use HasFactory;

    protected $table = 'inventory_logs';
    protected $primaryKey = 'id_log';

    protected $fillable = [
        'id_inventory', 'aksi', 'id_user_executor', 'keterangan'
    ];

    public function inventory()
    {
        return $this->belongsTo(InventoryItem::class, 'id_inventory', 'id_inventory');
    }

    public function executor()
    {
        return $this->belongsTo(User::class, 'id_user_executor', 'id');
    }
}
