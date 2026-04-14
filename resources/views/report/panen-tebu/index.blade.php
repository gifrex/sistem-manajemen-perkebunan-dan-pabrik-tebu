<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md w-full">
        <!-- Header Form -->
        <div class="px-4 py-3 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 text-center">Berita Acara Panen Tebu Giling</h2>
        </div>

        <!-- Alert Messages for Form -->
        @if(session('error'))
        <div class="mx-4 mt-4 p-4 bg-red-50 border-l-4 border-red-400 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        <strong>Error!</strong> {{ session('error') }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Form Berita Acara -->
        <div class="p-6">
            <form method="POST" action="{{ route('report.panen-tebu-report.proses') }}" class="space-y-6">
                @csrf
                
                <!-- Nama Kontraktor -->
                <div class="space-y-2">
                    <label for="nama_kontraktor" class="block text-sm font-medium text-gray-700">
                        Nama Kontraktor <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <!-- Hidden input for form submission -->
                        <input type="hidden" id="nama_kontraktor" name="idkontraktor" value="{{ old('nama_kontraktor') }}" required>
                        
                        <!-- Search input -->
                        <input type="text" 
                               id="kontraktor_search" 
                               autocomplete="off"
                               placeholder="Cari dan pilih kontraktor..."
                               class="block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white text-gray-900"
                               onclick="toggleDropdown()"
                               oninput="filterOptions()">
                        
                        <!-- Dropdown arrow -->
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        
                        <!-- Dropdown options -->
                        <div id="kontraktor_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-y-auto">
                            <div class="py-1">
                                @foreach($kontraktor as $ktk)
                                <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100 text-sm text-gray-900" 
                                     data-value="{{$ktk->id}}" 
                                     data-text="{{$ktk->namakontraktor}}"
                                     onclick="selectOption('{{$ktk->id}}', '{{$ktk->namakontraktor}}')">
                                    {{$ktk->namakontraktor}}
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @error('nama_kontraktor')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kode Harga -->
                <div class="space-y-2">
                    <label for="kode_harga" class="block text-sm font-medium text-gray-700">
                        Kode Harga <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <select id="kode_harga" 
                                    name="kode_harga" 
                                    required
                                    onchange="toggleInfoButton()"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white text-gray-900">
                                <option value="">Pilih Kode Harga</option>
                                @foreach($tabel_harga as $harga)
                                <option value="{{$harga->kodeharga}}" {{ old('kode_harga') == $harga->kodeharga ? 'selected' : '' }}>
                                    {{$harga->kodeharga}}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" 
                                id="info_detail_btn"
                                onclick="showHargaDetail()" 
                                class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Info Detail
                        </button>
                    </div>
                    @error('kode_harga')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Range Tanggal -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="start_date" class="block text-sm font-medium text-gray-700">
                            Tanggal Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="start_date" 
                               name="start_date" 
                               required
                               value="{{ old('start_date') }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-400"
                               oninput="this.className = this.value ? 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-black' : 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-400'">
                        @error('start_date')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="end_date" class="block text-sm font-medium text-gray-700">
                            Tanggal Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="end_date" 
                               name="end_date" 
                               required
                               value="{{ old('end_date') }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-400"
                               oninput="this.className = this.value ? 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-black' : 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-400'">
                        @error('end_date')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-center pt-4">
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Generate Report
                    </button>
                </div>
            </form>
        </div>

        <!-- Information Card -->
        <div class="mx-4 mb-4 p-4 bg-blue-50 border-l-4 border-blue-400 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Informasi:</strong> Pastikan semua field telah terisi dengan benar sebelum generate report. Report akan dibuat berdasarkan range tanggal yang dipilih.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- History Section -->
    <div class="mx-auto py-4 bg-white rounded-md shadow-md w-full mt-6">
        <!-- History Header -->
        <div class="px-4 py-3 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">History Report Panen Tebu</h2>
                    <p class="text-sm text-gray-600 mt-1">Daftar report yang pernah di-generate sebelumnya</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="refreshHistoryTable()" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Alert Messages for History Actions -->
        @if(session('success'))
        <div class="mx-4 mt-4 p-4 bg-green-50 border-l-4 border-green-400 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">
                        <strong>Berhasil!</strong> {{ session('success') }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- History Table -->
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="historyTable" class="min-w-full divide-y divide-gray-200 border border-gray-300 display">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">
                                No Doc
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">
                                Nama Pembuat
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">
                                Range Tanggal
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">
                                Nama Kontraktor
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">
                                Kode Harga
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">
                                Grand Total
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">
                                Tanggal Dibuat
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($history as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-center font-medium">
                                <a href="{{ route('report.panen-tebu-report.show', $item->nodoc) }}" 
                                   class="text-blue-600 hover:text-blue-800 font-semibold cursor-pointer underline hover:no-underline">
                                    {{ $item->nodoc }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->userid }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $item->startdate->format('d M Y') }} s/d {{ $item->enddate->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->namakontraktor }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                {{ $item->kodeharga }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600 text-right">
                                Rp {{ number_format($item->grandtotal, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center">
                                {{ $item->createdat->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                <button onclick="confirmDelete('{{ $item->nodoc }}', '{{ $item->nodoc }}', '{{ $item->namakontraktor }}', '{{ $item->startdate->format('d M Y') }} s/d {{ $item->enddate->format('d M Y') }}')" 
                                        class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Include DataTables CSS and JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <!-- Delete Confirmation Modal -->
    <div id="delete_confirmation_modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeDeleteModal()"></div>
            
            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <!-- Modal content -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Konfirmasi Hapus History
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" id="delete-message">
                                    <!-- Message akan diisi oleh JavaScript -->
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form id="delete-form" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Hapus
                        </button>
                    </form>
                    <button type="button" onclick="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Info Detail Harga -->
    <div id="harga_detail_modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeHargaModal()"></div>
            
            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <!-- Modal header -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg leading-6 font-bold text-gray-900 mb-1">
                                Harga Panen Tebu Giling (<span id="modal_company_code"></span>)
                            </h3>
                            <p class="text-sm text-gray-600 mb-1">
                                Periode Giling Tahun <span id="modal_periode"></span>
                            </p>
                            <p class="text-sm text-gray-600">
                                No Kode : <span id="modal_kode_harga"></span>
                            </p>
                        </div>
                        <button type="button" onclick="closeHargaModal()" class="rounded-md bg-white text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Table content -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">Kategori</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">Manual<br>Rp /kg</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">GL Kebun<br>Rp /kg</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">GL Kontraktor<br>Rp /kg</th>
                                </tr>
                            </thead>
                            <tbody id="harga_table_body" class="bg-white divide-y divide-gray-200">
                                <!-- Content will be filled by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeHargaModal()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Data harga untuk modal (akan diisi oleh Blade)
        const hargaData = @json($tabel_harga);

        // History DataTable
        let historyTable;

        // Initialize DataTables when document is ready
        $(document).ready(function() {
            historyTable = $('#historyTable').DataTable({
                order: [[6, 'desc']], // Order by created date descending (index 6 = Tanggal Dibuat)
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                language: {
                    processing: '<div class="flex justify-center items-center p-4"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Loading...</div>',
                    emptyTable: '<div class="text-center py-8 text-gray-500"><svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg><h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada history report</h3><p class="mt-1 text-sm text-gray-500">Generate report pertama Anda untuk melihat history di sini.</p></div>',
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data per halaman',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                    infoFiltered: '(difilter dari _MAX_ total data)',
                    paginate: {
                        next: 'Selanjutnya',
                        previous: 'Sebelumnya'
                    },
                    zeroRecords: '<div class="text-center py-8 text-gray-500"><svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg><h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data ditemukan</h3><p class="mt-1 text-sm text-gray-500">Coba ubah kata kunci pencarian Anda.</p></div>'
                },
                dom: '<"flex flex-col sm:flex-row justify-between items-center mb-4"<"mb-2 sm:mb-0"l><"flex items-center"f>>rt<"flex flex-col sm:flex-row justify-between items-center mt-4"<"mb-2 sm:mb-0"i><"flex items-center"p>>',
                responsive: true,
                autoWidth: false,
                className: 'table-auto w-full',
                columnDefs: [
                    { orderable: false, targets: [7] }, // Disable sorting for Actions column
                    { searchable: false, targets: [7] }, // Disable search for Actions column
                    { className: 'text-center', targets: [0, 2, 4, 6, 7] }, // Center align specific columns
                    { className: 'text-right', targets: [5] }, // Right align Grand Total column
                    { className: 'text-left', targets: [1, 3] } // Left align Name columns
                ]
            });
        });

        // Function to refresh history table
        function refreshHistoryTable() {
            if (historyTable) {
                // For client-side DataTables, we need to reload the page to get fresh data
                window.location.reload();
            }
        }

        // Searchable Select Functions
        function toggleDropdown() {
            const dropdown = document.getElementById('kontraktor_dropdown');
            dropdown.classList.toggle('hidden');
        }

        function selectOption(value, text) {
            document.getElementById('nama_kontraktor').value = value;
            document.getElementById('kontraktor_search').value = text;
            document.getElementById('kontraktor_dropdown').classList.add('hidden');
            
            // Remove validation error styling if exists
            const searchInput = document.getElementById('kontraktor_search');
            searchInput.classList.remove('border-red-500');
        }

        function filterOptions() {
            const searchInput = document.getElementById('kontraktor_search');
            const filter = searchInput.value.toLowerCase();
            const dropdown = document.getElementById('kontraktor_dropdown');
            const options = dropdown.getElementsByClassName('option-item');
            
            // Show dropdown when typing
            dropdown.classList.remove('hidden');
            
            // Filter options
            let visibleCount = 0;
            for (let i = 0; i < options.length; i++) {
                const text = options[i].textContent.toLowerCase();
                if (text.includes(filter)) {
                    options[i].style.display = 'block';
                    visibleCount++;
                } else {
                    options[i].style.display = 'none';
                }
            }
            
            // Clear selection if search doesn't match exactly
            if (visibleCount === 0 || !searchInput.value) {
                document.getElementById('nama_kontraktor').value = '';
            }
        }

        // Toggle Info Detail button
        function toggleInfoButton() {
            const select = document.getElementById('kode_harga');
            const button = document.getElementById('info_detail_btn');
            
            if (select.value) {
                button.disabled = false;
            } else {
                button.disabled = true;
            }
        }

        // Show Harga Detail Modal
        function showHargaDetail() {
            const selectedKode = document.getElementById('kode_harga').value;
            if (!selectedKode) return;

            const selectedHarga = hargaData.find(item => item.kodeharga === selectedKode);
            if (!selectedHarga) return;

            // Update modal title
            document.getElementById('modal_kode_harga').textContent = selectedKode;
            document.getElementById('modal_periode').textContent = selectedHarga.periode;
            document.getElementById('modal_company_code').textContent = selectedHarga.companycode;

            // Build table content
            const tableBody = document.getElementById('harga_table_body');
            tableBody.innerHTML = `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Tebang</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.manualtebang)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.glkebuntebang)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">Rp ${formatNumber(selectedHarga.glkontraktortebang)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Muat</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.manualmuat)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.glkebunmuat)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">Rp ${formatNumber(selectedHarga.glkontraktormuat)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Angkutan</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.manualangkutan)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.glkebunangkutan)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">Rp ${formatNumber(selectedHarga.glkontraktorangkutan)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Fee Kontraktor</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.manualfeekont)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.glkebunfeekont)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">Rp ${formatNumber(selectedHarga.glkontraktorfeekont)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Non Premi</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.manualnonpremi)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.glkebunnonpremi)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">Rp ${formatNumber(selectedHarga.glkontraktornonpremi)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">BSM</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.manualbsm)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.glkebunbsm)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">Rp ${formatNumber(selectedHarga.glkontraktorbsm)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Tebu Sulit</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.manualtebusulit)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.glkebuntebusulit)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">Rp ${formatNumber(selectedHarga.glkontraktortebusulit)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Premi Ton</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.manualpremiton)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.glkebunpremiton)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">Rp ${formatNumber(selectedHarga.glkontraktorpremiton)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Extra Fooding</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500" colspan="3">Rp ${formatNumber(selectedHarga.extrafooding)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Tebu Tidak Seset</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500" colspan="3">Rp ${formatNumber(selectedHarga.tebutdkseset)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Langsir</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500" colspan="3">Rp ${formatNumber(selectedHarga.langsir)}</td>
                </tr>
            `;

            // Show modal
            document.getElementById('harga_detail_modal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        // Close Harga Detail Modal
        function closeHargaModal() {
            document.getElementById('harga_detail_modal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Format number with thousands separator
        function formatNumber(num) {
            return parseInt(num || 0).toLocaleString('id-ID');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('kontraktor_dropdown');
            const searchInput = document.getElementById('kontraktor_search');
            
            if (!dropdown.contains(event.target) && !searchInput.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Initialize selected value on page load (for old input)
        document.addEventListener('DOMContentLoaded', function() {
            const selectedValue = document.getElementById('nama_kontraktor').value;
            const searchInput = document.getElementById('kontraktor_search');
            
            if (selectedValue) {
                const options = document.getElementsByClassName('option-item');
                for (let i = 0; i < options.length; i++) {
                    if (options[i].dataset.value === selectedValue) {
                        searchInput.value = options[i].dataset.text;
                        break;
                    }
                }
            }

            // Initialize info button state
            toggleInfoButton();

            // Existing date validation code
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            function validateDateRange() {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);

                if (startDate && endDate && startDate > endDate) {
                    endDateInput.setCustomValidity('Tanggal selesai harus setelah tanggal mulai');
                } else {
                    endDateInput.setCustomValidity('');
                }
            }

            startDateInput.addEventListener('change', validateDateRange);
            endDateInput.addEventListener('change', validateDateRange);

            // Set max date to today for both inputs
            const today = new Date().toISOString().split('T')[0];
            startDateInput.setAttribute('max', today);
            endDateInput.setAttribute('max', today);
        });

        // Enhanced form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const namaKontraktor = document.getElementById('nama_kontraktor').value;
            const kodeHarga = document.getElementById('kode_harga').value;
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const searchInput = document.getElementById('kontraktor_search');

            if (!namaKontraktor || !kodeHarga || !startDate || !endDate) {
                e.preventDefault();
                
                // Highlight empty fields
                if (!namaKontraktor) {
                    searchInput.classList.add('border-red-500');
                    searchInput.focus();
                }
                if (!kodeHarga) {
                    document.getElementById('kode_harga').classList.add('border-red-500');
                }
                
                alert('Mohon lengkapi semua field yang wajib diisi');
                return false;
            }

            // Additional validation for date range
            if (new Date(startDate) > new Date(endDate)) {
                e.preventDefault();
                alert('Tanggal mulai tidak boleh lebih besar dari tanggal selesai');
                return false;
            }

            // Show loading state
            const submitBtn = e.target.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Generating...
            `;
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeHargaModal();
                closeDeleteModal();
            }
        });

        // Delete confirmation functions
        function confirmDelete(nodoc, noDoc, kontraktor, periode) {
            document.getElementById('delete-message').innerHTML = 
                'Apakah Anda yakin ingin menghapus history report ini?<br><br>' +
                '<strong>No Doc:</strong> ' + noDoc + '<br>' +
                '<strong>Kontraktor:</strong> ' + kontraktor + '<br>' +
                '<strong>Periode:</strong> ' + periode + '<br><br>' +
                '<span class="text-red-600">Tindakan ini tidak dapat dibatalkan.</span>';
            
            document.getElementById('delete-form').action = '{{ url("report/panen-tebu-report") }}/' + nodoc;
            document.getElementById('delete_confirmation_modal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeDeleteModal() {
            document.getElementById('delete_confirmation_modal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    </script>

    <style>
        /* Custom styles to match the original design */
        .transition {
            transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
        }
        
        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
        }
        
        /* Focus states for inputs */
        input:focus, select:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        /* Button hover effects */
        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        /* Required field indicator */
        .text-red-500 {
            color: #ef4444;
        }
        
        /* Form spacing */
        .space-y-6 > * + * {
            margin-top: 1.5rem;
        }
        
        .space-y-2 > * + * {
            margin-top: 0.5rem;
        }

        /* Searchable select dropdown styles */
        .option-item:hover {
            background-color: #f3f4f6;
        }
        
        .option-item:active {
            background-color: #e5e7eb;
        }
        
        /* Dropdown scrollbar styling */
        #kontraktor_dropdown::-webkit-scrollbar {
            width: 6px;
        }
        
        #kontraktor_dropdown::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        #kontraktor_dropdown::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        #kontraktor_dropdown::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Error state for inputs */
        .border-red-500 {
            border-color: #ef4444 !important;
        }

        /* Ensure dropdown appears above other elements */
        .relative {
            position: relative;
        }

        /* Modal animation */
        .fixed {
            backdrop-filter: blur(4px);
        }

        /* Table styling improvements */
        table th {
            position: sticky;
            top: 0;
            background-color: #f9fafb;
        }

        /* Scroll styling for modal table */
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

        /* History table specific styles */
        table tbody tr:hover {
            background-color: #f8fafc;
        }

        table tbody tr td {
            vertical-align: middle;
        }

        /* Make No Doc links more prominent */
        table tbody tr td a {
            font-weight: 600;
        }

        table tbody tr td a:hover {
            text-decoration: none;
        }

        /* DataTables custom styling */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_processing,
        .dataTables_wrapper .dataTables_paginate {
            color: #374151;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.5rem 0.75rem !important;
            margin: 0.125rem !important;
            border-radius: 0.375rem !important;
            border: 1px solid #d1d5db !important;
            color: #374151 !important;
            background: white !important;
            text-decoration: none !important;
            display: inline-block !important;
            min-width: auto !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f3f4f6 !important;
            border-color: #9ca3af !important;
            color: #374151 !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #3b82f6 !important;
            border-color: #3b82f6 !important;
            color: white !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            background: #f9fafb !important;
            border-color: #e5e7eb !important;
            color: #9ca3af !important;
            cursor: not-allowed !important;
        }
        
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background: white;
        }

        /* DataTables search input styling */
        .dataTables_wrapper .dataTables_filter input[type="search"] {
            width: 300px;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .dataTables_wrapper .dataTables_filter input[type="search"]:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* DataTables wrapper styling */
        .dataTables_wrapper {
            width: 100%;
        }

        /* Pagination container styling */
        .dataTables_wrapper .dataTables_paginate {
            float: right;
            text-align: right;
            padding-top: 0.5rem;
        }

        .dataTables_wrapper .dataTables_paginate .pagination {
            margin: 0 !important;
            display: inline-flex;
            align-items: center;
        }

        /* Empty state styling for DataTables */
        .dataTables_empty {
            padding: 3rem 1rem !important;
            text-align: center;
        }

        /* History table specific styles for DataTables */
        #historyTable tbody tr:hover {
            background-color: #f8fafc !important;
        }

        #historyTable tbody tr td {
            vertical-align: middle;
        }

        /* Fix for pagination buttons spacing */
        .dataTables_wrapper .dataTables_paginate span {
            display: inline-flex;
            align-items: center;
        }

        .dataTables_wrapper .dataTables_paginate a {
            margin: 0 2px;
        }
    </style>

</x-layout>