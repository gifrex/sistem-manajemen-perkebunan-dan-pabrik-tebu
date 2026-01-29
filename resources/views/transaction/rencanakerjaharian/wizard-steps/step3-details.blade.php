{{-- resources/views/transaction/rencanakerjaharian/wizard-steps/step3-details.blade.php --}}

<div class="space-y-4">
  
  {{-- Header --}}
  <div class="flex items-center justify-between mb-4">
    <div>
      <h3 class="text-xl font-bold text-gray-800">Confirm Plot Details</h3>
      <p class="text-sm text-gray-600">Review and adjust work area for each plot</p>
    </div>
  </div>

  {{-- Plot Details by Activity --}}
  <div class="space-y-4">
    
    <template x-for="(activity, actCode) in selectedActivities" :key="actCode">
      <div x-show="plotAssignments[actCode]?.length > 0" class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        
        {{-- Activity Header with Stats --}}
        <div class="bg-gradient-to-r from-blue-50 to-sky-50 border-b border-gray-200 px-4 py-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="text-sm font-bold text-blue-600" x-text="actCode"></span>
              <span class="text-gray-400">•</span>
              <span class="text-sm font-semibold text-gray-800" x-text="activity.name"></span>
            </div>
            <div class="flex items-center gap-4">
              {{-- Total Plots --}}
              <div class="text-right">
                <p class="text-[10px] text-gray-500">Total Plots</p>
                <p class="text-sm font-bold text-blue-600" x-text="plotAssignments[actCode]?.length || 0"></p>
              </div>
              {{-- Total Area --}}
              <div class="text-right">
                <p class="text-[10px] text-gray-500">Total Area</p>
                <p class="text-sm font-bold text-green-600" x-text="getTotalLuasForActivityConfirmed(actCode) + ' Ha'"></p>
              </div>
            </div>
          </div>
        </div>

        {{-- ✅ Plot List - Super Compact (1 row per plot) --}}
        <div class="p-4">
          <div class="space-y-2">
            
            <template x-for="(plot, plotIndex) in (plotAssignments[actCode] || [])" :key="`${actCode}-${plot.blok}-${plot.plot}`">
              <div 
                x-data="plotDetailCard(actCode, plot)"
                x-init="init()"
                class="bg-gray-50 rounded-lg border border-gray-200 hover:border-blue-300 transition-colors">
                
                {{-- Loading State --}}
                <div x-show="isLoading" class="px-4 py-3 text-center">
                  <svg class="animate-spin h-4 w-4 mx-auto text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>

                {{-- ✅ Single Row Layout --}}
                <div x-show="!isLoading" class="flex items-center divide-x divide-gray-200">
                  
                  {{-- Column 1: Blok-Plot (Fixed Width) --}}
                  <div class="px-4 py-3 w-32 min-w-[8rem] flex-shrink-0">
                    <div class="min-w-0">
                      <p class="text-sm font-bold text-gray-800 truncate" x-text="`${plot.blok}-${plot.plot}`"></p>
                      <span 
                        x-show="plotInfo.lifecyclestatus"
                        class="inline-block px-1.5 py-0.5 text-[9px] font-bold rounded mt-0.5"
                        :class="{
                          'bg-yellow-100 text-yellow-700': plotInfo.lifecyclestatus === 'PC',
                          'bg-green-100 text-green-700': plotInfo.lifecyclestatus === 'RC1',
                          'bg-blue-100 text-blue-700': plotInfo.lifecyclestatus === 'RC2',
                          'bg-purple-100 text-purple-700': plotInfo.lifecyclestatus === 'RC3'
                        }"
                        x-text="plotInfo.lifecyclestatus">
                      </span>
                    </div>
                  </div>

                  {{-- Column 2: Luas Plot (Fixed Width) --}}
                  <div class="px-4 py-3 w-24 min-w-[6rem] flex-shrink-0 text-center">
                    <p class="text-[10px] text-gray-500 mb-0.5">Luas Plot</p>
                    <p class="text-sm font-bold text-gray-800" 
                      x-text="plotInfo.luasplot === '-' ? '-' : parseFloat(plotInfo.luasplot || 0).toFixed(2) + ' Ha'">
                    </p>
                  </div>

                  {{-- Column 3: Luas Sisa (Fixed Width) --}}
                  <div class="px-4 py-3 w-24 min-w-[6rem] flex-shrink-0 text-center">
                    <p class="text-[10px] text-gray-500 mb-0.5">Luas Sisa</p>
                    <p class="text-sm font-bold text-green-600" 
                      x-text="plotInfo.luassisa === '-' ? '-' : parseFloat(plotInfo.luassisa || 0).toFixed(2) + ' Ha'">
                    </p>
                  </div>

                  {{-- Column 4: Luas Rencana Kerja Input (Fixed Width) --}}
                  <div class="px-4 py-3 w-36 min-w-[9rem] flex-shrink-0">
                    <label class="block text-[10px] text-gray-500 mb-1">
                      Luas Rencana Kerja <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                      <input
                        type="text"
                        x-model="luasKerja"
                        @input="handleLuasInput($event)"
                        @blur="formatLuasOnBlur($event)"
                        class="w-full px-2 py-1.5 border-2 border-gray-300 rounded text-sm font-bold text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="0.00"
                      >
                      <div class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] font-medium pointer-events-none">
                        Ha
                      </div>
                    </div>
                    <p class="text-[9px] text-gray-500 mt-0.5">
                      Max: <span class="font-semibold" 
                          x-text="plotInfo.luassisa === '-' ? parseFloat(plotInfo.luasplot || 0).toFixed(2) : parseFloat(plotInfo.luassisa || 0).toFixed(2)">
                      </span> Ha
                    </p>
                  </div>

                  {{-- Column 5: Additional Info (43%) --}}
                  <div class="px-4 py-3 flex-1 min-w-0">
                    <div class="space-y-1.5">
                      
                      {{-- ✅ Batch Info (Always show if exists) --}}
                      <div x-show="plotInfo.batchno" class="bg-gray-50 border border-gray-200 rounded px-2 py-1.5">
                        <div class="flex items-center justify-between">
                          <div class="flex items-center gap-1.5">
                            <svg class="w-3 h-3 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <span class="text-[10px] font-semibold text-gray-700">Batch:</span>
                            <span class="text-[10px] font-mono font-bold text-gray-800" x-text="plotInfo.batchno"></span>
                          </div>
                          <span 
                            x-show="plotInfo.lifecyclestatus"
                            class="inline-block px-1.5 py-0.5 text-[9px] font-bold rounded"
                            :class="{
                              'bg-yellow-100 text-yellow-700': plotInfo.lifecyclestatus === 'PC',
                              'bg-green-100 text-green-700': plotInfo.lifecyclestatus === 'RC1',
                              'bg-blue-100 text-blue-700': plotInfo.lifecyclestatus === 'RC2',
                              'bg-purple-100 text-purple-700': plotInfo.lifecyclestatus === 'RC3'
                            }"
                            x-text="plotInfo.lifecyclestatus">
                          </span>
                        </div>
                      </div>

                      {{-- ✅ Info Panen (if PANEN activity) --}}
                      <div x-show="isPanenActivity && plotInfo.tanggal" class="bg-yellow-50 border border-yellow-200 rounded px-2 py-1.5">
                        <div class="flex items-center justify-between">
                          <span class="text-[10px] font-semibold text-yellow-800">Info Panen</span>
                          <span class="text-[10px] text-yellow-700" x-text="'Tgl: ' + (plotInfo.tanggal || 'Belum Panen')"></span>
                        </div>
                      </div>

                      {{-- ✅ Last Activity Info (if NOT panen) --}}
                      <div x-show="!isPanenActivity" class="bg-blue-50 border border-blue-200 rounded px-2 py-1.5">
                        <div class="flex items-center justify-between text-[10px]">
                          <div class="flex items-center gap-1.5 min-w-0 flex-1">
                            <span class="text-blue-600 font-semibold flex-shrink-0">Last Activity:</span>
                            <span class="font-bold text-blue-800 flex-shrink-0" x-text="plotInfo.last_activitycode || '-'"></span>
                            <template x-if="plotInfo.last_activitycode && plotInfo.last_activityname">
                              <span class="text-blue-400 flex-shrink-0">•</span>
                            </template>
                            <span class="text-blue-700 truncate" x-text="plotInfo.last_activityname || '-'"></span>
                          </div>
                          <div class="flex items-center gap-1.5 flex-shrink-0 text-blue-600">
                            <span class="font-semibold">Tgl:</span>
                            <span x-text="plotInfo.last_activity_date ? formatDateDMY(plotInfo.last_activity_date) : (plotInfo.tanggal || '-')"></span>
                            <template x-if="plotInfo.last_activity_date">
                              <span class="text-blue-400">•</span>
                            </template>
                            <template x-if="plotInfo.last_activity_date">
                              <span x-text="getDaysGap(plotInfo.last_activity_date) + 'd ago'"></span>
                            </template>
                          </div>
                        </div>
                      </div>

                      {{-- ✅ ZPK Info (if exists) --}}
                      <div x-show="plotInfo.zpk_date" class="bg-purple-50 border border-purple-200 rounded px-2 py-1">
                        <div class="flex items-center justify-between text-[10px]">
                          <span class="font-semibold text-purple-700">ZPK Date: <span x-text="plotInfo.zpk_date"></span></span>
                          <span 
                            class="font-bold"
                            :class="{
                              'text-green-600': plotInfo.zpk_status === 'ideal',
                              'text-red-600': plotInfo.zpk_status === 'too_early' || plotInfo.zpk_status === 'too_late'
                            }"
                            x-text="plotInfo.zpk_days_gap + ' days gap'">
                          </span>
                        </div>
                      </div>

                    </div>
                  </div>

                </div>

              </div>
            </template>

          </div>
        </div>

      </div>
    </template>

  </div>

  {{-- Validation Warning --}}
  <div x-show="!allLuasConfirmed()">
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded-lg">
      <div class="flex items-start">
        <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <div>
          <h3 class="text-sm font-semibold text-yellow-800 mb-1">Incomplete Information</h3>
          <p class="text-xs text-yellow-700">Please confirm work area (luas) for all plots before proceeding.</p>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
