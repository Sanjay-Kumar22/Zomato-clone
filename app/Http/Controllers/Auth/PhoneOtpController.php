<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PhoneOtpController extends Controller
{
    public function create()
    {
        return view('auth.phone-login');
    }

    public function send(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $sid = config('services.twilio.account_sid');
        $token = config('services.twilio.auth_token');
        $service = config('services.twilio.verify_service_sid');
        if (!$sid || !$token || !$service) {
            return back()->withInput()->withErrors([
                'phone' => 'Phone sign-in is not configured yet. Contact the administrator or use another sign-in method.',
            ]);
        }

        try {
            $response = Http::asForm()->withBasicAuth($sid, $token)
                ->post("https://verify.twilio.com/v2/Services/{$service}/Verifications", [
                    'To' => $request->phone,
                    'Channel' => 'sms',
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $exception) {
            Log::error('Twilio Verify request failed to connect', ['message' => $exception->getMessage()]);
            return back()->withInput()->withErrors(['phone' => 'We could not send a code right now. Please try again shortly.']);
        }

        if ($response->failed() || $response->json('status') !== 'pending') {
            Log::warning('Twilio Verify rejected an OTP request', ['status' => $response->status()]);
            return back()->withInput()->withErrors(['phone' => 'A verification code could not be sent. Check the phone number and try again.']);
        }

        session([
            'phone_otp_login' => [
                'phone' => $request->phone,
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'sent_at' => now()->timestamp,
            ],
        ]);

        return redirect()->route('phone-login')->with('status', 'A verification code was sent to your phone.');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);
        $login = session('phone_otp_login');
        if (!$login || now()->timestamp - (int) $login['sent_at'] > 600) {
            session()->forget('phone_otp_login');
            return redirect()->route('phone-login')->withErrors(['code' => 'Your code expired. Request a new one.']);
        }

        $sid = config('services.twilio.account_sid');
        $token = config('services.twilio.auth_token');
        $service = config('services.twilio.verify_service_sid');
        if (!$sid || !$token || !$service) {
            return redirect()->route('phone-login')->withErrors(['code' => 'Phone sign-in is not configured.']);
        }

        try {
            $response = Http::asForm()->withBasicAuth($sid, $token)
                ->post("https://verify.twilio.com/v2/Services/{$service}/VerificationCheck", [
                    'To' => $login['phone'],
                    'Code' => $request->code,
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $exception) {
            Log::error('Twilio Verify check failed to connect', ['message' => $exception->getMessage()]);
            return back()->withErrors(['code' => 'We could not verify the code right now. Try again shortly.']);
        }

        if ($response->failed() || $response->json('status') !== 'approved') {
            return back()->withErrors(['code' => 'That verification code is incorrect or expired.']);
        }

        $matches = User::where('phone', $login['phone'])->limit(2)->get();
        if ($matches->count() > 1) {
            Log::error('Phone OTP login found duplicate accounts for a verified phone');
            session()->forget('phone_otp_login');
            return redirect()->route('login')->withErrors(['phone' => 'This phone number is linked to multiple accounts. Contact support.']);
        }

        $user = $matches->first();
        if ($user && $user->role !== 'customer') {
            session()->forget('phone_otp_login');
            return redirect()->route('login')->withErrors(['phone' => 'Phone OTP sign-in is available for customer accounts.']);
        }

        if (!$user) {
            $email = strtolower(trim((string) ($login['email'] ?? '')));
            $name = trim((string) ($login['name'] ?? ''));
            if ($email === '' || $name === '') {
                return redirect()->route('phone-login')->withErrors([
                    'email' => 'Enter your name and email to create a new account, then request a new code.',
                ]);
            }
            if (User::where('email', $email)->exists()) {
                return redirect()->route('phone-login')->withErrors([
                    'email' => 'That email already belongs to an account. Sign in with that account or use another email.',
                ]);
            }

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'phone' => $login['phone'],
                'password' => Hash::make(Str::random(48)),
                'role' => 'customer',
            ]);
        }

        $user->forceFill(['phone_verified_at' => now()])->save();
        session()->forget('phone_otp_login');
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard')->with('success', 'Signed in with your verified phone number.');
    }
}
