{{-- resources/views/transaction/rencanakerjaharian/lkh-edit-steps/step1-plots.blade.php --}}

<div class="space-y-4">
  
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
        </svg>
        Plot Details
      </h3>
      <p class="text-xs text-gray-600 mt-1">Select plots and confirm area measurements</p>
    </div>
    <div class="flex items-center gap-4">
      <div class="text-right">
        <div class="text-xl font-bold text-blue-600" x-text="plots.length"></div>
        <div class="text-[10px] text-gray-500">Selected Plots</div>
      </div>
      <div class="text-right">
        <div class="text-xl font-bold text-green-600" x-text="getTotalLuas() + ' Ha'"></div>
        <div class="text-[10px] text-gray-500">Total Luas Hasil</div>
      </div>
    </div>
  </div>

  {{-- Activity Info Card --}}
  <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
      </div>
      <div class="flex-1">
        <p class="text-xs text-gray-500">Activity</p>
        <p class="text-sm font-semibold text-gray-800">
          <span class="font-mono bg-blue-100 px-1.5 py-0.5 rounded text-blue-700">{{ $lkhData->activitycode }}</span>
          {{ Str::limit($lkhData->activityname, 50) }}
        </p>
      </div>
    </div>
  </div>

  {{-- Alasan Edit Section (REQUIRED) --}}
  <div class="bg-white border border-gray-300 rounded-lg p-4">
    <label class="block text-sm font-semibold text-gray-700 mb-2">
      Alasan Edit <span class="text-red-500">*</span>
    </label>
    <textarea 
      x-model="keterangan"
      rows="3"
      maxlength="500"
      placeholder="Jelaskan alasan perubahan data LKH ini (wajib diisi)..."
      class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-sm resize-none"
      :class="!keterangan.trim() ? 'border-red-300 bg-red-50' : 'border-gray-300'"
    ></textarea>
    <div class="flex items-center justify-between mt-2">
      <p class="text-xs text-gray-500">
        Alasan ini akan tersimpan sebagai audit trail
      </p>
      <span class="text-xs text-gray-500">
        <span x-text="keterangan.length"></span>/500
      </span>
    </div>
  </div>

  {{-- Plot Selection Panel --}}
  <div class="grid grid-cols-12 gap-4">
    
    {{-- Left Panel: Blok & Plot Selector --}}
    <div class="col-span-5 border border-gray-200 rounded-lg overflow-hidden bg-white">
      
      {{-- Blok Tabs --}}
      <div class="bg-gray-50 border-b border-gray-200 px-3 py-2">
        <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide">
          <span class="text-xs font-medium text-gray-600 flex-shrink-0">Blok:</span>
          <template x-for="blok in availableBloks" :key="blok">
            <button type="button" @click="selectedBlok = blok; plotSearch = ''"
              class="px-3 py-1.5 text-xs font-medium rounded transition-all flex-shrink-0"
              :class="selectedBlok === blok ? 'bg-blue-500 text-white shadow' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'">
              <span x-text="blok"></span>
              <span x-show="getSelectedPlotsInBlok(blok).length > 0"
                class="ml-1 inline-flex items-center justify-center w-4 h-4 text-[9px] font-bold rounded-full"
                :class="selectedBlok === blok ? 'bg-white text-blue-600' : 'bg-blue-100 text-blue-600'"
                x-text="getSelectedPlotsInBlok(blok).length"></span>
            </button>
          </template>
        </div>
      </div>

      {{-- Search --}}
      <div class="p-3 bg-gray-50 border-b border-gray-200">
        <div class="relative">
          <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          <input type="text" x-model="plotSearch" placeholder="Search plot..."
            class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
      </div>

      {{-- Plot List --}}
      <div class="max-h-[350px] overflow-y-auto">
        <template x-for="plot in filteredPlotsForBlok()" :key="plot.plot">
          <div @click="togglePlot(plot)"
            class="group cursor-pointer px-3 py-2.5 border-b border-gray-100 hover:bg-blue-50 transition-all"
            :class="isPlotSelected(plot) ? 'bg-blue-50' : ''">
            <div class="flex items-center gap-3">
              {{-- Checkbox --}}
              <div class="w-5 h-5 rounded border-2 flex items-center justify-center transition-all flex-shrink-0"
                :class="isPlotSelected(plot) ? 'bg-blue-500 border-blue-500' : 'border-gray-300 group-hover:border-blue-400'">
                <svg x-show="isPlotSelected(plot)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
              
              {{-- Plot Info --}}
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <span class="text-sm font-bold text-gray-800" x-text="plot.plot"></span>
                  <span class="text-xs text-gray-500" x-text="(parseFloat(plot.batcharea) || 0).toFixed(2) + ' Ha'"></span>
                  <template x-if="plot.lifecyclestatus">
                    <span class="px-1.5 py-0.5 text-[9px] font-bold rounded"
                      :class="{
                        'bg-yellow-100 text-yellow-700': plot.lifecyclestatus === 'PC',
                        'bg-green-100 text-green-700': plot.lifecyclestatus === 'RC1',
                        'bg-blue-100 text-blue-700': plot.lifecyclestatus === 'RC2',
                        'bg-purple-100 text-purple-700': plot.lifecyclestatus === 'RC3'
                      }" x-text="plot.lifecyclestatus"></span>
                  </template>
                </div>
                <div class="text-[10px] text-gray-500 mt-0.5">
                  Batch: <span x-text="plot.activebatchno || '-'"></span>
                </div>
              </div>
            </div>
          </div>
        </template>

        {{-- Empty State --}}
        <div x-show="filteredPlotsForBlok().length === 0" class="px-4 py-8 text-center">
          <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          <p class="text-sm text-gray-500">No plots found</p>
        </div>
      </div>
    </div>

    {{-- Right Panel: Selected Plots with Luas Input --}}
    <div class="col-span-7 border border-gray-200 rounded-lg overflow-hidden bg-white">
      
      {{-- Header --}}
      <div class="bg-gray-50 border-b border-gray-200 px-4 py-3 flex items-center justify-between">
        <h4 class="text-sm font-semibold text-gray-800">Selected Plots</h4>
        <button type="button" @click="clearAllPlots()" x-show="plots.length > 0"
          class="text-xs text-red-600 hover:text-red-700 font-medium">
          Clear All
        </button>
      </div>

      {{-- Selected Plots Table --}}
      <div class="max-h-[400px] overflow-y-auto">
        <table class="w-full" x-show="plots.length > 0">
          <thead class="bg-gray-50 sticky top-0">
            <tr>
              <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Plot</th>
              <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">Luas RKH</th>
              <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600 bg-green-50">Luas Hasil</th>
              <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">Sisa</th>
              <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 w-12">Act</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <template x-for="(plot, index) in plots" :key="index">
              <tr class="hover:bg-gray-50">
                <td class="px-3 py-2">
                  <div class="text-sm font-semibold text-gray-800" x-text="plot.plot"></div>
                  <div class="text-[10px] text-gray-500" x-text="'Batch: ' + (plot.batchno || '-')"></div>
                </td>
                <td class="px-3 py-2 text-right">
                  <span class="text-sm font-mono text-gray-600" x-text="parseFloat(plot.luasrkh || 0).toFixed(2)"></span>
                </td>
                <td class="px-3 py-2 bg-green-50">
                  <input type="number" x-model="plot.luashasil" @input="calculateLuasSisa(index)"
                    step="0.01" min="0" :max="plot.luasrkh"
                    class="w-full px-2 py-1.5 text-sm border border-green-300 rounded-lg text-right font-mono bg-green-50 focus:ring-2 focus:ring-green-500 focus:bg-white">
                </td>
                <td class="px-3 py-2 text-right">
                  <span class="text-sm font-mono" 
                    :class="parseFloat(plot.luassisa) < 0 ? 'text-red-600' : 'text-gray-600'"
                    x-text="parseFloat(plot.luassisa || 0).toFixed(2)"></span>
                </td>
                <td class="px-3 py-2 text-center">
                  <button type="button" @click="removePlot(index)"
                    class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </button>
                </td>
              </tr>
            </template>
          </tbody>
          <tfoot class="bg-gray-50 border-t-2 border-gray-200">
            <tr>
              <td class="px-3 py-2 text-sm font-semibold text-gray-700">Total</td>
              <td class="px-3 py-2 text-right text-sm font-mono font-semibold text-gray-700" x-text="getTotalLuasRKH() + ' Ha'"></td>
              <td class="px-3 py-2 text-right text-sm font-mono font-bold text-green-700 bg-green-100" x-text="getTotalLuas() + ' Ha'"></td>
              <td class="px-3 py-2 text-right text-sm font-mono font-semibold text-orange-600" x-text="getTotalLuasSisa() + ' Ha'"></td>
              <td></td>
            </tr>
          </tfoot>
        </table>

        {{-- Empty State --}}
        <div x-show="plots.length === 0" class="px-4 py-12 text-center">
          <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
          </svg>
          <p class="text-gray-500 font-medium mb-2">No plots selected</p>
          <p class="text-gray-400 text-sm">Click plots from the left panel to add</p>
        </div>
      </div>
    </div>

  </div>

  {{-- Validation Warnings --}}
  <div class="space-y-2">
    {{-- Keterangan Warning --}}
    <div x-show="!keterangan.trim()" class="bg-red-50 border-l-4 border-red-500 p-3 rounded">
      <div class="flex items-start gap-2">
        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <div>
          <h3 class="text-sm font-semibold text-red-800">Alasan Edit wajib diisi</h3>
          <p class="text-xs text-red-700 mt-1">Isi alasan edit sebelum melanjutkan ke step berikutnya.</p>
        </div>
      </div>
    </div>

    {{-- Plot Warning --}}
    <div x-show="plots.length === 0" class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
      <div class="flex items-start gap-2">
        <svg class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <div>
          <h3 class="text-sm font-semibold text-yellow-800">At least 1 plot required</h3>
          <p class="text-xs text-yellow-700 mt-1">Please select plots from the left panel to continue.</p>
        </div>
      </div>
    </div>
  </div>

</div>