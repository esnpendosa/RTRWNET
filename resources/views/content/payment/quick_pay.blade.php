@extends('layouts/blankLayout')

@section('title', 'Pembayaran Tagihan - RTRW Net')

@section('page-style')
<style>
    body {
        background: linear-gradient(135deg, #f5f7ff 0%, #e8eaff 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .payment-card {
        max-width: 450px;
        width: 90%;
        border-radius: 20px;
        border: none;
        box-shadow: 0 20px 40px rgba(105, 108, 255, 0.15);
        overflow: hidden;
    }
    .payment-header {
        background: linear-gradient(135deg, #696cff 0%, #a2a4ff 100%);
        padding: 40px 20px;
        text-align: center;
        color: white;
    }
    .payment-body {
        padding: 30px;
        background: white;
    }
    .amount-box {
        background: #f8f9ff;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        margin-bottom: 25px;
    }
    .amount-value {
        font-size: 2rem;
        font-weight: 800;
        color: #696cff;
    }
    .btn-pay {
        padding: 15px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1.1rem;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }
    .btn-pay:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(105, 108, 255, 0.3);
    }
</style>
@endsection

@section('content')
<div class="payment-card">
    <div class="payment-header">
        <img src="{{ asset('assets/img/favicon/favicon.ico') }}" alt="logo" height="50" class="mb-3" style="filter: brightness(0) invert(1);">
        <h4 class="mb-0 text-white">Pembayaran Tagihan WiFi</h4>
        <p class="mb-0 opacity-75">ROZITECH MULTIMEDIA INDONESIA</p>
    </div>
    <div class="payment-body">
        <div class="mb-4 text-center">
            <h6 class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">Pelanggan</h6>
            <h5 class="mb-1">{{ $pelanggan->nama_pelanggan }}</h5>
            <span class="badge bg-label-primary">{{ $pelanggan->kode_pelanggan }}</span>
        </div>

        <div class="amount-box">
            <h6 class="text-muted mb-1">Total Tagihan</h6>
            <div class="amount-value">Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}</div>
            <small class="text-muted">Periode: {{ $tagihan->bulan }} / {{ $tagihan->tahun }}</small>
        </div>

        <button id="pay-button" class="btn btn-primary w-100 btn-pay mb-3">
            <i class="bx bx-qr-scan me-2"></i> BAYAR SEKARANG
        </button>
        
        <p class="text-center text-muted small mb-0">
            Dukung pembayaran via QRIS (Gopay, ShopeePay, OVO, Dana) & Transfer Bank.
        </p>
    </div>
</div>

<!-- Midtrans Snap Script -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ \App\Models\Setting::get('midtrans_client_key') }}"></script>
<script>
    const payButton = document.getElementById('pay-button');
    payButton.addEventListener('click', function () {
        payButton.disabled = true;
        payButton.innerHTML = '<i class="bx bx-loader-alt bx-spin me-2"></i> Memproses...';

        fetch("{{ route('billing.pay', $tagihan->id_tagihan) }}")
            .then(response => response.json())
            .then(data => {
                if (data.token) {
                    snap.pay(data.token, {
                        onSuccess: function(result) {
                            window.location.href = "{{ route('dashboard') }}?success=payment";
                        },
                        onPending: function(result) {
                            window.location.reload();
                        },
                        onError: function(result) {
                            alert("Pembayaran Gagal!");
                            payButton.disabled = false;
                            payButton.innerHTML = '<i class="bx bx-qr-scan me-2"></i> BAYAR SEKARANG';
                        },
                        onClose: function() {
                            payButton.disabled = false;
                            payButton.innerHTML = '<i class="bx bx-qr-scan me-2"></i> BAYAR SEKARANG';
                        }
                    });
                } else {
                    alert("Error: " + data.error);
                    payButton.disabled = false;
                    payButton.innerHTML = '<i class="bx bx-qr-scan me-2"></i> BAYAR SEKARANG';
                }
            });
    });
</script>
@endsection
