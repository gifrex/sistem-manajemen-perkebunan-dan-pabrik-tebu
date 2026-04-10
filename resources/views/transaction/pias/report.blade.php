<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  @once
  <style>
    @media print {
      .no-print { display: none !important; }
      .print-only { display: block !important; }
      body { font-size: 11px; }
      table { border-collapse: collapse !important; width: 100% !important; }
      th, td { border: 1px solid #555 !important; padding: 4px 6px !important; }
      thead { display: table-header-group; }
      tr { page-break-inside: avoid !important; }
    }
    @media screen { .print-only { display: none; } }
  </style>
  @endonce

  <div class="w-full p-4">

    {{-- Filter --}}
    <form method="GET" class="no-print mb-4">
      <div class="max-w-4xl mx-auto bg-gray-50 border border-gray-300 rounded-lg p-4 shadow-sm">
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

          <div class="md:col-span-2 flex gap-2 flex-wrap">
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

    {{-- Grand total --}}
    @php
      $grandTJ = 0; $grandTC = 0; $grandTV = 0;
      foreach ($grouped as $rows) {
        foreach ($rows as $r) {
          $grandTJ += $r->tj;
          $grandTC += $r->tc;
          $grandTV += $r->tv;
        }
      }
    @endphp

    <div class="overflow-x-auto">
      <table class="w-full text-sm border border-gray-300">
        <thead>
          <tr class="bg-gray-700 text-white text-center">
            <th class="border border-gray-400 px-3 py-2 whitespace-nowrap">TANGGAL</th>
            <th class="border border-gray-400 px-3 py-2">BLOK</th>
            <th class="border border-gray-400 px-3 py-2">PLOT</th>
            <th class="border border-gray-400 px-3 py-2">HA</th>
            <th class="border border-gray-400 px-3 py-2 whitespace-nowrap">TGL TANAM</th>
            <th class="border border-gray-400 px-3 py-2">BULAN</th>
            <th class="border border-gray-400 px-3 py-2">KATEGORI</th>
            <th class="border border-gray-400 px-3 py-2">VARIETAS</th>
            <th class="border border-gray-400 px-3 py-2 bg-blue-800">TJ</th>
            <th class="border border-gray-400 px-3 py-2 bg-green-800">TC</th>
            <th class="border border-gray-400 px-3 py-2 bg-yellow-700">TV</th>
          </tr>
        </thead>
        <tbody>
          @foreach($grouped as $tgl => $rows)
            @php
              $rowCount = $rows->count();
              $dayTJ = $rows->sum('tj');
              $dayTC = $rows->sum('tc');
              $dayTV = $rows->sum('tv');
            @endphp

            @foreach($rows as $i => $r)
              <tr class="hover:bg-gray-50 {{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">

                {{-- Tanggal dengan rowspan --}}
                @if($i === 0)
                  <td class="border border-gray-300 px-3 py-2 text-center font-semibold align-top whitespace-nowrap"
                      rowspan="{{ $rowCount + 1 }}">
                    {{ \Carbon\Carbon::parse($tgl)->format('d-M-y') }}
                  </td>
                @endif

                <td class="border border-gray-300 px-3 py-2 text-center">{{ $r->blok }}</td>
                <td class="border border-gray-300 px-3 py-2 font-mono">{{ $r->plot }}</td>
                <td class="border border-gray-300 px-3 py-2 text-right">{{ number_format($r->ha, 2) }}</td>
                <td class="border border-gray-300 px-3 py-2 text-center whitespace-nowrap">
                  {{ $r->tgl_tanam ? \Carbon\Carbon::parse($r->tgl_tanam)->format('d-M-y') : '-' }}
                </td>
                <td class="border border-gray-300 px-3 py-2 text-center">
                  @if($r->bulan !== '-')
                    <span class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 text-xs font-semibold">{{ $r->bulan }} Bln</span>
                  @else
                    -
                  @endif
                </td>
                <td class="border border-gray-300 px-3 py-2 text-center">{{ $r->kategori ?? '-' }}</td>
                <td class="border border-gray-300 px-3 py-2 text-center">{{ $r->varietas ?? '-' }}</td>
                <td class="border border-gray-300 px-3 py-2 text-right bg-blue-50 font-semibold">
                  {{ $r->tj > 0 ? number_format($r->tj) : '-' }}
                </td>
                <td class="border border-gray-300 px-3 py-2 text-right bg-green-50 font-semibold">
                  {{ $r->tc > 0 ? number_format($r->tc) : '-' }}
                </td>
                <td class="border border-gray-300 px-3 py-2 text-right bg-yellow-50 font-semibold">
                  {{ $r->tv > 0 ? number_format($r->tv) : '-' }}
                </td>
              </tr>
            @endforeach

            {{-- Subtotal per tanggal --}}
            <tr class="bg-blue-100 font-bold text-sm">
              <td class="border border-gray-300 px-3 py-1 text-right text-gray-600" colspan="7">
                Subtotal {{ \Carbon\Carbon::parse($tgl)->format('d-M-y') }}
              </td>
              <td class="border border-gray-300 px-3 py-1 text-right text-blue-700">{{ number_format($dayTJ) }}</td>
              <td class="border border-gray-300 px-3 py-1 text-right text-green-700">{{ number_format($dayTC) }}</td>
              <td class="border border-gray-300 px-3 py-1 text-right text-yellow-700">{{ number_format($dayTV) }}</td>
            </tr>

          @endforeach

          {{-- Grand Total --}}
          <tr class="bg-gray-700 text-white font-bold">
            <td class="border border-gray-500 px-3 py-2 text-center" colspan="8">GRAND TOTAL</td>
            <td class="border border-gray-500 px-3 py-2 text-right bg-blue-900">{{ number_format($grandTJ) }}</td>
            <td class="border border-gray-500 px-3 py-2 text-right bg-green-900">{{ number_format($grandTC) }}</td>
            <td class="border border-gray-500 px-3 py-2 text-right bg-yellow-800">{{ number_format($grandTV) }}</td>
          </tr>
        </tbody>
      </table>
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
