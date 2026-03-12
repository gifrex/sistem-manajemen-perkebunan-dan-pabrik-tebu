<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    <x-slot:navnav>{{ $title }}</x-slot:navnav>

    <style>
        /* ===== PRINT STYLES ===== */
        @media print {

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

            @page {
                size: A4 portrait;
                margin: 10mm 10mm 12mm 10mm;
            }

            .no-print {
                display: none !important;
            }

            .print-container {
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .overflow-x-auto {
                overflow: visible !important;
            }

            .shadow-md,
            .rounded-lg,
            .rounded-xl {
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            table {
                width: 100% !important;
                font-size: 7.5pt !important;
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
                page-break-inside: avoid !important;
            }

            th,
            td {
                padding: 3px 5px !important;
                font-size: 7.5pt !important;
                line-height: 1.35 !important;
                word-wrap: break-word !important;
            }

            th {
                font-weight: 700 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .row-activity-header td,
            .row-lkh-header td,
            .row-plot-info td,
            .row-subtotal td,
            .row-grand-total td {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            td[rowspan] {
                border-bottom: 1px solid #6b7280 !important;
                box-decoration-break: clone !important;
                -webkit-box-decoration-break: clone !important;
            }

            .doc-header-line {
                border-bottom: 2px solid #000 !important;
            }

            .doc-title {
                font-size: 13pt !important;
                font-weight: 700 !important;
            }

            .doc-subtitle {
                font-size: 10pt !important;
            }

            .doc-meta {
                font-size: 8.5pt !important;
            }

            .terbilang-box {
                border: 1px solid #000 !important;
                padding: 6px 10px !important;
                font-size: 8.5pt !important;
            }

        }

        /* ===== SCREEN STYLES ===== */
        @media screen {
            body {
                background: #e5e7eb;
            }

            .print-container {
                max-width: 1100px;
                margin: 0 auto;
                background: #fff;
            }

            .doc-title {
                font-size: 18px;
                font-weight: 700;
            }

            .doc-subtitle {
                font-size: 14px;
            }

            .doc-meta {
                font-size: 13px;
            }
        }
    </style>

    {{-- ===== TOP BAR (no-print) ===== --}}
    <div class="no-print" style="max-width:1100px; margin:0 auto;">
        <div
            class="bg-gradient-to-r from-emerald-700 to-emerald-600 text-white px-6 py-3 flex justify-between items-center rounded-t-lg shadow">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="font-semibold text-sm tracking-wide">Preview — Rekap Upah Mingguan &middot; Tenaga Kerja
                    {{ session('tenagakerjarum') == 'Harian' ? 'Harian' : 'Borongan' }}</span>
            </div>
            <button id="print-btn"
                class="flex items-center gap-2 bg-white text-emerald-700 px-4 py-1.5 rounded text-sm font-semibold hover:bg-emerald-100 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print / Simpan PDF
            </button>
        </div>
    </div>

    {{-- ===== PRINT CONTAINER ===== --}}
    <div id="print-container" class="print-container px-10 py-8 bg-white">

        {{-- ===== DOCUMENT HEADER ===== --}}
        <div class="text-center mb-1">
            <p class="doc-subtitle font-semibold text-gray-600 uppercase tracking-widest mb-1" style="font-size:11px;">
                Divisi {{ session('companycode') }}
            </p>
            <p class="doc-title text-gray-900 uppercase tracking-wide mb-1">
                Rekap Upah Mingguan
            </p>
            <p class="doc-subtitle font-semibold text-gray-700 mb-1">
                Tenaga Kerja {{ session('tenagakerjarum') == 'Harian' ? 'Harian' : 'Borongan' }}
            </p>

            @php
                $currentLocale = \Carbon\Carbon::getLocale();
                \Carbon\Carbon::setLocale('id');
                $start = \Carbon\Carbon::parse($startDate);
                $end = \Carbon\Carbon::parse($endDate);
            @endphp
            <p class="doc-meta text-gray-600">
                Periode:
                @if ($start->format('m Y') === $end->format('m Y'))
                    {{ $start->translatedFormat('d') }} s.d. {{ $end->translatedFormat('d F Y') }}
                @else
                    {{ $start->translatedFormat('d F Y') }} s.d. {{ $end->translatedFormat('d F Y') }}
                @endif
            </p>
            @php \Carbon\Carbon::setLocale($currentLocale); @endphp
        </div>

        <div class="doc-header-line border-b-2 border-gray-800 mb-3"></div>

        {{-- No. Voucher --}}
        <div class="flex justify-between items-center mb-3 doc-meta text-gray-700">
            <span>No. Voucher: <span class="font-semibold">___________________________</span></span>
            <span class="text-gray-400 text-xs">Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
        </div>

        {{-- ===== TABLE ===== --}}
        <div class="overflow-x-auto mb-4">
            <table class="w-full border-collapse" style="border: 1.5px solid #374151;">
                <thead>
                    <tr style="background:#1e3a5f;">
                        <th class="border border-gray-400 px-2 py-2 text-white text-center" style="width:4%;">No.</th>

                        @if (session('tenagakerjarum') == 'Harian')
                            <th class="border border-gray-400 px-2 py-2 text-white text-left" style="width:25%;">Nama
                                Tenaga Kerja</th>
                            <th class="border border-gray-400 px-2 py-2 text-white text-center" style="width:13%;">Tgl
                                Kegiatan</th>
                            <th class="border border-gray-400 px-2 py-2 text-white text-right" style="width:14%;">Upah
                                Pokok (Rp)</th>
                            <th class="border border-gray-400 px-2 py-2 text-white text-right" style="width:14%;">Upah
                                Lembur (Rp)</th>
                            <th class="border border-gray-400 px-2 py-2 text-white text-right" style="width:14%;">Total
                                Upah (Rp)</th>
                        @else
                            <th class="border border-gray-400 px-2 py-2 text-white text-center" style="width:9%;">Plot
                            </th>
                            <th class="border border-gray-400 px-2 py-2 text-white text-left" style="width:21%;">
                                Material</th>
                            <th class="border border-gray-400 px-2 py-2 text-white text-right" style="width:8%;">Luas
                                (Ha)</th>
                            <th class="border border-gray-400 px-2 py-2 text-white text-center" style="width:10%;">
                                Status Tanam</th>
                            <th class="border border-gray-400 px-2 py-2 text-white text-right" style="width:8%;">Hasil
                                (Ha)</th>
                            <th class="border border-gray-400 px-2 py-2 text-white text-center" style="width:9%;">Tgl
                                Kegiatan</th>
                            <th class="border border-gray-400 px-2 py-2 text-white text-right" style="width:13%;">Biaya
                                (Rp)</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php
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
                        $isHarian = session('tenagakerjarum') == 'Harian';
                        $colspanFull = $isHarian ? 6 : 8;
                        $colspanLabel = $isHarian ? 5 : 7;
                    @endphp

                    @if (count($groupedByActivity) > 0)
                        @foreach ($groupedByActivity as $activityName => $lkhGroups)
                            @php $activitySubtotal = 0; @endphp

                            {{-- ===== ACTIVITY HEADER ROW ===== --}}
                            <tr class="row-activity-header">
                                <td colspan="{{ $colspanFull }}" class="px-3 py-2 font-bold text-white text-sm"
                                    style="background:#1e3a5f; border: 1px solid #374151; letter-spacing:0.03em;">
                                    &#9654;&nbsp; {{ $activityName }}
                                </td>
                            </tr>

                            @foreach ($lkhGroups as $lkhno => $items)
                                @php
                                    $subtotal = 0;
                                    $itemCount = count($items);
                                    $firstItem = $items[0];
                                @endphp

                                @if ($isHarian)
                                    {{-- === LKH HEADER === --}}
                                    <tr class="row-lkh-header" style="background:#dbeafe;">
                                        <td colspan="{{ $colspanFull }}" class="px-3 py-1.5 text-xs"
                                            style="border: 1px solid #93c5fd; color:#1d4ed8;">
                                            <span class="font-bold">No. LKH:</span>
                                            <span class="font-semibold">{{ $lkhno }}</span>
                                            &ensp;&bull;&ensp;
                                            <span class="font-bold">Mandor:</span>
                                            {{ $firstItem->mandorname ?? '-' }}
                                        </td>
                                    </tr>

                                    {{-- === PLOT DETAIL ROWS (Harian) === --}}
                                    @foreach ($plotDetailsByLkh->get($lkhno, collect()) as $plotRow)
                                        @php
                                            $matKey = $lkhno . '_' . $plotRow->plot;
                                            $plotMaterial = $materialDetails->get($matKey)?->materials ?? '';
                                        @endphp
                                        <tr class="row-plot-info" style="background:#f0fdf4;">
                                            <td colspan="{{ $colspanFull }}" class="px-3 py-1 text-xs"
                                                style="border: 1px solid #86efac; color:#166534;">
                                                <span class="font-semibold">Plot:</span> {{ $plotRow->plot }}
                                                &ensp;&bull;&ensp;
                                                <span class="font-semibold">Luas:</span>
                                                {{ number_format($plotRow->luasrkh, 2, ',', '.') }} Ha
                                                &ensp;&bull;&ensp;
                                                <span class="font-semibold">Status Tanam:</span>
                                                {{ $plotRow->batchdate }} / {{ $plotRow->lifecyclestatus }}
                                                &ensp;&bull;&ensp;
                                                <span class="font-semibold">Hasil:</span>
                                                {{ number_format($plotRow->luashasil, 2, ',', '.') }} Ha
                                                @if (!empty($plotMaterial))
                                                    &ensp;&bull;&ensp;
                                                    <span class="font-semibold">Material:</span> {{ $plotMaterial }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach

                                    {{-- === WORKER ROWS (Harian) === --}}
                                    @foreach ($items as $i => $item)
                                        @php
                                            $cleanTotal = preg_replace('/[^\d,.]/', '', $item->total);
                                            if (
                                                strpos($cleanTotal, ',') !== false &&
                                                strpos($cleanTotal, '.') !== false
                                            ) {
                                                $cleanTotal = str_replace('.', '', $cleanTotal);
                                                $cleanTotal = str_replace(',', '.', $cleanTotal);
                                            } elseif (strpos($cleanTotal, ',') !== false) {
                                                $cleanTotal = str_replace(',', '.', $cleanTotal);
                                            }
                                            $subtotal += floatval($cleanTotal);
                                            $rowBg = $i % 2 === 0 ? '' : 'background:#f9fafb;';
                                        @endphp
                                        <tr style="{{ $rowBg }}">
                                            <td
                                                class="border border-gray-300 px-2 py-1.5 text-center text-xs text-gray-500">
                                                {{ $rowNumber++ }}.
                                            </td>
                                            <td
                                                class="border border-gray-300 px-2 py-1.5 text-sm font-medium text-gray-800">
                                                {{ $item->namatenagakerja }}
                                            </td>
                                            <td
                                                class="border border-gray-300 px-2 py-1.5 text-center text-xs text-gray-700">
                                                {{ \Carbon\Carbon::parse($item->lkhdate)->translatedFormat('d M Y') }}
                                            </td>
                                            <td
                                                class="border border-gray-300 px-2 py-1.5 text-right text-xs text-gray-700">
                                                {{ $item->upah }}
                                            </td>
                                            <td
                                                class="border border-gray-300 px-2 py-1.5 text-right text-xs text-gray-700">
                                                {{ $item->upahlembur }}
                                            </td>
                                            <td
                                                class="border border-gray-300 px-2 py-1.5 text-right text-sm font-semibold text-gray-900">
                                                {{ $item->total }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    {{-- ===== BORONGAN ===== --}}
                                    @php
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
                                        if ($currentPlot !== null) {
                                            $plotSpans[] = [
                                                'plot' => $currentPlot,
                                                'start' => $spanStart,
                                                'count' => count($items) - $spanStart,
                                            ];
                                        }
                                    @endphp

                                    {{-- LKH Header --}}
                                    <tr class="row-lkh-header" style="background:#dbeafe;">
                                        <td colspan="{{ $colspanFull }}" class="px-3 py-1.5 text-xs"
                                            style="border: 1px solid #93c5fd; color:#1d4ed8;">
                                            <span class="font-bold">No. LKH:</span>
                                            <span class="font-semibold">{{ $lkhno }}</span>
                                            &ensp;&bull;&ensp;
                                            <span class="font-bold">Mandor:</span>
                                            {{ $firstItem->mandorname ?? '-' }}
                                        </td>
                                    </tr>

                                    @foreach ($items as $index => $item)
                                        @php
                                            $isPlotStart = false;
                                            $plotRowspan = 1;
                                            foreach ($plotSpans as $span) {
                                                if ($span['start'] === $index) {
                                                    $isPlotStart = true;
                                                    $plotRowspan = $span['count'];
                                                    break;
                                                }
                                            }
                                            $rowBg = $index % 2 === 0 ? '' : 'background:#f9fafb;';
                                        @endphp
                                        <tr style="{{ $rowBg }}">
                                            <td
                                                class="border border-gray-300 px-2 py-1.5 text-center text-xs text-gray-500">
                                                {{ $rowNumber++ }}.
                                            </td>
                                            @if ($isPlotStart)
                                                <td class="border border-gray-300 px-2 py-1.5 text-sm font-semibold text-center text-gray-800"
                                                    rowspan="{{ $plotRowspan }}" style="background:#f0f9ff;">
                                                    {{ $item->plot }}
                                                </td>
                                            @endif
                                            <td class="border border-gray-300 px-2 py-1.5 text-xs text-gray-700"
                                                style="white-space: pre-line;">{!! e($item->materials ?? '') !!}</td>
                                            <td
                                                class="border border-gray-300 px-2 py-1.5 text-right text-xs text-gray-700">
                                                {{ number_format($item->luasan, 2, ',', '.') }}</td>
                                            <td class="border border-gray-300 px-2 py-1.5 text-xs text-gray-700">
                                                {{ $item->batchdate }}/{{ $item->lifecyclestatus }}</td>
                                            <td
                                                class="border border-gray-300 px-2 py-1.5 text-right text-xs text-gray-700">
                                                {{ number_format($item->hasil, 2, ',', '.') }}</td>
                                            @if ($index === 0)
                                                <td class="border border-gray-300 px-2 py-1.5 text-center text-xs text-gray-700"
                                                    rowspan="{{ $itemCount }}" style="background:#f0f9ff;">
                                                    {{ \Carbon\Carbon::parse($item->lkhdate)->translatedFormat('d M Y') }}
                                                </td>
                                                <td class="border border-gray-300 px-2 py-1.5 text-right text-sm font-semibold text-gray-900"
                                                    rowspan="{{ $itemCount }}">
                                                    {{ $item->totalupahall }}
                                                </td>
                                            @endif
                                        </tr>

                                        @php
                                            if ($index === 0) {
                                                $cleanTotal = preg_replace('/[^\d,.]/', '', $item->totalupahall);
                                                if (
                                                    strpos($cleanTotal, ',') !== false &&
                                                    strpos($cleanTotal, '.') !== false
                                                ) {
                                                    $cleanTotal = str_replace('.', '', $cleanTotal);
                                                    $cleanTotal = str_replace(',', '.', $cleanTotal);
                                                } elseif (strpos($cleanTotal, ',') !== false) {
                                                    $cleanTotal = str_replace(',', '.', $cleanTotal);
                                                }
                                                $subtotal += floatval($cleanTotal);
                                            }
                                        @endphp
                                    @endforeach
                                @endif

                                @php $activitySubtotal += $subtotal; @endphp
                            @endforeach

                            {{-- ===== SUBTOTAL PER KEGIATAN ===== --}}
                            <tr class="row-subtotal">
                                <td class="px-3 py-2 text-right font-bold text-sm" colspan="{{ $colspanLabel }}"
                                    style="background:#fef9c3; border: 1px solid #fde047; color:#713f12;">
                                    Subtotal &mdash; {{ $activityName }}
                                </td>
                                <td class="px-3 py-2 text-right font-bold text-sm"
                                    style="background:#fef9c3; border: 1px solid #fde047; color:#713f12;">
                                    Rp {{ number_format($activitySubtotal, 2, ',', '.') }}
                                </td>
                            </tr>

                            @php $totalKeseluruhan += $activitySubtotal; @endphp
                        @endforeach

                        {{-- ===== GRAND TOTAL ===== --}}
                        <tr class="row-grand-total">
                            <td class="px-3 py-2.5 text-right font-extrabold text-sm uppercase tracking-wide"
                                colspan="{{ $colspanLabel }}"
                                style="background:#dcfce7; border: 1.5px solid #4ade80; color:#14532d;">
                                Total Keseluruhan
                            </td>
                            <td class="px-3 py-2.5 text-right font-extrabold text-sm"
                                style="background:#dcfce7; border: 1.5px solid #4ade80; color:#14532d;">
                                Rp {{ number_format($totalKeseluruhan, 2, ',', '.') }}
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="{{ $colspanFull }}"
                                class="border border-gray-300 px-4 py-6 text-center text-gray-400 italic">
                                Tidak ada data untuk periode yang dipilih.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- ===== TERBILANG ===== --}}
        @if ($totalKeseluruhan > 0)
            <div class="terbilang-box mb-5 rounded border border-gray-300 px-4 py-2 bg-gray-50 text-sm text-gray-700">
                <span class="font-semibold text-gray-600">Terbilang:</span>
                <span class="italic">
                    @php
                        // Simple terbilang helper (angka ke kata Indonesia)
                        function terbilangRupiah($angka)
                        {
                            $angka = intval(round($angka));
                            $satuan = [
                                '',
                                'satu',
                                'dua',
                                'tiga',
                                'empat',
                                'lima',
                                'enam',
                                'tujuh',
                                'delapan',
                                'sembilan',
                                'sepuluh',
                                'sebelas',
                            ];
                            if ($angka < 12) {
                                return $satuan[$angka];
                            }
                            if ($angka < 20) {
                                return $satuan[$angka - 10] . ' belas';
                            }
                            if ($angka < 100) {
                                return $satuan[intval($angka / 10)] .
                                    ' puluh' .
                                    ($angka % 10 ? ' ' . $satuan[$angka % 10] : '');
                            }
                            if ($angka < 200) {
                                return 'seratus' . ($angka % 100 ? ' ' . terbilangRupiah($angka % 100) : '');
                            }
                            if ($angka < 1000) {
                                return $satuan[intval($angka / 100)] .
                                    ' ratus' .
                                    ($angka % 100 ? ' ' . terbilangRupiah($angka % 100) : '');
                            }
                            if ($angka < 2000) {
                                return 'seribu' . ($angka % 1000 ? ' ' . terbilangRupiah($angka % 1000) : '');
                            }
                            if ($angka < 1000000) {
                                return terbilangRupiah(intval($angka / 1000)) .
                                    ' ribu' .
                                    ($angka % 1000 ? ' ' . terbilangRupiah($angka % 1000) : '');
                            }
                            if ($angka < 1000000000) {
                                return terbilangRupiah(intval($angka / 1000000)) .
                                    ' juta' .
                                    ($angka % 1000000 ? ' ' . terbilangRupiah($angka % 1000000) : '');
                            }
                            if ($angka < 1000000000000) {
                                return terbilangRupiah(intval($angka / 1000000000)) .
                                    ' miliar' .
                                    ($angka % 1000000000 ? ' ' . terbilangRupiah($angka % 1000000000) : '');
                            }
                            return terbilangRupiah(intval($angka / 1000000000000)) .
                                ' triliun' .
                                ($angka % 1000000000000 ? ' ' . terbilangRupiah($angka % 1000000000000) : '');
                        }
                        $terbilang = ucfirst(terbilangRupiah(intval(round($totalKeseluruhan)))) . ' rupiah';
                    @endphp
                    {{ $terbilang }}
                </span>
            </div>
        @endif

    </div>{{-- end print-container --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('print-btn').addEventListener('click', function() {
                window.print();
            });
        });
    </script>

</x-layout>
