{{-- resources/views/masterdata/open-rework/index.blade.php --}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div 
    x-data="openReworkData()"
    class="mx-auto py-1 bg-white rounded-md shadow-md">

    <!-- Header Actions -->
    <div class="flex items-center justify-between px-4 py-2 border-b">
      <div class="flex gap-2">
        <button @click="openRequestModal()"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
          </svg>
          Request Rework
        </button>
      </div>

      <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
        <label for="search" class="text-xs font-medium text-gray-700">Search:</label>
        <input
          type="text"
          name="search"
          id="search"
          value="{{ request('search') }}"
          class="text-xs mt-1 block w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          onkeydown="if(event.key==='Enter') this.form.submit()"
        />
      </form>

      <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
        <label for="perPage" class="text-xs font-medium text-gray-700">Items per page:</label>
        <select 
          name="perPage" id="perPage"
          onchange="this.form.submit()"
          class="text-xs mt-1 block w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="10" {{ (int)request('perPage', $perPage) === 10 ? 'selected' : '' }}>10</option>
            <option value="20" {{ (int)request('perPage', $perPage) === 20 ? 'selected' : '' }}>20</option>
            <option value="50" {{ (int)request('perPage', $perPage) === 50 ? 'selected' : '' }}>50</option>
        </select>
      </form>
    </div>

    <!-- Request History Table -->
    <div class="mx-auto px-4 py-2">
      <h3 class="text-lg font-semibold mb-3">Riwayat Request Open Rework</h3>
      <div class="overflow-x-auto border border-gray-300 rounded-md">
        <table class="min-w-full bg-white text-sm">
          <thead>
            <tr class="bg-gray-100 text-gray-700">
              <th class="py-2 px-3 border-b text-center" style="width: 3%;">No.</th>
              <th class="py-2 px-3 border-b text-center" style="width: 10%;">Transaction No</th>
              <th class="py-2 px-3 border-b text-center" style="width: 8%;">Tanggal</th>
              <th class="py-2 px-3 border-b text-center" style="width: 10%;">Status Approval</th>
              <th class="py-2 px-3 border-b text-left" style="width: 15%;">Plot</th>
              <th class="py-2 px-3 border-b text-left" style="width: 15%;">Activities</th>
              <th class="py-2 px-3 border-b text-left" style="width: 20%;">Alasan</th>
              <th class="py-2 px-3 border-b text-left" style="width: 10%;">Input By</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($requests as $index => $req)
            <tr class="hover:bg-gray-50">
              <td class="py-2 px-3 border-b text-center">{{ $requests->firstItem() + $index }}</td>
              
              <td class="py-2 px-3 border-b text-center">
                <span class="font-mono text-sm font-semibold text-gray-700">{{ $req->transactionnumber }}</span>
              </td>
              
              <td class="py-2 px-3 border-b text-center">{{ $req->formatted_date }}</td>
              
              <!-- Status Approval -->
              <td class="py-2 px-3 border-b text-center">
                @if($req->approvalstatus === null)
                  @php
                    $total = $req->jumlahapproval ?? 0;
                    $completed = 0;
                    if($req->approval1flag == '1') $completed++;
                    if($req->approval2flag == '1') $completed++;
                    if($req->approval3flag == '1') $completed++;
                    $waitingText = $total == 0 ? "Pending" : "Pending ({$completed}/{$total})";
                  @endphp
                  <button @click="showApprovalDetailModal = true; selectedApprovalNo = '{{ $req->approvalno }}'; loadApprovalDetail('{{ $req->approvalno }}')"
                          class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 hover:bg-yellow-200 cursor-pointer">
                    {{ $waitingText }}
                  </button>
                @elseif($req->approvalstatus === '1')
                  <button @click="showApprovalDetailModal = true; selectedApprovalNo = '{{ $req->approvalno }}'; loadApprovalDetail('{{ $req->approvalno }}')"
                          class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 hover:bg-green-200 cursor-pointer">
                    Approved
                  </button>
                @elseif($req->approvalstatus === '0')
                  <button @click="showApprovalDetailModal = true; selectedApprovalNo = '{{ $req->approvalno }}'; loadApprovalDetail('{{ $req->approvalno }}')"
                          class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 hover:bg-red-200 cursor-pointer">
                    Declined
                  </button>
                @else
                  <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
                    No Approval
                  </span>
                @endif
                
                @if($req->approvalno)
                <div class="text-xs text-gray-400 mt-1 font-mono">
                  {{ $req->approvalno }}
                </div>
                @endif
              </td>
              
              <!-- Plot List -->
              <td class="py-2 px-3 border-b text-left">
                <div class="flex flex-wrap gap-1">
                  @foreach($req->plots_array as $plot)
                  <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full font-medium">
                    {{ $plot }}
                  </span>
                  @endforeach
                </div>
              </td>
              
              <!-- Activities List -->
              <td class="py-2 px-3 border-b text-left">
                <div class="flex flex-wrap gap-1">
                  @foreach($req->activities_array as $activity)
                  <span class="px-2 py-0.5 text-xs bg-green-100 text-green-800 rounded-full font-medium">
                    {{ $activity }}
                  </span>
                  @endforeach
                </div>
              </td>
              
              <!-- Alasan -->
              <td class="py-2 px-3 border-b text-left">
                <div class="text-xs" title="{{ $req->reason }}">
                  {{ Str::limit($req->reason ?? '-', 50) }}
                </div>
              </td>
              
              <!-- Input By -->
              <td class="py-2 px-3 border-b text-left">
                <div class="text-xs">
                  <div class="font-medium">{{ $req->inputby }}</div>
                  <div class="text-gray-500">{{ $req->formatted_createdat }}</div>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="py-4 text-center text-gray-500">
                Belum ada request rework
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      
      <div class="mt-4">
        @if ($requests->hasPages())
        {{ $requests->appends(request()->query())->links() }}
        @else
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-700">
            Showing <span class="font-medium">{{ $requests->count() }}</span> of <span class="font-medium">{{ $requests->total() }}</span> results
          </p>
        </div>
        @endif
      </div>
    </div>

    <!-- REQUEST MODAL (Step-by-Step) -->
    <div x-show="showRequestModal" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showRequestModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
             @click="closeRequestModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="showRequestModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
          
          <!-- Header -->
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <h2 class="text-xl font-bold text-white">Request Open Rework</h2>
            <p class="text-sm text-blue-100 mt-1">Pilih LKH yang activity-nya akan dibuka untuk rework</p>
          </div>

          <!-- Step Indicator -->
          <div class="bg-gray-50 px-6 py-3 border-b">
            <div class="flex items-center justify-between max-w-2xl mx-auto">
              <div class="flex items-center" :class="currentStep >= 1 ? 'text-blue-600' : 'text-gray-400'">
                <div class="w-8 h-8 rounded-full flex items-center justify-center" 
                     :class="currentStep >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600'">
                  1
                </div>
                <span class="ml-2 text-sm font-medium">Activity & Periode</span>
              </div>
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
              <div class="flex items-center" :class="currentStep >= 2 ? 'text-blue-600' : 'text-gray-400'">
                <div class="w-8 h-8 rounded-full flex items-center justify-center" 
                     :class="currentStep >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600'">
                  2
                </div>
                <span class="ml-2 text-sm font-medium">Pilih LKH</span>
              </div>
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
              <div class="flex items-center" :class="currentStep >= 3 ? 'text-blue-600' : 'text-gray-400'">
                <div class="w-8 h-8 rounded-full flex items-center justify-center" 
                     :class="currentStep >= 3 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600'">
                  3
                </div>
                <span class="ml-2 text-sm font-medium">Pilih Plot & Submit</span>
              </div>
            </div>
          </div>

          <!-- Body -->
          <div class="bg-white px-6 py-5">
            
            <!-- STEP 1: Activity & Date Range -->
            <div x-show="currentStep === 1">
              <h3 class="text-lg font-semibold mb-4">Step 1: Pilih Activity & Periode</h3>
              
              <div class="space-y-4">
                <!-- Activity Selection -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Pilih Activity <span class="text-red-500">*</span>
                  </label>
                  <select x-model="selectedActivity"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Pilih Activity --</option>
                    @foreach($activities as $activity)
                    <option value="{{ $activity->activitycode }}">{{ $activity->activitycode }} - {{ $activity->activityname }}</option>
                    @endforeach
                  </select>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Dari Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           x-model="startDate"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Sampai Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           x-model="endDate"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  </div>
                </div>

                <button @click="loadLkhList()" 
                        :disabled="!selectedActivity || !startDate || !endDate || loadingLkh"
                        class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                  <svg x-show="loadingLkh" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span x-show="!loadingLkh">Tampilkan LKH</span>
                  <span x-show="loadingLkh">Loading...</span>
                </button>
              </div>
            </div>

            <!-- STEP 2: Select LKH -->
            <div x-show="currentStep === 2">
              <h3 class="text-lg font-semibold mb-4">Step 2: Pilih LKH</h3>
              
              <div class="space-y-2 max-h-96 overflow-y-auto">
                <template x-for="lkh in lkhList" :key="lkh.lkhno">
                  <div @click="selectedLkhno = lkh.lkhno" 
                       class="p-3 border rounded-lg cursor-pointer hover:bg-blue-50 transition-colors"
                       :class="selectedLkhno === lkh.lkhno ? 'border-blue-500 bg-blue-50' : 'border-gray-300'">
                    <div class="flex items-start justify-between">
                      <div class="flex-1">
                        <div class="font-semibold text-blue-700" x-text="lkh.lkhno"></div>
                        <div class="text-xs text-gray-600 mt-1">
                          Tanggal: <span x-text="lkh.formatted_date"></span>
                        </div>
                        <div class="text-xs text-gray-600 mt-1">
                          Plot: <span x-text="lkh.plots"></span>
                        </div>
                      </div>
                      <div class="text-right">
                        <div class="text-xs text-gray-500">
                          Hasil: <span class="font-semibold" x-text="parseFloat(lkh.totalhasil).toFixed(2)"></span> Ha
                        </div>
                        <div class="text-xs text-gray-500">
                          Total Plot: <span class="font-semibold" x-text="lkh.total_plots || 0"></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>

                <div x-show="lkhList.length === 0" class="text-center py-8 text-gray-500">
                  Tidak ada LKH ditemukan untuk periode ini
                </div>
              </div>

              <div x-show="lkhList.length > 0" class="mt-4 pt-4 border-t">
                <button @click="loadPlotDetails()" 
                        :disabled="!selectedLkhno || loadingPlots"
                        class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                  <svg x-show="loadingPlots" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span x-show="!loadingPlots">Lanjut ke Pilih Plot →</span>
                  <span x-show="loadingPlots">Loading...</span>
                </button>
              </div>
            </div>

            <!-- STEP 3: Select Plots & Reason -->
            <div x-show="currentStep === 3">
              <h3 class="text-lg font-semibold mb-4">Step 3: Pilih Plot & Alasan</h3>
              
              <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="text-sm">
                  <span class="font-semibold text-gray-700">LKH:</span>
                  <span class="ml-2 font-mono text-blue-700" x-text="selectedLkhno"></span>
                </div>
                <div class="text-sm mt-1">
                  <span class="font-semibold text-gray-700">Activity:</span>
                  <span class="ml-2 text-gray-900" x-text="getActivityName(selectedActivity)"></span>
                </div>
              </div>

              <!-- Plot List Table -->
              <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  Pilih Plot untuk Rework <span class="text-red-500">*</span>
                  <span class="text-xs font-normal text-gray-500 ml-2">(<span x-text="selectedPlots.length"></span> terpilih)</span>
                </label>
                <div class="border border-gray-300 rounded-lg overflow-hidden">
                  <table class="min-w-full text-sm">
                    <thead class="bg-gray-100">
                      <tr>
                        <th class="py-2 px-3 text-left" style="width: 5%;">
                          <input type="checkbox" 
                                 @change="toggleAllPlots($event.target.checked)"
                                 :disabled="plotDetails.filter(p => p.can_select).length === 0"
                                 class="rounded border-gray-300">
                        </th>
                        <th class="py-2 px-3 text-left" style="width: 10%;">Blok</th>
                        <th class="py-2 px-3 text-left" style="width: 15%;">Plot</th>
                        <th class="py-2 px-3 text-right" style="width: 15%;">Luas Rencana</th>
                        <th class="py-2 px-3 text-right" style="width: 15%;">Luas Hasil</th>
                        <th class="py-2 px-3 text-right" style="width: 15%;">Luas Sisa</th>
                        <th class="py-2 px-3 text-center" style="width: 15%;">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <template x-for="plot in plotDetails" :key="plot.plot">
                        <tr class="hover:bg-gray-50"
                            :class="!plot.can_select ? 'bg-gray-100 opacity-60' : ''">
                          <td class="py-2 px-3">
                            <input type="checkbox" 
                                   :value="plot.plot"
                                   x-model="selectedPlots"
                                   :disabled="!plot.can_select"
                                   class="rounded border-gray-300">
                          </td>
                          <td class="py-2 px-3 text-gray-600" x-text="plot.blok"></td>
                          <td class="py-2 px-3 font-medium" x-text="plot.plot"></td>
                          <td class="py-2 px-3 text-right" x-text="plot.luas_rencana + ' Ha'"></td>
                          <td class="py-2 px-3 text-right" x-text="plot.luas_hasil + ' Ha'"></td>
                          <td class="py-2 px-3 text-right" x-text="plot.luas_sisa + ' Ha'"></td>
                          <td class="py-2 px-3 text-center">
                            <span x-show="plot.rework == 0" 
                                  class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                              Bisa Rework
                            </span>
                            <span x-show="plot.rework == 1" 
                                  class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                              Sudah Rework
                            </span>
                          </td>
                        </tr>
                      </template>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Reason -->
              <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  Alasan Request Rework <span class="text-red-500">*</span>
                </label>
                <textarea 
                  x-model="reason"
                  rows="3"
                  placeholder="Contoh: Activity gagal karena hujan, perlu dikerjakan ulang"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  maxlength="500"
                ></textarea>
                <p class="text-xs text-gray-500 mt-1">
                  <span x-text="reason.length"></span>/500 karakter
                </p>
              </div>

            </div>

          </div>

          <!-- Footer -->
          <div class="bg-gray-50 px-6 py-4 flex justify-between border-t">
            <button 
              @click="goToPreviousStep()" 
              x-show="currentStep > 1"
              type="button"
              :disabled="loading"
              class="px-5 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed">
              ← Kembali
            </button>

            <button 
              @click="closeRequestModal()" 
              x-show="currentStep === 1"
              type="button"
              :disabled="loading"
              class="px-5 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed">
              Batal
            </button>
            
            <button 
              @click="submitRequest()"
              x-show="currentStep === 3"
              :disabled="loading || selectedPlots.length === 0 || !reason"
              type="button"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium shadow-sm transition-all flex items-center gap-2">
              <svg x-show="loading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span x-show="!loading">Submit Request</span>
              <span x-show="loading">Processing...</span>
            </button>
          </div>

        </div>
      </div>
    </div>

    <!-- Approval Detail Modal -->
    <div x-show="showApprovalDetailModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showApprovalDetailModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
             @click="showApprovalDetailModal = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="showApprovalDetailModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          
          <!-- Modal Header -->
          <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-4 py-3 sm:px-6">
            <div class="flex items-center justify-between">
              <h3 class="text-lg leading-6 font-medium text-white">
                Approval Detail
              </h3>
              <button @click="showApprovalDetailModal = false" class="text-white hover:text-gray-200">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Modal Body -->
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
            <div x-show="isLoadingApprovalDetail" class="text-center py-4">
              <svg class="animate-spin h-8 w-8 mx-auto text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <p class="text-sm text-gray-600 mt-2">Loading...</p>
            </div>

            <div x-show="!isLoadingApprovalDetail">
              <!-- Approval Info -->
              <div class="mb-4 p-3 bg-purple-50 rounded-lg border border-purple-200">
                <div class="text-sm">
                  <span class="font-semibold text-gray-700">Approval No:</span>
                  <span class="ml-2 font-mono text-purple-700" x-text="approvalDetail.approvalno"></span>
                </div>
                <div class="text-sm mt-1">
                  <span class="font-semibold text-gray-700">Transaction:</span>
                  <span class="ml-2 text-gray-900" x-text="approvalDetail.transactionnumber"></span>
                </div>
                <div class="text-sm mt-1">
                  <span class="font-semibold text-gray-700">Category:</span>
                  <span class="ml-2 text-gray-900" x-text="approvalDetail.category"></span>
                </div>
              </div>

              <!-- Approval Levels -->
              <div class="space-y-3">
                <template x-if="approvalDetail.jumlahapproval >= 1">
                  <div class="p-3 rounded-lg border" 
                       :class="approvalDetail.approval1flag === '1' ? 'bg-green-50 border-green-200' : 
                               approvalDetail.approval1flag === '0' ? 'bg-red-50 border-red-200' : 
                               'bg-gray-50 border-gray-200'">
                    <div class="flex items-center justify-between">
                      <div>
                        <div class="text-sm font-semibold text-gray-700">Approval Level 1</div>
                        <div class="text-xs text-gray-600 mt-1" x-text="approvalDetail.jabatan1_name || '-'"></div>
                        <div class="text-xs text-gray-500" x-show="approvalDetail.approval1userid">
                          By: <span x-text="approvalDetail.approval1_username"></span>
                        </div>
                        <div class="text-xs text-gray-500" x-show="approvalDetail.approval1date">
                          Date: <span x-text="approvalDetail.approval1date"></span>
                        </div>
                      </div>
                      <div>
                        <span x-show="approvalDetail.approval1flag === '1'" 
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                          ✓ Approved
                        </span>
                        <span x-show="approvalDetail.approval1flag === '0'" 
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                          ✗ Declined
                        </span>
                        <span x-show="approvalDetail.approval1flag === null" 
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                          ○ Pending
                        </span>
                      </div>
                    </div>
                  </div>
                </template>

                <template x-if="approvalDetail.jumlahapproval >= 2">
                  <div class="p-3 rounded-lg border" 
                       :class="approvalDetail.approval2flag === '1' ? 'bg-green-50 border-green-200' : 
                               approvalDetail.approval2flag === '0' ? 'bg-red-50 border-red-200' : 
                               'bg-gray-50 border-gray-200'">
                    <div class="flex items-center justify-between">
                      <div>
                        <div class="text-sm font-semibold text-gray-700">Approval Level 2</div>
                        <div class="text-xs text-gray-600 mt-1" x-text="approvalDetail.jabatan2_name || '-'"></div>
                        <div class="text-xs text-gray-500" x-show="approvalDetail.approval2userid">
                          By: <span x-text="approvalDetail.approval2_username"></span>
                        </div>
                        <div class="text-xs text-gray-500" x-show="approvalDetail.approval2date">
                          Date: <span x-text="approvalDetail.approval2date"></span>
                        </div>
                      </div>
                      <div>
                        <span x-show="approvalDetail.approval2flag === '1'" 
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                          ✓ Approved
                        </span>
                        <span x-show="approvalDetail.approval2flag === '0'" 
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                          ✗ Declined
                        </span>
                        <span x-show="approvalDetail.approval2flag === null" 
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                          ○ Pending
                        </span>
                      </div>
                    </div>
                  </div>
                </template>

                <template x-if="approvalDetail.jumlahapproval >= 3">
                  <div class="p-3 rounded-lg border" 
                       :class="approvalDetail.approval3flag === '1' ? 'bg-green-50 border-green-200' : 
                               approvalDetail.approval3flag === '0' ? 'bg-red-50 border-red-200' : 
                               'bg-gray-50 border-gray-200'">
                    <div class="flex items-center justify-between">
                      <div>
                        <div class="text-sm font-semibold text-gray-700">Approval Level 3</div>
                        <div class="text-xs text-gray-600 mt-1" x-text="approvalDetail.jabatan3_name || '-'"></div>
                        <div class="text-xs text-gray-500" x-show="approvalDetail.approval3userid">
                          By: <span x-text="approvalDetail.approval3_username"></span>
                        </div>
                        <div class="text-xs text-gray-500" x-show="approvalDetail.approval3date">
                          Date: <span x-text="approvalDetail.approval3date"></span>
                        </div>
                      </div>
                      <div>
                        <span x-show="approvalDetail.approval3flag === '1'" 
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                          ✓ Approved
                        </span>
                        <span x-show="approvalDetail.approval3flag === '0'" 
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                          ✗ Declined
                        </span>
                        <span x-show="approvalDetail.approval3flag === null" 
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                          ○ Pending
                        </span>
                      </div>
                    </div>
                  </div>
                </template>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button @click="showApprovalDetailModal = false" 
                    type="button"
                    class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>

  <script>
    function openReworkData() {
      return {
        // Modal states
        showRequestModal: false,
        showApprovalDetailModal: false,
        loading: false,
        loadingLkh: false,
        loadingPlots: false,
        
        // Step management
        currentStep: 1,
        
        // Step 1 data
        selectedActivity: '',
        startDate: '',
        endDate: '',
        
        // Step 2 data
        lkhList: [],
        selectedLkhno: '',
        
        // Step 3 data
        plotDetails: [],
        selectedPlots: [],
        reason: '',
        
        // Approval modal
        selectedApprovalNo: '',
        approvalDetail: {},
        isLoadingApprovalDetail: false,
        
        // Methods
        async openRequestModal() {
          this.showRequestModal = true;
          this.currentStep = 1;
          this.resetForm();
        },
        
        closeRequestModal() {
          this.showRequestModal = false;
          this.resetForm();
        },
        
        resetForm() {
          this.currentStep = 1;
          this.selectedActivity = '';
          this.startDate = '';
          this.endDate = '';
          this.lkhList = [];
          this.selectedLkhno = '';
          this.plotDetails = [];
          this.selectedPlots = [];
          this.reason = '';
        },
        
        goToPreviousStep() {
          if (this.currentStep > 1) {
            this.currentStep--;
            if (this.currentStep === 2) {
              this.selectedLkhno = '';
              this.plotDetails = [];
              this.selectedPlots = [];
            }
            if (this.currentStep === 1) {
              this.lkhList = [];
              this.selectedLkhno = '';
              this.plotDetails = [];
              this.selectedPlots = [];
            }
          }
        },
        
        getActivityName(activitycode) {
          const activities = @json($activities);
          const activity = activities.find(a => a.activitycode === activitycode);
          return activity ? `${activity.activitycode} - ${activity.activityname}` : activitycode;
        },
        
        async loadLkhList() {
          this.loadingLkh = true;
          
          try {
            const response = await fetch('{{ route("masterdata.open-rework.get-lkh-list") }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              },
              body: JSON.stringify({
                activitycode: this.selectedActivity,
                start_date: this.startDate,
                end_date: this.endDate
              })
            });
            
            const data = await response.json();
            
            if (data.success) {
              this.lkhList = data.data;
              this.currentStep = 2;
            } else {
              alert(data.message);
            }
          } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memuat LKH');
          } finally {
            this.loadingLkh = false;
          }
        },
        
        async loadPlotDetails() {
          if (!this.selectedLkhno) {
            alert('Pilih LKH terlebih dahulu');
            return;
          }
          
          this.loadingPlots = true;
          
          try {
            const response = await fetch(`{{ url('masterdata/open-rework/lkh-detail') }}/${this.selectedLkhno}`);
            const data = await response.json();
            
            if (data.success) {
              this.plotDetails = data.data;
              this.currentStep = 3;
            } else {
              alert(data.message);
            }
          } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memuat detail plot');
          } finally {
            this.loadingPlots = false;
          }
        },
        
        toggleAllPlots(checked) {
          if (checked) {
            this.selectedPlots = this.plotDetails.filter(p => p.can_select).map(p => p.plot);
          } else {
            this.selectedPlots = [];
          }
        },
        
        async submitRequest() {
          if (this.selectedPlots.length === 0) {
            alert('Pilih minimal 1 plot');
            return;
          }
          
          if (!this.reason.trim()) {
            alert('Alasan harus diisi');
            return;
          }
          
          this.loading = true;
          
          try {
            const response = await fetch('{{ route("masterdata.open-rework.store") }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              },
              body: JSON.stringify({
                lkhno: this.selectedLkhno,
                plots: this.selectedPlots,
                activities: [this.selectedActivity], // Array dengan 1 activity yang dipilih di step 1
                reason: this.reason
              })
            });
            
            const data = await response.json();
            
            if (data.success) {
              alert(data.message);
              window.location.reload();
            } else {
              alert(data.message);
            }
          } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat submit request');
          } finally {
            this.loading = false;
          }
        },
        
        async loadApprovalDetail(approvalno) {
          this.isLoadingApprovalDetail = true;
          this.approvalDetail = {};
          
          try {
            const response = await fetch(`{{ url('masterdata/open-rework/approval') }}/${approvalno}`);
            const data = await response.json();
            
            if (data.success) {
              this.approvalDetail = data.data;
            } else {
              alert('Gagal memuat detail approval: ' + data.message);
            }
          } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memuat detail approval');
          } finally {
            this.isLoadingApprovalDetail = false;
          }
        }
      }
    }
  </script>
</x-layout>