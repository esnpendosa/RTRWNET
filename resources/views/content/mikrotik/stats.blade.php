@extends('layouts/contentNavbarLayout')

@section('title', 'Router Stats - ' . $router->nama_router)

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Mikrotik /</span> Stats / {{ $router->nama_router }}</h4>

<div class="row">
    <div class="col-md-12 mb-4">
        @if(str_contains(strtolower($router->status_koneksi), 'simulated'))
        <div class="alert alert-warning d-flex align-items-center mb-0" role="alert">
            <span class="badge badge-center rounded-pill bg-warning border-label-warning p-3 me-2"><i class="bx bx-error bx-xs"></i></span>
            <div class="d-flex flex-column ps-1">
                <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">Mode Simulasi Aktif</h6>
                <span>Sistem tidak dapat terhubung ke mikrotik Anda ({{ $router->status_koneksi }}). Data yang ditampilkan saat ini adalah simulasi untuk keperluan demo. Periksa IP, Port, dan Password Anda.</span>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Histori Performa (CPU & Memory)</h5>
                <a href="{{ route('mikrotik.sync', $router->id_router) }}" class="btn btn-primary btn-sm"><i class="bx bx-sync me-1"></i> Sync Now</a>
            </div>
            <div class="card-body">
                <canvas id="statChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card">
            <h5 class="card-header">Log Data Terakhir</h5>
            <div class="table-responsive text-nowrap">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Uptime</th>
                            <th>CPU Load</th>
                            <th>Free Memory</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats as $s)
                        <tr>
                            <td>{{ $s->recorded_at->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $s->uptime }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress w-100 me-2" style="height: 8px;">
                                        <div class="progress-bar {{ $s->cpu_load > 80 ? 'bg-danger' : ($s->cpu_load > 50 ? 'bg-warning' : 'bg-success') }}" role="progressbar" style="width: {{ $s->cpu_load }}%"></div>
                                    </div>
                                    <span>{{ $s->cpu_load }}%</span>
                                </div>
                            </td>
                            <td>{{ number_format($s->memory_free / 1024 / 1024, 2) }} MB</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var ctx = document.getElementById('statChart').getContext('2d');
        var data = @json($stats->reverse()->values());
        
        var labels = data.map(s => new Date(s.recorded_at).toLocaleTimeString());
        var cpuData = data.map(s => s.cpu_load);
        var memData = data.map(s => s.memory_free / 1024 / 1024);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'CPU Load (%)',
                        data: cpuData,
                        borderColor: '#696cff',
                        backgroundColor: 'rgba(105, 108, 255, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Free Memory (MB)',
                        data: memData,
                        borderColor: '#03c3ec',
                        backgroundColor: 'rgba(3, 195, 236, 0.1)',
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: { display: true, text: 'CPU %' }
                    },
                    y1: {
                        position: 'right',
                        title: { display: true, text: 'Memory (MB)' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
    });
</script>
@endsection
