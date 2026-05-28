@extends('layouts/contentNavbarLayout')

@section('title', 'System & Activity Logs')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sistem /</span> Logs Realtime</h4>

<div class="row">
    <div class="col-md-12">
        <!-- Tabs Header -->
        <div class="nav-align-top mb-4">
            <ul class="nav nav-tabs nav-fill" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#nav-activities" aria-controls="nav-activities" aria-selected="true">
                        <i class="tf-icons bx bx-group me-1"></i> Aktivitas Pengguna (Audit Log)
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#nav-developer" aria-controls="nav-developer" aria-selected="false">
                        <i class="tf-icons bx bx-terminal me-1"></i> Developer System Logs (laravel.log)
                    </button>
                </li>
            </ul>

            <div class="tab-content p-0 border-0 bg-transparent">
                <!-- TAB 1: User Activities -->
                <div class="tab-pane fade show active" id="nav-activities" role="tabpanel">
                    <div class="card shadow-sm border-0 bg-white">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center bg-transparent border-bottom py-3 gap-2">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-2">
                                    <span class="avatar-initial rounded bg-label-info"><i class="bx bx-group text-info" style="font-size: 1.5rem;"></i></span>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold text-dark">Log Aktivitas Pengguna</h5>
                                    <small class="text-muted">Jejak audit seluruh aksi admin dan pengguna sistem</small>
                                </div>
                            </div>
                        </div>

                        <div class="card-body py-3">
                            <!-- Filters -->
                            <form action="{{ route('logs.index') }}" method="GET" class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari pelaku atau aktivitas..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-3">
                                    <select name="type" class="form-select form-select-sm">
                                        <option value="">-- Semua Kategori --</option>
                                        <option value="auth" {{ request('type') === 'auth' ? 'selected' : '' }}>Autentikasi (Login/Logout)</option>
                                        <option value="pelanggan" {{ request('type') === 'pelanggan' ? 'selected' : '' }}>Pelanggan</option>
                                        <option value="tagihan" {{ request('type') === 'tagihan' ? 'selected' : '' }}>Tagihan / Keuangan</option>
                                        <option value="kas_bon" {{ request('type') === 'kas_bon' ? 'selected' : '' }}>Kas Bon</option>
                                        <option value="system" {{ request('type') === 'system' ? 'selected' : '' }}>Sistem</option>
                                    </select>
                                </div>
                                <div class="col-md-5 d-flex gap-2">
                                    <button type="submit" class="btn btn-sm btn-primary"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                                    @if(request()->anyFilled(['search', 'type']))
                                    <a href="{{ route('logs.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                                    @endif
                                </div>
                            </form>

                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr class="table-light">
                                            <th>Waktu</th>
                                            <th>Pelaku</th>
                                            <th>Role</th>
                                            <th>Kategori</th>
                                            <th>Detail Aktivitas</th>
                                            <th>IP Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($activities as $act)
                                        <tr>
                                            <td class="small text-secondary">{{ $act->created_at->format('d M Y H:i:s') }}</td>
                                            <td class="fw-semibold text-dark">{{ $act->nama_user }}</td>
                                            <td>
                                                <span class="badge bg-label-secondary small">{{ $act->role ?: 'Guest' }}</span>
                                            </td>
                                            <td>
                                                @if($act->tipe === 'auth')
                                                <span class="badge bg-label-primary">Koneksi / Auth</span>
                                                @elseif($act->tipe === 'pelanggan')
                                                <span class="badge bg-label-info">Pelanggan</span>
                                                @elseif($act->tipe === 'tagihan')
                                                <span class="badge bg-label-success">Tagihan</span>
                                                @elseif($act->tipe === 'kas_bon')
                                                <span class="badge bg-label-danger">Kas Bon</span>
                                                @else
                                                <span class="badge bg-label-warning">Sistem</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-wrap d-block fw-medium text-dark" style="max-width: 380px;">
                                                    {{ $act->aktivitas }}
                                                </span>
                                            </td>
                                            <td><code>{{ $act->ip_address }}</code></td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">Belum ada catatan aktivitas saat ini.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-center mt-3">
                                {{ $activities->links() }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: Developer System Logs -->
                <div class="tab-pane fade" id="nav-developer" role="tabpanel">
                    <div class="card bg-dark text-white shadow-lg border-0">
                        <div class="card-header d-flex justify-content-between align-items-center border-bottom border-secondary bg-transparent py-3">
                            <div class="d-flex align-items-center">
                                <span class="spinner-grow spinner-grow-sm text-success me-2" role="status"></span>
                                <h5 class="mb-0 text-white fw-bold"><i class="bx bx-terminal me-2"></i> Real-time Developer System Logs</h5>
                            </div>
                            <div class="d-flex gap-2">
                                <button id="toggleScroll" class="btn btn-sm btn-outline-info">
                                    <i class="bx bx-mouse me-1"></i> Auto Scroll: ON
                                </button>
                                <form action="{{ route('logs.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan seluruh logs developer?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bx bx-trash me-1"></i> Kosongkan Logs
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div id="logContainer" style="height: 500px; overflow-y: auto; font-family: 'Courier New', Courier, monospace; font-size: 13px; line-height: 1.5; padding: 20px; background-color: #0d1117;">
                                <div id="logContent">
                                    <div class="text-secondary">Menginisialisasi logs developer...</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center border-top border-secondary py-2 bg-transparent">
                            <div class="text-secondary small">
                                <span id="updateTimer">Update Terakhir: --:--:--</span>
                            </div>
                            <div class="text-success small fw-bold">LIVE STREAM</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #logContainer::-webkit-scrollbar {
        width: 8px;
    }
    #logContainer::-webkit-scrollbar-track {
        background: #0d1117;
    }
    #logContainer::-webkit-scrollbar-thumb {
        background: #30363d;
        border-radius: 4px;
    }
    #logContainer::-webkit-scrollbar-thumb:hover {
        background: #484f58;
    }
    .log-line {
        margin-bottom: 2px;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    .log-error { color: #ff7b72; }
    .log-info { color: #79c0ff; }
    .log-warning { color: #d29922; }
    .log-success { color: #7ee787; }
    .log-time { color: #8b949e; margin-right: 8px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const logContainer = document.getElementById('logContainer');
    const logContent = document.getElementById('logContent');
    const timer = document.getElementById('updateTimer');
    const toggleScrollBtn = document.getElementById('toggleScroll');
    
    let autoScroll = true;
    let lastLogContent = '';

    if (toggleScrollBtn) {
        toggleScrollBtn.addEventListener('click', function() {
            autoScroll = !autoScroll;
            toggleScrollBtn.innerHTML = autoScroll ? '<i class="bx bx-mouse me-1"></i> Auto Scroll: ON' : '<i class="bx bx-mouse me-1"></i> Auto Scroll: OFF';
            toggleScrollBtn.classList.toggle('btn-outline-info');
            toggleScrollBtn.classList.toggle('btn-outline-secondary');
        });
    }

    function formatLogs(rawLogs) {
        if (!rawLogs) return '<div class="text-secondary">Belum ada logs developer saat ini.</div>';
        
        return rawLogs.split('\n').map(line => {
            if (!line.trim()) return '';
            
            let className = '';
            if (line.includes('.ERROR')) className = 'log-error';
            else if (line.includes('.INFO')) className = 'log-info';
            else if (line.includes('.WARNING')) className = 'log-warning';
            
            // Extract time [2026-05-15 16:19:01]
            const timeMatch = line.match(/^\[(.*?)\]/);
            if (timeMatch) {
                const time = timeMatch[0];
                const rest = line.substring(time.length);
                return `<div class="log-line ${className}"><span class="log-time">${time}</span>${rest}</div>`;
            }
            
            return `<div class="log-line ${className}">${line}</div>`;
        }).join('');
    }

    function fetchLogs() {
        fetch('{{ route("logs.fetch") }}')
            .then(response => response.json())
            .then(data => {
                if (data.logs !== lastLogContent) {
                    if (logContent) {
                        logContent.innerHTML = formatLogs(data.logs);
                    }
                    lastLogContent = data.logs;
                    
                    if (autoScroll && logContainer) {
                        logContainer.scrollTop = logContainer.scrollHeight;
                    }
                }
                if (timer) {
                    timer.innerText = 'Update Terakhir: ' + data.time;
                }
            })
            .catch(err => {
                console.error('Gagal mengambil logs developer:', err);
                if (timer) {
                    timer.innerText = 'Koneksi error...';
                }
            });
    }

    // Check if active tab is developer tab before polling to save resources
    fetchLogs();
    setInterval(fetchLogs, 3000);
});
</script>
@endsection
