{{-- resources/views/transaction/rencanakerjaharian/wizard-steps/step1-activities.blade.php --}}

<div class="space-y-3">
  
  {{-- Header - Compact --}}
  <div class="flex items-center justify-between">
    <div>
      <h3 class="text-lg font-bold text-gray-800">Select Activities</h3>
      <p class="text-xs text-gray-600">Choose activities for this RKH (V = Aktifitas dengan Alat, M = Aktivitas dengan Material, B = Aktivitas per Blok)</p>
    </div>
    <div class="text-right">
      <div class="text-xl font-bold text-blue-600" x-text="Object.keys(selectedActivities).length"></div>
      <div class="text-[10px] text-gray-500">Selected</div>
    </div>
  </div>

  {{-- Search & Clear - Compact --}}
  <div class="flex items-center gap-2">
    <div class="flex-1 relative">
      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
      </div>
      <input 
        type="text" 
        x-model="activitySearch"
        placeholder="Search activities..."
        class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
      >
    </div>
    <button 
      type="button"
      @click="selectedActivities = {}"
      x-show="Object.keys(selectedActivities).length > 0"
      class="px-3 py-2 text-xs text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors font-medium whitespace-nowrap">
      Clear All
    </button>
  </div>

  {{-- ✅ Activities Grouped by Database Groups - Compact List --}}
  <div class="space-y-2">
    <template x-for="group in groupedActivities" :key="group.code">
      <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
        {{-- Group Header - Compact --}}
        <div class="bg-gray-50 px-3 py-2 border-b border-gray-200 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-800" x-text="group.code + ' - ' + group.name"></span>
          </div>
          <span class="text-xs text-gray-500"><span x-text="group.activities.length"></span> activities</span>
        </div>

        {{-- Activities in Group - Compact List --}}
        <div class="divide-y divide-gray-100">
          <template x-for="activity in group.activities" :key="activity.activitycode">
            <div 
              @click="toggleActivity(activity)"
              class="group cursor-pointer px-3 py-2 hover:bg-blue-50 transition-all flex items-center gap-3"
              :class="selectedActivities[activity.activitycode] ? 'bg-blue-50' : ''">
              
              {{-- Checkbox - Compact --}}
              <div class="w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0 transition-all"
                   :class="selectedActivities[activity.activitycode] ? 'bg-blue-500 border-blue-500' : 'border-gray-300 group-hover:border-blue-400'">
                <svg x-show="selectedActivities[activity.activitycode]" 
                     class="w-3 h-3 text-white" 
                     fill="none" 
                     stroke="currentColor" 
                     viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>

              {{-- Activity Code --}}
              <div class="w-16 flex-shrink-0">
                <span class="inline-block px-2 py-0.5 text-xs font-bold rounded bg-gray-100 text-gray-700"
                      x-text="activity.activitycode">
                </span>
              </div>

              {{-- Activity Name --}}
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate" 
                   x-text="activity.activityname"
                   :title="activity.activityname">
                </p>
              </div>

              {{-- Badges - Compact (Single Letter) --}}
              <div class="flex items-center gap-1 flex-shrink-0">
                <template x-if="activity.usingmaterial == 1">
                  <span class="inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold bg-green-100 text-green-700 rounded" title="Uses Material">
                    M
                  </span>
                </template>
                
                <template x-if="activity.usingvehicle == 1">
                  <span class="inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold bg-orange-100 text-orange-700 rounded" title="Uses Vehicle">
                    V
                  </span>
                </template>

                <template x-if="activity.isblokactivity == 1">
                  <span class="inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold bg-purple-100 text-purple-700 rounded" title="Blok Activity">
                    B
                  </span>
                </template>
              </div>

            </div>
          </template>
        </div>
      </div>
    </template>
  </div>

  {{-- Empty State --}}
  <div x-show="groupedActivities.length === 0" class="text-center py-12">
    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    <p class="text-gray-500 text-sm font-medium">No activities found</p>
    <p class="text-gray-400 text-xs mt-1">Try adjusting your search</p>
  </div>

</div>