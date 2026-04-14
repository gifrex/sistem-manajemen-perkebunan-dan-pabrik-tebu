{{-- resources/views/transaction/rencanakerjaharian/wizard-steps/step4-materials.blade.php --}}
<div class="space-y-4">
  
  {{-- Header --}}
  <div class="mb-4">
    <h3 class="text-xl font-bold text-gray-800 mb-1">Select Materials</h3>
    <p class="text-sm text-gray-600">Choose herbicide group for each plot (different plots can use different materials)</p>
  </div>

  {{-- Progress Info - Compact --}}
  <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-4">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
          </svg>
        </div>
        <div>
          <p class="text-xs font-medium text-gray-600 mb-0.5">Material Selection Progress</p>
          <p class="text-xl font-bold text-green-700">
            <span x-text="getCompletedMaterialCount()"></span>
            <span class="text-sm text-gray-400 font-normal">/</span>
            <span x-text="getRequiredMaterialCount()"></span>
            <span class="text-xs text-gray-500 font-normal ml-1">plots</span>
          </p>
        </div>
      </div>
      
      {{-- Progress Bar - Compact --}}
      <div class="w-32">
        <div class="relative">
          <div class="flex mb-1 items-center justify-between">
            <span class="text-xs font-semibold text-green-600" 
                  x-text="`${getMaterialProgress()}%`">
            </span>
          </div>
          <div class="overflow-hidden h-2 flex rounded-full bg-green-100">
            <div 
              class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-green-500 to-green-600 transition-all duration-500"
              :style="`width: ${getMaterialProgress()}%`">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Materials by Activity --}}
  <div class="space-y-4">
    
    <template x-for="(activity, actCode) in selectedActivities" :key="actCode">
      <div x-show="activity.usingmaterial == 1 && plotAssignments[actCode]?.length > 0" 
           class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        
        {{-- Activity Header --}}
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200 px-4 py-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                </svg>
              </div>
              <div>
                <span class="text-xs font-bold text-green-600" x-text="actCode"></span>
                <p class="text-sm font-semibold text-gray-800" x-text="activity.name"></p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-xs text-gray-500">Plots Selected</p>
              <p class="text-lg font-bold text-green-600" x-text="getMaterialCountForActivity(actCode)"></p>
            </div>
          </div>
        </div>

        {{-- Plot Material Cards --}}
        <div class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            
            <template x-for="plot in (plotAssignments[actCode] || [])" :key="`${actCode}-${plot.blok}-${plot.plot}`">
              <div 
                class="bg-gray-50 rounded-lg border-2 p-4 transition-all hover:shadow-md"
                :class="materials[`${actCode}_${plot.blok}_${plot.plot}`] ? 'border-green-400 bg-green-50' : 'border-gray-200'">
                
                {{-- Plot Header --}}
                <div class="flex items-center justify-between mb-3">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-gradient-to-br from-gray-600 to-gray-700 rounded-lg flex items-center justify-center flex-shrink-0">
                      <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm font-bold text-gray-800" x-text="`${plot.blok}-${plot.plot}`"></p>
                      <p class="text-xs text-gray-500" x-text="`${luasConfirmed[`${actCode}_${plot.blok}_${plot.plot}`] || 0} Ha`"></p>
                    </div>
                  </div>
                  
                  {{-- Status Badge --}}
                  <span 
                    x-show="materials[`${actCode}_${plot.blok}_${plot.plot}`]"
                    class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-bold bg-green-500 text-white rounded-full">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Selected
                  </span>
                </div>

                {{-- Material Selection --}}
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-2">
                    Herbicide Group <span class="text-red-500">*</span>
                  </label>
                  
                  <div class="space-y-2">
                    <template x-for="group in getAvailableMaterialGroups(actCode)" :key="group.herbisidagroupid">
                      <div 
                        class="border-2 rounded-lg transition-all overflow-hidden"
                        :class="{
                          'border-green-500 bg-white shadow-sm': materials[`${actCode}_${plot.blok}_${plot.plot}`]?.groupid === group.herbisidagroupid,
                          'border-gray-200 bg-gray-50': materials[`${actCode}_${plot.blok}_${plot.plot}`]?.groupid !== group.herbisidagroupid
                        }"
                        x-data="{ expanded: false }">
                        
                        {{-- Header: Radio + Group Name (Clickable) --}}
                        <label class="flex items-start p-2.5 cursor-pointer hover:bg-white transition-colors">
                          <input 
                            type="radio" 
                            :name="`material_${actCode}_${plot.blok}_${plot.plot}`"
                            :value="group.herbisidagroupid"
                            @change="selectMaterial(actCode, plot, group)"
                            :checked="materials[`${actCode}_${plot.blok}_${plot.plot}`]?.groupid === group.herbisidagroupid"
                            class="mt-1 w-4 h-4 text-green-600 focus:ring-green-500 flex-shrink-0">
                          <div class="ml-3 flex-1">
                            <div class="flex items-center justify-between">
                              <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-800" x-text="group.herbisidagroupname"></p>
                                <p class="text-xs text-gray-500 mt-0.5" x-text="group.description"></p>
                              </div>
                              
                              {{-- Toggle Button --}}
                              <button 
                                type="button"
                                @click.stop="expanded = !expanded"
                                class="ml-2 p-1 text-gray-500 hover:text-gray-700 transition-colors flex-shrink-0">
                                <svg 
                                  class="w-4 h-4 transform transition-transform duration-200"
                                  :class="expanded ? 'rotate-180' : ''"
                                  fill="none" 
                                  stroke="currentColor" 
                                  viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                              </button>
                            </div>
                          </div>
                        </label>
                        
                        {{-- Expandable Material Items --}}
                        <div 
                          x-show="expanded"
                          x-transition:enter="transition ease-out duration-200"
                          x-transition:enter-start="opacity-0 transform scale-95 -translate-y-2"
                          x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                          x-transition:leave="transition ease-in duration-150"
                          x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
                          x-transition:leave-end="opacity-0 transform scale-95 -translate-y-2"
                          class="border-t border-gray-200 bg-white px-2.5 pb-2.5 pt-2">
                          <p class="text-xs font-semibold text-gray-600 mb-1.5">Materials in this group:</p>
                          <div class="space-y-1">
                            <template x-for="item in group.items" :key="item.itemcode">
                              <div class="flex items-center gap-1.5 text-xs">
                                <span class="inline-block px-1.5 py-0.5 text-[10px] font-mono bg-gray-100 text-gray-700 rounded">
                                  <span x-text="item.itemcode"></span>
                                </span>
                                <span class="text-gray-600 flex-1" x-text="item.itemname"></span>
                              </div>
                            </template>
                          </div>
                        </div>
                      </div>
                    </template>
                  </div>
                  
                  {{-- No Materials Available --}}
                  <div x-show="getAvailableMaterialGroups(actCode).length === 0" 
                       class="text-center py-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <svg class="w-6 h-6 mx-auto text-yellow-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <p class="text-xs text-yellow-700 font-medium">No material groups available</p>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </template>
  </div>

  {{-- No Materials Needed Info --}}
  <div x-show="getRequiredMaterialCount() === 0">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
      <svg class="w-12 h-12 mx-auto text-blue-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      <h3 class="text-lg font-bold text-gray-800 mb-1">No Materials Required</h3>
      <p class="text-sm text-gray-600">None of your selected activities require materials. You can proceed to the next step.</p>
    </div>
  </div>

  {{-- Validation Warning --}}
  <div x-show="getRequiredMaterialCount() > 0 && !allMaterialsSelected()">
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded-lg">
      <div class="flex items-start">
        <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <div>
          <h3 class="text-sm font-semibold text-yellow-800 mb-1">Incomplete Selection</h3>
          <p class="text-xs text-yellow-700">Please select herbicide group for all plots before proceeding.</p>
        </div>
      </div>
    </div>
  </div>

</div>