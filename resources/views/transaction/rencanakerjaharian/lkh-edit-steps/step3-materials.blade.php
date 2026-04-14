{{-- resources/views/transaction/rencanakerjaharian/lkh-edit-steps/step3-materials.blade.php --}}

<div class="space-y-4">
  
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>
        Material Usage
      </h3>
      <p class="text-xs text-gray-600 mt-1">Edit material usage - Only "Used" quantity is editable</p>
    </div>
    <div class="text-right">
      <div class="text-xl font-bold text-orange-600" x-text="materials.length"></div>
      <div class="text-[10px] text-gray-500">Total Items</div>
    </div>
  </div>

  {{-- Info Card --}}
  <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-300 rounded-lg p-4">
    <div class="flex items-start gap-3">
      <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      <div>
        <p class="text-sm font-bold text-blue-900 mb-1">✏️ Editing Rules</p>
        <ul class="text-xs text-blue-700 leading-relaxed space-y-1">
          <li>• <strong>Editable:</strong> "Used" quantity only</li>
          <li>• <strong>Auto-calculated:</strong> "Remaining" = Received - Used</li>
          <li>• <strong>Synced:</strong> Updates both lkhdetailmaterial & usemateriallst</li>
          <li>• <strong>Validation:</strong> Used cannot exceed Received</li>
        </ul>
      </div>
    </div>
  </div>

  {{-- Material Table --}}
  <div class="border border-gray-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="bg-gray-100 border-b border-gray-200">
            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-10">No</th>
            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Plot</th>
            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Item Code</th>
            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase min-w-[180px]">Item Name</th>
            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-600 uppercase bg-gray-50">Received</th>
            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-600 uppercase bg-green-50">Remaining</th>
            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-600 uppercase bg-blue-50">Used ✏️</th>
            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Unit</th>
            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Notes</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <template x-for="(material, index) in materials" :key="material.id">
            <tr class="hover:bg-gray-50 transition-colors">
              <td class="px-3 py-2 text-sm text-gray-600 font-medium" x-text="index + 1"></td>
              
              {{-- READ-ONLY FIELDS --}}
              <td class="px-3 py-2">
                <div class="px-2 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg font-mono uppercase text-gray-700" 
                     x-text="material.plot || '-'"></div>
              </td>
              <td class="px-3 py-2">
                <div class="px-2 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg font-mono text-gray-700" 
                     x-text="material.itemcode || '-'"></div>
              </td>
              <td class="px-3 py-2">
                <div class="px-2 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg text-gray-700" 
                     x-text="material.itemname || '-'"></div>
              </td>
              <td class="px-3 py-2 bg-gray-50">
                <div class="px-2 py-1.5 text-sm bg-white border border-gray-200 rounded-lg text-right font-mono text-gray-700" 
                     x-text="parseFloat(material.qtyditerima || 0).toFixed(3)"></div>
              </td>
              
              {{-- AUTO-CALCULATED REMAINING --}}
              <td class="px-3 py-2 bg-green-50">
                <div class="px-2 py-1.5 text-sm bg-green-100 border border-green-200 rounded-lg text-right font-semibold font-mono"
                     :class="(parseFloat(material.qtyditerima) - parseFloat(material.qtydigunakan)) < 0 ? 'text-red-600 bg-red-50 border-red-300' : 'text-green-700'"
                     x-text="(parseFloat(material.qtyditerima || 0) - parseFloat(material.qtydigunakan || 0)).toFixed(3)"></div>
              </td>
              
              {{-- ✏️ EDITABLE: USED QUANTITY --}}
              <td class="px-3 py-2 bg-blue-50">
                <input 
                  type="number" 
                  step="0.001"
                  min="0"
                  :max="material.qtyditerima"
                  x-model="material.qtydigunakan"
                  @input="validateMaterialUsed(material)"
                  class="w-full px-2 py-1.5 text-sm bg-white border-2 rounded-lg text-right font-semibold font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                  :class="parseFloat(material.qtydigunakan) > parseFloat(material.qtyditerima) ? 'border-red-500 bg-red-50' : 'border-blue-300'"
                  placeholder="0.000">
                <div x-show="parseFloat(material.qtydigunakan) > parseFloat(material.qtyditerima)" 
                     class="text-xs text-red-600 mt-1 font-medium">
                  ⚠️ Exceeds received!
                </div>
              </td>
              
              <td class="px-3 py-2">
                <div class="px-2 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg text-center text-gray-700" 
                     x-text="material.satuan || '-'"></div>
              </td>
              <td class="px-3 py-2">
                <div class="px-2 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg text-gray-700 truncate max-w-[150px]" 
                     :title="material.keterangan"
                     x-text="material.keterangan || '-'"></div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    
    {{-- Empty State --}}
    <div x-show="materials.length === 0" class="px-4 py-12 text-center bg-gray-50">
      <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
      </svg>
      <p class="text-gray-500 font-medium mb-2">No materials recorded</p>
      <p class="text-gray-400 text-sm">This LKH has no material usage data.</p>
    </div>
  </div>

  {{-- Summary Cards --}}
  <div x-show="materials.length > 0" class="grid grid-cols-3 gap-4">
    <div class="bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-lg p-4 text-center shadow-sm">
      <p class="text-xs text-gray-600 font-medium mb-1">Total Received</p>
      <p class="text-2xl font-bold text-gray-700" x-text="getTotalReceived()"></p>
    </div>
    <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-lg p-4 text-center shadow-sm">
      <p class="text-xs text-amber-600 font-medium mb-1">Total Remaining</p>
      <p class="text-2xl font-bold text-amber-700" x-text="getTotalRemaining()"></p>
    </div>
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4 text-center shadow-sm">
      <p class="text-xs text-blue-600 font-medium mb-1">Total Used</p>
      <p class="text-2xl font-bold text-blue-700" x-text="getTotalUsed()"></p>
    </div>
  </div>

  {{-- Validation Warning --}}
  <div x-show="hasMaterialValidationError()" class="bg-red-50 border-l-4 border-red-400 rounded-lg p-4 animate-pulse">
    <div class="flex items-start gap-3">
      <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      <div>
        <p class="text-sm font-semibold text-red-800 mb-1">❌ Validation Error</p>
        <p class="text-xs text-red-700 leading-relaxed">
          Some materials have "Used" quantity exceeding "Received" quantity. Please correct before proceeding.
        </p>
      </div>
    </div>
  </div>
</div>