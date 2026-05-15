@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Titik ODC/ODP')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Manajemen Data / ODC & ODP /</span> Edit</h4>

<div class="row">
  <div class="col-xl">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Form Edit Titik Infrastruktur</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('odc-odp.update', $odcOdp->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')
          <div class="mb-3">
            <label class="form-label" for="nama">Nama Titik</label>
            <input type="text" class="form-control" id="nama" name="nama" value="{{ $odcOdp->nama }}" required />
          </div>
          <div class="mb-3">
            <label class="form-label" for="tipe">Tipe Perangkat</label>
            <select class="form-select" id="tipe" name="tipe" required onchange="toggleParentSelect()">
              <option value="ODC" {{ $odcOdp->tipe == 'ODC' ? 'selected' : '' }}>ODC (Optical Distribution Cabinet)</option>
              <option value="ODP" {{ $odcOdp->tipe == 'ODP' ? 'selected' : '' }}>ODP (Optical Distribution Point)</option>
            </select>
          </div>
          <div class="mb-3" id="parent-container">
            <label class="form-label" for="parent_id">Parent ODC (Sumber)</label>
            <select class="form-select" id="parent_id" name="parent_id">
              <option value="">-- Pilih ODC Sumber --</option>
              @foreach($odcs as $odc)
                <option value="{{ $odc->id }}" {{ $odcOdp->parent_id == $odc->id ? 'selected' : '' }}>{{ $odc->nama }}</option>
              @endforeach
            </select>
            <div class="form-text">Pilih ODC mana yang menyuplai ODP ini.</div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label" for="latitude">Latitude</label>
              <input type="text" class="form-control" id="lat" name="latitude" value="{{ $odcOdp->latitude }}" required readonly />
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label" for="longitude">Longitude</label>
              <input type="text" class="form-control" id="lng" name="longitude" value="{{ $odcOdp->longitude }}" required readonly />
            </div>
          </div>
          
          <div class="mb-3">
              <div id="map" style="height: 350px; border-radius: 8px;"></div>
              <small class="text-muted">Klik pada peta untuk mengubah lokasi</small>
          </div>

          <div class="mb-3">
            <label class="form-label" for="foto">Foto Lokasi</label>
            @if($odcOdp->foto)
              <div class="mb-2">
                <img src="{{ $odcOdp->foto }}" alt="Current Foto" width="150" class="rounded">
              </div>
            @endif
            <input type="file" class="form-control" id="foto" name="foto" accept="image/*" />
            <div class="form-text">Biarkan kosong jika tidak ingin mengubah foto.</div>
          </div>
          <div class="mb-3">
            <label class="form-label" for="deskripsi">Deskripsi / Catatan</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3">{{ $odcOdp->deskripsi }}</textarea>
          </div>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          <a href="{{ route('odc-odp.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </form>
      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var initialLat = {{ $odcOdp->latitude }};
        var initialLng = {{ $odcOdp->longitude }};
        var map = L.map('map').setView([initialLat, initialLng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        var marker = L.marker([initialLat, initialLng]).addTo(map);

        map.on('click', function(e) {
            if(marker) map.removeLayer(marker);
            marker = L.marker(e.latlng).addTo(map);
            document.getElementById('lat').value = e.latlng.lat;
            document.getElementById('lng').value = e.latlng.lng;
        });

        window.toggleParentSelect = function() {
            var tipe = document.getElementById('tipe').value;
            var container = document.getElementById('parent-container');
            if (tipe === 'ODC') {
                container.style.display = 'none';
                document.getElementById('parent_id').value = '';
            } else {
                container.style.display = 'block';
            }
        };
        
        toggleParentSelect(); // Init
    });
</script>
@endsection
