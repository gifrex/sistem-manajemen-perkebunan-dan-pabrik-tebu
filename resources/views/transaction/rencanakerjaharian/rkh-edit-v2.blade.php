{{-- resources/views/transaction/rencanakerjaharian/rkh-edit-v2.blade.php --}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div x-data="rkhEditWizardApp()" x-init="loadExistingData()" class="max-w-7xl mx-auto px-4 pb-6">
    
    {{-- STICKY Progress Bar - Compact & Professional --}}
    <div class="sticky top-[6rem] z-30 bg-white border-b border-gray-200 shadow-sm mb-6">
      <div class="max-w-7xl mx-auto px-6 py-4">
        <div class="flex items-center justify-between relative">
          
          {{-- Progress Line Background --}}
          <div class="absolute top-5 left-0 right-0 h-0.5 bg-gray-200 -z-10"></div>
          
          {{-- Active Progress Line --}}
          <div class="absolute top-5 left-0 h-0.5 bg-blue-600 transition-all duration-500 -z-10"
               :style="`width: ${((currentStep - 1) / 6) * 100}%`"></div>

          {{-- Step Circles --}}
          <template x-for="(step, index) in steps" :key="step.id">
            <div class="flex flex-col items-center flex-1 relative">
              
              {{-- Circle --}}
              <div 
                class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 cursor-pointer hover:scale-110 z-10 text-sm font-bold"
                :class="{
                  'bg-blue-600 text-white shadow-md': currentStep === step.id,
                  'bg-green-600 text-white shadow-sm': currentStep > step.id,
                  'bg-white border-2 border-gray-300 text-gray-400': currentStep < step.id
                }"
                @click="currentStep > step.id ? goToStep(step.id) : null"
              >
                <template x-if="currentStep > step.id">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                  </svg>
                </template>
                <template x-if="currentStep <= step.id">
                  <span x-text="step.id"></span>
                </template>
              </div>

              {{-- Step Label --}}
              <div class="mt-2 text-center">
                <p class="text-xs font-semibold transition-colors"
                   :class="currentStep >= step.id ? 'text-gray-800' : 'text-gray-400'"
                   x-text="step.title">
                </p>
              </div>

            </div>
          </template>

        </div>

        {{-- Current Step Info --}}
        <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-100">
          <div class="text-sm text-gray-600">
            <span class="font-medium">{{ $rkhHeader->mandor_nama ?? 'Loading...' }}</span>
            <span class="text-gray-400 mx-2">•</span>
            <span>{{ \Carbon\Carbon::parse($rkhHeader->rkhdate)->format('d M Y') }}</span>
            <span class="text-gray-400 mx-2">•</span>
            <span class="text-orange-600 font-semibold">EDIT MODE</span>
          </div>
          <div class="flex items-center gap-3">
            <div class="text-xs text-gray-500">
              Step <span class="font-bold" x-text="currentStep"></span> of 7
            </div>
            
            {{-- Quick Next Button --}}
            <button 
              type="button"
              @click="nextStep()" 
              x-show="currentStep < 7"
              :disabled="!canProceed()"
              :class="canProceed() ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm hover:shadow' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
              class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all flex items-center gap-1">
              <span x-text="currentStep === 6 ? 'Review' : 'Next'"></span>
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    {{-- Main Wizard Container --}}
    <div class="bg-white rounded-lg shadow border border-gray-200">
      
      {{-- Step Content Area --}}
      <div class="p-6">

        {{-- STEP 1: Select Activities --}}
        <div x-show="currentStep === 1" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0">
          @include('transaction.rencanakerjaharian.wizard-steps.step1-activities')
        </div>

        {{-- STEP 2: Assign Plots --}}
        <div x-show="currentStep === 2"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0">
          @include('transaction.rencanakerjaharian.wizard-steps.step2-plots')
        </div>

        {{-- STEP 3: Plot Details --}}
        <div x-show="currentStep === 3"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0">
          @include('transaction.rencanakerjaharian.wizard-steps.step3-details')
        </div>

        {{-- STEP 4: Materials --}}
        <div x-show="currentStep === 4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0">
          @include('transaction.rencanakerjaharian.wizard-steps.step4-materials')
        </div>

        {{-- STEP 5: Vehicles --}}
        <div x-show="currentStep === 5"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0">
          @include('transaction.rencanakerjaharian.wizard-steps.step5-vehicles')
        </div>

        {{-- STEP 6: Manpower --}}
        <div x-show="currentStep === 6"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0">
          @include('transaction.rencanakerjaharian.wizard-steps.step6-manpower')
        </div>

        {{-- STEP 7: Review --}}
        <div x-show="currentStep === 7"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0">
          @include('transaction.rencanakerjaharian.wizard-steps.step7-review')
        </div>

      </div>

      {{-- Navigation Footer --}}
      <div class="bg-gray-50 border-t border-gray-200 px-6 py-4">
        <div class="flex justify-between items-center">
          
          {{-- Back Button --}}
          <button 
            type="button"
            @click="prevStep()" 
            x-show="currentStep > 1"
            class="px-5 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Previous
          </button>

          {{-- Spacer --}}
          <div x-show="currentStep === 1"></div>

          {{-- Next Button --}}
          <button 
            type="button"
            @click="nextStep()" 
            x-show="currentStep < 7"
            :disabled="!canProceed()"
            :class="canProceed() ? 'bg-blue-600 hover:bg-blue-700 shadow-md hover:shadow-lg' : 'bg-gray-300 cursor-not-allowed'"
            class="px-5 py-2 text-white rounded-lg text-sm font-medium transition-all flex items-center gap-2">
            <span x-text="getNextButtonText()"></span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </button>

          {{-- Update Button --}}
          <button 
            type="button"
            id="update-btn"
            @click="submitForm()"
            x-show="currentStep === 7"
            :disabled="isSubmitting"
            :class="isSubmitting ? 'opacity-50 cursor-not-allowed' : 'hover:bg-orange-700 hover:shadow-lg'"
            class="px-8 py-2.5 bg-orange-600 text-white rounded-lg text-sm font-semibold transition-all flex items-center gap-2 shadow-md">
            
            {{-- Normal State Icon --}}
            <svg 
              x-show="!isSubmitting"
              class="w-5 h-5" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            
            {{-- Loading Spinner --}}
            <svg 
              x-show="isSubmitting"
              class="animate-spin h-5 w-5" 
              fill="none" 
              viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            
            {{-- Button Text --}}
            <span x-show="!isSubmitting">Update RKH</span>
            <span x-show="isSubmitting">Updating...</span>
          </button>

        </div>
      </div>

    </div>

    {{-- SUCCESS MODAL --}}
    <div 
      x-data="{
        showModal: false,
        isRedirecting: false,
        rkhNo: @js($rkhHeader->rkhno),
        mandorName: @js($rkhHeader->mandor_nama ?? ''),
        tanggal: @js(\Carbon\Carbon::parse($rkhHeader->rkhdate)->format('d M Y')),
        totalActivities: 0,
        totalPlots: 0,
        totalLuas: 0,
        totalWorkers: 0,
        
        openModal(data) {
          console.log('🎉 Update Success Modal:', data);
          this.totalActivities = data.summary?.activities || 0;
          this.totalPlots = data.summary?.plots || 0;
          this.totalLuas = data.summary?.luas || 0;
          this.totalWorkers = data.summary?.workers || 0;
          this.showModal = true;
        },
        
        redirectToShow() {
          this.isRedirecting = true;
          setTimeout(() => {
            window.location.href = @js(route('transaction.rencanakerjaharian.show', $rkhHeader->rkhno));
          }, 300);
        }
      }"
      @rkh-update-success.window="openModal($event.detail)"
      x-show="showModal"
      x-cloak
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4"
      style="display: none;"
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100"
    >
      <div 
        @click.away="false"
        class="bg-white rounded-lg shadow-2xl w-full max-w-lg border-2 border-gray-800"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
      >
        {{-- Header --}}
        <div class="bg-gray-800 text-white px-6 py-4 border-b-2 border-gray-800">
          <h2 class="text-lg font-bold text-center">RKH BERHASIL DIUPDATE</h2>
          <p class="text-center text-sm text-gray-300 mt-1">Daily Work Plan Updated Successfully</p>
        </div>

        {{-- Content --}}
        <div class="p-6">
          {{-- Success Icon --}}
          <div class="flex justify-center mb-4">
            <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center">
              <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
          </div>

          {{-- RKH Number --}}
          <div class="text-center mb-6">
            <p class="text-sm text-gray-600 mb-2">Nomor RKH:</p>
            <p class="text-2xl font-bold text-gray-800 font-mono tracking-wide" x-text="rkhNo"></p>
          </div>

          {{-- Summary --}}
          <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600">Mandor:</span>
                <span class="font-semibold text-gray-800" x-text="mandorName"></span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Tanggal:</span>
                <span class="font-semibold text-gray-800" x-text="tanggal"></span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Total Aktivitas:</span>
                <span class="font-semibold text-gray-800" x-text="totalActivities"></span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Total Plot:</span>
                <span class="font-semibold text-gray-800" x-text="totalPlots"></span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Total Luas:</span>
                <span class="font-semibold text-gray-800" x-text="totalLuas + ' Ha'"></span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Total Pekerja:</span>
                <span class="font-semibold text-gray-800" x-text="totalWorkers"></span>
              </div>
            </div>
          </div>

          {{-- Success Message --}}
          <div class="bg-orange-50 border-l-4 border-orange-500 p-4 mb-6">
            <div class="flex items-start">
              <svg class="w-5 h-5 text-orange-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <div>
                <p class="text-sm font-medium text-orange-800">
                  Perubahan RKH telah berhasil disimpan ke sistem
                </p>
                <p class="text-xs text-orange-700 mt-1">
                  Approval status akan direset. RKH perlu diapprove ulang.
                </p>
              </div>
            </div>
          </div>
        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 border-t border-gray-300 px-6 py-4">
          <button
            type="button"
            @click="redirectToShow()"
            :disabled="isRedirecting"
            class="w-full bg-gray-800 hover:bg-gray-900 text-white px-6 py-3 rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
            <span x-show="!isRedirecting">OK, Lihat Detail RKH</span>
            <span x-show="isRedirecting" class="flex items-center gap-2">
              <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Redirecting...
            </span>
          </button>
        </div>
      </div>
    </div>

  </div>

  @push('scripts')
