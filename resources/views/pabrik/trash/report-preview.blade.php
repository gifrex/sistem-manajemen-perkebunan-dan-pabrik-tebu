<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview Report Trash</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom styles for uniform table appearance */
        .uniform-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .uniform-header {
            padding: 6px 4px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            border: 2px solid #6b7280;
            background-color: #f9fafb;
            vertical-align: middle;
        }

        .uniform-header-sub {
            padding: 4px 3px;
            text-align: center;
            font-size: 8px;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            border: 2px solid #6b7280;
            background-color: #f3f4f6;
        }

        .uniform-cell {
            padding: 3px 4px;
            font-size: 9px;
            border: 2px solid #d1d5db;
            vertical-align: middle;
        }

        .uniform-cell-group {
            padding: 4px 4px;
            font-size: 9px;
            border: 2px solid #6b7280;
            background-color: #f3f4f6;
            font-weight: bold;
        }

        /* Width classes for consistent column sizing */
        .w-12 { width: 3rem; }
        .w-14 { width: 3.5rem; }
        .w-16 { width: 4rem; }
        .w-18 { width: 4.5rem; }
        .w-20 { width: 5rem; }

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

            .uniform-table {
                font-size: 9px;
            }

            .uniform-header {
                font-size: 8px;
                padding: 4px 2px;
            }

            .uniform-cell {
                font-size: 8px;
                padding: 2px 3px;
            }

            .border-2 {
                border-width: 2px !important;
                border-color: #333 !important;
            }
        }
    </style>
</head>

