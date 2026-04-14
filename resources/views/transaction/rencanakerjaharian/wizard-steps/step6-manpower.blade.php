{{-- resources/views/transaction/rencanakerjaharian/wizard-steps/step6-manpower.blade.php --}}

<div class="space-y-4">
  
  {{-- Header with Simple Summary --}}
  <div class="mb-6">
    <h3 class="text-xl font-bold text-gray-800 mb-2">Input Manpower</h3>
    <div class="flex items-center gap-6 text-sm">
      <p class="text-gray-600">Specify the number of workers for each activity</p>
      <div class="flex items-center gap-4 ml-auto">
        <div class="flex items-center gap-2">
          <span class="text-gray-500">Male:</span>
          <span class="font-semibold text-blue-600" x-text="getTotalWorkers('laki')"></span>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-gray-500">Female:</span>
          <span class="font-semibold text-pink-600" x-text="getTotalWorkers('perempuan')"></span>
        </div>
        <div class="flex items-center gap-2 pl-4 border-l border-gray-300">
          <span class="text-gray-500">Total:</span>
          <span class="font-bold text-purple-600 text-lg" x-text="getTotalWorkers('total')"></span>
          <span class="text-xs text-gray-400">workers</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Manpower Input Cards --}}
  <div class="space-y-3">
    
    <template x-for="(activity, actCode) in selectedActivities" :key="actCode">
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:border-gray-300 transition-colors">
        
        {{-- Activity Header --}}
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200 px-4 py-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 flex-1">
              <span class="text-sm font-bold text-indigo-600" x-text="actCode"></span>
              <span class="text-gray-300">|</span>
              <div class="flex-1">
                <div class="flex items-center gap-2">
                  <span class="text-sm font-semibold text-gray-800" x-text="activity.name"></span>
                  <span 
                    class="inline-block px-2 py-0.5 text-[9px] font-bold rounded"
                    :class="{
                      'bg-blue-100 text-blue-700': activity.jenistenagakerja == 1,
                      'bg-green-100 text-green-700': activity.jenistenagakerja == 2,
                      'bg-orange-100 text-orange-700': activity.jenistenagakerja == 3,
                      'bg-purple-100 text-purple-700': activity.jenistenagakerja == 4
                    }"
                    x-text="getJenisLabel(activity.jenistenagakerja)">
                  </span>
                </div>
              </div>
            </div>
            
            {{-- Total Badge --}}
            <div class="flex items-center gap-2">
              <span class="text-xs text-gray-500">Total:</span>
              <span 
                class="text-xl font-bold tabular-nums"
                :class="workers[actCode]?.total > 0 ? 'text-indigo-600' : 'text-gray-400'"
                x-text="workers[actCode]?.total || 0">
              </span>
              <span class="text-xs text-gray-500">workers</span>
            </div>
          </div>
        </div>

        {{-- Input Form --}}
        <div class="p-4">
          <div class="grid grid-cols-3 gap-3">
            
            {{-- Male Input --}}
            <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
              <div class="flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="text-xs font-semibold text-blue-700">Male</span>
              </div>
              <input
                type="number"
                min="0"
                max="999"
                x-model="workers[actCode].laki"
                @input="updateWorkerTotal(actCode)"
                placeholder="0"
                class="w-full px-3 py-2 border border-blue-300 rounded-lg text-lg font-bold text-blue-700 text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white transition-all"
              >
            </div>

            {{-- Female Input --}}
            <div class="bg-pink-50 rounded-lg p-3 border border-pink-200">
              <div class="flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="text-xs font-semibold text-pink-700">Female</span>
              </div>
              <input
                type="number"
                min="0"
                max="999"
                x-model="workers[actCode].perempuan"
                @input="updateWorkerTotal(actCode)"
                placeholder="0"
                class="w-full px-3 py-2 border border-pink-300 rounded-lg text-lg font-bold text-pink-700 text-center focus:ring-2 focus:ring-pink-500 focus:border-pink-500 bg-white transition-all"
              >
            </div>

            {{-- Total Display --}}
            <div class="bg-purple-50 rounded-lg p-3 border border-purple-200">
              <div class="flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span class="text-xs font-semibold text-purple-700">Total</span>
              </div>
              <div class="w-full px-3 py-2 border border-purple-300 rounded-lg text-lg font-bold text-purple-700 text-center bg-white">
                <span x-text="workers[actCode]?.total || 0"></span>
              </div>
            </div>

          </div>

          {{-- Quick Suggestions from Absen --}}
          <div x-show="getAbsenSuggestion(actCode).total > 0" 
               class="mt-3 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
            <div class="flex items-start gap-2">
              <svg class="w-4 h-4 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <div class="flex-1">
                <p class="text-xs font-semibold text-yellow-800 mb-1">Today's Attendance Available</p>
                <div class="flex items-center gap-3">
                  <p class="text-xs text-yellow-700">
                    Male: <span class="font-bold" x-text="getAbsenSuggestion(actCode).laki"></span> • 
                    Female: <span class="font-bold" x-text="getAbsenSuggestion(actCode).perempuan"></span> • 
                    Total: <span class="font-bold" x-text="getAbsenSuggestion(actCode).total"></span>
                  </p>
                  <button
                    type="button"
                    @click="applyAbsenSuggestion(actCode)"
                    class="px-3 py-1 text-xs font-medium bg-yellow-600 hover:bg-yellow-700 text-white rounded transition-colors">
                    Apply
                  </button>
                </div>
              </div>
            </div>
          </div>

        </div>

      </div>
    </template>

  </div>

  {{-- Validation Warning --}}
  <div x-show="!allWorkersCompleted()">
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded-lg">
      <div class="flex items-start">
        <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <div>
          <h3 class="text-sm font-semibold text-yellow-800 mb-1">Incomplete Information</h3>
          <p class="text-xs text-yellow-700">Please fill in the number of workers for all activities. You can input 0 if no workers are needed.</p>
        </div>
      </div>
    </div>
  </div>

</div>