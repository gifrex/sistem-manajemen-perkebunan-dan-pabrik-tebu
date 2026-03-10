<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    <x-slot:navnav>{{ $title }}</x-slot:navnav>

    <style>
        @media print {

            /* Reset dasar untuk print */
            html,
            body {
                height: auto !important;
                min-height: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                background: #fff !important;
            }

            .layout-container,
            .main-wrapper,
            main {
                display: block !important;
                height: auto !important;
                min-height: 0 !important;
                overflow: visible !important;
            }

            /* Ukuran kertas A4 dan F4 */
            @page {
                size: A4;
                margin: 8mm 8mm 8mm 8mm;
            }

            /* Hide elemen yang tidak perlu dicetak */
            .no-print {
                display: none !important;
            }

            /* Container utama */
            .print-container {
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                margin-bottom: 0 !important;
            }

            /* Remove extra spacing */
            .overflow-x-auto,
            .mb-4 {
                margin-bottom: 8px !important;
            }

            /* Header report - lebih kompak */
            .text-center.mb-6 {
                margin-bottom: 8px !important;
            }

            .text-center.mb-6 h1 {
                font-size: 14pt !important;
                margin-bottom: 4px !important;
            }

            .text-center.mb-6 h2,
            .text-center.mb-6 h3 {
                font-size: 11pt !important;
                margin-bottom: 2px !important;
            }

            /* Voucher section */
            .mb-2 {
                margin-bottom: 6px !important;
                font-size: 10pt !important;
            }

            /* Table styling */
            table {
                width: 100% !important;
                font-size: 8pt !important;
                border-collapse: collapse !important;
                page-break-inside: auto !important;
            }

            thead {
                display: table-header-group !important;
            }

            tbody {
                display: table-row-group !important;
            }

            tr {
                page-break-inside: auto !important;
                page-break-after: auto !important;
            }

            /* Hindari page break pada row header kegiatan dan subtotal */
            tr.bg-blue-50,
            tr.bg-yellow-50,
            tr.bg-green-100 {
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
            }

            /* Hindari page break antara header kegiatan dan row pertamanya */
            tr.bg-blue-50+tr {
                page-break-before: avoid !important;
            }

            th,
            td {
                padding: 3px 4px !important;
                font-size: 8pt !important;
                line-height: 1.3 !important;
                word-wrap: break-word !important;
            }

            th {
                font-weight: 600 !important;
                background-color: #f3f4f6 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Activity header row */
            .bg-blue-50 {
                background-color: #eff6ff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* LKH sub-header row */
            .bg-indigo-50 {
                background-color: #eef2ff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Subtotal row untuk Harian */
            .bg-yellow-50 {
                background-color: #fefce8 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Total keseluruhan row */
            .bg-green-100 {
                background-color: #dcfce7 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Footer - lebih kecil */
            .text-right.text-sm {
                font-size: 8pt !important;
                margin-top: 8px !important;
            }

            /* Shadow dan rounded hilang saat print */
            .shadow-md,
            .rounded-lg {
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            /* Overflow handling */
            .overflow-x-auto {
                overflow: visible !important;
            }

            /* Ensure borders are visible */
            .border {
                border: 1px solid #000 !important;
            }

            /* Fix untuk border pada merged cells */
            td[rowspan] {
                border-bottom: 1px solid #000 !important;
                box-decoration-break: clone !important;
                -webkit-box-decoration-break: clone !important;
            }

            /* Adjustments untuk kolom yang terlalu panjang */
            td {
                max-width: none !important;
            }

            /* Alternatif: hindari page break pada merged cells */
            tr:has(td[rowspan]) {
                page-break-inside: avoid !important;
            }
        }

        /* Screen view - tetap normal */
        @media screen {
            .max-w-full {
                max-width: 100%;
            }
        }
    </style>

    <div class="max-w-full mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Header dengan tombol print -->
        <div
            class="no-print bg-gradient-to-r from-green-600 to-emerald-500 text-white p-4 flex justify-between items-center">
            <h1 class="text-xl font-bold">Preview Rekap Upah Mingguan</h1>
            <div class="flex items-center space-x-2">
                <button id="print-btn"
                    class="bg-white text-green-600 px-4 py-2 rounded font-semibold hover:bg-gray-100 transition flex items-center gap-2">
                    <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                            d="M16.444 18H19a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2.556M17 11V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v6h10ZM7 15h10v4a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-4Z" />
                    </svg>
                    <span>Print</span>
                </button>
            </div>
        </div>

        <!-- Container untuk konten yang akan dicetak -->
        <div id="print-container" class="print-container p-8 pb-4 bg-white">
            <!-- Header Report -->
            <div class="text-center mb-6">
                <h1 class="text-xl font-bold mb-2">Rekap Upah Mingguan</h1>
                <h2 class="text-lg">Tenaga Kerja {{ session('tenagakerjarum') == 'Harian' ? 'Harian' : 'Borongan' }}
                </h2>
                <h2 class="text-lg">Divisi {{ session('companycode') }}</h2>
                @php
                    $currentLocale = \Carbon\Carbon::getLocale();
                    \Carbon\Carbon::setLocale('id');
                    $start = \Carbon\Carbon::parse($startDate);
                    $end = \Carbon\Carbon::parse($endDate);
                @endphp

                <h3 class="text-base">Periode:
                    @if ($start->format('m Y') === $end->format('m Y'))
                        {{ $start->translatedFormat('d') }} s.d {{ $end->translatedFormat('d F Y') }}
                    @else
                        {{ $start->translatedFormat('d F Y') }} s.d {{ $end->translatedFormat('d F Y') }}
                    @endif
                </h3>

                @php
                    \Carbon\Carbon::setLocale($currentLocale);
                @endphp
            </div>

            <div class="mb-2">
                <span class="font-medium">No. Voucher:</span>
            </div>

            <!-- Tabel Data -->
            <div class="overflow-x-auto mb-4">
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 px-2 py-2" style="width: 4%;">No.</th>
                            @if (session('tenagakerjarum') == 'Harian')
                                <th class="border border-gray-300 px-2 py-2" style="width: 14%;">Tenaga Kerja</th>
                            @endif
                            <th class="border border-gray-300 px-2 py-2" style="width: 10%;">Plot</th>
                            <th class="border border-gray-300 px-2 py-2" style="width: 8%;">Luas (Ha)</th>
                            <th class="border border-gray-300 px-2 py-2" style="width: 10%;">Status Tanam</th>
                            <th class="border border-gray-300 px-2 py-2" style="width: 8%;">Hasil (Ha)</th>
                            <th class="border border-gray-300 px-2 py-2" style="width: 10%;">Tanggal Kegiatan</th>
                            @if (session('tenagakerjarum') == 'Harian')
                                <th class="border border-gray-300 px-2 py-2" style="width: 10%;">Cost/Unit</th>
                                <th class="border border-gray-300 px-2 py-2" style="width: 10%;">Upah Lembur</th>
                            @endif
                            <th class="border border-gray-300 px-2 py-2" style="width: 14%;">Biaya (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Group data by activityname first, then by lkhno
                            $groupedByActivity = [];
                            $totalKeseluruhan = 0;

                            if (isset($data) && count($data) > 0) {
                                foreach ($data as $item) {
                                    $activityName = $item->activityname;
                                    $lkhno = $item->lkhno;

                                    if (!isset($groupedByActivity[$activityName])) {
                                        $groupedByActivity[$activityName] = [];
                                    }
                                    if (!isset($groupedByActivity[$activityName][$lkhno])) {
                                        $groupedByActivity[$activityName][$lkhno] = [];
                                    }
                                    $groupedByActivity[$activityName][$lkhno][] = $item;
                                }
                            }

                            $rowNumber = 1;
                        @endphp

                        @if (count($groupedByActivity) > 0)
                            @foreach ($groupedByActivity as $activityName => $lkhGroups)
                                @php
                                    $activitySubtotal = 0;
                                @endphp

                                <!-- Row Header Kegiatan -->
                                <tr class="bg-blue-50">
                                    <td colspan="{{ session('tenagakerjarum') == 'Harian' ? '10' : '7' }}"
                                        class="border border-gray-300 px-3 py-2 font-bold text-left">
                                        Kegiatan: {{ $activityName }}
                                    </td>
                                </tr>

                                @foreach ($lkhGroups as $lkhno => $items)
                                    @php
                                        $subtotal = 0;
                                        $itemCount = count($items);
                                        $firstItem = $items[0];
                                    @endphp

                                    <!-- Sub-header per LKH: No. LKH & Mandor -->
                                    <tr class="bg-indigo-50">
                                        <td colspan="{{ session('tenagakerjarum') == 'Harian' ? '10' : '7' }}"
                                            class="border border-gray-300 px-3 py-1 text-xs text-indigo-700">
                                            <span class="font-semibold">No. LKH: {{ $lkhno }}</span>
                                            &nbsp;|&nbsp;
                                            <span class="font-semibold">Mandor:</span>
                                            {{ $firstItem->mandorname ?? '-' }}
                                        </td>
                                    </tr>

                                    @php

                                        // Cek berapa baris yang memiliki plot yang sama secara berurutan
                                        $plotSpans = [];
                                        $currentPlot = null;
                                        $spanStart = 0;

                                        foreach ($items as $idx => $item) {
                                            if ($currentPlot === null || $currentPlot !== $item->plot) {
                                                if ($currentPlot !== null) {
                                                    $plotSpans[] = [
                                                        'plot' => $currentPlot,
                                                        'start' => $spanStart,
                                                        'count' => $idx - $spanStart,
                                                    ];
                                                }
                                                $currentPlot = $item->plot;
                                                $spanStart = $idx;
                                            }
                                        }
                                        // Tambahkan span terakhir
                                        if ($currentPlot !== null) {
                                            $plotSpans[] = [
                                                'plot' => $currentPlot,
                                                'start' => $spanStart,
                                                'count' => count($items) - $spanStart,
                                            ];
                                        }
                                    @endphp

                                    @foreach ($items as $index => $item)
                                        @php
                                            // Cari apakah index ini adalah start dari plot span
                                            $isPlotStart = false;
                                            $plotRowspan = 1;
                                            foreach ($plotSpans as $span) {
                                                if ($span['start'] === $index) {
                                                    $isPlotStart = true;
                                                    $plotRowspan = $span['count'];
                                                    break;
                                                }
                                            }
                                        @endphp

                                        <tr>
                                            <td class="border border-gray-300 px-2 py-2 text-center">
                                                {{ $rowNumber++ }}.</td>

                                            @if (session('tenagakerjarum') == 'Harian')
                                                <td class="border border-gray-300 px-2 py-2">
                                                    {{ $item->namatenagakerja }}</td>
                                            @endif

                                            @if ($isPlotStart)
                                                <td class="border border-gray-300 px-2 py-2 bg-gray-50"
                                                    rowspan="{{ $plotRowspan }}">{{ $item->plot }}</td>
                                            @endif

                                            <td class="border border-gray-300 px-2 py-2 text-right">
                                                {{ number_format($item->luasan, 2, ',', '.') }}
                                            </td>
                                            <td class="border border-gray-300 px-2 py-2">
                                                {{ $item->batchdate }}/{{ $item->lifecyclestatus }}
                                            </td>
                                            <td class="border border-gray-300 px-2 py-2 text-right">
                                                {{ number_format($item->hasil, 2, ',', '.') }}
                                            </td>

                                            @if (session('tenagakerjarum') == 'Borongan')
                                                @if ($index === 0)
                                                    <td class="border border-gray-300 px-2 py-2 text-center bg-gray-50"
                                                        rowspan="{{ $itemCount }}">
                                                        {{ \Carbon\Carbon::parse($item->lkhdate)->format('Y-m-d') }}
                                                    </td>
                                                    <td class="border border-gray-300 px-2 py-2 text-right bg-white"
                                                        rowspan="{{ $itemCount }}">
                                                        {{ $item->totalupahall }}
                                                    </td>
                                                @endif
                                            @else
                                                <td class="border border-gray-300 px-2 py-2 text-center">
                                                    {{ \Carbon\Carbon::parse($item->lkhdate)->format('Y-m-d') }}
                                                </td>
                                                <td class="border border-gray-300 px-2 py-2 text-right">
                                                    {{ $item->upah }}
                                                </td>
                                                <td class="border border-gray-300 px-2 py-2 text-right">
                                                    {{ $item->upahlembur }}
                                                </td>
                                                <td class="border border-gray-300 px-2 py-2 text-right">
                                                    {{ $item->total }}</td>
                                            @endif
                                        </tr>

                                        @php
                                            if (session('tenagakerjarum') == 'Harian') {
                                                $cleanTotal = preg_replace('/[^\d,.]/', '', $item->total);
                                            } else {
                                                if ($index === 0) {
                                                    $cleanTotal = preg_replace('/[^\d,.]/', '', $item->totalupahall);
                                                } else {
                                                    $cleanTotal = '0';
                                                }
                                            }

                                            if (
                                                strpos($cleanTotal, ',') !== false &&
                                                strpos($cleanTotal, '.') !== false
                                            ) {
                                                $cleanTotal = str_replace('.', '', $cleanTotal);
                                                $cleanTotal = str_replace(',', '.', $cleanTotal);
                                            } elseif (strpos($cleanTotal, ',') !== false) {
                                                $cleanTotal = str_replace(',', '.', $cleanTotal);
                                            }

                                            $totalValue = floatval($cleanTotal);
                                            $subtotal += $totalValue;
                                        @endphp
                                    @endforeach

                                    @php
                                        $activitySubtotal += $subtotal;
                                    @endphp
                                @endforeach

                                <tr class="bg-yellow-50 font-bold">
                                    <td class="border border-gray-300 px-2 py-2 text-center"
                                        colspan="{{ session('tenagakerjarum') == 'Harian' ? '9' : '6' }}">
                                        Subtotal {{ $activityName }}
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2 text-right">
                                        Rp {{ number_format($activitySubtotal, 2, ',', '.') }}
                                    </td>
                                </tr>


                                @php
                                    $totalKeseluruhan += $activitySubtotal;
                                @endphp
                            @endforeach

                            <!-- Row Total Keseluruhan -->
                            <tr class="bg-green-100 font-bold text-base">
                                <td class="border border-gray-300 px-2 py-2 text-center"
                                    colspan="{{ session('tenagakerjarum') == 'Harian' ? '9' : '6' }}">
                                    TOTAL KESELURUHAN
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-right">
                                    Rp {{ number_format($totalKeseluruhan, 2, ',', '.') }}
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="{{ session('tenagakerjarum') == 'Harian' ? '10' : '7' }}"
                                    class="border border-gray-300 px-2 py-2 text-center">
                                    Tidak ada data
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Footer dengan tanggal cetak -->
            <div class="text-right text-sm text-gray-500" style="margin-top: 8px; margin-bottom: 0;">
                Dicetak pada: {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('print-btn').addEventListener('click', function() {
                window.print();
            });
        });
    </script>

</x-layout>
