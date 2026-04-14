{{-- resources/views/transaction/gudang-bbm/show.blade.php --}}
{{-- Pure read-only print preview. Semua aksi (input solar, finalize) ada di index page. --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <style>
        @media print {
            body * { visibility: hidden; }
            .print-area, .print-area * { visibility: visible; }
            .print-area { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
            .print-area table { font-size: 9pt; }
            .print-area { font-size: 10pt; }
        }
        @media screen {
            .print-area {
                max-width: 210mm;
                margin: 0 auto;
                background: white;
                padding: 15mm 20mm;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
        }
        .print-area table { border-collapse: collapse; }
        .print-area th, .print-area td { border: 1px solid #000; padding: 4px 8px; }
        .info-table td { border: none; padding: 2px 0; }
    </style>

@php
    if ($header->gudangconfirm == 0) {
        $statusLabel = 'Menunggu Input Solar Real';
    } elseif (($header->gudangapprovalstatus ?? null) === '1') {
        $statusLabel = 'Approved';
    } elseif (($header->gudangapprovalstatus ?? null) === '0') {
        $statusLabel = 'Ditolak';
    } else {
        $statusLabel = 'Menunggu Approval Pengeluaran';
    }
@endphp

    {{-- Action Bar (screen only) --}}
    <div class="no-print mb-4 flex justify-between items-center">
        <a href="{{ route('transaction.gudang-bbm.index') }}"
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm">&larr; Kembali</a>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">Print</button>
    </div>

    {{-- ================================================================= --}}
    {{-- PRINT AREA --}}
    {{-- ================================================================= --}}
    <div class="print-area">

        {{-- Document Header --}}
        <div style="text-align:center; border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 16px;">
            <div style="font-size: 16pt; font-weight: bold; letter-spacing: 1px;">KONFIRMASI PENGELUARAN BBM</div>
            <div style="font-size: 9pt; margin-top: 4px;">{{ config('app.name') }}</div>
        </div>

        {{-- Order Info --}}
        <table style="width:100%; margin-bottom: 16px;" class="info-table">
            <tr>
                <td style="width:50%; vertical-align:top;">
                    <table class="info-table" style="width:100%">
                        <tr>
                            <td style="width:110px; color:#555;">No. Order</td>
                            <td style="font-weight:bold; font-size: 12pt;">: #{{ $header->orderno }}</td>
                        </tr>
                        <tr>
                            <td style="color:#555;">Tanggal</td>
                            <td>: {{ \Carbon\Carbon::parse($header->orderdate)->translatedFormat('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td style="color:#555;">Status</td>
                            <td>: <strong>{{ $statusLabel }}</strong></td>
                        </tr>
                    </table>
                </td>
                <td style="width:50%; vertical-align:top;">
                    <table class="info-table" style="width:100%">
                        <tr>
                            <td style="width:110px; color:#555;">Tipe Sumber</td>
                            <td>: {{ $header->sourcetype === 'LKH' ? 'LKH (Kendaraan Kerja)' : 'SJS (Kendaraan Supply)' }}</td>
                        </tr>
                        <tr>
                            <td style="color:#555;">No. Referensi</td>
                            <td>: {{ $header->sourceno }}</td>
                        </tr>
                        @if($sourceInfo)
                            @if($header->sourcetype === 'LKH')
                            <tr>
                                <td style="color:#555;">Activity</td>
                                <td>: {{ $sourceInfo->activitycode ?? '' }} - {{ $sourceInfo->activityname ?? '' }}</td>
                            </tr>
                            <tr>
                                <td style="color:#555;">Mandor</td>
                                <td>: {{ $sourceInfo->mandor_nama ?? 'N/A' }}</td>
                            </tr>
                            @else
                            <tr>
                                <td style="color:#555;">Tujuan</td>
                                <td>: {{ $sourceInfo->tujuan ?? '' }}</td>
                            </tr>
                            <tr>
                                <td style="color:#555;">Aktivitas</td>
                                <td>: {{ $sourceInfo->keteranganaktivitas ?? '' }}</td>
                            </tr>
                            @endif
                        @endif
                    </table>
                </td>
            </tr>
        </table>

        {{-- Detail Table --}}
        <div style="margin-bottom: 8px; font-weight:bold; font-size: 9pt; text-transform:uppercase; border-bottom:1px solid #999; padding-bottom:4px; color:#333;">
            Detail Kendaraan & BBM
        </div>
        <table style="width:100%; font-size: 9pt;">
            <thead>
                <tr style="background:#f0f0f0;">
                    <th style="text-align:center; width:30px;">No</th>
                    <th style="text-align:left;">Kendaraan</th>
                    <th style="text-align:left;">Jenis</th>
                    <th style="text-align:left;">Operator</th>
                    <th style="text-align:center;">Hasil Kerja</th>
                    <th style="text-align:center;">Kalibrasi</th>
                    <th style="text-align:center;">Solar Diminta (L)</th>
                    <th style="text-align:center;">Solar Real (L)</th>
                    <th style="text-align:center;">Selisih (L)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detail as $idx => $item)
                <tr>
                    <td style="text-align:center;">{{ $idx + 1 }}</td>
                    <td style="font-weight:600;">
                        {{ $item->nokendaraan }}
                        @if($item->ismanualoverride)
                            <span style="font-size:8pt; font-weight:normal;"> *)</span>
                        @endif
                    </td>
                    <td>{{ $item->jenis ?? '-' }}</td>
                    <td>{{ $item->operator_nama ?? '-' }}</td>
                    <td style="text-align:center;">{{ number_format($item->hasilkerja, 2) }} {{ $item->satuanhasil }}</td>
                    <td style="text-align:center;">{{ number_format($item->nilaikalibrasi, 2) }} {{ $item->satuankalibrasi }}</td>
                    <td style="text-align:center;">{{ number_format($item->solarrequested, 2) }}</td>
                    <td style="text-align:center; font-weight:600;">
                        {{ $item->solarreal !== null ? number_format($item->solarreal, 2) : '-' }}
                    </td>
                    <td style="text-align:center;">
                        @if($item->solarreal !== null)
                            @php $selisih = $item->solarrequested - $item->solarreal; @endphp
                            {{ $selisih > 0 ? '-' . number_format($selisih, 2) : '0.00' }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f0f0f0; font-weight:bold;">
                    <td colspan="6" style="text-align:right;">TOTAL</td>
                    <td style="text-align:center;">{{ number_format($header->totalsolarrequested, 2) }}</td>
                    <td style="text-align:center;">
                        {{ $header->totalsolarreal !== null ? number_format($header->totalsolarreal, 2) : '-' }}
                    </td>
                    <td style="text-align:center;">
                        @if($header->totalsolarreal !== null)
                            @php $totalSelisih = $header->totalsolarrequested - $header->totalsolarreal; @endphp
                            {{ $totalSelisih > 0 ? '-' . number_format($totalSelisih, 2) : '0.00' }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>

        @if($detail->where('ismanualoverride', 1)->count() > 0)
        <div style="font-size: 8pt; color:#666; margin-top:4px;">
            *) Solar diminta di-override manual dari nilai kalkulasi
        </div>
        @endif

        {{-- ============================================================= --}}
        {{-- RIWAYAT PROSES --}}
        {{-- ============================================================= --}}
        <div style="margin-top: 24px; border-top: 1px solid #999; padding-top: 12px;">
            <div style="font-weight:bold; font-size: 9pt; text-transform:uppercase; margin-bottom:8px; color:#333;">
                Riwayat Proses
            </div>
            <table style="width:100%; font-size: 9pt;">
                <thead>
                    <tr style="background:#f0f0f0;">
                        <th style="text-align:left;">Tahap</th>
                        <th style="text-align:left;">Pelaksana</th>
                        <th style="text-align:center;">Status</th>
                        <th style="text-align:center;">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 1. Pembuatan Order --}}
                    <tr>
                        <td>Pembuatan Order</td>
                        <td>{{ $header->inputby ?? '-' }}</td>
                        <td style="text-align:center;">Dibuat</td>
                        <td style="text-align:center;">
                            {{ $header->createdat ? \Carbon\Carbon::parse($header->createdat)->format('d/m/Y H:i') : '-' }}
                        </td>
                    </tr>

                    {{-- 2. Approval Permintaan (multi-level) --}}
                    @for($i = 1; $i <= ($header->jumlahapproval ?? 0); $i++)
                    @php
                        $aUser = $header->{"approval{$i}userid"} ?? null;
                        $aFlag = $header->{"approval{$i}flag"} ?? null;
                        $aDate = $header->{"approval{$i}date"} ?? null;
                        $aJab  = $header->{"approval{$i}idjabatan"} ?? null;
                        $jabName = $aJab ? \Illuminate\Support\Facades\DB::table('jabatan')->where('idjabatan', $aJab)->value('namajabatan') : null;

                        if ($aFlag === '1') $aStatus = 'Approved';
                        elseif ($aFlag === '0') $aStatus = 'Ditolak';
                        else $aStatus = 'Pending';
                    @endphp
                    <tr>
                        <td>Approval Permintaan {{ $i }}{{ $jabName ? " ({$jabName})" : '' }}</td>
                        <td>{{ $aUser ?? '-' }}</td>
                        <td style="text-align:center;">{{ $aStatus }}</td>
                        <td style="text-align:center;">{{ $aDate ? \Carbon\Carbon::parse($aDate)->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                    @endfor

                    {{-- 3. Konfirmasi Gudang --}}
                    <tr>
                        <td>Konfirmasi Gudang BBM</td>
                        <td>{{ $header->gudangconfirmedby ?? '-' }}</td>
                        <td style="text-align:center;">{{ $header->gudangconfirm == 1 ? 'Dikonfirmasi' : 'Pending' }}</td>
                        <td style="text-align:center;">
                            {{ isset($header->gudangconfirmedat) && $header->gudangconfirmedat ? \Carbon\Carbon::parse($header->gudangconfirmedat)->format('d/m/Y H:i') : '-' }}
                        </td>
                    </tr>

                    {{-- 4. Approval Pengeluaran (multi-level) --}}
                    @for($i = 1; $i <= ($header->gudangjumlahapproval ?? 0); $i++)
                    @php
                        $gUser = $header->{"gudangapproval{$i}userid"} ?? null;
                        $gFlag = $header->{"gudangapproval{$i}flag"} ?? null;
                        $gDate = $header->{"gudangapproval{$i}date"} ?? null;
                        $gJab  = $header->{"gudangapproval{$i}idjabatan"} ?? null;
                        $gJabName = $gJab ? \Illuminate\Support\Facades\DB::table('jabatan')->where('idjabatan', $gJab)->value('namajabatan') : null;

                        if ($gFlag === '1') $gStatus = 'Approved';
                        elseif ($gFlag === '0') $gStatus = 'Ditolak';
                        else $gStatus = 'Pending';
                    @endphp
                    <tr>
                        <td>Approval Pengeluaran {{ $i }}{{ $gJabName ? " ({$gJabName})" : '' }}</td>
                        <td>{{ $gUser ?? '-' }}</td>
                        <td style="text-align:center;">{{ $gStatus }}</td>
                        <td style="text-align:center;">{{ $gDate ? \Carbon\Carbon::parse($gDate)->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div style="text-align:center; font-size: 8pt; margin-top: 16px; border-top: 1px solid #ccc; padding-top: 6px; color: #999;">
            Dokumen ini digenerate oleh sistem. Validitas dapat diverifikasi melalui nomor order dan riwayat proses di atas.
            <br>Order #{{ $header->orderno }} | Dicetak: {{ $printDate }}
        </div>
    </div>
</x-layout>