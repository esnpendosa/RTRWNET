@extends('layouts/contentNavbarLayout')

@section('title', 'Laporan Tagihan')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Laporan /</span> Tagihan & Pembayaran</h4>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <h5 class="card-header">Filter Laporan</h5>
            <div class="card-body">
                <form action="{{ route('laporan.tagihan') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Bulan</label>
                            <select name="month" class="form-select">
                                <option value="">Semua Bulan</option>
                                @for($i=1; $i<=12; $i++)
                                    <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tahun</label>
                            <select name="year" class="form-select">
                                <option value="">Semua Tahun</option>
                                @for($i=date('Y'); $i>=2023; $i--)
                                    <option value="{{ $i }}" {{ request('year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                                <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-3">
                            <label class="form-label">Dari Tanggal (Bayar)</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sampai Tanggal (Bayar)</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <a href="{{ route('laporan.tagihan') }}" class="btn btn-outline-secondary me-2">Reset</a>
                            <a href="{{ route('laporan.tagihan.export', request()->all()) }}" class="btn btn-danger"><i class="bx bxs-file-pdf me-1"></i> Cetak PDF</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h5 class="card-title text-white">Total Tagihan</h5>
                <h3 class="text-white">Rp {{ number_format($total_jumlah) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h5 class="card-title text-white">Total Terbayar</h5>
                <h3 class="text-white">Rp {{ number_format($total_lunas) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h5 class="card-title text-white">Total Piutang</h5>
                <h3 class="text-white">Rp {{ number_format($total_piutang) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <h5 class="card-header">Data Pembayaran</h5>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pelanggan</th>
                    <th>Periode</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Metode</th>
                    <th>Tgl Bayar</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($tagihan as $t)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $t->pelanggan->nama_pelanggan }}</strong><br>
                        <small>{{ $t->pelanggan->kode_pelanggan }}</small>
                    </td>
                    <td>{{ date('F', mktime(0, 0, 0, $t->bulan, 10)) }} {{ $t->tahun }}</td>
                    <td>Rp {{ number_format($t->jumlah) }}</td>
                    <td>
                        @if($t->status == 'paid')
                            <span class="badge bg-label-success">LUNAS</span>
                        @else
                            <span class="badge bg-label-warning">BELUM BAYAR</span>
                        @endif
                    </td>
                    <td>{{ $t->metode_pembayaran ?: '-' }}</td>
                    <td>{{ $t->paid_at ? date('d/m/Y H:i', strtotime($t->paid_at)) : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Data tidak ditemukan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
