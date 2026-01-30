{{-- resources/views/transaction/rencanakerjaharian/lkh-edit-steps/wizard-script.blade.php --}}

<script>
// Global data initialization
window.masterlistData = @json($masterlist ?? []);
window.plotsData = @json($plots ?? []);

document.addEventListener('alpine:init', () => {
  Alpine.data('lkhEditWizard', () => ({
    // Navigation
    currentStep: 1,
    steps: [
      { id: 1, title: 'Plots' },
      { id: 2, title: 'Workers' },
      { id: 3, title: 'Materials' },
      { id: 4, title: 'Review' }
    ],

    // State
    isSubmitting: false,
    isCalculating: false,
    showValidationModal: false,
    showRecalculateModal: false,
    validationErrors: [],
    isDirty: false, // Track if worker data has been modified
    lastRecalculateHash: null, // Hash of worker data when last recalculated

    // Data from server
    lkhData: @json($lkhData),
    tenagaKerja: @json($tenagaKerja ?? []),
    workers: @json($lkhWorkerDetails->toArray()),
    materials: @json($lkhMaterialDetails->toArray()), // READ-ONLY (tidak akan diubah)
    keterangan: '{{ old('keterangan', $lkhData->keterangan) }}',
    jenistenagakerja: {{ $lkhData->jenistenagakerja }},
    boronganRate: {{ $boronganRate ?? 0 }},

    // Plot selection state
    plots: @json($lkhPlotDetails->toArray()),
    selectedBlok: null,
    plotSearch: '',
    availableBloks: [],

    // Initialization
    init() {
      console.log('🚀 LKH Edit Wizard initialized');
      
      // Build available bloks from masterlist
      this.buildAvailableBloks();
      
      // Set default selected blok
      if (this.availableBloks.length > 0) {
        this.selectedBlok = this.availableBloks[0];
      }
      
      // Calculate luassisa for existing plots
      this.plots.forEach((p, i) => this.calculateLuasSisa(i));
      
      // FIX: Normalize tenagaKerja options FIRST (convert to string)
      this.tenagaKerja = this.tenagaKerja.map(tk => ({
        ...tk,
        tenagakerjaid: String(tk.tenagakerjaid || '').trim()
      }));
      
      // FIX: Then normalize workers and ensure they match available options
      this.workers = this.workers.map(w => {
        const normalizedId = String(w.tenagakerjaid || '').trim();
        
        // Check if this ID exists in tenagaKerja options
        const exists = this.tenagaKerja.some(tk => tk.tenagakerjaid === normalizedId);
        
        if (!exists && normalizedId) {
          console.warn('⚠️ Worker ID not found in options:', normalizedId);
        }
        
        return {
          ...w,
          tenagakerjaid: normalizedId,
          nik: w.nik || '',
          nama: w.nama || ''
        };
      });
      
      // 🐛 DEBUG: Verify data after normalization
      console.group('📊 Data Verification');
      console.log('Total workers:', this.workers.length);
      console.log('Total worker options:', this.tenagaKerja.length);
      console.log('Total materials (read-only):', this.materials.length);
      console.log('Jenis Tenaga Kerja:', this.jenistenagakerja, '(1=Harian, 2=Borongan)');
      console.log('Borongan Rate:', this.boronganRate);
      
      if (this.workers.length > 0) {
        const sample = this.workers[0];
        console.log('Sample worker:', {
          id: sample.tenagakerjaid,
          type: typeof sample.tenagakerjaid,
          length: sample.tenagakerjaid.length,
          nik: sample.nik,
          nama: sample.nama
        });
        
        const matchFound = this.tenagaKerja.find(tk => tk.tenagakerjaid === sample.tenagakerjaid);
        console.log('Match found:', matchFound ? 'YES' : '❌ NO');
        
        if (matchFound) {
          console.log('Matched option:', matchFound);
        }
      }
      console.groupEnd();
      
      // Force re-render selects after data loaded
      this.$nextTick(() => {
        this.workers = [...this.workers]; // Trigger reactivity
        
        // Calculate initial hash for dirty tracking
        this.lastRecalculateHash = this.getWorkerDataHash();
        this.isDirty = false;
      });
    },

    // =====================================
    // PLOT METHODS
    // =====================================
    buildAvailableBloks() {
      const blokSet = new Set();
      (window.masterlistData || []).forEach(plot => {
        if (plot.blok) blokSet.add(plot.blok);
      });
      this.availableBloks = Array.from(blokSet).sort();
    },

    getPlotsForBlok(blok) {
      if (!blok) return [];
      return (window.masterlistData || []).filter(p => p.blok === blok);
    },

    filteredPlotsForBlok() {
      const plots = this.getPlotsForBlok(this.selectedBlok);
      if (!this.plotSearch) return plots;
      const q = this.plotSearch.toLowerCase();
      return plots.filter(p => p.plot.toLowerCase().includes(q));
    },

    isPlotSelected(plot) {
      return this.plots.some(p => p.blok === plot.blok && p.plot === plot.plot);
    },

    getSelectedPlotsInBlok(blok) {
      return this.plots.filter(p => p.blok === blok);
    },

    togglePlot(plot) {
      const index = this.plots.findIndex(p => p.blok === plot.blok && p.plot === plot.plot);
      
      if (index > -1) {
        this.plots.splice(index, 1);
      } else {
        this.plots.push({
          blok: plot.blok,
          plot: plot.plot,
          luasrkh: parseFloat(plot.batcharea) || 0,
          luashasil: parseFloat(plot.batcharea) || 0,
          luassisa: 0,
          batchno: plot.activebatchno || '-', // FIX: Use activebatchno from backend
          batchid: plot.batch_id || plot.id || null,
          lifecyclestatus: plot.lifecyclestatus || null
        });
      }
    },

    removePlot(index) {
      this.plots.splice(index, 1);
    },

    clearAllPlots() {
      if (confirm('Clear all selected plots?')) {
        this.plots = [];
      }
    },

    calculateLuasSisa(index) {
      const plot = this.plots[index];
      const luasrkh = parseFloat(plot.luasrkh) || 0;
      const luashasil = parseFloat(plot.luashasil) || 0;
      plot.luassisa = (luasrkh - luashasil).toFixed(2);
    },

    getTotalLuas() {
      return this.plots.reduce((sum, p) => sum + (parseFloat(p.luashasil) || 0), 0).toFixed(2);
    },

    getTotalLuasRKH() {
      return this.plots.reduce((sum, p) => sum + (parseFloat(p.luasrkh) || 0), 0).toFixed(2);
    },

    getTotalLuasSisa() {
      return this.plots.reduce((sum, p) => sum + (parseFloat(p.luassisa) || 0), 0).toFixed(2);
    },

    // =====================================
    // NAVIGATION METHODS
    // =====================================
    nextStep() {
      // Check if on step 2 (workers) and data is dirty for HARIAN only
      if (this.currentStep === 2 && this.jenistenagakerja == 1 && this.isDirty) {
        this.showRecalculateModal = true;
        return;
      }
      
      if (this.canProceed() && this.currentStep < 4) {
        this.currentStep++;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    },

    prevStep() {
      if (this.currentStep > 1) {
        this.currentStep--;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    },

    goToStep(step) {
      if (step < this.currentStep) {
        this.currentStep = step;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    },

    canProceed() {
      switch(this.currentStep) {
        case 1: 
          // Step 1: Require plots AND keterangan (Alasan Edit)
          return this.plots.length > 0 && this.keterangan.trim() !== '';
        case 2: 
          return this.workers.length > 0 && this.allWorkersHaveSelection();
        case 3: 
          return true; // Materials are read-only, always can proceed
        default: 
          return true;
      }
    },

    getNextButtonText() {
      const texts = { 1: 'Next: Workers', 2: 'Next: Materials', 3: 'Review' };
      return texts[this.currentStep] || 'Next';
    },

    // =====================================
    // WORKER METHODS
    // =====================================
    addWorker() {
      this.workers.push({
        tenagakerjaid: '', nik: '', nama: '', jammasuk: '07:00', jamselesai: '16:00',
        totaljamkerja: 0, overtimehours: 0, premi: 0, upahharian: 0,
        upahperjam: 0, upahlembur: 0, upahborongan: 0, totalupah: 0, keterangan: ''
      });
      this.markDirty(); // Mark as dirty when adding worker
    },

    removeWorker(i) {
      if (confirm('Remove this worker?')) {
        this.workers.splice(i, 1);
        this.markDirty(); // Mark as dirty when removing worker
      }
    },

    updateWorkerNIK(i) {
      const w = this.workers[i];
      if (!w.tenagakerjaid) {
        w.nik = '';
        w.nama = '';
        return;
      }
      
      const tk = this.tenagaKerja.find(t => t.tenagakerjaid === w.tenagakerjaid);
      
      if (tk) {
        w.nik = tk.nik || '';
        w.nama = tk.nama || '';
        console.log('Worker data updated:', w.tenagakerjaid, '->', tk.nama);
      } else {
        w.nik = '';
        w.nama = '';
        console.warn('⚠️ Worker not found:', w.tenagakerjaid);
      }
      
      this.markDirty(); // Mark as dirty when changing worker
    },

    getWorkerName(id) {
      if (!id) return '-';
      const tk = this.tenagaKerja.find(t => t.tenagakerjaid === id);
      return tk ? tk.nama : '-';
    },

    allWorkersHaveSelection() {
      return this.workers.length > 0 && this.workers.every(w => w.tenagakerjaid);
    },

    // =====================================
    // WAGE CALCULATION METHODS
    // =====================================
    
    /**
     * Get total wage (OLD METHOD - kept for backward compatibility)
     * For HARIAN: Sum all worker wages
     * For BORONGAN: Not applicable (use calculateTotalWage instead)
     */
    getTotalUpah() {
      return this.calculateTotalWage();
    },

    /**
     * Calculate total wage (NEW METHOD - handles both HARIAN and BORONGAN)
     * This is the main method used in Step 4 Review
     */
    calculateTotalWage() {
      if (this.jenistenagakerja == 1) {
        // HARIAN: Sum dari totalupah setiap worker
        return this.workers.reduce((sum, w) => sum + (parseFloat(w.totalupah) || 0), 0);
      } else {
        // BORONGAN: Total Luas × Borongan Rate
        const totalLuas = parseFloat(this.getTotalLuas()) || 0;
        return totalLuas * (this.boronganRate || 0);
      }
    },

    /**
     * Recalculate wages from API (for HARIAN only)
     */
    async recalculateWages() {
      if (!confirm('Recalculate all wages?')) return;
      this.isCalculating = true;

      try {
        // FIX: Normalize time format H:i -> H:i:s BEFORE sending
        const normalizedWorkers = this.workers.map(w => ({
          ...w,
          jammasuk: this.normalizeTime(w.jammasuk),
          jamselesai: this.normalizeTime(w.jamselesai),
        }));

        console.log('📤 Sending recalculate request with normalized workers:', normalizedWorkers);

        const res = await fetch('{{ route("transaction.rencanakerjaharian.recalculateWages") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            companycode: this.lkhData.companycode,
            lkhno: this.lkhData.lkhno,
            activitycode: this.lkhData.activitycode,
            lkhdate: this.lkhData.lkhdate,
            jenistenagakerja: this.jenistenagakerja,
            workers: normalizedWorkers, // Use normalized workers
            plots: this.plots,
          })
        });

        const result = await res.json();
        console.log('📥 Recalculate response:', result);
        
        if (result.success) {
          if (result.type === 'harian') {
            // Update worker wages with calculated values
            this.workers.forEach(w => {
              const wage = result.wages[w.tenagakerjaid];
              if (wage) {
                w.totaljamkerja = wage.totaljamkerja || 0;
                w.upahharian = wage.upahharian || 0;
                w.upahperjam = wage.upahperjam || 0;
                w.upahlembur = wage.upahlembur || 0;
                w.premi = wage.premi || 0;
                w.totalupah = wage.totalupah || 0;
              }
            });
            this.showToast('Wages recalculated successfully!', 'success');
            
            // Reset dirty flag after successful recalculation
            this.lastRecalculateHash = this.getWorkerDataHash();
            this.isDirty = false;
          }
        } else {
          this.showToast('❌ ' + (result.message || 'Failed to recalculate'), 'error');
        }
      } catch (e) {
        console.error('❌ Recalculate error:', e);
        this.showToast('❌ Error: ' + e.message, 'error');
      } finally {
        this.isCalculating = false;
      }
    },

    // =====================================
    // MATERIAL METHODS (READ-ONLY)
    // =====================================
    // ❌ DISABLED: addMaterial() - Materials cannot be added
    // ❌ DISABLED: removeMaterial(i) - Materials cannot be removed
    // ❌ DISABLED: calculateMaterialUsage(i) - Materials are read-only
    
    // KEEP: Read-only summary methods
    getTotalReceived() {
      return this.materials.reduce((s, m) => s + (parseFloat(m.qtyditerima) || 0), 0).toFixed(3);
    },

    getTotalRemaining() {
      return this.materials.reduce((s, m) => s + (parseFloat(m.qtysisa) || 0), 0).toFixed(3);
    },

    getTotalUsed() {
      return this.materials.reduce((s, m) => s + (parseFloat(m.qtydigunakan) || 0), 0).toFixed(3);
    },

    // =====================================
    // VALIDATION & SUBMIT
    // =====================================
    validateAndSubmit() {
      this.validationErrors = [];

      // Validate keterangan (Alasan Edit) first - MANDATORY
      if (!this.keterangan || this.keterangan.trim() === '') {
        this.validationErrors.push('Alasan Edit wajib diisi');
      }

      if (this.plots.length === 0) {
        this.validationErrors.push('At least 1 plot is required');
      }

      if (this.workers.length === 0) {
        this.validationErrors.push('At least 1 worker is required');
      } else {
        this.workers.forEach((w, i) => {
          if (!w.tenagakerjaid) this.validationErrors.push(`Worker #${i + 1}: Select worker name`);
        });
      }

      if (this.validationErrors.length > 0) {
        this.showValidationModal = true;
        return;
      }

      this.submitForm();
    },

    async submitForm() {
      this.isSubmitting = true;

      try {
        // Normalize time format to H:i:s before sending
        const normalizedWorkers = this.workers.map(w => ({
          ...w,
          jammasuk: this.normalizeTime(w.jammasuk),
          jamselesai: this.normalizeTime(w.jamselesai),
        }));

        const formData = {
          _token: '{{ csrf_token() }}',
          _method: 'PUT',
          keterangan: this.keterangan,
          plots: this.plots,
          workers: normalizedWorkers,
          // ❌ MATERIALS NOT SENT - Read-only, won't be updated
          // materials: this.materials
        };

        console.log('📤 Submitting LKH data:', formData);

        const res = await fetch('{{ route("transaction.rencanakerjaharian.updateLKH", $lkhData->lkhno) }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          },
          body: JSON.stringify(formData)
        });

        // DEBUG: Check response before parsing
        console.log('📥 Response status:', res.status);
        const responseText = await res.text();
        console.log('📥 Response text (first 500 chars):', responseText.substring(0, 500));

        // Try parse JSON
        let result;
        try {
          result = JSON.parse(responseText);
          console.log('Parsed JSON result:', result);
        } catch (parseError) {
          console.error('❌ JSON Parse Error:', parseError);
          console.error('📄 Full response:', responseText);
          this.showToast('❌ Server returned invalid response. Check console for details.', 'error');
          this.isSubmitting = false;
          return;
        }

        if (result.success) {
          console.log('LKH update successful');
          window.dispatchEvent(new CustomEvent('lkh-success', {
            detail: { lkhno: this.lkhData.lkhno, message: result.message || 'LKH berhasil diupdate' }
          }));
        } else {
          console.warn('⚠️ LKH update failed:', result.message);
          this.showToast('❌ ' + (result.message || 'Failed to save'), 'error');
          this.isSubmitting = false;
        }
      } catch (e) {
        console.error('❌ Submit error:', e);
        this.showToast('❌ Error: ' + e.message, 'error');
        this.isSubmitting = false;
      }
    },

    // =====================================
    // HELPER METHODS
    // =====================================
    
    /**
     * Normalize time format from H:i to H:i:s
     * @param {string} time - Time in H:i or H:i:s format
     * @return {string|null} - Time in H:i:s format or null
     */
    normalizeTime(time) {
      if (!time) return null;
      // If already H:i:s format (length 8), return as is
      if (time.length === 8) return time;
      // If H:i format (length 5), append :00
      if (time.length === 5) return time + ':00';
      return time;
    },

    formatRupiah(amount) {
      return new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', minimumFractionDigits: 0
      }).format(amount || 0);
    },

    showToast(msg, type = 'info') {
      const colors = { 
        success: 'bg-green-500', 
        error: 'bg-red-500', 
        warning: 'bg-yellow-500', 
        info: 'bg-blue-500' 
      };
      const toast = document.createElement('div');
      toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white ${colors[type] || colors.info} transform transition-all duration-300 max-w-md`;
      toast.textContent = msg;
      document.body.appendChild(toast);
      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
      }, 4000);
    },

    // =====================================
    // DIRTY STATE TRACKING
    // =====================================
    
    /**
     * Mark worker data as dirty (modified)
     */
    markDirty() {
      if (this.jenistenagakerja != 1) return; // Only track for HARIAN
      this.isDirty = true;
      console.log('🔴 Worker data marked as dirty - recalculation required');
    },

    /**
     * Generate hash of worker data for comparison
     */
    getWorkerDataHash() {
      if (this.jenistenagakerja != 1) return null; // Only for HARIAN
      
      const data = this.workers.map(w => ({
        id: w.tenagakerjaid,
        in: w.jammasuk,
        out: w.jamselesai,
        ot: w.overtimehours
      }));
      
      return JSON.stringify(data);
    }
  }));
});
</script>