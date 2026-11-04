<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ZomatoClone') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-10 sm:pt-0" style="background:#FFF7F2">
            <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-2 mb-6 no-underline">
                <span style="font-size:34px;line-height:1">🍔</span>
                <span style="font-size:26px;font-weight:800;color:#e8342a">Zomato<span style="color:#333">Clone</span></span>
            </a>

            <div class="w-full sm:max-w-md mt-2 px-6 py-6 bg-white overflow-hidden sm:rounded-2xl" style="box-shadow:0 8px 30px rgba(0,0,0,0.08); border:1px solid #f3e9e2">
                {{ $slot }}
            </div>

            <a href="{{ route('customer.dashboard') }}" class="mt-6 text-sm no-underline" style="color:#A9998A">
                ← Continue browsing without an account
            </a>
        </div>
    </body>
</html>
