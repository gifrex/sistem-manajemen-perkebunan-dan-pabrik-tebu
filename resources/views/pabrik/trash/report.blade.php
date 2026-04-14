<x-layout>
    <x-slot:title>Laporan Trash {{ ucfirst($reportType) }}</x-slot:title>
    <x-slot:navbar>Pabrik</x-slot:navbar>
    <x-slot:nav>Laporan Trash</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md">

        <!-- Print Controls -->
        <div class="no-print px-4 py-4 border-b border-gray-200 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Laporan Trash</h1>
            <div class="flex gap-2">
                <button onclick="window.print()"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4a1 1 0 00-1-1H9a1 1 0 001 1v4a1 1 0 001 1zm3-5h2a2 2 0 002-2v-3a2 2 0 00-2-2H5a2 2 0 00-2 2v3a2 2 0 002 2h2"></path>
                    </svg>
                    Print
                </button>
                <button onclick="window.close()"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Tutup
                </button>
            </div>
        </div>

        <!-- Report Header -->
        <div class="text-center mb-6 px-4">
            @php
            // Ambil company pertama dari actualCompanies untuk judul print
            $primaryCompany = !empty($actualCompanies) ? $actualCompanies[0] : 'UNKNOWN';

            // Mapping company code ke nama lengkap untuk judul print
            $companyNames = [
            'TBL1' => 'KEBUN TBL',
            'TBL2' => 'KEBUN TBL',
            'TBL3' => 'KEBUN TBL',
            'BNIL1' => 'KEBUN BNIL',
            'BNIL2' => 'KEBUN BNIL',
            'BNIL3' => 'KEBUN BNIL',
            'BNIL4' => 'KEBUN BNIL',
            'SILVA1' => 'KEBUN SILVA',
            'SILVA2' => 'KEBUN SILVA',
            'SILVA3' => 'KEBUN SILVA'
            ];

            // Tentukan nama kebun berdasarkan company pertama untuk print
            $kebunName = 'KEBUN UNKNOWN';
            foreach($companyNames as $code => $name) {
            if (strpos($primaryCompany, substr($code, 0, -1)) === 0) { // Check prefix (TBL, BNIL, SILVA)
            $kebunName = $name;
            break;
            }
            }

            // Nama bulan dalam bahasa Indonesia
            $monthNames = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
            ];
            @endphp

            <!-- Judul untuk screen -->
            <h2 class="text-xl font-bold text-gray-800 uppercase no-print">
                @if($reportType === 'bulanan')
                RATA-RATA TRASH KEBUN {{ strtoupper($monthNames[$month] ?? $month) }} {{ $year }}
                @else
                LAPORAN {{ strtoupper($reportType) }} DATA TRASH
                @endif
            </h2>

            <!-- Judul untuk print -->
            @if($reportType === 'mingguan')
            <h2 class="text-xl font-bold text-gray-800 uppercase print-only" style="display: none;">
                HASIL ANALISA TRASH {{ $kebunName }}
            </h2>
            @elseif($reportType === 'bulanan')
            <h2 class="text-xl font-bold text-gray-800 uppercase print-only" style="display: none;">
                RATA-RATA TRASH KEBUN {{ strtoupper($monthNames[$month] ?? $month) }} {{ $year }}
            </h2>
            @else
            <h2 class="text-xl font-bold text-gray-800 uppercase print-only" style="display: none;">
                LAPORAN {{ strtoupper($reportType) }} DATA TRASH
            </h2>
            @endif

            <h3 class="text-lg font-semibold text-gray-700">
                SUNGAI BUDI GROUP
            </h3>

            @if($reportType === 'bulanan')
            <p class="text-gray-600 mt-2">
                Periode: {{ $monthNames[$month] ?? $month }} {{ $year }}
            </p>
            @else
            <p class="text-gray-600 mt-2">
                Periode: {{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }}
            </p>
            @endif

            <p class="text-gray-600">
                Company: {{ !empty($actualCompanies) ? implode(', ', $actualCompanies) : (is_array($company) ? implode(', ', $company) : ($company === 'all' ? 'SEMUA COMPANY' : $company)) }}
            </p>
            <p class="text-gray-600 text-sm">
                Dicetak pada: {{ date('d/m/Y H:i:s') }}
            </p>
        </div>

        <div class="px-4">
            @php
            // Fix: Handle both array and string company values for view condition
            $isAllCompanies = ($company === 'all') || (is_array($company) && in_array('all', $company)) || (is_array($company) && count($company) > 1);
            @endphp

            @if($reportType === 'bulanan' && $isAllCompanies)
            <!-- LAPORAN BULANAN - Group by company dengan average -->
            <div class="mb-8">
                <div class="overflow-x-auto rounded-md border-2 border-gray-400">
                    <table class="min-w-full bg-white text-xs border-collapse">
                        <thead class="bg-gray-50">
                            <!-- Header Level 1 -->
                            <tr>
                                <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Asal Tebu</th>
                                <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tonase</th>
                                <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Pucuk (%)</th>
                                <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Daun Gulma (%)</th>
                                <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Sogolan (%)</th>
                                <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Siwilan (%)</th>
                                <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tebu Mati (%)</th>
                                <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tanah dll (%)</th>
                                <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Total Trash</th>
                                <th colspan="2" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Trash Persentase</th>
                                <th colspan="2" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">KG Trash</th>
                            </tr>
                            <!-- Header Level 2 -->
                            <tr>
                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Bruto</th>
                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Netto (Pot 5%)</th>
                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Bruto</th>
                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Netto</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @php
                            $grandTotalPucuk = 0;
                            $grandTotalDaun = 0;
                            $grandTotalSogolan = 0;
                            $grandTotalSiwilan = 0;
                            $grandTotalTebumati = 0;
                            $grandTotalTanah = 0;
                            $grandTotalCompanies = 0;

                            // Group companies by prefix (TBL, BNIL, SILVA)
                            $companyGroups = [];
                            foreach($dataGrouped as $companyCode => $items) {
                            $prefix = '';
                            if (strpos($companyCode, 'TBL') === 0) {
                            $prefix = 'TBL';
                            } elseif (strpos($companyCode, 'BNIL') === 0) {
                            $prefix = 'BNIL';
                            } elseif (strpos($companyCode, 'SILVA') === 0) {
                            $prefix = 'SILVA';
                            } else {
                            $prefix = $companyCode;
                            }

                            if (!isset($companyGroups[$prefix])) {
                            $companyGroups[$prefix] = [];
                            }
                            $companyGroups[$prefix][$companyCode] = $items;
                            }
                            @endphp

                            @php
                            $previousGroupName = null;
                            @endphp

                            @foreach($companyGroups as $groupName => $companies)
                            @php
                            // Add spacing between different groups (TBL, BNIL, SILVA)
                            if ($previousGroupName !== null && $previousGroupName !== $groupName) {
                            echo '<tr class="bg-white">
                                <td colspan="13" class="px-3 py-2 border-0">&nbsp;</td>
                            </tr>';
                            }
                            $previousGroupName = $groupName;

                            $groupTotalPucuk = 0;
                            $groupTotalDaun = 0;
                            $groupTotalSogolan = 0;
                            $groupTotalSiwilan = 0;
                            $groupTotalTebumati = 0;
                            $groupTotalTanah = 0;
                            $groupTotalCompanies = 0;
                            $groupSumTonase = 0; // Initialize group tonase sum
                            @endphp

                            <!-- Group Header -->
                            <tr class="bg-blue-50">
                                <td colspan="13" class="px-3 py-2 text-sm font-bold text-gray-800 border-2 border-gray-400">
                                    {{ $groupName }}
                                </td>
                            </tr>

                            @foreach($companies as $companyCode => $items)
                            @php
                            // Group by jenis within this company
                            $companyByJenis = [];
                            foreach($items as $item) {
                            $jenis = $item['jenis'];
                            if (!isset($companyByJenis[$jenis])) {
                            $companyByJenis[$jenis] = [];
                            }
                            $companyByJenis[$jenis][] = $item;
                            }

                            // Sort jenis: manual first, then mesin
                            $sortedJenis = [];
                            if (isset($companyByJenis['manual'])) {
                            $sortedJenis['manual'] = $companyByJenis['manual'];
                            }
                            if (isset($companyByJenis['mesin'])) {
                            $sortedJenis['mesin'] = $companyByJenis['mesin'];
                            }
                            @endphp

                            @foreach($sortedJenis as $jenis => $jenisItems)
                            @php
                            // Calculate averages for trash percentages and SUM for tonase
                            $totalPucuk = 0;
                            $totalDaun = 0;
                            $totalSogolan = 0;
                            $totalSiwilan = 0;
                            $totalTebumati = 0;
                            $totalTanah = 0;
                            $sumTonase = 0; // SUM untuk tonase, bukan average
                            $count = count($jenisItems);

                            foreach($jenisItems as $item) {
                            $totalPucuk += $item['pucuk'];
                            $totalDaun += $item['daungulma'];
                            $totalSogolan += $item['sogolan'];
                            $totalSiwilan += $item['siwilan'];
                            $totalTebumati += $item['tebumati'];
                            $totalTanah += $item['tanahetc'];
                            $sumTonase += $item['tonase_netto']; // SUM tonase
                            }

                            $avgPucuk = $count > 0 ? $totalPucuk / $count : 0;
                            $avgDaun = $count > 0 ? $totalDaun / $count : 0;
                            $avgSogolan = $count > 0 ? $totalSogolan / $count : 0;
                            $avgSiwilan = $count > 0 ? $totalSiwilan / $count : 0;
                            $avgTebumati = $count > 0 ? $totalTebumati / $count : 0;
                            $avgTanah = $count > 0 ? $totalTanah / $count : 0;

                            // Add to group totals
                            $groupTotalPucuk += $avgPucuk;
                            $groupTotalDaun += $avgDaun;
                            $groupTotalSogolan += $avgSogolan;
                            $groupTotalSiwilan += $avgSiwilan;
                            $groupTotalTebumati += $avgTebumati;
                            $groupTotalTanah += $avgTanah;
                            $groupTotalCompanies++;

                            $totaltrash = $avgPucuk + $avgDaun + $avgSogolan + $avgSiwilan + $avgTebumati + $avgTanah;

                            // Add tonase to group total (akan disum di level group)
                            if (!isset($groupSumTonase)) {
                            $groupSumTonase = 0;
                            }
                            $groupSumTonase += $sumTonase;
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-sm text-center border-2 border-gray-300">
                                    {{ $companyCode }} ({{ ucfirst($jenis) }})
                                </td>
                                <td class="px-3 py-2 text-sm text-center border-2 border-gray-300">
                                    {{ number_format($sumTonase, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">{{ number_format($avgPucuk, 3, ',', '.') }}</td>
                                <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">{{ number_format($avgDaun, 3, ',', '.') }}</td>
                                <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">{{ number_format($avgSogolan, 3, ',', '.') }}</td>
                                <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">{{ number_format($avgSiwilan, 3, ',', '.') }}</td>
                                <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">{{ number_format($avgTebumati, 3, ',', '.') }}</td>
                                <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">{{ number_format($avgTanah, 3, ',', '.') }}</td>
                                <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">{{ number_format($totaltrash, 3, ',', '.') }}</td>
                                <!-- Trash Persentase - Bruto -->
                                <td class="px-2 py-2 text-sm text-right border-2 border-gray-300">
                                    @php
                                    $totalAvgTrash = $avgPucuk + $avgDaun + $avgSogolan + $avgSiwilan + $avgTebumati + $avgTanah;
                                    @endphp
                                    {{ number_format($totalAvgTrash, 3, ',', '.') }}
                                </td>
                                <!-- Trash Persentase - Netto (Pot 5%) -->
                                <td class="px-2 py-2 text-sm text-right border-2 border-gray-300">
                                    @php
                                    $nettoTrash = $totalAvgTrash - 5; // Potong 5%
                                    $nettoTrash = $nettoTrash < 0 ? 0 : $nettoTrash;
                                        @endphp
                                        {{ number_format($nettoTrash, 3, ',', '.') }}
                                        </td>
                                        <!-- KG Trash - Bruto -->
                                <td class="px-2 py-2 text-sm text-right border-2 border-gray-300">
                                    @php
                                    $kgTrashBruto = ( $totalAvgTrash / 100) * $sumTonase ;
                                    @endphp
                                    {{ number_format($kgTrashBruto, 0, ',', '.') }}
                                </td>
                                <!-- KG Trash - Netto -->
                                <td class="px-2 py-2 text-sm text-right border-2 border-gray-300">
                                    @php
                                    $kgTrashNetto = ($nettoTrash / 100) * $sumTonase ;
                                    @endphp
                                    {{ number_format($kgTrashNetto, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                            @endforeach

                            <!-- Group Total Row -->
                            <tr class="bg-gray-100 font-semibold">
                                <td class="px-3 py-2 text-sm text-center border-2 border-gray-300">
                                    <strong>TOTAL {{ $groupName }}</strong>
                                </td>
                                <td class="px-3 py-2 text-sm text-center border-2 border-gray-300">
                                    <strong>{{ number_format($groupSumTonase, 0, ',', '.') }}</strong>
                                </td>
                                <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">
                                    <strong>{{ number_format($groupTotalCompanies > 0 ? $groupTotalPucuk / $groupTotalCompanies : 0, 3, ',', '.') }}</strong>
                                </td>
                                <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">
                                    <strong>{{ number_format($groupTotalCompanies > 0 ? $groupTotalDaun / $groupTotalCompanies : 0, 3, ',', '.') }}</strong>
                                </td>
                                <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">
                                    <strong>{{ number_format($groupTotalCompanies > 0 ? $groupTotalSogolan / $groupTotalCompanies : 0, 3, ',', '.') }}</strong>
                                </td>
                                <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">
                                    <strong>{{ number_format($groupTotalCompanies > 0 ? $groupTotalSiwilan / $groupTotalCompanies : 0, 3, ',', '.') }}</strong>
                                </td>
                                <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">
                                    <strong>{{ number_format($groupTotalCompanies > 0 ? $groupTotalTebumati / $groupTotalCompanies : 0, 3, ',', '.') }}</strong>
                                </td>
                                <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">
                                    <strong>{{ number_format($groupTotalCompanies > 0 ? $groupTotalTanah / $groupTotalCompanies : 0, 3, ',', '.') }}</strong>
                                </td>
                                <!-- KOLOM TOTAL TRASH - CLEANED UP -->
                                <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">
                                    @php
                                    // Calculate group averages
                                    $groupAvgPucuk = $groupTotalCompanies > 0 ? $groupTotalPucuk / $groupTotalCompanies : 0;
                                    $groupAvgDaun = $groupTotalCompanies > 0 ? $groupTotalDaun / $groupTotalCompanies : 0;
                                    $groupAvgSogolan = $groupTotalCompanies > 0 ? $groupTotalSogolan / $groupTotalCompanies : 0;
                                    $groupAvgSiwilan = $groupTotalCompanies > 0 ? $groupTotalSiwilan / $groupTotalCompanies : 0;
                                    $groupAvgTebumati = $groupTotalCompanies > 0 ? $groupTotalTebumati / $groupTotalCompanies : 0;
                                    $groupAvgTanah = $groupTotalCompanies > 0 ? $groupTotalTanah / $groupTotalCompanies : 0;

                                    // Calculate group total trash percentage
                                    $groupTotalTrash = $groupAvgPucuk + $groupAvgDaun + $groupAvgSogolan + $groupAvgSiwilan + $groupAvgTebumati + $groupAvgTanah;

                                    // Calculate group KG Trash
                                    $groupKgTrashBruto = ($groupSumTonase * $groupTotalTrash) / 100;
                                    $groupNettoTrash = $groupTotalTrash - 5; // Pot 5%
                                    $groupNettoTrash = $groupNettoTrash < 0 ? 0 : $groupNettoTrash;
                                        $groupKgTrashNetto=($groupSumTonase * $groupNettoTrash) / 100;

                                        // Calculate group trash persentase (KG/Tonase * 100)
                                        $trashpresentasebruto=$groupSumTonase> 0 ? ($groupKgTrashBruto / $groupSumTonase) * 100 : 0;
                                        $trashpresentasenetto = $groupSumTonase > 0 ? ($groupKgTrashNetto / $groupSumTonase) * 100 : 0;
                                        @endphp
                                        <strong>{{ number_format($groupTotalTrash, 3, ',', '.') }}</strong>
                                </td>
                                <!-- Group Total - Trash Persentase Bruto -->
                                <td class="px-2 py-2 text-sm text-right border-2 border-gray-300">
                                    <strong>{{ number_format($trashpresentasebruto, 3, ',', '.') }}</strong>
                                </td>
                                <!-- Group Total - Trash Persentase Netto -->
                                <td class="px-2 py-2 text-sm text-right border-2 border-gray-300">
                                    <strong>{{ number_format($trashpresentasenetto, 3, ',', '.') }}</strong>
                                </td>
                                <!-- Group Total - KG Trash Bruto -->
                                <td class="px-2 py-2 text-sm text-right border-2 border-gray-300">
                                    <strong>{{ number_format($groupKgTrashBruto, 0, ',', '.') }}</strong>
                                </td>
                                <!-- Group Total - KG Trash Netto -->
                                <td class="px-2 py-2 text-sm text-right border-2 border-gray-300">
                                    <strong>{{ number_format($groupKgTrashNetto, 0, ',', '.') }}</strong>
                                </td>
                            </tr>


                            @php
                            // Add group totals to grand totals
                            $grandTotalPucuk += ($groupTotalCompanies > 0 ? $groupTotalPucuk / $groupTotalCompanies : 0);
                            $grandTotalDaun += ($groupTotalCompanies > 0 ? $groupTotalDaun / $groupTotalCompanies : 0);
                            $grandTotalSogolan += ($groupTotalCompanies > 0 ? $groupTotalSogolan / $groupTotalCompanies : 0);
                            $grandTotalSiwilan += ($groupTotalCompanies > 0 ? $groupTotalSiwilan / $groupTotalCompanies : 0);
                            $grandTotalTebumati += ($groupTotalCompanies > 0 ? $groupTotalTebumati / $groupTotalCompanies : 0);
                            $grandTotalTanah += ($groupTotalCompanies > 0 ? $groupTotalTanah / $groupTotalCompanies : 0);
                            $grandTotalCompanies++;

                            // Add group tonase to grand total
                            if (!isset($grandSumTonase)) {
                            $grandSumTonase = 0;
                            }
                            $grandSumTonase += $groupSumTonase;
                            @endphp
                            @endforeach


                        </tbody>
                    </table>
                </div>
            </div>

            @elseif($reportType === 'harian' && $isAllCompanies)
            <!-- LAPORAN HARIAN - Dipisah per tanggal -->
            @forelse($dataGrouped as $tanggal => $items)
            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4 bg-blue-100 p-2 rounded">
                    TANGGAL: {{ date('d/m/Y', strtotime($tanggal)) }}
                </h3>

                <div class="overflow-x-auto rounded-md border-2 border-gray-400">
                    <table class="min-w-full bg-white text-xs border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">No</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Jenis</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Surat Jalan</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">No. Polisi</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Asal Tebu</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Plot</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Kontraktor</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Sub Kontraktor</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Pucuk</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Daun Gulma</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Sogolan</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Siwilan</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tebu Mati</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tanah dll</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Total</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Toleransi</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Netto</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach($items as $index => $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-2 py-2 text-xs text-center border-2 border-gray-300">{{ $index + 1 }}</td>
                                <td class="px-2 py-2 text-xs text-center border-2 border-gray-300">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ ($item['jenis'] ?? '') === 'manual' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst($item['jenis'] ?? '') }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 text-xs text-center border-2 border-gray-300">{{ $item['suratjalanno'] ?? '' }}</td>
                                <td class="px-2 py-2 text-xs text-center border-2 border-gray-300">{{ $item['nomorpolisi'] ?? '' }}</td>
                                <td class="px-2 py-2 text-xs text-center border-2 border-gray-300">{{ $item['companycode'] ?? '' }}</td>
                                <td class="px-2 py-2 text-xs text-center border-2 border-gray-300">{{ $item['plot'] ?? '' }}</td>
                                <td class="px-2 py-2 text-xs text-center border-2 border-gray-300">{{ $item['namakontraktor'] ?? '-' }}</td>
                                <td class="px-2 py-2 text-xs text-center border-2 border-gray-300">{{ $item['namasubkontraktor'] ?? '-' }}</td>
                                <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['pucuk'] ?? 0, 3, ',', '.') }}</td>
                                <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['daungulma'] ?? 0, 3, ',', '.') }}</td>
                                <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['sogolan'] ?? 0, 3, ',', '.') }}</td>
                                <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['siwilan'] ?? 0, 3, ',', '.') }}</td>
                                <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['tebumati'] ?? 0, 3, ',', '.') }}</td>
                                <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['tanahetc'] ?? 0, 3, ',', '.') }}</td>
                                <td class="px-2 py-2 text-xs text-right font-semibold border-2 border-gray-300">{{ number_format($item['total'] ?? 0, 3, ',', '.') }}</td>
                                <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['toleransi'] ?? 0, 3, ',', '.') }}</td>
                                <td class="px-2 py-2 text-xs text-right font-semibold border-2 border-gray-300">{{ number_format($item['nettotrash'] ?? 0, 3, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @empty
            <div class="text-center py-8">
                <p class="text-gray-500">Tidak ada data untuk ditampilkan</p>
            </div>
            @endforelse

            @elseif($reportType === 'mingguan')
            <!-- LAPORAN MINGGUAN - Dipisah per jenis dan company -->
            @forelse($dataGrouped as $jenis => $companies)
            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4 bg-{{ $jenis === 'manual' ? 'green' : 'blue' }}-100 p-2 rounded">
                    JENIS: {{ strtoupper($jenis) }}
                </h3>

                @foreach($companies as $companyCode => $items)
                <div class="mb-6">
                    <h4 class="text-lg font-bold text-gray-700 mb-2 bg-gray-100 p-3 rounded">
                        Company: {{ $companyCode }}
                    </h4>

                    <div class="overflow-x-auto rounded-md border-2 border-gray-400">
                        <table class="min-w-full bg-white text-xs border-collapse">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tanggal</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">No</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Surat Jalan</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Kontraktor</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Sub Kontraktor</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Pucuk</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Daun Gulma</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Sogolan</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Siwilan</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tebu Mati</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tanah dll</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Total</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Toleransi</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Netto</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @foreach($items as $index => $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-2 py-2 text-xs border-2 border-gray-300">{{ date('d/m/Y', strtotime($item['tanggalangkut'] ?? '')) }}</td>
                                    <td class="px-2 py-2 text-xs border-2 border-gray-300">{{ $index + 1 }}</td>
                                    <td class="px-2 py-2 text-xs border-2 border-gray-300">{{ $item['suratjalanno'] ?? '' }}</td>
                                    <td class="px-2 py-2 text-xs border-2 border-gray-300">{{ $item['namakontraktor'] ?? '-' }}</td>
                                    <td class="px-2 py-2 text-xs border-2 border-gray-300">{{ $item['namasubkontraktor'] ?? '-' }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['pucuk'] ?? 0, 3, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['daungulma'] ?? 0, 3, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['sogolan'] ?? 0, 3, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['siwilan'] ?? 0, 3, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['tebumati'] ?? 0, 3, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['tanahetc'] ?? 0, 3, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right font-semibold border-2 border-gray-300">{{ number_format($item['total'] ?? 0, 3, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['toleransi'] ?? 0, 3, ',', '.') }}%</td>
                                    <td class="px-2 py-2 text-xs text-right font-semibold border-2 border-gray-300">{{ number_format($item['nettotrash'] ?? 0, 3, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>
            @empty
            <div class="text-center py-8">
                <p class="text-gray-500">Tidak ada data untuk ditampilkan</p>
            </div>
            @endforelse
            @endif
        </div>

        <!-- Signature Section - Khusus untuk Mingguan dan hanya saat Print -->
        @if($reportType === 'mingguan')
        <div class="print-only mt-8 px-4" style="display: none;">
            <div class="flex justify-between items-start mt-12">
                <!-- Tanda Tangan 1: Disetujui Oleh -->
                <div class="text-center flex-1">
                    <p class="text-sm font-semibold mb-2 mt-5">DISETUJUI OLEH</p>
                    <div class="h-20 mb-2"></div> <!-- Space untuk tanda tangan -->
                    <div class="inline-block">
                        <p class="text-sm font-bold border-b-2 border-black px-4 pb-1">Tandy</p>
                        <p class="text-xs mt-1">General Manager</p>
                    </div>
                </div>

                <!-- Tanda Tangan 2: Diketahui Oleh -->
                <div class="text-center flex-1">
                    <p class="text-sm font-semibold mb-2 mt-5">DIKETAHUI OLEH</p>
                    <div class="h-20 mb-2"></div> <!-- Space untuk tanda tangan -->
                    <div class="inline-block">
                        <p class="text-sm font-bold border-b-2 border-black px-4 pb-1">Tyas Rudito M</p>
                        <p class="text-xs mt-1">Kabag Laborat</p>
                    </div>
                </div>

                <!-- Tanda Tangan 3: Disiapkan Oleh -->
                <div class="text-center flex-1">
                    <p class="text-sm font-semibold mb-3 mt-0">Terbanggi Besar, {{ date('d-M-Y') }}</p>
                    <p class="text-sm font-semibold mb-2">DISIAPKAN OLEH</p>
                    <div class="h-20 mb-2"></div> <!-- Space untuk tanda tangan -->
                    <div class="inline-block">
                        <p class="text-sm font-bold border-b-2 border-black px-4 pb-1">{{ Auth::user()->userid ?? 'Admin' }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>

    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
                font-size: 10px;
            }

            .no-print {
                display: none !important;
            }

            .print-only {
                display: block !important;
            }

            table {
                font-size: 9px;
                border-collapse: collapse !important;
            }

            th,
            td {
                padding: 3px !important;
                border: 2px solid #333 !important;
                /* Lebih tebel untuk print */
            }

            /* Khusus untuk print, border lebih jelas */
            .border-2 {
                border-width: 2px !important;
                border-color: #333 !important;
            }
        }
    </style>

</x-layout>