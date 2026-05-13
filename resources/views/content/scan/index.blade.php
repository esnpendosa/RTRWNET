@extends('layouts/contentNavbarLayout')

@section('title', 'Scan QR Code - RTRW Net')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
            <div class="card-header bg-primary text-white text-center py-4">
                <h4 class="mb-0 text-white"><i class="bx bx-qr-scan me-2"></i> Scanner Pintar</h4>
                <p class="mb-0 opacity-75">Scan QR Card Pelanggan / Inventaris</p>
            </div>
            <div class="card-body p-0">
                <div id="reader" style="width: 100%; border: none;"></div>
            </div>
            <div class="card-footer bg-light text-center py-3">
                <div id="result-text" class="text-muted italic">Mencari kode QR...</div>
                <form id="scan-form" action="{{ route('scan.process') }}" method="POST" style="display: none;">
                    @csrf
                    <input type="hidden" name="code" id="scan-code">
                </form>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <button onclick="location.reload()" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-refresh me-1"></i> Refresh Kamera
            </button>
        </div>
    </div>
</div>

@section('page-script')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    function onScanSuccess(decodedText, decodedResult) {
        // Handle the scanned code
        console.log(`Code matched = ${decodedText}`, decodedResult);
        
        document.getElementById('result-text').innerHTML = '<span class="text-success fw-bold">Ditemukan: ' + decodedText + '</span>';
        document.getElementById('scan-code').value = decodedText;
        
        // Pause scanning
        html5QrcodeScanner.clear();
        
        // Auto submit
        setTimeout(() => {
            document.getElementById('scan-form').submit();
        }, 1000);
    }

    function onScanFailure(error) {
        // handle scan failure, usually better to ignore and keep scanning
    }

    let html5QrcodeScanner = new Html5QrcodeScanner(
        "reader", 
        { 
            fps: 10, 
            qrbox: {width: 250, height: 250},
            aspectRatio: 1.0
        },
        /* verbose= */ false);
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
</script>
<style>
    #reader {
        border: none !important;
    }
    #reader__dashboard_section_csr button {
        background-color: #696cff !important;
        color: white !important;
        border: none !important;
        padding: 8px 16px !important;
        border-radius: 5px !important;
        margin: 10px 0 !important;
    }
    #reader__camera_selection {
        padding: 8px !important;
        border-radius: 5px !important;
        border: 1px solid #d9dee3 !important;
        margin-bottom: 10px !important;
    }
</style>
@endsection
@endsection
