{{--resources\views\input\rencanakerjaharian\lkh-report-bsm.blade.php--}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <!-- Print-optimized container -->
    <div class="print:p-0 print:m-0 max-w-full mx-auto bg-white p-6">
        
        {{-- Header Section - Professional & Simple --}}
        <div class="border-b-2 border-gray-800 pb-4 mb-6">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2 uppercase tracking-wide">
                        Laporan Kegiatan Harian (LKH)
                    </h1>
                    <h2 class="text-lg font-semibold text-gray-700 mb-3">
                        Cek BSM (Bersih, Segar, Manis)
                    </h2>
                    
                    {{-- Info Grid --}}
                    <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm">
                        <div class="flex">
                            <span class="font-semibold text-gray-700 w-32">No. LKH:</span>
                            <span class="font-mono text-gray-900">{{ $lkhData->lkhno }}</span>
                        </div>
                        <div class="flex">
                            <span class="font-semibold text-gray-700 w-32">No. RKH:</span>
                            <span class="font-mono text-gray-900">{{ $lkhData->rkhno }}</span>
                        </div>
                        <div class="flex">
                            <span class="font-semibold text-gray-700 w-32">Tanggal:</span>
                            <span class="text-gray-900">{{ \Carbon\Carbon::parse($lkhData->lkhdate)->format('d F Y') }}</span>
                        </div>
                        <div class="flex">
                            <span class="font-semibold text-gray-700 w-32">Aktivitas:</span>
                            <span class="text-gray-900">{{ $lkhData->activitycode }} - Cek BSM</span>
                        </div>
                        <div class="flex">
                            <span class="font-semibold text-gray-700 w-32">Mandor:</span>
                            <span class="text-gray-900">{{ $lkhData->mandornama ?? $lkhData->mandorid }}</span>
                        </div>
                        <div class="flex">
                            <span class="font-semibold text-gray-700 w-32">Status:</span>
                            <span class="font-medium
                                {{ $lkhData->status == 'APPROVED' ? 'text-green-700' : '' }}
                                {{ $lkhData->status == 'SUBMITTED' ? 'text-blue-700' : '' }}
                                {{ $lkhData->status == 'DRAFT' ? 'text-yellow-700' : '' }}">
                                {{ $lkhData->status }}
                            </span>
                        </div>
                    </div>
                </div>
                
                {{-- Simple Summary Box --}}
                <div class="ml-8 border-l-4 border-gray-800 pl-6 text-sm">
                    <h3 class="font-bold text-gray-900 mb-2 uppercase text-xs tracking-wide">Ringkasan</h3>
                    @php
                        $totalPlots = $lkhBsmDetails->count();
                        $completedPlots = $lkhBsmDetails->where('status', 'COMPLETED')->count();
                        $pendingPlots = $totalPlots - $completedPlots;
                        $percentage = $totalPlots > 0 ? round(($completedPlots / $totalPlots) * 100, 1) : 0;
                    @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between gap-8">
                            <span class="text-gray-700">Total SJ:</span>
                            <span class="font-bold text-gray-900">{{ $totalPlots }}</span>
                        </div>
                        <div class="flex justify-between gap-8">
                            <span class="text-gray-700">Completed:</span>
                            <span class="font-bold text-green-700">{{ $completedPlots }}</span>
                        </div>
                        <div class="flex justify-between gap-8">
                            <span class="text-gray-700">Pending:</span>
                            <span class="font-bold text-orange-700">{{ $pendingPlots }}</span>
                        </div>
                        <div class="flex justify-between gap-8 pt-1 border-t border-gray-300">
                            <span class="text-gray-700">Progress:</span>
                            <span class="font-bold text-lg {{ $percentage == 100 ? 'text-green-700' : 'text-orange-700' }}">
                                {{ $percentage }}%
                            </span>
                        </div>
                        <div class="flex justify-between gap-8 pt-1 border-t border-gray-300">
                            <span class="text-gray-700">Total Pekerja:</span>
                            <span class="font-bold text-gray-900">{{ $lkhData->totalworkers ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between gap-8">
                            <span class="text-gray-700">Total Upah:</span>
                            <span class="font-bold text-gray-900">Rp {{ number_format($lkhData->totalupahall ?? 0, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics - FIXED numeric conversion --}}
        @php
            $completedData = $lkhBsmDetails->where('status', 'COMPLETED');
            $premiumData = $completedData->where('kodetebang_label', 'Premium');
            $nonPremiumData = $completedData->where('kodetebang_label', 'Non-Premium');
            
            $gradeA = $completedData->where('grade', 'A')->count();
            $gradeB = $completedData->where('grade', 'B')->count();
            $gradeC = $completedData->where('grade', 'C')->count();
            
            // Convert averagescore string to float before calculating average
            $avgScore = $completedData
                ->filter(function($item) { 
                    return !is_null($item->averagescore) && $item->averagescore !== ''; 
                })
                ->map(function($item) {
                    return (float)str_replace(',', '', $item->averagescore);
                })
                ->avg();
            
            $avgScore = $avgScore ?: 0;
            
            // Calculate premium average
            $premiumAvg = $premiumData
                ->filter(function($item) { 
                    return !is_null($item->averagescore) && $item->averagescore !== ''; 
                })
                ->map(function($item) {
                    return (float)str_replace(',', '', $item->averagescore);
                })
                ->avg();
            
            $premiumAvg = $premiumAvg ?: 0;
            
            // Calculate non-premium average
            $nonPremiumAvg = $nonPremiumData
                ->filter(function($item) { 
                    return !is_null($item->averagescore) && $item->averagescore !== ''; 
                })
                ->map(function($item) {
                    return (float)str_replace(',', '', $item->averagescore);
                })
                ->avg();
            
            $nonPremiumAvg = $nonPremiumAvg ?: 0;
        @endphp

        @if($completedData->count() > 0)
        <div class="mb-6">
            <h3 class="text-sm font-bold text-gray-900 mb-2 uppercase tracking-wide">Statistik BSM</h3>
            
            {{-- Overall Stats --}}
            <div class="grid grid-cols-5 gap-4 text-center text-sm border border-gray-300 mb-4">
                <div class="p-3 border-r border-gray-300">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($avgScore, 2) }}</div>
                    <div class="text-xs text-gray-600 uppercase tracking-wide">Avg Score</div>
                </div>
                <div class="p-3 border-r border-gray-300 bg-gray-50">
                    <div class="text-2xl font-bold text-green-700">{{ $gradeA }}</div>
                    <div class="text-xs text-gray-600 uppercase tracking-wide">Grade A</div>
                </div>
                <div class="p-3 border-r border-gray-300">
                    <div class="text-2xl font-bold text-yellow-700">{{ $gradeB }}</div>
                    <div class="text-xs text-gray-600 uppercase tracking-wide">Grade B</div>
                </div>
                <div class="p-3 border-r border-gray-300 bg-gray-50">
                    <div class="text-2xl font-bold text-red-700">{{ $gradeC }}</div>
                    <div class="text-xs text-gray-600 uppercase tracking-wide">Grade C</div>
                </div>
                <div class="p-3 bg-blue-50">
                    <div class="text-2xl font-bold text-blue-700">{{ $lkhBsmDetails->pluck('suratjalanno')->unique()->count() }}</div>
                    <div class="text-xs text-gray-600 uppercase tracking-wide">Total SJ</div>
                </div>
            </div>
            
            {{-- Premium vs Non-Premium Breakdown --}}
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="border border-blue-300 bg-blue-50 p-3">
                    <h4 class="font-bold text-blue-900 mb-2">Premium</h4>
                    <div class="space-y-1 text-gray-700">
                        <div class="flex justify-between">
                            <span>Total SJ:</span>
                            <strong>{{ $premiumData->count() }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span>Avg Score:</span>
                            <strong>{{ number_format($premiumAvg, 2) }}</strong>
                        </div>
                        <div class="flex justify-between text-xs text-gray-600">
                            <span>A (&lt;1200) | B (1200-1700) | C (&gt;1700)</span>
                        </div>
                    </div>
                </div>
                <div class="border border-gray-300 bg-gray-50 p-3">
                    <h4 class="font-bold text-gray-900 mb-2">Non-Premium</h4>
                    <div class="space-y-1 text-gray-700">
                        <div class="flex justify-between">
                            <span>Total SJ:</span>
                            <strong>{{ $nonPremiumData->count() }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span>Avg Score:</span>
                            <strong>{{ number_format($nonPremiumAvg, 2) }}</strong>
                        </div>
                        <div class="flex justify-between text-xs text-gray-600">
                            <span>A (&lt;1000) | B (1000-2000) | C (&gt;2000)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Section 1: Detail Hasil Cek BSM - Grouped by Plot --}}
        <div class="mb-6">
            <h3 class="text-sm font-bold text-gray-900 mb-2 uppercase tracking-wide border-b border-gray-400 pb-1">
                Detail Hasil Cek BSM Per Surat Jalan
            </h3>
            
            @php
                $groupedByPlot = $lkhBsmDetails->groupBy('plot');
            @endphp
            
            @foreach($groupedByPlot as $plot => $details)
                {{-- Plot Header --}}
                <div class="mt-4 mb-2 bg-gray-200 px-3 py-2 font-bold text-gray-900">
                    Plot: {{ $details->first()->plot_display }}
                    <span class="text-sm font-normal text-gray-600 ml-3">
                        ({{ $details->count() }} Surat Jalan)
                    </span>
                </div>
                
                <table class="w-full border-collapse border border-gray-400 text-xs mb-6" style="table-layout: fixed;">
                    <thead>
                        <tr class="bg-gray-200">
                            <th rowspan="2" class="border border-gray-400 px-2 py-2 font-semibold text-gray-900" style="width: 4%;">No</th>
                            <th rowspan="2" class="border border-gray-400 px-2 py-2 font-semibold text-gray-900" style="width: 18%;">Surat Jalan</th>
                            <th rowspan="2" class="border border-gray-400 px-2 py-2 font-semibold text-gray-900" style="width: 10%;">Kodetebang</th>
                            <th rowspan="2" class="border border-gray-400 px-2 py-2 font-semibold text-gray-900" style="width: 12%;">Batch</th>
                            <th colspan="3" class="border border-gray-400 px-2 py-2 font-semibold text-gray-900 bg-gray-300" style="width: 24%;">Nilai BSM</th>
                            <th rowspan="2" class="border border-gray-400 px-2 py-2 font-semibold text-gray-900" style="width: 8%;">Average</th>
                            <th rowspan="2" class="border border-gray-400 px-2 py-2 font-semibold text-gray-900" style="width: 6%;">Grade</th>
                            <th rowspan="2" class="border border-gray-400 px-2 py-2 font-semibold text-gray-900" style="width: 10%;">Status</th>
                            <th rowspan="2" class="border border-gray-400 px-2 py-2 font-semibold text-gray-900" style="width: 8%;">Keterangan</th>
                        </tr>
                        <tr class="bg-gray-300">
                            <th class="border border-gray-400 px-2 py-1 font-semibold text-gray-900" style="width: 8%;">Bersih</th>
                            <th class="border border-gray-400 px-2 py-1 font-semibold text-gray-900" style="width: 8%;">Segar</th>
                            <th class="border border-gray-400 px-2 py-1 font-semibold text-gray-900" style="width: 8%;">Manis</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($details as $index => $detail)
                        <tr class="{{ $detail->status == 'COMPLETED' ? 'bg-gray-50' : '' }}">
                            <td class="border border-gray-400 px-2 py-2 text-center">{{ $index + 1 }}</td>
                            <td class="border border-gray-400 px-2 py-2 font-mono text-xs">{{ $detail->suratjalanno }}</td>
                            <td class="border border-gray-400 px-2 py-2 text-center">
                                <span class="text-xs {{ $detail->kodetebang_label == 'Premium' ? 'font-bold text-blue-700' : 'text-gray-700' }}">
                                    {{ $detail->kodetebang_label }}
                                </span>
                            </td>
                            <td class="border border-gray-400 px-2 py-2 text-center font-mono">{{ $detail->batchno }}</td>
                            
                            {{-- Nilai BSM --}}
                            <td class="border border-gray-400 px-2 py-2 text-center bg-gray-50">
                                @if($detail->nilaibersih)
                                    <strong>{{ $detail->nilaibersih }}</strong>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="border border-gray-400 px-2 py-2 text-center bg-gray-50">
                                @if($detail->nilaisegar)
                                    <strong>{{ $detail->nilaisegar }}</strong>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="border border-gray-400 px-2 py-2 text-center bg-gray-50">
                                @if($detail->nilaimanis)
                                    <strong>{{ $detail->nilaimanis }}</strong>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            
                            {{-- Average --}}
                            <td class="border border-gray-400 px-2 py-2 text-center">
                                @if($detail->averagescore)
                                    <strong class="text-base">{{ $detail->averagescore }}</strong>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            
                            {{-- Grade --}}
                            <td class="border border-gray-400 px-2 py-2 text-center">
                                @if($detail->grade == 'A')
                                    <span class="font-bold text-green-700">A</span>
                                @elseif($detail->grade == 'B')
                                    <span class="font-bold text-yellow-700">B</span>
                                @elseif($detail->grade == 'C')
                                    <span class="font-bold text-red-700">C</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            
                            {{-- Status --}}
                            <td class="border border-gray-400 px-2 py-2 text-center">
                                @if($detail->status == 'COMPLETED')
                                    <span class="font-medium text-green-700">✓ COMPLETED</span>
                                @else
                                    <span class="font-medium text-orange-700">PENDING</span>
                                @endif
                            </td>
                            
                            <td class="border border-gray-400 px-2 py-2 text-gray-700">{{ $detail->keterangan }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
            
            @if($lkhBsmDetails->isEmpty())
            <div class="border border-gray-400 px-4 py-8 text-center text-gray-500">
                Tidak ada data BSM. Android akan insert data per Surat Jalan.
            </div>
            @endif
        </div>

        {{-- Section 2: Detail Pekerja Harian --}}
        @if($lkhWorkerDetails && $lkhWorkerDetails->count() > 0)
        <div class="mb-6">
            <h3 class="text-sm font-bold text-gray-900 mb-2 uppercase tracking-wide border-b border-gray-400 pb-1">
                Detail Pekerja Harian
            </h3>
            
            <table class="w-full border-collapse border border-gray-400 text-xs">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border border-gray-400 px-2 py-2 font-semibold text-gray-900 w-8">No</th>
                        <th class="border border-gray-400 px-2 py-2 font-semibold text-gray-900">Nama Pekerja</th>
                        <th class="border border-gray-400 px-2 py-2 font-semibold text-gray-900">NIK</th>
                        <th class="border border-gray-400 px-2 py-2 font-semibold text-gray-900">Jam Masuk</th>
                        <th class="border border-gray-400 px-2 py-2 font-semibold text-gray-900">Jam Selesai</th>
                        <th class="border border-gray-400 px-2 py-2 font-semibold text-gray-900">Jam Kerja</th>
                        <th class="border border-gray-400 px-2 py-2 font-semibold text-gray-900">Overtime</th>
                        <th class="border border-gray-400 px-2 py-2 font-semibold text-gray-900">Premi</th>
                        <th class="border border-gray-400 px-2 py-2 font-semibold text-gray-900">Upah Harian</th>
                        <th class="border border-gray-400 px-2 py-2 font-semibold text-gray-900">Upah Lembur</th>
                        <th class="border border-gray-400 px-2 py-2 font-semibold text-gray-900 bg-gray-300">Total Upah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lkhWorkerDetails as $index => $worker)
                    <tr>
                        <td class="border border-gray-400 px-2 py-2 text-center bg-gray-50">{{ $index + 1 }}</td>
                        <td class="border border-gray-400 px-2 py-2 font-medium">
                            {{ $worker->tenagakerja->nama ?? $worker->tenagakerjaid }}
                        </td>
                        <td class="border border-gray-400 px-2 py-2 font-mono text-center">
                            {{ $worker->tenagakerja->nik ?? '-' }}
                        </td>
                        <td class="border border-gray-400 px-2 py-2 text-center font-mono">
                            {{ $worker->jammasuk ? \Carbon\Carbon::parse($worker->jammasuk)->format('H:i') : '-' }}
                        </td>
                        <td class="border border-gray-400 px-2 py-2 text-center font-mono">
                            {{ $worker->jamselesai ? \Carbon\Carbon::parse($worker->jamselesai)->format('H:i') : '-' }}
                        </td>
                        <td class="border border-gray-400 px-2 py-2 text-center">
                            {{ ($worker->totaljamkerja ?? 0) > 0 ? number_format($worker->totaljamkerja, 0) . ' jam' : '-' }}
                        </td>
                        <td class="border border-gray-400 px-2 py-2 text-center">
                            {{ ($worker->overtimehours ?? 0) > 0 ? number_format($worker->overtimehours, 0) . ' jam' : '-' }}
                        </td>
                        <td class="border border-gray-400 px-2 py-2 text-right">
                            Rp {{ number_format($worker->premi ?? 0, 2, ',', '.') }}
                        </td>
                        <td class="border border-gray-400 px-2 py-2 text-right">
                            Rp {{ number_format($worker->upahharian ?? 0, 2, ',', '.') }}
                        </td>
                        <td class="border border-gray-400 px-2 py-2 text-right">
                            Rp {{ number_format($worker->upahlembur ?? 0, 2, ',', '.') }}
                        </td>
                        <td class="border border-gray-400 px-2 py-2 text-right font-bold bg-gray-100">
                            Rp {{ number_format($worker->totalupah ?? 0, 2, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-200 font-bold">
                        <td colspan="10" class="border border-gray-400 px-2 py-2 text-right">TOTAL UPAH:</td>
                        <td class="border border-gray-400 px-2 py-2 text-right bg-gray-300">
                            Rp {{ number_format($lkhWorkerDetails->sum('totalupah'), 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        {{-- Keterangan --}}
        @if($lkhData->keterangan)
        <div class="mb-6">
            <h3 class="text-sm font-bold text-gray-900 mb-2 uppercase tracking-wide">Keterangan</h3>
            <div class="border border-gray-400 p-3 bg-gray-50">
                <p class="text-sm text-gray-800">{{ $lkhData->keterangan }}</p>
            </div>
        </div>
        @endif

        {{-- Signature Section --}}
        <div class="mt-8 border-t-2 border-gray-800 pt-6">
            <h3 class="text-sm font-bold text-gray-900 mb-4 uppercase tracking-wide">Persetujuan</h3>
            <div class="grid grid-cols-3 gap-8 text-center text-sm">
                @for($i = 1; $i <= 3; $i++)
                    @php
                        $flagField = "approval{$i}flag";
                        $dateField = "approval{$i}date";
                        $jabatanField = "jabatan{$i}name";
                    @endphp
                    <div>
                        <div class="border-b-2 border-gray-800 h-16 mb-2"></div>
                        <p class="font-semibold text-gray-900">Jabatan {{ $i }}</p>
                        <p class="text-xs text-gray-600">{{ $approvals->$jabatanField ?? 'Tidak diatur' }}</p>
                        @if($lkhData->$flagField == '1')
                            <p class="text-xs text-green-700 mt-1">
                                ✓ Disetujui: {{ $lkhData->$dateField ? \Carbon\Carbon::parse($lkhData->$dateField)->format('d/m/Y H:i') : '' }}
                            </p>
                        @elseif($lkhData->$flagField == '0')
                            <p class="text-xs text-red-700 mt-1">
                                ✗ Ditolak: {{ $lkhData->$dateField ? \Carbon\Carbon::parse($lkhData->$dateField)->format('d/m/Y H:i') : '' }}
                            </p>
                        @else
                            <p class="text-xs text-gray-500 mt-1">Menunggu persetujuan</p>
                        @endif
                    </div>
                @endfor
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
                onclick="window.open('{{ route('transaction.rencanakerjaharian.printLKH', $lkhData->lkhno) }}', '_blank')"
                class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
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
            .no-print {
                display: none !important;
            }
            
            body {
                font-size: 11px;
            }
            
            table {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>

    <script>
        function handlePrint() {
            const elementsToHide = [
                '.sidebar', '.navbar', '.header', '.nav', '.breadcrumb', 
                '.footer', 'nav', '#sidebar', '#header', '#navbar'
            ];
            
            elementsToHide.forEach(selector => {
                const elements = document.querySelectorAll(selector);
                elements.forEach(el => el.style.display = 'none');
            });
            
            window.print();
            
            setTimeout(() => {
                elementsToHide.forEach(selector => {
                    const elements = document.querySelectorAll(selector);
                    elements.forEach(el => el.style.display = '');
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