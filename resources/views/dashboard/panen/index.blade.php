<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div x-data="dashboardPanen()" class="space-y-4">
        
        <!-- Loading State -->
        <div x-show="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
        </div>

        <!-- Main Content -->
        <div x-show="!loading" style="display: none;" x-transition>
            
            <!-- Compact Header -->
            <div class="bg-white border-b border-gray-200 rounded-lg shadow-sm p-4 mb-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h1 class="text-lg font-bold text-gray-800">Dashboard Panen</h1>
                        <p class="text-sm text-gray-500" x-show="data.dateRange" x-text="data.dateRange ? `${data.dateRange.start} - ${data.dateRange.end}` : ''"></p>
                    </div>
                    
                    <!-- Compact Date Filter -->
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="date" x-model="filters.start_date" class="text-xs border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-blue-500">
                        <span class="text-gray-400">-</span>
                        <input type="date" x-model="filters.end_date" class="text-xs border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-blue-500">
                        <button @click="setToday()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded text-xs font-medium transition-colors">Hari Ini</button>
                        <button @click="applyFilters()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-medium transition-colors">Apply</button>
                    </div>
                </div>
            </div>

            <!-- Grand Total Strip -->
            <div class="bg-slate-800 rounded-lg shadow-md p-3 mb-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-slate-400 text-xs font-semibold uppercase">Grand Total</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-6">
                        <div class="text-center">
                            <p class="text-slate-400 text-[10px] uppercase">Total Rit</p>
                            <p class="text-white text-xl font-bold" x-text="data.grandTotal?.total_sj || 0"></p>
                        </div>
                        <div class="text-center">
                            <p class="text-slate-400 text-[10px] uppercase">Tonase</p>
                            <p class="text-white text-xl font-bold"><span x-text="formatTon(data.grandTotal?.total_netto || 0)"></span> <span class="text-xs font-normal text-slate-400">ton</span></p>
                        </div>
                        <div class="text-center">
                            <p class="text-slate-400 text-[10px] uppercase">Selesai</p>
                            <p class="text-white text-xl font-bold" x-text="data.grandTotal?.sudah_timbang || 0"></p>
                        </div>
                        <div class="text-center">
                            <p class="text-slate-400 text-[10px] uppercase">On Route</p>
                            <p class="text-white text-xl font-bold" x-text="data.grandTotal?.pending_timbangan || 0"></p>
                        </div>
                        <div class="text-center">
                            <p class="text-slate-400 text-[10px] uppercase">Avg Deload</p>
                            <p class="text-white text-sm font-semibold" x-text="formatDuration(data.grandTotal?.avg_durasi_deload)"></p>
                        </div>
                        <div class="text-center">
                            <p class="text-slate-400 text-[10px] uppercase">Avg POS-Timbang</p>
                            <p class="text-white text-sm font-semibold" x-text="formatDuration(data.grandTotal?.avg_durasi_pos_timbang)"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Company Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="company in data.companies" :key="company.companycode">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow overflow-hidden">
                        <!-- Company Header -->
                        <div class="bg-slate-700 px-4 py-2">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-white" x-text="company.companycode"></h3>
                                <span class="bg-white bg-opacity-20 text-white text-xs font-bold px-2 py-0.5 rounded" x-text="company.percentage_done + '%'"></span>
                            </div>
                            <p class="text-slate-300 text-[10px] truncate" x-text="company.companyname"></p>
                        </div>

                        <!-- Stats Content -->
                        <div class="p-3 space-y-3">
                            
                            <!-- Rit Section -->
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div class="bg-blue-50 rounded p-2 border border-blue-100">
                                    <p class="text-[10px] text-blue-600 uppercase font-semibold">Total Rit</p>
                                    <p class="text-2xl font-bold text-blue-700" x-text="company.total_sj"></p>
                                </div>
                                <div class="bg-green-50 rounded p-2 border border-green-100">
                                    <p class="text-[10px] text-green-600 uppercase font-semibold">Selesai</p>
                                    <p class="text-2xl font-bold text-green-700" x-text="company.sudah_timbang"></p>
                                </div>
                                <div class="bg-amber-50 rounded p-2 border border-amber-100">
                                    <p class="text-[10px] text-amber-600 uppercase font-semibold">On Route</p>
                                    <p class="text-2xl font-bold text-amber-700" x-text="company.pending_timbangan"></p>
                                </div>
                            </div>

                            <!-- Tonase -->
                            <div class="bg-gray-50 rounded p-2 border border-gray-100">
                                <div class="flex items-center justify-between">
                                    <p class="text-[10px] text-gray-500 uppercase font-semibold">Total Tonase (Netto)</p>
                                    <p class="text-lg font-bold text-gray-800"><span x-text="formatTon(company.total_netto)"></span> <span class="text-xs font-normal text-gray-500">ton</span></p>
                                </div>
                            </div>

                            <!-- Durations -->
                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100">
                                <div class="text-center">
                                    <p class="text-[10px] text-gray-500 uppercase">Avg Deload</p>
                                    <p class="text-sm font-semibold text-gray-700" x-text="formatDuration(company.avg_durasi_deload)"></p>
                                </div>
                                <div class="text-center">
                                    <p class="text-[10px] text-gray-500 uppercase">Avg POS-Timbang</p>
                                    <p class="text-sm font-semibold text-gray-700" x-text="formatDuration(company.avg_durasi_pos_timbang)"></p>
                                </div>
                            </div>

                        </div>
                    </div>
                </template>
            </div>

            <!-- No Company Data State -->
            <template x-if="!data.companies || data.companies.length === 0">
                <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-700 mb-1">Tidak Ada Data</h3>
                    <p class="text-sm text-gray-500">Belum ada data untuk periode yang dipilih</p>
                </div>
            </template>

        </div>
    </div>

    <script>
    function dashboardPanen() {
        return {
            loading: true,
            data: {
                companies: [],
                grandTotal: {},
                dateRange: null,
                isSingleDay: true
            },
            filters: {
                start_date: new Date().toISOString().split('T')[0],
                end_date: new Date().toISOString().split('T')[0]
            },

            async loadData() {
                this.loading = true;
                try {
                    const params = new URLSearchParams(this.filters);
                    const response = await fetch(`{{ route('dashboard.panen.data') }}?${params}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        this.data = result.data;
                    } else {
                        console.error(result.message);
                    }
                } catch (error) {
                    console.error('Load data error:', error);
                } finally {
                    this.loading = false;
                }
            },

            init() {
                this.loadData();
                setInterval(() => {
                    this.loadData();
                }, 300000);
            },

            applyFilters() {
                this.loadData();
            },

            setToday() {
                const today = new Date().toISOString().split('T')[0];
                this.filters.start_date = today;
                this.filters.end_date = today;
                this.applyFilters();
            },

            formatTon(kg) {
                if (!kg) return '0.00';
                const ton = parseFloat(kg) / 1000;
                return ton.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            formatDuration(minutes) {
                if (!minutes || minutes === null) return '-';
                const mins = parseFloat(minutes);
                if (mins < 60) return Math.round(mins) + 'm';
                const hours = Math.floor(mins / 60);
                const remainingMins = Math.round(mins % 60);
                return `${hours}h ${remainingMins}m`;
            }
        }
    }
    </script>
</x-layout>