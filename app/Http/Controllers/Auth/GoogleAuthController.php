<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.redirect') ?: route('google.callback');
        if (!$clientId || !config('services.google.client_secret')) {
            return redirect()->route('login')->withErrors([
                'google' => 'Google sign-in is not configured. Please use email/password or phone verification.',
            ]);
        }

        $state = Str::random(48);
        session(['google_oauth_state' => $state]);
        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ], '', '&', PHP_QUERY_RFC3986);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    public function callback(Request $request)
    {
        $expectedState = session()->pull('google_oauth_state');
        if (!$expectedState || !$request->filled('state')
            || !hash_equals((string) $expectedState, (string) $request->query('state'))) {
            return redirect()->route('login')->withErrors(['google' => 'Google sign-in could not be verified. Please try again.']);
        }

        if ($request->filled('error') || !$request->filled('code')) {
            return redirect()->route('login')->withErrors(['google' => 'Google sign-in was cancelled or denied.']);
        }

        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirectUri = config('services.google.redirect') ?: route('google.callback');
        if (!$clientId || !$clientSecret) {
            return redirect()->route('login')->withErrors(['google' => 'Google sign-in is not configured.']);
        }

        try {
            $tokenResponse = Http::asForm()->timeout(10)->post('https://oauth2.googleapis.com/token', [
                'code' => $request->query('code'),
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $exception) {
            Log::error('Google OAuth token service could not be reached', ['message' => $exception->getMessage()]);
            return redirect()->route('login')->withErrors(['google' => 'Google sign-in is temporarily unavailable.']);
        }
        if ($tokenResponse->failed() || !$tokenResponse->json('access_token')) {
            Log::warning('Google OAuth token exchange failed', ['status' => $tokenResponse->status()]);
            return redirect()->route('login')->withErrors(['google' => 'Google sign-in could not be completed. Please try again.']);
        }

        try {
            $profileResponse = Http::withToken($tokenResponse->json('access_token'))
                ->timeout(10)
                ->get('https://openidconnect.googleapis.com/v1/userinfo');
        } catch (\Illuminate\Http\Client\ConnectionException $exception) {
            Log::error('Google profile service could not be reached', ['message' => $exception->getMessage()]);
            return redirect()->route('login')->withErrors(['google' => 'Google sign-in is temporarily unavailable.']);
        }
        $profile = $profileResponse->json();
        if ($profileResponse->failed() || empty($profile['sub']) || empty($profile['email'])
            || !filter_var($profile['email'], FILTER_VALIDATE_EMAIL)
            || !filter_var($profile['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            Log::warning('Google OAuth returned an invalid or unverified profile');
            return redirect()->route('login')->withErrors(['google' => 'Google did not provide a verified email address.']);
        }

        $user = User::where('google_id', $profile['sub'])->first();
        $emailOwner = User::where('email', $profile['email'])->first();
        if ($user && $emailOwner && $emailOwner->id !== $user->id) {
            return redirect()->route('login')->withErrors(['google' => 'This Google account conflicts with an existing account. Contact support.']);
        }
        if (!$user && $emailOwner) {
            if ($emailOwner->google_id && $emailOwner->google_id !== $profile['sub']) {
                return redirect()->route('login')->withErrors(['google' => 'This email is already linked to another Google account.']);
            }
            $user = $emailOwner;
            $user->forceFill([
                'google_id' => $profile['sub'],
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } elseif (!$user) {
            $user = User::create([
                'name' => mb_substr($profile['name'] ?? $profile['email'], 0, 255),
                'email' => $profile['email'],
                'password' => Str::random(48),
                'role' => 'customer',
            ]);
            $user->forceFill([
                'google_id' => $profile['sub'],
                'email_verified_at' => now(),
            ])->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route($user->dashboardRouteName());
    }
}
