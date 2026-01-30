{{-- resources/views/transaction/rencanakerjaharian/lkh-edit-steps/validation-modal.blade.php --}}

<div x-show="showValidationModal" 
     x-cloak
     class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4"
     style="display: none;"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     @click.self="showValidationModal = false">
  
  <div class="bg-white rounded-lg shadow-2xl w-full max-w-md border-2 border-red-200"
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0 transform scale-95"
       x-transition:enter-end="opacity-100 transform scale-100">
    
    {{-- Header --}}
    <div class="bg-red-600 text-white px-6 py-4 rounded-t-lg">
      <h3 class="text-lg font-bold flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        Validation Failed
      </h3>
    </div>
    
    {{-- Content --}}
    <div class="p-6">
      <p class="text-sm text-gray-600 mb-4">Please fix the following errors:</p>
      <div class="bg-red-50 border-l-4 border-red-500 rounded p-4 max-h-60 overflow-y-auto">
        <ul class="text-sm text-red-700 space-y-2">
          <template x-for="(error, index) in validationErrors" :key="index">
            <li class="flex items-start gap-2">
              <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
              </svg>
              <span x-text="error"></span>
            </li>
          </template>
        </ul>
      </div>
    </div>
    
    {{-- Footer --}}
    <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 rounded-b-lg">
      <button @click="showValidationModal = false"
        class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg font-semibold transition-colors">
        Got it, I'll fix them
      </button>
    </div>
  </div>
</div>