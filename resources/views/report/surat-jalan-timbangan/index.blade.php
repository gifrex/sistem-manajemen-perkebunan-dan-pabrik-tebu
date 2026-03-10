<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div x-data="suratJalanReport()" x-init="loadData()" class="space-y-5">
        
        <!-- Loading State -->
        <div x-show="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>

        <!-- Main Content -->
        <div x-show="!loading" style="display: none;" x-transition>
            
            <!-- Header Title -->
            <div class="bg-white border-b-2 border-gray-200 rounded-xl shadow-sm p-5 mb-5">
                <h2 class="text-xl font-semibold text-gray-800">
                    Data Surat Jalan & Timbangan: 
                    <span class="font-bold text-gray-900" x-text="getCurrentCompanyTitle()"></span>
                </h2>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-blue-500 transform hover:scale-105 transition-transform hover:shadow-xl">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Total Surat Jalan</p>
                            <p class="text-4xl font-bold text-gray-900" x-text="data.summary?.total_sj || 0"></p>
                        </div>
                        <div class="bg-blue-100 rounded-lg px-3 py-1.5">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-xs text-gray-600 font-medium">
                        Surat jalan yang dicetak
                        <span x-show="data.dateRange" class="block text-blue-600 font-semibold mt-1" x-text="data.dateRange ? `(${data.dateRange.start} - ${data.dateRange.end})` : ''"></span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-green-500 transform hover:scale-105 transition-transform hover:shadow-xl">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Total Tonase (Netto)</p>
                            <p class="text-4xl font-bold text-gray-900" x-text="formatTon(data.summary?.total_netto || 0)"></p>
                        </div>
                        <div class="bg-green-100 rounded-lg px-3 py-1.5">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-xs text-gray-600 font-medium">
                        Ton
                        <span x-show="data.dateRange" class="block text-green-600 font-semibold mt-1" x-text="data.dateRange ? `(${data.dateRange.start} - ${data.dateRange.end})` : ''"></span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-amber-500 transform hover:scale-105 transition-transform hover:shadow-xl">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Pending Timbangan</p>
                            <p class="text-4xl font-bold text-amber-600" x-text="data.summary?.pending_timbangan || 0"></p>
                        </div>
                        <div class="bg-amber-100 rounded-lg px-3 py-1.5">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-xs text-gray-600 font-medium">
                        Dalam perjalanan
                        <span x-show="data.dateRange" class="block text-amber-600 font-semibold mt-1" x-text="data.dateRange ? `(${data.dateRange.start} - ${data.dateRange.end})` : ''"></span>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white rounded-xl shadow-md p-4 mb-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Filters & Date Range</h3>
                    <div class="flex gap-2">
                        <button @click="applyFilters()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-1.5 rounded-lg text-xs font-medium transition-colors">
                            Apply Filters
                        </button>
                        <button @click="resetFilters()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-1.5 rounded-lg text-xs font-medium transition-colors">
                            Reset
                        </button>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-3 mb-3 pb-3 border-b border-gray-200">
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Group</label>
                        <select x-model="filters.group" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Current Company</option>
                            <option value="all-tbl">All TBL (TBL1/2/3)</option>
                            <option value="all-divisi">All Divisi</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Tanggal Mulai</label>
                        <input type="date" x-model="filters.start_date" :disabled="dateRangeLocked" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed">
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Tanggal Akhir</label>
                        <input type="date" x-model="filters.end_date" :disabled="dateRangeLocked" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed">
                    </div>
                    <div class="flex items-end gap-2">
                        <button @click="setToday()" :class="activeRange === 'today' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-2 rounded-lg text-xs font-medium transition-colors">Hari Ini</button>
                        <button @click="setYesterday()" :class="activeRange === 'yesterday' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-2 rounded-lg text-xs font-medium transition-colors">Kemarin</button>
                        <button @click="setLast7Days()" :class="activeRange === '7days' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-2 rounded-lg text-xs font-medium transition-colors">7 Hari</button>
                        <button @click="setLast30Days()" :class="activeRange === '30days' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-2 rounded-lg text-xs font-medium transition-colors">30 Hari</button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Mandor</label>
                        <select x-model="filters.mandor" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Mandor</option>
                            <template x-for="mandor in data.filterOptions?.mandors" :key="mandor.id">
                                <option :value="mandor.id" x-text="mandor.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Plot</label>
                        <select x-model="filters.plot" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Plot</option>
                            <template x-for="plot in data.filterOptions?.plots" :key="plot">
                                <option :value="plot" x-text="plot"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Kontraktor</label>
                        <select x-model="filters.kontraktor" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Kontraktor</option>
                            <template x-for="kontraktor in data.filterOptions?.kontraktors" :key="kontraktor">
                                <option :value="kontraktor" x-text="kontraktor"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Sub Kontraktor</label>
                        <select x-model="filters.subkontraktor" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Sub Kontraktor</option>
                            <template x-for="subkontraktor in data.filterOptions?.subkontraktors" :key="subkontraktor.id">
                                <option :value="subkontraktor.id" x-text="subkontraktor.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">No Polisi</label>
                        <select x-model="filters.nopol" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Kendaraan</option>
                            <template x-for="nopol in data.filterOptions?.nopols" :key="nopol">
                                <option :value="nopol" x-text="nopol"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Status</label>
                        <select x-model="filters.status" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Status</option>
                            <option value="sudah">Sudah Timbang</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Quick Search Section -->
            <div class="bg-white rounded-xl shadow-md p-4 mb-5">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Cari Nomor SJ:</h3>
                    </div>
                    <div class="flex-grow flex gap-2">
                        <input 
                            type="text" 
                            x-model="searchSJ" 
                            @keyup.enter="goToDetail()" 
                            placeholder="Masukkan nomor surat jalan..."
                            class="flex-grow text-sm border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        <button 
                            @click="goToDetail()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Cari
                        </button>
                    </div>
                </div>
            </div>

            <!-- Charts Row 1: SJ & Tonase per Tanggal -->
            <div x-show="!data.isSingleDay" class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div class="bg-white rounded-xl shadow-md p-5">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Jumlah Surat Jalan</h3>
                        <div class="flex gap-2">
                            <button @click="setSJPeriod('daily')" :disabled="chartLoading.sj" :class="sjPeriod === 'daily' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-1 rounded text-xs font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Harian</button>
                            <button @click="setSJPeriod('monthly')" :disabled="chartLoading.sj" :class="sjPeriod === 'monthly' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-1 rounded text-xs font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Bulanan</button>
                        </div>
                    </div>
                    <canvas id="sjPerTanggalChart" height="220"></canvas>
                </div>

                <div class="bg-white rounded-xl shadow-md p-5">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Hasil Tonase (Netto)</h3>
                        <div class="flex gap-2">
                            <button @click="setTonasePeriod('daily')" :disabled="chartLoading.tonase" :class="tonasePeriod === 'daily' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-1 rounded text-xs font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Harian</button>
                            <button @click="setTonasePeriod('monthly')" :disabled="chartLoading.tonase" :class="tonasePeriod === 'monthly' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-1 rounded text-xs font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Bulanan</button>
                        </div>
                    </div>
                    <canvas id="tonasePerTanggalChart" height="220"></canvas>
                </div>
            </div>

            <!-- Charts Row 2: Hourly (if single day) -->
            <div x-show="data.isSingleDay" class="mb-5">
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Trend Hasil Kumulatif Tonase Per Jam - Grafik Harian</h3>
                    <canvas id="hourlyChart" height="90"></canvas>
                </div>
            </div>

            <!-- Charts Row 3: Rit, Vehicle, Durasi -->
            <div class="grid grid-cols-1 md:grid-cols-11 gap-5 mb-5">
                <div class="bg-white rounded-xl shadow-md p-5 min-h-[500px] md:col-span-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Jumlah Rit per Kontraktor</h3>
                    <div class="max-h-80">
                        <table class="min-w-full text-base">
                            <thead class="sticky top-0 bg-gray-100 z-10">
                                <tr>
                                    <th class="border border-gray-300 px-2 py-1.5 text-left">Kontraktor</th>
                                    <th class="border border-gray-300 px-2 py-1.5 text-center">Total<br>Rit</th>
                                    <th class="border border-gray-300 px-2 py-1.5 text-center">Sudah<br>Timbang</th>
                                    <th class="border border-gray-300 px-2 py-1.5 text-center">Pending</th>
                                    <th class="border border-gray-300 px-2 py-1.5 text-right">Total<br>Netto<br>(ton)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in data.ritPerKontraktor" :key="item.kontraktor">
                                    <tr class="hover:bg-blue-50">
                                        <td class="border border-gray-300 px-2 py-1.5 font-semibold text-xs" x-text="item.kontraktor"></td>
                                        <td class="border border-gray-300 px-2 py-1.5 text-center font-bold text-blue-600" x-text="item.total_rit"></td>
                                        <td class="border border-gray-300 px-2 py-1.5 text-center" x-text="item.sudah_timbang"></td>
                                        <td class="border border-gray-300 px-2 py-1.5 text-center" x-text="item.pending"></td>
                                        <td class="border border-gray-300 px-2 py-1.5 text-right font-semibold" x-text="formatTon(item.total_netto)"></td>
                                    </tr>
                                </template>
                                <tr class="bg-gray-100 font-bold border-t-2 border-gray-400">
                                    <td class="border border-gray-300 px-2 py-1.5 text-right">TOTAL</td>
                                    <td class="border border-gray-300 px-2 py-1.5 text-center text-blue-700" x-text="data.ritPerKontraktor?.reduce((sum, item) => sum + item.total_rit, 0) || 0"></td>
                                    <td class="border border-gray-300 px-2 py-1.5 text-center" x-text="data.ritPerKontraktor?.reduce((sum, item) => sum + item.sudah_timbang, 0) || 0"></td>
                                    <td class="border border-gray-300 px-2 py-1.5 text-center" x-text="data.ritPerKontraktor?.reduce((sum, item) => sum + item.pending, 0) || 0"></td>
                                    <td class="border border-gray-300 px-2 py-1.5 text-right text-green-700" x-text="formatTon(data.ritPerKontraktor?.reduce((sum, item) => sum + (item.total_netto || 0), 0) || 0)"></td>
                                </tr>
                                <template x-if="!data.ritPerKontraktor || data.ritPerKontraktor.length === 0">
                                    <tr>
                                        <td colspan="5" class="border border-gray-300 px-2 py-4 text-center text-gray-500">Tidak ada data</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-5 md:col-span-3">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Performance Kendaraan</h3>
                    <div class="overflow-y-auto max-h-96">
                        <table class="min-w-full text-xs">
                            <thead class="sticky top-0 bg-gray-100 z-10">
                                <tr>
                                    <th class="border border-gray-300 px-2 py-1.5 text-center">No</th>
                                    <th class="border border-gray-300 px-2 py-1.5 text-left">No Polisi</th>
                                    <th class="border border-gray-300 px-2 py-1.5 text-center">Trip</th>
                                    <th class="border border-gray-300 px-2 py-1.5 text-right">Total Netto</th>
                                    <th class="border border-gray-300 px-2 py-1.5 text-center">Avg Deload</th>
                                    <th class="border border-gray-300 px-2 py-1.5 text-center">Avg POS-Timbang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(vehicle, index) in data.vehiclePerformance" :key="vehicle.nopol">
                                    <tr class="hover:bg-blue-50">
                                        <td class="border border-gray-300 px-2 py-1.5 text-center" x-text="index + 1"></td>
                                        <td class="border border-gray-300 px-2 py-1.5 font-semibold" x-text="vehicle.nopol"></td>
                                        <td class="border border-gray-300 px-2 py-1.5 text-center font-bold text-blue-600" x-text="vehicle.trip_count"></td>
                                        <td class="border border-gray-300 px-2 py-1.5 text-right" x-text="formatNumber(vehicle.total_netto)"></td>
                                        <td class="border border-gray-300 px-2 py-1.5 text-center" x-text="formatDuration(vehicle.avg_durasi_deload)"></td>
                                        <td class="border border-gray-300 px-2 py-1.5 text-center" x-text="formatDuration(vehicle.avg_durasi_pos_timbang)"></td>
                                    </tr>
                                </template>
                                <template x-if="!data.vehiclePerformance || data.vehiclePerformance.length === 0">
                                    <tr>
                                        <td colspan="6" class="border border-gray-300 px-2 py-4 text-center text-gray-500">Tidak ada data</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-5 md:col-span-3">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Distribusi Durasi Perjalanan</h3>
                    <p class="text-xs text-gray-500 mb-3">Waktu dari Cetak POS ke Timbangan</p>
                    <canvas id="durasiChart" height="200"></canvas>
                </div>
            </div>

            <!-- Charts Row 4: Compact Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
                <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-green-500">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs font-bold text-gray-700 uppercase">Langsir</h3>
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="text-4xl font-bold text-green-600 mb-1" x-text="data.langsirPercentage + '%'"></div>
                    <div class="text-xs text-gray-600">
                        <span class="font-semibold" x-text="data.langsirCount"></span> langsir, 
                        <span class="font-semibold" x-text="data.nonLangsirCount"></span> non-langsir
                    </div>
                    <div class="text-xs text-gray-500 mt-1">dari <span x-text="data.summary?.total_sj"></span> total</div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-red-500">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs font-bold text-gray-700 uppercase">Tebu Sulit</h3>
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="text-4xl font-bold text-red-600 mb-1" x-text="data.tebuSulitPercentage + '%'"></div>
                    <div class="text-xs text-gray-600">
                        <span class="font-semibold" x-text="data.tebuSulitCount"></span> sulit, 
                        <span class="font-semibold" x-text="data.tebuNormalCount"></span> normal
                    </div>
                    <div class="text-xs text-gray-500 mt-1">dari <span x-text="data.summary?.total_sj"></span> total</div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs font-bold text-gray-700 uppercase">Kode Tebang</h3>
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                    <div class="flex items-baseline gap-2 mb-1">
                        <span class="text-2xl font-bold text-purple-600" x-text="data.premiumCount"></span>
                        <span class="text-sm text-gray-500">-</span>
                        <span class="text-2xl font-bold text-gray-600" x-text="data.nonPremiumCount"></span>
                    </div>
                    <div class="text-xs text-gray-600">
                        <span class="font-semibold">Premium</span> - 
                        <span class="font-semibold">Non-Premium</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">dari <span x-text="data.summary?.total_sj"></span> total</div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs font-bold text-gray-700 uppercase">Kendaraan</h3>
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                        </svg>
                    </div>
                    <div class="flex items-baseline gap-2 mb-1">
                        <span class="text-2xl font-bold text-blue-600" x-text="data.kendaraanWL"></span>
                        <span class="text-sm text-gray-500">-</span>
                        <span class="text-2xl font-bold text-orange-600" x-text="data.kendaraanUmum"></span>
                    </div>
                    <div class="text-xs text-gray-600">
                        <span class="font-semibold">WL</span> - 
                        <span class="font-semibold">Umum</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">dari <span x-text="data.summary?.total_sj"></span> total</div>
                </div>
            </div>

            <!-- Charts Row 5: Status, Kontraktor & Subkontraktor -->
            <div class="grid grid-cols-1 md:grid-cols-8 gap-5 mb-5">
                <div class="bg-white rounded-xl shadow-md p-5 md:col-span-2">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Status Timbangan</h3>
                    <canvas id="statusChart" height="200"></canvas>
                </div>

                <div class="bg-white rounded-xl shadow-md p-5 md:col-span-3">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Kontraktor by Tonase</h3>
                    <canvas id="kontraktorChart" height="220"></canvas>
                </div>

                <div class="bg-white rounded-xl shadow-md p-5 md:col-span-3">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Sub Kontraktor by Tonase</h3>
                    <canvas id="subkontraktorChart" height="220"></canvas>
                </div>
            </div>

            <!-- Detail Table -->
            <div class="bg-white rounded-xl shadow-md p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Detail Surat Jalan</h3>
                    <div class="flex gap-2">
                        <button @click="exportSummary()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-xs font-medium transition-colors">
                            📊 Export Summary
                        </button>
                        <button @click="exportDetail()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-medium transition-colors">
                            📋 Export Detail
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-100">
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">No</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Company</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">No SJ</th>
                                <th rowspan="2" @click="sortBy('tanggalcetakpossecurity')" class="border border-gray-300 px-2 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Tgl SJ
                                        <span x-html="getSortIcon('tanggalcetakpossecurity')"></span>
                                    </div>
                                </th>
                                <th rowspan="2" @click="sortBy('nama_mandor')" class="border border-gray-300 px-2 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Mandor
                                        <span x-html="getSortIcon('nama_mandor')"></span>
                                    </div>
                                </th>
                                <th rowspan="2" @click="sortBy('plot')" class="border border-gray-300 px-2 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Plot
                                        <span x-html="getSortIcon('plot')"></span>
                                    </div>
                                </th>
                                <th rowspan="2" @click="sortBy('umur')" class="border border-gray-300 px-2 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Umur<br>(bulan)
                                        <span x-html="getSortIcon('umur')"></span>
                                    </div>
                                </th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Kategori</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Varietas</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Kode<br>Tebang</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Langsir</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Tebu<br>Sulit</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Jenis<br>Kendaraan</th>
                                <th rowspan="2" @click="sortBy('nomorpolisi')" class="border border-gray-300 px-2 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        No Polisi
                                        <span x-html="getSortIcon('nomorpolisi')"></span>
                                    </div>
                                </th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Supir</th>
                                <th rowspan="2" @click="sortBy('nama_kontraktor_lengkap')" class="border border-gray-300 px-2 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Kontraktor
                                        <span x-html="getSortIcon('nama_kontraktor_lengkap')"></span>
                                    </div>
                                </th>
                                <th rowspan="2" @click="sortBy('nama_subkontraktor_lengkap')" class="border border-gray-300 px-2 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Sub Kontraktor
                                        <span x-html="getSortIcon('nama_subkontraktor_lengkap')"></span>
                                    </div>
                                </th>
                                <th colspan="4" class="border border-gray-300 px-2 py-1 text-center bg-gray-100">Waktu</th>
                                <th rowspan="2" @click="sortBy('bruto')" class="border border-gray-300 px-2 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Bruto
                                        <span x-html="getSortIcon('bruto')"></span>
                                    </div>
                                </th>
                                <th rowspan="2" @click="sortBy('netto')" class="border border-gray-300 px-2 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Netto
                                        <span x-html="getSortIcon('netto')"></span>
                                    </div>
                                </th>
                                <th colspan="2" class="border border-gray-300 px-2 py-1 text-center bg-gray-100">Durasi</th>
                                <th rowspan="2" @click="sortBy('status')" class="border border-gray-300 px-2 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Status
                                        <span x-html="getSortIcon('status')"></span>
                                    </div>
                                </th>
                            </tr>
                            <tr class="bg-gray-100">
                                <th @click="sortBy('tanggalangkut')" class="border border-gray-300 px-2 py-1 text-xs cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Jam<br>Angkut
                                        <span x-html="getSortIcon('tanggalangkut')"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('tanggalcetakpossecurity')" class="border border-gray-300 px-2 py-1 text-xs cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Jam<br>Cetak POS
                                        <span x-html="getSortIcon('tanggalcetakpossecurity')"></span>
                                    </div>
                                </th>
                                <th class="border border-gray-300 px-2 py-1 text-xs">Jam<br>Masuk</th>
                                <th class="border border-gray-300 px-2 py-1 text-xs">Jam<br>Keluar</th>
                                <th class="border border-gray-300 px-2 py-1 text-xs">POS ke<br>Timbang<br>(min)</th>
                                <th class="border border-gray-300 px-2 py-1 text-xs">Durasi<br>Deload<br>(min)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in sortedDetails()" :key="`${item.companycode}-${item.suratjalanno}`">
                                <tr :class="item.status === 'Sudah Timbang' ? 'bg-green-50 hover:bg-green-100' : 'bg-yellow-50 hover:bg-yellow-100'" class="transition-colors">
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="index + 1"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center font-semibold" x-text="item.companycode"></td>
                                    <td class="border border-gray-300 px-2 py-2">
                                        <a :href="`{{ route('report.report-surat-jalan-timbangan.index') }}/${item.suratjalanno}`"
                                           class="font-semibold text-blue-600 hover:text-blue-800 underline"
                                           x-text="item.suratjalanno"></a>
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="formatDate(item.tanggalcetakpossecurity)"></td>
                                    <td class="border border-gray-300 px-2 py-2" x-text="item.nama_mandor || item.mandorid"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center font-bold" x-text="item.plot"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="item.umur || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        <span class="px-2 py-1 rounded text-xs font-semibold" :class="getKategoriColor(item.kategori)" x-text="item.kategori || '-'"></span>
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="item.varietas || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="item.kodetebang || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        <span x-show="item.langsir === 1" class="text-green-600">✓</span>
                                        <span x-show="item.langsir === 0" class="text-gray-400">-</span>
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        <span x-show="item.tebusulit === 1" class="text-red-600">✓</span>
                                        <span x-show="item.tebusulit === 0" class="text-gray-400">-</span>
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="item.kendaraankontraktor === 0 ? 'WL' : 'Umum'"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center font-medium" x-text="item.nomorpolisi || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2" x-text="item.namasupir || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-xs" x-text="item.nama_kontraktor_lengkap || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-xs" x-text="item.nama_subkontraktor_lengkap || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="formatTime24(item.tanggalangkut)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="formatTime24(item.tanggalcetakpossecurity)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="formatTime24FromJam(item.jam1)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="formatTime24FromJam(item.jam2)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-right" x-text="formatNumber(item.bruto)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-right font-bold" x-text="formatNumber(item.netto)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="formatDuration(item.durasi_pos_timbangan)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="formatDuration(item.durasi_deload)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        <span :class="item.status === 'Sudah Timbang' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" 
                                              class="px-2 py-1 rounded-full text-xs font-bold whitespace-nowrap inline-block" 
                                              x-text="item.status"></span>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="!data.details || data.details.length === 0">
                                <tr>
                                    <td colspan="26" class="border border-gray-300 px-3 py-8 text-center text-gray-500 font-medium">Tidak ada data</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
    function suratJalanReport() {
        return {
            loading: true,
            dateRangeLocked: false,
            activeRange: '',
            sjPeriod: 'daily',
            tonasePeriod: 'daily',
            searchSJ: '',
            sortColumn: '',
            sortDirection: 'asc',
            chartLoading: {
                sj: false,
                tonase: false
            },
            data: {
                summary: {},
                details: [],
                filterOptions: {},
                hourlyTrend: [],
                statusBreakdown: [],
                vehiclePerformance: [],
                ritPerKontraktor: [],
                sjDaily: [],
                sjMonthly: [],
                tonaseDaily: [],
                tonaseMonthly: [],
                durasiPerjalanan: [],
                langsirCount: 0,
                langsirPercentage: 0,
                nonLangsirCount: 0,
                tebuSulitCount: 0,
                tebuSulitPercentage: 0,
                tebuNormalCount: 0,
                premiumCount: 0,
                nonPremiumCount: 0,
                kendaraanWL: 0,
                kendaraanUmum: 0,
                kontraktorTonase: [],
                subkontraktorTonase: [],
                isSingleDay: true
            },
            filters: {
                group: '',
                start_date: new Date().toISOString().split('T')[0],
                end_date: new Date().toISOString().split('T')[0],
                mandor: '',
                plot: '',
                kontraktor: '',
                subkontraktor: '',
                nopol: '',
                status: ''
            },
            appliedFilters: {
                group: ''
            },
            charts: {
                hourly: null,
                status: null,
                sjPerTanggal: null,
                tonasePerTanggal: null,
                durasi: null,
                kontraktor: null,
                subkontraktor: null
            },

            sortBy(column) {
                if (this.sortColumn === column) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortColumn = column;
                    this.sortDirection = 'asc';
                }
            },

            getSortIcon(column) {
                if (this.sortColumn !== column) {
                    return '<svg class="w-3 h-3 text-gray-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>';
                }
                if (this.sortDirection === 'asc') {
                    return '<svg class="w-3 h-3 text-blue-600 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>';
                }
                return '<svg class="w-3 h-3 text-blue-600 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>';
            },

            sortedDetails() {
                if (!this.data.details || this.data.details.length === 0) return [];
                if (!this.sortColumn) return this.data.details;

                const col = this.sortColumn;
                const dir = this.sortDirection === 'asc' ? 1 : -1;

                const numericCols = ['umur', 'bruto', 'netto'];
                const dateCols = ['tanggalcetakpossecurity', 'tanggalangkut'];

                return [...this.data.details].sort((a, b) => {
                    let valA = a[col];
                    let valB = b[col];

                    // Handle null/undefined
                    if (valA == null && valB == null) return 0;
                    if (valA == null) return 1;
                    if (valB == null) return -1;

                    // Numeric sort
                    if (numericCols.includes(col)) {
                        return (parseFloat(valA) - parseFloat(valB)) * dir;
                    }

                    // Date sort
                    if (dateCols.includes(col)) {
                        return (new Date(valA) - new Date(valB)) * dir;
                    }

                    // String sort
                    return String(valA).localeCompare(String(valB), 'id') * dir;
                });
            },

            async loadData() {
                this.loading = true;
                try {
                    const params = new URLSearchParams(this.filters);
                    const response = await fetch(`{{ route('report.report-surat-jalan-timbangan.data') }}?${params}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        this.data = result.data;
                        this.appliedFilters.group = this.filters.group;
                        
                        await this.$nextTick();
                        this.renderCharts();
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('Load data error:', error);
                    alert('Gagal memuat data');
                } finally {
                    this.loading = false;
                }
            },

            getCurrentCompanyTitle() {
                if (this.appliedFilters.group === 'all-tbl') {
                    return 'TBL Group (TBL1, TBL2 & TBL3)';
                } else if (this.appliedFilters.group === 'all-divisi') {
                    return 'All Division';
                } else {
                    return '{{ Session::get("companycode") }}';
                }
            },

            renderCharts() {
                this.renderSJPerTanggalChart();
                this.renderTonasePerTanggalChart();
                if (this.data.isSingleDay) {
                    this.renderHourlyChart();
                }
                this.renderStatusChart();
                this.renderDurasiChart();
                this.renderKontraktorChart();
                this.renderSubkontraktorChart();
            },

            renderHourlyChart() {
                const ctx = document.getElementById('hourlyChart');
                if (!ctx) return;
                if (this.charts.hourly) { this.charts.hourly.destroy(); this.charts.hourly = null; }

                this.charts.hourly = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: this.data.hourlyTrend.map(d => d.hour),
                        datasets: [{
                            label: 'Hasil Tonase Kumulatif (ton)',
                            data: this.data.hourlyTrend.map(d => d.netto / 1000),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true, tension: 0, borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { callback: v => v.toLocaleString('id-ID') + ' ton' } },
                            x: { grid: { display: false }, ticks: { font: { size: 9 } } }
                        }
                    }
                });
            },

            renderStatusChart() {
                const ctx = document.getElementById('statusChart');
                if (!ctx) return;
                if (this.charts.status) { this.charts.status.destroy(); this.charts.status = null; }

                this.charts.status = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: this.data.statusBreakdown.map(d => d.name),
                        datasets: [{ data: this.data.statusBreakdown.map(d => d.value), backgroundColor: ['#10b981', '#f59e0b'] }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: true,
                        plugins: { legend: { position: 'bottom', labels: { font: { size: 10 } } } }
                    }
                });
            },

            async setSJPeriod(period) {
                if (this.sjPeriod === period || this.chartLoading.sj) return;
                this.chartLoading.sj = true;
                this.sjPeriod = period;
                this.renderSJPerTanggalChart();
                await new Promise(r => setTimeout(r, 3000));
                this.chartLoading.sj = false;
            },

            async setTonasePeriod(period) {
                if (this.tonasePeriod === period || this.chartLoading.tonase) return;
                this.chartLoading.tonase = true;
                this.tonasePeriod = period;
                this.renderTonasePerTanggalChart();
                await new Promise(r => setTimeout(r, 3000));
                this.chartLoading.tonase = false;
            },

            renderSJPerTanggalChart() {
                const ctx = document.getElementById('sjPerTanggalChart');
                if (!ctx) return;
                if (this.charts.sjPerTanggal) { this.charts.sjPerTanggal.destroy(); this.charts.sjPerTanggal = null; }

                const chartData = this.sjPeriod === 'monthly' ? this.data.sjMonthly : this.data.sjDaily;

                this.charts.sjPerTanggal = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.map(d => d.label),
                        datasets: [{
                            label: 'Jumlah SJ', data: chartData.map(d => d.value),
                            borderColor: '#3b82f6', backgroundColor: '#3b82f6',
                            pointBackgroundColor: '#3b82f6', pointBorderColor: '#070088ff',
                            pointBorderWidth: 1, pointRadius: 5, tension: 0, borderWidth: 4, fill: false
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                            x: { grid: { display: false }, ticks: { font: { size: 9 } } }
                        }
                    }
                });
            },

            renderTonasePerTanggalChart() {
                const ctx = document.getElementById('tonasePerTanggalChart');
                if (!ctx) return;
                if (this.charts.tonasePerTanggal) { this.charts.tonasePerTanggal.destroy(); this.charts.tonasePerTanggal = null; }

                const chartData = this.tonasePeriod === 'monthly' ? this.data.tonaseMonthly : this.data.tonaseDaily;

                this.charts.tonasePerTanggal = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.map(d => d.label),
                        datasets: [{
                            label: 'Tonase (ton)', data: chartData.map(d => d.value / 1000),
                            borderColor: '#10b981', backgroundColor: '#10b981',
                            pointBackgroundColor: '#10b981', pointBorderColor: '#0a6b34ff',
                            pointBorderWidth: 1, pointRadius: 5, tension: 0, borderWidth: 4, fill: false
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: ctx => ctx.parsed.y.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ton' } }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { callback: v => v.toLocaleString('id-ID') + ' ton' } },
                            x: { grid: { display: false }, ticks: { font: { size: 9 } } }
                        }
                    }
                });
            },

            renderDurasiChart() {
                const ctx = document.getElementById('durasiChart');
                if (!ctx || !this.data.durasiPerjalanan || this.data.durasiPerjalanan.length === 0) return;
                if (this.charts.durasi) { this.charts.durasi.destroy(); this.charts.durasi = null; }

                this.charts.durasi = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.data.durasiPerjalanan.map(d => d.label),
                        datasets: [{ label: 'Jumlah SJ', data: this.data.durasiPerjalanan.map(d => d.value), backgroundColor: '#8b5cf6', borderRadius: 6 }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                        }
                    }
                });
            },

            renderKontraktorChart() {
                const ctx = document.getElementById('kontraktorChart');
                if (!ctx || !this.data.kontraktorTonase || this.data.kontraktorTonase.length === 0) return;
                if (this.charts.kontraktor) { this.charts.kontraktor.destroy(); this.charts.kontraktor = null; }

                this.charts.kontraktor = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.data.kontraktorTonase.map(d => d.name),
                        datasets: [{ label: 'Tonase (ton)', data: this.data.kontraktorTonase.map(d => d.value), backgroundColor: '#10b981', borderRadius: 6 }]
                    },
                    options: {
                        indexAxis: 'y', responsive: true, maintainAspectRatio: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: ctx => ctx.parsed.x.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ton' } }
                        },
                        scales: {
                            x: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { callback: v => v.toLocaleString('id-ID') + ' ton' } },
                            y: { grid: { display: false }, ticks: { font: { size: 9 } } }
                        }
                    }
                });
            },

            renderSubkontraktorChart() {
                const ctx = document.getElementById('subkontraktorChart');
                if (!ctx || !this.data.subkontraktorTonase || this.data.subkontraktorTonase.length === 0) return;
                if (this.charts.subkontraktor) { this.charts.subkontraktor.destroy(); this.charts.subkontraktor = null; }

                this.charts.subkontraktor = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.data.subkontraktorTonase.map(d => d.name),
                        datasets: [{ label: 'Tonase (ton)', data: this.data.subkontraktorTonase.map(d => d.value), backgroundColor: '#f59e0b', borderRadius: 6 }]
                    },
                    options: {
                        indexAxis: 'y', responsive: true, maintainAspectRatio: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: ctx => ctx.parsed.x.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ton' } }
                        },
                        scales: {
                            x: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { callback: v => v.toLocaleString('id-ID') + ' ton' } },
                            y: { grid: { display: false }, ticks: { font: { size: 9 } } }
                        }
                    }
                });
            },

            applyFilters() { this.loadData(); },

            goToDetail() {
                if (!this.searchSJ || this.searchSJ.trim() === '') {
                    alert('Masukkan nomor surat jalan terlebih dahulu');
                    return;
                }
                window.open(`{{ route('report.report-surat-jalan-timbangan.index') }}/${this.searchSJ.trim()}`, '_blank');
            },

            resetFilters() {
                this.filters = {
                    group: '', start_date: new Date().toISOString().split('T')[0],
                    end_date: new Date().toISOString().split('T')[0],
                    mandor: '', plot: '', kontraktor: '', subkontraktor: '', nopol: '', status: ''
                };
                this.activeRange = '';
                this.dateRangeLocked = false;
                this.sjPeriod = 'daily';
                this.tonasePeriod = 'daily';
                this.sortColumn = '';
                this.sortDirection = 'asc';
                this.loadData();
            },

            setToday() {
                const today = new Date().toISOString().split('T')[0];
                this.filters.start_date = today;
                this.filters.end_date = today;
                this.activeRange = 'today';
                this.dateRangeLocked = true;
                this.applyFilters();
            },

            setYesterday() {
                const d = new Date(); d.setDate(d.getDate() - 1);
                const s = d.toISOString().split('T')[0];
                this.filters.start_date = s;
                this.filters.end_date = s;
                this.activeRange = 'yesterday';
                this.dateRangeLocked = true;
                this.applyFilters();
            },

            setLast7Days() {
                const end = new Date(); const start = new Date();
                start.setDate(start.getDate() - 6);
                this.filters.start_date = start.toISOString().split('T')[0];
                this.filters.end_date = end.toISOString().split('T')[0];
                this.activeRange = '7days';
                this.dateRangeLocked = true;
                this.applyFilters();
            },

            setLast30Days() {
                const end = new Date(); const start = new Date();
                start.setDate(start.getDate() - 29);
                this.filters.start_date = start.toISOString().split('T')[0];
                this.filters.end_date = end.toISOString().split('T')[0];
                this.activeRange = '30days';
                this.dateRangeLocked = true;
                this.applyFilters();
            },

            formatNumber(num) {
                if (!num) return '0';
                return parseFloat(num).toLocaleString('id-ID');
            },
            
            formatTon(kg) {
                if (!kg) return '0.00';
                return (parseFloat(kg) / 1000).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            formatDuration(minutes) {
                if (!minutes) return '-';
                const mins = parseFloat(minutes);
                if (mins < 60) return Math.round(mins) + ' menit';
                return `${Math.floor(mins / 60)} jam ${Math.round(mins % 60)} menit`;
            },

            formatDate(date) {
                if (!date) return '-';
                return new Date(date).toLocaleDateString('id-ID');
            },

            formatTime24(datetime) {
                if (!datetime) return '-';
                const d = new Date(datetime);
                return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
            },

            formatTime24FromJam(jam) {
                if (!jam) return '-';
                const p = jam.split(':');
                return p.length >= 2 ? `${p[0]}:${p[1]}` : jam;
            },

            getKategoriColor(kategori) {
                const c = { 'PC': 'bg-emerald-100 text-emerald-800', 'RC1': 'bg-blue-100 text-blue-800', 'RC2': 'bg-amber-100 text-amber-800', 'RC3': 'bg-rose-100 text-rose-800' };
                return c[kategori] || 'bg-gray-100 text-gray-800';
            },

            exportSummary() {
                let html = '<table border="1"><thead><tr><th>Mandor</th><th>Total SJ</th><th>Total Netto (kg)</th></tr></thead><tbody>';
                const s = {};
                this.data.details.forEach(item => {
                    const m = item.nama_mandor || item.mandorid;
                    if (!s[m]) s[m] = { count: 0, netto: 0 };
                    s[m].count++;
                    if (item.netto) s[m].netto += parseFloat(item.netto);
                });
                Object.keys(s).forEach(m => { html += `<tr><td>${m}</td><td>${s[m].count}</td><td>${s[m].netto}</td></tr>`; });
                html += '</tbody></table>';
                const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = `Summary_SJ_${this.filters.start_date}_${this.filters.end_date}.xls`;
                a.click();
            },

            exportDetail() {
                const blob = new Blob([document.querySelector('table').outerHTML], { type: 'application/vnd.ms-excel' });
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = `Detail_SJ_${this.filters.start_date}_${this.filters.end_date}.xls`;
                a.click();
            }
        }
    }
    </script>
</x-layout>