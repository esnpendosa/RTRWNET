@extends('layouts/contentNavbarLayout')

@section('title', 'Tambah Pelanggan')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Pelanggan /</span> Tambah</h4>

<div class="card">
  <div class="card-body">
    <form action="{{ route('pelanggan.store') }}" method="POST">
      @csrf
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Kode Pelanggan</label>
          <input type="text" name="kode_pelanggan" class="form-control" placeholder="PEL001" required />
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Nama Pelanggan</label>
          <input type="text" name="nama_pelanggan" class="form-control" required />
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">No. WhatsApp</label>
          <input type="text" name="no_wa" class="form-control" placeholder="6281xxx" />
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Email (Untuk Akun Login)</label>
          <input type="email" name="email" class="form-control" placeholder="pelanggan@example.com" />
          <small class="text-muted">Kosongkan jika ingin generate otomatis: (kode)@rtrwnet.com</small>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Alamat</label>
          <textarea name="alamat" class="form-control" required></textarea>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Latitude</label>
          <input type="text" name="latitude" id="lat" class="form-control" value="{{ $lat ?? '' }}" required />
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Longitude</label>
          <input type="text" name="longitude" id="lng" class="form-control" value="{{ $lng ?? '' }}" required />
        </div>
        <div class="col-md-12 mb-3">
            <div id="map" style="height: 300px; border-radius: 8px;"></div>
            <small class="text-muted">Klik pada peta untuk menentukan lokasi</small>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Usage (GB)</label>
            <input type="number" name="usage_gb" class="form-control" value="0" />
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Jumlah Device</label>
            <input type="number" name="jumlah_device" class="form-control" value="0" />
        </div>
        <hr>
        <h5>Mapping Mikrotik & Billing</h5>
        <div class="col-md-6 mb-3">
            <label class="form-label">Router</label>
            <select name="id_router" class="form-select">
                <option value="">-- Pilih Router --</option>
                @foreach($routers as $r)
                <option value="{{ $r->id_router }}">{{ $r->nama_router }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Tipe Layanan</label>
            <select name="mikrotik_type" class="form-select">
                <option value="pppoe">PPPoE</option>
                <option value="hotspot">Hotspot</option>
                <option value="static">Static/Firewall</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Harga Layanan (Rp)</label>
            <input type="number" name="harga_layanan" class="form-control" placeholder="150000" required />
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Alamat IP (Static/ONT)</label>
            <input type="text" name="ip_address" class="form-control" placeholder="192.168.1.1" />
            <small class="text-muted">Diisi jika ingin monitoring IP spesifik secara manual</small>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Tanggal Penagihan (Billing Date)</label>
            <select name="billing_date" class="form-select" required>
                @for($i=1; $i<=28; $i++)
                <option value="{{ $i }}">Tanggal {{ $i }}</option>
                @endfor
            </select>
            <small class="text-muted">Tagihan otomatis dibuat setiap bulan pada tanggal ini.</small>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
  </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var initialLat = {{ $lat ?? -7.1207 }};
        var initialLng = {{ $lng ?? 112.5959 }};
        var map = L.map('map').setView([initialLat, initialLng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        var marker;
        
        // Jika ada koordinat awal, buat marker
        if ({{ isset($lat) ? 'true' : 'false' }}) {
            marker = L.marker([initialLat, initialLng]).addTo(map);
        }

        map.on('click', function(e) {
            if(marker) map.removeLayer(marker);
            marker = L.marker(e.latlng).addTo(map);
            document.getElementById('lat').value = e.latlng.lat;
            document.getElementById('lng').value = e.latlng.lng;
        });
    });
</script>
@endsection
