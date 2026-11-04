# Zomato Clone — Fixes (2026-09-23)

Ye zip sirf un files ka hai jo change/add hui hain — kuch bhi purana delete/overwrite
nahi hoga jo already sahi chal raha hai. Bas ye files apke project ke upar copy
(overwrite) karni hain, same folder structure follow karti hain.

## Kya-kya fix hua

### 1. Payment na hone par bhi "Order" dikhna (asli bug)
Pehle Stripe/Razorpay chunne par order turant `orders` table me ban jata tha —
payment se pehle hi. Agar customer payment cancel/abandon kar de, phir bhi wo
order "My Orders" me dikhta rehta tha, coupon consume ho chuka hota, loyalty
points kat chuke hote, aur wallet se paisa bhi kat chuka hota — sab bina
payment complete hue.

**Fix:** Ab Stripe/Razorpay checkout ek naye `pending_checkouts` table me
"draft" ke roop me store hota hai. Order sirf tab banta hai jab gateway
(Stripe/Razorpay) confirm kare ki payment ho gaya. Tab tak cart, coupon,
points, wallet — kuch bhi touch nahi hota. Agar payment cancel/fail ho jaye,
kuch bhi trace nahi bachta — cart wapas bilkul waisa hi rehta hai jaisa
customer ne chhoda tha.

Cash on Delivery (COD) waise hi kaam karega jaise pehle karta tha — order
turant place hota hai, kyunki wahan payment gateway involve hi nahi hai.

Naye files: `app/Models/PendingCheckout.php`, `app/Services/CheckoutFinalizer.php`,
migration `create_pending_checkouts_table.php`.
Update hui files: `OrderController.php`, `PaymentController.php`,
`RazorpayController.php`, `razorpay-checkout.blade.php`, `order-detail.blade.php`,
`routes/web.php`.

### 2. Coupon apply/remove issue
Expired ya invalid coupon session me atka reh jata tha (checkout page reload
hone par bhi clear nahi hota tha), isliye "Remove" button kabhi dikhta hi
nahi tha aur customer confuse ho jata tha. Ab jaise hi koi coupon invalid
(expired/inactive/wrong restaurant) nikalta hai, wo turant apne aap session
se clear ho jata hai — sirf "minimum order amount" wala case chhoda hai
jaan-bujh kar, taaki ek item aur add karne par wahi coupon apply ho sake.

Update hui file: `app/Services/CouponService.php`, `CouponController.php`.

### 3. Pincode match fail hone ka issue
Agar pincode ke aage/peeche koi space aa jaye (copy-paste karte waqt aksar
hota hai), to system usko match nahi karta tha aur ghalat "we don't deliver
here" error dikhata tha. Ab har jagah pincode save karte waqt aur compare
karte waqt trim ho raha hai — Customer address, restaurant ka apna pincode,
aur owner ke delivery zones — sabme.

Update hui files: `AddressController.php`, `Restaurant.php`,
`OrderController.php`, `DeliveryZoneController.php` (Owner),
`RestaurantController.php` (Owner & Admin dono).

### 4. Address "Area" field chhota tha
Checkout ke "Add New Address" form me "Street, Area, Landmark" field ek
single-line chhota input tha. Ab wo 3-line ka textarea hai taaki log poora
address aaram se likh sakein.

Update hui file: `resources/views/customer/checkout.blade.php`.

## Install kaise karein

1. Ye zip apne project folder me extract kar dijiye (usi jagah jaha
   `app`, `routes`, `resources` folders hain) — overwrite allow karna hai jab
   pucha jaye.
2. Phir project root me ye commands chalayein:

   ```
   php artisan migrate
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

Bas itna hi — `php artisan migrate` naya `pending_checkouts` table bana dega,
baaki 3 commands sirf cache clear karte hain taaki naye routes/views turant
reflect ho jayein. Koi data delete nahi hota, koi existing table nahi
chhedi gayi.
