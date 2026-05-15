<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OdcOdp extends Model
{
    use HasFactory;

    protected $table = 'odc_odp';

    protected $fillable = [
        'nama',
        'tipe',
        'latitude',
        'longitude',
        'foto',
        'deskripsi',
        'parent_id',
    ];

    public function parent()
    {
        return $this->belongsTo(OdcOdp::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(OdcOdp::class, 'parent_id');
    }
}
