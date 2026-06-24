# 🌐 Rozitech WiFi Manager
### Sistem Manajemen Jaringan RTRWNet — Web GIS + AI Chatbot + KNN

> **Skripsi** — Universitas Muhammadiyah Gresik (UMG)  
> **Penulis**: Muhammad As'ad Muhibbin Akbar  
> **Fokus**: Web GIS, Integrasi AI, Klasifikasi Naive Bayes & Optimasi Jaringan  
> **Repository**: https://github.com/esnpendosa/RTRWNET

---

## 📋 Daftar Isi

1. [Tentang Sistem](#-tentang-sistem)
2. [Fitur Utama](#-fitur-utama)
3. [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
4. [Persyaratan Sistem](#-persyaratan-sistem)
5. [Panduan Instalasi](#-panduan-instalasi)
6. [Konfigurasi Environment](#-konfigurasi-environment)
7. [Menjalankan Sistem](#-menjalankan-sistem)
8. [Panduan Penggunaan](#-panduan-penggunaan)
9. [Panduan WhatsApp Bot](#-panduan-whatsapp-bot)
10. [Panduan Training AI (R-Care)](#-panduan-training-ai-r-care)
11. [Troubleshooting](#-troubleshooting)
12. [Struktur Direktori](#-struktur-direktori)

---

## 📖 Tentang Sistem

**Rozitech WiFi Manager** adalah sistem manajemen jaringan RTRWNet berbasis web yang dikembangkan sebagai tugas akhir (skripsi) di Universitas Muhammadiyah Gresik. Sistem ini mengintegrasikan:

- 🗺️ **Web GIS** — Peta interaktif untuk manajemen lokasi pelanggan
- 🤖 **AI Chatbot (R-Care)** — Bot WhatsApp otomatis berbasis kecerdasan buatan
- 📊 **Klasifikasi Naive Bayes** — Klasifikasi tingkat kesejahteraan pelanggan berdasarkan data sosial-ekonomi
- 💳 **Billing Otomatis** — Tagihan, OCR verifikasi transfer, dan isolir otomatis Mikrotik

---

## ✨ Fitur Utama

### 1. 💰 Billing & OCR Automation
- **Smart OCR**: Verifikasi bukti transfer otomatis via Tesseract.js dengan fuzzy matching nominal & tanggal
- **PDF Receipt**: Nota pembayaran otomatis dikirim ke WhatsApp pelanggan
- **Isolir Otomatis**: Sinkronisasi real-time dengan Mikrotik (putus/aktif berdasarkan status bayar)
- **Flexible Billing**: Siklus tagihan dinamis (Tanggal 1–28) sesuai tanggal instalasi

### 2. 🤖 WhatsApp AI Bot (R-Care)
- **Self-Service**: Pelanggan cek tagihan, lapor gangguan, dan konfirmasi bayar via chat
- **AI Integration**: OpenRouter (Gemini/Llama) untuk jawaban teknis cerdas
- **Multi-Media**: Kirim teks, gambar, dan dokumen PDF secara otomatis
- **Pencarian Lokasi**: Berbasis koordinat GPS maupun URL Google Maps

### 3. 📊 Klasifikasi Naive Bayes
- Klasifikasi tingkat kesejahteraan pelanggan berdasarkan kriteria ekonomis
- Evaluasi akurasi dengan Confusion Matrix, Precision, Recall, dan F1-Score
- Training data dan test data terpisah untuk validasi model
- Hanya admin tunggal yang bisa akses dan mengelola sistem

### 4. 🗺️ Web GIS & Peta
- Peta interaktif pelanggan menggunakan Leaflet.js
- Visualisasi jarak dan koordinat akurat (Haversine Formula)
- Manajemen lokasi BTS/router via peta

### 5. 🌐 Mikrotik Manager
- Manajemen User Secret & Profile Mikrotik dari Dashboard Laravel
- Monitoring pelanggan aktif secara real-time
- Sinkronisasi data router otomatis

---

## 🛠️ Teknologi yang Digunakan

| Komponen | Teknologi |
|---|---|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Blade + Vite + Bootstrap |
| Database | MySQL 8.0+ |
| WhatsApp Bot | Node.js + Baileys |
| OCR | Tesseract.js |
| AI Engine | OpenRouter API (Gemini/Llama) |
| PDF Generator | DomPDF (Laravel) |
| GIS / Peta | Leaflet.js |
| Payment Gateway | Midtrans |
| Process Manager | PM2 |

---

## 📦 Persyaratan Sistem

Pastikan perangkat memenuhi persyaratan berikut sebelum instalasi:

| Software | Versi Minimum | Keterangan |
|---|---|---|
| **PHP** | 8.2+ | Ekstensi: `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `gd` |
| **Composer** | 2.x | Dependency manager PHP |
| **Node.js** | 18.x LTS | Untuk WhatsApp Bot & Vite |
| **NPM** | 9.x+ | Otomatis terinstal bersama Node.js |
| **MySQL** | 8.0+ | Database utama |
| **Git** | 2.x+ | Version control |
| **PM2** | Latest | Process manager bot (direkomendasikan) |

> **💡 Rekomendasi**: Gunakan **Laragon** di Windows atau **XAMPP** sebagai local server.

---

## 🚀 Panduan Instalasi

### Langkah 1 — Clone Repositori

```bash
git clone git@github.com:esnpendosa/RTRWNET.git skripsi
cd skripsi
```

---

### Langkah 2 — Instalasi Dependensi Laravel (PHP)

```bash
composer install
```

> Perintah ini mengunduh semua package PHP yang diperlukan ke folder `vendor/`.

---

### Langkah 3 — Konfigurasi Environment Laravel

Salin file contoh environment:

```bash
cp .env.example .env
```

Kemudian edit file `.env` sesuai konfigurasi lokal Anda (lihat bagian [Konfigurasi Environment](#-konfigurasi-environment)).

---

### Langkah 4 — Generate Application Key

```bash
php artisan key:generate
```

---

### Langkah 5 — Setup Database

**A. Buat database MySQL baru:**
```sql
CREATE DATABASE rozitech_wifi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**B. Jalankan migrasi database:**
```bash
php artisan migrate
```

**C. (Opsional) Isi data awal:**
```bash
php artisan db:seed
```

---

### Langkah 6 — Setup Storage Link

```bash
php artisan storage:link
```

---

### Langkah 7 — Instalasi Dependensi Frontend (Node.js)

```bash
npm install
```

---

### Langkah 8 — Instalasi WhatsApp Bot

```bash
cd whatsapp-bot
npm install
cp .env.example .env
```

Kemudian konfigurasi file `whatsapp-bot/.env` (lihat bagian [Konfigurasi Bot](#2-konfigurasi-whatsapp-bot-whatsapp-botenv)).

---

## ⚙️ Konfigurasi Environment

### 1. Konfigurasi Laravel (`.env`)

```env
# ── Aplikasi ────────────────────────────────────────────
APP_NAME="Rozitech WiFi Manager"
APP_ENV=local            # Ganti ke 'production' saat deploy
APP_KEY=                 # Diisi otomatis: php artisan key:generate
APP_DEBUG=true           # Ganti ke 'false' saat production
APP_URL=http://localhost  # Ganti ke URL publik saat deploy

# ── Database ─────────────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rozitech_wifi   # Nama database yang dibuat
DB_USERNAME=root             # Username MySQL
DB_PASSWORD=                 # Password MySQL (kosong = default Laragon)

# ── WhatsApp Bot ──────────────────────────────────────────
BOT_URL=http://127.0.0.1:3000        # URL bot berjalan
BOT_SECRET=rozitech-bot-secret-2024  # Secret key (harus sama dengan bot)

# ── OpenRouter AI ─────────────────────────────────────────
OPENROUTER_API_KEY=sk-or-xxxxxxxxxxxx    # Dapatkan di: https://openrouter.ai
OPENROUTER_MODEL=google/gemini-flash-1.5

# ── Midtrans (Payment Gateway) ────────────────────────────
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxx
MIDTRANS_IS_PRODUCTION=false  # Ganti 'true' saat production
```

---

### 2. Konfigurasi WhatsApp Bot (`whatsapp-bot/.env`)

```env
BOT_PORT=3000                                        # Port bot berjalan
BOT_SECRET=rozitech-bot-secret-2024                  # HARUS SAMA dengan Laravel .env
APP_URL=http://127.0.0.1:8000                        # URL Laravel
WHATSAPP_ADMIN_NUMBER=6281234567890@s.whatsapp.net   # Nomor admin (format: 628xxx@s.whatsapp.net)
ALLOWED_SESSIONS=main                                # Nama sesi WhatsApp
```

> **⚠️ Penting**: Nilai `BOT_SECRET` di `.env` Laravel dan `whatsapp-bot/.env` **harus identik**.

---

## ▶️ Menjalankan Sistem

### Mode Development (Lokal)

Jalankan semua service sekaligus dari root project:

```bash
composer run dev
```

Perintah ini menjalankan bersamaan:
- `php artisan serve` — Server Laravel di `http://127.0.0.1:8000`
- `npm run dev` — Vite hot-reload untuk aset frontend
- `php artisan queue:listen` — Worker antrean notifikasi
- `php artisan pail` — Log viewer real-time

Buka terminal **baru** untuk menjalankan WhatsApp bot:

```bash
cd whatsapp-bot
node index.js
```

---

### Mode Production (Server/VPS)

**A. Build aset frontend:**
```bash
npm run build
```

**B. Optimasi Laravel:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**C. Jalankan Bot dengan PM2:**
```bash
cd whatsapp-bot
pm2 start index.js --name whatsapp-bot
pm2 save
pm2 startup   # Agar bot otomatis start saat server reboot
```

**D. Jalankan Queue Worker dengan PM2:**
```bash
# Di folder root project
pm2 start "php artisan queue:work --tries=3" --name laravel-queue
pm2 save
```

---

## 📱 Panduan Penggunaan

### Akses Dashboard

Setelah sistem berjalan, buka browser dan akses:
- **Local**: `http://127.0.0.1:8000` atau `http://localhost/skripsi/public`
- **Production**: URL domain yang dikonfigurasi

### Login Admin

Sistem hanya memiliki **satu akun admin**. Gunakan akun yang telah dikonfigurasi saat setup.

---

### Menu Dashboard

| Menu | Fungsi |
|---|---|
| **Dashboard** | Statistik ringkasan: pelanggan aktif, pendapatan, tagihan jatuh tempo |
| **Pelanggan** | Kelola data pelanggan, paket, dan status aktif/isolir |
| **Tagihan** | Lihat semua tagihan, tandai lunas, generate nota PDF |
| **Pembayaran** | Verifikasi bukti transfer dengan OCR otomatis |
| **Peta / GIS** | Lihat semua pelanggan di peta interaktif |
| **WhatsApp** | Kelola sesi bot, kirim broadcast, lihat status koneksi |
| **Mikrotik** | Manajemen router, user secret, dan profile Mikrotik |
| **Klasifikasi** | Training data dan evaluasi model Naive Bayes |
| **Laporan** | Export laporan keuangan ke PDF |
| **Pengaturan Akun** | Ubah password dan data akun admin |

---

### Manajemen Pelanggan

1. Buka menu **Pelanggan** → **Tambah Pelanggan**
2. Isi data: Nama, No. HP (format `628xxx`), Alamat, Koordinat GPS, Paket
3. Klik **Simpan** — sistem otomatis membuat tagihan bulan pertama
4. Pelanggan mendapat notifikasi WhatsApp otomatis

---

### Verifikasi Pembayaran (OCR)

1. Buka menu **Pembayaran** → **Verifikasi Transfer**
2. Upload foto/struk bukti transfer dari pelanggan
3. Sistem OCR membaca nominal dan tanggal secara otomatis
4. Konfirmasi hasil OCR → klik **Tandai Lunas**
5. Nota PDF otomatis dikirim ke WhatsApp pelanggan

---

### Klasifikasi Naive Bayes

1. Buka menu **Klasifikasi** → **Data Training**
2. Input atau upload data pelanggan sebagai data latih
3. Buka tab **Training & Evaluasi** → klik **Mulai Training**
4. Sistem mengevaluasi menggunakan data test (`data_latih = 0`)
5. Lihat hasil: **Confusion Matrix**, **Precision**, **Recall**, **F1-Score**

---

## 📲 Panduan WhatsApp Bot

### Menghubungkan WhatsApp

1. Buka menu **WhatsApp** di Dashboard
2. Klik **Mulai Sesi** — muncul QR Code
3. Buka WhatsApp di HP → **⋮ Menu** → **Perangkat Tertaut** → **Tautkan Perangkat**
4. Scan QR Code yang tampil di layar
5. Tunggu status berubah menjadi **🟢 Terhubung**

> **💡 Alternatif — Pairing Code**: Jika kamera tidak tersedia, masukkan nomor HP dan gunakan kode yang digenerate sistem, lalu di WhatsApp pilih: Perangkat Tertaut → Tautkan dengan Nomor Telepon.

---

### Perintah Bot untuk Pelanggan

| Perintah | Fungsi |
|---|---|
| `halo` / `hai` | Salam pembuka & tampilkan menu |
| `cek tagihan` | Lihat status tagihan bulan ini |
| `bayar` | Panduan cara pembayaran |
| `lapor gangguan` | Laporkan gangguan internet |
| `paket` | Informasi paket yang tersedia |
| `lokasi [nama]` | Cari lokasi berdasarkan koordinat GPS |
| `lok [nama]` | Cari lokasi via Google Maps URL |

---

### Kirim Broadcast Manual

1. Buka menu **WhatsApp** → **Broadcast**
2. Pilih target: semua pelanggan / pelanggan tertentu / nomor manual
3. Tulis pesan (bisa sertakan gambar/PDF)
4. Klik **Kirim** — pesan masuk antrean dengan jeda anti-ban otomatis (20–30 detik per pesan)

---

## 🧠 Panduan Training AI (R-Care)

Admin dapat melatih bot langsung melalui chat WhatsApp tanpa perlu masuk ke sistem.

### Format Perintah

```
!train [kata kunci] | [jawaban yang diinginkan]
```

> **Penting**: Gunakan tanda pemisah pipa `|` di antara kata kunci dan jawaban.  
> Hanya nomor yang terdaftar sebagai admin (`WHATSAPP_ADMIN_NUMBER`) yang dapat menggunakan perintah ini.

---

### Contoh Penggunaan

**Menambahkan informasi paket:**
```
!train paket super | Paket Super Rozitech kecepatannya 50Mbps harga Rp 350.000/bulan nggih Kak 😊
```

**Menambahkan info cara bayar:**
```
!train cara bayar | Pembayaran saged lewat Transfer Bank Mandiri (1234567890) a.n Rozitech Network. Setelah transfer, kirim foto struk ke sini ya Kak. Matur nuwun 🙏
```

**Menambahkan identitas AI:**
```
!train siapa kamu | Saya R-Care, asisten personal Rozitech Network. Ada yang bisa saya bantu?
```

**Menambahkan sapaan bahasa Jawa:**
```
!train sapaan jawa | Sugeng siang Kak, wonten ingkang saged R-Care bantu babagan internet?
```

---

### Tips Training Efektif

1. **Kata Kunci Singkat** — Gunakan kata yang sering ditanyakan pelanggan (misal: `harga`, `gangguan`, `lokasi`)
2. **Jawaban Lengkap** — Tuliskan jawaban sedetail mungkin agar pelanggan tidak perlu bertanya ulang
3. **Bahasa Manusiawi** — Tuliskan jawaban seolah sedang berbicara langsung dengan pelanggan
4. **Gunakan Emoji** — Emoji membuat pesan terasa lebih ramah dan tidak kaku

---

### Verifikasi Training

Setelah mengirim `!train`, bot akan membalas:
> **Berhasil Melatih R-Care! ✅**

Langsung tes dengan mengirim kata kunci tersebut via chat biasa untuk memastikan bot merespons dengan benar.

---

## 🔧 Troubleshooting

### ❌ Bot WhatsApp Tidak Terhubung

**Gejala**: Status bot selalu `connecting` atau terus menampilkan QR.

**Solusi**:
1. Pastikan bot berjalan: cek terminal atau `pm2 status`
2. Pastikan Laravel bisa diakses dari bot (cek `APP_URL` di `whatsapp-bot/.env`)
3. Hapus sesi lama dan scan ulang QR:
   ```bash
   cd whatsapp-bot
   # Hapus folder sesi
   rm -rf sessions/main
   node index.js
   ```

---

### ❌ Error saat `php artisan migrate`

**Gejala**: `SQLSTATE[HY000] [1049] Unknown database`.

**Solusi**:
1. Pastikan MySQL berjalan (cek Laragon/XAMPP Control Panel)
2. Buat database terlebih dahulu: `CREATE DATABASE rozitech_wifi;`
3. Cek nilai `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD` di `.env`

---

### ❌ OCR Tidak Akurat / Salah Baca Nominal

**Solusi**:
1. Pastikan foto struk cerah, tidak buram, dan tidak terpotong
2. Gunakan foto asli (bukan screenshot bertumpuk)
3. Nominal harus terlihat jelas dengan format angka standar

---

### ❌ Halaman Menampilkan Error 500

**Solusi**:
```bash
# Bersihkan semua cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Cek log error
php artisan pail
# atau buka langsung file log
# storage/logs/laravel.log
```

---

### ❌ Aset CSS/JS Tidak Muncul / Halaman Berantakan

**Solusi**:
```bash
# Mode development (hot reload)
npm run dev

# Mode production (build statis)
npm run build
```

---

### ❌ Notifikasi WhatsApp Tidak Terkirim

**Solusi**:
1. Cek status sesi di menu **WhatsApp** Dashboard — pastikan **🟢 Terhubung**
2. Pastikan Queue Worker berjalan: `php artisan queue:work`
3. Cek log: buka `storage/logs/laravel.log`

---

## 📁 Struktur Direktori

```
skripsi/
├── app/
│   ├── Console/Commands/       # Scheduled tasks (billing, reminder, isolir)
│   ├── Http/Controllers/       # Controller Laravel (Pelanggan, Tagihan, KNN, dll.)
│   ├── Models/                 # Model Eloquent (Pelanggan, Tagihan, dll.)
│   └── Services/               # Service class (Mikrotik, WhatsApp, KNN, dll.)
├── database/
│   ├── migrations/             # Skema database (buat/ubah tabel)
│   └── seeders/                # Data awal (akun admin, dll.)
├── public/                     # Aset publik (CSS, JS, gambar)
├── resources/
│   ├── menu/                   # Konfigurasi menu sidebar
│   └── views/                  # Template Blade (halaman web)
│       └── content/
│           ├── knn/            # Halaman klasifikasi Naive Bayes
│           └── pelanggan/      # Halaman manajemen pelanggan
├── routes/
│   └── web.php                 # Definisi semua route/URL sistem
├── whatsapp-bot/               # WhatsApp Bot (Node.js, terpisah dari Laravel)
│   ├── index.js                # Entry point bot — logika utama
│   ├── sessions/               # Data sesi WhatsApp (auto-generated, tidak di-commit)
│   ├── .env.example            # Contoh konfigurasi bot
│   └── package.json            # Dependensi Node.js
├── .env.example                # Contoh konfigurasi Laravel
├── composer.json               # Dependensi PHP
├── package.json                # Dependensi frontend (Vite)
└── README.md                   # Dokumentasi ini
```

---

## 📄 Lisensi

Proyek ini dikembangkan untuk keperluan akademis (Skripsi S1).

---

**Author**: Muhammad As'ad Muhibbin Akbar  
**NIM**: *(sesuaikan)*  
**Program Studi**: Informatika  
**Institusi**: Universitas Muhammadiyah Gresik (UMG)  
**Tahun**: 2026  
**Status Proyek**: ✅ Production Ready & Thesis Verified
