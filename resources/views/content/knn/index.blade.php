@extends('layouts/contentNavbarLayout')

@section('title', 'Klasifikasi Prioritas KNN')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Analisis /</span> Klasifikasi KNN</h4>

<div class="row">
  <div class="col-md-4">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Proses KNN</h5>
        <form action="{{ route('knn.batch') }}" method="POST" class="d-inline">
          @csrf
          <input type="hidden" name="nilai_k" value="3">
          <button type="submit" class="btn btn-sm btn-outline-info">Proses Semua Pelanggan (K=3)</button>
        </form>
      </div>
      <div class="card-body">
        <form action="{{ route('knn.process') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label" for="id_pelanggan">Pilih Pelanggan</label>
            <select name="id_pelanggan" id="id_pelanggan" class="form-select" required>
              <option value="">-- Pilih Pelanggan --</option>
              @foreach($pelanggan as $p)
                <option value="{{ $p->id_pelanggan }}">{{ $p->nama_pelanggan }} (Usage: {{ $p->usage_gb }}GB)</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="nilai_k">Nilai K (Tetangga Terdekat)</label>
            <input type="number" name="nilai_k" id="nilai_k" class="form-control" value="3" min="1" required />
          </div>
          <button type="submit" class="btn btn-primary w-100">Proses Klasifikasi</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Riwayat Klasifikasi (Hasil Analisis KNN)</h5>
        <span class="badge bg-label-secondary">Metode: Euclidean + Haversine</span>
      </div>
      <div class="card-body">
        <div class="alert alert-primary d-flex align-items-center mb-3" role="alert">
          <span class="badge badge-center rounded-pill bg-primary me-3"><i class="bx bx-info-circle"></i></span>
          <div class="small">
            <strong>Penjelasan Jarak:</strong> Nilai jarak dihitung berdasarkan jarak fisik nyata (Haversine) dalam satuan **Kilometer (KM)**. Semakin kecil nilainya, semakin dekat lokasi pelanggan tersebut dengan tetangganya.
          </div>
        </div>
        <div class="table-responsive text-nowrap">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Data Pelanggan</th>
                <th>Parameter (K)</th>
                <th>Jarak (KM)</th>
                <th>Label Prioritas</th>
                <th>Waktu Proses</th>
              </tr>
            </thead>
            <tbody>
              @foreach($hasil as $h)
              <tr>
                <td>
                  <strong>{{ $h->pelanggan->nama_pelanggan }}</strong><br>
                  <small class="text-muted">Lat: {{ $h->pelanggan->latitude }}, Lng: {{ $h->pelanggan->longitude }}</small>
                </td>
                <td class="text-center">
                    <span class="badge bg-label-info">K={{ $h->parameter->nilai_k ?? '-' }}</span>
                </td>
                <td>
                    <span class="fw-bold text-primary">{{ number_format($h->jarak_min, 4) }}</span>
                    <small class="text-muted"> KM</small>
                </td>
                <td>
                  @if($h->label_hasil == 'High' || $h->label_hasil == 'Sangat Prioritas')
                    <span class="badge bg-danger">SANGAT PRIORITAS</span>
                  @elseif($h->label_hasil == 'Medium' || $h->label_hasil == 'Prioritas')
                    <span class="badge bg-warning text-dark">PRIORITAS</span>
                  @else
                    <span class="badge bg-success">TIDAK PRIORITAS</span>
                  @endif
                </td>
                <td><small>{{ $h->created_at->format('d/m/Y H:i') }}</small></td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@section('page-style')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('id_pelanggan')) {
            new TomSelect("#id_pelanggan", {
                create: false,
                placeholder: "-- Pilih Pelanggan --"
            });
        }
    });
</script>
@endsection
@endsection
