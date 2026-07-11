# BUKU PETUNJUK PENGGUNAAN APLIKASI
## ROZITECH WIFI MANAGER
### Sistem Manajemen Jaringan RTRWNet Berbasis Web GIS & AI

---

**Versi 1.1.0**
**Rozitech Network — Ujungpangkah, Gresik**
**Universitas Muhammadiyah Gresik (UMG)**
**Tahun 2026**

---

## INFORMASI DOKUMEN

Dokumen ini ditujukan kepada:
- **Admin / Operator Jaringan**: Pengelola utama sistem RTRWNet Rozitech Network.
- **Pengembang / Tim IT**: Fasilitator pemeliharaan dan pengembangan sistem.

> © Hak Cipta Muhammad As'ad Muhibbin Akbar — Universitas Muhammadiyah Gresik.
> Dokumen ini dibuat sebagai bagian dari Tugas Akhir (Skripsi) Program Studi Informatika UMG.
> Dilarang menyalin atau menerbitkan ulang tanpa izin tertulis dari penulis.

---

## KATA PENGANTAR

Perkembangan teknologi informasi membawa dampak besar pada pengelolaan jaringan internet berbasis komunitas (RTRWNet). Pengelolaan yang sebelumnya dilakukan secara manual — mulai dari pencatatan tagihan, verifikasi pembayaran, hingga pemantauan perangkat Mikrotik — kini dapat dilakukan secara digital, otomatis, dan real-time.

**Rozitech WiFi Manager** hadir sebagai solusi terintegrasi yang menggabungkan kecerdasan buatan (AI Chatbot WhatsApp), sistem informasi geografis (Web GIS), algoritma klasifikasi Naive Bayes, serta otomasi billing berbasis OCR. Sistem ini dirancang agar operator jaringan dapat mengelola seluruh aspek operasional dari satu dashboard terpusat.

Buku Petunjuk Penggunaan ini disusun agar seluruh pengguna — baik admin teknis maupun pengurus — dapat memahami dan mengoperasikan sistem dengan benar dan efisien.

Gresik, Juni 2026
**Penulis**
Muhammad As'ad Muhibbin Akbar
Mahasiswa Informatika — Universitas Muhammadiyah Gresik

---

## DAFTAR ISI

- **BAB I: PENDAHULUAN**
  - 1.1. Tujuan Pembuatan Dokumen
  - 1.2. Deskripsi Umum Sistem
  - 1.3. Ikhtisar Bab
- **BAB II: SUMBER DAYA YANG DIBUTUHKAN**
  - 2.1. Spesifikasi Perangkat Lunak
  - 2.2. Spesifikasi Perangkat Keras
  - 2.3. Kebutuhan Sumber Daya Manusia
- **BAB III: MENU DAN CARA PENGGUNAAN**
  - 3.1. Struktur Menu Aplikasi
  - 3.2. Cara Login & Logout
  - 3.3. Dashboard Utama
  - 3.4. Manajemen Pelanggan
  - 3.5. Manajemen Tagihan & Pembayaran (OCR)
  - 3.6. WhatsApp Bot (R-Care)
  - 3.7. Peta Pelanggan (Web GIS)
  - 3.8. Mikrotik Manager
  - 3.9. Klasifikasi Naive Bayes
  - 3.10. Laporan Keuangan (PDF)
  - 3.11. Pengaturan Akun Admin
- **BAB IV: PEMECAHAN MASALAH (TROUBLESHOOTING)**

---

## BAB I: PENDAHULUAN

### 1.1. Tujuan Pembuatan Dokumen

Dokumen User Manual Aplikasi Rozitech WiFi Manager ini dibuat dengan tujuan:

