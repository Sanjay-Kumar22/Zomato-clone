# Google Maps Integration — Setup

## 1. Apply the patch
Extract into your project root, overwriting existing files.

```powershell
php artisan migrate
php artisan view:clear
```

## 2. Get a free Google Maps API key
This is required — I can't generate one for you, it's tied to your own Google account
and billing (Google gives a generous free monthly quota that's more than enough for
a project like this).

1. Go to https://console.cloud.google.com/google/maps-apis/start
2. Create a project (or pick an existing one)
3. Enable these 2 APIs: **Maps JavaScript API** and **Places API**
4. Go to **APIs & Services → Credentials** → **Create Credentials → API Key**
5. Copy the key
6. (Recommended) Click the key → under "Application restrictions" choose
   **HTTP referrers** → add `127.0.0.1:8000/*` and `localhost:8000/*` for local dev
   (add your real domain later when you deploy)

## 3. Add it to your `.env`

```env
GOOGLE_MAPS_API_KEY=your_key_here
```

```powershell
php artisan config:clear
```

## What was added

### Restaurant location picker (Owner → Add/Edit Restaurant)
- Search box (Google Places Autocomplete) — type your restaurant's area and pick it
- Interactive map with a draggable pin to fine-tune the exact spot
- Auto-fills the address and pincode fields when you pick a place
- Saves `lat`/`lng` on the restaurant — used for the map link customers see, and
  as the map center in Delivery Zones

### Delivery Zones map (Owner → Delivery Zones)
- Search box to quickly add a zone by area name (auto-fills pincode + location)
- Full map showing your restaurant (red marker) and every delivery zone pincode
  you've added (click a pin to see its delivery fee)
- Zones added without using the search box still work — they just won't show a
  location marker on the map (table shows "Pincode only" vs "Located")

### Customer restaurant page
- "View on Map" link next to the address (opens Google Maps directions/location in
  a new tab) — only shows once the restaurant has a pinned location

## If the key isn't set yet
Everything still works — the map picker areas just show a small notice instead of
the map, and you can type the pincode/address manually like before. Nothing breaks.
