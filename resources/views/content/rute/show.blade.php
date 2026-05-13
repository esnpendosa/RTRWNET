@extends('layouts/contentNavbarLayout')

@section('title', 'Detail Rute Kunjungan')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Rute /</span> Detail Kunjungan #{{ $rute->id_rute }}</h4>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informasi Rute</h5>
            </div>
            <div class="card-body">
                <p><strong>Teknisi:</strong> {{ $rute->teknisi->nama_teknisi }}</p>
                <p><strong>Tanggal:</strong> {{ $rute->tanggal_kunjungan }}</p>
                <p><strong>Total Jarak:</strong> {{ number_format($rute->total_jarak_km, 2) }} KM</p>
                <hr>
                <h6>Urutan Kunjungan:</h6>
                <ul class="list-group">
                    <li class="list-group-item bg-light">Titik Awal (Kantor CV. ROZITECH)</li>
                    @foreach($rute->details as $d)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $d->urutan }}. {{ $d->pelanggan->nama_pelanggan }}</strong> <br>
                            <small class="text-muted">
                                Jarak: {{ number_format($d->jarak_dari_sebelumnya_km, 2) }} KM | 
                                Estimasi: {{ $d->estimasi_waktu_menit }} Menit
                            </small> <br>
                            <span class="badge {{ $d->status_kunjungan == 'Visited' ? 'bg-label-success' : 'bg-label-secondary' }}">
                                {{ $d->status_kunjungan == 'Visited' ? 'Selesai' : ($d->status_kunjungan == 'Pending' ? 'Menunggu' : $d->status_kunjungan) }}
                            </span>
                        </div>
                        @if($d->status_kunjungan == 'Pending')
                        <div class="d-flex gap-2">
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $d->pelanggan->latitude }},{{ $d->pelanggan->longitude }}" target="_blank" class="btn btn-sm btn-info">
                                <i class="bx bx-navigation"></i> Navigasi
                            </a>
                            <form action="{{ route('rute.detail.status', $d->id_rute_detail) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Selesai</button>
                            </form>
                        </div>
                        @else
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $d->pelanggan->latitude }},{{ $d->pelanggan->longitude }}" target="_blank" class="btn btn-sm btn-outline-info">
                            <i class="bx bx-navigation"></i> Peta
                        </a>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Visualisasi Rute (Leaflet Polyline)</h5>
            </div>
            <div class="card-body">
                <div id="map" style="height: 500px; border-radius: 8px;"></div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var baseLat = {{ $rute->titik_awal_lat }};
        var baseLng = {{ $rute->titik_awal_lng }};
        
        var map = L.map('map').setView([baseLat, baseLng], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        var waypoints = [];
        waypoints.push(L.latLng(baseLat, baseLng));

        // Base Marker
        L.marker([baseLat, baseLng], {
            icon: L.icon({
                iconUrl: 'https://cdn-icons-png.flaticon.com/512/609/609803.png',
                iconSize: [30, 30]
            })
        }).addTo(map).bindPopup("<b>Kantor CV. ROZITECH</b>");

        var details = @json($rute->details->load('pelanggan'));
        details.forEach(function(d) {
            var p = L.latLng(d.pelanggan.latitude, d.pelanggan.longitude);
            waypoints.push(p);
            
            L.circleMarker([d.pelanggan.latitude, d.pelanggan.longitude], {
                radius: 10, 
                fillColor: 'red', 
                color: '#fff', 
                weight: 2, 
                fillOpacity: 1
            }).addTo(map).bindPopup("<b>" + d.urutan + ". " + d.pelanggan.nama_pelanggan + "</b><br>" + d.pelanggan.alamat);
        });

        // Road-Following Route
        if (waypoints.length > 1) {
            L.Routing.control({
                waypoints: waypoints,
                addWaypoints: false,
                draggableWaypoints: false,
                fitSelectedRoutes: true,
                showAlternatives: false,
                lineOptions: {
                    styles: [{color: 'blue', opacity: 0.6, weight: 4}]
                },
                createMarker: function() { return null; }
            }).addTo(map);

            // Hide the routing container for clean look
            setTimeout(function() {
                var container = document.querySelector('.leaflet-routing-container');
                if (container) container.style.display = 'none';
            }, 500);
        }
    });
</script>
@endsection
