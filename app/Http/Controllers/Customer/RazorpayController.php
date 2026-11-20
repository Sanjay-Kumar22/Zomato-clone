<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\User;
use App\Models\WalletTopup;
use App\Notifications\NewOrderPlaced;
use App\Services\WalletService;
use App\Mail\OrderConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class RazorpayController extends Controller
{
    protected string $apiBase = 'https://api.razorpay.com/v1';

    /**
     * Create a Razorpay Order server-side, then show a small page that opens
     * Razorpay's Checkout.js popup for the customer to pay.
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

        $keyId     = config('services.razorpay.key');
        $keySecret = config('services.razorpay.secret');

        if (!$keyId || !$keySecret) {
            Log::error('Razorpay keys are not configured.');
            return redirect()->route('customer.orders.show', $order)
                ->with('error', 'Online payment is not set up yet. Please choose Cash on Delivery.');
        }

        $response = Http::withBasicAuth($keyId, $keySecret)
            // Local Windows/antivirus setups often intercept HTTPS with a
            // self-signed cert ("cURL error 60: self-signed certificate in
            // certificate chain") — skip verification only in local dev so
            // this never ships live. Proper fix for a real deployment: give
            // PHP a current CA bundle (curl.cainfo in php.ini), not this.
            ->withOptions(['verify' => !app()->environment('local')])
            ->post("{$this->apiBase}/orders", [
                'amount'   => (int) round($order->total * 100), // paise
                'currency' => 'INR',
                'receipt'  => 'order_' . $order->id,
                'notes'    => ['order_id' => $order->id],
            ]);

        if ($response->failed()) {
            Log::error('Razorpay order creation failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            $order->update(['payment_status' => 'failed']);
            $order->paymentAttempts()->create([
                'gateway' => 'razorpay',
                'amount' => $order->total,
                'currency' => 'INR',
                'status' => 'failed',
                'failure_reason' => $response->json('error.description') ?? 'Razorpay order creation failed.',
            ]);
            return redirect()->route('customer.orders.show', $order)
                ->with('error', 'Razorpay payment could not be started. Please verify the Razorpay test keys/configuration and try again.');
        }

        $razorpayOrder = $response->json();

        $order->update(['payment_id' => $razorpayOrder['id']]);
        $order->paymentAttempts()->create([
            'gateway' => 'razorpay',
            'gateway_order_id' => $razorpayOrder['id'],
            'amount' => $order->total,
            'currency' => $razorpayOrder['currency'] ?? 'INR',
            'status' => 'pending',
        ]);

        return view('customer.razorpay-checkout', [
            'order'          => $order,
            'razorpayOrder'  => $razorpayOrder,
            'keyId'          => $keyId,
        ]);
    }

    /**
     * Checkout.js posts here after the customer completes payment in the popup.
     * We re-verify the signature server-side (never trust the browser alone)
     * before marking the order as paid.
     */
    public function verify(Request $request, Order $order)
    {
        if (!$this->canPayOrder($order)) {
            return redirect()->route('customer.orders.show', $order)
                ->with('error', 'This payment session is no longer valid for your account. Please start payment again from the order.');
        }

        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id'   => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $keySecret = config('services.razorpay.secret');
        abort_unless(hash_equals((string) $order->payment_id, (string) $request->razorpay_order_id), 422, 'Payment order mismatch.');

        $expected = hash_hmac(
            'sha256',
            $request->razorpay_order_id . '|' . $request->razorpay_payment_id,
            $keySecret
        );

        if (!hash_equals($expected, $request->razorpay_signature)) {
            Log::warning('Razorpay signature mismatch on order #' . $order->id);
            return redirect()->route('customer.orders.show', $order)
                ->with('error', 'Payment could not be verified. If the amount was deducted, please contact support.');
        }

        $wasAlreadyPaid = $order->payment_status === 'paid';

        $order->update([
            'payment_status' => 'paid',
            'payment_id'     => $request->razorpay_payment_id,
        ]);
        $order->paymentAttempts()
            ->where('gateway_order_id', $request->razorpay_order_id)
            ->update([
                'status' => 'captured',
                'gateway_payment_id' => $request->razorpay_payment_id,
            ]);

        if (!$wasAlreadyPaid) {
            $order->load('restaurant.owner', 'items', 'user');
            $order->restaurant?->owner?->notify(new NewOrderPlaced($order));
            Notification::send(User::admins()->get(), new NewOrderPlaced($order));

            if ($order->user?->email) {
                Mail::to($order->user->email)->send(new OrderConfirmationMail($order));
            }
        }

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Payment successful! Your order is confirmed. 🎉');
    }

    /**
     * Customer closed/cancelled the Razorpay popup without paying.
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
     * Razorpay webhook — the source of truth for payment confirmation.
     * Fires even if the customer closes the tab right after paying.
     * Registered without CSRF protection (see bootstrap/app.php) since Razorpay
     * calls this directly, not through a browser session.
     */
    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('X-Razorpay-Signature');
        $secret    = config('services.razorpay.webhook_secret');

        if (!$sigHeader || !$secret) {
            Log::warning('Razorpay webhook missing signature or secret not configured.');
            return response('Invalid signature', 400);
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($expected, $sigHeader)) {
            Log::warning('Razorpay webhook signature verification failed.');
            return response('Invalid signature', 400);
        }

        $event = json_decode($payload, true);

        if (in_array(($event['event'] ?? null), ['payment.captured', 'payment.failed'], true)) {
            $payment  = $event['payload']['payment']['entity'] ?? [];
            $orderId  = $payment['notes']['order_id'] ?? null;
            $topupId  = $payment['notes']['wallet_topup_id'] ?? null;

            if ($orderId && ($order = Order::find($orderId))) {
                $captured = ($event['event'] ?? null) === 'payment.captured';
                $attempt = $order->paymentAttempts()
                    ->where('gateway_order_id', $payment['order_id'] ?? '')
                    ->first();
                $wasAlreadyPaid = $order->payment_status === 'paid';

                if ($captured) {
                    $order->update([
                        'payment_status' => 'paid',
                        'payment_id'     => $payment['id'] ?? $order->payment_id,
                    ]);
                    $attempt?->update([
                        'status' => 'captured',
                        'gateway_payment_id' => $payment['id'] ?? null,
                    ]);
                } elseif (!$wasAlreadyPaid) {
                    $order->update(['payment_status' => 'failed']);
                    $attempt?->update([
                        'status' => 'failed',
                        'gateway_payment_id' => $payment['id'] ?? null,
                        'failure_reason' => $payment['error_description'] ?? 'Payment failed',
                    ]);
                }

                if (!$wasAlreadyPaid) {
                    $order->load('restaurant.owner', 'items', 'user');
                    $order->restaurant?->owner?->notify(new NewOrderPlaced($order));
                    Notification::send(User::admins()->get(), new NewOrderPlaced($order));

                    if ($order->user?->email) {
                        Mail::to($order->user->email)->send(new OrderConfirmationMail($order));
                    }
                }
            }

            // Wallet top-up — customer may have closed the popup right after
            // paying, so this webhook is the fallback that still credits them.
            if (($event['event'] ?? null) === 'payment.captured'
                && $topupId && ($topup = WalletTopup::find($topupId)) && $topup->status !== 'paid') {
                $topup->update([
                    'status'              => 'paid',
                    'razorpay_payment_id' => $payment['id'] ?? $topup->razorpay_payment_id,
                ]);

                app(WalletService::class)->credit($topup->user, (float) $topup->amount, 'wallet_topup');
            }
        }

        return response('OK', 200);
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
