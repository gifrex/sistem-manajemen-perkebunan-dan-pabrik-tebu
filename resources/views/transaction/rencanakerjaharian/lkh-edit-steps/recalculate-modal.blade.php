{{-- resources/views/transaction/rencanakerjaharian/lkh-edit-steps/recalculate-modal.blade.php --}}

{{-- Recalculate Warning Modal --}}
<div x-show="showRecalculateModal" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
  
  {{-- Backdrop --}}
  <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
       @click="showRecalculateModal = false"></div>
  
  {{-- Modal --}}
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 transform transition-all"
         @click.away="showRecalculateModal = false">
      
      {{-- Icon --}}
      <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-4">
        <svg class="h-10 w-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
      </div>
      
      {{-- Title --}}
      <h3 class="text-xl font-bold text-gray-900 text-center mb-2">
        Recalculation Required
      </h3>
      
      {{-- Message --}}
      <div class="text-center mb-6">
        <p class="text-gray-600 mb-3">
          You have made changes to worker data. Please recalculate wages before proceeding to the next step.
        </p>
        
        {{-- Changes Detected --}}
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-left">
          <p class="text-sm font-semibold text-yellow-800 mb-2">Changes detected:</p>
          <ul class="text-sm text-yellow-700 space-y-1">
            <li class="flex items-start">
              <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              Worker selection, time, or overtime hours modified
            </li>
            <li class="flex items-start">
              <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              Current wage calculations may be outdated
            </li>
          </ul>
        </div>
      </div>
      
      {{-- Actions --}}
      <div class="flex gap-3">
        <button type="button" 
                @click="showRecalculateModal = false"
                class="flex-1 px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors">
          Cancel
        </button>
        <button type="button" 
                @click="showRecalculateModal = false; recalculateWages()"
                class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
          Hitung Ulang Upah Sekarang
        </button>
      </div>
      
      {{-- Info Footer --}}
      <p class="text-xs text-gray-500 text-center mt-4">
        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
        </svg>
        This ensures accurate wage calculations before review
      </p>
      
    </div>
  </div>
</div>