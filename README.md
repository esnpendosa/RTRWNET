# 🌐 Rozitech WiFi Manager
### Sistem Manajemen Jaringan RTRWNet — Web GIS + AI Chatbot + Klasifikasi Naive Bayes

> **Skripsi** — Universitas Muhammadiyah Gresik (UMG)  
> **Penulis**: Muhammad As'ad Muhibbin Akbar  
> **Fokus**: Web GIS, Integrasi AI, Klasifikasi Naive Bayes & Optimasi Jaringan

---

## 📋 Daftar Isi

- [Tentang Sistem](#tentang-sistem)
- [Fitur Utama](#fitur-utama)
- [Teknologi yang Digunakan](#teknologi-yang-digunakan)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Panduan Instalasi](#panduan-instalasi)
- [Konfigurasi Environment](#konfigurasi-environment)
- [Menjalankan Sistem](#menjalankan-sistem)
- [Panduan Penggunaan](#panduan-penggunaan)
- [Panduan WhatsApp Bot](#panduan-whatsapp-bot)
- [Panduan Training AI](#panduan-training-ai)
- [Troubleshooting](#troubleshooting)

---

## 📖 Tentang Sistem

**Rozitech WiFi Manager** adalah sistem manajemen jaringan RTRWNet berbasis web yang mengintegrasikan:

- 🗺️ **Web GIS** — Peta interaktif untuk manajemen lokasi pelanggan
- 🤖 **AI Chatbot (R-Care)** — Bot WhatsApp otomatis dengan kecerdasan buatan
- 📊 **Klasifikasi Naive Bayes** — Klasifikasi tingkat kesejahteraan pelanggan
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

Pastikan perangkat Anda memenuhi persyaratan berikut sebelum instalasi:

| Software | Versi Minimum | Keterangan |
|---|---|---|
| **PHP** | 8.2+ | Dengan ekstensi: `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `gd` |
| **Composer** | 2.x | Dependency manager PHP |
| **Node.js** | 18.x LTS | Untuk WhatsApp Bot & Vite |
| **NPM** | 9.x+ | Otomatis terinstal bersama Node.js |
| **MySQL** | 8.0+ | Database utama |
| **Git** | 2.x+ | Version control |
| **PM2** | Latest | Process manager untuk bot (opsional, direkomendasikan) |

> **💡 Rekomendasi**: Gunakan **Laragon** di Windows atau **XAMPP** sebagai local server.

---

## 🚀 Panduan Instalasi

### Langkah 1 — Clone Repositori

```bash
git clone https://github.com/username/rozitech-wifi-manager.git
cd rozitech-wifi-manager
```

Atau jika menggunakan Laragon, taruh folder project di:
```
C:\laragon\www\skripsi
```

---

### Langkah 2 — Instalasi Dependensi Laravel (PHP)

```bash
composer install
```

> Proses ini akan mengunduh semua package PHP yang diperlukan ke folder `vendor/`.

---

### Langkah 3 — Konfigurasi Environment Laravel

Salin file `.env.example` menjadi `.env`:

```bash
cp .env.example .env
```

Kemudian buka file `.env` dan sesuaikan konfigurasinya (lihat bagian [Konfigurasi Environment](#konfigurasi-environment)).

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

Masuk ke folder bot dan instal dependensinya:

```bash
cd whatsapp-bot
npm install
```

Salin file environment bot:
```bash
cp .env.example .env
```

Kemudian konfigurasi file `whatsapp-bot/.env` (lihat bagian [Konfigurasi Bot](#2-konfigurasi-whatsapp-bot-whatsapp-botenv)).

---

## ⚙️ Konfigurasi Environment

### 1. Konfigurasi Laravel (`.env`)

Buka file `.env` di root project dan isi nilai-nilai berikut:

```env
# ── Aplikasi ────────────────────────────────────────
APP_NAME="Rozitech WiFi Manager"
APP_ENV=local           # Ganti ke 'production' saat deploy
APP_KEY=                # Diisi otomatis oleh: php artisan key:generate
APP_DEBUG=true          # Ganti ke 'false' saat production
APP_URL=http://localhost # Ganti ke URL publik saat deploy (misal: https://net.rozitech.co.id)

# ── Database ─────────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rozitech_wifi   # Nama database yang sudah dibuat
DB_USERNAME=root             # Username MySQL
DB_PASSWORD=                 # Password MySQL (kosong jika default Laragon)

# ── WhatsApp Bot ──────────────────────────────────────
BOT_URL=http://127.0.0.1:3000       # URL WhatsApp bot berjalan
BOT_SECRET=rozitech-bot-secret-2024  # Secret key (harus sama dengan bot)

# ── OpenRouter AI (untuk Chatbot R-Care) ──────────────
OPENROUTER_API_KEY=sk-or-xxxxxxxxxxxx   # Dapatkan di: https://openrouter.ai
OPENROUTER_MODEL=google/gemini-flash-1.5 # Model AI yang digunakan

# ── Midtrans (Payment Gateway) ────────────────────────
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxx
MIDTRANS_IS_PRODUCTION=false  # Ganti ke 'true' saat production
```

---

### 2. Konfigurasi WhatsApp Bot (`whatsapp-bot/.env`)

```env
BOT_PORT=3000                                   # Port bot berjalan
BOT_SECRET=rozitech-bot-secret-2024             # Harus SAMA dengan BOT_SECRET di Laravel .env
APP_URL=http://127.0.0.1:8000                   # URL Laravel (local) atau URL publik saat deploy
WHATSAPP_ADMIN_NUMBER=6281234567890@s.whatsapp.net  # Nomor admin (format: 628xxx@s.whatsapp.net)
ALLOWED_SESSIONS=main                           # Nama sesi WhatsApp
```

> **⚠️ Penting**: Nilai `BOT_SECRET` di `.env` Laravel dan `whatsapp-bot/.env` **harus identik**.

---

## ▶️ Menjalankan Sistem

### Mode Development (Lokal)

Jalankan semua service secara bersamaan dengan perintah ini di root project:

```bash
composer run dev
```

Perintah ini akan menjalankan sekaligus:
- `php artisan serve` — Server Laravel di `http://127.0.0.1:8000`
- `npm run dev` — Vite untuk hot-reload aset frontend
- `php artisan queue:listen` — Worker antrean (untuk notifikasi, dll.)
- `php artisan pail` — Log viewer real-time

Kemudian, buka terminal **baru** untuk menjalankan WhatsApp bot:

```bash
cd whatsapp-bot
node index.js
```

---

### Mode Production (Server)

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

**C. Jalankan Bot dengan PM2 (agar tetap hidup di background):**
```bash
cd whatsapp-bot
pm2 start index.js --name whatsapp-bot
pm2 save
pm2 startup  # Agar bot otomatis start saat server reboot
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
- **Local**: `http://localhost/skripsi/public` atau `http://127.0.0.1:8000`
- **Production**: `https://net.rozitech.co.id`

### Login Admin

Gunakan akun admin yang telah dikonfigurasi. Hanya ada **satu akun admin** dalam sistem.

---

### Menu Dashboard

| Menu | Fungsi |
|---|---|
| **Dashboard** | Statistik ringkasan: pelanggan aktif, pendapatan bulan ini, tagihan jatuh tempo |
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
4. Pelanggan akan mendapat notifikasi WhatsApp otomatis

---

### Verifikasi Pembayaran (OCR)

1. Buka menu **Pembayaran** → **Verifikasi Transfer**
2. Upload foto/struk bukti transfer dari pelanggan
3. Sistem OCR membaca nominal dan tanggal secara otomatis
4. Konfirmasi hasil OCR dan klik **Tandai Lunas**
5. Nota PDF otomatis dikirim ke WhatsApp pelanggan

---

### Klasifikasi Naive Bayes

1. Buka menu **Klasifikasi** → **Data Training**
2. Upload atau input data pelanggan sebagai data latih
3. Buka tab **Training & Evaluasi** → klik **Mulai Training**
4. Sistem akan mengevaluasi menggunakan data test
5. Lihat hasil: **Confusion Matrix**, **Precision**, **Recall**, **F1-Score**

---

## 📲 Panduan WhatsApp Bot

### Menghubungkan WhatsApp

1. Buka menu **WhatsApp** di Dashboard
2. Klik **Mulai Sesi** — akan muncul QR Code
3. Buka WhatsApp di HP → **Perangkat Tertaut** → **Tautkan Perangkat**
4. Scan QR Code yang muncul
5. Tunggu status berubah menjadi **🟢 Terhubung**

> **💡 Alternatif**: Gunakan **Pairing Code** jika kamera tidak tersedia.  
> Masukkan nomor HP dan gunakan kode yang digenerate sistem di WhatsApp > Perangkat Tertaut > Tautkan dengan Nomor Telepon.

---

### Perintah Bot untuk Pelanggan

Pelanggan dapat mengirim pesan berikut ke nomor WhatsApp bisnis:

| Perintah | Fungsi |
|---|---|
| `halo` / `hai` | Salam pembuka & tampilkan menu |
| `cek tagihan` | Lihat status tagihan bulan ini |
| `bayar` | Panduan cara pembayaran |
| `lapor gangguan` | Laporkan gangguan internet |
| `paket` | Informasi paket yang tersedia |
| `lokasi [nama]` | Cari lokasi berdasarkan GPS |
| `lok [nama]` | Cari lokasi via Google Maps URL |

---

### Kirim Broadcast Manual

1. Buka menu **WhatsApp** → **Broadcast**
2. Pilih target: semua pelanggan / pelanggan tertentu / nomor manual
3. Tulis pesan (bisa sertakan gambar/PDF)
4. Klik **Kirim** — pesan masuk antrean dengan jeda anti-ban otomatis

---

## 🧠 Panduan Training AI (R-Care)

Admin dapat melatih bot melalui chat WhatsApp langsung:

### Format Perintah

```
!train [kata kunci] | [jawaban]
```

### Contoh

```
!train paket super | Paket Super Rozitech 50Mbps harga Rp 350.000/bulan. Info lebih lanjut hubungi admin ya Kak! 😊

!train cara bayar | Pembayaran bisa via Transfer Bank Mandiri No. 1234567890 a/n Rozitech Network. Setelah transfer, kirim foto struk ke sini ya Kak!

!train jam operasional | Kami melayani Senin–Sabtu pukul 08.00–17.00 WIB. Di luar jam tersebut silakan tinggalkan pesan, kami akan segera merespons! 🙏
```

> **⚠️ Catatan**: Hanya nomor admin yang terdaftar (`WHATSAPP_ADMIN_NUMBER`) yang bisa menggunakan perintah `!train`.

Setelah melatih, bot akan membalas: **"Berhasil Melatih R-Care! ✅"**

---

## 🔧 Troubleshooting

### ❌ Bot WhatsApp Tidak Terhubung

**Gejala**: Status bot selalu `connecting` atau `qr`.

**Solusi**:
1. Cek apakah bot berjalan: `pm2 status` atau cek terminal
2. Pastikan server Laravel bisa diakses dari bot (`APP_URL` di `whatsapp-bot/.env`)
3. Hapus sesi lama dan scan ulang QR:
   ```bash
   # Di folder whatsapp-bot
   rm -rf sessions/main
   node index.js
   ```

---

### ❌ Error `php artisan migrate` — Database Not Found

**Solusi**:
1. Pastikan MySQL berjalan (cek di Laragon/XAMPP)
2. Pastikan database sudah dibuat: `CREATE DATABASE rozitech_wifi;`
3. Cek konfigurasi `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD` di `.env`

---

### ❌ OCR Tidak Akurat / Salah Baca Nominal

**Solusi**:
1. Pastikan foto struk cerah, tidak buram, dan tidak terpotong
2. Gunakan foto asli (bukan screenshot bertumpuk)
3. Nominal harus terlihat jelas dengan format angka yang standar

---

### ❌ Halaman Menampilkan Error 500

**Solusi**:
```bash
# Bersihkan cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Cek log error
php artisan pail
# atau
tail -f storage/logs/laravel.log
```

---

### ❌ Aset CSS/JS Tidak Muncul

**Solusi**:
```bash
# Mode development
npm run dev

# Mode production
npm run build
```

---

### ❌ Notifikasi WhatsApp Tidak Terkirim

**Solusi**:
1. Cek status sesi di menu **WhatsApp** Dashboard
2. Pastikan Queue Worker berjalan: `php artisan queue:work`
3. Cek log: `storage/logs/laravel.log`

---

## 📁 Struktur Direktori

```
skripsi/
├── app/
│   ├── Http/Controllers/   # Controller Laravel
│   ├── Models/             # Model Eloquent
│   └── Services/           # Service classes (Mikrotik, WhatsApp, dll.)
├── database/
│   ├── migrations/         # Skema database
│   └── seeders/            # Data awal
├── public/                 # Aset publik (CSS, JS, images)
├── resources/
│   └── views/              # Template Blade
├── routes/
│   └── web.php             # Definisi route
├── whatsapp-bot/           # WhatsApp Bot (Node.js)
│   ├── index.js            # Entry point bot
│   ├── sessions/           # Data sesi WhatsApp (auto-generated)
│   └── .env                # Konfigurasi bot
├── .env                    # Konfigurasi Laravel
└── README.md               # Dokumentasi ini
```

---

## 📄 Lisensi

Proyek ini dibuat untuk keperluan skripsi dan bersifat akademis.

---

**Author**: Muhammad As'ad Muhibbin Akbar  
**Institusi**: Universitas Muhammadiyah Gresik (UMG)  
**Status**: ✅ Production Ready & Thesis Verified
