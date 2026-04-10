<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    
    <style>
        h1{text-align:center;color:#333;}
        table{width:100%;border-collapse:separate;border-spacing:0;box-shadow:0 3px 8px rgba(0,0,0,.1);animation:fadeIn 0.4s ease-in;}
        th,td{border:1px solid #ddd;padding:6px 8px;font-size:13px;vertical-align:middle;background-clip:padding-box;}
        tbody tr{transition:background-color .15s;}
        tbody tr:hover td{background:#bbf7d0 !important;color:#14532d !important;}
        tbody tr:hover td *{color:#14532d !important;}
         
        /* ✅ Sticky Vertical */
        .sticky-v{position:sticky;background:#166534;color:#fff;}
        thead tr:first-child .sticky-v{top:0;z-index:16;}
        
        /* ✅ Sticky Horizontal */
        .sticky-h{position:sticky;font-weight:600;}
        thead .sticky-h{background:#166534;color:white;z-index:20;}
        tbody .sticky-h{background:white;color:#333;z-index:9;}

        /* ✅ Minimum width untuk kolom-kolom */
        th.sticky-h.blok{min-width:50px;}
        th.sticky-h:not(.blok){min-width:80px;}  /* Plot */
        th.sticky-v[rowspan="2"]{min-width:70px;}  /* Saldo, Realisasi, % */
        th.sticky-v[colspan="2"]{min-width:140px;}  /* Activity headers */

        .total-row td:not(.sticky-h){min-width:70px;}

        /* ✅ Khusus blok */
        .sticky-h.blok{background:#0f766e;color:white;text-align:center;}
        tbody .sticky-h.blok{background:#0f766e;}
        
        /* ✅ Total row - nempel di bawah header */
        .total-row{position:sticky;top:52px;z-index:15;}
        .total-row td{background:#166534;color:white;font-weight:bold;}
        .total-row .sticky-h{z-index:19;}
        .total-row .sticky-h.blok{background:#0f766e;}
        
        tbody td{background:#fff;} 
        #map{height:720px;width:100%;}
        @keyframes fadeIn{from{opacity:0;}to{opacity:1;}}
        @keyframes modalIn{from{opacity:0;transform:translateY(-16px);}to{opacity:1;transform:translateY(0);}}
        #plot-modal-box{animation:modalIn .2s ease;}
    </style>
    <div class="mx-auto px-6" x-data="{activeTab:'{{ request('tab','table') }}',map:null,markers:[],polygons:[]}">

        
        <div class="mb-6 border-b border-gray-200">
            <nav class="flex space-x-4">
                <a href="?activity={{$activityFilter}}&tab={{ request('tab','table') }}" 
                class="py-2 px-4 border-b-2 font-medium text-sm {{$cropType!=='p'?'border-blue-600 text-blue-600':'border-transparent text-gray-500 hover:text-gray-700'}}">
                📊 Timeline
                </a>
                
                <a href="?crop=p&activity={{$activityFilter}}&tab={{ request('tab','table') }}"
                class="py-2 px-4 border-b-2 font-medium text-sm {{$cropType==='p'?'border-blue-600 text-blue-600':'border-transparent text-gray-500 hover:text-gray-700'}}">
                 🌾 Panen
                </a>
                
                <button 
                @click="activeTab = activeTab === 'map' ? 'table' : 'map'; activeTab === 'map' && $nextTick(() => initMapIfNeeded())" 
                class="py-2 px-4 border-b-2 font-medium text-sm"
                x-bind:class="activeTab === 'map' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
                🗺️ Tampilan Map
                </button>
            
                
<div class="ml-auto flex items-center gap-3">
    <button type="button"
    @click="window.location.href='{{ url()->current() }}?activity={{ $activityFilter }}&fill={{ $fillFilter ?? 'all' }}&export=excel&tab=' + activeTab + '{{ $cropType === 'p' ? '&crop=p' : '' }}'"
    class="py-2 px-4 bg-green-600 hover:bg-green-700 text-white rounded font-medium text-sm flex items-center gap-2">
    📊 Export Excel
    </button>

    <button type="button"
    onclick="openPanenEfisiensiModal()"
    class="py-2 px-4 bg-orange-500 hover:bg-orange-600 text-white rounded font-medium text-sm flex items-center gap-2">
    🌾 Proporsi Panen
    </button>
  

    <label class="text-sm font-medium text-gray-700">Filter Activity:</label>
    <select onchange="window.location.href='{{ url()->current() }}?activity=' + this.value + '&tab=map' + '{{ $cropType === 'p' ? '&crop=p' : '' }}'"

        class="py-1 px-3 rounded border border-gray-300 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
        <option value="all" {{$activityFilter==='all'?'selected':''}}>📋 Semua Activity</option>
        @foreach($activityMap as $code => $label)
            <option value="{{$code}}" {{$activityFilter===$code?'selected':''}}>
                {{$code}} - {{$label}}
            </option>
        @endforeach
    </select>
    
    {{-- Display count --}}
    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
        {{count($plotHeaders)}} plot
    </span>
</div>
            </nav>
        </div>
        
        <div x-show="activeTab==='table'" x-transition>
            <div style="height: calc(100vh - 220px); overflow: auto;">
                <table>
                    <thead>
                        <tr>
                            <th class="sticky-v sticky-h blok" style="left:0;" rowspan="2">Blok</th>
                            <th class="sticky-v sticky-h" style="left:60px;" rowspan="2">Plot</th>
                            <th class="sticky-v" rowspan="2">Saldo<br><small>HA</small></th>
                            
                            {{-- DINAMIS: Loop dari $activityMap --}}
                            @foreach($activityMap as $activitycode => $label)
                                @php
                                    $isGrouped = isset($activityGrouping[$activitycode]);
                                    $isRcUnique = str_starts_with($activitycode, '3.2');
                                    $headerBg = $isRcUnique ? '#1d4ed8' : '#166534';
                                @endphp
                                <th class="sticky-v" colspan="3" style="text-align:center; background: {{ $headerBg }}; color: white;">
                                    <span style="{{ $isGrouped ? 'text-decoration: underline; text-decoration-color: #fbbf24; text-decoration-thickness: 2px; text-underline-offset: 3px;' : '' }}">
                                        {{ $activitycode }}
                                    </span>
                                    @if($isGrouped)
                                        <span style="color:#fbbf24;font-size:12px;" title="Gabungan dari {{ implode(' + ', $activityGrouping[$activitycode]) }}"></span>
                                    @endif
                                    <br>{{ $label }}<br>
                                    <small style="font-weight:normal;">HA / % / Tanggal</small>
                                </th>
                            @endforeach
                            
                            {{-- Kolom Terakhir --}}
                            <th class="sticky-v" rowspan="2">Realisasi<br>{{ $cropType === 'p' ? 'Panen' : 'Tanam' }}<br><small>HA</small></th>
                            @if($cropType === 'p')
                            <th class="sticky-v" rowspan="2">Est.<br>Ton</th>
                            <th class="sticky-v" rowspan="2">Real.<br>Ton</th>
                            <th class="sticky-v" rowspan="2">%<br>Ton</th>
                            <th class="sticky-v" rowspan="2">Rit</th>
                            @else
                            <th class="sticky-v" rowspan="2">%</th>
                            @endif
                        </tr>
                    </thead>
                    
                    <tbody>
                        @php
                            $blokPlots = $plotHeaders->groupBy(fn($item)=>substr($item->plot,0,1));
                        @endphp
                        
                        {{-- BARIS TOTAL SUMMARY --}}
                        <tr class="total-row">
                            <td class="sticky-v sticky-h blok" style="left:0;">TOTAL</td>
                            <td class="sticky-v sticky-h" style="left:60px;">ALL</td>
                            <td class="sticky-h" style="text-align:right; left:120px;" >{{ number_format($plotHeaders->sum('batcharea'), 2) }}</td>
                            
                            @php
                                $grandTotalRealisasi = 0;
                            @endphp
                            
                            @foreach($activityMap as $activitycode => $label)
                            @php 
                                $totalActivity = 0;
                                $totalPercentage = 0;
                                $plotCount = 0;
                                $allDates = [];
                                
                                foreach($activityData as $plot => $activities) {
                                    if($act = $activities->get($activitycode)) {
                                        $totalActivity += $act->total_luas;
                                        $totalPercentage += ($act->avg_percentage ?? 0);
                                        $plotCount++;
                                        if($act->tanggal_terbaru) {
                                            $allDates[] = $act->tanggal_terbaru;
                                        }
                                    }
                                }
                                
                                if ($activitycode !== '4.2.2') $grandTotalRealisasi += $totalActivity;
                                $latestDate = !empty($allDates) ? max($allDates) : null;
                                
                                // Calculate average percentage for total
                                $avgPercentage = $plotCount > 0 ? $totalPercentage / $plotCount : 0;
                                $percentageColor = $avgPercentage >= 100 ? '#22c55e' : ($avgPercentage > 0 ? '#dc2626' : '#6b7280');
                            @endphp
                        
                                <td style="text-align:right;">{{ $totalActivity > 0 ? number_format($totalActivity, 2) : '-' }}</td>
                                <td style="text-align:right; font-weight:bold; color: {{ $percentageColor }};">
                                    {{ $avgPercentage > 0 ? number_format($avgPercentage, 2) . '%' : '-' }}
                                </td>
                                <td style="text-align:center;font-size:11px;">
                                    {{ $latestDate ? \Carbon\Carbon::parse($latestDate)->format('d M y') : '-' }}
                                </td>
                            @endforeach
                            
                            {{-- Total Realisasi Panen/Tanam --}}
                            <td style="text-align:right;">
                                {{ number_format($grandTotalRealisasi, 2) }}
                            </td>

                            @if($cropType === 'p')
                            @php
                                $grandEstTon  = collect($plotActivityDetails)->sum('estimasi_ton');
                                $grandRealTon = collect($plotActivityDetails)->sum('total_netto_ton');
                                $grandPctTon  = $grandEstTon > 0 ? ($grandRealTon / $grandEstTon) * 100 : 0;
                                $grandRit     = collect($plotActivityDetails)->sum('total_rit');
                            @endphp
                            <td style="text-align:right;">{{ number_format($grandEstTon, 2) }}</td>
                            <td style="text-align:right;">{{ number_format($grandRealTon, 2) }}</td>
                            <td style="text-align:right; font-weight:bold; color:{{ $grandPctTon >= 100 ? '#22c55e' : ($grandPctTon > 0 ? '#dc2626' : '#6b7280') }};">
                                {{ $grandPctTon > 0 ? number_format($grandPctTon, 2).'%' : '-' }}
                            </td>
                            <td style="text-align:right;">{{ $grandRit }}</td>
                            @else
                            {{-- Total Persentase --}}
                            <td style="text-align:right;">
                                @php
                                    $totalSaldo = $plotHeaders->sum('batcharea');
                                    $persenTotal = $totalSaldo > 0 ? ($grandTotalRealisasi / $totalSaldo) * 100 : 0;
                                @endphp
                                {{ number_format($persenTotal, 2) }}%
                            </td>
                            @endif
                        </tr>
                        
                        

                        {{-- DATA PER PLOT --}}
                        @foreach($blokPlots as $blok => $plots)
                            @foreach($plots as $index => $plot)
                                @php
                                    $status = strtoupper($plot->lifecyclestatus ?? '');
                                    $rowBg = ($status === 'PC')
                                        ? '#dcfce7'
                                        : (str_starts_with($status, 'RC') ? '#dbeafe' : '#ffffff');
                                @endphp

                                <tr style="background: {{ $rowBg }}; cursor:pointer;"
                                    onclick="openPlotModal('{{ $plot->plot }}')">

                                    @if($index === 0)
                                        <td rowspan="{{ count($plots) }}" class="sticky-h blok" style="left:0;">
                                            {{ $blok }}
                                        </td>
                                    @endif

                                    <td class="sticky-h" style="left:60px;">
                                        {{ $plot->plot }} ({{ $status }})
                                    </td>

                                    <td class="sticky-h" style="left:120px; text-align:right;">
                                        {{ $plot->batcharea ? number_format($plot->batcharea, 2) : '-' }}
                                    </td>

                                    @php
                                        $totalRealisasiPlot = 0;
                                    @endphp

                                    @foreach($activityMap as $activitycode => $label)
                                        @php
                                            $activity = $activityData->get($plot->plot)?->get($activitycode);
                                            $value = $activity->total_luas ?? 0;
                                            $percentage = $activity->avg_percentage ?? 0;
                                            $tanggal = $activity->tanggal_terbaru ?? null;
                                            if ($activitycode !== '4.2.2') $totalRealisasiPlot += $value;

                                            $cellBg = $isRcUnique ? '#eff6ff' : '#f0fdf4';
                                            $percentageColor = $percentage >= 100 ? '#22c55e' : ($percentage > 0 ? '#dc2626' : '#6b7280');
                                        @endphp

                                        <td style="text-align:right; background: {{ $cellBg }};">
                                            {{ $value > 0 ? number_format($value, 2) : '-' }}
                                        </td>

                                        <td style="text-align:right; font-weight:600; color: {{ $percentageColor }}; background: {{ $cellBg }};">
                                            {{ $value > 0 ? number_format($percentage, 2) . '%' : '-' }}
                                        </td>

                                        <td style="text-align:center; font-size:11px; background: {{ $cellBg }};">
                                            {{ $tanggal ? \Carbon\Carbon::parse($tanggal)->format('d M y') : '-' }}
                                        </td>
                                    @endforeach

                                    <td style="text-align:right;">
                                        {{ $totalRealisasiPlot > 0 ? number_format($totalRealisasiPlot, 2) : '-' }}
                                    </td>

                                    @if($cropType === 'p')
                                        @php
                                            $pDetail  = $plotActivityDetails[$plot->plot] ?? [];
                                            $estTon   = (float)($pDetail['estimasi_ton'] ?? 0);
                                            $realTon  = (float)($pDetail['total_netto_ton'] ?? 0);
                                            $pctTon   = $estTon > 0 ? ($realTon / $estTon) * 100 : 0;
                                            $rit      = (int)($pDetail['total_rit'] ?? 0);
                                            $tonColor = $pctTon >= 100 ? '#22c55e' : ($pctTon > 0 ? '#dc2626' : '#6b7280');
                                        @endphp
                                        <td style="text-align:right;">{{ $estTon > 0 ? number_format($estTon, 2) : '-' }}</td>
                                        <td style="text-align:right;">{{ $realTon > 0 ? number_format($realTon, 2) : '-' }}</td>
                                        <td style="text-align:right; font-weight:600; color:{{ $tonColor }};">
                                            {{ $pctTon > 0 ? number_format($pctTon, 2).'%' : '-' }}
                                        </td>
                                        <td style="text-align:right;">{{ $rit > 0 ? $rit : '-' }}</td>
                                    @else
                                        <td style="text-align:right;">
                                            @php
                                                $stagePct = $plotActivityDetails[$plot->plot]['stage_percentage'] ?? 0;
                                            @endphp
                                            {{ number_format($stagePct, 2) }}%
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @endforeach



                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===== MODAL DETAIL PLOT ===== --}}
        <div id="plot-modal-overlay"
             onclick="closePlotModal(event)"
             style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:9999; padding:16px; align-items:flex-start; justify-content:center;">
            <div id="plot-modal-box"
                 style="background:white; border-radius:10px; width:99vw; max-width:1800px; max-height:95vh; display:flex; flex-direction:column; box-shadow:0 24px 64px rgba(0,0,0,.35); overflow:hidden;">

                {{-- Modal Header --}}
                <div style="background:#166534; color:white; padding:12px 18px; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
                    <div id="plot-modal-title" style="font-size:15px; font-weight:700; letter-spacing:.3px;">Detail Plot</div>
                    <button onclick="closePlotModal(null)"
                            style="background:rgba(255,255,255,.2); border:none; color:white; border-radius:5px; width:28px; height:28px; cursor:pointer; font-size:16px; line-height:1; transition:background .15s;"
                            onmouseover="this.style.background='rgba(255,255,255,.35)'"
                            onmouseout="this.style.background='rgba(255,255,255,.2)'">✕</button>
                </div>

                {{-- Modal Body scrollable --}}
                <div id="plot-modal-body" style="padding:16px; font-size:12px; color:#374151; overflow-y:auto; flex:1;">
                    <div style="text-align:center; padding:40px; color:#6b7280;">Loading...</div>
                </div>
            </div>
        </div>
        {{-- ===== /MODAL ===== --}}

        {{-- ===== MODAL PROPORSI PANEN ===== --}}
        <div id="panen-efisiensi-overlay"
             onclick="closePanenEfisiensiModal(event)"
             style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:9999; padding:16px; align-items:flex-start; justify-content:center;">
            <div id="panen-efisiensi-box"
                 style="background:white; border-radius:10px; width:99vw; max-width:1400px; max-height:95vh; display:flex; flex-direction:column; box-shadow:0 24px 64px rgba(0,0,0,.35); overflow:hidden; animation:modalIn .2s ease;">

                <div style="background:#92400e; color:white; padding:12px 18px; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
                    <div style="font-size:15px; font-weight:700; letter-spacing:.3px;">🌾 Proporsi Panen — Status Kesiapan & Efisiensi</div>
                    <button onclick="closePanenEfisiensiModal(null)"
                            style="background:rgba(255,255,255,.2); border:none; color:white; border-radius:5px; width:28px; height:28px; cursor:pointer; font-size:16px; line-height:1;"
                            onmouseover="this.style.background='rgba(255,255,255,.35)'"
                            onmouseout="this.style.background='rgba(255,255,255,.2)'">✕</button>
                </div>

                <div id="panen-efisiensi-body" style="padding:16px; font-size:12px; color:#374151; overflow-y:auto; flex:1;">
                    <div style="text-align:center; padding:40px; color:#6b7280;">Memuat data...</div>
                </div>
            </div>
        </div>
        {{-- ===== /MODAL PROPORSI PANEN ===== --}}

        <div x-show="activeTab==='map'" x-transition class="bg-white shadow-md rounded-lg p-6">
            <!-- MAP SECTION (EXISTING) -->
            <h3 class="text-xl font-bold mb-4">Peta Lokasi Plot</h3>
            <div id="map" class="border border-gray-300 rounded-lg"></div>

<!-- LEGEND -->
<div class="mt-3 grid grid-cols-2 gap-2 text-xs">

  <!-- Kolom kiri: Fill -->
  <div class="space-y-1">
    <div class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Fill — status kesiapan panen</div>
    <div class="flex items-center gap-2 bg-white border rounded px-2 py-1">
      <span class="inline-block w-4 h-4 rounded-full flex-shrink-0" style="background:#fef3c7;border:1px solid #d1d5db;"></span>
      <span>Cream — belum ada activity</span>
    </div>
    <div class="flex items-center gap-2 bg-white border rounded px-2 py-1">
      <span class="inline-block w-4 h-4 rounded-full flex-shrink-0" style="background:#86efac;border:1px solid #d1d5db;"></span>
      <span>Hijau muda — activity sedang berjalan</span>
    </div>
    <div class="flex items-center gap-2 bg-white border rounded px-2 py-1">
      <span class="inline-block w-4 h-4 rounded-full flex-shrink-0" style="background:#0f766e;border:1px solid #d1d5db;"></span>
      <span>Hijau tua — semua activity selesai</span>
    </div>
    <div class="flex items-center gap-2 bg-white border rounded px-2 py-1">
      <span class="inline-block w-4 h-4 rounded-full flex-shrink-0" style="background:#fb923c;border:1px solid #d1d5db;"></span>
      <span>Orange — <strong>siap panen</strong> (umur ≥ 9 bln, belum ZPK)</span>
    </div>
    <div class="flex items-center gap-2 bg-white border rounded px-2 py-1">
      <span class="inline-block w-4 h-4 rounded-full flex-shrink-0" style="background:#3b82f6;border:1px solid #d1d5db;"></span>
      <span>Biru — siap panen (ZPK 25–35 hari)</span>
    </div>
    <div class="flex items-center gap-2 bg-white border rounded px-2 py-1">
      <span class="inline-block w-4 h-4 rounded-full flex-shrink-0" style="background:#000;opacity:.30;border:1px solid #d1d5db;"></span>
      <span>Hitam redup — tidak memenuhi filter aktif</span>
    </div>
  </div>

  <!-- Kolom kanan: Ring -->
  <div class="space-y-1">
    <div class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Ring — peringatan tindakan</div>
    <div class="flex items-center gap-2 bg-white border rounded px-2 py-1">
      <span class="inline-block w-4 h-4 rounded-full flex-shrink-0" style="background:#e5e7eb;border:3px solid #facc15;"></span>
      <span>Ring kuning — ZPK &lt; 25 hari (masih tunggu)</span>
    </div>
    <div class="flex items-center gap-2 bg-white border rounded px-2 py-1">
      <span class="inline-block w-4 h-4 rounded-full flex-shrink-0" style="background:#e5e7eb;border:3px solid #f97316;"></span>
      <span>Ring orange — ZPK &gt; 35 hari (terlambat dipanen!)</span>
    </div>
    <div class="flex items-center gap-2 bg-white border rounded px-2 py-1">
      <span class="inline-block w-4 h-4 rounded-full flex-shrink-0" style="background:#e5e7eb;border:3px solid #dc2626;"></span>
      <span>Ring merah — umur ≥ 10 bln belum panen / trash mulcher overdue</span>
    </div>
    <div class="flex items-center gap-2 bg-white border rounded px-2 py-1">
      <span class="inline-block w-4 h-4 rounded-full flex-shrink-0" style="background:#e5e7eb;border:2px solid #374151;"></span>
      <span>Ring abu — normal</span>
    </div>
  </div>

</div>



            <!-- DATATABLE SECTION (BARU) -->
            <div class="mt-8 border-t-2 border-gray-300 pt-8">
                <h3 class="text-xl font-bold mb-4">📋 Detail Activities & LKH per Plot</h3>
                
                <!-- SECTION 1: Plot yang Ada di Map -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold mb-2 text-green-700">Plot yang Tampil di Map ({{ count($plotHeadersForMap) }} plot)</h4>
                    <div style="overflow-x: auto;">
                        <table class="w-full text-xs border-collapse border">
                            <thead class="bg-green-700 text-white">
                                <tr>
                                    <th class="border p-2">Plot</th>
                                    <th class="border p-2">Status</th>
                                    <th class="border p-2">Umur</th>
                                    <th class="border p-2">Luas RKH</th>
                                    <th class="border p-2">Total Hasil</th>
                                    <th class="border p-2">Progress</th>
                                    <th class="border p-2">Activities</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // Hanya loop plot yang ada di map
                                    $plotsInMap = collect($plotHeadersForMap)->pluck('plot')->toArray();
                                @endphp
                                
                                @foreach($plotsInMap as $plotCode)
                                    @php
                                        $detail = $plotActivityDetails[$plotCode] ?? null;
                                        if (!$detail) continue; // Skip kalau tidak ada data
                                        
                                        $umurText = ($detail['umur_bulan'] ?? 0) >= 0
                                            ? (($detail['umur_bulan'] ?? 0) . ' bln / ' . ($detail['umur_hari'] ?? 0) . ' hr')
                                            : '-';
                                        $avgPct = $detail['avg_percentage'] ?? 0;
                                        $pctColor = $avgPct >= 100 ? 'text-green-600' : ($avgPct > 0 ? 'text-orange-600' : 'text-gray-500');
                                    @endphp
                                    
                                    <tr class="hover:bg-green-50">
                                        <td class="border p-2 font-bold">{{ $plotCode }}</td>
                                        <td class="border p-2">
                                            <span class="px-2 py-1 rounded text-xs">
                                                {{ $detail['lifecyclestatus'] ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="border p-2">{{ $umurText }}</td>
                                        <td class="border p-2 text-right">{{ number_format($detail['luas_rkh'] ?? 0, 2) }} HA</td>
                                        <td class="border p-2 text-right">{{ number_format($detail['total_luas_hasil'] ?? 0, 2) }} HA</td>
                                        <td class="border p-2 text-right font-bold {{ $pctColor }}">
                                            {{ number_format($avgPct, 1) }}%
                                        </td>
                                        <td class="border p-2">
                                            @if(count($detail['activities'] ?? []) > 0)
                                                <details class="text-xs">
                                                    <summary class="cursor-pointer text-blue-600 hover:text-blue-800">
                                                        {{ count($detail['activities']) }} activities
                                                    </summary>
                                                    <div class="mt-2 space-y-2 pl-2">
                                                        @foreach($detail['activities'] as $act)
                                                        @php
                                                        $isOk = ((float)($act['percentage'] ?? 0) >= 100);
                                                        $lkhOk = collect($act['lkh_details'] ?? [])->filter(fn($x)=>($x['luas_hasil'] ?? 0) > 0)->count();
                                                        $lkhTotal = (int)($act['lkh_total'] ?? count($act['lkh_details'] ?? []));
                                                        $badge = $isOk ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100';
                                                        @endphp
                                                            <div class="border-b pb-2">
                                                                <div class="flex justify-between gap-2 mb-1">
                                                                    <span class="text-gray-700 font-semibold">{{ $act['code'] }} - {{ $act['label'] }}</span>
                                                                    <span class="px-2 py-0.5 rounded text-xs font-bold {{ $badge }}">
                                                                        {{ $isOk ? '✔️ OK' : '✖️ NOT OK' }}
                                                                    </span>
                                                                </div>
                                                                <div class="text-gray-500 text-xs mb-1">
                                                                    Luas: <strong>{{ number_format($act['luas_hasil'], 2) }} HA</strong> / {{ number_format($detail['luas_rkh'], 2) }} HA
                                                                    @if($act['tanggal'])
                                                                        <span class="ml-2">📅 {{ \Carbon\Carbon::parse($act['tanggal'])->format('d/m/Y') }}</span>
                                                                    @endif
                                                                </div>
                                                                
                                                                {{-- ✅ DETAIL LKH --}}
                                                                @if(count($act['lkh_details'] ?? []) > 0)
                                                                    <div class="pl-3 mt-1 space-y-0.5 bg-gray-50 p-2 rounded">
                                                                        <div class="text-gray-600 font-semibold text-xs mb-1">Detail LKH:</div>
                                                                        @foreach($act['lkh_details'] as $lkh)
                                                                            <div class="flex justify-between text-xs text-gray-600">
                                                                                <span>📄 {{ $lkh['lkhno'] }}</span>
                                                                                <span class="font-semibold">{{ number_format($lkh['luas_hasil'], 2) }} HA</span>
                                                                                <span class="text-gray-400">{{ \Carbon\Carbon::parse($lkh['tanggal'])->format('d/m/y') }}</span>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </details>
                                            @else
                                                <span class="text-gray-400 italic">Tidak ada</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- SECTION 2: Plot yang Tidak di Map -->
                @php
                    // Ambil plot yang ada di plotHeaders tapi tidak ada di plotHeadersForMap
                    $plotsInMap = collect($plotHeadersForMap)->pluck('plot')->toArray();
                    $plotsNotInMap = $plotHeaders->whereNotIn('plot', $plotsInMap);
                    $plotsNotInMapGrouped = $plotsNotInMap->groupBy(fn($item) => substr($item->plot, 0, 1));
                @endphp
                
                @if($plotsNotInMap->count() > 0)
                    <div class="mt-6 bg-gray-50 p-4 rounded border border-gray-300">
                        <h4 class="text-sm font-semibold mb-3 text-gray-700">
                            Plot Tidak Tampil di Map ({{ $plotsNotInMap->count() }} plot)
                        </h4>
                        <div class="space-y-2">
                            @foreach($plotsNotInMapGrouped as $blok => $plots)
                                <div class="text-xs">
                                    <span class="font-bold text-gray-600">Blok {{ $blok }}:</span>
                                    <span class="text-gray-500">
                                        {{ $plots->pluck('plot')->implode(', ') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>


        </div>
        
  
    </div>
  
    <script>
    const plotHeaders = @json($plotHeadersForMap ?? []);
    const plotData = @json($plotData ?? []);
    const plotActivityDetails = @json($plotActivityDetails ?? []);
    const activityFilter = @json($activityFilter ?? 'all');
        
    let map, markers = [], polygons = [];

    // 🔑 Tentukan warna plot berdasarkan umur, ZPK, dan panen
function getPlotColor(d) {
  // 0) kalau tidak ada activity sama sekali → cream
  const hasAnyActivity = Array.isArray(d.activities) && d.activities.length > 0;
  if (!hasAnyActivity) return '#fef3c7';

  // cari ZPK (4.2.2) dan hitung hari sejak ZPK
  let daysSinceZpk = null;
  const zpkAct = d.activities.find(a => a.code === '4.2.2');
  if (zpkAct && zpkAct.tanggal) {
    const zpkDate = new Date(zpkAct.tanggal);
    const today   = new Date();
    daysSinceZpk  = (today - zpkDate) / (1000 * 60 * 60 * 24);
  }

  // ✅ 1) siap panen → fill biru (ada activity + ZPK 25-35 hari)
  if (daysSinceZpk !== null && daysSinceZpk > 25 && daysSinceZpk < 35) {
    return '#3b82f6'; // biru (tailwind blue-500)
  }

  // ✅ 2) belum ZPK tapi umur ≥ 9 bulan → fill orange (siap panen belum ZPK)
  const umurBulan = d.umur_bulan ?? 0;
  if (daysSinceZpk === null && umurBulan >= 9) {
    return '#fb923c'; // orange-400
  }

  // 3) kalau SEMUA activity sudah 100% → hijau tua
  const allDone = d.activities.every(a => (parseFloat(a.percentage || 0) >= 100));
  if (allDone) return '#0f766e';

  // 4) selain itu → hijau muda
  return '#86efac';
}



function getRingColor(d) {
  const umurHari  = d.umur_hari || 0;
  const umurBulan = (d.umur_bulan ?? 0);

  // cari activity ZPK (4.2.2)
  let hasZpk = false;
  let daysSinceZpk = null;

  if (Array.isArray(d.activities)) {
    const zpkAct = d.activities.find(a => a.code === '4.2.2');
    if (zpkAct && zpkAct.tanggal) {
      hasZpk = true;
      const zpkDate = new Date(zpkAct.tanggal);
      const today   = new Date();
      const diffMs  = today - zpkDate;
      daysSinceZpk  = diffMs / (1000 * 60 * 60 * 24);
    }
  }

  // Ring merah: umur ≥ 10 bulan & belum panen sama sekali
  if (!hasZpk && umurBulan >= 10 && !d.is_panen) return '#dc2626';

  // Ring post-ZPK timing
  if (hasZpk && daysSinceZpk > 35) return '#f97316'; // ZPK terlambat dipanen
  if (hasZpk && daysSinceZpk < 25) return '#facc15'; // ZPK masih tunggu

  // Ring merah: trash mulcher overdue (> 14 hari setelah panen selesai)
  if (d.last_panen_lkh_date && !d.last_trash_mulcher_date) {
    const panenDone = new Date(d.last_panen_lkh_date);
    const today = new Date();
    const daysSincePanen = (today - panenDone) / (1000 * 60 * 60 * 24);
    if (daysSincePanen > 14) return '#dc2626';
  }

  return '#374151'; // default ring abu gelap
}

        
    function initMapIfNeeded() {
        if (window.mapInitialized) return;
            
        map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: parseFloat(plotHeaders[0]?.centerlatitude || -4.12893), lng: parseFloat(plotHeaders[0]?.centerlongitude || 105.2971) },
            zoom: 14.5
        });
            
        createMapContent();
        window.mapInitialized = true;
    }

        let activeInfoWindow = null;
        function createMapContent() {
            // Markers
            plotHeaders.forEach(h => {
                const d = plotActivityDetails[h.plot] || {
                    marker_color:'black', 
                    avg_percentage:0, 
                    activities:[], 
                    luas_rkh:0, 
                    total_luas_hasil:0,
                    lifecyclestatus: '-',
                    umur_hari: 0,
                    is_panen: 0,
                    tanggal_panen_terakhir: null
                };

                // 🔑 warna marker berdasarkan umur, ZPK, panen
                const baseColor = getPlotColor(d);
                ringColor = getRingColor(d);
                const isMatch = (activityFilter === 'all') ? true : (d.is_match === 1 || d.is_match === true);
                const color = isMatch ? baseColor : '#000000';
                const opacity = isMatch ? 0.95 : 0.75;

                const marker = new google.maps.Marker({
                position: {lat: parseFloat(h.centerlatitude), lng: parseFloat(h.centerlongitude)},
                map: map,
                // icon: {
                //     path: google.maps.SymbolPath.CIRCLE,
                //     scale: 25,
                //     fillColor: color,
                //     fillOpacity: opacity,
                //     strokeColor: '#ffffff',
                //     strokeWeight: 3
                // },
                icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 20,
                fillOpacity: 0,
                strokeOpacity: 0
                },
                label: {text: h.plot, color: '#000', fontSize: '11px', fontWeight: 'bold'}
                });
                //


                let umurText = '-';
                if (d.umur_bulan !== undefined && d.umur_bulan !== null) {
                    umurText = `${d.umur_bulan} bulan`;
                }


                let acts = '';
        if (d.activities?.length > 0) {
            acts = `<div style="margin-top:10px;border-top:1px solid #ddd;padding-top:10px"><strong>Activities (${d.activities.length}):</strong><div style="max-height:200px;overflow-y:auto;margin-top:5px">`;
            d.activities.forEach((a,i) => {
                const p = parseFloat(a.percentage).toFixed(2);
                const c = p >= 100 ? '#22c55e' : p > 0 ? '#dc2626' : '#6b7280';
                
                acts += `<div style="margin:5px 0;padding:8px;background:#f9fafb;border-radius:4px;border-left:3px solid ${c}">
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                        <div style="font-weight:600;color:#374151;font-size:11px">${i+1}. ${a.code} - ${a.label}</div>
                        <div style="font-weight:700;color:${c};font-size:12px">${p}%</div>
                    </div>
                    <div style="color:#6b7280;font-size:10px;margin-bottom:4px">
                        Luas: <strong>${parseFloat(a.luas_hasil).toFixed(2)} HA</strong> / ${parseFloat(d.luas_rkh).toFixed(2)} HA
                        ${a.lkh_details && a.lkh_details.length > 0 ? ` <span style="color:#9ca3af">(${a.lkh_details.length} LKH)</span>` : ''}
                    </div>`;
                
                // ✅ Detail LKH
                if (a.lkh_details && a.lkh_details.length > 0) {
                    acts += `<div style="margin-top:6px;padding:6px;background:#fff;border-radius:3px;border:1px solid #e5e7eb">
                        <div style="font-size:9px;color:#6b7280;font-weight:600;margin-bottom:3px">Detail LKH:</div>`;
                    
                    a.lkh_details.forEach((lkh) => {
                        acts += `<div style="font-size:9px;color:#374151;padding:2px 0;display:flex;justify-content:space-between;gap:8px">
                            <span style="flex:1">📄 ${lkh.lkhno}</span>
                            <span style="font-weight:600;white-space:nowrap">${parseFloat(lkh.luas_hasil).toFixed(2)} HA</span>
                            <span style="color:#9ca3af;white-space:nowrap">${new Date(lkh.tanggal).toLocaleDateString('id-ID', {day:'2-digit',month:'short'})}</span>
                        </div>`;
                    });
                    
                    acts += `</div>`;
                }
                
                acts += `<div style="background:#e5e7eb;height:6px;border-radius:3px;overflow:hidden;margin-top:4px">
                        <div style="background:${c};height:100%;width:${Math.min(p,100)}%"></div>
                    </div>
                    ${a.tanggal ? `<div style="color:#9ca3af;font-size:9px;margin-top:3px">📅 Terakhir: ${new Date(a.tanggal).toLocaleDateString('id-ID')}</div>` : ''}
                </div>`;
            });
            acts += '</div></div>';
        } else {
            acts = '<div style="margin-top:10px;padding:8px;background:#f3f4f6;border-radius:4px;color:#6b7280;font-size:11px;text-align:center">Tidak ada data</div>';
        }
                
                const info = new google.maps.InfoWindow({
                    content: `<div style="padding:12px;min-width:280px;max-width:350px">
                        <h3 style="margin:0 0 10px 0;color:#2c3e50;font-size:15px;font-weight:bold;border-bottom:2px solid #e5e7eb;padding-bottom:6px">📍 Plot ${h.plot}</h3>
                        <div style="font-size:11px;color:#6b7280;background:#f9fafb;padding:8px;border-radius:4px;margin-bottom:8px">
                            <div><strong>Luas RKH:</strong> ${parseFloat(d.luas_rkh).toFixed(2)} HA</div>
                            <div><strong>Total Hasil:</strong> ${parseFloat(d.total_luas_hasil).toFixed(2)} HA</div>
                            <div style="margin-top:6px;padding-top:6px;border-top:1px solid #e5e7eb">
                                <strong>Status:</strong> 
                                <span style="background:#e0f2fe;color:#0369a1;padding:2px 6px;border-radius:3px;font-weight:600">${d.lifecyclestatus}</span>
                            </div>
                            <div><strong>Umur:</strong> <span style="color:#059669;font-weight:600">${umurText}</span></div>
                        </div>
                        <div style="background:linear-gradient(135deg,${color}22,${color}11);padding:10px;border-radius:6px;border:2px solid ${color};margin-bottom:8px;text-align:center">
                            <div style="color:#6b7280;font-size:10px;font-weight:600;text-transform:uppercase;margin-bottom:4px">Progress</div>
                            <div style="color:#000;font-weight:700;font-size:24px">${parseFloat(d.stage_percentage ?? 0).toFixed(1)}%</div>
                        </div>
                        ${acts}
                    </div>`
                });
                
                marker.addListener('click', () => {
                if (activeInfoWindow) activeInfoWindow.close();  // tutup yang lama kalau ada
                info.open(map, marker);                           // buka yang baru
                activeInfoWindow = info;                          // simpan yang aktif
                });

                markers.push(marker);
            });
            
            // Polygons
            plotHeaders.forEach(h => {
                const pts = plotData.filter(p => p.plot === h.plot);
                if (pts.length < 3) return;

                const d = plotActivityDetails[h.plot] || {
                    avg_percentage:0, 
                    activities:[], 
                    luas_rkh:0, 
                    total_luas_hasil:0,
                    lifecyclestatus: '-',
                    umur_hari: 0,
                    is_panen: 0
                };

                const baseColor = getPlotColor(d);
                const zpkColor  = getRingColor(d);
                const isMatch = (activityFilter === 'all') ? true : (d.is_match === 1 || d.is_match === true);
                const color = isMatch ? baseColor : '#000000';

                const hasZpkWarning = (zpkColor !== '#ffffff');
                // ✅ fill hanya status (cream/hijau muda/hijau tua)
                const fillColor = !isMatch ? '#000000' : baseColor;
                // ✅ stroke hanya ring warning (orange/kuning/merah), tanpa warning pakai abu
                const strokeColor = !isMatch ? '#000000' : (hasZpkWarning ? zpkColor : '#374151');
                polygons.push(new google.maps.Polygon({
                paths: pts.map(p => ({lat: parseFloat(p.latitude), lng: parseFloat(p.longitude)})),
                strokeColor: strokeColor,
                strokeOpacity: isMatch ? 0.9 : 0.25,
                strokeWeight: 2,
                fillColor: fillColor,
                fillOpacity: isMatch ? 0.45 : 0.10,
                map: map
                }));



            });
            



        }




        



        function closePlotModal(event) {
            if (event && event.target !== document.getElementById('plot-modal-overlay')) return;
            document.getElementById('plot-modal-overlay').style.display = 'none';
            document.getElementById('plot-modal-body').innerHTML = '<div style="text-align:center;padding:40px;color:#6b7280;">Loading...</div>';
        }

        // ESC key close
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') document.getElementById('plot-modal-overlay').style.display = 'none';
        });

        async function openPlotModal(plot) {
            const overlay  = document.getElementById('plot-modal-overlay');
            const modalBox = document.getElementById('plot-modal-body');
            const title    = document.getElementById('plot-modal-title');
            const blok     = plot.charAt(0);

            title.textContent = `Detail Plot ${plot} (Blok ${blok})`;
            overlay.style.display = 'flex';
            modalBox.innerHTML = '<div style="text-align:center;padding:40px;color:#6b7280;">Loading...</div>';

            try {
                const cropParam = @json($cropType);
                const res = await fetch(`{{ route('dashboard.timeline-plot.detail') }}?plot=${encodeURIComponent(plot)}&crop=${cropParam}`);
                const data = await res.json();

                if (!data.success) {
                    modalBox.innerHTML = `<div style="padding:16px;color:red;">Gagal ambil detail plot.</div>`;
                    return;
                }

                const activeBatch      = data.batch || {};
                const batches          = Array.isArray(data.batches) ? data.batches : [];
                const activities       = Array.isArray(data.activities) ? data.activities : [];
                const activityMapJs    = @json($activityMap);
                const activeBatchNo    = data.active_batchno || null;
                const suratJalanList   = Array.isArray(data.surat_jalan) ? data.surat_jalan : [];
                const estimasiTonBatch = data.estimasi_ton_batch || {};
                const isPanen          = @json($cropType) === 'p';

                // group aktivitas per batchno -> activitycode
                const groupedByBatch = {};
                activities.forEach(r => {
                    const bno  = r.batchno || '-';
                    const code = r.activitycode || '-';
                    if (!groupedByBatch[bno]) groupedByBatch[bno] = {};
                    if (!groupedByBatch[bno][code]) groupedByBatch[bno][code] = { rows: [], total: 0 };
                    const luas = Number(r.luashasil || 0);
                    groupedByBatch[bno][code].rows.push({ lkhno: r.lkhno || '-', lkhdate: r.lkhdate || '-', luashasil: luas });
                    groupedByBatch[bno][code].total += luas;
                });

                // group suratjalan per tanggal (untuk panen mode)
                const totalSJNetto = suratJalanList.reduce((s, r) => s + Number(r.netto || 0), 0);
                const totalSJRit   = suratJalanList.length;
                const sudahTimbang = suratJalanList.filter(r => r.sudah_timbang).length;

                // ===== TIMELINE ALERT CHECKS =====
                const d = plotActivityDetails[plot] || {};
                const today = new Date();
                const alerts = [];

                // Helper: days diff from date string to today
                const daysSince = (dateStr) => dateStr ? (today - new Date(dateStr)) / 86400000 : null;

                const umurBulan = d.umur_bulan ?? 0;
                const umurHari  = d.umur_hari ?? 0;
                const status    = (d.lifecyclestatus || '').toUpperCase();
                const isRC      = status.startsWith('RC');

                // 1) Umur ≥ 10 bulan belum panen
                if (umurBulan >= 10 && !d.is_panen) {
                    alerts.push({ level: 'red', msg: `⛔ Umur ${umurBulan} bulan (${umurHari} hari) — belum panen! Segera jadwalkan panen.` });
                }

                // 2) Harvest duration check: panen mulai dari tanggalpanen, selesai di last_panen_lkh_date
                if (d.tanggal_panen_terakhir && d.last_panen_lkh_date) {
                    const startPanen = new Date(d.tanggal_panen_terakhir);
                    const endPanen   = new Date(d.last_panen_lkh_date);
                    const panenDays  = Math.ceil((endPanen - startPanen) / 86400000) + 1;
                    const luasRkh    = d.luas_rkh ?? 0;
                    // Normal: ~3 hari/ha, max: 7 hari untuk plot kecil, max: 14 hari untuk plot besar
                    const maxDays = luasRkh > 5 ? 14 : 7;
                    if (panenDays > maxDays) {
                        alerts.push({ level: 'red', msg: `⛔ Durasi panen ${panenDays} hari (luas ${luasRkh.toFixed(1)} HA) — melebihi batas (max ~${maxDays} hari).` });
                    } else if (panenDays > 7) {
                        alerts.push({ level: 'yellow', msg: `⚠️ Durasi panen ${panenDays} hari — perhatikan kecepatan panen.` });
                    }
                }

                // 3) Trash mulcher: wajib ≤ 14 hari setelah panen selesai
                if (d.last_panen_lkh_date) {
                    const daysSincePanen = daysSince(d.last_panen_lkh_date);
                    if (!d.last_trash_mulcher_date) {
                        if (daysSincePanen > 14) {
                            alerts.push({ level: 'red', msg: `⛔ Panen selesai ${Math.floor(daysSincePanen)} hari lalu — trash mulcher BELUM dilakukan! (wajib ≤ 14 hari)` });
                        } else if (daysSincePanen > 7) {
                            alerts.push({ level: 'yellow', msg: `⚠️ Panen selesai ${Math.floor(daysSincePanen)} hari lalu — segera lakukan trash mulcher (sisa ${Math.floor(14 - daysSincePanen)} hari).` });
                        }
                    }
                }

                // 4) RC3 → perlu replanting
                if (status === 'RC3') {
                    alerts.push({ level: 'yellow', msg: `⚠️ Status RC3 — plot ini wajib replanting. Pastikan sudah dijadwalkan.` });
                }

                // 5) Jika belum RC3 tapi ada replanting activity
                const hasReplanting = (d.activities || []).some(a => a.code && a.code.startsWith('2.'));
                if (!isRC && hasReplanting) {
                    alerts.push({ level: 'blue', msg: `ℹ️ Ada activity replanting pada plot non-RC — perlu verifikasi.` });
                }

                let html = `<div>`;

                // ===== ALERT PANEL =====
                if (alerts.length > 0) {
                    html += `<div style="margin-bottom:14px;">`;
                    alerts.forEach(a => {
                        const bg  = a.level === 'red' ? '#fef2f2' : a.level === 'yellow' ? '#fefce8' : '#eff6ff';
                        const bdr = a.level === 'red' ? '#fca5a5' : a.level === 'yellow' ? '#fde047' : '#93c5fd';
                        const tx  = a.level === 'red' ? '#991b1b' : a.level === 'yellow' ? '#854d0e' : '#1e40af';
                        html += `<div style="background:${bg};border-left:4px solid ${bdr};border-radius:4px;padding:7px 12px;margin-bottom:6px;font-size:11px;color:${tx};font-weight:600;">${a.msg}</div>`;
                    });
                    html += `</div>`;
                }

                // ===== PANEN SUMMARY CARD =====
                if (isPanen && batches.length > 0) {
                    const activeBatchInfo = batches.find(b => b.batchno === activeBatchNo) || batches[0];
                    const batchArea = parseFloat(activeBatchInfo?.batcharea || 0);
                    const estBatch  = estimasiTonBatch[activeBatchNo] || {};
                    const estTon    = parseFloat(estBatch.total_estimasi_ton || 0);
                    const realLuas  = parseFloat(estBatch.total_luas_panen || 0);
                    const realTon   = totalSJNetto / 1000;
                    const pctLuas   = batchArea > 0 ? Math.min((realLuas / batchArea) * 100, 100) : 0;
                    const pctTon    = estTon > 0 ? (realTon / estTon) * 100 : 0;
                    const cLuas     = pctLuas >= 100 ? '#16a34a' : pctLuas > 0 ? '#dc2626' : '#6b7280';
                    const cTon      = pctTon  >= 100 ? '#16a34a' : pctTon  > 0 ? '#dc2626' : '#6b7280';

                    html += `
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:16px;">
                        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:10px;text-align:center;">
                            <div style="font-size:10px;color:#6b7280;font-weight:600;text-transform:uppercase;">Estimasi Luas</div>
                            <div style="font-size:18px;font-weight:700;color:#166534;">${batchArea.toFixed(2)} HA</div>
                        </div>
                        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:10px;text-align:center;">
                            <div style="font-size:10px;color:#6b7280;font-weight:600;text-transform:uppercase;">Realisasi Luas</div>
                            <div style="font-size:18px;font-weight:700;color:${cLuas};">${realLuas.toFixed(2)} HA</div>
                            <div style="font-size:11px;color:${cLuas};font-weight:600;">${pctLuas.toFixed(1)}%</div>
                        </div>
                        <div style="background:#eff6ff;border:1px solid #93c5fd;border-radius:8px;padding:10px;text-align:center;">
                            <div style="font-size:10px;color:#6b7280;font-weight:600;text-transform:uppercase;">Estimasi Tonase</div>
                            <div style="font-size:18px;font-weight:700;color:#1d4ed8;">${estTon.toFixed(2)} Ton</div>
                        </div>
                        <div style="background:#eff6ff;border:1px solid #93c5fd;border-radius:8px;padding:10px;text-align:center;">
                            <div style="font-size:10px;color:#6b7280;font-weight:600;text-transform:uppercase;">Realisasi Tonase</div>
                            <div style="font-size:18px;font-weight:700;color:${cTon};">${realTon.toFixed(2)} Ton</div>
                            <div style="font-size:11px;color:${cTon};font-weight:600;">${pctTon.toFixed(1)}%</div>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px;">
                        <div style="background:#fafafa;border:1px solid #e5e7eb;border-radius:6px;padding:8px;text-align:center;">
                            <div style="font-size:10px;color:#6b7280;text-transform:uppercase;">Total Rit</div>
                            <div style="font-size:16px;font-weight:700;">${totalSJRit}</div>
                        </div>
                        <div style="background:#fafafa;border:1px solid #e5e7eb;border-radius:6px;padding:8px;text-align:center;">
                            <div style="font-size:10px;color:#6b7280;text-transform:uppercase;">Sudah Timbang</div>
                            <div style="font-size:16px;font-weight:700;color:#16a34a;">${sudahTimbang}</div>
                        </div>
                        <div style="background:#fafafa;border:1px solid #e5e7eb;border-radius:6px;padding:8px;text-align:center;">
                            <div style="font-size:10px;color:#6b7280;text-transform:uppercase;">Pending Timbang</div>
                            <div style="font-size:16px;font-weight:700;color:#dc2626;">${totalSJRit - sudahTimbang}</div>
                        </div>
                    </div>`;

                    // Tabel SuratJalan
                    if (suratJalanList.length > 0) {
                        html += `<div style="margin-bottom:16px;">
                            <div style="font-weight:700;font-size:12px;margin-bottom:6px;color:#1d4ed8;">📦 Detail Surat Jalan & Timbangan</div>
                            <div style="overflow-x:auto;max-height:220px;overflow-y:auto;">
                            <table style="width:100%;border-collapse:collapse;font-size:10px;">
                                <thead style="position:sticky;top:0;">
                                    <tr style="background:#1d4ed8;color:white;">
                                        <th style="padding:5px 8px;border:1px solid #e5e7eb;">No. SJ</th>
                                        <th style="padding:5px 8px;border:1px solid #e5e7eb;">Tgl Angkut</th>
                                        <th style="padding:5px 8px;border:1px solid #e5e7eb;">Nopol</th>
                                        <th style="padding:5px 8px;border:1px solid #e5e7eb;">Kontraktor</th>
                                        <th style="padding:5px 8px;border:1px solid #e5e7eb;text-align:right;">Netto (kg)</th>
                                        <th style="padding:5px 8px;border:1px solid #e5e7eb;text-align:center;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${suratJalanList.map((sj, i) => {
                                        const st = sj.sudah_timbang ? '<span style="color:#16a34a;font-weight:600;">✔ Timbang</span>' : '<span style="color:#dc2626;">⏳ Pending</span>';
                                        const bg = i % 2 === 0 ? '#ffffff' : '#f9fafb';
                                        return `<tr style="background:${bg};">
                                            <td style="padding:4px 8px;border:1px solid #e5e7eb;font-weight:600;">${sj.suratjalanno}</td>
                                            <td style="padding:4px 8px;border:1px solid #e5e7eb;">${sj.tanggalangkut ? new Date(sj.tanggalangkut).toLocaleDateString('id-ID') : '-'}</td>
                                            <td style="padding:4px 8px;border:1px solid #e5e7eb;">${sj.nomorpolisi || '-'}</td>
                                            <td style="padding:4px 8px;border:1px solid #e5e7eb;">${sj.namakontraktor || '-'}</td>
                                            <td style="padding:4px 8px;border:1px solid #e5e7eb;text-align:right;font-weight:600;">${sj.netto > 0 ? Number(sj.netto).toLocaleString('id-ID') : '-'}</td>
                                            <td style="padding:4px 8px;border:1px solid #e5e7eb;text-align:center;">${st}</td>
                                        </tr>`;
                                    }).join('')}
                                </tbody>
                                <tfoot>
                                    <tr style="background:#dbeafe;font-weight:700;font-size:10px;">
                                        <td colspan="4" style="padding:5px 8px;border:1px solid #e5e7eb;text-align:right;">TOTAL NETTO</td>
                                        <td style="padding:5px 8px;border:1px solid #e5e7eb;text-align:right;color:#1d4ed8;">${totalSJNetto.toLocaleString('id-ID')} kg = ${(totalSJNetto/1000).toFixed(2)} Ton</td>
                                        <td style="padding:5px 8px;border:1px solid #e5e7eb;"></td>
                                    </tr>
                                </tfoot>
                            </table></div>
                        </div>`;
                    }
                }

                if (batches.length === 0) {
                    html += `<div style="color:#6b7280;font-style:italic;padding:12px;">Tidak ada data batch untuk plot ini.</div>`;
                } else {
                    batches.forEach(b => {
                        const isActive   = b.batchno === activeBatchNo;
                        const batchGroup = groupedByBatch[b.batchno] || {};

                        // hanya tampilkan kode yang ada di activityMapJs, urut sesuai map
                        const codesWithData = Object.keys(activityMapJs).filter(c => batchGroup[c]);

                        const totalRealisasi = codesWithData.filter(c => c !== '4.2.2').reduce((s, c) => s + (batchGroup[c]?.total || 0), 0);

                        const borderColor    = isActive ? '#86efac' : '#d1d5db';
                        const headerBg       = isActive ? '#166534' : '#9ca3af';
                        const sectionOpacity = isActive ? '1' : '0.7';

                        // panen: estimasi ton batch
                        const estBatch  = estimasiTonBatch[b.batchno] || {};
                        const estTonB   = parseFloat(estBatch.total_estimasi_ton || 0);

                        html += `
                            <div style="margin-bottom:16px;border:2px solid ${borderColor};border-radius:8px;overflow:hidden;opacity:${sectionOpacity};">
                                <div style="background:${headerBg};color:white;
                                    padding:7px 14px;display:flex;align-items:center;gap:10px;font-size:12px;flex-wrap:wrap;">
                                    <span style="font-weight:700;">${b.batchno}</span>
                                    ${isActive
                                        ? '<span style="background:#bbf7d0;color:#166534;font-size:10px;font-weight:700;padding:1px 7px;border-radius:20px;">AKTIF</span>'
                                        : '<span style="background:rgba(255,255,255,.2);font-size:10px;padding:1px 7px;border-radius:20px;">HISTORY</span>'
                                    }
                                    <span style="opacity:.85;">Status: ${b.lifecyclestatus ?? '-'}</span>
                                    <span style="opacity:.85;">Luas: ${b.batcharea ?? 0} HA</span>
                                    <span style="opacity:.85;">Date: ${b.batchdate ?? '-'}</span>
                                    ${b.tanggalpanen ? `<span style="opacity:.85;">Tgl Panen: ${b.tanggalpanen}</span>` : ''}
                                    ${isPanen && estTonB > 0 ? `<span style="background:#dbeafe;color:#1d4ed8;font-size:10px;padding:1px 7px;border-radius:20px;">Est. ${estTonB.toFixed(2)} Ton</span>` : ''}
                                </div>`;

                        if (codesWithData.length === 0) {
                            html += `<div style="padding:10px 14px;color:#9ca3af;font-style:italic;background:#f9fafb;">Tidak ada activity tercatat.</div>`;
                        } else {
                            html += `
                                <table style="width:100%;border-collapse:collapse;font-size:11px;">
                                    <thead>
                                        <tr style="background:${isActive ? '#f0fdf4' : '#f5f5f5'};">
                                            <th style="border:1px solid #e5e7eb;padding:6px 10px;text-align:left;width:90px;">Kode</th>
                                            <th style="border:1px solid #e5e7eb;padding:6px 10px;text-align:left;">Kegiatan</th>
                                            <th style="border:1px solid #e5e7eb;padding:6px 10px;text-align:right;width:90px;">Total HA</th>
                                            <th style="border:1px solid #e5e7eb;padding:6px 10px;text-align:left;">Detail LKH</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${codesWithData.map(code => {
                                            const g     = batchGroup[code];
                                            const label = activityMapJs[code] || code;
                                            const isZpk = (code === '4.2.2');
                                            const lkhDetail = g.rows.map(r =>
                                                `<span style="display:inline-block;margin-right:8px;white-space:nowrap;">${r.lkhno} <span style="color:#6b7280;">${r.lkhdate} · ${r.luashasil.toFixed(2)} HA</span></span>`
                                            ).join('');
                                            return `
                                                <tr style="background:${isZpk ? '#fef9c3' : (isActive ? '#ffffff' : '#fafafa')};">
                                                    <td style="border:1px solid #e5e7eb;padding:6px 10px;font-weight:700;color:${isZpk ? '#854d0e' : (isActive ? '#166534' : '#4b5563')};">${code}${isZpk ? ' ⚡' : ''}</td>
                                                    <td style="border:1px solid #e5e7eb;padding:6px 10px;">${label}</td>
                                                    <td style="border:1px solid #e5e7eb;padding:6px 10px;text-align:right;font-weight:700;">${g.total.toFixed(2)}</td>
                                                    <td style="border:1px solid #e5e7eb;padding:6px 10px;font-size:10px;color:#374151;">${lkhDetail}</td>
                                                </tr>`;
                                        }).join('')}
                                    </tbody>
                                    <tfoot>
                                        <tr style="background:${isActive ? '#dcfce7' : '#f3f4f6'};font-weight:700;">
                                            <td colspan="2" style="border:1px solid #e5e7eb;padding:6px 10px;text-align:right;">Total Realisasi ${isPanen ? 'Panen' : ''}</td>
                                            <td style="border:1px solid #e5e7eb;padding:6px 10px;text-align:right;color:${isActive ? '#166534' : '#374151'};">${totalRealisasi.toFixed(2)} HA</td>
                                            <td style="border:1px solid #e5e7eb;padding:6px 10px;font-size:10px;color:#6b7280;">${isPanen ? '(ZPK tidak dihitung)' : ''}</td>
                                        </tr>
                                    </tfoot>
                                </table>`;
                        }

                        html += `</div>`;
                    });
                }

                html += `</div>`;
                modalBox.innerHTML = html;

            } catch (err) {
                console.error(err);
                modalBox.innerHTML = `<div style="padding:16px;color:red;">Error: ${err.message}</div>`;
            }
        }
    



        

    // ===== PROPORSI PANEN MODAL =====
    function openPanenEfisiensiModal() {
        const overlay = document.getElementById('panen-efisiensi-overlay');
        overlay.style.display = 'flex';
        renderPanenEfisiensi();
    }

    function closePanenEfisiensiModal(e) {
        if (e && e.target !== document.getElementById('panen-efisiensi-overlay')) return;
        document.getElementById('panen-efisiensi-overlay').style.display = 'none';
    }

    function renderPanenEfisiensi() {
        const cats = { siap: [], ready: [], panen: [], proses: [] };

        Object.entries(plotActivityDetails).forEach(([plot, d]) => {
            const color = getPlotColor(d);
            const blok  = plot.charAt(0);
            const zpkAct = (d.activities || []).find(a => a.code === '4.2.2');
            const item  = {
                plot,
                blok,
                luas     : parseFloat(d.luas_rkh || 0),
                umur     : d.umur_bulan ?? 0,
                status   : d.lifecyclestatus || '-',
                zpkDate  : zpkAct?.tanggal || null,
                lastPanen: d.last_panen_lkh_date || null
            };
            if (d.is_panen)              cats.panen.push({...item, cat:'panen'});
            else if (color === '#3b82f6') cats.ready.push({...item, cat:'ready'});
            else if (color === '#fb923c') cats.siap.push({...item, cat:'siap'});
            else                          cats.proses.push({...item, cat:'proses'});
        });

        const totalSiap      = cats.siap.reduce((s,p)  => s+p.luas, 0);
        const totalReady     = cats.ready.reduce((s,p) => s+p.luas, 0);
        const totalPanen     = cats.panen.reduce((s,p) => s+p.luas, 0);
        const totalProses    = cats.proses.reduce((s,p)=> s+p.luas, 0);
        const totalAll       = totalSiap + totalReady + totalPanen + totalProses;
        const totalSiapReady = totalSiap + totalReady;

        const STORAGE_KEY = 'panen_snapshots_{{ session("companycode") ?? "default" }}';
        const snapshots   = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');

        const pctSiapReady = totalAll > 0 ? (totalSiapReady / totalAll * 100).toFixed(1) : '0.0';
        const pctPanen     = totalAll > 0 ? (totalPanen     / totalAll * 100).toFixed(1) : '0.0';

        let html = `
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(155px,1fr));gap:10px;margin-bottom:16px;">
            <div style="background:#fff7ed;border:2px solid #fb923c;border-radius:8px;padding:10px;text-align:center;">
                <div style="font-size:10px;color:#92400e;font-weight:700;text-transform:uppercase;margin-bottom:3px;">Siap Panen (belum ZPK)</div>
                <div style="font-size:22px;font-weight:800;color:#ea580c;">${totalSiap.toFixed(2)} HA</div>
                <div style="font-size:11px;color:#9a3412;">${cats.siap.length} plot</div>
            </div>
            <div style="background:#eff6ff;border:2px solid #3b82f6;border-radius:8px;padding:10px;text-align:center;">
                <div style="font-size:10px;color:#1e40af;font-weight:700;text-transform:uppercase;margin-bottom:3px;">Ready Panen (ZPK aktif)</div>
                <div style="font-size:22px;font-weight:800;color:#2563eb;">${totalReady.toFixed(2)} HA</div>
                <div style="font-size:11px;color:#1e3a8a;">${cats.ready.length} plot</div>
            </div>
            <div style="background:#fef9c3;border:2px solid #facc15;border-radius:8px;padding:10px;text-align:center;">
                <div style="font-size:10px;color:#854d0e;font-weight:700;text-transform:uppercase;margin-bottom:3px;">Total Siap + Ready</div>
                <div style="font-size:22px;font-weight:800;color:#d97706;">${totalSiapReady.toFixed(2)} HA</div>
                <div style="font-size:11px;color:#92400e;">${pctSiapReady}% dr total lahan</div>
            </div>
            <div style="background:#f0fdf4;border:2px solid #22c55e;border-radius:8px;padding:10px;text-align:center;">
                <div style="font-size:10px;color:#166534;font-weight:700;text-transform:uppercase;margin-bottom:3px;">Sudah Panen</div>
                <div style="font-size:22px;font-weight:800;color:#16a34a;">${totalPanen.toFixed(2)} HA</div>
                <div style="font-size:11px;color:#14532d;">${cats.panen.length} plot · ${pctPanen}%</div>
            </div>
            <div style="background:#f9fafb;border:1px solid #d1d5db;border-radius:8px;padding:10px;text-align:center;">
                <div style="font-size:10px;color:#6b7280;font-weight:700;text-transform:uppercase;margin-bottom:3px;">Total Lahan</div>
                <div style="font-size:22px;font-weight:800;color:#374151;">${totalAll.toFixed(2)} HA</div>
                <div style="font-size:11px;color:#6b7280;">${Object.keys(plotActivityDetails).length} plot aktif</div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

        <div>
        <div style="font-weight:700;font-size:13px;margin-bottom:8px;color:#374151;border-bottom:2px solid #e5e7eb;padding-bottom:6px;">
            📋 Rincian per Blok & Plot
            <span style="font-size:10px;font-weight:400;color:#6b7280;margin-left:6px;">Per ${new Date().toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'})}</span>
        </div>
        <div style="overflow-x:auto;max-height:500px;overflow-y:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:11px;">
            <thead style="position:sticky;top:0;z-index:2;">
                <tr style="background:#92400e;color:white;">
                    <th style="padding:6px 8px;border:1px solid #d1d5db;min-width:40px;">Blok</th>
                    <th style="padding:6px 8px;border:1px solid #d1d5db;min-width:55px;">Plot</th>
                    <th style="padding:6px 8px;border:1px solid #d1d5db;text-align:right;min-width:65px;">Luas HA</th>
                    <th style="padding:6px 8px;border:1px solid #d1d5db;text-align:center;min-width:50px;">Umur</th>
                    <th style="padding:6px 8px;border:1px solid #d1d5db;text-align:center;min-width:110px;">Kategori</th>
                </tr>
            </thead>
            <tbody>`;

        const allPlots = [...cats.siap, ...cats.ready, ...cats.panen, ...cats.proses];
        allPlots.sort((a,b) => a.plot.localeCompare(b.plot));

        const byBlok = {};
        allPlots.forEach(p => { if (!byBlok[p.blok]) byBlok[p.blok]=[]; byBlok[p.blok].push(p); });

        const catLabels = {
            siap  : { label:'🟠 Siap Panen',  bg:'#fff7ed', color:'#ea580c', border:'#fed7aa' },
            ready : { label:'🔵 Ready Panen',  bg:'#eff6ff', color:'#2563eb', border:'#bfdbfe' },
            panen : { label:'✅ Sudah Panen',  bg:'#f0fdf4', color:'#16a34a', border:'#bbf7d0' },
            proses: { label:'⬜ Dalam Proses', bg:'#fafafa', color:'#6b7280', border:'#e5e7eb' },
        };

        Object.keys(byBlok).sort().forEach(blok => {
            const plots    = byBlok[blok];
            const blokLuas = plots.reduce((s,p) => s+p.luas, 0);
            plots.forEach((p, i) => {
                const c = catLabels[p.cat];
                const zpkInfo = p.zpkDate
                    ? (() => { const days=Math.round((Date.now()-new Date(p.zpkDate))/ 86400000); return `ZPK ${days}hr`; })()
                    : '';
                html += `<tr style="background:${c.bg};">`;
                if (i === 0) {
                    html += `<td rowspan="${plots.length}" style="border:1px solid #d1d5db;padding:6px;font-weight:700;background:#0f766e;color:white;text-align:center;vertical-align:middle;">
                        ${blok}<br><span style="font-size:9px;font-weight:400;opacity:.85;">${blokLuas.toFixed(1)}</span></td>`;
                }
                html += `
                    <td style="border:1px solid #d1d5db;padding:5px 8px;font-weight:700;text-align:center;">${p.plot}</td>
                    <td style="border:1px solid #d1d5db;padding:5px 8px;text-align:right;">${p.luas.toFixed(2)}</td>
                    <td style="border:1px solid #d1d5db;padding:5px 8px;text-align:center;">${p.umur} bln</td>
                    <td style="border:1px solid ${c.border};padding:4px 8px;text-align:center;">
                        <span style="color:${c.color};font-weight:700;font-size:10px;">${c.label}</span>
                        ${zpkInfo ? `<br><span style="font-size:9px;color:#6b7280;">${zpkInfo}</span>` : ''}
                    </td>
                </tr>`;
            });
        });

        html += `</tbody>
            <tfoot><tr style="background:#fef3c7;font-weight:700;position:sticky;bottom:0;">
                <td colspan="2" style="border:1px solid #d1d5db;padding:5px 8px;text-align:right;color:#92400e;">TOTAL</td>
                <td style="border:1px solid #d1d5db;padding:5px 8px;text-align:right;color:#92400e;">${totalAll.toFixed(2)}</td>
                <td colspan="2" style="border:1px solid #d1d5db;padding:5px 8px;font-size:10px;color:#6b7280;">
                    Siap: ${totalSiap.toFixed(1)} · Ready: ${totalReady.toFixed(1)} · Panen: ${totalPanen.toFixed(1)} · Proses: ${totalProses.toFixed(1)}
                </td>
            </tr></tfoot>
        </table></div>
        </div>

        <div>
            <div style="border:2px solid #fb923c;border-radius:8px;padding:14px;margin-bottom:14px;background:#fffbf7;">
                <div style="font-weight:700;font-size:13px;color:#92400e;margin-bottom:10px;">📝 Catat Snapshot Berkala</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px;">
                    <div>
                        <label style="font-size:10px;font-weight:600;color:#6b7280;display:block;margin-bottom:2px;">Tanggal</label>
                        <input id="snap-tanggal" type="date" value="${new Date().toISOString().split('T')[0]}"
                            style="width:100%;padding:5px 8px;border:1px solid #d1d5db;border-radius:4px;font-size:12px;">
                    </div>
                    <div>
                        <label style="font-size:10px;font-weight:600;color:#6b7280;display:block;margin-bottom:2px;">Harapan Panen (HA) <span style="color:#dc2626;">*</span></label>
                        <input id="snap-harapan" type="number" step="0.01" placeholder="Target HA..."
                            style="width:100%;padding:5px 8px;border:1px solid #d1d5db;border-radius:4px;font-size:12px;">
                    </div>
                    <div>
                        <label style="font-size:10px;font-weight:600;color:#6b7280;display:block;margin-bottom:2px;">
                            Realisasi Panen (HA)
                            <span style="color:#16a34a;font-style:italic;">(data: ${totalPanen.toFixed(2)})</span>
                        </label>
                        <input id="snap-realisasi" type="number" step="0.01" value="${totalPanen.toFixed(2)}"
                            style="width:100%;padding:5px 8px;border:1px solid #d1d5db;border-radius:4px;font-size:12px;">
                    </div>
                    <div>
                        <label style="font-size:10px;font-weight:600;color:#6b7280;display:block;margin-bottom:2px;">
                            Siap Panen (HA)
                            <span style="color:#ea580c;font-style:italic;">(data: ${totalSiapReady.toFixed(2)})</span>
                        </label>
                        <input id="snap-siap" type="number" step="0.01" value="${totalSiapReady.toFixed(2)}"
                            style="width:100%;padding:5px 8px;border:1px solid #d1d5db;border-radius:4px;font-size:12px;">
                    </div>
                </div>
                <div style="margin-bottom:8px;">
                    <label style="font-size:10px;font-weight:600;color:#6b7280;display:block;margin-bottom:2px;">Catatan</label>
                    <textarea id="snap-catatan" rows="2" placeholder="Catatan tambahan..."
                        style="width:100%;padding:5px 8px;border:1px solid #d1d5db;border-radius:4px;font-size:12px;resize:vertical;"></textarea>
                </div>
                <button onclick="savePanenSnapshot('${STORAGE_KEY}')"
                    style="width:100%;padding:7px;background:#ea580c;color:white;border:none;border-radius:5px;font-weight:700;font-size:12px;cursor:pointer;"
                    onmouseover="this.style.background='#c2410c'" onmouseout="this.style.background='#ea580c'">
                    💾 Simpan Snapshot
                </button>
            </div>

            <div>
                <div style="font-weight:700;font-size:12px;color:#374151;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;">
                    📊 Riwayat Snapshot
                    ${snapshots.length > 0 ? `<button onclick="clearPanenSnapshots('${STORAGE_KEY}')" style="font-size:10px;background:#fee2e2;color:#dc2626;border:none;border-radius:3px;padding:2px 6px;cursor:pointer;">Hapus Semua</button>` : ''}
                </div>`;

        if (snapshots.length === 0) {
            html += `<div style="color:#9ca3af;font-style:italic;font-size:11px;padding:12px;text-align:center;background:#f9fafb;border-radius:6px;">Belum ada snapshot. Isi form di atas lalu klik Simpan.</div>`;
        } else {
            html += `<div style="max-height:320px;overflow-y:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:11px;">
                <thead style="position:sticky;top:0;">
                    <tr style="background:#374151;color:white;">
                        <th style="padding:5px 8px;border:1px solid #e5e7eb;">Tgl</th>
                        <th style="padding:5px 8px;border:1px solid #e5e7eb;text-align:right;">Harapan</th>
                        <th style="padding:5px 8px;border:1px solid #e5e7eb;text-align:right;">Realisasi</th>
                        <th style="padding:5px 8px;border:1px solid #e5e7eb;text-align:right;">Siap</th>
                        <th style="padding:5px 8px;border:1px solid #e5e7eb;text-align:right;">Efisiensi</th>
                        <th style="padding:5px 8px;border:1px solid #e5e7eb;">Catatan</th>
                        <th style="padding:5px 8px;border:1px solid #e5e7eb;"></th>
                    </tr>
                </thead>
                <tbody>`;
            [...snapshots].reverse().forEach((s, idx) => {
                const realIdx   = snapshots.length - 1 - idx;
                const efisiensi = s.harapan_ha > 0 ? (s.realisasi_ha / s.harapan_ha * 100).toFixed(1) : '-';
                const efColor   = s.harapan_ha > 0 ? (s.realisasi_ha >= s.harapan_ha ? '#16a34a' : '#dc2626') : '#6b7280';
                const rowBg     = idx % 2 === 0 ? '#ffffff' : '#f9fafb';
                html += `<tr style="background:${rowBg};">
                    <td style="border:1px solid #e5e7eb;padding:4px 8px;white-space:nowrap;">${s.tanggal}</td>
                    <td style="border:1px solid #e5e7eb;padding:4px 8px;text-align:right;font-weight:600;">${s.harapan_ha>0 ? Number(s.harapan_ha).toFixed(2) : '-'}</td>
                    <td style="border:1px solid #e5e7eb;padding:4px 8px;text-align:right;color:#16a34a;font-weight:600;">${Number(s.realisasi_ha).toFixed(2)}</td>
                    <td style="border:1px solid #e5e7eb;padding:4px 8px;text-align:right;color:#ea580c;">${Number(s.siap_ha).toFixed(2)}</td>
                    <td style="border:1px solid #e5e7eb;padding:4px 8px;text-align:right;font-weight:700;color:${efColor};">${efisiensi !== '-' ? efisiensi+'%' : '-'}</td>
                    <td style="border:1px solid #e5e7eb;padding:4px 8px;color:#6b7280;max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${(s.catatan||'').replace(/"/g,'&quot;')}">${s.catatan||'-'}</td>
                    <td style="border:1px solid #e5e7eb;padding:4px 8px;text-align:center;">
                        <button onclick="deletePanenSnapshot('${STORAGE_KEY}',${realIdx})" style="font-size:10px;background:#fee2e2;color:#dc2626;border:none;border-radius:3px;padding:1px 5px;cursor:pointer;">✕</button>
                    </td>
                </tr>`;
            });
            html += `</tbody></table></div>`;
        }

        html += `</div></div></div>`;
        document.getElementById('panen-efisiensi-body').innerHTML = html;
    }

    function savePanenSnapshot(storageKey) {
        const tanggal      = document.getElementById('snap-tanggal').value;
        const harapan_ha   = parseFloat(document.getElementById('snap-harapan').value)   || 0;
        const realisasi_ha = parseFloat(document.getElementById('snap-realisasi').value) || 0;
        const siap_ha      = parseFloat(document.getElementById('snap-siap').value)      || 0;
        const catatan      = document.getElementById('snap-catatan').value.trim();
        const snapshots    = JSON.parse(localStorage.getItem(storageKey) || '[]');
        snapshots.push({ tanggal, harapan_ha, realisasi_ha, siap_ha, catatan, saved_at: new Date().toISOString() });
        localStorage.setItem(storageKey, JSON.stringify(snapshots));
        renderPanenEfisiensi();
    }

    function deletePanenSnapshot(storageKey, idx) {
        const snapshots = JSON.parse(localStorage.getItem(storageKey) || '[]');
        snapshots.splice(idx, 1);
        localStorage.setItem(storageKey, JSON.stringify(snapshots));
        renderPanenEfisiensi();
    }

    function clearPanenSnapshots(storageKey) {
        if (!confirm('Hapus semua riwayat snapshot?')) return;
        localStorage.removeItem(storageKey);
        renderPanenEfisiensi();
    }
    // ===== /PROPORSI PANEN MODAL =====


    </script>

    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCc2vFD26wD5ox_5EwLJhR6U1jcfKibxBQ&callback=initMapIfNeeded"></script>
</x-layout>