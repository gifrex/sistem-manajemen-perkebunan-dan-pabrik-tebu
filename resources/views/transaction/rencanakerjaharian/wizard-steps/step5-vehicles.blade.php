{{-- resources/views/transaction/rencanakerjaharian/wizard-steps/step5-vehicles.blade.php --}}

<div class="space-y-4">
  
  {{-- Header - Compact --}}
  <div class="flex items-center justify-between mb-4">
    <div>
      <h3 class="text-xl font-bold text-gray-800">Assign Vehicles</h3>
      <p class="text-sm text-gray-600">Select vehicles and operators for activities</p>
    </div>
    <div class="text-right">
      <div class="text-2xl font-bold text-orange-600" x-text="getTotalVehiclesAssigned()"></div>
      <div class="text-xs text-gray-500">Total Units</div>
    </div>
  </div>

  {{-- Progress Info - Compact --}}
  <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
        </svg>
        <span class="text-sm font-medium text-gray-700">
          <span class="text-lg font-bold text-orange-700" x-text="getActivitiesWithVehicles()"></span>
          <span class="text-gray-500">/</span>
          <span x-text="getActivitiesRequiringVehicles()"></span>
          activities completed
        </span>
      </div>
    </div>
  </div>

  {{-- Activities Requiring Vehicles - Compact --}}
  <div class="space-y-3">
    
    <template x-for="(activity, actCode) in selectedActivities" :key="actCode">
      <div x-show="activity.usingvehicle == 1" 
           class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        
        {{-- Activity Header - Compact --}}
        <div class="bg-orange-50 border-b border-orange-200 px-4 py-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 bg-orange-500 text-white text-xs font-bold rounded" x-text="actCode"></span>
              <span class="text-sm font-semibold text-gray-800" x-text="activity.name"></span>
            </div>
            <div class="flex items-center gap-3">
              <div class="text-right">
                <span class="text-xs text-gray-500">Vehicles: </span>
                <span class="text-lg font-bold text-orange-600" x-text="(vehicles[actCode] || []).length"></span>
              </div>
              <button
                type="button"
                @click="openVehicleSelector(actCode)"
                class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-xs font-medium transition-colors flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add
              </button>
            </div>
          </div>
        </div>

        {{-- Assigned Vehicles List - Compact --}}
        <div class="p-4">
          
          {{-- Vehicles Grid - Compact --}}
          <div x-show="(vehicles[actCode] || []).length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <template x-for="(vehicle, vIndex) in (vehicles[actCode] || [])" :key="`${actCode}-vehicle-${vIndex}`">
              <div class="bg-gray-50 rounded-lg border border-gray-200 p-3 hover:border-orange-300 transition-all">
                
                <div class="flex items-start justify-between mb-2">
                  {{-- Vehicle Info --}}
                  <div class="flex items-center gap-2 flex-1">
                    <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center flex-shrink-0">
                      <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm font-bold text-gray-800" x-text="vehicle.nokendaraan"></p>
                      <p class="text-[10px] text-gray-500" x-text="vehicle.vehicle_type || 'Vehicle'"></p>
                    </div>
                  </div>

                  {{-- Delete Button --}}
                  <button
                    type="button"
                    @click="removeVehicle(actCode, vIndex)"
                    class="text-red-500 hover:text-red-700 hover:bg-red-50 rounded p-1 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>

                {{-- Operator Info - Compact --}}
                <div class="bg-white rounded p-2 border border-gray-200">
                  <p class="text-[10px] text-gray-500 mb-0.5">Operator</p>
                  <p class="text-xs font-semibold text-gray-800" x-text="vehicle.operator_name || 'No Operator'"></p>
                </div>

                {{-- Helper Info (if exists) - Compact --}}
                <div x-show="vehicle.helperid" class="bg-blue-50 rounded p-2 border border-blue-200 mt-2">
                  <p class="text-[10px] text-blue-600 mb-0.5">Helper</p>
                  <p class="text-xs font-semibold text-gray-800" x-text="vehicle.helper_name"></p>
                </div>

              </div>
            </template>
          </div>

          {{-- Empty State - Compact --}}
          <div x-show="!(vehicles[actCode] || []).length" class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
            </svg>
            <p class="text-gray-500 text-sm font-medium mb-1">No vehicles assigned yet</p>
            <p class="text-xs text-gray-400">Click "Add" button to assign vehicles</p>
          </div>

        </div>

      </div>
    </template>

  </div>

  {{-- No Vehicles Needed Info --}}
  <div x-show="getActivitiesRequiringVehicles() === 0" class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
    <svg class="w-12 h-12 mx-auto text-blue-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    <h3 class="text-lg font-bold text-gray-800 mb-1">No Vehicles Required</h3>
    <p class="text-sm text-gray-600">None of your selected activities require vehicles.</p>
  </div>

  {{-- Validation Warning --}}
  <div x-show="getActivitiesRequiringVehicles() > 0 && !allVehiclesAssigned()">
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded-lg">
      <div class="flex items-start">
        <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <div>
          <h3 class="text-sm font-semibold text-yellow-800 mb-1">Incomplete Assignment</h3>
          <p class="text-xs text-yellow-700">Please assign at least one vehicle for each activity that requires vehicles.</p>
        </div>
      </div>
    </div>
  </div>