1. **Admin / Operator**: Panduan teknis lengkap untuk mengoperasikan seluruh fitur sistem — mulai dari pencatatan pelanggan, verifikasi pembayaran, pengelolaan WhatsApp Bot, hingga klasifikasi data menggunakan algoritma Naive Bayes.
2. **Pengembang / Administrator Sistem**: Acuan teknis untuk pemeliharaan, debugging, dan pengembangan sistem lebih lanjut.

### 1.2. Deskripsi Umum Sistem

**Rozitech WiFi Manager** adalah platform manajemen jaringan RTRWNet berbasis web yang dibangun menggunakan framework Laravel 12. Sistem ini menggantikan pengelolaan manual menjadi digital dan terotomasi.

#### Fitur-Fitur Utama

| No. | Nama Fitur | Hak Akses | Fungsi Utama |
|-----|-----------|-----------|-------------|
| 1 | Dashboard | Admin | Statistik ringkasan sistem secara real-time |
| 2 | Manajemen Pelanggan | Admin | Kelola data, paket, dan status pelanggan |
| 3 | Tagihan Otomatis | Admin | Generate & kelola tagihan bulanan |
| 4 | Verifikasi Pembayaran (OCR) | Admin | Verifikasi struk transfer otomatis |
| 5 | WhatsApp Bot (R-Care) | Admin | Kelola bot AI untuk layanan pelanggan |
| 6 | Peta GIS | Admin | Visualisasi lokasi pelanggan di peta |
| 7 | Mikrotik Manager | Admin | Kelola router, user secret, dan profile |
| 8 | Klasifikasi Naive Bayes | Admin | Training & evaluasi model klasifikasi |
| 9 | Laporan Keuangan | Admin | Export laporan ke PDF |
| 10 | Pengaturan Akun | Admin | Kelola password dan profil admin |

#### Teknologi yang Diintegrasikan

- **Prakiraan cuaca & lokasi**: Terintegrasi dalam peta GIS pelanggan
- **OCR (Optical Character Recognition)**: Membaca nominal dan tanggal pada foto struk transfer secara otomatis menggunakan Tesseract.js
- **AI Chatbot (R-Care)**: Bot WhatsApp berbasis OpenRouter (Gemini/Llama) untuk layanan pelanggan mandiri
- **Klasifikasi Naive Bayes**: Mengklasifikasikan tingkat kesejahteraan pelanggan berdasarkan data ekonomis
- **Isolir Otomatis Mikrotik**: Memutus dan mengaktifkan layanan internet pelanggan secara otomatis berdasarkan status pembayaran

### 1.3. Ikhtisar Bab

- **BAB I** — Pendahuluan: Tujuan dokumen, deskripsi sistem, dan ikhtisar.
- **BAB II** — Sumber Daya: Spesifikasi perangkat keras, lunak, dan manusia.
- **BAB III** — Menu & Cara Penggunaan: Langkah-langkah penggunaan lengkap semua fitur.
- **BAB IV** — Troubleshooting: Panduan mengatasi kendala teknis.

---

## BAB II: SUMBER DAYA YANG DIBUTUHKAN

### 2.1. Spesifikasi Perangkat Lunak (Software)

| Platform | Kebutuhan Software | Keterangan |
|---------|-------------------|-----------|
| Server / Hosting | PHP 8.2+, MySQL 8.0+, Nginx/Apache | Lingkungan produksi sistem |
| Server | Node.js 18.x LTS | Untuk WhatsApp Bot (R-Care) |
| Server | Composer 2.x, NPM 9.x+ | Manajemen dependensi |
| Server | PM2 (Process Manager) | Menjalankan bot secara persisten |
| Admin (Client) | Browser modern (Chrome, Firefox, Edge) | Akses dashboard berbasis web |
| Admin (Client) | Koneksi internet aktif | Untuk sinkronisasi data real-time |

> **Rekomendasi Server Lokal**: Gunakan **Laragon** (Windows) untuk environment development.

### 2.2. Spesifikasi Perangkat Keras (Hardware)

