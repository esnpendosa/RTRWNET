@extends('layouts/contentNavbarLayout')

@section('title', 'Koneksi Saya - RTRW Net')

@section('content')
<div class="row">
    <!-- Status Card -->
    <div class="col-md-12 mb-4">
        <div class="card bg-primary text-white shadow-none border-0 overflow-hidden" style="border-radius: 15px;">
            <div class="card-body p-4 position-relative">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="text-white mb-1">Halo, {{ auth()->user()->name }}!</h4>
                        <p class="mb-0 opacity-75">Monitoring koneksi internet {{ $isAdmin ? 'Pelanggan' : 'Anda' }}.</p>
                    </div>
                    @if($isAdmin && $allPelanggan)
                    <div class="ms-auto me-3" style="min-width: 250px;">
                        <form action="{{ route('pelanggan.my-connection') }}" method="GET" id="selectPelangganForm">
                            <select name="id" class="form-select form-select-sm shadow-sm" onchange="document.getElementById('selectPelangganForm').submit()">
                                <option value="">-- Pilih Pelanggan --</option>
                                @foreach($allPelanggan as $p)
                                <option value="{{ $p->id_pelanggan }}" {{ $pelanggan->id_pelanggan == $p->id_pelanggan ? 'selected' : '' }}>
                                    {{ $p->kode_pelanggan }} - {{ $p->nama_pelanggan }}
                                </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    @endif
                    <div class="d-flex align-items-center">
                        <span class="badge {{ $pelanggan->is_active ? 'bg-success' : 'bg-danger' }} p-2 px-3 shadow-sm">
                            <i class="bx {{ $pelanggan->is_active ? 'bx-wifi' : 'bx-wifi-off' }} me-1"></i>
                            {{ $pelanggan->is_active ? 'TERHUBUNG' : 'ISOLIR' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="col-md-4">
        <div class="card mb-4 shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i> Info Layanan</h5>
            </div>
            <div class="card-body pt-4">
                <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted">Kode Pelanggan</span>
                    <span class="fw-bold">{{ $pelanggan->kode_pelanggan }}</span>
                </div>
                <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted">Paket Kecepatan</span>
                    <span class="fw-bold text-primary">{{ $mikrotikData['secret']['limit-out'] ?? 'N/A' }}</span>
                </div>
                <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted">Tipe Layanan</span>
                    <span class="fw-bold text-uppercase">{{ $pelanggan->mikrotik_type }}</span>
                </div>
                <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted">Alamat IP</span>
                    <span class="fw-bold">{{ $mikrotikData['active']['address'] ?? 'Offline' }}</span>
                </div>
                <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted">Lama Aktif (Uptime)</span>
                    <span class="fw-bold text-success">{{ $mikrotikData['active']['uptime'] ?? '00:00:00' }}</span>
                </div>
                <hr>
                <div class="d-grid gap-2">
                    <a href="{{ route('pelanggan.card', $pelanggan->id_pelanggan) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                        <i class="bx bx-id-card me-1"></i> Lihat Kartu Digital
                    </a>
                </div>
                <div class="text-center mt-3">
                    <small class="text-muted d-block mb-1">
                        Tagihan Periode {{ $currentBill ? date('F Y', mktime(0, 0, 0, $currentBill->bulan, 10, $currentBill->tahun)) : date('F Y') }}
                    </small>
                    <h3 class="fw-bold text-dark mb-1">Rp {{ number_format($currentBill->jumlah ?? $pelanggan->harga_layanan, 0, ',', '.') }}</h3>
                    @if($currentBill)
                        @if($currentBill->status == 'paid')
                            <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i> LUNAS</span>
                        @elseif($currentBill->status == 'unpaid' && $currentBill->bukti_bayar)
                            <span class="badge bg-label-info"><i class="bx bx-time-five me-1"></i> MENUNGGU VERIFIKASI</span>
                        @else
                            <span class="badge bg-label-danger"><i class="bx bx-error me-1"></i> BELUM BAYAR</span>
                        @endif
                    @else
                        <span class="badge bg-label-secondary">BELUM TERBIT</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Traffic Monitor -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h5 class="mb-0"><i class="bx bx-line-chart me-2"></i> Monitor Trafik (Mbps)</h5>
                <div class="d-flex align-items-center">
                    <span class="badge bg-label-primary me-2" id="rx-bps">RX: 0 Mbps</span>
                    <span class="badge bg-label-info" id="tx-bps">TX: 0 Mbps</span>
                </div>
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="trafficChart"></canvas>
                </div>
                <div class="alert alert-info mt-3 d-flex align-items-center" role="alert">
                    <i class="bx bx-help-circle me-2 fs-4"></i>
                    <div>
                        Grafik ini menunjukkan penggunaan bandwidth internet Anda secara real-time.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('trafficChart').getContext('2d');
        let trafficData = {
            labels: [],
            datasets: [{
                label: 'Download (RX)',
                borderColor: '#696cff',
                backgroundColor: 'rgba(105, 108, 255, 0.1)',
                data: [],
                fill: true,
                tension: 0.4,
                pointRadius: 0
            }, {
                label: 'Upload (TX)',
                borderColor: '#03c3ec',
                backgroundColor: 'rgba(3, 195, 236, 0.1)',
                data: [],
                fill: true,
                tension: 0.4,
                pointRadius: 0
            }]
        };

        const config = {
            type: 'line',
            data: trafficData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                scales: {
                    x: { display: false },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return (value / 1024 / 1024).toFixed(1) + ' Mb';
                            }
                        }
                    }
                },
                plugins: {
                    legend: { position: 'top' }
                }
            }
        };

        const trafficChart = new Chart(ctx, config);

        function updateTraffic() {
            fetch("{{ route('pelanggan.traffic', $pelanggan->id_pelanggan) }}")
                .then(res => res.json())
                .then(data => {
                    const rx = parseInt(data['rx-bits-per-second'] || 0);
                    const tx = parseInt(data['tx-bits-per-second'] || 0);
                    const now = new Date().toLocaleTimeString();

                    document.getElementById('rx-bps').innerText = 'RX: ' + (rx / 1024 / 1024).toFixed(2) + ' Mbps';
                    document.getElementById('tx-bps').innerText = 'TX: ' + (tx / 1024 / 1024).toFixed(2) + ' Mbps';

                    if (trafficData.labels.length > 30) {
                        trafficData.labels.shift();
                        trafficData.datasets[0].data.shift();
                        trafficData.datasets[1].data.shift();
                    }

                    trafficData.labels.push(now);
                    trafficData.datasets[0].data.push(rx);
                    trafficData.datasets[1].data.push(tx);

                    trafficChart.update();
                })
                .catch(err => console.error('Traffic update error:', err));
        }

        setInterval(updateTraffic, 5000);
    });
</script>
@endsection
@endsection
