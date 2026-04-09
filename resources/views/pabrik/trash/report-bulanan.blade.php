<x-layout>
    <x-slot:title>Laporan Trash Bulanan</x-slot:title>
    <x-slot:navbar>Pabrik</x-slot:navbar>
    <x-slot:nav>Laporan Trash Bulanan</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md">
        {{-- Print Controls --}}
        <div class="no-print px-4 py-4 border-b border-gray-200 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Laporan Trash Bulanan</h1>
            <div class="flex gap-2">
                <button onclick="window.print()"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4a1 1 0 00-1-1H9a1 1 0 001 1v4a1 1 0 001 1zm3-5h2a2 2 0 002-2v-3a2 2 0 00-2-2H5a2 2 0 00-2 2v3a2 2 0 002 2h2"></path>
                    </svg>
                    Print
                </button>
                <form method="POST" action="{{ route('pabrik.trash.report.export-excel') }}" style="display:inline">
                    @csrf
                    <input type="hidden" name="report_type" value="bulanan">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <button type="submit"
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a1 1 0 001 1h16a1 1 0 001-1v-3"></path>
                        </svg>
                        Export Excel
                    </button>
                </form>
                <button onclick="window.close()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Tutup
                </button>
            </div>
        </div>

        {{-- Report Header --}}
        @php
        $monthNames = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        $displayCompany = !empty($actualCompanies)
        ? implode(', ', $actualCompanies)
        : (is_array($company)
        ? implode(', ', $company)
        : ($company === 'all' ? 'SEMUA COMPANY' : $company));
        @endphp

        <div class="text-center mb-6 px-4">
            <h2 class="text-xl font-bold text-gray-800 uppercase">
                RATA-RATA TRASH KEBUN {{ strtoupper($monthNames[$month] ?? $month) }} {{ $year }}
            </h2>
            <h3 class="text-lg font-semibold text-gray-700">SUNGAI BUDI GROUP</h3>
            <p class="text-gray-600 mt-2">Periode: {{ $monthNames[$month] ?? $month }} {{ $year }}</p>
            <p class="text-gray-600">Company: {{ $displayCompany }}</p>
            <p class="text-gray-600 text-sm">Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
        </div>

        {{-- TOP TABLES: Main Data + KG Breakdown --}}
        <div class="px-4 overflow-x-auto">
            <div class="grid grid-cols-2 gap-2 mb-8">
                {{-- TOP LEFT: Main Table --}}
                <div class="min-w-0">
                    <div class="rounded-md border-2 border-gray-400">
                        <table class="uniform-table">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="uniform-header w-18">Asal Tebu</th>
                                    <th rowspan="2" class="uniform-header w-14">Tonase</th>
                                    <th rowspan="2" class="uniform-header w-12">Pucuk (%)</th>
                                    <th rowspan="2" class="uniform-header w-14">Daun Gulma (%)</th>
                                    <th rowspan="2" class="uniform-header w-12">Sogolan (%)</th>
                                    <th rowspan="2" class="uniform-header w-12">Siwilan (%)</th>
                                    <th rowspan="2" class="uniform-header w-14">Tebu Mati (%)</th>
                                    <th rowspan="2" class="uniform-header w-14">Tanah dll (%)</th>
                                    <th rowspan="2" class="uniform-header w-14">Total Trash</th>
                                    <th colspan="2" class="uniform-header-sub">Trash Persentase</th>
                                    <th colspan="2" class="uniform-header-sub">KG Trash</th>
                                </tr>
                                <tr>
                                    <th class="uniform-header-sub">Bruto</th>
                                    <th class="uniform-header-sub">Netto (Pot 5%)</th>
                                    <th class="uniform-header-sub">Bruto</th>
                                    <th class="uniform-header-sub">Netto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                // Group companies by prefix (TBL, BNIL, SILVA)
                                $companyGroups = [];
                                foreach($dataGrouped as $companyCode => $items) {
                                $prefix = '';
                                if (strpos($companyCode, 'TBL') === 0) {
                                $prefix = 'TBL';
                                } elseif (strpos($companyCode, 'BNL') === 0) {
                                $prefix = 'BNL';
                                } elseif (strpos($companyCode, 'SIL') === 0) {
                                $prefix = 'SIL';
                                } else {
                                $prefix = $companyCode;
                                }

                                if (!isset($companyGroups[$prefix])) {
                                $companyGroups[$prefix] = [];
                                }
                                $companyGroups[$prefix][$companyCode] = $items;
                                }

                                $previousGroupName = null;
                                @endphp

                                @foreach($companyGroups as $groupName => $companies)
                                {{-- Add spacing row between groups --}}
                                @if($previousGroupName !== null && $previousGroupName !== $groupName)
                                <tr class="bg-white" style="height: 4px;">
                                    <td colspan="13" class="px-2 py-0 border-0">&nbsp;</td>
                                </tr>
                                @endif

                                @php
                                $previousGroupName = $groupName;

                                $groupTotalPucuk = 0;
                                $groupTotalDaun = 0;
                                $groupTotalSogolan = 0;
                                $groupTotalSiwilan = 0;
                                $groupTotalTebumati = 0;
                                $groupTotalTanah = 0;
                                $groupTotalCompanies = 0;
                                $groupSumTonase = 0;
                                @endphp

                                {{-- Individual company rows --}}
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
                                $sumTonase = 0;
                                $count = count($jenisItems);

                                foreach($jenisItems as $item) {
                                $totalPucuk += $item['pucuk'];
                                $totalDaun += $item['daungulma'];
                                $totalSogolan += $item['sogolan'];
                                $totalSiwilan += $item['siwilan'];
                                $totalTebumati += $item['tebumati'];
                                $totalTanah += $item['tanahetc'];
                                $sumTonase += $item['tonase_netto'];
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
                                $groupSumTonase += $sumTonase;

                                $totaltrash = $avgPucuk + $avgDaun + $avgSogolan + $avgSiwilan + $avgTebumati + $avgTanah;

                                $nettoTrash = $totaltrash - 5;
                                $nettoTrash = $nettoTrash < 0 ? 0 : $nettoTrash;

                                    $kgTrashBruto=($totaltrash / 100) * $sumTonase;
                                    $kgTrashNetto=($nettoTrash / 100) * $sumTonase;
                                    @endphp

                                    <tr class="hover:bg-gray-50">
                                    <td class="uniform-cell text-center">
                                        {{ $companyCode }} ({{ ucfirst($jenis) }})
                                    </td>
                                    <td class="uniform-cell text-center">
                                        {{ number_format($sumTonase, 0, ',', '.') }}
                                    </td>
                                    <td class="uniform-cell text-right">{{ number_format($avgPucuk, 2, ',', '.') }}</td>
                                    <td class="uniform-cell text-right">{{ number_format($avgDaun, 2, ',', '.') }}</td>
                                    <td class="uniform-cell text-right">{{ number_format($avgSogolan, 2, ',', '.') }}</td>
                                    <td class="uniform-cell text-right">{{ number_format($avgSiwilan, 2, ',', '.') }}</td>
                                    <td class="uniform-cell text-right">{{ number_format($avgTebumati, 2, ',', '.') }}</td>
                                    <td class="uniform-cell text-right">{{ number_format($avgTanah, 2, ',', '.') }}</td>
                                    <td class="uniform-cell text-right">{{ number_format($totaltrash, 2, ',', '.') }}</td>
                                    <td class="uniform-cell text-right">{{ number_format($totaltrash, 2, ',', '.') }}</td>
                                    <td class="uniform-cell text-right">{{ number_format($nettoTrash, 2, ',', '.') }}</td>
                                    <td class="uniform-cell text-right">{{ number_format($kgTrashBruto, 0, ',', '.') }}</td>
                                    <td class="uniform-cell text-right">{{ number_format($kgTrashNetto, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                    @endforeach

                                    @php
                                    // Calculate group averages
                                    $groupAvgPucuk = $groupTotalCompanies > 0 ? $groupTotalPucuk / $groupTotalCompanies : 0;
                                    $groupAvgDaun = $groupTotalCompanies > 0 ? $groupTotalDaun / $groupTotalCompanies : 0;
                                    $groupAvgSogolan = $groupTotalCompanies > 0 ? $groupTotalSogolan / $groupTotalCompanies : 0;
                                    $groupAvgSiwilan = $groupTotalCompanies > 0 ? $groupTotalSiwilan / $groupTotalCompanies : 0;
                                    $groupAvgTebumati = $groupTotalCompanies > 0 ? $groupTotalTebumati / $groupTotalCompanies : 0;
                                    $groupAvgTanah = $groupTotalCompanies > 0 ? $groupTotalTanah / $groupTotalCompanies : 0;

                                    // Calculate group total trash
                                    $groupTotalTrash = $groupAvgPucuk + $groupAvgDaun + $groupAvgSogolan + $groupAvgSiwilan + $groupAvgTebumati + $groupAvgTanah;

                                    // Calculate group KG Trash
                                    $groupKgTrashBruto = ($groupSumTonase * $groupTotalTrash) / 100;
                                    $groupNettoTrash = $groupTotalTrash - 5;
                                    $groupNettoTrash = $groupNettoTrash < 0 ? 0 : $groupNettoTrash;
                                        $groupKgTrashNetto=($groupSumTonase * $groupNettoTrash) / 100;

                                        $trashPersentaseBruto=$groupSumTonase> 0 ? ($groupKgTrashBruto / $groupSumTonase) * 100 : 0;
                                        $trashPersentaseNetto = $groupSumTonase > 0 ? ($groupKgTrashNetto / $groupSumTonase) * 100 : 0;
                                        @endphp

                                        {{-- Group total row --}}
                                        <tr class="bg-gray-100 font-semibold">
                                            <td class="uniform-cell-group text-center">
                                                <strong>{{ $groupName }}</strong>
                                            </td>
                                            <td class="uniform-cell-group text-center">
                                                <strong>{{ number_format($groupSumTonase, 0, ',', '.') }}</strong>
                                            </td>
                                            <td class="uniform-cell-group text-right">
                                                <strong>{{ number_format($groupAvgPucuk, 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="uniform-cell-group text-right">
                                                <strong>{{ number_format($groupAvgDaun, 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="uniform-cell-group text-right">
                                                <strong>{{ number_format($groupAvgSogolan, 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="uniform-cell-group text-right">
                                                <strong>{{ number_format($groupAvgSiwilan, 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="uniform-cell-group text-right">
                                                <strong>{{ number_format($groupAvgTebumati, 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="uniform-cell-group text-right">
                                                <strong>{{ number_format($groupAvgTanah, 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="uniform-cell-group text-right">
                                                <strong>{{ number_format($groupTotalTrash, 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="uniform-cell-group text-right">
                                                <strong>{{ number_format($trashPersentaseBruto, 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="uniform-cell-group text-right">
                                                <strong>{{ number_format($trashPersentaseNetto, 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="uniform-cell-group text-right">
                                                <strong>{{ number_format($groupKgTrashBruto, 0, ',', '.') }}</strong>
                                            </td>
                                            <td class="uniform-cell-group text-right">
                                                <strong>{{ number_format($groupKgTrashNetto, 0, ',', '.') }}</strong>
                                            </td>
                                        </tr>
                                        @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TOP RIGHT: KG Breakdown Table --}}
                <div class="min-w-0">
                    <div class="rounded-md border-2 border-gray-400">
                        <table class="uniform-table">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="uniform-header w-16">Pucuk<br>(KG)</th>
                                    <th rowspan="2" class="uniform-header w-14">Daun<br>(KG)</th>
                                    <th rowspan="2" class="uniform-header w-12">Sogolan<br>(KG)</th>
                                    <th rowspan="2" class="uniform-header w-14">Siwilan<br>(KG)</th>
                                    <th rowspan="2" class="uniform-header w-16">Tebu Mati<br>(KG)</th>
                                    <th rowspan="2" class="uniform-header w-14">Tanah dll<br>(KG)</th>
                                </tr>
                                <tr>
                                    {{-- Row kedua header kosong untuk alignment --}}
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $previousGroupName2 = null;
                                // Hitung KG untuk setiap group dan company
                                $kgBreakdownData = [];
                                @endphp

                                @foreach($companyGroups as $groupName => $companies)
                                {{-- Spacing row --}}
                                @if($previousGroupName2 !== null)
                                <tr class="bg-white" style="height: 4px;">
                                    <td colspan="6" class="px-2 py-0 border-0">&nbsp;</td>
                                </tr>
                                @endif

                                @php
                                $previousGroupName2 = $groupName;
                                $groupKgPucuk = 0;
                                $groupKgDaun = 0;
                                $groupKgSogolan = 0;
                                $groupKgSiwilan = 0;
                                $groupKgTebumati = 0;
                                $groupKgTanah = 0;
                                @endphp

                                @foreach($companies as $companyCode => $items)
                                @php
                                $companyByJenis = [];
                                foreach($items as $item) {
                                if (!isset($companyByJenis[$item['jenis']])) {
                                $companyByJenis[$item['jenis']] = [];
                                }
                                $companyByJenis[$item['jenis']][] = $item;
                                }

                                $sortedJenis = [];
                                if (isset($companyByJenis['manual'])) $sortedJenis['manual'] = $companyByJenis['manual'];
                                if (isset($companyByJenis['mesin'])) $sortedJenis['mesin'] = $companyByJenis['mesin'];
                                @endphp

                                @foreach($sortedJenis as $jenis => $jenisItems)
                                @php
                                $count = count($jenisItems);
                                $sumTonase = 0;
                                $sumPucuk = $sumDaun = $sumSogolan = $sumSiwilan = $sumTebumati = $sumTanah = 0;

                                foreach($jenisItems as $item) {
                                $sumTonase += floatval($item['tonase_netto'] ?? 0);
                                $sumPucuk += floatval($item['pucuk'] ?? 0);
                                $sumDaun += floatval($item['daungulma'] ?? 0);
                                $sumSogolan += floatval($item['sogolan'] ?? 0);
                                $sumSiwilan += floatval($item['siwilan'] ?? 0);
                                $sumTebumati += floatval($item['tebumati'] ?? 0);
                                $sumTanah += floatval($item['tanahetc'] ?? 0);
                                }

                                if ($count > 0) {
                                $avgPucuk = $sumPucuk / $count;
                                $avgDaun = $sumDaun / $count;
                                $avgSogolan = $sumSogolan / $count;
                                $avgSiwilan = $sumSiwilan / $count;
                                $avgTebumati = $sumTebumati / $count;
                                $avgTanah = $sumTanah / $count;
                                } else {
                                $avgPucuk = $avgDaun = $avgSogolan = $avgSiwilan = $avgTebumati = $avgTanah = 0;
                                }

                                $kgPucuk = ($sumTonase * $avgPucuk) / 100;
                                $kgDaun = ($sumTonase * $avgDaun) / 100;
                                $kgSogolan = ($sumTonase * $avgSogolan) / 100;
                                $kgSiwilan = ($sumTonase * $avgSiwilan) / 100;
                                $kgTebumati = ($sumTonase * $avgTebumati) / 100;
                                $kgTanah = ($sumTonase * $avgTanah) / 100;

                                $groupKgPucuk += $kgPucuk;
                                $groupKgDaun += $kgDaun;
                                $groupKgSogolan += $kgSogolan;
                                $groupKgSiwilan += $kgSiwilan;
                                $groupKgTebumati += $kgTebumati;
                                $groupKgTanah += $kgTanah;
                                @endphp

                                <tr class="hover:bg-gray-50">
                                    <td class="uniform-cell text-right">{{ number_format($kgPucuk, 0, ',', '.') }}</td>
                                    <td class="uniform-cell text-right">{{ number_format($kgDaun, 0, ',', '.') }}</td>
                                    <td class="uniform-cell text-right">{{ number_format($kgSogolan, 0, ',', '.') }}</td>
                                    <td class="uniform-cell text-right">{{ number_format($kgSiwilan, 0, ',', '.') }}</td>
                                    <td class="uniform-cell text-right">{{ number_format($kgTebumati, 0, ',', '.') }}</td>
                                    <td class="uniform-cell text-right">{{ number_format($kgTanah, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                                @endforeach

                                {{-- Group total row --}}
                                <tr class="bg-gray-100 font-semibold">
                                    <td class="uniform-cell-group text-right">
                                        <strong>{{ number_format($groupKgPucuk, 0, ',', '.') }}</strong>
                                    </td>
                                    <td class="uniform-cell-group text-right">
                                        <strong>{{ number_format($groupKgDaun, 0, ',', '.') }}</strong>
                                    </td>
                                    <td class="uniform-cell-group text-right">
                                        <strong>{{ number_format($groupKgSogolan, 0, ',', '.') }}</strong>
                                    </td>
                                    <td class="uniform-cell-group text-right">
                                        <strong>{{ number_format($groupKgSiwilan, 0, ',', '.') }}</strong>
                                    </td>
                                    <td class="uniform-cell-group text-right">
                                        <strong>{{ number_format($groupKgTebumati, 0, ',', '.') }}</strong>
                                    </td>
                                    <td class="uniform-cell-group text-right">
                                        <strong>{{ number_format($groupKgTanah, 0, ',', '.') }}</strong>
                                    </td>
                                </tr>

                                @php
                                // Store untuk bottom table
                                if (!isset($kgBreakdownData[$groupName])) {
                                $kgBreakdownData[$groupName] = [
                                'pucuk' => 0, 'daun' => 0, 'sogolan' => 0,
                                'siwilan' => 0, 'tebumati' => 0, 'tanah' => 0
                                ];
                                }
                                $kgBreakdownData[$groupName]['pucuk'] += $groupKgPucuk;
                                $kgBreakdownData[$groupName]['daun'] += $groupKgDaun;
                                $kgBreakdownData[$groupName]['sogolan'] += $groupKgSogolan;
                                $kgBreakdownData[$groupName]['siwilan'] += $groupKgSiwilan;
                                $kgBreakdownData[$groupName]['tebumati'] += $groupKgTebumati;
                                $kgBreakdownData[$groupName]['tanah'] += $groupKgTanah;
                                @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- BOTTOM TABLES: Summary + Total KG --}}
        <div class="px-4 overflow-x-auto">
            <div class="mt-8 mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-3 text-center">REKAPITULASI PER GROUP COMPANY</h3>

                <div class="grid grid-cols-2 gap-2">
                    {{-- BOTTOM LEFT: Summary Table --}}
                    <div class="min-w-0">
                        <div class="rounded-md border-2 border-gray-400">
                            <table class="uniform-table">
                                <thead>
                                    <tr>
                                        <th class="uniform-header w-20">Keterangan</th>
                                        <th class="uniform-header w-14">Total Tonase</th>
                                        <th class="uniform-header w-12">Pucuk (%)</th>
                                        <th class="uniform-header w-14">Daun (%)</th>
                                        <th class="uniform-header w-12">Sogolan (%)</th>
                                        <th class="uniform-header w-12">Siwilan (%)</th>
                                        <th class="uniform-header w-14">Tebu Mati (%)</th>
                                        <th class="uniform-header w-14">Tanah dll (%)</th>
                                        <th class="uniform-header w-14">Total Trash (%)</th>
                                        <th class="uniform-header w-14">Trash Bruto (%)</th>
                                        <th class="uniform-header w-14">Trash Netto (%)</th>
                                        <th class="uniform-header w-16">KG Trash Bruto</th>
                                        <th class="uniform-header w-16">KG Trash Netto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    // Calculate grand totals nasional
                                    $grandTotalTonaseAll = 0;
                                    $grandTotalPucukAll = 0;
                                    $grandTotalDaunAll = 0;
                                    $grandTotalSogolanAll = 0;
                                    $grandTotalSiwilanAll = 0;
                                    $grandTotalTebumatiAll = 0;
                                    $grandTotalTanahAll = 0;
                                    $grandKgTrashBrutoAll = 0;
                                    $grandKgTrashNettoAll = 0;
                                    $grandCountGroups = 0;

                                    $grandKgPucukAll = 0;
                                    $grandKgDaunAll = 0;
                                    $grandKgSogolanAll = 0;
                                    $grandKgSiwilanAll = 0;
                                    $grandKgTebumatiAll = 0;
                                    $grandKgTanahAll = 0;

                                    foreach($companyGroups as $groupName => $companies) {
                                    $groupSumTonase = 0;
                                    $groupTotalPucuk = 0;
                                    $groupTotalDaun = 0;
                                    $groupTotalSogolan = 0;
                                    $groupTotalSiwilan = 0;
                                    $groupTotalTebumati = 0;
                                    $groupTotalTanah = 0;
                                    $groupTotalCompanies = 0;

                                    foreach ($companies as $companyCode => $items) {
                                    $companyByJenis = [];
                                    foreach ($items as $item) {
                                    $jenis = $item['jenis'];
                                    if (!isset($companyByJenis[$jenis])) {
                                    $companyByJenis[$jenis] = [];
                                    }
                                    $companyByJenis[$jenis][] = $item;
                                    }

                                    $sortedJenis = [];
                                    if (isset($companyByJenis['manual'])) {
                                    $sortedJenis['manual'] = $companyByJenis['manual'];
                                    }
                                    if (isset($companyByJenis['mesin'])) {
                                    $sortedJenis['mesin'] = $companyByJenis['mesin'];
                                    }

                                    foreach ($sortedJenis as $jenis => $jenisItems) {
                                    $count = count($jenisItems);
                                    if ($count === 0) continue;

                                    $sumTonase = 0;
                                    $sumPucuk = 0;
                                    $sumDaun = 0;
                                    $sumSogolan = 0;
                                    $sumSiwilan = 0;
                                    $sumTebumati = 0;
                                    $sumTanah = 0;

                                    foreach ($jenisItems as $row) {
                                    $sumTonase += $row['tonase_netto'];
                                    $sumPucuk += $row['pucuk'];
                                    $sumDaun += $row['daungulma'];
                                    $sumSogolan += $row['sogolan'];
                                    $sumSiwilan += $row['siwilan'];
                                    $sumTebumati += $row['tebumati'];
                                    $sumTanah += $row['tanahetc'];
                                    }

                                    $avgPucuk = $sumPucuk / $count;
                                    $avgDaun = $sumDaun / $count;
                                    $avgSogolan = $sumSogolan / $count;
                                    $avgSiwilan = $sumSiwilan / $count;
                                    $avgTebumati = $sumTebumati / $count;
                                    $avgTanah = $sumTanah / $count;

                                    // KG per komponen untuk company+jenis ini
                                    $kgPucuk = ($avgPucuk / 100) * $sumTonase;
                                    $kgDaun = ($avgDaun / 100) * $sumTonase;
                                    $kgSogolan = ($avgSogolan / 100) * $sumTonase;
                                    $kgSiwilan = ($avgSiwilan / 100) * $sumTonase;
                                    $kgTebumati = ($avgTebumati / 100) * $sumTonase;
                                    $kgTanah = ($avgTanah / 100) * $sumTonase;

                                    $groupSumTonase += $sumTonase;
                                    $groupTotalPucuk += $avgPucuk;
                                    $groupTotalDaun += $avgDaun;
                                    $groupTotalSogolan += $avgSogolan;
                                    $groupTotalSiwilan += $avgSiwilan;
                                    $groupTotalTebumati += $avgTebumati;
                                    $groupTotalTanah += $avgTanah;
                                    $groupTotalCompanies++;

                                    $grandKgPucukAll += $kgPucuk;
                                    $grandKgDaunAll += $kgDaun;
                                    $grandKgSogolanAll += $kgSogolan;
                                    $grandKgSiwilanAll += $kgSiwilan;
                                    $grandKgTebumatiAll += $kgTebumati;
                                    $grandKgTanahAll += $kgTanah;
                                    }
                                    }

                                    $groupAvgPucuk = $groupTotalCompanies > 0 ? $groupTotalPucuk / $groupTotalCompanies : 0;
                                    $groupAvgDaun = $groupTotalCompanies > 0 ? $groupTotalDaun / $groupTotalCompanies : 0;
                                    $groupAvgSogolan = $groupTotalCompanies > 0 ? $groupTotalSogolan / $groupTotalCompanies : 0;
                                    $groupAvgSiwilan = $groupTotalCompanies > 0 ? $groupTotalSiwilan / $groupTotalCompanies : 0;
                                    $groupAvgTebumati = $groupTotalCompanies > 0 ? $groupTotalTebumati / $groupTotalCompanies : 0;
                                    $groupAvgTanah = $groupTotalCompanies > 0 ? $groupTotalTanah / $groupTotalCompanies : 0;

                                    $groupTotalTrash = $groupAvgPucuk + $groupAvgDaun + $groupAvgSogolan + $groupAvgSiwilan + $groupAvgTebumati + $groupAvgTanah;
                                    $groupNettoTrash = max(0, $groupTotalTrash - 5);

                                    $groupKgTrashBruto = ($groupSumTonase * $groupTotalTrash) / 100;
                                    $groupKgTrashNetto = ($groupSumTonase * $groupNettoTrash) / 100;

                                    $grandTotalTonaseAll += $groupSumTonase;
                                    $grandTotalPucukAll += $groupAvgPucuk;
                                    $grandTotalDaunAll += $groupAvgDaun;
                                    $grandTotalSogolanAll += $groupAvgSogolan;
                                    $grandTotalSiwilanAll += $groupAvgSiwilan;
                                    $grandTotalTebumatiAll += $groupAvgTebumati;
                                    $grandTotalTanahAll += $groupAvgTanah;
                                    $grandKgTrashBrutoAll += $groupKgTrashBruto;
                                    $grandKgTrashNettoAll += $groupKgTrashNetto;
                                    $grandCountGroups++;
                                    }

                                    $grandAvgPucuk = $grandCountGroups > 0 ? $grandTotalPucukAll / $grandCountGroups : 0;
                                    $grandAvgDaun = $grandCountGroups > 0 ? $grandTotalDaunAll / $grandCountGroups : 0;
                                    $grandAvgSogolan = $grandCountGroups > 0 ? $grandTotalSogolanAll / $grandCountGroups : 0;
                                    $grandAvgSiwilan = $grandCountGroups > 0 ? $grandTotalSiwilanAll / $grandCountGroups : 0;
                                    $grandAvgTebumati = $grandCountGroups > 0 ? $grandTotalTebumatiAll / $grandCountGroups : 0;
                                    $grandAvgTanah = $grandCountGroups > 0 ? $grandTotalTanahAll / $grandCountGroups : 0;

                                    $grandTotalTrashSum = $grandAvgPucuk + $grandAvgDaun + $grandAvgSogolan + $grandAvgSiwilan + $grandAvgTebumati + $grandAvgTanah;

                                    $grandTrashPersentaseBruto = $grandTotalTonaseAll > 0 ? ($grandKgTrashBrutoAll / $grandTotalTonaseAll) * 100 : 0;
                                    $grandTrashPersentaseNetto = $grandTotalTonaseAll > 0 ? ($grandKgTrashNettoAll / $grandTotalTonaseAll) * 100 : 0;

                                    // rata per ton dari KG / tonase
                                    $rataPucukPerTon = $grandTotalTonaseAll > 0 ? ($grandKgPucukAll / $grandTotalTonaseAll) * 100 : 0;
                                    $rataDaunPerTon = $grandTotalTonaseAll > 0 ? ($grandKgDaunAll / $grandTotalTonaseAll) * 100 : 0;
                                    $rataSogolanPerTon = $grandTotalTonaseAll > 0 ? ($grandKgSogolanAll / $grandTotalTonaseAll) * 100 : 0;
                                    $rataSiwilanPerTon = $grandTotalTonaseAll > 0 ? ($grandKgSiwilanAll / $grandTotalTonaseAll) * 100 : 0;
                                    $rataTebumatiPerTon = $grandTotalTonaseAll > 0 ? ($grandKgTebumatiAll / $grandTotalTonaseAll) * 100 : 0;
                                    $rataTanahPerTon = $grandTotalTonaseAll > 0 ? ($grandKgTanahAll / $grandTotalTonaseAll) * 100 : 0;

                                    $totalTrashPerTon = $rataPucukPerTon + $rataDaunPerTon + $rataSogolanPerTon +
                                    $rataSiwilanPerTon + $rataTebumatiPerTon + $rataTanahPerTon;
                                    @endphp

                                    {{-- Total Row --}}
                                    <tr class="bg-yellow-100">
                                        <td class="uniform-cell text-center font-bold">TOTAL</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($grandTotalTonaseAll, 0, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($grandAvgPucuk, 2, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($grandAvgDaun, 2, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($grandAvgSogolan, 2, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($grandAvgSiwilan, 2, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($grandAvgTebumati, 2, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($grandAvgTanah, 2, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($grandTotalTrashSum, 2, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($grandTrashPersentaseBruto, 2, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($grandTrashPersentaseNetto, 2, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($grandKgTrashBrutoAll, 0, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($grandKgTrashNettoAll, 0, ',', '.') }}</td>
                                    </tr>

                                    {{-- Per Ton Average Row --}}
                                    <tr class="bg-white">
                                        <td colspan="2" class="uniform-cell text-center font-bold">RATA PERTON</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($rataPucukPerTon, 2, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($rataDaunPerTon, 2, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($rataSogolanPerTon, 2, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($rataSiwilanPerTon, 2, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($rataTebumatiPerTon, 2, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($rataTanahPerTon, 2, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($totalTrashPerTon, 2, ',', '.') }}</td>
                                        <td colspan="4" class="uniform-cell text-right"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- BOTTOM RIGHT: TOTAL KG Table --}}
                    <div class="min-w-0">
                        <div class="rounded-md border-2 border-gray-400">
                            <table class="uniform-table">
                                <thead>
                                    <tr>
                                        <th class="uniform-header w-16">TOTAL<br>Pucuk (KG)</th>
                                        <th class="uniform-header w-14">TOTAL<br>Daun (KG)</th>
                                        <th class="uniform-header w-12">TOTAL<br>Sogolan (KG)</th>
                                        <th class="uniform-header w-14">TOTAL<br>Siwilan (KG)</th>
                                        <th class="uniform-header w-16">TOTAL<br>Tebu Mati (KG)</th>
                                        <th class="uniform-header w-14">TOTAL<br>Tanah dll (KG)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    // Calculate final totals dari semua breakdown
                                    $finalTotalPucuk = 0;
                                    $finalTotalDaun = 0;
                                    $finalTotalSogolan = 0;
                                    $finalTotalSiwilan = 0;
                                    $finalTotalTebumati = 0;
                                    $finalTotalTanah = 0;

                                    // Hitung ulang total dari semua group companies
                                    foreach($companyGroups as $groupName => $companies) {
                                    foreach($companies as $companyCode => $items) {
                                    $companyByJenis = [];
                                    foreach($items as $item) {
                                    if (!isset($companyByJenis[$item['jenis']])) {
                                    $companyByJenis[$item['jenis']] = [];
                                    }
                                    $companyByJenis[$item['jenis']][] = $item;
                                    }

                                    foreach(['manual', 'mesin'] as $jenis) {
                                    if (!isset($companyByJenis[$jenis])) continue;

                                    $jenisItems = $companyByJenis[$jenis];
                                    $count = count($jenisItems);
                                    $sumTonase = 0;
                                    $sumPucuk = $sumDaun = $sumSogolan = $sumSiwilan = $sumTebumati = $sumTanah = 0;

                                    foreach($jenisItems as $item) {
                                    $sumTonase += floatval($item['tonase_netto'] ?? 0);
                                    $sumPucuk += floatval($item['pucuk'] ?? 0);
                                    $sumDaun += floatval($item['daungulma'] ?? 0);
                                    $sumSogolan += floatval($item['sogolan'] ?? 0);
                                    $sumSiwilan += floatval($item['siwilan'] ?? 0);
                                    $sumTebumati += floatval($item['tebumati'] ?? 0);
                                    $sumTanah += floatval($item['tanahetc'] ?? 0);
                                    }

                                    if ($count > 0) {
                                    $avgPucuk = $sumPucuk / $count;
                                    $avgDaun = $sumDaun / $count;
                                    $avgSogolan = $sumSogolan / $count;
                                    $avgSiwilan = $sumSiwilan / $count;
                                    $avgTebumati = $sumTebumati / $count;
                                    $avgTanah = $sumTanah / $count;

                                    $finalTotalPucuk += ($sumTonase * $avgPucuk) / 100;
                                    $finalTotalDaun += ($sumTonase * $avgDaun) / 100;
                                    $finalTotalSogolan += ($sumTonase * $avgSogolan) / 100;
                                    $finalTotalSiwilan += ($sumTonase * $avgSiwilan) / 100;
                                    $finalTotalTebumati += ($sumTonase * $avgTebumati) / 100;
                                    $finalTotalTanah += ($sumTonase * $avgTanah) / 100;
                                    }
                                    }
                                    }
                                    }
                                    @endphp

                                    {{-- Row TOTAL NASIONAL --}}
                                    <tr class="bg-yellow-100 font-semibold">
                                        <td class="uniform-cell text-right font-bold">{{ number_format($finalTotalPucuk, 0, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($finalTotalDaun, 0, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($finalTotalSogolan, 0, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($finalTotalSiwilan, 0, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($finalTotalTebumati, 0, ',', '.') }}</td>
                                        <td class="uniform-cell text-right font-bold">{{ number_format($finalTotalTanah, 0, ',', '.') }}</td>
                                    </tr>
                                    {{-- Empty row untuk sejajar dengan RATA PERTON --}}
                                    <tr class="bg-white">
                                        <td class="uniform-cell text-center" colspan="6">&nbsp;</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Normal display styling */
        .uniform-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            background-color: white;
        }

        .uniform-header,
        .uniform-header-sub {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 8px 6px;
            text-align: center;
            font-weight: 600;
            font-size: 11px;
            line-height: 1.3;
            vertical-align: middle;
        }

        .uniform-cell {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            font-size: 11px;
            line-height: 1.4;
            vertical-align: middle;
        }

        .uniform-cell-group {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            font-size: 11px;
            line-height: 1.4;
            vertical-align: middle;
            background-color: #f9fafb;
        }

        /* Column widths for better readability */
        .col-asal {
            min-width: 120px;
        }

        .col-tonase {
            min-width: 80px;
        }

        .col-percentage {
            min-width: 70px;
        }

        .col-total {
            min-width: 90px;
        }

        .col-kg {
            min-width: 85px;
        }

        /* Hover effects */
        .uniform-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .uniform-table tbody tr.bg-gray-100:hover {
            background-color: #e5e7eb;
        }

        /* Responsive design */
        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        /* Print styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
                font-size: 8px;
            }

            .no-print {
                display: none !important;
            }

            .grid.grid-cols-2 {
                display: block !important;
            }

            .grid.grid-cols-2>div {
                width: 100% !important;
                margin-bottom: 15px !important;
            }

            .uniform-table {
                font-size: 7px !important;
                width: 100%;
            }

            .uniform-header,
            .uniform-header-sub,
            .uniform-cell,
            .uniform-cell-group {
                padding: 1px 2px !important;
                border: 1px solid #333 !important;
                font-size: 6px !important;
                line-height: 1.2 !important;
            }

            .text-xl {
                font-size: 14px !important;
            }

            .text-lg {
                font-size: 12px !important;
            }

            .px-4 {
                padding-left: 8px !important;
                padding-right: 8px !important;
            }

            .py-4 {
                padding-top: 8px !important;
                padding-bottom: 8px !important;
            }
        }
    </style>
</x-layout>