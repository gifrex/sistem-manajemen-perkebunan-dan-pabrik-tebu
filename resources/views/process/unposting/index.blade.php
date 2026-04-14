<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-6 bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-lg border border-gray-200">
        <!-- Header Section -->
        <div class="px-6 pb-4 border-b border-gray-200">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <!-- Title & Info -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Unposting Data Pengamatan
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Batalkan posting data pengamatan Agronomi dan HPT dari sistem
                    </p>
                </div>

                <!-- Action Button -->
                @can('process.unposting.submit')
                    <form action="{{ route('process.unposting.submit') }}" method="POST" id="unpost">
                        @csrf
                        <input type="hidden" name="selected_items" id="selected_items">
                        <input type="hidden" name="unposting_type" id="unposting_type" value="{{ $unposting }}">
                        <button type="submit" onclick="Alpine.store('loading').start();"
                            class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd"
                                    d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm7.707-3.707a1 1 0 0 0-1.414 1.414L10.586 12l-2.293 2.293a1 1 0 1 0 1.414 1.414L12 13.414l2.293 2.293a1 1 0 0 0 1.414-1.414L13.414 12l2.293-2.293a1 1 0 0 0-1.414-1.414L12 10.586 9.707 8.293Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Unposting Data</span>
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        <!-- Filter Section -->
        <form method="GET" action="{{ route('process.unposting') }}" id="filterForm">
            <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <div class="flex items-end gap-4 flex-wrap justify-between">
                    <!-- Left Side Filters -->
                    <div class="flex items-end gap-4 flex-wrap">
                        <!-- Observation Type -->
                        <div>
                            <label for="unposting" class="block text-sm font-semibold text-gray-700 mb-2">Pilih
                                Pengamatan:</label>
                            <select name="unposting" id="unposting"
                                onchange="Alpine.store('loading').start(); this.form.submit();"
                                class="px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium text-gray-700 bg-white transition-all duration-200">
                                <option value="" {{ $unposting == '' ? 'selected' : '' }}>
                                    --Pilih Pengamatan--
                                </option>
                                <option value="Agronomi" {{ $unposting == 'Agronomi' ? 'selected' : '' }}>
                                    Agronomi
                                </option>
                                <option value="HPT" {{ $unposting == 'HPT' ? 'selected' : '' }}>
                                    HPT
                                </option>
                            </select>
                        </div>

                        <!-- Date Filter -->
                        <div class="flex items-center gap-3">
                            <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
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
                                    <span>Pilih Tanggal</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div class="absolute left-0 z-10 mt-2 w-80 rounded-lg bg-white border border-gray-200 shadow-xl hidden"
                                    id="menu-dropdown">
                                    <div class="p-4 space-y-4">
                                        <div>
                                            <label for="start_date"
                                                class="block text-sm font-semibold text-gray-700 mb-2">Tanggal
                                                Mulai</label>
                                            <input type="date" id="start_date" name="start_date"
                                                value="{{ old('start_date', $startDate ?? '') }}"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm transition-all duration-200">
                                        </div>

                                        <div>
                                            <label for="end_date"
                                                class="block text-sm font-semibold text-gray-700 mb-2">Tanggal
                                                Akhir</label>
                                            <input type="date" id="end_date" name="end_date"
                                                value="{{ old('end_date', $endDate ?? '') }}"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm transition-all duration-200">
                                        </div>

                                        <button type="submit"
                                            class="w-full py-2.5 px-4 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                                            Terapkan Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side Filters -->
                    <div class="flex items-center gap-4 flex-wrap">
                        <div id="ajax-data" data-url="{{ route('process.unposting') }}">
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
                                placeholder="Cari Sample, Variety, Category..." />
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Info Note -->
        @if (!empty($unposting))
            <div class="px-6 py-3 bg-red-50 border-b border-red-100">
                <p class="text-xs text-red-600 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>Centang checkbox untuk data yang akan di-unpost dari sistem</span>
                </p>
            </div>
        @endif

        <!-- Table Section -->
        <div class="px-6 py-5">
            @if (empty($unposting))
                <div class="text-center py-12">
                    <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Pengamatan Dipilih</h3>
                    <p class="text-gray-500 text-sm">Silakan pilih jenis pengamatan terlebih dahulu (Agronomi atau HPT)
                    </p>
                </div>
            @else
                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full bg-white text-sm" id="tables">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-100 to-gray-50">
                                <th
                                    class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap w-1">
                                    <input type="checkbox" id="selectAll" onclick="toggleCheckboxes(this)"
                                        class="rounded focus:ring-2 focus:ring-red-500">
                                </th>
                                <th
                                    class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                    No.</th>
                                <th
                                    class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                    No. Sample</th>
                                <th
                                    class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                    Varietas</th>
                                <th
                                    class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                    Kategori</th>
                                <th
                                    class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                    Tanggal Tanam</th>
                                <th
                                    class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                    Tanggal Pengamatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($unposts as $item)
                                <tr class="hover:bg-red-50 transition-colors duration-150">
                                    <td class="py-3 px-4 text-center w-1">
                                        <input type="checkbox"
                                            class="rowCheckbox rounded focus:ring-2 focus:ring-red-500"
                                            name="selected_items[]"
                                            value="{{ $item->nosample }},{{ $item->companycode }},{{ $item->tanggalpengamatan }}">
                                    </td>
                                    <td class="py-3 px-4 text-center text-gray-700">{{ $item->no }}.</td>
                                    <td class="py-3 px-4 text-center text-gray-700 font-medium">{{ $item->nosample }}
                                    </td>
                                    <td class="py-3 px-4 text-center text-gray-700">{{ $item->varietas }}</td>
                                    <td class="py-3 px-4 text-center text-gray-700">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $item->kat }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center text-gray-700">{{ $item->tanggaltanam }}</td>
                                    <td class="py-3 px-4 text-center text-gray-700">{{ $item->tanggalpengamatan }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-500">
                                        Tidak ada data yang tersedia
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Pagination Section -->
        @if (!empty($unposting))
            <div class="px-6 pb-2" id="pagination-links">
                @if ($unposts->hasPages())
                    {{ $unposts->appends([
                            'perPage' => $unposts->perPage(),
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'unposting' => $unposting,
                            'search' => $search,
                        ])->links() }}
                @else
                    <div class="flex items-center justify-between bg-gray-50 px-4 py-3 rounded-lg">
                        <p class="text-sm text-gray-600">
                            Menampilkan <span class="font-semibold text-gray-800">{{ $unposts->count() }}</span> dari
                            <span class="font-semibold text-gray-800">{{ $unposts->total() }}</span> hasil
                        </p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <script>
        function toggleCheckboxes(source) {
            const checkboxes = document.querySelectorAll('.rowCheckbox');
            checkboxes.forEach(checkbox => checkbox.checked = source.checked);
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

        document.querySelector("#unpost").addEventListener("submit", function(event) {
            event.preventDefault();
            const selectedItems = [];
            document.querySelectorAll(".rowCheckbox:checked").forEach(checkbox => {
                selectedItems.push(checkbox.value);
            });
            if (selectedItems.length === 0) {
                alert("Silakan pilih setidaknya satu data untuk di unposting.");
                return;
            }
            const selectedSamples = selectedItems.map(item => item.split(',')[0]);
            const confirmText =
                `Yakin ingin unposting data untuk nomor sample berikut?\n\n${selectedSamples.join(', ')}`;
            if (!confirm(confirmText)) {
                return;
            }
            document.getElementById("selected_items").value = JSON.stringify(selectedItems);
            this.submit();
        });

        // Handle search input
        const searchInput = document.getElementById('search');
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 500);
        });

        // Handle perPage change
        const perPageInput = document.getElementById('perPage');
        let perPageTimeout;
        perPageInput.addEventListener('input', function() {
            clearTimeout(perPageTimeout);
            perPageTimeout = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 500);
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
    </style>

</x-layout>
