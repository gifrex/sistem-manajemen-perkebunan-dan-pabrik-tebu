{{-- resources/views/transaction/rencanakerjaharian/lkh-print/lkh-print-panen.blade.php --}}
{{-- Standalone print layout for LKH Panen --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Arial', 'Helvetica Neue', sans-serif;
            font-size: 10px; line-height: 1.4; color: #1a1a1a; background: #fff;
        }

        @page { size: A4 landscape; margin: 10mm 8mm 12mm 8mm; }
        .print-page { width: 277mm; max-width: 277mm; margin: 0 auto; padding: 10px; }

        /* Screen controls */
        .screen-controls {
            position: fixed; top: 0; left: 0; right: 0;
            background: #1f2937; color: #fff; padding: 10px 20px;
            display: flex; align-items: center; justify-content: space-between;
            z-index: 999; box-shadow: 0 2px 8px rgba(0,0,0,.3);
        }
        .screen-controls button {
            padding: 6px 18px; border: none; border-radius: 4px;
            font-size: 12px; font-weight: 700; cursor: pointer;
            text-transform: uppercase; letter-spacing: .5px;
        }
        .btn-print { background: #16a34a; color: #fff; }
        .btn-print:hover { background: #15803d; }
        .btn-back { background: #6b7280; color: #fff; }
        .btn-back:hover { background: #4b5563; }

        @media screen {
            body { background: #e5e7eb; padding-top: 56px; }
            .print-page {
                background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,.15);
                margin: 20px auto; padding: 20px; border-radius: 4px;
            }
        }
        @media print {
            .screen-controls { display: none !important; }
            body { background: #fff; padding: 0; }
            .print-page { box-shadow: none; margin: 0; padding: 0; width: 100%; max-width: 100%; }
        }

        /* Header */
        .doc-header { border: 1.5px solid #000; padding: 8px 12px; margin-bottom: 8px; }
        .doc-header-top {
            display: flex; justify-content: space-between; align-items: flex-start;
            margin-bottom: 6px; padding-bottom: 6px; border-bottom: 1px solid #ccc;
        }
        .doc-title { font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .doc-subtitle { font-size: 11px; font-weight: 600; color: #555; margin-top: 2px; }
        .doc-lkhno { font-size: 16px; font-weight: 700; font-family: 'Courier New', monospace; }

        .doc-info-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 4px 16px; }
        .info-item { display: flex; gap: 4px; }
        .info-label { font-size: 9px; font-weight: 700; color: #555; text-transform: uppercase; white-space: nowrap; min-width: 55px; }
        .info-value { font-size: 10px; font-weight: 600; }

        .status-badge {
            display: inline-block; padding: 1px 6px; border: 1px solid #000;
            font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px;
        }
        .status-approved  { border-color: #16a34a; color: #16a34a; }
        .status-submitted { border-color: #2563eb; color: #2563eb; }
        .status-draft     { border-color: #ca8a04; color: #ca8a04; }
        .status-completed { border-color: #16a34a; color: #16a34a; }

        /* Summary */
        .summary-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 8px; }
        .summary-box { border: 1px solid #ccc; padding: 5px 8px; }
        .summary-box-title { font-size: 8px; font-weight: 700; text-transform: uppercase; color: #555; margin-bottom: 3px; }
        .summary-box-value { font-size: 14px; font-weight: 700; }
        .summary-box-sub { font-size: 8px; color: #666; margin-top: 1px; }

        /* Tables */
        .section-title {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .5px; padding: 4px 8px; background: #1f2937;
            color: #fff; margin-bottom: 0; margin-top: 10px;
        }
        .section-subtitle { font-size: 8px; font-weight: 400; color: #ccc; margin-left: 8px; }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        table th {
            background: #f3f4f6; border: 1px solid #999; padding: 4px 5px;
            font-size: 8px; font-weight: 700; text-transform: uppercase; text-align: center; color: #333;
        }
        table td { border: 1px solid #ccc; padding: 3px 5px; vertical-align: top; }
        table tfoot td { background: #f3f4f6; font-weight: 700; border: 1px solid #999; }

        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .text-left   { text-align: left; }
        .font-mono   { font-family: 'Courier New', monospace; }
        .font-bold   { font-weight: 700; }
        .text-muted  { color: #666; font-size: 8px; }
        .text-green  { color: #16a34a; }
        .text-orange { color: #ea580c; }
        .text-blue   { color: #2563eb; }
        .text-red    { color: #dc2626; }

        .bg-orange-light { background: #fff7ed; }
        .bg-green-light  { background: #f0fdf4; }
        .bg-blue-light   { background: #eff6ff; }
        .bg-gray-light   { background: #f9fafb; }

        /* Lifecycle badge */
        .lifecycle-badge {
            display: inline-block; padding: 1px 5px; border: 1px solid; border-radius: 2px;
            font-size: 8px; font-weight: 700;
        }
        .lc-pc  { border-color: #ca8a04; color: #ca8a04; background: #fefce8; }
        .lc-rc1 { border-color: #16a34a; color: #16a34a; background: #f0fdf4; }
        .lc-rc2 { border-color: #2563eb; color: #2563eb; background: #eff6ff; }
        .lc-rc3 { border-color: #9333ea; color: #9333ea; background: #faf5ff; }

        /* Hari tebang */
        .hari-badge {
            display: inline-block; padding: 1px 5px; border-radius: 2px;
            font-size: 8px; font-weight: 700;
        }
        .hari-1 { background: #fef3c7; color: #92400e; }
        .hari-danger { background: #fee2e2; color: #991b1b; }

        /* Petak baru */
        .petak-baru-box {
            border: 1px solid #ccc; margin-top: 10px; max-width: 280px;
        }
        .petak-baru-header {
            background: #1f2937; color: #fff; padding: 4px 8px;
            font-size: 9px; font-weight: 700; text-transform: uppercase;
        }
        .petak-baru-item {
            display: flex; justify-content: space-between; padding: 3px 8px;
            border-bottom: 1px solid #eee; font-size: 9px;
        }
        .petak-baru-total {
            display: flex; justify-content: space-between; padding: 4px 8px;
            background: #f3f4f6; font-size: 9px; font-weight: 700; border-top: 1.5px solid #999;
        }

        /* Keterangan */
        .keterangan-box { border: 1px solid #ccc; padding: 5px 8px; margin-top: 10px; font-size: 9px; }
        .keterangan-label { font-size: 8px; font-weight: 700; text-transform: uppercase; color: #555; }

        /* Signatures */
        .signature-section { margin-top: 20px; page-break-inside: avoid; }
        .signature-grid { display: grid; gap: 0; border: 1px solid #999; }
        .signature-grid-row { display: grid; grid-template-columns: repeat(var(--sig-cols, 4), 1fr); }
        .signature-cell { border: 1px solid #ccc; padding: 6px 8px; text-align: center; }
        .sig-role { font-size: 8px; font-weight: 700; text-transform: uppercase; color: #555; margin-bottom: 2px; }
        .sig-name { font-size: 9px; font-weight: 700; color: #1a1a1a; }
        .sig-space { height: 45px; }
        .sig-date { font-size: 8px; color: #666; }
        .sig-status { font-size: 7px; font-weight: 700; margin-top: 2px; }

        /* Footer */
        .print-footer {
            margin-top: 10px; padding-top: 4px; border-top: 1px solid #ccc;
            font-size: 7px; color: #999; display: flex; justify-content: space-between;
        }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    <div class="screen-controls">
        <span style="font-size:13px; font-weight:700;">Print Preview — {{ $lkhData->lkhno ?? '' }} (Panen)</span>
        <div style="display:flex; gap:8px;">
            <button class="btn-back" onclick="history.back()">← Kembali</button>
            <button class="btn-print" onclick="window.print()">🖨 Print</button>
        </div>
    </div>

    <div class="print-page">

        {{-- ============================================ --}}
        {{-- HEADER                                       --}}
        {{-- ============================================ --}}
        @php
            $statusLabel = $lkhData->status ?? 'DRAFT';
            $statusClass = match($statusLabel) {
                'APPROVED'  => 'status-approved',
                'SUBMITTED' => 'status-submitted',
                'COMPLETED' => 'status-completed',
                default     => 'status-draft',
            };
        @endphp

        <div class="doc-header">
            <div class="doc-header-top">
                <div>
                    <div class="doc-title">Laporan Kegiatan Harian (LKH)</div>
                    <div class="doc-subtitle">Panen</div>
                </div>
                <div style="text-align:right;">
                    <div class="doc-lkhno">{{ $lkhData->lkhno ?? '-' }}</div>
                    <div style="margin-top:4px;">
                        <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                </div>
            </div>
            <div class="doc-info-grid">
                <div class="info-item">
                    <span class="info-label">Tanggal:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($lkhData->lkhdate)->format('d/m/Y') }} ({{ \Carbon\Carbon::parse($lkhData->lkhdate)->locale('id')->isoFormat('dddd') }})</span>
                </div>
                <div class="info-item">
                    <span class="info-label">No. RKH:</span>
                    <span class="info-value font-mono">{{ $lkhData->rkhno ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Mandor:</span>
                    <span class="info-value">{{ $lkhData->mandorid ?? '-' }} — {{ $lkhData->mandornama ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Aktivitas:</span>
                    <span class="info-value">{{ $lkhData->activitycode ?? '-' }} — {{ $lkhData->activityname ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- SUMMARY                                      --}}
        {{-- ============================================ --}}
        <div class="summary-row">
            <div class="summary-box">
                <div class="summary-box-title">Total Plot</div>
                <div class="summary-box-value">{{ $lkhPanenDetails->count() }}</div>
                <div class="summary-box-sub">plot</div>
            </div>
            <div class="summary-box">
                <div class="summary-box-title">Total HC (Hasil)</div>
                <div class="summary-box-value text-green">{{ number_format($lkhPanenDetails->sum('hc'), 2) }}</div>
                <div class="summary-box-sub">Ha</div>
            </div>
            <div class="summary-box">
                <div class="summary-box-title">Total STC (Sisa)</div>
                <div class="summary-box-value text-orange">{{ number_format($lkhPanenDetails->sum('stc'), 2) }}</div>
                <div class="summary-box-sub">Ha</div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- KONTRAKTOR & SUBKONTRAKTOR                   --}}
        {{-- ============================================ --}}
        @if($kontraktorSummary->count() > 0)
        <div class="section-title">Kontraktor & Subkontraktor</div>
        <table>
            <thead>
                <tr>
                    <th style="width:25px;">No</th>
                    <th style="width:70px;">ID</th>
                    <th class="text-left">Nama Kontraktor</th>
                    <th style="width:60px;">Subkon</th>
                    <th style="width:50px;">Plot</th>
                    <th class="text-left">List Plot</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kontraktorSummary as $index => $k)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-mono">{{ $k->kontraktor_id }}</td>
                    <td class="font-bold">{{ $k->kontraktor_nama ?? 'Unknown' }}</td>
                    <td class="text-center font-bold">{{ $k->total_subkontraktor }}</td>
                    <td class="text-center font-bold">{{ $k->total_plot }}</td>
                    <td class="font-mono" style="font-size:8px;">{{ $k->list_plot }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ============================================ --}}
        {{-- LAPORAN PANEN                                --}}
        {{-- ============================================ --}}
        <div class="section-title">Laporan Panen</div>
        <table>
            <thead>
                <tr>
                    <th style="width:40px;">Blok</th>
                    <th style="width:45px;">Plot</th>
                    <th style="width:55px;">Luas Batch (Ha)</th>
                    <th style="width:35px;">Status</th>
                    <th style="width:30px;">Hari</th>
                    <th style="width:50px;" class="bg-orange-light">STC (Ha)</th>
                    <th style="width:50px;" class="bg-green-light">HC (Ha)</th>
                    <th style="width:50px;" class="bg-gray-light">BC (Ha)</th>
                    <th style="width:45px;" class="bg-blue-light">FB Rit</th>
                    <th style="width:45px;" class="bg-blue-light">FB Ton</th>
                    <th class="text-left">Subkontraktor</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lkhPanenDetails as $item)
                <tr>
                    <td class="text-center font-bold">{{ $item->blok }}</td>
                    <td class="text-center font-bold">{{ $item->plot }}</td>
                    <td class="text-right">{{ number_format($item->batcharea, 2) }}</td>
                    <td class="text-center">
                        @php
                            $lcClass = match($item->kodestatus) {
                                'PC'  => 'lc-pc',
                                'RC1' => 'lc-rc1',
                                'RC2' => 'lc-rc2',
                                'RC3' => 'lc-rc3',
                                default => '',
                            };
                        @endphp
                        <span class="lifecycle-badge {{ $lcClass }}">{{ $item->kodestatus ?? '-' }}</span>
                    </td>
                    <td class="text-center">
                        @if($item->haritebang === '-' || is_null($item->haritebang))
                            <span class="text-muted">-</span>
                        @elseif($item->haritebang == 1)
                            <span class="hari-badge hari-1">1</span>
                        @else
                            {{ $item->haritebang }}
                        @endif
                    </td>
                    <td class="text-right font-bold text-orange bg-orange-light">{{ number_format($item->stc, 2) }}</td>
                    @if(!is_null($item->hc))
                        <td class="text-right font-bold text-green bg-green-light">{{ number_format($item->hc, 2) }}</td>
                        <td class="text-right bg-gray-light">{{ number_format($item->bc, 2) }}</td>
                        <td class="text-right text-blue bg-blue-light">{{ $item->fieldbalancerit ? number_format($item->fieldbalancerit, 2) : '-' }}</td>
                        <td class="text-right text-blue bg-blue-light">{{ $item->fieldbalanceton ? number_format($item->fieldbalanceton, 2) : '-' }}</td>
                    @else
                        <td colspan="4" class="text-center text-muted" style="font-style:italic;">Menunggu input hasil</td>
                    @endif
                    <td style="font-size:8px;">
                        @php
                            $plotSk = $subkontraktorDetail->where('plot', $item->plot);
                        @endphp
                        @if($plotSk->count() > 0)
                            @php $kontraktor = $plotSk->first(); @endphp
                            <div class="font-bold">{{ $kontraktor->kontraktor_id }} — {{ $kontraktor->kontraktor_nama ?? '' }}</div>
                            @foreach($plotSk as $sk)
                                <div style="margin-left:6px;">• {{ $sk->subkontraktor_nama ?? $sk->subkontraktor_id }} ({{ $sk->jumlah_sj }} SJ)</div>
                            @endforeach
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding:12px; color:#999;">Tidak ada data panen</td>
                </tr>
                @endforelse
            </tbody>
            @if($lkhPanenDetails->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right font-bold">TOTAL:</td>
                    <td class="text-right font-bold text-orange bg-orange-light">{{ number_format($lkhPanenDetails->sum('stc'), 2) }}</td>
                    <td class="text-right font-bold text-green bg-green-light">{{ number_format($lkhPanenDetails->sum('hc'), 2) }}</td>
                    <td class="text-right font-bold bg-gray-light">{{ number_format($lkhPanenDetails->sum('bc'), 2) }}</td>
                    <td class="text-right font-bold text-blue bg-blue-light">{{ number_format($lkhPanenDetails->sum('fieldbalancerit'), 2) }}</td>
                    <td class="text-right font-bold text-blue bg-blue-light">{{ number_format($lkhPanenDetails->sum('fieldbalanceton'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>

        {{-- ============================================ --}}
        {{-- PETAK BARU + ONGOING (side by side)          --}}
        {{-- ============================================ --}}
        @php
            $petakBaru = $lkhPanenDetails->filter(fn($item) => $item->haritebang == 1);
        @endphp

        <div style="display:grid; grid-template-columns: 280px 1fr; gap: 12px; margin-top: 10px;">

            {{-- Petak Baru --}}
            <div class="petak-baru-box">
                <div class="petak-baru-header">Petak Baru Hari Ini (Hari Tebang 1)</div>
                @forelse($petakBaru as $pb)
                    <div class="petak-baru-item">
                        <span class="font-bold">{{ $pb->blok }}-{{ $pb->plot }}</span>
                        <span>{{ number_format($pb->batcharea, 2) }} Ha</span>
                    </div>
                @empty
                    <div style="padding:10px; text-align:center; color:#999; font-size:9px;">Tidak ada petak baru</div>
                @endforelse
                @if($petakBaru->count() > 0)
                <div class="petak-baru-total">
                    <span>{{ $petakBaru->count() }} Plot</span>
                    <span>{{ number_format($petakBaru->sum('batcharea'), 2) }} Ha</span>
                </div>
                @endif
            </div>

            {{-- Ongoing Plots --}}
            @if($ongoingPlots->count() > 0)
            <div>
                <div class="section-title" style="margin-top:0;">Plot Masih Ongoing<span class="section-subtitle">Plot dipanen tapi tidak ada di laporan hari ini</span></div>
                <table>
                    <thead>
                        <tr>
                            <th>Blok</th>
                            <th>Plot</th>
                            <th>Batch</th>
                            <th>Status</th>
                            <th>Luas Batch</th>
                            <th>Dipanen</th>
                            <th>Sisa</th>
                            <th>Terakhir</th>
                            <th>Skip</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ongoingPlots as $op)
                        <tr>
                            <td class="text-center font-bold">{{ $op->blok }}</td>
                            <td class="text-center font-bold">{{ $op->plot }}</td>
                            <td class="font-mono" style="font-size:7px;">{{ $op->batchno }}</td>
                            <td class="text-center">
                                @php
                                    $opLcClass = match($op->kodestatus) {
                                        'PC'  => 'lc-pc',  'RC1' => 'lc-rc1',
                                        'RC2' => 'lc-rc2', 'RC3' => 'lc-rc3',
                                        default => '',
                                    };
                                @endphp
                                <span class="lifecycle-badge {{ $opLcClass }}">{{ $op->kodestatus }}</span>
                            </td>
                            <td class="text-right">{{ $op->batcharea }}</td>
                            <td class="text-right text-green font-bold">{{ $op->total_dipanen }}</td>
                            <td class="text-right text-orange font-bold">{{ $op->sisa }}</td>
                            <td style="font-size:8px;">{{ $op->last_harvest_date }}</td>
                            <td class="text-center">
                                @if($op->days_since_harvest >= 3)
                                    <span class="hari-badge hari-danger">{{ $op->days_since_harvest }}d</span>
                                @elseif($op->days_since_harvest == 1)
                                    <span class="hari-badge hari-1">1d</span>
                                @else
                                    {{ $op->days_since_harvest }}d
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- ============================================ --}}
        {{-- KETERANGAN                                   --}}
        {{-- ============================================ --}}
        @if($lkhData->keterangan)
        <div class="keterangan-box">
            <span class="keterangan-label">Keterangan:</span>
            {{ $lkhData->keterangan }}
        </div>
        @endif

        {{-- ============================================ --}}
        {{-- TANDA TANGAN                                 --}}
        {{-- ============================================ --}}
        @php
            $signatureCols = [];

            $signatureCols[] = [
                'role'  => 'Dibuat Oleh',
                'title' => 'Mandor',
                'name'  => $lkhData->mandornama ?? $lkhData->mandorid ?? '-',
                'date'  => \Carbon\Carbon::parse($lkhData->lkhdate)->format('d/m/Y'),
                'flag'  => null,
            ];

            $jumlahApproval = (int)($lkhData->jumlahapproval ?? 0);

            $approvalUserIds = array_filter([
                $lkhData->approval1userid ?? null,
                $lkhData->approval2userid ?? null,
                $lkhData->approval3userid ?? null,
            ]);
            $approvalNames = [];
            if (!empty($approvalUserIds)) {
                $approvalNames = \Illuminate\Support\Facades\DB::table('user')
                    ->whereIn('userid', $approvalUserIds)
                    ->pluck('name', 'userid')->toArray();
            }

            $jabatanIds = array_filter([
                $lkhData->approval1idjabatan ?? null,
                $lkhData->approval2idjabatan ?? null,
                $lkhData->approval3idjabatan ?? null,
            ]);
            $jabatanNames = [];
            if (!empty($jabatanIds)) {
                $jabatanNames = \Illuminate\Support\Facades\DB::table('jabatan')
                    ->whereIn('idjabatan', $jabatanIds)
                    ->pluck('namajabatan', 'idjabatan')->toArray();
            }

            for ($i = 1; $i <= $jumlahApproval; $i++) {
                $uidField  = "approval{$i}userid";
                $jabField  = "approval{$i}idjabatan";
                $flagField = "approval{$i}flag";
                $dateField = "approval{$i}date";

                $signatureCols[] = [
                    'role'  => "Approval Level {$i}",
                    'title' => $jabatanNames[$lkhData->$jabField ?? ''] ?? "Approver L{$i}",
                    'name'  => $approvalNames[$lkhData->$uidField ?? ''] ?? ($lkhData->$uidField ?? '-'),
                    'date'  => $lkhData->$dateField ? \Carbon\Carbon::parse($lkhData->$dateField)->format('d/m/Y H:i') : '-',
                    'flag'  => $lkhData->$flagField,
                ];
            }

            $colCount = count($signatureCols);
        @endphp

        <div class="signature-section">
            <div class="signature-grid" style="--sig-cols: {{ $colCount }};">
                <div class="signature-grid-row">
                    @foreach($signatureCols as $sig)
                    <div class="signature-cell" style="background:#f3f4f6;">
                        <div class="sig-role">{{ $sig['role'] }}</div>
                        <div style="font-size:8px; color:#666;">{{ $sig['title'] }}</div>
                    </div>
                    @endforeach
                </div>
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
                <div class="signature-grid-row">
                    @foreach($signatureCols as $sig)
                    <div class="signature-cell">
                        <div class="sig-name">{{ $sig['name'] }}</div>
                        <div class="sig-date">{{ $sig['date'] }}</div>
                        @if($sig['flag'] === '1')
                            <div class="sig-status" style="color:#16a34a;">APPROVED</div>
                        @elseif($sig['flag'] === '0')
                            <div class="sig-status" style="color:#dc2626;">DECLINED</div>
                        @elseif($sig['flag'] === null && $sig['role'] !== 'Dibuat Oleh')
                            <div class="sig-status" style="color:#ca8a04;">PENDING</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="print-footer">
            <span>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</span>
            <span>{{ $lkhData->lkhno ?? '' }} — Halaman 1</span>
        </div>

    </div>

</body>
</html>