| Perangkat | Spesifikasi Minimal | Catatan |
|----------|--------------------|----|
| **Server / VPS** | RAM 2 GB, Storage 20 GB SSD, CPU 2 Core | Untuk production deployment |
| **Server Lokal** | RAM 4 GB, Storage 10 GB kosong | Untuk development/testing |
| **PC Admin** | RAM 4 GB, layar 1366×768 | Untuk mengakses dashboard |
| **Router Mikrotik** | RouterOS v6.x / v7.x, API aktif | Untuk fitur isolir & manajemen |
| **Smartphone Admin** | Android/iOS dengan WhatsApp | Untuk scan QR bot & monitoring |

### 2.3. Kebutuhan Sumber Daya Manusia (Brainware)

Pengguna sistem ini adalah **satu akun admin tunggal** dengan kompetensi:

- Pemahaman dasar pengoperasian komputer dan browser web
- Pemahaman dasar konsep jaringan internet (IP, paket data, bandwidth)
- Pemahaman dasar manajemen keuangan (tagihan, pemasukan, piutang)
- Kemampuan membaca dan menggunakan antarmuka web berbahasa Indonesia

### 2.4. Pengenalan dan Pelatihan

Sebelum menggunakan sistem untuk operasional nyata, admin wajib:

1. Membaca seluruh Buku Petunjuk Penggunaan ini dari awal hingga akhir
2. Mengikuti sesi onboarding teknis bersama pengembang sistem
3. Melakukan uji coba fitur di lingkungan development/staging sebelum production
4. Memastikan seluruh konfigurasi `.env` (database, bot, API key) sudah benar

---

## BAB III: MENU DAN CARA PENGGUNAAN

### 3.1. Struktur Menu Aplikasi

Menu aplikasi hanya dapat diakses oleh **satu akun admin**. Seluruh menu tersedia di sidebar kiri setelah login berhasil.

| No. | Menu | Lokasi | Fungsi |
|-----|------|--------|--------|
| 1 | Dashboard | Sidebar atas | Statistik ringkasan sistem |
| 2 | Pelanggan | Sidebar | Kelola data pelanggan |
| 3 | Tagihan | Sidebar | Kelola tagihan bulanan |
| 4 | Pembayaran | Sidebar | Verifikasi pembayaran OCR |
| 5 | Peta / GIS | Sidebar | Peta interaktif pelanggan |
| 6 | WhatsApp | Sidebar | Kelola bot R-Care |
| 7 | Mikrotik | Sidebar | Manajemen router |
| 8 | Klasifikasi | Sidebar | Model Naive Bayes |
| 9 | Laporan | Sidebar | Export PDF keuangan |
| 10 | Pengaturan Akun | Sudut kanan atas | Profil & password admin |

---

### 3.2. Masuk ke Aplikasi (Login)

Sistem hanya memiliki satu akun admin. Tidak ada fitur registrasi publik.

**Langkah-langkah Login:**

1. Buka browser dan akses URL sistem (contoh: `http://127.0.0.1:8000` atau URL domain produksi)
2. Halaman login otomatis tampil
3. Masukkan **Username** admin yang telah dikonfigurasi
4. Masukkan **Password** admin
5. Klik tombol **"MASUK"**
6. Jika berhasil, sistem mengarahkan ke halaman **Dashboard**

> **⚠️ Catatan**: Jika lupa password, gunakan perintah artisan di server:
> ```bash
> php artisan tinker
> # Kemudian jalankan:
> App\Models\User::first()->update(['password' => bcrypt('password_baru')])
> ```

**Keluar dari Aplikasi (Logout):**

1. Klik nama admin di sudut kanan atas
2. Pilih menu **"Keluar"**
3. Sesi login terhapus dan diarahkan kembali ke halaman login

---

### 3.3. Dashboard Utama

Dashboard adalah halaman pertama setelah login. Menyajikan ringkasan kondisi sistem secara real-time.

**Informasi yang Ditampilkan:**

