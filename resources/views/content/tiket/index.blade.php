@extends('layouts/contentNavbarLayout')

@section('title', 'Tiket Gangguan')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Operasional /</span> Tiket Gangguan</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Daftar Tiket</h5>
    <a href="{{ route('tiket.create') }}" class="btn btn-primary btn-sm">Buat Tiket</a>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Pelanggan</th>
          <th>Keluhan</th>
          <th>Teknisi</th>
          <th>Prioritas</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($tiket as $t)
        <tr>
          <td>{{ $t->kode_tiket }}</td>
          <td>{{ $t->pelanggan->nama_pelanggan }}</td>
          <td>{{ Str::limit($t->keluhan, 20) }}</td>
          <td>{{ $t->teknisi->nama_teknisi ?? '-' }}</td>
          <td>
            <span class="badge {{ $t->prioritas == 'High' ? 'bg-danger' : 'bg-warning' }}">{{ $t->prioritas }}</span>
          </td>
          <td>{{ $t->status }}</td>
          <td>
            <form action="{{ route('tiket.status', $t->id_tiket) }}" method="POST" style="display:inline;">
              @csrf
              <select name="status" onchange="this.form.submit()" class="form-select form-select-sm" style="width: auto; display: inline-block;">
                <option value="Open" {{ $t->status == 'Open' ? 'selected' : '' }}>Open</option>
                <option value="In Progress" {{ $t->status == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                <option value="Resolved" {{ $t->status == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="Closed" {{ $t->status == 'Closed' ? 'selected' : '' }}>Closed</option>
              </select>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
