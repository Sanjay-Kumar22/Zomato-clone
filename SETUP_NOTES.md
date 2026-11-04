# Cuisines/Tags + Rating Breakdown + Delivery Zones — Setup

## 1. Apply patch
Extract into your project root (overwrites existing files, adds new ones).

## 2. Run migrations

```powershell
php artisan migrate
php artisan view:clear
```

## What was added

### Restaurant Cuisines / Tags
- `restaurants.cuisines` — comma-separated text column (e.g. "North Indian, Chinese")
- Owner can set it on the Add/Edit Restaurant form
- Shown as tags on restaurant cards, restaurant detail page
- New filter dropdown on the customer dashboard ("🍴 All Cuisines")

### Rating Breakdown
- `reviews.food_rating` and `reviews.delivery_rating` — separate 1-5 star ratings
- Review form (on order detail page) now has 3 star pickers: Overall, Food Quality, Delivery Experience
- Restaurant page shows average Food Quality and Delivery Experience scores above the reviews list
- Existing reviews keep working — food_rating/delivery_rating are nullable, so old reviews just show blank for the breakdown

### Restaurant-wise Delivery Zones (Owner UI)
- New page: Owner Dashboard → My Restaurants → **"Delivery Zones"** button on each restaurant
- Owner adds a pincode + delivery fee (e.g. 160055 → ₹40)
- This is the UI for the delivery-fee logic that was already wired into the cart/checkout pages — until zones are added for a restaurant, delivery fee defaults to ₹0

## Note
No new composer packages needed. Everything uses your existing setup.