<body class="bg-gray-50">
    <div class="max-w-full mx-auto p-4 bg-white">

        <!-- Report Header -->
        <div class="text-center mb-6">
            @php
            // Ambil company pertama dari actualCompanies untuk judul
            $primaryCompany = !empty($actualCompanies) ? $actualCompanies[0] : 'UNKNOWN';

            // Mapping company code ke nama lengkap
            $companyNames = [
                'TBL1' => 'KEBUN TBL', 'TBL2' => 'KEBUN TBL', 'TBL3' => 'KEBUN TBL',
                'BNL1' => 'KEBUN BNIL', 'BNL2' => 'KEBUN BNIL', 'BNL3' => 'KEBUN BNIL', 'BNL4' => 'KEBUN BNIL',
                'SIL1' => 'KEBUN SILVA', 'SIL2' => 'KEBUN SILVA', 'SIL3' => 'KEBUN SILVA'
            ];

            $kebunName = 'KEBUN UNKNOWN';
            foreach($companyNames as $code => $name) {
                if (strpos($primaryCompany, substr($code, 0, -1)) === 0) {
                    $kebunName = $name;
                    break;
                }
            }

            // For monthly reports - month names
            $monthNames = [
                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
            ];
            @endphp

            @if($reportType === 'bulanan')
                <!-- MONTHLY HEADER -->
                <h2 class="text-xl font-bold text-gray-800 uppercase">
                    RATA-RATA TRASH KEBUN {{ strtoupper($monthNames[$month] ?? $month) }} {{ $year }}
                </h2>
            @else
                <!-- REGULAR HEADER -->
                <!-- Judul untuk screen (original) -->
                <h2 class="text-xl font-bold text-gray-800 uppercase no-print">
                    LAPORAN {{ strtoupper($reportType) }} DATA TRASH
                </h2>

                <!-- Judul untuk print (dinamis hanya untuk mingguan) -->
                @if($reportType === 'mingguan')
                <h2 class="text-xl font-bold text-gray-800 uppercase print-only" style="display: none;">
                    HASIL ANALISA TRASH {{ $kebunName }}
                </h2>
                @else
                <h2 class="text-xl font-bold text-gray-800 uppercase print-only" style="display: none;">
                    LAPORAN {{ strtoupper($reportType) }} DATA TRASH
                </h2>
                @endif
            @endif

            <h3 class="text-lg font-semibold text-gray-700">SUNGAI BUDI GROUP</h3>
            
            @if($reportType === 'bulanan')
                <p class="text-gray-600 mt-2">Periode: {{ $monthNames[$month] ?? $month }} {{ $year }}</p>
            @else
                <p class="text-gray-600 mt-2">
                    Periode: {{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }}
                </p>
            @endif

            <p class="text-gray-600">
                Company: {{ !empty($actualCompanies) ? implode(', ', $actualCompanies) : (is_array($company) ? implode(', ', $company) : ($company === 'all' ? 'SEMUA COMPANY' : $company)) }}
            </p>
            <p class="text-gray-600 text-sm">Preview pada: {{ date('d/m/Y H:i:s') }}</p>
        </div>

        <div class="px-2">
            @php
            $isAllCompanies = ($company === 'all') || (is_array($company) && in_array('all', $company)) || (is_array($company) && count($company) > 1);
            @endphp

            @if($reportType === 'harian' && $isAllCompanies)
                <!-- LAPORAN HARIAN -->
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
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Pucuk (%)</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Daun Gulma (%)</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Sogolan (%)</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Siwilan (%)</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tebu Mati (%)</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tanah dll (%)</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Total (%)</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Toleransi (%)</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Netto (%)</th>
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
                <!-- LAPORAN MINGGUAN -->
                @forelse($dataGrouped as $jenis => $companies)
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 bg-{{ $jenis === 'manual' ? 'green' : 'blue' }}-100 p-2 rounded">
                        JENIS: {{ strtoupper($jenis) }}
                    </h3>

                    @foreach($companies as $companyCode => $items)
                    <div class="mb-6">
                        <h4 class="text-lg font-bold text-gray-700 mb-2 bg-gray-100 p-3 rounded">
                            Company: 
                            @if ($companyCode == 'BNL1') BNIL1
                            @elseif ($companyCode == 'BNL2') BNIL2
                            @elseif ($companyCode == 'BNL3') BNIL3
                            @elseif ($companyCode == 'BNL4') BNIL4
                            @elseif ($companyCode == 'TBL1') TBL1
                            @elseif ($companyCode == 'TBL2') TBL2
                            @elseif ($companyCode == 'TBL3') TBL3
                            @elseif( $companyCode == 'SIL1') SILVA1
                            @elseif( $companyCode == 'SIL2') SILVA2
                            @elseif( $companyCode == 'SIL3') SILVA3
                            @else {{ $companyCode }}
                            @endif 
                        </h4>

                        <div class="overflow-x-auto rounded-md border-2 border-gray-400">
                            <table class="min-w-full bg-white text-xs border-collapse">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tanggal</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">No</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Surat Jalan</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Plot</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Kontraktor</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Sub Kontraktor</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Pucuk (%)</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Daun Gulma (%)</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Sogolan (%)</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Siwilan (%)</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tebu Mati (%)</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tanah dll (%)</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Total (%)</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Toleransi (%)</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Netto (%)</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    @foreach($items as $index => $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-2 py-2 text-xs border-2 border-gray-300">{{ date('d/m/Y', strtotime($item['tanggalangkut'] ?? $item['createddate'] ?? '')) }}</td>
                                        <td class="px-2 py-2 text-xs border-2 border-gray-300">{{ $index + 1 }}</td>
                                        <td class="px-2 py-2 text-xs border-2 border-gray-300">{{ $item['suratjalanno'] ?? '' }}</td>
                                        <td class="px-2 py-2 text-xs border-2 border-gray-300">{{ $item['plot'] ?? '' }}</td>
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

            @elseif($reportType === 'bulanan')
                <!-- LAPORAN BULANAN -->
                <div class="px-2 overflow-x-auto">
                    @php
                    // Group companies by prefix (TBL, BNL, SIL)
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
                    @endphp

                    {{-- TOP TABLES: Main Data + KG Breakdown --}}
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
                                        @php $previousGroupName = null; @endphp
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
                                                        $sumTonase += $item['tonase_netto'] ?? 0;
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

                                                    $kgTrashBruto = ($totaltrash / 100) * $sumTonase;
                                                    $kgTrashNetto = ($nettoTrash / 100) * $sumTonase;
                                                    @endphp

                                                    <tr class="hover:bg-gray-50">
                                                        <td class="uniform-cell text-center">
                                                            {{ $companyCode }} ({{ ucfirst($jenis) }})
                                                        </td>
                                                        <td class="uniform-cell text-center">
                                                            {{ number_format($sumTonase, 0, ',', '.') }}
                                                        </td>
                                                        <td class="uniform-cell text-right">{{ number_format($avgPucuk, 3, ',', '.') }}</td>
                                                        <td class="uniform-cell text-right">{{ number_format($avgDaun, 3, ',', '.') }}</td>
                                                        <td class="uniform-cell text-right">{{ number_format($avgSogolan, 3, ',', '.') }}</td>
                                                        <td class="uniform-cell text-right">{{ number_format($avgSiwilan, 3, ',', '.') }}</td>
                                                        <td class="uniform-cell text-right">{{ number_format($avgTebumati, 3, ',', '.') }}</td>
                                                        <td class="uniform-cell text-right">{{ number_format($avgTanah, 3, ',', '.') }}</td>
                                                        <td class="uniform-cell text-right">{{ number_format($totaltrash, 3, ',', '.') }}</td>
                                                        <td class="uniform-cell text-right">{{ number_format($totaltrash, 3, ',', '.') }}</td>
                                                        <td class="uniform-cell text-right">{{ number_format($nettoTrash, 3, ',', '.') }}</td>
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
                                            $groupKgTrashNetto = ($groupSumTonase * $groupNettoTrash) / 100;

                                            $trashPersentaseBruto = $groupSumTonase > 0 ? ($groupKgTrashBruto / $groupSumTonase) * 100 : 0;
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
                                                    <strong>{{ number_format($groupAvgPucuk, 3, ',', '.') }}</strong>
                                                </td>
                                                <td class="uniform-cell-group text-right">
                                                    <strong>{{ number_format($groupAvgDaun, 3, ',', '.') }}</strong>
                                                </td>
                                                <td class="uniform-cell-group text-right">
                                                    <strong>{{ number_format($groupAvgSogolan, 3, ',', '.') }}</strong>
                                                </td>
                                                <td class="uniform-cell-group text-right">
                                                    <strong>{{ number_format($groupAvgSiwilan, 3, ',', '.') }}</strong>
                                                </td>
                                                <td class="uniform-cell-group text-right">
                                                    <strong>{{ number_format($groupAvgTebumati, 3, ',', '.') }}</strong>
                                                </td>
                                                <td class="uniform-cell-group text-right">
                                                    <strong>{{ number_format($groupAvgTanah, 3, ',', '.') }}</strong>
                                                </td>
                                                <td class="uniform-cell-group text-right">
                                                    <strong>{{ number_format($groupTotalTrash, 3, ',', '.') }}</strong>
                                                </td>
                                                <td class="uniform-cell-group text-right">
                                                    <strong>{{ number_format($trashPersentaseBruto, 3, ',', '.') }}</strong>
                                                </td>
                                                <td class="uniform-cell-group text-right">
                                                    <strong>{{ number_format($trashPersentaseNetto, 3, ',', '.') }}</strong>
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

                    {{-- BOTTOM TABLES: Summary + Total KG --}}
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
                                            $sumTonase += $row['tonase_netto'] ?? 0;
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
                                                <td class="uniform-cell text-right font-bold">{{ number_format($grandAvgPucuk, 3, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($grandAvgDaun, 3, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($grandAvgSogolan, 3, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($grandAvgSiwilan, 3, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($grandAvgTebumati, 3, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($grandAvgTanah, 3, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($grandTotalTrashSum, 3, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($grandTrashPersentaseBruto, 3, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($grandTrashPersentaseNetto, 3, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($grandKgTrashBrutoAll, 0, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($grandKgTrashNettoAll, 0, ',', '.') }}</td>
                                            </tr>

                                            {{-- Per Ton Average Row --}}
                                            <tr class="bg-white">
                                                <td colspan="2" class="uniform-cell text-center font-bold">RATA PERTON</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($rataPucukPerTon, 3, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($rataDaunPerTon, 3, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($rataSogolanPerTon, 3, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($rataSiwilanPerTon, 3, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($rataTebumatiPerTon, 3, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($rataTanahPerTon, 3, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($totalTrashPerTon, 3, ',', '.') }}</td>
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
                                            <tr class="bg-yellow-100">
                                                <td class="uniform-cell text-right font-bold">{{ number_format($grandKgPucukAll, 0, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($grandKgDaunAll, 0, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($grandKgSogolanAll, 0, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($grandKgSiwilanAll, 0, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($grandKgTebumatiAll, 0, ',', '.') }}</td>
                                                <td class="uniform-cell text-right font-bold">{{ number_format($grandKgTanahAll, 0, ',', '.') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- LAPORAN LAIN -->
                <div class="text-center py-8">
                    <p class="text-gray-500">Tipe laporan tidak dikenal</p>
                </div>
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

        <!-- Summary info -->
        <div class="mt-6 text-center text-sm text-gray-600 border-t border-gray-200 pt-4">
            <p><strong>Preview Report</strong> - Klik "Generate Report" untuk mendapatkan file lengkap dengan format cetak</p>
        </div>

    </div>
</body>

</html>