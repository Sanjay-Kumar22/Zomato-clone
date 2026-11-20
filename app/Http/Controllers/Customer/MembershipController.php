<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerMembership;
use App\Models\User;
use App\Services\PlatformSettings;
use Illuminate\Support\Facades\DB;

class MembershipController extends Controller
{
    public function index(PlatformSettings $settings)
    {
        $membership = auth()->user()->memberships()->latest('expires_at')->first();
        $activeMembership = auth()->user()->memberships()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->first();
        $price = $settings->value('membership_monthly_price');

        return view('customer.membership', compact('membership', 'activeMembership', 'price'));
    }

    public function purchase(PlatformSettings $settings)
    {
        $price = $settings->value('membership_monthly_price');
        if ($price <= 0) {
            abort(503, 'Membership pricing is not configured.');
        }

        $purchased = DB::transaction(function () use ($price): string {
            $user = User::whereKey(auth()->id())->lockForUpdate()->firstOrFail();
            $active = CustomerMembership::where('user_id', $user->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->latest('expires_at')
                ->first();

            if ($active) {
                return 'active';
            }
            if ((float) $user->wallet_balance < $price) {
                return 'balance';
            }

            $startsAt = now();
            app(\App\Services\WalletService::class)->debit($user, $price, 'membership_purchase');
            CustomerMembership::create([
                'user_id' => $user->id,
                'plan' => 'monthly',
                'price_paid' => $price,
                'starts_at' => $startsAt,
                'expires_at' => $startsAt->copy()->addMonth(),
                'status' => 'active',
            ]);

            return 'purchased';
        });

        return match ($purchased) {
            'active' => back()->with('error', 'Your free-delivery membership is already active.'),
            'balance' => redirect()->route('customer.wallet.index')->with('error', 'Add funds to your wallet before purchasing membership.'),
            default => back()->with('success', 'Membership activated. Free delivery will apply automatically at checkout.'),
        };
    }
}
