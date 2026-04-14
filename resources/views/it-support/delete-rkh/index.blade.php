{{-- resources/views/it-support/delete-rkh/index.blade.php --}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div x-data="deleteRkhApp()" class="min-h-screen bg-slate-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      
      <!-- Header Section -->
      <div class="mb-10">
        <div class="flex items-center gap-4 mb-4">
          <div class="p-3 bg-slate-800 rounded-xl">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-semibold text-slate-800">Delete RKH Transaction</h1>
            <p class="text-slate-500 text-sm mt-0.5">Hapus RKH beserta seluruh data terkait dengan aman</p>
          </div>
        </div>
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-slate-400">
          <span>IT Support</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          <span class="text-slate-600 font-medium">Delete RKH</span>
        </div>
      </div>

      <!-- Search Card -->
      <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6 shadow-sm">
        <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-3">
          <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
          </svg>
          Nomor RKH
        </label>
        <div class="flex gap-3">
          <input 
            type="text"
            x-model="rkhno"
            @keydown.enter="searchRkh()"
            placeholder="Contoh: RKH08010126"
            class="flex-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-slate-800 focus:border-slate-800 focus:bg-white text-base font-mono uppercase tracking-wide transition-all placeholder:text-slate-400"
          />
          <button 
            @click="searchRkh()"
            :disabled="loading || !rkhno.trim()"
            class="px-6 py-3 bg-slate-800 text-white rounded-xl font-medium hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all flex items-center gap-2">
            <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <svg x-show="loading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span x-text="loading ? 'Mencari...' : 'Cari'"></span>
          </button>
        </div>
      </div>

      <!-- RKH Data Section -->
      <div x-show="rkhData" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        
        <!-- Block Reason Alert (CRITICAL - Material DISPATCHED) -->
        <div x-show="rkhData && !rkhData.canDelete" x-transition class="bg-red-50 rounded-2xl border-2 border-red-300 p-6">
          <div class="flex items-start gap-4">
            <div class="p-3 bg-red-100 rounded-xl flex-shrink-0">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
            </div>
            <div class="flex-1">
              <h3 class="text-base font-bold text-red-800 mb-2">🚫 TIDAK DAPAT DIHAPUS</h3>
              <p class="text-sm text-red-700 leading-relaxed font-medium" x-text="rkhData?.blockReason"></p>
              <div class="mt-4 p-3 bg-red-100 rounded-lg">
                <p class="text-xs text-red-700 font-medium">💡 Solusi:</p>
                <ul class="text-xs text-red-700 mt-2 space-y-1 ml-4">
                  <li>• Gunakan proses <strong>Retur Material</strong> terlebih dahulu</li>
                  <li>• Koordinasikan dengan Tim Gudang untuk membatalkan dispatch</li>
                  <li>• Hubungi IT Support untuk penanganan khusus</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Critical Warning (Material exists but not dispatched yet) -->
        <div x-show="rkhData?.hasCriticalImpact && rkhData?.canDelete" x-transition class="bg-amber-50 rounded-2xl border border-amber-200 p-5">
          <div class="flex items-start gap-4">
            <div class="p-2 bg-amber-100 rounded-lg flex-shrink-0">
              <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
            </div>
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-amber-800 mb-1">⚠️ Peringatan Penting</h3>
              <p class="text-sm text-amber-700 leading-relaxed">
                RKH ini sudah memiliki data 
                <span x-show="rkhData?.hasmaterialimpact" class="font-semibold">Material</span>
                <span x-show="rkhData?.hasmaterialimpact && (rkhData?.hassuratjalanimpact || rkhData?.hastimbanganimpact)">, </span>
                <span x-show="rkhData?.hassuratjalanimpact" class="font-semibold">Surat Jalan</span>
                <span x-show="rkhData?.hassuratjalanimpact && rkhData?.hastimbanganimpact">, </span>
                <span x-show="rkhData?.hastimbanganimpact" class="font-semibold">Timbangan</span>
                yang mungkin berpengaruh ke aplikasi lain. Pastikan data di aplikasi lain sudah di-clear terlebih dahulu.
              </p>
            </div>
          </div>
        </div>

        <!-- RKH Info Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="bg-slate-800 px-6 py-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h2 class="text-lg font-semibold text-white">Informasi RKH</h2>
              </div>
              <div>
                <span x-show="rkhData?.rkhInfo?.approvalstatus === '1'" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                  <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full"></span> Approved
                </span>
                <span x-show="rkhData?.rkhInfo?.approvalstatus === '0'" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full bg-red-500/20 text-red-300 border border-red-500/30">
                  <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span> Declined
                </span>
                <span x-show="!rkhData?.rkhInfo?.approvalstatus || (rkhData?.rkhInfo?.approvalstatus !== '1' && rkhData?.rkhInfo?.approvalstatus !== '0')" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                  <span class="w-1.5 h-1.5 bg-amber-400 rounded-full"></span> Pending
                </span>
              </div>
            </div>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
              <!-- RKH Number -->
              <div class="col-span-2 p-4 bg-slate-50 rounded-xl border border-slate-100">
                <div class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">RKH Number</div>
                <div class="text-xl font-semibold font-mono text-slate-800 tracking-wide" x-text="rkhData?.rkhInfo?.rkhno"></div>
              </div>
              <!-- Date -->
              <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <div class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Tanggal</div>
                <div class="text-base font-medium text-slate-800" x-text="rkhData?.rkhInfo?.formatted_date"></div>
              </div>
              <!-- Manpower -->
              <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <div class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Manpower</div>
                <div class="text-base font-medium text-slate-800" x-text="rkhData?.rkhInfo?.manpower || 0"></div>
              </div>
              <!-- Total Luas -->
              <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <div class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Total Luas</div>
                <div class="text-base font-medium text-slate-800" x-text="(rkhData?.rkhInfo?.totalluas || 0).toFixed(2) + ' Ha'"></div>
              </div>
              <!-- Mandor -->
              <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <div class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Mandor</div>
                <div class="text-base font-medium text-slate-800" x-text="rkhData?.rkhInfo?.mandorid || '-'"></div>
              </div>
              <!-- Activity Group -->
              <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <div class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Activity Group</div>
                <div class="text-base font-medium text-slate-800" x-text="rkhData?.rkhInfo?.activitygroup || '-'"></div>
              </div>
              <!-- Created By -->
              <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <div class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Created By</div>
                <div class="text-base font-medium text-slate-800" x-text="rkhData?.rkhInfo?.inputby"></div>
                <div class="text-xs text-slate-400 mt-0.5" x-text="rkhData?.rkhInfo?.formatted_createdat"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Material Status Card (NEW) -->
        <div x-show="rkhData?.materialStatus?.exists" x-transition class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="bg-slate-800 px-6 py-4">
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
              </svg>
              <h2 class="text-lg font-semibold text-white">Status Material</h2>
            </div>
          </div>
          <div class="p-6">
            <div class="flex items-center justify-between p-4 rounded-xl border"
                 :class="rkhData?.materialStatus?.can_delete ? 'bg-green-50 border-green-100' : 'bg-red-50 border-red-100'">
              <div class="flex items-center gap-3">
                <div class="w-2 h-2 rounded-full" :class="rkhData?.materialStatus?.can_delete ? 'bg-green-400' : 'bg-red-400'"></div>
                <span class="text-sm font-medium text-slate-700" x-text="rkhData?.materialStatus?.message"></span>
              </div>
              <span class="px-3 py-1 text-xs font-semibold rounded-lg"
                    :class="rkhData?.materialStatus?.can_delete ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                    x-text="rkhData?.materialStatus?.status"></span>
            </div>
            
            <!-- Additional Info if DISPATCHED -->
            <div x-show="rkhData?.materialStatus?.status === 'DISPATCHED'" x-transition class="mt-4 p-4 bg-red-50 rounded-xl border border-red-100">
              <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                  <p class="text-xs font-semibold text-red-700 mb-1">Material sudah diserahkan ke Gudang</p>
                  <p class="text-xs text-red-600 leading-relaxed">
                    Material dengan status DISPATCHED berarti sudah diserahkan ke gudang dan tercatat di sistem inventori. 
                    Penghapusan tidak diizinkan untuk menjaga integritas data antar aplikasi.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Impact Summary Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="bg-slate-800 px-6 py-4">
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
              </svg>
              <h2 class="text-lg font-semibold text-white">Data yang Akan Terhapus</h2>
            </div>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
              <template x-for="(count, table) in rkhData?.impact" :key="table">
                <div class="flex items-center justify-between p-3.5 rounded-xl border transition-colors"
                     :class="count > 0 ? 'bg-red-50 border-red-100' : 'bg-slate-50 border-slate-100'">
                  <div class="flex items-center gap-2.5">
                    <div class="w-2 h-2 rounded-full" :class="count > 0 ? 'bg-red-400' : 'bg-slate-300'"></div>
                    <span class="text-sm font-medium" :class="count > 0 ? 'text-slate-700' : 'text-slate-400'" x-text="formatTableName(table)"></span>
                  </div>
                  <span class="text-sm font-semibold font-mono px-2.5 py-0.5 rounded-lg" 
                        :class="count > 0 ? 'text-red-600 bg-red-100' : 'text-slate-400 bg-slate-100'" 
                        x-text="count"></span>
                </div>
              </template>
            </div>
          </div>
        </div>

        <!-- Delete Button (Only show if can delete) -->
        <div x-show="rkhData?.canDelete" class="flex justify-end pt-2">
          <button 
            @click="openDeleteModal()"
            class="px-6 py-3 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition-colors flex items-center gap-2.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Hapus RKH Permanen
          </button>
        </div>

        <!-- Alternative Action (if cannot delete - suggest Change Date) -->
        <div x-show="!rkhData?.canDelete && rkhData?.materialStatus?.status === 'DISPATCHED'" class="flex justify-end pt-2">
          <a 
            href="{{ route('it-support.change-date-rkh.index') }}"
            class="px-6 py-3 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition-colors flex items-center gap-2.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Alternatif: Ubah Tanggal RKH
          </a>
        </div>

      </div>

      <!-- Delete Confirmation Modal -->
      <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
          <!-- Backdrop -->
          <div x-show="showDeleteModal" 
               x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
               x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
               class="fixed inset-0 bg-slate-900/50 transition-opacity" 
               @click="closeDeleteModal()"></div>

          <!-- Modal -->
          <div x-show="showDeleteModal"
               x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
               x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
               class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden">
            
            <!-- Modal Header -->
            <div class="bg-slate-800 px-6 py-4">
              <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h2 class="text-lg font-semibold text-white">Konfirmasi Penghapusan</h2>
              </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-5">
              
              <!-- RKH Number Display -->
              <div class="p-4 bg-red-50 rounded-xl border border-red-100">
                <div class="text-xs text-red-600 font-medium uppercase tracking-wide mb-1">RKH yang akan dihapus</div>
                <div class="text-xl font-semibold font-mono text-red-700 tracking-wide" x-text="rkhData?.rkhInfo?.rkhno"></div>
              </div>

              <!-- Deletion Reason -->
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                  Alasan Penghapusan <span class="text-red-500">*</span>
                </label>
                <textarea 
                  x-model="deletionReason"
                  rows="3"
                  placeholder="Contoh: Data salah input tanggal, sudah dikonfirmasi dengan manager"
                  class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-slate-800 focus:border-slate-800 transition-all resize-none placeholder:text-slate-400"
                  maxlength="1000"
                ></textarea>
                <div class="flex justify-end mt-1.5">
                  <span class="text-xs text-slate-400"><span x-text="deletionReason.length"></span>/1000</span>
                </div>
              </div>

              <!-- Confirmation Input -->
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                  Konfirmasi <span class="text-red-500">*</span>
                </label>
                <p class="text-sm text-slate-500 mb-2">
                  Ketik <code class="font-mono font-medium text-slate-700 px-1.5 py-0.5 bg-slate-100 rounded">hapus aman</code> untuk melanjutkan:
                </p>
                <input 
                  type="text"
                  x-model="confirmationText"
                  placeholder="hapus aman"
                  class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm font-mono focus:ring-2 focus:ring-slate-800 focus:border-slate-800 transition-all placeholder:text-slate-400"
                />
                <div x-show="confirmationText && confirmationText !== 'hapus aman'" 
                     x-transition
                     class="mt-2 flex items-center gap-2 text-sm text-red-600">
                  <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                  Konfirmasi tidak sesuai
                </div>
                <div x-show="confirmationText === 'hapus aman'" 
                     x-transition
                     class="mt-2 flex items-center gap-2 text-sm text-emerald-600">
                  <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  Konfirmasi valid
                </div>
              </div>

            </div>

            <!-- Modal Footer -->
            <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 border-t border-slate-100">
              <button 
                @click="closeDeleteModal()" 
                :disabled="isDeleting"
                class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-100 font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                Batal
              </button>
              
              <button 
                @click="confirmDelete()"
                :disabled="isDeleting || !deletionReason.trim() || confirmationText !== 'hapus aman'"
                class="px-5 py-2.5 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors flex items-center gap-2">
                <svg x-show="isDeleting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="isDeleting ? 'Menghapus...' : 'Hapus Permanen'"></span>
              </button>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>

  <script>
    function deleteRkhApp() {
      return {
        rkhno: '',
        loading: false,
        rkhData: null,
        showDeleteModal: false,
        deletionReason: '',
        confirmationText: '',
        isDeleting: false,

        async searchRkh() {
          if (!this.rkhno.trim()) {
            alert('Masukkan nomor RKH terlebih dahulu');
            return;
          }
          this.loading = true;
          this.rkhData = null;
          try {
            const response = await fetch('{{ route("it-support.delete-rkh.search") }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              },
              body: JSON.stringify({ rkhno: this.rkhno.toUpperCase() })
            });
            const data = await response.json();
            if (data.success) {
              this.rkhData = data.data;
              
              // Show alert if cannot delete
              if (!this.rkhData.canDelete) {
                setTimeout(() => {
                  alert('⚠️ RKH ini tidak dapat dihapus:\n\n' + this.rkhData.blockReason);
                }, 300);
              }
            } else {
              alert(data.message || 'RKH tidak ditemukan');
            }
          } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mencari RKH');
          } finally {
            this.loading = false;
          }
        },

        openDeleteModal() {
          // Double check canDelete
          if (!this.rkhData?.canDelete) {
            alert('RKH ini tidak dapat dihapus: ' + this.rkhData?.blockReason);
            return;
          }
          this.showDeleteModal = true;
          this.deletionReason = '';
          this.confirmationText = '';
        },

        closeDeleteModal() {
          this.showDeleteModal = false;
          this.deletionReason = '';
          this.confirmationText = '';
        },

        async confirmDelete() {
          if (!this.deletionReason.trim()) {
            alert('Alasan penghapusan harus diisi');
            return;
          }
          if (this.confirmationText !== 'hapus aman') {
            alert('Konfirmasi tidak sesuai');
            return;
          }
          
          // Final safety check
          if (!this.rkhData?.canDelete) {
            alert('RKH ini tidak dapat dihapus');
            return;
          }
          
          this.isDeleting = true;
          try {
            const response = await fetch(`{{ url('it-support/delete-rkh') }}/${this.rkhData.rkhInfo.rkhno}`, {
              method: 'DELETE',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              },
              body: JSON.stringify({
                deletionreason: this.deletionReason,
                confirmation: this.confirmationText
              })
            });
            const data = await response.json();
            if (data.success) {
              alert('✅ ' + data.message);
              this.closeDeleteModal();
              this.rkhData = null;
              this.rkhno = '';
            } else {
              alert('❌ ' + data.message);
            }
          } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus RKH');
          } finally {
            this.isDeleting = false;
          }
        },

        formatTableName(table) {
          const names = {
            'rkhhdr': 'RKH Header',
            'rkhlst': 'RKH List',
            'rkhlstworker': 'RKH List Worker',
            'rkhlstkendaraan': 'RKH List Kendaraan',
            'lkhhdr': 'LKH Header',
            'lkhdetailplot': 'LKH Detail Plot',
            'lkhdetailworker': 'LKH Detail Worker',
            'lkhdetailkendaraan': 'LKH Detail Kendaraan',
            'lkhdetailmaterial': 'LKH Detail Material',
            'lkhdetailbsm': 'LKH Detail BSM',
            'usematerialhdr': 'Use Material Header',
            'usemateriallst': 'Use Material List',
            'suratjalanpos': 'Surat Jalan POS',
            'timbanganpayload': 'Timbangan Payload'
          };
          return names[table] || table;
        }
      }
    }
  </script>

  <style>
    [x-cloak] { display: none !important; }
  </style>
</x-layout>