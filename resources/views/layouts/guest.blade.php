<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'WISN Staffing Tool') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
            <!-- Decorative background elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
                <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-teal-100 rounded-full mix-blend-multiply filter blur-3xl opacity-10"></div>
            </div>

            <!-- Logo -->
            <div class="relative z-10 mb-6">
                <a href="/" class="flex flex-col items-center gap-2 group">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/25 group-hover:shadow-blue-500/40 transition-shadow duration-300">
                        <svg class="w-9 h-9 text-white" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="32" cy="32" r="30" fill="none"/>
                            <path d="M20 18c-3.3 0-6 2.7-6 6v14c0 5.5 4.5 10 10 10h12c5.5 0 10-4.5 10-10V24c0-3.3-2.7-6-6-6s-6 2.7-6 6v8c0 1.1-.9 2-2 2h-4c-1.1 0-2-.9-2-2v-8c0-3.3-2.7-6-6-6z" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="32" y1="26" x2="32" y2="42" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                            <line x1="24" y1="34" x2="40" y2="34" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="text-center">
                        <span class="text-xl font-bold bg-gradient-to-r from-blue-700 to-indigo-600 bg-clip-text text-transparent">WISN Staffing</span>
                        <span class="block text-xs text-gray-500 font-medium">Nursing Workforce Planning</span>
                    </div>
                </a>
            </div>

            <!-- Card -->
            <div class="relative z-10 w-full sm:max-w-md mt-2 px-6 py-8 bg-white/80 backdrop-blur-sm shadow-xl shadow-gray-200/50 overflow-hidden sm:rounded-2xl border border-white/60">
                {{ $slot }}
            </div>

            <!-- Footer -->
            <div class="relative z-10 mt-6 text-center text-xs text-gray-400">
                <p>WHO WISN Methodology &bull; Nursing Workforce Planning Tool</p>
            </div>
        </div>
    </body>
</html>
