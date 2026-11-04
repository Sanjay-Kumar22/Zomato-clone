# Razorpay Payment Gateway — Setup

Stripe ke exact pattern par Razorpay bhi add kar diya hai — customer ko ab checkout par 3 payment
options milte hain: **Cash on Delivery**, **Stripe (Card)**, aur **Razorpay (UPI / Card / Wallet)**.

Extract this zip into your project root (overwrites the files below).

## 🆕 NAYI FILES
```
app/Http/Controllers/Customer/RazorpayController.php
resources/views/customer/razorpay-checkout.blade.php
```

## ✏️ MODIFY HUI FILES
| File | Kya badla |
|---|---|
| `config/services.php` | `razorpay` key/secret/webhook_secret config add |
| `bootstrap/app.php` | `razorpay/webhook` CSRF-exempt list me add (Razorpay seedha yeh URL hit karta hai, browser session nahi hota) |
| `routes/web.php` | naye routes: `razorpay.webhook`, `customer.payment.razorpay.checkout`, `.verify`, `.cancel` |
| `app/Http/Controllers/Customer/OrderController.php` | `payment_method` validation me `razorpay` allow, aur order place hone par razorpay checkout page pe redirect |
| `resources/views/customer/checkout.blade.php` | naya "Pay Online (UPI / Card / Wallet)" radio option |
| `resources/views/customer/order-detail.blade.php` | payment badge + "Retry Payment" button ab razorpay ko bhi handle karta hai |
| `resources/views/admin/orders/index.blade.php`, `admin/orders/show.blade.php`, `owner/orders.blade.php`, `delivery/history.blade.php`, `emails/order-confirmation.blade.php` | "Paid Online" badge ab stripe + razorpay dono ke liye dikhta hai |

## 1. Razorpay test keys lo
1. https://dashboard.razorpay.com par sign up/login karo
2. **Test Mode** on rakho (top-right toggle)
3. **Settings → API Keys** → **Generate Test Key**
4. `Key Id` (`rzp_test_...`) aur `Key Secret` copy karo

## 2. `.env` me add karo
```env
RAZORPAY_KEY=rzp_test_xxxxxxxxxxxx
RAZORPAY_SECRET=your_test_secret_here
```

Phir:
```bash
php artisan config:clear
```

## 3. (Optional but recommended) Webhook set karo
Real production me payment confirm karne ka sabse reliable tarika webhook hai (browser band
ho jaaye tab bhi order paid mark ho jaata hai). Local testing ke liye webhook zaroori nahi —
`verify()` method already order ko paid mark kar deta hai jab customer payment complete karta
hai.

Agar chaho to Razorpay Dashboard → **Settings → Webhooks** → naya webhook add karo:
- URL: `https://tumhara-domain.com/razorpay/webhook`
- Active event: `payment.captured`
- Secret generate karo aur `.env` me daalo:
```env
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret_here
```

## Kaise kaam karta hai
1. Customer checkout par "Pay Online (UPI / Card / Wallet)" select karke order place karta hai
2. Server-side Razorpay Order banta hai, customer ek chhota "Pay Now" page par jaata hai jahan
   Razorpay ka official Checkout.js popup **automatically khul jaata hai**
3. Payment complete hone par Razorpay ek signed response deta hai — server us signature ko
   `HMAC SHA256` se verify karta hai (Stripe wale pattern jaisa hi) taaki koi fake "success"
   na bhej sake
4. Verify ho jaane par order `paid` mark hota hai, restaurant owner ko notification milti hai,
   customer ko confirmation email jaata hai
5. Agar customer popup band kar de bina pay kiye, to order pending reh jaata hai aur order-detail
   page par "Retry Payment" button dikhta hai

## Note
Koi naya composer package nahi chahiye — Stripe ki tarah Razorpay ke liye bhi seedhe unka
REST API (`Http` facade se) aur `checkout.js` (CDN script) use kiya hai.
