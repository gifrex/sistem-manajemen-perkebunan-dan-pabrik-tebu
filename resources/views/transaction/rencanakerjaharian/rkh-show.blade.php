{{--resources\views\input\rencanakerjaharian\rkh-show.blade.php--}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <!-- HEADER CONTENT - GRAYSCALE DESIGN -->
  <div class="bg-white rounded-xl p-6 mb-6 border-2 border-gray-300 shadow-sm">
    
    <!-- TOP ROW: No RKH + Status Badges -->
    <div class="flex flex-wrap items-start justify-between gap-4 mb-5 pb-5 border-b-2 border-gray-200">
      <!-- No RKH -->
      <div>
        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">No RKH</label>
        <p class="text-4xl font-mono font-bold text-gray-900 tracking-wide">
          {{ $rkhHeader->rkhno ?? '-' }}
        </p>
      </div>

      <!-- Status Badges Group -->
      <div class="flex flex-wrap gap-3">
        @php
          // Status Approval
          $approvalStatus = 'Waiting';
          $approvalClass = 'bg-yellow-100 text-yellow-800 border-yellow-300';
          $approvalCount = '';

          if (isset($rkhHeader->jumlahapproval) && $rkhHeader->jumlahapproval > 0) {
            $approvedCount = 0;
            if ($rkhHeader->approval1flag === '1') $approvedCount++;
            if ($rkhHeader->approval2flag === '1') $approvedCount++;
            if ($rkhHeader->approval3flag === '1') $approvedCount++;

            if ($rkhHeader->approval1flag === '0' || $rkhHeader->approval2flag === '0' || $rkhHeader->approval3flag === '0') {
              $approvalStatus = 'Declined';
              $approvalClass = 'bg-red-100 text-red-800 border-red-300';
              if ($rkhHeader->approval1flag === '0') {
                $approvalStatus = 'Declined L1';
              } elseif ($rkhHeader->approval2flag === '0') {
                $approvalStatus = 'Declined L2';
              } elseif ($rkhHeader->approval3flag === '0') {
                $approvalStatus = 'Declined L3';
              }
            } elseif ($approvedCount === $rkhHeader->jumlahapproval) {
              $approvalStatus = 'Approved';
              $approvalClass = 'bg-green-100 text-green-800 border-green-300';
            } else {
              $approvalCount = " ({$approvedCount}/{$rkhHeader->jumlahapproval})";
              $approvalStatus = "Waiting{$approvalCount}";
            }
          } else {
            $approvalStatus = 'No Approval';
            $approvalClass = 'bg-gray-200 text-gray-700 border-gray-400';
          }

          $rkhStatus = $rkhHeader->status === 'Completed' ? 'Completed' : 'In Progress';
          $rkhStatusClass = $rkhHeader->status === 'Completed' ? 'bg-green-100 text-green-800 border-green-300' : 'bg-blue-100 text-blue-800 border-blue-300';
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
      
      <!-- LEFT COLUMN: Basic Info (7 cols) -->
      <div class="lg:col-span-7 space-y-4">
        
        <!-- Mandor & Date -->
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-gray-50 rounded-lg p-4 border border-gray-300">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Mandor</label>
            <div class="text-sm font-bold text-gray-900">
              {{ $rkhHeader->mandorid ?? '-' }}
            </div>
            <div class="text-xs text-gray-600 mt-0.5">
              {{ $rkhHeader->mandor_nama ?? '-' }}
            </div>
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
          <div class="text-sm text-gray-700">
            {{ $rkhHeader->keterangan }}
          </div>
        </div>
        @endif

        <!-- Compact Summary Row -->
        <div class="grid grid-cols-3 gap-3">
          
          <!-- Absen Summary -->
          <div class="bg-gray-50 rounded-lg p-3 border border-gray-300">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-2 h-2 bg-gray-500 rounded-full"></div>
              <h4 class="text-xs font-bold text-gray-800 uppercase">Absen</h4>
            </div>
            @php
              $absenSummary = collect($absentenagakerja ?? [])->where('mandorid', $rkhHeader->mandorid);
              $lakiCount = $absenSummary->where('gender', 'L')->count();
              $perempuanCount = $absenSummary->where('gender', 'P')->count();
              $totalCount = $lakiCount + $perempuanCount;
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

          <!-- Workers Summary -->
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

          <!-- Kendaraan Summary -->
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

      <!-- RIGHT COLUMN: Details (5 cols) -->
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
                    <div class="text-xs font-bold text-gray-900 truncate" title="{{ $worker->activitycode }} - {{ $worker->activityname }}">
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
                  <span class="text-xs font-bold text-gray-900">{{ $activityCode }} - {{ $vehicles->first()->activityname ?? '' }}</span>
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
      <button onclick="window.print()" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-xs font-bold uppercase transition-colors flex items-center">
        <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
        </svg>
        Print
      </button>
    </div>

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
              // Calculate luas sisa from total_sudah_dikerjakan
              $luasPlot = $detail->luasarea ?? 0;
              $totalSudahDikerjakan = $detail->total_sudah_dikerjakan ?? 0;
              $luasSisa = $luasPlot - $totalSudahDikerjakan;
            @endphp
            <tr class="hover:bg-gray-50 transition-colors">
              <td class="px-3 py-3 text-sm text-center font-bold text-gray-700">{{ $index + 1 }}</td>

              <td class="px-3 py-3 text-sm">
                <div class="font-bold text-gray-900">{{ $detail->activitycode ?? '-' }}</div>
                <div class="text-xs text-gray-600 line-clamp-1">{{ $detail->activityname ?? '-' }}</div>
              </td>

              <td class="px-3 py-3 text-sm text-center font-bold text-gray-900">{{ $detail->blok ?? '-' }}</td>

              <td class="px-3 py-3 text-sm text-center font-bold text-gray-900">{{ $detail->plot ?? '-' }}</td>

              {{-- ✅ NEW: Info Plot (untuk semua activity) --}}
              <td class="px-3 py-3 text-xs">
                @if($detail->batch_number && $detail->batch_lifecycle)
                  {{-- Panen Activity: Show batch info --}}
                  <div class="space-y-1">
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold border
                      {{ $detail->batch_lifecycle === 'PC' ? 'bg-yellow-100 text-yellow-800 border-yellow-300' : '' }}
                      {{ $detail->batch_lifecycle === 'RC1' ? 'bg-green-100 text-green-800 border-green-300' : '' }}
                      {{ $detail->batch_lifecycle === 'RC2' ? 'bg-blue-100 text-blue-800 border-blue-300' : '' }}
                      {{ $detail->batch_lifecycle === 'RC3' ? 'bg-purple-100 text-purple-800 border-purple-300' : '' }}">
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
                  {{-- Non-Panen Activity: Show luas info --}}
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
                    class="bg-green-100 text-green-800 px-2 py-1 rounded border border-green-300 cursor-pointer hover:bg-green-200 transition-colors"
                    onclick="openMaterialModal({
                      activitycode: '{{ $detail->activitycode }}',
                      activityname: '{{ addslashes($detail->activityname ?? '') }}',
                      blok: '{{ $detail->blok }}',
                      plot: '{{ $detail->plot }}',
                      luasarea: {{ $detail->luasarea ?? 0 }},
                      herbisidagroupid: {{ $detail->herbisidagroupid ?? 'null' }},
                      herbisidagroupname: '{{ addslashes($detail->herbisidagroupname) }}'
                    })"
                    title="Klik untuk detail"
                  >
                    <div class="font-bold text-[11px]">{{ $detail->herbisidagroupname }}</div>
                    <div class="text-[9px]">(klik)</div>
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
      onclick="window.location.href = '{{ route('transaction.rencanakerjaharian.index') }}';"
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

  <!-- Material Modal -->
  <div x-data="{
    showMaterialModal: false,
    currentMaterial: {
      activitycode: '',
      activityname: '',
      blok: '',
      plot: '',
      luasarea: 0,
      herbisidagroupid: null,
      herbisidagroupname: ''
    },
    materialItems: [],

    init() {
      window.addEventListener('open-material-modal', (e) => {
        this.currentMaterial = {
          activitycode: e.detail.activitycode || '',
          activityname: e.detail.activityname || '',
          blok: e.detail.blok || '',
          plot: e.detail.plot || '',
          luasarea: parseFloat(e.detail.luasarea) || 0,
          herbisidagroupid: e.detail.herbisidagroupid || null,
          herbisidagroupname: e.detail.herbisidagroupname || ''
        };
        this.loadMaterialItems();
        this.showMaterialModal = true;
      });
    },

    loadMaterialItems() {
      if (!this.currentMaterial.herbisidagroupid || !window.herbisidaData) {
        this.materialItems = [];
        return;
      }

      this.materialItems = window.herbisidaData
        .filter(item => 
          item.herbisidagroupid == this.currentMaterial.herbisidagroupid &&
          item.activitycode === this.currentMaterial.activitycode
        )
        .map(item => ({
          itemcode: item.itemcode || '',
          itemname: item.itemname || 'Unknown',
          dosageperha: parseFloat(item.dosageperha) || 0,
          measure: item.measure || '-'
        }));
    },

    // Add rounding to 0.25
    getTotalQty() {
      if (this.materialItems.length === 0) return '-';
      
      const total = this.materialItems.reduce((sum, item) => {
        const qtyRaw = parseFloat(item.dosageperha) * parseFloat(this.currentMaterial.luasarea);
        const qtyRounded = qtyRaw > 0 ? Math.max(0.25, Math.round(qtyRaw / 0.25) * 0.25) : 0;
        return sum + qtyRounded;
      }, 0);
      
      return total.toFixed(2);
    },

    // Helper for per-item qty
    getItemQty(dosageperha) {
      const qtyRaw = parseFloat(dosageperha) * parseFloat(this.currentMaterial.luasarea);
      const qtyRounded = qtyRaw > 0 ? Math.max(0.25, Math.round(qtyRaw / 0.25) * 0.25) : 0;
      return qtyRounded.toFixed(2);
    }
  }" x-cloak>
    
    <!-- Modal Overlay -->
    <div
      x-show="showMaterialModal"
      x-cloak
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4"
      style="display: none;"
      @click.self="showMaterialModal = false"
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
    >
      <!-- Modal Content -->
      <div class="bg-white rounded-lg shadow-2xl w-full max-w-3xl max-h-[85vh] flex flex-col"
          x-transition:enter="transition ease-out duration-300"
          x-transition:enter-start="opacity-0 transform scale-95"
          x-transition:enter-end="opacity-100 transform scale-100"
          x-transition:leave="transition ease-in duration-200"
          x-transition:leave-start="opacity-100 transform scale-100"
          x-transition:leave-end="opacity-0 transform scale-95">
        
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Detail Material</h2>
            <button @click="showMaterialModal = false" type="button" class="text-gray-400 hover:text-gray-600 rounded-full p-2 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        {{-- Content --}}
        <div class="flex-1 overflow-y-auto p-6">
          {{-- Activity Info --}}
          <div class="bg-blue-50 rounded-lg p-4 mb-4 border border-blue-200">
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span class="font-semibold text-gray-700">Aktivitas:</span>
                <span class="ml-2 text-gray-900" x-text="currentMaterial.activitycode + ' - ' + currentMaterial.activityname"></span>
              </div>
              <div>
                <span class="font-semibold text-gray-700">Blok-Plot:</span>
                <span class="ml-2 text-gray-900" x-text="currentMaterial.blok + '-' + currentMaterial.plot"></span>
              </div>
              <div>
                <span class="font-semibold text-gray-700">Luas Area:</span>
                <span class="ml-2 text-gray-900" x-text="currentMaterial.luasarea + ' Ha'"></span>
              </div>
              <div>
                <span class="font-semibold text-gray-700">Grup Material:</span>
                <span class="ml-2 text-green-800 font-semibold" x-text="currentMaterial.herbisidagroupname"></span>
              </div>
            </div>
          </div>

          {{-- Material Items Table --}}
          <div class="border border-gray-200 rounded-lg overflow-hidden">
            <table class="w-full">
              <thead class="bg-gray-800 text-white">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Kode</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Nama Material</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide">Dosis/Ha</th>
                  <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide">Satuan</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide">Total Qty</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <template x-for="item in materialItems" :key="item.itemcode">
                  <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 text-sm font-mono text-gray-900" x-text="item.itemcode"></td>
                    <td class="px-4 py-3 text-sm text-gray-900" x-text="item.itemname"></td>
                    <td class="px-4 py-3 text-sm text-right text-gray-700" x-text="item.dosageperha.toFixed(2)"></td>
                    <td class="px-4 py-3 text-sm text-center text-gray-700" x-text="item.measure"></td>
                    <td class="px-4 py-3 text-sm text-right font-semibold text-green-700" 
                        x-text="getItemQty(item.dosageperha)"></td>
                  </tr>
                </template>
              </tbody>
              <tfoot class="bg-gray-100 border-t-2 border-gray-300">
                <tr>
                  <td colspan="4" class="px-4 py-3 text-sm font-bold text-gray-700 text-right">
                    Total Material untuk <span x-text="currentMaterial.luasarea"></span> Ha:
                  </td>
                  <td class="px-4 py-3 text-sm font-bold text-green-700 text-right" x-text="getTotalQty()"></td>
                </tr>
              </tfoot>
            </table>
          </div>

          {{-- Empty State --}}
          <template x-if="materialItems.length === 0">
            <div class="text-center py-8 text-gray-400">
              <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
              </svg>
              <p class="text-sm font-medium">Tidak ada material untuk grup ini</p>
            </div>
          </template>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 bg-gray-50 border-t flex justify-end">
          <button @click="showMaterialModal = false" type="button" 
                  class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    window.herbisidaData = @json($herbisidagroups ?? []);
    
    function openMaterialModal(data) {
      window.dispatchEvent(new CustomEvent('open-material-modal', { detail: data }));
    }
  </script>

</x-layout>