</div>

{{-- Vehicle Selection Modal - Keep existing modal component --}}
<div 
  x-data="vehicleSelectionModal()"
  x-show="showModal"
  x-cloak
  class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4"
  @keydown.escape.window="closeModal()">
  
  <div 
    @click.away="closeModal()"
    class="bg-white rounded-lg shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col">
    
    {{-- Modal Header - Compact --}}
    <div class="bg-orange-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
      <div>
        <h3 class="text-lg font-bold text-gray-800">Select Vehicle & Operator</h3>
        <p class="text-xs text-gray-600 mt-1">For activity: <span class="font-semibold" x-text="currentActivityCode"></span></p>
      </div>
      <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    {{-- Search & Helper Toggle - Compact --}}
    <div class="border-b border-gray-200 px-6 py-3">
      <div class="flex items-center gap-4">
        <div class="flex-1 relative">
          <input 
            type="text" 
            x-model="searchQuery"
            placeholder="Search vehicle or operator..."
            class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
          <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" x-model="useHelper" class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500">
          <span class="text-sm font-medium text-gray-700">Use Helper</span>
        </label>
      </div>
    </div>

    {{-- Modal Content - Compact --}}
    <div class="flex-1 overflow-y-auto p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        
        <template x-for="vehicle in filteredVehicles()" :key="vehicle.nokendaraan">
          <div 
            @click="selectVehicle(vehicle)"
            class="bg-gray-50 rounded-lg border-2 p-3 cursor-pointer transition-all hover:border-orange-400 hover:bg-orange-50"
            :class="selectedVehicle?.nokendaraan === vehicle.nokendaraan ? 'border-orange-500 bg-orange-50' : 'border-gray-200'">
            
            <div class="flex items-center gap-3 mb-2">
              <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                </svg>
              </div>
              <div>
                <p class="text-sm font-bold text-gray-800" x-text="vehicle.nokendaraan"></p>
                <p class="text-xs text-gray-500" x-text="vehicle.vehicle_type || 'Vehicle'"></p>
              </div>
            </div>

            <div class="bg-white rounded p-2 border border-gray-200">
              <p class="text-[10px] text-gray-500 mb-0.5">Operator</p>
              <p class="text-xs font-semibold text-gray-800" x-text="vehicle.operator_name || 'No Operator'"></p>
            </div>

          </div>
        </template>

      </div>
    </div>

    {{-- Helper Selection (if enabled) - Compact --}}
    <div x-show="useHelper && selectedVehicle" class="border-t border-gray-200 px-6 py-3 bg-blue-50">
      <p class="text-xs font-semibold text-gray-700 mb-2">Select Helper:</p>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-2 max-h-32 overflow-y-auto">
        <template x-for="helper in availableHelpers()" :key="helper.tenagakerjaid">
          <label 
            class="flex items-center gap-2 p-2 bg-white border-2 rounded-lg cursor-pointer hover:bg-blue-50 transition-colors"
            :class="selectedHelper?.tenagakerjaid === helper.tenagakerjaid ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
            <input 
              type="radio" 
              name="helper"
              :value="helper.tenagakerjaid"
              @change="selectHelper(helper)"
              :checked="selectedHelper?.tenagakerjaid === helper.tenagakerjaid"
              class="w-4 h-4 text-blue-600">
            <span class="text-xs font-medium text-gray-800" x-text="helper.nama"></span>
          </label>
        </template>
      </div>
    </div>

    {{-- Modal Footer - Compact --}}
    <div class="border-t border-gray-200 px-6 py-3 flex justify-between items-center bg-gray-50">
      <p class="text-xs text-gray-600">
        Selected: <span class="font-semibold" x-text="selectedVehicle ? selectedVehicle.nokendaraan : 'None'"></span>
      </p>
      <div class="flex gap-2">
        <button 
          @click="closeModal()"
          class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition-colors">
          Cancel
        </button>
        <button 
          @click="confirmSelection()"
          :disabled="!selectedVehicle"
          :class="selectedVehicle ? 'bg-orange-500 hover:bg-orange-600' : 'bg-gray-300 cursor-not-allowed'"
          class="px-6 py-2 text-white rounded-lg text-sm font-medium transition-colors">
          Add Vehicle
        </button>
      </div>
    </div>

  </div>
</div>