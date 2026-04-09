{{--resources\views\components\header.blade.php--}}
<header class="bg-green-700 shadow-sm border-b border-gray-200 sticky top-0 z-40">
    <div class="flex h-14 items-center justify-between px-4 sm:px-6 lg:px-8">
        <!-- Left: Desktop toggle button + Mobile menu button + Page title -->
        <div class="flex items-center space-x-4">
            <!-- Mobile Sidebar Toggle Button (Hamburger) -->
            <button @click="$store.sidebar.toggleMobile()" 
                    class="lg:hidden flex p-2 rounded-lg bg-gray-100 text-gray-900 hover:text-gray-900 hover:bg-gray-200 transition-colors">
                <span class="sr-only">Open sidebar</span>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            
            <!-- Desktop Sidebar Toggle Button (Collapse/Expand) -->
            <button @click="$store.sidebar.toggle()" 
                    class="hidden lg:flex p-2 rounded-lg bg-gray-100 text-gray-900 hover:text-gray-900 hover:bg-gray-200 transition-colors">
                <span class="sr-only">Toggle sidebar</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Page Title -->
            <h1 class="text-lg font-semibold text-white truncate">{{ $slot }}</h1>
        </div>

        <!-- Center: Company Info (hidden on mobile) - Clickable -->
        <div class="hidden md:flex items-center space-x-2 text-sm text-gray-600">
            <button @click="$dispatch('open-company-modal')" 
                    class="flex items-center space-x-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors group">
                <div class="flex items-center space-x-2">
                    <div class="h-2 w-2 bg-green-500 rounded-full"></div>
                    <div class="font-medium">{{ formatCompanyCode(session('companycode')) }}</div>
                    <span class="text-gray-400">•</span>
                    <span>{{ session('companyname') }}</span>
                </div>
                <svg class="h-4 w-4 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                </svg>
            </button>
        </div>

        <!-- Right: Chat + Notifications + User Profile -->
        <div class="flex items-center space-x-3">
            
            <!-- ✅ HEADER CHAT - Skip di homepage (sudah ada floating chat) -->
            @if(!request()->routeIs('home'))
                <x-header-chat />
            @endif
            
            <!-- ✅ NOTIFICATION DROPDOWN - No Permission Required (accessible to all authenticated users) -->
            <x-notification-dropdown />

            <!-- User Profile Dropdown -->
            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                @php
                    $name = Auth::user()->name;
                    $words = array_filter(explode(' ', trim($name)));
                    
                    if (count($words) === 0) {
                        $initials = '?';
                    } elseif (count($words) === 1) {
                        $initials = strtoupper(substr($words[0], 0, 1));
                    } else {
                        $initials = strtoupper(substr($words[0], 0, 1)) . 
                                    strtoupper(substr($words[1], 0, 1));
                    }
                    
                    $colors = [
                        'bg-slate-600', 'bg-gray-600', 'bg-zinc-600', 'bg-stone-600',
                        'bg-neutral-600', 'bg-slate-700', 'bg-gray-700', 'bg-zinc-700',
                    ];
                    $colorIndex = abs(crc32($name)) % count($colors);
                    $bgColor = $colors[$colorIndex];
                @endphp
                <button @click="open = !open" 
                        class="flex items-center space-x-2 p-1 rounded-full text-gray-500 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 transition-colors">
                    <div class="h-8 w-8 rounded-full ring-2 ring-gray-200 {{ $bgColor }} flex items-center justify-center">
                        <span class="text-white text-sm font-semibold">{{ $initials }}</span>
                    </div>
                    <div class="hidden md:block text-left">
                        <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                    </div>
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-lg bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                    
                    <!-- User Info -->
                    <div class="px-4 py-2 border-b border-gray-100">
                        <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                    </div>
                    
                    <!-- Company Info (Mobile) -->
                    <div class="md:hidden border-b border-gray-100">
                        <button @click="$dispatch('open-company-modal')" 
                                class="w-full text-left px-4 py-2 hover:bg-gray-50 transition-colors">
                            <div class="text-xs text-gray-600">
                                <div class="font-medium">{{ formatCompanyCode(session('companycode')) }}</div>
                                <div class="text-gray-500">{{ session('companyname') }}</div>
                            </div>
                        </button>
                    </div>

                    <!-- Change Company -->
                    <button @click="$dispatch('open-company-modal')" 
                            class="flex w-full items-center space-x-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span>Change Company</span>
                    </button>

                    <!-- Change Password -->
                    <a href="{{ route('change-password.show') }}"
                        class="flex w-full items-center space-x-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <span>Change Password</span>
                    </a>

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                class="flex w-full items-center space-x-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span>Sign out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Breadcrumb/Navigation hint -->
    @if(isset($navhint))
        <div class="border-t border-gray-200 bg-green-50 px-4 py-2 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    {{ $navhint }}
                </div>
                
                @if(isset($period) && $period)
                    <div class="text-right">
                        <div class="flex justify-end items-center space-x-2 text-sm text-gray-700">
                            <span class="text-xs text-gray-500">Monitoring Period:</span>
                            <span class="font-medium">{{ $period }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</header>