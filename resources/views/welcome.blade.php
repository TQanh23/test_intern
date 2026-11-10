<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'ExamPro') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|plus-jakarta-sans:600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        {{-- Nền gradient giống khung khách --}}
        <div class="min-h-screen flex flex-col items-center justify-center px-4 text-center bg-ink">
            <x-application-logo class="text-5xl" :dark="true" />
            <p class="mt-4 text-lg text-white/90">Hệ thống thi trắc nghiệm trực tuyến</p>

            <div class="mt-8 flex gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-white px-6 py-2.5 text-sm font-semibold text-ink hover:bg-surface-low">Vào dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg bg-white px-6 py-2.5 text-sm font-semibold text-ink hover:bg-surface-low">Đăng nhập</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="rounded-lg px-6 py-2.5 text-sm font-semibold text-white ring-1 ring-white/70 hover:bg-white/10">Đăng ký</a>
                    @endif
                @endauth
            </div>
        </div>
    </body>
</html>
