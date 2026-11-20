<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDispute;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class RefundService
{
    public function issue(Order $order, float $amount, string $method, ?OrderDispute $dispute, ?User $actor): Refund
    {
        if (!in_array($method, ['wallet', 'original'], true)) {
            throw new \InvalidArgumentException('Choose a supported refund method.');
        }

        $refund = DB::transaction(function () use ($order, $amount, $method, $dispute, $actor): Refund {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $refunded = (float) $lockedOrder->refunds()->where('status', 'processed')->sum('amount');
            $inFlight = (float) $lockedOrder->refunds()->where('status', 'pending')->sum('amount');
            if ($amount <= 0 || $amount > (float) $lockedOrder->total - $refunded - $inFlight) {
                throw new \InvalidArgumentException('Refund amount exceeds the remaining refundable order balance.');
            }

            $attempt = null;
            if ($method === 'original') {
                $attempt = $lockedOrder->paymentAttempts()
                    ->where('status', 'captured')
                    ->whereNotNull('gateway_payment_id')
                    ->lockForUpdate()
                    ->first();
                if (!$attempt || !in_array($attempt->gateway, ['stripe', 'razorpay'], true)) {
                    throw new \InvalidArgumentException('No captured online payment is available for an original-method refund.');
                }
            }

            return Refund::create([
                'order_id' => $lockedOrder->id,
                'payment_attempt_id' => $attempt?->id,
                'dispute_id' => $dispute?->id,
                'requested_by' => $actor?->id,
                'amount' => $amount,
                'method' => $method,
                'status' => 'pending',
            ]);
        });

        if ($method === 'wallet') {
            DB::transaction(function () use ($refund, $order): void {
                $lockedRefund = Refund::whereKey($refund->id)->lockForUpdate()->firstOrFail();
                if ($lockedRefund->status !== 'pending') {
                    return;
                }

                app(WalletService::class)->credit($order->user, (float) $lockedRefund->amount, 'refund', $order->id);
                $lockedRefund->update(['status' => 'processed']);
            });

            return $refund->fresh();
        }

        $attempt = $refund->paymentAttempt;
        try {
            $response = match ($attempt->gateway) {
                'stripe' => $this->stripeRefund($attempt->gateway_payment_id, $amount),
                'razorpay' => $this->razorpayRefund($attempt->gateway_payment_id, $amount),
            };
        } catch (ConnectionException $exception) {
            $refund->update([
                'failure_reason' => 'Provider response is unknown; reconcile before retrying. ' . $exception->getMessage(),
            ]);
            throw $exception;
        }

        if ($response->failed()) {
            $message = $response->json('error.description')
                ?? $response->json('error.message')
                ?? 'The payment provider rejected the refund request.';
            $refund->update(['status' => 'failed', 'failure_reason' => $message]);

            return $refund->fresh();
        }

        $refund->update([
            'status' => 'processed',
            'gateway_refund_id' => $response->json('id'),
        ]);

        return $refund->fresh();
    }

    private function stripeRefund(string $paymentId, float $amount)
    {
        $secret = config('services.stripe.secret');
        if (!$secret) {
            throw new \RuntimeException('Stripe is not configured for original-method refunds.');
        }

        return Http::asForm()
            ->withToken($secret)
            ->withOptions(['verify' => !app()->environment('local')])
            ->post('https://api.stripe.com/v1/refunds', [
                'payment_intent' => $paymentId,
                'amount' => (int) round($amount * 100),
            ]);
    }

    private function razorpayRefund(string $paymentId, float $amount)
    {
        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');
        if (!$key || !$secret) {
            throw new \RuntimeException('Razorpay is not configured for original-method refunds.');
        }

        return Http::withBasicAuth($key, $secret)
            ->withOptions(['verify' => !app()->environment('local')])
            ->post("https://api.razorpay.com/v1/payments/{$paymentId}/refund", [
                'amount' => (int) round($amount * 100),
            ]);
    }
}
