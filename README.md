# Rozitech WiFi Manager - Sistem Optimasi Spasial & Billing Otomatis

Sistem Manajemen Jaringan RTRWNet berbasis Laravel yang mengintegrasikan pemodelan geografis (**Web GIS**) dengan kecerdasan buatan (**AI Chatbot**) dan algoritma klasifikasi untuk optimasi operasional serta layanan keuangan mandiri.

## 🚀 Fitur Unggulan

### 1. Advanced Billing & OCR Automation ✅ (Terbaru)
*   **Smart OCR Verification**: Verifikasi bukti transfer otomatis menggunakan Tesseract.js dengan logika fuzzy matching untuk nominal dan tanggal.
*   **Digital PDF Receipt**: Pembuatan nota pembayaran otomatis dalam format PDF profesional yang langsung dikirim ke WhatsApp pelanggan setelah pembayaran sukses.
*   **Isolir Otomatis**: Sinkronisasi real-time dengan Mikrotik untuk memutus/mengaktifkan layanan berdasarkan status pembayaran.
*   **Flexible Billing Cycle**: Penjadwalan tagihan dinamis (Tanggal 1 - 28) sesuai tanggal instalasi pelanggan.

### 2. WhatsApp AI Bot (Rozitech AI) & Gateway
*   **Multi-Media Support**: Gateway yang mampu mengirim teks, gambar, dan dokumen (PDF) secara otomatis via URL atau Buffer.
*   **Self-Service Billing**: Pelanggan bisa cek tagihan, lapor gangguan, dan konfirmasi bayar hanya melalui chat.
*   **AI Integration**: Menggunakan OpenRouter (Gemini/Llama) untuk menjawab pertanyaan teknis dan paket internet secara cerdas.
*   **Dual-Mode Location Search**:
    *   `lokasi [query]`: Pencarian lokasi berbasis koordinat GIS (**GPS Latitude/Longitude**).
    *   `lok [query]`: Pencarian lokasi menggunakan **Manual Google Maps URL** dari database.

### 3. Analisis & Klasifikasi KNN (Sesuai Proposal Skripsi)
*   **Spatial KNN (Optimasi Peta)**: Menggunakan algoritma *K-Nearest Neighbor* dengan perhitungan jarak fisik nyata (**Haversine Formula**) dalam satuan **Kilometer (KM)**.
*   **Multidimensi Analisis**: Klasifikasi pelanggan berdasarkan 3 kriteria utama: Pemakaian (Usage), Beban Jaringan (Devices), dan Nilai Ekonomis (Price).
*   **Visualisasi Presisi**: Menampilkan koordinat Lat/Lng dan jarak tempuh terdekat secara akurat untuk kebutuhan sidang/presentasi.

### 4. Pelaporan & Manajemen
*   **Financial Reports**: Ekspor laporan pendapatan, piutang, dan statistik bulanan ke format PDF.
*   **Mikrotik Manager**: Manajemen User Secret dan Profile Mikrotik langsung dari Dashboard Laravel.

## 🛠️ Teknologi & Algoritma

*   **Framework**: Laravel 12 & Node.js (Baileys WhatsApp API)
*   **Database**: MySQL (Added `maps_url` column for precise location linking)
*   **GIS Engine**: Leaflet.js & Haversine Distance
*   **AI Engine**: OpenRouter API & Tesseract OCR
*   **Algoritma Utama**:
    *   **KNN (K-Nearest Neighbor)**: Untuk Klasifikasi & Clustering Pelanggan.
    *   **Fuzzy Matching**: Untuk verifikasi nominal pembayaran pada struk.

## ⚙️ Instalasi & Update

1. **Backend (Laravel)**:
   ```bash
   composer install
   php artisan migrate
   php artisan storage:link
   ```
2. **WhatsApp Bot**:
   ```bash
   cd whatsapp-bot
   npm install
   pm2 start index.js --name whatsapp-bot
   ```
3. **Environment**: Pastikan file `.env` di root dan `whatsapp-bot/.env` sudah terkonfigurasi dengan benar.

---
**Author**: Muhammad As'ad Muhibbin Akbar
**Institutional**: Universitas Muhammadiyah Gresik (UMG)
**Focus**: Web GIS, AI Integration, & Network Optimization
**Project Status**: Production Ready & Thesis Verified ✅

