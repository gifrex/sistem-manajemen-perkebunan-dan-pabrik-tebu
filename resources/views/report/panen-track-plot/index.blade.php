{{-- resources\views\report\panen-track-plot\index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="max-w-7xl mx-auto">
        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border border-gray-200">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Filter Tracking</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Plot</label>
                    <button onclick="openPlotModal()" id="plotButton" class="w-full px-4 py-3 bg-white border-2 border-gray-300 text-gray-800 rounded-lg hover:border-gray-900 hover:bg-gray-50 transition-colors text-left flex justify-between items-center">
                        <span id="plotButtonText" class="font-medium">-- Pilih Plot --</span>
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Batch</label>
                    <button onclick="openBatchModal()" id="batchButton" disabled class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-300 text-gray-400 rounded-lg text-left flex justify-between items-center opacity-50 cursor-not-allowed">
                        <span id="batchButtonText" class="font-medium">-- Pilih Plot Terlebih Dahulu --</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="mt-4">
                <button onclick="loadTrackingData()" id="loadButton" disabled class="w-full bg-gray-900 hover:bg-black text-white px-6 py-3 rounded-lg font-semibold transition-colors opacity-50 cursor-not-allowed shadow-md">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Tampilkan Tracking
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="hidden bg-white rounded-lg shadow-lg p-12 text-center border border-gray-200">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mb-4"></div>
            <p class="text-gray-600 font-medium">Memuat data tracking...</p>
        </div>

        <!-- Content Container -->
        <div id="contentContainer" class="hidden">
            <!-- Batch Info Section -->
            <div id="batchInfoSection" class="bg-white rounded-lg shadow-lg p-6 mb-6 border border-gray-200">
                <!-- Will be populated by JavaScript -->
            </div>

            <!-- Summary Section -->
            <div id="summarySection" class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <!-- Will be populated by JavaScript -->
            </div>

            <!-- Timeline Section -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
                <div class="bg-gray-900 px-6 py-4">
                    <h3 class="text-lg font-bold text-white">Timeline Panen Harian</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-300 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Hari Ke</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tanggal</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">HC (ha)</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Sisa (ha)</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">FB Rit</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">FB Ton</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700">Jumlah SJ</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Netto (ton)</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">YPH</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Surat Jalan</th>
                            </tr>
                        </thead>
                        <tbody id="timelineTableBody" class="divide-y divide-gray-200 bg-white">
                            <!-- Will be populated by JavaScript -->
                        </tbody>
                        <tfoot id="timelineTableFooter" class="bg-gray-100 font-bold border-t-2 border-gray-300">
                            <!-- Will be populated by JavaScript -->
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="bg-white rounded-lg shadow-lg p-12 text-center border border-gray-200">
            <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Pilih Plot & Batch untuk Tracking</h3>
            <p class="text-gray-500">Pilih plot dan batch dari filter di atas untuk melihat detail tracking panen</p>
        </div>
    </div>

    <!-- Plot Selection Modal -->
    <div id="plotModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full mx-4 max-h-[85vh] overflow-hidden">
            <div class="bg-gray-900 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white">Pilih Plot</h3>
                <button onclick="closePlotModal()" class="text-white hover:text-gray-300 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <!-- Search and Filter -->
                <div class="mb-4 space-y-3">
                    <input type="text" id="plotSearch" placeholder="Cari plot..." class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900">
                    
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" id="filterActiveBatch" onchange="filterPlotList()" class="w-4 h-4 text-gray-900 border-gray-300 rounded focus:ring-gray-900">
                        <span class="font-medium">Hanya tampilkan plot yang sedang dipanen</span>
                    </label>
                </div>
                
                <!-- Plot List -->
                <div class="border border-gray-200 rounded-lg overflow-hidden max-h-96 overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Plot</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Active Batch</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody id="plotListBody" class="divide-y divide-gray-200 bg-white">
                            @foreach($plots as $plot)
                            <tr class="plot-item hover:bg-gray-50 cursor-pointer transition-colors" 
                                onclick="selectPlot('{{ $plot->plot }}', '{{ $plot->activebatchno ?? '' }}')"
                                data-plot="{{ $plot->plot }}"
                                data-activebatch="{{ $plot->activebatchno ?? '' }}"
                                data-ispanen="{{ $plot->tanggalpanen ? '1' : '0' }}">
                                <td class="px-4 py-3">
                                    <span class="font-bold text-gray-900 text-base">{{ $plot->plot }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($plot->activebatchno)
                                        <span class="font-mono text-sm text-gray-700">{{ $plot->activebatchno }}</span>
                                    @else
                                        <span class="text-gray-400 italic text-sm">No active batch</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($plot->activebatchno && $plot->tanggalpanen)
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Panen</span>
                                    @elseif($plot->activebatchno)
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">Active</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold">Idle</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Selection Modal -->
    <div id="batchModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg shadow-2xl max-w-3xl w-full mx-4 max-h-[80vh] overflow-hidden">
            <div class="bg-gray-900 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white">Pilih Batch - <span id="batchModalPlotName"></span></h3>
                <button onclick="closeBatchModal()" class="text-white hover:text-gray-300 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <div id="batchLoading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                    <p class="mt-2 text-sm text-gray-600 font-medium">Memuat batch...</p>
                </div>

                <div id="batchList" class="hidden space-y-3 max-h-96 overflow-y-auto">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedPlot = null;
        let selectedBatchno = null;
        let activeBatchno = null;
        let batchArea = 0; // Store batch area globally

        // Plot Search
        document.getElementById('plotSearch')?.addEventListener('input', function(e) {
            filterPlotList();
        });

        function filterPlotList() {
            const search = document.getElementById('plotSearch').value.toLowerCase();
            const filterActive = document.getElementById('filterActiveBatch').checked;
            
            let visibleCount = 0;
            document.querySelectorAll('.plot-item').forEach(item => {
                const text = item.textContent.toLowerCase();
                const isPanen = item.dataset.ispanen === '1';
                
                const matchSearch = text.includes(search);
                const matchFilter = !filterActive || isPanen;
                
                if (matchSearch && matchFilter) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            console.log('Filtered:', visibleCount, 'plots visible');
        }

        function openPlotModal() {
            document.getElementById('plotModal').classList.remove('hidden');
            document.getElementById('plotModal').classList.add('flex');
            document.getElementById('plotSearch').value = '';
            document.getElementById('filterActiveBatch').checked = false;
            filterPlotList();
        }

        function closePlotModal() {
            document.getElementById('plotModal').classList.add('hidden');
            document.getElementById('plotModal').classList.remove('flex');
        }

        function selectPlot(plot, activeBatch) {
            selectedPlot = plot;
            activeBatchno = activeBatch || null;
            
            console.log('Selected plot:', plot, 'Active batch:', activeBatch);
            
            document.getElementById('plotButtonText').textContent = plot;
            
            const batchButton = document.getElementById('batchButton');
            batchButton.disabled = false;
            batchButton.classList.remove('opacity-50', 'cursor-not-allowed', 'text-gray-400', 'bg-gray-100');
            batchButton.classList.add('hover:border-gray-900', 'hover:bg-gray-50', 'transition-colors', 'text-gray-800', 'bg-white');
            
            // Reset load button
            const loadButton = document.getElementById('loadButton');
            loadButton.disabled = true;
            loadButton.classList.add('opacity-50', 'cursor-not-allowed');
            document.getElementById('batchButtonText').textContent = '-- Pilih Batch --';
            
            closePlotModal();
            
            // Auto-load batches and select active batch if exists
            if (activeBatch && activeBatch !== '') {
                loadBatchesAndSelectActive();
            }
        }

        function loadBatchesAndSelectActive() {
            fetch(`{{ route('report.panen-track-plot.batches') }}?plot=${selectedPlot}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.batches.length > 0) {
                        // Auto-select active batch
                        const activeBatch = data.batches.find(b => b.is_active === 1);
                        if (activeBatch) {
                            selectedBatchno = activeBatch.batchno;
                            batchArea = parseFloat(activeBatch.batcharea);
                            document.getElementById('batchButtonText').textContent = `${activeBatch.batchno} (Active)`;
                            
                            const loadButton = document.getElementById('loadButton');
                            loadButton.disabled = false;
                            loadButton.classList.remove('opacity-50', 'cursor-not-allowed');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function openBatchModal() {
            if (!selectedPlot) return;
            
            document.getElementById('batchModal').classList.remove('hidden');
            document.getElementById('batchModal').classList.add('flex');
            document.getElementById('batchModalPlotName').textContent = selectedPlot;
            document.getElementById('batchLoading').classList.remove('hidden');
            document.getElementById('batchList').classList.add('hidden');

            fetch(`{{ route('report.panen-track-plot.batches') }}?plot=${selectedPlot}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderBatches(data.batches);
                        document.getElementById('batchLoading').classList.add('hidden');
                        document.getElementById('batchList').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal memuat batch');
                });
        }

        function closeBatchModal() {
            document.getElementById('batchModal').classList.add('hidden');
            document.getElementById('batchModal').classList.remove('flex');
        }

        function renderBatches(batches) {
            const list = document.getElementById('batchList');
            list.innerHTML = '';

            batches.forEach(batch => {
                const div = document.createElement('div');
                div.className = 'p-4 bg-gray-50 hover:bg-gray-100 rounded-lg cursor-pointer transition-colors border-2 border-gray-200 hover:border-gray-900';
                div.onclick = () => selectBatch(batch.batchno, batch);
                
                const statusColors = {
                    'PC': 'bg-yellow-100 text-yellow-800 border-yellow-300',
                    'RC1': 'bg-green-100 text-green-800 border-green-300',
                    'RC2': 'bg-blue-100 text-blue-800 border-blue-300',
                    'RC3': 'bg-purple-100 text-purple-800 border-purple-300'
                };
                
                div.innerHTML = `
                    <div class="flex justify-between items-start gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="font-bold text-gray-900 font-mono text-lg">${batch.batchno}</div>
                                ${batch.is_active === 1 ? '<span class="px-2 py-1 bg-gray-900 text-white rounded-full text-xs font-bold">ACTIVE</span>' : ''}
                            </div>
                            <div class="text-sm text-gray-600">
                                Mulai: ${formatDate(batch.tanggalpanen)} | Luas: ${parseFloat(batch.batcharea).toFixed(2)} ha
                            </div>
                            ${batch.kodevarietas ? `<div class="text-xs text-gray-500 mt-1">Varietas: ${batch.kodevarietas}</div>` : ''}
                        </div>
                        <span class="px-3 py-1 rounded-lg text-sm font-bold border-2 ${statusColors[batch.lifecyclestatus] || 'bg-gray-100 text-gray-800 border-gray-300'}">
                            ${batch.lifecyclestatus}
                        </span>
                    </div>
                `;
                
                list.appendChild(div);
            });
        }

        function selectBatch(batchno, batchData) {
            selectedBatchno = batchno;
            batchArea = parseFloat(batchData.batcharea);
            const isActive = batchData.is_active === 1;
            document.getElementById('batchButtonText').textContent = isActive ? `${batchno} (Active)` : batchno;
            
            const loadButton = document.getElementById('loadButton');
            loadButton.disabled = false;
            loadButton.classList.remove('opacity-50', 'cursor-not-allowed');
            
            closeBatchModal();
        }

        function loadTrackingData() {
            if (!selectedPlot || !selectedBatchno) {
                alert('Silakan pilih plot dan batch terlebih dahulu');
                return;
            }

            document.getElementById('loadingState').classList.remove('hidden');
            document.getElementById('contentContainer').classList.add('hidden');
            document.getElementById('emptyState').classList.add('hidden');

            fetch(`{{ route('report.panen-track-plot.data') }}?plot=${selectedPlot}&batchno=${selectedBatchno}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Store batch area if not already set
                        if (!batchArea && data.data.batch_info.batcharea) {
                            batchArea = parseFloat(data.data.batch_info.batcharea);
                        }
                        
                        renderBatchInfo(data.data.batch_info);
                        renderSummary(data.data.summary);
                        renderTimeline(data.data.timeline, data.data.summary);
                        
                        document.getElementById('loadingState').classList.add('hidden');
                        document.getElementById('contentContainer').classList.remove('hidden');
                    } else {
                        alert(data.message || 'Gagal memuat data');
                        document.getElementById('loadingState').classList.add('hidden');
                        document.getElementById('emptyState').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memuat data');
                    document.getElementById('loadingState').classList.add('hidden');
                    document.getElementById('emptyState').classList.remove('hidden');
                });
        }

        function renderBatchInfo(batchInfo) {
            const statusColors = {
                'PC': 'text-green-600',
                'RC1': 'text-blue-600',
                'RC2': 'text-yellow-600',
                'RC3': 'text-purple-600'
            };
            
            const section = document.getElementById('batchInfoSection');
            section.innerHTML = `
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-2xl font-bold text-gray-900">Informasi Batch</h2>
                    <span class="text-2xl font-bold ${statusColors[batchInfo.kodestatus] || 'text-gray-600'}">
                        ${batchInfo.kodestatus}
                    </span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <p class="text-gray-600 text-sm mb-1">Plot</p>
                        <p class="text-2xl font-bold text-gray-900">${batchInfo.plot}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <p class="text-gray-600 text-sm mb-1">Batch No</p>
                        <p class="text-lg font-bold font-mono text-gray-900">${batchInfo.batchno}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <p class="text-gray-600 text-sm mb-1">Tanggal Mulai</p>
                        <p class="text-base font-bold text-gray-900">${formatDate(batchInfo.tanggalpanen)}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <p class="text-gray-600 text-sm mb-1">Tanggal Selesai</p>
                        <p class="text-base font-bold ${batchInfo.tanggalselesai ? 'text-green-600' : 'text-orange-600'}">${batchInfo.tanggalselesai ? formatDate(batchInfo.tanggalselesai) : 'Belum Selesai'}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <p class="text-gray-600 text-sm mb-1">Luas Batch</p>
                        <p class="text-2xl font-bold text-gray-900">${parseFloat(batchInfo.batcharea).toFixed(2)} ha</p>
                    </div>
                </div>
            `;
        }

        function renderSummary(summary) {
            const currentYPH = summary.total_hc > 0 ? (summary.total_netto_ton / summary.total_hc) : 0;
            
            const section = document.getElementById('summarySection');
            section.innerHTML = `
                <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-gray-800">
                    <p class="text-sm text-gray-600 mb-1 font-semibold">Total Hari</p>
                    <p class="text-3xl font-bold text-gray-900">${Math.floor(summary.total_days)}</p>
                    <p class="text-xs text-gray-500 mt-2">
                        ${summary.total_harvest_days} hari panen, ${Math.floor(summary.total_skipped_days)} hari skip${summary.total_ongoing_days > 0 ? ', ' + summary.total_ongoing_days + ' hari ongoing' : ''}
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-green-600">
                    <p class="text-sm text-gray-600 mb-1 font-semibold">Total Area yang Sudah Dipanen</p>
                    <p class="text-3xl font-bold text-green-600">${parseFloat(summary.total_hc).toFixed(2)} ha</p>
                    <p class="text-xs text-gray-500 mt-2">
                        dari ${batchArea.toFixed(2)} ha | Rata-rata: ${parseFloat(summary.avg_hc_per_day).toFixed(2)} ha/hari
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-gray-800">
                    <p class="text-sm text-gray-600 mb-1 font-semibold">Progress</p>
                    <p class="text-3xl font-bold text-gray-900">${parseFloat(summary.percentage_complete).toFixed(1)}%</p>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                        <div class="bg-gray-900 h-2.5 rounded-full" style="width: ${Math.min(100, summary.percentage_complete)}%"></div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-green-600">
                    <p class="text-sm text-gray-600 mb-1 font-semibold">Total Hasil Panen</p>
                    <p class="text-3xl font-bold text-green-600">${parseFloat(summary.total_netto_ton || 0).toFixed(2)} ton</p>
                    <p class="text-xs text-gray-500 mt-2">
                        Total surat jalan: ${summary.total_sj}
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-gray-800">
                    <p class="text-sm text-gray-600 mb-1 font-semibold">Current YPH</p>
                    <p class="text-3xl font-bold text-gray-900">${currentYPH.toFixed(2)} ton/ha</p>
                    <p class="text-xs text-gray-500 mt-2">
                        Yield Per Hectare
                    </p>
                </div>
            `;
        }

        function renderTimeline(timeline, summary) {
            const tbody = document.getElementById('timelineTableBody');
            tbody.innerHTML = '';

            timeline.forEach(day => {
                const row = document.createElement('tr');
                row.className = day.has_harvest ? 'hover:bg-green-50 bg-white' : 'bg-gray-50 hover:bg-gray-100';
                
                let statusBadge = '';
                if (day.has_harvest) {
                    statusBadge = '<span class="px-3 py-1 bg-green-600 text-white rounded-full text-xs font-bold">PANEN</span>';
                } else {
                    const isToday = day.tanggal === new Date().toISOString().split('T')[0];
                    if (isToday) {
                        statusBadge = '<span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold">ONGOING</span>';
                    } else {
                        statusBadge = '<span class="px-3 py-1 bg-gray-400 text-white rounded-full text-xs font-bold">SKIP</span>';
                    }
                }

                // Calculate YPH for this day
                const dayYPH = (day.has_harvest && day.hc > 0 && day.netto_ton) 
                    ? (parseFloat(day.netto_ton) / parseFloat(day.hc)).toFixed(2)
                    : '-';

                const sjList = day.list_sj.length > 0 
                    ? `<div class="space-y-1">${day.list_sj.map(sj => `
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 bg-gray-800 rounded-full flex-shrink-0"></span>
                            <a href="{{ route('report.report-surat-jalan-timbangan.index') }}/${sj}" class="text-gray-800 font-mono text-xs font-semibold underline">${sj}</a>
                        </div>
                    `).join('')}</div>`
                    : '<span class="text-gray-400 italic text-xs">-</span>';

                row.innerHTML = `
                    <td class="px-4 py-3 text-center font-bold text-gray-900 text-base">${day.hari_ke}</td>
                    <td class="px-4 py-3">
                        <div class="font-semibold text-gray-900">${formatDate(day.tanggal)}</div>
                        <div class="text-xs text-gray-500">${day.day_name}</div>
                    </td>
                    <td class="px-4 py-3 text-center">${statusBadge}</td>
                    <td class="px-4 py-3 text-right font-bold ${day.has_harvest ? 'text-gray-900' : 'text-gray-400'}">${day.has_harvest ? parseFloat(day.hc).toFixed(2) : '-'}</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-900">${parseFloat(day.remaining_area).toFixed(2)}</td>
                    <td class="px-4 py-3 text-right text-gray-700">${day.field_balance_rit ? parseFloat(day.field_balance_rit).toFixed(0) : '-'}</td>
                    <td class="px-4 py-3 text-right text-gray-700">${day.field_balance_ton ? parseFloat(day.field_balance_ton).toFixed(2) : '-'}</td>
                    <td class="px-4 py-3 text-center text-gray-900 font-semibold">
                        ${day.jumlah_sj > 0 ? day.jumlah_sj : '-'}
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-green-700">
                        ${day.netto_ton ? parseFloat(day.netto_ton).toFixed(2) : '-'}
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-blue-700">
                        ${dayYPH}
                    </td>
                    <td class="px-4 py-3 text-xs">${sjList}</td>
                `;
                
                tbody.appendChild(row);
            });

            // Calculate average YPH for footer
            const avgYPH = summary.total_hc > 0 ? (summary.total_netto_ton / summary.total_hc) : 0;

            const tfoot = document.getElementById('timelineTableFooter');
            tfoot.innerHTML = `
                <tr>
                    <td colspan="3" class="px-4 py-3 text-right text-gray-900 uppercase font-bold">Total:</td>
                    <td class="px-4 py-3 text-right text-gray-900 font-bold">${parseFloat(summary.total_hc).toFixed(2)}</td>
                    <td class="px-4 py-3 text-right text-gray-900 font-bold">${parseFloat(summary.remaining_area).toFixed(2)}</td>
                    <td class="px-4 py-3 text-center text-gray-500">-</td>
                    <td class="px-4 py-3 text-center text-gray-500">-</td>
                    <td class="px-4 py-3 text-center text-gray-900 font-bold">${summary.total_sj}</td>
                    <td class="px-4 py-3 text-right text-green-700 font-bold">${parseFloat(summary.total_netto_ton || 0).toFixed(2)}</td>
                    <td class="px-4 py-3 text-right text-blue-700 font-bold">${avgYPH.toFixed(2)}</td>
                    <td class="px-4 py-3"></td>
                </tr>
            `;
        }

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
        }
    </script>
</x-layout>