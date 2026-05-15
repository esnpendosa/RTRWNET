@extends('layouts/contentNavbarLayout')

@section('title', 'Detail Pelanggan - ' . $pelanggan->nama_pelanggan)

@section('content')
<div class="row">
    <!-- Profile Card -->
    <div class="col-md-4">
        <div class="card mb-4" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
            <div class="card-body text-center pt-5">
                <div class="avatar avatar-xl mx-auto mb-3" style="width: 100px; height: 100px;">
                    <span class="avatar-initial rounded-circle bg-label-primary fs-1 shadow-sm">{{ substr($pelanggan->nama_pelanggan, 0, 1) }}</span>
                </div>
                <h4 class="mb-1">{{ $pelanggan->nama_pelanggan }}</h4>
                <div class="mb-3">
                    <span class="badge bg-label-info">{{ $pelanggan->kode_pelanggan }}</span>
                    <span class="badge {{ $pelanggan->is_active ? 'bg-label-success' : 'bg-label-danger' }}">{{ $pelanggan->is_active ? 'Active' : 'Isolated' }}</span>
                </div>
                
                <div class="row mt-4 g-2">
                    <div class="col-6">
                        <div class="p-3 border rounded bg-light">
                            <h5 class="mb-0 text-primary">{{ $pelanggan->usage_gb }}</h5>
                            <small class="text-muted">Usage (GB)</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded bg-light">
                            <h5 class="mb-0 text-info">{{ $pelanggan->jumlah_device }}</h5>
                            <small class="text-muted">Devices</small>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-start">
                    <p class="mb-1 text-muted small"><i class="bx bx-package me-1"></i> Paket Layanan</p>
                    <p class="fw-bold text-primary">{{ $pelanggan->paket ?? 'Custom / Belum Set' }}</p>

                    <p class="mb-1 text-muted small"><i class="bx bx-money me-1"></i> Harga Bulanan</p>
                    <p class="fw-bold">Rp {{ number_format($pelanggan->harga_layanan, 0, ',', '.') }}</p>

                    <p class="mb-1 text-muted small"><i class="bx bx-phone me-1"></i> WhatsApp</p>
                    <p class="fw-bold">{{ $pelanggan->no_wa ?? '-' }}</p>
                    
                    <p class="mb-1 text-muted small"><i class="bx bx-map me-1"></i> Alamat</p>
                    <p class="fw-bold">{{ $pelanggan->alamat }}</p>
                </div>
                
                <hr>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('pelanggan.edit', $pelanggan->id_pelanggan) }}" class="btn btn-outline-primary">
                        <i class="bx bx-edit-alt me-1"></i> Edit Profil
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mikrotik Stats -->
    <div class="col-md-8">
        <div class="card mb-4 shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom bg-transparent py-3">
                <h5 class="mb-0"><i class="bx bx-chip me-2"></i> Status MikroTik</h5>
                <small class="text-muted">Router: {{ $pelanggan->router->nama_router ?? 'N/A' }}</small>
            </div>
            <div class="card-body pt-4">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm bg-label-secondary me-3">
                                <span class="avatar-initial rounded"><i class="bx bx-user"></i></span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Username</small>
                                <span class="fw-bold">{{ $mikrotikData['secret']['name'] ?? $pelanggan->mikrotik_username ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm bg-label-secondary me-3">
                                <span class="avatar-initial rounded"><i class="bx bx-cog"></i></span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Profile / Type</small>
                                <span class="fw-bold">{{ $mikrotikData['secret']['profile'] ?? ucfirst($pelanggan->mikrotik_type) }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm bg-label-secondary me-3">
                                <span class="avatar-initial rounded"><i class="bx bx-tachometer"></i></span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Limit (Speed)</small>
                                <span class="fw-bold">{{ $mikrotikData['secret']['limit-out'] ?? 'Default' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm bg-label-secondary me-3">
                                <span class="avatar-initial rounded"><i class="bx bx-broadcast"></i></span>
                            </div>
                            <div>
                                <small class="text-muted d-block">IP Address</small>
                                <span class="fw-bold">{{ $mikrotikData['active']['address'] ?? $pelanggan->ip_address ?? 'Offline' }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm bg-label-secondary me-3">
                                <span class="avatar-initial rounded"><i class="bx bx-time"></i></span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Uptime</small>
                                <span class="fw-bold text-success">{{ $mikrotikData['active']['uptime'] ?? 'Disconnected' }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm bg-label-secondary me-3">
                                <span class="avatar-initial rounded"><i class="bx bx-data"></i></span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Total Data (Session)</small>
                                <span class="fw-bold">{{ isset($mikrotikData['active']['bytes-out']) ? round($mikrotikData['active']['bytes-out'] / 1024 / 1024, 2) . ' MB' : '0' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Traffic Graph -->
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h5 class="mb-0"><i class="bx bx-line-chart me-2"></i> Trafik Real-time (Bps)</h5>
                <div id="traffic-indicator" class="d-flex align-items-center">
                    <span class="badge bg-label-primary me-2" id="rx-bps">RX: 0 Mbps</span>
                    <span class="badge bg-label-info" id="tx-bps">TX: 0 Mbps</span>
                </div>
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="trafficChart"></canvas>
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
                    x: {
                        display: false
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return (value / 1024 / 1024).toFixed(1) + ' Mbps';
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

        setInterval(updateTraffic, 2000);
    });
</script>
@endsection
@endsection
