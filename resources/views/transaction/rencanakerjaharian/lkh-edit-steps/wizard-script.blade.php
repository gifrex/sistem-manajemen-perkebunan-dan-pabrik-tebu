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
    isDirty: false,
    lastRecalculateHash: null,

    // Data from server
    lkhData: @json($lkhData),
    keterangan: '{{ old('keterangan', $lkhData->keterangan) }}',
    jenistenagakerja: {{ $lkhData->jenistenagakerja }},
    boronganRate: {{ $boronganRate ?? 0 }},

    // ✅ Blok Activity flag
    isBlokActivity: {{ $isBlokActivity ? 'true' : 'false' }},

    // ✅ Worker data — mandor-filtered + all
    tenagaKerja: @json($tenagaKerja ?? []),
    allTenagaKerja: @json($allTenagaKerja ?? []),
    showAllWorkers: false,

    workers: @json($lkhWorkerDetails->toArray()),
    materials: @json($lkhMaterialDetails->toArray()),

    // Plot selection state
    plots: @json($lkhPlotDetails->toArray()),
    selectedBlok: null,
    plotSearch: '',
    availableBloks: [],

    // ✅ Blok activity: available blok list for add
    blokOptions: @json($bloks ?? []),

    // Initialization
    init() {
      this.buildAvailableBloks();
      
      if (this.availableBloks.length > 0) {
        this.selectedBlok = this.availableBloks[0];
      }
      
      if (!this.isBlokActivity) {
        this.plots.forEach((p, i) => this.calculateLuasSisa(i));
      }
      
      // Normalize tenagaKerja IDs
      const normalizeTk = (list) => list.map(tk => ({
        ...tk,
        tenagakerjaid: String(tk.tenagakerjaid || '').trim()
      }));

      this.tenagaKerja = normalizeTk(this.tenagaKerja);
      this.allTenagaKerja = normalizeTk(this.allTenagaKerja);

      // Check if any existing worker is NOT in mandor list — auto-enable show all
      const mandorIds = new Set(this.tenagaKerja.map(tk => tk.tenagakerjaid));
      
      this.workers = this.workers.map(w => {
        const normalizedId = String(w.tenagakerjaid || '').trim();
        return {
          ...w,
          tenagakerjaid: normalizedId,
          nik: w.nik || '',
          nama: w.nama || ''
        };
      });

      const hasOutsideMandor = this.workers.some(w => w.tenagakerjaid && !mandorIds.has(w.tenagakerjaid));
      if (hasOutsideMandor) {
        this.showAllWorkers = true;
      }

      this.materials = this.materials.map(m => ({
        ...m,
        id: parseInt(m.id),
        plot: String(m.plot || ''),
        itemcode: String(m.itemcode || ''),
        itemname: String(m.itemname || ''),
        qtyditerima: parseFloat(m.qtyditerima || 0),
        qtysisa: parseFloat(m.qtysisa || 0),
        qtydigunakan: parseFloat(m.qtydigunakan || 0),
        satuan: String(m.satuan || ''),
        keterangan: String(m.keterangan || '')
      }));
      
      this.$nextTick(() => {
        this.workers = [...this.workers];
        this.materials = [...this.materials];
        this.lastRecalculateHash = this.getWorkerDataHash();
        this.isDirty = false;
      });
    },

    // =====================================
    // WORKER LIST (mandor filter)
    // =====================================

    /**
     * Returns the active worker list based on toggle
     */
    getActiveWorkerList() {
      return this.showAllWorkers ? this.allTenagaKerja : this.tenagaKerja;
    },

    toggleShowAllWorkers() {
      this.showAllWorkers = !this.showAllWorkers;
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
          batchno: plot.activebatchno || '-',
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
      if (this.isBlokActivity) return '0.00';
      return this.plots.reduce((sum, p) => sum + (parseFloat(p.luashasil) || 0), 0).toFixed(2);
    },

    getTotalLuasRKH() {
      if (this.isBlokActivity) return '0.00';
      return this.plots.reduce((sum, p) => sum + (parseFloat(p.luasrkh) || 0), 0).toFixed(2);
    },

    getTotalLuasSisa() {
      if (this.isBlokActivity) return '0.00';
      return this.plots.reduce((sum, p) => sum + (parseFloat(p.luassisa) || 0), 0).toFixed(2);
    },

    // =====================================
    // BLOK ACTIVITY METHODS
    // =====================================

    /**
     * Get unique blok options not yet added
     * Uses blokOptions (from master data) — each item may have .blok or .kodeblok property
     */
    getAvailableBlokOptions() {
      const usedBloks = new Set(this.plots.map(p => p.blok));
      
      // blokOptions from server: extract blok codes
      const allBloks = new Set();
      (this.blokOptions || []).forEach(b => {
        const code = b.blok || b.kodeblok || b.blokcode || b;
        if (code) allBloks.add(String(code));
      });
      
      // Also include bloks from masterlistData as fallback
      this.availableBloks.forEach(b => allBloks.add(b));
      
      return Array.from(allBloks).sort().filter(b => !usedBloks.has(b));
    },

    addBlokRow(blok) {
      if (!blok) return;
      // Check duplicate
      if (this.plots.some(p => p.blok === blok)) {
        this.showToast('Blok ' + blok + ' sudah ditambahkan', 'warning');
        return;
      }
      this.plots.push({
        blok: blok,
        plot: null,
        luasrkh: null,
        luashasil: null,
        luassisa: null,
        batchno: null,
        batchid: null,
        keterangan: null
      });
    },

    removeBlokRow(index) {
      if (this.plots.length <= 1) {
        this.showToast('Minimal 1 blok harus ada', 'warning');
        return;
      }
      this.plots.splice(index, 1);
    },

    // =====================================
    // NAVIGATION METHODS
    // =====================================
    nextStep() {
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
          if (this.isBlokActivity) {
            // Blok activity: at least 1 blok row, keterangan required
            return this.plots.length > 0 && this.keterangan.trim() !== '';
          }
          return this.plots.length > 0 && this.keterangan.trim() !== '';
        case 2:
          if (this.workers.length === 0) return false;
          if (!this.allWorkersHaveSelection()) return false;
          if (this.jenistenagakerja === 2 && !this.hasBoronganRate()) return false;
          return true;
        case 3: 
          return !this.hasMaterialValidationError();
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
      this.markDirty();
    },

    removeWorker(i) {
      if (confirm('Remove this worker?')) {
        this.workers.splice(i, 1);
        this.markDirty();
      }
    },

    updateWorkerNIK(i) {
      const w = this.workers[i];
      if (!w.tenagakerjaid) {
        w.nik = '';
        w.nama = '';
        return;
      }
      
      // Search in active list first, then fallback to all
      let tk = this.getActiveWorkerList().find(t => t.tenagakerjaid === w.tenagakerjaid);
      if (!tk) {
        tk = this.allTenagaKerja.find(t => t.tenagakerjaid === w.tenagakerjaid);
      }
      
      if (tk) {
        w.nik = tk.nik || '';
        w.nama = tk.nama || '';
      } else {
        w.nik = '';
        w.nama = '';
      }
      
      this.markDirty();
    },

    getWorkerName(id) {
      if (!id) return '-';
      let tk = this.getActiveWorkerList().find(t => t.tenagakerjaid === id);
      if (!tk) {
        tk = this.allTenagaKerja.find(t => t.tenagakerjaid === id);
      }
      return tk ? tk.nama : '-';
    },

    allWorkersHaveSelection() {
      return this.workers.length > 0 && this.workers.every(w => w.tenagakerjaid);
    },

    // =====================================
    // WAGE CALCULATION METHODS
    // =====================================
    
    getTotalUpah() {
      return this.calculateTotalWage();
    },

    calculateTotalWage() {
      if (this.jenistenagakerja == 1) {
        return this.workers.reduce((sum, w) => sum + (parseFloat(w.totalupah) || 0), 0);
      } else {
        const totalLuas = parseFloat(this.getTotalLuas()) || 0;
        return totalLuas * (this.boronganRate || 0);
      }
    },

    hasBoronganRate() {
      if (this.jenistenagakerja !== 2) return true;
      return this.boronganRate > 0;
    },

    async recalculateWages() {
      if (!confirm('Recalculate all wages?')) return;
      this.isCalculating = true;

      try {
        const normalizedWorkers = this.workers.map(w => ({
          ...w,
          jammasuk: this.normalizeTime(w.jammasuk),
          jamselesai: this.normalizeTime(w.jamselesai),
        }));

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
            workers: normalizedWorkers,
            plots: this.isBlokActivity ? [] : this.plots,
          })
        });

        const result = await res.json();
        
        if (result.success) {
          if (result.type === 'harian') {
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
            this.lastRecalculateHash = this.getWorkerDataHash();
            this.isDirty = false;
          }
        } else {
          this.showToast('❌ ' + (result.message || 'Failed to recalculate'), 'error');
        }
      } catch (e) {
        console.error('Recalculate error:', e);
        this.showToast('❌ Error: ' + e.message, 'error');
      } finally {
        this.isCalculating = false;
      }
    },

    // =====================================
    // MATERIAL METHODS
    // =====================================
    
    validateMaterialUsed(material) {
      const used = parseFloat(material.qtydigunakan || 0);
      const received = parseFloat(material.qtyditerima || 0);
      material.qtysisa = received - used;
    },
    
    hasMaterialValidationError() {
      return this.materials.some(m => {
        const used = parseFloat(m.qtydigunakan || 0);
        const received = parseFloat(m.qtyditerima || 0);
        return used > received;
      });
    },
    
    getTotalReceived() {
      return this.materials.reduce((sum, m) => sum + parseFloat(m.qtyditerima || 0), 0).toFixed(3);
    },
    
    getTotalRemaining() {
      return this.materials.reduce((sum, m) => {
        return sum + (parseFloat(m.qtyditerima || 0) - parseFloat(m.qtydigunakan || 0));
      }, 0).toFixed(3);
    },
    
    getTotalUsed() {
      return this.materials.reduce((sum, m) => sum + parseFloat(m.qtydigunakan || 0), 0).toFixed(3);
    },

    // =====================================
    // VALIDATION & SUBMIT
    // =====================================
    validateAndSubmit() {
      this.validationErrors = [];

      if (!this.keterangan || this.keterangan.trim() === '') {
        this.validationErrors.push('Alasan Edit wajib diisi');
      }

      if (this.plots.length === 0) {
        this.validationErrors.push(this.isBlokActivity ? 'Minimal 1 blok harus ada' : 'At least 1 plot is required');
      }

      if (this.workers.length === 0) {
        this.validationErrors.push('At least 1 worker is required');
      } else {
        this.workers.forEach((w, i) => {
          if (!w.tenagakerjaid) this.validationErrors.push(`Worker #${i + 1}: Select worker name`);
        });
      }

      if (this.hasMaterialValidationError()) {
        this.validationErrors.push('Some materials have invalid quantities (Used > Received)');
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
          materials: this.materials,
          is_blok_activity: this.isBlokActivity,
        };

        const res = await fetch('{{ route("transaction.rencanakerjaharian.updateLKH", $lkhData->lkhno) }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          },
          body: JSON.stringify(formData)
        });

        const responseText = await res.text();

        let result;
        try {
          result = JSON.parse(responseText);
        } catch (parseError) {
          console.error('JSON Parse Error:', parseError);
          this.showToast('❌ Server returned invalid response.', 'error');
          this.isSubmitting = false;
          return;
        }

        if (result.success) {
          window.dispatchEvent(new CustomEvent('lkh-success', {
            detail: { lkhno: this.lkhData.lkhno, message: result.message || 'LKH berhasil diupdate' }
          }));
        } else {
          this.showToast('❌ ' + (result.message || 'Failed to save'), 'error');
          this.isSubmitting = false;
        }
      } catch (e) {
        console.error('Submit error:', e);
        this.showToast('❌ Error: ' + e.message, 'error');
        this.isSubmitting = false;
      }
    },

    // =====================================
    // HELPER METHODS
    // =====================================
    
    normalizeTime(time) {
      if (!time) return null;
      if (time.length === 8) return time;
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

    markDirty() {
      if (this.jenistenagakerja != 1) return;
      this.isDirty = true;
    },

    getWorkerDataHash() {
      if (this.jenistenagakerja != 1) return null;
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