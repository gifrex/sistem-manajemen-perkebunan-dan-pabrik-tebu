{{-- resources/views/transaction/rencanakerjaharian/lkh-edit-steps/step4-review.blade.php --}}

<div class="space-y-6">
  
  {{-- Header --}}
  <div class="text-center">
    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
      <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
    </div>
    <h3 class="text-xl font-bold text-gray-800">Review & Submit</h3>
    <p class="text-sm text-gray-600 mt-1">Verify all information before saving changes</p>
  </div>

  {{-- LKH Info Card --}}
  <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div>
        <p class="text-xs text-gray-500 mb-1">LKH Number</p>
        <p class="text-sm font-bold text-gray-800 font-mono">{{ $lkhData->lkhno }}</p>
      </div>
      <div>
        <p class="text-xs text-gray-500 mb-1">Date</p>
        <p class="text-sm font-semibold text-gray-800">{{ \Carbon\Carbon::parse($lkhData->lkhdate)->format('d M Y') }}</p>
      </div>
      <div>
        <p class="text-xs text-gray-500 mb-1">Activity</p>
        <p class="text-sm font-semibold text-gray-800">{{ $lkhData->activitycode }}</p>
      </div>
      <div>
        <p class="text-xs text-gray-500 mb-1">Worker Type</p>
        <span class="px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700">
          {{ $lkhData->jenistenagakerja == 1 ? 'Harian' : 'Borongan' }}
        </span>
      </div>
    </div>
  </div>

  {{-- Summary Stats --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    {{-- Plots --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-400 hover:shadow-md transition-all"
         @click="goToStep(1)">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 font-medium mb-1">Plots</p>
          <p class="text-2xl font-bold text-gray-800" x-text="plots.length"></p>
          <p class="text-xs text-gray-500 mt-1" x-text="getTotalLuas() + ' Ha'"></p>
        </div>
        <div class="w-10 h-10 bg-gray-50 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
          </svg>
        </div>
      </div>
    </div>

    {{-- Workers --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-400 hover:shadow-md transition-all"
         @click="goToStep(2)">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 font-medium mb-1">Workers</p>
          <p class="text-2xl font-bold text-gray-800" x-text="workers.length"></p>
          <p class="text-xs text-gray-500 mt-1" x-text="formatRupiah(calculateTotalWage())"></p>
        </div>
        <div class="w-10 h-10 bg-gray-50 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
        </div>
      </div>
    </div>

    {{-- Materials --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-400 hover:shadow-md transition-all"
         @click="goToStep(3)">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-gray-500 font-medium mb-1">Materials</p>
          <p class="text-2xl font-bold text-gray-800" x-text="materials.length"></p>
          <p class="text-xs text-gray-500 mt-1" x-text="materials.length > 0 ? getTotalUsed() + ' used' : 'None'"></p>
        </div>
        <div class="w-10 h-10 bg-gray-50 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
          </svg>
        </div>
      </div>
    </div>

    {{-- Total Wage - ACCENT COLOR --}}
    <div class="bg-gradient-to-br from-blue-600 to-blue-700 text-white rounded-lg p-4 shadow-lg">
      <div class="flex items-center justify-between">
        <div class="w-full">
          <p class="text-xs text-blue-100 font-medium mb-1">Total Wage</p>
          <p class="text-xl font-bold" x-text="formatRupiah(calculateTotalWage())"></p>
          <p class="text-[10px] text-blue-200 mt-1" x-show="jenistenagakerja == 1">
            Harian (sum of workers)
          </p>
          <p class="text-[10px] text-blue-200 mt-1" x-show="jenistenagakerja == 2">
            Borongan (<span x-text="getTotalLuas()"></span> Ha × <span x-text="formatRupiah(boronganRate)"></span>)
          </p>
        </div>
        <div class="w-10 h-10 bg-blue-500/30 rounded-lg flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
      </div>
    </div>
  </div>

  {{-- Wage Calculation Detail (Borongan only) --}}
  <div x-show="jenistenagakerja == 2 && workers.length > 0" 
       class="bg-blue-50 border border-blue-200 rounded-lg p-4">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs text-blue-700 font-medium mb-1">Borongan Calculation</p>
        <div class="flex items-center gap-2 text-sm">
          <span class="font-mono font-semibold text-gray-700" x-text="getTotalLuas() + ' Ha'"></span>
          <span class="text-gray-400">×</span>
          <span class="font-mono font-semibold text-gray-700" x-text="formatRupiah(boronganRate)"></span>
          <span class="text-gray-400">=</span>
          <span class="font-mono font-bold text-blue-700" x-text="formatRupiah(getTotalLuas() * boronganRate)"></span>
        </div>
      </div>
      <div class="text-right">
        <p class="text-xs text-blue-700 mb-1">Per Worker</p>
        <p class="text-lg font-bold text-blue-700" 
           x-text="formatRupiah((getTotalLuas() * boronganRate) / workers.length)"></p>
        <p class="text-[10px] text-gray-600">
          (<span x-text="workers.length"></span> workers)
        </p>
      </div>
    </div>
  </div>

  {{-- Keterangan Preview --}}
  <div x-show="keterangan" class="bg-amber-50 border-l-4 border-amber-400 rounded-lg p-4 shadow-sm">
    <p class="text-xs text-amber-700 font-semibold mb-1 flex items-center gap-1">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
      </svg>
      Alasan Edit
    </p>
    <p class="text-sm text-gray-700" x-text="keterangan"></p>
  </div>

  {{-- Plot Details Accordion --}}
  <div x-data="{ openPlots: false }" class="border border-gray-200 rounded-lg overflow-hidden shadow-sm">
    <button @click="openPlots = !openPlots" type="button"
      class="w-full px-4 py-3 bg-white hover:bg-gray-50 flex items-center justify-between transition-colors">
      <span class="text-sm font-semibold text-gray-700 flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
        </svg>
        Plot Details (<span x-text="plots.length"></span>)
      </span>
      <svg class="w-5 h-5 text-gray-500 transition-transform duration-200" :class="openPlots && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
      </svg>
    </button>
    <div x-show="openPlots" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="border-t border-gray-200 bg-gray-50">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-white">
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-600">Blok</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-600">Plot</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-600">Luas RKH</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-600">Luas Hasil</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-600">Luas Sisa</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <template x-for="plot in plots" :key="plot.blok + plot.plot">
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 font-mono text-gray-700" x-text="plot.blok"></td>
                <td class="px-4 py-2 font-mono text-gray-700" x-text="plot.plot"></td>
                <td class="px-4 py-2 text-right font-mono text-gray-700" x-text="parseFloat(plot.luasrkh).toFixed(2) + ' Ha'"></td>
                <td class="px-4 py-2 text-right font-mono text-gray-700" x-text="parseFloat(plot.luashasil).toFixed(2) + ' Ha'"></td>
                <td class="px-4 py-2 text-right font-mono text-gray-600" x-text="parseFloat(plot.luassisa).toFixed(2) + ' Ha'"></td>
              </tr>
            </template>
          </tbody>
          <tfoot class="bg-blue-50 border-t-2 border-blue-200">
            <tr>
              <td colspan="3" class="px-4 py-2 text-right font-semibold text-gray-700">Total:</td>
              <td class="px-4 py-2 text-right font-mono font-bold text-blue-700" x-text="getTotalLuas() + ' Ha'"></td>
              <td class="px-4 py-2 text-right font-mono font-bold text-gray-700" x-text="getTotalLuasSisa() + ' Ha'"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  {{-- Worker Details Accordion --}}
  <div x-data="{ openWorkers: false }" class="border border-gray-200 rounded-lg overflow-hidden shadow-sm">
    <button @click="openWorkers = !openWorkers" type="button"
      class="w-full px-4 py-3 bg-white hover:bg-gray-50 flex items-center justify-between transition-colors">
      <span class="text-sm font-semibold text-gray-700 flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        Worker Details (<span x-text="workers.length"></span>)
      </span>
      <svg class="w-5 h-5 text-gray-500 transition-transform duration-200" :class="openWorkers && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
      </svg>
    </button>
    <div x-show="openWorkers" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="border-t border-gray-200 bg-gray-50">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-white">
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-600">Worker</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-600">NIK</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-600" x-show="jenistenagakerja == 1">Hours</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-600">Total Upah</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <template x-for="worker in workers" :key="worker.tenagakerjaid">
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 text-gray-700" x-text="getWorkerName(worker.tenagakerjaid)"></td>
                <td class="px-4 py-2 font-mono text-gray-600" x-text="worker.nik || '-'"></td>
                <td class="px-4 py-2 text-right font-mono text-gray-600" x-show="jenistenagakerja == 1" 
                    x-text="parseFloat(worker.totaljamkerja || 0).toFixed(1) + ' hrs'"></td>
                <td class="px-4 py-2 text-right font-mono font-semibold text-gray-800" 
                    x-text="jenistenagakerja == 1 ? formatRupiah(worker.totalupah) : formatRupiah((getTotalLuas() * boronganRate) / workers.length)"></td>
              </tr>
            </template>
          </tbody>
          <tfoot class="bg-blue-50 border-t-2 border-blue-200">
            <tr>
              <td :colspan="jenistenagakerja == 1 ? 3 : 2" class="px-4 py-2 text-right font-semibold text-gray-700">Total:</td>
              <td class="px-4 py-2 text-right font-mono font-bold text-blue-700" x-text="formatRupiah(calculateTotalWage())"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  {{-- ✅ NEW: Material Details Accordion --}}
  <div x-data="{ openMaterials: false }" class="border border-gray-200 rounded-lg overflow-hidden shadow-sm" x-show="materials.length > 0">
    <button @click="openMaterials = !openMaterials" type="button"
      class="w-full px-4 py-3 bg-white hover:bg-gray-50 flex items-center justify-between transition-colors">
      <span class="text-sm font-semibold text-gray-700 flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>
        Material Details (<span x-text="materials.length"></span>)
      </span>
      <svg class="w-5 h-5 text-gray-500 transition-transform duration-200" :class="openMaterials && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
      </svg>
    </button>
    <div x-show="openMaterials" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="border-t border-gray-200 bg-gray-50">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-white">
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-600">Plot</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-600">Item Code</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-600">Item Name</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-600">Received</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-600 bg-blue-50">Used</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-600 bg-green-50">Remaining</th>
              <th class="px-4 py-2 text-center text-xs font-medium text-gray-600">Unit</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <template x-for="material in materials" :key="material.id">
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 font-mono text-gray-700 uppercase" x-text="material.plot"></td>
                <td class="px-4 py-2 font-mono text-gray-700" x-text="material.itemcode"></td>
                <td class="px-4 py-2 text-gray-700" x-text="material.itemname"></td>
                <td class="px-4 py-2 text-right font-mono text-gray-700" x-text="parseFloat(material.qtyditerima || 0).toFixed(3)"></td>
                <td class="px-4 py-2 text-right font-mono font-semibold text-blue-700 bg-blue-50" x-text="parseFloat(material.qtydigunakan || 0).toFixed(3)"></td>
                <td class="px-4 py-2 text-right font-mono font-semibold bg-green-50"
                    :class="(parseFloat(material.qtyditerima) - parseFloat(material.qtydigunakan)) < 0 ? 'text-red-600' : 'text-green-700'"
                    x-text="(parseFloat(material.qtyditerima || 0) - parseFloat(material.qtydigunakan || 0)).toFixed(3)"></td>
                <td class="px-4 py-2 text-center text-gray-700" x-text="material.satuan"></td>
              </tr>
            </template>
          </tbody>
          <tfoot class="bg-orange-50 border-t-2 border-orange-200">
            <tr>
              <td colspan="3" class="px-4 py-2 text-right font-semibold text-gray-700">Total:</td>
              <td class="px-4 py-2 text-right font-mono font-bold text-gray-700" x-text="getTotalReceived()"></td>
              <td class="px-4 py-2 text-right font-mono font-bold text-blue-700 bg-blue-50" x-text="getTotalUsed()"></td>
              <td class="px-4 py-2 text-right font-mono font-bold text-green-700 bg-green-50" x-text="getTotalRemaining()"></td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  {{-- Validation Checklist --}}
  <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
    <p class="text-sm font-semibold text-gray-700 mb-3">Validation Checklist</p>
    <div class="space-y-2">
      <div class="flex items-center gap-2">
        <div class="w-5 h-5 rounded-full flex items-center justify-center"
             :class="keterangan.trim() !== '' ? 'bg-blue-600' : 'bg-gray-300'">
          <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <span class="text-sm text-gray-700">
          <strong class="text-red-600">*</strong> Alasan Edit is required
        </span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-5 h-5 rounded-full flex items-center justify-center"
             :class="plots.length > 0 ? 'bg-blue-600' : 'bg-gray-300'">
          <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <span class="text-sm text-gray-700">
          At least 1 plot required (<span class="font-semibold" x-text="plots.length"></span> plots selected)
        </span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-5 h-5 rounded-full flex items-center justify-center"
             :class="workers.length > 0 ? 'bg-blue-600' : 'bg-gray-300'">
          <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <span class="text-sm text-gray-700">
          At least 1 worker required (<span class="font-semibold" x-text="workers.length"></span> workers assigned)
        </span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-5 h-5 rounded-full flex items-center justify-center"
             :class="allWorkersHaveSelection() ? 'bg-blue-600' : 'bg-gray-300'">
          <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <span class="text-sm text-gray-700">
          All workers must have a name selected
        </span>
      </div>
      <div class="flex items-center gap-2" x-show="materials.length > 0">
        <div class="w-5 h-5 rounded-full flex items-center justify-center"
             :class="!hasMaterialValidationError() ? 'bg-blue-600' : 'bg-red-500'">
          <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <span class="text-sm" :class="!hasMaterialValidationError() ? 'text-gray-700' : 'text-red-600'">
          Material quantities must be valid (Used ≤ Received)
        </span>
      </div>
    </div>
  </div>
</div>