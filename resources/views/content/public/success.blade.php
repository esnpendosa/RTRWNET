<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrasi Berhasil! | RT RW NET</title>
  <!-- Google Fonts: Outfit -->
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Boxicons -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <style>
    body {
      font-family: 'Outfit', sans-serif;
      background: linear-gradient(135deg, #f5f7fb 0%, #e4e9f2 100%);
      color: #2b3a4a;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    
    .success-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 24px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.8);
      padding: 40px;
      max-width: 550px;
      width: 100%;
      text-align: center;
    }
    
    .checkmark-circle {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background-color: #d1fae5;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 25px auto;
      color: #10b981;
      font-size: 3.5rem;
      animation: popIn 0.5s ease-out;
    }
    
    @keyframes popIn {
      0% { transform: scale(0.7); opacity: 0; }
      100% { transform: scale(1); opacity: 1; }
    }
    
    .code-badge {
      background-color: #f7fafc;
      border: 1px dashed #cbd5e0;
      color: #3f51b5;
      font-size: 1.5rem;
      font-weight: 700;
      letter-spacing: 2px;
      padding: 12px 24px;
      border-radius: 12px;
      display: inline-block;
      margin: 20px 0;
    }
    
    .btn-wa {
      background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
      border: none;
      color: white;
      padding: 14px 28px;
      border-radius: 12px;
      font-weight: 600;
      font-size: 1.05rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    
    .btn-wa:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
      color: white;
    }
    
    p.lead {
      color: #4a5568;
      font-size: 1.1rem;
    }
    
    .timeline {
      text-align: left;
      margin: 30px auto;
      max-width: 400px;
      border-left: 2px solid #e2e8f0;
      padding-left: 20px;
      position: relative;
    }
    
    .timeline-item {
      margin-bottom: 20px;
      position: relative;
    }
    
    .timeline-item::before {
      content: '';
      position: absolute;
      left: -27px;
      top: 5px;
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background-color: #cbd5e0;
      border: 2px solid #fff;
    }
    
    .timeline-item.active::before {
      background-color: #3f51b5;
    }
    
    .timeline-item h6 {
      font-weight: 600;
      margin-bottom: 2px;
      font-size: 0.95rem;
    }
    
    .timeline-item p {
      margin-bottom: 0;
      font-size: 0.85rem;
      color: #718096;
    }
  </style>
</head>
<body>

  <div class="success-card">
    <div class="checkmark-circle">
      <i class="bx bx-check-circle"></i>
    </div>
    
    <h3 class="fw-bold mb-2">Registrasi Dikirim!</h3>
    <p class="lead">Terima kasih <strong>{{ $pelanggan->nama_pelanggan }}</strong>, data registrasi Anda telah tersimpan dengan aman.</p>
    
    <div>
      <span class="text-muted d-block small">KODE REGISTRASI ANDA:</span>
      <span class="code-badge">{{ $pelanggan->kode_pelanggan }}</span>
    </div>

    <!-- Stepper process -->
    <div class="timeline">
      <div class="timeline-item active">
        <h6 class="text-primary">Registrasi Online Selesai</h6>
        <p>Data tersimpan di sistem kami dengan aman.</p>
      </div>
      <div class="timeline-item">
        <h6>Survei Lokasi & Kelayakan</h6>
        <p>Tim teknisi kami akan mengecek jarak rumah Anda ke box ODP terdekat.</p>
      </div>
      <div class="timeline-item">
        <h6>Penarikan Kabel & Aktivasi</h6>
        <p>Setelah jarak aman terkonfirmasi, wifi akan dipasang dan langsung aktif.</p>
      </div>
    </div>

    <div class="mt-4 pt-2">
      <p class="small text-muted mb-3">Untuk mempercepat proses survei, Anda dapat langsung menghubungi Admin kami melalui tombol WhatsApp di bawah ini:</p>
      <a href="{{ $waUrl }}" target="_blank" class="btn btn-wa w-100 justify-content-center py-3">
        <i class="bx bxl-whatsapp" style="font-size: 1.4rem;"></i> Konfirmasi Melalui WhatsApp
      </a>
    </div>
  </div>

</body>
</html>
