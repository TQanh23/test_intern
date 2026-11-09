<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'ExamPro') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        {{-- Nền gradient giống khung khách --}}
        <div class="min-h-screen flex flex-col items-center justify-center px-4 text-center bg-gradient-to-br from-sky-500 to-indigo-500">
            <x-application-logo class="text-5xl !text-white" />
            <p class="mt-4 text-lg text-white/90">Hệ thống thi trắc nghiệm trực tuyến</p>

            <div class="mt-8 flex gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-full bg-white px-6 py-2.5 text-sm font-semibold text-slate-900 hover:bg-slate-100">Vào dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-full bg-white px-6 py-2.5 text-sm font-semibold text-slate-900 hover:bg-slate-100">Đăng nhập</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="rounded-full px-6 py-2.5 text-sm font-semibold text-white ring-1 ring-white/70 hover:bg-white/10">Đăng ký</a>
                    @endif
                @endauth
            </div>
        </div>
    </body>
</html>
