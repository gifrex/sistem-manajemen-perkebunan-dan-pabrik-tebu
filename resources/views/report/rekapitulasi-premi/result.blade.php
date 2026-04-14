<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Premi Target Kontraktor - {{ $namaKontraktor }} - {{ $periodeLabel }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .print-break { page-break-after: always; }
            * { box-shadow: none !important; text-shadow: none !important; }
        }
        body { background-color: white; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .report-table { border-collapse: collapse; width: 100%; }
        .report-table th, .report-table td { border: 1px solid #d1d5db; padding: 5px 6px; text-align: center; vertical-align: middle; font-size: 11px; }
        .report-table th { background-color: #f3f4f6; font-weight: 600; }
        .report-table tr:nth-child(even) td { background-color: #f9fafb; }
    </style>
</head>
<body class="bg-white min-h-screen">

    <!-- Action Buttons -->
    <div class="no-print fixed top-4 right-4 z-50 flex space-x-2">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-lg transition duration-200 flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            <span>Print</span>
        </button>
    </div>
    <div class="no-print fixed top-4 left-4 z-50">
        <button onclick="window.history.back()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md shadow-lg transition duration-200 flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kembali</span>
        </button>
    </div>

    <div class="w-full max-w-none mx-auto p-8">

        <!-- Company Header -->
        <div class="text-center mb-2">
            <div class="text-lg font-bold text-gray-900 uppercase tracking-wide">
                {{ session('companycode') ?? 'PT. PERKEBUNAN NUSANTARA' }}
            </div>
        </div>

        <!-- Report Header -->
        <div class="text-center mb-8 border-b-2 border-gray-300 pb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-4 uppercase">REKAPITULASI PREMI TARGET TONASE KONTRAKTOR PANEN TEBU</h1>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-gray-700 max-w-5xl mx-auto">
                <div class="bg-gray-50 p-3 rounded">
                    <span class="font-semibold">Kontraktor:</span><br>
                    <span class="text-base">{{ $idkontraktor }} - {{ $namaKontraktor }}</span>
                </div>
                <div class="bg-gray-50 p-3 rounded">
                    <span class="font-semibold">Periode:</span><br>
                    <span class="text-base font-bold">{{ $periodeLabel }}</span>
                </div>
                <div class="bg-gray-50 p-3 rounded">
                    <span class="font-semibold">Jumlah Dokumen:</span><br>
                    <span class="text-base">{{ $documents->count() }} Dokumen</span>
                </div>
                <div class="bg-gray-50 p-3 rounded">
                    <span class="font-semibold">Tanggal Cetak:</span><br>
                    <span class="text-base">{{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</span>
                </div>
            </div>
            <div class="mt-3 text-xs text-gray-500">
                No. Dokumen:
                @foreach($documents as $doc)
                    <span class="inline-block bg-indigo-50 text-indigo-700 border border-indigo-100 rounded px-2 py-0.5 font-mono mr-1">{{ $doc->nodoc }}</span>
                @endforeach
            </div>
        </div>

        @php
            $harga = $tabelharga[0] ?? (object)[];
            $h = function($key) use ($harga) {
                return is_array($harga) ? ($harga[$key] ?? 0) : ($harga->$key ?? 0);
            };
            $groupedData = $data->groupBy(function($item) {
                $tgl = is_array($item) ? $item['tanggalangkut'] : $item->tanggalangkut;
                return \Carbon\Carbon::parse($tgl)->format('Y-m-d');
            })->sortKeys();

            $grandBruto = $grandBrkend = $grandNetto = $grandPotKg = $grandBeratBersih = $grandBiaya = $grandRetensi = 0;
            $cost = $h('manualfeekont');
            $tanggalEfektif = $data->map(function($item) {
                $tgl = is_array($item) ? ($item["tanggalangkut"] ?? null) : ($item->tanggalangkut ?? null);
                return $tgl ? \Carbon\Carbon::parse($tgl)->format("Y-m-d") : null;
            })->filter()->unique()->count();
            $targetTotal = $targetPerHari * $tanggalEfektif;
        @endphp

        {{-- DETAIL PER TANGGAL --}}
        @foreach ($groupedData as $tanggal => $dataPerTanggal)
            @php
                $totalBruto = $totalBrkend = $totalNetto = $totalPotKg = $totalBeratBersih = $totalBiaya = $totalRetensi = 0;
            @endphp

            <div class="mb-8 @if(!$loop->last) print-break @endif">
                <h2 class="text-base font-semibold text-gray-900 mb-3 text-center uppercase border-b border-gray-200 pb-2">
                    Detail Panen Harian ({{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }})
                </h2>
                <div class="overflow-x-auto">
                    <table class="report-table min-w-full">
                        <thead>
                            <tr class="bg-gray-100">
                                <th>No</th>
                                <th>Sub Kontraktor</th>
                                <th>Nama Sopir</th>
                                <th>No Polisi</th>
                                <th>No SJL</th>
                                <th>Plot</th>
                                <th>Bruto (KG)</th>
                                <th>Tarra (KG)</th>
                                <th>Netto (KG)</th>
                                <th>Trash Pabrik %</th>
                                <th>Trash Kebun %</th>
                                <th>Pot (KG)</th>
                                <th>Berat Bersih (KG)</th>
                                <th>Retensi (Rp 1/KG)</th>
                                <th>Cost (Rp/KG)</th>
                                <th>Biaya (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dataPerTanggal as $dt)
                                @php
                                    $get         = fn($key) => is_array($dt) ? ($dt[$key] ?? null) : ($dt->$key ?? null);
                                    $trashPct    = (float)($get('trash_percentage') ?? 0);
                                    $netto       = (float)($get('netto') ?? 0);
                                    $bruto       = (float)($get('bruto') ?? 0);
                                    $brkend      = (float)($get('brkend') ?? 0);
                                    $trashKebun  = ($trashPct > 3) ? $trashPct - 3 : 0;
                                    $potKg       = ($trashKebun > 0) ? round($netto * $trashKebun / 100, 0, PHP_ROUND_HALF_UP) : 0;
                                    $beratBersih = $netto - $potKg;
                                    $retensi     = $beratBersih * 1;
                                    $biaya       = $beratBersih * $cost;

                                    $totalBruto       += $bruto;
                                    $totalBrkend      += $brkend;
                                    $totalNetto       += $netto;
                                    $totalPotKg       += $potKg;
                                    $totalBeratBersih += $beratBersih;
                                    $totalRetensi     += $retensi;
                                    $totalBiaya       += $biaya;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="text-left">{{ $get('namasubkontraktor') }}</td>
                                    <td class="text-left">{{ $get('namasupir') }}</td>
                                    <td>{{ $get('nomorpolisi') }}</td>
                                    <td>{{ $get('suratjalanno') }}</td>
                                    <td>{{ $get('plot') }}</td>
                                    <td class="text-right">{{ number_format($bruto) }}</td>
                                    <td class="text-right">{{ number_format($brkend) }}</td>
                                    <td class="text-right">{{ number_format($netto) }}</td>
                                    <td class="text-center">{{ $trashPct > 3 ? number_format($trashPct, 3) : '' }}</td>
                                    <td class="text-center">{{ $trashKebun > 0 ? number_format($trashKebun, 3) : '' }}</td>
                                    <td class="text-right">{{ $potKg > 0 ? number_format($potKg) : '' }}</td>
                                    <td class="text-right font-semibold">{{ number_format($beratBersih) }}</td>
                                    <td class="text-right">{{ number_format($retensi) }}</td>
                                    <td class="text-right">{{ number_format($cost) }}</td>
                                    <td class="text-right font-semibold">{{ number_format($biaya) }}</td>
                                </tr>
                            @endforeach

                            <tr style="background-color:#fefce8; border-top: 2px solid #ca8a04; font-weight:700;">
                                <td colspan="6" class="text-right text-xs font-bold uppercase">TOTAL {{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }}:</td>
                                <td class="text-right">{{ number_format($totalBruto) }}</td>
                                <td class="text-right">{{ number_format($totalBrkend) }}</td>
                                <td class="text-right">{{ number_format($totalNetto) }}</td>
                                <td>-</td>
                                <td>-</td>
                                <td class="text-right">{{ number_format($totalPotKg) }}</td>
                                <td class="text-right">{{ number_format($totalBeratBersih) }}</td>
                                <td class="text-right">{{ number_format($totalRetensi) }}</td>
                                <td>-</td>
                                <td class="text-right">{{ number_format($totalBiaya) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            @php
                $grandBruto       += $totalBruto;
                $grandBrkend      += $totalBrkend;
                $grandNetto       += $totalNetto;
                $grandPotKg       += $totalPotKg;
                $grandBeratBersih += $totalBeratBersih;
                $grandRetensi     += $totalRetensi;
                $grandBiaya       += $totalBiaya;
            @endphp
        @endforeach

        {{-- REKAPITULASI TOTAL --}}
        <div class="mt-12 mb-8 print-break">
            <h2 class="text-xl font-bold text-gray-900 mb-6 text-center uppercase border-b-2 border-gray-300 pb-4">
                REKAPITULASI TOTAL PERIODE {{ strtoupper($periodeLabel) }}
            </h2>

            <div class="overflow-x-auto">
                <table class="report-table min-w-full border-2 border-gray-400 text-sm">
                    <thead>
                        <tr class="bg-gray-200 border-b-2 border-gray-400">
                            <th class="py-3 px-6 text-left w-1/2">Keterangan</th>
                            <th class="py-3 px-6 text-right w-1/2">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-white border-b border-gray-200">
                            <td class="py-3 px-6 text-left font-medium">Hari Efektif</td>
                            <td class="py-3 px-6 text-right font-semibold">{{ $tanggalEfektif }} Hari</td>
                        </tr>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <td class="py-3 px-6 text-left font-medium">Total Berat Bersih</td>
                            <td class="py-3 px-6 text-right font-semibold">{{ number_format($grandBeratBersih) }} KG</td>
                        </tr>
                        <tr class="bg-white border-b border-gray-200">
                            <td class="py-3 px-6 text-left font-medium">
                                Target
                                <span class="ml-2 text-sm text-gray-600 font-medium">
                                    ({{ number_format($targetPerHari) }} KG/hari × {{ $tanggalEfektif }} hari efektif)
                                </span>
                            </td>
                            <td class="py-3 px-6 text-right font-semibold">
                                @if($targetPerHari > 0)
                                    {{ number_format($targetTotal) }} KG
                                @else
                                    <span class="text-gray-400 italic text-xs">Target belum diset</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <td class="py-3 px-6 text-left font-medium">
                                Persentase
                                <span class="ml-2 text-sm text-gray-600 font-medium">(Berat Bersih / Target × 100%)</span>
                            </td>
                            <td class="py-3 px-6 text-right font-semibold">
                                @if($targetTotal > 0)
                                    {{ number_format(($grandBeratBersih / $targetTotal) * 100, 2) }} %
                                @else
                                    <span class="text-gray-400 italic text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="bg-white border-b border-gray-200">
                            <td class="py-3 px-6 text-left font-medium">Total Retensi</td>
                            <td class="py-3 px-6 text-right font-semibold">{{ number_format($grandRetensi) }}</td>
                        </tr>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <td class="py-3 px-6 text-left font-medium">
                                Total Biaya
                                <span class="ml-2 text-sm text-gray-600 font-medium">(Berat Bersih × Cost - Total Retensi)</span>
                            </td>
                            <td class="py-3 px-6 text-right font-semibold">{{ number_format(($grandBeratBersih * $cost) - $grandRetensi) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        {{-- ============================================================ --}}
        {{-- KETERANGAN TANGGAL TIDAK ADA DATA                           --}}
        {{-- ============================================================ --}}
        @if(!empty($alasanList))
        <div class="mt-8 mb-8 print-break">
            <h2 class="text-lg font-bold text-gray-900 mb-4 text-center uppercase border-b-2 border-gray-300 pb-3">
                KETERANGAN TANGGAL TIDAK EFEKTIF
            </h2>
            <div class="overflow-x-auto">
                <table class="report-table min-w-full border-2 border-gray-300 text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="py-2 px-4 w-12 text-center">No</th>
                            <th class="py-2 px-4 text-left">Tanggal</th>
                            <th class="py-2 px-4 text-left">Hari</th>
                            <th class="py-2 px-4 text-left">Keterangan / Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $namaHariMap = [
                                'Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu',
                                'Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu','Sunday'=>'Minggu',
                            ];
                        @endphp
                        @foreach($alasanList as $idx => $item)
                        @php
                            $carbon = \Carbon\Carbon::parse($item['tanggal']);
                            $hari   = $namaHariMap[$carbon->format('l')] ?? $carbon->format('l');
                        @endphp
                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                            <td class="py-2 px-4 text-center">{{ $idx + 1 }}</td>
                            <td class="py-2 px-4 text-left font-semibold">{{ $carbon->format('d M Y') }}</td>
                            <td class="py-2 px-4 text-left">{{ $hari }}</td>
                            <td class="py-2 px-4 text-left">{{ $item['alasan'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- SIGNATURE SECTION --}}
        <div class="mt-16">
            <div class="text-right mb-8">
                <p class="text-sm font-semibold">Terbanggi Besar, <span id="currentDate"></span></p>
            </div>
            <div class="grid grid-cols-7 gap-4 text-center text-xs">
                <div><p class="font-semibold mb-16">Mengetahui</p><div class="border-t-2 border-gray-400 pt-2"><p class="font-bold">Est. Manager</p></div></div>
                <div><p class="font-semibold mb-16">&nbsp;</p><div class="border-t-2 border-gray-400 pt-2"><p class="font-bold">Askep</p></div></div>
                <div><p class="font-semibold mb-16">Diperiksa</p><div class="border-t-2 border-gray-400 pt-2"><p class="font-bold">KTU</p></div></div>
                <div><p class="font-semibold mb-16">&nbsp;</p><div class="border-t-2 border-gray-400 pt-2"><p class="font-bold">PP&C</p></div></div>
                <div><p class="font-semibold mb-16">&nbsp;</p><div class="border-t-2 border-gray-400 pt-2"><p class="font-bold">Audit</p></div></div>
                <div><p class="font-semibold mb-16">Diterima</p><div class="border-t-2 border-gray-400 pt-2"><p class="font-bold">(...........)</p></div></div>
                <div><p class="font-semibold mb-16">Disiapkan</p><div class="border-t-2 border-gray-400 pt-2"><p class="font-bold">Adm.panen</p></div></div>
            </div>
            <div class="mt-8 text-center text-xs text-gray-400 border-t border-gray-200 pt-4">
                <p>Dokumen ini digenerate secara otomatis pada <span id="currentDateTime"></span></p>
                <p>{{ session('companycode') ?? 'PT. PERKEBUNAN NUSANTARA' }} &mdash; Periode: {{ $periodeLabel }}</p>
            </div>
        </div>

    </div>

    <script>
        const bulanId = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            document.getElementById('currentDate').textContent = now.getDate() + ' ' + bulanId[now.getMonth()] + ' ' + now.getFullYear();
            document.getElementById('currentDateTime').textContent = now.toLocaleString('id-ID');
        });
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') { e.preventDefault(); window.print(); }
            if (e.key === 'Escape') window.history.back();
        });
        window.addEventListener('beforeprint', function() {
            document.title = 'Berita Acara Panen Tebu - {{ $namaKontraktor }} - {{ $periodeLabel }}';
        });
    </script>
</body>
</html>