| Widget | Keterangan |
|--------|------------|
| Total Pelanggan Aktif | Jumlah pelanggan yang layanannya aktif |
| Pendapatan Bulan Ini | Total tagihan yang sudah dibayar bulan berjalan |
| Tagihan Jatuh Tempo | Jumlah tagihan yang belum dibayar |
| Pelanggan Diisolir | Jumlah pelanggan yang diputus karena menunggak |
| Status WhatsApp Bot | Status koneksi bot (Terhubung / Terputus) |
| Grafik Pendapatan | Tren pendapatan bulanan 6 bulan terakhir |

---

### 3.4. Manajemen Pelanggan

#### A. Melihat Daftar Pelanggan

1. Klik menu **Pelanggan** di sidebar
2. Daftar semua pelanggan tampil dalam tabel
3. Gunakan kolom **Pencarian** untuk filter berdasarkan nama atau nomor HP
4. Klik header kolom untuk **mengurutkan** data

#### B. Menambah Pelanggan Baru

1. Klik tombol **"+ Tambah Pelanggan"** di sudut kanan atas tabel
2. Isi formulir data pelanggan:
   - **Nama Lengkap**: Sesuai KTP
   - **No. HP**: Format `628xxxxxxxxx` (tanpa tanda `+`, tanpa spasi)
   - **Alamat**: Alamat rumah lengkap
   - **Koordinat GPS**: Latitude & Longitude (untuk peta GIS)
   - **Paket Internet**: Pilih dari daftar paket yang tersedia
   - **Tanggal Instalasi**: Tanggal pemasangan internet
   - **Status**: Aktif / Isolir
3. Klik **"Simpan"**
4. Sistem otomatis:
   - Membuat tagihan bulan pertama
   - Mengirim notifikasi WhatsApp selamat datang ke pelanggan
   - Mendaftarkan username di Mikrotik (jika terhubung)

> **💡 Format No. HP**: Selalu gunakan format internasional `628xxx`. Contoh: `6281234567890`

#### C. Mengubah Data Pelanggan (Edit)

1. Pada baris pelanggan, klik tombol **ikon pensil** (Edit)
2. Ubah data yang diperlukan pada formulir
3. Klik **"Simpan Perubahan"**

#### D. Mengaktifkan / Mengisolir Pelanggan

1. Pada baris pelanggan, klik tombol **"Isolir"** (merah) atau **"Aktifkan"** (hijau)
2. Sistem mengirim perintah ke Mikrotik untuk memutus/mengaktifkan layanan
3. Status pelanggan berubah secara real-time

> **⚠️ Isolir Otomatis**: Sistem secara otomatis mengisolir pelanggan yang tagihannya melewati batas jatuh tempo. Jadwal cek dijalankan via Laravel Scheduler setiap hari.

---

### 3.5. Manajemen Tagihan & Pembayaran

#### A. Melihat Daftar Tagihan

1. Klik menu **Tagihan** di sidebar
2. Daftar tagihan tampil dengan status: **Belum Bayar**, **Lunas**, atau **Jatuh Tempo**
3. Gunakan filter **Bulan/Tahun** dan **Status** untuk mempersempit tampilan

#### B. Generate Tagihan Manual

1. Klik tombol **"Generate Tagihan"**
2. Pilih **Bulan** dan **Tahun** target
3. Klik **"Proses"** — sistem membuat tagihan untuk semua pelanggan aktif sekaligus

> **💡 Tagihan Otomatis**: Sistem men-generate tagihan bulanan secara otomatis via Laravel Scheduler setiap tanggal 1.

#### C. Verifikasi Pembayaran dengan OCR

Fitur ini memungkinkan admin memverifikasi bukti transfer dari pelanggan secara otomatis.

