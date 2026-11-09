<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        @php
            // Mục sidebar: [nhãn, route, các pattern route để tô màu mục đang chọn]
            $items = [
                ['Tổng quan', 'admin.dashboard', ['admin.dashboard']],
                ['Đề thi', 'admin.exams.index', ['admin.exams.*', 'admin.questions.*']],
                ['Thống kê', 'admin.statistics', ['admin.statistics']],
                ['Lượt xem thử', 'admin.preview-statistics', ['admin.preview-statistics']],
                ['Gói IELTS', 'packages.index', ['packages.*']],
            ];
        @endphp

        <div x-data="{ open: false }" class="min-h-screen bg-slate-50">
            <!-- Thanh trên cùng (chỉ hiện dưới lg) -->
            <div class="flex h-14 items-center justify-between bg-slate-900 px-4 lg:hidden">
                <a href="{{ route('admin.dashboard') }}">
                    <x-application-logo class="text-xl" />
                </a>
                <button type="button" x-on:click="open = true" aria-label="Mở menu" :aria-expanded="open" class="rounded-lg p-2 text-slate-300 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <!-- Lớp nền tối của ngăn kéo, bấm vào thì đóng -->
            <div x-show="open" x-cloak x-on:click="open = false" class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>

            <!-- Sidebar: cố định từ lg trở lên, dưới lg là ngăn kéo -->
            <aside :class="open ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-slate-900 text-slate-300 transition-transform lg:translate-x-0">
                <div class="flex h-16 items-center justify-between px-6">
                    <a href="{{ route('admin.dashboard') }}">
                        <x-application-logo class="text-2xl" />
                    </a>
                    <button type="button" x-on:click="open = false" aria-label="Đóng menu" class="rounded-lg p-1 text-slate-400 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 lg:hidden">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 space-y-1 px-3 py-4" aria-label="Menu quản trị">
                    @foreach ($items as [$label, $routeName, $patterns])
                        <a href="{{ route($routeName) }}" @if (request()->routeIs(...$patterns)) aria-current="page" @endif
                            class="block rounded-xl px-3 py-2 text-sm font-semibold transition {{ request()->routeIs(...$patterns) ? 'bg-gradient-to-r from-sky-500 to-indigo-500 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>

                <div class="border-t border-slate-800 px-6 py-4 text-sm">
                    <p class="truncate font-semibold text-white">{{ Auth::user()->name }}</p>
                    <a href="{{ route('profile.edit') }}" class="mt-2 block hover:text-white">Hồ sơ</a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-1">
                        @csrf
                        <button type="submit" class="hover:text-white">Đăng xuất</button>
                    </form>
                </div>
            </aside>

            <!-- Nội dung -->
            <div class="lg:pl-64">
                @isset($header)
                    <header>
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
