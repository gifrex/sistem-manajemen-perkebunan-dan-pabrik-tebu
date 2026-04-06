<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div x-data="masterLahanReport()" x-init="loadData()" class="space-y-5">
        
        <!-- Loading State -->
        <div x-show="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>

        <!-- Main Content -->
        <div x-show="!loading" style="display: none;" x-transition>
            
            <!-- Header Title -->
            <div class="bg-white border-b-2 border-gray-200 rounded-xl shadow-sm p-5 mb-5">
                <h2 class="text-xl font-semibold text-gray-800">
                    Data Lahan Company: 
                    <span class="font-bold text-gray-900" x-text="getCurrentCompanyTitle()"></span>
                </h2>
            </div>

            <!-- Lifecycle Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
                <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-emerald-500 transform hover:scale-105 transition-transform hover:shadow-xl">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Plant Cane</p>
                            <p class="text-4xl font-bold text-gray-900" x-text="data.summary?.pc_count || 0"></p>
                        </div>
                        <div class="bg-emerald-100 rounded-lg px-3 py-1.5">
                            <span class="text-sm font-bold text-emerald-700">PC</span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-emerald-600" x-text="parseFloat(data.summary?.pc_area || 0).toFixed(2)"></span>
                        <span class="text-sm text-gray-500">Ha</span>
                        <span class="text-sm text-gray-500 ml-1" x-text="`(${((parseFloat(data.summary?.pc_area || 0) / parseFloat(data.summary?.total_area || 1)) * 100).toFixed(1)}%)`"></span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-blue-500 transform hover:scale-105 transition-transform hover:shadow-xl">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Ratoon 1</p>
                            <p class="text-4xl font-bold text-gray-900" x-text="data.summary?.rc1_count || 0"></p>
                        </div>
                        <div class="bg-blue-100 rounded-lg px-3 py-1.5">
                            <span class="text-sm font-bold text-blue-700">RC1</span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-blue-600" x-text="parseFloat(data.summary?.rc1_area || 0).toFixed(2)"></span>
                        <span class="text-sm text-gray-500">Ha</span>
                        <span class="text-sm text-gray-500 ml-1" x-text="`(${((parseFloat(data.summary?.rc1_area || 0) / parseFloat(data.summary?.total_area || 1)) * 100).toFixed(1)}%)`"></span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-amber-500 transform hover:scale-105 transition-transform hover:shadow-xl">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Ratoon 2</p>
                            <p class="text-4xl font-bold text-gray-900" x-text="data.summary?.rc2_count || 0"></p>
                        </div>
                        <div class="bg-amber-100 rounded-lg px-3 py-1.5">
                            <span class="text-sm font-bold text-amber-700">RC2</span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-amber-600" x-text="parseFloat(data.summary?.rc2_area || 0).toFixed(2)"></span>
                        <span class="text-sm text-gray-500">Ha</span>
                        <span class="text-sm text-gray-500 ml-1" x-text="`(${((parseFloat(data.summary?.rc2_area || 0) / parseFloat(data.summary?.total_area || 1)) * 100).toFixed(1)}%)`"></span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-rose-500 transform hover:scale-105 transition-transform hover:shadow-xl">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Ratoon 3</p>
                            <p class="text-4xl font-bold text-gray-900" x-text="data.summary?.rc3_count || 0"></p>
                        </div>
                        <div class="bg-rose-100 rounded-lg px-3 py-1.5">
                            <span class="text-sm font-bold text-rose-700">RC3</span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-rose-600" x-text="parseFloat(data.summary?.rc3_area || 0).toFixed(2)"></span>
                        <span class="text-sm text-gray-500">Ha</span>
                        <span class="text-sm text-gray-500 ml-1" x-text="`(${((parseFloat(data.summary?.rc3_area || 0) / parseFloat(data.summary?.total_area || 1)) * 100).toFixed(1)}%)`"></span>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Bar -->
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl shadow-md p-5 mb-5 border border-gray-200">
                <div class="grid grid-cols-3 gap-6">
                    <div class="flex items-center space-x-4 bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                        <div class="bg-blue-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Plots</p>
                            <p class="text-3xl font-bold text-gray-900" x-text="data.summary?.total_plots || 0"></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                        <div class="bg-green-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Area</p>
                            <p class="text-3xl font-bold text-gray-900" x-text="parseFloat(data.summary?.total_area || 0).toFixed(2)"></p>
                            <p class="text-xs text-gray-500 font-medium">Hectares</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                        <div class="bg-purple-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Varietas Types</p>
                            <p class="text-3xl font-bold text-gray-900" x-text="data.varietasChart?.length || 0"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white rounded-xl shadow-md p-4 mb-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Filters</h3>
                    <div class="flex gap-2">
                        <button @click="applyFilters()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-1.5 rounded-lg text-xs font-medium transition-colors">
                            Apply Filters
                        </button>
                        <button @click="resetFilters()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-1.5 rounded-lg text-xs font-medium transition-colors">
                            Reset
                        </button>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Group</label>
                        <select x-model="filters.group" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Current Company</option>
                            <option value="all-tbl">All TBL (TBL I/TBL II/TBL III)</option>
                            <option value="all-divisi">All Divisi</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Blok</label>
                        <select x-model="filters.blok" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Blok</option>
                            <template x-for="blok in data.filterOptions?.bloks" :key="blok">
                                <option :value="blok" x-text="blok"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Plot Type</label>
                        <select x-model="filters.plottype" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Types</option>
                            <option value="KBD">KBD</option>
                            <option value="KTG">KTG</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Varietas</label>
                        <select x-model="filters.varietas" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Varietas</option>
                            <template x-for="varietas in data.filterOptions?.varietas" :key="varietas">
                                <option :value="varietas" x-text="varietas"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Lifecycle</label>
                        <select x-model="filters.lifecycle" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Status</option>
                            <option value="PC">PC</option>
                            <option value="RC1">RC1</option>
                            <option value="RC2">RC2</option>
                            <option value="RC3">RC3</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[120px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">PKP</label>
                        <select x-model="filters.pkp" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All PKP</option>
                            <option value="135">135</option>
                            <option value="180">180</option>
                            <option value="210">210</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[180px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Age Range (Days)</label>
                        <div class="flex gap-2">
                            <input type="number" x-model="filters.age_min" class="w-1/2 text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Min">
                            <input type="number" x-model="filters.age_max" class="w-1/2 text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Max">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Lifecycle Distribution</h3>
                    <canvas id="lifecycleChart" height="220"></canvas>
                </div>
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Varietas Distribution</h3>
                    <canvas id="varietasChart" height="220"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Plot Type Distribution</h3>
                    <canvas id="plottypeChart" height="180"></canvas>
                </div>
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Age Distribution (Histogram)</h3>
                    <canvas id="ageChart" height="180"></canvas>
                </div>
            </div>

            <!-- Detailed Table -->
            <div class="bg-white rounded-xl shadow-md p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Plot Details</h3>
                    <button @click="exportToExcel()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-xs font-medium transition-colors">
                        📊 Export Excel
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-3 py-2">No</th>
                                <th class="border border-gray-300 px-3 py-2">Company</th>
                                <th class="border border-gray-300 px-3 py-2">Blok</th>
                                <th @click="sortBy('plot')" class="border border-gray-300 px-3 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Plot
                                        <span x-html="getSortIcon('plot')"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('plottype')" class="border border-gray-300 px-3 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Type
                                        <span x-html="getSortIcon('plottype')"></span>
                                    </div>
                                </th>
                                <th class="border border-gray-300 px-3 py-2">Batch No</th>
                                <th @click="sortBy('batcharea')" class="border border-gray-300 px-3 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Area<br>(Ha)
                                        <span x-html="getSortIcon('batcharea')"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('lifecyclestatus')" class="border border-gray-300 px-3 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Lifecycle
                                        <span x-html="getSortIcon('lifecyclestatus')"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('kodevarietas')" class="border border-gray-300 px-3 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Varietas
                                        <span x-html="getSortIcon('kodevarietas')"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('pkp')" class="border border-gray-300 px-3 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        PKP
                                        <span x-html="getSortIcon('pkp')"></span>
                                    </div>
                                </th>
                                <th class="border border-gray-300 px-3 py-2">Tanggal<br>Ulang Tahun</th>
                                <th @click="sortBy('age_months')" class="border border-gray-300 px-3 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Age<br>(Months)
                                        <span x-html="getSortIcon('age_months')"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('age_days')" class="border border-gray-300 px-3 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Age<br>(Days)
                                        <span x-html="getSortIcon('age_days')"></span>
                                    </div>
                                </th>
                                <th @click="sortBy('age_months')" class="border border-gray-300 px-3 py-2 cursor-pointer hover:bg-gray-200 select-none">
                                    <div class="flex items-center justify-center gap-1">
                                        Status
                                        <span x-html="getSortIcon('age_months')"></span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in sortedDetails()" :key="`${item.companycode}-${item.batchno}`">
                                <tr class="hover:bg-blue-50 transition-colors">
                                    <td class="border border-gray-300 px-3 py-2 text-center" x-text="index + 1"></td>
                                    <td class="border border-gray-300 px-3 py-2 text-center font-semibold" x-text="item.companycode_formatted"></td>
                                    <td class="border border-gray-300 px-3 py-2 text-center font-semibold" x-text="item.blok"></td>
                                    <td class="border border-gray-300 px-3 py-2 font-semibold" x-text="item.plot"></td>
                                    <td class="border border-gray-300 px-3 py-2 text-center">
                                        <span class="px-2 py-1 rounded text-xs font-semibold" :class="item.plottype === 'KBD' ? 'bg-purple-100 text-purple-800' : 'bg-teal-100 text-teal-800'" x-text="item.plottype || '-'"></span>
                                    </td>
                                    <td class="border border-gray-300 px-3 py-2 text-xs text-gray-600" x-text="item.batchno"></td>
                                    <td class="border border-gray-300 px-3 py-2 text-right font-medium" x-text="parseFloat(item.batcharea).toFixed(2)"></td>
                                    <td class="border border-gray-300 px-3 py-2 text-center">
                                        <span :class="getLifecycleColor(item.lifecyclestatus)" class="px-3 py-1 rounded-full text-xs font-bold" x-text="item.lifecyclestatus"></span>
                                    </td>
                                    <td class="border border-gray-300 px-3 py-2 text-center font-medium" x-text="item.kodevarietas || '-'"></td>
                                    <td class="border border-gray-300 px-3 py-2 text-center" x-text="item.pkp || '-'"></td>
                                    <td class="border border-gray-300 px-3 py-2 text-center" x-text="formatDate(item.tanggalulangtahun)"></td>
                                    <td class="border border-gray-300 px-3 py-2 text-center" x-text="item.age_months"></td>
                                    <td class="border border-gray-300 px-3 py-2 text-center" x-text="item.age_days"></td>
                                    <td class="border border-gray-300 px-3 py-2 text-center">
                                        <span :class="getAgeStatusColor(item.age_months)" class="px-3 py-1 rounded-full text-xs font-bold" x-text="getAgeStatus(item.age_months)"></span>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="!data.details || data.details.length === 0">
                                <tr>
                                    <td colspan="14" class="border border-gray-300 px-3 py-8 text-center text-gray-500 font-medium">No data available</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
    function masterLahanReport() {
        return {
            loading: true,
            sortColumn: '',
            sortDirection: 'asc',
            data: {
                summary: {},
                details: [],
                filterOptions: {},
                lifecycleChart: [],
                varietasChart: [],
                plottypeChart: [],
                ageDistribution: {}
            },
            filters: {
                group: '',
                blok: '',
                varietas: '',
                lifecycle: '',
                plottype: '',
                pkp: '',
                age_min: '',
                age_max: ''
            },
            appliedFilters: {
                group: ''
            },
            charts: {
                lifecycle: null,
                varietas: null,
                plottype: null,
                age: null
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
                const numericCols = ['batcharea', 'pkp', 'age_months', 'age_days'];

                return [...this.data.details].sort((a, b) => {
                    let valA = a[col];
                    let valB = b[col];

                    if (valA == null && valB == null) return 0;
                    if (valA == null) return 1;
                    if (valB == null) return -1;

                    if (numericCols.includes(col)) {
                        return (parseFloat(valA) - parseFloat(valB)) * dir;
                    }

                    return String(valA).localeCompare(String(valB), 'id') * dir;
                });
            },

            async loadData() {
                this.loading = true;
                try {
                    const params = new URLSearchParams(this.filters);
                    const response = await fetch(`{{ route('report.report-manajemen-lahan.data') }}?${params}`);
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
                    alert('Failed to load data');
                } finally {
                    this.loading = false;
                }
            },

            getCurrentCompanyTitle() {
                if (this.appliedFilters.group === 'all-tbl') {
                    return 'TBL Group (TBL I, TBL II & TBL III)';
                } else if (this.appliedFilters.group === 'all-divisi') {
                    return 'All Division';
                } else {
                    return '{{ formatCompanyCode(Session::get("companycode")) }}';
                }
            },

            renderCharts() {
                this.renderLifecycleChart();
                this.renderVarietasChart();
                this.renderPlottypeChart();
                this.renderAgeChart();
            },

            renderLifecycleChart() {
                const ctx = document.getElementById('lifecycleChart');
                if (!ctx) return;
                if (this.charts.lifecycle) { this.charts.lifecycle.destroy(); }

                this.charts.lifecycle = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.data.lifecycleChart.map(d => d.name),
                        datasets: [
                            { label: 'Plot Count', data: this.data.lifecycleChart.map(d => d.value), backgroundColor: '#3b82f6', borderRadius: 6, yAxisID: 'y' },
                            { label: 'Area (Ha)', data: this.data.lifecycleChart.map(d => d.area), backgroundColor: '#10b981', borderRadius: 6, yAxisID: 'y1' }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: true,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'top', labels: { font: { size: 12, weight: 'bold' } } },
                            tooltip: { callbacks: { label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y.toFixed(2)}` } }
                        },
                        scales: {
                            y: { type: 'linear', display: true, position: 'left', title: { display: true, text: 'Plot Count', font: { size: 11 } }, grid: { color: '#f3f4f6' } },
                            y1: { type: 'linear', display: true, position: 'right', title: { display: true, text: 'Area (Ha)', font: { size: 11 } }, grid: { drawOnChartArea: false } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            },

            renderVarietasChart() {
                const ctx = document.getElementById('varietasChart');
                if (!ctx) return;
                if (this.charts.varietas) { this.charts.varietas.destroy(); }

                this.charts.varietas = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.data.varietasChart.map(d => d.name),
                        datasets: [{ label: 'Area (Ha)', data: this.data.varietasChart.map(d => d.area), backgroundColor: '#f97316', borderRadius: 6, borderWidth: 0 }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: ctx => { const i = this.data.varietasChart[ctx.dataIndex]; return `${i.plots} plots - ${i.area.toFixed(2)} Ha`; } } }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f3f4f6' }, title: { display: true, text: 'Area (Ha)', font: { size: 11 } } },
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                        }
                    }
                });
            },

            renderPlottypeChart() {
                const ctx = document.getElementById('plottypeChart');
                if (!ctx) return;
                if (this.charts.plottype) { this.charts.plottype.destroy(); }

                this.charts.plottype = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.data.plottypeChart?.map(d => d.name) || [],
                        datasets: [{ label: 'Plot Count', data: this.data.plottypeChart?.map(d => d.value) || [], backgroundColor: ['#1f2937', '#6b7280'], borderRadius: 6 }]
                    },
                    options: {
                        indexAxis: 'y', responsive: true, maintainAspectRatio: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: ctx => { const i = this.data.plottypeChart[ctx.dataIndex]; return `${i.value} plots (${i.area.toFixed(2)} Ha)`; } } }
                        },
                        scales: {
                            x: { beginAtZero: true, grid: { color: '#f3f4f6' }, title: { display: true, text: 'Number of Plots', font: { size: 11 } } },
                            y: { grid: { display: false } }
                        }
                    }
                });
            },

            renderAgeChart() {
                const ctx = document.getElementById('ageChart');
                if (!ctx) return;
                if (this.charts.age) { this.charts.age.destroy(); }

                const ad = this.data.ageDistribution || {};

                this.charts.age = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['0-3', '4-6', '7-12', '>12'],
                        datasets: [{
                            label: 'Plot Count',
                            data: [ad.young || 0, ad.growing || 0, ad.mature || 0, ad.overdue || 0],
                            backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                            borderWidth: 0, barPercentage: 1.0, categoryPercentage: 1.0
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { title: items => { const l = ['Young (0-3 months)', 'Growing (4-6 months)', 'Mature (7-12 months)', 'Overdue (>12 months)']; return l[items[0].dataIndex]; } } }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f3f4f6' }, title: { display: true, text: 'Number of Plots', font: { size: 11 } } },
                            x: { grid: { display: false }, title: { display: true, text: 'Age Range (Months)', font: { size: 11 } } }
                        }
                    }
                });
            },

            applyFilters() { this.loadData(); },

            resetFilters() {
                this.filters = { group: '', blok: '', varietas: '', lifecycle: '', plottype: '', pkp: '', age_min: '', age_max: '' };
                this.sortColumn = '';
                this.sortDirection = 'asc';
                this.loadData();
            },

            formatArea(area) {
                return area ? `${parseFloat(area).toFixed(2)} Ha` : '0.00 Ha';
            },

            formatDate(date) {
                if (!date) return '-';
                return new Date(date).toLocaleDateString('id-ID');
            },

            getLifecycleColor(lifecycle) {
                const c = { 'PC': 'bg-emerald-100 text-emerald-800', 'RC1': 'bg-blue-100 text-blue-800', 'RC2': 'bg-amber-100 text-amber-800', 'RC3': 'bg-rose-100 text-rose-800' };
                return c[lifecycle] || 'bg-gray-100 text-gray-800';
            },

            getAgeStatus(months) {
                if (months <= 3) return 'Young';
                if (months <= 6) return 'Growing';
                if (months <= 12) return 'Mature';
                return 'Overdue';
            },

            getAgeStatusColor(months) {
                if (months <= 3) return 'bg-green-100 text-green-700';
                if (months <= 6) return 'bg-blue-100 text-blue-700';
                if (months <= 12) return 'bg-amber-100 text-amber-700';
                return 'bg-red-100 text-red-700';
            },

            exportToExcel() {
                const blob = new Blob([document.querySelector('table').outerHTML], { type: 'application/vnd.ms-excel' });
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = `Master_Lahan_Report_${new Date().toISOString().split('T')[0]}.xls`;
                a.click();
            }
        }
    }
    </script>
</x-layout>