<script>
  // ============================================================
  // GLOBAL DATA INITIALIZATION
  // ============================================================
  window.activitiesData = @json($activities ?? []);
  window.bloksData = @json($bloks ?? []);
  window.masterlistData = @json($masterlist ?? []);
  window.herbisidaData = @json($herbisidagroups ?? []);
  window.vehiclesData = @json($vehiclesData ?? []);
  window.helpersData = @json($helpersData ?? []);
  window.absenData = @json($absentenagakerja ?? []);
  window.rkhDate = '{{ $rkhHeader->rkhdate }}';
  window.mandorId = '{{ $rkhHeader->mandorid }}';
  window.PANEN_ACTIVITIES = ['4.3.3', '4.4.3', '4.5.2'];
  window.PIAS_ACTIVITIES = ['5.2.1','5.2.3a'];
  window.PLOT_INFO_BASE_URL = "{{ url('transaction/kerjaharian/rencanakerjaharian/plot-info') }}";
  window.RKH_SUBMISSION_LOCK = false;

  console.log('🔧 Edit Mode - Global Data Loaded:', {
    activities: window.activitiesData?.length,
    masterlist: window.masterlistData?.length,
    vehicles: window.vehiclesData?.length,
    helpers: window.helpersData?.length,
    rkhno: '{{ $rkhHeader->rkhno }}'
  });
