<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|plus-jakarta-sans:600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink antialiased">
        <div class="flex min-h-screen flex-col bg-surface">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header>
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1">
                {{ $slot }}
            </main>

            <!-- Footer theo Figma: logo | mô tả bên trái, bản quyền bên phải -->
            <footer class="mt-10 bg-white shadow-[0_-1px_4px_rgba(0,0,0,0.04)]">
                <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-6 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <p class="flex items-center gap-2">
                        <x-application-logo class="text-lg" />
                        <span class="text-slate-400" aria-hidden="true">|</span>
                        <span>Hệ thống thi trắc nghiệm trực tuyến</span>
                    </p>
                    <p class="text-xs font-semibold tracking-wide text-slate-500">© {{ date('Y') }} {{ config('app.name', 'ExamPro') }}</p>
                </div>
            </footer>
        </div>
    </body>
</html>
