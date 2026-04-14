<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->user()->userid ?? '' }}">
    <meta name="app-url" content="{{ url('/') }}">
    <meta name="current-username" content="{{ Auth::user()->name ?? '' }}">
    <meta name="theme-color" content="#3b82f6">

    <!-- Preload font awesome untuk prevent icon flash -->
    <link rel="preload" href="{{ asset('asset/font-awesome-6.5.1-all.min.css') }}" as="style">
    <link rel="preload" href="{{ asset('asset/inter.css') }}" as="style">

    <!-- Dynamic Manifest Link -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="SB Tebu App">
    <link rel="apple-touch-icon" href="{{ asset('img/icon-sb-tebu-circle.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Critical CSS inline - Load FIRST before anything -->
    <style>
        body {
            visibility: hidden;
            opacity: 0;
        }

        .layout-container {
            display: flex;
            min-height: 100vh;
        }

        /* Mobile: No sidebar space reserved */
        .sidebar-wrapper {
            width: 0;
            flex-shrink: 0;
            transition: width 0.3s ease;
        }

        /* Desktop: Reserve sidebar space - FIXED WIDTH */
        @media (min-width: 1024px) {
            .sidebar-wrapper {
                width: 240px;  /* ✅ Changed from 18rem (288px) */
            }

            .sidebar-minimized .sidebar-wrapper {
                width: 64px;   /* ✅ Changed from 4rem to match w-16 */
            }
        }

        .main-wrapper {
            flex: 1;
            min-width: 0;
            /* ❌ REMOVE: margin-left: 0; dan max-width - tidak perlu */
        }

        body.ready {
            visibility: visible;
            opacity: 1;
            transition: opacity 0.15s ease;
        }

        [x-cloak] {
            display: none !important;
        }

        @media print {
            .sidebar-wrapper,
            aside,
            header,
            .header,
            nav,
            footer,
            .footer,
            .no-print,
            .print-hidden {
                display: none !important;
            }

            .main-wrapper {
                margin-left: 0 !important;
                width: 100% !important;
                flex: none !important;
            }

            .layout-container {
                display: block !important;
            }

            main {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }

            * {
                box-shadow: none !important;
                text-shadow: none !important;
            }
        }
    </style>

    <!-- Set initial state IMMEDIATELY -->
    <script>
        (function() {
            // Check localStorage first (client priority), then cookie (server fallback)
            const localState = localStorage.getItem('sidebar-minimized');
            const cookieState = document.cookie.match(/sidebar_minimized=([^;]+)/);
            const isMinimized = localState ? localState === 'true' : (cookieState ? cookieState[1] === 'true' : false);

            // Apply state to HTML element (only affects desktop)
            if (isMinimized && window.innerWidth >= 1024) {
                document.documentElement.classList.add('sidebar-minimized');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('asset/inter.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/font-awesome-6.5.1-all.min.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <script defer src="{{ asset('asset/alpinejs.min.js') }}"></script>
    <script src="{{ asset('asset/chart.js') }}"></script>
    <script src="{{ asset('asset/chartjs-plugin-datalabels@2.0.0.js') }}"></script>
    <script src="{{ asset('asset/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('asset/simple-datatables@9.0.3.js') }}"></script>

    <title>{{ $title }}</title>
    <x-slot:head></x-slot>
</head>

<body class="h-full bg-gray-50">
    <div class="layout-container" x-data="mainLayoutData()" x-init="init()">
        <!-- Sidebar -->
        <aside class="sidebar-wrapper" x-cloak>
            {{-- ✅ FIXED: Keep variable name as 'company' --}}
            <x-sidebar 
                :navigationMenus="$navigationMenus ?? collect([])" 
                :companyName="$companyName ?? session('companyname', 'Default Company')"
            />
        </aside>

        <!-- Main Content Area -->
        <div class="main-wrapper flex flex-col min-h-screen">

            <!-- Header -->
            <x-header>{{ $title }}
                <x-slot:navhint>
                    <x-nav-hint>
                        {{ $navbar ?? 'Not Defined' }}
                        @isset($nav)
                            <x-slot:secondarySlot>
                                {{ $nav }}
                            </x-slot:secondarySlot>
                        @endisset
                        @isset($navnav)
                            @if (isset($navnav) && isset($routeName))
                                <x-slot:routeName>
                                    {{ $routeName }}
                                </x-slot:routeName>
                                <x-slot:tertiarySlot>
                                    {{ $navnav }}
                                </x-slot:tertiarySlot>
                            @else
                                <x-slot:tertiarySlot>
                                    {{ $navnav }}
                                </x-slot:tertiarySlot>
                            @endif
                        @endisset
                    </x-nav-hint>
                </x-slot:navhint>
            </x-header>

            <!-- Main Content -->
            <main class="bg-gray-200 flex-1">
                {{ $hero ?? null }}
                <div class="px-2 py-3 sm:px-3 lg:px-4">
                    @include('errorfile')

                    {{ $slot }}
                </div>
            </main>

            <!-- Footer -->
            <x-footer></x-footer>
        </div>

        <!-- Mobile sidebar overlay -->
        <div x-show="$store.sidebar.mobileOpen" @click="$store.sidebar.closeMobile()"
            x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-gray-900 bg-opacity-50 lg:hidden">
        </div>
    </div>

    <!-- Global Company Modal -->
    {{-- ✅ FIXED: Keep as $company for backward compatibility --}}
    @if (isset($company) && count($company) > 0)
        <x-company-modal :companies="$company" />
    @endif

    <script>
        // ============================================
        // COOKIE HELPER FUNCTIONS
        // ============================================
        function setCookie(name, value, days = 365) {
            const expires = new Date(Date.now() + days * 864e5).toUTCString();
            document.cookie = `${name}=${value}; expires=${expires}; path=/; SameSite=Lax`;
        }

        function getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? match[2] : null;
        }

        // ============================================
        // MAIN LAYOUT COMPONENT
        // ============================================
        function mainLayoutData() {
            return {
                init() {
                    // Desktop only: Get initial state from localStorage or cookie
                    if (window.innerWidth >= 1024) {
                        const localState = localStorage.getItem('sidebar-minimized');
                        const cookieState = getCookie('sidebar_minimized');

                        const isMinimized = localState !== null ?
                            localState === 'true' :
                            (cookieState === 'true');

                        // Apply state to HTML
                        if (isMinimized) {
                            document.documentElement.classList.add('sidebar-minimized');
                        } else {
                            document.documentElement.classList.remove('sidebar-minimized');
                        }

                        // Sync with Alpine store
                        this.$nextTick(() => {
                            if (Alpine.store('sidebar')) {
                                Alpine.store('sidebar').isMinimized = isMinimized;
                            }
                        });
                    }

                    // Listen for toggle events
                    window.addEventListener('sidebar-toggle', (e) => {
                        if (window.innerWidth >= 1024) {
                            // Update HTML class
                            if (e.detail.minimized) {
                                document.documentElement.classList.add('sidebar-minimized');
                            } else {
                                document.documentElement.classList.remove('sidebar-minimized');
                            }
                        }
                    });
                }
            }
        }

        // ============================================
        // GLOBAL ALPINE STORE
        // ============================================
        document.addEventListener('alpine:init', () => {
            Alpine.store('sidebar', {
                isMinimized: false,
                mobileOpen: false,

                init() {
                    // Initialize from storage (desktop only)
                    if (window.innerWidth >= 1024) {
                        const localState = localStorage.getItem('sidebar-minimized');
                        const cookieState = getCookie('sidebar_minimized');
                        this.isMinimized = localState !== null ?
                            localState === 'true' :
                            (cookieState === 'true');
                    }

                    // Responsive resize handler
                    window.addEventListener('resize', () => {
                        if (window.innerWidth < 1024) {
                            // Mobile: Reset minimized state
                            this.isMinimized = false;
                            document.documentElement.classList.remove('sidebar-minimized');
                        } else {
                            // Desktop: Restore state from storage
                            const localState = localStorage.getItem('sidebar-minimized');
                            const cookieState = getCookie('sidebar_minimized');
                            this.isMinimized = localState !== null ?
                                localState === 'true' :
                                (cookieState === 'true');

                            // Apply to HTML
                            if (this.isMinimized) {
                                document.documentElement.classList.add('sidebar-minimized');
                            } else {
                                document.documentElement.classList.remove('sidebar-minimized');
                            }

                            // Close mobile sidebar if open
                            this.mobileOpen = false;
                        }
                    });
                },

                // Desktop toggle (collapse/expand)
                toggle() {
                    if (window.innerWidth >= 1024) {
                        this.isMinimized = !this.isMinimized;

                        // Save to both localStorage and cookie
                        localStorage.setItem('sidebar-minimized', this.isMinimized);
                        setCookie('sidebar_minimized', this.isMinimized);

                        // Update HTML class
                        if (this.isMinimized) {
                            document.documentElement.classList.add('sidebar-minimized');
                        } else {
                            document.documentElement.classList.remove('sidebar-minimized');
                        }

                        // Dispatch event
                        window.dispatchEvent(new CustomEvent('sidebar-toggle', {
                            detail: {
                                minimized: this.isMinimized
                            }
                        }));
                    }
                },

                // Mobile toggle (show/hide)
                toggleMobile() {
                    if (window.innerWidth < 1024) {
                        this.mobileOpen = !this.mobileOpen;
                    }
                },

                // Mobile close
                closeMobile() {
                    this.mobileOpen = false;
                },

                // Mobile open
                openMobile() {
                    if (window.innerWidth < 1024) {
                        this.mobileOpen = true;
                    }
                }
            });

            // Initialize store
            Alpine.store('sidebar').init();
        });

        // Show body after everything loads
        window.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('ready');
        });

        // ============================================
        // SERVICE WORKER MANAGEMENT
        // ============================================

        // Clear cache on logout
        document.addEventListener('DOMContentLoaded', function() {
            // Intercept logout forms
            const logoutForms = document.querySelectorAll('form[action*="logout"]');
            logoutForms.forEach(form => {
                form.addEventListener('submit', function() {
                    // Clear SW cache before logout
                    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                        navigator.serviceWorker.controller.postMessage({
                            type: 'CLEAR_CACHE'
                        });
                    }
                    // Also clear browser caches
                    if ('caches' in window) {
                        caches.keys().then(names => {
                            names.forEach(name => caches.delete(name));
                        });
                    }
                });
            });

            // Handle login success
            if (window.location.pathname.includes('/dashboard') &&
                document.referrer && document.referrer.includes('/login')) {
                // User just logged in, update SW
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.ready.then(registration => {
                        registration.update();
                    });
                }
            }
        });

        // Service Worker Registration with version control
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                // Add timestamp to force update in development
                const swUrl = '{{ asset('sw.js') }}';

                navigator.serviceWorker.register(swUrl)
                    .then(function(registration) {
                        console.log('ServiceWorker registered:', registration.scope);

                        // Check for updates periodically
                        @if (config('app.env') === 'local')
                            // Development: Check every minute
                            setInterval(() => {
                                registration.update();
                            }, 60 * 1000);
                        @else
                            // Production: Check every 30 minutes
                            setInterval(() => {
                                registration.update();
                            }, 30 * 60 * 1000);
                        @endif

                        // Handle updates
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    console.log('New service worker available');
                                    // Optionally show update notification
                                    if (confirm('Update tersedia! Refresh halaman?')) {
                                        newWorker.postMessage({
                                            type: 'SKIP_WAITING'
                                        });
                                        window.location.reload();
                                    }
                                }
                            });
                        });
                    })
                    .catch(function(err) {
                        console.error('ServiceWorker registration failed:', err);
                    });

                // Handle controller change
                navigator.serviceWorker.addEventListener('controllerchange', () => {
                    window.location.reload();
                });
            });
        }

        // Helper function to clear all caches (for debugging)
        window.clearAllCache = function() {
            console.log('Clearing all caches...');

            // Clear Service Worker
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistrations().then(function(registrations) {
                    for (let registration of registrations) {
                        registration.unregister();
                    }
                });
            }

            // Clear Caches
            if ('caches' in window) {
                caches.keys().then(names => {
                    names.forEach(name => {
                        caches.delete(name);
                    });
                });
            }

            // Clear localStorage
            localStorage.clear();

            // Clear sessionStorage
            sessionStorage.clear();

            console.log('All caches cleared. Reloading...');
            setTimeout(() => {
                window.location.reload(true);
            }, 500);
        };

        // Debug helper
        window.swInfo = function() {
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.ready.then(reg => {
                    console.log('SW Ready:', reg);
                    console.log('SW Active:', reg.active);
                    console.log('SW Scope:', reg.scope);
                });

                caches.keys().then(names => {
                    console.log('Active Caches:', names);
                });
            }
        };
    </script>

    <x-sprite-svg />
    @stack('scripts')

    <!-- Global Loading Indicator -->
    <x-global-loader />

</body>
<x-script></x-script>
<x-style></x-style>

</html>