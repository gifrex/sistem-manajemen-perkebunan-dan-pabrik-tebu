{{-- resources/views/report/saldo-panen/index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="max-w-full mx-auto">

        {{-- ── FILTER SECTION ── --}}
        <div class="bg-white rounded-lg shadow-lg p-5 mb-5 border border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Filter Saldo Panen</h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                {{-- Date Start --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Mulai Panen</label>
                    <input type="date" id="startDate"
                        class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-800 focus:border-gray-800 text-sm">
                </div>

                {{-- Date End --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Akhir</label>
                    <input type="date" id="endDate"
                        class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-800 focus:border-gray-800 text-sm">
                </div>

                {{-- Mandor --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Mandor</label>
                    <select id="mandorFilter"
                        class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-800 focus:border-gray-800 text-sm">
                        <option value="">-- Semua Mandor --</option>
                        @foreach($mandors as $m)
                            <option value="{{ $m->userid }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                    <select id="statusFilter"
                        class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-800 focus:border-gray-800 text-sm">
                        <option value="ongoing" selected>Ongoing</option>
                        <option value="complete">Selesai</option>
                        <option value="all">Semua</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button onclick="loadData()"
                    class="bg-gray-900 hover:bg-black text-white px-6 py-2.5 rounded-lg font-semibold transition-colors text-sm shadow">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Tampilkan
                </button>
            </div>
        </div>

        {{-- ── DEV NOTICE ── --}}
        <div class="flex items-start gap-3 bg-amber-50 border border-amber-300 text-amber-800 rounded-lg px-4 py-3 mb-5 text-sm">
            <svg class="w-5 h-5 mt-0.5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <div>
                <p class="font-semibold">Report ini masih dalam tahap pengembangan.</p>
                <p class="mt-0.5 text-amber-700">Jika terdapat perbedaan data dengan LKH Report, mohon segera hubungi tim IT untuk klarifikasi lebih lanjut.</p>
            </div>
        </div>

        {{-- ── LOADING ── --}}
        <div id="loadingState" class="hidden bg-white rounded-lg shadow p-10 text-center border border-gray-200 mb-5">
            <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-gray-900 mb-3"></div>
            <p class="text-gray-600 font-medium text-sm">Memuat data...</p>
        </div>

        {{-- ── HEADER SUMMARY ── --}}
        <div id="summarySection" class="hidden grid grid-cols-2 md:grid-cols-5 gap-4 mb-5">
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-gray-800">
                <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Total Plot</p>
                <p id="sumTotalPlots" class="text-3xl font-bold text-gray-900">0</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
                <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Ongoing</p>
                <p id="sumOngoing" class="text-3xl font-bold text-yellow-600">0</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-600">
                <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Selesai</p>
                <p id="sumComplete" class="text-3xl font-bold text-green-600">0</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-600">
                <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Total HC (Ha)</p>
                <p id="sumHC" class="text-3xl font-bold text-blue-600">0</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-orange-500">
                <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Total Area (Ha)</p>
                <p id="sumArea" class="text-3xl font-bold text-orange-600">0</p>
            </div>
        </div>

        {{-- ── MAIN TABLE ── --}}
        <div id="contentSection" class="hidden space-y-6"></div>

        {{-- ── EMPTY STATE ── --}}
        <div id="emptyState" class="bg-white rounded-lg shadow p-12 text-center border border-gray-200">
            <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <p class="text-gray-500 font-medium">Pilih tanggal dan klik Tampilkan</p>
        </div>

    </div>

    <script>
        // Set default dates (current month)
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
        const lastDay  = today.toISOString().split('T')[0];
        document.getElementById('startDate').value = firstDay;
        document.getElementById('endDate').value   = lastDay;

        function show(id)  { document.getElementById(id)?.classList.remove('hidden'); }
        function hide(id)  { document.getElementById(id)?.classList.add('hidden'); }

        function loadData() {
            const startDate = document.getElementById('startDate').value;
            const endDate   = document.getElementById('endDate').value;
            const mandorId  = document.getElementById('mandorFilter').value;
            const status    = document.getElementById('statusFilter').value;

            if (!startDate || !endDate) {
                alert('Tanggal harus diisi');
                return;
            }
            if (startDate > endDate) {
                alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
                return;
            }

            hide('emptyState');
            hide('contentSection');
            hide('summarySection');
            show('loadingState');

            const params = new URLSearchParams({ start_date: startDate, end_date: endDate, status });
            if (mandorId) params.append('mandor_id', mandorId);

            fetch(`{{ route('report.saldo-panen.data') }}?${params}`)
                .then(r => r.json())
                .then(res => {
                    hide('loadingState');
                    if (!res.success) {
                        alert(res.message || 'Gagal memuat data');
                        show('emptyState');
                        return;
                    }
                    renderSummary(res.summary);
                    renderContent(res.data);
                })
                .catch(err => {
                    hide('loadingState');
                    console.error(err);
                    alert('Terjadi kesalahan saat memuat data');
                    show('emptyState');
                });
        }

        function renderSummary(s) {
            document.getElementById('sumTotalPlots').textContent = s.total_plots;
            document.getElementById('sumOngoing').textContent    = s.ongoing_plots;
            document.getElementById('sumComplete').textContent   = s.complete_plots;
            document.getElementById('sumHC').textContent         = fmt(s.total_hc);
            document.getElementById('sumArea').textContent       = fmt(s.total_area);
            show('summarySection');
        }

        function renderContent(data) {
            const container = document.getElementById('contentSection');
            container.innerHTML = '';

            if (!data || data.length === 0) {
                show('emptyState');
                return;
            }

            data.forEach(mandorGroup => {
                const block = document.createElement('div');
                block.className = 'bg-white rounded-lg shadow border border-gray-200 overflow-hidden';

                // ── Mandor header ──
                const hdr = document.createElement('div');
                hdr.className = 'bg-gray-800 text-white px-5 py-3 flex justify-between items-center';
                hdr.innerHTML = `
                    <div>
                        <span class="text-xs uppercase tracking-wide text-gray-400 mr-2">Mandor</span>
                        <span class="font-bold text-base">${mandorGroup.mandorname}</span>
                        <span class="text-gray-400 text-xs ml-2">(${mandorGroup.mandorid})</span>
                    </div>
                    <div class="flex gap-4 text-sm">
                        <span>${mandorGroup.plots.length} Plot</span>
                        <span class="text-green-300">HC: ${fmt(mandorGroup.total_hc)} Ha</span>
                        <span class="text-orange-300">BC: ${fmt(mandorGroup.total_bc)} Ha</span>
                    </div>
                `;
                block.appendChild(hdr);

                // ── Table ──
                const tableWrap = document.createElement('div');
                tableWrap.className = 'overflow-x-auto';
                tableWrap.innerHTML = `
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold text-gray-600 uppercase w-14">No</th>
                                <th class="px-3 py-2 text-left font-semibold text-gray-600 uppercase">Blok</th>
                                <th class="px-3 py-2 text-left font-semibold text-gray-600 uppercase">Plot</th>
                                <th class="px-3 py-2 text-left font-semibold text-gray-600 uppercase">Batch No</th>
                                <th class="px-3 py-2 text-center font-semibold text-gray-600 uppercase">Lifecycle</th>
                                <th class="px-3 py-2 text-center font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-3 py-2 text-left font-semibold text-gray-600 uppercase">Tgl Mulai</th>
                                <th class="px-3 py-2 text-right font-semibold text-gray-600 uppercase bg-gray-100">Luas Batch (Ha)</th>
                                <th class="px-3 py-2 text-right font-semibold text-gray-600 uppercase bg-orange-50">STC (Ha)</th>
                                <th class="px-3 py-2 text-right font-semibold text-gray-600 uppercase bg-green-50">HC (Ha)</th>
                                <th class="px-3 py-2 text-right font-semibold text-gray-600 uppercase bg-gray-50">BC (Ha)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white" id="tbody-${mandorGroup.mandorid}">
                        </tbody>
                        <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-300">
                            <tr>
                                <td colspan="7" class="px-3 py-2 text-right text-gray-800 uppercase text-xs">Total:</td>
                                <td class="px-3 py-2 text-right text-gray-800 bg-gray-100">${fmt(mandorGroup.total_area)}</td>
                                <td class="px-3 py-2 bg-orange-50"></td>
                                <td class="px-3 py-2 text-right text-green-700 bg-green-50">${fmt(mandorGroup.total_hc)}</td>
                                <td class="px-3 py-2 text-right text-gray-700 bg-gray-50">${fmt(mandorGroup.total_bc)}</td>
                            </tr>
                        </tfoot>
                    </table>
                `;
                block.appendChild(tableWrap);
                container.appendChild(block);

                // Fill tbody
                const tbody = document.getElementById(`tbody-${mandorGroup.mandorid}`);
                mandorGroup.plots.forEach((p, i) => {
                    const lifecycleColor = {
                        PC:  'bg-yellow-100 text-yellow-800',
                        RC1: 'bg-green-100 text-green-800',
                        RC2: 'bg-blue-100 text-blue-800',
                        RC3: 'bg-purple-100 text-purple-800',
                    }[p.lifecyclestatus] || 'bg-gray-100 text-gray-800';

                    const statusBadge = p.status === 'complete'
                        ? '<span class="px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Selesai</span>'
                        : '<span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">Ongoing</span>';

                    const stcDisplay = p.stc > 0 ? fmt(p.stc) : '<span class="text-gray-400">0.00</span>';
                    const bcDisplay  = p.bc  > 0
                        ? `<span class="font-semibold text-orange-700">${fmt(p.bc)}</span>`
                        : '<span class="text-gray-400">0.00</span>';

                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-gray-50 transition-colors';
                    tr.innerHTML = `
                        <td class="px-3 py-2 text-gray-500">${i + 1}</td>
                        <td class="px-3 py-2 font-medium text-gray-900">${p.blok ?? '-'}</td>
                        <td class="px-3 py-2 font-bold text-gray-900">${p.plot}</td>
                        <td class="px-3 py-2 font-mono text-gray-700">${p.batchno}</td>
                        <td class="px-3 py-2 text-center">
                            <span class="px-2 py-0.5 rounded text-xs font-semibold ${lifecycleColor}">${p.lifecyclestatus}</span>
                        </td>
                        <td class="px-3 py-2 text-center">${statusBadge}</td>
                        <td class="px-3 py-2 text-gray-600">${formatDate(p.tanggalpanen)}</td>
                        <td class="px-3 py-2 text-right text-gray-800 bg-gray-50">${fmt(p.batcharea)}</td>
                        <td class="px-3 py-2 text-right text-orange-700 bg-orange-50">${stcDisplay}</td>
                        <td class="px-3 py-2 text-right text-green-700 bg-green-50 font-semibold">${fmt(p.hc)}</td>
                        <td class="px-3 py-2 text-right bg-gray-50">${bcDisplay}</td>
                    `;
                    tbody.appendChild(tr);
                });
            });

            show('contentSection');
        }

        function fmt(val) {
            return parseFloat(val || 0).toFixed(2);
        }

        function formatDate(d) {
            if (!d) return '-';
            const dt = new Date(d);
            const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            return `${dt.getDate()} ${months[dt.getMonth()]} ${dt.getFullYear()}`;
        }
    </script>
</x-layout>