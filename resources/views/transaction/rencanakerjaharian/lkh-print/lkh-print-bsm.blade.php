{{-- resources/views/transaction/rencanakerjaharian/lkh-print/lkh-print-bsm.blade.php --}}
{{-- Standalone print layout for LKH Cek BSM --}}
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
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
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

        .doc-info-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 4px 16px; }
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

        /* Stats row */
        .stats-row { display: grid; grid-template-columns: repeat(5, 1fr); gap: 0; margin-bottom: 8px; border: 1px solid #ccc; }
        .stat-cell { padding: 6px 8px; text-align: center; border-right: 1px solid #ccc; }
        .stat-cell:last-child { border-right: none; }
        .stat-value { font-size: 18px; font-weight: 700; }
        .stat-label { font-size: 8px; color: #555; text-transform: uppercase; letter-spacing: .3px; margin-top: 1px; }
        .stat-cell-alt { background: #f9fafb; }

        /* Premium vs Non-Premium */
        .pnp-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px; }
        .pnp-box { border: 1px solid #ccc; padding: 6px 10px; }
        .pnp-title { font-size: 9px; font-weight: 700; margin-bottom: 4px; }
        .pnp-line { display: flex; justify-content: space-between; font-size: 9px; margin-bottom: 1px; }
        .pnp-line span:last-child { font-weight: 700; }
        .pnp-note { font-size: 7px; color: #888; margin-top: 3px; }

        /* Tables */
        .section-title {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .5px; padding: 4px 8px; background: #1f2937;
            color: #fff; margin-bottom: 0; margin-top: 10px;
        }
        .plot-header {
            background: #e5e7eb; padding: 3px 8px; font-weight: 700; font-size: 9px;
            border: 1px solid #ccc; border-bottom: none;
        }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        table th {
            background: #f3f4f6; border: 1px solid #999; padding: 3px 5px;
            font-size: 7.5px; font-weight: 700; text-transform: uppercase; text-align: center; color: #333;
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
        .text-yellow { color: #ca8a04; }
        .text-red    { color: #dc2626; }
        .text-blue   { color: #2563eb; }
        .text-orange { color: #ea580c; }
        .bg-bsm      { background: #f9fafb; }

        /* Grade */
        .grade-a { font-weight: 700; color: #16a34a; }
        .grade-b { font-weight: 700; color: #ca8a04; }
        .grade-c { font-weight: 700; color: #dc2626; }

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
    </style>
</head>
<body>

    <div class="screen-controls">
        <span style="font-size:13px; font-weight:700;">Print Preview — {{ $lkhData->lkhno ?? '' }} (Cek BSM)</span>
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
                    <div class="doc-subtitle">Cek BSM (Bersih, Segar, Manis)</div>
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
                    <span class="info-value">{{ $lkhData->activitycode ?? '-' }} — Cek BSM</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Pekerja:</span>
                    <span class="info-value">{{ $lkhData->totalworkers ?? 0 }} orang</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Upah:</span>
                    <span class="info-value">Rp {{ number_format($lkhData->totalupahall ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- STATISTIK BSM                                --}}
        {{-- ============================================ --}}
        @php
            $completedData   = $lkhBsmDetails->where('status', 'COMPLETED');
            $premiumData     = $completedData->where('kodetebang_label', 'Premium');
            $nonPremiumData  = $completedData->where('kodetebang_label', 'Non-Premium');

            $gradeA = $completedData->where('grade', 'A')->count();
            $gradeB = $completedData->where('grade', 'B')->count();
            $gradeC = $completedData->where('grade', 'C')->count();

            $toFloat = fn($item) => (float)str_replace(',', '', $item->averagescore ?? '0');

            $avgScore = $completedData->filter(fn($i) => $i->averagescore)->map($toFloat)->avg() ?: 0;
            $premiumAvg = $premiumData->filter(fn($i) => $i->averagescore)->map($toFloat)->avg() ?: 0;
            $nonPremiumAvg = $nonPremiumData->filter(fn($i) => $i->averagescore)->map($toFloat)->avg() ?: 0;

            $totalSJ = $lkhBsmDetails->pluck('suratjalanno')->unique()->count();
            $totalCompleted = $completedData->count();
            $totalPending = $lkhBsmDetails->count() - $totalCompleted;
            $percentage = $lkhBsmDetails->count() > 0 ? round(($totalCompleted / $lkhBsmDetails->count()) * 100, 1) : 0;
        @endphp

        @if($completedData->count() > 0)
        <div class="stats-row">
            <div class="stat-cell">
                <div class="stat-value">{{ number_format($avgScore, 2) }}</div>
                <div class="stat-label">Avg Score</div>
            </div>
            <div class="stat-cell stat-cell-alt">
                <div class="stat-value text-green">{{ $gradeA }}</div>
                <div class="stat-label">Grade A</div>
            </div>
            <div class="stat-cell">
                <div class="stat-value text-yellow">{{ $gradeB }}</div>
                <div class="stat-label">Grade B</div>
            </div>
            <div class="stat-cell stat-cell-alt">
                <div class="stat-value text-red">{{ $gradeC }}</div>
                <div class="stat-label">Grade C</div>
            </div>
            <div class="stat-cell" style="background:#eff6ff;">
                <div class="stat-value text-blue">{{ $totalSJ }}</div>
                <div class="stat-label">Total SJ</div>
            </div>
        </div>

        {{-- Premium vs Non-Premium --}}
        <div class="pnp-row">
            <div class="pnp-box" style="border-color:#93c5fd; background:#eff6ff;">
                <div class="pnp-title text-blue">Premium</div>
                <div class="pnp-line"><span>Total SJ:</span><span>{{ $premiumData->count() }}</span></div>
                <div class="pnp-line"><span>Avg Score:</span><span>{{ number_format($premiumAvg, 2) }}</span></div>
                <div class="pnp-note">A (&lt;1200) | B (1200-1700) | C (&gt;1700)</div>
            </div>
            <div class="pnp-box">
                <div class="pnp-title">Non-Premium</div>
                <div class="pnp-line"><span>Total SJ:</span><span>{{ $nonPremiumData->count() }}</span></div>
                <div class="pnp-line"><span>Avg Score:</span><span>{{ number_format($nonPremiumAvg, 2) }}</span></div>
                <div class="pnp-note">A (&lt;1000) | B (1000-2000) | C (&gt;2000)</div>
            </div>
        </div>
        @endif

        {{-- Progress bar text --}}
        <div style="font-size:9px; margin-bottom:6px; display:flex; gap:12px; align-items:center;">
            <span>Progress: <strong>{{ $percentage }}%</strong> ({{ $totalCompleted }} completed, {{ $totalPending }} pending)</span>
        </div>

        {{-- ============================================ --}}
        {{-- DETAIL BSM PER PLOT                          --}}
        {{-- ============================================ --}}
        <div class="section-title">Detail Hasil Cek BSM Per Surat Jalan</div>

        @php
            $groupedByPlot = $lkhBsmDetails->groupBy('plot');
        @endphp

        @foreach($groupedByPlot as $plot => $details)
            <div class="plot-header">
                Plot: {{ $details->first()->plot_display ?? $plot }}
                <span style="font-weight:400; color:#666; margin-left:8px;">({{ $details->count() }} Surat Jalan)</span>
            </div>
            <table style="margin-bottom:8px;">
                <thead>
                    <tr>
                        <th style="width:20px;">No</th>
                        <th style="width:auto;" class="text-left">Surat Jalan</th>
                        <th style="width:55px;">Kodetebang</th>
                        <th style="width:65px;">Batch</th>
                        <th style="width:50px;" class="bg-bsm">Bersih</th>
                        <th style="width:50px;" class="bg-bsm">Segar</th>
                        <th style="width:50px;" class="bg-bsm">Manis</th>
                        <th style="width:55px;">Average</th>
                        <th style="width:35px;">Grade</th>
                        <th style="width:55px;">Status</th>
                        <th style="width:60px;" class="text-left">Ket</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($details as $index => $d)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="font-mono" style="font-size:8px;">{{ $d->suratjalanno }}</td>
                        <td class="text-center">
                            @if($d->kodetebang_label == 'Premium')
                                <span class="font-bold text-blue" style="font-size:8px;">Premium</span>
                            @else
                                <span style="font-size:8px;">Non-Premium</span>
                            @endif
                        </td>
                        <td class="text-center font-mono" style="font-size:8px;">{{ $d->batchno }}</td>
                        <td class="text-center bg-bsm font-bold">{{ $d->nilaibersih ?? '-' }}</td>
                        <td class="text-center bg-bsm font-bold">{{ $d->nilaisegar ?? '-' }}</td>
                        <td class="text-center bg-bsm font-bold">{{ $d->nilaimanis ?? '-' }}</td>
                        <td class="text-center">
                            @if($d->averagescore)
                                <strong style="font-size:11px;">{{ $d->averagescore }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($d->grade == 'A')
                                <span class="grade-a">A</span>
                            @elseif($d->grade == 'B')
                                <span class="grade-b">B</span>
                            @elseif($d->grade == 'C')
                                <span class="grade-c">C</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center" style="font-size:8px;">
                            @if($d->status == 'COMPLETED')
                                <span class="text-green font-bold">✓</span>
                            @else
                                <span class="text-orange font-bold">PENDING</span>
                            @endif
                        </td>
                        <td style="font-size:8px;">{{ $d->keterangan != '-' ? $d->keterangan : '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach

        @if($lkhBsmDetails->isEmpty())
        <div style="border:1px solid #ccc; padding:16px; text-align:center; color:#999; font-size:9px;">
            Tidak ada data BSM. Data akan muncul setelah input dari mobile.
        </div>
        @endif

        {{-- ============================================ --}}
        {{-- DETAIL PEKERJA HARIAN                        --}}
        {{-- ============================================ --}}
        @if($lkhWorkerDetails && $lkhWorkerDetails->count() > 0)
        <div class="section-title">Detail Pekerja Harian</div>
        <table>
            <thead>
                <tr>
                    <th style="width:20px;">No</th>
                    <th class="text-left">Nama Pekerja</th>
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
                @php
                    // Handle both object styles (direct property or nested tenagakerja)
                    $nama = $w->nama ?? ($w->tenagakerja->nama ?? ($w->tenagakerjaid ?? '-'));
                    $nik  = $w->nik ?? ($w->tenagakerja->nik ?? '-');
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $nama }}</td>
                    <td class="font-mono" style="font-size:8px;">{{ $nik }}</td>
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
                    <td colspan="10" class="text-right font-bold">TOTAL UPAH:</td>
                    <td class="text-right font-bold text-green">{{ number_format($lkhWorkerDetails->sum('totalupah'), 0, ',', '.') }}</td>
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