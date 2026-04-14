{{-- resources\views\input\rencanakerjaharian\modal-index\index-modal-lkh-list.blade.php --}}

{{-- LKH LIST MODAL --}}
<div x-show="showLKHModal" x-cloak x-transition.opacity
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
   <div x-show="showLKHModal" x-transition.scale
        class="bg-white rounded-lg shadow-2xl w-11/12 md:w-4/5 lg:w-4/5 max-h-[90vh] flex flex-col">
       <!-- Header -->
       <div class="flex justify-between items-center p-6 border-b border-gray-200">
           <div>
               <h2 class="text-lg font-bold text-gray-800">Daftar LKH</h2>
               <p class="text-sm text-gray-600 mt-1">Laporan Kegiatan Harian untuk RKH terpilih</p>
           </div>
           <div class="text-right">
               <div class="text-sm text-gray-600">No. RKH:</div>
               <div class="font-mono font-semibold text-gray-800" x-text="selectedRkhno"></div>
           </div>
           <button @click="showLKHModal = false" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
       </div>
       
       <!-- Loading State -->
       <div x-show="isLkhModalLoading" class="p-8 text-center">
           <div class="loading-spinner mx-auto"></div>
           <p class="mt-2 text-gray-500 loading-dots">Loading LKH data</p>
       </div>

       <!-- Body -->
       <div x-show="!isLkhModalLoading" class="p-6 overflow-hidden flex-grow">
           <div class="overflow-x-auto">
               <div class="max-h-[400px] overflow-y-auto">
                   <table class="w-full border-collapse bg-white">
                       <thead class="bg-gray-100 sticky top-0">
                           <tr>
                               <th class="border border-gray-300 px-4 py-3 text-left text-xs font-medium text-gray-700 w-40">No LKH</th>
                               <th class="border border-gray-300 px-3 py-3 text-center text-xs font-medium text-gray-700 w-24">Jenis</th>
                               <th class="border border-gray-300 px-3 py-3 text-left text-xs font-medium text-gray-700 w-48">Aktivitas</th>
                               <th class="border border-gray-300 px-3 py-3 text-left text-xs font-medium text-gray-700 w-32">Plot</th>
                               <th class="border border-gray-300 px-3 py-3 text-center text-xs font-medium text-gray-700 w-20">Workers</th>
                               <th class="border border-gray-300 px-3 py-3 text-center text-xs font-medium text-gray-700 w-20">Material</th>
                               <th class="border border-gray-300 px-3 py-3 text-center text-xs font-medium text-gray-700 w-24">Status</th>
                               <th class="border border-gray-300 px-3 py-3 text-center text-xs font-medium text-gray-700 w-28">Approval</th>
                               <th class="border border-gray-300 px-3 py-3 text-center text-xs font-medium text-gray-700 w-36">Aksi</th>
                           </tr>
                       </thead>
                       <tbody>
                           <template x-for="(lkh, index) in lkhData" :key="`lkh-row-${index}-${lkh.lkhno || 'empty'}`">
                               <tr class="hover:bg-gray-50">
                                   <td class="border border-gray-300 px-4 py-3">
                                       <div class="font-mono text-sm font-medium text-gray-800" x-text="lkh.lkhno"></div>
                                   </td>
                                   <td class="border border-gray-300 px-3 py-3 text-center">
                                       <span class="px-2 py-1 text-xs rounded-full font-medium"
                                             :class="lkh.jenis_tenaga === 'Harian' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700'"
                                             x-text="lkh.jenis_tenaga"></span>
                                   </td>
                                   <td class="border border-gray-300 px-3 py-3">
                                       <div>
                                           <div class="font-medium text-sm text-gray-800" x-text="lkh.activityname"></div>
                                           <div class="text-xs text-gray-500 font-mono" x-text="lkh.activitycode"></div>
                                       </div>
                                   </td>
                                   <td class="border border-gray-300 px-3 py-3">
                                       <div class="text-sm text-gray-700" x-text="lkh.plots && lkh.plots.includes(',') ? lkh.plots.split(',').map(p => p.trim().split(' ')[0]).join(', ') : (lkh.plots || 'No plots')"></div>
                                   </td>
                                   <td class="border border-gray-300 px-3 py-3 text-center">
                                       <div class="text-sm font-medium text-gray-800" x-text="lkh.workers_assigned || 0"></div>
                                   </td>
                                   <td class="border border-gray-300 px-3 py-3 text-center">
                                       <span class="text-sm font-medium"
                                             :class="(lkh.material_count || 0) > 0 ? 'text-emerald-600' : 'text-gray-500'"
                                             x-text="(lkh.material_count || 0) > 0 ? 'Yes' : 'No'"></span>
                                   </td>
                                   <td class="border border-gray-300 px-3 py-3 text-center">
                                       <span class="px-2 py-1 text-xs rounded-full font-medium"
                                             :class="{
                                                 'bg-emerald-100 text-emerald-700': lkh.status === 'COMPLETED',
                                                 'bg-amber-100 text-amber-700': lkh.status === 'DRAFT',
                                                 'bg-sky-100 text-sky-700': lkh.status === 'SUBMITTED',
                                                 'bg-green-100 text-green-700': lkh.status === 'APPROVED',
                                                 'bg-slate-100 text-slate-600': lkh.status === 'EMPTY'
                                             }"
                                             x-text="lkh.status"></span>
                                   </td>
                                   <td class="border border-gray-300 px-3 py-3 text-center">
                                       <template x-if="lkh.approval_status === 'Approved'">
                                           <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700 font-medium">Approved</span>
                                       </template>
                                       <template x-if="lkh.approval_status === 'No Approval Required'">
                                           <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-700 font-medium">No Approval</span>
                                       </template>
                                       <template x-if="lkh.approval_status === 'Declined'">
                                           <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 font-medium">Declined</span>
                                       </template>
                                       <template x-if="lkh.approval_status === 'Not Yet Submitted'">
                                           <span class="px-2 py-1 text-xs rounded-full bg-slate-100 text-slate-600 font-medium">Not Submitted</span>
                                       </template>
                                       <template x-if="lkh.approval_status && lkh.approval_status.includes('Waiting')">
                                           <button @click="showLKHModal = false; setTimeout(() => { showLkhApprovalInfoModal = true; selectedLkhno = lkh.lkhno; loadLkhApprovalDetail(lkh.lkhno); }, 100)"
                                                   class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-700 hover:bg-amber-200 transition-colors cursor-pointer font-medium">
                                               <span x-text="lkh.approval_status.replace('Waiting ', 'Waiting')"></span>
                                           </button>
                                       </template>
                                   </td>
                                   <td class="border border-gray-300 px-3 py-3">
                                       <div class="flex justify-center space-x-1">
                                           <!-- View Button - Gray/Black -->
                                           <a :href="lkh.view_url"
                                              class="bg-gray-500 hover:bg-gray-600 text-white px-2 py-1 rounded text-xs transition-colors font-medium">
                                               View
                                           </a>
                                           
                                           <!-- Edit Button - Blue (only show if status is NOT EMPTY) -->
                                           <template x-if="lkh.can_edit && lkh.status !== 'EMPTY'">
                                               <a :href="lkh.edit_url" 
                                                  class="bg-blue-600 hover:bg-blue-800 text-white px-2 py-1 rounded text-xs transition-colors font-medium">
                                                   Edit
                                               </a>
                                           </template>
                                           
                                           <template x-if="lkh.can_submit">
                                               <button @click="submitLKH(lkh.lkhno)"
                                                       class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded text-xs transition-colors font-medium">
                                                   Submit
                                               </button>
                                           </template>
                                       </div>
                                   </td>
                               </tr>
                           </template>
                           <tr x-show="!Array.isArray(lkhData) || lkhData.length === 0">
                               <td colspan="9" class="border border-gray-300 px-3 py-8 text-center text-gray-500">
                                   <template x-if="!Array.isArray(lkhData)">
                                       <div class="text-sm">Loading data LKH...</div>
                                   </template>
                                   <template x-if="Array.isArray(lkhData) && lkhData.length === 0">
                                       <div>
                                           <div class="text-sm text-gray-600 mb-2">Belum ada LKH yang dibuat untuk RKH ini</div>
                                           <div class="text-xs text-gray-500">LKH akan otomatis dibuat setelah RKH mendapat persetujuan</div>
                                       </div>
                                   </template>
                               </td>
                           </tr>
                       </tbody>
                   </table>
               </div>
           </div>
       </div>
       
       <!-- Footer -->
       <div x-show="!isLkhModalLoading" class="flex justify-between items-center p-6 border-t border-gray-200 bg-gray-50">
           <div class="text-sm text-gray-600">
               <span class="font-medium">Total:</span> <span x-text="Array.isArray(lkhData) ? lkhData.length : 0"></span> LKH
               <template x-if="Array.isArray(lkhData) && lkhData.length > 0">
                   <span class="ml-4">
                       <span class="font-medium">Workers:</span> <span x-text="lkhData.reduce((sum, lkh) => sum + (lkh.workers_assigned || 0), 0)"></span>
                       | <span class="font-medium">Materials:</span> <span x-text="lkhData.reduce((sum, lkh) => sum + (lkh.material_count || 0), 0)"></span> items
                   </span>
               </template>
           </div>
           <button @click="showLKHModal = false; lkhData = []"
                   class="bg-white border border-gray-300 hover:border-gray-400 text-gray-700 px-6 py-2 text-sm rounded-lg font-medium transition-colors hover:bg-gray-50">
               Close
           </button>
       </div>
   </div>
</div>