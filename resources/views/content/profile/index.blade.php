@extends('layouts/contentNavbarLayout')

@section('title', 'My Profile')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Sistem /</span> Pengaturan Profil
</h4>

<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Detail Profil</h5>
      <!-- Account -->
      <div class="card-body">
        <div class="d-flex align-items-start align-items-sm-center gap-4">
          <img src="{{ asset('assets/img/avatars/1.png') }}" alt="user-avatar" class="d-block rounded" height="100" width="100" id="uploadedAvatar" />
          <div class="button-wrapper">
            <h4 class="mb-1">{{ $user->name }}</h4>
            <p class="text-muted mb-0">{{ $user->role->name ?? 'User' }}</p>
          </div>
        </div>
      </div>
      <hr class="my-0">
      <div class="card-body">
        <form id="formAccountSettings" method="POST" action="{{ route('profile.update') }}">
          @csrf
          @method('PUT')
          <div class="row">
            <div class="mb-3 col-md-6">
              <label for="name" class="form-label">Nama Lengkap</label>
              <input class="form-control" type="text" id="name" name="name" value="{{ old('name', $user->name) }}" autofocus required />
            </div>
            <div class="mb-3 col-md-6">
              <label for="email" class="form-label">E-mail</label>
              <input class="form-control" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="john.doe@example.com" required />
            </div>
          </div>

          <hr class="my-4">
          <h6 class="fw-bold">Ganti Password (Kosongkan jika tidak ingin mengubah)</h6>
          
          <div class="row">
            <div class="mb-3 col-md-4">
              <label for="current_password" class="form-label">Password Sekarang</label>
              <input class="form-control" type="password" name="current_password" id="current_password" />
            </div>
            <div class="mb-3 col-md-4">
              <label for="new_password" class="form-label">Password Baru</label>
              <input class="form-control" type="password" name="new_password" id="new_password" />
            </div>
            <div class="mb-3 col-md-4">
              <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
              <input class="form-control" type="password" name="new_password_confirmation" id="new_password_confirmation" />
            </div>
          </div>

          <div class="mt-2">
            <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
            <button type="reset" class="btn btn-outline-secondary">Reset</button>
          </div>
        </form>
      </div>
      <!-- /Account -->
    </div>
  </div>
</div>
@endsection
