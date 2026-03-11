{{-- resources/views/transaction/rencanakerjaharian/lkh-print/lkh-print.blade.php --}}
{{-- Standalone print layout for LKH Normal (Tenaga Harian & Borongan) --}}
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
        @page { size: A4 portrait; margin: 12mm 10mm 15mm 10mm; }
        .print-page {
            width: 190mm; max-width: 190mm;
            margin: 0 auto; padding: 10px;
        }

        /* ============================================ */
        /* SCREEN-ONLY CONTROLS                         */
        /* ============================================ */
        .screen-controls {
            position: fixed; top: 0; left: 0; right: 0;
            background: #1f2937; color: #fff;
            padding: 10px 20px;
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

        /* ============================================ */
        /* HEADER                                       */
        /* ============================================ */
        .doc-header { border: 1.5px solid #000; padding: 8px 12px; margin-bottom: 10px; }
        .doc-header-top {
            display: flex; justify-content: space-between; align-items: flex-start;
            margin-bottom: 6px; padding-bottom: 6px; border-bottom: 1px solid #ccc;
        }
        .doc-title { font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .doc-subtitle { font-size: 11px; font-weight: 600; color: #555; margin-top: 2px; }
        .doc-lkhno { font-size: 16px; font-weight: 700; font-family: 'Courier New', monospace; }

        .doc-info-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 4px 16px; }
        .info-item { display: flex; gap: 4px; }
        .info-label {
            font-size: 9px; font-weight: 700; color: #555;
            text-transform: uppercase; white-space: nowrap; min-width: 60px;
        }
        .info-value { font-size: 10px; font-weight: 600; }

        .status-badge {
            display: inline-block; padding: 1px 6px; border: 1px solid #000;
            font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px;
        }
        .status-approved  { border-color: #16a34a; color: #16a34a; }
        .status-submitted { border-color: #2563eb; color: #2563eb; }
        .status-draft     { border-color: #ca8a04; color: #ca8a04; }
        .status-completed { border-color: #16a34a; color: #16a34a; }

        /* ============================================ */
        /* SUMMARY                                      */
        /* ============================================ */
        .summary-row { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 8px; margin-bottom: 10px; }
        .summary-box { border: 1px solid #ccc; padding: 5px 8px; }
        .summary-box-title {
            font-size: 8px; font-weight: 700; text-transform: uppercase;
            color: #555; margin-bottom: 3px; letter-spacing: .3px;
        }
        .summary-box-value { font-size: 14px; font-weight: 700; color: #1a1a1a; }
        .summary-box-sub { font-size: 8px; color: #666; margin-top: 1px; }

        /* ============================================ */
        /* TABLES                                       */
        /* ============================================ */
        .section-title {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .5px; padding: 4px 8px; background: #1f2937;
            color: #fff; margin-bottom: 0; margin-top: 10px;
        }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        table th {
            background: #f3f4f6; border: 1px solid #999; padding: 4px 6px;
            font-size: 8px; font-weight: 700; text-transform: uppercase;
            text-align: center; color: #333;
        }
        table td { border: 1px solid #ccc; padding: 3px 6px; vertical-align: top; }
        table tfoot td { background: #f3f4f6; font-weight: 700; border: 1px solid #999; }

        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .text-left   { text-align: left; }
        .font-mono   { font-family: 'Courier New', monospace; }
        .font-bold   { font-weight: 700; }
        .text-muted  { color: #666; font-size: 8px; }
        .text-green  { color: #16a34a; }

        /* ============================================ */
        /* BORONGAN WAGE BOX                            */
        /* ============================================ */
        .wage-box {
            border: 1px solid #ccc; padding: 8px 12px; margin-top: 10px;
            max-width: 350px;
        }
        .wage-box-title {
            font-size: 9px; font-weight: 700; text-transform: uppercase;
            color: #555; margin-bottom: 4px;
        }
        .wage-line { display: flex; justify-content: space-between; font-size: 9px; margin-bottom: 2px; }
        .wage-line span:last-child { font-weight: 700; }
        .wage-total { border-top: 1px solid #999; padding-top: 3px; margin-top: 3px; font-size: 10px; }
        .wage-note { font-size: 8px; color: #666; font-style: italic; margin-top: 4px; }

        /* ============================================ */
        /* KETERANGAN                                   */
        /* ============================================ */
        .keterangan-box {
            border: 1px solid #ccc; padding: 5px 8px; margin-top: 10px; font-size: 9px;
        }
        .keterangan-label { font-size: 8px; font-weight: 700; text-transform: uppercase; color: #555; }

        /* ============================================ */
        /* SIGNATURES                                   */
        /* ============================================ */
        .signature-section { margin-top: 20px; page-break-inside: avoid; }
        .signature-grid { display: grid; gap: 0; border: 1px solid #999; }
        .signature-grid-row { display: grid; grid-template-columns: repeat(var(--sig-cols, 4), 1fr); }
        .signature-cell { border: 1px solid #ccc; padding: 6px 8px; text-align: center; }
        .sig-role { font-size: 8px; font-weight: 700; text-transform: uppercase; color: #555; margin-bottom: 2px; }
        .sig-name { font-size: 9px; font-weight: 700; color: #1a1a1a; }
        .sig-space { height: 45px; }
        .sig-date { font-size: 8px; color: #666; }
        .sig-status { font-size: 7px; font-weight: 700; margin-top: 2px; }

        /* ============================================ */
        /* FOOTER                                       */
        /* ============================================ */
        .print-footer {
            margin-top: 10px; padding-top: 4px; border-top: 1px solid #ccc;
            font-size: 7px; color: #999; display: flex; justify-content: space-between;
        }

        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    {{-- Screen toolbar --}}
    <div class="screen-controls">
        <span style="font-size:13px; font-weight:700;">Print Preview — {{ $lkhData->lkhno ?? '' }}</span>
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
            $statusLabel = $lkhData->status ?? 'DRAFT';
            $statusClass = match($statusLabel) {
                'APPROVED'  => 'status-approved',
                'SUBMITTED' => 'status-submitted',
                'COMPLETED' => 'status-completed',
                default     => 'status-draft',
            };

            $jenisLabel = $lkhData->jenistenagakerja == 1 ? 'Tenaga Harian' : 'Tenaga Borongan';
        @endphp

        <div class="doc-header">
            <div class="doc-header-top">
                <div>
                    <div class="doc-title">Laporan Kegiatan Harian (LKH)</div>
                    <div class="doc-subtitle">{{ $jenisLabel }}</div>
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
                <div class="info-item" style="grid-column: span 2;">
                    <span class="info-label">Aktivitas:</span>
                    <span class="info-value">{{ $lkhData->activitycode ?? '-' }} — {{ $lkhData->activityname ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Jenis TK:</span>
                    <span class="info-value">{{ $jenisLabel }}</span>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- SUMMARY ROW                                  --}}
        {{-- ============================================ --}}
        <div class="summary-row">
            <div class="summary-box">
                <div class="summary-box-title">Total Pekerja</div>
                <div class="summary-box-value">{{ $lkhData->totalworkers ?? 0 }}</div>
                <div class="summary-box-sub">orang</div>
            </div>
            <div class="summary-box">
                <div class="summary-box-title">Total Hasil</div>
                <div class="summary-box-value">{{ number_format($lkhData->totalhasil ?? 0, 2) }}</div>
                <div class="summary-box-sub">Ha</div>
            </div>
            <div class="summary-box">
                <div class="summary-box-title">Total Sisa</div>
                <div class="summary-box-value">{{ number_format($lkhData->totalsisa ?? 0, 2) }}</div>
                <div class="summary-box-sub">Ha</div>
            </div>
            <div class="summary-box">
                <div class="summary-box-title">Total Upah</div>
                <div class="summary-box-value">Rp {{ number_format($lkhData->totalupahall ?? 0, 0, ',', '.') }}</div>
                @if(($lkhData->insentifhk ?? 0) > 0)
                    <div class="summary-box-sub">+ Insentif {{ number_format($lkhData->insentifhk, 0) }} HK = Rp {{ number_format($lkhData->totalinsentif ?? 0, 0, ',', '.') }}</div>
                @endif
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- DETAIL PLOT                                  --}}
        {{-- ============================================ --}}
        @if($lkhPlotDetails && $lkhPlotDetails->count() > 0)
        <div class="section-title">Detail Plot</div>
        <table>
            <thead>
                <tr>
                    <th style="width:25px;">No</th>
                    <th style="width:50px;">Blok</th>
                    <th style="width:50px;">Plot</th>
                    <th style="width:70px;">Luas RKH (Ha)</th>
                    <th style="width:70px;">Luas Hasil (Ha)</th>
                    <th style="width:70px;">Luas Sisa (Ha)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lkhPlotDetails as $index => $plot)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center font-bold">{{ $plot->blok }}</td>
                    <td class="text-center font-bold">{{ $plot->plot }}</td>
                    <td class="text-right">{{ number_format($plot->luasrkh ?? 0, 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($plot->luashasil ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($plot->luassisa ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-center font-bold">TOTAL</td>
                    <td class="text-right font-bold">{{ number_format($lkhPlotDetails->sum('luasrkh'), 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($lkhPlotDetails->sum('luashasil'), 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($lkhPlotDetails->sum('luassisa'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
        @endif

        {{-- ============================================ --}}
        {{-- DETAIL PEKERJA                               --}}
        {{-- ============================================ --}}
        @if($lkhWorkerDetails && $lkhWorkerDetails->count() > 0)

            @if($lkhData->jenistenagakerja == 1)
                {{-- TENAGA HARIAN --}}
                <div class="section-title">Detail Pekerja — Tenaga Harian</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width:20px;">No</th>
                            <th style="width:60px;">Kode</th>
                            <th class="text-left">Nama</th>
                            <th style="width:70px;">NIK</th>
                            <th style="width:40px;">Masuk</th>
                            <th style="width:40px;">Selesai</th>
                            <th style="width:35px;">Jam</th>
                            <th style="width:30px;">OT</th>
                            <th style="width:65px;">Premi</th>
                            <th style="width:65px;">Upah Harian</th>
                            <th style="width:65px;">Upah Lembur</th>
                            <th style="width:70px;">Total Upah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lkhWorkerDetails as $index => $w)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center font-mono" style="font-size:8px;">{{ $w->tenagakerjaid ?? '-' }}</td>
                            <td class="font-bold">{{ $w->nama ?? '-' }}</td>
                            <td class="font-mono" style="font-size:8px;">{{ $w->nik ?? '-' }}</td>
                            <td class="text-center font-mono">{{ $w->jammasuk ? \Carbon\Carbon::parse($w->jammasuk)->format('H:i') : '-' }}</td>
                            <td class="text-center font-mono">{{ $w->jamselesai ? \Carbon\Carbon::parse($w->jamselesai)->format('H:i') : '-' }}</td>
                            <td class="text-center">{{ ($w->totaljamkerja ?? 0) > 0 ? number_format($w->totaljamkerja, 0) : '-' }}</td>
                            <td class="text-center">{{ ($w->overtimehours ?? 0) > 0 ? number_format($w->overtimehours, 0) : '-' }}</td>
                            <td class="text-right">{{ number_format($w->premi ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($w->upahharian ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($w->upahlembur ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right font-bold text-green">{{ number_format($w->totalupah ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="10" class="text-center font-bold">TOTAL UPAH</td>
                            <td class="text-right font-bold">{{ number_format($lkhWorkerDetails->sum('upahlembur'), 0, ',', '.') }}</td>
                            <td class="text-right font-bold text-green">{{ number_format($lkhWorkerDetails->sum('totalupah'), 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>

            @else
                {{-- TENAGA BORONGAN --}}
                <div class="section-title">Detail Pekerja — Tenaga Borongan</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width:25px;">No</th>
                            <th style="width:80px;">Kode Pekerja</th>
                            <th class="text-left">Nama Pekerja</th>
                            <th style="width:100px;">NIK</th>
                            <th class="text-left">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lkhWorkerDetails as $index => $w)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center font-mono">{{ $w->tenagakerjaid ?? '-' }}</td>
                            <td class="font-bold">{{ $w->nama ?? '-' }}</td>
                            <td class="font-mono">{{ $w->nik ?? '-' }}</td>
                            <td>{{ $w->keterangan ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Borongan Wage Summary --}}
                @if(isset($boronganRate) && $boronganRate > 0)
                <div class="wage-box">
                    <div class="wage-box-title">Ringkasan Upah Borongan</div>
                    <div class="wage-line">
                        <span>Luas Hasil:</span>
                        <span>{{ number_format($lkhData->totalhasil ?? 0, 2) }} Ha</span>
                    </div>
                    <div class="wage-line">
                        <span>Rate per Ha:</span>
                        <span>Rp {{ number_format($boronganRate, 0, ',', '.') }}</span>
                    </div>
                    <div class="wage-line wage-total">
                        <span style="font-weight:700;">Total Upah:</span>
                        <span style="font-size:12px;">Rp {{ number_format($lkhData->totalupahall ?? 0, 0, ',', '.') }}</span>
                    </div>
                    @if(($lkhData->insentifhk ?? 0) > 0)
                    <div class="wage-line" style="margin-top:4px;">
                        <span>Insentif:</span>
                        <span>{{ number_format($lkhData->insentifhk, 0) }} HK — Rp {{ number_format($lkhData->totalinsentif ?? 0, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="wage-note">* Pembagian upah ke masing-masing pekerja dikelola oleh mandor</div>
                </div>
                @endif
            @endif
        @endif

        {{-- ============================================ --}}
        {{-- DETAIL MATERIAL                              --}}
        {{-- ============================================ --}}
        @if($lkhMaterialDetails && $lkhMaterialDetails->count() > 0)
        <div class="section-title">Detail Material</div>
        <table>
            <thead>
                <tr>
                    <th style="width:20px;">No</th>
                    <th style="width:40px;">Plot</th>
                    <th style="width:70px;">Item Code</th>
                    <th class="text-left">Nama Item</th>
                    <th style="width:60px;">Qty Diterima</th>
                    <th style="width:55px;">Qty Sisa</th>
                    <th style="width:65px;">Qty Digunakan</th>
                    <th style="width:35px;">Sat</th>
                    <th class="text-left" style="width:70px;">Ket</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lkhMaterialDetails as $index => $m)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center font-bold">{{ $m->plot ?? '-' }}</td>
                    <td class="font-mono font-bold" style="font-size:8px;">{{ $m->itemcode }}</td>
                    <td>{{ $m->itemname ?? '-' }}</td>
                    <td class="text-right">{{ number_format($m->qtyditerima ?? 0, 3) }}</td>
                    <td class="text-right">{{ number_format($m->qtysisa ?? 0, 3) }}</td>
                    <td class="text-right font-bold text-green">{{ number_format($m->qtydigunakan ?? 0, 3) }}</td>
                    <td class="text-center">{{ $m->satuan ?? '-' }}</td>
                    <td style="font-size:8px;">{{ $m->keterangan ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-center font-bold">TOTAL</td>
                    <td class="text-right font-bold">{{ number_format($lkhMaterialDetails->sum('qtyditerima'), 3) }}</td>
                    <td class="text-right font-bold">{{ number_format($lkhMaterialDetails->sum('qtysisa'), 3) }}</td>
                    <td class="text-right font-bold text-green">{{ number_format($lkhMaterialDetails->sum('qtydigunakan'), 3) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
        @endif

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
        {{-- TANDA TANGAN / APPROVAL                      --}}
        {{-- ============================================ --}}
        @php
            $signatureCols = [];

            // Dibuat oleh (Mandor)
            $signatureCols[] = [
                'role'  => 'Dibuat Oleh',
                'title' => 'Mandor',
                'name'  => $lkhData->mandornama ?? $lkhData->mandorid ?? '-',
                'date'  => \Carbon\Carbon::parse($lkhData->lkhdate)->format('d/m/Y'),
                'flag'  => null,
            ];

            // Approval levels — langsung dari lkhhdr
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
                    ->pluck('name', 'userid')
                    ->toArray();
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
                    ->pluck('namajabatan', 'idjabatan')
                    ->toArray();
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
                        @elseif($sig['flag'] === null && $sig['role'] !== 'Dibuat Oleh')
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
            <span>{{ $lkhData->lkhno ?? '' }} — Halaman 1</span>
        </div>

    </div>

</body>
</html>