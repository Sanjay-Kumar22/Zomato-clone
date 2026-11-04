<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Notifications\NewOrderPlaced;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class PaymentController extends Controller
{
    protected string $apiBase = 'https://api.stripe.com/v1';

    /**
     * Create a Stripe Checkout Session for the given order and
     * redirect the customer to Stripe's hosted payment page.
     */
    public function checkout(Order $order)
    {
        if (!$this->canPayOrder($order)) {
            return redirect()->route('customer.orders.show', $order)
                ->with('error', 'This payment session is no longer valid for your account. Please start payment again from the order.');
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('customer.orders.show', $order)
                ->with('success', 'This order is already paid.');
        }

        $response = Http::asForm()
            ->withToken(config('services.stripe.secret'))
            // Local Windows/antivirus setups often intercept HTTPS with a
            // self-signed cert ("cURL error 60") — skip verification only in
            // local dev so this never ships live. See services.stripe.secret.
            ->withOptions(['verify' => !app()->environment('local')])
            ->post("{$this->apiBase}/checkout/sessions", [
                'mode'                 => 'payment',
                'payment_method_types' => ['card'],
                'customer_email'       => auth()->user()->email,
                'client_reference_id'  => $order->id,
                'line_items'           => [[
                    'quantity'   => 1,
                    'price_data' => [
                        'currency'     => 'inr',
                        'unit_amount'  => (int) round($order->total * 100), // paise
                        'product_data' => [
                            'name' => 'Order #' . $order->id . ' — ' . ($order->restaurant->name ?? 'Zomato Clone'),
                        ],
                    ],
                ]],
                'success_url' => route('customer.payment.success', $order) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('customer.payment.cancel', $order),
            ]);

        if ($response->failed()) {
            Log::error('Stripe checkout session creation failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            return redirect()->route('customer.orders.show', $order)
                ->with('error', 'Stripe payment could not be started. Please verify the Stripe test keys/configuration and try again.');
        }

        $session = $response->json();

        $order->update(['payment_id' => $session['id']]);

        return redirect()->away($session['url']);
    }

    /**
     * Stripe redirects here after a successful checkout. We re-verify the
     * session server-side (never trust the redirect alone) before marking
     * the order as paid.
     */
    public function success(Request $request, Order $order)
    {
        if (!$this->canPayOrder($order)) {
            return redirect()->route('customer.orders.show', $order)
                ->with('error', 'This payment session is no longer valid for your account. Please start payment again from the order.');
        }

        $sessionId = $request->query('session_id');

        if ($sessionId) {
            $response = Http::withToken(config('services.stripe.secret'))
                ->withOptions(['verify' => !app()->environment('local')])
                ->get("{$this->apiBase}/checkout/sessions/{$sessionId}");

            if ($response->successful()) {
                $session = $response->json();

                if (($session['payment_status'] ?? null) === 'paid') {
                    $wasAlreadyPaid = $order->payment_status === 'paid';

                    $order->update([
                        'payment_status' => 'paid',
                        'payment_id'     => $session['payment_intent'] ?? $sessionId,
                    ]);

                    if (!$wasAlreadyPaid) {
                        $order->load('restaurant.owner');
                        $order->restaurant?->owner?->notify(new NewOrderPlaced($order));
                        Notification::send(User::admins()->get(), new NewOrderPlaced($order));
                    }
                }
            }
        }

        $message = $order->fresh()->payment_status === 'paid'
            ? 'Payment successful! Your order is confirmed. 🎉'
            : 'We could not confirm your payment yet. If the amount was deducted, please contact support.';

        return redirect()->route('customer.orders.show', $order)
            ->with($order->fresh()->payment_status === 'paid' ? 'success' : 'error', $message);
    }

    /**
     * Stripe redirects here if the customer cancels/abandons checkout.
     */
    public function cancel(Order $order)
    {
        if (!$this->canPayOrder($order)) {
            return redirect()->route('customer.orders.show', $order)
                ->with('error', 'This payment session is no longer valid for your account. Please start payment again from the order.');
        }

        return redirect()->route('customer.orders.show', $order)
            ->with('error', 'Payment was cancelled. You can retry payment anytime from your order details page.');
    }

    /**
     * Stripe webhook — the source of truth for payment confirmation.
     * Runs even if the customer closes the tab before the success redirect fires.
     * Registered without CSRF protection (see bootstrap/app.php) since Stripe
     * calls this directly, not through a browser session.
     */
    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $this->verifySignature($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            Log::warning('Stripe webhook signature verification failed: ' . $e->getMessage());
            return response('Invalid signature', 400);
        }

        $event = json_decode($payload, true);

        if (($event['type'] ?? null) === 'checkout.session.completed') {
            $session = $event['data']['object'];
            $orderId = $session['client_reference_id'] ?? null;

            if ($orderId && ($order = Order::find($orderId))) {
                $wasAlreadyPaid = $order->payment_status === 'paid';

                $order->update([
                    'payment_status' => 'paid',
                    'payment_id'     => $session['payment_intent'] ?? $session['id'],
                ]);

                if (!$wasAlreadyPaid) {
                    $order->load('restaurant.owner');
                    $order->restaurant?->owner?->notify(new NewOrderPlaced($order));
                    Notification::send(User::admins()->get(), new NewOrderPlaced($order));
                }
            }
        }

        return response('OK', 200);
    }

    protected function verifySignature(string $payload, ?string $sigHeader, ?string $secret): void
    {
        if (!$sigHeader || !$secret) {
            throw new \Exception('Missing signature header or webhook secret.');
        }

        $parts = [];
        foreach (explode(',', $sigHeader) as $part) {
            [$key, $value] = array_pad(explode('=', $part, 2), 2, null);
            $parts[$key][] = $value;
        }

        $timestamp   = $parts['t'][0] ?? null;
        $signatures  = $parts['v1'] ?? [];

        if (!$timestamp || empty($signatures)) {
            throw new \Exception('Malformed Stripe-Signature header.');
        }

        $expected = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);

        foreach ($signatures as $sig) {
            if (hash_equals($expected, (string) $sig)) {
                return;
            }
        }

        throw new \Exception('Signature mismatch.');
    }
    /**
     * Payment pages are only accessible to the current customer. The session
     * fallback covers the order just created during checkout when a custom
     * cart flow has not yet persisted the customer relation consistently.
     */
    protected function canPayOrder(Order $order): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return (int) $order->user_id === (int) auth()->id()
            || (int) session('last_order_id') === (int) $order->id;
    }

}
