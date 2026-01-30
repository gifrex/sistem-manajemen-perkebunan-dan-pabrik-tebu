{{-- resources/views/transaction/rencanakerjaharian/lkh-edit-steps/step3-materials.blade.php --}}

<div class="space-y-4">
  
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>
        Material Details
      </h3>
      <p class="text-xs text-gray-600 mt-1">Material usage for this LKH (Read-only)</p>
    </div>
    <div class="flex items-center gap-3">
      <div class="text-right">
        <div class="text-xl font-bold text-orange-600" x-text="materials.length"></div>
        <div class="text-[10px] text-gray-500">Total Items</div>
      </div>
      {{-- ADD BUTTON DISABLED --}}
      <button type="button" disabled
        class="px-3 py-2 bg-gray-300 text-gray-500 rounded-lg text-sm font-medium cursor-not-allowed flex items-center gap-1.5 opacity-60">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
        </svg>
        Locked
      </button>
    </div>
  </div>

  {{-- Info Card - READ-ONLY WARNING --}}
  <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-300 rounded-lg p-4">
    <div class="flex items-start gap-3">
      <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
        </svg>
      </div>
      <div>
        <p class="text-sm font-bold text-blue-900 mb-1">🔒 Material Editing Locked</p>
        <p class="text-xs text-blue-700 leading-relaxed">
          Material data cannot be modified from this page because it affects stock inventory and API integrations. 
          Any changes must be made through the dedicated Material Management module to ensure proper stock tracking.
        </p>
      </div>
    </div>
  </div>

  {{-- Material Table - READ-ONLY --}}
  <div class="border border-gray-200 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="bg-gray-100 border-b border-gray-200">
            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-10">No</th>
            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Plot</th>
            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Item Code</th>
            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase min-w-[180px]">Item Name</th>
            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Received</th>
            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Remaining</th>
            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-600 uppercase bg-blue-50">Used</th>
            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Unit</th>
            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Notes</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <template x-for="(material, index) in materials" :key="index">
            <tr class="bg-gray-50">
              <td class="px-3 py-2 text-sm text-gray-600 font-medium" x-text="index + 1"></td>
              
              {{-- ALL INPUTS ARE READ-ONLY --}}
              <td class="px-3 py-2">
                <div class="px-2 py-1.5 text-sm bg-white border border-gray-200 rounded-lg font-mono uppercase text-gray-700" 
                     x-text="material.plot || '-'"></div>
              </td>
              <td class="px-3 py-2">
                <div class="px-2 py-1.5 text-sm bg-white border border-gray-200 rounded-lg font-mono text-gray-700" 
                     x-text="material.itemcode || '-'"></div>
              </td>
              <td class="px-3 py-2">
                <div class="px-2 py-1.5 text-sm bg-white border border-gray-200 rounded-lg text-gray-700" 
                     x-text="material.itemname || '-'"></div>
              </td>
              <td class="px-3 py-2">
                <div class="px-2 py-1.5 text-sm bg-white border border-gray-200 rounded-lg text-right font-mono text-gray-700" 
                     x-text="parseFloat(material.qtyditerima || 0).toFixed(3)"></div>
              </td>
              <td class="px-3 py-2">
                <div class="px-2 py-1.5 text-sm bg-white border border-gray-200 rounded-lg text-right font-mono text-gray-700" 
                     x-text="parseFloat(material.qtysisa || 0).toFixed(3)"></div>
              </td>
              <td class="px-3 py-2 bg-blue-50">
                <div class="px-2 py-1.5 text-sm bg-blue-100 border border-blue-200 rounded-lg text-right font-semibold font-mono text-blue-800" 
                     x-text="parseFloat(material.qtydigunakan || 0).toFixed(3)"></div>
              </td>
              <td class="px-3 py-2">
                <div class="px-2 py-1.5 text-sm bg-white border border-gray-200 rounded-lg text-center text-gray-700" 
                     x-text="material.satuan || '-'"></div>
              </td>
              <td class="px-3 py-2">
                <div class="px-2 py-1.5 text-sm bg-white border border-gray-200 rounded-lg text-gray-700" 
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

  {{-- Important Notice --}}
  <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-lg p-4">
    <div class="flex items-start gap-3">
      <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
      </svg>
      <div>
        <p class="text-sm font-semibold text-yellow-800 mb-1">Material Management Restriction</p>
        <p class="text-xs text-yellow-700 leading-relaxed">
          Material quantities are linked to inventory stock levels and cannot be modified here. 
          To adjust material data, please contact your system administrator or use the Material Stock Management module.
        </p>
      </div>
    </div>
  </div>

  {{-- Navigation Info --}}
  <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
    <div class="flex items-center gap-3">
      <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      <p class="text-sm text-blue-700">
        <span class="font-medium">You can proceed to the next step.</span> Material data is for reference only and will not be modified during this edit session.
      </p>
    </div>
  </div>
</div>