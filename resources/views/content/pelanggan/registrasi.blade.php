@extends('layouts/contentNavbarLayout')

@section('title', 'Registrasi Mandiri Baru')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Pelanggan /</span> Registrasi Mandiri</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <h5 class="mb-0">Daftar Pendaftaran Online</h5>
      <small class="text-muted">Kelola calon pelanggan yang mendaftar mandiri via form online di satu halaman</small>
    </div>
    <div class="d-flex align-items-center">
      <form action="{{ route('pelanggan.registrasi.index') }}" method="GET" class="me-2">
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="bx bx-search"></i></span>
          <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari pendaftar..." value="{{ request('search') }}">
        </div>
      </form>
      <a href="{{ route('public.register') }}" target="_blank" class="btn btn-outline-primary btn-sm">
        <i class="bx bx-link-external me-1"></i> Buka Form Publik
      </a>
    </div>
  </div>

  @if($registrations->isEmpty())
    <div class="card-body text-center py-5">
      <div class="mb-3">
        <i class="bx bx-user-voice text-muted" style="font-size: 5rem;"></i>
      </div>
      <h5 class="text-muted">Tidak Ada Pendaftaran Baru</h5>
      <p class="text-muted mb-0">Belum ada calon pelanggan yang mendaftar secara online atau pencarian Anda tidak ditemukan.</p>
    </div>
  @else
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Kode Reg</th>
            <th>Nama Pelanggan</th>
            <th>No. WhatsApp</th>
            <th>Alamat</th>
            <th>Paket Pilihan</th>
            <th>Koordinat & Maps</th>
            <th>Status Aktif</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @foreach($registrations as $p)
          <tr>
            <td><strong>{{ $p->kode_pelanggan }}</strong></td>
            <td>
              <span class="fw-semibold">{{ $p->nama_pelanggan }}</span>
            </td>
            <td>
              <div class="d-flex align-items-center gap-1">
                <span>{{ $p->no_wa }}</span>
                @if($p->no_wa)
                  <form action="{{ route('pelanggan.registrasi.send-to-group', $p->id_pelanggan) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-icon btn-sm btn-outline-success" title="Kirim Info Registrasi ke Grup WhatsApp">
                      <i class="bx bxl-whatsapp"></i>
                    </button>
                  </form>
                @endif
              </div>
            </td>
            <td>
              <span title="{{ $p->alamat }}">{{ Str::limit($p->alamat, 25) }}</span>
            </td>
            <td>
              <span class="badge bg-label-info">{{ $p->paket ?? 'umum' }}</span>
              <div class="small text-muted">Rp {{ number_format($p->harga_layanan, 0, ',', '.') }}</div>
            </td>
            <td>
              @if($p->latitude && $p->longitude)
                <a href="https://www.google.com/maps?q={{ $p->latitude }},{{ $p->longitude }}" target="_blank" class="btn btn-xs btn-outline-primary">
                  <i class="bx bx-map-alt me-1"></i> Buka Google Maps
                </a>
                <div class="small text-muted" style="font-size: 10px;">{{ Str::limit($p->latitude, 8, '') }}, {{ Str::limit($p->longitude, 8, '') }}</div>
              @else
                <span class="text-muted small">Tidak ada koordinat</span>
              @endif
            </td>
            <td>
              <form action="{{ route('pelanggan.toggle-status', $p->id_pelanggan) }}" method="POST" class="d-inline">
                @csrf
                <div class="form-check form-switch d-inline-block">
                  <input class="form-check-input" type="checkbox" role="switch" onchange="this.form.submit()" style="cursor: pointer; width: 2.5em; height: 1.25em;" {{ $p->is_active ? 'checked' : '' }} title="{{ $p->is_active ? 'Nonaktifkan (Kembalikan ke antrean)' : 'Aktifkan & Sinkronkan MikroTik' }}">
                </div>
              </form>
              @if($p->is_active)
                <span class="badge bg-label-success ms-1">Aktif</span>
              @else
                <span class="badge bg-label-warning ms-1">Menunggu</span>
              @endif
            </td>
            <td>
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="{{ route('pelanggan.show', $p->id_pelanggan) }}"><i class="bx bx-show-alt me-1"></i> Detail / Statistik</a>
                  <a class="dropdown-item" href="{{ route('pelanggan.edit', $p->id_pelanggan) }}"><i class="bx bx-edit-alt me-1"></i> Edit & Tentukan Router</a>
                  <form action="{{ route('pelanggan.destroy', $p->id_pelanggan) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pendaftaran ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger"><i class="bx bx-trash me-1"></i> Hapus</button>
                  </form>
                </div>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
@endsection
