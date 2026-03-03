{{-- resources/views/components/sidebar.blade.php --}}
@props(['navigationMenus' => null, 'companyName' => null])

@php
    $navigationMenus = $navigationMenus ?? collect([]);
    $compName = $companyName ?? session('companyname', 'Default Company');

    $isActive = function ($routeName) {
        if (!$routeName) {
            return false;
        }
        try {
            $currentRoute = request()->route()->getName();
            if ($currentRoute === $routeName) {
                return true;
            }
            if (str_starts_with($currentRoute, $routeName . '.')) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    };

    $hasActiveChild = function ($children) use ($isActive, &$hasActiveChild) {
        if (!isset($children) || !is_array($children)) {
            return false;
        }
        foreach ($children as $child) {
            if (isset($child['route']) && $isActive($child['route'])) {
                return true;
            }
            if (isset($child['children']) && $hasActiveChild($child['children'])) {
                return true;
            }
        }
        return false;
    };

    $safeRoute = function ($routeName) {
        try {
            return route($routeName);
        } catch (\Exception $e) {
            return '#';
        }
    };

    $iconPaths = [
        'database' =>
            'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4',
        'edit' =>
            'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        'report' =>
            'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'dashboard' =>
            'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z',
        'settings' =>
            'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
        'finance' =>
            'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z',
        'building' =>
            'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        'users' =>
            'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        'wrench' =>
            'M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z',
        'megaphone' =>
            'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
        'grid' =>
            'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
    ];

    $getIconPath = function ($icon) use ($iconPaths) {
        return $iconPaths[$icon] ?? $iconPaths['grid'];
    };
@endphp

<!-- Sidebar Container - LIGHT THEME -->
<div x-data="awsSidebar()" x-init="init()"
    class="fixed inset-y-0 left-0 z-50 flex flex-col bg-slate-50 border-r border-slate-200 transition-all duration-300"
    :class="[
        $store.sidebar.isMinimized ? 'w-16' : 'w-60',
        $store.sidebar.mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]">
    <!-- Header -->
    <div class="shrink-0 border-b border-slate-200 bg-white"
        :class="$store.sidebar.isMinimized ? 'px-2 py-3' : 'px-3 py-3'">
        <a href="{{ route('home') }}" class="flex items-center"
            :class="$store.sidebar.isMinimized ? 'justify-center' : 'gap-2.5'">
            <div
                class="w-9 h-9 bg-white rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm border border-slate-200">
                <img class="h-6 w-6 object-contain" src="{{ asset('img/Logo-1.png') }}" alt="Logo">
            </div>
            <div x-show="!$store.sidebar.isMinimized" x-cloak class="min-w-0 overflow-hidden">
                <div class="text-slate-800 text-[13px] font-semibold truncate">Sungai Budi Group</div>
                <div class="text-slate-500 text-[10px] truncate">Sugarcane Management</div>
            </div>
        </a>
    </div>

    <!-- Search -->
    <div x-show="!$store.sidebar.isMinimized" x-cloak class="px-2 py-2 border-b border-slate-200 bg-white">
        <div class="relative">
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" x-model="search" x-ref="searchInput"
                @keydown.slash.window.prevent="$refs.searchInput.focus()"
                @keydown.escape="search = ''; $refs.searchInput.blur()" placeholder="Search menu..."
                class="w-full bg-slate-50 border border-slate-200 rounded-md text-[12px] pl-8 pr-8 py-1.5 text-slate-700 placeholder-slate-400 focus:outline-none focus:border-slate-300 focus:ring-1 focus:ring-slate-300 focus:bg-white">
            <kbd x-show="!search"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 bg-white px-1.5 py-0.5 rounded border border-slate-200">/</kbd>
            <button x-show="search" x-cloak @click="search = ''"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto sidebar-scroll py-2 bg-slate-50"
        :class="$store.sidebar.isMinimized ? 'px-1' : 'px-2'">
        @foreach ($navigationMenus as $sectionIdx => $menu)
            @php
                $menuHasActiveChild = $hasActiveChild($menu['children'] ?? []);
                $iconPath = $getIconPath($menu['icon'] ?? 'grid');
            @endphp

            <div x-data="{ open: {{ $menuHasActiveChild ? 'true' : 'false' }} }" x-show="filterSection('{{ addslashes($menu['name']) }}', {{ $sectionIdx }})"
                class="mb-1">
                <!-- Section Header -->
                <button @click="$store.sidebar.isMinimized ? null : (open = !open)"
                    class="w-full flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wider transition-colors rounded-md"
                    :class="$store.sidebar.isMinimized ?
                        'justify-center px-2 py-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100' :
                        'justify-between px-2 py-1.5 text-slate-500 hover:text-slate-700'"
                    :title="$store.sidebar.isMinimized ? '{{ $menu['name'] }}' : ''">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="{{ $iconPath }}" />
                        </svg>
                        <span x-show="!$store.sidebar.isMinimized" x-cloak>{{ $menu['name'] }}</span>
                    </div>
                    <svg x-show="!$store.sidebar.isMinimized" x-cloak
                        class="w-3 h-3 transition-transform duration-200 text-slate-400"
                        :class="open ? 'rotate-0' : '-rotate-90'" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Section Items -->
                <div x-show="open && !$store.sidebar.isMinimized" x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1">
                    @if (isset($menu['children']) && count($menu['children']) > 0)
                        @foreach ($menu['children'] as $itemIdx => $child)
                            @php
                                $isChildGroup = !isset($child['route']) && isset($child['children']);
                                $childIsActive = isset($child['route']) && $isActive($child['route']);
                                $childHasActiveChild = $hasActiveChild($child['children'] ?? []);
                                $flyoutId = $sectionIdx . '-' . $itemIdx;
                            @endphp

                            <div x-show="filterItem('{{ addslashes($child['name']) }}', {{ $sectionIdx }}, {{ $itemIdx }})"
                                class="relative">
                                @if ($isChildGroup)
                                    {{-- Item with flyout children - CLICK BASED --}}
                                    <button
                                        @click="toggleFlyout('{{ $flyoutId }}', $event, {{ json_encode($child['children']) }})"
                                        class="flex items-center justify-between w-full px-2 py-1.5 rounded-md text-[12px] transition-all duration-150 group {{ $childHasActiveChild ? 'bg-slate-200/70 text-slate-800' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-800' }}">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="w-1 h-1 rounded-full {{ $childHasActiveChild ? 'bg-emerald-500' : 'bg-slate-400 group-hover:bg-slate-500' }}"></span>
                                            <span>{{ $child['name'] }}</span>
                                        </div>
                                        <svg class="w-3 h-3 transition-transform {{ $childHasActiveChild ? 'text-slate-600' : 'text-slate-400 group-hover:text-slate-500' }}"
                                            :class="activeFlyout === '{{ $flyoutId }}' ? 'rotate-90' : ''"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                @else
                                    {{-- Direct link item --}}
                                    @if (isset($child['route']))
                                        <a href="{{ $safeRoute($child['route']) }}"
                                            class="flex items-center gap-2 px-2 py-1.5 rounded-md text-[12px] transition-all duration-150 group {{ $childIsActive ? 'bg-slate-200/70 text-slate-800 font-medium' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-800' }}"
                                            @click="$store.sidebar.closeMobile()">
                                            <span
                                                class="w-1 h-1 rounded-full {{ $childIsActive ? 'bg-emerald-500' : 'bg-slate-400 group-hover:bg-slate-500' }}"></span>
                                            <span>{{ $child['name'] }}</span>
                                        </a>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endforeach
    </nav>

    <!-- Footer -->
    <div class="hidden lg:block border-t border-slate-200 p-2 bg-white">
        <button @click="$store.sidebar.toggle()"
            class="w-full flex items-center gap-2 px-2 py-1.5 rounded-md text-[11px] text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors"
            :class="$store.sidebar.isMinimized ? 'justify-center' : ''">
            <svg class="w-4 h-4 transition-transform duration-200"
                :class="$store.sidebar.isMinimized ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
            <span x-show="!$store.sidebar.isMinimized" x-cloak>Collapse</span>
        </button>
    </div>

    <!-- Mobile Close -->
    <button @click="$store.sidebar.closeMobile()"
        class="lg:hidden absolute top-3 right-3 p-1.5 rounded-md text-slate-500 hover:text-slate-700 hover:bg-slate-200 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <!-- Single Flyout Panel (rendered once, content dynamic) -->
    <div x-show="activeFlyout !== null" x-cloak x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed bg-white border border-slate-200 rounded-lg shadow-lg py-1 min-w-[180px] max-w-[220px] z-[60]"
        :style="flyoutStyle">
        <template x-for="(item, idx) in flyoutItems" :key="idx">
            <a :href="item.url"
                class="flex items-center gap-2 px-3 py-2 text-[12px] transition-colors text-slate-600 hover:bg-slate-50 hover:text-slate-900"
                :class="item.active ? 'bg-slate-100 text-slate-900 font-medium' : ''"
                @click="activeFlyout = null; $store.sidebar.closeMobile()">
                <span class="w-1 h-1 rounded-full" :class="item.active ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                <span class="truncate" x-text="item.name"></span>
            </a>
        </template>
    </div>
