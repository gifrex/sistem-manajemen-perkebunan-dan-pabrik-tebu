<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-6 bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-lg border border-gray-200">
        <!-- Header Section -->
        <div class="px-6 pb-4 border-b border-gray-200">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Data Agronomi
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola dan monitor data pengamatan agronomi</p>
                </div>

                <div class="flex gap-2 flex-wrap">
                    @can('transaction.agronomi.create')
                        <a href="{{ route('transaction.agronomi.create') }}"
                            class="bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-700 hover:to-sky-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Tambah Data</span>
                        </a>
                    @endcan
                    @can('transaction.agronomi.export')
                        <button data-export="agronomi"
                            class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd"
                                    d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v9.293l-2-2a1 1 0 0 0-1.414 1.414l.293.293h-6.586a1 1 0 1 0 0 2h6.586l-.293.293A1 1 0 0 0 18 16.707l2-2V20a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Export</span>
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
            <div class="flex items-end gap-4 flex-wrap justify-between">
                <!-- Left: Date Filter -->
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
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 transition-all duration-200 text-sm font-medium text-gray-700"
                            id="menu-button" onclick="toggleDropdown()">
                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span id="date-label">
                                {{ \Carbon\Carbon::parse($startDate)->format('d-M-Y') }} s/d
                                {{ \Carbon\Carbon::parse($endDate)->format('d-M-Y') }}
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
                                        class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Mulai</label>
                                    <input type="date" id="start_date" name="start_date"
                                        value="{{ old('start_date', $startDate ?? '') }}"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm transition-all duration-200">
                                </div>
                                <div>
                                    <label for="end_date" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal
                                        Akhir</label>
                                    <input type="date" id="end_date" name="end_date"
                                        value="{{ old('end_date', $endDate ?? '') }}"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm transition-all duration-200">
                                </div>
                                <button type="button" id="btn-apply-filter"
                                    onclick="
                                                document.getElementById('menu-dropdown').classList.add('hidden');
                                                document.getElementById('date-label').textContent =
                                                    fmtDate(document.getElementById('start_date').value) + ' s/d ' +
                                                    fmtDate(document.getElementById('end_date').value);
                                            "
                                    class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-all duration-200">
                                    Terapkan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Per Page + Search -->
                <div class="flex items-center gap-4 flex-wrap">
                    <div id="ajax-data" data-url="{{ route('transaction.agronomi.handle') }}">
                        <div class="flex items-center gap-2">
                            <label for="perPage" class="text-sm font-semibold text-gray-700 whitespace-nowrap">Items
                                per page:</label>
                            <input type="text" name="perPage" id="perPage" value="{{ $perPage }}"
                                autocomplete="off"
                                class="w-16 px-3 py-2 border border-gray-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-all duration-200" />
                        </div>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" id="search" autocomplete="off" name="search"
                            value="{{ old('search', $search) }}"
                            class="w-80 pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                            placeholder="Cari Sample, Plot, Varietas, atau Kategori..." />
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="px-6 py-5">
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm" id="tables">
                <table class="min-w-full bg-white text-sm">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-100 to-gray-50">
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                No</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-left whitespace-nowrap">
                                No. Sample</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-left whitespace-nowrap">
                                Plot</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-left whitespace-nowrap">
                                Varietas</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-left whitespace-nowrap">
                                Kategori</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-left whitespace-nowrap">
                                Tgl Tanam</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-left whitespace-nowrap">
                                Tgl Pengamatan</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Status</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($agronomi as $item)
                            <tr class="hover:bg-blue-50 transition-colors duration-150">
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->no }}</td>
                                <td class="py-3 px-4 text-left text-gray-700 font-medium">{{ $item->nosample }}</td>
                                <td class="py-3 px-4 text-left text-gray-700">{{ $item->plot }}</td>
                                <td class="py-3 px-4 text-left text-gray-700">{{ $item->varietas }}</td>
                                <td class="py-3 px-4 text-left text-gray-700">{{ $item->kat }}</td>
                                <td class="py-3 px-4 text-left text-gray-700">{{ $item->tanggaltanam_fmt }}</td>
                                <td class="py-3 px-4 text-left text-gray-700">{{ $item->tanggalpengamatan_fmt }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $item->status === 'Posted' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button
                                            onclick="showList('{{ $item->nosample }}', '{{ $item->companycode }}', '{{ $item->tanggalpengamatan }}')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-all duration-200"
                                            title="Lihat Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Detail
                                        </button>
                                        @can('transaction.agronomi.edit')
                                            @if ($item->status === 'Unposted')
                                                <a href="{{ route('transaction.agronomi.edit', ['nosample' => $item->nosample, 'companycode' => $item->companycode, 'tanggalpengamatan' => $item->tanggalpengamatan]) }}"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-all duration-200"
                                                    title="Edit">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </a>
                                            @endif
                                        @endcan
                                        @can('transaction.agronomi.delete')
                                            @if ($item->status === 'Unposted')
                                                <form
                                                    action="{{ route('transaction.agronomi.destroy', ['nosample' => $item->nosample, 'companycode' => $item->companycode, 'tanggalpengamatan' => $item->tanggalpengamatan]) }}"
                                                    method="POST" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        onclick="return confirm('Yakin ingin menghapus data ini?')"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg border border-red-200 transition-all duration-200"
                                                        title="Hapus">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
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
                                    <p class="text-gray-500 text-sm">Belum ada kegiatan pengamatan Agronomi yang sesuai
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
            @if ($agronomi->hasPages())
                {{ $agronomi->appends(['perPage' => $agronomi->perPage(), 'start_date' => $startDate, 'end_date' => $endDate])->links() }}
            @else
                <div class="flex items-center justify-between bg-gray-50 px-4 py-3 rounded-lg">
                    <p class="text-sm text-gray-600">
                        Menampilkan <span class="font-semibold text-gray-800">{{ $agronomi->count() }}</span> dari
                        <span class="font-semibold text-gray-800">{{ $agronomi->total() }}</span> hasil
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Detail -->
    <div id="listModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 ease-out invisible opacity-0"
        style="opacity: 0;">
        <div
            class="bg-white w-11/12 max-h-[90vh] rounded-xl shadow-2xl transition-transform duration-300 ease-out transform scale-95 flex flex-col">
            <!-- Modal Header -->
            <div
                class="flex items-center justify-between p-6 border-b bg-gradient-to-r from-blue-50 to-indigo-50 rounded-t-2xl flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Detail Data Agronomi</h2>
                </div>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg transition-all duration-200">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="overflow-auto p-6 flex-1" id="modalBody">

                <!-- Loading Indicator (ditampilkan saat fetch belum selesai) -->
                <div id="loadingIndicator" class="flex flex-col items-center justify-center py-20 gap-4">
                    <svg class="animate-spin h-12 w-12 text-blue-600" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <p class="text-gray-500 text-sm font-medium">Memuat data, mohon tunggu...</p>
                    <!-- Skeleton rows -->
                    <div class="w-full max-w-2xl space-y-3 mt-2">
                        <div class="h-4 bg-gray-200 rounded animate-pulse w-full"></div>
                        <div class="h-4 bg-gray-200 rounded animate-pulse w-4/6"></div>
                        <div class="h-4 bg-gray-200 rounded animate-pulse w-5/6"></div>
                    </div>
                </div>

                <!-- Table (tersembunyi sampai data selesai) -->
                <div id="tableWrapper" class="hidden overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full bg-white text-sm">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <!-- Single-row headers (rowspan=2) -->
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    No.</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    No. Sample</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    Kebun</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    Blok</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    Plot</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    Luas</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    Varietas</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    Kategori</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    Tanggal Tanam</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    Umur Tanam</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    Jarak Tanam</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    Tanggal Pengamatan</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    Bulan Pengamatan</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    Bulan Panen</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    Umur Panen</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    Tanggal ZPK</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    ni</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    Jumlah Batang</th>
                                <!-- Group headers -->
                                <th colspan="4"
                                    class="py-2 px-4 border-b border-gray-200 font-semibold text-gray-700 text-center bg-blue-50">
                                    Jumlah Batang</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    Panjang GAP</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    %GAP</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    %Germinasi</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    pH Tanah</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    Populasi</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    Kotak Gulma</th>
                                <th rowspan="2"
                                    class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left align-middle">
                                    %Penutupan Gulma</th>
                                <th colspan="4"
                                    class="py-2 px-4 border-b border-gray-200 font-semibold text-gray-700 text-center bg-green-50">
                                    Tinggi Batang</th>
                                <th colspan="4"
                                    class="py-2 px-4 border-b border-gray-200 font-semibold text-gray-700 text-center bg-yellow-50">
                                    Diameter Batang</th>
                                <th colspan="4"
                                    class="py-2 px-4 border-b border-gray-200 font-semibold text-gray-700 text-center bg-purple-50">
                                    Berat Batang</th>
                                <th colspan="4"
                                    class="py-2 px-4 border-b border-gray-200 font-semibold text-gray-700 text-center bg-red-50">
                                    Brix Batang</th>
                            </tr>
                            <tr>
                                <!-- Sub-headers: Jumlah Batang -->
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-blue-50">
                                    Primer</th>
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-blue-50">
                                    Sekunder</th>
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-blue-50">
                                    Tersier</th>
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-blue-50">
                                    Kuarter</th>
                                <!-- Sub-headers: Tinggi Batang -->
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-green-50">
                                    Primer</th>
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-green-50">
                                    Sekunder</th>
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-green-50">
                                    Tersier</th>
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-green-50">
                                    Kuarter</th>
                                <!-- Sub-headers: Diameter Batang -->
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-yellow-50">
                                    Primer</th>
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-yellow-50">
                                    Sekunder</th>
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-yellow-50">
                                    Tersier</th>
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-yellow-50">
                                    Kuarter</th>
                                <!-- Sub-headers: Berat Batang -->
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-purple-50">
                                    Primer</th>
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-purple-50">
                                    Sekunder</th>
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-purple-50">
                                    Tersier</th>
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-purple-50">
                                    Kuarter</th>
                                <!-- Sub-headers: Brix Batang -->
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-red-50">
                                    Primer</th>
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-red-50">
                                    Sekunder</th>
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-red-50">
                                    Tersier</th>
                                <th
                                    class="py-2 px-4 border-b border-gray-200 font-medium text-gray-600 text-left bg-red-50">
                                    Kuarter</th>
                            </tr>
                        </thead>
                        <tbody id="listTableBody" class="divide-y divide-gray-200">
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

        #listModal.visible {
            opacity: 1 !important;
        }

        #listModal {
            will-change: opacity;
        }

        #listModal .bg-white {
            will-change: transform;
        }

        .overflow-auto::-webkit-scrollbar,
        .overflow-x-auto::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .overflow-auto::-webkit-scrollbar-track,
        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .overflow-auto::-webkit-scrollbar-thumb,
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .overflow-auto::-webkit-scrollbar-thumb:hover,
        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>

    <script>
        /* ── Dropdown ── */
        function fmtDate(d) {
            if (!d) return '';
            const [y, m, day] = d.split('-');
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return `${day}-${months[+m - 1]}-${y}`;
        }

        function toggleDropdown() {
            document.getElementById('menu-dropdown').classList.toggle('hidden');
        }
        document.addEventListener('click', function(e) {
            const dd = document.getElementById('menu-dropdown');
            const btn = document.getElementById('menu-button');
            if (!dd.contains(e.target) && !btn.contains(e.target)) dd.classList.add('hidden');
        });

        /* ── Modal helpers ── */
        function openModal() {
            const modal = document.getElementById('listModal');
            modal.classList.remove('invisible');
            modal.classList.add('visible');
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    modal.style.opacity = '1';
                    modal.querySelector('.bg-white').style.transform = 'scale(1)';
                });
            });
        }

        function closeModal() {
            const modal = document.getElementById('listModal');
            modal.style.opacity = '0';
            modal.querySelector('.bg-white').style.transform = 'scale(0.95)';
            setTimeout(() => {
                modal.classList.remove('visible');
                modal.classList.add('invisible');
                // Reset state saat modal ditutup
                document.getElementById('loadingIndicator').classList.remove('hidden');
                document.getElementById('tableWrapper').classList.add('hidden');
                document.getElementById('listTableBody').innerHTML = '';
            }, 300);
        }

        /* ── Show Detail ── */
        function showList(nosample, companycode, tanggalpengamatan) {
            // 1. Reset ke kondisi loading
            document.getElementById('loadingIndicator').classList.remove('hidden');
            document.getElementById('tableWrapper').classList.add('hidden');
            document.getElementById('listTableBody').innerHTML = '';

            // 2. Buka modal langsung — user langsung melihat loading indicator
            openModal();

            const url =
                `{{ route('transaction.agronomi.show', ['nosample' => '__nosample__', 'companycode' => '__companycode__', 'tanggalpengamatan' => '__tanggalpengamatan__']) }}`
                .replace('__nosample__', nosample)
                .replace('__companycode__', companycode)
                .replace('__tanggalpengamatan__', tanggalpengamatan);

            // 3. Fetch di background
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById('listTableBody');
                    data.forEach(item => {
                        const month = new Date(item.tanggalpengamatan).toLocaleString('en-US', {
                            month: 'long'
                        });
                        tbody.innerHTML += `
                            <tr class="hover:bg-blue-50 transition-colors">
                                <td class="py-3 px-4 text-gray-700">${item.no}.</td>
                                <td class="py-3 px-4 text-gray-700">${item.nosample}</td>
                                <td class="py-3 px-4 text-gray-700">${item.compName}</td>
                                <td class="py-3 px-4 text-gray-700">${item.blokName}</td>
                                <td class="py-3 px-4 text-gray-700">${item.plotName}</td>
                                <td class="py-3 px-4 text-gray-700">${item.luasarea}</td>
                                <td class="py-3 px-4 text-gray-700">${item.varietas}</td>
                                <td class="py-3 px-4 text-gray-700">${item.kat}</td>
                                <td class="py-3 px-4 text-gray-700">${item.tanggaltanam_fmt}</td>
                                <td class="py-3 px-4 text-gray-700">${item.umur_tanam} Bulan</td>
                                <td class="py-3 px-4 text-gray-700">${item.jaraktanam}</td>
                                <td class="py-3 px-4 text-gray-700">${item.tanggalpengamatan_fmt}</td>
                                <td class="py-3 px-4 text-gray-700">${month}</td>
                                <td class="py-3 px-4 text-gray-700">${item.bulanpanen ?? '-'}</td>
                                <td class="py-3 px-4 text-gray-700">${item.umurpanen ?? '-'}</td>
                                <td class="py-3 px-4 text-gray-700">${item.tanggalzpk_fmt}</td>
                                <td class="py-3 px-4 text-gray-700">${item.nourut}</td>
                                <td class="py-3 px-4 text-gray-700">${item.jumlahbatang}</td>
                                <td class="py-3 px-4 text-gray-700">${item.bat_primer ?? '-'}</td>
                                <td class="py-3 px-4 text-gray-700">${item.bat_sekunder ?? '-'}</td>
                                <td class="py-3 px-4 text-gray-700">${item.bat_tersier ?? '-'}</td>
                                <td class="py-3 px-4 text-gray-700">${item.bat_kuarter ?? '-'}</td>
                                <td class="py-3 px-4 text-gray-700">${item.pan_gap}</td>
                                <td class="py-3 px-4 text-gray-700">${(item.per_gap * 100).toFixed(2)}%</td>
                                <td class="py-3 px-4 text-gray-700">${(item.per_germinasi * 100).toFixed(2)}%</td>
                                <td class="py-3 px-4 text-gray-700">${item.ph_tanah}</td>
                                <td class="py-3 px-4 text-gray-700">${item.populasi}</td>
                                <td class="py-3 px-4 text-gray-700">${item.ktk_gulma}</td>
                                <td class="py-3 px-4 text-gray-700">${(item.per_gulma * 100).toFixed(2)}%</td>
                                <td class="py-3 px-4 text-gray-700">${item.t_primer}</td>
                                <td class="py-3 px-4 text-gray-700">${item.t_sekunder}</td>
                                <td class="py-3 px-4 text-gray-700">${item.t_tersier}</td>
                                <td class="py-3 px-4 text-gray-700">${item.t_kuarter}</td>
                                <td class="py-3 px-4 text-gray-700">${item.d_primer}</td>
                                <td class="py-3 px-4 text-gray-700">${item.d_sekunder}</td>
                                <td class="py-3 px-4 text-gray-700">${item.d_tersier}</td>
                                <td class="py-3 px-4 text-gray-700">${item.d_kuarter}</td>
                                <td class="py-3 px-4 text-gray-700">${item.berat_primer ?? '-'}</td>
                                <td class="py-3 px-4 text-gray-700">${item.berat_sekunder ?? '-'}</td>
                                <td class="py-3 px-4 text-gray-700">${item.berat_tersier ?? '-'}</td>
                                <td class="py-3 px-4 text-gray-700">${item.berat_kuarter ?? '-'}</td>
                                <td class="py-3 px-4 text-gray-700">${item.brix_primer ?? '-'}</td>
                                <td class="py-3 px-4 text-gray-700">${item.brix_sekunder ?? '-'}</td>
                                <td class="py-3 px-4 text-gray-700">${item.brix_tersier ?? '-'}</td>
                                <td class="py-3 px-4 text-gray-700">${item.brix_kuarter ?? '-'}</td>
                            </tr>
                        `;
                    });

                    // 4. Sembunyikan loading, tampilkan table
                    document.getElementById('loadingIndicator').classList.add('hidden');
                    document.getElementById('tableWrapper').classList.remove('hidden');
                })
                .catch(err => {
                    console.error('Error:', err);
                    document.getElementById('loadingIndicator').innerHTML = `
                        <div class="flex flex-col items-center justify-center py-20 gap-3">
                            <svg class="w-12 h-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                            <p class="text-red-600 font-semibold">Gagal memuat data.</p>
                            <p class="text-gray-400 text-sm">Silakan tutup dan coba lagi.</p>
                        </div>
                    `;
                });
        }
    </script>
</x-layout>