1. Buka menu **Pembayaran** → **Verifikasi Transfer**
2. Upload foto struk / bukti transfer dari pelanggan (format JPG/PNG)
3. Sistem OCR (Tesseract.js) membaca secara otomatis:
   - **Nominal transfer**
   - **Tanggal transfer**
   - **Nama pengirim** (jika terbaca)
4. Periksa hasil pembacaan OCR
5. Pilih **tagihan pelanggan** yang sesuai dari dropdown
6. Klik **"Tandai Lunas"**
7. Sistem otomatis:
   - Mengubah status tagihan menjadi **Lunas**
   - Membuat nota pembayaran PDF
   - Mengirim nota PDF ke WhatsApp pelanggan
   - Mengaktifkan kembali layanan jika pelanggan sedang diisolir

> **⚠️ Akurasi OCR**: Pastikan foto struk **cerah, fokus, dan tidak terpotong**. Foto buram atau gelap dapat menghasilkan pembacaan yang tidak akurat.

#### D. Tandai Lunas Manual (Tanpa OCR)

1. Pada baris tagihan, klik tombol **"Tandai Lunas"**
2. Isi nominal pembayaran dan tanggal
3. Upload bukti transfer (opsional)
4. Klik **"Konfirmasi"**

#### E. Edit Bukti Pembayaran *(Fitur Baru)*

Fitur ini memungkinkan admin maupun pelanggan mengganti foto bukti bayar yang sudah diunggah — misalnya jika foto sebelumnya buram, salah, atau perlu diperbarui.

**Untuk Admin:**
1. Di daftar tagihan, temukan tagihan yang sudah ada bukti bayarnya
2. Klik link **"Edit Bukti"** di samping link "Lihat Bukti"
3. Halaman form edit terbuka — tampil foto bukti yang sedang aktif
4. Klik **"Choose File"** → pilih foto baru (JPG/PNG/GIF/PDF, maks. 3MB)
5. Opsional: pilih **Metode Pembayaran**
6. Centang **"Verifikasi & Tandai Sebagai Lunas"** jika ingin langsung memverifikasi
7. Klik **"Simpan Perubahan"**

**Untuk Pelanggan:**
- Pelanggan hanya bisa mengedit bukti bayar milik tagihan mereka sendiri
- Setelah edit, status kembali ke **Menunggu Verifikasi** untuk dicek admin
- Pelanggan tidak bisa centang opsi Tandai Lunas (hanya admin)

> **Aturan file**: Format yang diterima: JPG, JPEG, PNG, GIF, PDF. Maksimal ukuran: **3MB**.

---

### 3.6. WhatsApp Bot (R-Care)

#### A. Menghubungkan WhatsApp (Scan QR)

1. Buka menu **WhatsApp** di sidebar
2. Klik tombol **"Mulai Sesi"**
3. QR Code muncul di layar
4. Buka WhatsApp di smartphone → ketuk **⋮ (titik tiga)** → **Perangkat Tertaut** → **Tautkan Perangkat**
5. Arahkan kamera ke QR Code di layar
6. Tunggu beberapa detik — status berubah menjadi **🟢 Terhubung**

**Alternatif — Pairing Code (tanpa kamera):**

1. Klik **"Pairing Code"** → masukkan nomor HP WhatsApp
2. Kode 8 digit muncul di layar
3. Di WhatsApp: **Perangkat Tertaut** → **Tautkan dengan Nomor Telepon** → masukkan kode

#### B. Perintah WhatsApp untuk Pelanggan

Pelanggan dapat mengirim pesan berikut ke nomor WhatsApp bisnis:

| Perintah | Balasan Bot |
|----------|-------------|
| `halo` / `hai` | Menu layanan lengkap |
| `cek tagihan` | Status tagihan bulan ini & jumlah tunggakan |
| `bayar` | Nomor rekening & cara konfirmasi pembayaran |
| `lapor gangguan` | Formulir laporan gangguan internet |
| `paket` | Daftar paket & harga yang tersedia |
| `lokasi [nama]` | Cari lokasi via koordinat GPS |
| `lok [nama]` | Cari lokasi via Google Maps URL |

