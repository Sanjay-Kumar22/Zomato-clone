<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class ReferralController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Safety net — guarantees a code exists even for accounts created
        // before this feature shipped or in any other edge case.
        if (!$user->referral_code) {
            do {
                $code = strtoupper(Str::random(6));
            } while (\App\Models\User::where('referral_code', $code)->exists());

            $user->update(['referral_code' => $code]);
        }

        $referredUsers = $user->referredUsers()->latest()->get();

        return view('customer.referral', compact('user', 'referredUsers'));
    }
}
