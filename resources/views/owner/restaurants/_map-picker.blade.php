@php
    $initLat = old('lat', $restaurant->lat ?? 30.7333);
    $initLng = old('lng', $restaurant->lng ?? 76.7794);
    $hasCoords = old('lat', $restaurant->lat ?? null) !== null;
@endphp

<div class="mb-3">
    <label class="form-label fw-500">Pin Location on Map</label>

    <input type="text" id="mapSearchBox" class="form-control mb-2"
        placeholder="Search for your restaurant's location...">
    <div id="locationMap" style="height:280px;border-radius:12px;overflow:hidden;border:1px solid #ddd"></div>
    <small class="text-muted">Search above, or drag the pin to fine-tune the exact spot. This sets your restaurant's map location for customers and delivery agents.</small>

    <input type="hidden" name="lat" id="latInput" value="{{ $hasCoords ? $initLat : '' }}">
    <input type="hidden" name="lng" id="lngInput" value="{{ $hasCoords ? $initLng : '' }}">
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function() {
    const defaultPos = [{{ $initLat }}, {{ $initLng }}];
    const hasCoords = {{ $hasCoords ? 'true' : 'false' }};

    const map = L.map('locationMap').setView(defaultPos, hasCoords ? 16 : 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);

    const marker = L.marker(defaultPos, { draggable: true }).addTo(map);

    function setCoords(lat, lng) {
        document.getElementById('latInput').value = lat;
        document.getElementById('lngInput').value = lng;
    }

    if (hasCoords) setCoords(defaultPos[0], defaultPos[1]);

    marker.on('dragend', function() {
        const pos = marker.getLatLng();
        setCoords(pos.lat, pos.lng);
    });

    // Free OpenStreetMap search (Nominatim) — no API key needed
    const searchInput = document.getElementById('mapSearchBox');
    let debounceTimer;
    let resultsBox;

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
                .then(results => showResults(results))
                .catch(() => {});
        }, 400);
    });

    function showResults(results) {
        if (resultsBox) resultsBox.remove();
        if (!results.length) return;

        resultsBox = document.createElement('div');
        resultsBox.style.cssText = 'position:relative;background:white;border:1px solid #ddd;border-radius:8px;margin-top:-6px;margin-bottom:6px;max-height:220px;overflow-y:auto;box-shadow:0 8px 20px rgba(0,0,0,0.1);z-index:1000;';

        results.forEach(r => {
            const item = document.createElement('div');
            item.textContent = r.display_name;
            item.style.cssText = 'padding:8px 12px;font-size:13px;cursor:pointer;border-bottom:1px solid #f0f0f0;';
            item.addEventListener('mouseenter', () => item.style.background = '#fff5f5');
            item.addEventListener('mouseleave', () => item.style.background = 'white');
            item.addEventListener('click', function() {
                const lat = parseFloat(r.lat), lng = parseFloat(r.lon);
                map.setView([lat, lng], 16);
                marker.setLatLng([lat, lng]);
                setCoords(lat, lng);
                searchInput.value = r.display_name;
                resultsBox.remove();
                resultsBox = null;

                const addressField = document.querySelector('textarea[name="address"]');
                if (addressField && !addressField.value.trim()) {
                    addressField.value = r.display_name;
                }

                const pincodeField = document.querySelector('input[name="pincode"]');
                const match = r.display_name.match(/\b\d{6}\b/);
                if (pincodeField && match && !pincodeField.value.trim()) {
                    pincodeField.value = match[0];
                }
            });
            resultsBox.appendChild(item);
        });

        searchInput.parentNode.insertBefore(resultsBox, searchInput.nextSibling);
    }
})();
</script>
@endpush
