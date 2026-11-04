# Fix Category Icons + Restaurant/Food Images

## 1. Apply the patch (code fix)
Extract into project root, overwriting existing files.

```powershell
composer dump-autoload
php artisan view:clear
```

## 2. Re-seed categories (fixes "pizza.svg Pizza" text bug)
The old seeder stored filenames like "pizza.svg" as plain text, and the page was printing
that text directly instead of using it as an image. Fixed version uses emoji instead —
no image files needed, renders everywhere instantly.

```powershell
php artisan db:seed --class=CategorySeeder
```

## 3. Fix your existing "Saffron" restaurant + "Butter Chicken" item images
Your restaurant's `image` column currently has a broken/wrong value (looks like a
screenshot got saved instead of a food photo). Easiest fix: run this SQL in phpMyAdmin
(zomato_clone database → SQL tab) to point them at real, free-to-use food photos:

```sql
UPDATE restaurants
SET image = 'https://images.unsplash.com/photo-1742281257707-0c7f7e5ca9c6?fm=jpg&q=80&w=1200&auto=format&fit=crop'
WHERE id = 1;

UPDATE food_items
SET image = 'https://images.unsplash.com/photo-1742599361498-79824d24e355?fm=jpg&q=80&w=800&auto=format&fit=crop'
WHERE name = 'Butter Chiken';
```

(Both images are free-to-use under the Unsplash License — no attribution required.)

## Why this works now
Added an `image_url` accessor on the `Restaurant` and `FoodItem` models that
intelligently handles BOTH cases:
- A full external URL (like the ones above) → used directly
- A local upload path (from the owner panel's file upload) → still resolves via
  `asset('storage/...')` like before

So going forward, restaurant owners can either **upload a photo** through the panel
(existing flow, unchanged) **or** you can paste a direct image URL straight into the
database for quick testing — both now display correctly.

## Going forward — real restaurant photo uploads
For any *new* restaurants/dishes added through the owner panel, just make sure:
```powershell
php artisan storage:link
```
has been run once (creates the `public/storage` symlink Laravel needs to serve
uploaded files). If this was never run, uploaded images will 404 even though the
upload itself succeeds.
