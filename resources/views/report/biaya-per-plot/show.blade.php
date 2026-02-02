<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div x-data="detailBiaya()" x-init="loadData()" class="space-y-4">

        <!-- Back Button & Header -->
        <div class="flex items-center gap-4">
            <a href="{{ route('report.biaya-per-plot.index') }}" class="p-2 bg-white rounded-lg shadow-sm border border-gray-200 hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-800">Detail Biaya Plot {{ $batch->plot }}</h1>
                <p class="text-sm text-gray-500">Batch: {{ $batch->batchno }}</p>
            </div>
        </div>

        <!-- Loading -->
        <div x-show="loading" class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
        </div>

        <!-- Content -->
        <div x-show="!loading" x-transition class="space-y-4">

            <!-- Batch Info Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h3 class="font-semibold text-gray-800 mb-3">Informasi Batch</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Plot</p>
                        <p class="font-semibold text-gray-800">{{ $batch->blok ?? '-' }} / {{ $batch->plot }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Batch No</p>
                        <p class="font-semibold text-gray-800">{{ $batch->batchno }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Status</p>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            @if($batch->lifecyclestatus == 'PC') bg-green-100 text-green-800
                            @elseif($batch->lifecyclestatus == 'RC1') bg-blue-100 text-blue-800
                            @elseif($batch->lifecyclestatus == 'RC2') bg-purple-100 text-purple-800
                            @else bg-orange-100 text-orange-800 @endif">
                            {{ $batch->lifecyclestatus }}
                        </span>
                    </div>
                    <div>
                        <p class="text-gray-500">Varietas</p>
                        <p class="font-semibold text-gray-800">{{ $batch->kodevarietas ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Luas</p>
                        <p class="font-semibold text-gray-800">{{ $batch->batcharea ? number_format($batch->batcharea, 2) : '-' }} Ha</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Tgl Tanam</p>
                        <p class="font-semibold text-gray-800">{{ $batch->tanggalulangtahun ? \Carbon\Carbon::parse($batch->tanggalulangtahun)->format('d M Y') : '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="bg-slate-800 rounded-lg shadow-md p-4">
                <h3 class="text-white font-semibold mb-3">Ringkasan Biaya</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-slate-700 rounded-lg p-3 text-center">
                        <p class="text-slate-400 text-xs uppercase">Biaya TK</p>
                        <p class="text-white text-lg font-bold" x-text="formatRupiah(summary.biaya_tk)"></p>
                    </div>
                    <div class="bg-slate-700 rounded-lg p-3 text-center">
                        <p class="text-slate-400 text-xs uppercase">Biaya Material</p>
                        <p class="text-white text-lg font-bold" x-text="formatRupiah(summary.biaya_material)"></p>
                    </div>
                    <div class="bg-slate-700 rounded-lg p-3 text-center">
                        <p class="text-slate-400 text-xs uppercase">Biaya Kontraktor</p>
                        <p class="text-white text-lg font-bold" x-text="formatRupiah(summary.biaya_kontraktor)"></p>
                    </div>
                    <div class="bg-emerald-600 rounded-lg p-3 text-center">
                        <p class="text-emerald-200 text-xs uppercase">Total Biaya</p>
                        <p class="text-white text-xl font-bold" x-text="formatRupiah(summary.total)"></p>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button @click="activeTab = 'tk'" 
                                :class="activeTab === 'tk' ? 'border-blue-500 text-blue-600 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                                class="px-6 py-3 border-b-2 font-medium text-sm transition-colors">
                            Tenaga Kerja
                            <span class="ml-1 px-2 py-0.5 text-xs rounded-full" 
                                  :class="activeTab === 'tk' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'"
                                  x-text="lkhDetails.length"></span>
                        </button>
                        <button @click="activeTab = 'material'" 
                                :class="activeTab === 'material' ? 'border-blue-500 text-blue-600 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                                class="px-6 py-3 border-b-2 font-medium text-sm transition-colors">
                            Material
                            <span class="ml-1 px-2 py-0.5 text-xs rounded-full"
                                  :class="activeTab === 'material' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'"
                                  x-text="materialDetails.length"></span>
                        </button>
                        <button @click="activeTab = 'kontraktor'" 
                                :class="activeTab === 'kontraktor' ? 'border-blue-500 text-blue-600 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                                class="px-6 py-3 border-b-2 font-medium text-sm transition-colors">
                            Kontraktor (Panen)
                            <span class="ml-1 px-2 py-0.5 text-xs rounded-full"
                                  :class="activeTab === 'kontraktor' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'"
                                  x-text="kontraktorDetails.length"></span>
                        </button>
                    </nav>
                </div>

                <!-- Tab: Tenaga Kerja -->
                <div x-show="activeTab === 'tk'" class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">LKH No</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Aktivitas</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Pekerja</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Luas Hasil</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total Luas LKH</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Proporsi</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total Upah LKH</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Biaya Proporsional</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="item in lkhDetails" :key="item.lkhno">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 text-sm text-gray-600" x-text="formatDate(item.lkhdate)"></td>
                                        <td class="px-3 py-2 text-sm font-medium text-blue-600" x-text="item.lkhno"></td>
                                        <td class="px-3 py-2 text-sm text-gray-800">
                                            <span x-text="item.activitycode"></span>
                                            <span class="text-gray-500 text-xs block" x-text="item.activityname"></span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-0.5 text-xs rounded-full"
                                                  :class="item.jenistenagakerja === 'Harian' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'"
                                                  x-text="item.jenistenagakerja"></span>
                                        </td>
                                        <td class="px-3 py-2 text-sm text-right text-gray-600" x-text="item.totalworkers || '-'"></td>
                                        <td class="px-3 py-2 text-sm text-right text-gray-800" x-text="item.luashasil + ' Ha'"></td>
                                        <td class="px-3 py-2 text-sm text-right text-gray-600" x-text="item.total_luas_lkh + ' Ha'"></td>
                                        <td class="px-3 py-2 text-sm text-right font-medium text-orange-600" x-text="item.proporsi + '%'"></td>
                                        <td class="px-3 py-2 text-sm text-right text-gray-600" x-text="formatRupiah(item.totalupahall)"></td>
                                        <td class="px-3 py-2 text-sm text-right font-semibold text-green-600" x-text="formatRupiah(item.biaya_proporsional)"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-gray-100">
                                <tr>
                                    <td colspan="9" class="px-3 py-2 text-sm font-semibold text-gray-700">Total Biaya TK</td>
                                    <td class="px-3 py-2 text-sm text-right font-bold text-green-600" x-text="formatRupiah(summary.biaya_tk)"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div x-show="lkhDetails.length === 0" class="text-center py-8 text-gray-500">
                        Tidak ada data LKH untuk batch ini
                    </div>
                </div>

                <!-- Tab: Material -->
                <div x-show="activeTab === 'material'" class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">LKH No</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kode Item</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Item</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Harga Satuan</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total Biaya</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(item, idx) in materialDetails" :key="idx">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 text-sm text-gray-600" x-text="formatDate(item.lkhdate)"></td>
                                        <td class="px-3 py-2 text-sm font-medium text-blue-600" x-text="item.lkhno"></td>
                                        <td class="px-3 py-2 text-sm text-gray-800" x-text="item.itemcode"></td>
                                        <td class="px-3 py-2 text-sm text-gray-600" x-text="item.itemname"></td>
                                        <td class="px-3 py-2 text-sm text-right text-gray-800" x-text="item.qtydigunakan + ' ' + (item.measure || '')"></td>
                                        <td class="px-3 py-2 text-sm text-right text-gray-600" x-text="formatRupiah(item.itemprice)"></td>
                                        <td class="px-3 py-2 text-sm text-right font-semibold text-green-600" x-text="formatRupiah(item.total_biaya)"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-gray-100">
                                <tr>
                                    <td colspan="6" class="px-3 py-2 text-sm font-semibold text-gray-700">Total Biaya Material</td>
                                    <td class="px-3 py-2 text-sm text-right font-bold text-green-600" x-text="formatRupiah(summary.biaya_material)"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div x-show="materialDetails.length === 0" class="text-center py-8 text-gray-500">
                        Tidak ada data material untuk batch ini
                    </div>
                </div>

                <!-- Tab: Kontraktor -->
                <div x-show="activeTab === 'kontraktor'" class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Surat Jalan</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kontraktor</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Supir</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nopol</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Berat (Ton)</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total Biaya</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(item, idx) in kontraktorDetails" :key="idx">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 text-sm text-gray-600" x-text="formatDate(item.tanggalangkut)"></td>
                                        <td class="px-3 py-2 text-sm font-medium text-blue-600" x-text="item.suratjalanno"></td>
                                        <td class="px-3 py-2 text-sm text-gray-800">
                                            <span x-text="item.namakontraktor || '-'"></span>
                                            <span class="text-gray-500 text-xs block" x-text="item.namasubkontraktor"></span>
                                        </td>
                                        <td class="px-3 py-2 text-sm text-gray-600" x-text="item.namasupir || '-'"></td>
                                        <td class="px-3 py-2 text-sm text-gray-600" x-text="item.nomorpolisi || '-'"></td>
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-0.5 text-xs rounded-full"
                                                  :class="{
                                                      'bg-green-100 text-green-700': item.jenis === 'Manual',
                                                      'bg-blue-100 text-blue-700': item.jenis === 'GL Kebun',
                                                      'bg-purple-100 text-purple-700': item.jenis === 'GL Kontraktor'
                                                  }"
                                                  x-text="item.jenis"></span>
                                            <span x-show="item.tebusulit" class="ml-1 px-1 py-0.5 text-xs bg-red-100 text-red-600 rounded">Sulit</span>
                                            <span x-show="item.langsir" class="ml-1 px-1 py-0.5 text-xs bg-yellow-100 text-yellow-600 rounded">Langsir</span>
                                        </td>
                                        <td class="px-3 py-2 text-sm text-right text-gray-800" x-text="item.berat_ton"></td>
                                        <td class="px-3 py-2 text-sm text-right font-semibold text-green-600" x-text="formatRupiah(item.total_biaya)"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-gray-100">
                                <tr>
                                    <td colspan="7" class="px-3 py-2 text-sm font-semibold text-gray-700">Total Biaya Kontraktor</td>
                                    <td class="px-3 py-2 text-sm text-right font-bold text-green-600" x-text="formatRupiah(summary.biaya_kontraktor)"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div x-show="kontraktorDetails.length === 0" class="text-center py-8 text-gray-500">
                        Tidak ada data panen/kontraktor untuk plot ini
                    </div>
                </div>

            </div>

        </div>

    </div>

    <script>
    function detailBiaya() {
        return {
            loading: true,
            activeTab: 'tk',
            batch: @json($batch),
            lkhDetails: [],
            materialDetails: [],
            kontraktorDetails: [],
            summary: {
                biaya_tk: 0,
                biaya_material: 0,
                biaya_kontraktor: 0,
                total: 0
            },

            async loadData() {
                this.loading = true;
                try {
                    const response = await fetch(`{{ route('report.biaya-per-plot.detail', $batch->batchno) }}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        this.lkhDetails = result.data.lkh_details;
                        this.materialDetails = result.data.material_details;
                        this.kontraktorDetails = result.data.kontraktor_details;
                        this.summary = result.data.summary;
                    }
                } catch (error) {
                    console.error('Load error:', error);
                } finally {
                    this.loading = false;
                }
            },

            formatRupiah(value) {
                if (!value || value === 0) return 'Rp 0';
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(value));
            },

            formatDate(dateStr) {
                if (!dateStr) return '-';
                const date = new Date(dateStr);
                return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            }
        }
    }
    </script>
</x-layout>