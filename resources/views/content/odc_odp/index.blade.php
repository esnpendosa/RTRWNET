@extends('layouts/contentNavbarLayout')

@section('title', 'Web GIS ODC & ODP')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Manajemen Data /</span> Titik ODC & ODP</h4>

<!-- Search & Action Bar -->
<div class="row mb-4">
    <div class="col-md-9">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="input-group input-group-merge">
                    <span class="input-group-text border-end-0 bg-transparent" id="basic-addon-search31"><i class="bx bx-search text-primary"></i></span>
                    <input type="text" id="map-search" class="form-control border-start-0 ps-0" placeholder="Cari Nama Titik ODC atau ODP..." aria-label="Search..." aria-describedby="basic-addon-search31">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <a href="{{ route('odc-odp.create') }}" class="btn btn-primary w-100 h-100 d-flex align-items-center justify-content-center shadow">
            <i class="bx bx-plus-circle me-2 fs-4"></i> Tambah Titik Baru
        </a>
    </div>
</div>

<!-- Map GIS ODC/ODP -->
<div class="card mb-4 shadow-sm border-0 overflow-hidden">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold"><i class="bx bx-map-pin text-danger me-2"></i>Sebaran Infrastruktur Jaringan</h5>
        <div class="legend">
            <span class="badge bg-warning text-dark me-2 px-3"><i class="bx bxs-circle me-1"></i> ODC (Cabinet)</span>
            <span class="badge bg-success me-2 px-3"><i class="bx bxs-circle me-1"></i> ODP (Point)</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div id="map-odc-odp" style="height: 550px; width: 100%;"></div>
    </div>
</div>

