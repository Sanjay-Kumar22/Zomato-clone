# Analytics/Revenue Reports + Live Tracking Map + Address Pin-Save Bugfix — Setup

Is zip me sirf wahi files hain jo **naya banayi** ya **modify ki gayi** hain teen cheezon ke liye:
1. Owner + Admin Analytics/Revenue Reports (pehle bilkul missing tha)
2. Live Tracking Map on order-detail page (pehle sirf step-based tracker tha, real map nahi tha)
3. Bugfix: checkout ke map se pin ki hui address location (lat/lng) save hi nahi ho rahi thi

Har file ka path zip ke andar EXACTLY wahi hai jahan wo tumhare project me jaani chahiye.
`zomato-clone/app/...` jaisa base folder hata ke apne project root me isi structure ke
hisaab se overwrite/copy kar do.

## 🆕 NAYI FILES (as-is copy karo)
```
database/migrations/2026_08_05_000002_add_agent_location_to_orders_table.php
app/Http/Controllers/Owner/AnalyticsController.php
app/Http/Controllers/Admin/AnalyticsController.php
resources/views/owner/analytics.blade.php
resources/views/admin/analytics.blade.php
```

## ✏️ MODIFY HUI FILES (existing file ko is content se REPLACE karo)
| File | Kya badla |
|---|---|
| `app/Models/Address.php` | `lat`, `lng` fillable + cast add (pehle checkout se aayi hui pin location save hi nahi hoti thi) |
| `app/Http/Controllers/Customer/AddressController.php` | `store()` ab `lat`/`lng` validate + save karta hai |
| `app/Models/Order.php` | `agent_lat`, `agent_lng`, `agent_location_updated_at` fillable + cast add |
| `app/Http/Controllers/Customer/OrderController.php` | `status()` JSON endpoint ab agent ki live location bhi return karta hai |
| `app/Http/Controllers/DeliveryAgent/OrderController.php` | naya `updateLocation()` — agent ke phone se GPS receive karke order me save karta hai |
| `routes/web.php` | naye routes: `owner.analytics`, `admin.analytics`, `delivery.orders.location` (poori file replace kar sakte ho — pehle se customize kar rakha ho to sirf naye `use` imports aur naye `Route::` lines manually merge kar lena) |
| `resources/views/owner/layouts/app.blade.php` | sidebar me "Analytics" link add |
| `resources/views/admin/layouts/app.blade.php` | sidebar me "Analytics" link add |
| `resources/views/customer/order-detail.blade.php` | active orders (`confirmed`/`preparing`/`out_for_delivery`) ke liye Leaflet-based live map: restaurant 🍽️, delivery address 📍, rider 🛵 — rider marker agent ki real GPS location se 8-second polling par update hota hai |
| `resources/views/delivery/dashboard.blade.php` | active order hone par background me `navigator.geolocation.watchPosition` se location bhejta rehta hai (koi extra UI nahi, silent) |
| `resources/views/delivery/layouts/app.blade.php` | `<meta name="csrf-token">` add kiya (GPS POST request ke liye zaroori tha, missing tha) |

## Deploy ke baad karna hai
```bash
php artisan migrate
php artisan view:clear
```

## Kaise kaam karta hai (Live Tracking Map)
- Delivery agent apne phone/browser me dashboard khula rakhega (jab order "out for delivery"
  ho), tab browser GPS permission maangega — allow karne par uski location har ~10-15s me
  automatically bheji jaati hai.
- Customer ke order-detail page par map restaurant + delivery address dikhata hai; jaise hi
  agent ki location aani shuru hoti hai, 🛵 marker uska real position dikhata hai aur har 8s
  me refresh hota hai.
- Agar agent ne location permission deny kar di ya abhi tak nahi bheji, tab map "Waiting for
  rider GPS…" dikhata hai (restaurant par marker rehta hai) — koi crash/error nahi aata.
- Yeh **real device GPS** hai, koi fake/simulated data nahi — lekin isko test karne ke liye
  delivery agent ka dashboard ek actual phone/browser me GPS-permission ke saath khula hona
  chahiye jab order out_for_delivery ho.

## Kaise kaam karta hai (Analytics)
- Owner → sidebar → **Analytics**: revenue trend chart (Chart.js), best-selling items,
  restaurant-wise revenue, order status doughnut chart. 7/30/90-day filter.
- Admin → sidebar → **Analytics**: platform-wide revenue trend, top restaurants by revenue,
  top-selling items platform-wide.
- Sirf `delivered` orders revenue/best-seller me count hote hain (jaisa real revenue reports
  me hota hai).

## Note
- Koi naya composer package nahi chahiye. Chart.js aur Leaflet dono CDN se load ho rahe hain
  (jaise tumhare project me pehle se Leaflet checkout page par use ho raha hai).
- Razorpay abhi bhi implement nahi hai — sirf Stripe hai. Agar chahiye to bata dena, alag
  patch bana dunga (Razorpay ke liye tumhare apne test API keys chahiye honge `.env` me).
