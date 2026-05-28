@extends('layouts/contentNavbarLayout')

@section('title', 'User Aktif (Realtime)')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-lg border-0 mb-4 bg-white">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom py-3">
                <div class="d-flex align-items-center">
                    <div class="avatar me-2">
                        <span class="avatar-initial rounded bg-label-success"><i class="bx bx-wifi text-success" style="font-size: 1.5rem;"></i></span>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">User Aktif Realtime</h5>
                        <small class="text-muted">Koneksi aktif PPPoE & Hotspot langsung dari MikroTik</small>
                    </div>
                </div>
                <div>
                    <form action="{{ route('mikrotik.active-users') }}" method="GET" id="routerForm">
                        <select name="id_router" class="form-select form-select-sm" onchange="document.getElementById('routerForm').submit()">
                            @foreach($routers as $r)
                            <option value="{{ $r->id_router }}" {{ $selectedRouter && $selectedRouter->id_router == $r->id_router ? 'selected' : '' }}>
                                🟢 {{ $r->nama_router }} ({{ $r->ip_host }})
                            </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
            
            <div class="card-body py-3">
                <!-- Summary Stats -->
                <div class="row mb-3 g-3">
                    <div class="col-6 col-md-4">
                        <div class="border rounded p-3 text-center bg-light">
                            <span class="text-muted d-block small mb-1">Total Koneksi Aktif</span>
                            <h3 class="mb-0 fw-bold text-primary" id="totalActive">{{ count($activeUsers) }}</h3>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="border rounded p-3 text-center bg-light">
                            <span class="text-muted d-block small mb-1">PPPoE Aktif</span>
                            <h3 class="mb-0 fw-bold text-success" id="totalPppoe">
                                {{ collect($activeUsers)->where('service', 'PPPoE')->count() }}
                            </h3>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="border rounded p-3 text-center bg-light">
                            <span class="text-muted d-block small mb-1">Hotspot Aktif</span>
                            <h3 class="mb-0 fw-bold text-info" id="totalHotspot">
                                {{ collect($activeUsers)->where('service', 'Hotspot')->count() }}
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="input-group input-group-merge mb-3">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" id="searchActiveInput" class="form-control" placeholder="Cari username, IP, atau MAC..." onkeyup="filterActiveUsers()">
                </div>

                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle" id="activeUsersTable">
                        <thead>
                            <tr class="table-light">
                                <th>Username</th>
                                <th>Layanan</th>
                                <th>IP Address</th>
                                <th>Caller ID / MAC</th>
                                <th>Uptime</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeUsers as $user)
                            <tr class="active-user-row">
                                <td class="fw-semibold text-dark">{{ $user['username'] }}</td>
                                <td>
                                    @if($user['service'] === 'PPPoE')
                                    <span class="badge bg-label-success">PPPoE</span>
                                    @else
                                    <span class="badge bg-label-info">Hotspot</span>
                                    @endif
                                </td>
                                <td><code>{{ $user['address'] }}</code></td>
                                <td><small class="text-muted">{{ $user['caller_id'] }}</small></td>
                                <td>
                                    <span class="text-secondary"><i class="bx bx-time-five me-1"></i>{{ $user['uptime'] }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bx bx-wifi-off mb-2" style="font-size: 2rem;"></i>
                                    <div>Tidak ada pengguna yang sedang online saat ini.</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterActiveUsers() {
    const input = document.getElementById('searchActiveInput');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('.active-user-row');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
@endsection
