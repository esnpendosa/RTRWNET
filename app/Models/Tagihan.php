<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    use HasFactory;

    protected $table = 'tagihan';
    protected $primaryKey = 'id_tagihan';

    protected $fillable = [
        'id_pelanggan',
        'bulan',
        'tahun',
        'jumlah',
        'status',
        'metode_pembayaran',
        'bukti_bayar',
        'catatan_admin',
        'snap_token',
        'payment_url',
        'paid_at',
        'bayar_di_awal',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'bayar_di_awal' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($tagihan) {
            if ($tagihan->status === 'paid' && $tagihan->paid_at) {
                $paidDate = \Carbon\Carbon::parse($tagihan->paid_at);
                $paidYearMonth = $paidDate->format('Y-m');
                $billYearMonth = sprintf('%04d-%02d', $tagihan->tahun, $tagihan->bulan);
                
                if ($paidYearMonth < $billYearMonth) {
                    $tagihan->bayar_di_awal = true;
                }
            }
        });
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }
}
