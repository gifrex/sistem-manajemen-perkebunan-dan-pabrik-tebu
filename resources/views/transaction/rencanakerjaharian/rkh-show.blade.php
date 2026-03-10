{{--resources\views\input\rencanakerjaharian\rkh-show.blade.php--}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <!-- HEADER CONTENT -->
  <div class="bg-white rounded-xl p-6 mb-6 border-2 border-gray-300 shadow-sm">

    <!-- TOP ROW: No RKH + Status Badges -->
    <div class="flex flex-wrap items-start justify-between gap-4 mb-5 pb-5 border-b-2 border-gray-200">
      <div>
        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">No RKH</label>
        <p class="text-4xl font-mono font-bold text-gray-900 tracking-wide">
          {{ $rkhHeader->rkhno ?? '-' }}
        </p>
      </div>

      <div class="flex flex-wrap gap-3">
        @php
          $approvalStatus = 'Waiting';
          $approvalClass  = 'bg-yellow-100 text-yellow-800 border-yellow-300';

          if (isset($rkhHeader->jumlahapproval) && $rkhHeader->jumlahapproval > 0) {
            $approvedCount = 0;
            if ($rkhHeader->approval1flag === '1') $approvedCount++;
            if ($rkhHeader->approval2flag === '1') $approvedCount++;
            if ($rkhHeader->approval3flag === '1') $approvedCount++;

            if ($rkhHeader->approval1flag === '0' || $rkhHeader->approval2flag === '0' || $rkhHeader->approval3flag === '0') {
              $approvalClass = 'bg-red-100 text-red-800 border-red-300';
              if ($rkhHeader->approval1flag === '0')      $approvalStatus = 'Declined L1';
              elseif ($rkhHeader->approval2flag === '0')  $approvalStatus = 'Declined L2';
              elseif ($rkhHeader->approval3flag === '0')  $approvalStatus = 'Declined L3';
            } elseif ($approvedCount === $rkhHeader->jumlahapproval) {
              $approvalStatus = 'Approved';
              $approvalClass  = 'bg-green-100 text-green-800 border-green-300';
            } else {
              $approvalStatus = "Waiting ({$approvedCount}/{$rkhHeader->jumlahapproval})";
            }
          } else {
            $approvalStatus = 'No Approval';
            $approvalClass  = 'bg-gray-200 text-gray-700 border-gray-400';
          }

          $rkhStatus      = $rkhHeader->status === 'Completed' ? 'Completed' : 'In Progress';
          $rkhStatusClass = $rkhHeader->status === 'Completed'
            ? 'bg-green-100 text-green-800 border-green-300'
            : 'bg-blue-100 text-blue-800 border-blue-300';
        @endphp

        <div>
          <div class="text-[10px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Approval</div>
          <span class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-bold border-2 {{ $approvalClass }}">
            {{ $approvalStatus }}
          </span>
        </div>

        <div>
          <div class="text-[10px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Status RKH</div>
          <span class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-bold border-2 {{ $rkhStatusClass }}">
            {{ $rkhStatus }}
          </span>
        </div>
      </div>
    </div>

    <!-- MIDDLE ROW: Info Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

      <!-- LEFT COLUMN (7 cols) -->
      <div class="lg:col-span-7 space-y-4">

        <!-- Mandor & Date -->
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-gray-50 rounded-lg p-4 border border-gray-300">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Mandor</label>
            <div class="text-sm font-bold text-gray-900">{{ $rkhHeader->mandorid ?? '-' }}</div>
            <div class="text-xs text-gray-600 mt-0.5">{{ $rkhHeader->mandor_nama ?? '-' }}</div>
          </div>

          <div class="bg-gray-50 rounded-lg p-4 border border-gray-300">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Tanggal</label>
            <div class="text-sm font-bold text-gray-900">
              {{ \Carbon\Carbon::parse($rkhHeader->rkhdate)->format('d/m/Y') }}
            </div>
            <div class="text-xs text-gray-600 mt-0.5">
              {{ \Carbon\Carbon::parse($rkhHeader->rkhdate)->locale('id')->isoFormat('dddd') }}
            </div>
          </div>
        </div>

        <!-- Keterangan -->
        @if($rkhHeader->keterangan)
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-300">
          <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Keterangan</label>
          <div class="text-sm text-gray-700">{{ $rkhHeader->keterangan }}</div>
        </div>
        @endif

        <!-- Compact Summary Row -->
        <div class="grid grid-cols-3 gap-3">

          <div class="bg-gray-50 rounded-lg p-3 border border-gray-300">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-2 h-2 bg-gray-500 rounded-full"></div>
              <h4 class="text-xs font-bold text-gray-800 uppercase">Absen</h4>
            </div>
            @php
              $absenSummary   = collect($absentenagakerja ?? [])->where('mandorid', $rkhHeader->mandorid);
              $lakiCount      = $absenSummary->where('gender', 'L')->count();
              $perempuanCount = $absenSummary->where('gender', 'P')->count();
              $totalCount     = $lakiCount + $perempuanCount;
            @endphp
            <div class="space-y-1">
              <div class="flex justify-between text-xs">
                <span class="text-gray-600">Laki-laki:</span>
                <span class="font-bold text-gray-900">{{ $lakiCount }}</span>
              </div>
              <div class="flex justify-between text-xs">
                <span class="text-gray-600">Perempuan:</span>
                <span class="font-bold text-gray-900">{{ $perempuanCount }}</span>
              </div>
              <div class="flex justify-between text-xs pt-1 border-t border-gray-300">
                <span class="text-gray-700 font-bold">Total:</span>
                <span class="font-bold text-gray-900">{{ $totalCount }}</span>
              </div>
            </div>
          </div>

          <div class="bg-gray-50 rounded-lg p-3 border border-gray-300">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-2 h-2 bg-gray-500 rounded-full"></div>
              <h4 class="text-xs font-bold text-gray-800 uppercase">Pekerja</h4>
              <span class="ml-auto text-[10px] bg-gray-700 text-white px-2 py-0.5 rounded font-bold">
                {{ $workersByActivity->count() }}
              </span>
            </div>
            <div class="text-xs text-gray-600">
              Total {{ $workersByActivity->sum('jumlahtenagakerja') }} pekerja dalam {{ $workersByActivity->count() }} aktivitas
            </div>
          </div>

          <div class="bg-gray-50 rounded-lg p-3 border border-gray-300">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-2 h-2 bg-gray-500 rounded-full"></div>
              <h4 class="text-xs font-bold text-gray-800 uppercase">Kendaraan</h4>
              <span class="ml-auto text-[10px] bg-gray-700 text-white px-2 py-0.5 rounded font-bold">
                {{ $kendaraanByActivity->flatten(1)->count() }}
              </span>
            </div>
            <div class="text-xs text-gray-600">
              Total {{ $kendaraanByActivity->flatten(1)->count() }} unit dalam {{ $kendaraanByActivity->count() }} aktivitas
            </div>
          </div>

        </div>
      </div>

      <!-- RIGHT COLUMN (5 cols) -->
      <div class="lg:col-span-5 space-y-4">

        <!-- Workers Detail -->
        <div class="bg-gray-50 rounded-lg border border-gray-300">
          <div class="p-3 border-b border-gray-300 bg-gray-100">
            <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wide">Detail Pekerja</h4>
          </div>
          <div class="p-3 space-y-2 max-h-[180px] overflow-y-auto">
            @foreach($workersByActivity as $worker)
              <div class="bg-white rounded border border-gray-300 p-2">
                <div class="flex items-start justify-between mb-1">
                  <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-gray-900 truncate"
                         title="{{ $worker->activitycode }} - {{ $worker->activityname }}">
                      {{ $worker->activitycode }} - {{ $worker->activityname }}
                    </div>
                  </div>
                  <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-200 text-gray-700 font-semibold ml-2 flex-shrink-0">
                    {{ $worker->jenis_nama ?? '-' }}
                  </span>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center">
                  <div>
                    <div class="text-[10px] text-gray-600">L</div>
                    <div class="text-xs font-bold text-gray-900">{{ $worker->jumlahlaki ?? 0 }}</div>
                  </div>
                  <div>
                    <div class="text-[10px] text-gray-600">P</div>
                    <div class="text-xs font-bold text-gray-900">{{ $worker->jumlahperempuan ?? 0 }}</div>
                  </div>
                  <div>
                    <div class="text-[10px] text-gray-600">Total</div>
                    <div class="text-xs font-bold text-gray-900">{{ $worker->jumlahtenagakerja ?? 0 }}</div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        <!-- Kendaraan Detail -->
        <div class="bg-gray-50 rounded-lg border border-gray-300">
          <div class="p-3 border-b border-gray-300 bg-gray-100">
            <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wide">Detail Kendaraan</h4>
          </div>
          <div class="p-3 space-y-2 max-h-[180px] overflow-y-auto">
            @foreach($kendaraanByActivity as $activityCode => $vehicles)
              <div class="bg-white rounded border border-gray-300 p-2">
                <div class="flex items-center justify-between mb-2 pb-1 border-b border-gray-200">
                  <span class="text-xs font-bold text-gray-900">
                    {{ $activityCode }} - {{ $vehicles->first()->activityname ?? '' }}
                  </span>
                  <span class="text-[10px] text-gray-600">{{ $vehicles->count() }} unit</span>
                </div>
                <div class="space-y-1.5">
                  @foreach($vehicles as $vehicle)
                    <div class="flex items-start gap-2 bg-gray-50 rounded p-1.5">
                      <svg class="w-3 h-3 text-gray-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                      </svg>
                      <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-gray-900">{{ $vehicle->nokendaraan }}</div>
                        <div class="text-[10px] text-gray-600 truncate">
                          {{ $vehicle->operator_nama }}
                          @if($vehicle->usinghelper && $vehicle->helper_nama)
                            <span class="text-gray-800 font-semibold">+ {{ $vehicle->helper_nama }}</span>
                          @endif
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Detail Table -->
  <div class="bg-white rounded-xl border-2 border-gray-300 shadow-sm">
    <div class="flex justify-between items-center p-4 border-b-2 border-gray-200 bg-gray-50">
      <h3 class="text-base font-bold text-gray-900 uppercase tracking-wide">Detail Rencana Kerja</h3>
      <div class="flex gap-2">
        {{-- Rekap Material button --}}
        @if(collect($rkhDetails)->where('usingmaterial', 1)->count() > 0)
        <button onclick="openRekapMaterialModal()"
                class="{{ ($isMaterialEstimated ?? false) ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-700 hover:bg-green-800' }} text-white px-4 py-2 rounded-lg text-xs font-bold uppercase transition-colors flex items-center">
          <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
          </svg>
          Rekap Material
          @if($isMaterialEstimated ?? false)
            <span class="ml-1.5 text-[10px] bg-yellow-800 px-1.5 py-0.5 rounded">(Estimasi)</span>
          @endif
        </button>
        @endif
        <button onclick="window.print()"
                class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-xs font-bold uppercase transition-colors flex items-center">
          <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
          </svg>
          Print
        </button>
      </div>
    </div>

    {{-- Estimated banner --}}
    @if($isMaterialEstimated ?? false)
    <div class="mx-4 mt-4 p-3 bg-yellow-50 border border-yellow-300 rounded-lg flex items-start gap-2">
      <svg class="w-4 h-4 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.27 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
      </svg>
      <div>
        <p class="text-xs font-bold text-yellow-800">Data Material Estimasi</p>
        <p class="text-[11px] text-yellow-700 mt-0.5">
          Material belum di-generate. Data di bawah adalah perkiraan berdasarkan dosis × luas RKH. 
          Angka final didapat setelah approval & generate material.
        </p>
      </div>
    </div>
    @endif

    <div class="overflow-x-auto p-4">
      <table class="table-fixed w-full border-collapse bg-white">
        <colgroup>
          <col style="width: 40px">
          <col style="width: 250px">
          <col style="width: 80px">
          <col style="width: 80px">
          <col style="width: 120px">
          <col style="width: 80px">
          <col style="width: 120px">
        </colgroup>

        <thead>
          <tr class="bg-gray-800 text-white border-b-2 border-gray-900">
            <th class="px-3 py-3 text-xs font-bold uppercase">No.</th>
            <th class="px-3 py-3 text-xs font-bold uppercase text-left">Aktivitas</th>
            <th class="px-3 py-3 text-xs font-bold uppercase">Blok</th>
            <th class="px-3 py-3 text-xs font-bold uppercase">Plot</th>
            <th class="px-3 py-3 text-xs font-bold uppercase">Info Plot</th>
            <th class="px-3 py-3 text-xs font-bold uppercase">Luas (ha)</th>
            <th class="px-3 py-3 text-xs font-bold uppercase">Material</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200">
          @forelse ($rkhDetails as $index => $detail)
            @php
              $luasPlot             = $detail->luasarea ?? 0;
              $totalSudahDikerjakan = $detail->total_sudah_dikerjakan ?? 0;
              $luasSisa             = $luasPlot - $totalSudahDikerjakan;
            @endphp
            <tr class="hover:bg-gray-50 transition-colors">
              <td class="px-3 py-3 text-sm text-center font-bold text-gray-700">{{ $index + 1 }}</td>

              <td class="px-3 py-3 text-sm">
                <div class="font-bold text-gray-900">{{ $detail->activitycode ?? '-' }}</div>
                <div class="text-xs text-gray-600 line-clamp-1">{{ $detail->activityname ?? '-' }}</div>
              </td>

              <td class="px-3 py-3 text-sm text-center font-bold text-gray-900">{{ $detail->blok ?? '-' }}</td>
              <td class="px-3 py-3 text-sm text-center font-bold text-gray-900">{{ $detail->plot ?? '-' }}</td>

              <td class="px-3 py-3 text-xs">
                @if($detail->batch_number && $detail->batch_lifecycle)
                  <div class="space-y-1">
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold border
                      {{ $detail->batch_lifecycle === 'PC'  ? 'bg-yellow-100 text-yellow-800 border-yellow-300' : '' }}
                      {{ $detail->batch_lifecycle === 'RC1' ? 'bg-green-100 text-green-800 border-green-300'   : '' }}
                      {{ $detail->batch_lifecycle === 'RC2' ? 'bg-blue-100 text-blue-800 border-blue-300'     : '' }}
                      {{ $detail->batch_lifecycle === 'RC3' ? 'bg-purple-100 text-purple-800 border-purple-300': '' }}">
                      {{ $detail->batch_lifecycle }}
                    </span>
                    <div class="text-[10px] text-gray-600">
                      <span class="font-semibold">Batch:</span> {{ $detail->batch_number }}
                    </div>
                    @if($detail->batcharea)
                      <div class="text-[10px] text-gray-600">
                        <span class="font-semibold">Area Batch:</span> {{ number_format($detail->batcharea, 2) }} Ha
                      </div>
                    @endif
                    @if($detail->tanggalpanen)
                      <div class="text-[10px] text-gray-600">
                        <span class="font-semibold">Tgl Panen:</span> {{ \Carbon\Carbon::parse($detail->tanggalpanen)->format('d/m/Y') }}
                      </div>
                    @endif
                  </div>
                @else
                  <div class="space-y-1">
                    <div class="text-[10px] text-gray-700">
                      <span class="font-semibold">Luas Plot:</span> {{ number_format($luasPlot, 2) }} Ha
                    </div>
                    <div class="text-[10px] text-gray-700">
                      <span class="font-semibold">Luas Sisa:</span> {{ number_format($luasSisa, 2) }} Ha
                    </div>
                    @if($totalSudahDikerjakan > 0)
                      <div class="text-[10px] text-gray-500">
                        <span class="font-semibold">Sudah:</span> {{ number_format($totalSudahDikerjakan, 2) }} Ha
                      </div>
                    @endif
                  </div>
                @endif
              </td>

              <td class="px-3 py-3 text-sm text-right font-bold text-gray-900">
                {{ $detail->luasarea ? number_format($detail->luasarea, 2) : '-' }}
              </td>

              <td class="px-3 py-3 text-xs text-center">
                @if($detail->usingmaterial == 1 && $detail->herbisidagroupname)
                  <div
                    class="{{ ($isMaterialEstimated ?? false)
                        ? 'bg-yellow-100 text-yellow-800 border-yellow-300 hover:bg-yellow-200'
                        : 'bg-green-100 text-green-800 border-green-300 hover:bg-green-200' }} px-2 py-1 rounded border cursor-pointer transition-colors"
                    onclick="openMaterialModal({
                      activitycode: '{{ $detail->activitycode }}',
                      activityname: '{{ addslashes($detail->activityname ?? '') }}',
                      blok: '{{ $detail->blok }}',
                      plot: '{{ $detail->plot }}',
                      luasarea: {{ $detail->luasarea ?? 0 }},
                      herbisidagroupid: {{ $detail->herbisidagroupid ?? 'null' }},
                      herbisidagroupname: '{{ addslashes($detail->herbisidagroupname) }}'
                    })"
                    title="{{ ($isMaterialEstimated ?? false) ? 'Estimasi - klik untuk detail' : 'Klik untuk detail' }}"
                  >
                    <div class="font-bold text-[11px]">{{ $detail->herbisidagroupname }}</div>
                    <div class="text-[9px]">{{ ($isMaterialEstimated ?? false) ? '(estimasi)' : '(klik)' }}</div>
                  </div>
                @elseif($detail->usingmaterial == 1)
                  <span class="bg-gray-200 text-gray-700 px-2 py-1 rounded border border-gray-300 text-[11px] font-semibold">Ya</span>
                @else
                  <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded border border-gray-200 text-[11px]">Tidak</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-sm font-semibold">Tidak ada data detail RKH</p>
              </td>
            </tr>
          @endforelse
        </tbody>

        @if($rkhDetails->count() > 0)
        <tfoot>
          <tr class="bg-gray-100 border-t-2 border-gray-300">
            <td colspan="5" class="px-3 py-3 text-center text-xs font-bold uppercase text-gray-700">Total Luas</td>
            <td class="px-3 py-3 text-center text-sm font-bold text-gray-900">
              {{ number_format($rkhDetails->sum('luasarea'), 2) }}
            </td>
            <td class="px-3 py-3"></td>
          </tr>
        </tfoot>
        @endif
      </table>
    </div>
  </div>

  <!-- Action Buttons -->
  <div class="mt-6 flex flex-wrap justify-center gap-3">
    <button
      onclick="history.back()"
      class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-2.5 rounded-lg text-sm font-bold uppercase transition-colors flex items-center"
    >
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
      </svg>
      Kembali
    </button>

    @if($rkhHeader->status !== 'Completed' && $rkhHeader->approvalstatus != '1')
      <button
        onclick="window.location.href = '{{ route('transaction.rencanakerjaharian.edit', $rkhHeader->rkhno) }}';"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-bold uppercase transition-colors flex items-center border-2 border-blue-700"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        Edit
      </button>
    @endif

    <button
      onclick="window.print()"
      class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg text-sm font-bold uppercase transition-colors flex items-center border-2 border-green-700"
    >
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
      </svg>
      Print
    </button>
  </div>

  <!-- ============================================================ -->
  <!-- MODAL 1: Detail Material per baris (compact, no total)      -->
  <!-- ============================================================ -->
  <div x-data="{
    show: false,
    info: { activitycode:'', activityname:'', blok:'', plot:'', luasarea:0, herbisidagroupid:null, herbisidagroupname:'' },
    items: [],
    init() {
      window.addEventListener('open-material-modal', (e) => {
        this.info = {
          activitycode:       e.detail.activitycode       || '',
          activityname:       e.detail.activityname       || '',
          blok:               e.detail.blok               || '',
          plot:               e.detail.plot               || '',
          luasarea:           parseFloat(e.detail.luasarea) || 0,
          herbisidagroupid:   e.detail.herbisidagroupid   || null,
          herbisidagroupname: e.detail.herbisidagroupname || ''
        };
        const key  = this.info.activitycode + '||' + this.info.herbisidagroupid;
        const rows = window.materialData[key];

        // Filter by plot, lalu group by itemcode → sum qty per item
        const filtered = (rows || []).filter(r => r.plot === this.info.plot);
        const grouped  = {};
        filtered.forEach(r => {
          const ic = r.itemcode || '';
          if (!grouped[ic]) {
            grouped[ic] = {
              itemcode:    ic,
              itemname:    r.itemname    || '-',
              dosageperha: parseFloat(r.dosageperha) || 0,
              qty:         0,
              unit:        r.unit        || '-',
            };
          }
          grouped[ic].qty += parseFloat(r.qty) || 0;
        });
        this.items = Object.values(grouped);
        this.show = true;
      });
    }
  }" x-cloak>
    <div x-show="show" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4"
         style="display:none;" @click.self="show=false"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
      <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl flex flex-col max-h-[80vh]"
           x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
           x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

        {{-- Header --}}
        <div class="px-5 py-3 border-b border-gray-200 flex items-center justify-between"
             :class="window.isMaterialEstimated ? 'bg-yellow-50' : 'bg-green-50'">
          <div>
            <p class="text-xs font-bold uppercase tracking-wide"
               :class="window.isMaterialEstimated ? 'text-yellow-800' : 'text-green-800'"
               x-text="info.activitycode + ' — ' + info.activityname"></p>
            <p class="text-[11px] text-gray-500 mt-0.5">
              Blok-Plot: <span class="font-semibold text-gray-700" x-text="info.blok + '-' + info.plot"></span>
              &nbsp;·&nbsp; Luas: <span class="font-semibold text-gray-700" x-text="info.luasarea + ' Ha'"></span>
              &nbsp;·&nbsp; Grup: <span class="font-semibold text-green-700" x-text="info.herbisidagroupname"></span>
            </p>
            <template x-if="window.isMaterialEstimated">
              <p class="text-[10px] text-yellow-700 font-semibold mt-1">
                ⚠ Data estimasi — belum di-generate (material belum final)
              </p>
            </template>
          </div>
          <button @click="show=false" type="button" class="text-gray-400 hover:text-gray-600 p-1 rounded">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto p-4">

          {{-- Table --}}
          <div x-show="items.length > 0" x-cloak class="border border-gray-200 rounded-lg overflow-hidden">
            <table class="w-full text-xs">
              <thead class="bg-gray-800 text-white">
                <tr>
                  <th class="px-3 py-2 text-left font-semibold uppercase">Material</th>
                  <th class="px-3 py-2 text-right font-semibold uppercase">Dosis/Ha</th>
                  <th class="px-3 py-2 text-center font-semibold uppercase">Sat</th>
                  <th class="px-3 py-2 text-right font-semibold uppercase">Luas</th>
                  <th class="px-3 py-2 text-right font-semibold uppercase">Hasil</th>
                  <th class="px-3 py-2 text-right font-semibold uppercase">Pembulatan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <template x-for="(item, idx) in items" :key="idx">
                  <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2">
                      <div class="font-mono font-bold text-gray-900" x-text="item.itemcode"></div>
                      <div class="text-[10px] text-gray-500" x-text="item.itemname"></div>
                    </td>
                    <td class="px-3 py-2 text-right text-gray-700" x-text="item.dosageperha.toFixed(3)"></td>
                    <td class="px-3 py-2 text-center text-gray-600" x-text="item.unit"></td>
                    <td class="px-3 py-2 text-right text-gray-600" x-text="info.luasarea.toFixed(2)"></td>
                    <td class="px-3 py-2 text-right text-gray-500 italic"
                        x-text="(item.dosageperha * info.luasarea).toFixed(2)"></td>
                    <td class="px-3 py-2 text-right font-bold text-green-700" x-text="item.qty.toFixed(3)"></td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>

          {{-- Empty --}}
          <div x-show="items.length === 0" x-cloak class="text-center py-10 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="text-sm font-medium">Belum ada realisasi material</p>
          </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-3 bg-gray-50 border-t flex justify-end">
          <button @click="show=false" type="button"
                  class="px-5 py-1.5 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition-colors">
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================================ -->
  <!-- MODAL 2: Rekap Material — grouped by aktivitas, subtotal     -->
  <!-- ============================================================ -->
  <div x-data="{
    show: false,
    grouped: [],
    init() {
      window.addEventListener('open-rekap-material-modal', () => {
        this.grouped = window.buildRekapMaterial();
        this.show    = true;
      });
    }
  }" x-cloak>
    <div x-show="show" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4"
         style="display:none;" @click.self="show=false"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
      <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl flex flex-col max-h-[85vh]"
           x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
           x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

        {{-- Header --}}
        <div class="px-5 py-3 border-b border-gray-200 flex items-center justify-between"
             :class="window.isMaterialEstimated ? 'bg-yellow-50' : 'bg-gray-50'">
          <div>
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Rekap Penggunaan Material</h2>
            <p class="text-[11px] text-gray-500 mt-0.5">
              RKH: <span class="font-semibold">{{ $rkhHeader->rkhno }}</span>
              <template x-if="window.isMaterialEstimated">
                <span class="ml-2 text-yellow-700 font-semibold">⚠ Estimasi — angka bisa berubah setelah generate</span>
              </template>
            </p>
          </div>
          <button @click="show=false" type="button" class="text-gray-400 hover:text-gray-600 p-1 rounded">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-3">
          <template x-for="(act, ai) in grouped" :key="ai">
            <div class="border border-gray-200 rounded-lg overflow-hidden">

              {{-- Aktivitas Header --}}
              <div class="bg-gray-800 text-white px-4 py-2.5">
                <span class="text-xs font-bold uppercase tracking-wide"
                      x-text="act.activitycode + ' — ' + act.activityname"></span>
              </div>

              {{-- Items --}}
              <template x-for="(item, ii) in act.items" :key="ii">
                <div x-data="{ open: false }" class="border-b border-gray-100 last:border-b-0">

                  {{-- Item Header Row (clickable) --}}
                  <div class="flex items-center justify-between px-4 py-2 bg-gray-50 cursor-pointer hover:bg-gray-100 transition-colors select-none"
                       @click="open = !open">
                    <div class="flex items-center gap-2">
                      {{-- chevron --}}
                      <svg class="w-3 h-3 text-gray-400 transition-transform duration-200 flex-shrink-0"
                           :class="open ? 'rotate-90' : ''"
                           fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                      </svg>
                      <span class="font-mono text-xs font-bold text-gray-800" x-text="item.itemcode"></span>
                      <span class="text-xs text-gray-500" x-text="item.itemname"></span>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                      <span class="text-[10px] text-gray-400 uppercase" x-text="item.unit"></span>
                      <span class="text-xs font-bold text-green-700 min-w-[55px] text-right"
                            x-text="item.totalqty.toFixed(3)"></span>
                      <span class="text-[10px] text-gray-400 bg-gray-200 px-1.5 py-0.5 rounded"
                            x-text="item.plots.length + ' plot'"></span>
                    </div>
                  </div>

                  {{-- Plot Breakdown (dropdown) --}}
                  <div x-show="open" x-collapse class="bg-white">
                    {{-- sub-header --}}
                    <div class="grid grid-cols-12 gap-1 px-4 py-1 bg-gray-50 border-t border-gray-100 text-[10px] font-semibold text-gray-400 uppercase tracking-wide">
                      <div class="col-span-2">Plot</div>
                      <div class="col-span-2 text-right">Luas (Ha)</div>
                      <div class="col-span-2 text-right">Dosis/Ha</div>
                      <div class="col-span-3 text-right">Hasil Asli</div>
                      <div class="col-span-3 text-right">Pembulatan</div>
                    </div>
                    <template x-for="(p, pi) in item.plots" :key="pi">
                      <div class="grid grid-cols-12 gap-1 px-4 py-1.5 border-t border-gray-50 text-xs hover:bg-gray-50">
                        <div class="col-span-2 font-semibold text-gray-700" x-text="p.plot"></div>
                        <div class="col-span-2 text-right text-gray-500" x-text="p.luasarea.toFixed(2)"></div>
                        <div class="col-span-2 text-right text-gray-500" x-text="p.dosageperha.toFixed(3)"></div>
                        <div class="col-span-3 text-right text-gray-400 italic"
                             x-text="(p.dosageperha * p.luasarea).toFixed(2)"></div>
                        <div class="col-span-3 text-right font-semibold text-green-700"
                             x-text="p.qty.toFixed(3)"></div>
                      </div>
                    </template>
                    {{-- subtotal row --}}
                    <div class="grid grid-cols-12 gap-1 px-4 py-1.5 border-t-2 border-gray-200 bg-gray-50 text-xs font-bold">
                      <div class="col-span-9 text-right text-gray-600">Subtotal</div>
                      <div class="col-span-3 text-right text-green-700" x-text="item.totalqty.toFixed(3)"></div>
                    </div>
                  </div>

                </div>
              </template>

            </div>
          </template>

          {{-- Empty --}}
          <div x-show="grouped.length === 0" class="text-center py-10 text-gray-400">
            <p class="text-sm font-medium">Belum ada data material</p>
          </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-3 bg-gray-50 border-t flex justify-end">
          <button @click="show=false" type="button"
                  class="px-5 py-1.5 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition-colors">
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    window.herbisidaData        = @json($herbisidagroups ?? []);
    window.materialData         = @json($materialData ?? []);
    window.isMaterialEstimated  = @json($isMaterialEstimated ?? false);

    function openMaterialModal(data) {
      window.dispatchEvent(new CustomEvent('open-material-modal', { detail: data }));
    }

    function openRekapMaterialModal() {
      window.dispatchEvent(new CustomEvent('open-rekap-material-modal'));
    }

    /**
     * Build rekap: group by activitycode → itemcode → plots
     */
    window.buildRekapMaterial = function() {
      const byActivity = {};

      Object.keys(window.materialData).forEach(key => {
        const rows = window.materialData[key];
        if (!rows || !rows.length) return;

        const activitycode = rows[0].activitycode || key.split('||')[0];
        const activityname = rows[0].activityname || activitycode;

        if (!byActivity[activitycode]) {
          byActivity[activitycode] = { activitycode, activityname, items: {} };
        }

        rows.forEach(r => {
          const ic = r.itemcode || '';
          if (!byActivity[activitycode].items[ic]) {
            byActivity[activitycode].items[ic] = {
              itemcode: ic,
              itemname: r.itemname || '-',
              unit:     r.unit     || '-',
              totalqty: 0,
              plots:    {}
            };
          }

          const item = byActivity[activitycode].items[ic];
          item.totalqty += parseFloat(r.qty) || 0;

          // group plots: sum qty per plot
          const plot = r.plot || '-';
          if (!item.plots[plot]) {
            item.plots[plot] = {
              plot,
              luasarea:    parseFloat(r.luasarea)    || 0,
              dosageperha: parseFloat(r.dosageperha) || 0,
              qty: 0
            };
          }
          item.plots[plot].qty += parseFloat(r.qty) || 0;
        });
      });

      return Object.values(byActivity).map(act => ({
        ...act,
        items: Object.values(act.items).map(item => ({
          ...item,
          plots: Object.values(item.plots)
        }))
      }));
    };
  </script>

</x-layout>