</div>

<style>
    .sidebar-scroll::-webkit-scrollbar {
        width: 4px;
    }

    .sidebar-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 2px;
    }

    .sidebar-scroll::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<script type="application/json" id="sidebar-menu-data">
@json($navigationMenus)
</script>

<script>
    function awsSidebar() {
        return {
            search: '',
            menuData: [],
            activeFlyout: null,
            flyoutStyle: '',
            flyoutItems: [],

            init() {
                try {
                    this.menuData = JSON.parse(document.getElementById('sidebar-menu-data').textContent);
                } catch (e) {
                    this.menuData = [];
                }

                this.$watch('$store.sidebar.isMinimized', (val) => {
                    if (val) this.activeFlyout = null;
                });
            },

            toggleFlyout(id, event, children) {
                // Kalau klik yang sama → tutup
                if (this.activeFlyout === id) {
                    this.activeFlyout = null;
                    this.flyoutItems = [];
                    return;
                }

                // Kalau klik yang beda → tutup yang lama, buka yang baru
                const rect = event.currentTarget.getBoundingClientRect();
                const sidebarWidth = this.$store.sidebar.isMinimized ? 64 : 240;

                let topPos = rect.top;
                const flyoutHeight = children.length * 36 + 16;
                if (topPos + flyoutHeight > window.innerHeight) {
                    topPos = window.innerHeight - flyoutHeight - 20;
                }

                this.flyoutStyle = `top: ${topPos}px; left: ${sidebarWidth + 4}px;`;

                // Build flyout items with URLs
                this.flyoutItems = children.filter(c => c.route).map(c => ({
                    name: c.name,
                    url: this.getRouteUrl(c.route),
                    active: this.isCurrentRoute(c.route)
                }));

                this.activeFlyout = id;
            },

            getRouteUrl(routeName) {
                // Route URLs are pre-generated in the data
                const routeMap = @json(collect($navigationMenus)->flatMap(function ($menu) use ($safeRoute) {
                            return collect($menu['children'] ?? [])->flatMap(function ($child) use ($safeRoute) {
                                if (isset($child['children'])) {
                                    return collect($child['children'])->mapWithKeys(function ($gc) use ($safeRoute) {
                                        return isset($gc['route']) ? [$gc['route'] => $safeRoute($gc['route'])] : [];
                                    });
                                }
                                return [];
                            });
                        })->toArray());
                return routeMap[routeName] || '#';
            },

            isCurrentRoute(routeName) {
                const current = '{{ request()->route()?->getName() ?? '' }}';
                return current === routeName || current.startsWith(routeName + '.');
            },

            filterSection(sectionName, sectionIdx) {
                if (!this.search) return true;
                const q = this.search.toLowerCase();
                if (sectionName.toLowerCase().includes(q)) return true;

                const section = this.menuData[sectionIdx];
                if (!section?.children) return false;

                return section.children.some(item => {
                    if (item.name.toLowerCase().includes(q)) return true;
                    if (item.children) {
                        return item.children.some(c => c.name.toLowerCase().includes(q));
                    }
                    return false;
                });
            },

            filterItem(itemName, sectionIdx, itemIdx) {
                if (!this.search) return true;
                const q = this.search.toLowerCase();
                if (itemName.toLowerCase().includes(q)) return true;

                const item = this.menuData[sectionIdx]?.children?.[itemIdx];
                if (item?.children) {
                    return item.children.some(c => c.name.toLowerCase().includes(q));
                }
                return false;
            }
        }
    }
</script>