function plotDetailCard(actCode, plot) {
  return {
    actCode: actCode,
    plot: plot,
    isLoading: false,
    isPanenActivity: false,
    isPiasActivity: false,
    plotInfo: {
      luasplot: plot.luasplot || 0,
      luassisa: plot.luassisa || plot.luasplot || 0,
      batchno: '',
      lifecyclestatus: plot.lifecyclestatus || '',
      tanggal: '',
      last_activitycode: '',
      last_activityname: '',
      last_activity_date: '',
      zpk_date: '',
      zpk_days_gap: 0,
      zpk_status: ''
    },
    luasKerja: '',

    init() {
      this.isPanenActivity = window.PANEN_ACTIVITIES.includes(this.actCode);
      this.isPiasActivity = window.PIAS_ACTIVITIES.includes(this.actCode);
      this.fetchPlotInfo();
    },

    async fetchPlotInfo() {
      this.isLoading = true;
      
      try {
        const url = `${window.PLOT_INFO_BASE_URL}/${this.plot.plot}/${this.actCode}`;
        const response = await fetch(url);
        const data = await response.json();

        if (data.success) {
          this.plotInfo = {
            luasplot: parseFloat(data.luasplot || 0).toFixed(2),
            luassisa: this.isPiasActivity ? '-' : parseFloat(data.luassisa || 0).toFixed(2),
            batchno: data.batchinfo?.batchno || data.activebatchno || '',
            lifecyclestatus: data.batchinfo?.lifecyclestatus || this.plot.lifecyclestatus || '',
            tanggal: data.tanggal || '',
            last_activitycode: data.last_activitycode || this.plot.last_activitycode || '',
            last_activityname: data.last_activityname || this.plot.last_activityname || '',
            last_activity_date: data.last_activity_date || this.plot.last_activity_date || '',
            zpk_date: data.batchinfo?.zpk_date || '',
            zpk_days_gap: data.batchinfo?.zpk_days_gap || 0,
            zpk_status: data.batchinfo?.zpk_status || ''
          };
          
          // Auto-fill luas
          this.luasKerja = this.isPiasActivity ? 
            parseFloat(this.plotInfo.luasplot).toFixed(2) : 
            parseFloat(this.plotInfo.luassisa).toFixed(2);
          
          // ✅ FIX: Safe parent access
          this.updateParentLuas(this.luasKerja);
        }
      } catch (error) {
        console.error('Error fetching plot info:', error);
      } finally {
        this.isLoading = false;
      }
    },

    handleLuasInput(event) {
      let value = event.target.value;
      value = value.replace(/[^\d.]/g, '');
      const parts = value.split('.');
      if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
      }
      
      this.luasKerja = value;
      
      if (this.isPiasActivity) {
        if (value && !value.endsWith('.')) {
          const numValue = parseFloat(value);
          if (!isNaN(numValue)) {
            this.validateAndUpdate(numValue, event.target);
          }
        }
        return;
      }
      
      if (value && !value.endsWith('.')) {
        const numValue = parseFloat(value);
        if (!isNaN(numValue)) {
          this.validateAndUpdate(numValue, event.target);
        }
      }
    },

    formatLuasOnBlur(event) {
      let value = parseFloat(this.luasKerja);
      
      if (isNaN(value) || value < 0) {
        value = 0;
      }
      
      const maxLuas = this.isPiasActivity ? 
        parseFloat(this.plotInfo.luasplot) : 
        parseFloat(this.plotInfo.luassisa);
      
      if (value > maxLuas) {
        value = maxLuas;
        showToast(`Luas tidak boleh melebihi ${maxLuas.toFixed(2)} Ha`, 'warning', 3000);
        
        event.target.classList.add('border-red-500', 'bg-red-50');
        setTimeout(() => {
          event.target.classList.remove('border-red-500', 'bg-red-50');
        }, 2000);
      }
      
      this.luasKerja = value.toFixed(2);
      this.updateParentLuas(this.luasKerja);
    },

    validateAndUpdate(value, inputElement) {
      const maxLuas = this.isPiasActivity ? 
        parseFloat(this.plotInfo.luasplot) : 
        parseFloat(this.plotInfo.luassisa);
      
      if (value > maxLuas) {
        showToast(`Luas tidak boleh melebihi ${maxLuas.toFixed(2)} Ha`, 'warning', 2000);
        inputElement.classList.add('border-red-500');
        setTimeout(() => {
          inputElement.classList.remove('border-red-500');
        }, 1500);
      } else {
        this.updateParentLuas(value.toFixed(2));
      }
    },

    // ✅ FIX: Safe parent component access
    updateParentLuas(value) {
      try {
        // Method 1: Try $root (most reliable)
        const wizardApp = this.$root;
        if (wizardApp && wizardApp.luasConfirmed) {
          const key = `${this.actCode}_${this.plot.blok}_${this.plot.plot}`;
          wizardApp.luasConfirmed[key] = value;
          return;
        }
        
        // Method 2: Fallback to querySelector
        const wizardEl = document.querySelector('[x-data*="rkhEditWizardApp"], [x-data*="rkhWizardApp"]');
        if (wizardEl) {
          const app = Alpine.$data(wizardEl);
          if (app && app.luasConfirmed) {
            const key = `${this.actCode}_${this.plot.blok}_${this.plot.plot}`;
            app.luasConfirmed[key] = value;
          }
        }
      } catch (error) {
        console.warn('Could not update parent luas, parent component not found:', error);
      }
    },

    getDaysGap(lastActivityDate) {
      if (!lastActivityDate || !window.rkhDate) return 0;
      
      const lastDate = new Date(lastActivityDate);
      const rkhDate = new Date(window.rkhDate);
      const diffTime = rkhDate - lastDate;
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      
      return diffDays;
    },

    formatDateDMY(dateString) {
      if (!dateString) return '-';
      
      if (dateString.includes('/')) {
        const parts = dateString.split('/');
        return `${parts[0]}-${parts[1]}-${parts[2].slice(-2)}`;
      }
      
      const date = new Date(dateString);
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = String(date.getFullYear()).slice(-2);
      return `${day}-${month}-${year}`;
    }
  };
}
</script>