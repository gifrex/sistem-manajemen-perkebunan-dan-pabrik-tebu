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
                        <svg class="w-7 h-7 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                            <path fill-rule="evenodd"
                                d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                clip-rule="evenodd" />
                        </svg>
                        Rencana Kerja Mingguan
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola dan pantau rencana kerja mingguan Anda</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2 flex-wrap">
                    @can('transaction.rencanakerjamingguan.create')
                        <button type="button" id="openModalBtn"
                            class="bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-700 hover:to-sky-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Create RKM</span>
                        </button>
                    @endcan
                    @can('transaction.rencanakerjamingguan.export')
                        <button id="btn-export-rkm"
                            class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2 disabled:opacity-75 disabled:cursor-not-allowed disabled:translate-y-0">
                            <svg id="icon-export-rkm" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd"
                                    d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v9.293l-2-2a1 1 0 0 0-1.414 1.414l.293.293h-6.586a1 1 0 1 0 0 2h6.586l-.293.293A1 1 0 0 0 18 16.707l2-2V20a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <svg id="spinner-export-rkm" class="w-5 h-5 animate-spin hidden"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span id="text-export-rkm">Export</span>
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <form method="POST" action="{{ route('transaction.rencana-kerja-mingguan.index') }}" id="filterForm">
            @csrf
            <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <div class="flex items-end gap-4 flex-wrap justify-between">
                    <!-- Left Side: Date Filter -->
                    <div class="flex items-end gap-4 flex-wrap">
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
                                            <input type="date" id="start_date" name="start_date"
                                                value="{{ old('start_date', $startDate ?? now()->startOfWeek()->format('Y-m-d')) }}"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm transition-all duration-200">
                                        </div>
                                        <div>
                                            <label for="end_date"
                                                class="block text-sm font-semibold text-gray-700 mb-2">Tanggal
                                                Akhir</label>
                                            <input type="date" id="end_date" name="end_date"
                                                value="{{ old('end_date', $endDate ?? now()->endOfWeek()->format('Y-m-d')) }}"
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
                    </div>

                    <!-- Right Side: Per Page + Search -->
                    <div class="flex items-center gap-4 flex-wrap">
                        <div id="ajax-data" data-url="{{ route('transaction.rencana-kerja-mingguan.index') }}">
                            <div class="flex items-center gap-2">
                                <label for="perPage"
                                    class="text-sm font-semibold text-gray-700 whitespace-nowrap">Items per
                                    page:</label>
                                <input type="text" name="perPage" id="perPage" value="{{ $perPage }}"
                                    autocomplete="off"
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
                                placeholder="Search No.RKM, or Activity..." />
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Table Section -->
        <div class="px-6 py-5">
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm" id="tables">
                <table class="min-w-full bg-white text-sm">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-100 to-gray-50">
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                No.</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                No. RKM</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-left whitespace-nowrap">
                                RKM Date</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-left whitespace-nowrap">
                                Start Date</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-left whitespace-nowrap">
                                End Date</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-left whitespace-nowrap">
                                Kode Aktivitas</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-left whitespace-nowrap">
                                Nama Aktivitas</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-left whitespace-nowrap">
                                Input By</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($rkm as $item)
                            <tr class="hover:bg-blue-50 transition-colors duration-150">
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->no }}.</td>
                                <td class="py-3 px-4 text-center text-gray-700 font-medium">{{ $item->rkmno }}</td>
                                <td class="py-3 px-4 text-left text-gray-700">{{ $item->rkmdate }}</td>
                                <td class="py-3 px-4 text-left text-gray-700">{{ $item->startdate ?? '-' }}</td>
                                <td class="py-3 px-4 text-left text-gray-700">{{ $item->enddate ?? '-' }}</td>
                                <td class="py-3 px-4 text-left text-gray-700">{{ $item->activitycode ?? '-' }}</td>
                                <td class="py-3 px-4 text-left text-gray-700">{{ $item->activityname ?? '-' }}</td>
                                <td class="py-3 px-4 text-left text-gray-700">{{ $item->inputby ?? '-' }}</td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button onclick="showList('{{ $item->rkmno }}')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-all duration-200"
                                            title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                <path stroke-width="2"
                                                    d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z" />
                                            </svg>
                                            Detail
                                        </button>
                                        @can('transaction.rencanakerjamingguan.edit')
                                            <a href="{{ route('transaction.rencana-kerja-mingguan.edit', ['rkmno' => $item->rkmno]) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-all duration-200"
                                                title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z" />
                                                </svg>
                                                Edit
                                            </a>
                                        @endcan
                                        @can('transaction.rencanakerjamingguan.delete')
                                            <form
                                                action="{{ route('transaction.rencana-kerja-mingguan.destroy', ['rkmno' => $item->rkmno]) }}"
                                                method="POST" class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg border border-red-200 transition-all duration-200"
                                                    onclick="return confirm('Yakin ingin menghapus data ini?')"
                                                    title="Delete">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                                                    </svg>
                                                    Hapus
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center">
                                    <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Tidak Ada Data</h3>
                                    <p class="text-gray-500 text-sm">Belum ada rencana kerja mingguan yang sesuai
                                        dengan filter Anda</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="px-6 pb-2" id="pagination-links">
            @if ($rkm->hasPages())
                {{ $rkm->appends(['perPage' => $rkm->perPage()])->links() }}
            @else
                <div class="flex items-center justify-between bg-gray-50 px-4 py-3 rounded-lg">
                    <p class="text-sm text-gray-600">
                        Menampilkan <span class="font-semibold text-gray-800">{{ $rkm->count() }}</span> dari
                        <span class="font-semibold text-gray-800">{{ $rkm->total() }}</span> hasil
                    </p>
                </div>
            @endif
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
                        <p id="errorMessage" class="text-red-500 text-sm mt-2 hidden">Silakan pilih tanggal terlebih
                            dahulu</p>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 p-6 border-t bg-gray-50 rounded-b-2xl">
                    <button type="button" id="cancelBtn"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn"
                        class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-8 py-2.5 text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        Lanjutkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="listModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm p-4 invisible opacity-0 transition-all duration-300">
        <div
            class="bg-white w-11/12 max-w-5xl max-h-[90vh] flex flex-col rounded-2xl shadow-2xl transform scale-95 transition-transform duration-300">
            <div
                class="flex items-center justify-between p-6 border-b bg-gradient-to-r from-blue-50 to-indigo-50 rounded-t-2xl flex-shrink-0">
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
            <div class="overflow-auto flex-1 p-6">
                <div class="rounded-lg border border-gray-200 shadow-sm overflow-hidden">
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
                            <tr>
                                <td colspan="8" class="text-center py-8">
                                    <div
                                        class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-gray-300 border-t-blue-600">
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

        // Search & perPage debounce
        const searchInput = document.getElementById('search');
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => document.getElementById('filterForm').submit(), 500);
        });
        const perPageInput = document.getElementById('perPage');
        let perPageTimeout;
        perPageInput.addEventListener('input', function() {
            clearTimeout(perPageTimeout);
            perPageTimeout = setTimeout(() => document.getElementById('filterForm').submit(), 500);
        });

        // Create RKM Modal
        const createModal = document.getElementById('targetDateModal');
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
            targetDateInput.setAttribute('min', today.toISOString().split('T')[0]);
            targetDateInput.setAttribute('max', maxDate.toISOString().split('T')[0]);
        }

        openModalBtn.addEventListener('click', () => {
            createModal.classList.remove('invisible');
            createModal.classList.add('visible');
            setTimeout(() => {
                createModal.style.opacity = "1";
                createModal.querySelector('.modal-content').style.transform = "scale(1)";
            }, 10);
            targetDateInput.value = new Date().toISOString().split('T')[0];
            errorMessage.classList.add('hidden');
            setDateLimits();
        });

        function closeCreateModal() {
            createModal.style.opacity = "0";
            createModal.querySelector('.modal-content').style.transform = "scale(0.95)";
            setTimeout(() => {
                createModal.classList.remove('visible');
                createModal.classList.add('invisible');
            }, 300);
        }
        closeModalBtn.addEventListener('click', closeCreateModal);
        cancelBtn.addEventListener('click', closeCreateModal);
        createModal.addEventListener('click', (e) => {
            if (e.target === createModal) closeCreateModal();
        });

        targetDateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!targetDateInput.value) {
                errorMessage.classList.remove('hidden');
                return;
            }
            window.location.href = "{{ route('transaction.rencana-kerja-mingguan.create') }}" + "?targetDate=" +
                targetDateInput.value;
        });

        // Detail Modal
        function showList(rkmno) {
            const modal = document.getElementById('listModal');
            const tableBody = document.getElementById('listTableBody');

            // Show loading indicator immediately
            tableBody.innerHTML =
                '<tr><td colspan="8" class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-gray-300 border-t-blue-600"></div></td></tr>';

            // Open modal right away before fetch
            modal.classList.remove('invisible');
            modal.classList.add('visible');
            setTimeout(() => {
                modal.style.opacity = "1";
                modal.querySelector('.bg-white').style.transform = "scale(1)";
            }, 10);

            const url =
                `{{ route('transaction.rencana-kerja-mingguan.show', ['rkmno' => '__rkmno__', 'companycode' => '__companycode__']) }}`
                .replace('__rkmno__', rkmno).replace('__companycode__', '');

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    tableBody.innerHTML = '';
                    if (!data.length) {
                        tableBody.innerHTML =
                            '<tr><td colspan="8" class="text-center py-8 text-gray-500">Tidak ada data</td></tr>';
                    } else {
                        data.forEach(item => {
                            tableBody.innerHTML += `
                                <tr class="hover:bg-blue-50 transition-colors duration-150">
                                    <td class="px-4 py-3 text-sm text-gray-900">${item.no}.</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">${item.rkmno}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.blok}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.plot}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.totalluasactual}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.totalestimasi}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.hasil ?? 0}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.sisa ?? 0}</td>
                                </tr>`;
                        });
                    }
                })
                .catch(err => {
                    tableBody.innerHTML =
                        `<tr><td colspan="8" class="text-center py-8 text-red-600">Gagal memuat data: ${err.message}</td></tr>`;
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

        /* ── Export Excel ── */
        document.getElementById('btn-export-rkm')?.addEventListener('click', function() {
            const btn = this;
            const icon = document.getElementById('icon-export-rkm');
            const spinner = document.getElementById('spinner-export-rkm');
            const text = document.getElementById('text-export-rkm');

            const startDate = document.getElementById('start_date')?.value ?? '';
            const endDate = document.getElementById('end_date')?.value ?? '';
            const search = document.getElementById('search')?.value ?? '';

            const url = new URL('{{ route('transaction.rencana-kerja-mingguan.exportExcel') }}', window.location
                .origin);
            if (startDate) url.searchParams.set('start_date', startDate);
            if (endDate) url.searchParams.set('end_date', endDate);
            if (search) url.searchParams.set('search', search);

            btn.disabled = true;
            icon.classList.add('hidden');
            spinner.classList.remove('hidden');
            text.textContent = 'Exporting...';

            fetch(url.toString())
                .then(res => {
                    const disposition = res.headers.get('Content-Disposition');
                    let filename = 'RKMReport.xlsx';
                    if (disposition) {
                        const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/i);
                        if (match && match[1]) filename = match[1].replace(/['"]/g, '').trim();
                    }
                    return res.blob().then(blob => ({
                        blob,
                        filename
                    }));
                })
                .then(({
                    blob,
                    filename
                }) => {
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(a.href);
                })
                .catch(err => console.error('Export failed:', err))
                .finally(() => {
                    btn.disabled = false;
                    icon.classList.remove('hidden');
                    spinner.classList.add('hidden');
                    text.textContent = 'Export';
                });
        });
    </script>
</x-layout>
