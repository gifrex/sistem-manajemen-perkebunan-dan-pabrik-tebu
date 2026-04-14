{{-- resources/views/transaction/rencanakerjaharian/modal-index/index-modal-batal.blade.php --}}

{{-- Modal Konfirmasi Pembatalan RKH --}}
<div x-show="showCancelModal" x-cloak x-transition.opacity
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div x-show="showCancelModal" x-transition.scale 
         class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/2 lg:w-1/3 max-h-[90vh] overflow-hidden">
        
        {{-- Header --}}
        <div class="flex justify-between items-center p-4 border-b bg-red-50">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">Batalkan RKH</h2>
            </div>
            <button @click="showCancelModal = false; cancelRkhno = ''; cancelAlasan = ''" 
                    class="text-gray-600 hover:text-gray-800 text-2xl leading-none">&times;</button>
        </div>

        {{-- Body --}}
        <div class="p-6 space-y-4">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-sm text-yellow-800">
                    <strong>Perhatian:</strong> Pembatalan RKH tidak dapat dibatalkan. 
                    Mandor akan dapat membuat RKH baru untuk tanggal yang sama setelah pembatalan.
                </p>
            </div>
            
            <div>
                <p class="text-sm text-gray-700 mb-2">
                    Anda akan membatalkan RKH: <strong class="text-red-600" x-text="cancelRkhno"></strong>
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Alasan Pembatalan <span class="text-red-500">*</span>
                </label>
                <textarea x-model="cancelAlasan" rows="3" 
                          placeholder="Contoh: Kegiatan dibatalkan karena hujan deras..."
                          class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                <p class="text-xs text-gray-500 mt-1">Minimal 10 karakter</p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex justify-end space-x-2 p-4 border-t bg-gray-50">
            <button @click="showCancelModal = false; cancelRkhno = ''; cancelAlasan = ''" 
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded-lg transition-colors">
                Kembali
            </button>
            <button @click="submitCancelRkh()" 
                    :disabled="cancelAlasan.length < 10 || isCancelling"
                    class="bg-red-600 hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-6 py-2 text-sm rounded-lg transition-colors flex items-center">
                <svg x-show="isCancelling" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="isCancelling ? 'Memproses...' : 'Ya, Batalkan RKH'"></span>
            </button>
        </div>
    </div>
</div>

{{-- Modal Info Detail Pembatalan --}}
<div x-show="showBatalInfoModal" x-cloak x-transition.opacity
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div x-show="showBatalInfoModal" x-transition.scale 
         class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/2 lg:w-1/3 max-h-[90vh] overflow-hidden">
        
        {{-- Header --}}
        <div class="flex justify-between items-center p-4 border-b bg-red-50">
            <h2 class="text-lg font-semibold text-gray-900">Detail Pembatalan RKH</h2>
            <button @click="showBatalInfoModal = false" 
                    class="text-gray-600 hover:text-gray-800 text-2xl leading-none">&times;</button>
        </div>

        {{-- Body --}}
        <div class="p-6 space-y-4">
            <template x-if="isBatalInfoLoading">
                <div class="flex justify-center py-8">
                    <svg class="animate-spin h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </template>
            
            <template x-if="!isBatalInfoLoading && batalDetail">
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">No RKH:</span>
                        <span class="font-semibold" x-text="batalDetail.rkhno"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tanggal RKH:</span>
                        <span class="font-semibold" x-text="batalDetail.rkhdate_formatted"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Dibatalkan Oleh:</span>
                        <span class="font-semibold" x-text="batalDetail.batal_by_nama"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tanggal Batal:</span>
                        <span class="font-semibold" x-text="batalDetail.batalat_formatted"></span>
                    </div>
                    <div class="border-t pt-3">
                        <p class="text-sm text-gray-600 mb-1">Alasan Pembatalan:</p>
                        <p class="text-sm bg-gray-100 rounded p-3" x-text="batalDetail.batalalasan"></p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <div class="flex justify-end p-4 border-t bg-gray-50">
            <button @click="showBatalInfoModal = false" 
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded-lg transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>