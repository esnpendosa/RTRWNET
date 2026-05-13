@extends('layouts/blankLayout')

@section('title', 'Tidak Ada Tagihan - RTRW Net')

@section('page-style')
<style>
    body {
        background: #f5f7ff;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .status-card {
        max-width: 450px;
        width: 90%;
        border-radius: 20px;
        background: white;
        padding: 40px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .icon-success {
        font-size: 5rem;
        color: #71dd37;
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')
<div class="status-card">
    <div class="icon-success">
        <i class="bx bxs-check-circle"></i>
    </div>
    <h4>Halo, {{ $pelanggan->nama_pelanggan }}!</h4>
    <p class="text-muted">Semua tagihan Anda sudah terbayar. Terima kasih telah berlangganan!</p>
    <hr>
    <a href="/" class="btn btn-primary w-100">Kembali ke Beranda</a>
</div>
@endsection