#### C. Kirim Broadcast ke Semua Pelanggan

1. Buka menu **WhatsApp** → tab **Broadcast**
2. Tulis pesan broadcast (bisa tambahkan gambar atau dokumen PDF)
3. Pilih target: **Semua Pelanggan** atau **Pilih Manual**
4. Klik **"Kirim Broadcast"**
5. Pesan masuk antrean — dikirim otomatis dengan jeda **20–30 detik per pesan** untuk mencegah banned

#### D. Training Bot R-Care via WhatsApp

Admin dapat melatih bot langsung dari chat WhatsApp:

**Format:**
```
!train [kata kunci] | [jawaban]
```

**Contoh:**
```
!train harga paket murah | Paket Hemat 10Mbps hanya Rp 150.000/bulan. Hubungi kami untuk pemasangan! 😊
!train gangguan malam | Mohon maaf atas ketidaknyamanannya. Tim kami akan memeriksa jaringan segera. 🙏
```

Bot membalas: **"Berhasil Melatih R-Care! ✅"** jika training sukses.

> **⚠️ Hanya nomor admin** yang terdaftar di `WHATSAPP_ADMIN_NUMBER` yang dapat menggunakan perintah `!train`.

---

### 3.7. Peta Pelanggan (Web GIS)

1. Klik menu **Peta / GIS** di sidebar
2. Peta interaktif Leaflet.js tampil dengan **marker** lokasi setiap pelanggan
3. **Warna marker** menunjukkan status:
   - 🟢 **Hijau**: Pelanggan aktif & lunas
   - 🔴 **Merah**: Pelanggan diisolir / menunggak
   - 🟡 **Kuning**: Tagihan mendekati jatuh tempo
4. Klik marker untuk melihat detail pelanggan: nama, paket, status tagihan
5. Gunakan **kontrol zoom** (+/−) untuk memperbesar/perkecil peta
6. Gunakan fitur **Filter Layer** untuk menampilkan hanya pelanggan aktif atau diisolir

---

### 3.8. Mikrotik Manager

#### A. Melihat Status Router

1. Klik menu **Mikrotik** di sidebar
2. Sistem menampilkan daftar router yang terdaftar beserta status koneksi
3. Status **🟢 Online** berarti API Mikrotik dapat diakses
4. Status **🔴 Offline** berarti koneksi ke router terputus

#### B. Manajemen User Secret

1. Pilih router → klik tab **User Secret**
2. Daftar username PPPoE/Hotspot pelanggan tampil
3. Tersedia aksi:
   - **Nonaktifkan**: Disable user (isolir manual)
   - **Aktifkan**: Enable user kembali
   - **Hapus**: Remove user dari Mikrotik

#### C. Sinkronisasi Data

1. Klik tombol **"Sinkronisasi"** untuk menyamakan data pelanggan di sistem dengan Mikrotik
2. Sistem membandingkan username di database dengan username di Mikrotik
3. Perbedaan ditampilkan untuk dikonfirmasi admin

---

### 3.9. Klasifikasi Naive Bayes

#### A. Input Data Training

1. Klik menu **Klasifikasi** di sidebar
2. Buka tab **"Data Training"**
3. Tambahkan data satu per satu via formulir, atau **import dari Excel** (.xlsx)
4. Setiap data memiliki atribut: nama pelanggan, kategori ekonomi, penggunaan data, jumlah perangkat, harga paket
5. Tandai kolom **"Data Latih"** = `Ya` untuk data training, `Tidak` untuk data test

#### B. Proses Training & Evaluasi

1. Buka tab **"Training & Evaluasi"**
2. Klik tombol **"Mulai Training"**
3. Sistem memproses:
   - Menghitung probabilitas prior setiap kelas
   - Menghitung likelihood setiap atribut
   - Membentuk model Naive Bayes
