<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $title }}</x-slot:nav>

    <div class="mx-auto py-4">
        <!-- Header Card with Gradient -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white mb-2">Rencana Kerja Mingguan</h1>
                    <p class="text-blue-100 text-sm">Kelola dan pantau rencana kerja mingguan Anda</p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-16 h-16 text-blue-300 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                        <path fill-rule="evenodd"
                            d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-visible">
            <!-- Action Bar -->
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200 p-4 rounded-t-lg">
                <div class="flex flex-wrap items-center gap-3 lg:justify-between justify-center">
                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <button type="button" id="openModalBtn"
                            class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-5 py-2.5 text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Create RKM</span>
                        </button>
                        <button
                            class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-5 py-2.5 text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2"
                            onclick="window.location.href='{{ route('transaction.rencana-kerja-mingguan.exportExcel', ['start_date' => old('start_date', request()->start_date), 'end_date' => old('end_date', request()->end_date)]) }}'">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd"
                                    d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v9.293l-2-2a1 1 0 0 0-1.414 1.414l.293.293h-6.586a1 1 0 1 0 0 2h6.586l-.293.293A1 1 0 0 0 18 16.707l2-2V20a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Export Excel</span>
                        </button>
                    </div>

                    <!-- Filters Form -->
                    <form method="POST" action="{{ route('transaction.rencana-kerja-mingguan.index') }}">
                        @csrf
                        <div class="flex items-center gap-2 flex-wrap">
                            <!-- Items per page -->
                            <div
                                class="flex items-center gap-2 bg-white px-3 py-2 rounded-lg border border-gray-200 shadow-sm">
                                <label for="perPage"
                                    class="text-xs font-medium text-gray-600 whitespace-nowrap">Items:</label>
                                <input type="text" name="perPage" id="perPage" value="{{ $perPage }}"
                                    class="w-12 p-1.5 border border-gray-300 rounded-md text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                            </div>

                            <!-- Date Filter Dropdown -->
                            <div class="relative">
                                <button type="button"
                                    class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-lg border border-gray-200 shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all duration-200"
                                    id="menu-button" onclick="toggleDropdown()">
                                    <svg class="h-4 w-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Date Filter</span>
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div class="absolute z-10 mt-2 w-72 rounded-lg bg-white border border-gray-200 shadow-xl hidden"
                                    id="menu-dropdown">
                                    <div class="p-4 space-y-3">
                                        <div>
                                            <label for="start_date"
                                                class="block text-sm font-semibold text-gray-700 mb-1.5">Start
                                                Date</label>
                                            <input type="date" id="start_date" name="start_date"
                                                value="{{ old('start_date', $startDate ?? now()->startOfWeek()->format('Y-m-d')) }}"
                                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm" />
                                        </div>
                                        <div>
                                            <label for="end_date"
                                                class="block text-sm font-semibold text-gray-700 mb-1.5">End
                                                Date</label>
                                            <input type="date" id="end_date" name="end_date"
                                                value="{{ old('end_date', $endDate ?? now()->endOfWeek()->format('Y-m-d')) }}"
                                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Search Box -->
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                    </svg>
                                </div>
                                <input type="text" id="search" name="search" value="{{ old('search', $search) }}"
                                    autocomplete="off"
                                    class="w-80 pl-10 pr-4 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                    placeholder="Search No.RKM, or Activity..." />
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="ajax-data" data-url="{{ route('transaction.rencana-kerja-mingguan.index') }}"></div>

            <!-- Table Container -->
            <div class="overflow-x-auto" id="tables">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-100 to-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">No.
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                No.
                                RKM</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                RKM Date</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Start Date</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                End Date</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Kode Aktivitas</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Nama Aktivitas</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Input By</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($rkm as $item)
                            <tr class="hover:bg-blue-50 transition-colors duration-150">
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->no }}.</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->rkmno }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->rkmdate }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->startdate ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->enddate ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->activitycode ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->activityname ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->inputby ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick="showList('{{ $item->rkmno }}')"
                                            class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                            title="View Details">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                <path stroke-width="2"
                                                    d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z" />
                                            </svg>
                                        </button>
                                        <a href="{{ route('transaction.rencana-kerja-mingguan.edit', ['rkmno' => $item->rkmno]) }}"
                                            class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                            title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z" />
                                            </svg>
                                        </a>
                                        <form
                                            action="{{ route('transaction.rencana-kerja-mingguan.destroy', ['rkmno' => $item->rkmno]) }}"
                                            method="POST" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="p-2 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200"
                                                onclick="return confirm('Yakin ingin menghapus data ini?')"
                                                title="Delete">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12">
                                    <div class="flex flex-col items-center justify-center text-center">
                                        <svg class="w-20 h-20 text-gray-300 mb-4" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Tidak Ada Aktivitas Minggu
                                            Ini</h3>
                                        <p class="text-gray-500 text-sm mb-4">Belum ada rencana kerja mingguan yang
                                            sesuai dengan filter Anda</p>
                                        <button type="button"
                                            onclick="document.getElementById('openModalBtn').click()"
                                            class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-2.5 text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                            <span>Buat RKM Baru</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 rounded-b-lg" id="pagination-links">
                @if ($rkm->hasPages())
                    {{ $rkm->appends(['perPage' => $rkm->perPage()])->links() }}
                @else
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600">
                            Showing <span class="font-semibold text-gray-900">{{ $rkm->count() }}</span> of
                            <span class="font-semibold text-gray-900">{{ $rkm->total() }}</span> results
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Create RKM Modal -->
    <div id="targetDateModal"
        class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 opacity-0 invisible transition-all duration-300">
        <div
            class="modal-content bg-white rounded-2xl shadow-2xl w-11/12 md:w-1/3 transform scale-95 transition-transform duration-300">
            <div
                class="flex justify-between items-center p-6 border-b bg-gradient-to-r from-blue-50 to-indigo-50 rounded-t-2xl">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center shadow-md">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Create RKM Baru</h2>
                        <p class="text-sm text-gray-600">Pilih tanggal untuk memulai</p>
                    </div>
                </div>
                <button id="closeModalBtn"
                    class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-2 transition-all duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="targetDateForm">
                <div class="p-6 space-y-4">
                    <div>
                        <label for="targetDate" class="block text-sm font-semibold text-gray-700 mb-2">
                            Tanggal RKM <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="targetDate" name="targetDate" required
                            class="w-full border-2 border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            Pilih tanggal untuk membuat RKM (maksimal 7 hari ke depan)
                        </p>
                        <p id="errorMessage" class="text-red-500 text-sm mt-2 hidden">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                                Silakan pilih tanggal terlebih dahulu
                            </span>
                        </p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 p-6 border-t bg-gray-50 rounded-b-2xl">
                    <button type="button" id="cancelBtn"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn"
                        class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 disabled:from-gray-400 disabled:to-gray-500 text-white px-8 py-2.5 text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        Lanjutkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- List Detail Modal -->
    <div id="listModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm invisible opacity-0 transition-all duration-300">
        <div
            class="bg-white w-11/12 max-w-6xl rounded-2xl shadow-2xl transform scale-95 transition-transform duration-300">
            <div
                class="flex items-center justify-between p-6 border-b bg-gradient-to-r from-blue-50 to-indigo-50 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                            <path fill-rule="evenodd"
                                d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Detail Daftar List</h2>
                </div>
                <button onclick="closeListModal()"
                    class="p-2 hover:bg-gray-100 rounded-lg transition-all duration-200">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 max-h-[70vh] overflow-auto">
                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-100 to-gray-200 sticky top-0">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    No.</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    No. RKM</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Blok</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Plot</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Luas Plot (Ha)</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Estimasi (Ha)</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Aktual (Ha)</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Sisa (Ha)</th>
                            </tr>
                        </thead>
                        <tbody id="listTableBody" class="bg-white divide-y divide-gray-200">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ========================================
        // RKM SPECIFIC SCRIPTS ONLY
        // (Remove duplicates already in global.js)
        // ========================================

        // 1. DROPDOWN TOGGLE (Page Specific)
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

        // 2. CREATE RKM MODAL (Page Specific)
        const modal = document.getElementById('targetDateModal');
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const targetDateForm = document.getElementById('targetDateForm');
        const targetDateInput = document.getElementById('targetDate');
        const submitBtn = document.getElementById('submitBtn');
        const errorMessage = document.getElementById('errorMessage');

        function setDateLimits() {
            const today = new Date();
            const maxDate = new Date();
            maxDate.setDate(today.getDate() + 7);
            const todayStr = today.toISOString().split('T')[0];
            const maxDateStr = maxDate.toISOString().split('T')[0];
            targetDateInput.setAttribute('min', todayStr);
            targetDateInput.setAttribute('max', maxDateStr);
        }

        function updateSubmitButton() {
            submitBtn.disabled = !targetDateInput.value;
        }

        openModalBtn.addEventListener('click', () => {
            modal.classList.remove('invisible');
            modal.classList.add('visible');
            setTimeout(() => {
                modal.style.opacity = "1";
                modal.querySelector('.modal-content').style.transform = "scale(1)";
            }, 10);
            const today = new Date();
            targetDateInput.value = today.toISOString().split('T')[0];
            errorMessage.classList.add('hidden');
            setDateLimits();
            updateSubmitButton();
        });

        function closeModal() {
            modal.style.opacity = "0";
            modal.querySelector('.modal-content').style.transform = "scale(0.95)";
            setTimeout(() => {
                modal.classList.remove('visible');
                modal.classList.add('invisible');
            }, 300);
        }

        closeModalBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        targetDateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const targetDate = targetDateInput.value;
            if (!targetDate) {
                errorMessage.classList.remove('hidden');
                return;
            }
            window.location.href = "{{ route('transaction.rencana-kerja-mingguan.create') }}" + "?targetDate=" +
                targetDate;
        });

        targetDateInput.addEventListener('input', () => {
            errorMessage.classList.add('hidden');
            updateSubmitButton();
        });

        // 3. SHOW LIST MODAL (Page Specific)
        function showList(rkmno, companycode) {
            const modal = document.getElementById('listModal');
            const tableBody = document.getElementById('listTableBody');

            tableBody.innerHTML =
                '<tr><td colspan="8" class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-gray-300 border-t-blue-600"></div></td></tr>';

            const url =
                `{{ route('transaction.rencana-kerja-mingguan.show', ['rkmno' => '__rkmno__', 'companycode' => '__companycode__']) }}`
                .replace('__rkmno__', rkmno)
                .replace('__companycode__', companycode);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    tableBody.innerHTML = '';

                    if (data.length === 0) {
                        tableBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="px-4 py-12">
                            <div class="flex flex-col items-center justify-center text-center">
                                <svg class="w-16 h-16 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="text-base font-semibold text-gray-700 mb-1">Tidak Ada Detail</h3>
                                <p class="text-gray-500 text-sm">Belum ada data detail untuk RKM ini</p>
                            </div>
                        </td>
                    </tr>
                `;
                    } else {
                        data.forEach((item, index) => {
                            const row = `
                        <tr class="hover:bg-blue-50 transition-colors duration-150">
                            <td class="px-4 py-3 text-sm text-gray-900">${item.no}.</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">${item.rkmno}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">${item.blok}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">${item.plot}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">${item.totalluasactual}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">${item.totalestimasi}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">${item.hasil ?? 0}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">${item.sisa ?? 0}</td>
                        </tr>
                    `;
                            tableBody.innerHTML += row;
                        });
                    }

                    modal.classList.remove('invisible');
                    modal.classList.add('visible');
                    setTimeout(() => {
                        modal.style.opacity = "1";
                        modal.querySelector('.bg-white').style.transform = "scale(1)";
                    }, 10);
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    tableBody.innerHTML =
                        '<tr><td colspan="8" class="text-center py-8 text-red-600">Gagal memuat data. Silakan coba lagi.</td></tr>';
                });
        }

        function closeListModal() {
            const modal = document.getElementById('listModal');
            modal.style.opacity = "0";
            modal.querySelector('.bg-white').style.transform = "scale(0.95)";
            setTimeout(() => {
                modal.classList.remove('visible');
                modal.classList.add('invisible');
            }, 300);
        }
    </script>
</x-layout>
