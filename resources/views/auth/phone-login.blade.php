<x-guest-layout>
    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-900">Sign in with phone</h1>
        <p class="mt-1 text-sm text-gray-600">Enter your number to receive a secure SMS verification code.</p>
    </div>

    @if(session('status'))
        <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('phone-login.send') }}">
        @csrf
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">Phone number (international format)</label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone', session('phone_otp_login.phone')) }}" placeholder="+919876543210"
                pattern="\+[1-9][0-9]{7,14}" autocomplete="tel" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
        </div>
        <div class="mt-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Name <span class="text-gray-400">(new accounts)</span></label>
            <input id="name" name="name" type="text" value="{{ old('name', session('phone_otp_login.name')) }}" maxlength="255" autocomplete="name"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
        </div>
        <div class="mt-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-gray-400">(new accounts)</span></label>
            <input id="email" name="email" type="email" value="{{ old('email', session('phone_otp_login.email')) }}" maxlength="255" autocomplete="email"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
        </div>
        <button class="mt-5 w-full rounded-md bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-700">Send verification code</button>
    </form>

    @if(session('phone_otp_login'))
    <form method="POST" action="{{ route('phone-login.verify') }}" class="mt-6 border-t border-gray-200 pt-5">
        @csrf
        <label for="code" class="block text-sm font-medium text-gray-700">6-digit verification code</label>
        <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" autocomplete="one-time-code" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
        <button class="mt-3 w-full rounded-md bg-gray-900 px-4 py-2 font-semibold text-white hover:bg-gray-800">Verify & sign in</button>
    </form>
    @endif

    <div class="mt-5 text-center text-sm"><a class="text-red-600 hover:underline" href="{{ route('login') }}">Back to password sign-in</a></div>
</x-guest-layout>