4. Evaluasi otomatis dilakukan terhadap **data test** (`Data Latih = Tidak`)
5. Hasil evaluasi ditampilkan:

| Metrik | Keterangan |
|--------|------------|
| **Confusion Matrix** | Tabel perbandingan kelas prediksi vs aktual |
| **Akurasi (%)** | Persentase prediksi benar keseluruhan |
| **Precision (%)** | Ketepatan prediksi positif |
| **Recall (%)** | Kemampuan menemukan data positif |
| **F1-Score (%)** | Rata-rata harmonis Precision & Recall |

#### C. Klasifikasi Data Baru

1. Buka tab **"Klasifikasi Baru"**
2. Masukkan atribut pelanggan yang ingin diklasifikasi
3. Klik **"Klasifikasi"** — sistem menampilkan prediksi kelas kesejahteraan beserta nilai probabilitas

---

### 3.10. Laporan Keuangan (PDF)

1. Klik menu **Laporan** di sidebar
2. Pilih **Periode**: Bulan dan Tahun laporan
3. Pilih **Tipe Laporan**:
   - **Laporan Pendapatan**: Ringkasan pemasukan dari tagihan lunas
   - **Laporan Piutang**: Daftar tagihan yang belum dibayar
   - **Laporan Bulanan Lengkap**: Kombinasi semua transaksi
4. Klik **"Preview"** untuk melihat laporan di browser
5. Klik **"Export PDF"** untuk mengunduh file PDF resmi
6. Laporan PDF berisi:
   - Kop laporan resmi Rozitech Network
   - Tabel rinci transaksi
   - Total pemasukan, piutang, dan saldo bersih
   - Tanda tangan admin

---

### 3.11. Pengaturan Akun Admin

1. Klik **nama admin** di sudut kanan atas → pilih **"Pengaturan Akun"**
2. Tersedia pengaturan:
   - **Ubah Nama**: Nama tampilan admin
   - **Ubah Password**: Masukkan password lama → password baru → konfirmasi
3. Klik **"Simpan Perubahan"**

> **⚠️ Keamanan**: Ganti password secara berkala dan jangan bagikan kredensial login kepada pihak lain.

---

## BAB IV: PEMECAHAN MASALAH (TROUBLESHOOTING)

### 1. ❌ Bot WhatsApp Tidak Terhubung / Status Selalu "Connecting"

**Penyebab**: Sesi WhatsApp kadaluarsa, file sesi rusak, atau layanan bot tidak berjalan.

**Solusi**:
1. Pastikan layanan bot berjalan: cek terminal atau jalankan `pm2 status`
2. Hapus sesi lama dan scan QR ulang:
   ```bash
   cd whatsapp-bot
   # Hapus folder sesi
   rmdir /s /q sessions\main
   node index.js
   ```
3. Pastikan nilai `APP_URL` di `whatsapp-bot/.env` mengarah ke URL Laravel yang aktif
4. Pastikan `BOT_SECRET` di kedua file `.env` identik

---

### 2. ❌ OCR Salah Membaca Nominal / Tanggal pada Struk

**Penyebab**: Kualitas foto struk buruk — buram, gelap, terpotong, atau ada pantulan cahaya.

**Solusi**:
1. Minta pelanggan kirim ulang foto struk dengan pencahayaan yang baik
2. Foto harus diambil langsung (bukan screenshot bertumpuk)
3. Seluruh area nominal dan tanggal harus terlihat jelas
4. Jika OCR tetap tidak akurat, gunakan fitur **Tandai Lunas Manual** dan isi nominal secara manual

---

### 3. ❌ Error 500 — Halaman Tidak Dapat Ditampilkan

**Penyebab**: Cache konfigurasi atau view Laravel korup, atau terjadi error pada kode.

**Solusi**:
```bash
# Bersihkan semua cache Laravel
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Lihat log error terbaru
php artisan pail
# atau buka file: storage/logs/laravel.log
```

