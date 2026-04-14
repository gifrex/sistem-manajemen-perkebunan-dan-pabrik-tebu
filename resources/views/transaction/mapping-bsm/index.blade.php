<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <!-- Add meta token for CSRF -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="mx-auto py-4 bg-white rounded-md shadow-md w-full">
        <!-- Header Form -->
        <div class="px-4 py-3 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 text-center">Mapping BSM</h2>
        </div>

        <!-- Form Filter -->
        <div class="p-6">
            <form method="POST" action="{{ route('transaction.mapping-bsm.index') }}" class="space-y-6">
                @csrf
                
                <!-- Range Tanggal -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="tanggalawal" class="block text-sm font-medium text-gray-700">
                            Tanggal Awal <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="tanggalawal" 
                               name="tanggalawal" 
                               required
                               value="{{ request('tanggalawal') ?? old('tanggalawal') }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm {{ (request('tanggalawal') || old('tanggalawal')) ? 'text-black' : 'text-gray-400' }}"
                               oninput="this.className = this.value ? 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-black' : 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-400'">
                        @error('tanggalawal')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="tanggalakhir" class="block text-sm font-medium text-gray-700">
                            Tanggal Akhir <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="tanggalakhir" 
                               name="tanggalakhir" 
                               required
                               value="{{ request('tanggalakhir') ?? old('tanggalakhir') }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm {{ (request('tanggalakhir') || old('tanggalakhir')) ? 'text-black' : 'text-gray-400' }}"
                               oninput="this.className = this.value ? 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-black' : 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-400'">
                        @error('tanggalakhir')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Search Button -->
                <div class="flex justify-center pt-4">
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Cari Data
                    </button>
                </div>
            </form>
        </div>

        <!-- Information Card - UPDATED TEXT -->
        <div class="mx-4 mb-4 p-4 bg-blue-50 border-l-4 border-blue-400 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Informasi Copy BSM:</strong> BSM yang kosong (belum ada nilai) dapat mengcopy data dari BSM lain yang sudah lengkap dalam plot yang sama. 
                        Klik "Detail" untuk melihat data BSM, lalu klik tombol "Copy" pada BSM kosong untuk memilih sumber data.
                    </p>
                </div>
            </div>
        </div>

        <!-- DataTable Container -->
        <div class="p-6 pt-0">
            @if(request('tanggalawal') && request('tanggalakhir') && isset($data))
            <div id="table-container">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Ringkasan Data RKH</h3>
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="text-sm text-gray-600">
                            Periode: <span class="font-medium">{{ \Carbon\Carbon::parse(request('tanggalawal'))->format('d F Y') }} - {{ \Carbon\Carbon::parse(request('tanggalakhir'))->format('d F Y') }}</span>
                            | Total RKH: <span class="font-medium text-indigo-600">{{ count($data) }}</span>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" 
                                    id="export-excel-btn"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export Excel
                            </button>
                            <button type="button" 
                                    id="print-btn"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Print
                            </button>
                        </div>
                    </div>
                </div>

                @if(count($data) > 0)
                <!-- Summary Cards - UPDATED -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <dt class="text-sm font-medium text-blue-600">Total RKH</dt>
                                <dd class="text-2xl font-bold text-blue-900">{{ count($data) }}</dd>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <dt class="text-sm font-medium text-purple-600">Total Surat Jalan</dt>
                                <dd class="text-2xl font-bold text-purple-900">{{ $data->sum('jumlah_sj') ?: $data->sum('jumlah_lkh') }}</dd>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <dt class="text-sm font-medium text-green-600">Total BSM</dt>
                                <dd class="text-2xl font-bold text-green-900">{{ $data->sum('total_bsm') ?: $data->sum('jumlah_bsm') }}</dd>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DataTable - UPDATED -->
                <div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table id="mapping-bsm-table" class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No RKH</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal RKH</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Surat Jalan</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total BSM</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($data as $index => $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $item->rkhno }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($item->rkhdate)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ ($item->jumlah_sj ?? $item->jumlah_lkh) > 0 ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}" title="Jumlah Surat Jalan">
                                            {{ $item->jumlah_sj ?? $item->jumlah_lkh }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ ($item->total_bsm ?? $item->jumlah_bsm) > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}" title="Total BSM">
                                            {{ $item->total_bsm ?? $item->jumlah_bsm }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <button type="button" 
                                                onclick="showDetailModal('{{ $item->rkhno }}')"
                                                class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 616 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                </div>
                @else
                <!-- No Data Found -->
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v10z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Data tidak ditemukan</h3>
                    <p class="mt-1 text-sm text-gray-500">Tidak ada data RKH untuk periode tanggal yang dipilih.</p>
                </div>
                @endif
            </div>
            @else
            <!-- No Data State -->
            <div id="no-data-state" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v10z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada data</h3>
                <p class="mt-1 text-sm text-gray-500">Silakan pilih rentang tanggal dan klik "Cari Data" untuk menampilkan ringkasan data RKH.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Detail BSM Modal -->
    <div id="detail_modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-full items-end justify-center p-0 sm:items-center sm:p-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeDetailModal()"></div>

            <div class="relative w-full sm:max-w-7xl bg-white rounded-t-xl sm:rounded-xl shadow-xl flex flex-col max-h-screen sm:max-h-[90vh]">
                <!-- Header -->
                <div class="bg-white px-4 pt-4 pb-3 border-b border-gray-200 flex-shrink-0">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h3 class="text-base sm:text-lg leading-6 font-bold text-gray-900">Detail BSM per Surat Jalan</h3>
                            <p class="text-xs sm:text-sm text-gray-600 mt-0.5 truncate" id="modal_detail_subtitle"></p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button type="button" id="bulk-save-btn" onclick="saveBulkChanges()" class="hidden inline-flex items-center px-2 py-1.5 sm:px-3 sm:py-2 border border-transparent text-xs sm:text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="hidden sm:inline">Simpan Semua Perubahan</span>
                                <span class="sm:hidden">Simpan Semua</span>
                            </button>
                            <button type="button" onclick="closeDetailModal()" class="rounded-md text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <span class="sr-only">Close</span>
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Scrollable body -->
                <div class="flex-1 overflow-y-auto overflow-x-auto px-4 py-4">
                    <div id="modal_loading" class="flex justify-center items-center py-8 hidden">
                        <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-gray-600">Memuat data...</span>
                    </div>

                    <div id="modal_detail_content"></div>

                    <div id="modal_error" class="text-center py-8 hidden">
                        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-red-900">Terjadi kesalahan</h3>
                        <p class="mt-1 text-sm text-red-600" id="error_message">Gagal memuat data BSM.</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-4 py-3 flex justify-end border-t border-gray-200 flex-shrink-0">
                    <button type="button" onclick="closeDetailModal()" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- BSM Copy Modal -->
    <div id="copy_bsm_modal" class="fixed inset-0 z-[60] overflow-y-auto hidden" aria-labelledby="copy-modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-full items-end justify-center p-0 sm:items-center sm:p-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-90 transition-opacity z-[61]" aria-hidden="true"></div>

            <div class="relative w-full sm:max-w-4xl bg-white rounded-t-xl sm:rounded-xl shadow-xl flex flex-col max-h-screen sm:max-h-[85vh] z-[62]">
                <!-- Header -->
                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-4 sm:px-6 py-4 flex-shrink-0 rounded-t-xl sm:rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 mr-2">
                            <h3 class="text-base sm:text-lg font-bold text-white">📋 Copy Data BSM</h3>
                            <p class="text-purple-100 text-xs sm:text-sm truncate" id="copy_modal_subtitle"></p>
                        </div>
                        <button type="button" onclick="closeCopyModal()" class="flex-shrink-0 rounded-md bg-white bg-opacity-20 text-white hover:bg-opacity-30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white p-1">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Scrollable body -->
                <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-4">
                    <div id="copy_modal_loading" class="flex justify-center items-center py-8 hidden">
                        <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-gray-600">Mencari BSM yang bisa dicopy...</span>
                    </div>

                    <div id="copy_modal_content"></div>

                    <div id="copy_modal_error" class="text-center py-8 hidden">
                        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-red-900">Tidak dapat copy</h3>
                        <p class="mt-1 text-sm text-red-600" id="copy_error_message">Tidak ada BSM yang dapat dicopy.</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-4 sm:px-6 py-3 flex justify-end border-t border-gray-200 flex-shrink-0">
                    <button type="button" onclick="closeCopyModal()" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- BSM Remap Modal -->
    <div id="remap_bsm_modal" class="fixed inset-0 z-[70] overflow-y-auto hidden" aria-labelledby="remap-modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-full items-end justify-center p-0 sm:items-center sm:p-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-90 transition-opacity z-[71]" aria-hidden="true"></div>

            <div class="relative w-full sm:max-w-2xl bg-white rounded-t-xl sm:rounded-xl shadow-xl flex flex-col max-h-screen sm:max-h-[80vh] z-[72]">
                <!-- Header -->
                <div class="bg-gradient-to-r from-orange-500 to-red-500 px-4 sm:px-6 py-4 flex-shrink-0 rounded-t-xl sm:rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 mr-2">
                            <h3 class="text-base sm:text-lg font-bold text-white">🔗 Hubungkan BSM ke Surat Jalan</h3>
                            <p class="text-orange-100 text-xs sm:text-sm truncate" id="remap_modal_subtitle"></p>
                        </div>
                        <button type="button" onclick="closeRemapModal()" class="flex-shrink-0 rounded-md bg-white bg-opacity-20 text-white hover:bg-opacity-30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white p-1">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Scrollable body -->
                <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-4" id="remap_modal_content"></div>

                <!-- Footer -->
                <div class="bg-gray-50 px-4 sm:px-6 py-3 flex justify-end border-t border-gray-200 flex-shrink-0">
                    <button type="button" onclick="closeRemapModal()" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include DataTables CSS and JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwind.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.tailwind.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwind.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <!-- Configuration variables for the script -->
    <script>
        // Global configuration variables
        window.hasData = @if(request('tanggalawal') && request('tanggalakhir') && isset($data) && count($data) > 0) true @else false @endif;
        window.csrfToken = '{{ csrf_token() }}';
        window.getBsmDetailUrl = '{{ route('transaction.mapping-bsm.get-bsm-detail') }}';
        window.getBsmForCopyUrl = '{{ route('transaction.mapping-bsm.get-bsm-for-copy') }}';
        window.copyBsmUrl = '{{ route('transaction.mapping-bsm.copy-bsm') }}';
        window.updateBsmUrl = '{{ route('transaction.mapping-bsm.update-bsm') }}';
        window.updateBsmBulkUrl = '{{ route('transaction.mapping-bsm.update-bsm-bulk') }}';
        window.remapBsmUrl = '{{ route('transaction.mapping-bsm.remap-bsm') }}';
        window.exportTitle = 'Ringkasan RKH - {{ request("tanggalawal") ?? "" }} s/d {{ request("tanggalakhir") ?? "" }}';
        window.exportMessageTop = 'Periode: {{ request("tanggalawal") ?? "" }} s/d {{ request("tanggalakhir") ?? "" }}';
    </script>

    <!-- Include the main BSM script -->
    <script src="{{ asset('js/mapping-bsm-script.js') }}"></script>

    <style>
        .transition {
            transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
        }
        
        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
        }
        
        input:focus, select:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .text-red-500 {
            color: #ef4444;
        }
        
        .space-y-6 > * + * {
            margin-top: 1.5rem;
        }
        
        .space-y-2 > * + * {
            margin-top: 0.5rem;
        }

        .dataTables_wrapper {
            font-family: inherit;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_paginate {
            margin-top: 1rem;
        }

        #mapping-bsm-table thead th {
            position: sticky;
            top: 0;
            background-color: #f9fafb;
            z-index: 10;
        }

        .fixed {
            backdrop-filter: blur(4px);
        }

        .overflow-x-auto::-webkit-scrollbar {
            height: 6px;
        }
        
        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 768px) {
            #modal_detail_content table {
                font-size: 0.75rem;
            }
            #modal_detail_content th,
            #modal_detail_content td {
                padding: 0.4rem 0.35rem;
            }
            #modal_detail_content .w-28 {
                width: 4.5rem;
            }
        }

        .editable-input {
            transition: all 0.2s ease;
        }
        
        .editable-input:focus {
            transform: scale(1.05);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        /* Enhanced styling for copy functionality */
        .bg-yellow-50 {
            background-color: #fefce8;
        }

        .hover\:bg-yellow-100:hover {
            background-color: #fef3c7;
        }
        
        .bg-amber-400 {
            background-color: #fbbf24;
        }

        .bg-purple-400 {
            background-color: #a855f7;
        }

        .bg-purple-50 {
            background-color: #faf5ff;
        }

        .text-purple-600 {
            color: #9333ea;
        }

        .text-purple-700 {
            color: #7c3aed;
        }

        .text-purple-800 {
            color: #6b21a8;
        }

        .border-purple-200 {
            border-color: #e9d5ff;
        }

        .bg-purple-100 {
            background-color: #f3e8ff;
        }

        .hover\:bg-purple-200:hover {
            background-color: #e9d5ff;
        }

        /* Visual indicators for BSM states */
        .bsm-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .bsm-indicator.empty {
            background-color: #f59e0b; /* amber for empty/editable */
        }

        .bsm-indicator.copied {
            background-color: #a855f7; /* purple for copied */
        }

        .bsm-indicator.completed {
            background-color: #10b981; /* green for completed/graded */
        }

        /* Z-index fix for modal layering */
        #detail_modal   { z-index: 50; }
        #copy_bsm_modal { z-index: 60; }
        #remap_bsm_modal { z-index: 70; }

        .z-\[60\] { z-index: 60 !important; }
        .z-\[61\] { z-index: 61 !important; }
        .z-\[62\] { z-index: 62 !important; }
        .z-\[70\] { z-index: 70 !important; }
        .z-\[71\] { z-index: 71 !important; }
        .z-\[72\] { z-index: 72 !important; }

        /* Copy button styling fix */
        #execute-copy-btn:disabled {
            background-color: #d1d5db !important;
            color: #6b7280 !important;
            cursor: not-allowed !important;
        }

        #execute-copy-btn:not(:disabled) {
            background-color: #9333ea !important;
            color: white !important;
        }

        #execute-copy-btn:not(:disabled):hover {
            background-color: #7c3aed !important;
        }
    </style>

</x-layout>