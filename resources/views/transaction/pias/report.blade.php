<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  @once
  <style>
    .pct-bar { display:flex; height:6px; border-radius:3px; overflow:hidden; min-width:60px; }
    .pct-tj  { background:#3B82F6; }
    .pct-tc  { background:#22C55E; }
    .pct-tv  { background:#EAB308; }
    @media print {
      .no-print { display: none !important; }
      .print-only { display: block !important; }
      body { font-size: 10px; }
      table { border-collapse: collapse !important; width: 100% !important; }
      th, td { border: 1px solid #555 !important; padding: 3px 5px !important; }
      thead { display: table-header-group; }
      tr { page-break-inside: avoid !important; }
      .pct-bar { display: none; }
    }
    @media screen { .print-only { display: none; } }
  </style>
  @endonce

  <div class="w-full p-4">

    {{-- Filter --}}
    <form method="GET" class="no-print mb-4">
      <div class="max-w-5xl mx-auto bg-gray-50 border border-gray-300 rounded-lg p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">

          <div class="md:col-span-4 relative">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder=" "
              class="peer w-full p-2 pt-5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500" />
            <label class="absolute left-2 top-1 text-xs text-gray-600 peer-focus:text-blue-600">
              Search RKH / Blok / Plot
            </label>
          </div>

          <div class="md:col-span-2 relative">
            <input type="date" name="start_date" value="{{ $startDate }}"
              class="peer w-full p-2 pt-5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500" />
            <label class="absolute left-2 top-1 text-xs text-gray-600 peer-focus:text-blue-600">Start Date</label>
          </div>

          <div class="md:col-span-2 relative">
            <input type="date" name="end_date" value="{{ $endDate }}"
              class="peer w-full p-2 pt-5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500" />
            <label class="absolute left-2 top-1 text-xs text-gray-600 peer-focus:text-blue-600">End Date</label>
          </div>

          <div class="md:col-span-4 flex gap-2">
            <button type="submit"
              class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded-md">
              Cari
            </button>
            <button type="button" onclick="window.print()"
              class="flex-1 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium py-2 px-3 rounded-md">
              Print
            </button>
            <a id="exportLink" href="#"
              class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 px-3 rounded-md text-center whitespace-nowrap">
              Export Excel
            </a>
          </div>

        </div>
      </div>
    </form>

    {{-- Judul Print --}}
    <div class="print-only text-center mb-3">
      <div class="text-lg font-bold">LAPORAN PIAS HARIAN</div>
      <div class="text-sm">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>
    </div>

    @if($grouped->isEmpty())
      <div class="text-center py-10 text-gray-500">Tidak ada data untuk periode yang dipilih.</div>
    @else

    @php
      $grandTJ = 0; $grandTC = 0; $grandTV = 0;
      foreach ($grouped as $rows) {
        foreach ($rows as $r) { $grandTJ += $r->tj; $grandTC += $r->tc; $grandTV += $r->tv; }
      }
      $grandTotal = $grandTJ + $grandTC + $grandTV;
      $pct = fn($v, $tot) => $tot > 0 ? round($v / $tot * 100, 1) : 0;
    @endphp

    <div class="overflow-x-auto">
      <table class="w-full text-sm border border-gray-300" style="border-collapse:collapse">
        <thead>
          {{-- Baris 1: grup header --}}
          <tr class="bg-gray-800 text-white text-center text-xs">
            <th class="border border-gray-500 px-2 py-2" rowspan="2">TANGGAL</th>
            <th class="border border-gray-500 px-2 py-2" rowspan="2">BLOK</th>
            <th class="border border-gray-500 px-2 py-2" rowspan="2">PLOT</th>
            <th class="border border-gray-500 px-2 py-2" rowspan="2">HA</th>
            <th class="border border-gray-500 px-2 py-2" rowspan="2">TGL TANAM</th>
            <th class="border border-gray-500 px-2 py-2" rowspan="2">BULAN</th>
            <th class="border border-gray-500 px-2 py-2" rowspan="2">KAT.</th>
            <th class="border border-gray-500 px-2 py-2" rowspan="2">VARIETAS</th>
            <th class="border border-gray-500 px-2 py-2 bg-blue-900" colspan="2">TJ</th>
            <th class="border border-gray-500 px-2 py-2 bg-green-900" colspan="2">TC</th>
            <th class="border border-gray-500 px-2 py-2 bg-yellow-800" colspan="2">TV</th>
            <th class="border border-gray-500 px-2 py-2 bg-gray-600" rowspan="2">TOTAL</th>
            <th class="border border-gray-500 px-2 py-2 bg-gray-600" rowspan="2">PROPORSI</th>
          </tr>
          <tr class="bg-gray-700 text-white text-center text-xs">
            <th class="border border-gray-500 px-2 py-1 bg-blue-900">Jml</th>
            <th class="border border-gray-500 px-2 py-1 bg-blue-800">%</th>
            <th class="border border-gray-500 px-2 py-1 bg-green-900">Jml</th>
            <th class="border border-gray-500 px-2 py-1 bg-green-800">%</th>
            <th class="border border-gray-500 px-2 py-1 bg-yellow-800">Jml</th>
            <th class="border border-gray-500 px-2 py-1 bg-yellow-700">%</th>
          </tr>
        </thead>
        <tbody>
          @foreach($grouped as $tgl => $rows)
            @php
              $rowCount = $rows->count();
              $dayTJ    = $rows->sum('tj');
              $dayTC    = $rows->sum('tc');
              $dayTV    = $rows->sum('tv');
              $dayTotal = $dayTJ + $dayTC + $dayTV;
            @endphp

            @foreach($rows as $i => $r)
              @php
                $plotTotal = $r->tj + $r->tc + $r->tv;
                $pTJ = $pct($r->tj, $plotTotal);
                $pTC = $pct($r->tc, $plotTotal);
                $pTV = $pct($r->tv, $plotTotal);
              @endphp
              <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50">

                @if($i === 0)
                  <td class="border border-gray-300 px-2 py-2 text-center font-semibold align-middle whitespace-nowrap bg-gray-100"
                      rowspan="{{ $rowCount + 1 }}">
                    {{ \Carbon\Carbon::parse($tgl)->format('d-M-y') }}
                  </td>
                @endif

                <td class="border border-gray-300 px-2 py-1 text-center">{{ $r->blok }}</td>
                <td class="border border-gray-300 px-2 py-1 font-mono font-medium">{{ $r->plot }}</td>
                <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format($r->ha, 2) }}</td>
                <td class="border border-gray-300 px-2 py-1 text-center whitespace-nowrap text-xs">
                  {{ $r->tgl_tanam ? \Carbon\Carbon::parse($r->tgl_tanam)->format('d/m/y') : '-' }}
                </td>
                <td class="border border-gray-300 px-2 py-1 text-center">
                  @if($r->bulan !== '-')
                    <span class="px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-800 text-xs font-semibold">{{ $r->bulan }}</span>
                  @else -
                  @endif
                </td>
                <td class="border border-gray-300 px-2 py-1 text-center text-xs">{{ $r->kategori ?? '-' }}</td>
                <td class="border border-gray-300 px-2 py-1 text-center text-xs">{{ $r->varietas ?? '-' }}</td>

                {{-- TJ --}}
                <td class="border border-gray-300 px-2 py-1 text-right bg-blue-50 font-semibold">
                  {{ $r->tj > 0 ? number_format($r->tj) : '-' }}
                </td>
                <td class="border border-gray-300 px-2 py-1 text-right bg-blue-50 text-blue-700 text-xs">
                  {{ $r->tj > 0 ? $pTJ.'%' : '-' }}
                </td>

                {{-- TC --}}
                <td class="border border-gray-300 px-2 py-1 text-right bg-green-50 font-semibold">
                  {{ $r->tc > 0 ? number_format($r->tc) : '-' }}
                </td>
                <td class="border border-gray-300 px-2 py-1 text-right bg-green-50 text-green-700 text-xs">
                  {{ $r->tc > 0 ? $pTC.'%' : '-' }}
                </td>

                {{-- TV --}}
                <td class="border border-gray-300 px-2 py-1 text-right bg-yellow-50 font-semibold">
                  {{ $r->tv > 0 ? number_format($r->tv) : '-' }}
                </td>
                <td class="border border-gray-300 px-2 py-1 text-right bg-yellow-50 text-yellow-700 text-xs">
                  {{ $r->tv > 0 ? $pTV.'%' : '-' }}
                </td>

                {{-- TOTAL --}}
                <td class="border border-gray-300 px-2 py-1 text-right font-bold">
                  {{ $plotTotal > 0 ? number_format($plotTotal) : '-' }}
                </td>

                {{-- Bar proporsi --}}
                <td class="border border-gray-300 px-2 py-1">
                  @if($plotTotal > 0)
                  <div class="pct-bar" title="TJ:{{ $pTJ }}% TC:{{ $pTC }}% TV:{{ $pTV }}%">
                    <div class="pct-tj" style="width:{{ $pTJ }}%"></div>
                    <div class="pct-tc" style="width:{{ $pTC }}%"></div>
                    <div class="pct-tv" style="width:{{ $pTV }}%"></div>
                  </div>
                  <div class="text-xs text-gray-500 whitespace-nowrap mt-0.5">
                    <span class="text-blue-600">{{ $pTJ }}%</span>
                    <span class="text-green-600">{{ $pTC }}%</span>
                    <span class="text-yellow-600">{{ $pTV }}%</span>
                  </div>
                  @else -
                  @endif
                </td>
              </tr>
            @endforeach

            {{-- Subtotal per tanggal --}}
            @php
              $sTJ = $pct($dayTJ, $dayTotal);
              $sTC = $pct($dayTC, $dayTotal);
              $sTV = $pct($dayTV, $dayTotal);
            @endphp
            <tr class="bg-blue-100 font-bold text-xs">
              <td class="border border-gray-400 px-2 py-1 text-right text-gray-700" colspan="7">
                Subtotal {{ \Carbon\Carbon::parse($tgl)->format('d-M-y') }}
              </td>
              <td class="border border-gray-400 px-2 py-1 text-right text-blue-800">{{ number_format($dayTJ) }}</td>
              <td class="border border-gray-400 px-2 py-1 text-right text-blue-700">{{ $sTJ }}%</td>
              <td class="border border-gray-400 px-2 py-1 text-right text-green-800">{{ number_format($dayTC) }}</td>
              <td class="border border-gray-400 px-2 py-1 text-right text-green-700">{{ $sTC }}%</td>
              <td class="border border-gray-400 px-2 py-1 text-right text-yellow-800">{{ number_format($dayTV) }}</td>
              <td class="border border-gray-400 px-2 py-1 text-right text-yellow-700">{{ $sTV }}%</td>
              <td class="border border-gray-400 px-2 py-1 text-right text-gray-800">{{ number_format($dayTotal) }}</td>
              <td class="border border-gray-400 px-2 py-1">
                <div class="pct-bar">
                  <div class="pct-tj" style="width:{{ $sTJ }}%"></div>
                  <div class="pct-tc" style="width:{{ $sTC }}%"></div>
                  <div class="pct-tv" style="width:{{ $sTV }}%"></div>
                </div>
                <div class="text-xs whitespace-nowrap mt-0.5">
                  <span class="text-blue-600">{{ $sTJ }}%</span>
                  <span class="text-green-600">{{ $sTC }}%</span>
                  <span class="text-yellow-600">{{ $sTV }}%</span>
                </div>
              </td>
            </tr>

          @endforeach

          {{-- Grand Total --}}
          @php
            $gTJ = $pct($grandTJ, $grandTotal);
            $gTC = $pct($grandTC, $grandTotal);
            $gTV = $pct($grandTV, $grandTotal);
          @endphp
          <tr class="bg-gray-800 text-white font-bold text-xs">
            <td class="border border-gray-600 px-2 py-2 text-center" colspan="8">GRAND TOTAL</td>
            <td class="border border-gray-600 px-2 py-2 text-right bg-blue-900">{{ number_format($grandTJ) }}</td>
            <td class="border border-gray-600 px-2 py-2 text-right bg-blue-800">{{ $gTJ }}%</td>
            <td class="border border-gray-600 px-2 py-2 text-right bg-green-900">{{ number_format($grandTC) }}</td>
            <td class="border border-gray-600 px-2 py-2 text-right bg-green-800">{{ $gTC }}%</td>
            <td class="border border-gray-600 px-2 py-2 text-right bg-yellow-800">{{ number_format($grandTV) }}</td>
            <td class="border border-gray-600 px-2 py-2 text-right bg-yellow-700">{{ $gTV }}%</td>
            <td class="border border-gray-600 px-2 py-2 text-right">{{ number_format($grandTotal) }}</td>
            <td class="border border-gray-600 px-2 py-2">
              <div class="pct-bar">
                <div class="pct-tj" style="width:{{ $gTJ }}%"></div>
                <div class="pct-tc" style="width:{{ $gTC }}%"></div>
                <div class="pct-tv" style="width:{{ $gTV }}%"></div>
              </div>
              <div class="text-xs whitespace-nowrap mt-0.5">
                <span class="text-blue-300">{{ $gTJ }}%</span>
                <span class="text-green-300">{{ $gTC }}%</span>
                <span class="text-yellow-300">{{ $gTV }}%</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    {{-- Legend --}}
    <div class="no-print flex gap-4 mt-3 text-xs text-gray-600">
      <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-blue-500"></span> TJ = Tanaman Jadi</span>
      <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-green-500"></span> TC = Tanaman Campuran</span>
      <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-yellow-400"></span> TV = Tanaman Vegetatif</span>
      <span class="text-gray-400">| % dihitung dari total TJ+TC+TV per plot / per tanggal / grand total</span>
    </div>

    @endif

  </div>

  <script>
    (function () {
      var base = "{{ route('transaction.pias.export') }}";
      function updateExport() {
        var s = document.querySelector('[name=start_date]')?.value || '';
        var e = document.querySelector('[name=end_date]')?.value || '';
        var q = document.querySelector('[name=search]')?.value || '';
        var params = new URLSearchParams({ start_date: s, end_date: e, search: q });
        document.getElementById('exportLink').href = base + '?' + params.toString();
      }
      document.querySelectorAll('[name=start_date],[name=end_date],[name=search]')
        .forEach(el => el.addEventListener('change', updateExport));
      updateExport();
    })();
  </script>
</x-layout>
