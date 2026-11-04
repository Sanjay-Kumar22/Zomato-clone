<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    // Signup bonus — only given when the new customer used a friend's referral
    // code, since it's the reward for the referral itself, not for signing up.
    protected const REFERRED_SIGNUP_BONUS = 50;

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', [
            'referralCode' => request('ref'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request, WalletService $wallet): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral_code' => ['nullable', 'string', 'max:12'],
        ]);

        $referrer = $request->filled('referral_code')
            ? User::where('referral_code', strtoupper($request->referral_code))->first()
            : null;

        // Public sign-up is always a customer account.
        // Restaurant owner / delivery agent / admin roles are granted
        // only by an admin from the admin panel — never self-selected.
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'referred_by' => $referrer?->id,
        ]);

        if ($referrer) {
            $wallet->credit($user, self::REFERRED_SIGNUP_BONUS, 'referral_bonus');
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route($user->dashboardRouteName());
    }
}