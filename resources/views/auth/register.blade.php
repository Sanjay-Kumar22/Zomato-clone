<x-guest-layout>
    <div class="auth-heading">A better bite starts here</div>
    <p class="auth-subheading">Create your account and find something delicious nearby.</p>

    @if($errors->any())
        <div class="auth-alert" role="alert">
            <strong>Please check the details below.</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="auth-form-grid">
        <div class="auth-field">
            <label class="auth-label" for="name">Your name</label>
            <div class="auth-input-wrap">
                <i class="auth-input-icon bi bi-person" aria-hidden="true"></i>
                <input id="name" class="auth-input {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" value="{{ old('name') }}" placeholder="What should we call you?" required autofocus autocomplete="name">
            </div>
            @if($errors->has('name'))<div class="auth-error">{{ $errors->first('name') }}</div>@endif
        </div>

        <div class="auth-field">
            <label class="auth-label" for="email">Email address</label>
            <div class="auth-input-wrap">
                <i class="auth-input-icon bi bi-envelope" aria-hidden="true"></i>
                <input id="email" class="auth-input {{ $errors->has('email') ? 'is-invalid' : '' }}" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="username">
            </div>
            @if($errors->has('email'))<div class="auth-error">{{ $errors->first('email') }}</div>@endif
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password">Create password</label>
            <div class="auth-input-wrap">
                <i class="auth-input-icon bi bi-lock" aria-hidden="true"></i>
                <input id="password" class="auth-input auth-password-input {{ $errors->has('password') ? 'is-invalid' : '' }}" type="password" name="password" placeholder="Choose a secure password" required autocomplete="new-password">
                <button class="auth-password-toggle" type="button" data-auth-password-toggle="password" aria-label="Show password"><i class="bi bi-eye"></i></button>
            </div>
            @if($errors->has('password'))<div class="auth-error">{{ $errors->first('password') }}</div>@endif
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password_confirmation">Confirm password</label>
            <div class="auth-input-wrap">
                <i class="auth-input-icon bi bi-shield-check" aria-hidden="true"></i>
                <input id="password_confirmation" class="auth-input auth-password-input" type="password" name="password_confirmation" placeholder="Enter your password again" required autocomplete="new-password">
                <button class="auth-password-toggle" type="button" data-auth-password-toggle="password_confirmation" aria-label="Show password"><i class="bi bi-eye"></i></button>
            </div>
            @if($errors->has('password_confirmation'))<div class="auth-error">{{ $errors->first('password_confirmation') }}</div>@endif
        </div>

        <div class="auth-field auth-field-wide">
            <label class="auth-label" for="referral_code">Referral code <span style="color:#a0958a;font-weight:500">(optional)</span></label>
            <div class="auth-input-wrap">
                <i class="auth-input-icon bi bi-ticket-perforated" aria-hidden="true"></i>
                <input id="referral_code" class="auth-input {{ $errors->has('referral_code') ? 'is-invalid' : '' }}" type="text" name="referral_code" value="{{ old('referral_code', $referralCode ?? '') }}" placeholder="Got a friend's code?">
            </div>
            @if($errors->has('referral_code'))<div class="auth-error">{{ $errors->first('referral_code') }}</div>@endif
            @if(!empty($referralCode))<p class="auth-referral-note"><i class="bi bi-gift me-1"></i>You'll get ₹50 wallet credit for using this code!</p>@endif
        </div>
        </div>

        <button type="submit" class="auth-primary"><span>Create my account</span><i class="bi bi-arrow-right"></i></button>
    </form>

    <p class="auth-inline-cta">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
    <p class="auth-footnote">By creating an account, you agree to keep your account details secure.</p>
</x-guest-layout>
