{{--resources\views\transaction\rencanakerjaharian\lkh-report.blade.php--}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="print:p-0 print:m-0 max-w-full mx-auto bg-white rounded-lg shadow-lg p-4">
        {{-- Header Section --}}
        <div class="bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
            <div class="flex gap-4">
                <div class="flex-1">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h1 class="text-xl font-bold text-gray-800 mb-2">
                                LAPORAN KEGIATAN HARIAN (LKH)
                                @if($lkhData->jenistenagakerja == 1)
                                    - TENAGA HARIAN
                                @else
                                    - TENAGA BORONGAN
                                @endif
                            </h1>
                            <div class="space-y-1">
                                <p class="text-sm text-gray-600">No. LKH: <span class="font-mono font-semibold">{{ $lkhData->lkhno }}</span></p>
                                <p class="text-sm text-gray-600">No. RKH: <span class="font-mono font-semibold">{{ $lkhData->rkhno }}</span></p>
                                <p class="text-sm text-gray-600">Tanggal: <span class="font-semibold">{{ \Carbon\Carbon::parse($lkhData->lkhdate)->format('l, d F Y') }}</span></p>
                                <p class="text-sm text-gray-600">Aktivitas: <span class="font-semibold">{{ $lkhData->activitycode }} - {{ $lkhData->activityname ?? '' }}</span></p>
                                <p class="text-sm text-gray-600">Mandor: <span class="font-semibold">{{ $lkhData->mandornama ?? $lkhData->mandorid }}</span></p>
                            </div>
                        </div>
                        <div class="text-right ml-4">
                            @if($lkhData->status == 'COMPLETED')
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">Selesai</span>
                            @elseif($lkhData->status == 'DRAFT')
                                <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm font-medium">Draft</span>
                            @elseif($lkhData->status == 'APPROVED')
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">Approved</span>
                            @elseif($lkhData->status == 'SUBMITTED')
                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">Submitted</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Summary Box --}}
                <div class="w-64 bg-white rounded-lg p-4 border border-gray-300">
                    <h3 class="text-sm font-bold text-gray-800 mb-3">Ringkasan</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Total Pekerja:</span> 
                            <span class="font-bold text-gray-900">{{ $lkhData->totalworkers ?? 0 }} orang</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Total Hasil:</span> 
                            <span class="font-bold text-gray-900">{{ number_format($lkhData->totalhasil ?? 0, 2) }} Ha</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Total Sisa:</span> 
                            <span class="font-bold text-gray-900">{{ number_format($lkhData->totalsisa ?? 0, 2) }} Ha</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Total Upah:</span> 
                            <span class="font-bold text-gray-900">Rp {{ number_format($lkhData->totalupahall ?? 0, 2, ',', '.') }}</span>
                        </div>
                        @if($lkhData->insentifhk > 0)
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Insentif:</span> 
                            <span class="font-bold text-gray-900">{{ number_format($lkhData->insentifhk, 0) }} HK — Rp {{ number_format($lkhData->totalinsentif ?? 0, 2, ',', '.') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Plot Details Section --}}
        @if($lkhPlotDetails && $lkhPlotDetails->count() > 0)
        <div class="bg-white rounded-lg p-4 mb-4 border border-gray-200">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-bold text-gray-800">Detail Plot</h3>
                <div class="text-sm text-gray-500">
                    Total Plot: {{ $lkhPlotDetails->count() }}
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-12">No</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-20">Blok</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-20">Plot</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-24">Luas RKH</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-24">Luas Hasil</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-24">Luas Sisa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lkhPlotDetails as $index => $plot)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm bg-gray-50">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm font-medium">{{ $plot->blok }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm font-medium">{{ $plot->plot }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($plot->luasrkh ?? 0, 2) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm font-semibold">
                                {{ number_format($plot->luashasil ?? 0, 2) }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($plot->luassisa ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-semibold">
                        <tr>
                            <td colspan="3" class="border border-gray-300 px-3 py-2 text-center text-sm">TOTAL</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($lkhPlotDetails->sum('luasrkh'), 2) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($lkhPlotDetails->sum('luashasil'), 2) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($lkhPlotDetails->sum('luassisa'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif

        {{-- Worker Details Section --}}
        @if($lkhWorkerDetails && $lkhWorkerDetails->count() > 0)
        <div class="bg-white rounded-lg p-4 mb-4 border border-gray-200">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-bold text-gray-800">Detail Pekerja</h3>
                <div class="text-sm text-gray-500">
                    Total Pekerja: {{ $lkhWorkerDetails->count() }}
                </div>
            </div>

            @if($lkhData->jenistenagakerja == 1)
                {{-- Tenaga Harian Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse bg-white">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-8">No</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Kode Pekerja</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-40">Nama Pekerja</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-32">NIK</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Jam Masuk</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Jam Selesai</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Jam Kerja</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Overtime</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Premi</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Upah Harian</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Upah Lembur</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Total Upah</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lkhWorkerDetails as $index => $worker)
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-300 px-2 py-2 text-center text-sm bg-gray-50">{{ $index + 1 }}</td>
                                <td class="border border-gray-300 px-2 py-2 text-sm font-mono text-center">{{ $worker->tenagakerjaid ?? '-' }}</td>
                                <td class="border border-gray-300 px-2 py-2 text-sm font-medium">{{ $worker->nama ?? '-' }}</td>
                                <td class="border border-gray-300 px-2 py-2 text-sm font-mono">{{ $worker->nik ?? '-' }}</td>
                                <td class="border border-gray-300 px-2 py-2 text-center text-sm font-mono">
                                    {{ $worker->jammasuk ? \Carbon\Carbon::parse($worker->jammasuk)->format('H:i') : '-' }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-center text-sm font-mono">
                                    {{ $worker->jamselesai ? \Carbon\Carbon::parse($worker->jamselesai)->format('H:i') : '-' }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-center text-sm">
                                    {{ ($worker->totaljamkerja ?? 0) > 0 ? number_format($worker->totaljamkerja, 0) . ' jam' : '-' }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-center text-sm">
                                    {{ ($worker->overtimehours ?? 0) > 0 ? number_format($worker->overtimehours, 0) . ' jam' : '-' }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-right text-sm">
                                    Rp {{ number_format($worker->premi ?? 0, 2, ',', '.') }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-right text-sm">
                                    Rp {{ number_format($worker->upahharian ?? 0, 2, ',', '.') }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-right text-sm">
                                    Rp {{ number_format($worker->upahlembur ?? 0, 2, ',', '.') }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-right text-sm font-semibold bg-green-50">
                                    Rp {{ number_format($worker->totalupah ?? 0, 2, ',', '.') }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-sm text-gray-600">{{ $worker->keterangan ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-100 font-semibold">
                            <tr>
                                <td colspan="10" class="border border-gray-300 px-2 py-2 text-center text-sm">TOTAL UPAH</td>
                                <td class="border border-gray-300 px-2 py-2 text-right text-sm">
                                    Rp {{ number_format($lkhWorkerDetails->sum('upahlembur'), 2, ',', '.') }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-right text-sm bg-green-100">
                                    Rp {{ number_format($lkhWorkerDetails->sum('totalupah'), 2, ',', '.') }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                {{-- Tenaga Borongan Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse bg-white">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-12">No</th>
                                <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-32">Kode Pekerja</th>
                                <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700">Nama Pekerja</th>
                                <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-40">NIK</th>
                                <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lkhWorkerDetails as $index => $worker)
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-300 px-3 py-2 text-center text-sm bg-gray-50">{{ $index + 1 }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-sm font-mono text-center">{{ $worker->tenagakerjaid ?? '-' }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-sm font-medium">{{ $worker->nama ?? '-' }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-sm font-mono">{{ $worker->nik ?? '-' }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-sm text-gray-600">{{ $worker->keterangan ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Borongan Wage Summary --}}
                <div class="mt-4 bg-white rounded-lg p-4 border border-gray-200">
                    @if(isset($boronganRate) && $boronganRate > 0)
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-300">
                            <h4 class="text-sm font-bold text-gray-800 mb-3">Total Upah Borongan</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-700">Luas Hasil:</span> 
                                    <span class="font-bold text-gray-900">{{ number_format($lkhData->totalhasil ?? 0, 2) }} Ha</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-700">Rate per Ha:</span> 
                                    <span class="font-bold text-gray-900">Rp {{ number_format($boronganRate, 2, ',', '.') }}</span>
                                </div>
                                <div class="pt-2 border-t border-gray-300"></div>
                                <div class="flex justify-between">
                                    <span class="font-semibold text-gray-800">Total Upah:</span> 
                                    <span class="font-bold text-lg text-gray-900">Rp {{ number_format($lkhData->totalupahall ?? 0, 2, ',', '.') }}</span>
                                </div>
                                @if($lkhData->insentifhk > 0)
                                <div class="pt-2 border-t border-gray-300"></div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-700">Insentif:</span> 
                                    <span class="font-bold text-gray-900">{{ number_format($lkhData->insentifhk, 0) }} HK</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold text-gray-800">Total Insentif:</span> 
                                    <span class="font-bold text-lg text-gray-900">Rp {{ number_format($lkhData->totalinsentif ?? 0, 2, ',', '.') }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="mt-3 pt-3 border-t border-gray-300">
                                <p class="text-xs text-gray-600 italic">
                                    * Pembagian upah ke masing-masing pekerja dikelola oleh mandor
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-300">
                            <div class="text-center py-3">
                                <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-200 rounded-full mb-2">
                                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-bold text-gray-800 mb-1">Rate Upah Borongan Tidak Ditemukan</h4>
                                <p class="text-xs text-gray-600 mb-1">
                                    Aktivitas: <span class="font-semibold">{{ $lkhData->activitycode }}</span> | 
                                    Tanggal: <span class="font-semibold">{{ \Carbon\Carbon::parse($lkhData->lkhdate)->format('d/m/Y') }}</span>
                                </p>
                                <p class="text-xs text-gray-500 italic mt-2">
                                    Silakan hubungi admin untuk mengatur rate upah borongan di master data
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
        @endif

        {{-- Material Details Section --}}
        @if($lkhMaterialDetails && $lkhMaterialDetails->count() > 0)
        <div class="bg-white rounded-lg p-4 mb-4 border border-gray-200">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-bold text-gray-800">Detail Material</h3>
                <div class="text-sm text-gray-500">
                    Total Items: {{ $lkhMaterialDetails->count() }}
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-12">No</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-20">Plot</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-32">Item Code</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-40">Nama Item</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-24">Qty Diterima</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-24">Qty Sisa</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-24">Qty Digunakan</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-20">Satuan</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-32">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lkhMaterialDetails as $index => $material)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm bg-gray-50">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm font-medium">{{ $material->plot ?? '-' }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-sm font-mono font-medium">{{ $material->itemcode }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-sm">{{ $material->itemname ?? 'Unknown Item' }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($material->qtyditerima ?? 0, 3) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($material->qtysisa ?? 0, 3) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm font-semibold {{ ($material->qtydigunakan ?? 0) > 0 ? 'text-green-600' : '' }}">
                                {{ number_format($material->qtydigunakan ?? 0, 3) }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm">{{ $material->satuan ?? '-' }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-sm text-gray-600">{{ $material->keterangan ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-semibold">
                        <tr>
                            <td colspan="4" class="border border-gray-300 px-3 py-2 text-center text-sm">TOTAL</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($lkhMaterialDetails->sum('qtyditerima'), 3) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($lkhMaterialDetails->sum('qtysisa'), 3) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm font-bold text-green-600">{{ number_format($lkhMaterialDetails->sum('qtydigunakan'), 3) }}</td>
                            <td colspan="2" class="border border-gray-300 px-3 py-2 text-center text-sm">-</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @else
        <div class="bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
            <div class="text-center">
                <h3 class="text-lg font-bold text-gray-800 mb-2">Detail Material</h3>
                <p class="text-gray-500">Tidak ada data material untuk LKH ini</p>
            </div>
        </div>
        @endif

        {{-- Keterangan Section --}}
        @if($lkhData->keterangan)
        <div class="bg-white rounded-lg p-4 mb-4 border border-gray-200">
            <div class="mb-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan</label>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-300">
                    <p class="text-sm text-gray-700">{{ $lkhData->keterangan }}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Approval Section --}}
        <div class="bg-white rounded-lg p-4 mb-4 border border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-3">Persetujuan</h3>
            <div class="grid grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="border-b border-gray-300 h-16 mb-2"></div>
                    <p class="text-sm font-medium">Jabatan 1</p>
                    <p class="text-xs text-gray-600">{{ $approvals->jabatan1name ?? 'Tidak diatur' }}</p>
                    @if($lkhData->approval1flag == '1')
                        <p class="text-xs text-green-600 mt-1">✓ Disetujui: {{ $lkhData->approval1date ? \Carbon\Carbon::parse($lkhData->approval1date)->format('d/m/Y H:i') : '' }}</p>
                    @elseif($lkhData->approval1flag == '0')
                        <p class="text-xs text-red-600 mt-1">✗ Ditolak: {{ $lkhData->approval1date ? \Carbon\Carbon::parse($lkhData->approval1date)->format('d/m/Y H:i') : '' }}</p>
                    @else
                        <p class="text-xs text-gray-500 mt-1">Menunggu persetujuan</p>
                    @endif
                </div>
                <div class="text-center">
                    <div class="border-b border-gray-300 h-16 mb-2"></div>
                    <p class="text-sm font-medium">Jabatan 2</p>
                    <p class="text-xs text-gray-600">{{ $approvals->jabatan2name ?? 'Tidak diatur' }}</p>
                    @if($lkhData->approval2flag == '1')
                        <p class="text-xs text-green-600 mt-1">✓ Disetujui: {{ $lkhData->approval2date ? \Carbon\Carbon::parse($lkhData->approval2date)->format('d/m/Y H:i') : '' }}</p>
                    @elseif($lkhData->approval2flag == '0')
                        <p class="text-xs text-red-600 mt-1">✗ Ditolak: {{ $lkhData->approval2date ? \Carbon\Carbon::parse($lkhData->approval2date)->format('d/m/Y H:i') : '' }}</p>
                    @else
                        <p class="text-xs text-gray-500 mt-1">Menunggu persetujuan</p>
                    @endif
                </div>
                <div class="text-center">
                    <div class="border-b border-gray-300 h-16 mb-2"></div>
                    <p class="text-sm font-medium">Jabatan 3</p>
                    <p class="text-xs text-gray-600">{{ $approvals->jabatan3name ?? 'Tidak diatur' }}</p>
                    @if($lkhData->approval3flag == '1')
                        <p class="text-xs text-green-600 mt-1">✓ Disetujui: {{ $lkhData->approval3date ? \Carbon\Carbon::parse($lkhData->approval3date)->format('d/m/Y H:i') : '' }}</p>
                    @elseif($lkhData->approval3flag == '0')
                        <p class="text-xs text-red-600 mt-1">✗ Ditolak: {{ $lkhData->approval3date ? \Carbon\Carbon::parse($lkhData->approval3date)->format('d/m/Y H:i') : '' }}</p>
                    @else
                        <p class="text-xs text-gray-500 mt-1">Menunggu persetujuan</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="mt-6 flex justify-center space-x-4 no-print">
            @if($lkhData->status != 'APPROVED' && $lkhData->status != 'COMPLETED' && !$lkhData->issubmit)
            <button 
                onclick="window.location.href='{{ route('transaction.rencanakerjaharian.editLKH', $lkhData->lkhno) }}'"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit LKH
            </button>
            @endif

            {{-- Submit Button: only show if status DRAFT and not yet submitted --}}
            @if(!$lkhData->issubmit && $lkhData->status === 'DRAFT')
            <button 
                onclick="handleSubmitLKH('{{ $lkhData->lkhno }}')"
                id="btn-submit-lkh"
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Submit untuk Approval
            </button>
            @endif
            
            <button 
                onclick="handlePrint()"
                class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2 2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Print
            </button>
            
            <button 
                onclick="window.history.back()"
                class="bg-white border border-gray-300 hover:border-gray-400 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors hover:bg-gray-50 flex items-center"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </button>
        </div>
    </div>

    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; }
            table { font-size: 10px; }
            .action-buttons { display: none; }
        }
    </style>

    <script>
        function handlePrint() {
            const elementsToHide = [
                '.sidebar', '.navbar', '.header', '.nav', '.breadcrumb', 
                '.footer', 'nav', '#sidebar', '#header', '#navbar'
            ];
            
            elementsToHide.forEach(selector => {
                document.querySelectorAll(selector).forEach(el => el.style.display = 'none');
            });
            
            window.print();
            
            setTimeout(() => {
                elementsToHide.forEach(selector => {
                    document.querySelectorAll(selector).forEach(el => el.style.display = '');
                });
            }, 1000);
        }

        async function handleSubmitLKH(lkhno) {
            if (!confirm('Apakah Anda yakin ingin mengirim LKH ini untuk approval?')) return;

            const btn = document.getElementById('btn-submit-lkh');
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Mengirim...
            `;

            try {
                const response = await fetch('{{ route("transaction.rencanakerjaharian.submitLKH") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ lkhno: lkhno })
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Gagal mengirim LKH: ' + data.message);
                    btn.disabled = false;
                    btn.innerHTML = `
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Submit untuk Approval
                    `;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengirim LKH');
                btn.disabled = false;
                btn.innerHTML = `
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Submit untuk Approval
                `;
            }
        }
    </script>
</x-layout>