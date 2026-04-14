<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div 
    x-data="{
      open: @json($errors->any()),
      mode: 'create',
      form: { 
        companycode: '{{ session('companycode') }}', 
        batchnooriginal: '',
        batchno: '{{ old('batchno') }}', 
        plot: '{{ old('plot') }}',
        plottype: '{{ old('plottype') }}',
        batchdate: '{{ old('batchdate') }}',
        tanggalulangtahun: '{{ old('tanggalulangtahun') }}',
        batcharea: '{{ old('batcharea') }}',
        kodevarietas: '{{ old('kodevarietas') }}',
        lifecyclestatus: '{{ old('lifecyclestatus', 'PC') }}',
        pkp: '{{ old('pkp') }}',
        lastactivity: '{{ old('lastactivity') }}',
        isactive: '{{ old('isactive', '1') }}',
        plantinglkhno: '{{ old('plantinglkhno') }}',
        tanggalpanen: '{{ old('tanggalpanen') }}'
      },
      resetForm() {
        this.mode = 'create';
        this.form = { 
          companycode: '{{ session('companycode') }}', 
          batchnooriginal: '',
          batchno: '', 
          plot: '',
          plottype: '',
          batchdate: '',
          tanggalulangtahun: '',
          batcharea: '',
          kodevarietas: '',
          lifecyclestatus: 'PC',
          pkp: '',
          lastactivity: '',
          isactive: '1',
          plantinglkhno: '',
          tanggalpanen: ''
        };
        this.open = true;
      },
      editBatch(data) {
        this.mode = 'edit';
        this.form = {
          companycode: data.companycode,
          batchnooriginal: data.batchno,
          batchno: data.batchno,
          plot: data.plot,
          plottype: data.plottype || '',
          batchdate: data.batchdate ? data.batchdate.substring(0, 10) : '',
          tanggalulangtahun: data.tanggalulangtahun ? data.tanggalulangtahun.substring(0, 10) : '',
          batcharea: data.batcharea || '',
          kodevarietas: data.kodevarietas || '',
          lifecyclestatus: data.lifecyclestatus || 'PC',
          pkp: data.pkp ?? '',
          lastactivity: data.lastactivity || '',
          isactive: String(data.isactive ? 1 : 0),
          plantinglkhno: data.plantinglkhno || '',
          tanggalpanen: data.tanggalpanen ? data.tanggalpanen.substring(0, 10) : ''
        };
        this.open = true;
      },

      {{-- Split/Merge Info Modal --}}
      smOpen: false,
      smData: {
        batchno: '',
        splitfrombatchno: '',
        mergedtobatchno: '',
        splitmergedreason: '',
        previousbatchno: ''
      },
      showSplitMerge(data) {
        this.smData = {
          batchno: data.batchno,
          splitfrombatchno: data.splitfrombatchno || '',
          mergedtobatchno: data.mergedtobatchno || '',
          splitmergedreason: data.splitmergedreason || '',
          previousbatchno: data.previousbatchno || ''
        };
        this.smOpen = true;
      }
    }"
    class="mx-auto py-1 bg-white rounded-md shadow-md">

    {{-- ==================== TOOLBAR ==================== --}}
    <div class="flex items-center justify-between px-4 py-2">
      @can('masterdata.batch.create')
        <button @click="resetForm()"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2">
          <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5"/>
          </svg>
          New Data
        </button>
      @endcan

      <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
        <label for="search" class="text-xs font-medium text-gray-700">Search:</label>
        <input type="text" name="search" id="search" value="{{ request('search') }}"
               class="text-xs mt-1 block w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
               onkeydown="if(event.key==='Enter') this.form.submit()"/>
      </form>

      <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
        <label for="perPage" class="text-xs font-medium text-gray-700">Items per page:</label>
        <select name="perPage" id="perPage" onchange="this.form.submit()"
                class="text-xs mt-1 block w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
          <option value="10" {{ (int)request('perPage', $perPage) === 10 ? 'selected' : '' }}>10</option>
          <option value="20" {{ (int)request('perPage', $perPage) === 20 ? 'selected' : '' }}>20</option>
          <option value="50" {{ (int)request('perPage', $perPage) === 50 ? 'selected' : '' }}>50</option>
        </select>
      </form>
    </div>

    {{-- ==================== MODAL CREATE/EDIT ==================== --}}
    <template x-teleport="body">
      <div x-show="open" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-500/75" @click="open = false"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.outside="open = false"
                 class="relative w-full max-w-5xl transform rounded-lg bg-white shadow-xl">

              <form method="POST"
                    :action="mode === 'edit'
                      ? '{{ url('masterdata/batch') }}/' + encodeURIComponent(form.batchnooriginal)
                      : '{{ url('masterdata/batch') }}'"
                    class="px-6 pt-4 pb-6 space-y-5">
                @csrf
                <template x-if="mode === 'edit'">
                  <input type="hidden" name="_method" value="PATCH">
                </template>

                {{-- Header --}}
                <div class="flex items-center justify-between border-b pb-3">
                  <h3 class="text-lg font-semibold text-gray-900" x-text="mode === 'edit' ? 'Edit Batch' : 'Create Batch'"></h3>
                  <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>

                {{-- Section: Informasi Utama --}}
                <div>
                  <h4 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">Informasi Utama</h4>
                  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-5 gap-y-4">

                    {{-- Company Code --}}
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Kode Company</label>
                      <input type="hidden" name="companycode" x-model="form.companycode">
                      <div class="mt-1 px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm text-gray-700 font-medium"
                           x-text="form.companycode"></div>
                    </div>

                    {{-- Batch No --}}
                    <div>
                      <label for="batchno" class="block text-sm font-medium text-gray-700">Batch No <span class="text-red-500">*</span></label>
                      <input type="text" name="batchno" id="batchno" x-model="form.batchno"
                             @input="form.batchno = form.batchno.toUpperCase()"
                             class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 uppercase"
                             maxlength="20" required>
                      @error('batchno')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                      @enderror
                    </div>

                    {{-- Plot --}}
                    <div>
                      <label for="plot" class="block text-sm font-medium text-gray-700">Plot <span class="text-red-500">*</span></label>
                      <input type="text" name="plot" id="plot" x-model="form.plot"
                             @input="form.plot = form.plot.toUpperCase()"
                             class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 uppercase"
                             maxlength="5" required>
                    </div>

                    {{-- Tipe Plot --}}
                    <div>
                      <label for="plottype" class="block text-sm font-medium text-gray-700">Tipe Plot</label>
                      <select name="plottype" id="plottype" x-model="form.plottype"
                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih --</option>
                        <option value="KBD">KBD - Kebun Bibit</option>
                        <option value="KTG">KTG - Kebun Tebu Giling</option>
                        <option value="KBI">KBI - Kebun Bibit Induk</option>
                      </select>
                    </div>
                  </div>
                </div>

                {{-- Section: Tanggal & Area --}}
                <div>
                  <h4 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">Tanggal & Area</h4>
                  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-5 gap-y-4">

                    {{-- Batch Date --}}
                    <div>
                      <label for="batchdate" class="block text-sm font-medium text-gray-700">Batch Date <span class="text-red-500">*</span></label>
                      <input type="date" name="batchdate" id="batchdate" x-model="form.batchdate"
                             class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>

                    {{-- Tanggal Ulang Tahun --}}
                    <div>
                      <label for="tanggalulangtahun" class="block text-sm font-medium text-gray-700">Tgl Ulang Tahun</label>
                      <input type="date" name="tanggalulangtahun" id="tanggalulangtahun" x-model="form.tanggalulangtahun"
                             class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                      <p class="mt-1 text-xs text-gray-500">Anniversary batch (otomatis/manual)</p>
                    </div>

                    {{-- Batch Area --}}
                    <div>
                      <label for="batcharea" class="block text-sm font-medium text-gray-700">Batch Area (ha) <span class="text-red-500">*</span></label>
                      <input type="number" step="0.01" name="batcharea" id="batcharea" x-model="form.batcharea"
                             class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                             min="0" max="9999.99" required>
                    </div>

                    {{-- Tanggal Panen --}}
                    <div>
                      <label for="tanggalpanen" class="block text-sm font-medium text-gray-700">Tanggal Panen</label>
                      <input type="date" name="tanggalpanen" id="tanggalpanen" x-model="form.tanggalpanen"
                             class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                  </div>
                </div>

                {{-- Section: Varietas & Status --}}
                <div>
                  <h4 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">Varietas & Status</h4>
                  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-5 gap-y-4">

                    {{-- Kode Varietas --}}
                    <div>
                      <label for="kodevarietas" class="block text-sm font-medium text-gray-700">Kode Varietas</label>
                      <input type="text" name="kodevarietas" id="kodevarietas" x-model="form.kodevarietas"
                             class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                             maxlength="10">
                    </div>

                    {{-- Lifecycle Status --}}
                    <div>
                      <label for="lifecyclestatus" class="block text-sm font-medium text-gray-700">Lifecycle Status <span class="text-red-500">*</span></label>
                      <select name="lifecyclestatus" id="lifecyclestatus" x-model="form.lifecyclestatus"
                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="PC">PC</option>
                        <option value="RC1">RC1</option>
                        <option value="RC2">RC2</option>
                        <option value="RC3">RC3</option>
                      </select>
                    </div>

                    {{-- PKP --}}
                    <div>
                      <label for="pkp" class="block text-sm font-medium text-gray-700">PKP (Populasi/Ha)</label>
                      <input type="number" name="pkp" id="pkp" x-model="form.pkp"
                             class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                             min="0">
                    </div>

                    {{-- Status Batch (edit only) --}}
                    <div x-show="mode === 'edit'" x-cloak>
                      <label for="isactive" class="block text-sm font-medium text-gray-700">Status Batch</label>
                      <select name="isactive" id="isactive" x-model="form.isactive"
                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="1">Active</option>
                        <option value="0">Closed</option>
                      </select>
                    </div>
                  </div>
                </div>

                {{-- Section: Lain-lain --}}
                <div>
                  <h4 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">Lain-lain</h4>
                  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-5 gap-y-4">

                    {{-- Planting RKH No --}}
                    <div>
                      <label for="plantinglkhno" class="block text-sm font-medium text-gray-700">Planting RKH No</label>
                      <input type="text" name="plantinglkhno" id="plantinglkhno" x-model="form.plantinglkhno"
                             class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                             maxlength="15">
                    </div>

                    {{-- Last Activity --}}
                    <div>
                      <label for="lastactivity" class="block text-sm font-medium text-gray-700">Last Activity</label>
                      <input type="text" name="lastactivity" id="lastactivity" x-model="form.lastactivity"
                             class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                             maxlength="100">
                    </div>
                  </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-3 border-t pt-4">
                  <button type="button" @click="open = false"
                          class="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50">
                    Cancel
                  </button>
                  <button type="submit"
                          class="rounded-md bg-blue-600 px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700"
                          x-text="mode === 'edit' ? 'Update' : 'Create'">
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </template>

    {{-- ==================== MODAL SPLIT/MERGE INFO ==================== --}}
    <template x-teleport="body">
      <div x-show="smOpen" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true">
        <div x-show="smOpen" x-transition.opacity class="fixed inset-0 bg-gray-500/75" @click="smOpen = false"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="smOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.outside="smOpen = false"
                 class="relative w-full max-w-lg transform rounded-lg bg-white shadow-xl">

              <div class="px-6 pt-4 pb-6 space-y-4">
                {{-- Header --}}
                <div class="flex items-center justify-between border-b pb-3">
                  <h3 class="text-lg font-semibold text-gray-900">
                    Split / Merge Info
                  </h3>
                  <button type="button" @click="smOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>

                {{-- Batch No --}}
                <div class="flex items-center gap-2">
                  <span class="text-sm font-medium text-gray-500 w-40">Batch No</span>
                  <span class="text-sm font-semibold text-gray-900" x-text="smData.batchno"></span>
                </div>

                {{-- Previous Batch --}}
                <div class="flex items-center gap-2">
                  <span class="text-sm font-medium text-gray-500 w-40">Previous Batch</span>
                  <template x-if="smData.previousbatchno">
                    <span class="inline-flex items-center gap-1 text-sm text-blue-700 bg-blue-50 px-2 py-0.5 rounded font-medium" x-text="smData.previousbatchno"></span>
                  </template>
                  <template x-if="!smData.previousbatchno">
                    <span class="text-sm text-gray-400">—</span>
                  </template>
                </div>

                {{-- Split From --}}
                <div class="flex items-center gap-2">
                  <span class="text-sm font-medium text-gray-500 w-40">Split From Batch</span>
                  <template x-if="smData.splitfrombatchno">
                    <span class="inline-flex items-center gap-1 text-sm text-orange-700 bg-orange-50 px-2 py-0.5 rounded font-medium">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                      </svg>
                      <span x-text="smData.splitfrombatchno"></span>
                    </span>
                  </template>
                  <template x-if="!smData.splitfrombatchno">
                    <span class="text-sm text-gray-400">—</span>
                  </template>
                </div>

                {{-- Merged To --}}
                <div class="flex items-center gap-2">
                  <span class="text-sm font-medium text-gray-500 w-40">Merged To Batch</span>
                  <template x-if="smData.mergedtobatchno">
                    <span class="inline-flex items-center gap-1 text-sm text-purple-700 bg-purple-50 px-2 py-0.5 rounded font-medium">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4M8 17H4m0 0l4-4m-4 4l4 4"/>
                      </svg>
                      <span x-text="smData.mergedtobatchno"></span>
                    </span>
                  </template>
                  <template x-if="!smData.mergedtobatchno">
                    <span class="text-sm text-gray-400">—</span>
                  </template>
                </div>

                {{-- Reason --}}
                <div>
                  <span class="text-sm font-medium text-gray-500">Alasan Split/Merge</span>
                  <template x-if="smData.splitmergedreason">
                    <div class="mt-1 p-3 bg-gray-50 border border-gray-200 rounded-md text-sm text-gray-700 whitespace-pre-wrap" x-text="smData.splitmergedreason"></div>
                  </template>
                  <template x-if="!smData.splitmergedreason">
                    <p class="mt-1 text-sm text-gray-400">Tidak ada catatan</p>
                  </template>
                </div>

                {{-- No info state --}}
                <template x-if="!smData.previousbatchno && !smData.splitfrombatchno && !smData.mergedtobatchno && !smData.splitmergedreason">
                  <div class="text-center py-4">
                    <svg class="mx-auto w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-3-3v6m-7 4h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Batch ini bukan hasil split/merge</p>
                  </div>
                </template>

                {{-- Footer --}}
                <div class="flex justify-end border-t pt-4">
                  <button type="button" @click="smOpen = false"
                          class="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50">
                    Tutup
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    {{-- ==================== TABLE ==================== --}}
    <div class="mx-auto px-4 py-2">
      <div class="overflow-x-auto border border-gray-300 rounded-md">
        <table class="min-w-full bg-white text-sm text-center">
          <thead>
            <tr class="bg-gray-100 text-gray-700">
              <th class="py-2 px-4 border-b">No.</th>
              <th class="py-2 px-4 border-b">Batch No</th>
              <th class="py-2 px-4 border-b">Plot</th>
              <th class="py-2 px-4 border-b">Tipe Plot</th>
              <th class="py-2 px-4 border-b">Batch Date</th>
              <th class="py-2 px-4 border-b">Tgl Ultah</th>
              <th class="py-2 px-4 border-b">Area (ha)</th>
              <th class="py-2 px-4 border-b">Varietas</th>
              <th class="py-2 px-4 border-b">Lifecycle</th>
              <th class="py-2 px-4 border-b">PKP</th>
              <th class="py-2 px-4 border-b">Status</th>
              <th class="py-2 px-4 border-b">Tgl Panen</th>
              <th class="py-2 px-4 border-b">Split/Merge</th>
              <th class="py-2 px-4 border-b">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($batch as $index => $data)
              <tr class="hover:bg-gray-50">
                <td class="py-2 px-4 border-b">{{ $batch->firstItem() + $index }}</td>
                <td class="py-2 px-4 border-b font-medium">{{ $data->batchno }}</td>
                <td class="py-2 px-4 border-b">{{ $data->plot }}</td>
                <td class="py-2 px-4 border-b">
                  @if($data->plottype)
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $data->plottype_badge_color }}">
                      {{ $data->plottype }}
                    </span>
                  @else
                    <span class="text-gray-400">-</span>
                  @endif
                </td>
                <td class="py-2 px-4 border-b">{{ $data->batchdate ? \Carbon\Carbon::parse($data->batchdate)->format('d/m/Y') : '-' }}</td>
                <td class="py-2 px-4 border-b">{{ $data->tanggalulangtahun ? \Carbon\Carbon::parse($data->tanggalulangtahun)->format('d/m/Y') : '-' }}</td>
                <td class="py-2 px-4 border-b">{{ $data->batcharea ? number_format($data->batcharea, 2) : '-' }}</td>
                <td class="py-2 px-4 border-b">{{ $data->kodevarietas ?? '-' }}</td>
                <td class="py-2 px-4 border-b">
                  <span class="px-2 py-1 text-xs font-medium rounded-full {{ $data->lifecycle_badge_color }}">
                    {{ $data->lifecyclestatus }}
                  </span>
                </td>
                <td class="py-2 px-4 border-b">{{ $data->pkp ?? '-' }}</td>
                <td class="py-2 px-4 border-b">
                  @if($data->isactive)
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                  @else
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Closed</span>
                  @endif
                </td>
                <td class="py-2 px-4 border-b">
                  {{ $data->tanggalpanen ? \Carbon\Carbon::parse($data->tanggalpanen)->format('d/m/Y') : '-' }}
                </td>

                {{-- Split/Merge column --}}
                <td class="py-2 px-4 border-b">
                  @if($data->splitfrombatchno || $data->mergedtobatchno || $data->previousbatchno)
                    <button type="button"
                            @click="showSplitMerge(@js($data))"
                            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800 hover:bg-amber-200 transition-colors">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 110 20 10 10 0 010-20z"/>
                      </svg>
                      Info
                    </button>
                  @else
                    <span class="text-gray-400">-</span>
                  @endif
                </td>

                {{-- Actions --}}
                <td class="py-2 px-4 border-b">
                  <div class="flex items-center justify-center space-x-2">
                    @can('masterdata.batch.edit')
                      <button type="button"
                              @click="editBatch(@js($data))"
                              class="group flex items-center text-blue-600 hover:text-blue-800 rounded-md px-2 py-1 text-sm">
                        <svg class="w-6 h-6 text-blue-500 group-hover:hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <use xlink:href="#icon-edit-outline"/>
                        </svg>
                        <svg class="w-6 h-6 text-blue-500 hidden group-hover:block" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                          <use xlink:href="#icon-edit-solid"/>
                          <use xlink:href="#icon-edit-solid2"/>
                        </svg>
                      </button>
                    @endcan
                    @can('masterdata.batch.delete')
                      <form action="{{ url("masterdata/batch/{$data->batchno}") }}" method="POST"
                            onsubmit="return confirm('Yakin ingin menghapus data ini?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="group flex items-center text-red-600 hover:text-red-800 rounded-md px-2 py-1 text-sm">
                          <svg class="w-6 h-6 text-red-500 group-hover:hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <use xlink:href="#icon-trash-outline"/>
                          </svg>
                          <svg class="w-6 h-6 text-red-500 hidden group-hover:block" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                            <use xlink:href="#icon-trash-solid"/>
                          </svg>
                        </button>
                      </form>
                    @endcan
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    {{-- ==================== PAGINATION ==================== --}}
    <div class="mx-4 my-1">
      @if ($batch->hasPages())
        {{ $batch->appends(request()->query())->links() }}
      @else
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-700">
            Showing <span class="font-medium">{{ $batch->count() }}</span> of <span class="font-medium">{{ $batch->total() }}</span> results
          </p>
        </div>
      @endif
    </div>

    @if (session('success'))
      <div x-data x-init="alert('{{ session('success') }}')"></div>
    @endif
  </div>
</x-layout>