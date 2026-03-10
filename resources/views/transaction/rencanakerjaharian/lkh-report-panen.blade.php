{{--resources\views\input\rencanakerjaharian\lkh-report-panen.blade.php--}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <!-- Print-optimized container -->
    <div class="print:p-0 print:m-0 max-w-full mx-auto bg-white rounded-lg shadow-lg p-4">
        
        <!-- Header and Summary Section -->
        <div class="bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
            <div class="flex gap-4">
                <!-- Header Section -->
                <div class="flex-1">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h1 class="text-xl font-bold text-gray-800 mb-2">
                                LAPORAN KEGIATAN HARIAN (LKH) - PANEN
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

                <!-- Summary Section -->
                <div class="w-64 bg-white rounded-lg p-4 border border-gray-300">
                    <h3 class="text-sm font-bold text-gray-800 mb-3">Ringkasan</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Total Plot:</span> 
                            <span class="font-bold text-gray-900">{{ $lkhPanenDetails->count() }} plot</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Total HC (Hasil):</span> 
                            <span class="font-bold text-green-700">{{ number_format($lkhPanenDetails->sum('hc'), 2) }} Ha</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Total STC (Sisa):</span> 
                            <span class="font-bold text-orange-700">{{ number_format($lkhPanenDetails->sum('stc'), 2) }} Ha</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 1: KONTRAKTOR & SUBKONTRAKTOR --}}
        <div class="mb-8">
            <div class="bg-gray-800 text-white px-4 py-3 rounded-t-md">
                <h3 class="font-bold text-sm uppercase tracking-wide">Kontraktor & Subkontraktor</h3>
            </div>
            
            <div class="overflow-x-auto border-x border-b border-gray-300 rounded-b-md">
                <table class="min-w-full divide-y divide-gray-300 text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">No</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">ID Kontraktor</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">Nama Kontraktor</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide">Total Subkontraktor</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide">Total Plot</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">List Plot</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($kontraktorSummary as $index => $kontraktor)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-700 font-medium">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 text-gray-900 font-mono">{{ $kontraktor->kontraktor_id }}</td>
                            <td class="px-4 py-3 text-gray-900 font-semibold">{{ $kontraktor->kontraktor_nama ?? 'Unknown' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                    {{ $kontraktor->total_subkontraktor }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                    {{ $kontraktor->total_plot }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 font-mono text-xs">{{ $kontraktor->list_plot }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-sm font-medium">Belum ada data kontraktor</p>
                                    <p class="text-xs text-gray-400">Data akan muncul setelah input dari mobile</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Section 2: LAPORAN PANEN --}}
        <div class="mb-8">
            <div class="bg-gray-800 text-white px-4 py-3 rounded-t-md">
                <h3 class="font-bold text-sm uppercase tracking-wide">Laporan Panen</h3>
            </div>
            
            <div class="overflow-x-auto border-x border-b border-gray-300 rounded-b-md">
                <table class="min-w-full divide-y divide-gray-300 text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide w-16">Blok</th>
                            <th class="px-3 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide w-20">Plot</th>
                            <th class="px-3 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide w-24">Luas Batch<br>(Ha)</th>
                            <th class="px-3 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide w-20">Status</th>
                            <th class="px-3 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide w-16">Hari</th>
                            <th class="px-3 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide bg-orange-50 w-20">STC<br>(Ha)</th>
                            <th class="px-3 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide bg-green-50 w-20">HC<br>(Ha)</th>
                            <th class="px-3 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide bg-gray-50 w-20">BC<br>(Ha)</th>
                            <th class="px-3 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide bg-blue-50 w-20">FB<br>Rit</th>
                            <th class="px-3 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide bg-blue-50 w-20">FB<br>Ton</th>
                            <th class="px-3 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide w-64">Subkontraktor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($lkhPanenDetails as $item)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 py-3 text-gray-900 font-medium">{{ $item->blok }}</td>
                            <td class="px-3 py-3 text-gray-900 font-medium">{{ $item->plot }}</td>
                            <td class="px-3 py-3 text-right text-gray-700">{{ number_format($item->batcharea, 2) }}</td>
                            <td class="px-3 py-3 text-center">
                                <span class="px-2 py-1 rounded text-xs font-medium 
                                    {{ $item->kodestatus == 'PC' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($item->kodestatus == 'RC1' ? 'bg-green-100 text-green-800' : 
                                    ($item->kodestatus == 'RC2' ? 'bg-blue-100 text-blue-800' : 
                                    ($item->kodestatus == 'RC3' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'))) }}">
                                    {{ $item->kodestatus ?? '-' }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                @if($item->haritebang === '-' || is_null($item->haritebang))
                                    <span class="text-gray-400">-</span>
                                @elseif($item->haritebang == 1)
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-800">1</span>
                                @else
                                    <span class="text-gray-700">{{ $item->haritebang }}</span>
                                @endif
                            </td>
                            
                            <td class="px-3 py-3 text-right font-semibold text-orange-700 bg-orange-50">
                                {{ number_format($item->stc, 2) }}
                            </td>
                            
                            @if(!is_null($item->hc))
                                <td class="px-3 py-3 text-right font-semibold text-green-700 bg-green-50">{{ number_format($item->hc, 2) }}</td>
                                <td class="px-3 py-3 text-right text-gray-700 bg-gray-50">{{ number_format($item->bc, 2) }}</td>
                                <td class="px-3 py-3 text-right text-blue-700 bg-blue-50">{{ $item->fieldbalancerit ? number_format($item->fieldbalancerit, 2) : '-' }}</td>
                                <td class="px-3 py-3 text-right text-blue-700 bg-blue-50">{{ $item->fieldbalanceton ? number_format($item->fieldbalanceton, 2) : '-' }}</td>
                            @else
                                <td class="px-3 py-3 text-center text-gray-400 italic text-xs" colspan="4">Menunggu input hasil</td>
                            @endif
                            
                            <td class="px-3 py-3">
                                @php
                                    $plotData = $subkontraktorDetail->where('plot', $item->plot);
                                @endphp
                                
                                @if($plotData->count() > 0)
                                    @php
                                        $kontraktor = $plotData->first();
                                    @endphp
                                    
                                    <div class="mb-2 pb-2 border-b border-gray-200">
                                        <div class="text-xs font-bold text-gray-800">
                                            {{ $kontraktor->kontraktor_id }} - {{ $kontraktor->kontraktor_nama ?? 'Unknown' }}
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-1">
                                        @foreach($plotData as $sk)
                                        <div class="flex items-start gap-2">
                                            <span class="text-blue-600">•</span>
                                            <div class="flex-1 text-xs">
                                                <span class="font-semibold text-gray-900">
                                                    {{ $sk->subkontraktor_nama ?? $sk->subkontraktor_id }}
                                                </span>
                                                <button 
                                                    onclick="showSJModal('{{ $item->plot }}', '{{ $sk->subkontraktor_id }}')"
                                                    class="text-blue-600 hover:text-blue-800 underline ml-1 no-print cursor-pointer"
                                                >
                                                    ({{ $sk->jumlah_sj }} SJ)
                                                </button>
                                                <span class="text-gray-500 ml-1 print-only">({{ $sk->jumlah_sj }} SJ)</span>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 italic text-xs">Belum ada SJ</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="px-3 py-6 text-center text-gray-500">Tidak ada plot yang direncanakan</td>
                        </tr>
                        @endforelse
                    </tbody>
                    
                    @if($lkhPanenDetails->count() > 0)
                    <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-300">
                        <tr>
                            <td colspan="5" class="px-3 py-3 text-right text-gray-900 uppercase text-xs">Total:</td>
                            <td class="px-3 py-3 text-right text-orange-700 bg-orange-50">{{ number_format($lkhPanenDetails->sum('stc'), 2) }}</td>
                            <td class="px-3 py-3 text-right text-green-700 bg-green-50">{{ number_format($lkhPanenDetails->sum('hc'), 2) }}</td>
                            <td class="px-3 py-3 text-right text-gray-700 bg-gray-50">{{ number_format($lkhPanenDetails->sum('bc'), 2) }}</td>
                            <td class="px-3 py-3 text-right text-blue-700 bg-blue-50">{{ number_format($lkhPanenDetails->sum('fieldbalancerit'), 2) }}</td>
                            <td class="px-3 py-3 text-right text-blue-700 bg-blue-50">{{ number_format($lkhPanenDetails->sum('fieldbalanceton'), 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Section 3: PETAK BARU HARI INI (COMPACT 1/3 WIDTH) --}}
        @php
            $petakBaru = $lkhPanenDetails->filter(function($item) {
                return $item->haritebang == 1;
            });
        @endphp

        <div class="mb-8 max-w-md">
            <div class="bg-gray-800 text-white px-4 py-3 rounded-t-md">
                <h3 class="font-bold text-sm uppercase tracking-wide">Petak Baru Hari Ini</h3>
                <p class="text-xs text-gray-300 mt-1">Plot yang baru mulai dipanen hari ini (Hari Tebang 1)</p>
            </div>
            
            <div class="border-x border-b border-gray-300 rounded-b-md bg-white">
                @forelse($petakBaru as $item)
                    <div class="px-4 py-3 border-b border-gray-200 hover:bg-yellow-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-gray-900">{{ $item->blok }}-{{ $item->plot }}</span>
                            <span class="text-sm text-gray-600">{{ number_format($item->batcharea, 2) }} Ha</span>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-sm font-medium">Tidak ada petak baru hari ini</p>
                    </div>
                @endforelse
                
                @if($petakBaru->count() > 0)
                <div class="bg-yellow-50 px-4 py-3 border-t-2 border-gray-300">
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-bold text-gray-900 uppercase">Total:</span>
                        <span class="font-bold text-gray-900">
                            {{ $petakBaru->count() }} Plot • {{ number_format($petakBaru->sum('batcharea'), 2) }} Ha
                        </span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- ✅ NEW Section 4: PLOT MASIH ONGOING --}}
        <div class="mb-8">
            <div class="bg-gray-800 text-white px-4 py-3 rounded-t-md">
                <h3 class="font-bold text-sm uppercase tracking-wide">Plot Masih Ongoing</h3>
                <p class="text-xs text-gray-300 mt-1">Plot yang sedang dipanen namun tidak ada di laporan hari ini</p>
            </div>
            
            <div class="overflow-x-auto border-x border-b border-gray-300 rounded-b-md">
                <table class="min-w-full divide-y divide-gray-300 text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">Blok</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">Plot</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">Batch No</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide">Luas Batch (Ha)</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide">Total Dipanen (Ha)</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide">Sisa (Ha)</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">Terakhir Panen</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide">Skip (Hari)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($ongoingPlots as $plot)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-900 font-medium">{{ $plot->blok }}</td>
                            <td class="px-4 py-3 text-gray-900 font-medium">{{ $plot->plot }}</td>
                            <td class="px-4 py-3 text-gray-900 font-mono text-xs">{{ $plot->batchno }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded text-xs font-medium 
                                    {{ $plot->kodestatus == 'PC' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($plot->kodestatus == 'RC1' ? 'bg-green-100 text-green-800' : 
                                    ($plot->kodestatus == 'RC2' ? 'bg-blue-100 text-blue-800' : 
                                    ($plot->kodestatus == 'RC3' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'))) }}">
                                    {{ $plot->kodestatus }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ $plot->batcharea }}</td>
                            <td class="px-4 py-3 text-right text-green-700 font-semibold">{{ $plot->total_dipanen }}</td>
                            <td class="px-4 py-3 text-right text-orange-700 font-semibold">{{ $plot->sisa }}</td>
                            <td class="px-4 py-3 text-gray-700 text-xs">{{ $plot->last_harvest_date }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($plot->days_since_harvest == 1)
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-semibold">1 hari</span>
                                @elseif($plot->days_since_harvest >= 3)
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-bold">{{ $plot->days_since_harvest }} hari</span>
                                @elseif($plot->days_since_harvest > 1)
                                    <span class="text-gray-700">{{ $plot->days_since_harvest }} hari</span>
                                @else
                                    <span class="text-gray-400">Hari ini</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-sm font-medium">Tidak ada plot ongoing lainnya</p>
                                    <p class="text-xs text-gray-400">Semua plot yang sedang dipanen sudah tercatat di laporan hari ini</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Keterangan Section -->
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

        <!-- Signature Section -->
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

    {{-- MODAL SJ (unchanged) --}}
    <div id="sjModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden items-center justify-center no-print">
        <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white" id="modalTitle">Daftar Surat Jalan</h3>
                <button 
                    onclick="closeSJModal()"
                    class="text-white hover:text-gray-200 transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                <div id="modalLoading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <p class="mt-2 text-sm text-gray-600">Memuat data...</p>
                </div>
                
                <div id="modalContent" class="hidden">
                    <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-sm">
                                <span class="font-semibold text-gray-700">Plot:</span> <span id="modalPlot" class="font-mono text-gray-900"></span>
                                <span class="mx-2 text-gray-400">•</span>
                                <span class="font-semibold text-gray-700">Subkontraktor:</span> <span id="modalSubkontraktor" class="text-gray-900"></span>
                            </div>
                        </div>
                    </div>

                    <table class="min-w-full divide-y divide-gray-300 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">No</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Nomor SJ</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700">Tanggal</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="sjTableBody" class="divide-y divide-gray-200 bg-white">
                        </tbody>
                    </table>

                    <div id="noDataMessage" class="hidden text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-sm">Tidak ada surat jalan ditemukan</p>
                    </div>
                </div>

                <div id="modalError" class="hidden text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-red-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-red-600">Gagal memuat data</p>
                    <p class="text-xs text-gray-500 mt-1" id="errorMessage"></p>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex justify-end border-t border-gray-200">
                <button 
                    onclick="closeSJModal()"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition-colors"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            
            .print-only {
                display: inline !important;
            }
            
            body {
                font-size: 12px;
            }
            
            table {
                font-size: 10px;
            }
            
            .action-buttons {
                display: none;
            }
        }

        .print-only {
            display: none;
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
                elements.forEach(el => {
                    el.style.display = 'none';
                });
            });
            
            window.print();
            
            setTimeout(() => {
                elementsToHide.forEach(selector => {
                    const elements = document.querySelectorAll(selector);
                    elements.forEach(el => {
                        el.style.display = '';
                    });
                });
            }, 1000);
        }

        async function showSJModal(plot, subkontraktorId) {
            const modal = document.getElementById('sjModal');
            const loading = document.getElementById('modalLoading');
            const content = document.getElementById('modalContent');
            const error = document.getElementById('modalError');
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            loading.classList.remove('hidden');
            content.classList.add('hidden');
            error.classList.add('hidden');
            
            document.getElementById('modalPlot').textContent = plot;
            
            try {
                const response = await fetch(`{{ route('transaction.rencanakerjaharian.lkh-panen-report.get-sj') }}?plot=${plot}&subkontraktor_id=${subkontraktorId}&lkhno={{ $lkhData->lkhno }}`);
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('modalSubkontraktor').textContent = data.subkontraktor_nama || subkontraktorId;
                    
                    const tbody = document.getElementById('sjTableBody');
                    tbody.innerHTML = '';
                    
                    if (data.surat_jalan.length > 0) {
                        data.surat_jalan.forEach((sj, index) => {
                            const row = `
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-700">${index + 1}</td>
                                    <td class="px-4 py-3">
                                        <span class="font-mono font-semibold text-blue-600">${sj.suratjalanno}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-700">
                                        ${new Date(sj.tanggalcetakpossecurity).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold ${sj.status === 'Sudah Timbang' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                                            ${sj.status}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a 
                                            href="{{ route('report.report-surat-jalan-timbangan.index') }}/${sj.suratjalanno}"
                                            class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            Lihat Detail
                                        </a>
                                    </td>
                                </tr>
                            `;
                            tbody.innerHTML += row;
                        });
                        
                        document.getElementById('noDataMessage').classList.add('hidden');
                    } else {
                        document.getElementById('noDataMessage').classList.remove('hidden');
                    }
                    
                    loading.classList.add('hidden');
                    content.classList.remove('hidden');
                } else {
                    throw new Error(data.message || 'Gagal memuat data');
                }
            } catch (err) {
                console.error('Error loading SJ data:', err);
                document.getElementById('errorMessage').textContent = err.message;
                loading.classList.add('hidden');
                error.classList.remove('hidden');
            }
        }

        function closeSJModal() {
            const modal = document.getElementById('sjModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.getElementById('sjModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSJModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSJModal();
            }
        });

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