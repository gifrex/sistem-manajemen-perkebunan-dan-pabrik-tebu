<x-layout>
    <x-slot:title>Laporan Trash Mingguan</x-slot:title>
    <x-slot:navbar>Pabrik</x-slot:navbar>
    <x-slot:nav>Laporan Trash Mingguan</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md">

        <!-- Print Controls -->
        <div class="no-print px-4 py-4 border-b border-gray-200 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Laporan Trash Mingguan</h1>
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
            // Get primary company for print header
            $primaryCompany = !empty($actualCompanies) ? $actualCompanies[0] : 'UNKNOWN';

            // Company mapping for print header
            $companyNames = [
                'TBL1' => 'KEBUN TBL',
                'TBL2' => 'KEBUN TBL',
                'TBL3' => 'KEBUN TBL',
                'BNL1' => 'KEBUN BNIL',
                'BNL2' => 'KEBUN BNIL',
                'BNL3' => 'KEBUN BNIL',
                'BNL4' => 'KEBUN BNIL',
                'SIL1' => 'KEBUN SILVA',
                'SIL2' => 'KEBUN SILVA',
                'SIL3' => 'KEBUN SILVA'
            ];

            $kebunName = 'KEBUN UNKNOWN';
            foreach($companyNames as $code => $name) {
                if (strpos($primaryCompany, substr($code, 0, -1)) === 0) {
                    $kebunName = $name;
                    break;
                }
            }
            @endphp

            <!-- Screen title -->
            <h2 class="text-xl font-bold text-gray-800 uppercase no-print">
                LAPORAN MINGGUAN DATA TRASH
            </h2>

            <!-- Print title -->
            <h2 class="text-xl font-bold text-gray-800 uppercase print-only" style="display: none;">
                HASIL ANALISA TRASH {{ $kebunName }}
            </h2>

            <h3 class="text-lg font-semibold text-gray-700">
                SUNGAI BUDI GROUP
            </h3>
            <p class="text-gray-600 mt-2">
                Periode: {{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }}
            </p>
            <p class="text-gray-600">
                Company: {{ !empty($actualCompanies) ? implode(', ', $actualCompanies) : (is_array($company) ? implode(', ', $company) : ($company === 'all' ? 'SEMUA COMPANY' : $company)) }}
            </p>
            <p class="text-gray-600 text-sm">
                Dicetak pada: {{ date('d/m/Y H:i:s') }}
            </p>
        </div>

        <div class="px-4">
            @forelse($dataGrouped as $jenis => $companies)
            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4 bg-{{ $jenis === 'manual' ? 'green' : 'blue' }}-100 p-2 rounded">
                    JENIS: {{ strtoupper($jenis) }}
                </h3>

                @foreach($companies as $companyCode => $items)
                <div class="mb-6">
                    <h4 class="text-lg font-bold text-gray-700 mb-2 bg-gray-100 p-3 rounded">
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
                                    <td class="px-2 py-2 text-xs border-2 border-gray-300">{{ date('d/m/Y', strtotime($item['tanggalangkut'] ?? '')) }}</td>
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
        </div>

        <!-- Signature Section - Only for print -->
        <div class="print-only mt-8 px-4" style="display: none;">
            <div class="flex justify-between items-start mt-12">
                <!-- Signature 1: Approved by -->
                <div class="text-center flex-1">
                    <p class="text-sm font-semibold mb-2 mt-5">DISETUJUI OLEH</p>
                    <div class="h-20 mb-2"></div> <!-- Space for signature -->
                    <div class="inline-block">
                        <p class="text-sm font-bold border-b-2 border-black px-4 pb-1">Tandy</p>
                        <p class="text-xs mt-1">General Manager</p>
                    </div>
                </div>

                <!-- Signature 2: Known by -->
                <div class="text-center flex-1">
                    <p class="text-sm font-semibold mb-2 mt-5">DIKETAHUI OLEH</p>
                    <div class="h-20 mb-2"></div> <!-- Space for signature -->
                    <div class="inline-block">
                        <p class="text-sm font-bold border-b-2 border-black px-4 pb-1">Tyas Rudito M</p>
                        <p class="text-xs mt-1">Kabag Laborat</p>
                    </div>
                </div>

                <!-- Signature 3: Prepared by -->
                <div class="text-center flex-1">
                    <p class="text-sm font-semibold mb-3 mt-0">Terbanggi Besar, {{ date('d-M-Y') }}</p>
                    <p class="text-sm font-semibold mb-2">DISIAPKAN OLEH</p>
                    <div class="h-20 mb-2"></div> <!-- Space for signature -->
                    <div class="inline-block">
                        <p class="text-sm font-bold border-b-2 border-black px-4 pb-1">{{ Auth::user()->userid ?? 'Admin' }}</p>
                    </div>
                </div>
            </div>
        </div>

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
            }

            .border-2 {
                border-width: 2px !important;
                border-color: #333 !important;
            }
        }
    </style>

</x-layout>