<?php

use App\Models\BotResponse;

$data = [
    [
        'keyword' => 'wifi mati,internet mati,los merah,kabel putus,tidak ada koneksi,internet gangguan,lelet,lambat,lag',
        'response' => "Halo! Kami mohon maaf atas ketidaknyamanan yang Anda alami.\n\nJika lampu *LOS* pada modem berkedip *Merah*, berarti ada masalah pada kabel jalur. Jika lampu normal namun internet lambat, cobalah *Restart Modem* dengan mematikan tombol daya selama 2 menit.\n\nJika masalah berlanjut, silakan ketik laporan Anda dengan menyertakan *Kode Pelanggan* agar teknisi segera mengeceknya.",
        'is_exact_match' => false,
        'is_active' => true,
    ],
    [
        'keyword' => 'lupa password,ganti password wifi,ubah sandi wifi,lupa sandi,ganti nama wifi',
        'response' => "Halo! Untuk menjaga keamanan jaringan Anda, penggantian Password atau Nama WiFi (SSID) dibantu oleh tim Admin dari sistem pusat.\n\nSilakan informasikan:\n1. Kode Pelanggan / Nama Lengkap\n2. Nama WiFi Baru:\n3. Password WiFi Baru (minimal 8 karakter):\n\nAdmin kami akan memprosesnya segera pada jam kerja.",
        'is_exact_match' => false,
        'is_active' => true,
    ],
    [
        'keyword' => 'cara bayar,metode pembayaran,bayar tagihan,rekening,bayar dimana,transfer kemana',
        'response' => "Untuk pembayaran layanan WiFi RT RW Net Rozitech, silakan transfer ke:\n\n🏦 *BCA*: 1234567890 a.n. Rozitech\n🏦 *Mandiri*: 0987654321 a.n. Rozitech\n💵 *DANA/OVO/GoPay*: 085895825582\n\nSetelah melakukan transfer, *langsung kirimkan foto struk/bukti transfer* di ruang chat ini. Sistem kami akan otomatis memverifikasi dan mengaktifkan layanan Anda kembali! ✅",
        'is_exact_match' => false,
        'is_active' => true,
    ],
    [
        'keyword' => 'halo,hai,pagi,siang,sore,malam,assalamualaikum,ping,p',
        'response' => "Halo! Selamat datang di Layanan Pelanggan Rozitech WiFi. 🤖\n\nAda yang bisa kami bantu hari ini? Anda bisa mengetikkan kata kunci berikut:\n🔹 *cek tagihan [KODE]* : Untuk cek info tagihan\n🔹 *cara bayar* : Informasi rekening pembayaran\n🔹 *wifi mati* : Panduan jika ada gangguan internet\n🔹 *ganti password wifi* : Panduan ubah sandi\n🔹 *paket wifi* : Cek harga pasang baru",
        'is_exact_match' => false,
        'is_active' => true,
    ],
    [
        'keyword' => 'alamat,lokasi kantor,kantor dimana,pusat,lokasi',
        'response' => "📍 *Alamat Kantor Pusat Rozitech:*\nJl. Raya Peganden, Leran, Peganden, Kec. Manyar, Kabupaten Gresik, Jawa Timur.\n\nJam Operasional Layanan:\nSenin - Sabtu pukul 08:00 - 16:00 WIB. Hari Minggu/Libur Nasional libur.",
        'is_exact_match' => false,
        'is_active' => true,
    ],
    [
        'keyword' => 'paket wifi,pasang wifi,harga wifi,daftar wifi,biaya pasang,pasang baru',
        'response' => "Halo calon pelanggan! Tertarik berlangganan Rozitech WiFi yang tanpa FUP?\n\nBerikut pilihan paket kami:\n📶 *Paket 10 Mbps* : Rp 150.000 / bulan\n📶 *Paket 20 Mbps* : Rp 250.000 / bulan\n📶 *Paket 30 Mbps* : Rp 350.000 / bulan\n\nBiaya instalasi/pemasangan baru: Rp 200.000.\nUntuk mendaftar, silakan balas dengan menyertakan *Nama Lengkap*, *No HP Aktif*, dan *Share Lokasi* rumah Anda. Tim kami akan segera mengatur jadwal survey lokasi! 🚀",
        'is_exact_match' => false,
        'is_active' => true,
    ]
];

foreach ($data as $item) {
    BotResponse::firstOrCreate(['keyword' => $item['keyword']], $item);
}

echo "Bot responses seeded successfully!\n";
