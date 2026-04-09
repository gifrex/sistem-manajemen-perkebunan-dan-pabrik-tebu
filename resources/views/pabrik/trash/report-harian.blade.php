<x-layout>
    <x-slot:title>Laporan Trash Harian</x-slot:title>
    <x-slot:navbar>Pabrik</x-slot:navbar>
    <x-slot:nav>Laporan Trash Harian</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md">

        <!-- Print Controls -->
        <div class="no-print px-4 py-4 border-b border-gray-200 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Laporan Trash Harian</h1>
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
            <h2 class="text-xl font-bold text-gray-800 uppercase">
                LAPORAN HARIAN DATA TRASH
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