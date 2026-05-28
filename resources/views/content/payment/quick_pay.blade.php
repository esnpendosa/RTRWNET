@extends('layouts/blankLayout')

@section('title', 'Pembayaran Tagihan - RTRW Net')

@section('page-style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
    body {
        background: linear-gradient(135deg, #f5f7ff 0%, #e8eaff 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Oxygen, Ubuntu, Cantarell, 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
    }
    .payment-card {
        max-width: 500px;
        width: 95%;
        border-radius: 24px;
        border: none;
        box-shadow: 0 20px 50px rgba(105, 108, 255, 0.15);
        overflow: hidden;
        margin: 20px 0;
    }
    .payment-header {
        background: linear-gradient(135deg, #696cff 0%, #7e80ff 100%);
        padding: 35px 25px;
        text-align: center;
        color: white;
        position: relative;
    }
    .payment-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 12px;
        background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,42.4V0Z" style="fill:%23ffffff;opacity:.15"></path></svg>') bottom;
        background-size: cover;
    }
    .payment-body {
        padding: 30px;
        background: white;
    }
    .amount-box {
        background: #f8f9ff;
        border: 1px solid rgba(105, 108, 255, 0.1);
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        margin-bottom: 25px;
    }
    .amount-value {
        font-size: 2.2rem;
        font-weight: 800;
        color: #696cff;
        letter-spacing: -0.5px;
    }
    .nav-tabs-custom {
        display: flex;
        border-radius: 12px;
        background: #f1f2f7;
        padding: 4px;
        margin-bottom: 25px;
    }
    .nav-tabs-custom .tab-link {
        flex: 1;
        text-align: center;
        padding: 12px 10px;
        font-size: 0.9rem;
        font-weight: 700;
        color: #697a8d;
        border-radius: 9px;
        cursor: pointer;
        transition: all 0.25s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .nav-tabs-custom .tab-link.active {
        background: white;
        color: #696cff;
        box-shadow: 0 4px 10px rgba(105, 108, 255, 0.08);
    }
    .bank-card {
        background: linear-gradient(145deg, #ffffff, #fcfcff);
        border: 1px solid #eef0f7;
        border-radius: 16px;
        padding: 18px;
        margin-bottom: 15px;
        position: relative;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .bank-card:hover {
        border-color: rgba(105, 108, 255, 0.3);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(105, 108, 255, 0.05);
    }
    .bank-logo {
        width: 65px;
        font-weight: 850;
        font-size: 1.3rem;
        letter-spacing: -1px;
    }
    .bank-bca { color: #005caa; }
    .bank-bri { color: #003399; }
    .bank-dana { color: #118ee9; }
    
    .copy-btn {
        background: #f3f4fd;
        border: none;
        padding: 8px 12px;
        border-radius: 8px;
        color: #696cff;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .copy-btn:hover {
        background: #696cff;
        color: white;
    }
    .upload-area {
        border: 2px dashed #d9dee3;
        border-radius: 16px;
        padding: 25px;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s ease;
        background: #fafafc;
    }
    .upload-area:hover {
        border-color: #696cff;
        background: rgba(105, 108, 255, 0.02);
    }
    .btn-submit {
        padding: 14px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(105, 108, 255, 0.15);
    }
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(105, 108, 255, 0.3);
    }
    .whatsapp-btn {
        background: #25d366;
        color: white;
        border: none;
        padding: 14px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        transition: all 0.3s ease;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.2);
    }
    .whatsapp-btn:hover {
        background: #1ebd54;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37, 211, 102, 0.3);
    }
    .status-alert {
        border-radius: 16px;
        padding: 20px;
        background: #eef9f2;
        border: 1px solid #b7ebb5;
        color: #28c76f;
        text-align: center;
        margin-bottom: 25px;
    }
</style>
@endsection

@section('content')
<div class="payment-card">
    <div class="payment-header">
        <h4 class="mb-1 text-white fw-bold"><i class="fa-solid fa-file-invoice-dollar me-2"></i> Pembayaran Tagihan WiFi</h4>
        <p class="mb-0 opacity-75 small">ROZITECH MULTIMEDIA INDONESIA</p>
    </div>
    
    <div class="payment-body">
        @if(auth()->check() && auth()->user()->hasPermission('pelanggan_manage'))
            <div class="row g-2 mb-4">
                <div class="col-6">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-sm w-100 py-2 rounded-3 fw-bold">
                        <i class="fa-solid fa-gauge me-1"></i> Dashboard Admin
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('scan.index') }}" class="btn btn-primary btn-sm w-100 py-2 rounded-3 fw-bold">
                        <i class="fa-solid fa-qrcode me-1"></i> Scanner QR
                    </a>
                </div>
            </div>
        @endif

        <div class="mb-4 text-center">
            <span class="text-muted text-uppercase mb-1 d-block" style="font-size: 0.7rem; letter-spacing: 1px; font-weight: 700;">Data Pelanggan</span>
            <h5 class="mb-1 fw-bold text-dark">{{ $pelanggan->nama_pelanggan }}</h5>
            <span class="badge bg-label-primary px-3 py-1 rounded-pill" style="font-weight: 700;">{{ $pelanggan->kode_pelanggan }}</span>
        </div>

        <div class="amount-box">
            <span class="text-muted mb-1 d-block" style="font-size: 0.85rem;">Total Tagihan</span>
            <div class="amount-value">Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}</div>
            <span class="badge bg-label-secondary mt-1">Periode: {{ $tagihan->bulan }} / {{ $tagihan->tahun }}</span>
        </div>

        @if($tagihan->bukti_bayar)
            <div class="status-alert">
                <i class="fa-solid fa-circle-notch fa-spin fs-4 mb-2 d-block"></i>
                <h6 class="fw-bold mb-1 text-success">Bukti Transfer Berhasil Dikirim</h6>
                <p class="small mb-0 text-muted">Admin sedang melakukan verifikasi. Koneksi Anda akan aktif otomatis setelah pembayaran disetujui.</p>
                <div class="mt-3">
                    <a href="{{ asset('storage/' . $tagihan->bukti_bayar) }}" target="_blank" class="btn btn-xs btn-outline-success rounded-pill">
                        <i class="fa-solid fa-eye me-1"></i> Lihat Bukti Terkirim
                    </a>
                </div>
            </div>
        @endif

        @if(!$tagihan->bukti_bayar)
            <!-- Tab Selector -->
            <div class="nav-tabs-custom">
                <div class="tab-link active" onclick="switchTab('transfer')">
                    <i class="fa-solid fa-university"></i> Transfer Bank
                </div>
                <div class="tab-link" onclick="switchTab('cash')">
                    <i class="fa-solid fa-hand-holding-dollar"></i> Bayar Tunai
                </div>
            </div>

            <!-- Tab 1: Transfer -->
            <div id="tab-transfer" class="tab-content-item">
                <p class="text-muted small mb-3 text-center">Silakan transfer sesuai nominal ke salah satu rekening di bawah ini:</p>
                
                <!-- BCA Card -->
                <div class="bank-card">
                    <div>
                        <div class="bank-logo bank-bca">BCA</div>
                        <div class="small fw-bold text-dark mt-1">7415234155</div>
                        <div class="text-muted" style="font-size: 0.75rem;">A.N. FACHRUR ROZI</div>
                    </div>
                    <button class="copy-btn" onclick="copyNumber('7415234155', this)">
                        <i class="fa-regular fa-copy me-1"></i> Salin
                    </button>
                </div>

                <!-- BRI Card -->
                <div class="bank-card">
                    <div>
                        <div class="bank-logo bank-bri">BRI</div>
                        <div class="small fw-bold text-dark mt-1">621001017663537</div>
                        <div class="text-muted" style="font-size: 0.75rem;">A.N. FACHRUR ROZI</div>
                    </div>
                    <button class="copy-btn" onclick="copyNumber('621001017663537', this)">
                        <i class="fa-regular fa-copy me-1"></i> Salin
                    </button>
                </div>

                <!-- DANA Card -->
                <div class="bank-card">
                    <div>
                        <div class="bank-logo bank-dana"><i class="fa-solid fa-wallet me-1"></i> DANA</div>
                        <div class="small fw-bold text-dark mt-1">082187827382</div>
                        <div class="text-muted" style="font-size: 0.75rem;">A.N. FACHRUR ROZI</div>
                    </div>
                    <button class="copy-btn" onclick="copyNumber('082187827382', this)">
                        <i class="fa-regular fa-copy me-1"></i> Salin
                    </button>
                </div>

                <!-- Upload Form -->
                <form action="{{ route('billing.confirm', $tagihan->id_tagihan) }}" method="POST" enctype="multipart/form-data" class="mt-4" id="upload-form">
                    @csrf
                    <input type="hidden" name="metode_pembayaran" value="transfer">
                    
                    <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-cloud-upload me-1 text-primary"></i> Upload Bukti Transfer</h6>
                    <div class="upload-area mb-3" onclick="document.getElementById('file-input').click()">
                        <i class="fa-solid fa-image-portrait text-muted fs-2 mb-2" id="upload-icon"></i>
                        <div class="small fw-bold text-dark" id="upload-text">Ketuk untuk pilih foto / berkas bukti</div>
                        <div class="text-muted" style="font-size: 0.7rem;">Format JPG, PNG, atau PDF (Max 3MB)</div>
                        <input type="file" name="bukti_bayar" id="file-input" style="display: none;" accept="image/*,application/pdf" onchange="fileSelected(this)" required>
                    </div>

                    @error('bukti_bayar')
                        <div class="text-danger small mb-3">{{ $message }}</div>
                    @enderror

                    <button type="submit" class="btn btn-primary w-100 btn-submit">
                        <i class="fa-solid fa-paper-plane me-2"></i> KIRIM KONFIRMASI
                    </button>
                </form>
            </div>

            <!-- Tab 2: Cash -->
            <div id="tab-cash" class="tab-content-item" style="display: none;">
                <div class="text-center py-3">
                    <div class="avatar avatar-xl bg-label-success mx-auto mb-3" style="width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-comments-dollar text-success fs-2"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Ingin Bayar Secara Tunai?</h6>
                    <p class="text-muted small px-3 mb-4">
                        Anda dapat langsung melakukan pembayaran secara tunai dengan mengunjungi kantor pusat kami atau melalui petugas penagihan kami di lapangan.
                    </p>
                    
                    <a href="https://wa.me/6285604118932?text=Halo%20Admin%2C%20saya%20ingin%20melakukan%20pembayaran%20secara%20tunai%20untuk%20pelanggan%20{{ urlencode($pelanggan->nama_pelanggan) }}%20({{ $pelanggan->kode_pelanggan }})%20periode%20{{ $tagihan->bulan }}%2F{{ $tagihan->tahun }}%2E" 
                       class="whatsapp-btn" target="_blank">
                        <i class="fa-brands fa-whatsapp fs-5"></i> Konfirmasi Pembayaran Tunai
                    </a>
                </div>
            </div>
        @endif

        <p class="text-center text-muted small mt-4 mb-0" style="font-size: 0.75rem;">
            Terima kasih telah berlangganan layanan internet kami.
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function switchTab(tab) {
        document.querySelectorAll('.tab-link').forEach(link => link.classList.remove('active'));
        document.querySelectorAll('.tab-content-item').forEach(content => content.style.display = 'none');
        
        if (tab === 'transfer') {
            document.querySelectorAll('.tab-link')[0].classList.add('active');
            document.getElementById('tab-transfer').style.display = 'block';
        } else {
            document.querySelectorAll('.tab-link')[1].classList.add('active');
            document.getElementById('tab-cash').style.display = 'block';
        }
    }

    function copyNumber(number, btn) {
        navigator.clipboard.writeText(number).then(() => {
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Tersalin';
            btn.style.background = '#28c76f';
            btn.style.color = 'white';
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = '#f3f4fd';
                btn.style.color = '#696cff';
            }, 2000);
        });
    }

    function fileSelected(input) {
        const file = input.files[0];
        if (file) {
            const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
            if (sizeInMB > 3) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Terlalu Besar',
                    text: 'Ukuran file ' + sizeInMB + 'MB melebihi batas maksimal 3MB!'
                });
                input.value = '';
                return;
            }
            
            const icon = document.getElementById('upload-icon');
            const text = document.getElementById('upload-text');
            
            icon.className = 'fa-solid fa-circle-check text-success fs-2 mb-2';
            text.innerHTML = '<span class="text-success fw-bold">' + file.name + ' (' + sizeInMB + ' MB)</span>';
        }
    }

    // Submit animation
    const form = document.getElementById('upload-form');
    if (form) {
        form.addEventListener('submit', function() {
            const btn = form.querySelector('.btn-submit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Mengirim Bukti...';
        });
    }
</script>
@endsection
