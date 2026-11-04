@extends('owner.layouts.app')
@section('title', 'Delivery Zones')
@section('page-title', 'Delivery Zones — ' . $restaurant->name)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-geo-alt me-2 text-danger"></i>{{ $restaurant->name }} — Delivery Zones</span>
        <a href="{{ route('owner.restaurants.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
    <div class="card-body p-4">
        <p class="text-muted" style="font-size:13px">
            Set a delivery fee for each pincode you deliver to. Customers outside these pincodes
            will see a default (₹0) delivery fee unless you add their pincode here.
        </p>

        <form action="{{ route('owner.restaurants.delivery-zones.store', $restaurant) }}" method="POST" class="row g-2 mb-3" id="zoneForm">
            @csrf
            <div class="col-md-12 mb-1 position-relative">
                <input type="text" id="zoneSearchBox" class="form-control" placeholder="Search an area/locality to auto-fill pincode & location (optional)">
            </div>
            <div class="col-md-3">
                <input type="text" name="pincode" id="zonePincode" class="form-control" placeholder="Pincode e.g. 160055" required maxlength="10">
            </div>
            <div class="col-md-3">
                <select name="zone_type" id="zoneType" class="form-select" required>
                    <option value="metro">🟢 Metro — fast delivery</option>
                    <option value="remote">🟡 Remote — slower delivery</option>
                    <option value="blocked">🔴 Blocked — no delivery</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" name="delivery_fee" id="zoneFee" step="0.01" min="0" class="form-control" placeholder="Delivery Fee (₹)" value="40">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-danger w-100">
                    <i class="bi bi-plus-lg me-1"></i>Add Zone
                </button>
            </div>
            <div class="col-md-6">
                <input type="text" name="estimated_time" id="zoneTime" class="form-control form-control-sm" placeholder="Estimated time e.g. 30 min" value="30 min">
            </div>
            <input type="hidden" name="lat" id="zoneLat">
            <input type="hidden" name="lng" id="zoneLng">
        </form>

        <script>
        document.getElementById('zoneType')?.addEventListener('change', function() {
            const fee = document.getElementById('zoneFee');
            const time = document.getElementById('zoneTime');
            if (this.value === 'metro') { fee.value = 40; time.value = '30 min'; fee.disabled = false; time.disabled = false; }
            else if (this.value === 'remote') { fee.value = 60; time.value = '60 min'; fee.disabled = false; time.disabled = false; }
            else { fee.value = ''; time.value = ''; fee.disabled = true; time.disabled = true; }
        });
        </script>

        <div id="zonesMap" style="height:340px;border-radius:12px;overflow:hidden;border:1px solid #ddd" class="mb-4"></div>

        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Pincode</th>
                    <th>Type</th>
                    <th>Delivery Fee</th>
                    <th>Est. Time</th>
                    <th>On Map</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($zones as $zone)
                @php
                    $typeStyle = match($zone->zone_type ?? 'metro') {
                        'metro' => ['background:#e8f5e9;color:#2e7d32', '🟢 Metro'],
                        'remote' => ['background:#fff8e1;color:#f57f17', '🟡 Remote'],
                        'blocked' => ['background:#fce4ec;color:#c62828', '🔴 Blocked'],
                        default => ['background:#f5f5f5;color:#666', ucfirst($zone->zone_type ?? '')],
                    };
                @endphp
                <tr>
                    <td class="ps-4">{{ $zone->pincode }}</td>
                    <td><span class="badge" style="{{ $typeStyle[0] }};font-weight:600">{{ $typeStyle[1] }}</span></td>
                    <td>{{ $zone->zone_type === 'blocked' ? '—' : '₹' . number_format($zone->delivery_fee, 2) }}</td>
                    <td>{{ $zone->estimated_time ?? '—' }}</td>
                    <td>
                        @if($zone->lat && $zone->lng)
                            <span class="badge bg-success-subtle text-success">Located</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">Pincode only</span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('owner.restaurants.delivery-zones.destroy', [$restaurant, $zone]) }}"
                            method="POST" onsubmit="return confirm('Remove this delivery zone?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No delivery zones set yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function() {
    const restaurantPos = [{{ $restaurant->lat ?? 30.7333 }}, {{ $restaurant->lng ?? 76.7794 }}];

    const map = L.map('zonesMap').setView(restaurantPos, 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);

    // Restaurant marker — red circle
    L.circleMarker(restaurantPos, {
        radius: 9, color: '#fff', weight: 2, fillColor: '#e8342a', fillOpacity: 1,
    }).addTo(map).bindPopup({{ Js::from($restaurant->name) }});

    const zones = @json($zones->filter(fn($z) => $z->lat && $z->lng)->values());
    const bounds = L.latLngBounds([restaurantPos]);

    zones.forEach(zone => {
        const pos = [parseFloat(zone.lat), parseFloat(zone.lng)];
        bounds.extend(pos);
        L.marker(pos).addTo(map)
            .bindPopup(`<strong>${zone.pincode}</strong><br>Delivery Fee: ₹${Number(zone.delivery_fee).toFixed(2)}`);
    });

    if (zones.length > 0) map.fitBounds(bounds.pad(0.2));

    // Free OpenStreetMap search (Nominatim) for the "add zone" form
    const searchInput = document.getElementById('zoneSearchBox');
    let debounceTimer, resultsBox;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const q = this.value.trim();
        if (resultsBox) { resultsBox.remove(); resultsBox = null; }
        if (q.length < 3) return;

        debounceTimer = setTimeout(() => {
            fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&countrycodes=in&q=${encodeURIComponent(q)}`, {
                headers: { 'Accept-Language': 'en' }
            })
                .then(res => res.json())
                .then(showResults)
                .catch(() => {});
        }, 400);
    });

    function showResults(results) {
        if (resultsBox) resultsBox.remove();
        if (!results.length) return;

        resultsBox = document.createElement('div');
        resultsBox.style.cssText = 'position:absolute;left:15px;right:15px;background:white;border:1px solid #ddd;border-radius:8px;max-height:220px;overflow-y:auto;box-shadow:0 8px 20px rgba(0,0,0,0.15);z-index:1000;';

        results.forEach(r => {
            const item = document.createElement('div');
            item.textContent = r.display_name;
            item.style.cssText = 'padding:8px 12px;font-size:13px;cursor:pointer;border-bottom:1px solid #f0f0f0;';
            item.addEventListener('mouseenter', () => item.style.background = '#fff5f5');
            item.addEventListener('mouseleave', () => item.style.background = 'white');
            item.addEventListener('click', function() {
                const lat = parseFloat(r.lat), lng = parseFloat(r.lon);
                document.getElementById('zoneLat').value = lat;
                document.getElementById('zoneLng').value = lng;

                const match = r.display_name.match(/\b\d{6}\b/);
                if (match) document.getElementById('zonePincode').value = match[0];

                searchInput.value = r.display_name;
                resultsBox.remove();
                resultsBox = null;

                map.panTo([lat, lng]);
                L.marker([lat, lng]).addTo(map);
            });
            resultsBox.appendChild(item);
        });

        searchInput.parentNode.appendChild(resultsBox);
    }
})();
</script>
@endpush
