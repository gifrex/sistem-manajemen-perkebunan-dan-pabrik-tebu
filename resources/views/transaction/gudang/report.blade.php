<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
  
    <style>
      @media print {
        .no-print { display: none !important; }
  
        /* satu item = section; mulai halaman baru */
        .item-section { 
          page-break-after: always; 
          break-after: page;
          /* ✅ paksa "enter" di setiap halaman */
          padding-top: 14mm;
        }
        .item-section:last-child { page-break-after: auto; break-after: auto; }
  
        tr { page-break-inside: avoid; break-inside: avoid; }
  
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
  
        .item-header { position: static !important; }
      }
  
      @media screen {
        .item-header {
          position: sticky;
          top: 0;
          z-index: 10;
        }
      }
    </style>
  
<div class="p-4 max-w-6xl mx-auto">
  
  {{-- Top info --}}
  <div class="flex items-start justify-between mb-4 no-print">
    <div class="text-sm text-gray-700 leading-6">
      <div>Company: <b>{{ session('companycode') }}</b></div>
      @if($search) <div>Filter: <b>{{ $search }}</b></div> @endif
    </div>


    
          <div class="flex gap-2">
            <a href="{{ route('transaction.gudang.report', array_merge(request()->only(['search','start_date','end_date']), ['mode' => 'item'])) }}"
              class="px-4 py-2 rounded text-sm {{ ($mode ?? 'item') === 'item' ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300' }}">
              View per Item
            </a>

            <a href="{{ route('transaction.gudang.report', array_merge(request()->only(['search','start_date','end_date']), ['mode' => 'activity'])) }}"
              class="px-4 py-2 rounded text-sm {{ ($mode ?? 'item') === 'activity' ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300' }}">
              View per Activity
            </a>

            <a href="{{ route('transaction.gudang.index', request()->only(['search','start_date','end_date','mode'])) }}"
              class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-sm">← Back</a>

            <button onclick="window.print()"
                    class="px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50 text-sm">🖨️ Print</button>
          </div>
  </div>

  @if(($mode ?? 'item') === 'activity')

  @forelse($report as $block)
    @php
      $totalMasuk  = collect($block->rows)->sum(fn($r) => (float)($r->masuk ?? 0));
      $totalKeluar = collect($block->rows)->sum(fn($r) => (float)($r->keluar ?? 0));
    @endphp

    <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm mb-3 overflow-hidden">

      {{-- Header activity / collapse button --}}
      <div @click="open = !open"
           class="px-4 py-3 cursor-pointer hover:bg-gray-50 flex items-center justify-between gap-4 no-print">
        <div class="min-w-0">
          <div class="text-sm text-gray-900">
            <b>{{ !empty($block->tgl) ? date('d M Y', strtotime($block->tgl)) : '-' }}</b>
          </div>
          <div class="text-xs text-gray-500 mt-1">
            Periode: {{ $startDate }} s/d {{ $endDate }}
            @if($search)
              <span class="text-gray-400"> • </span>
              Filter: {{ $search }}
            @endif
          </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
          <div class="text-xs text-gray-600 text-right">
            Masuk: <span class="font-semibold text-green-700">{{ number_format($totalMasuk, 2) }}</span><br>
            Keluar: <span class="font-semibold text-red-700">{{ number_format($totalKeluar, 2) }}</span>
          </div>

          <svg :class="open ? 'rotate-180' : ''"
               class="w-4 h-4 transition-transform duration-200 text-gray-500"
               fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 9l-7 7-7-7"/>
          </svg>
        </div>
      </div>

      {{-- versi print tetap tampil --}}
      <div class="hidden print:block px-4 py-3">
        <div class="flex items-start justify-between gap-4">
          <div class="text-sm text-gray-900 min-w-0">
            <b>{{ !empty($block->tgl) ? date('d M Y', strtotime($block->tgl)) : '-' }}</b>
          </div>

          <div class="text-xs text-gray-600 text-right whitespace-nowrap">
            Periode: {{ $startDate }} s/d {{ $endDate }}
            @if($search)
              <span class="text-gray-400"> • </span>
              Filter: {{ $search }}
            @endif
          </div>
        </div>
      </div>

      {{-- Content collapse --}}
      <div x-show="open" x-transition class="overflow-x-auto no-print">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="py-2 px-3 border border-gray-300 text-center">RT/USE</th>
              <th class="py-2 px-3 border border-gray-300">KET</th>
              <th class="py-2 px-3 border border-gray-300">ITEM CODE</th>
              <th class="py-2 px-3 border border-gray-300">ITEM NAME</th>
              <th class="py-2 px-3 border border-gray-300">UNIT</th>
              <th class="py-2 px-3 border border-gray-300 text-right">MASUK</th>
              <th class="py-2 px-3 border border-gray-300 text-right">KELUAR</th>
            </tr>
          </thead>

          <tbody>
            @foreach($block->rows as $r)
              <tr class="hover:bg-gray-50">
                <td class="py-2 px-3 border border-gray-300 text-center font-semibold
                {{ strtoupper(trim($r->type ?? '')) === 'R' ? 'text-green-700' : (strtoupper(trim($r->type ?? '')) === 'U' ? 'text-red-700' : 'text-gray-600') }}">
                  {{ strtoupper(trim($r->type ?? '')) === 'R' ? 'RT' : (strtoupper(trim($r->type ?? '')) === 'U' ? 'USE' : '') }}
                </td>

                <td class="py-2 px-3 border border-gray-300">
                  {{ $r->ket ?? '' }}
                </td>

                <td class="py-2 px-3 border border-gray-300">
                  {{ $r->itemcode ?? '' }}
                </td>

                <td class="py-2 px-3 border border-gray-300">
                  {{ $r->itemname ?? '' }}
                </td>

                <td class="py-2 px-3 border border-gray-300">
                  {{ $r->unit ?? '' }}
                </td>

                <td class="py-2 px-3 border border-gray-300 text-right font-medium text-green-700">
                  {{ is_null($r->masuk) ? '' : number_format($r->masuk, 2) }}
                </td>

                <td class="py-2 px-3 border border-gray-300 text-right font-medium text-red-700">
                  {{ is_null($r->keluar) ? '' : number_format($r->keluar, 2) }}
                </td>
              </tr>
            @endforeach
          </tbody>

          <tfoot>
            <tr class="bg-gray-100 font-semibold">
              <td class="py-2 px-3 border border-gray-300 text-right" colspan="5">TOTAL</td>
              <td class="py-2 px-3 border border-gray-300 text-right text-green-800">
                {{ number_format($totalMasuk, 2) }}
              </td>
              <td class="py-2 px-3 border border-gray-300 text-right text-red-800">
                {{ number_format($totalKeluar, 2) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

      {{-- khusus print: table tetap tampil --}}
      <div class="hidden print:block overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="py-2 px-3 border border-gray-300 text-center">RT/USE</th>
              <th class="py-2 px-3 border border-gray-300">KET</th>
              <th class="py-2 px-3 border border-gray-300">ITEM CODE</th>
              <th class="py-2 px-3 border border-gray-300">ITEM NAME</th>
              <th class="py-2 px-3 border border-gray-300">UNIT</th>
              <th class="py-2 px-3 border border-gray-300 text-right">MASUK</th>
              <th class="py-2 px-3 border border-gray-300 text-right">KELUAR</th>
            </tr>
          </thead>

          <tbody>
            @foreach($block->rows as $r)
              <tr>
                <td class="py-2 px-3 border border-gray-300 text-center font-semibold
                {{ strtoupper(trim($r->type ?? '')) === 'R' ? 'text-green-700' : (strtoupper(trim($r->type ?? '')) === 'U' ? 'text-red-700' : 'text-gray-600') }}">
                  {{ strtoupper(trim($r->type ?? '')) === 'R' ? 'RT' : (strtoupper(trim($r->type ?? '')) === 'U' ? 'USE' : '') }}
                </td>

                <td class="py-2 px-3 border border-gray-300">
                  {{ $r->ket ?? '' }}
                </td>

                <td class="py-2 px-3 border border-gray-300">
                  {{ $r->itemcode ?? '' }}
                </td>

                <td class="py-2 px-3 border border-gray-300">
                  {{ $r->itemname ?? '' }}
                </td>

                <td class="py-2 px-3 border border-gray-300">
                  {{ $r->unit ?? '' }}
                </td>

                <td class="py-2 px-3 border border-gray-300 text-right font-medium text-green-700">
                  {{ is_null($r->masuk) ? '' : number_format($r->masuk, 2) }}
                </td>

                <td class="py-2 px-3 border border-gray-300 text-right font-medium text-red-700">
                  {{ is_null($r->keluar) ? '' : number_format($r->keluar, 2) }}
                </td>
              </tr>
            @endforeach
          </tbody>

          <tfoot>
            <tr class="bg-gray-100 font-semibold">
              <td class="py-2 px-3 border border-gray-300 text-right" colspan="5">TOTAL</td>
              <td class="py-2 px-3 border border-gray-300 text-right text-green-800">
                {{ number_format($totalMasuk, 2) }}
              </td>
              <td class="py-2 px-3 border border-gray-300 text-right text-red-800">
                {{ number_format($totalKeluar, 2) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

    </div>

  @empty
    <div class="bg-yellow-50 border border-yellow-200 p-4 rounded text-sm text-yellow-800">
      Tidak ada transaksi pada periode ini.
    </div>
  @endforelse

@else

  @forelse($report as $block)
    @php
      $totalMasuk  = collect($block->rows)->sum(fn($r) => (float)($r->masuk ?? 0));
      $totalKeluar = collect($block->rows)->sum(fn($r) => (float)($r->keluar ?? 0));
      $saldo = $totalMasuk - $totalKeluar;
    @endphp

    <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm mb-3 overflow-hidden">

      {{-- Header item / collapse button --}}
      <div @click="open = !open"
           class="px-4 py-3 cursor-pointer hover:bg-gray-50 flex items-center justify-between gap-4 no-print">
        <div class="min-w-0">
          <div class="text-sm text-gray-900">
            <b>{{ $block->itemcode }}</b> — {{ $block->itemname }} ({{ $block->unit }})
          </div>
          <div class="text-xs text-gray-500 mt-1">
            Periode: {{ $startDate }} s/d {{ $endDate }}
            @if($search)
              <span class="text-gray-400"> • </span>
              Filter: {{ $search }}
            @endif
          </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
          <div class="text-xs text-gray-600 text-right">
            Saldo: <span class="font-semibold text-gray-900">{{ number_format($saldo, 2) }}</span>
          </div>

          <svg :class="open ? 'rotate-180' : ''"
               class="w-4 h-4 transition-transform duration-200 text-gray-500"
               fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 9l-7 7-7-7"/>
          </svg>
        </div>
      </div>

      {{-- versi print tetap tampil --}}
      <div class="hidden print:block px-4 py-3">
        <div class="flex items-start justify-between gap-4">
          <div class="text-sm text-gray-900 min-w-0">
            <b>{{ $block->itemcode }}</b> — {{ $block->itemname }} ({{ $block->unit }})
          </div>

          <div class="text-xs text-gray-600 text-right whitespace-nowrap">
            Periode: {{ $startDate }} s/d {{ $endDate }}
            @if($search)
              <span class="text-gray-400"> • </span>
              Filter: {{ $search }}
            @endif
          </div>
        </div>
      </div>

      {{-- Content collapse --}}
      <div x-show="open" x-transition class="overflow-x-auto no-print">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="py-2 px-3 border border-gray-300">TANGGAL</th>
              <th class="py-2 px-3 border border-gray-300 text-center">RT/USE</th>
              <th class="py-2 px-3 border border-gray-300">KET</th>
              <th class="py-2 px-3 border border-gray-300 text-right">MASUK</th>
              <th class="py-2 px-3 border border-gray-300 text-right">KELUAR</th>
            </tr>
          </thead>

          <tbody>
            @foreach($block->rows as $r)
              <tr class="hover:bg-gray-50">
                <td class="py-2 px-3 border border-gray-300 text-center">
                  {{ !empty($r->tgl) ? date('d M Y', strtotime($r->tgl)) : '' }}
                </td>
                
                <td class="py-2 px-3 border border-gray-300 text-center font-semibold
                {{ strtoupper(trim($r->type ?? '')) === 'R' ? 'text-green-700' : (strtoupper(trim($r->type ?? '')) === 'U' ? 'text-red-700' : 'text-gray-600') }}">
                  {{ strtoupper(trim($r->type ?? '')) === 'R' ? 'RT' : (strtoupper(trim($r->type ?? '')) === 'U' ? 'USE' : '') }}
                </td>

                <td class="py-2 px-3 border border-gray-300">
                  {{ $r->ket ?? '' }}
                </td>

                <td class="py-2 px-3 border border-gray-300 text-right font-medium text-green-700">
                  {{ is_null($r->masuk) ? '' : number_format($r->masuk, 2) }}
                </td>

                <td class="py-2 px-3 border border-gray-300 text-right font-medium text-red-700">
                  {{ is_null($r->keluar) ? '' : number_format($r->keluar, 2) }}
                </td>
              </tr>
            @endforeach
          </tbody>

          <tfoot>
            <tr class="bg-gray-100 font-semibold">
              <td class="py-2 px-3 border border-gray-300 text-right" colspan="3">TOTAL</td>
              <td class="py-2 px-3 border border-gray-300 text-right text-green-800">
                {{ number_format($totalMasuk, 2) }}
              </td>
              <td class="py-2 px-3 border border-gray-300 text-right text-red-800">
                {{ number_format($totalKeluar, 2) }}
              </td>
            </tr>

            <tr class="bg-white font-semibold">
              <td class="py-2 px-3 border border-gray-300 text-right" colspan="3">Selisih</td>
              <td class="py-2 px-3 border border-gray-300 text-right text-gray-900" colspan="2">
                {{ number_format($saldo, 2) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

      {{-- khusus print: table tetap tampil --}}
      <div class="hidden print:block overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="py-2 px-3 border border-gray-300">TANGGAL</th>
              <th class="py-2 px-3 border border-gray-300 text-center">RT/USE</th>
              <th class="py-2 px-3 border border-gray-300">KET</th>
              <th class="py-2 px-3 border border-gray-300 text-right">MASUK</th>
              <th class="py-2 px-3 border border-gray-300 text-right">KELUAR</th>
            </tr>
          </thead>

          <tbody>
            @foreach($block->rows as $r)
              <tr>
                <td class="py-2 px-3 border border-gray-300 text-center">
                  {{ !empty($r->tgl) ? date('d M Y', strtotime($r->tgl)) : '' }}
                </td>
                
                <td class="py-2 px-3 border border-gray-300 text-center font-semibold
                {{ strtoupper(trim($r->type ?? '')) === 'R' ? 'text-green-700' : (strtoupper(trim($r->type ?? '')) === 'U' ? 'text-red-700' : 'text-gray-600') }}">
                  {{ strtoupper(trim($r->type ?? '')) === 'R' ? 'RT' : (strtoupper(trim($r->type ?? '')) === 'U' ? 'USE' : '') }}
                </td>

                <td class="py-2 px-3 border border-gray-300">
                  {{ $r->ket ?? '' }}
                </td>

                <td class="py-2 px-3 border border-gray-300 text-right font-medium text-green-700">
                  {{ is_null($r->masuk) ? '' : number_format($r->masuk, 2) }}
                </td>

                <td class="py-2 px-3 border border-gray-300 text-right font-medium text-red-700">
                  {{ is_null($r->keluar) ? '' : number_format($r->keluar, 2) }}
                </td>
              </tr>
            @endforeach
          </tbody>

          <tfoot>
            <tr class="bg-gray-100 font-semibold">
              <td class="py-2 px-3 border border-gray-300 text-right" colspan="3">TOTAL</td>
              <td class="py-2 px-3 border border-gray-300 text-right text-green-800">
                {{ number_format($totalMasuk, 2) }}
              </td>
              <td class="py-2 px-3 border border-gray-300 text-right text-red-800">
                {{ number_format($totalKeluar, 2) }}
              </td>
            </tr>

            <tr class="bg-white font-semibold">
              <td class="py-2 px-3 border border-gray-300 text-right" colspan="3">Selisih</td>
              <td class="py-2 px-3 border border-gray-300 text-right text-gray-900" colspan="2">
                {{ number_format($saldo, 2) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

    </div>

  @empty
    <div class="bg-yellow-50 border border-yellow-200 p-4 rounded text-sm text-yellow-800">
      Tidak ada transaksi pada periode ini.
    </div>
  @endforelse

@endif


</div>

    </x-layout>
  