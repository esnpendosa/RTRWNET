@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Router - ' . $router->nama_router)

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Mikrotik /</span> Edit Router</h4>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Config Router</h5>
                <small class="text-muted float-end">Update kredensial mikrotik</small>
            </div>
            <div class="card-body">
                <form action="{{ route('mikrotik.update', $router->id_router) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label" for="nama_router">Nama Router</label>
                        <input type="text" name="nama_router" id="nama_router" class="form-control" value="{{ $router->nama_router }}" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="ip_host">IP Host / Domain</label>
                        <input type="text" name="ip_host" id="ip_host" class="form-control" value="{{ $router->ip_host }}" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="api_port">API Port</label>
                        <input type="number" name="api_port" id="api_port" class="form-control" value="{{ $router->api_port }}" />
                        <small class="text-muted">Default: 8728 (Winbox API)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="username">User API</label>
                        <input type="text" name="username" id="username" class="form-control" value="{{ $router->username }}" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Pass API (Kosongkan jika tidak diubah)</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" />
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('mikrotik.index') }}" class="btn btn-outline-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card bg-label-info">
            <div class="card-body">
                <h5 class="card-title text-info">Informasi API Mikrotik</h5>
                <p class="card-text">
                    Untuk terhubung, pastikan service API di Mikrotik sudah aktif:
                </p>
                <code>/ip service enable api</code><br>
                <code>/ip service set api port=8728</code>
                <p class="mt-3">
                    Gunakan user Mikrotik yang memiliki group <b>full</b> atau setidaknya akses <b>read, write, api</b>.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
