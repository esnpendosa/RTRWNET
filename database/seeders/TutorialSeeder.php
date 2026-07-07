<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tutorial;
use App\Models\User;

class TutorialSeeder extends Seeder
{
    public function run(): void
    {
        // Cari user admin pertama untuk dikaitkan sebagai pembuat
        $admin = User::whereHas('role', function($q) {
            $q->whereIn('name', ['Admin', 'Manajer']);
        })->first() ?? User::first();

        $adminId = $admin ? $admin->id : null;

        $data = [
            [
                'judul' => 'Cara Mengganti Nama WiFi & Password Modem Huawei HG8245H5',
                'slug' => 'cara-ganti-nama-wifi-password-huawei-hg8245h5',
                'kategori' => 'Modem',
                'ringkasan' => 'Panduan lengkap dan praktis cara mengganti nama jaringan WiFi (SSID) dan kata sandi pada modem ONT Huawei HG8245H5.',
                'konten' => '
                    <p>Mengganti password WiFi secara berkala sangat disarankan untuk menjaga keamanan jaringan internet rumah Anda dari pengguna yang tidak dikenal. Berikut adalah langkah-langkah mengganti nama WiFi dan password pada modem <strong>Huawei HG8245H5</strong>:</p>
                    
                    <h3 class="fw-bold mt-4">Langkah 1: Hubungkan Perangkat ke Modem</h3>
                    <p>Pastikan HP atau Laptop Anda sudah terhubung ke jaringan WiFi modem Huawei tersebut (atau menggunakan kabel LAN jika menggunakan PC).</p>
                    
                    <h3 class="fw-bold mt-4">Langkah 2: Buka IP Address Modem di Browser</h3>
                    <ol>
                        <li>Buka browser favorit Anda (Google Chrome, Safari, atau Firefox).</li>
                        <li>Ketik IP address modem pada kolom URL: <strong class="text-primary">192.168.100.1</strong> lalu tekan <strong>Enter</strong>.</li>
                    </ol>

                    <h3 class="fw-bold mt-4">Langkah 3: Login ke Web Admin</h3>
                    <p>Masukkan username dan password administrator berikut (pilih salah satu jika gagal):</p>
                    <table class="table table-bordered mt-2 mb-3">
                        <thead class="table-light">
                            <tr>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Level Akses</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>telecomadmin</code></td>
                                <td><code>admintelecom</code></td>
                                <td>Super Admin (Direkomendasikan)</td>
                            </tr>
                            <tr>
                                <td><code>admin</code></td>
                                <td><code>admin</code></td>
                                <td>Standard User</td>
                            </tr>
                        </tbody>
                    </table>

                    <h3 class="fw-bold mt-4">Langkah 4: Ubah Nama WiFi & Password</h3>
                    <ol>
                        <li>Setelah berhasil login, klik menu <strong>Advanced</strong> di bagian atas.</li>
                        <li>Pilih sub-menu <strong>WLAN</strong> &gt; <strong>WLAN Basic Configuration</strong> di sisi kiri.</li>
                        <li>Untuk mengubah nama WiFi, ganti isi kolom <strong>SSID Name</strong> sesuai keinginan Anda.</li>
                        <li>Untuk mengubah password WiFi, ganti isi kolom <strong>WPA PreSharedKey</strong> dengan password baru Anda (minimal 8 karakter).</li>
                        <li>Klik tombol <strong>Apply</strong> untuk menyimpan perubahan.</li>
                    </ol>

                    <blockquote class="bg-light p-3 border-start border-primary border-4 rounded mt-4">
                        <strong>Catatan Penting:</strong> Setelah Anda menekan tombol Apply, koneksi WiFi di HP atau laptop Anda akan otomatis terputus. Silakan hubungkan ulang dengan memilih nama WiFi baru dan memasukkan password yang baru saja Anda buat.
                    </blockquote>
                ',
                'urutan' => 1,
                'is_published' => true,
                'created_by' => $adminId,
            ],
            [
                'judul' => 'Cara Membatasi Jumlah Pengguna WiFi pada Modem Huawei',
                'slug' => 'cara-membatasi-jumlah-pengguna-wifi-huawei',
                'kategori' => 'WiFi',
                'ringkasan' => 'Batasi kuota perangkat yang terhubung ke modem Huawei Anda untuk menghindari penurunan kecepatan akibat kelebihan pengguna.',
                'konten' => '
                    <p>Apakah internet Anda terasa lambat? Bisa jadi terlalu banyak perangkat yang terhubung secara bersamaan. Untuk menjaga kualitas internet tetap stabil, Anda bisa membatasi jumlah maksimal pengguna (HP/Laptop) yang boleh tersambung ke WiFi.</p>
                    
                    <h3 class="fw-bold mt-4">Langkah Pembatasan Pengguna WiFi:</h3>
                    <ol>
                        <li>Sambungkan perangkat Anda ke jaringan WiFi, lalu buka halaman admin modem melalui URL <strong class="text-primary">192.168.100.1</strong>.</li>
                        <li>Login menggunakan username: <code>telecomadmin</code> dan password: <code>admintelecom</code>.</li>
                        <li>Masuk ke menu <strong>Advanced</strong> &gt; <strong>WLAN</strong> &gt; <strong>WLAN Basic Configuration</strong>.</li>
                        <li>Cari kolom bernama <strong>Number of Associated Devices</strong>.</li>
                        <li>Ubah nilainya dari default (misal 32) menjadi jumlah yang Anda inginkan (misal: <code>8</code> jika Anda hanya ingin maksimal 8 perangkat yang terhubung).</li>
                        <li>Klik <strong>Apply</strong> untuk menyimpan pengaturan.</li>
                    </ol>
                    
                    <h3 class="fw-bold mt-4">Mengapa Fitur ini Berguna?</h3>
                    <ul>
                        <li><strong>Mencegah Pencurian WiFi:</strong> Meskipun orang lain mengetahui password Anda, mereka tidak akan bisa masuk apabila kuota batas maksimal perangkat sudah terpenuhi.</li>
                        <li><strong>Bandwidth Stabil:</strong> Menghindari lag saat bermain game online atau streaming video akibat jaringan yang terbagi terlalu banyak.</li>
                    </ul>
                ',
                'urutan' => 2,
                'is_published' => true,
                'created_by' => $adminId,
            ],
            [
                'judul' => 'Cara Mengatasi Lampu LOS Merah Berkedip di Modem Huawei',
                'slug' => 'cara-mengatasi-los-merah-modem-huawei',
                'kategori' => 'Troubleshooting',
                'ringkasan' => 'Panduan cepat mendiagnosis dan menangani kendala lampu indikator LOS menyala merah berkedip pada modem Huawei Anda.',
                'konten' => '
                    <p>Lampu indikator <strong>LOS (Loss of Signal)</strong> yang berkedip merah pada modem Huawei menandakan bahwa modem Anda tidak menerima sinyal optik (cahaya) dari jalur kabel fiber optik utama. Berikut cara mendiagnosis dan solusinya:</p>
                    
                    <h3 class="fw-bold mt-4">Langkah Awal Pengecekan Mandiri</h3>
                    <ol>
                        <li>
                            <strong>Periksa Konektor Kabel Kuning (Patch Cord):</strong>
                            <p>Lihat bagian bawah atau belakang modem Anda. Pastikan konektor kabel fiber berwarna biru/hijau yang menancap ke port bertuliskan <strong>PON</strong> sudah terpasang dengan kencang dan tidak longgar.</p>
                        </li>
                        <li>
                            <strong>Periksa Tekukan Kabel:</strong>
                            <p>Kabel fiber optik terbuat dari kaca tipis. Pastikan kabel tidak tertekuk tajam, terjepit pintu, atau tergulung terlalu sempit karena hal ini dapat memutus aliran cahaya di dalam kabel.</p>
                        </li>
                        <li>
                            <strong>Restart Modem:</strong>
                            <p>Matikan modem menggunakan tombol power di sisi samping, tunggu sekitar 30 detik, kemudian nyalakan kembali.</p>
                        </li>
                    </ol>

                    <h3 class="fw-bold mt-4">Kapan Harus Menghubungi Admin Rozitech?</h3>
                    <p>Jika Anda sudah mencoba langkah di atas namun lampu LOS masih tetap berkedip merah, kemungkinan terjadi:</p>
                    <ul>
                        <li>Kabel fiber optik di luar rumah putus akibat terkena pohon atau gesekan.</li>
                        <li>Ada gangguan massal di area Anda.</li>
                        <li>Port splitter di tiang ODP mengalami masalah teknis.</li>
                    </ul>
                    
                    <blockquote class="bg-light p-3 border-start border-danger border-4 rounded mt-4">
                        <strong>Solusi Tercepat:</strong> Silakan buat <strong>Tiket Gangguan</strong> melalui menu di dashboard portal ini agar teknisi kami dapat langsung diterjunkan ke lokasi Anda untuk melakukan perbaikan.
                    </blockquote>
                ',
                'urutan' => 3,
                'is_published' => true,
                'created_by' => $adminId,
            ]
        ];

        foreach ($data as $item) {
            Tutorial::updateOrCreate(['slug' => $item['slug']], $item);
        }
    }
}