</script>

<script>
  // ============================================================
  // EDIT RKH WIZARD APP - COMPLETE
  // ============================================================
  document.addEventListener('alpine:init', () => {
    Alpine.data('rkhEditWizardApp', () => ({
      currentStep: 1,
      isSubmitting: false,

      steps: [
        { id: 1, title: 'Activities' },
        { id: 2, title: 'Plots' },
        { id: 3, title: 'Details' },
        { id: 4, title: 'Materials' },
        { id: 5, title: 'Vehicles' },
        { id: 6, title: 'Manpower' },
        { id: 7, title: 'Review' }
      ],

      // State management
      selectedActivities: {},
      plotAssignments: {},
      blokActivityAssignments: {},
      luasConfirmed: {},
      materials: {},
      vehicles: {},
      workers: {},
      
      // Step 1: Activities
      activitySearch: '',
      
      // Step 2: Plots
      currentActivityForPlots: null,
      selectedBlokForPlots: null,
      blokSearchQuery: '',
      plotSearchQuery: '',

      init() {
        console.log('🚀 RKH Edit Wizard Initialized');
        
        this.$watch('selectedActivities', (activities) => {
          const actCodes = Object.keys(activities);
          if (actCodes.length > 0 && !this.currentActivityForPlots) {
            this.currentActivityForPlots = actCodes[0];
          }
          if (actCodes.length === 0) {
            this.currentActivityForPlots = null;
          }
        });
        
        this.$nextTick(() => {
          const bloks = this.availableBloks();
          if (bloks.length > 0 && !this.selectedBlokForPlots) {
            this.selectedBlokForPlots = bloks[0];
          }
        });
      },

      // ==================== LOAD EXISTING DATA ====================
      loadExistingData() {
        console.log('📦 Loading existing RKH data...');
        
        const details = @json($rkhDetails ?? []);
        const workers = @json($existingWorkers ?? []);
        const vehicles = @json($existingKendaraan ?? []);

        console.log('Raw existing data:', { details, workers, vehicles });

        // Transform activities
        this.selectedActivities = this.transformActivities(details);
        console.log('✅ Transformed activities:', this.selectedActivities);

        // Transform plots & blok activities
        const plotTransform = this.transformPlots(details);
        this.plotAssignments = plotTransform.plots;
        this.blokActivityAssignments = plotTransform.blokActivities;
        console.log('✅ Transformed plots:', this.plotAssignments);
        console.log('✅ Transformed blok activities:', this.blokActivityAssignments);

        // Transform luas
        this.luasConfirmed = this.transformLuas(details);
        console.log('✅ Transformed luas:', this.luasConfirmed);

        // Transform materials
        this.materials = this.transformMaterials(details);
        console.log('✅ Transformed materials:', this.materials);

        // Transform workers
        this.workers = this.transformWorkers(workers);
        console.log('✅ Transformed workers:', this.workers);

        // Transform vehicles
        this.vehicles = this.transformVehicles(vehicles);
        console.log('✅ Transformed vehicles:', this.vehicles);

        // Set initial activity for plots
        const firstActivity = Object.keys(this.selectedActivities)[0];
        if (firstActivity) {
          this.currentActivityForPlots = firstActivity;
        }

        console.log('🎉 All data loaded successfully!');
      },

      transformActivities(details) {
        const activities = {};
        const activityCodes = [...new Set(details.map(d => d.activitycode))];
        
        activityCodes.forEach(actCode => {
          const detail = details.find(d => d.activitycode === actCode);
          if (detail) {
            activities[actCode] = {
              code: actCode,
              name: detail.activityname,
              usingvehicle: detail.activity?.usingvehicle || 0,
              usingmaterial: detail.activity?.usingmaterial || 0,
              isblokactivity: detail.isblokactivity || 0,
              jenistenagakerja: detail.jenistenagakerja
            };
          }
        });
        
        return activities;
      },

      transformPlots(details) {
        const plots = {};
        const blokActivities = {};
        
        details.forEach(detail => {
          const actCode = detail.activitycode;
          
          // Blok activity (plot is null or blok is 'ALL')
          if (detail.isblokactivity == 1 || detail.plot === null || detail.blok === 'ALL') {
            blokActivities[actCode] = detail.blok === 'ALL' ? 'ALL' : detail.blok;
          } else {
            // Normal activity with plots
            if (!plots[actCode]) {
              plots[actCode] = [];
            }
            
            plots[actCode].push({
              blok: detail.blok,
              plot: detail.plot,
              batchno: detail.batch_number || null,
              batcharea: parseFloat(detail.batcharea) || 0,
              lifecyclestatus: detail.batch_lifecycle || null,
              last_activitycode: detail.last_activitycode || null,
              last_activity_date: detail.last_activity_date || null
            });
          }
        });
        
        return { plots, blokActivities };
      },

      transformLuas(details) {
        const luas = {};
        
        details.forEach(detail => {
          if (detail.plot) {
            const key = `${detail.activitycode}_${detail.blok}_${detail.plot}`;
            luas[key] = parseFloat(detail.luasarea || 0).toFixed(2);
          }
        });
        
        return luas;
      },

      transformMaterials(details) {
        const materials = {};
        
        details.forEach(detail => {
          if (detail.herbisidagroupid && detail.plot) {
            const key = `${detail.activitycode}_${detail.blok}_${detail.plot}`;
            materials[key] = {
              groupid: detail.herbisidagroupid,
              groupname: detail.herbisidagroupname || 'Unknown'
            };
          }
        });
        
        return materials;
      },

      transformWorkers(existingWorkers) {
        const workers = {};
        
        existingWorkers.forEach(worker => {
          workers[worker.activitycode] = {
            laki: worker.jumlahlaki?.toString() || '0',
            perempuan: worker.jumlahperempuan?.toString() || '0',
            total: parseInt(worker.jumlahtenagakerja) || 0
          };
        });
        
        return workers;
      },

      transformVehicles(existingKendaraan) {
        const vehicles = {};
        
        existingKendaraan.forEach(veh => {
          if (!vehicles[veh.activitycode]) {
            vehicles[veh.activitycode] = [];
          }
          
          vehicles[veh.activitycode].push({
            nokendaraan: veh.nokendaraan,
            vehicle_type: veh.vehicle_type || 'Vehicle',
            operatorid: veh.operatorid,
            operator_name: veh.operator_name,
            helperid: veh.helperid || null,
            helper_name: veh.helper_name || null
          });
        });
        
        return vehicles;
      },

      // ==================== STEP 1: ACTIVITIES ====================
      get groupedActivities() {
        const activities = window.activitiesData || [];
        const filtered = !this.activitySearch ? activities : activities.filter(act => 
          act.activitycode.toLowerCase().includes(this.activitySearch.toLowerCase()) ||
          act.activityname.toLowerCase().includes(this.activitySearch.toLowerCase())
        );

        const groups = {};
        filtered.forEach(act => {
          const groupCode = act.activitygroup || 'Uncategorized';
          const groupName = act.groupname || groupCode;
          
          if (!groups[groupCode]) {
            groups[groupCode] = {
              code: groupCode,
              name: groupName,
              activities: []
            };
          }
          groups[groupCode].activities.push(act);
        });

        return Object.values(groups).sort((a, b) => {
          const romanValues = { 'I': 1, 'II': 2, 'III': 3, 'IV': 4, 'V': 5, 'VI': 6, 'VII': 7, 'VIII': 8, 'IX': 9, 'X': 10 };
          return (romanValues[a.code] || 999) - (romanValues[b.code] || 999);
        });
      },
      
      toggleActivity(activity) {
        if (this.selectedActivities[activity.activitycode]) {
          delete this.selectedActivities[activity.activitycode];
          delete this.plotAssignments[activity.activitycode];
          delete this.blokActivityAssignments[activity.activitycode];
          
          Object.keys(this.luasConfirmed).forEach(key => {
            if (key.startsWith(activity.activitycode + '_')) {
              delete this.luasConfirmed[key];
            }
          });
          Object.keys(this.materials).forEach(key => {
            if (key.startsWith(activity.activitycode + '_')) {
              delete this.materials[key];
            }
          });
          
          delete this.vehicles[activity.activitycode];
          delete this.workers[activity.activitycode];
          
        } else {
          this.selectedActivities[activity.activitycode] = {
            code: activity.activitycode,
            name: activity.activityname,
            usingvehicle: activity.usingvehicle || 0,
            usingmaterial: activity.usingmaterial || 0,
            isblokactivity: activity.isblokactivity || 0,
            jenistenagakerja: activity.jenistenagakerja
          };
          
          this.workers[activity.activitycode] = {
            laki: '',
            perempuan: '',
            total: 0
          };
        }
      },

      // ==================== STEP 2: PLOTS ====================
      availableBloks() {
        const bloksSet = new Set();
        (window.masterlistData || []).forEach(plot => {
          if (plot.blok) bloksSet.add(plot.blok);
        });
        return Array.from(bloksSet).sort();
      },

      filteredBloksForActivity() {
        const bloks = this.availableBloks();
        if (!this.blokSearchQuery) return bloks;
        
        const q = this.blokSearchQuery.toUpperCase();
        return bloks.filter(blok => blok.toUpperCase().includes(q));
      },

      filteredPlotsForBlok(blok) {
        const plots = this.getPlotsForBlok(blok);
        if (!this.plotSearchQuery) return plots;
        
        const q = this.plotSearchQuery.toUpperCase();
        return plots.filter(plot => plot.plot.toUpperCase().includes(q));
      },

      getPlotsForBlok(blok) {
        if (!blok) return [];
        return (window.masterlistData || []).filter(plot => plot.blok === blok);
      },

      isPlotSelectedForActivity(actCode, plot) {
        const assignments = this.plotAssignments[actCode] || [];
        return assignments.some(p => p.blok === plot.blok && p.plot === plot.plot);
      },

      togglePlotForActivity(actCode, plot) {
        if (!this.plotAssignments[actCode]) {
          this.plotAssignments[actCode] = [];
        }
        
        const index = this.plotAssignments[actCode].findIndex(
          p => p.blok === plot.blok && p.plot === plot.plot
        );
        
        if (index > -1) {
          this.plotAssignments[actCode].splice(index, 1);
          
          const key = `${actCode}_${plot.blok}_${plot.plot}`;
          delete this.luasConfirmed[key];
          delete this.materials[key];
        } else {
          this.plotAssignments[actCode].push({
            blok: plot.blok,
            plot: plot.plot,
            batchno: plot.batchno || null,
            batcharea: parseFloat(plot.batcharea) || 0,
            lifecyclestatus: plot.lifecyclestatus || null,
            last_activitycode: plot.last_activitycode || null,
            last_activity_date: plot.last_activity_date || null
          });
          
          const key = `${actCode}_${plot.blok}_${plot.plot}`;
          this.luasConfirmed[key] = parseFloat(plot.batcharea) || 0;
        }
      },

      selectBlokForBlokActivity(blok) {
        if (!this.currentActivityForPlots) return;
        
        const activity = this.selectedActivities[this.currentActivityForPlots];
        if (!activity || activity.isblokactivity != 1) return;
        
        this.blokActivityAssignments[this.currentActivityForPlots] = blok;
        this.plotAssignments[this.currentActivityForPlots] = [];
        
        showToast(`Blok "${blok}" selected for ${this.currentActivityForPlots}`, 'success', 2000);
      },

      getSelectedBlokForActivity(actCode) {
        return this.blokActivityAssignments[actCode] || '';
      },

      hasAnyBlokActivitySelected() {
        return Object.keys(this.blokActivityAssignments).length > 0;
      },

      getSelectedPlotsInBlok(blok, actCode) {
        const assignments = this.plotAssignments[actCode] || [];
        return assignments.filter(p => p.blok === blok);
      },

      clearPlotsForActivity(actCode) {
        (this.plotAssignments[actCode] || []).forEach(plot => {
          const key = `${actCode}_${plot.blok}_${plot.plot}`;
          delete this.luasConfirmed[key];
          delete this.materials[key];
        });
        
        this.plotAssignments[actCode] = [];
        
        if (this.blokActivityAssignments[actCode]) {
          delete this.blokActivityAssignments[actCode];
        }
      },

      removePlotFromActivity(actCode, plot) {
        const index = this.plotAssignments[actCode].findIndex(
          p => p.blok === plot.blok && p.plot === plot.plot
        );
        if (index > -1) {
          this.plotAssignments[actCode].splice(index, 1);
          
          const key = `${actCode}_${plot.blok}_${plot.plot}`;
          delete this.luasConfirmed[key];
          delete this.materials[key];
        }
      },

      getTotalLuasForActivity(actCode) {
        const plots = this.plotAssignments[actCode] || [];
        const total = plots.reduce((sum, plot) => sum + parseFloat(plot.batcharea || 0), 0);
        return total.toFixed(2);
      },

      formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = String(date.getFullYear()).slice(-2);
        return `${day}-${month}-${year}`;
      },

      getDaysGap(lastActivityDate) {
        if (!lastActivityDate || !window.rkhDate) return 0;
        
        const lastDate = new Date(lastActivityDate);
        const rkhDate = new Date(window.rkhDate);
        const diffTime = rkhDate - lastDate;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        return diffDays;
      },

      canProceedStep2() {
        return Object.entries(this.selectedActivities).every(([actCode, activity]) => {
          if (activity.isblokactivity == 1) {
            return this.blokActivityAssignments[actCode] !== undefined;
          }
          return this.plotAssignments[actCode] && this.plotAssignments[actCode].length > 0;
        });
      },

      // ==================== STEP 3: DETAILS ====================
      getTotalPlotsCount() {
        let total = 0;
        Object.values(this.plotAssignments).forEach(plots => {
          total += plots?.length || 0;
        });
        return total;
      },
      
      getTotalLuasAll() {
        let total = 0;
        Object.keys(this.luasConfirmed).forEach(key => {
          total += parseFloat(this.luasConfirmed[key]) || 0;
        });
        return total.toFixed(2);
      },
      
      getTotalLuasForActivityConfirmed(actCode) {
        let total = 0;
        const plots = this.plotAssignments[actCode] || [];
        plots.forEach(plot => {
          const key = `${actCode}_${plot.blok}_${plot.plot}`;
          total += parseFloat(this.luasConfirmed[key]) || 0;
        });
        return total.toFixed(2);
      },

      validateLuasInput(event, actCode, plot) {
        const input = event.target;
        const value = parseFloat(input.value) || 0;
        const maxLuas = parseFloat(plot.batcharea);
        const key = `${actCode}_${plot.blok}_${plot.plot}`;
        
        if (value > maxLuas) {
          this.luasConfirmed[key] = maxLuas.toFixed(2);
          input.value = maxLuas.toFixed(2);
          showToast(`Luas tidak boleh melebihi ${maxLuas.toFixed(2)} Ha`, 'warning', 3000);
          
          input.classList.add('border-red-500', 'bg-red-50');
          setTimeout(() => {
            input.classList.remove('border-red-500', 'bg-red-50');
          }, 2000);
        } else {
          this.luasConfirmed[key] = value.toFixed(2);
        }
      },

      // ==================== STEP 4: MATERIALS ====================
      getAvailableMaterialGroups(actCode) {
        const herbisidaData = window.herbisidaData || [];
        const groups = {};
        
        herbisidaData.forEach(item => {
          if (item.activitycode === actCode) {
            if (!groups[item.herbisidagroupid]) {
              groups[item.herbisidagroupid] = {
                herbisidagroupid: item.herbisidagroupid,
                herbisidagroupname: item.herbisidagroupname,
                description: item.description || 'No description',
                items: []
              };
            }
            groups[item.herbisidagroupid].items.push(item);
          }
        });
        
        return Object.values(groups);
      },
      
      selectMaterial(actCode, plot, group) {
        const key = `${actCode}_${plot.blok}_${plot.plot}`;
        this.materials[key] = {
          groupid: group.herbisidagroupid,
          groupname: group.herbisidagroupname
        };
      },

      getRequiredMaterialCount() {
        let count = 0;
        Object.entries(this.selectedActivities).forEach(([actCode, activity]) => {
          if (activity.usingmaterial == 1) {
            count += this.plotAssignments[actCode]?.length || 0;
          }
        });
        return count;
      },
      
      getCompletedMaterialCount() {
        let count = 0;
        Object.entries(this.selectedActivities).forEach(([actCode, activity]) => {
          if (activity.usingmaterial == 1) {
            const plots = this.plotAssignments[actCode] || [];
            plots.forEach(plot => {
              const key = `${actCode}_${plot.blok}_${plot.plot}`;
              if (this.materials[key]) count++;
            });
          }
        });
        return count;
      },
      
      getMaterialProgress() {
        const required = this.getRequiredMaterialCount();
        if (required === 0) return 100;
        return Math.round((this.getCompletedMaterialCount() / required) * 100);
      },

      getMaterialCountForActivity(actCode) {
        const plots = this.plotAssignments[actCode] || [];
        let completed = 0;
        plots.forEach(plot => {
          const key = `${actCode}_${plot.blok}_${plot.plot}`;
          if (this.materials[key]) completed++;
        });
        return `${completed}/${plots.length}`;
      },

      allMaterialsSelected() {
        return this.getCompletedMaterialCount() === this.getRequiredMaterialCount();
      },

      // ==================== STEP 5: VEHICLES ====================
      getActivitiesRequiringVehicles() {
        return Object.values(this.selectedActivities).filter(act => act.usingvehicle == 1).length;
      },
      
      getActivitiesWithVehicles() {
        let count = 0;
        Object.entries(this.selectedActivities).forEach(([actCode, activity]) => {
          if (activity.usingvehicle == 1 && this.vehicles[actCode]?.length > 0) {
            count++;
          }
        });
        return count;
      },
      
      getTotalVehiclesAssigned() {
        let total = 0;
        Object.values(this.vehicles).forEach(actVehicles => {
          total += actVehicles?.length || 0;
        });
        return total;
      },

      openVehicleSelector(actCode) {
        window.dispatchEvent(new CustomEvent('open-vehicle-modal', { 
          detail: { activityCode: actCode } 
        }));
      },
      
      removeVehicle(actCode, index) {
        if (this.vehicles[actCode]) {
          this.vehicles[actCode].splice(index, 1);
          if (this.vehicles[actCode].length === 0) {
            delete this.vehicles[actCode];
          }
        }
      },

      allVehiclesAssigned() {
        return Object.entries(this.selectedActivities).every(([actCode, activity]) => {
          if (activity.usingvehicle == 1) {
            return this.vehicles[actCode] && this.vehicles[actCode].length > 0;
          }
          return true;
        });
      },

      // ==================== STEP 6: MANPOWER ====================
      updateWorkerTotal(actCode) {
        if (!this.workers[actCode]) return;
        
        const laki = parseInt(this.workers[actCode].laki) || 0;
        const perempuan = parseInt(this.workers[actCode].perempuan) || 0;
        
        this.workers[actCode].total = laki + perempuan;
      },
      
      getTotalWorkers(type) {
        let total = 0;
        Object.values(this.workers).forEach(worker => {
          if (type === 'laki') {
            total += parseInt(worker.laki) || 0;
          } else if (type === 'perempuan') {
            total += parseInt(worker.perempuan) || 0;
          } else if (type === 'total') {
            total += parseInt(worker.total) || 0;
          }
        });
        return total;
      },

      getJenisLabel(jenisId) {
        const labels = {
          1: 'Harian',
          2: 'Borongan',
          3: 'Operator',
          4: 'Helper'
        };
        return labels[jenisId] || '-';
      },

      getAbsenSuggestion(actCode) {
        const absenData = window.absenData || [];
        const mandorId = window.mandorId;
        
        const filtered = absenData.filter(a => a.mandorid === mandorId);
        
        let laki = 0, perempuan = 0;
        filtered.forEach(absen => {
          if (absen.gender === 'L') laki++;
          else if (absen.gender === 'P') perempuan++;
        });
        
        return { laki, perempuan, total: laki + perempuan };
      },
      
      applyAbsenSuggestion(actCode) {
        const suggestion = this.getAbsenSuggestion(actCode);
        if (!this.workers[actCode]) {
          this.workers[actCode] = { laki: '', perempuan: '', total: 0 };
        }
        
        this.workers[actCode].laki = suggestion.laki.toString();
        this.workers[actCode].perempuan = suggestion.perempuan.toString();
        this.updateWorkerTotal(actCode);
        
        showToast('Attendance data applied', 'success', 2000);
      },

      allWorkersCompleted() {
        return Object.keys(this.selectedActivities).every(actCode => {
          const worker = this.workers[actCode];
          return worker && (worker.laki !== '' || worker.perempuan !== '');
        });
      },

      // ==================== NAVIGATION ====================
      nextStep() {
        if (this.canProceed()) {
          let nextStep = this.currentStep + 1;
          
          if (nextStep === 4 && !this.hasActivityWithMaterial()) {
            nextStep = 5;
          }
          
          if (nextStep === 5 && !this.hasActivityWithVehicle()) {
            nextStep = 6;
          }
          
          this.currentStep = nextStep;
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      },

      prevStep() {
        let prevStep = this.currentStep - 1;
        
        if (prevStep === 5 && !this.hasActivityWithVehicle()) {
          prevStep = 4;
        }
        if (prevStep === 4 && !this.hasActivityWithMaterial()) {
          prevStep = 3;
        }
        
        this.currentStep = Math.max(1, prevStep);
        window.scrollTo({ top: 0, behavior: 'smooth' });
      },

      goToStep(stepId) {
        if (stepId < this.currentStep) {
          this.currentStep = stepId;
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      },

      // ==================== VALIDATION ====================
      canProceed() {
        switch(this.currentStep) {
          case 1: 
            return Object.keys(this.selectedActivities).length > 0;
          case 2:
            return this.canProceedStep2();
          case 3:
            return this.allLuasConfirmed();
          case 4:
            return this.validateMaterials();
          case 5:
            return this.validateVehicles();
          case 6:
            return this.validateWorkers();
          default:
            return true;
        }
      },

      getNextButtonText() {
        if (this.currentStep === 6) return 'Review';
        
        if (this.currentStep === 3 && !this.hasActivityWithMaterial()) {
          return this.hasActivityWithVehicle() ? 'Next: Vehicles' : 'Next: Manpower';
        }
        if (this.currentStep === 4 && !this.hasActivityWithVehicle()) {
          return 'Next: Manpower';
        }
        
        return 'Next';
      },

      hasActivityWithMaterial() {
        return Object.values(this.selectedActivities).some(act => act.usingmaterial == 1);
      },

      hasActivityWithVehicle() {
        return Object.values(this.selectedActivities).some(act => act.usingvehicle == 1);
      },

      allLuasConfirmed() {
        const allPlots = [];
        Object.entries(this.plotAssignments).forEach(([actCode, plots]) => {
          plots.forEach(plot => {
            allPlots.push(`${actCode}_${plot.blok}_${plot.plot}`);
          });
        });
        
        return allPlots.every(key => {
          const luas = this.luasConfirmed[key];
          return luas !== undefined && luas !== '' && parseFloat(luas) > 0;
        });
      },

      validateMaterials() {
        if (!this.hasActivityWithMaterial()) return true;
        
        const plotsNeedingMaterial = [];
        Object.entries(this.plotAssignments).forEach(([actCode, plots]) => {
          const activity = this.selectedActivities[actCode];
          if (activity?.usingmaterial == 1) {
            plots.forEach(plot => {
              plotsNeedingMaterial.push(`${actCode}_${plot.blok}_${plot.plot}`);
            });
          }
        });
        
        return plotsNeedingMaterial.every(key => this.materials[key]);
      },

      validateVehicles() {
        if (!this.hasActivityWithVehicle()) return true;
        
        return Object.entries(this.selectedActivities).every(([actCode, activity]) => {
          if (activity.usingvehicle == 1) {
            return this.vehicles[actCode] && this.vehicles[actCode].length > 0;
          }
          return true;
        });
      },

      validateWorkers() {
        return Object.keys(this.selectedActivities).every(actCode => {
          const worker = this.workers[actCode];
          return worker && (worker.laki !== '' || worker.perempuan !== '');
        });
      },

      // ==================== SUBMIT UPDATE ====================
      async submitForm() {
        if (this.isSubmitting) {
          console.warn('Update blocked: Already submitting');
          showToast('Sedang memproses, mohon tunggu...', 'warning', 2000);
          return false;
        }

        if (window.RKH_SUBMISSION_LOCK) {
          console.warn('Update blocked: Global lock active');
          showToast('Sedang memproses, mohon tunggu...', 'warning', 2000);
          return false;
        }

        const updateBtn = document.getElementById('update-btn');
        if (updateBtn) {
          if (updateBtn.disabled) {
            console.warn('Update blocked: Button already disabled');
            showToast('Mohon tunggu...', 'warning', 2000);
            return false;
          }
          updateBtn.disabled = true;
        }

        this.isSubmitting = true;
        window.RKH_SUBMISSION_LOCK = true;

        const payload = this.transformToBackendFormat();
        
        try {
          const response = await fetch('{{ route("transaction.rencanakerjaharian.update", $rkhHeader->rkhno) }}', {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
          });
          
          const result = await response.json();
          
          if (result.success) {
            console.log('✅ Update successful:', result);
            
            window.dispatchEvent(new CustomEvent('rkh-update-success', {
              detail: {
                rkhno: '{{ $rkhHeader->rkhno }}',
                summary: {
                  activities: Object.keys(this.selectedActivities).length,
                  plots: this.getTotalPlotsCount(),
                  luas: parseFloat(this.getTotalLuasAll()),
                  workers: this.getTotalWorkers('total')
                }
              }
            }));

          } else {
            console.error('❌ Update failed:', result.message);
            showToast(result.message || 'Gagal update RKH', 'error', 4000);
            
            setTimeout(() => {
              this.unlockSubmission();
            }, 3000);
          }
        } catch (error) {
          console.error('❌ Update error:', error);
          showToast('Terjadi kesalahan koneksi', 'error', 4000);
          
          setTimeout(() => {
            this.unlockSubmission();
          }, 3000);
        }
      },

      unlockSubmission() {
        console.log('🔓 Unlocking submission...');
        
        this.isSubmitting = false;
        window.RKH_SUBMISSION_LOCK = false;
        
        const updateBtn = document.getElementById('update-btn');
        if (updateBtn) {
          updateBtn.disabled = false;
          updateBtn.classList.add('animate-pulse');
          setTimeout(() => {
            updateBtn.classList.remove('animate-pulse');
          }, 1000);
        }
        
        showToast('Silakan coba update lagi', 'info', 2000);
      },

      transformToBackendFormat() {
        const rows = [];
        
        // Handle normal activities with plots
        Object.entries(this.plotAssignments).forEach(([activityCode, plots]) => {
          plots.forEach((plot) => {
            const key = `${activityCode}_${plot.blok}_${plot.plot}`;
            const material = this.materials[key];
            
            rows.push({
              nama: activityCode,
              blok: plot.blok,
              plot: plot.plot,
              luas: this.luasConfirmed[key] || plot.batcharea,
              material_group_id: material?.groupid || '',
              usingmaterial: material ? '1' : '0',
              batchno: plot.batchno || null
            });
          });
        });
        
        // Handle blok activities
        Object.entries(this.blokActivityAssignments).forEach(([activityCode, blok]) => {
          rows.push({
            nama: activityCode,
            blok: blok,
            plot: null,
            luas: null,
            material_group_id: '',
            usingmaterial: '0',
            batchno: null
          });
        });
        
        return {
          tanggal: window.rkhDate,
          mandor_id: window.mandorId,
          keterangan: '',
          rows: rows,
          workers: this.workers,
          kendaraan: this.vehicles
        };
      }
    }));

    // ============================================================
    // VEHICLE SELECTION MODAL COMPONENT (Reuse from Create)
    // ============================================================
    Alpine.data('vehicleSelectionModal', () => ({
      showModal: false,
      currentActivityCode: '',
      searchQuery: '',
      useHelper: false,
      selectedVehicle: null,
      selectedHelper: null,
      
      init() {
        window.addEventListener('open-vehicle-modal', (e) => {
          this.currentActivityCode = e.detail.activityCode;
          this.showModal = true;
        });
      },
      
      filteredVehicles() {
        const vehicles = window.vehiclesData || [];
        if (!this.searchQuery) return vehicles;
        
        const q = this.searchQuery.toLowerCase();
        return vehicles.filter(v => 
          v.nokendaraan?.toLowerCase().includes(q) ||
          v.operator_name?.toLowerCase().includes(q) ||
          v.vehicle_type?.toLowerCase().includes(q)
        );
      },
      
      availableHelpers() {
        return window.helpersData || [];
      },
      
      selectVehicle(vehicle) {
        this.selectedVehicle = vehicle;
      },
      
      selectHelper(helper) {
        this.selectedHelper = helper;
      },
      
      confirmSelection() {
        if (!this.selectedVehicle) return;
        
        const wizardApp = Alpine.$data(document.querySelector('[x-data*="rkhEditWizardApp"]'));
        
        if (!wizardApp.vehicles[this.currentActivityCode]) {
          wizardApp.vehicles[this.currentActivityCode] = [];
        }
        
        const isDuplicate = wizardApp.vehicles[this.currentActivityCode].some(
          v => v.nokendaraan === this.selectedVehicle.nokendaraan
        );
        
        if (isDuplicate) {
          showToast('Vehicle already assigned to this activity', 'warning', 3000);
          return;
        }
        
        wizardApp.vehicles[this.currentActivityCode].push({
          nokendaraan: this.selectedVehicle.nokendaraan,
          vehicle_type: this.selectedVehicle.vehicle_type,
          operatorid: this.selectedVehicle.operator_id,
          operator_name: this.selectedVehicle.operator_name,
          helperid: this.useHelper && this.selectedHelper ? this.selectedHelper.tenagakerjaid : null,
          helper_name: this.useHelper && this.selectedHelper ? this.selectedHelper.nama : null
        });
        
        showToast('Vehicle added successfully', 'success', 2000);
        this.closeModal();
      },
      
      closeModal() {
        this.showModal = false;
        this.searchQuery = '';
        this.useHelper = false;
        this.selectedVehicle = null;
        this.selectedHelper = null;
      }
    }));
  });

  // ============================================================
  // TOAST UTILITY
  // ============================================================
  function showToast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white transform transition-all duration-300 ${
      type === 'success' ? 'bg-green-500' : 
      type === 'error' ? 'bg-red-500' : 
      type === 'warning' ? 'bg-yellow-500' :
      'bg-blue-500'
    }`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => toast.remove(), duration);
  }
</script>
@endpush
</x-layout>