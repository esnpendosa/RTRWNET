@extends('layouts/contentNavbarLayout')

@section('title', 'Optimasi Rute Teknisi')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Optimasi /</span> Rute Kunjungan</h4>

<div class="row">
  <div class="col-md-12 mb-4">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Generate Rute Baru (K-Nearest Neighbor / KNN)</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('rute.generate') }}" method="POST">
          @csrf
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Pilih Teknisi</label>
              <select name="id_teknisi" id="teknisi_select" class="form-select select2" required>
                @foreach($teknisi as $t)
                  <option value="{{ $t->id_teknisi }}">{{ $t->nama_teknisi }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-8 mb-3">
              <label class="form-label">Pilih Pelanggan Tujuan (High/Medium Priority)</label>
              <input type="text" id="searchPelanggan" class="form-control form-control-sm mb-3" placeholder="Cari by ID, Nama, atau Prioritas...">
              <div class="row" id="pelangganList" style="max-height: 250px; overflow-y: auto;">
                @foreach($pelangganHigh as $p)
                <div class="col-md-4 pelanggan-item" data-search="{{ strtolower($p->id_pelanggan . ' ' . $p->nama_pelanggan . ' ' . $p->prioritas_label) }}">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="pelanggan_ids[]" value="{{ $p->id_pelanggan }}" id="p-{{ $p->id_pelanggan }}">
                    <label class="form-check-label" for="p-{{ $p->id_pelanggan }}">
                      {{ $p->id_pelanggan }} - {{ $p->nama_pelanggan }} ({{ $p->prioritas_label }})
                    </label>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary mt-3">Generate Optimasi Rute</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-12">
    <div class="card">
      <h5 class="card-header">Daftar Rute Teroptimasi</h5>
      <div class="table-responsive text-nowrap">
        <table class="table">
          <thead>
            <tr>
              <th>Teknisi</th>
              <th>Tanggal</th>
              <th>Total Jarak</th>
              <th>Metode</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($rute as $r)
            <tr>
              <td>{{ $r->teknisi->nama_teknisi }}</td>
              <td>{{ $r->tanggal_kunjungan }}</td>
              <td>{{ number_format($r->total_jarak_km, 2) }} KM</td>
              <td>{{ $r->metode }}</td>
              <td>
                <span class="badge {{ $r->status == 'Completed' ? 'bg-label-success' : 'bg-label-primary' }}">
                  {{ $r->status == 'Completed' ? 'Selesai' : ($r->status == 'Planned' ? 'Direncanakan' : $r->status) }}
                </span>
              </td>
              <td>
                <a href="{{ route('rute.show', $r->id_rute) }}" class="btn btn-sm btn-outline-info">Detail & Map</a>
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

@section('page-script')
  <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        new TomSelect("#teknisi_select", {
            create: false,
            placeholder: "-- Pilih Teknisi --"
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchPelanggan');
        const items = document.querySelectorAll('.pelanggan-item');

        searchInput.addEventListener('keyup', function() {
            const query = this.value.toLowerCase();
            items.forEach(function(item) {
                const text = item.getAttribute('data-search');
                if (text.includes(query)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
  </script>
@endsection
