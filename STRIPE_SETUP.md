# Stripe Payment Gateway — Setup Instructions

## 1. Apply the patch files
Extract this zip into your project root (overwrites the files below):

```
database/migrations/2026_07_23_000001_add_payment_method_to_orders_table.php   [NEW]
app/Models/Order.php                                                            [MODIFIED]
app/Http/Controllers/Customer/PaymentController.php                             [NEW]
app/Http/Controllers/Customer/OrderController.php                               [MODIFIED]
config/services.php                                                             [MODIFIED]
bootstrap/app.php                                                                [MODIFIED]
routes/web.php                                                                   [MODIFIED]
resources/views/customer/checkout.blade.php                                     [MODIFIED]
resources/views/customer/order-detail.blade.php                                 [MODIFIED]
```

No new composer package needed — Guzzle (`guzzlehttp/guzzle`) is already bundled with
Laravel, so we call Stripe's REST API directly via Laravel's `Http` facade instead of
installing the `stripe/stripe-php` SDK.

## 2. Get your Stripe test keys
1. Sign up / log in at https://dashboard.stripe.com
2. Make sure you're in **Test mode** (toggle top-right)
3. Go to **Developers → API keys**
4. Copy the **Publishable key** (`pk_test_...`) and **Secret key** (`sk_test_...`)

## 3. Add keys to your `.env` file

```env
STRIPE_KEY=pk_test_xxxxxxxxxxxxx
STRIPE_SECRET=sk_test_xxxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx
```

(Webhook secret — see step 5 below. You can leave it blank for now and add it later;
webhook is a backup confirmation, the success-redirect flow works without it too.)

## 4. Run the migration

```powershell
php artisan migrate
php artisan config:clear
```

## 5. (Recommended) Set up the webhook for reliable payment confirmation
The success-page redirect confirms payment for most cases, but if a customer closes
the tab right after paying, the webhook is what marks the order as paid.

**For local testing (XAMPP/localhost), use the Stripe CLI:**
1. Download from https://github.com/stripe/stripe-cli/releases/latest (Windows zip)
2. In a terminal:
   ```powershell
   stripe login
   stripe listen --forward-to 127.0.0.1:8000/stripe/webhook
   ```
3. It will print a `whsec_...` value — put that in `STRIPE_WEBHOOK_SECRET` in `.env`

**For production**, add the webhook in the Stripe Dashboard instead:
Developers → Webhooks → Add endpoint → `https://yourdomain.com/stripe/webhook`
→ select event `checkout.session.completed` → copy the signing secret into `.env`.

## 6. Test it
1. `php artisan serve`
2. Add items to cart → Checkout → select **"Pay Online (Card)"** → Place Order
3. You'll be redirected to Stripe's hosted checkout page
4. Use Stripe's test card: `4242 4242 4242 4242`, any future expiry, any CVC, any ZIP
5. After payment, you land back on the order detail page — payment status should show **"Paid Online"**

## What was built
- **Checkout page**: customer chooses Cash on Delivery or Pay Online (Card)
- **`PaymentController@checkout`**: creates a Stripe Checkout Session for the order total and redirects to Stripe's hosted page
- **`PaymentController@success`**: verifies the session server-side (never trusts the redirect alone) and marks the order paid
- **`PaymentController@cancel`**: handles abandoned/cancelled payments, lets the customer retry
- **`PaymentController@webhook`**: Stripe calls this directly (signature-verified manually via HMAC) as the source of truth for payment confirmation — works even if the browser redirect never fires
- **Order model**: new `payment_method` column (`cod` / `stripe`)
- **Order detail page**: payment status badge + "Retry Payment" button if a Stripe payment is still pending