<!-- Table List -->
<div class="card shadow-sm border-0">
  <div class="card-header bg-white py-3">
    <h5 class="mb-0 fw-bold">Daftar Data Titik Jaringan</h5>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table table-hover" id="odc-odp-table">
      <thead class="bg-light">
        <tr>
          <th class="fw-bold">Nama Titik</th>
          <th class="fw-bold">Tipe</th>
          <th class="fw-bold">Koordinat</th>
          <th class="fw-bold text-center">Foto</th>
          <th class="fw-bold text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach($data as $item)
        <tr data-nama="{{ strtolower($item->nama) }}">
          <td>
              <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-3">
                      <span class="avatar-initial rounded-circle {{ $item->tipe == 'ODC' ? 'bg-label-warning' : 'bg-label-success' }}">
                          <i class="bx {{ $item->tipe == 'ODC' ? 'bx-cabinet' : 'bx-map-alt' }}"></i>
                      </span>
                  </div>
                  <strong>{{ $item->nama }}</strong>
              </div>
          </td>
          <td>
            <span class="badge {{ $item->tipe == 'ODC' ? 'bg-warning text-dark' : 'bg-success' }}">
              {{ $item->tipe }}
            </span>
          </td>
          <td>
            <code class="text-primary small">{{ $item->latitude }}, {{ $item->longitude }}</code>
          </td>
          <td class="text-center">
            @if($item->foto)
              <img src="{{ $item->foto }}" alt="Foto" width="45" height="45" class="rounded shadow-sm cursor-pointer border" onclick="window.open(this.src)">
            @else
              <span class="badge bg-label-secondary"><i class="bx bx-image-alt"></i></span>
            @endif
          </td>
          <td class="text-center">
            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('odc-odp.edit', $item->id) }}" class="btn btn-sm btn-icon btn-outline-primary shadow-sm" title="Edit">
                  <i class="bx bx-edit-alt"></i>
                </a>
                <form action="{{ route('odc-odp.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-icon btn-outline-danger shadow-sm" title="Hapus">
                      <i class="bx bx-trash"></i>
                  </button>
                </form>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<style>
    /* Premium Pulsing Animation */
    .marker-container {
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .pulse-ring {
        position: absolute;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        animation: pulse-animation 1.5s ease-out infinite;
    }

    .pulse-ring-odc {
        background-color: rgba(255, 193, 7, 0.6);
    }

    .pulse-ring-odp {
        background-color: rgba(40, 167, 69, 0.6);
    }

    @keyframes pulse-animation {
        0% { transform: scale(0.2); opacity: 1; }
        100% { transform: scale(1.5); opacity: 0; }
    }

    .marker-icon-img {
        z-index: 10;
        width: 28px;
        height: 28px;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
    }

    .popup-card {
        width: 220px;
        font-family: 'Public Sans', sans-serif;
    }
    .popup-photo-container {
        width: 100%;
        height: 130px;
        overflow: hidden;
        border-radius: 8px;
        margin-top: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .popup-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    .popup-photo:hover {
        transform: scale(1.1);
    }
    
    .leaflet-popup-content-wrapper {
        border-radius: 12px;
        padding: 5px;
    }
    .leaflet-popup-tip-container {
        margin-top: -1px;
    }

    /* Animated Polyline (Route) */
    .route-line {
        stroke-dasharray: 10, 10;
        animation: flow 20s linear infinite;
        stroke-linejoin: round;
        stroke-linecap: round;
    }

    @keyframes flow {
        from { stroke-dashoffset: 500; }
        to { stroke-dashoffset: 0; }
    }

    /* Hide routing container */
    .leaflet-routing-container {
        display: none !important;
    }
</style>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var items = @json($data);
        var map = L.map('map-odc-odp').setView([-7.1238, 112.5926], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var markers = L.layerGroup().addTo(map);
        var routingControls = [];

        function renderMapMarkers(filter = '') {
            markers.clearLayers();
            routingControls.forEach(function(ctrl) {
                map.removeControl(ctrl);
            });
            routingControls = [];
            
            var bounds = [];

            items.forEach(function(item) {
                if (filter && !item.nama.toLowerCase().includes(filter.toLowerCase())) {
                    return;
                }

                var pulseClass = item.tipe === 'ODC' ? 'pulse-ring-odc' : 'pulse-ring-odp';
                var iconUrl = item.tipe === 'ODC' 
                    ? 'https://cdn-icons-png.flaticon.com/512/2885/2885417.png' 
                    : 'https://cdn-icons-png.flaticon.com/512/944/944455.png';
                
                var customIcon = L.divIcon({
                    className: 'marker-container',
                    html: `
                        <div class="pulse-ring ${pulseClass}"></div>
                        <img src="${iconUrl}" class="marker-icon-img">
                    `,
                    iconSize: [40, 40],
                    iconAnchor: [20, 20]
                });

                var photoHtml = item.foto ? `
                    <div class="popup-photo-container">
                        <img src="${item.foto}" class="popup-photo">
                    </div>
                ` : '';
                
                var marker = L.marker([item.latitude, item.longitude], { icon: customIcon })
                    .bindPopup(`
                        <div class="popup-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge ${item.tipe === 'ODC' ? 'bg-warning text-dark' : 'bg-success'} text-uppercase" style="font-size: 10px;">${item.tipe}</span>
                                <small class="text-muted"><i class="bx bx-coordinate"></i> ${item.latitude}</small>
                            </div>
                            <h6 class="mb-1 fw-bold text-primary">${item.nama}</h6>
                            <p class="small text-muted mb-1"><i class="bx bx-note"></i> ${item.deskripsi || 'Tidak ada deskripsi'}</p>
                            ${photoHtml}
                            <hr class="my-2">
                            <div class="d-grid">
                                <a href="/odc-odp/${item.id}/edit" class="btn btn-xs btn-primary text-white"><i class="bx bx-edit me-1"></i>Edit Data</a>
                            </div>
                        </div>
                    `);
                
                markers.addLayer(marker);
                bounds.push([item.latitude, item.longitude]);
            });

            if (bounds.length > 0) {
                map.fitBounds(bounds, {padding: [70, 70]});
            }

            // Draw Routes (Road-Following)
            items.forEach(function(item) {
                if (item.tipe === 'ODP' && item.parent) {
                    var routing = L.Routing.control({
                        waypoints: [
                            L.latLng(item.latitude, item.longitude),
                            L.latLng(item.parent.latitude, item.parent.longitude)
                        ],
                        routeWhileDragging: false,
                        addWaypoints: false,
                        draggableWaypoints: false,
                        fitSelectedRoutes: false,
                        showAlternatives: false,
                        createMarker: function() { return null; },
                        lineOptions: {
                            styles: [
                                {
                                    color: '#28a745', 
                                    opacity: 0.8, 
                                    weight: 4,
                                    className: 'route-line' // Apply our animation!
                                }
                            ]
                        }
                    }).addTo(map);
                    
                    routingControls.push(routing);
                }
            });
        }

        // Search Sync
        document.getElementById('map-search').addEventListener('input', function(e) {
            var filter = e.target.value.toLowerCase();
            renderMapMarkers(filter);

            // Filter Table
            document.querySelectorAll('#odc-odp-table tbody tr').forEach(function(tr) {
                var nama = tr.getAttribute('data-nama');
                if (nama.includes(filter)) {
                    tr.style.display = '';
                } else {
                    tr.style.display = 'none';
                }
            });
        });

        // Click map to add
        map.on('click', function(e) {
            var lat = e.latlng.lat.toFixed(8);
            var lng = e.latlng.lng.toFixed(8);
            L.popup()
                .setLatLng(e.latlng)
                .setContent(`
                    <div class="text-center p-2" style="width:180px">
                        <p class="mb-2 fw-bold">Tambah Titik Baru?</p>
                        <code class="d-block mb-3 small text-primary">${lat}, ${lng}</code>
                        <a href="/odc-odp/create?lat=${lat}&lng=${lng}" class="btn btn-sm btn-primary w-100"><i class="bx bx-plus me-1"></i>Tambah Disini</a>
                    </div>
                `)
                .openOn(map);
        });

        renderMapMarkers();
    });
</script>
@endsection
