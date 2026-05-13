@extends('layouts/contentNavbarLayout')

@section('title', 'Manajemen Pengguna')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sistem /</span> Pengguna</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Daftar Pengguna</h5>
    <div class="d-flex align-items-center">
      <form action="{{ route('users.index') }}" method="GET" class="me-3">
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="bx bx-search"></i></span>
          <input type="text" name="search" class="form-control" placeholder="Cari nama/email..." value="{{ request('search') }}" />
        </div>
      </form>
      <form action="{{ route('users.reset-customer-passwords') }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin mereset password SEMUA pengguna pelanggan menjadi 12345678?')">
        @csrf
        <button type="submit" class="btn btn-warning btn-sm me-2">
          <i class="bx bx-refresh me-1"></i> Reset Pass Pelanggan
        </button>
      </form>
      <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">Tambah Pengguna</a>
    </div>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Nama</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach($users as $user)
        <tr>
          <td>{{ $user->name }}</td>
          <td>{{ $user->email }}</td>
          <td><span class="badge bg-label-primary">{{ $user->role->name ?? 'None' }}</span></td>
          <td><span class="badge bg-label-success">{{ $user->is_active ? 'Aktif' : 'Non-aktif' }}</span></td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('users.edit', $user->id) }}"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                @if($user->id !== auth()->id())
                <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="dropdown-item"><i class="bx bx-trash me-1"></i> Delete</button>
                </form>
                @endif
              </div>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
