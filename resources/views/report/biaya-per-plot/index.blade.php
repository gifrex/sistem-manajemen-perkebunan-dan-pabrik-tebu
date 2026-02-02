<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div x-data="biayaPerPlot()" class="space-y-4">
        
        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex flex-col gap-4">
                
                <!-- Blok Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Blok</label>
                    <div class="flex flex-wrap gap-2">
                        <!-- Select All Checkbox -->
                        <label class="inline-flex items-center px-3 py-1.5 bg-gray-100 rounded-lg cursor-pointer hover:bg-gray-200 transition-colors">
                            <input type="checkbox" 
                                   x-model="selectAll" 
                                   @change="toggleSelectAll()"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Semua</span>
                        </label>
                        
                        <!-- Individual Blok Checkboxes -->
                        @foreach($bloks as $blok)
                        <label class="inline-flex items-center px-3 py-1.5 rounded-lg cursor-pointer transition-colors border"
                               :class="selectedBloks.includes('{{ $blok->blok }}') ? 'border-blue-500 bg-blue-50 hover:bg-blue-100' : 'border-gray-200 bg-gray-50 hover:bg-gray-100'">
                            <input type="checkbox" 
                                   value="{{ $blok->blok }}" 
                                   x-model="selectedBloks"
                                   @change="updateSelectAll()"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">{{ $blok->blok }}</span>
                            <span class="ml-1 text-xs text-gray-400">({{ $blok->plot_count }})</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Action Button -->
                <div class="flex items-center gap-3">
                    <button @click="loadData()" 
                            :disabled="selectedBloks.length === 0 || loading"
                            class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors flex items-center gap-2 font-medium">
                        <svg x-show="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="loading ? 'Memuat...' : 'Tampilkan Data'"></span>
                    </button>
                    <span x-show="selectedBloks.length > 0" class="text-sm text-gray-500" x-text="selectedBloks.length + ' blok dipilih'"></span>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex justify-center items-center py-12">
            <div class="text-center">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600 mx-auto mb-3"></div>
                <p class="text-sm text-gray-500">Memuat data biaya...</p>
            </div>
        </div>

        <!-- Results Section -->
        <div x-show="!loading && dataLoaded" x-transition class="space-y-4">
            
            <!-- Grand Total Card -->
            <div class="bg-slate-800 rounded-lg shadow-md p-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h3 class="text-white font-bold text-lg">Grand Total</h3>
                        <p class="text-slate-400 text-sm" x-text="grandTotal.total_plot + ' Plot (Active Batch)'"></p>
                    </div>
                    <div class="flex flex-wrap gap-6">
                        <div class="text-center">
                            <p class="text-slate-400 text-xs uppercase">Biaya TK</p>
                            <p class="text-white text-lg font-bold" x-text="formatRupiah(grandTotal.biaya_tk)"></p>
                        </div>
                        <div class="text-center">
                            <p class="text-slate-400 text-xs uppercase">Biaya Material</p>
                            <p class="text-white text-lg font-bold" x-text="formatRupiah(grandTotal.biaya_material)"></p>
                        </div>
                        <div class="text-center">
                            <p class="text-slate-400 text-xs uppercase">Biaya Kontraktor</p>
                            <p class="text-white text-lg font-bold" x-text="formatRupiah(grandTotal.biaya_kontraktor)"></p>
                        </div>
                        <div class="text-center border-l border-slate-600 pl-6">
                            <p class="text-slate-400 text-xs uppercase">Total Biaya</p>
                            <p class="text-emerald-400 text-xl font-bold" x-text="formatRupiah(grandTotal.total_biaya)"></p>
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
                                <span class="text-gray-500">Biaya Kontraktor</span>
                                <span class="font-medium text-gray-700" x-text="formatRupiah(summary.biaya_kontraktor)"></span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-gray-100 mt-2">
                                <span class="text-gray-800 font-semibold">Total</span>
                                <span class="font-bold text-blue-600" x-text="formatRupiah(summary.total_biaya)"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Detail Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Detail Per Plot</h3>
                    <span class="text-xs text-gray-500" x-text="'Total: ' + plots.length + ' plot'"></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Blok</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plot</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Varietas</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Luas (Ha)</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Umur</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Biaya TK</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Biaya Material</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Biaya Kontraktor</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Biaya</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="plot in plots" :key="plot.batchno">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-600" x-text="plot.blok"></td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        <a :href="'{{ url('report/biaya-per-plot') }}/' + plot.batchno" 
                                           class="text-blue-600 hover:text-blue-800 hover:underline"
                                           x-text="plot.plot"></a>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                              :class="{
                                                  'bg-green-100 text-green-800': plot.lifecyclestatus === 'PC',
                                                  'bg-blue-100 text-blue-800': plot.lifecyclestatus === 'RC1',
                                                  'bg-purple-100 text-purple-800': plot.lifecyclestatus === 'RC2',
                                                  'bg-orange-100 text-orange-800': plot.lifecyclestatus === 'RC3'
                                              }"
                                              x-text="plot.lifecyclestatus"></span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600" x-text="plot.kodevarietas || '-'"></td>
                                    <td class="px-4 py-3 text-sm text-gray-600" x-text="plot.batcharea ? parseFloat(plot.batcharea).toFixed(2) : '-'"></td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <span x-text="plot.umur_bulan ? plot.umur_bulan + ' bln' : (plot.umur_hari ? plot.umur_hari + ' hr' : '-')"></span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right" 
                                        :class="plot.biaya_tk > 0 ? 'text-gray-900' : 'text-gray-400'"
                                        x-text="formatRupiah(plot.biaya_tk)"></td>
                                    <td class="px-4 py-3 text-sm text-right"
                                        :class="plot.biaya_material > 0 ? 'text-gray-900' : 'text-gray-400'"
                                        x-text="formatRupiah(plot.biaya_material)"></td>
                                    <td class="px-4 py-3 text-sm text-right"
                                        :class="plot.biaya_kontraktor > 0 ? 'text-gray-900' : 'text-gray-400'"
                                        x-text="formatRupiah(plot.biaya_kontraktor)"></td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold"
                                        :class="plot.total_biaya > 0 ? 'text-blue-600' : 'text-gray-400'"
                                        x-text="formatRupiah(plot.total_biaya)"></td>
                                </tr>
                            </template>
                        </tbody>
                        <!-- Table Footer Total -->
                        <tfoot class="bg-gray-100">
                            <tr class="font-semibold">
                                <td colspan="6" class="px-4 py-3 text-sm text-gray-700">TOTAL</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900" x-text="formatRupiah(grandTotal.biaya_tk)"></td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900" x-text="formatRupiah(grandTotal.biaya_material)"></td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900" x-text="formatRupiah(grandTotal.biaya_kontraktor)"></td>
                                <td class="px-4 py-3 text-sm text-right text-blue-600 font-bold" x-text="formatRupiah(grandTotal.total_biaya)"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>

        <!-- Empty State -->
        <div x-show="!loading && !dataLoaded" class="bg-white rounded-lg shadow-sm p-12 text-center">
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
            allBloks: @json($bloks->pluck('blok')),
            
            plots: [],
            summaryPerBlok: [],
            grandTotal: {
                total_plot: 0,
                biaya_tk: 0,
                biaya_material: 0,
                biaya_kontraktor: 0,
                total_biaya: 0
            },

            toggleSelectAll() {
                if (this.selectAll) {
                    this.selectedBloks = [...this.allBloks];
                } else {
                    this.selectedBloks = [];
                }
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
                            bloks: this.selectedBloks
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
                    console.error('Load data error:', error);
                    alert('Terjadi kesalahan saat memuat data');
                } finally {
                    this.loading = false;
                }
            },

            formatRupiah(value) {
                if (!value || value === 0) return 'Rp 0';
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(value));
            }
        }
    }
    </script>
</x-layout>