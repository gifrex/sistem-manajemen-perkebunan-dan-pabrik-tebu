{{-- resources/views/report/track-pias/index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="max-w-full mx-auto">
        {{-- FILTER --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border border-gray-200">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Report Track Pias (Activity 5.2.1)</h2>
                    <p class="text-sm text-gray-600 mt-1">Tracking aplikasi Pias & Parasitoid - 2x per bulan (RON1 & RON2)</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="loadData()" class="bg-gray-900 hover:bg-black text-white px-6 py-2.5 rounded-lg font-semibold transition-colors shadow-md">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Tampilkan Data
                    </button>
                    <button onclick="handlePrint()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2.5 rounded-lg font-semibold transition-colors shadow-md no-print">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun</label>
                    <select id="filterYear" class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900">
                        @foreach($years as $year)
                        <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Pilih Blok
                        <button onclick="toggleSelectAll()" class="ml-2 text-xs text-blue-600 hover:text-blue-800 font-normal underline">
                            Select All / Deselect All
                        </button>
                    </label>
                    <div id="blokSelection" class="border-2 border-gray-300 rounded-lg p-3 max-h-32 overflow-y-auto bg-white">
                        @foreach($bloks as $blok)
                        <label class="flex items-center mb-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
                            <input type="checkbox" name="bloks[]" value="{{ $blok }}" class="blok-checkbox w-4 h-4 text-gray-900 border-gray-300 rounded focus:ring-gray-900">
                            <span class="ml-2 text-sm text-gray-700 font-medium">{{ $blok }}</span>
                        </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        <span id="selectedCount">0</span> blok dipilih
                    </p>
                </div>
            </div>
        </div>

        {{-- LOADING --}}
        <div id="loadingState" class="hidden bg-white rounded-lg shadow-lg p-12 text-center border border-gray-200">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mb-4"></div>
            <p class="text-gray-600 font-medium">Memuat data...</p>
        </div>

        {{-- SUMMARY --}}
        <div id="summarySection" class="hidden mb-6">
            <div id="summaryCards" class="grid gap-4">
                {{-- Dirender oleh JS secara dinamis --}}
            </div>
        </div>

        {{-- DATA --}}
        <div id="dataSection" class="hidden space-y-6"></div>

        {{-- EMPTY --}}
        <div id="emptyState" class="bg-white rounded-lg shadow-lg p-12 text-center border border-gray-200">
            <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Pilih Blok untuk Melihat Data</h3>
            <p class="text-gray-500">Centang minimal 1 blok, lalu klik "Tampilkan Data"</p>
        </div>
    </div>

    <style>
        @media print {
            .no-print { display: none !important; }
            @page { size: landscape; margin: 10mm; }
            body { font-size: 9pt; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
        }
        .month-cell { min-width: 80px; }
        .ron-cell { min-width: 36px; max-width: 36px; font-size: 11px; }
    </style>

    <script>
        let currentData = [];
        let currentMaxRon = 2;
        let allBloksSelected = false;

        document.querySelectorAll('.blok-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });

        function updateSelectedCount() {
            document.getElementById('selectedCount').textContent =
                document.querySelectorAll('.blok-checkbox:checked').length;
        }

        function toggleSelectAll() {
            allBloksSelected = !allBloksSelected;
            document.querySelectorAll('.blok-checkbox').forEach(cb => cb.checked = allBloksSelected);
            updateSelectedCount();
        }

        function loadData() {
            const selectedBloks = Array.from(document.querySelectorAll('.blok-checkbox:checked')).map(cb => cb.value);
            if (selectedBloks.length === 0) { alert('Pilih minimal 1 blok terlebih dahulu'); return; }

            const year = document.getElementById('filterYear').value;

            document.getElementById('loadingState').classList.remove('hidden');
            document.getElementById('summarySection').classList.add('hidden');
            document.getElementById('dataSection').classList.add('hidden');
            document.getElementById('emptyState').classList.add('hidden');

            fetch(`{{ route('report.track-pias.data') }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ year, bloks: selectedBloks })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    currentData   = data.data;
                    currentMaxRon = data.max_ron || 2;
                    renderSummary(data.summary);
                    renderTable(data.data, data.year, currentMaxRon);
                    document.getElementById('loadingState').classList.add('hidden');
                    document.getElementById('summarySection').classList.remove('hidden');
                    document.getElementById('dataSection').classList.remove('hidden');
                } else {
                    showError(data.message);
                }
            })
            .catch(err => { console.error(err); showError('Gagal memuat data'); });
        }

        // ── SUMMARY (dinamis berdasarkan max_ron) ──
        function renderSummary(summary) {
            const maxRon   = summary.max_ron || 2;
            const totals   = summary.ron_totals || [];
            const rates    = summary.ron_rates || [];
            const colors   = ['blue', 'green', 'orange', 'purple', 'pink'];

            let cards = `
                <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-gray-800">
                    <p class="text-sm text-gray-600 mb-1 font-semibold">Total Plot</p>
                    <p class="text-3xl font-bold text-gray-900">${summary.total_plots}</p>
                </div>`;

            for (let i = 0; i < maxRon; i++) {
                const c = colors[i % colors.length];
                cards += `
                    <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-${c}-600">
                        <p class="text-sm text-gray-600 mb-1 font-semibold">RON${i + 1} Completed</p>
                        <p class="text-3xl font-bold text-${c}-600">${totals[i] || 0}</p>
                        <p class="text-xs text-gray-500 mt-2">${rates[i] || 0}% completion</p>
                    </div>`;
            }

            // Total activities
            const totalAct = totals.reduce((s, v) => s + v, 0);
            cards += `
                <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-gray-400">
                    <p class="text-sm text-gray-600 mb-1 font-semibold">Total Activities</p>
                    <p class="text-3xl font-bold text-gray-700">${totalAct}</p>
                </div>`;

            const container = document.getElementById('summaryCards');
            // grid cols = 2 + maxRon + 1 (total plot + rons + total act), cap at 6
            const cols = Math.min(2 + maxRon, 6);
            container.className = `grid grid-cols-2 md:grid-cols-${cols} gap-4`;
            container.innerHTML = cards;
        }

        // ── TABLE ──
        function renderTable(data, year, maxRon) {
            const section = document.getElementById('dataSection');
            section.innerHTML = '';

            if (data.length === 0) {
                section.innerHTML = '<div class="bg-white rounded-lg shadow-lg p-8 text-center"><p class="text-gray-500">Tidak ada data</p></div>';
                return;
            }

            const grouped = {};
            data.forEach(item => {
                if (!grouped[item.blok]) grouped[item.blok] = [];
                grouped[item.blok].push(item);
            });

            Object.keys(grouped).sort().forEach(blok => {
                const blokData = grouped[blok];
                section.innerHTML += `
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
                        <div class="bg-gray-800 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">Blok: ${blok}</h3>
                            <p class="text-xs text-gray-300 mt-1">${blokData.length} Plot • Tahun ${year} • ${maxRon > 2 ? maxRon + ' RON terdeteksi' : '2 RON'}</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-300 text-xs">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th rowspan="2" class="px-3 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide border-r-2 border-gray-300 sticky left-0 bg-gray-50 z-10">Plot</th>
                                        <th rowspan="2" class="px-3 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide border-r-2 border-gray-300">Batch</th>
                                        <th rowspan="2" class="px-3 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide border-r-2 border-gray-300">Status</th>
                                        <th rowspan="2" class="px-3 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide border-r-2 border-gray-300">Varietas</th>
                                        ${renderMonthHeaders(maxRon)}
                                    </tr>
                                    <tr>
                                        ${renderRONHeaders(maxRon)}
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    ${renderBlokRows(blokData, maxRon)}
                                </tbody>
                            </table>
                        </div>
                    </div>`;
            });
        }

        function renderMonthHeaders(maxRon) {
            const months = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];
            return months.map(m =>
                `<th colspan="${maxRon}" class="px-2 py-2 text-center font-semibold text-gray-700 uppercase tracking-wide border-r border-gray-300 bg-blue-50">${m}</th>`
            ).join('');
        }

        function renderRONHeaders(maxRon) {
            const bgColors = ['bg-green-50', 'bg-orange-50', 'bg-purple-50', 'bg-pink-50', 'bg-cyan-50'];
            let html = '';
            for (let m = 0; m < 12; m++) {
                for (let r = 0; r < maxRon; r++) {
                    const bg = bgColors[r % bgColors.length];
                    const isLast = r === maxRon - 1;
                    html += `<th class="px-1 py-2 text-center font-semibold text-gray-700 text-xs ${isLast ? 'border-r border-gray-300' : 'border-r border-gray-200'} ron-cell ${bg}">R${r + 1}</th>`;
                }
            }
            return html;
        }

        function renderBlokRows(blokData, maxRon) {
            const statusColors = {
                'PC':  'bg-yellow-100 text-yellow-800',
                'RC1': 'bg-green-100 text-green-800',
                'RC2': 'bg-blue-100 text-blue-800',
                'RC3': 'bg-purple-100 text-purple-800',
            };
            const cellFilled = [
                'bg-green-100 text-green-800 font-semibold',
                'bg-orange-100 text-orange-800 font-semibold',
                'bg-purple-100 text-purple-800 font-semibold',
                'bg-pink-100 text-pink-800 font-semibold',
                'bg-cyan-100 text-cyan-800 font-semibold',
            ];

            return blokData.map(item => {
                const monthCells = item.months.map(month => {
                    let cells = '';
                    for (let r = 0; r < maxRon; r++) {
                        const val      = month.rons[r] || '';
                        const filled   = val !== '';
                        const cls      = filled ? cellFilled[r % cellFilled.length] : 'bg-gray-50';
                        const isLast   = r === maxRon - 1;
                        const borderCls = isLast ? 'border-r border-gray-300' : 'border-r border-gray-200';
                        cells += `<td class="px-1 py-2 text-center ${borderCls} ron-cell ${cls}">${val}</td>`;
                    }
                    return cells;
                }).join('');

                return `
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-3 text-gray-900 font-semibold border-r-2 border-gray-300 sticky left-0 bg-white">${item.plot}</td>
                        <td class="px-3 py-3 text-gray-900 font-mono text-xs border-r-2 border-gray-300">${item.batchno}</td>
                        <td class="px-3 py-3 text-center border-r-2 border-gray-300">
                            <span class="px-2 py-1 rounded text-xs font-medium ${statusColors[item.lifecycle] || 'bg-gray-100 text-gray-800'}">${item.lifecycle}</span>
                        </td>
                        <td class="px-3 py-3 text-center text-gray-700 border-r-2 border-gray-300">${item.varietas || '-'}</td>
                        ${monthCells}
                    </tr>`;
            }).join('');
        }

        function showError(msg) {
            document.getElementById('loadingState').classList.add('hidden');
            document.getElementById('emptyState').classList.remove('hidden');
            alert(msg);
        }

        function handlePrint() { window.print(); }
    </script>
</x-layout>