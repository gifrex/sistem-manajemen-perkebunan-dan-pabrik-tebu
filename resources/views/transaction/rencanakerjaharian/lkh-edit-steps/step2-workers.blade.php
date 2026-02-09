{{-- resources/views/transaction/rencanakerjaharian/lkh-edit-steps/step2-workers.blade.php --}}

<div class="space-y-4">
  
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        Worker Details
      </h3>
      <p class="text-xs text-gray-600 mt-1">
        <span x-show="jenistenagakerja == 1">Upah per jam kerja (Harian)</span>
        <span x-show="jenistenagakerja == 2">Upah dibagi rata dari total luas (Borongan)</span>
      </p>
    </div>
    <div class="flex items-center gap-3">
      <div class="text-right">
        <div class="text-xl font-bold text-purple-600" x-text="workers.length"></div>
        <div class="text-[10px] text-gray-500">Workers</div>
      </div>
      <button type="button" @click="addWorker()" 
        class="px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Add Worker
      </button>
    </div>
  </div>

  {{-- Wage Type Info --}}
  <div class="rounded-lg p-4 border-2"
       :class="jenistenagakerja == 1 ? 'bg-blue-50 border-blue-200' : 'bg-purple-50 border-purple-200'">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center"
             :class="jenistenagakerja == 1 ? 'bg-blue-100' : 'bg-purple-100'">
          <svg x-show="jenistenagakerja == 1" class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <svg x-show="jenistenagakerja == 2" class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
          </svg>
        </div>
        <div>
          <p class="text-sm font-bold" :class="jenistenagakerja == 1 ? 'text-blue-700' : 'text-purple-700'"
             x-text="jenistenagakerja == 1 ? 'TENAGA HARIAN' : 'TENAGA BORONGAN'"></p>
          <p class="text-xs text-gray-600 mt-0.5" x-show="jenistenagakerja == 1">
            Wage calculated per working hours
          </p>
          <p class="text-xs text-gray-600 mt-0.5" x-show="jenistenagakerja == 2">
            Total: <span class="font-semibold" x-text="formatRupiah(getTotalLuas() * boronganRate)"></span> ÷ 
            <span class="font-semibold" x-text="workers.length + ' workers'"></span> = 
            <span class="font-semibold text-green-600" x-text="workers.length > 0 ? formatRupiah((getTotalLuas() * boronganRate) / workers.length) + '/worker' : 'Rp 0'"></span>
          </p>
        </div>
      </div>
      
      {{-- Recalculate Button (Harian only) --}}
      <button x-show="jenistenagakerja == 1" type="button" @click="recalculateWages()" 
        :disabled="isCalculating || workers.length === 0"
        :class="isCalculating || workers.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
        <svg x-show="!isCalculating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        <svg x-show="isCalculating" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span x-text="isCalculating ? 'Calculating...' : 'Hitung Ulang Upah'"></span>
      </button>
    </div>
  </div>

  {{-- ================================================
      TENAGA HARIAN TABLE
  ================================================= --}}
  <div x-show="jenistenagakerja == 1" class="border border-gray-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-100 border-b border-gray-200">
          <tr>
            <th class="px-2 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-8">#</th>
            <th class="px-2 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-[200px]">Tenaga Kerja</th>
            <th class="px-2 py-2 text-center text-xs font-semibold text-gray-600 uppercase w-20">Jam Masuk</th>
            <th class="px-2 py-2 text-center text-xs font-semibold text-gray-600 uppercase w-20">Jam Pulang</th>
            <th class="px-2 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-16">Jam Kerja</th>
            <th class="px-2 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-16">Lembur</th>
            <th class="px-2 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-24 bg-green-50">Upah</th>
            <th class="px-2 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-24 bg-green-50">Upah Lembur</th>
            <th class="px-2 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-28 bg-green-100">Total Upah</th>
            <th class="px-2 py-2 text-center text-xs font-semibold text-gray-600 uppercase w-10"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <template x-for="(w, i) in workers" :key="i">
            <tr class="hover:bg-gray-50">
              <td class="px-2 py-2 text-gray-600 font-medium" x-text="i + 1"></td>
              <td class="px-2 py-2">
                <select x-model="w.tenagakerjaid" @change="updateWorkerNIK(i)"
                  class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-purple-500"
                  :class="!w.tenagakerjaid && 'border-red-300'">
                  <option value="">-- Select --</option>
                  <template x-for="tk in tenagaKerja" :key="tk.tenagakerjaid">
                    <option :value="tk.tenagakerjaid" 
                            :selected="tk.tenagakerjaid === w.tenagakerjaid"
                            x-text="`[${tk.tenagakerjaid}] ${tk.nama}`"></option>
                  </template>
                </select>
              </td>
              <td class="px-2 py-2">
                <input type="time" x-model="w.jammasuk" @input="markDirty()"
                  class="w-full px-1 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-purple-500 font-mono text-center">
              </td>
              <td class="px-2 py-2">
                <input type="time" x-model="w.jamselesai" @input="markDirty()"
                  class="w-full px-1 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-purple-500 font-mono text-center">
              </td>
              <td class="px-2 py-2">
                <input type="number" :value="Math.floor(w.totaljamkerja || 0)" readonly
                  class="w-full px-1 py-1 text-xs bg-gray-100 border border-gray-200 rounded text-right font-mono text-gray-600">
              </td>
              <td class="px-2 py-2">
                <input type="number" x-model.number="w.overtimehours" @input="markDirty()" step="1" min="0"
                  class="w-full px-1 py-1 text-xs border border-gray-300 rounded text-right focus:ring-1 focus:ring-purple-500 font-mono">
              </td>
              <td class="px-2 py-2 bg-green-50">
                <div class="text-right text-xs font-mono text-green-700" x-text="formatRupiah(w.upahharian)"></div>
              </td>
              <td class="px-2 py-2 bg-green-50">
                <div class="text-right text-xs font-mono text-green-700" x-text="formatRupiah(w.upahlembur)"></div>
              </td>
              <td class="px-2 py-2 bg-green-100">
                <div class="text-right text-xs font-bold font-mono text-green-800" x-text="formatRupiah(w.totalupah)"></div>
              </td>
              <td class="px-2 py-2 text-center">
                <button type="button" @click="removeWorker(i)" 
                  class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    
    {{-- Empty State --}}
    <div x-show="workers.length === 0" class="px-4 py-10 text-center bg-gray-50">
      <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
      </svg>
      <p class="text-gray-500 text-sm font-medium mb-3">No workers added</p>
      <button type="button" @click="addWorker()" 
        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium">
        Add First Worker
      </button>
    </div>
  </div>

  {{-- ================================================
      TENAGA BORONGAN TABLE (SIMPLIFIED)
  ================================================= --}}
  <div x-show="jenistenagakerja == 2" class="border border-gray-200 rounded-lg overflow-hidden">
    
    {{-- Borongan Summary --}}
    <div class="border-b-2 p-4"
        :class="boronganRate > 0 ? 'bg-purple-50 border-purple-200' : 'bg-amber-50 border-amber-200'">
      <div class="grid grid-cols-4 gap-4 text-center">
        <div>
          <p class="text-xs text-gray-600 mb-1">Total Luas</p>
          <p class="text-xl font-bold text-purple-700" x-text="getTotalLuas() + ' Ha'"></p>
        </div>
        <div>
          <p class="text-xs text-gray-600 mb-1">Rate/Ha</p>
          <p class="text-xl font-bold" 
            :class="boronganRate > 0 ? 'text-purple-700' : 'text-amber-600'"
            x-text="boronganRate > 0 ? formatRupiah(boronganRate) : 'Belum diset'"></p>
        </div>
        <div>
          <p class="text-xs text-gray-600 mb-1">Total Upah</p>
          <p class="text-xl font-bold" 
            :class="boronganRate > 0 ? 'text-green-700' : 'text-gray-400'"
            x-text="formatRupiah(getTotalLuas() * boronganRate)"></p>
        </div>
        <div>
          <p class="text-xs text-gray-600 mb-1">Per Worker</p>
          <p class="text-xl font-bold" 
            :class="boronganRate > 0 ? 'text-green-700' : 'text-gray-400'"
            x-text="workers.length > 0 ? formatRupiah((getTotalLuas() * boronganRate) / workers.length) : 'Rp 0'"></p>
        </div>
      </div>
    </div>

    {{-- Borongan Rate Warning --}}
    <div x-show="jenistenagakerja == 2 && boronganRate === 0" 
        class="bg-amber-50 border border-amber-300 p-4 m-4 rounded-lg">
      <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <div class="flex-1">
          <p class="text-sm font-semibold text-amber-800 mb-1">Rate upah borongan belum tersedia</p>
          <p class="text-xs text-amber-700 leading-relaxed">
            Activity <strong x-text="lkhData.activitycode"></strong> belum memiliki rate untuk tanggal LKH ini.
            Silakan atur terlebih dahulu di <strong>Master Data &rarr; Upah Borongan</strong>.
          </p>
        </div>
      </div>
    </div>

    {{-- Simple Worker List --}}
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-100 border-b border-gray-200">
          <tr>
            <th class="px-2 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-8">#</th>
            <th class="px-2 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Tenaga Kerja</th>
            <th class="px-2 py-2 text-center text-xs font-semibold text-gray-600 uppercase w-10"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <template x-for="(w, i) in workers" :key="i">
            <tr class="hover:bg-gray-50">
              <td class="px-2 py-2 text-gray-600 font-medium" x-text="i + 1"></td>
              <td class="px-2 py-2">
                <select x-model="w.tenagakerjaid" @change="updateWorkerNIK(i)"
                  class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-purple-500"
                  :class="!w.tenagakerjaid && 'border-red-300'">
                  <option value="">-- Select --</option>
                  <template x-for="tk in tenagaKerja" :key="tk.tenagakerjaid">
                    <option :value="tk.tenagakerjaid"
                            :selected="tk.tenagakerjaid === w.tenagakerjaid"
                            x-text="`[${tk.tenagakerjaid}] ${tk.nama}`"></option>
                  </template>
                </select>
              </td>
              <td class="px-2 py-2 text-center">
                <button type="button" @click="removeWorker(i)" 
                  class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    
    {{-- Empty State --}}
    <div x-show="workers.length === 0" class="px-4 py-10 text-center bg-gray-50">
      <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
      </svg>
      <p class="text-gray-500 text-sm font-medium mb-3">No workers assigned</p>
      <button type="button" @click="addWorker()" 
        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium">
        Add Worker
      </button>
    </div>
  </div>

  {{-- Total Summary (Harian) --}}
  <div x-show="jenistenagakerja == 1 && workers.length > 0" 
       class="bg-green-50 border-2 border-green-200 rounded-lg p-4">
    <div class="flex justify-between items-center">
      <div>
        <p class="text-sm font-semibold text-gray-700">Total Upah Harian</p>
        <p class="text-xs text-gray-600" x-text="workers.length + ' workers'"></p>
      </div>
      <p class="text-2xl font-bold text-green-700" x-text="formatRupiah(getTotalUpah())"></p>
    </div>
  </div>

  {{-- Validation Warning --}}
  <div x-show="workers.length === 0" class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded-lg">
    <div class="flex items-start">
      <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
      </svg>
      <div>
        <h3 class="text-sm font-semibold text-yellow-800">At least 1 worker required</h3>
        <p class="text-xs text-yellow-700 mt-1">Add workers to continue to next step.</p>
      </div>
    </div>
  </div>

</div>