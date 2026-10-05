<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>WISN Staffing Tool — Nursing Workforce Planning</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <!-- Background -->
        <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 relative overflow-hidden">
            <!-- Decorative blobs -->
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
            <div class="absolute top-1/3 right-1/4 w-64 h-64 bg-teal-100 rounded-full mix-blend-multiply filter blur-3xl opacity-15"></div>

            <!-- Navbar -->
            <nav class="relative z-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/25">
                                <svg class="w-6 h-6 text-white" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 18c-3.3 0-6 2.7-6 6v14c0 5.5 4.5 10 10 10h12c5.5 0 10-4.5 10-10V24c0-3.3-2.7-6-6-6s-6 2.7-6 6v8c0 1.1-.9 2-2 2h-4c-1.1 0-2-.9-2-2v-8c0-3.3-2.7-6-6-6z" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <line x1="32" y1="26" x2="32" y2="42" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                                    <line x1="24" y1="34" x2="40" y2="34" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-lg font-bold bg-gradient-to-r from-blue-700 to-indigo-600 bg-clip-text text-transparent">WISN Staffing</span>
                                <span class="block text-[10px] text-gray-500 -mt-0.5 font-medium">Nursing Workforce Planning</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            @if (Route::has('login'))
                                @auth
                                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2 px-5 rounded-lg shadow-md shadow-blue-500/25 hover:shadow-blue-500/40 transition-all duration-200 text-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25A2.25 2.25 0 0113.5 10.5V6z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                        </svg>
                                        Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 font-medium text-sm transition">Sign in</a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2 px-5 rounded-lg shadow-md shadow-blue-500/25 hover:shadow-blue-500/40 transition-all duration-200 text-sm">
                                            Get Started
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                            </svg>
                                        </a>
                                    @endif
                                @endauth
                            @endif
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Hero Section -->
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-24">
                <div class="text-center max-w-3xl mx-auto">
                    <div class="inline-flex items-center gap-2 bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1.5 rounded-full mb-6">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        WHO-Approved Methodology
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight">
                        <span class="text-gray-900">Evidence-Based</span><br>
                        <span class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">Nurse Staffing</span><br>
                        <span class="text-gray-900">Made Simple</span>
                    </h1>
                    <p class="mt-6 text-lg text-gray-600 max-w-2xl mx-auto leading-relaxed">
                        Calculate your hospital's nursing workforce requirements using the WHO WISN methodology.
                        Move beyond guesswork — make staffing decisions grounded in actual workload data.
                    </p>
                    <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3 px-8 rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transition-all duration-200 text-base">
                                Start Free
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        @endif
                        <a href="{{ route('help') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-gray-700 font-semibold py-3 px-8 rounded-xl shadow-sm border border-gray-200 transition-all duration-200 text-base">
                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                            </svg>
                            Learn WISN Method
                        </a>
                    </div>
                </div>

                <!-- Illustration / Dashboard Preview -->
                <div class="mt-16 relative">
                    <div class="absolute inset-0 bg-gradient-to-t from-blue-50 via-transparent to-transparent pointer-events-none"></div>
                    <div class="bg-white/60 backdrop-blur-sm rounded-2xl shadow-2xl shadow-gray-200/50 border border-white/80 p-2 max-w-5xl mx-auto">
                        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-6 sm:p-8">
                            <!-- Fake dashboard header -->
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    </div>
                                    <span class="text-white font-semibold text-sm">WISN Dashboard</span>
                                </div>
                                <div class="flex gap-1.5">
                                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                </div>
                            </div>
                            <!-- Fake cards -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                                <div class="bg-white/10 rounded-lg p-3">
                                    <div class="text-xs text-slate-400">Current Staff</div>
                                    <div class="text-xl font-bold text-white mt-1">45</div>
                                </div>
                                <div class="bg-white/10 rounded-lg p-3">
                                    <div class="text-xs text-slate-400">Required</div>
                                    <div class="text-xl font-bold text-white mt-1">52</div>
                                </div>
                                <div class="bg-white/10 rounded-lg p-3">
                                    <div class="text-xs text-slate-400">WISN Ratio</div>
                                    <div class="text-xl font-bold text-amber-400 mt-1">0.87</div>
                                </div>
                                <div class="bg-white/10 rounded-lg p-3">
                                    <div class="text-xs text-slate-400">Departments</div>
                                    <div class="text-xl font-bold text-white mt-1">4</div>
                                </div>
                            </div>
                            <!-- Fake chart bars -->
                            <div class="flex items-end gap-2 h-24">
                                <div class="flex-1 bg-gradient-to-t from-blue-600 to-blue-400 rounded-t-md" style="height: 60%"></div>
                                <div class="flex-1 bg-gradient-to-t from-red-600 to-red-400 rounded-t-md" style="height: 85%"></div>
                                <div class="flex-1 bg-gradient-to-t from-amber-600 to-amber-400 rounded-t-md" style="height: 70%"></div>
                                <div class="flex-1 bg-gradient-to-t from-green-600 to-green-400 rounded-t-md" style="height: 95%"></div>
                            </div>
                            <div class="flex justify-between mt-2 text-[10px] text-slate-500">
                                <span>ICU</span>
                                <span>Emergency</span>
                                <span>Medical</span>
                                <span>Surgical</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-24">
                <div class="text-center mb-12">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">How WISN Staffing Works</h2>
                    <p class="text-gray-500 mt-2">Four steps to evidence-based nurse staffing</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Step 1 -->
                    <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 border border-white/80 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819" />
                            </svg>
                        </div>
                        <div class="text-xs font-bold text-blue-600 mb-1">Step 1</div>
                        <h3 class="font-bold text-gray-900 mb-2">Add Departments</h3>
                        <p class="text-sm text-gray-500">Register your hospital departments with nurse headcount and working time parameters.</p>
                    </div>
                    <!-- Step 2 -->
                    <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 border border-white/80 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                        <div class="text-xs font-bold text-indigo-600 mb-1">Step 2</div>
                        <h3 class="font-bold text-gray-900 mb-2">Log Activities</h3>
                        <p class="text-sm text-gray-500">Record all nursing tasks — direct care, support duties, and additional responsibilities.</p>
                    </div>
                    <!-- Step 3 -->
                    <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 border border-white/80 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                        </div>
                        <div class="text-xs font-bold text-purple-600 mb-1">Step 3</div>
                        <h3 class="font-bold text-gray-900 mb-2">Auto Calculate</h3>
                        <p class="text-sm text-gray-500">WISN formulas compute required staff, CAF, AAF, and ratios automatically.</p>
                    </div>
                    <!-- Step 4 -->
                    <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 border border-white/80 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <div class="text-xs font-bold text-teal-600 mb-1">Step 4</div>
                        <h3 class="font-bold text-gray-900 mb-2">View & Export</h3>
                        <p class="text-sm text-gray-500">See interactive charts on the dashboard and download PDF reports for administration.</p>
                    </div>
                </div>
            </div>

            <!-- Stats / Trust Section -->
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-24">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-8 sm:p-12 text-center shadow-xl shadow-blue-500/20">
                    <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">Built on WHO WISN Methodology</h2>
                    <p class="text-blue-100 max-w-2xl mx-auto mb-8">
                        The Workload Indicators of Staffing Need (WISN) method is used by health ministries in over 60 countries
                        to make evidence-based staffing decisions.
                    </p>
                    <div class="grid grid-cols-3 gap-6 max-w-lg mx-auto">
                        <div>
                            <div class="text-3xl font-extrabold text-white">60+</div>
                            <div class="text-xs text-blue-200 mt-1">Countries</div>
                        </div>
                        <div>
                            <div class="text-3xl font-extrabold text-white">WHO</div>
                            <div class="text-xs text-blue-200 mt-1">Standard</div>
                        </div>
                        <div>
                            <div class="text-3xl font-extrabold text-white">1.0</div>
                            <div class="text-xs text-blue-200 mt-1">Target Ratio</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="relative z-10 border-t border-gray-200 bg-white/50 backdrop-blur-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                                    <line x1="32" y1="26" x2="32" y2="42" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                                    <line x1="24" y1="34" x2="40" y2="34" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-700">WISN Staffing Tool</span>
                        </div>
                        <p class="text-xs text-gray-400">
                            Nursing Workforce Planning for Nepalese Hospitals &bull; WHO WISN Methodology
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
