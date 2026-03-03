<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $title }}</x-slot:nav>

    <div class="mx-auto py-6 bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-lg border border-gray-200">
        <!-- Header Section -->
        <div class="px-6 pb-4 border-b border-gray-200">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <!-- Title & Info -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-7 h-7 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"
                                clip-rule="evenodd" />
                        </svg>
                        Rekap Upah Mingguan
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola dan pantau rekap upah tenaga kerja mingguan</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2 flex-wrap">
                    <button type="button" onclick="openPreviewReport()"
                        class="bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-700 hover:to-sky-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd"
                                d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Zm.5 5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Zm0 5c.47 0 .917-.092 1.326-.26l1.967 1.967a1 1 0 0 0 1.414-1.414l-1.817-1.818A3.5 3.5 0 1 0 11.5 17Z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Preview</span>
                    </button>
                    <button type="button" onclick="printBp()"
                        class="bg-gradient-to-r from-red-600 to-rose-500 hover:from-red-700 hover:to-rose-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16.444 18H19a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2.556M17 11V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v6h10ZM7 15h10v4a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-4Z" />
                        </svg>
                        <span>Print BP</span>
                    </button>
                    <button type="button" onclick="exportToExcel()"
                        class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd"
                                d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Zm2-2a1 1 0 1 0 0 2h3a1 1 0 1 0 0-2h-3Zm0 3a1 1 0 1 0 0 2h3a1 1 0 1 0 0-2h-3Zm-6 4a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-6Zm8 1v1h-2v-1h2Zm0 3h-2v1h2v-1Zm-4-3v1H9v-1h2Zm0 3H9v1h2v-1Z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Export</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <form method="POST" action="{{ route('report.rekap-upah-mingguan.index') }}" id="filterForm">
            @csrf
            <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <div class="flex items-end gap-4 flex-wrap justify-between">
                    <!-- Left Side Filters -->
                    <div class="flex items-end gap-4 flex-wrap">
                        <!-- Jenis Tenaga Kerja -->
                        <div>
                            <label for="tenagakerjarum" class="block text-sm font-semibold text-gray-700 mb-2">
                                Jenis Tenaga Kerja: <span class="text-red-500">*</span>
                            </label>
                            <select name="tenagakerjarum" id="tenagakerjarum"
                                onchange="Alpine.store('loading').start(); this.form.submit()"
                                class="px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-medium text-gray-700 bg-white transition-all duration-200"
                                required>
                                <option value="" disabled
                                    {{ old('tenagakerjarum', session('tenagakerjarum')) == null ? 'selected' : '' }}>
                                    -- Pilih Tenaga Kerja --
                                </option>
                                <option value="Harian"
                                    {{ old('tenagakerjarum', session('tenagakerjarum')) == 'Harian' ? 'selected' : '' }}>
                                    Harian
                                </option>
                                <option value="Borongan"
                                    {{ old('tenagakerjarum', session('tenagakerjarum')) == 'Borongan' ? 'selected' : '' }}>
                                    Borongan
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
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 text-sm font-medium text-gray-700"
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

                                <div class="absolute left-0 z-10 mt-2 w-56 rounded-lg bg-white border border-gray-200 shadow-xl hidden"
                                    id="menu-dropdown">
                                    <div class="p-4 space-y-4">
                                        <div>
                                            <label for="start_date"
                                                class="block text-sm font-semibold text-gray-700 mb-2">Tanggal
                                                Mulai</label>
                                            <input type="date" id="start_date" name="start_date" required
                                                value="{{ old('start_date', $startDate ?? now()->startOfWeek()->format('Y-m-d')) }}"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 text-sm transition-all duration-200">
                                        </div>
                                        <div>
                                            <label for="end_date"
                                                class="block text-sm font-semibold text-gray-700 mb-2">Tanggal
                                                Akhir</label>
                                            <input type="date" id="end_date" name="end_date" required
                                                value="{{ old('end_date', $endDate ?? now()->endOfWeek()->format('Y-m-d')) }}"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 text-sm transition-all duration-200">
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
                    </div>

                    <!-- Right Side Filters -->
                    <div class="flex items-center gap-4 flex-wrap">
                        <div id="ajax-data" data-url="{{ route('report.rekap-upah-mingguan.index') }}">
                            <div class="flex items-center gap-2">
                                <label for="perPage"
                                    class="text-sm font-semibold text-gray-700 whitespace-nowrap">Items per
                                    page:</label>
                                <input type="text" name="perPage" id="perPage" value="{{ $perPage }}"
                                    autocomplete="off"
                                    class="w-16 px-3 py-2 border border-gray-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-all duration-200" />
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
                                class="w-80 pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                                placeholder="Search Kegiatan..." />
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Info Note -->
        @if (session('tenagakerjarum') != null)
            <div class="px-6 py-3 bg-indigo-50 border-b border-indigo-100">
                <p class="text-xs text-indigo-600 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>Klik ikon mata pada kolom Actions untuk melihat detail rekap upah per LKH</span>
                </p>
            </div>
        @endif

        <!-- Table Section -->
        <div class="px-6 py-5">
            @if (session('tenagakerjarum') == null || !$startDate || !$endDate)
                <div class="text-center py-12">
                    <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Data</h3>
                    <p class="text-gray-500 text-sm">Silakan pilih Jenis Tenaga Kerja dan Range Tanggal</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm" id="tables">
                    <table class="min-w-full bg-white text-sm">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-100 to-gray-50">
                                <th
                                    class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                    No.</th>
                                <th
                                    class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                    LKH No.</th>
                                <th
                                    class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-left whitespace-nowrap">
                                    Kegiatan</th>
                                <th
                                    class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-left whitespace-nowrap">
                                    Plot</th>
                                <th
                                    class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                    Tanggal</th>
                                <th
                                    class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                    Total Biaya (Rp)</th>
                                <th
                                    class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                    {{ session('tenagakerjarum') == 'Harian' ? 'TKH' : 'TKB' }}
                                </th>
                                <th
                                    class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($rum as $item)
                                <tr class="hover:bg-indigo-50 transition-colors duration-150">
                                    <td class="py-3 px-4 text-center text-gray-700">{{ $item->no }}.</td>
                                    <td class="py-3 px-4 text-center text-gray-700 font-medium">{{ $item->lkhno }}
                                    </td>
                                    <td class="py-3 px-4 text-left text-gray-700">{{ $item->activityname }}</td>
                                    <td class="py-3 px-4 text-left text-gray-700 max-w-xs truncate">
                                        {{ $item->plots }}</td>
                                    <td class="py-3 px-4 text-center text-gray-700">{{ $item->lkhdate }}</td>
                                    <td class="py-3 px-4 text-center text-gray-700">{{ $item->totalupahall }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ $item->totalworkers ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <button onclick="showList('{{ $item->lkhno }}')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg border border-indigo-200 transition-all duration-200"
                                            title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                <path stroke-width="2"
                                                    d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z" />
                                            </svg>
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-gray-500">
                                        Tidak ada data rekap upah yang sesuai dengan filter Anda
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Pagination -->
        @if (session('tenagakerjarum') != null && $startDate && $endDate)
            <div class="px-6 pb-2" id="pagination-links">
                @if ($rum->hasPages())
                    {{ $rum->appends(['perPage' => $rum->perPage()])->links() }}
                @else
                    <div class="flex items-center justify-between bg-gray-50 px-4 py-3 rounded-lg">
                        <p class="text-sm text-gray-600">
                            Menampilkan <span class="font-semibold text-gray-800">{{ $rum->count() }}</span> dari
                            <span class="font-semibold text-gray-800">{{ $rum->total() }}</span> hasil
                        </p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Detail Modal -->
    <div id="listModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm p-4 invisible opacity-0 transition-all duration-300">
        <div
            class="bg-white w-11/12 max-w-7xl max-h-[90vh] flex flex-col rounded-2xl shadow-2xl transform scale-95 transition-transform duration-300">
            <!-- Modal Header -->
            <div
                class="flex items-center justify-between p-6 border-b bg-gradient-to-r from-indigo-50 to-purple-50 rounded-t-2xl flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                            <path fill-rule="evenodd"
                                d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Detail Daftar List</h2>
                </div>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg transition-all duration-200">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="overflow-auto flex-1 p-6">
                @if (session('tenagakerjarum') == 'Harian')
                    <!-- Info Card above table (Harian only) -->
                    <div class="mb-4 grid grid-cols-3 gap-3" id="modal-info-cards">
                        <div class="bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-3">
                            <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wider mb-1">Plot</p>
                            <p class="text-sm font-medium text-gray-800" id="modal-plot">-</p>
                        </div>
                        <div class="bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-3">
                            <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wider mb-1">Status Tanam
                            </p>
                            <p class="text-sm font-medium text-gray-800" id="modal-status">-</p>
                        </div>
                        <div class="bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-3">
                            <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wider mb-1">Hasil (Ha)
                            </p>
                            <p class="text-sm font-medium text-gray-800" id="modal-hasil">-</p>
                        </div>
                    </div>
                @endif
                <div class="rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-100 to-gray-200 sticky top-0">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    No.</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Kegiatan</th>
                                @if (session('tenagakerjarum') == 'Harian')
                                    <th
                                        class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Tenaga Kerja</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Luasan (Ha)</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Cost/Unit</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Biaya (Rp)</th>
                                @else
                                    <th
                                        class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Plot</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Luasan (Ha)</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Status Tanam</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Hasil (Ha)</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="listTableBody" class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="10" class="text-center py-8">
                                    <div
                                        class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-gray-300 border-t-indigo-600">
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        th,
        td {
            white-space: nowrap;
        }

        .invisible {
            visibility: hidden;
            pointer-events: none;
        }

        .visible {
            visibility: visible;
            pointer-events: auto;
        }

        .overflow-auto::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .overflow-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .overflow-auto::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .overflow-auto::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

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

    <script>
        function toggleDropdown() {
            document.getElementById('menu-dropdown').classList.toggle('hidden');
        }

        document.addEventListener("click", function(event) {
            const dropdown = document.getElementById("menu-dropdown");
            const button = document.getElementById("menu-button");
            if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                dropdown.classList.add("hidden");
            }
        });

        // Search with debounce
        const searchInput = document.getElementById('search');
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => document.getElementById('filterForm').submit(), 500);
        });

        // PerPage with debounce
        const perPageInput = document.getElementById('perPage');
        let perPageTimeout;
        perPageInput.addEventListener('input', function() {
            clearTimeout(perPageTimeout);
            perPageTimeout = setTimeout(() => document.getElementById('filterForm').submit(), 500);
        });

        function showList(lkhno) {
            const modal = document.getElementById('listModal');
            const tableBody = document.getElementById('listTableBody');

            tableBody.innerHTML =
                '<tr><td colspan="10" class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-gray-300 border-t-indigo-600"></div></td></tr>';

            const url = `{{ route('report.rekap-upah-mingguan.show', ['lkhno' => '__lkhno__']) }}`.replace('__lkhno__',
                lkhno);

            fetch(url)
                .then(r => {
                    if (!r.ok) throw new Error(`HTTP error! status: ${r.status}`);
                    return r.json();
                })
                .then(response => {
                    tableBody.innerHTML = '';
                    if (response.error) {
                        tableBody.innerHTML =
                            `<tr><td colspan="10" class="text-center py-8 text-red-600">${response.error}</td></tr>`;
                        return;
                    }
                    const data = response.data || response;
                    if (!data || data.length === 0) {
                        tableBody.innerHTML =
                            '<tr><td colspan="10" class="text-center py-8 text-gray-500">Tidak ada data</td></tr>';
                        return;
                    }

                    let totalBiaya = 0;
                    data.forEach(item => {
                        @if (session('tenagakerjarum') == 'Harian')
                            const biaya = item.total || '0';
                            totalBiaya += parseFloat(biaya.toString().replace(/[^0-9,-]/g, '').replace(',',
                                '.')) || 0;
                        @endif
                        @if (session('tenagakerjarum') == 'Harian')
                            // Populate info cards from first item
                            if (item.no == 1) {
                                document.getElementById('modal-plot').textContent = item.plot || '-';
                                document.getElementById('modal-status').textContent =
                                    `${item.batchdate || ''}/${item.lifecyclestatus}`;
                                document.getElementById('modal-hasil').textContent = item.luashasil || '-';
                            }
                            tableBody.innerHTML += `
                                <tr class="hover:bg-indigo-50 transition-colors duration-150">
                                    <td class="px-4 py-3 text-sm text-gray-900">${item.no}.</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.activityname || ''}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.namatenagakerja}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.luasrkh || ''}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.upah || '-'}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.total || '-'}</td>
                                </tr>`;
                        @else
                            tableBody.innerHTML += `
                                <tr class="hover:bg-indigo-50 transition-colors duration-150">
                                    <td class="px-4 py-3 text-sm text-gray-900">${item.no}.</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.activityname || ''}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.plot || ''}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.luasrkh || ''}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.batchdate || ''}/${item.lifecyclestatus}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.luashasil || ''}</td>
                                </tr>`;
                        @endif
                    });

                    @if (session('tenagakerjarum') == 'Harian')
                        const totalFormatted = new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 2
                        }).format(totalBiaya);
                        tableBody.innerHTML += `
                            <tr class="font-bold bg-indigo-50">
                                <td colspan="5" class="px-4 py-3 text-right border-t-2 border-indigo-400 text-gray-900">Total Biaya:</td>
                                <td class="px-4 py-3 border-t-2 border-indigo-400 text-indigo-700">${totalFormatted}</td>
                            </tr>`;
                    @endif

                    modal.classList.remove('invisible');
                    modal.classList.add('visible');
                    setTimeout(() => {
                        modal.style.opacity = "1";
                        modal.querySelector('.bg-white').style.transform = "scale(1)";
                    }, 10);
                })
                .catch(err => {
                    tableBody.innerHTML =
                        `<tr><td colspan="10" class="text-center py-8 text-red-600">Gagal memuat data: ${err.message}</td></tr>`;
                });
        }

        function closeModal() {
            const modal = document.getElementById('listModal');
            modal.style.opacity = "0";
            modal.querySelector('.bg-white').style.transform = "scale(0.95)";
            setTimeout(() => {
                modal.classList.remove('visible');
                modal.classList.add('invisible');
            }, 300);
        }

        function openPreviewReport() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const tenagaKerja = document.getElementById('tenagakerjarum').value;
            if (!tenagaKerja) return alert('Harap pilih tenaga kerja terlebih dahulu');
            if (!startDate || !endDate) return alert('Harap pilih range tanggal terlebih dahulu');
            window.open(`{{ route('report.rekap-upah-mingguan.preview') }}?start_date=${startDate}&end_date=${endDate}`,
                '_blank');
        }

        function printBp() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const tenagaKerja = document.getElementById('tenagakerjarum').value;
            if (!tenagaKerja) return alert('Harap pilih tenaga kerja terlebih dahulu');
            if (!startDate || !endDate) return alert('Harap pilih range tanggal terlebih dahulu');
            window.open(
                `{{ route('report.rekap-upah-mingguan.print-bp') }}?start_date=${startDate}&end_date=${endDate}&tenagakerja=${tenagaKerja}`,
                '_blank');
        }

        function exportToExcel() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            if (!startDate || !endDate) return alert('Harap pilih range tanggal terlebih dahulu');
            window.location.href =
                `{{ route('report.rekap-upah-mingguan.export-excel') }}?start_date=${startDate}&end_date=${endDate}`;
        }
    </script>
</x-layout>
