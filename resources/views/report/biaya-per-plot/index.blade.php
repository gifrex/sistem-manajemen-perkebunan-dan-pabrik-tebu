<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div x-data="biayaPerPlot()" class="space-y-4">
        
        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex flex-col gap-4">
                
                <!-- Generation Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cycle Batch</label>
                    <div class="flex flex-wrap gap-3">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="generation" value="0" x-model="selectedGeneration" class="text-gray-700 focus:ring-gray-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Current Cycle</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="generation" value="-1" x-model="selectedGeneration" class="text-gray-700 focus:ring-gray-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Last Cycle (-1)</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="generation" value="-2" x-model="selectedGeneration" class="text-gray-700 focus:ring-gray-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Cycle -2</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="generation" value="-3" x-model="selectedGeneration" class="text-gray-700 focus:ring-gray-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Cycle -3</span>
                        </label>
                    </div>
                </div>

                <!-- Blok Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Blok</label>
                    <div class="flex flex-wrap gap-2">
                        <label class="inline-flex items-center px-3 py-1.5 bg-gray-100 rounded cursor-pointer hover:bg-gray-200 transition-colors">
                            <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()" class="rounded border-gray-300 text-gray-700 focus:ring-gray-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Semua</span>
                        </label>
                        @foreach($bloks as $blok)
                        <label class="inline-flex items-center px-3 py-1.5 rounded cursor-pointer transition-colors border"
                               :class="selectedBloks.includes('{{ $blok->blok }}') ? 'border-gray-700 bg-gray-100' : 'border-gray-200 bg-white hover:bg-gray-50'">
                            <input type="checkbox" value="{{ $blok->blok }}" x-model="selectedBloks" @change="updateSelectAll()" class="rounded border-gray-300 text-gray-700 focus:ring-gray-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">{{ $blok->blok }}</span>
                            <span class="ml-1 text-xs text-gray-400">({{ $blok->plot_count }})</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <button @click="loadData()" :disabled="selectedBloks.length === 0 || loading"
                            class="px-5 py-2 bg-gray-800 text-white rounded hover:bg-gray-900 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors flex items-center gap-2 font-medium">
                        <svg x-show="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="loading ? 'Memuat...' : 'Tampilkan Data'"></span>
                    </button>
                    
                    <!-- Export Buttons -->
                    <div x-show="dataLoaded" class="flex gap-2">
                        <button @click="exportExcel()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors flex items-center gap-2 font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Excel</span>
                        </button>
                        <button @click="exportPdf()" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors flex items-center gap-2 font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <span>PDF</span>
                        </button>
                    </div>
                    
                    <span x-show="selectedBloks.length > 0" class="text-sm text-gray-500" x-text="selectedBloks.length + ' blok dipilih'"></span>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex justify-center items-center py-12">
            <div class="text-center">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-gray-700 mx-auto mb-3"></div>
                <p class="text-sm text-gray-500">Memuat data biaya...</p>
            </div>
        </div>

        <!-- Results Section -->
        <div x-show="!loading && dataLoaded" x-transition class="space-y-4">
            
            <!-- Grand Total Card -->
            <div class="bg-gray-800 rounded-lg shadow p-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h3 class="text-white font-semibold text-lg">Ringkasan Total</h3>
                        <p class="text-gray-400 text-sm">
                            <span x-text="grandTotal.plots_with_batch + '/' + grandTotal.total_plot + ' Plot'"></span>
                            <span class="ml-2 text-xs" x-text="'(' + generationLabel + ')'"></span>
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-6 text-sm">
                        <div class="text-center">
                            <p class="text-gray-400 text-xs uppercase tracking-wide">Biaya TK</p>
                            <p class="text-white font-semibold" x-text="formatRupiah(grandTotal.biaya_tk)"></p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-400 text-xs uppercase tracking-wide">Biaya Material</p>
                            <p class="text-white font-semibold" x-text="formatRupiah(grandTotal.biaya_material)"></p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-400 text-xs uppercase tracking-wide">Biaya Panen</p>
                            <p class="text-white font-semibold" x-text="formatRupiah(grandTotal.biaya_panen)"></p>
                        </div>
                        <div class="text-center border-l border-gray-600 pl-6">
                            <p class="text-gray-400 text-xs uppercase tracking-wide">Total Biaya</p>
                            <p class="text-white font-bold text-lg" x-text="formatRupiah(grandTotal.total_biaya)"></p>
                        </div>
                        <div class="text-center border-l border-gray-600 pl-6">
                            <p class="text-gray-400 text-xs uppercase tracking-wide">Total Panen</p>
                            <p class="text-white font-bold text-lg"><span x-text="formatNumber(grandTotal.total_ton)"></span> <span class="text-xs font-normal">ton</span></p>
                        </div>
                        <div class="text-center border-l border-gray-600 pl-6">
                            <p class="text-gray-400 text-xs uppercase tracking-wide">Avg YPH</p>
                            <p class="text-white font-bold text-lg"><span x-text="formatNumber(grandTotal.avg_yph)"></span> <span class="text-xs font-normal">ton/ha</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Per Blok -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="summary in summaryPerBlok" :key="summary.blok">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-bold text-gray-800 text-lg" x-text="'Blok ' + summary.blok"></h4>
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full" x-text="summary.total_plot + ' plot'"></span>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Biaya TK</span>
                                <span class="font-medium text-gray-700" x-text="formatRupiah(summary.biaya_tk)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Biaya Material</span>
                                <span class="font-medium text-gray-700" x-text="formatRupiah(summary.biaya_material)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Biaya Panen</span>
                                <span class="font-medium text-gray-700" x-text="formatRupiah(summary.biaya_panen)"></span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-gray-100 mt-2">
                                <span class="text-gray-800 font-semibold">Total Biaya</span>
                                <span class="font-bold text-gray-800" x-text="formatRupiah(summary.total_biaya)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Total Panen</span>
                                <span class="font-medium text-green-600" x-text="formatNumber(summary.total_ton) + ' ton'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Avg YPH</span>
                                <span class="font-bold text-blue-600" x-text="formatNumber(summary.avg_yph) + ' ton/ha'"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Detail Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Detail Biaya Per Plot</h3>
                    <span class="text-xs text-gray-500" x-text="plots.length + ' data'"></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th rowspan="2" class="px-2 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide border-r border-gray-200 w-12">Blok</th>
                                <th rowspan="2" class="px-2 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide border-r border-gray-200 w-16">Plot</th>
                                <th rowspan="2" class="px-2 py-2 text-center text-xs font-semibold text-gray-600 uppercase tracking-wide border-r border-gray-200 w-14">Status</th>
                                <th rowspan="2" class="px-2 py-2 text-center text-xs font-semibold text-gray-600 uppercase tracking-wide border-r border-gray-200 w-20">Umur (Bln)</th>
                                <th rowspan="2" class="px-2 py-2 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide border-r border-gray-200 w-16">Luas</th>
                                <th rowspan="2" class="px-2 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide border-r border-gray-200 w-20">Varietas</th>
                                <th colspan="4" class="px-2 py-2 text-center text-xs font-semibold text-gray-600 uppercase tracking-wide border-r border-gray-200 bg-gray-100">Biaya</th>
                                <th colspan="2" class="px-2 py-2 text-center text-xs font-semibold text-gray-600 uppercase tracking-wide bg-gray-100">Info Hasil Panen</th>
                            </tr>
                            <tr class="bg-gray-50 border-b border-gray-300">
                                <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 border-r border-gray-200">TK</th>
                                <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 border-r border-gray-200">Material</th>
                                <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 border-r border-gray-200">Panen</th>
                                <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 border-r border-gray-200">Total</th>
                                <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 border-r border-gray-200">Ton</th>
                                <th class="px-2 py-2 text-right text-xs font-medium text-gray-500">YPH (Ton/ha)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="plot in plots" :key="plot.batchno">
                                <tr class="hover:bg-gray-50 transition-colors" :class="!plot.has_batch ? 'bg-gray-50' : ''">
                                    <td class="px-2 py-2 text-gray-600 border-r border-gray-100" x-text="plot.blok"></td>
                                    <td class="px-2 py-2 border-r border-gray-100">
                                        <template x-if="plot.has_batch">
                                            <a :href="`{{ url('report/biaya-per-plot') }}/${plot.batchno}?cycle=${selectedGeneration}`" 
                                               class="font-medium text-blue-600 hover:text-blue-800 underline hover:no-underline" 
                                               x-text="plot.plot"></a>
                                        </template>
                                        <template x-if="!plot.has_batch">
                                            <span class="font-medium text-gray-400" x-text="plot.plot"></span>
                                        </template>
                                    </td>
                                    <td class="px-2 py-2 text-center border-r border-gray-100">
                                        <template x-if="plot.has_batch">
                                            <span class="px-2 py-0.5 text-xs font-medium rounded"
                                                  :class="{
                                                      'bg-emerald-100 text-emerald-700': plot.lifecyclestatus === 'PC',
                                                      'bg-sky-100 text-sky-700': plot.lifecyclestatus === 'RC1',
                                                      'bg-amber-100 text-amber-700': plot.lifecyclestatus === 'RC2',
                                                      'bg-rose-100 text-rose-700': plot.lifecyclestatus === 'RC3'
                                                  }"
                                                  x-text="plot.lifecyclestatus"></span>
                                        </template>
                                        <template x-if="!plot.has_batch">
                                            <span class="text-xs text-gray-400 italic">-</span>
                                        </template>
                                    </td>
                                    <td class="px-2 py-2 text-center border-r border-gray-100" :class="plot.has_batch ? 'text-gray-600' : 'text-gray-300'" x-text="plot.umur_bulan ?? '-'"></td>
                                    <td class="px-2 py-2 text-right border-r border-gray-100" :class="plot.has_batch ? 'text-gray-700' : 'text-gray-300'" x-text="plot.batcharea ? parseFloat(plot.batcharea).toFixed(2) : '-'"></td>
                                    <td class="px-2 py-2 border-r border-gray-100" :class="plot.has_batch ? 'text-gray-600' : 'text-gray-300'" x-text="plot.kodevarietas || '-'"></td>
                                    <td class="px-2 py-2 text-right border-r border-gray-100" :class="plot.biaya_tk > 0 ? 'text-gray-700' : 'text-gray-300'" x-text="formatRupiah(plot.biaya_tk)"></td>
                                    <td class="px-2 py-2 text-right border-r border-gray-100" :class="plot.biaya_material > 0 ? 'text-gray-700' : 'text-gray-300'" x-text="formatRupiah(plot.biaya_material)"></td>
                                    <td class="px-2 py-2 text-right border-r border-gray-100" :class="plot.biaya_panen > 0 ? 'text-gray-700' : 'text-gray-300'" x-text="formatRupiah(plot.biaya_panen)"></td>
                                    <td class="px-2 py-2 text-right font-semibold border-r border-gray-100" :class="plot.total_biaya > 0 ? 'text-gray-800' : 'text-gray-300'" x-text="formatRupiah(plot.total_biaya)"></td>
                                    <td class="px-2 py-2 text-right border-r border-gray-100" :class="plot.total_ton > 0 ? 'text-gray-700' : 'text-gray-300'" x-text="plot.total_ton > 0 ? formatNumber(plot.total_ton) : '-'"></td>
                                    <td class="px-2 py-2 text-right" :class="plot.yph > 0 ? 'text-blue-600 font-bold' : 'text-gray-300'" x-text="plot.yph > 0 ? formatNumber(plot.yph) : '-'"></td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-gray-100 border-t-2 border-gray-300">
                            <tr class="font-semibold text-gray-800">
                                <td colspan="6" class="px-2 py-2 text-right border-r border-gray-200">TOTAL / AVERAGE</td>
                                <td class="px-2 py-2 text-right border-r border-gray-200" x-text="formatRupiah(grandTotal.biaya_tk)"></td>
                                <td class="px-2 py-2 text-right border-r border-gray-200" x-text="formatRupiah(grandTotal.biaya_material)"></td>
                                <td class="px-2 py-2 text-right border-r border-gray-200" x-text="formatRupiah(grandTotal.biaya_panen)"></td>
                                <td class="px-2 py-2 text-right font-bold border-r border-gray-200" x-text="formatRupiah(grandTotal.total_biaya)"></td>
                                <td class="px-2 py-2 text-right border-r border-gray-200" x-text="formatNumber(grandTotal.total_ton)"></td>
                                <td class="px-2 py-2 text-right font-bold text-blue-600" x-text="formatNumber(grandTotal.avg_yph)"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && !dataLoaded" class="bg-white rounded-lg shadow-sm p-12 text-center border border-gray-200">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Pilih Blok untuk Melihat Data</h3>
            <p class="text-gray-500">Centang blok yang ingin ditampilkan, lalu klik tombol "Tampilkan Data"</p>
        </div>
    </div>

    <script>
    function biayaPerPlot() {
        return {
            loading: false,
            dataLoaded: false,
            selectAll: false,
            selectedBloks: [],
            selectedGeneration: '0',
            allBloks: @json($bloks->pluck('blok')),
            plots: [],
            summaryPerBlok: [],
            grandTotal: {
                total_plot: 0,
                plots_with_batch: 0,
                biaya_tk: 0,
                biaya_material: 0,
                biaya_panen: 0,
                total_biaya: 0,
                total_ton: 0,
                avg_yph: 0
            },

            get generationLabel() {
                const labels = {
                    '0': 'Current Cycle',
                    '-1': 'Last Cycle',
                    '-2': 'Cycle -2',
                    '-3': 'Cycle -3'
                };
                return labels[this.selectedGeneration] || 'Current Cycle';
            },

            toggleSelectAll() {
                this.selectedBloks = this.selectAll ? [...this.allBloks] : [];
            },

            updateSelectAll() {
                this.selectAll = this.selectedBloks.length === this.allBloks.length;
            },

            async loadData() {
                if (this.selectedBloks.length === 0) return;
                this.loading = true;
                this.dataLoaded = false;
                
                try {
                    const response = await fetch(`{{ route('report.biaya-per-plot.data') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ 
                            bloks: this.selectedBloks,
                            generation: parseInt(this.selectedGeneration)
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.plots = result.data.plots;
                        this.summaryPerBlok = result.data.summaryPerBlok;
                        this.grandTotal = result.data.grandTotal;
                        this.dataLoaded = true;
                    } else {
                        alert(result.message || 'Gagal memuat data');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan');
                } finally {
                    this.loading = false;
                }
            },

            formatRupiah(value) {
                if (!value || value === 0) return 'Rp 0';
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(value));
            },

            formatNumber(value) {
                if (!value || value === 0) return '0.00';
                return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
            },

            exportExcel() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("report.biaya-per-plot.export-excel") }}';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);
                
                const bloksInput = document.createElement('input');
                bloksInput.type = 'hidden';
                bloksInput.name = 'bloks';
                bloksInput.value = JSON.stringify(this.selectedBloks);
                form.appendChild(bloksInput);
                
                const generationInput = document.createElement('input');
                generationInput.type = 'hidden';
                generationInput.name = 'generation';
                generationInput.value = this.selectedGeneration;
                form.appendChild(generationInput);
                
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            },

            exportPdf() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("report.biaya-per-plot.export-pdf") }}';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);
                
                const bloksInput = document.createElement('input');
                bloksInput.type = 'hidden';
                bloksInput.name = 'bloks';
                bloksInput.value = JSON.stringify(this.selectedBloks);
                form.appendChild(bloksInput);
                
                const generationInput = document.createElement('input');
                generationInput.type = 'hidden';
                generationInput.name = 'generation';
                generationInput.value = this.selectedGeneration;
                form.appendChild(generationInput);
                
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }
        }
    }
    </script>
</x-layout>