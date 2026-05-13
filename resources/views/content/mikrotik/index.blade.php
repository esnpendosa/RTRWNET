@extends('layouts/contentNavbarLayout')

@section('title', 'Monitoring Mikrotik')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sistem /</span> Monitoring Mikrotik</h4>

<div class="row">
  <div class="col-md-4">
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Tambah Router</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('mikrotik.store') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label">Nama Router</label>
            <input type="text" name="nama_router" class="form-control" placeholder="Mikrotik Core" required />
          </div>
          <div class="mb-3">
            <label class="form-label">IP Host</label>
            <input type="text" name="ip_host" class="form-control" placeholder="192.168.1.1" required />
          </div>
          <div class="mb-3">
            <label class="form-label">User API</label>
            <input type="text" name="username" class="form-control" required />
          </div>
          <div class="mb-3">
            <label class="form-label">Pass API</label>
            <input type="password" name="password" class="form-control" required />
          </div>
          <button type="submit" class="btn btn-primary w-100">Simpan Router</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-8">
    <div class="card">
      <h5 class="card-header">Daftar Router</h5>
      <div class="table-responsive text-nowrap">
        <table class="table">
          <thead>
            <tr>
              <th>Router</th>
              <th>IP Host</th>
              <th>Status</th>
              <th>Last Sync</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($routers as $r)
            <tr>
              <td>{{ $r->nama_router }}</td>
              <td>{{ $r->ip_host }}</td>
              <td>
                <span class="badge {{ $r->status_koneksi == 'Connected' ? 'bg-label-success' : (str_contains(strtolower($r->status_koneksi), 'simulated') ? 'bg-label-warning' : 'bg-label-danger') }}">
                  {{ $r->status_koneksi }}
                </span>
              </td>
              <td>{{ $r->last_sync_at }}</td>
              <td>
                <a href="{{ route('mikrotik.sync', $r->id_router) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Sync Stats"><i class="bx bx-sync"></i></a>
                <a href="{{ route('mikrotik.stats', $r->id_router) }}" class="btn btn-sm btn-icon btn-outline-info" title="Lihat Statistik"><i class="bx bx-bar-chart-alt-2"></i></a>
                <a href="{{ route('mikrotik.edit', $r->id_router) }}" class="btn btn-sm btn-icon btn-outline-warning" title="Edit Config"><i class="bx bx-edit-alt"></i></a>
                <form action="{{ route('mikrotik.destroy', $r->id_router) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus router ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus Router"><i class="bx bx-trash"></i></button>
                </form>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
