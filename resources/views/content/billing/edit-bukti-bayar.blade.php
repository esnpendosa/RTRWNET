@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Bukti Pembayaran')

@section('content')
<h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Keuangan / Tagihan /</span> Edit Bukti Pembayaran
</h4>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Bukti Pembayaran</h5>
                <a href="{{ route('billing.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <!-- Success Message -->
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><i class="bx bx-check-circle me-2"></i>Berhasil!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <!-- Error Messages -->
                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="bx bx-error-circle me-2"></i>Terjadi kesalahan!</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <!-- Billing Information -->
                <div class="alert alert-info mb-4">
                    <h6 class="alert-heading mb-2"><i class="bx bx-info-circle me-1"></i> Informasi Tagihan</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Pelanggan:</strong> {{ $tagihan->pelanggan ? $tagihan->pelanggan->nama_pelanggan : 'Umum' }}<br>
                            <strong>Kode:</strong> {{ $tagihan->pelanggan ? $tagihan->pelanggan->kode_pelanggan : '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Periode:</strong> {{ date('F', mktime(0, 0, 0, $tagihan->bulan, 10)) }} {{ $tagihan->tahun }}<br>
                            <strong>Jumlah:</strong> Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}<br>
                            <strong>Status:</strong> 
                            @if($tagihan->status == 'paid')
                                <span class="badge bg-success">Lunas</span>
                            @elseif($tagihan->status == 'pending')
                                <span class="badge bg-info">Pending</span>
                            @else
                                <span class="badge bg-warning">Belum Bayar</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Current Payment Proof -->
                @if($tagihan->bukti_bayar && file_exists(storage_path('app/public/' . $tagihan->bukti_bayar)))
                <div class="mb-4">
                    <h6>Bukti Pembayaran Saat Ini:</h6>
                    <div class="text-center border rounded p-3">
                        @if(Str::endsWith($tagihan->bukti_bayar, '.pdf'))
                        <a href="{{ asset('storage/' . $tagihan->bukti_bayar) }}" target="_blank" class="btn btn-outline-primary">
                            <i class="bx bx-file-blank me-1"></i> Lihat PDF
                        </a>
                        @else
                        <img src="{{ asset('storage/' . $tagihan->bukti_bayar) }}" alt="Bukti Pembayaran" class="img-fluid" style="max-height: 300px; border-radius: 8px;">
                        @endif
                    </div>
                    <small class="text-muted d-block mt-2">File: {{ basename($tagihan->bukti_bayar) }}</small>
                </div>
                @else
                <div class="alert alert-warning mb-4">
                    <i class="bx bx-info-circle me-1"></i> Belum ada bukti pembayaran yang diunggah untuk tagihan ini.
                </div>
                @endif

                <!-- Edit Form -->
                <form action="{{ route('billing.update-bukti-bayar', $tagihan->id_tagihan) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="bukti_bayar" class="form-label fw-semibold">
                            <i class="bx bx-upload me-1"></i> Upload New Payment Proof
                        </label>
                        <input type="file" class="form-control @error('bukti_bayar') is-invalid @enderror" 
                               id="bukti_bayar" name="bukti_bayar" accept="image/jpeg,image/jpg,image/png,image/gif,application/pdf" required>
                        <div class="form-text">
                            <i class="bx bx-info-circle me-1"></i> Formats: JPG, PNG, GIF, PDF. Max size: 3MB
                        </div>
                        @error('bukti_bayar')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="metode_pembayaran" class="form-label">Metode Pembayaran (Opsional)</label>
                        <select name="metode_pembayaran" id="metode_pembayaran" class="form-select @error('metode_pembayaran') is-invalid @enderror">
                            <option value="">-- Pilih Metode Pembayaran --</option>
                            @foreach(explode(',', \App\Models\Setting::get('manual_payment_methods', 'Cash, Transfer BRI, Transfer BCA, Transfer BNI, Transfer Mandiri, Transfer DANA, Transfer OVO, Transfer ShopeePay, Transfer Gopay')) as $method)
                            <option value="{{ trim($method) }}" {{ old('metode_pembayaran', $tagihan->metode_pembayaran) == trim($method) ? 'selected' : '' }}>
                                {{ trim($method) }}
                            </option>
                            @endforeach
                        </select>
                        @error('metode_pembayaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if(auth()->user()->id_role == 1 || auth()->user()->id_role == 2)
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="verify_payment" name="verify_payment" value="1">
                            <label class="form-check-label" for="verify_payment">
                                <strong>Verifikasi & Tandai Sebagai Lunas</strong>
                            </label>
                        </div>
                        <small class="text-muted">Centang jika Anda ingin langsung memverifikasi pembayaran ini sebagai lunas.</small>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-x me-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Preview image before upload
document.getElementById('bukti_bayar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const fileSize = file.size / 1024 / 1024; // in MB
        if (fileSize > 3) {
            alert('Ukuran file terlalu besar! Maksimal 3MB.');
            e.target.value = '';
        }
    }
});
</script>

@endsection
