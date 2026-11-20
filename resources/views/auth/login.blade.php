<x-guest-layout>
    <div class="auth-heading">Welcome back</div>
    <p class="auth-subheading">Sign in to pick up where your cravings left off.</p>

    <x-auth-session-status class="auth-status" :status="session('status')" />

    @if($errors->has('google') || $errors->has('phone'))
        <div class="auth-alert" role="alert">
            {{ $errors->first('google') ?: $errors->first('phone') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="auth-field">
            <label class="auth-label" for="email">Email address</label>
            <div class="auth-input-wrap">
                <i class="auth-input-icon bi bi-envelope" aria-hidden="true"></i>
                <input id="email" class="auth-input {{ $errors->has('email') ? 'is-invalid' : '' }}" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus autocomplete="username" aria-describedby="{{ $errors->has('email') ? 'emailError' : '' }}">
            </div>
            @if($errors->has('email'))<div class="auth-error" id="emailError">{{ $errors->first('email') }}</div>@endif
        </div>

        <div class="auth-field">
            <label class="auth-label" for="password">Password</label>
            <div class="auth-input-wrap">
                <i class="auth-input-icon bi bi-lock" aria-hidden="true"></i>
                <input id="password" class="auth-input auth-password-input {{ $errors->has('password') ? 'is-invalid' : '' }}" type="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                <button class="auth-password-toggle" type="button" data-auth-password-toggle="password" aria-label="Show password"><i class="bi bi-eye"></i></button>
            </div>
            @if($errors->has('password'))<div class="auth-error">{{ $errors->first('password') }}</div>@endif
        </div>

        <div class="auth-options">
            <label for="remember_me" class="auth-remember">
                <input id="remember_me" type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                <span>Keep me signed in</span>
            </label>
            @if(Route::has('password.request'))
                <a class="auth-text-link" href="{{ route('password.request') }}">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="auth-primary"><span>Sign in</span><i class="bi bi-arrow-right"></i></button>
    </form>

    <div class="auth-divider">or continue with</div>
    <div class="auth-socials">
        <a href="{{ route('google.redirect') }}" class="auth-social-button">
            <span class="auth-google-mark" style="color:#4285f4" aria-hidden="true">G</span>
            <span>Continue with Google</span>
        </a>
        <a href="{{ route('phone-login') }}" class="auth-social-button">
            <i class="bi bi-phone" aria-hidden="true"></i><span>Sign in with phone OTP</span>
        </a>
    </div>

    <p class="auth-inline-cta">New to ZomatoClone? <a href="{{ route('register') }}">Create an account</a></p>
    <p class="auth-footnote">By continuing, you agree to use your account responsibly and keep your sign-in details secure.</p>
</x-guest-layout>
