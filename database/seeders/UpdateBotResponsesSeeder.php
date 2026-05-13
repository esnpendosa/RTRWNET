<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BotResponse;

class UpdateBotResponsesSeeder extends Seeder
{
    public function run()
    {
        // Update Rekening
        BotResponse::where('keyword', 'like', '%rekening%')
            ->orWhere('keyword', 'like', '%cara bayar%')
            ->update([
                'response' => "Untuk pembayaran layanan WiFi RT RW Net Rozitech, silakan transfer ke:\n\n🏦 *BRI*: 621001017663537\n🏦 *BCA*: 7415234155\n💵 *OVO/DANA*: 082187827382\n\nSemua atas nama: *FACHRUR ROZI*\n\nSetelah melakukan transfer, *langsung kirimkan foto struk/bukti transfer* di ruang chat ini. Terima kasih! ✅"
            ]);

        // Update Paket WiFi
        BotResponse::where('keyword', 'like', '%paket%')
            ->orWhere('keyword', 'like', '%pasang wifi%')
            ->update([
                'response' => "Halo! Berikut pilihan paket Rozitech WiFi (Tanpa FUP):\n\n📶 *Paket 10 Mbps* : Rp 150.000 / bulan\n📶 *Paket 20 Mbps* : Rp 250.000 / bulan\n📶 *Paket 30 Mbps* : Rp 350.000 / bulan\n\nBiaya pemasangan baru: *Rp 250.000*.\n\nSyarat pendaftaran:\n1. Nama Lengkap\n2. Alamat Lengkap\n3. Share Lokasi\n4. Foto Rumah\n\nTim kami akan segera mengecek lokasi Anda! 🚀"
            ]);
            
        echo "Bot Responses updated to match SOP!\n";
    }
}
