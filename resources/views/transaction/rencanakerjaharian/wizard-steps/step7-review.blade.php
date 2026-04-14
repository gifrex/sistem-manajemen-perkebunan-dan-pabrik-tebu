{{-- resources/views/transaction/rencanakerjaharian/wizard-steps/step7-review.blade.php --}}

<div class="max-w-5xl mx-auto">
  
  {{-- Document Header --}}
  <div class="bg-white border-2 border-gray-800 rounded-lg overflow-hidden">
    
    {{-- Title Section --}}
    <div class="bg-gray-800 text-white px-6 py-4 border-b-2 border-gray-800">
      <h2 class="text-xl font-bold text-center">RENCANA KERJA HARIAN (RKH)</h2>
      <p class="text-center text-sm text-gray-300 mt-1">Daily Work Plan Review</p>
    </div>

    {{-- Document Info --}}
    <div class="p-6 border-b border-gray-300">
      <div class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm">
        <div class="flex">
          <span class="font-semibold text-gray-700 w-32">Mandor:</span>
          <span class="text-gray-900">
            {{-- ✅ Support both Create & Edit mode --}}
            {{ $selectedMandor->name ?? $rkhHeader->mandor_nama ?? '-' }}
          </span>
        </div>
        <div class="flex">
          <span class="font-semibold text-gray-700 w-32">Tanggal:</span>
          <span class="text-gray-900">
            {{-- ✅ Support both Create & Edit mode --}}
            @if(isset($selectedDate))
              {{ \Carbon\Carbon::parse($selectedDate)->format('d F Y') }}
            @elseif(isset($rkhHeader))
              {{ \Carbon\Carbon::parse($rkhHeader->rkhdate)->format('d F Y') }}
            @else
              -
            @endif
          </span>
        </div>
        <div class="flex">
          <span class="font-semibold text-gray-700 w-32">Total Aktivitas:</span>
          <span class="text-gray-900 font-bold" x-text="Object.keys(selectedActivities).length"></span>
        </div>
        <div class="flex">
          <span class="font-semibold text-gray-700 w-32">Total Plot:</span>
          <span class="text-gray-900 font-bold" x-text="getTotalPlotsCount()"></span>
        </div>
        <div class="flex">
          <span class="font-semibold text-gray-700 w-32">Total Luas:</span>
          <span class="text-gray-900 font-bold" x-text="getTotalLuasAll() + ' Ha'"></span>
        </div>
        <div class="flex">
          <span class="font-semibold text-gray-700 w-32">Total Pekerja:</span>
          <span class="text-gray-900 font-bold" x-text="getTotalWorkers('total') + ' orang'"></span>
        </div>
      </div>
    </div>

    {{-- Activities Detail --}}
    <div class="p-6">
      <template x-for="([actCode, activity], actIndex) in Object.entries(selectedActivities)" :key="actCode">
        <div class="mb-6 last:mb-0">
          
          {{-- Activity Header --}}
          <div class="bg-gray-100 border-l-4 border-gray-700 px-4 py-2 mb-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <span class="text-lg font-bold text-gray-800" x-text="`${actIndex + 1}.`"></span>
                <div>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-gray-800" x-text="actCode"></span>
                    <span class="text-gray-400">•</span>
                    <span class="text-sm font-semibold text-gray-700" x-text="activity.name"></span>
                  </div>
                  <div class="flex items-center gap-3 mt-1 text-xs text-gray-600">
                    <span x-text="`Jenis: ${getJenisLabel(activity.jenistenagakerja)}`"></span>
                    <span x-show="activity.usingmaterial == 1">• Material: Ya</span>
                    <span x-show="activity.usingvehicle == 1">• Kendaraan: Ya</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Blok Activity (non-plot) --}}
          <div x-show="activity.isblokactivity == 1 && (blokActivityAssignments[actCode] || []).length > 0" class="mb-4">
              <table class="w-full text-sm border-collapse">
                  <thead>
                      <tr class="bg-gray-50 border-y border-gray-300">
                          <th class="text-left px-3 py-2 font-semibold text-gray-700 w-12">No</th>
                          <th class="text-left px-3 py-2 font-semibold text-gray-700">Blok</th>
                      </tr>
                  </thead>
                  <tbody>
                      <template x-for="(blok, bIndex) in (blokActivityAssignments[actCode] || [])" :key="`${actCode}-blok-${blok}`">
                          <tr class="border-b border-gray-200 hover:bg-gray-50">
                              <td class="px-3 py-2 text-gray-600" x-text="bIndex + 1"></td>
                              <td class="px-3 py-2 font-mono font-semibold text-gray-800" x-text="blok"></td>
                          </tr>
                      </template>
                  </tbody>
              </table>
          </div>

          {{-- Plot Table --}}
          <div class="mb-4">
            <table class="w-full text-sm border-collapse">
              <thead>
                <tr class="bg-gray-50 border-y border-gray-300">
                  <th class="text-left px-3 py-2 font-semibold text-gray-700 w-12">No</th>
                  <th class="text-left px-3 py-2 font-semibold text-gray-700">Plot</th>
                  <th class="text-left px-3 py-2 font-semibold text-gray-700">Status</th>
                  <th class="text-right px-3 py-2 font-semibold text-gray-700 w-24">Luas (Ha)</th>
                  <th class="text-left px-3 py-2 font-semibold text-gray-700" x-show="activity.usingmaterial == 1">Material</th>
                  <th class="text-left px-3 py-2 font-semibold text-gray-700" x-show="window.PANEN_ACTIVITIES.includes(actCode)">Batch</th>
                </tr>
              </thead>
              <tbody>
                <template x-for="(plot, pIndex) in (plotAssignments[actCode] || [])" :key="`${actCode}-${plot.blok}-${plot.plot}`">
                  <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="px-3 py-2 text-gray-600" x-text="pIndex + 1"></td>
                    <td class="px-3 py-2">
                      <span class="font-mono font-semibold text-gray-800" x-text="`${plot.blok}-${plot.plot}`"></span>
                    </td>
                    <td class="px-3 py-2">
                      <span 
                        x-show="plot.lifecyclestatus"
                        class="inline-block px-2 py-0.5 text-[10px] font-bold rounded"
                        :class="{
                          'bg-yellow-100 text-yellow-800': plot.lifecyclestatus === 'PC',
                          'bg-green-100 text-green-800': plot.lifecyclestatus === 'RC1',
                          'bg-blue-100 text-blue-800': plot.lifecyclestatus === 'RC2',
                          'bg-purple-100 text-purple-800': plot.lifecyclestatus === 'RC3'
                        }"
                        x-text="plot.lifecyclestatus">
                      </span>
                      <span x-show="!plot.lifecyclestatus" class="text-gray-400 text-xs">-</span>
                    </td>
                    <td class="px-3 py-2 text-right font-semibold text-gray-800 tabular-nums" 
                        x-text="(luasConfirmed[`${actCode}_${plot.blok}_${plot.plot}`] || 0)"></td>
                    <td class="px-3 py-2 text-xs" x-show="activity.usingmaterial == 1">
                      <span 
                        x-show="materials[`${actCode}_${plot.blok}_${plot.plot}`]"
                        class="text-gray-700"
                        x-text="materials[`${actCode}_${plot.blok}_${plot.plot}`]?.groupname">
                      </span>
                      <span x-show="!materials[`${actCode}_${plot.blok}_${plot.plot}`]" class="text-gray-400">-</span>
                    </td>
                    <td class="px-3 py-2 font-mono text-xs" x-show="window.PANEN_ACTIVITIES.includes(actCode)">
                      <span x-show="plot.batchno" x-text="plot.batchno"></span>
                      <span x-show="!plot.batchno" class="text-gray-400">-</span>
                    </td>
                  </tr>
                </template>
                <tr class="bg-gray-50 border-t-2 border-gray-300 font-semibold">
                  <td colspan="3" class="px-3 py-2 text-right text-gray-700">Subtotal:</td>
                  <td class="px-3 py-2 text-right text-gray-800 tabular-nums" x-text="getTotalLuasForActivityConfirmed(actCode)"></td>
                  <td x-show="activity.usingmaterial == 1"></td>
                  <td x-show="window.PANEN_ACTIVITIES.includes(actCode)"></td>
                </tr>
              </tbody>
            </table>
          </div>

          {{-- Resources Section --}}
          <div class="grid grid-cols-2 gap-4 pl-4">
            
            {{-- Manpower --}}
            <div class="border border-gray-200 rounded p-3 bg-gray-50">
              <div class="flex items-center gap-2 mb-2 pb-2 border-b border-gray-300">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="text-xs font-bold text-gray-700">TENAGA KERJA</span>
              </div>
              <div class="grid grid-cols-3 gap-2 text-xs">
                <div>
                  <span class="text-gray-600">Laki-laki:</span>
                  <span class="font-bold text-gray-800 ml-1" x-text="workers[actCode]?.laki || 0"></span>
                </div>
                <div>
                  <span class="text-gray-600">Perempuan:</span>
                  <span class="font-bold text-gray-800 ml-1" x-text="workers[actCode]?.perempuan || 0"></span>
                </div>
                <div>
                  <span class="text-gray-600">Total:</span>
                  <span class="font-bold text-gray-800 ml-1" x-text="workers[actCode]?.total || 0"></span>
                </div>
              </div>
            </div>

            {{-- Vehicles --}}
            <div 
              x-show="activity.usingvehicle == 1"
              class="border border-gray-200 rounded p-3 bg-gray-50">
              <div class="flex items-center gap-2 mb-2 pb-2 border-b border-gray-300">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                </svg>
                <span class="text-xs font-bold text-gray-700">KENDARAAN</span>
                <span class="ml-auto text-xs text-gray-600" x-text="`${(vehicles[actCode] || []).length} unit`"></span>
              </div>
              <div class="space-y-1.5 text-xs">
                <template x-for="(vehicle, vIdx) in (vehicles[actCode] || [])" :key="`${actCode}-v-${vIdx}`">
                  <div class="flex items-start gap-2">
                    <span class="text-gray-500" x-text="`${vIdx + 1}.`"></span>
                    <div class="flex-1">
                      <div class="font-semibold text-gray-800" x-text="vehicle.nokendaraan"></div>
                      <div class="text-gray-600">
                        <span x-text="vehicle.operator_name"></span>
                        <span x-show="vehicle.helperid" x-text="`+ ${vehicle.helper_name}`"></span>
                      </div>
                    </div>
                  </div>
                </template>
                <div x-show="!(vehicles[actCode] || []).length" class="text-gray-400 text-center py-1">
                  Tidak ada kendaraan
                </div>
              </div>
            </div>

          </div>

        </div>
      </template>
    </div>

    {{-- Grand Total --}}
    <div class="border-t-2 border-gray-800 bg-gray-100 px-6 py-4">
      <div class="grid grid-cols-3 gap-8 text-sm">
        <div class="text-center">
          <div class="text-gray-600 font-medium mb-1">Total Luas Kerja</div>
          <div class="text-2xl font-bold text-gray-800" x-text="getTotalLuasAll()"></div>
          <div class="text-xs text-gray-500">Hektar</div>
        </div>
        <div class="text-center">
          <div class="text-gray-600 font-medium mb-1">Total Pekerja</div>
          <div class="text-2xl font-bold text-gray-800" x-text="getTotalWorkers('total')"></div>
          <div class="text-xs text-gray-500">
            <span x-text="`L: ${getTotalWorkers('laki')}`"></span> • 
            <span x-text="`P: ${getTotalWorkers('perempuan')}`"></span>
          </div>
        </div>
        <div class="text-center">
          <div class="text-gray-600 font-medium mb-1">Total Kendaraan</div>
          <div class="text-2xl font-bold text-gray-800" x-text="getTotalVehiclesAssigned()"></div>
          <div class="text-xs text-gray-500">Unit</div>
        </div>
      </div>
    </div>

  </div>

  {{-- Action Buttons --}}
  <div class="mt-6 flex justify-center gap-3">
    <button
      type="button"
      @click="currentStep = 1"
      class="px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
      </svg>
      Edit dari Awal
    </button>
  </div>

</div>