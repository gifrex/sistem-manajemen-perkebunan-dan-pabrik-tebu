<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md w-full">
        <!-- Header Form -->
        <div class="px-4 py-3 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 text-center">Rekapitulasi Premi Target Kontraktor</h2>
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

        <!-- Form Pencarian -->
        <div class="p-6">
            <form method="POST" action="{{ route('report.rekapitulasi-premi-report.search') }}" id="search-form" class="space-y-6">
                @csrf

                <!-- Nama Kontraktor -->
                <div class="space-y-2">
                    <label for="nama_kontraktor" class="block text-sm font-medium text-gray-700">
                        Nama Kontraktor <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="hidden" id="nama_kontraktor" name="idkontraktor" value="{{ $searchParams['idkontraktor'] ?? old('idkontraktor') }}" required>
                        <input type="text"
                               id="kontraktor_search"
                               autocomplete="off"
                               placeholder="Cari dan pilih kontraktor..."
                               class="block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white text-gray-900"
                               onclick="toggleDropdown()"
                               oninput="filterOptions()">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </div>
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
                    @error('idkontraktor')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Periode -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Periode <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <select id="bulan" name="bulan" required
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white text-gray-900">
                                <option value="">Pilih Bulan</option>
                                <option value="01" {{ ($searchParams['bulan'] ?? old('bulan')) == '01' ? 'selected' : '' }}>Januari</option>
                                <option value="02" {{ ($searchParams['bulan'] ?? old('bulan')) == '02' ? 'selected' : '' }}>Februari</option>
                                <option value="03" {{ ($searchParams['bulan'] ?? old('bulan')) == '03' ? 'selected' : '' }}>Maret</option>
                                <option value="04" {{ ($searchParams['bulan'] ?? old('bulan')) == '04' ? 'selected' : '' }}>April</option>
                                <option value="05" {{ ($searchParams['bulan'] ?? old('bulan')) == '05' ? 'selected' : '' }}>Mei</option>
                                <option value="06" {{ ($searchParams['bulan'] ?? old('bulan')) == '06' ? 'selected' : '' }}>Juni</option>
                                <option value="07" {{ ($searchParams['bulan'] ?? old('bulan')) == '07' ? 'selected' : '' }}>Juli</option>
                                <option value="08" {{ ($searchParams['bulan'] ?? old('bulan')) == '08' ? 'selected' : '' }}>Agustus</option>
                                <option value="09" {{ ($searchParams['bulan'] ?? old('bulan')) == '09' ? 'selected' : '' }}>September</option>
                                <option value="10" {{ ($searchParams['bulan'] ?? old('bulan')) == '10' ? 'selected' : '' }}>Oktober</option>
                                <option value="11" {{ ($searchParams['bulan'] ?? old('bulan')) == '11' ? 'selected' : '' }}>November</option>
                                <option value="12" {{ ($searchParams['bulan'] ?? old('bulan')) == '12' ? 'selected' : '' }}>Desember</option>
                            </select>
                            @error('bulan')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <input type="text" id="tahun" name="tahun" required maxlength="4"
                                   placeholder="Contoh: 2025"
                                   value="{{ $searchParams['tahun'] ?? old('tahun') }}"
                                   inputmode="numeric" pattern="\d{4}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white text-gray-900"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4)">
                            @error('tahun')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Tombol Cari -->
                <div class="flex justify-center pt-2">
                    <button type="submit" id="btn-cari"
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 0 5 11a6 6 0 0 0 12 0z"/>
                        </svg>
                        Cari Data
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
                        <strong>Informasi:</strong> Pilih kontraktor dan periode terlebih dahulu, kemudian klik <strong>Cari Data</strong> untuk melihat data yang tersedia. Setelah data ditemukan, Anda dapat melakukan generate report.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== HASIL PENCARIAN + FORM ALASAN + GENERATE ===== --}}
    @if(!is_null($searchResults))
    {{-- 
        PENTING: Seluruh bagian hasil pencarian + form alasan + tombol generate
        dibungkus dalam SATU form POST ke route proses.
        Ini agar alasan ikut ter-submit bersama idkontraktor, bulan, tahun.
    --}}
    <form method="POST" action="{{ route('report.rekapitulasi-premi-report.proses') }}" id="generate-form">
        @csrf
        <input type="hidden" name="idkontraktor" value="{{ $searchParams['idkontraktor'] }}">
        <input type="hidden" name="bulan" value="{{ $searchParams['bulan'] }}">
        <input type="hidden" name="tahun" value="{{ $searchParams['tahun'] }}">

        {{-- Tabel Hasil Pencarian --}}
        <div class="mx-auto py-4 bg-white rounded-md shadow-md w-full mt-6" id="search-results-section">
            <div class="px-4 py-3 border-b border-gray-200">
                <div class="flex justify-between items-center flex-wrap gap-2">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Hasil Pencarian</h2>
                        <p class="text-sm text-gray-500 mt-0.5">
                            Kontraktor: <strong>{{ $searchParams['namakontraktor'] ?: '-' }}</strong> &nbsp;|&nbsp;
                            Periode: <strong>
                                @php
                                    $namaBulanList = [
                                        '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                                        '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                                        '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
                                    ];
                                @endphp
                                {{ $namaBulanList[$searchParams['bulan']] ?? $searchParams['bulan'] }} {{ $searchParams['tahun'] }}
                            </strong>
                        </p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $searchResults->count() > 0 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ $searchResults->count() }} dokumen ditemukan
                    </span>
                </div>
            </div>

            @if($searchResults->count() > 0)
            <div class="p-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No Dokumen</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID Kontraktor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Kontraktor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Start Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">End Date</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Grand Total</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dibuat Pada</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($searchResults as $index => $row)
                        <tr class="hover:bg-gray-50 transition-colors duration-100">
                            <td class="px-4 py-3 text-gray-500 text-center">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-mono font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $row->nodoc }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $row->idkontraktor }}</td>
                            <td class="px-4 py-3 text-gray-800 font-medium">{{ $row->namakontraktor }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($row->startdate)->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($row->enddate)->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-800">
                                Rp {{ number_format($row->grandtotal, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($row->createdat)->format('d M Y H:i') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary Grand Total -->
            <div class="mx-4 mb-2 p-3 bg-gray-50 border border-gray-200 rounded-md">
                <div class="text-sm text-gray-600">
                    Total Grand Total:
                    <span class="ml-2 text-lg font-bold text-indigo-700">
                        Rp {{ number_format($searchResults->sum('grandtotal'), 2, ',', '.') }}
                    </span>
                </div>
            </div>

            @else
            <div class="p-12 text-center">
                <svg class="mx-auto h-14 w-14 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-3 text-sm font-semibold text-gray-700">Tidak ada data ditemukan</h3>
                <p class="mt-1 text-sm text-gray-500">Tidak ditemukan data untuk kontraktor dan periode yang dipilih.</p>
            </div>
            @endif
        </div>

        {{-- ===== FORM ALASAN TANGGAL KOSONG ===== --}}
        @if($searchResults->count() > 0)
        <div class="mx-auto py-4 bg-white rounded-md shadow-md w-full mt-6" id="missing-dates-section">
            <div class="px-4 py-3 border-b border-gray-200">
                <div class="flex justify-between items-center flex-wrap gap-2">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Keterangan Tanggal Tanpa Data</h2>
                        <p class="text-sm text-gray-500 mt-0.5">
                            Bulan {{ $namaBulanList[$searchParams['bulan']] ?? $searchParams['bulan'] }} {{ $searchParams['tahun'] }}
                            memiliki <strong>{{ $jumlahHariBulan }} hari</strong>.
                            Tanggal berikut tidak ditemukan di seluruh dokumen.
                        </p>
                    </div>
                    @if(count($missingDates) > 0)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                        {{ count($missingDates) }} tanggal kosong
                    </span>
                    @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        Semua tanggal ada data ?
                    </span>
                    @endif
                </div>
            </div>

            <div class="p-4">
                @if(count($missingDates) > 0)
                <div class="mb-4 p-3 bg-orange-50 border-l-4 border-orange-400 rounded-md">
                    <p class="text-sm text-orange-700">
                        <strong>Perhatian:</strong> Isi alasan untuk setiap tanggal yang tidak ada data. Keterangan ini akan ditampilkan di dalam report.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-12">No</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-48">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hari</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Alasan / Keterangan <span class="text-red-500">*</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($missingDates as $i => $tgl)
                            @php
                                $carbon = \Carbon\Carbon::parse($tgl);
                                $namaHari = [
                                    'Monday'    => 'Senin',
                                    'Tuesday'   => 'Selasa',
                                    'Wednesday' => 'Rabu',
                                    'Thursday'  => 'Kamis',
                                    'Friday'    => 'Jumat',
                                    'Saturday'  => 'Sabtu',
                                    'Sunday'    => 'Minggu',
                                ];
                                $hariIndo = $namaHari[$carbon->format('l')] ?? $carbon->format('l');
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-center text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-mono font-semibold bg-orange-50 text-orange-700 border border-orange-200">
                                        {{ $carbon->format('d M Y') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-sm">{{ $hariIndo }}</td>
                                <td class="px-4 py-3">
                                    <input type="text"
                                           name="alasan[{{ $tgl }}]"
                                           placeholder="Masukkan alasan..."
                                           required
                                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-white text-gray-900 alasan-input">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @else
                <!-- Semua tanggal ada data -->
                <div class="flex items-center justify-center py-8 text-center">
                    <div>
                        <svg class="mx-auto h-12 w-12 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-700">Semua tanggal terisi</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Seluruh {{ $jumlahHariBulan }} hari di bulan
                            {{ $namaBulanList[$searchParams['bulan']] ?? '' }} {{ $searchParams['tahun'] }}
                            memiliki data panen.
                        </p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Tombol Generate Report --}}
        <div class="mx-auto mt-4 mb-2 flex justify-center">
            <button type="submit"
                    id="btn-generate"
                    class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Generate Report
            </button>
        </div>
        @endif

    </form>
    {{-- END FORM --}}
    @endif

    <!-- History Section -->
    <div class="mx-auto py-4 bg-white rounded-md shadow-md w-full mt-6">
        <div class="px-4 py-3 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">History Report Premi Target Kontraktor</h2>
                    <p class="text-sm text-gray-600 mt-1">Daftar report yang pernah di-generate sebelumnya</p>
                </div>
                <button onclick="refreshHistoryTable()" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        @if(session('success'))
        <div class="mx-4 mt-4 p-4 bg-green-50 border-l-4 border-green-400 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700"><strong>Berhasil!</strong> {{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif

        <div class="p-4 overflow-x-auto">
            <table id="historyTable" class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No Dokumen</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">ID Kontraktor</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Kontraktor</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Grand Total</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal Dibuat</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($history as $i => $h)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-center text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded border border-indigo-100">{{ $h->nodoc }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600 font-mono text-xs">{{ $h->idkontraktor }}</td>
                        <td class="px-4 py-3 text-gray-800 font-medium">{{ $h->namakontraktor }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-800">Rp {{ number_format($h->grandtotal, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center text-gray-500 text-xs">{{ \Carbon\Carbon::parse($h->createdat)->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 text-center">
                            <button type="button"
                                    onclick="confirmDelete('{{$h->nodoc}}', '{{$h->nodoc}}', '{{$h->namakontraktor}}', '{{ \Carbon\Carbon::parse($h->createdat)->format('d M Y') }}')"
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-700 bg-red-50 border border-red-200 rounded hover:bg-red-100 transition">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
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

    <!-- Include DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <!-- Delete Confirmation Modal -->
    <div id="delete_confirmation_modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeDeleteModal()"></div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Konfirmasi Hapus History</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" id="delete-message"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form id="delete-form" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">Hapus</button>
                    </form>
                    <button type="button" onclick="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let historyTable;

        $(document).ready(function() {
            historyTable = $('#historyTable').DataTable({
                order: [[5, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                language: {
                    emptyTable: '<div class="text-center py-8 text-gray-500"><h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada history report</h3></div>',
                    search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data per halaman',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                    infoFiltered: '(difilter dari _MAX_ total data)',
                    paginate: { next: 'Selanjutnya', previous: 'Sebelumnya' },
                    zeroRecords: 'Tidak ada data ditemukan'
                },
                columnDefs: [
                    { orderable: false, targets: [6] },
                    { searchable: false, targets: [6] },
                    { className: 'text-center', targets: [0, 2, 5, 6] },
                    { className: 'text-right', targets: [4] },
                    { className: 'text-left', targets: [1, 3] }
                ]
            });

            @if(!is_null($searchResults))
            const resultSection = document.getElementById('search-results-section');
            if (resultSection) {
                setTimeout(() => resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' }), 300);
            }
            @endif
        });

        function refreshHistoryTable() { window.location.reload(); }

        // ---- Searchable Select ----
        function toggleDropdown() {
            document.getElementById('kontraktor_dropdown').classList.toggle('hidden');
        }
        function selectOption(value, text) {
            document.getElementById('nama_kontraktor').value = value;
            document.getElementById('kontraktor_search').value = text;
            document.getElementById('kontraktor_dropdown').classList.add('hidden');
            document.getElementById('kontraktor_search').classList.remove('border-red-500');
        }
        function filterOptions() {
            const filter = document.getElementById('kontraktor_search').value.toLowerCase();
            const dropdown = document.getElementById('kontraktor_dropdown');
            const options = dropdown.getElementsByClassName('option-item');
            dropdown.classList.remove('hidden');
            let visible = 0;
            for (let i = 0; i < options.length; i++) {
                const match = options[i].textContent.toLowerCase().includes(filter);
                options[i].style.display = match ? 'block' : 'none';
                if (match) visible++;
            }
            if (visible === 0 || !document.getElementById('kontraktor_search').value) {
                document.getElementById('nama_kontraktor').value = '';
            }
        }
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('kontraktor_dropdown');
            const search   = document.getElementById('kontraktor_search');
            if (!dropdown.contains(e.target) && !search.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const val = document.getElementById('nama_kontraktor').value;
            if (val) {
                const opts = document.getElementsByClassName('option-item');
                for (let i = 0; i < opts.length; i++) {
                    if (opts[i].dataset.value === val) {
                        document.getElementById('kontraktor_search').value = opts[i].dataset.text;
                        break;
                    }
                }
            }
        });

        // ---- Form Validations ----
        document.getElementById('search-form').addEventListener('submit', function(e) {
            const ktk   = document.getElementById('nama_kontraktor').value;
            const bulan = document.getElementById('bulan').value;
            const tahun = document.getElementById('tahun').value;
            let valid   = true;
            if (!ktk)   { document.getElementById('kontraktor_search').classList.add('border-red-500'); valid = false; }
            if (!bulan) { document.getElementById('bulan').classList.add('border-red-500'); valid = false; }
            if (!tahun || tahun.length !== 4) { document.getElementById('tahun').classList.add('border-red-500'); valid = false; }
            if (!valid) { e.preventDefault(); alert('Mohon lengkapi semua field yang wajib diisi'); return false; }
            const tahunInt = parseInt(tahun);
            if (tahunInt < 2000 || tahunInt > 2099) {
                e.preventDefault();
                document.getElementById('tahun').classList.add('border-red-500');
                alert('Tahun tidak valid. Masukkan tahun antara 2000 - 2099');
                return false;
            }
            const btn = document.getElementById('btn-cari');
            btn.disabled = true;
            btn.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Mencari...`;
        });

        @if(!is_null($searchResults) && $searchResults->count() > 0)
        document.getElementById('generate-form').addEventListener('submit', function(e) {
            // Validasi semua field alasan harus terisi
            const alasanInputs = document.querySelectorAll('.alasan-input');
            let allFilled = true;
            let firstEmpty = null;

            alasanInputs.forEach(function(input) {
                if (input.value.trim() === '') {
                    input.classList.add('border-red-500', 'ring-1', 'ring-red-400');
                    allFilled = false;
                    if (!firstEmpty) firstEmpty = input;
                } else {
                    input.classList.remove('border-red-500', 'ring-1', 'ring-red-400');
                }
            });

            if (!allFilled) {
                e.preventDefault();
                alert('Mohon isi keterangan/alasan untuk semua tanggal yang tidak ada data.');
                if (firstEmpty) {
                    firstEmpty.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstEmpty.focus();
                }
                return false;
            }

            // Semua terisi, loading state
            const btn = document.getElementById('btn-generate');
            btn.disabled = true;
            btn.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Generating...`;
        });

        // Hapus highlight merah saat user mulai mengetik
        document.querySelectorAll('.alasan-input').forEach(function(input) {
            input.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    this.classList.remove('border-red-500', 'ring-1', 'ring-red-400');
                }
            });
        });
        @endif

        document.getElementById('bulan').addEventListener('change', function() { this.classList.remove('border-red-500'); });
        document.getElementById('tahun').addEventListener('input', function() { this.classList.remove('border-red-500'); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeDeleteModal(); });

        // ---- Delete Modal ----
        function confirmDelete(nodoc, noDoc, kontraktor, periode) {
            document.getElementById('delete-message').innerHTML =
                'Apakah Anda yakin ingin menghapus history report ini?<br><br>' +
                '<strong>No Doc:</strong> ' + noDoc + '<br><strong>Kontraktor:</strong> ' + kontraktor +
                '<br><strong>Periode:</strong> ' + periode + '<br><br><span class="text-red-600">Tindakan ini tidak dapat dibatalkan.</span>';
            document.getElementById('delete-form').action = '{{ url("report/premi-target-kontraktor") }}/' + nodoc;
            document.getElementById('delete_confirmation_modal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
        function closeDeleteModal() {
            document.getElementById('delete_confirmation_modal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    </script>

    <style>
        input:focus, select:focus { box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        button:hover { transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06); }
        .space-y-6>*+* { margin-top:1.5rem; }
        .space-y-2>*+* { margin-top:.5rem; }
        .option-item:hover { background-color:#f3f4f6; }
        #kontraktor_dropdown::-webkit-scrollbar { width:6px; }
        #kontraktor_dropdown::-webkit-scrollbar-track { background:#f1f1f1; border-radius:3px; }
        #kontraktor_dropdown::-webkit-scrollbar-thumb { background:#c1c1c1; border-radius:3px; }
        .border-red-500 { border-color:#ef4444 !important; }
        .relative { position:relative; }
        table th { position:sticky; top:0; background-color:#f9fafb; }
        table tbody tr:hover { background-color:#f8fafc; }
        table tbody tr td { vertical-align:middle; }
        .overflow-x-auto::-webkit-scrollbar { height:6px; }
        .overflow-x-auto::-webkit-scrollbar-track { background:#f1f1f1; border-radius:3px; }
        .overflow-x-auto::-webkit-scrollbar-thumb { background:#c1c1c1; border-radius:3px; }
        .dataTables_wrapper .dataTables_paginate .paginate_button { padding:.5rem .75rem!important; margin:.125rem!important; border-radius:.375rem!important; border:1px solid #d1d5db!important; color:#374151!important; background:white!important; text-decoration:none!important; display:inline-block!important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover { background:#f3f4f6!important; border-color:#9ca3af!important; color:#374151!important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background:#3b82f6!important; border-color:#3b82f6!important; color:white!important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled, .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover { background:#f9fafb!important; border-color:#e5e7eb!important; color:#9ca3af!important; cursor:not-allowed!important; }
        .dataTables_wrapper .dataTables_length select, .dataTables_wrapper .dataTables_filter input { padding:.5rem; border:1px solid #d1d5db; border-radius:.375rem; background:white; }
        .dataTables_wrapper .dataTables_filter input[type="search"] { width:300px; padding:.5rem .75rem; font-size:.875rem; }
        .dataTables_wrapper { width:100%; }
        .dataTables_wrapper .dataTables_paginate { float:right; text-align:right; padding-top:.5rem; }
    </style>

</x-layout>