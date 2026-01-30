{{-- resources/views/transaction/rencanakerjaharian/lkh-edit-steps/success-modal.blade.php --}}

<style>
  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }
  @keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes scaleIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
  }
  @keyframes drawCheck {
    from { stroke-dashoffset: 24; }
    to { stroke-dashoffset: 0; }
  }
  @keyframes drawCircle {
    from { stroke-dashoffset: 166; }
    to { stroke-dashoffset: 0; }
  }
  .backdrop-fade { animation: fadeIn 0.25s ease-out forwards; }
  .modal-slide { animation: slideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
  .icon-scale { animation: scaleIn 0.3s ease-out 0.1s forwards; opacity: 0; }
  .check-draw { 
    stroke-dasharray: 24; 
    stroke-dashoffset: 24;
    animation: drawCheck 0.4s ease-out 0.35s forwards; 
  }
  .circle-draw { 
    stroke-dasharray: 166; 
    stroke-dashoffset: 166;
    animation: drawCircle 0.5s ease-out 0.15s forwards; 
  }
  .content-fade { animation: fadeIn 0.3s ease-out 0.2s forwards; opacity: 0; }
  .content-fade-delay { animation: fadeIn 0.3s ease-out 0.35s forwards; opacity: 0; }
  .btn-fade { animation: fadeIn 0.3s ease-out 0.45s forwards; opacity: 0; }
</style>

<div 
  x-data="{
    showModal: false,
    isRedirecting: false,
    lkhNo: '',
    message: '',
    
    openModal(data) {
      this.lkhNo = data.lkhno || '{{ $lkhData->lkhno }}';
      this.message = data.message || 'LKH berhasil diupdate';
      this.showModal = true;
    },
    
    redirectToIndex() {
      this.isRedirecting = true;
      setTimeout(() => {
        window.location.href = '{{ route('transaction.rencanakerjaharian.index') }}';
      }, 200);
    }
  }"
  @lkh-success.window="openModal($event.detail)"
  x-show="showModal"
  x-cloak
  class="fixed inset-0 flex items-center justify-center z-50 p-4"
  style="display: none;">
  
  {{-- Backdrop --}}
  <div 
    class="absolute inset-0 bg-gray-900/50 backdrop-fade"
    @click="redirectToIndex()">
  </div>

  {{-- Modal Card --}}
  <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-xl modal-slide">
    
    {{-- Close Button --}}
    <button 
      @click="redirectToIndex()"
      class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>

    {{-- Content --}}
    <div class="px-8 pt-10 pb-8 text-center">
      
      {{-- Success Icon --}}
      <div class="flex justify-center mb-6 icon-scale">
        <svg class="w-16 h-16" viewBox="0 0 56 56" fill="none">
          <circle cx="28" cy="28" r="26" stroke="#22c55e" stroke-width="2.5" class="circle-draw"/>
          <path d="M18 28.5L24.5 35L38 21.5" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="check-draw"/>
        </svg>
      </div>

      {{-- Title --}}
      <h2 class="text-xl font-semibold text-gray-900 mb-2 content-fade">
        Update Successful
      </h2>
      
      {{-- Subtitle --}}
      <p class="text-sm text-gray-500 mb-8 content-fade">
        Your changes have been saved
      </p>

      {{-- LKH Number --}}
      <div class="mb-8 content-fade-delay">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">LKH Number</p>
        <p class="text-2xl font-semibold text-gray-900 font-mono tracking-tight" x-text="lkhNo"></p>
      </div>

      {{-- Message --}}
      <div class="bg-gray-50 rounded-xl px-4 py-3 mb-8 content-fade-delay">
        <p class="text-sm text-gray-600 leading-relaxed" x-text="message"></p>
      </div>

      {{-- Action Button --}}
      <button 
        type="button" 
        @click="redirectToIndex()" 
        :disabled="isRedirecting"
        class="w-full py-3 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium rounded-xl transition-colors duration-200 disabled:opacity-60 disabled:cursor-not-allowed btn-fade">
        <span x-show="!isRedirecting" class="flex items-center justify-center gap-2">
          <span>Back to List</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
          </svg>
        </span>
        <span x-show="isRedirecting" class="flex items-center justify-center gap-2">
          <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>Redirecting...</span>
        </span>
      </button>
    </div>
  </div>
</div>