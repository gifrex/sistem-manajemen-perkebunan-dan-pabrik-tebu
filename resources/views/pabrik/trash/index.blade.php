<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <!-- Success Alert -->
    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Berhasil!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-green-200 rounded"
            @click="show = false">&times;</span>
    </div>
    @endif

    <!-- Error Alert -->
    @if (session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Gagal!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-red-200 rounded"
            @click="show = false">&times;</span>
    </div>
    @endif

    <div x-data="trashManagement()" class="mx-auto py-4 bg-white rounded-md shadow-md">

        <!-- Header Section with Controls -->
        <div class="px-4 py-4 border-b border-gray-200">
            <div class="flex flex-col space-y-4 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">

                <!-- New Data Button -->
                <div class="flex justify-start">
                    @can('pabrik.trash.create')
                    <button @click="openModal('create')"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2 transition-colors duration-200">
                        <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5" />
                        </svg>
                        <span class="hidden sm:inline">Tambah Data Trash</span>
                        <span class="sm:hidden">Tambah</span>
                    </button>
                    @endcan
                </div>

                <!-- Search and Per Page Controls -->
                <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                    <!-- Search Form -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="search" class="text-xs font-medium text-gray-700 whitespace-nowrap">Cari:</label>
                        <input type="text" name="search" id="search"
                            value="{{ request('search') }}"
                            placeholder="Cari nomor surat jalan..."
                            class="text-xs w-full sm:w-48 md:w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                            onkeydown="if(event.key==='Enter') this.form.submit()" />
                        @if(request('search'))
                        <a href="{{ route('pabrik.trash.index') }}"
                            class="text-gray-500 hover:text-gray-700 px-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </a>
                        @endif
                        @if(request('perPage'))
                        <input type="hidden" name="perPage" value="{{ request('perPage') }}">
                        @endif
                    </form>

                    <!-- Per Page Form -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="perPage" class="text-xs font-medium text-gray-700 whitespace-nowrap">Per halaman:</label>
                        <select name="perPage" id="perPage"
                            onchange="this.form.submit()"
                            class="text-xs w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                            <option value="10" {{ (int)request('perPage', 10) === 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ (int)request('perPage', 10) === 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ (int)request('perPage', 10) === 50 ? 'selected' : '' }}>50</option>
                        </select>
                        @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="px-4 py-4">
            <div class="overflow-x-auto rounded-md border border-gray-300">
                <table class="min-w-full bg-white text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Surat Jalan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Toleransi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pucuk</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Daun Gulma</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sogolan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Siwilan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tebu Mati</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanah Dll</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Trash</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Netto Trash</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created Date</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        {{-- Loop data trash --}}
                        @forelse($data ?? [] as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $data->firstItem() + $index }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $item->suratjalanno ?? 'N/A' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->jenis === 'manual' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($item->jenis ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($item->toleransi ?? 0, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($item->pucuk ?? 0, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($item->daungulma ?? 0, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($item->sogolan ?? 0, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($item->siwilan ?? 0, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($item->tebumati ?? 0, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($item->tanahetc ?? 0, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($item->total ?? 0, 3, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($item->nettotrash ?? 0, 3, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-900">{{ $item->createdby ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-900">{{ $item->createddate ? date('d/m/Y H:i', strtotime($item->createddate)) : '-' }}</td>
                            <td class="py-3 px-3">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Edit Button -->
                                     @can('pabrik.trash.edit')
                                    <button @click="openModal('edit', {
                                            suratjalanno: '{{ $item->suratjalanno }}',
                                            companycode: '{{ $item->companycode }}',
                                            jenis: '{{ $item->jenis }}',
                                            toleransi: '{{ $item->toleransi }}',
                                            berat_bersih: '{{ $item->berat_bersih }}',
                                            pucuk: '{{ $item->pucuk }}',
                                            daun_gulma: '{{ $item->daungulma }}',
                                            sogolan: '{{ $item->sogolan }}',
                                            siwilan: '{{ $item->siwilan }}',
                                            tebumati: '{{ $item->tebumati }}',
                                            tanah_etc: '{{ $item->tanahetc }}'
                                        })"
                                        class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md p-1 transition-all duration-150"
                                        title="Edit Data Trash">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    @endcan

                                    <!-- Delete Button -->
                                     @can('pabrik.trash.delete')
                                    <button @click="deleteItem({
                                            suratjalanno: '{{ $item->suratjalanno }}',
                                            companycode: '{{ $item->companycode }}',
                                            jenis: '{{ $item->jenis }}'
                                        })"
                                        class="text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md p-1 transition-all duration-150"
                                        title="Hapus Data Trash">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>  
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="15" class="px-4 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Tidak ada data trash yang ditemukan</p>
                                    <p class="text-sm">{{ request('search') ? 'Tidak ada hasil untuk pencarian "'.request('search').'"' : 'Belum ada data trash yang diinput' }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>


            </div>

            <!-- Report Section - Tambahkan setelah pagination -->
            <div class="mt-6 pt-4">
                <div class="flex justify-between items-center">
                    <button @click="openReportModal()"
                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 flex items-center gap-2 transition-colors duration-200">
                        <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="hidden sm:inline">Generate Report</span>
                        <span class="sm:hidden">Report</span>
                    </button>
                </div>
            </div>

            <!-- Pagination -->
            @if(isset($data) && $data->hasPages())
            <div class="mt-6">
                {{ $data->appends(request()->query())->links() }}
            </div>
            @else
            <div class="mt-4 flex items-center justify-between text-sm text-gray-700">
                <p>Menampilkan <span class="font-medium">{{ $data->count() }}</span> dari <span class="font-medium">{{ $data->total() }}</span> data</p>
            </div>
            @endif
        </div>

        <!-- Modal Report - Updated Version with Monthly Option -->
        <div x-show="showReportModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeReportModal()"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <form action="{{ route('pabrik.trash.report') }}" method="POST" target="_blank">
                        @csrf

                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="w-full">
                                    <!-- Header -->
                                    <div class="flex items-center mb-4">
                                        <h3 class="ml-3 text-lg leading-6 font-medium text-gray-900">Generate Report Trash</h3>
                                    </div>

                                    <!-- Form Fields -->
                                    <div class="space-y-4">

                                        <!-- Report Type -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Tipe Report <span class="text-red-500">*</span>
                                            </label>
                                            <select name="report_type" x-model="reportForm.report_type"
                                                @change="updateCompanyOptions()"
                                                class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 px-3 py-2" required>
                                                <option value="">Pilih Tipe Report</option>
                                                <option value="harian">Laporan Harian</option>
                                                <option value="mingguan">Laporan Mingguan</option>
                                                <option value="bulanan">Laporan Bulanan</option>
                                            </select>
                                        </div>

                                        <!-- Date Range - Show for Harian & Mingguan -->
                                        <div x-show="reportForm.report_type === 'harian' || reportForm.report_type === 'mingguan'"
                                            x-transition
                                            class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    Tanggal Mulai <span class="text-red-500">*</span>
                                                </label>
                                                <input type="date" name="start_date" x-model="reportForm.start_date"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 px-3 py-2"
                                                    :required="reportForm.report_type === 'harian' || reportForm.report_type === 'mingguan'">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    Tanggal Selesai <span class="text-red-500">*</span>
                                                </label>
                                                <input type="date" name="end_date" x-model="reportForm.end_date"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 px-3 py-2"
                                                    :required="reportForm.report_type === 'harian' || reportForm.report_type === 'mingguan'">
                                            </div>
                                        </div>

                                        <!-- Month & Year - Show for Bulanan -->
                                        <div x-show="reportForm.report_type === 'bulanan'"
                                            x-transition
                                            class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    Bulan <span class="text-red-500">*</span>
                                                </label>
                                                <select name="month" x-model="reportForm.month"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 px-3 py-2"
                                                    :required="reportForm.report_type === 'bulanan'">
                                                    <option value="">Pilih Bulan</option>
                                                    <template x-for="month in getMonthOptions()" :key="month.value">
                                                        <option :value="month.value" x-text="month.label"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    Tahun <span class="text-red-500">*</span>
                                                </label>
                                                <select name="year" x-model="reportForm.year"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 px-3 py-2"
                                                    :required="reportForm.report_type === 'bulanan'">
                                                    <option value="">Pilih Tahun</option>
                                                    <template x-for="year in getYearOptions()" :key="year">
                                                        <option :value="year" x-text="year"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Company Selection - Updated untuk lebih spesifik -->
                                        <div x-show="reportForm.report_type === 'mingguan'" x-transition>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Company <span class="text-red-500">*</span>
                                            </label>
                                            <select name="company" x-model="reportForm.company"
                                                class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 px-3 py-2"
                                                :required="reportForm.report_type === 'mingguan'">
                                                <!-- Group Options -->
                                                <optgroup label="Group Company">
                                                    <option value="TBL">TBL</option>
                                                    <option value="BNIL">BNIL</option>
                                                    <option value="SIL">SILVA</option>
                                                </optgroup>

                                                <!-- Individual Company Options -->
                                                <!-- <optgroup label="Company Spesifik">
                                                    <option value="TBL1">TBL1 saja</option>
                                                    <option value="TBL2">TBL2 saja</option>
                                                    <option value="TBL3">TBL3 saja</option>
                                                    <option value="BNL1">BNL1 saja</option>
                                                    <option value="BNL2">BNL2 saja</option>
                                                    <option value="BNL3">BNL3 saja</option>
                                                    <option value="BNL4">BNL4 saja</option>
                                                    <option value="SIL1">SIL1 saja</option>
                                                    <option value="SIL2">SIL2 saja</option>
                                                    <option value="SIL3">SIL3 saja</option>
                                                </optgroup> -->
                                            </select>

                                            <div class="mt-1 text-xs text-gray-500">
                                                Pilih group untuk laporan gabungan atau company spesifik untuk laporan individual
                                            </div>
                                        </div>

                                        <!-- Info text untuk harian dan bulanan -->
                                        <div x-show="reportForm.report_type === 'harian'" class="text-xs text-gray-500 bg-blue-50 p-2 rounded">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Laporan harian akan menampilkan data dari semua company
                                        </div>
                                        <div x-show="reportForm.report_type === 'bulanan'" class="text-xs text-gray-500 bg-blue-50 p-2 rounded">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Laporan bulanan akan menampilkan data dari semua company
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Generate Report
                            </button>
                            <button type="button" @click="closeReportModal()"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Form dengan Tab System -->
        <div x-show="showModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal()"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <form :action="formAction" method="POST">
                        @csrf
                        <div x-show="mode === 'edit'">
                            @method('POST')
                        </div>

                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" x-text="modalTitle"></h3>

                                    <!-- Tab Navigation - Hanya tampil di mode create -->
                                    <div x-show="mode === 'create'" class="border-b border-gray-200 mb-6">
                                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                            <button type="button"
                                                @click="activeTab = 'suratjalan'"
                                                :class="activeTab === 'suratjalan' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                Cari by Surat Jalan
                                            </button>
                                            <button type="button"
                                                @click="activeTab = 'tanggal'"
                                                :class="activeTab === 'tanggal' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                Cari by Tanggal
                                            </button>
                                            <button type="button"
                                                @click="activeTab = 'nopol'"
                                                :class="activeTab === 'nopol' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM3 9l1-4h16l1 4M3 9h18M3 9l1 5h14l1-5"></path>
                                                </svg>
                                                Cari by Nopol
                                            </button>
                                        </nav>
                                    </div>

                                    <!-- Tab 1: Cari by Surat Jalan (Form yang sudah ada) -->
                                    <div x-show="(mode === 'create' && activeTab === 'suratjalan') || mode === 'edit'"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 transform translate-x-4"
                                        x-transition:enter-end="opacity-100 transform translate-x-0">

                                        <!-- Step 1: Company Code dan Nomor Surat Jalan -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Company Code</label>
                                                <input type="text" name="companycode" x-model="form.companycode"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                                    placeholder="Masukkan company code" required>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Surat Jalan</label>
                                                <input type="text" name="no_surat_jalan" x-model="form.no_surat_jalan"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                                    placeholder="Masukkan nomor surat jalan" required>
                                            </div>
                                        </div>

                                        <!-- Tombol Cari - hanya untuk create mode -->
                                        <div x-show="mode === 'create'" class="mb-4">
                                            <button type="button" @click="searchSuratJalan()"
                                                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                                Cari Surat Jalan
                                            </button>
                                            <div x-show="searchMessage" class="mt-2 text-sm" :class="searchSuccess ? 'text-green-600' : 'text-red-600'" x-text="searchMessage"></div>
                                        </div>

                                        <!-- Step 2: Data Entry Fields -->
                                        <div x-show="suratJalanFound || mode === 'edit'" x-transition class="space-y-4">

                                            <!-- Detail Surat Jalan - hanya untuk mode create -->
                                            <div x-show="mode === 'create'" class="bg-gray-50 border border-gray-200 rounded-md p-3">
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Detail Surat Jalan</p>
                                                <div class="grid grid-cols-3 gap-3">
                                                    <div>
                                                        <span class="text-xs text-gray-500">Plot</span>
                                                        <p class="text-sm font-medium text-gray-800" x-text="suratJalanInfo.plot"></p>
                                                    </div>
                                                    <div>
                                                        <span class="text-xs text-gray-500">Nama Subkontraktor</span>
                                                        <p class="text-sm font-medium text-gray-800" x-text="suratJalanInfo.namasubkontraktor"></p>
                                                    </div>
                                                    <div>
                                                        <span class="text-xs text-gray-500">Nomor Plat</span>
                                                        <p class="text-sm font-medium text-gray-800" x-text="suratJalanInfo.nomorpolisi"></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Row 1: Jenis -->
                                            <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
                                                    <select name="jenis" x-model="form.jenis"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" required>
                                                        <option value="">Pilih Jenis</option>
                                                        <option value="manual">Manual</option>
                                                        <option value="mesin">Mesin</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Row 2: Berat Bersih, Pucuk, Daun -->
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Berat Bersih</label>
                                                    <input type="text" name="berat_bersih" x-model="form.berat_bersih"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('berat_bersih')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" required>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Pucuk</label>
                                                    <input type="text" name="pucuk" x-model="form.pucuk"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('pucuk')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Daun Gulma</label>
                                                    <input type="text" name="daun_gulma" x-model="form.daun_gulma"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('daun_gulma')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                                </div>
                                            </div>

                                            <!-- Row 3: Sogolan, Siwilan, Tebu Mati -->
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sogolan</label>
                                                    <input type="text" name="sogolan" x-model="form.sogolan"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('sogolan')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Siwilan</label>
                                                    <input type="text" name="siwilan" x-model="form.siwilan"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('siwilan')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tebu Mati</label>
                                                    <input type="text" name="tebumati" x-model="form.tebumati"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('tebumati')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                                </div>
                                            </div>

                                            <!-- Row 4: Tanah dan Lain, Berat Kotor, Toleransi -->
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanah dll</label>
                                                    <input type="text" name="tanah_etc" x-model="form.tanah_etc"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('tanah_etc')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Berat Kotor
                                                        <span class="text-xs text-gray-500">(Auto Calculate)</span>
                                                    </label>
                                                    <input type="text" name="berat_kotor" x-model="form.berat_kotor" readonly
                                                        class="w-full border border-gray-200 rounded-md shadow-sm bg-gray-50 px-3 py-2 text-gray-700 cursor-not-allowed"
                                                        placeholder="Auto calculated">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Toleransi (%)</label>
                                                    <input type="text" name="toleransi" x-model="form.toleransi"
                                                        @blur="formatInput('toleransi')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                                        placeholder="Default: 5,000" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tab 2: Cari by Tanggal -->
                                    <div x-show="mode === 'create' && activeTab === 'tanggal'"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 transform translate-x-4"
                                        x-transition:enter-end="opacity-100 transform translate-x-0">

                                        <!-- Step 1: Company dan Tanggal Search -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                                                <select x-model="searchForm.company"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 px-3 py-2"
                                                    :required="mode === 'create' && activeTab === 'tanggal'">
                                                    <option value="">Pilih Company</option>
                                                    @foreach ($companies as $c)
                                                    <option value="{{ $c->companycode }}">{{ $c->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                                                <input type="date" x-model="searchForm.date"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 px-3 py-2"
                                                    :required="mode === 'create' && activeTab === 'tanggal'">
                                            </div>
                                        </div>

                                        <!-- Tombol Cari -->
                                        <div class="mb-4">
                                            <button type="button" @click="searchByDate()"
                                                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                                Cari Surat Jalan
                                            </button>
                                            <div x-show="dateSearchMessage" class="mt-2 text-sm" :class="dateSearchSuccess ? 'text-green-600' : 'text-red-600'" x-text="dateSearchMessage"></div>
                                        </div>

                                        <!-- Step 2: List Surat Jalan -->
                                        <div x-show="dateSearchSuccess && suratJalanList.length > 0" x-transition class="mb-6">
                                            <h4 class="text-md font-medium text-gray-900 mb-3">Pilih Surat Jalan:</h4>
                                            <div class="bg-gray-50 rounded-lg p-4 max-h-64 overflow-y-auto">
                                                <template x-for="sj in suratJalanList" :key="sj.suratjalanno">
                                                    <div class="flex items-center justify-between p-3 bg-white rounded-md shadow-sm mb-2 border border-gray-200">
                                                        <div class="flex-grow">
                                                            <div class="flex items-center space-x-4">
                                                                <div>
                                                                    <span class="font-medium text-gray-900" x-text="sj.suratjalanno"></span>
                                                                    <span class="text-sm text-gray-500 ml-2" x-text="`(${sj.companycode})`"></span>
                                                                </div>
                                                                <div class="text-sm text-gray-600">
                                                                    <span>Plot: </span><span class="font-medium" x-text="sj.plot || '-'"></span>
                                                                </div>
                                                                <div class="text-sm text-gray-600">
                                                                    <span>Varietas: </span><span class="font-medium" x-text="sj.varietas || '-'"></span>
                                                                </div>
                                                                <div class="text-sm text-gray-600">
                                                                    <span>Kategori: </span><span class="font-medium" x-text="sj.kategori || '-'"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button @click="selectSuratJalan(sj)"
                                                            class="bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-1 transition-colors duration-200">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                            </svg>
                                                            <span class="text-sm">Tambah</span>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Step 3: Data Entry Fields (sama seperti tab surat jalan) -->
                                        <div x-show="selectedSuratJalan !== null" x-transition class="space-y-4">
                                            <!-- Alert info surat jalan terpilih -->
                                            <div class="bg-blue-50 border border-blue-200 rounded-md p-3 mb-4">
                                                <div class="flex items-center">
                                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span class="text-sm font-medium text-blue-800">
                                                        Surat Jalan Terpilih: <span x-text="selectedSuratJalan ? selectedSuratJalan.suratjalanno : ''"></span>
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Detail Surat Jalan -->
                                            <div class="bg-gray-50 border border-gray-200 rounded-md p-3">
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Detail Surat Jalan</p>
                                                <div class="grid grid-cols-3 gap-3">
                                                    <div>
                                                        <span class="text-xs text-gray-500">Plot</span>
                                                        <p class="text-sm font-medium text-gray-800" x-text="selectedSuratJalan ? (selectedSuratJalan.plot || '-') : '-'"></p>
                                                    </div>
                                                    <div>
                                                        <span class="text-xs text-gray-500">Nama Subkontraktor</span>
                                                        <p class="text-sm font-medium text-gray-800" x-text="selectedSuratJalan ? (selectedSuratJalan.namasubkontraktor || '-') : '-'"></p>
                                                    </div>
                                                    <div>
                                                        <span class="text-xs text-gray-500">Nomor Plat</span>
                                                        <p class="text-sm font-medium text-gray-800" x-text="selectedSuratJalan ? (selectedSuratJalan.nomorpolisi || '-') : '-'"></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Row 1: Jenis -->
                                            <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
                                                    <select name="jenis" x-model="form.jenis"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" required>
                                                        <option value="">Pilih Jenis</option>
                                                        <option value="manual">Manual</option>
                                                        <option value="mesin">Mesin</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Row 2: Berat Bersih, Pucuk, Daun -->
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Berat Bersih</label>
                                                    <input type="text" name="berat_bersih" x-model="form.berat_bersih"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('berat_bersih')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" required>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Pucuk</label>
                                                    <input type="text" name="pucuk" x-model="form.pucuk"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('pucuk')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Daun Gulma</label>
                                                    <input type="text" name="daun_gulma" x-model="form.daun_gulma"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('daun_gulma')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                                </div>
                                            </div>

                                            <!-- Row 3: Sogolan, Siwilan, Tebu Mati -->
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sogolan</label>
                                                    <input type="text" name="sogolan" x-model="form.sogolan"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('sogolan')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Siwilan</label>
                                                    <input type="text" name="siwilan" x-model="form.siwilan"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('siwilan')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tebu Mati</label>
                                                    <input type="text" name="tebumati" x-model="form.tebumati"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('tebumati')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                                </div>
                                            </div>

                                            <!-- Row 4: Tanah dan Lain, Berat Kotor, Toleransi -->
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanah dll</label>
                                                    <input type="text" name="tanah_etc" x-model="form.tanah_etc"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('tanah_etc')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Berat Kotor
                                                        <span class="text-xs text-gray-500">(Auto Calculate)</span>
                                                    </label>
                                                    <input type="text" name="berat_kotor" x-model="form.berat_kotor" readonly
                                                        class="w-full border border-gray-200 rounded-md shadow-sm bg-gray-50 px-3 py-2 text-gray-700 cursor-not-allowed"
                                                        placeholder="Auto calculated">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Toleransi (%)</label>
                                                    <input type="text" name="toleransi" x-model="form.toleransi"
                                                        @blur="formatInput('toleransi')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                                        placeholder="Default: 5,000" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tab 3: Cari by Nopol -->
                                    <div x-show="mode === 'create' && activeTab === 'nopol'"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 transform translate-x-4"
                                        x-transition:enter-end="opacity-100 transform translate-x-0">

                                        <!-- Step 1: Company dan Nomor Polisi -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Company Code</label>
                                                <input type="text" x-model="nopolForm.company"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 px-3 py-2"
                                                    placeholder="Masukkan company code">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Polisi</label>
                                                <input type="text" x-model="nopolForm.nopol"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 px-3 py-2"
                                                    placeholder="Contoh: B 1234 AB"
                                                    @keydown.enter.prevent="searchByNopol()">
                                            </div>
                                        </div>

                                        <!-- Tombol Cari -->
                                        <div class="mb-4">
                                            <button type="button" @click="searchByNopol()"
                                                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                                Cari Surat Jalan
                                            </button>
                                            <div x-show="nopolSearchMessage" class="mt-2 text-sm" :class="nopolSearchSuccess ? 'text-green-600' : 'text-red-600'" x-text="nopolSearchMessage"></div>
                                        </div>

                                        <!-- Step 2: List Surat Jalan -->
                                        <div x-show="nopolSearchSuccess && nopolSuratJalanList.length > 0" x-transition class="mb-6">
                                            <h4 class="text-md font-medium text-gray-900 mb-3">Pilih Surat Jalan:</h4>
                                            <div class="bg-gray-50 rounded-lg p-4 max-h-64 overflow-y-auto">
                                                <template x-for="sj in nopolSuratJalanList" :key="sj.suratjalanno">
                                                    <div class="flex items-center justify-between p-3 bg-white rounded-md shadow-sm mb-2 border border-gray-200">
                                                        <div class="flex-grow">
                                                            <div class="flex items-center space-x-4">
                                                                <div>
                                                                    <span class="font-medium text-gray-900" x-text="sj.suratjalanno"></span>
                                                                    <span class="text-sm text-gray-500 ml-2" x-text="`(${sj.companycode})`"></span>
                                                                </div>
                                                                <div class="text-sm text-gray-600">
                                                                    <span>Plot: </span><span class="font-medium" x-text="sj.plot || '-'"></span>
                                                                </div>
                                                                <div class="text-sm text-gray-600">
                                                                    <span>Tgl: </span><span class="font-medium" x-text="sj.tanggalangkut ? sj.tanggalangkut.substring(0,10) : '-'"></span>
                                                                </div>
                                                                <div class="text-sm text-gray-600">
                                                                    <span>Kategori: </span><span class="font-medium" x-text="sj.kategori || '-'"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button @click="selectNopolSuratJalan(sj)"
                                                            class="bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-1 transition-colors duration-200">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                            </svg>
                                                            <span class="text-sm">Tambah</span>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Step 3: Data Entry Fields -->
                                        <div x-show="selectedNopolSuratJalan !== null" x-transition class="space-y-4">
                                            <!-- Alert info surat jalan terpilih -->
                                            <div class="bg-blue-50 border border-blue-200 rounded-md p-3 mb-4">
                                                <div class="flex items-center">
                                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span class="text-sm font-medium text-blue-800">
                                                        Surat Jalan Terpilih: <span x-text="selectedNopolSuratJalan ? selectedNopolSuratJalan.suratjalanno : ''"></span>
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Detail Surat Jalan -->
                                            <div class="bg-gray-50 border border-gray-200 rounded-md p-3">
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Detail Surat Jalan</p>
                                                <div class="grid grid-cols-3 gap-3">
                                                    <div>
                                                        <span class="text-xs text-gray-500">Plot</span>
                                                        <p class="text-sm font-medium text-gray-800" x-text="selectedNopolSuratJalan ? (selectedNopolSuratJalan.plot || '-') : '-'"></p>
                                                    </div>
                                                    <div>
                                                        <span class="text-xs text-gray-500">Nama Subkontraktor</span>
                                                        <p class="text-sm font-medium text-gray-800" x-text="selectedNopolSuratJalan ? (selectedNopolSuratJalan.namasubkontraktor || '-') : '-'"></p>
                                                    </div>
                                                    <div>
                                                        <span class="text-xs text-gray-500">Nomor Plat</span>
                                                        <p class="text-sm font-medium text-gray-800" x-text="selectedNopolSuratJalan ? (selectedNopolSuratJalan.nomorpolisi || '-') : '-'"></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Row 1: Jenis -->
                                            <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
                                                    <select name="jenis" x-model="form.jenis"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" required>
                                                        <option value="">Pilih Jenis</option>
                                                        <option value="manual">Manual</option>
                                                        <option value="mesin">Mesin</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Row 2: Berat Bersih, Pucuk, Daun -->
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Berat Bersih</label>
                                                    <input type="text" name="berat_bersih" x-model="form.berat_bersih"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('berat_bersih')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" required>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Pucuk</label>
                                                    <input type="text" name="pucuk" x-model="form.pucuk"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('pucuk')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Daun Gulma</label>
                                                    <input type="text" name="daun_gulma" x-model="form.daun_gulma"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('daun_gulma')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                                </div>
                                            </div>

                                            <!-- Row 3: Sogolan, Siwilan, Tebu Mati -->
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sogolan</label>
                                                    <input type="text" name="sogolan" x-model="form.sogolan"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('sogolan')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Siwilan</label>
                                                    <input type="text" name="siwilan" x-model="form.siwilan"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('siwilan')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tebu Mati</label>
                                                    <input type="text" name="tebumati" x-model="form.tebumati"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('tebumati')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                                </div>
                                            </div>

                                            <!-- Row 4: Tanah dan Lain, Berat Kotor, Toleransi -->
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanah dll</label>
                                                    <input type="text" name="tanah_etc" x-model="form.tanah_etc"
                                                        @input="calculateBeratKotor()"
                                                        @blur="formatInput('tanah_etc')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Berat Kotor
                                                        <span class="text-xs text-gray-500">(Auto Calculate)</span>
                                                    </label>
                                                    <input type="text" name="berat_kotor" x-model="form.berat_kotor" readonly
                                                        class="w-full border border-gray-200 rounded-md shadow-sm bg-gray-50 px-3 py-2 text-gray-700 cursor-not-allowed"
                                                        placeholder="Auto calculated">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Toleransi (%)</label>
                                                    <input type="text" name="toleransi" x-model="form.toleransi"
                                                        @blur="formatInput('toleransi')"
                                                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                                        placeholder="Default: 5,000" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                x-show="mode === 'edit' || suratJalanFound || selectedSuratJalan !== null || selectedNopolSuratJalan !== null"
                                :disabled="mode === 'create' && !suratJalanFound && selectedSuratJalan === null && selectedNopolSuratJalan === null"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors duration-200">
                                <span x-text="mode === 'create' ? 'Simpan' : 'Update'"></span>
                            </button>
                            <button type="button" @click="closeModal()"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function trashManagement() {
            return {
                showModal: false,
                showReportModal: false,
                mode: 'create',
                modalTitle: '',
                formAction: '',
                suratJalanFound: false,
                searchMessage: '',
                searchSuccess: false,
                activeTab: 'suratjalan', // Default tab

                // Form data yang sudah ada
                form: {
                    companycode: '',
                    no_surat_jalan: '',
                    jenis: '',
                    toleransi: '5,000',
                    berat_bersih: '',
                    pucuk: '',
                    daun_gulma: '',
                    sogolan: '',
                    siwilan: '',
                    tebumati: '',
                    tanah_etc: '',
                    berat_kotor: ''
                },

                // Form data untuk search by date
                searchForm: {
                    company: '',
                    date: ''
                },

                // State variables untuk tab tanggal
                suratJalanList: [],
                selectedSuratJalan: null,
                dateSearchMessage: '',
                dateSearchSuccess: false,
                companies: [], // Akan diisi dari server

                // Info surat jalan yang ditemukan (tab surat jalan)
                suratJalanInfo: {
                    plot: '',
                    namasubkontraktor: '',
                    nomorpolisi: ''
                },

                // State variables untuk tab nopol
                nopolForm: { company: '', nopol: '' },
                nopolSuratJalanList: [],
                selectedNopolSuratJalan: null,
                nopolSearchMessage: '',
                nopolSearchSuccess: false,

                // Update reportForm dengan field untuk bulanan
                reportForm: {
                    start_date: '',
                    end_date: '',
                    month: '',
                    year: '',
                    report_type: 'harian',
                    company: '',
                    format: 'pdf'
                },

                // Method init untuk set default dates dan month/year
                init() {
                    const today = new Date();
                    const yesterday = new Date(today);
                    yesterday.setDate(today.getDate() - 1);

                    this.reportForm.end_date = today.toISOString().split('T')[0];
                    this.reportForm.start_date = yesterday.toISOString().split('T')[0];

                    // Set default tanggal untuk search form ke hari ini
                    this.searchForm.date = today.toISOString().split('T')[0];

                    // Set default month dan year ke bulan/tahun sekarang
                    this.reportForm.month = String(today.getMonth() + 1).padStart(2, '0');
                    this.reportForm.year = String(today.getFullYear());
                },

                // Reset form dan tab
                resetForm() {
                    this.form = {
                        companycode: '',
                        no_surat_jalan: '',
                        jenis: '',
                        toleransi: '5,000',
                        berat_bersih: '',
                        pucuk: '',
                        daun_gulma: '',
                        sogolan: '',
                        siwilan: '',
                        tebumati: '',
                        tanah_etc: '',
                        berat_kotor: ''
                    };

                    // Reset search form
                    const today = new Date();
                    this.searchForm = {
                        company: '',
                        date: today.toISOString().split('T')[0]
                    };

                    // Reset state variables
                    this.activeTab = 'suratjalan';
                    this.suratJalanFound = false;
                    this.searchMessage = '';
                    this.searchSuccess = false;
                    this.suratJalanList = [];
                    this.selectedSuratJalan = null;
                    this.dateSearchMessage = '';
                    this.dateSearchSuccess = false;
                    this.suratJalanInfo = { plot: '', namasubkontraktor: '', nomorpolisi: '' };
                    this.nopolForm = { company: '', nopol: '' };
                    this.nopolSuratJalanList = [];
                    this.selectedNopolSuratJalan = null;
                    this.nopolSearchMessage = '';
                    this.nopolSearchSuccess = false;
                },

                openModal(mode, data = null) {
                    this.mode = mode;
                    this.showModal = true;
                    this.suratJalanFound = false;
                    this.searchMessage = '';

                    if (mode === 'create') {
                        this.modalTitle = 'Tambah Data Trash';
                        this.formAction = '{{ route("pabrik.trash.store") }}'; // FIX
                        this.resetForm();
                    } else {
                        this.modalTitle = 'Edit Data Trash';
                        // Untuk dynamic URL, pakai url() helper
                        this.formAction = `{{ url('pabrik/trash/update') }}/${data.suratjalanno}/${data.companycode}/${data.jenis}`; // FIX
                        this.fillForm(data);
                        this.suratJalanFound = true;
                    }
                },

                closeModal() {
                    this.showModal = false;
                    this.resetForm();
                },

                // Method untuk update company options berdasarkan report type
                updateCompanyOptions() {
                    // Reset company selection ketika report type berubah
                    this.reportForm.company = '';

                    // Jika harian atau bulanan, auto set ke 'all'
                    if (this.reportForm.report_type === 'harian' || this.reportForm.report_type === 'bulanan') {
                        this.reportForm.company = 'all';
                    }
                    // Untuk mingguan, biarkan kosong supaya user harus pilih company spesifik
                },

                fillForm(data) {
                    this.form = {
                        companycode: data.companycode || '',
                        no_surat_jalan: data.suratjalanno || '',
                        jenis: data.jenis || '',
                        toleransi: data.toleransi ? this.truncate3(parseFloat(data.toleransi.toString().replace(',', '.'))) : '5,000',
                        berat_bersih: data.berat_bersih || '',
                        pucuk: data.pucuk || '',
                        daun_gulma: data.daun_gulma || '',
                        sogolan: data.sogolan || '',
                        siwilan: data.siwilan || '',
                        tebumati: data.tebumati || '',
                        tanah_etc: data.tanah_etc || '',
                        berat_kotor: data.berat_kotor || ''
                    };
                    this.calculateBeratKotor();
                },

                // Truncate ke 3 desimal TANPA pembulatan
                truncate3(value) {
                    const str = value.toFixed(10);
                    const dotIdx = str.indexOf('.');
                    return str.substring(0, dotIdx + 4).replace('.', ',');
                },

                calculateBeratKotor() {
                    const beratBersih = parseFloat(this.form.berat_bersih.toString().replace(',', '.')) || 0;
                    const pucuk = parseFloat(this.form.pucuk.toString().replace(',', '.')) || 0;
                    const daunGulma = parseFloat(this.form.daun_gulma.toString().replace(',', '.')) || 0;
                    const sogolan = parseFloat(this.form.sogolan.toString().replace(',', '.')) || 0;
                    const siwilan = parseFloat(this.form.siwilan.toString().replace(',', '.')) || 0;
                    const tebumati = parseFloat(this.form.tebumati.toString().replace(',', '.')) || 0;
                    const tanahEtc = parseFloat(this.form.tanah_etc.toString().replace(',', '.')) || 0;

                    const beratKotor = beratBersih + pucuk + daunGulma + sogolan + siwilan + tebumati + tanahEtc;
                    this.form.berat_kotor = this.truncate3(beratKotor);
                },

                formatInput(field) {
                    if (this.form[field] && this.form[field] !== '') {
                        const value = parseFloat(this.form[field].toString().replace(',', '.')) || 0;
                        this.form[field] = this.truncate3(value);
                        if (field !== 'toleransi') {
                            this.calculateBeratKotor();
                        }
                    }
                },

                async searchSuratJalan() {
                    if (!this.form.no_surat_jalan) {
                        this.searchMessage = 'Masukkan nomor surat jalan terlebih dahulu';
                        this.searchSuccess = false;
                        return;
                    }

                    this.searchMessage = 'Mencari...';

                    try {
                        const response = await fetch(`{{ route('pabrik.trash.surat-jalan.check') }}?no=${encodeURIComponent(this.form.no_surat_jalan)}`);
                        const result = await response.json();
                        if (result.exists) {
                            this.searchMessage = result.message || 'Surat jalan ditemukan!';
                            this.searchSuccess = true;
                            this.suratJalanFound = true;

                            if (result.data && result.data.companycode) {
                                this.form.companycode = result.data.companycode;
                            }
                            if (result.data) {
                                this.suratJalanInfo = {
                                    plot: result.data.plot || '-',
                                    namasubkontraktor: result.data.namasubkontraktor || '-',
                                    nomorpolisi: result.data.nomorpolisi || '-'
                                };
                            }
                        } else {
                            this.searchMessage = result.message || 'Nomor surat jalan tidak ditemukan!';
                            this.searchSuccess = false;
                            this.suratJalanFound = false;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.searchMessage = 'Terjadi kesalahan saat mencari surat jalan';
                        this.searchSuccess = false;
                        this.suratJalanFound = false;
                    }
                },

                // Method baru untuk search by date
                async searchByDate() {
                    if (!this.searchForm.company || !this.searchForm.date) {
                        this.dateSearchMessage = 'Company dan tanggal harus diisi';
                        this.dateSearchSuccess = false;
                        return;
                    }

                    this.dateSearchMessage = 'Mencari surat jalan...';
                    this.dateSearchSuccess = false;

                    try {
                        // Gunakan AJAX seperti contoh report page
                        $.ajax({
                            url: '{{ route("pabrik.trash.surat-jalan.search-by-date") }}',
                            method: 'GET',
                            data: {
                                company: this.searchForm.company,
                                date: this.searchForm.date
                            },
                            success: (result) => {
                                if (result.success && result.data.length > 0) {
                                    this.suratJalanList = result.data;
                                    this.dateSearchMessage = `Ditemukan ${result.data.length} surat jalan`;
                                    this.dateSearchSuccess = true;
                                } else {
                                    this.suratJalanList = [];
                                    this.dateSearchMessage = 'Tidak ditemukan surat jalan untuk tanggal dan company tersebut';
                                    this.dateSearchSuccess = false;
                                }
                            },
                            error: (xhr, status, error) => {
                                console.error('Error:', error);
                                this.dateSearchMessage = 'Terjadi kesalahan saat mencari surat jalan';
                                this.dateSearchSuccess = false;
                                this.suratJalanList = [];
                            }
                        });
                    } catch (error) {
                        console.error('Error:', error);
                        this.dateSearchMessage = 'Terjadi kesalahan saat mencari surat jalan';
                        this.dateSearchSuccess = false;
                        this.suratJalanList = [];
                    }
                },

                // Method untuk memilih surat jalan dari list
                selectSuratJalan(suratJalan) {
                    this.selectedSuratJalan = suratJalan;

                    // Set form data dari surat jalan yang dipilih
                    this.form.companycode = suratJalan.companycode;
                    this.form.no_surat_jalan = suratJalan.suratjalanno;

                    // Reset form fields lainnya
                    this.form.jenis = '';
                    this.form.berat_bersih = '';
                    this.form.pucuk = '';
                    this.form.daun_gulma = '';
                    this.form.sogolan = '';
                    this.form.siwilan = '';
                    this.form.tebumati = '';
                    this.form.tanah_etc = '';
                    this.form.berat_kotor = '';
                },

                // Method search by nopol
                async searchByNopol() {
                    if (!this.nopolForm.company || !this.nopolForm.nopol) {
                        this.nopolSearchMessage = 'Company dan nomor polisi harus diisi';
                        this.nopolSearchSuccess = false;
                        return;
                    }

                    this.nopolSearchMessage = 'Mencari surat jalan...';
                    this.nopolSearchSuccess = false;
                    this.nopolSuratJalanList = [];
                    this.selectedNopolSuratJalan = null;

                    try {
                        $.ajax({
                            url: '{{ route("pabrik.trash.surat-jalan.search-by-nopol") }}',
                            method: 'GET',
                            data: {
                                company: this.nopolForm.company,
                                nopol: this.nopolForm.nopol
                            },
                            success: (result) => {
                                if (result.success && result.data.length > 0) {
                                    this.nopolSuratJalanList = result.data;
                                    this.nopolSearchMessage = `Ditemukan ${result.data.length} surat jalan`;
                                    this.nopolSearchSuccess = true;
                                } else {
                                    this.nopolSuratJalanList = [];
                                    this.nopolSearchMessage = result.message || 'Tidak ditemukan surat jalan untuk nomor polisi tersebut';
                                    this.nopolSearchSuccess = false;
                                }
                            },
                            error: (xhr, status, error) => {
                                console.error('Error:', error);
                                this.nopolSearchMessage = 'Terjadi kesalahan saat mencari surat jalan';
                                this.nopolSearchSuccess = false;
                                this.nopolSuratJalanList = [];
                            }
                        });
                    } catch (error) {
                        console.error('Error:', error);
                        this.nopolSearchMessage = 'Terjadi kesalahan saat mencari surat jalan';
                        this.nopolSearchSuccess = false;
                        this.nopolSuratJalanList = [];
                    }
                },

                // Method untuk memilih surat jalan dari list nopol
                selectNopolSuratJalan(suratJalan) {
                    this.selectedNopolSuratJalan = suratJalan;
                    this.form.companycode = suratJalan.companycode;
                    this.form.no_surat_jalan = suratJalan.suratjalanno;
                    this.form.jenis = '';
                    this.form.berat_bersih = '';
                    this.form.pucuk = '';
                    this.form.daun_gulma = '';
                    this.form.sogolan = '';
                    this.form.siwilan = '';
                    this.form.tebumati = '';
                    this.form.tanah_etc = '';
                    this.form.berat_kotor = '';
                },

                deleteItem(data) {
                    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        // FIX: pakai url()
                        form.action = `{{ url('pabrik/trash/delete') }}/${data.suratjalanno}/${data.companycode}/${data.jenis}`;
                        form.innerHTML = '@csrf';
                        document.body.appendChild(form);
                        form.submit();
                    }
                },

                // Methods untuk report modal
                openReportModal() {
                    this.showReportModal = true;
                },

                closeReportModal() {
                    this.showReportModal = false;
                    this.resetReportForm();
                },

                resetReportForm() {
                    const today = new Date();
                    const yesterday = new Date(today);
                    yesterday.setDate(today.getDate() - 1);

                    this.reportForm = {
                        start_date: yesterday.toISOString().split('T')[0],
                        end_date: today.toISOString().split('T')[0],
                        month: String(today.getMonth() + 1).padStart(2, '0'),
                        year: String(today.getFullYear()),
                        report_type: 'harian',
                        company: '',
                        format: 'pdf'
                    };
                },

                // Helper method untuk generate list tahun
                getYearOptions() {
                    const currentYear = new Date().getFullYear();
                    const years = [];
                    for (let i = currentYear; i >= currentYear - 10; i--) {
                        years.push(i);
                    }
                    return years;
                },

                // Helper method untuk generate list bulan
                getMonthOptions() {
                    return [{
                            value: '01',
                            label: 'Januari'
                        },
                        {
                            value: '02',
                            label: 'Februari'
                        },
                        {
                            value: '03',
                            label: 'Maret'
                        },
                        {
                            value: '04',
                            label: 'April'
                        },
                        {
                            value: '05',
                            label: 'Mei'
                        },
                        {
                            value: '06',
                            label: 'Juni'
                        },
                        {
                            value: '07',
                            label: 'Juli'
                        },
                        {
                            value: '08',
                            label: 'Agustus'
                        },
                        {
                            value: '09',
                            label: 'September'
                        },
                        {
                            value: '10',
                            label: 'Oktober'
                        },
                        {
                            value: '11',
                            label: 'November'
                        },
                        {
                            value: '12',
                            label: 'Desember'
                        }
                    ];
                }
            }
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</x-layout>