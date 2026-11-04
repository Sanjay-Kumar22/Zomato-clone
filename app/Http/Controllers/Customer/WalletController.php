<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\WalletTopup;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    protected string $apiBase = 'https://api.razorpay.com/v1';

    public function index()
    {
        $transactions = auth()->user()->walletTransactions()->with('order')->paginate(20);
        $balance = auth()->user()->wallet_balance;

        return view('customer.wallet', compact('transactions', 'balance'));
    }

    // Creates a Razorpay order for the top-up amount, then shows a page that
    // opens Razorpay's Checkout.js popup — same flow as paying for an order.
    public function topup(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10|max:10000',
        ]);

        $keyId     = config('services.razorpay.key');
        $keySecret = config('services.razorpay.secret');

        if (!$keyId || !$keySecret) {
            Log::error('Razorpay keys are not configured.');
            return back()->with('error', 'Online payment is not set up yet. Please try again later.');
        }

        $topup = WalletTopup::create([
            'user_id' => auth()->id(),
            'amount'  => $request->amount,
            'status'  => 'pending',
        ]);

        $response = Http::withBasicAuth($keyId, $keySecret)
            // Same local-only SSL bypass as RazorpayController — see the
            // comment there for why. Never disables verification outside
            // app()->environment('local').
            ->withOptions(['verify' => !app()->environment('local')])
            ->post("{$this->apiBase}/orders", [
                'amount'   => (int) round($request->amount * 100), // paise
                'currency' => 'INR',
                'receipt'  => 'wallet_topup_' . $topup->id,
                'notes'    => ['wallet_topup_id' => $topup->id],
            ]);

        if ($response->failed()) {
            Log::error('Razorpay wallet top-up order creation failed', ['response' => $response->json()]);
            $topup->update(['status' => 'cancelled']);
            return back()->with('error', 'Could not start payment. Please try again.');
        }

        $razorpayOrder = $response->json();
        $topup->update(['razorpay_order_id' => $razorpayOrder['id']]);

        return view('customer.wallet-topup-checkout', [
            'topup'         => $topup,
            'razorpayOrder' => $razorpayOrder,
            'keyId'         => $keyId,
        ]);
    }

    public function topupVerify(Request $request, WalletTopup $topup, WalletService $wallet)
    {
        abort_if($topup->user_id !== auth()->id(), 403);

        if ($topup->status === 'paid') {
            return redirect()->route('customer.wallet.index')->with('success', 'This top-up was already credited.');
        }

        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id'   => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $keySecret = config('services.razorpay.secret');

        $expected = hash_hmac(
            'sha256',
            $request->razorpay_order_id . '|' . $request->razorpay_payment_id,
            $keySecret
        );

        if (!hash_equals($expected, $request->razorpay_signature)) {
            Log::warning('Razorpay signature mismatch on wallet top-up #' . $topup->id);
            return redirect()->route('customer.wallet.index')
                ->with('error', 'Payment could not be verified. If the amount was deducted, please contact support.');
        }

        $topup->update([
            'status'               => 'paid',
            'razorpay_payment_id'  => $request->razorpay_payment_id,
        ]);

        $wallet->credit(auth()->user(), (float) $topup->amount, 'wallet_topup');

        return redirect()->route('customer.wallet.index')
            ->with('success', '₹' . number_format($topup->amount, 2) . ' added to your wallet! 🎉');
    }

    public function topupCancel(WalletTopup $topup)
    {
        abort_if($topup->user_id !== auth()->id(), 403);

        if ($topup->status === 'pending') {
            $topup->update(['status' => 'cancelled']);
        }

        return redirect()->route('customer.wallet.index')
            ->with('error', 'Top-up was cancelled.');
    }
}
