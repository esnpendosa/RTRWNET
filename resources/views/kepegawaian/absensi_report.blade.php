@extends('layouts/contentNavbarLayout')

@section('title', 'Laporan Kehadiran Pegawai')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border-radius: 16px;">
            <div class="position-absolute end-0 bottom-0 opacity-10" style="font-size: 15rem; transform: translate(10%, 20%); line-height: 1;">
                <i class="bx bx-chart"></i>
            </div>
            <div class="card-body p-4 p-md-5">
                <h4 class="card-title text-white mb-2 fw-bold"><i class="bx bx-chart me-2"></i> REKAPITULASI & LAPORAN ABSENSI</h4>
                <p class="mb-0 text-white-50">Kelola riwayat kehadiran karyawan, cetak rekapitulasi bulanan, import CSV, dan buat koreksi manual.</p>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm rounded-3 p-3 mb-4 d-flex align-items-center" style="border-radius: 12px;">
    <i class="bx bx-check-circle me-2 fs-4"></i> {{ session('success') }}
</div>
@endif

<!-- FILTER CARD -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
    <div class="card-body p-4">
        <form action="{{ route('absensi.index') }}" method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="tab" value="{{ $tab }}">
            
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted">PEGAWAI / KARYAWAN</label>
                <select name="user_id" class="form-select select2-filter" style="border-radius: 8px;">
                    @if(Auth::user()->id_role != 4)
                    <option value="all" {{ $targetUserId === 'all' ? 'selected' : '' }}>👥 SEMUA PEGAWAI (Kolektif)</option>
                    @endif
                    @foreach($allUsers as $u)
                    <option value="{{ $u->id }}" {{ $targetUserId == $u->id ? 'selected' : '' }}>👤 {{ $u->name }} [PIN: {{ $u->pin_fingerspot ?? 'Belum Set' }}]</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label fw-bold small text-muted">BULAN</label>
                <select name="month" class="form-select" style="border-radius: 8px;">
                    @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                    @endfor
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label fw-bold small text-muted">TAHUN</label>
                <select name="year" class="form-select" style="border-radius: 8px;">
                    @for($y = date('Y') - 3; $y <= date('Y') + 1; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill">
                    <i class="bx bx-filter-alt me-1"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ACTIONS BAR -->
@if(Auth::user()->id_role == 1)
<div class="d-flex flex-wrap gap-2 mb-4">
    <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalManual">
        <i class="bx bx-plus-circle me-1"></i> Input Absen Manual
    </button>
    <button class="btn btn-outline-success rounded-pill px-4 fw-bold bg-white" data-bs-toggle="modal" data-bs-target="#modalImport">
        <i class="bx bx-upload me-1"></i> Import CSV (Backup)
    </button>
    <a href="{{ route('absensi.export', ['user_id' => $targetUserId, 'month' => $month, 'year' => $year]) }}" class="btn btn-outline-indigo rounded-pill px-4 fw-bold bg-white">
        <i class="bx bx-download me-1"></i> Ekspor CSV
    </a>
    <form action="{{ route('absensi.send-rekap') }}" method="POST" class="d-inline">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">
        <button type="submit" class="btn btn-outline-primary rounded-pill px-4 fw-bold bg-white" onclick="return confirm('Kirim rekap absensi periode {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }} ke nomor WhatsApp target?')">
            <i class="bx bxl-whatsapp me-1 text-success"></i> Kirim Rekap WA
        </button>
    </form>
</div>
@endif

<!-- MAIN CONTENT BLOCK -->
<div class="row">
    <!-- Target Overview Stats Cards -->
    <div class="col-12 mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 bg-white" style="border-radius: 16px;">
                    <div class="d-flex align-items-center">
                        <div class="p-3 bg-label-success rounded-circle me-3" style="border-radius: 12px; background-color: #e8f5e9; color: #2e7d32;">
                            <i class="bx bx-calendar-check fs-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small fw-bold mb-1">TOTAL HARI HADIR</h6>
                            <h3 class="fw-bold mb-0 text-success">{{ $counts['hadir'] }} Hari</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 bg-white" style="border-radius: 16px;">
                    <div class="d-flex align-items-center">
                        <div class="p-3 bg-label-danger rounded-circle me-3" style="border-radius: 12px; background-color: #ffebee; color: #c62828;">
                            <i class="bx bx-calendar-x fs-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small fw-bold mb-1">TOTAL ALPHA / ABSEN</h6>
                            <h3 class="fw-bold mb-0 text-danger">{{ $counts['alpha'] }} Hari</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 bg-white" style="border-radius: 16px;">
                    <div class="d-flex align-items-center">
                        <div class="p-3 bg-label-primary rounded-circle me-3" style="border-radius: 12px; background-color: #e8eaf6; color: #3f51b5;">
                            <i class="bx bx-user-voice fs-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small fw-bold mb-1">PROFIL TARGET</h6>
                            <h4 class="fw-bold mb-0 text-primary text-truncate" style="max-width: 190px;">{{ $targetUser->name }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN DATA TABLE CARD -->
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark m-0">
                    <i class="bx bx-table text-primary me-2"></i>
                    {{ $tab === 'bulanan' ? 'REKAPITULASI PRESENSI BULANAN' : 'RIWAYAT PRESENSI HARIAN' }}
                </h5>
                <div class="btn-group bg-light rounded-pill p-1" role="group">
                    <a href="{{ request()->fullUrlWithQuery(['tab' => 'bulanan']) }}" class="btn btn-sm rounded-pill px-4 fw-bold {{ $tab === 'bulanan' ? 'btn-primary' : 'btn-transparent text-muted' }}">Bulanan</a>
                    <a href="{{ request()->fullUrlWithQuery(['tab' => 'harian']) }}" class="btn btn-sm rounded-pill px-4 fw-bold {{ $tab === 'harian' ? 'btn-primary' : 'btn-transparent text-muted' }}">Harian</a>
                </div>
            </div>

            <div class="card-body p-0 mt-3">
                <div class="table-responsive">
                    @if($tab === 'bulanan')
                        @if($targetUserId === 'all')
                            <!-- Collective Monthly Presences -->
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light border-bottom">
                                    <tr class="text-uppercase small fw-bold text-dark">
                                        <th class="ps-4 py-3">Nama Pegawai</th>
                                        <th class="py-3">PIN Sidik Jari</th>
                                        <th class="text-center py-3">Hadir</th>
                                        <th class="text-center py-3">Alpha</th>
                                        <th class="text-center py-3">Persentase Kehadiran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reportData as $row)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-indigo text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3 shadow-sm" style="width: 38px; height: 38px; background-color: #6366f1;">
                                                    {{ substr($row['user']->name, 0, 1) }}
                                                </div>
                                                <div class="fw-bold text-dark">{{ $row['user']->name }}</div>
                                            </div>
                                        </td>
                                        <td class="py-3 fw-semibold text-muted">{{ $row['user']->pin_fingerspot ?? 'Belum di-Set' }}</td>
                                        <td class="text-center py-3 fw-bold text-success">{{ $row['hadir'] }} Hari</td>
                                        <td class="text-center py-3 fw-bold text-danger">{{ $row['alpha'] }} Hari</td>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                <div class="progress w-50" style="height: 8px; border-radius: 4px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $row['persentase'] }}%" aria-valuenow="{{ $row['persentase'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span class="fw-bold text-dark small">{{ $row['persentase'] }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="py-5 text-center text-muted">Belum ada data kehadiran bulan ini.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        @else
                            <!-- Individual Monthly Summary -->
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light border-bottom">
                                    <tr class="text-uppercase small fw-bold text-dark">
                                        <th class="ps-4 py-3">Bulan</th>
                                        <th class="text-center py-3">Hadir</th>
                                        <th class="text-center py-3">Alpha</th>
                                        <th class="text-center py-3">Hari Kerja</th>
                                        <th class="text-center py-3">Persentase</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bulananData as $row)
                                    <tr>
                                        <td class="ps-4 py-3 fw-bold text-dark">{{ $row['bulan'] }}</td>
                                        <td class="text-center py-3 fw-bold text-success">{{ $row['hadir'] }} Hari</td>
                                        <td class="text-center py-3 fw-bold text-danger">{{ $row['alpha'] }} Hari</td>
                                        <td class="text-center py-3 fw-semibold text-muted">{{ $row['total'] }} Hari</td>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                <div class="progress w-50" style="height: 8px; border-radius: 4px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $row['persentase'] }}%" aria-valuenow="{{ $row['persentase'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span class="fw-bold text-dark small">{{ $row['persentase'] }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="py-5 text-center text-muted">Belum ada data bulanan.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        @endif
                    @else
                        @if($targetUserId === 'all')
                            <!-- Collective Daily Logs -->
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light border-bottom">
                                    <tr class="text-uppercase small fw-bold text-dark">
                                        <th class="ps-4 py-3">Nama Pegawai</th>
                                        <th class="py-3">Tanggal</th>
                                        <th class="text-center py-3">Masuk</th>
                                        <th class="text-center py-3">Pulang</th>
                                        <th class="text-center py-3">Status</th>
                                        <th class="text-center py-3">Lokasi</th>
                                        @if(Auth::user()->id_role == 1)
                                        <th class="pe-4 text-end py-3">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reportData as $row)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="fw-bold text-dark">{{ $row->user->name ?? 'N/A' }}</div>
                                            <small class="text-muted small">PIN: {{ $row->pin ?? '-' }}</small>
                                        </td>
                                        <td class="py-3 fw-semibold text-muted">{{ $row->tgl->translatedFormat('d F Y') }}</td>
                                        <td class="text-center py-3 fw-bold text-success fs-6">{{ $row->jam_masuk ?? '--:--' }}</td>
                                        <td class="text-center py-3 fw-bold text-danger fs-6">{{ $row->jam_pulang ?? '--:--' }}</td>
                                        <td class="text-center py-3">
                                            @php
                                                $statusClass = match($row->status_kehadiran) {
                                                    'Hadir' => 'success',
                                                    'Terlambat' => 'warning',
                                                    'Pulang Lebih Awal' => 'info',
                                                    'Terlambat & Pulang Awal' => 'danger',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusClass }} px-3 py-2 rounded-pill small fw-bold">{{ strtoupper($row->status_kehadiran) }}</span>
                                        </td>
                                        <td class="text-center py-3 text-muted small fw-medium"><i class="bx bx-map-pin me-1"></i>{{ $row->lokasi ?? 'Solutions X105' }}</td>
                                        @if(Auth::user()->id_role == 1)
                                        <td class="pe-4 text-end py-3">
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#deleteAbs{{ $row->id }}">Hapus</button>
                                        </td>
                                        @endif
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="py-5 text-center text-muted">Belum ada log kehadiran hari ini.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="card-footer bg-transparent border-0 px-4 py-3">
                                {{ $reportData->appends(request()->query())->links() }}
                            </div>
                        @else
                            <!-- Individual Daily logs -->
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light border-bottom">
                                    <tr class="text-uppercase small fw-bold text-dark">
                                        <th class="ps-4 py-3">Hari & Tanggal</th>
                                        <th class="text-center py-3">Jam Masuk</th>
                                        <th class="text-center py-3">Jam Pulang</th>
                                        <th class="text-center py-3">Status</th>
                                        <th class="text-center py-3">Lokasi / Keterangan</th>
                                        @if(Auth::user()->id_role == 1)
                                        <th class="pe-4 text-end py-3">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($harianData as $row)
                                    <tr>
                                        <td class="ps-4 py-3 fw-bold text-dark">
                                            {{ \Carbon\Carbon::parse($row['tanggal'])->translatedFormat('l, d F Y') }}
                                        </td>
                                        <td class="text-center py-3 fw-bold text-success fs-6">{{ $row['jam_masuk'] ?? '--:--' }}</td>
                                        <td class="text-center py-3 fw-bold text-danger fs-6">{{ $row['jam_pulang'] ?? '--:--' }}</td>
                                        <td class="text-center py-3">
                                            @php
                                                $statusClass = match($row['status']) {
                                                    'Hadir' => 'success',
                                                    'Terlambat' => 'warning',
                                                    'Pulang Lebih Awal' => 'info',
                                                    'Terlambat & Pulang Awal' => 'danger',
                                                    'Alpha' => 'danger bg-opacity-10 text-danger',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusClass }} px-3 py-2 rounded-pill small fw-bold">{{ strtoupper($row['status']) }}</span>
                                        </td>
                                        <td class="text-center py-3">
                                            <div class="text-muted small fw-medium"><i class="bx bx-map-pin me-1 text-primary"></i>{{ $row['lokasi'] }}</div>
                                            @if($row['keterangan'])
                                            <small class="text-muted text-italic">({{ $row['keterangan'] }})</small>
                                            @endif
                                        </td>
                                        @if(Auth::user()->id_role == 1)
                                        <td class="pe-4 text-end py-3">
                                            @if($row['absensi_id'])
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#deleteAbs{{ $row['absensi_id'] }}">Hapus</button>
                                            @else
                                            <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="py-5 text-center text-muted">Belum ada data harian.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODALS FOR ADMIN CONTROL ================= -->
