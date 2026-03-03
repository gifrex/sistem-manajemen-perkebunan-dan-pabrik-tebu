 <!-- Confirm Generate Modal -->
 <div id="generateModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm p-4 invisible opacity-0 transition-all duration-300">
     <div
         class="bg-white w-full max-w-md rounded-2xl shadow-2xl transform scale-95 transition-transform duration-300 p-6">
         <div class="flex items-center gap-3 mb-5">
             <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                 <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M13 10V3L4 14h7v7l9-11h-7z" />
                 </svg>
             </div>
             <div>
                 <h3 class="text-lg font-bold text-gray-900">Konfirmasi Generate</h3>
                 <p class="text-sm text-gray-500">Proses data LKH menjadi pembayaran upah mingguan</p>
             </div>
         </div>
         <div class="bg-gray-50 rounded-lg p-4 mb-4 space-y-3">
             <div>
                 <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Jenis Tenaga Kerja</p>
                 <p class="text-sm font-semibold text-gray-800" id="gen-tk">-</p>
             </div>
             <div>
                 <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Periode Generate</p>
                 <div class="flex items-center gap-2">
                     <div class="flex-1">
                         <label class="block text-xs text-gray-500 mb-1">Dari</label>
                         <input type="date" id="gen_start_date"
                             class="w-full px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 text-sm transition-all duration-200"
                             onchange="onGenStartDateChange(this.value)" />
                     </div>
                     <div class="pt-4 text-gray-400 font-medium text-sm">s/d</div>
                     <div class="flex-1">
                         <label class="block text-xs text-gray-500 mb-1">Sampai</label>
                         <input type="date" id="gen_end_date" readonly
                             class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-gray-100 text-gray-500 text-sm cursor-not-allowed" />
                     </div>
                 </div>
                 <p class="text-xs text-indigo-500 mt-1.5 flex items-center gap-1">
                     <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                         <path fill-rule="evenodd"
                             d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                             clip-rule="evenodd" />
                     </svg>
                     End date otomatis H+6 dari start date (1 minggu penuh)
                 </p>
             </div>
         </div>
         <p class="text-sm text-amber-600 bg-amber-50 rounded-lg p-3 mb-5">
             ⚠️ Pastikan semua data LKH untuk periode ini sudah benar sebelum generate.
         </p>
         <div class="flex justify-end gap-3">
             <button onclick="closeGenerateModal()"
                 class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200">
                 Batal
             </button>
             <button onclick="doGenerate()" id="btnDoGenerate"
                 class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                 <svg id="gen-icon-bolt" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M13 10V3L4 14h7v7l9-11h-7z" />
                 </svg>
                 <svg id="gen-icon-spin" class="w-4 h-4 animate-spin hidden" xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24">
                     <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                         stroke-width="4" />
                     <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                 </svg>
                 <span id="gen-btn-text">Generate Sekarang</span>
             </button>
         </div>
     </div>
 </div>
