@extends('layouts/contentNavbarLayout')

@section('title', 'Technician Nav - Jalur Jalan')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 border-primary shadow-sm">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 text-white"><i class="bx bx-map-pin me-2"></i>Navigasi Jalur Real-time</h5>
                <button class="btn btn-sm btn-light fw-bold" onclick="location.reload()"><i class="bx bx-refresh"></i> Update Rute</button>
            </div>
            <div class="card-body p-0">
                <div id="nav-map" style="height: 65vh; width: 100%;"></div>
            </div>
            <div class="card-footer bg-white p-3">
                <div class="row text-center gx-2">
                    <div class="col-4 border-end">
                        <small class="text-muted d-block uppercase text-xs">Total Jarak (Jalan)</small>
                        <span class="fw-bold text-primary"><i class="bx bx-navigation me-1"></i><span id="total-dist">--</span> KM</span>
                    </div>
                    <div class="col-4 border-end">
                        <small class="text-muted d-block uppercase text-xs">Estimasi Waktu</small>
                        <span class="fw-bold text-success"><i class="bx bx-time me-1"></i><span id="total-time">--</span> Mnt</span>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block uppercase text-xs">Titik Kunjungan</small>
                        <span class="fw-bold text-danger"><i class="bx bx-user me-1"></i><span id="cust-count">0</span> Lokasi</span>
                    </div>
                </div>
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
        var baseLat = {{ $teknisi->base_latitude ?? -7.1593 }};
        var baseLng = {{ $teknisi->base_longitude ?? 112.6519 }};
        
        var map = L.map('nav-map', {zoomControl: false}).setView([baseLat, baseLng], 14);
        L.control.zoom({position: 'bottomright'}).addTo(map);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var waypoints = [];
        waypoints.push(L.latLng(baseLat, baseLng));

        // Basecamp (Start)
        L.marker([baseLat, baseLng], {
            icon: L.icon({
                iconUrl: 'https://cdn-icons-png.flaticon.com/512/609/609803.png',
                iconSize: [35, 35],
                iconAnchor: [17, 35]
            })
        }).addTo(map).bindPopup("<b>Basecamp (Start Point)</b>");

        var routeData = @json($optimizedRoute['route'] ?? []);
        document.getElementById('cust-count').innerText = routeData.length;

        routeData.forEach(function(d, index) {
            var p = L.latLng(d.latitude, d.longitude);
            waypoints.push(p);

            // Customer Marker
            var marker = L.circleMarker([d.latitude, d.longitude], {
                radius: 14,
                fillColor: '#ff3e1d',
                color: '#fff',
                weight: 3,
                fillOpacity: 1
            }).addTo(map);

            marker.bindPopup(`
                <div class="text-center" style="min-width: 180px;">
                    <span class="badge bg-label-danger mb-2">Pemberhentian ${index + 1}</span>
                    <h6 class="mb-1">${d.nama_pelanggan}</h6>
                    <p class="mb-2 small text-muted">${d.alamat}</p>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=${d.latitude},${d.longitude}" target="_blank" class="btn btn-primary btn-sm w-100 mb-2">
                        <i class="bx bx-navigation me-1"></i> Buka Google Maps
                    </a>
                </div>
            `);
            
            // Sequence Number UI
            L.marker([d.latitude, d.longitude], {
                icon: L.divIcon({
                    className: 'custom-div-icon',
                    html: "<div style='color: white; font-weight: bold; font-size: 14px; margin-top: -10px; margin-left: -4px;'>" + (index + 1) + "</div>",
                    iconSize: [0, 0]
                })
            }).addTo(map);
        });

        // Initialize Routing Control (FOLLOWING ACTUAL ROADS)
        if (waypoints.length > 1) {
            var control = L.Routing.control({
                waypoints: waypoints,
                router: L.Routing.osrmv1({
                    serviceUrl: 'https://router.project-osrm.org/route/v1'
                }),
                lineOptions: {
                    styles: [{color: '#696cff', opacity: 0.8, weight: 6}]
                },
                addWaypoints: false,
                draggableWaypoints: false,
                fitSelectedRoutes: true,
                showAlternatives: false,
                createMarker: function() { return null; } // Custom markers used above
            }).addTo(map);

            control.on('routesfound', function(e) {
                var routes = e.routes;
                var summary = routes[0].summary;
                // Update stats based on actual road network distance
                document.getElementById('total-dist').innerText = (summary.totalDistance / 1000).toFixed(2);
                document.getElementById('total-time').innerText = Math.round(summary.totalTime / 60);
            });

            // Hide the default routing instructions container
            var container = document.querySelector('.leaflet-routing-container');
            if (container) container.style.display = 'none';
        }

        // Routers / Mikrotik Locations
        @foreach($routers as $router)
            L.marker([baseLat + 0.005, baseLng + 0.005], {
                icon: L.icon({
                    iconUrl: 'https://cdn-icons-png.flaticon.com/512/1000/1000966.png',
                    iconSize: [25, 25]
                })
            }).addTo(map).bindPopup("<b>Router Infrastructure: {{ $router->nama_router }}</b>");
        @endforeach
    });
</script>

<style>
    .uppercase { text-transform: uppercase; }
    .text-xs { font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px; }
    .custom-div-icon { background: none; border: none; }
</style>
@endsection
