{{-- resources/views/transaction/rencanakerjaharian/rkh-print.blade.php --}}
{{-- Standalone print layout - NO x-layout, no sidebar/header/footer --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <style>
        /* ============================================ */
        /* RESET & BASE                                 */
        /* ============================================ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Arial', 'Helvetica Neue', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1a1a1a;
            background: #fff;
        }

        /* ============================================ */
        /* PAGE SETUP - A4 Portrait                     */
        /* ============================================ */
        @page {
            size: A4 portrait;
            margin: 12mm 10mm 15mm 10mm;
        }

        .print-page {
            width: 190mm;
            max-width: 190mm;
            margin: 0 auto;
            padding: 10px;
        }

        /* ============================================ */
        /* SCREEN-ONLY CONTROLS                         */
        /* ============================================ */
        .screen-controls {
            position: fixed;
            top: 0; left: 0; right: 0;
            background: #1f2937;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 999;
            box-shadow: 0 2px 8px rgba(0,0,0,.3);
        }
        .screen-controls button {
            padding: 6px 18px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .btn-print { background: #16a34a; color: #fff; }
        .btn-print:hover { background: #15803d; }
        .btn-back  { background: #6b7280; color: #fff; }
        .btn-back:hover  { background: #4b5563; }

        @media screen {
            body { background: #e5e7eb; padding-top: 56px; }
            .print-page {
                background: #fff;
                box-shadow: 0 2px 12px rgba(0,0,0,.15);
                margin: 20px auto;
                padding: 20px;
                border-radius: 4px;
            }
        }

        @media print {
            .screen-controls { display: none !important; }
            body { background: #fff; padding: 0; }
            .print-page { box-shadow: none; margin: 0; padding: 0; width: 100%; max-width: 100%; }
        }

        /* ============================================ */
        /* HEADER                                       */
        /* ============================================ */
        .doc-header {
            border: 1.5px solid #000;
            padding: 8px 12px;
            margin-bottom: 10px;
        }
        .doc-header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
            padding-bottom: 6px;
            border-bottom: 1px solid #ccc;
        }
        .doc-title {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-rkhno {
            font-size: 16px;
            font-weight: 700;
            font-family: 'Courier New', monospace;
        }
        .doc-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 4px 16px;
        }
        .info-item { display: flex; gap: 4px; }
        .info-label {
            font-size: 9px;
            font-weight: 700;
            color: #555;
            text-transform: uppercase;
            white-space: nowrap;
            min-width: 55px;
        }
        .info-value { font-size: 10px; font-weight: 600; }

        .status-badge {
            display: inline-block;
            padding: 1px 6px;
            border: 1px solid #000;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        .status-approved  { border-color: #16a34a; color: #16a34a; }
        .status-waiting   { border-color: #ca8a04; color: #ca8a04; }
        .status-declined  { border-color: #dc2626; color: #dc2626; }
        .status-completed { border-color: #16a34a; color: #16a34a; }
        .status-progress  { border-color: #2563eb; color: #2563eb; }

        /* ============================================ */
        /* SUMMARY ROW                                  */
        /* ============================================ */
        .summary-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
            margin-bottom: 10px;
        }
        .summary-box {
            border: 1px solid #ccc;
            padding: 5px 8px;
        }
        .summary-box-title {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            color: #555;
            margin-bottom: 3px;
            letter-spacing: .3px;
        }
        .summary-box-content { font-size: 9px; }
        .summary-line {
            display: flex;
            justify-content: space-between;
        }
        .summary-line span:last-child { font-weight: 700; }

        /* ============================================ */
        /* TABLES                                       */
        /* ============================================ */
        .section-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: 4px 8px;
            background: #1f2937;
            color: #fff;
            margin-bottom: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        table th {
            background: #f3f4f6;
            border: 1px solid #999;
            padding: 4px 6px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
            color: #333;
        }
        table td {
            border: 1px solid #ccc;
            padding: 3px 6px;
            vertical-align: top;
        }
        table tfoot td {
            background: #f3f4f6;
            font-weight: 700;
            border: 1px solid #999;
        }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .text-left   { text-align: left; }
        .font-mono   { font-family: 'Courier New', monospace; }
        .font-bold   { font-weight: 700; }
        .text-muted  { color: #666; font-size: 8px; }

        /* ============================================ */
        /* MATERIAL SECTION                             */
        /* ============================================ */
        .material-section { margin-top: 10px; }
        .material-activity-header {
            background: #374151;
            color: #fff;
            padding: 3px 8px;
            font-size: 9px;
            font-weight: 700;
        }

        /* ============================================ */
        /* SIGNATURES                                   */
        /* ============================================ */
        .signature-section {
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .signature-grid {
            display: grid;
            gap: 0;
            border: 1px solid #999;
        }
        .signature-grid-row {
            display: grid;
            grid-template-columns: repeat(var(--sig-cols, 4), 1fr);
        }
        .signature-cell {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: center;
        }
        .sig-role {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            color: #555;
            margin-bottom: 2px;
        }
        .sig-name {
            font-size: 9px;
            font-weight: 700;
            color: #1a1a1a;
        }
        .sig-space {
            height: 45px;
        }
        .sig-date {
            font-size: 8px;
            color: #666;
        }
        .sig-status {
            font-size: 7px;
            font-weight: 700;
            margin-top: 2px;
        }

        /* ============================================ */
        /* KETERANGAN                                   */
        /* ============================================ */
        .keterangan-box {
            border: 1px solid #ccc;
            padding: 5px 8px;
            margin-bottom: 10px;
            font-size: 9px;
        }
        .keterangan-label {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            color: #555;
        }

        /* ============================================ */
        /* FOOTER                                       */
        /* ============================================ */
        .print-footer {
            margin-top: 10px;
            padding-top: 4px;
            border-top: 1px solid #ccc;
            font-size: 7px;
            color: #999;
            display: flex;
            justify-content: space-between;
        }

        /* Page break helper */
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    {{-- Screen-only toolbar --}}
    <div class="screen-controls">
        <div style="display:flex; align-items:center; gap:12px;">
            <span style="font-size:13px; font-weight:700;">Print Preview — {{ $rkhHeader->rkhno ?? '' }}</span>
        </div>
        <div style="display:flex; gap:8px;">
            <button class="btn-back" onclick="history.back()">← Kembali</button>
            <button class="btn-print" onclick="window.print()">🖨 Print</button>
        </div>
    </div>

    <div class="print-page">

        {{-- ============================================ --}}
        {{-- DOCUMENT HEADER                              --}}
        {{-- ============================================ --}}
        @php
            // Approval status
            $approvalStatus = 'Waiting';
            $approvalClass  = 'status-waiting';
            if (isset($rkhHeader->jumlahapproval) && $rkhHeader->jumlahapproval > 0) {
                $approvedCount = 0;
                if ($rkhHeader->approval1flag === '1') $approvedCount++;
                if ($rkhHeader->approval2flag === '1') $approvedCount++;
                if ($rkhHeader->approval3flag === '1') $approvedCount++;

                if ($rkhHeader->approval1flag === '0' || $rkhHeader->approval2flag === '0' || $rkhHeader->approval3flag === '0') {
                    $approvalClass = 'status-declined';
                    if ($rkhHeader->approval1flag === '0')      $approvalStatus = 'Declined L1';
                    elseif ($rkhHeader->approval2flag === '0')  $approvalStatus = 'Declined L2';
                    elseif ($rkhHeader->approval3flag === '0')  $approvalStatus = 'Declined L3';
                } elseif ($approvedCount === (int)$rkhHeader->jumlahapproval) {
                    $approvalStatus = 'Approved';
                    $approvalClass  = 'status-approved';
                } else {
                    $approvalStatus = "Waiting ({$approvedCount}/{$rkhHeader->jumlahapproval})";
                }
            }

        @endphp

        <div class="doc-header">
            <div class="doc-header-top">
                <div>
                    <div class="doc-title">Rencana Kerja Harian</div>
                    <div style="font-size:9px; color:#666; margin-top:2px;">Dokumen ini dicetak dari sistem</div>
                </div>
                <div style="text-align:right;">
                    <div class="doc-rkhno">{{ $rkhHeader->rkhno ?? '-' }}</div>
                    <div style="margin-top:4px;">
                        <span class="status-badge {{ $approvalClass }}">{{ $approvalStatus }}</span>
                    </div>
                </div>
            </div>
            <div class="doc-info-grid">
                <div class="info-item">
                    <span class="info-label">Tanggal:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($rkhHeader->rkhdate)->format('d/m/Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Hari:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($rkhHeader->rkhdate)->locale('id')->isoFormat('dddd') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Mandor:</span>
                    <span class="info-value">{{ $rkhHeader->mandorid ?? '-' }} — {{ $rkhHeader->mandor_nama ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Group:</span>
                    <span class="info-value">{{ $rkhHeader->activity_group_name ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Keterangan --}}
        @if($rkhHeader->keterangan)
        <div class="keterangan-box">
            <span class="keterangan-label">Keterangan:</span>
            {{ $rkhHeader->keterangan }}
        </div>
        @endif

        {{-- ============================================ --}}
        {{-- SUMMARY ROW                                  --}}
        {{-- ============================================ --}}
        @php
            $absenSummary   = collect($absentenagakerja ?? [])->where('mandorid', $rkhHeader->mandorid);
            $lakiCount      = $absenSummary->where('gender', 'L')->count();
            $perempuanCount = $absenSummary->where('gender', 'P')->count();
            $totalAbsen     = $lakiCount + $perempuanCount;
        @endphp

        <div class="summary-row">
            <div class="summary-box">
                <div class="summary-box-title">Absensi</div>
                <div class="summary-box-content">
                    <div class="summary-line"><span>Laki-laki</span><span>{{ $lakiCount }}</span></div>
                    <div class="summary-line"><span>Perempuan</span><span>{{ $perempuanCount }}</span></div>
                    <div class="summary-line" style="border-top:1px solid #ddd; padding-top:2px; margin-top:2px;">
                        <span style="font-weight:700;">Total</span><span>{{ $totalAbsen }}</span>
                    </div>
                </div>
            </div>

            <div class="summary-box">
                <div class="summary-box-title">Pekerja per Aktivitas</div>
                <div class="summary-box-content">
                    @foreach($workersByActivity as $w)
                    <div class="summary-line">
                        <span>{{ $w->activitycode }}</span>
                        <span>L:{{ $w->jumlahlaki ?? 0 }} P:{{ $w->jumlahperempuan ?? 0 }} = {{ $w->jumlahtenagakerja ?? 0 }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="summary-box">
                <div class="summary-box-title">Kendaraan</div>
                <div class="summary-box-content">
                    @foreach($kendaraanByActivity as $actCode => $vehicles)
                    <div class="summary-line">
                        <span>{{ $actCode }}</span>
                        <span>{{ $vehicles->count() }} unit</span>
                    </div>
                    @endforeach
                    @if($kendaraanByActivity->isEmpty())
                    <div style="color:#999;">Tidak ada kendaraan</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- DETAIL RENCANA KERJA                         --}}
        {{-- ============================================ --}}
        <div class="section-title">Detail Rencana Kerja</div>
        <table>
            <thead>
                <tr>
                    <th style="width:25px;">No</th>
                    <th style="width:auto;" class="text-left">Aktivitas</th>
                    <th style="width:45px;">Blok</th>
                    <th style="width:45px;">Plot</th>
                    <th style="width:80px;">Info Batch</th>
                    <th style="width:55px;">Luas (Ha)</th>
                    <th style="width:70px;">Material</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rkhDetails as $index => $detail)
                    @php
                        $luasPlot = $detail->luasarea ?? 0;
                        $totalSudah = $detail->total_sudah_dikerjakan ?? 0;
                        $luasSisa = $luasPlot - $totalSudah;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <span class="font-bold">{{ $detail->activitycode ?? '-' }}</span>
                            <span class="text-muted"> — {{ $detail->activityname ?? '-' }}</span>
                        </td>
                        <td class="text-center font-bold">{{ $detail->blok ?? '-' }}</td>
                        <td class="text-center font-bold">{{ $detail->plot ?? '-' }}</td>
                        <td style="font-size:8px;">
                            @if($detail->batch_number && $detail->batch_lifecycle)
                                <div><span class="font-bold">{{ $detail->batch_lifecycle }}</span> — {{ $detail->batch_number }}</div>
                                @if($detail->batcharea)
                                    <div class="text-muted">Area: {{ number_format($detail->batcharea, 2) }} Ha</div>
                                @endif
                                @if($detail->tanggalpanen)
                                    <div class="text-muted">Panen: {{ \Carbon\Carbon::parse($detail->tanggalpanen)->format('d/m/Y') }}</div>
                                @endif
                            @else
                                <div class="text-muted">Luas: {{ number_format($luasPlot, 2) }} Ha</div>
                                <div class="text-muted">Sisa: {{ number_format($luasSisa, 2) }} Ha</div>
                            @endif
                        </td>
                        <td class="text-right font-bold">{{ $detail->luasarea ? number_format($detail->luasarea, 2) : '-' }}</td>
                        <td class="text-center" style="font-size:8px;">
                            @if($detail->usingmaterial == 1 && $detail->herbisidagroupname)
                                <span class="font-bold">{{ $detail->herbisidagroupname }}</span>
                            @elseif($detail->usingmaterial == 1)
                                Ya
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding:12px; color:#999;">Tidak ada data detail RKH</td>
                    </tr>
                @endforelse
            </tbody>
            @if($rkhDetails->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="5" class="text-center font-bold" style="font-size:9px;">TOTAL LUAS</td>
                    <td class="text-right font-bold">{{ number_format($rkhDetails->sum('luasarea'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>

        {{-- ============================================ --}}
        {{-- KENDARAAN DETAIL                             --}}
        {{-- ============================================ --}}
        @if($kendaraanByActivity->isNotEmpty())
        <div style="margin-top:10px;">
            <div class="section-title">Detail Kendaraan</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:30px;">No</th>
                        <th class="text-left">Aktivitas</th>
                        <th>No. Kendaraan</th>
                        <th>Operator</th>
                        <th>Helper</th>
                    </tr>
                </thead>
                <tbody>
                    @php $vIdx = 0; @endphp
                    @foreach($kendaraanByActivity as $actCode => $vehicles)
                        @foreach($vehicles as $v)
                            @php $vIdx++; @endphp
                            <tr>
                                <td class="text-center">{{ $vIdx }}</td>
                                <td>
                                    <span class="font-bold">{{ $actCode }}</span>
                                    <span class="text-muted"> — {{ $v->activityname ?? '' }}</span>
                                </td>
                                <td class="text-center font-bold font-mono">{{ $v->nokendaraan }}</td>
                                <td>{{ $v->operator_nama ?? '-' }}</td>
                                <td>{{ ($v->usinghelper && $v->helper_nama) ? $v->helper_nama : '-' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- ============================================ --}}
        {{-- MATERIAL DETAIL (FLAT)                       --}}
        {{-- ============================================ --}}
        @php
            // Build material data grouped by activity → item → plots
            $materialGrouped = collect($materialData ?? []);
            $hasMaterial = false;

            $rekapByActivity = [];
            foreach ($materialGrouped as $key => $rows) {
                $rowsArr = is_array($rows) ? $rows : (is_object($rows) && method_exists($rows, 'toArray') ? $rows->toArray() : (array)$rows);
                if (empty($rowsArr)) continue;
                $hasMaterial = true;
                $first = is_object($rowsArr[0] ?? null) ? $rowsArr[0] : (object)($rowsArr[0] ?? []);
                $actCode = $first->activitycode ?? explode('||', $key)[0] ?? '-';
                $actName = $first->activityname ?? $actCode;

                if (!isset($rekapByActivity[$actCode])) {
                    $rekapByActivity[$actCode] = [
                        'activityname' => $actName,
                        'items' => []
                    ];
                }

                foreach ($rowsArr as $row) {
                    $r = is_object($row) ? $row : (object)$row;
                    $ic = $r->itemcode ?? '';
                    if (!isset($rekapByActivity[$actCode]['items'][$ic])) {
                        $rekapByActivity[$actCode]['items'][$ic] = [
                            'itemname' => $r->itemname ?? '-',
                            'unit' => $r->unit ?? '-',
                            'totalqty' => 0,
                            'plots' => []
                        ];
                    }
                    $qty = floatval($r->qty ?? 0);
                    $rekapByActivity[$actCode]['items'][$ic]['totalqty'] += $qty;
                    $plot = $r->plot ?? '-';
                    if (!isset($rekapByActivity[$actCode]['items'][$ic]['plots'][$plot])) {
                        $rekapByActivity[$actCode]['items'][$ic]['plots'][$plot] = [
                            'luasarea' => floatval($r->luasarea ?? 0),
                            'dosageperha' => floatval($r->dosageperha ?? 0),
                            'qty' => 0
                        ];
                    }
                    $rekapByActivity[$actCode]['items'][$ic]['plots'][$plot]['qty'] += $qty;
                }
            }
        @endphp

        @if($hasMaterial)
        <div class="material-section">
            <div class="section-title">
                Rekap Material
                @if($isMaterialEstimated ?? false)
                    <span style="font-weight:400; font-size:8px; margin-left:8px;">(ESTIMASI — belum di-generate)</span>
                @endif
            </div>

            @foreach($rekapByActivity as $actCode => $actData)
                <div class="material-activity-header">{{ $actCode }} — {{ $actData['activityname'] }}</div>
                <table>
                    <thead>
                        <tr>
                            <th class="text-left" style="width:auto;">Material</th>
                            <th style="width:40px;">Plot</th>
                            <th style="width:50px;">Luas (Ha)</th>
                            <th style="width:55px;">Dosis/Ha</th>
                            <th style="width:40px;">Sat</th>
                            <th style="width:55px;">Hasil</th>
                            <th style="width:60px;">Pembulatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($actData['items'] as $itemCode => $item)
                            @php $plotCount = count($item['plots']); $firstPlot = true; @endphp
                            @foreach($item['plots'] as $plotName => $plotData)
                                <tr>
                                    @if($firstPlot)
                                        <td rowspan="{{ $plotCount }}" style="vertical-align:middle;">
                                            <span class="font-bold font-mono">{{ $itemCode }}</span>
                                            <span class="text-muted"> {{ $item['itemname'] }}</span>
                                        </td>
                                    @endif
                                    <td class="text-center font-bold">{{ $plotName }}</td>
                                    <td class="text-right">{{ number_format($plotData['luasarea'], 2) }}</td>
                                    <td class="text-right">{{ number_format($plotData['dosageperha'], 3) }}</td>
                                    @if($firstPlot)
                                        <td rowspan="{{ $plotCount }}" class="text-center" style="vertical-align:middle;">{{ $item['unit'] }}</td>
                                    @endif
                                    <td class="text-right text-muted" style="font-style:italic;">
                                        {{ number_format($plotData['dosageperha'] * $plotData['luasarea'], 2) }}
                                    </td>
                                    <td class="text-right font-bold">{{ number_format($plotData['qty'], 3) }}</td>
                                </tr>
                                @php $firstPlot = false; @endphp
                            @endforeach
                            {{-- Subtotal per item --}}
                            @if($plotCount > 1)
                            <tr style="background:#f9fafb;">
                                <td colspan="5" class="text-right font-bold" style="font-size:8px;">Subtotal {{ $itemCode }}</td>
                                <td></td>
                                <td class="text-right font-bold" style="color:#16a34a;">{{ number_format($item['totalqty'], 3) }}</td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </div>
        @endif

        {{-- ============================================ --}}
        {{-- TANDA TANGAN / APPROVAL                      --}}
        {{-- ============================================ --}}
        @php
            // Build signature columns
            $signatureCols = [];

            // Dibuat oleh (Mandor)
            $signatureCols[] = [
                'role'   => 'Dibuat Oleh',
                'title'  => 'Mandor',
                'name'   => $rkhHeader->mandor_nama ?? $rkhHeader->mandorid ?? '-',
                'date'   => \Carbon\Carbon::parse($rkhHeader->rkhdate)->format('d/m/Y'),
                'status' => null,
                'flag'   => null,
            ];

            // Approval levels - langsung dari rkhhdr
            $jumlahApproval = (int)($rkhHeader->jumlahapproval ?? 0);

            // Lookup approval user names dari DB (lazy, sekali query)
            $approvalUserIds = array_filter([
                $rkhHeader->approval1userid ?? null,
                $rkhHeader->approval2userid ?? null,
                $rkhHeader->approval3userid ?? null,
            ]);
            $approvalNames = [];
            if (!empty($approvalUserIds)) {
                $approvalNames = \Illuminate\Support\Facades\DB::table('user')
                    ->whereIn('userid', $approvalUserIds)
                    ->pluck('name', 'userid')
                    ->toArray();
            }

            // Lookup jabatan names
            $jabatanIds = array_filter([
                $rkhHeader->approval1idjabatan ?? null,
                $rkhHeader->approval2idjabatan ?? null,
                $rkhHeader->approval3idjabatan ?? null,
            ]);
            $jabatanNames = [];
            if (!empty($jabatanIds)) {
                $jabatanNames = \Illuminate\Support\Facades\DB::table('jabatan')
                    ->whereIn('idjabatan', $jabatanIds)
                    ->pluck('namajabatan', 'idjabatan')
                    ->toArray();
            }

            if ($jumlahApproval >= 1) {
                $signatureCols[] = [
                    'role'   => 'Approval Level 1',
                    'title'  => $jabatanNames[$rkhHeader->approval1idjabatan] ?? 'Approver L1',
                    'name'   => $approvalNames[$rkhHeader->approval1userid] ?? ($rkhHeader->approval1userid ?? '-'),
                    'date'   => $rkhHeader->approval1date ? \Carbon\Carbon::parse($rkhHeader->approval1date)->format('d/m/Y H:i') : '-',
                    'status' => $rkhHeader->approval1flag,
                    'flag'   => $rkhHeader->approval1flag,
                ];
            }

            if ($jumlahApproval >= 2) {
                $signatureCols[] = [
                    'role'   => 'Approval Level 2',
                    'title'  => $jabatanNames[$rkhHeader->approval2idjabatan] ?? 'Approver L2',
                    'name'   => $approvalNames[$rkhHeader->approval2userid] ?? ($rkhHeader->approval2userid ?? '-'),
                    'date'   => $rkhHeader->approval2date ? \Carbon\Carbon::parse($rkhHeader->approval2date)->format('d/m/Y H:i') : '-',
                    'status' => $rkhHeader->approval2flag,
                    'flag'   => $rkhHeader->approval2flag,
                ];
            }

            if ($jumlahApproval >= 3) {
                $signatureCols[] = [
                    'role'   => 'Approval Level 3',
                    'title'  => $jabatanNames[$rkhHeader->approval3idjabatan] ?? 'Approver L3',
                    'name'   => $approvalNames[$rkhHeader->approval3userid] ?? ($rkhHeader->approval3userid ?? '-'),
                    'date'   => $rkhHeader->approval3date ? \Carbon\Carbon::parse($rkhHeader->approval3date)->format('d/m/Y H:i') : '-',
                    'status' => $rkhHeader->approval3flag,
                    'flag'   => $rkhHeader->approval3flag,
                ];
            }

            $colCount = count($signatureCols);
        @endphp

        <div class="signature-section">
            <div class="signature-grid" style="--sig-cols: {{ $colCount }};">
                {{-- Row 1: Roles --}}
                <div class="signature-grid-row">
                    @foreach($signatureCols as $sig)
                    <div class="signature-cell" style="background:#f3f4f6;">
                        <div class="sig-role">{{ $sig['role'] }}</div>
                        <div style="font-size:8px; color:#666;">{{ $sig['title'] }}</div>
                    </div>
                    @endforeach
                </div>

                {{-- Row 2: Signature space --}}
                <div class="signature-grid-row">
                    @foreach($signatureCols as $sig)
                    <div class="signature-cell">
                        <div class="sig-space">
                            @if($sig['flag'] === '1')
                                <div style="margin-top:12px; font-size:20px; color:#16a34a;">✓</div>
                            @elseif($sig['flag'] === '0')
                                <div style="margin-top:12px; font-size:20px; color:#dc2626;">✗</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Row 3: Names + Date --}}
                <div class="signature-grid-row">
                    @foreach($signatureCols as $sig)
                    <div class="signature-cell">
                        <div class="sig-name">{{ $sig['name'] }}</div>
                        <div class="sig-date">{{ $sig['date'] }}</div>
                        @if($sig['flag'] === '1')
                            <div class="sig-status" style="color:#16a34a;">APPROVED</div>
                        @elseif($sig['flag'] === '0')
                            <div class="sig-status" style="color:#dc2626;">DECLINED</div>
                        @elseif($sig['flag'] === null && $sig['status'] === null && $sig['role'] !== 'Dibuat Oleh')
                            <div class="sig-status" style="color:#ca8a04;">PENDING</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- FOOTER                                       --}}
        {{-- ============================================ --}}
        <div class="print-footer">
            <span>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</span>
            <span>{{ $rkhHeader->rkhno ?? '' }} — Halaman 1</span>
        </div>

    </div>

</body>
</html>