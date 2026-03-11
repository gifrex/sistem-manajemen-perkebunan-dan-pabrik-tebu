<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-6 bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-lg border border-gray-200">
        <!-- Header Section -->
        <div class="px-6 pb-4 border-b border-gray-200">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <!-- Title & Info -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Report ZPK (Zat Pemacu Kemasakan)
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Data aplikasi zat pemacu kemasakan dan jadwal panen tebu</p>
                </div>

                <!-- Action Button -->
                <div>
                    @can('report.zpk.export')
                        <button id="btn-export" data-base-url="{{ route('report.report-zpk.exportExcel') }}"
                            class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2"
                            onclick="startExport()">
                            <svg id="icon-export" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd"
                                    d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v9.293l-2-2a1 1 0 0 0-1.414 1.414l.293.293h-6.586a1 1 0 1 0 0 2h6.586l-.293.293A1 1 0 0 0 18 16.707l2-2V20a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <svg id="icon-spin" class="w-5 h-5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span id="label-export">Export Excel</span>
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <form method="POST" action="{{ route('report.report-zpk.index') }}">
            @csrf
            <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <div class="flex items-center gap-4 flex-wrap justify-between">
                    <!-- Date Filter -->
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Range Tanggal:
                        </label>
                        <div class="relative">
                            <button type="button"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-sm font-medium text-gray-700"
                                id="menu-button" onclick="toggleDropdown()">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span id="date-label">
                                    {{ $startDate }} s/d {{ $endDate }}
                                </span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div class="absolute left-0 z-50 mt-2 w-56 rounded-lg bg-white border border-gray-200 shadow-xl hidden"
                                id="menu-dropdown">
                                <div class="p-4 space-y-4">
                                    <div>
                                        <label for="start_date"
                                            class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Mulai</label>
                                        <input type="date" id="start_date" name="start_date"
                                            value="{{ old('start_date', $startDate ?? '') }}"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm transition-all duration-200">
                                    </div>

                                    <div>
                                        <label for="end_date"
                                            class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Akhir</label>
                                        <input type="date" id="end_date" name="end_date"
                                            value="{{ old('end_date', $endDate ?? '') }}"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm transition-all duration-200">
                                    </div>

                                    <button type="button" id="btn-apply-filter"
                                        onclick="
                                                document.getElementById('menu-dropdown').classList.add('hidden');
                                                document.getElementById('date-label').textContent =
                                                    document.getElementById('start_date').value + ' s/d ' +
                                                    document.getElementById('end_date').value;
                                            "
                                        class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-all duration-200">
                                        Terapkan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items per page & Search -->
                    <div class="flex items-center gap-4 flex-wrap">
                        <div id="ajax-data" data-url="{{ route('report.report-zpk.index') }}">
                            <div class="flex items-center gap-2">
                                <label for="perPage"
                                    class="text-sm font-semibold text-gray-700 whitespace-nowrap">Items per
                                    page:</label>
                                <input type="text" name="perPage" id="perPage" value="{{ $perPage }}"
                                    min="1" autocomplete="off"
                                    class="w-16 px-3 py-2 border border-gray-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-all duration-200" />
                            </div>
                        </div>

                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" id="search" autocomplete="off" name="search"
                                value="{{ old('search', $search) }}"
                                class="w-80 pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                placeholder="Cari Plot, Variety, Category..." />
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Table Section -->
        <div class="px-6 py-5">
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full bg-white text-sm" id="tables">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-100 to-gray-50">
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                No.</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Kebun</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Blok</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Plot</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap bg-blue-50">
                                Luas (Ha)</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Bulan Tanam</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap bg-green-50">
                                Umur</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Kategori</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Varietas</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap bg-yellow-50">
                                PKP</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap bg-purple-50">
                                Tanggal ZPK</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap bg-red-50">
                                Perkiraan Panen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($zpk as $item)
                            <tr>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->no }}.</td>
                                <td class="py-3 px-4 text-center text-gray-700 font-medium">{{ $item->companycode }}
                                </td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->blok ?? '-' }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->plot ?? '-' }}</td>
                                <td class="py-3 px-4 text-center text-gray-700 bg-blue-50">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $item->batcharea ?? '-' }} Ha
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->bulantanam ?? '-' }}</td>
                                <td class="py-3 px-4 text-center text-gray-700 bg-green-50">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ round($item->umur) }} Bulan
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->lifecyclestatus ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-center text-gray-700 font-medium">
                                    {{ $item->kodevarietas ?? '-' }}</td>
                                <td class="py-3 px-4 text-center text-gray-700 bg-yellow-50">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $item->pkp ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center text-gray-700 bg-purple-50">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ $item->lkhdate ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center text-gray-700 bg-red-50">
                                    @if ($item->perkiraan_panen_awal)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                            {{ $item->perkiraan_panen_awal }} &ndash;
                                            {{ $item->perkiraan_panen_akhir }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr class="!bg-white hover:!bg-white">
                                <td colspan="12" class="p-0 border-0">
                                    <div
                                        style="
                                            position: sticky;
                                            left: 0;
                                            width: calc(100vw - 330px);
                                            max-width: calc(100vw - 330px);
                                            display: flex;
                                            flex-direction: column;
                                            align-items: center;
                                            justify-content: center;
                                            gap: 12px;
                                            padding: 64px 16px;
                                        ">
                                        <div
                                            style="
                                                width: 64px;
                                                height: 64px;
                                                border-radius: 9999px;
                                                background-color: #f3f4f6;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                            ">
                                            <svg style="width:32px;height:32px;color:#9ca3af" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                            </svg>
                                        </div>
                                        <div style="text-align:center">
                                            <p style="color:#4b5563;font-weight:600;font-size:0.875rem">Tidak ada data
                                                ditemukan</p>
                                            <p style="color:#9ca3af;font-size:0.75rem;margin-top:4px">Coba ubah rentang
                                                tanggal atau kata kunci pencarian</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Section -->
        <div class="px-6 pb-2" id="pagination-links">
            @if ($zpk->hasPages())
                {{ $zpk->appends(['perPage' => $zpk->perPage(), 'start_date' => $startDate ?? '', 'end_date' => $endDate ?? ''])->links() }}
            @else
                <div class="flex items-center justify-between bg-gray-50 px-4 py-3 rounded-lg">
                    <p class="text-sm text-gray-600">
                        Menampilkan <span class="font-semibold text-gray-800">{{ $zpk->count() }}</span> dari <span
                            class="font-semibold text-gray-800">{{ $zpk->total() }}</span> hasil
                    </p>
                </div>
            @endif
        </div>
    </div>

    <script>
        function startExport() {
            const btn = document.getElementById('btn-export');
            const iconExport = document.getElementById('icon-export');
            const iconSpin = document.getElementById('icon-spin');
            const label = document.getElementById('label-export');

            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const baseUrl = btn.getAttribute('data-base-url');

            const params = new URLSearchParams();
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
            const url = baseUrl + '?' + params.toString();

            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            iconExport.classList.add('hidden');
            iconSpin.classList.remove('hidden');
            label.textContent = 'Mengekspor...';

            fetch(url)
                .then(function(response) {
                    const contentType = response.headers.get('Content-Type') || '';
                    if (contentType.includes('application/json')) {
                        return response.json().then(function(json) {
                            showToast('error', json.error || 'Tidak ada data untuk diekspor.');
                        });
                    }
                    return response.blob().then(function(blob) {
                        const disposition = response.headers.get('Content-Disposition') || '';
                        const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                        const filename = match ? match[1].replace(/['"]/g, '') : 'ZPKReport.xlsx';
                        const a = document.createElement('a');
                        a.href = URL.createObjectURL(blob);
                        a.download = filename;
                        a.click();
                        URL.revokeObjectURL(a.href);
                    });
                })
                .catch(function() {
                    showToast('error', 'Gagal mengekspor data. Silakan coba lagi.');
                })
                .finally(function() {
                    btn.disabled = false;
                    btn.classList.remove('opacity-75', 'cursor-not-allowed');
                    iconExport.classList.remove('hidden');
                    iconSpin.classList.add('hidden');
                    label.textContent = 'Export Excel';
                });
        }

        function showToast(type, msg) {
            const colors = type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white';
            const icon = type === 'success' ?
                '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>' :
                '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
            const t = document.createElement('div');
            t.className =
                `fixed top-6 right-6 z-[9999] flex items-start gap-3 px-5 py-4 rounded-xl shadow-xl ${colors} max-w-sm transition-all duration-300 opacity-0 translate-y-2`;
            t.innerHTML = `${icon}<span class="text-sm font-medium">${msg}</span>`;
            document.body.appendChild(t);
            setTimeout(function() {
                t.style.opacity = '1';
                t.style.transform = 'translateY(0)';
            }, 10);
            setTimeout(function() {
                t.style.opacity = '0';
                t.style.transform = 'translateY(-8px)';
                setTimeout(function() {
                    t.remove();
                }, 300);
            }, 4000);
        }

        function toggleDropdown() {
            const dropdown = document.getElementById('menu-dropdown');
            dropdown.classList.toggle('hidden');
        }

        document.addEventListener("click", function(event) {
            const dropdown = document.getElementById("menu-dropdown");
            const button = document.getElementById("menu-button");

            if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                dropdown.classList.add("hidden");
            }
        });
    </script>

    <style>
        th,
        td {
            white-space: nowrap;
        }

        /* Custom scrollbar */
        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 10px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        /* Sticky first column */
        #tables thead tr th:first-child,
        #tables tbody tr td:first-child {
            position: sticky;
            left: 0;
            z-index: 10;
            background: white;
        }

        #tables thead tr th:first-child {
            background: linear-gradient(to right, #f3f4f6, #e5e7eb);
        }
    </style>

</x-layout>