@if(Auth::user()->id_role == 1)
<!-- Modal Manual Absensi -->
<div class="modal fade" id="modalManual" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="bx bx-plus-circle me-1 text-primary"></i> INPUT ABSENSI MANUAL</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('absensi.store-manual') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Pilih Pegawai</label>
                        <select name="user_id" class="form-select" required style="border-radius: 8px;">
                            @foreach($allUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Tanggal Absen</label>
                        <input type="date" name="tgl" class="form-control" value="{{ date('Y-m-d') }}" required style="border-radius: 8px;">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small text-muted">Jam Masuk (Check-In)</label>
                            <input type="time" name="jam_masuk" class="form-control" style="border-radius: 8px;">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small text-muted">Jam Pulang (Check-Out)</label>
                            <input type="time" name="jam_pulang" class="form-control" style="border-radius: 8px;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Status Kehadiran</label>
                        <select name="status_kehadiran" class="form-select" required style="border-radius: 8px;">
                            <option value="Hadir">Hadir</option>
                            <option value="Terlambat">Terlambat</option>
                            <option value="Pulang Lebih Awal">Pulang Lebih Awal</option>
                            <option value="Terlambat & Pulang Awal">Terlambat & Pulang Awal</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Izin">Izin</option>
                            <option value="Cuti">Cuti</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Keterangan / Catatan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Izin urusan keluarga atau dinas luar" style="border-radius: 8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold border" data-bs-dismiss="modal">Batalkan</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import CSV -->
<div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="bx bx-upload me-1 text-success"></i> IMPORT CSV DARI SOFTWARE LOKAL</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('absensi.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted small">Anda bisa mengimpor data backup kehadiran pegawai menggunakan berkas .CSV dari aplikasi eksternal. Struktur kolom berkas CSV wajib mengikuti format berikut:</p>
                    
                    <div class="p-3 bg-light rounded-3 mb-3 border" style="border-radius: 8px;">
                        <code class="text-dark small fw-bold">PIN_FINGERSPOT, TANGGAL, JAM_MASUK, JAM_PULANG</code><br>
                        <small class="text-muted">Contoh baris data: <span class="text-success">5, 2026-05-29, 07:45:00, 17:05:00</span></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Pilih Berkas CSV (.csv / .txt)</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required style="border-radius: 8px;">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm text-white">Mulai Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals for Deletion -->
@if($tab === 'harian')
    @if($targetUserId === 'all')
        @foreach($reportData as $row)
        <div class="modal fade" id="deleteAbs{{ $row->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="modal-body p-4 text-center">
                        <div class="bg-light p-3 rounded-circle d-inline-block mb-3">
                            <i class="bx bx-trash text-danger fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Hapus Log Absensi?</h5>
                        <p class="text-muted small mb-4">Log absensi milik *{{ $row->user->name ?? '' }}* pada tanggal *{{ $row->tgl->format('d M Y') }}* akan dihapus dari sistem. Lanjutkan?</p>
                        
                        <form action="{{ route('absensi.destroy', $row->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="d-flex flex-column gap-2">
                                <button type="submit" class="btn btn-danger rounded-pill fw-bold py-2 shadow-sm">Ya, Hapus Sekarang</button>
                                <button type="button" class="btn btn-light rounded-pill fw-bold py-2 border" data-bs-dismiss="modal">Batalkan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    @else
        @foreach($harianData as $row)
            @if($row['absensi_id'])
            <div class="modal fade" id="deleteAbs{{ $row['absensi_id'] }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                        <div class="modal-body p-4 text-center">
                            <div class="bg-light p-3 rounded-circle d-inline-block mb-3">
                                <i class="bx bx-trash text-danger fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">Hapus Log Absensi?</h5>
                            <p class="text-muted small mb-4">Log absensi pada tanggal *{{ \Carbon\Carbon::parse($row['tanggal'])->format('d M Y') }}* akan dihapus. Lanjutkan?</p>
                            
                            <form action="{{ route('absensi.destroy', $row['absensi_id']) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="d-flex flex-column gap-2">
                                    <button type="submit" class="btn btn-danger rounded-pill fw-bold py-2 shadow-sm">Ya, Hapus Sekarang</button>
                                    <button type="button" class="btn btn-light rounded-pill fw-bold py-2 border" data-bs-dismiss="modal">Batalkan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    @endif
@endif

@endif

<style>
.bg-label-success {
    background-color: #d1fae5 !important;
    color: #065f46 !important;
}
.bg-label-danger {
    background-color: #fee2e2 !important;
    color: #991b1b !important;
}
.btn-outline-indigo {
    color: #4f46e5 !important;
    border-color: #818cf8 !important;
}
.btn-outline-indigo:hover {
    background-color: #4f46e5 !important;
    color: #ffffff !important;
}
</style>
@endsection