---

### 4. ❌ Mikrotik Tidak Terhubung / Status Offline

**Penyebab**: IP router berubah, port API Mikrotik tertutup, atau kredensial salah.

**Solusi**:
1. Buka **Winbox** atau **WebFig** Mikrotik → pastikan API Service aktif di port 8728
2. Cek konfigurasi router di menu **Mikrotik** → **Edit Router** → pastikan IP, port, username, password benar
3. Pastikan firewall Mikrotik mengizinkan koneksi dari IP server ke port API
4. Coba akses manual: `telnet [IP_ROUTER] 8728` — jika gagal, API belum aktif

---

### 5. ❌ Tagihan Otomatis Tidak Terbuat

**Penyebab**: Laravel Scheduler tidak berjalan di server.

**Solusi**:
1. Pastikan cron job sudah dikonfigurasi di server:
   ```bash
   # Tambahkan ke crontab (crontab -e)
   * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
   ```
2. Test generate tagihan manual via menu **Tagihan** → **Generate Tagihan**
3. Cek log scheduler: `storage/logs/laravel.log`

---

### 6. ❌ Notifikasi WhatsApp Tidak Terkirim ke Pelanggan

**Penyebab**: Bot terputus, queue worker tidak berjalan, atau nomor HP pelanggan salah format.

**Solusi**:
1. Cek status bot di menu **WhatsApp** — pastikan **🟢 Terhubung**
2. Pastikan Queue Worker berjalan:
   ```bash
   php artisan queue:work
   # atau dengan PM2:
   pm2 start "php artisan queue:work" --name laravel-queue
   ```
3. Pastikan nomor HP pelanggan format `628xxx` (bukan `08xxx` atau `+628xxx`)
4. Cek log antrian: `storage/logs/laravel.log`

---

### 7. ❌ Aset CSS/JS Tidak Muncul (Halaman Berantakan)

**Penyebab**: File build frontend belum ada atau Vite dev server tidak berjalan.

**Solusi**:
```bash
# Mode Development (hot reload)
npm run dev

# Mode Production (build statis)
npm run build

# Pastikan storage link ada
php artisan storage:link
```

---

### 8. ❌ Data Tidak Tersimpan / Gagal Input

**Penyebab**: Koneksi database terputus, validasi form gagal, atau sesi login kadaluarsa.

**Solusi**:
1. Cek apakah MySQL berjalan (Laragon/XAMPP Control Panel)
2. Periksa pesan error yang muncul di bawah form — biasanya ada indikasi field yang salah
3. Jika muncul pesan **"Sesi kadaluarsa"**, lakukan logout dan login kembali
4. Cek log: `storage/logs/laravel.log`

---

### 9. ❌ Peta GIS Tidak Muncul / Blank

**Penyebab**: Koneksi internet terputus (tile map membutuhkan internet) atau koordinat pelanggan kosong.

**Solusi**:
1. Pastikan perangkat terhubung ke internet (peta Leaflet membutuhkan CDN tile)
2. Pastikan pelanggan memiliki data koordinat GPS yang valid (Latitude & Longitude tidak kosong)
3. Cek apakah ada error di browser console (F12 → Console)

---

### 10. ❌ Login Gagal / Password Tidak Dikenali

**Penyebab**: Password salah atau akun admin belum dibuat.

**Solusi**:
```bash
# Reset password admin via artisan tinker
php artisan tinker

# Jalankan perintah berikut di dalam tinker:
App\Models\User::first()->update(['password' => bcrypt('password_baru_anda')]);
```

---

```
─── ✦ ───
Rotech Network — Rozitech WiFi Manager
Skripsi S1 Informatika — Universitas Muhammadiyah Gresik
Muhammad As'ad Muhibbin Akbar — 2026
Berkarya Nyata, Jaringan Terpercaya.
```
