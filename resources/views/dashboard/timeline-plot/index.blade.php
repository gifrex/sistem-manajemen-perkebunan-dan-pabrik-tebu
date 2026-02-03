<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    
    <style>
        h1{text-align:center;color:#333;}
        table{width:100%;border-collapse:separate;border-spacing:0;box-shadow:0 3px 8px rgba(0,0,0,.1);animation:fadeIn 0.4s ease-in;}
        th,td{border:1px solid #ddd;padding:6px 8px;font-size:13px;vertical-align:middle;background-clip:padding-box;}
        tbody tr{transition:background-color .2s;}
        tbody tr:hover td{background:#1f6f3d  !important;color:#ffffff ;}
        tbody tr:hover td *{color:#ffffff !important;}
        tbody tr:hover td * *{color:#000000 !important;}
         
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
    </style>
    <div class="mx-auto px-6" x-data="{activeTab:'{{ request('tab','table') }}',map:null,markers:[],polygons:[]}">

        
        <div class="mb-6 border-b border-gray-200">
            <nav class="flex space-x-4">
                <a href="?crop=pc&activity={{$activityFilter}}&tab={{ request('tab','table') }}" 
                class="py-2 px-4 border-b-2 font-medium text-sm {{$cropType==='pc'?'border-blue-600 text-blue-600':'border-transparent text-gray-500 hover:text-gray-700'}}">
                 📊 Data PC
                </a>
                
                <a href="?crop=rc&activity={{$activityFilter}}&tab={{ request('tab','table') }}" 
                class="py-2 px-4 border-b-2 font-medium text-sm {{$cropType==='rc'?'border-blue-600 text-blue-600':'border-transparent text-gray-500 hover:text-gray-700'}}">
                 📊 Data RC
                </a>
                
                <a href="?crop=p&activity={{$activityFilter}}&tab={{ request('tab','table') }}"
                class="py-2 px-4 border-b-2 font-medium text-sm {{$cropType==='p'?'border-blue-600 text-blue-600':'border-transparent text-gray-500 hover:text-gray-700'}}">
                 🌾 Data Panen
                </a>
                
                <button 
                @click="activeTab = activeTab === 'map' ? 'table' : 'map'; activeTab === 'map' && $nextTick(() => initMapIfNeeded())" 
                class="py-2 px-4 border-b-2 font-medium text-sm"
                x-bind:class="activeTab === 'map' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
                🗺️ Tampilan Map
                </button>
            
                
<div class="ml-auto flex items-center gap-3">
    <button type="button"
    @click="window.location.href='{{ url()->current() }}?crop={{ $cropType }}&activity={{ $activityFilter }}&fill={{ $fillFilter ?? 'all' }}&export=excel&tab=' + activeTab"
    class="py-2 px-4 bg-green-600 hover:bg-green-700 text-white rounded font-medium text-sm flex items-center gap-2">
    📊 Export Excel
  </button>
  

    <label class="text-sm font-medium text-gray-700">Filter Activity:</label>
    <select onchange="window.location.href='{{ url()->current() }}?crop={{ $cropType }}&activity=' + this.value + '&tab=map'"

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
                                @endphp
                                <th class="sticky-v" colspan="3" style="text-align:center;">
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
                            
                            {{-- 2 Kolom Terakhir --}}
                            <th class="sticky-v" rowspan="2">Realisasi<br>Tanam<br><small>HA</small></th>
                            @if($cropType !== 'p')
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
                                
                                $grandTotalRealisasi += $totalActivity;
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
                            
                            {{-- Total Realisasi Tanam --}}
                            <td style="text-align:right;">
                                {{ number_format($grandTotalRealisasi, 2) }}
                            </td>
                            
                            {{-- Total Persentase --}}
                            @if($cropType !== 'p')
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
                        @foreach($blokPlots as $blok=>$plots)
                        @foreach($plots as $index=>$plot)
                            <tr>
                                @if($index===0)<td rowspan="{{count($plots)}}" class="sticky-h blok" style="left:0;">{{$blok}}</td>@endif
                                <td class="sticky-h" style="left:60px;">{{$plot->plot}}</td>
                                <td class="sticky-h" style="left:120px;text-align:right;">{{$plot->batcharea?number_format($plot->batcharea,2):'-'}}</td>
                                
                                @php
                                    $totalRealisasiPlot = 0;
                                @endphp
                                
                                @foreach($activityMap as $activitycode => $label)
                                    @php 
                                        $activity = $activityData->get($plot->plot)?->get($activitycode);
                                        $value = $activity->total_luas ?? 0;
                                        $percentage = $activity->avg_percentage ?? 0;
                                        $tanggal = $activity->tanggal_terbaru ?? null;
                                        $totalRealisasiPlot += $value;
                                        
                                        $percentageColor = $percentage >= 100 ? '#22c55e' : ($percentage > 0 ? '#dc2626' : '#6b7280');
                                    @endphp
                                    
                                    <td style="text-align:right;">{{ $value > 0 ? number_format($value, 2) : '-' }}</td>
                                    <td style="text-align:right; font-weight:600; color: {{ $percentageColor }};">
                                        {{ $value > 0 ? number_format($percentage, 2) . '%' : '-' }}
                                    </td>
                                    <td style="text-align:center;font-size:11px;">
                                        {{ $tanggal ? \Carbon\Carbon::parse($tanggal)->format('d M y') : '-' }}
                                    </td>
                                @endforeach
                                
                                {{-- Realisasi Tanam (Total semua activity untuk plot ini) --}}
                                <td style="text-align:right;">
                                    {{ $totalRealisasiPlot > 0 ? number_format($totalRealisasiPlot, 2) : '-' }}
                                </td>

                                @if($cropType !== 'p')
                                {{-- Persentase (stage: 1/total activity) --}}
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
  
        <div x-show="activeTab==='map'" x-transition class="bg-white shadow-md rounded-lg p-6">
            <!-- MAP SECTION (EXISTING) -->
            <h3 class="text-xl font-bold mb-4">Peta Lokasi Plot</h3>
            <div id="map" class="border border-gray-300 rounded-lg"></div>

<!-- LEGEND -->
<div class="mt-3 grid grid-cols-2 md:grid-cols-3 gap-2 text-xs">
  <!-- Fill legend -->
  <div class="flex items-center gap-2 bg-white border rounded px-2 py-1">
    <span class="inline-block w-4 h-4 rounded-full" style="background:#fef3c7;border:2px solid #fff;"></span>
    <span>Belum ada activity (fill cream)</span>
  </div>

  <div class="flex items-center gap-2 bg-white border rounded px-2 py-1">
    <span class="inline-block w-4 h-4 rounded-full" style="background:#86efac;border:2px solid #fff;"></span>
    <span>Sudah ada activity, belum selesai (fill hijau muda)</span>
  </div>

  <div class="flex items-center gap-2 bg-white border rounded px-2 py-1">
    <span class="inline-block w-4 h-4 rounded-full" style="background:#0f766e;border:2px solid #fff;"></span>
    <span>Semua stage selesai (fill hijau tua)</span>
  </div>

  <!-- Ring legend -->
  <div class="flex items-center gap-2 bg-white border rounded px-2 py-1">
    <span class="inline-block w-4 h-4 rounded-full" style="background:#fef3c7;border:3px solid #f97316;"></span>
    <span>Ring orange: sudah ZPK &gt; 35 hari</span>
  </div>
  
  <div class="flex items-center gap-2 bg-white border rounded px-2 py-1">
    <span class="inline-block w-4 h-4 rounded-full" style="background:#fef3c7;border:3px solid #facc15;"></span>
    <span>Ring kuning: sudah ZPK &lt; 25 hari</span>
  </div>

  <div class="flex items-center gap-2 bg-white border rounded px-2 py-1">
    <span class="inline-block w-4 h-4 rounded-full" style="background:#fef3c7;border:3px solid #dc2626;"></span>
    <span>Ring merah: umur ≥ 9 bulan & belum ZPK</span>
  </div>

  <!-- Filter legend -->
  <div class="flex items-center gap-2 bg-white border rounded px-2 py-1 md:col-span-3">
    <span class="inline-block w-4 h-4 rounded-full" style="background:#000;border:2px solid #fff;opacity:.30;"></span>
    <span>Hitam redup: plot tidak match dengan filter activity</span>
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
                                        
                                        $umurText = ($detail['umur_bulan'] ?? 0) >= 0 ? (($detail['umur_bulan'] ?? 0) . ' bln') : '-';
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
    const stage = parseFloat(d.stage_percentage ?? 0);

    // Deteksi ada activity atau tidak (buat cream)
    const hasAnyActivity = Array.isArray(d.activities) && d.activities.length > 0;

    // --- ZPK logic (tetap) ---
    let hasZpk = false;
    let daysSinceZpk = null;

    if (Array.isArray(d.activities)) {
        const zpkAct = d.activities.find(a => a.code === '4.2.1');
        if (zpkAct && zpkAct.tanggal) {
            hasZpk = true;
            const zpkDate = new Date(zpkAct.tanggal);
            const today   = new Date();
            const diffMs  = today - zpkDate;
            daysSinceZpk  = diffMs / (1000 * 60 * 60 * 24);
        }
    }

    // --- aturan utama dulu: selesai/ada/belum ada ---
    if (!hasAnyActivity) return '#fef3c7';   // cream (belum ada activity)
    if (stage >= 100) return '#0f766e';      // hijau tua (semua stage selesai)

    // --- override ZPK (kalau sudah ada activity tapi belum selesai) ---
    // default progress (belum selesai) → hijau muda
    return '#86efac';
}

function getRingColor(d) {
  const umurHari  = d.umur_hari || 0;
  const umurBulan = (d.umur_bulan ?? 0);

  // cari activity ZPK (4.2.1)
  let hasZpk = false;
  let daysSinceZpk = null;

  if (Array.isArray(d.activities)) {
    const zpkAct = d.activities.find(a => a.code === '4.2.1');
    if (zpkAct && zpkAct.tanggal) {
      hasZpk = true;
      const zpkDate = new Date(zpkAct.tanggal);
      const today   = new Date();
      const diffMs  = today - zpkDate;
      daysSinceZpk  = diffMs / (1000 * 60 * 60 * 24);
    }
  }

  // RULE
  if (hasZpk && daysSinceZpk !== null && daysSinceZpk > 35) return '#f97316';
  if (hasZpk && daysSinceZpk !== null && daysSinceZpk < 25) return '#facc15';
  if (!hasZpk && umurBulan >= 9) return '#dc2626';

  return '#ffffff'; // default ring putih
}

        
    function initMapIfNeeded() {
        if (window.mapInitialized) return;
            
        map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: parseFloat(plotHeaders[0]?.centerlatitude || -4.12893), lng: parseFloat(plotHeaders[0]?.centerlongitude || 105.2971) },
            zoom: 14
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
                scale: 30,
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
    </script>
    
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCc2vFD26wD5ox_5EwLJhR6U1jcfKibxBQ&callback=initMapIfNeeded"></script>
</x-layout>