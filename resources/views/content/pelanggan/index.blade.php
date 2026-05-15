@extends('layouts/contentNavbarLayout')

@section('title', 'Data Pelanggan')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Data /</span> Pelanggan</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Daftar Pelanggan</h5>
    <div class="d-flex align-items-center">
      <form action="{{ route('pelanggan.index') }}" method="GET" class="me-2">
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="bx bx-search"></i></span>
          <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari pelanggan..." value="{{ request('search') }}">
        </div>
      </form>
      <a href="{{ route('pelanggan.export') }}" class="btn btn-outline-success btn-sm me-1">
        <i class="bx bx-download me-1"></i> Export
      </a>
      <button type="button" class="btn btn-outline-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#importModal">
        <i class="bx bx-upload me-1"></i> Import
      </button>
      <a href="{{ route('pelanggan.card-massal', ['search' => request('search')]) }}" class="btn btn-outline-info btn-sm me-1" target="_blank">
        <i class="bx bx-id-card me-1"></i> Cetak Kartu
      </a>
      <a href="{{ route('pelanggan.create') }}" class="btn btn-primary btn-sm">
        <i class="bx bx-plus me-1"></i> Tambah
      </a>
    </div>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Nama</th>
          <th>Alamat</th>
          <th>Usage (GB)</th>
          <th>Devices</th>
          <th>Paket</th>
          <th>Status</th>
          <th>Prioritas</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach($pelanggan as $p)
        <tr>
          <td><strong>{{ $p->kode_pelanggan }}</strong></td>
          <td>{{ $p->nama_pelanggan }}</td>
          <td>{{ Str::limit($p->alamat, 30) }}</td>
          <td>{{ $p->usage_gb }} GB</td>
          <td>{{ $p->jumlah_device }}</td>
          <td><span class="badge bg-label-primary">{{ $p->paket ?? '-' }}</span></td>
          <td>
            <form action="{{ route('pelanggan.toggle-status', $p->id_pelanggan) }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-sm p-0">
                <span class="badge {{ $p->is_active ? 'bg-label-success' : 'bg-label-danger' }}">
                  {{ $p->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </button>
            </form>
          </td>
          <td>
            @php
              $badge = ($p->prioritas_label == 'High' || $p->prioritas_label == 'Medium') ? 'bg-label-danger' : 'bg-label-success';
            @endphp
            <span class="badge {{ $badge }}">{{ $p->prioritas_label ?? 'Low' }}</span>
          </td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                <a class="dropdown-item" href="{{ route('pelanggan.show', $p->id_pelanggan) }}"><i class="bx bx-show-alt me-1"></i> Detail / Statistik</a>
                <a href="{{ route('pelanggan.card', $p->id_pelanggan) }}" class="dropdown-item" target="_blank">
                    <i class="bx bx-id-card me-1"></i> Cetak Kartu
                </a>
                <a class="dropdown-item" href="{{ route('pelanggan.edit', $p->id_pelanggan) }}"><i class="bx bx-edit-alt me-1"></i> Edit</a>

                <form action="{{ route('pelanggan.toggle-status', $p->id_pelanggan) }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="bx {{ $p->is_active ? 'bx-power-off' : 'bx-play' }} me-1"></i> {{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Import Data Pelanggan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('pelanggan.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="row">
            <div class="col mb-3">
              <label class="form-label">File Excel (.xlsx, .xls)</label>
              <input type="file" name="file" class="form-control" required>
            </div>
          </div>
          <div class="alert alert-info py-2 small mb-0">
            <strong>Info:</strong> Gunakan format header yang sesuai (ID, Kode, Nama, WA, User, Tipe, Alamat, Lat, Lng, Harga, Billing, Status). <br>
            <a href="{{ route('pelanggan.export') }}" class="text-primary fw-bold">Download Template (Export data sekarang)</a>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-primary">Mulai Import</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
