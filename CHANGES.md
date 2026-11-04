# Zomato Clone — New Features Patch

Is zip me sirf wahi files hain jo **naya banayi** ya **modify ki gayi** hain 4 features ke liye:
Favorites/Wishlist, Coupons/Promo Codes, Food Add-ons/Variants, Reorder + Notifications.

Har file ka path zip ke andar EXACTLY wahi hai jahan wo tumhare Laravel project me jaani chahiye.
Bas `zomato-clone/app/...` jaisa base folder hata ke apne project root me is folder structure ke
hisaab se copy-paste kar do (ya overwrite kar do agar file already exist karti hai).

## 🆕 NAYI FILES (as-is copy karo, project me exist nahi karti)

```
database/migrations/2026_07_22_000001_create_favorites_table.php
database/migrations/2026_07_22_000002_create_coupons_table.php
database/migrations/2026_07_22_000003_add_coupon_fields_to_orders_table.php
database/migrations/2026_07_22_000004_create_notifications_table.php

app/Models/Favorite.php
app/Models/Coupon.php

app/Services/CouponService.php
app/Notifications/OrderStatusUpdated.php

app/Http/Controllers/Customer/FavoriteController.php
app/Http/Controllers/Customer/CouponController.php
app/Http/Controllers/Customer/NotificationController.php
app/Http/Controllers/Admin/CouponController.php
app/Http/Controllers/Owner/FoodItemOptionController.php

resources/views/customer/favorites.blade.php
resources/views/customer/notifications.blade.php
resources/views/admin/coupons/index.blade.php
resources/views/admin/coupons/create.blade.php
resources/views/admin/coupons/edit.blade.php
resources/views/admin/coupons/_form.blade.php
```

## ✏️ MODIFY HUI FILES (existing file ko is content se REPLACE karo)

| File | Kya badla |
|---|---|
| `app/Models/FoodItemOption.php` | fillable + relation add (pehle khaali tha) |
| `app/Models/User.php` | `favorites()`, `favoriteRestaurants()` relation add |
| `app/Models/Restaurant.php` | `favorites()`, `favoritedByUsers()`, `coupons()` relation add |
| `app/Models/Order.php` | `$fillable` me `coupon_code`, `discount` add |
| `app/Services/CartService.php` | poora rewrite — add-on options support ke liye |
| `app/Http/Controllers/Customer/CartController.php` | options support + cart_key logic |
| `app/Http/Controllers/Customer/OrderController.php` | coupon-aware checkout/place + naya `reorder()` |
| `app/Http/Controllers/Customer/HomeController.php` | options eager load + `isFavorited` var |
| `app/Http/Controllers/Owner/OrderController.php` | status update pe notification fire |
| `app/Http/Controllers/DeliveryAgent/OrderController.php` | accept/deliver pe notification fire |
| `routes/web.php` | naye routes add (poori file replace kar sakte ho) |
| `resources/views/customer/layouts/app.blade.php` | navbar bell + favorites link |
| `resources/views/customer/dashboard.blade.php` | heart icon on cards |
| `resources/views/customer/restaurant.blade.php` | heart button + options modal |
| `resources/views/customer/cart.blade.php` | coupon box + discount row |
| `resources/views/customer/checkout.blade.php` | coupon box + discount row |
| `resources/views/customer/orders.blade.php` | reorder button |
| `resources/views/customer/order-detail.blade.php` | reorder button + options/discount |
| `resources/views/owner/food-items/edit.blade.php` | add-ons management section |
| `resources/views/admin/layouts/app.blade.php` | "Coupons" sidebar link |

## Deploy ke baad karna hai

```bash
php artisan migrate
```

Agar `routes/web.php` khud already bahut customize kar rakha hai, toh poori file replace mat karo —
isme naye `use` imports (top of file) aur naye `Route::` blocks (favorites/coupon/notifications/
reorder/options) hain jo tum manually apne existing file me merge kar sakte ho.
