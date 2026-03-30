{{--resources\views\input\rencanakerjaharian\rkh-create.blade.php--}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <!-- VALIDATION ERROR MODAL -->
  <div x-data="validationErrorModal()"
      x-show="showValidationModal"
      x-cloak
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4"
      style="display: none;">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md">
      <div class="p-6 text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
          <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Form Belum Lengkap</h3>
        <p class="text-sm text-gray-600 mb-4">Mohon lengkapi field yang diperlukan:</p>

        <div class="text-left bg-red-50 rounded-lg p-3 mb-4 max-h-48 overflow-y-auto">
          <ul class="text-sm text-red-700 space-y-1">
            <template x-for="(error, index) in validationErrors" :key="index">
              <li x-text="error"></li>
            </template>
          </ul>
        </div>

        <button @click="closeModal()"
                class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
          OK, Saya Mengerti
        </button>
      </div>
    </div>
  </div>

  <!-- SUCCESS MODAL -->
  <div x-data="{ showModal: false, modalType: '', modalMessage: '', modalErrors: [] }"
       x-show="showModal"
       x-cloak
       class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4"
       style="display: none;">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md">
      <div x-show="modalType === 'success'" class="p-6 text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
          <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Berhasil!</h3>
        <p class="text-sm text-gray-600 mb-4" x-html="modalMessage"></p>
        <button @click="window.location.href = '{{ route('transaction.rencanakerjaharian.index') }}'"
                class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          OK
        </button>
      </div>
    </div>
  </div>

  <form id="rkh-form" action="{{ route('transaction.rencanakerjaharian.store') }}" method="POST">
    @csrf

    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4 rounded-lg shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                        Terdapat {{ $errors->count() }} kesalahan yang perlu diperbaiki:
                    </h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-gray-50 rounded-lg p-4 lg:p-6 mb-6 border border-blue-100">

      <div class="flex flex-col lg:flex-row gap-4 lg:gap-6 mb-6">

        <!-- LEFT: Form Fields -->
        <div class="w-full lg:max-w-md space-y-3">
          <input type="hidden" name="rkhno" value="{{ $rkhno }}">

          <!-- Mandor -->
          <div>
            <label for="mandor" class="block text-sm font-semibold text-gray-700 mb-1">Mandor</label>
            <input
                type="text"
                name="mandor"
                id="mandor"
                readonly
                value="{{ $selectedMandor->userid ?? '' }} - {{ $selectedMandor->name ?? '' }}"
                class="w-full text-sm font-medium border-2 border-gray-300 rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed"
            >
            <input type="hidden" name="mandor_id" value="{{ $selectedMandor->userid ?? '' }}">
          </div>

          <!-- Tanggal -->
          <div>
            <label for="tanggal" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal</label>
            <input
              type="date"
              name="tanggal"
              id="tanggal"
              value="{{ $selectedDate }}"
              readonly
              class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-sm font-medium cursor-not-allowed"
            />
          </div>

          <!-- Keterangan -->
          <div>
            <label for="keterangan" class="block text-sm font-semibold text-gray-700 mb-1">
              Keterangan
              <span class="text-xs text-gray-500 font-normal">(opsional)</span>
            </label>
            <textarea
              name="keterangan"
              id="keterangan"
              rows="2"
              placeholder="Masukkan keterangan..."
              maxlength="500"
              class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
            >{{ old('keterangan') }}</textarea>
          </div>

          <!-- Card: Data Absen -->
          <div class="bg-white rounded-lg shadow-md p-3 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center">
                <div class="w-2.5 h-2.5 bg-green-500 rounded-full mr-2"></div>
                <h3 class="text-sm font-bold text-gray-800">Data Absen</h3>
              </div>
              <p class="text-xs text-gray-600" id="absen-info">{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</p>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center">
              <div class="bg-blue-50 rounded-lg p-2">
                <div class="text-lg font-bold" id="summary-laki">0</div>
                <div class="text-xs text-gray-600">Laki-laki</div>
              </div>
              <div class="bg-pink-50 rounded-lg p-2">
                <div class="text-lg font-bold" id="summary-perempuan">0</div>
                <div class="text-xs text-gray-600">Perempuan</div>
              </div>
              <div class="bg-green-50 rounded-lg p-2">
                <div class="text-lg font-bold" id="summary-total">0</div>
                <div class="text-xs text-gray-600">Total</div>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT: Cards Section -->
        <div class="flex-1 space-y-3">

          <!-- Card: Info Pekerja -->
          <div x-data="workerInfoCard()" class="bg-white rounded-lg shadow-md p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center">
                <div class="w-3 h-3 bg-gray-600 rounded-full mr-2"></div>
                <h3 class="text-sm font-bold text-gray-800">Info Pekerja</h3>
              </div>
              <span class="text-xs text-gray-600" x-text="`${Object.keys(workers).length} Aktivitas`"></span>
            </div>

            <div>
              <template x-if="Object.keys(workers).length === 0">
                <div class="text-center py-8 text-gray-400">
                  <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                  <p class="text-xs">Belum ada aktivitas dipilih</p>
                </div>
              </template>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <template x-for="(worker, activityCode) in workers" :key="activityCode">
                  <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-3 border border-gray-300">
                    
                    <div class="mb-3 flex items-start justify-between">
                      <p class="text-sm font-semibold text-gray-900 truncate flex-1" 
                        x-text="`${activityCode} - ${worker.activityname}`"
                        :title="`${activityCode} - ${worker.activityname}`"></p>
                      
                      <span 
                        class="inline-block px-2 py-0.5 text-[10px] font-medium rounded-full ml-2 flex-shrink-0"
                        :class="{
                          'bg-blue-100 text-blue-700': worker.jenisId == 1,
                          'bg-green-100 text-green-700': worker.jenisId == 2,
                          'bg-orange-100 text-orange-700': worker.jenisId == 3,
                          'bg-purple-100 text-purple-700': worker.jenisId == 4,
                          'bg-gray-200 text-gray-700': !worker.jenisId
                        }"
                        x-text="worker.jenisLabel"
                      ></span>
                    </div>

                    <div class="flex items-end gap-2">
                      <!-- Laki-laki -->
                      <div class="flex-1">
                        <label class="text-[10px] text-gray-600 block mb-1">L</label>
                        <input
                          type="number"
                          x-model="worker.laki"
                          @input="updateWorkerTotal(activityCode)"
                          oninput="if(this.value.length > 3) this.value = this.value.slice(0,3);"
                          min="0"
                          max="999"
                          placeholder="-"
                          class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-gray-500 focus:border-gray-500"
                          required
                        >
                      </div>

                      <!-- Perempuan -->
                      <div class="flex-1">
                        <label class="text-[10px] text-gray-600 block mb-1">P</label>
                        <input
                          type="number"
                          x-model="worker.perempuan"
                          @input="updateWorkerTotal(activityCode)"
                          oninput="if(this.value.length > 3) this.value = this.value.slice(0,3);"
                          min="0"
                          max="999"
                          placeholder="-"
                          class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-gray-500 focus:border-gray-500"
                          required
                        >
                      </div>

                      <div class="flex-1">
                        <label class="text-[10px] text-gray-600 block mb-1">Tot</label>
                        <input
                          type="text"
                          x-model="worker.total"
                          readonly
                          placeholder="-"
                          class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 bg-gray-100 font-bold text-center cursor-not-allowed"
                        >
                      </div>
                    </div>

                    <input type="hidden" :name="`workers[${activityCode}][laki]`" x-model="worker.laki">
                    <input type="hidden" :name="`workers[${activityCode}][perempuan]`" x-model="worker.perempuan">
                    <input type="hidden" :name="`workers[${activityCode}][total]`" x-model="worker.total">
                  </div>
                </template>
              </div>
            </div>
          </div>

          <!-- Card: Info Kendaraan -->
          <div x-data="kendaraanInfoCard()" class="bg-white rounded-lg shadow-md p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center">
                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                <h3 class="text-sm font-bold text-gray-800">Info Kendaraan</h3>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-xs text-gray-600" x-text="`${getTotalKendaraan()} Unit`"></span>
                <button
                  type="button"
                  @click="openKendaraanModal()"
                  class="px-2 py-1 text-[10px] font-medium text-white bg-green-600 hover:bg-green-700 rounded transition-colors"
                >
                  + Tambah
                </button>
              </div>
            </div>

            <div>
              <template x-if="Object.keys(kendaraan).length === 0">
                <div class="text-center py-8 text-gray-400">
                  <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                  </svg>
                  <p class="text-xs">Belum ada kendaraan dipilih</p>
                  <p class="text-[10px] text-gray-500 mt-1">Pilih aktivitas terlebih dahulu</p>
                </div>
              </template>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <template x-for="(activityGroup, activityCode) in kendaraan" :key="activityCode">
                  <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-3 border border-green-200">
                    
                    <!-- Activity Header -->
                    <div class="mb-3 pb-2 border-b border-green-200 flex items-center justify-between">
                      <p class="text-sm font-semibold text-green-900 flex-1" x-text="getActivityFullName(activityCode)"></p>
                      <span class="text-[10px] text-green-700 font-medium" x-text="`${Object.keys(activityGroup).length} unit`"></span>
                    </div>

                    <!-- Kendaraan List (Simple) -->
                    <div class="space-y-1.5">
                      <template x-for="(item, urutan) in activityGroup" :key="`${activityCode}-${urutan}`">
                        <div class="flex items-center justify-between bg-white rounded p-2 border border-green-100 hover:border-green-300 transition-colors">
                          <div class="flex-1 text-xs text-gray-700">
                            <span class="font-semibold text-gray-900" x-text="item.nokendaraan"></span>
                            <span class="text-gray-500"> - </span>
                            <span x-text="item.operatorName"></span>
                            <template x-if="item.helperName">
                              <span class="text-gray-500"> + <span x-text="item.helperName"></span></span>
                            </template>
                          </div>
                          <button
                            type="button"
                            @click="removeKendaraan(activityCode, urutan)"
                            class="text-red-500 hover:text-red-700 hover:bg-red-50 rounded p-1 transition-colors ml-2 flex-shrink-0"
                          >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                          </button>

                          <!-- Hidden inputs -->
                          <input type="hidden" :name="`kendaraan[${activityCode}][${urutan}][nokendaraan]`" x-model="item.nokendaraan">
                          <input type="hidden" :name="`kendaraan[${activityCode}][${urutan}][operatorid]`" x-model="item.operatorid">
                          <input type="hidden" :name="`kendaraan[${activityCode}][${urutan}][helperid]`" x-model="item.helperid">
                          <input type="hidden" :name="`kendaraan[${activityCode}][${urutan}][usinghelper]`" :value="item.helperid ? 1 : 0">
                        </div>
                      </template>
                    </div>
                  </div>
                </template>
              </div>
            </div>

            <!-- Kendaraan Modal Component -->
            @include('transaction.rencanakerjaharian.modal-form.form-modal-kendaraan')
          </div>

        </div>
      </div>

      <!-- TABLE -->
      <div class="bg-white rounded-xl border border-gray-300 shadow-md" x-data="rowManager()">
        <!-- Add Row Button (Top) -->
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <span class="text-sm font-semibold text-gray-700">Detail Aktivitas RKH</span>
          </div>
          <button
            type="button"
            @click="addRow()"
            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors shadow-sm"
          >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Baris
          </button>
        </div>

        <div class="overflow-x-auto">
          <table id="rkh-table" class="table-fixed w-full border-collapse bg-white rounded-lg overflow-hidden shadow-sm">
            <colgroup>
              <col style="width: 40px">
              <col style="width: 180px">
              <col style="width: 60px">
              <col style="width: 80px">
              <col style="width: 80px">
              <col style="width: 80px">
              <col style="width: 120px">
              <col style="width: 50px">
            </colgroup>

            <thead class="bg-gradient-to-r from-gray-800 to-gray-700 text-white">
              <tr>
                <th class="py-3 px-2 text-xs font-semibold">No.</th>
                <th class="py-3 px-2 text-xs font-semibold">Aktivitas</th>
                <th class="py-3 px-2 text-xs font-semibold">Blok</th>
                <th class="py-3 px-2 text-xs font-semibold">Plot</th>
                <th class="py-3 px-2 text-xs font-semibold">Info Plot</th>
                <th class="py-3 px-2 text-xs font-semibold">Luas<br>(ha)</th>
                <th class="py-3 px-2 text-xs font-semibold">Material</th>
                <th class="py-3 px-2 text-xs font-semibold">Aksi</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-100" id="rkh-tbody">
              <!-- Initial Row (Template akan di-clone) -->
              <template x-for="(row, index) in rows" :key="row.id">
                <tr x-data="activityPicker(index)"
                    class="rkh-row hover:bg-blue-50 transition-colors"
                    :data-row-id="row.id"
                    x-init="rowIndex = index">

                  <td class="px-2 py-3 text-sm text-center font-medium text-gray-600 bg-gray-50" x-text="index + 1"></td>

                  <!-- Activity -->
                  <td class="px-2 py-3">
                    <div class="relative">
                      <input
                        type="text"
                        readonly
                        @click="open = true"
                        :value="selected.activitycode && selected.activityname ? `${selected.activitycode} – ${selected.activityname}` : ''"
                        class="w-full text-xs border-2 border-gray-200 rounded-lg px-3 py-2 text-center cursor-pointer bg-white hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="selected.activitycode ? 'bg-blue-50 text-blue-900 font-medium' : 'bg-gray-50 text-gray-500'"
                      >
                      <div class="absolute right-2 top-1/2 transform -translate-y-1/2 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                      </div>
                    </div>
                    <input type="hidden" :name="`rows[${index}][nama]`" x-model="selected.activitycode">
                    @include('transaction.rencanakerjaharian.modal-form.form-modal-activity')
                  </td>

                  <!-- Blok -->
                  <td class="px-2 py-3">
                    <div x-data="blokPicker(index)" class="relative" x-init="init(); rowIndex = index">
                      <input
                        type="text"
                        readonly
                        @click="currentActivityCode ? (open = true) : null"
                        :value="selected.blok ? (selected.blok === 'ALL' ? 'Semua Blok' : selected.blok) : ''"
                        :class="{
                          'cursor-pointer bg-white hover:bg-gray-50': currentActivityCode,
                          'cursor-not-allowed bg-gray-100': !currentActivityCode,
                          'border-gray-200': currentActivityCode,
                          'border-gray-300': !currentActivityCode
                        }"
                        class="w-full text-xs border-2 rounded-lg px-3 py-2 text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                      >
                      <input type="hidden" :name="`rows[${index}][blok]`" x-model="selected.blok">
                      @include('transaction.rencanakerjaharian.modal-form.form-modal-blok')
                    </div>
                  </td>

                  <!-- Plot -->
                  <td class="px-2 py-3">
                    <div x-data="plotPicker(index)" class="relative" x-init="init(); rowIndex = index">
                      <input
                        type="text"
                        readonly
                        @click="isBlokSelected && !isBlokActivity ? (open = true) : null"
                        :value="selected.plot ? selected.plot : ''"
                        :class="{
                          'cursor-pointer bg-white hover:bg-gray-50': isBlokSelected && !isBlokActivity,
                          'cursor-not-allowed bg-gray-100': !isBlokSelected || isBlokActivity,
                          'border-gray-200': isBlokSelected && !isBlokActivity,
                          'border-gray-300': !isBlokSelected || isBlokActivity
                        }"
                        class="w-full text-xs border-2 rounded-lg px-3 py-2 text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                      >
                      <input type="hidden" :name="`rows[${index}][plot]`" x-model="selected.plot">
                      @include('transaction.rencanakerjaharian.modal-form.form-modal-plot')
                    </div>
                  </td>

                  <!-- Info Plot -->
                  <td class="px-2 py-3" x-data="plotInfoPicker(index)" x-init="init(); rowIndex = index">
                      <div x-show="!currentActivityCode || !currentPlot" class="text-center text-xs text-gray-400">-</div>

                      <div x-show="currentActivityCode && currentPlot" x-cloak class="text-xs space-y-1">
                          <div x-show="isLoading" class="text-center py-2">
                              <svg class="animate-spin h-5 w-5 mx-auto text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                              </svg>
                          </div>

                          <div x-show="!isLoading && plotInfo.luasplot">
                              <!-- Batch info (kalau panen) -->
                              <div x-show="plotInfo.batchno" class="mb-1">
                                  <span class="px-2 py-0.5 rounded text-[10px] font-semibold"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800': plotInfo.kodestatus === 'PC',
                                            'bg-green-100 text-green-800': plotInfo.kodestatus === 'RC1',
                                            'bg-blue-100 text-blue-800': plotInfo.kodestatus === 'RC2',
                                            'bg-purple-100 text-purple-800': plotInfo.kodestatus === 'RC3'
                                        }"
                                        x-text="plotInfo.kodestatus"></span>
                              </div>

                              <!-- Luas plot -->
                              <div class="text-gray-700">
                                  <span class="font-semibold">Luas Plot:</span>
                                  <span x-text="plotInfo.luasplot + ' Ha'"></span>
                              </div>

                              <!-- Luas sisa -->
                              <div class="text-gray-700">
                                  <span class="font-semibold">Luas Sisa:</span>
                                  <span x-text="plotInfo.luassisa + ' Ha'"></span>
                              </div>

                              <!-- Tanggal (panen atau activity terakhir) -->
                              <div class="text-gray-700">
                                  <span class="font-semibold" x-text="plotInfo.batchno ? 'Tgl Panen:' : 'Tgl Activity:'"></span>
                                  <span x-text="plotInfo.tanggal || '-'"></span>
                              </div>

                              <!-- Tanggal ZPK (kalau ada) -->
                              <div x-show="plotInfo.zpk_date" class="text-gray-700">
                                  <span class="font-semibold">ZPK:</span>
                                  <span x-text="plotInfo.zpk_date"></span>
                                  <span class="text-xs ml-1" 
                                        :class="{
                                            'text-green-600': plotInfo.zpk_status === 'ideal',
                                            'text-red-600': plotInfo.zpk_status === 'too_early',
                                            'text-red-600': plotInfo.zpk_status === 'too_late'
                                        }"
                                        x-text="`(${plotInfo.zpk_days_gap}d)`"></span>
                              </div>
                          </div>
                      </div>

                      <input type="hidden" :name="`rows[${index}][batchno]`" x-model="plotInfo.batchno">
                      <input type="hidden" :name="`rows[${index}][kodestatus]`" x-model="plotInfo.kodestatus">
                  </td>

                  <!-- Luas -->
                  <td class="px-2 py-3">
                    <input
                      type="number"
                      :name="`rows[${index}][luas]`"
                      min="0"
                      step="0.01"
                      :max="getMaxLuas(index)"
                      :readonly="!isPlotOrBlokSelected(index)"
                      @input="validateLuasInput($event, index)"
                      :class="{
                        'cursor-not-allowed bg-gray-100': !isPlotOrBlokSelected(index),
                        'bg-white': isPlotOrBlokSelected(index)
                      }"
                      class="w-full text-xs border-2 border-gray-200 rounded-lg px-3 py-2 text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                  </td>

                  <!-- Material -->
                  <td class="px-2 py-3" x-data="materialPicker(index)" x-init="init(); rowIndex = index">
                    <div class="relative">
                      <div
                        @click="checkMaterial()"
                        :class="{
                          'cursor-pointer bg-white hover:bg-gray-50': hasMaterial,
                          'cursor-not-allowed bg-gray-100': !hasMaterial,
                          'border-green-500 bg-green-50': hasMaterial && selectedGroup,
                          'border-green-300': hasMaterial && !selectedGroup,
                          'border-gray-300': !hasMaterial
                        }"
                        class="w-full text-xs border-2 rounded-lg px-2 py-2 text-center transition-colors min-h-[36px] flex items-center justify-center"
                      >
                        <div x-show="!currentActivityCode" x-cloak class="text-gray-500 text-xs">-</div>
                        <div x-show="currentActivityCode && !hasMaterial" x-cloak class="text-xs font-medium">Tidak</div>
                        <div x-show="hasMaterial && !selectedGroup" x-cloak class="text-green-600 text-xs font-medium">Pilih</div>
                        <div x-show="hasMaterial && selectedGroup" class="text-green-800 text-xs font-semibold" x-text="selectedGroup?.herbisidagroupname"></div>
                      </div>
                    </div>
                    
                    <!-- Hidden inputs - PERBAIKAN DI SINI -->
                    <input type="hidden" 
                          :name="`rows[${index}][material_group_id]`" 
                          :value="selectedGroup?.herbisidagroupid || ''">
                    <input type="hidden" 
                          :name="`rows[${index}][usingmaterial]`" 
                          :value="(hasMaterial && selectedGroup) ? '1' : '0'">
                    
                    @include('transaction.rencanakerjaharian.modal-form.form-modal-material')
                  </td>

                  <!-- Delete Button -->
                  <td class="px-2 py-3 text-center">
                    <button
                      type="button"
                      @click="$dispatch('delete-row', { rowId: row.id, index: index })"
                      class="text-red-500 hover:text-red-700 hover:bg-red-50 rounded p-1.5 transition-colors"
                      title="Hapus baris"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </td>
                </tr>
              </template>

              <!-- Empty State -->
              <template x-if="rows.length === 0">
                <tr>
                  <td colspan="8" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-400">
                      <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                      </svg>
                      <p class="text-sm font-medium">Belum ada baris aktivitas</p>
                      <p class="text-xs text-gray-500 mt-1">Klik tombol "Tambah Baris" untuk menambahkan aktivitas</p>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <!-- Footer Info -->
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
          <div class="flex items-center justify-between text-xs text-gray-600">
            <span x-text="`Total: ${rows.length} baris aktivitas`"></span>
            <span class="text-gray-500">Klik ikon 🗑️ untuk menghapus baris</span>
          </div>
        </div>
      </div>

      <!-- BUTTONS -->
      <div class="mt-6 flex flex-col items-center space-y-3">
        <div class="flex justify-center space-x-3">
          <button type="button" onclick="window.location.href = '{{ route('transaction.rencanakerjaharian.index') }}';"
                  class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 px-6 py-2.5 rounded-lg text-sm font-medium transition-colors hover:bg-gray-50 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
          </button>
        </div>

        <button type="submit" id="submit-btn"
                class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-12 py-4 rounded-lg text-sm font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="submit-icon">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <svg class="animate-spin w-5 h-5 mr-2 hidden" fill="none" viewBox="0 0 24 24" id="loading-spinner">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span id="submit-text">Submit RKH</span>
        </button>
      </div>
    </div>
  </form>

<script>
// ============================================================
// ROW MANAGER - FIXED DELETE WITH PROPER REINDEXING
// ============================================================
function rowManager() {
  return {
    rows: [],
    nextId: 1,

    init() {
      this.addRow();
      this.$el.addEventListener('delete-row', (e) => {
        this.deleteRow(e.detail.rowId, e.detail.index);
      });
    },

    addRow() {
      const newRow = {
        id: this.nextId++,
        activitycode: '',
        blok: '',
        plot: '',
        luas: '',
      };
      
      this.rows.push(newRow);
      
      this.$nextTick(() => {
        initializeRowValidation();
        showToast('Baris baru ditambahkan', 'success', 2000);
      });
    },

    deleteRow(rowId, index) {
      if (this.rows.length === 1) {
        showToast('Minimal harus ada 1 baris', 'warning', 2000);
        return;
      }

      // ✅ 1. Cleanup activity dari cards
      const activityStore = Alpine.store('activityPerRow');
      const activity = activityStore.getActivity(index);
      
      if (activity && activity.activitycode) {
        // Remove from worker card
        const workerCardElement = document.querySelector('[x-data*="workerInfoCard"]');
        if (workerCardElement && workerCardElement._x_dataStack && workerCardElement._x_dataStack[0]) {
          const workerCard = workerCardElement._x_dataStack[0];
          if (workerCard.workers[activity.activitycode]) {
            delete workerCard.workers[activity.activitycode];
          }
        }

        // Remove from kendaraan card
        const kendaraanCardElement = document.querySelector('[x-data*="kendaraanInfoCard"]');
        if (kendaraanCardElement && kendaraanCardElement._x_dataStack && kendaraanCardElement._x_dataStack[0]) {
          const kendaraanCard = kendaraanCardElement._x_dataStack[0];
          if (kendaraanCard.kendaraan[activity.activitycode]) {
            delete kendaraanCard.kendaraan[activity.activitycode];
          }
        }
      }

      // ✅ 2. Remove row from array
      this.rows.splice(index, 1);

      // ✅ 3. Update all components dan stores
      this.$nextTick(() => {
        this.updateAllRowIndexes(index);
        showToast('Baris berhasil dihapus', 'success', 2000);
      });
    },

    // ✅ NEW: Update rowIndex di semua Alpine components
    updateAllRowIndexes(deletedIndex) {
  const allRows = document.querySelectorAll('#rkh-tbody tr.rkh-row');
  
  allRows.forEach((rowElement, newIndex) => {
    // Update activityPicker
    const activityComp = rowElement.querySelector('[x-data*="activityPicker"]');
    if (activityComp && activityComp._x_dataStack && activityComp._x_dataStack[0]) {
      activityComp._x_dataStack[0].rowIndex = newIndex;
    }

    // Update blokPicker
    const blokComp = rowElement.querySelector('[x-data*="blokPicker"]');
    if (blokComp && blokComp._x_dataStack && blokComp._x_dataStack[0]) {
      blokComp._x_dataStack[0].rowIndex = newIndex;
    }

    // Update plotPicker
    const plotComp = rowElement.querySelector('[x-data*="plotPicker"]');
    if (plotComp && plotComp._x_dataStack && plotComp._x_dataStack[0]) {
      plotComp._x_dataStack[0].rowIndex = newIndex;
    }

    // Update plotInfoPicker
    const plotInfoComp = rowElement.querySelector('[x-data*="plotInfoPicker"]');
    if (plotInfoComp && plotInfoComp._x_dataStack && plotInfoComp._x_dataStack[0]) {
      plotInfoComp._x_dataStack[0].rowIndex = newIndex;
    }

    // Update materialPicker
    const materialComp = rowElement.querySelector('[x-data*="materialPicker"]');
    if (materialComp && materialComp._x_dataStack && materialComp._x_dataStack[0]) {
      materialComp._x_dataStack[0].rowIndex = newIndex;
    }

    // ✅ FIX: Cleanup old validation listeners
    const blokInput = rowElement.querySelector('input[name$="[blok]"]');
    const plotInput = rowElement.querySelector('input[name$="[plot]"]');
    const activityInput = rowElement.querySelector('input[name$="[nama]"]');

    [blokInput, plotInput, activityInput].forEach(input => {
      if (input && input.uniqueValidationObserver) {
        input.uniqueValidationObserver.disconnect();
        delete input.uniqueValidationObserver;
      }
    });
  });

  // ✅ Reindex stores dengan mapping yang benar
  this.reindexStoresProper(deletedIndex);
  
  // ✅ Re-attach validation listeners dengan rowIndex baru
  this.$nextTick(() => {
    initializeRowValidation();
  });
},

    // ✅ FIXED: Reindex stores dengan proper mapping
    reindexStoresProper(deletedIndex) {
      // 1. Reindex blokPerRow
      const blokStore = Alpine.store('blokPerRow');
      const newBlokSelected = {};
      
      Object.keys(blokStore.selected).forEach(oldIndex => {
        const oldIdx = parseInt(oldIndex);
        
        if (oldIdx < deletedIndex) {
          // Row sebelum deleted: index tetap
          newBlokSelected[oldIdx] = blokStore.selected[oldIndex];
        } else if (oldIdx > deletedIndex) {
          // Row setelah deleted: index turun 1
          newBlokSelected[oldIdx - 1] = blokStore.selected[oldIndex];
        }
        // oldIdx === deletedIndex: skip (sudah dihapus)
      });
      
      blokStore.selected = newBlokSelected;

      // 2. Reindex activityPerRow
      const activityStore = Alpine.store('activityPerRow');
      const newActivitySelected = {};
      
      Object.keys(activityStore.selected).forEach(oldIndex => {
        const oldIdx = parseInt(oldIndex);
        
        if (oldIdx < deletedIndex) {
          newActivitySelected[oldIdx] = activityStore.selected[oldIndex];
        } else if (oldIdx > deletedIndex) {
          newActivitySelected[oldIdx - 1] = activityStore.selected[oldIndex];
        }
      });
      
      activityStore.selected = newActivitySelected;

      // 3. Reindex uniqueCombinations
      const uniqueStore = Alpine.store('uniqueCombinations');
      const newCombinations = new Map();
      
      for (const [oldIndex, combo] of uniqueStore.combinations) {
        if (oldIndex < deletedIndex) {
          newCombinations.set(oldIndex, combo);
        } else if (oldIndex > deletedIndex) {
          newCombinations.set(oldIndex - 1, combo);
        }
      }
      
      uniqueStore.combinations = newCombinations;
    }
  };
}

// ============================================================
// GLOBAL DATA INITIALIZATION
// ============================================================
window.bloksData = @json($bloks ?? []);
window.masterlistData = @json($masterlist ?? []);
window.herbisidaData = @json($herbisidagroups ?? []);
window.absenData = @json($absentenagakerja ?? []);
window.activitiesData = @json($activities ?? []);
window.vehiclesData = @json($vehiclesData ?? []);
window.helpersData = @json($helpersData ?? []);

window.rkhDate = '{{ $selectedDate }}';

window.currentUser = {
  userid: '{{ Auth::user()->userid ?? '' }}',
  name: '{{ Auth::user()->name ?? '' }}',
  idjabatan: {{ Auth::user()->idjabatan ?? 'null' }}
};

window.PANEN_ACTIVITIES = ['4.3.3', '4.4.3', '4.5.2', '2.2.2a', '2.2.2b'];
window.PLOT_INFO_BASE_URL = "{{ url('transaction/kerjaharian/rencanakerjaharian/plot-info') }}";

// ============================================================
// ALPINE.JS COMPONENTS
// ============================================================

/**
 * Kendaraan Info Card Component
 * Now stores: nokendaraan (primary), operator_id, helper_id
 */
function kendaraanInfoCard() {
  return {
    kendaraan: {},
    currentActivityCode: null,

    init() {
      this.$watch('Alpine.store("activityPerRow").selected', (activities) => {
        this.syncKendaraanFromActivities(activities);
      }, { deep: true });
    },

    syncKendaraanFromActivities(activities) {
      const currentActivityCodes = Object.values(activities)
        .filter(act => act && act.activitycode && act.usingvehicle === 1)
        .map(act => act.activitycode);
      
      Object.keys(this.kendaraan).forEach(activityCode => {
        if (!currentActivityCodes.includes(activityCode)) {
          delete this.kendaraan[activityCode];
        }
      });
    },

    getActivityFullName(activityCode) {
      const activity = window.activitiesData?.find(a => a.activitycode === activityCode);
      return activity ? `${activityCode} - ${activity.activityname}` : activityCode;
    },

    openKendaraanModal() {
      const activities = Alpine.store('activityPerRow').selected;
      const activityCodes = Object.values(activities)
        .filter(act => act && act.activitycode && act.usingvehicle === 1)
        .map(act => act.activitycode);

      if (activityCodes.length === 0) {
        showToast('Pilih aktivitas yang menggunakan kendaraan terlebih dahulu', 'warning', 3000);
        return;
      }

      this.currentActivityCode = activityCodes[0];

      window.dispatchEvent(new CustomEvent('open-kendaraan-modal', {
        detail: { activityCodes: activityCodes }
      }));
    },

    // ✅ UPDATED: vehicleData now contains nokendaraan + operator info
    addKendaraan(activityCode, vehicleData, helperData = null) {
      // Check for duplicate VEHICLE (not operator)
      if (this.kendaraan[activityCode]) {
        const isDuplicate = Object.values(this.kendaraan[activityCode]).some(
          item => item.nokendaraan === vehicleData.nokendaraan
        );
        
        if (isDuplicate) {
          showToast(`Kendaraan ${vehicleData.nokendaraan} sudah ditambahkan untuk aktivitas ini`, 'warning', 3000);
          return false;
        }
      }

      if (!this.kendaraan[activityCode]) {
        this.kendaraan[activityCode] = {};
      }

      const urutan = Object.keys(this.kendaraan[activityCode]).length + 1;

      this.kendaraan[activityCode][urutan] = {
        nokendaraan: vehicleData.nokendaraan,
        operatorid: vehicleData.operator_id || null,
        operatorName: vehicleData.operator_name || 'No Operator',
        helperid: helperData ? helperData.tenagakerjaid : null,
        helperName: helperData ? helperData.nama : null
      };

      console.log('✅ Added kendaraan:', this.kendaraan[activityCode][urutan]);
      return true;
    },

    removeKendaraan(activityCode, urutan) {
      if (this.kendaraan[activityCode] && this.kendaraan[activityCode][urutan]) {
        delete this.kendaraan[activityCode][urutan];
        
        if (Object.keys(this.kendaraan[activityCode]).length === 0) {
          delete this.kendaraan[activityCode];
        }

        this.reindexKendaraan(activityCode);
      }
    },

    reindexKendaraan(activityCode) {
      if (!this.kendaraan[activityCode]) return;

      const items = Object.values(this.kendaraan[activityCode]);
      this.kendaraan[activityCode] = {};
      
      items.forEach((item, index) => {
        this.kendaraan[activityCode][index + 1] = item;
      });
    },

    getTotalKendaraan() {
      let total = 0;
      Object.values(this.kendaraan).forEach(activityGroup => {
        total += Object.keys(activityGroup).length;
      });
      return total;
    }
  };
}

/**
 * Plot Info Picker Component (Updated - untuk semua activity)
 */
function plotInfoPicker(rowIndex) {
  return {
    rowIndex: rowIndex,
    currentPlot: '',
    currentActivityCode: '',
    isLoading: false,
    plotInfo: {
      luasplot: 0,
      luassisa: 0,
      batchno: '',
      kodestatus: '',
      tanggal: '',
      luassisa_batch: '',
      zpk_date: '',
      zpk_days_gap: 0,
      zpk_status: ''
    },

    init() {
      this.watchPlotChanges();
      this.watchActivityChanges();
    },

    watchPlotChanges() {
      const plotInput = document.querySelector(`input[name="rows[${this.rowIndex}][plot]"]`);
      if (!plotInput) return;

      const observer = new MutationObserver(() => {
        const newPlot = plotInput.value || '';
        if (this.currentPlot !== newPlot) {
          this.currentPlot = newPlot;
          if (this.currentActivityCode && this.currentPlot) {
            this.updatePlotInfo();
          }
        }
      });

      observer.observe(plotInput, { attributes: true, attributeFilter: ['value'] });
      this.currentPlot = plotInput.value || '';
    },

    watchActivityChanges() {
      const activityInput = document.querySelector(`input[name="rows[${this.rowIndex}][nama]"]`);
      if (!activityInput) return;

      const observer = new MutationObserver(() => {
        const newActivity = activityInput.value || '';
        if (this.currentActivityCode !== newActivity) {
          this.currentActivityCode = newActivity;

          if (this.currentActivityCode && this.currentPlot) {
            this.updatePlotInfo();
          } else if (!this.currentActivityCode) {
            this.resetPlotInfo();
          }
        }
      });

      observer.observe(activityInput, { attributes: true, attributeFilter: ['value'] });
      this.currentActivityCode = activityInput.value || '';
    },

    async updatePlotInfo() {
      if (!this.currentActivityCode || !this.currentPlot) {
        this.resetPlotInfo();
        return;
      }

      this.isLoading = true;

      try {
        const url = `${window.PLOT_INFO_BASE_URL}/${this.currentPlot}/${this.currentActivityCode}`;
        const response = await fetch(url);
        const data = await response.json();

        if (data.success) {
          this.plotInfo = {
            luasplot: parseFloat(data.luasplot).toFixed(2),
            luassisa: parseFloat(data.luassisa).toFixed(2),
            batchno: data.batchinfo?.batchno || '',
            kodestatus: data.batchinfo?.lifecyclestatus || '',
            tanggal: data.tanggal || '',
            luassisa_batch: data.batchinfo?.luassisa_batch || '',
            zpk_date: data.batchinfo?.zpk_date || '',
            zpk_days_gap: data.batchinfo?.zpk_days_gap || 0,
            zpk_status: data.batchinfo?.zpk_status || ''
          };
          
          // Auto-fill luas sisa
          updateLuasFromPlotInfo(this.plotInfo.luassisa, this.rowIndex);
        } else {
          this.resetPlotInfo();
          showToast(data.message || 'Gagal memuat info plot', 'warning', 3000);
        }
      } catch (error) {
        console.error('Error fetching plot info:', error);
        this.resetPlotInfo();
        showToast('Error memuat info plot', 'error', 3000);
      } finally {
        this.isLoading = false;
      }
    },

    resetPlotInfo() {
      this.isLoading = false;
      this.plotInfo = {
        luasplot: 0,
        luassisa: 0,
        batchno: '',
        kodestatus: '',
        tanggalpanen: '',
        luassisa_batch: ''
      };
    }
  };
}

/**
 * Worker Info Card Component
 */
function workerInfoCard() {
  return {
    workers: {},

    init() {
      this.$watch('Alpine.store("activityPerRow").selected', (activities) => {
        this.syncWorkersFromActivities(activities);
      }, { deep: true });

      this.syncWorkersFromActivities(Alpine.store('activityPerRow').selected);
    },

    syncWorkersFromActivities(activities) {
      const currentActivityCodes = Object.values(activities)
        .filter(act => act && act.activitycode)
        .map(act => act.activitycode);
      
      Object.keys(this.workers).forEach(activityCode => {
        if (!currentActivityCodes.includes(activityCode)) {
          delete this.workers[activityCode];
        }
      });
      
      Object.values(activities).forEach(activity => {
        if (activity && activity.activitycode && !this.workers[activity.activitycode]) {
          const fullActivity = window.activitiesData?.find(a => a.activitycode === activity.activitycode);
          const jenisData = fullActivity?.jenistenagakerja;
          
          this.workers[activity.activitycode] = {
            activityname: activity.activityname || '',
            jenisId: typeof jenisData === 'object' ? jenisData?.idjenistenagakerja : jenisData,
            jenisLabel: fullActivity?.jenistenagakerja_nama || '-',
            laki: '',
            perempuan: '',
            total: ''
          };
        }
      });
    },

    updateWorkerTotal(activityCode) {
      if (!this.workers[activityCode]) return;

      const laki = parseInt(this.workers[activityCode].laki) || 0;
      const perempuan = parseInt(this.workers[activityCode].perempuan) || 0;

      if (this.workers[activityCode].laki !== '' || this.workers[activityCode].perempuan !== '') {
        this.workers[activityCode].total = laki + perempuan;
      } else {
        this.workers[activityCode].total = '';
      }
    }
  };
}

/**
 * Validation Error Modal Component
 */
function validationErrorModal() {
  return {
    showValidationModal: false,
    validationErrors: [],

    init() {
      // Listen for validation error events
      window.addEventListener('validation-error', (event) => {
        this.validationErrors = event.detail.errors || [];
        this.showValidationModal = true;
      });
    },

    closeModal() {
      this.showValidationModal = false;
      this.validationErrors = [];
    }
  };
}

// ============================================================
// ALPINE STORES INITIALIZATION
// ============================================================
document.addEventListener('alpine:init', () => {
  Alpine.store('modal', {
    showModal: false,
    modalType: '',
    modalMessage: '',
    modalErrors: []
  });

  Alpine.store('blokPerRow', {
    selected: {},
    setBlok(rowIndex, blok) { this.selected[rowIndex] = blok; },
    getBlok(rowIndex) { return this.selected[rowIndex] || ''; },
    hasBlok(rowIndex) { return !!this.selected[rowIndex]; }
  });

  Alpine.store('activityPerRow', {
    selected: {},
    setActivity(rowIndex, activity) { this.selected[rowIndex] = activity; },
    getActivity(rowIndex) { return this.selected[rowIndex] || null; }
  });

  Alpine.store('uniqueCombinations', {
    combinations: new Map(),

    setCombination(rowIndex, blok, plot, activity) {
      if (blok && plot && activity) {
        this.combinations.set(rowIndex, { blok, plot, activity });
      } else {
        this.combinations.delete(rowIndex);
      }
    },

    isDuplicate(currentRowIndex, blok, plot, activity) {
      if (!blok || !plot || !activity) return false;

      for (const [rowIndex, combo] of this.combinations) {
        if (rowIndex !== currentRowIndex &&
            combo.blok === blok &&
            combo.plot === plot &&
            combo.activity === activity) {
          return true;
        }
      }
      return false;
    },

    getAllDuplicates() {
      const duplicates = new Map();
      const combinations = {};

      for (const [rowIndex, combo] of this.combinations) {
        const key = `${combo.blok}|${combo.plot}|${combo.activity}`;
        if (!combinations[key]) combinations[key] = [];
        combinations[key].push(rowIndex);
      }

      for (const [key, rowIndexes] of Object.entries(combinations)) {
        if (rowIndexes.length > 1) {
          const [blok, plot, activity] = key.split('|');
          duplicates.set(key, { blok, plot, activity, rows: rowIndexes });
        }
      }

      return duplicates;
    },

    clear() {
      this.combinations.clear();
    }
  });
});

// ============================================================
// FORM HANDLER
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
  initializeRowValidation();
  initializeValidationStyles();
  initializeFormSubmit();
});

function initializeRowValidation() {
  const rows = document.querySelectorAll('#rkh-table tbody tr.rkh-row');
  rows.forEach((row, index) => {
    attachUniqueValidationListeners(row, index);
  });
}

function initializeFormSubmit() {
  const form = document.getElementById('rkh-form');
  if (!form) return;

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    clearValidationErrors();

    const validationResult = validateFormWithWorkerCard();
    if (!validationResult.isValid) {
      showValidationModal(validationResult.errors);
      return;
    }

    submitForm(this);
  });
}

function submitForm(form) {
  const submitBtn = document.getElementById('submit-btn');
  
  // ✅ CRITICAL: Prevent double submission
  if (submitBtn.disabled) {
    console.warn('⚠️ Form already submitting, ignoring duplicate request');
    return false;
  }
  
  // ✅ Disable button IMMEDIATELY to prevent race condition
  submitBtn.disabled = true;
  submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
  
  showLoadingState();
  const formData = new FormData(form);

  console.log('=== FORM DATA DEBUG ===');
  for (let [key, value] of formData.entries()) {
    if (key.includes('rows')) {
      console.log(key, '=', value);
    }
  }
  console.log('======================');

  fetch(form.action, {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
  .then(response => {
    // ✅ Check if response is OK before parsing JSON
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      showModal('success', data.message);
      // ✅ Don't re-enable button on success (will redirect anyway)
      // submitBtn will stay disabled to prevent accidental re-submit
    } else {
      const errors = data.errors ? Object.values(data.errors).flat() : [];
      showModal('error', data.message || 'Terjadi kesalahan saat menyimpan data', errors);
      
      // ✅ Re-enable button only on validation/business logic errors
      submitBtn.disabled = false;
      submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
  })
  .catch(error => {
    console.error('❌ Submit Error:', error);
    showModal('error', 'Terjadi kesalahan sistem: ' + error.message);
    
    // ✅ Re-enable button on network/system errors so user can retry
    submitBtn.disabled = false;
    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
  })
  .finally(() => {
    hideLoadingState();
  });
  
  return false; // ✅ Prevent default form submission
}

// ============================================================
// VALIDATION FUNCTIONS
// ============================================================
function attachUniqueValidationListeners(row, rowIndex) {
  const blokInput = row.querySelector('input[name$="[blok]"]');
  const plotInput = row.querySelector('input[name$="[plot]"]');
  const activityInput = row.querySelector('input[name$="[nama]"]');

  const validateUniqueness = debounce(() => {
    // ✅ FIX: Baca rowIndex real-time dari DOM, jangan pakai closure
    const currentRow = blokInput.closest('tr.rkh-row');
    const allRows = Array.from(document.querySelectorAll('#rkh-tbody tr.rkh-row'));
    const currentRowIndex = allRows.indexOf(currentRow);
    
    if (currentRowIndex === -1) return; // Row sudah tidak ada di DOM

    const blok = blokInput?.value || '';
    const plot = plotInput?.value || '';
    const activity = activityInput?.value || '';

    Alpine.store('uniqueCombinations').setCombination(currentRowIndex, blok, plot, activity);
    clearDuplicateHighlight(currentRowIndex);

    if (blok && plot && activity) {
      const isDuplicate = Alpine.store('uniqueCombinations').isDuplicate(currentRowIndex, blok, plot, activity);
      if (isDuplicate) {
        highlightDuplicateRow(currentRowIndex);
        showToast('Kombinasi duplikat terdeteksi', 'warning', 3000);
      }
    }
    updateAllDuplicateHighlights();
  }, 300);

  [blokInput, plotInput, activityInput].forEach(input => {
    if (input) {
      // Cleanup old observer
      if (input.uniqueValidationObserver) {
        input.uniqueValidationObserver.disconnect();
        delete input.uniqueValidationObserver;
      }
      
      const observer = new MutationObserver(validateUniqueness);
      observer.observe(input, { attributes: true, attributeFilter: ['value'] });
      input.addEventListener('change', validateUniqueness);
      input.addEventListener('input', validateUniqueness);
      input.uniqueValidationObserver = observer;
    }
  });
}

function validateFormWithWorkerCard() {
  const errors = [];

  // Validate mandor
  if (!document.querySelector('input[name="mandor_id"]').value) {
    errors.push('Silakan pilih Mandor terlebih dahulu');
  }

  // Validate rows
  const rows = document.querySelectorAll('#rkh-table tbody tr.rkh-row');
  let hasCompleteRow = false;

  rows.forEach((row, index) => {
    const blok = row.querySelector('input[name$="[blok]"]').value;
    const plot = row.querySelector('input[name$="[plot]"]').value;
    const activity = row.querySelector('input[name$="[nama]"]').value;
    const luas = row.querySelector('input[name$="[luas]"]').value;

    if (blok) {
      hasCompleteRow = true;
      const rowNum = index + 1;

      // Check if this is a blok activity
      const isBlokActivity = Alpine.store('activityPerRow').getActivity(index)?.isblokactivity === 1;

      if (!activity) {
        errors.push(`Baris ${rowNum}: Aktivitas harus dipilih`);
      }

      // Conditional validation based on activity type
      if (!isBlokActivity) {
        // Normal activity - plot & luas WAJIB
        if (!plot) errors.push(`Baris ${rowNum}: Plot harus dipilih`);
        if (!luas) errors.push(`Baris ${rowNum}: Luas area harus diisi`);
      } else {
        // Blok activity - plot HARUS NULL, luas OPTIONAL
        if (plot) {
          errors.push(`Baris ${rowNum}: Blok activity tidak boleh memiliki plot spesifik`);
        }
        // Luas optional untuk blok activity (bisa kosong)
      }

      // Validate jenistenagakerja mapping
      if (activity) {
        const activityData = window.activitiesData.find(act => act.activitycode === activity);
        if (!activityData || activityData.jenistenagakerja === null) {
          errors.push(`Baris ${rowNum}: Activity "${activity}" belum di-mapping jenistenagakerja`);
        }
      }
    }
  });

  // Material validation (unchanged)
  rows.forEach((row, index) => {
    const blok = row.querySelector('input[name$="[blok]"]').value;
    const activity = row.querySelector('input[name$="[nama]"]').value;
    
    if (blok && activity) {
      const hasMaterialOptions = window.herbisidaData?.some(item => item.activitycode === activity);
      if (hasMaterialOptions) {
        const materialGroupInput = row.querySelector('input[name$="[material_group_id]"]');
        const materialValue = materialGroupInput?.value || '';
        
        if (!materialValue) {
          const materialPicker = row.querySelector('[x-data*="materialPicker"]');
          if (materialPicker && materialPicker._x_dataStack && materialPicker._x_dataStack[0]) {
            const selectedGroup = materialPicker._x_dataStack[0].selectedGroup;
            if (!selectedGroup || !selectedGroup.herbisidagroupid) {
              errors.push(`Baris ${index + 1}: Grup material harus dipilih`);
            }
          } else {
            errors.push(`Baris ${index + 1}: Grup material harus dipilih`);
          }
        }
      }
    }
  });

  // Validate workers (unchanged)
  const workerCardElement = document.querySelector('[x-data*="workerInfoCard"]');
  if (workerCardElement && workerCardElement._x_dataStack && workerCardElement._x_dataStack[0]) {
    const workers = workerCardElement._x_dataStack[0].workers;
    Object.keys(workers).forEach(activityCode => {
      const laki = workers[activityCode].laki;
      const perempuan = workers[activityCode].perempuan;

      if (laki === '' && perempuan === '') {
        errors.push(`Aktivitas "${activityCode}": Jumlah pekerja harus diisi (boleh 0, tapi tidak boleh kosong)`);
      }
    });
  }

  // Validate kendaraan (unchanged)
  const kendaraanCardElement = document.querySelector('[x-data*="kendaraanInfoCard"]');
  if (kendaraanCardElement && kendaraanCardElement._x_dataStack && kendaraanCardElement._x_dataStack[0]) {
    const kendaraan = kendaraanCardElement._x_dataStack[0].kendaraan;
    
    const activities = Alpine.store('activityPerRow').selected;
    Object.values(activities).forEach(activity => {
      if (activity && activity.activitycode && activity.usingvehicle === 1) {
        if (!kendaraan[activity.activitycode] || Object.keys(kendaraan[activity.activitycode]).length === 0) {
          errors.push(`Aktivitas "${activity.activitycode}": Wajib memilih minimal 1 kendaraan`);
        }
      }
    });
  }

  // Check for duplicates
  const duplicates = Alpine.store('uniqueCombinations').getAllDuplicates();
  if (duplicates.size > 0) {
    for (const [key, duplicateInfo] of duplicates) {
      errors.push(`Kombinasi duplikat di baris: ${duplicateInfo.rows.map(r => r + 1).join(', ')}`);
    }
  }

  if (!hasCompleteRow) errors.push('Minimal satu baris harus diisi');

  return { isValid: errors.length === 0, errors: errors };
}

function updateAllDuplicateHighlights() {
  const rows = document.querySelectorAll('#rkh-table tbody tr.rkh-row');
  rows.forEach((row, index) => clearDuplicateHighlight(index));

  const duplicates = Alpine.store('uniqueCombinations').getAllDuplicates();
  for (const [key, duplicateInfo] of duplicates) {
    duplicateInfo.rows.forEach(rowIndex => highlightDuplicateRow(rowIndex));
  }
}

function highlightDuplicateRow(rowIndex) {
  const row = document.querySelector(`#rkh-table tbody tr:nth-child(${rowIndex + 1})`);
  if (!row) return;

  const fields = [
    row.querySelector('input[name$="[blok]"]'),
    row.querySelector('input[name$="[plot]"]'),
    row.querySelector('input[name$="[nama]"]')
  ].filter(Boolean);

  fields.forEach(field => {
    field.classList.add('border-orange-400', 'bg-orange-50');
    field.classList.remove('border-gray-200');
  });
}

function clearDuplicateHighlight(rowIndex) {
  const row = document.querySelector(`#rkh-table tbody tr:nth-child(${rowIndex + 1})`);
  if (!row) return;

  const fields = [
    row.querySelector('input[name$="[blok]"]'),
    row.querySelector('input[name$="[plot]"]'),
    row.querySelector('input[name$="[nama]"]')
  ].filter(Boolean);

  fields.forEach(field => {
    field.classList.remove('border-orange-400', 'bg-orange-50');
    field.classList.add('border-gray-200');
  });
}

function clearValidationErrors() {
  document.querySelectorAll('.border-red-500').forEach(field => {
    field.classList.remove('border-red-500', 'bg-red-50');
    field.classList.add('border-gray-200');
  });
}

// ============================================================
// UI HELPER FUNCTIONS
// ============================================================
function showLoadingState() {
  const submitBtn = document.getElementById('submit-btn');
  submitBtn.disabled = true;
  document.getElementById('submit-text').textContent = 'Menyimpan...';
  document.getElementById('submit-icon').classList.add('hidden');
  document.getElementById('loading-spinner').classList.remove('hidden');
}

function hideLoadingState() {
  const submitBtn = document.getElementById('submit-btn');
  submitBtn.disabled = false;
  document.getElementById('submit-text').textContent = 'Submit RKH';
  document.getElementById('submit-icon').classList.remove('hidden');
  document.getElementById('loading-spinner').classList.add('hidden');
}

function showModal(type, message, errors = []) {
  if (type === 'success') {
    const modalElement = document.querySelector('[x-data*="showModal"]');
    if (modalElement && modalElement._x_dataStack && modalElement._x_dataStack[0]) {
      modalElement._x_dataStack[0].showModal = true;
      modalElement._x_dataStack[0].modalType = type;
      modalElement._x_dataStack[0].modalMessage = message;
      modalElement._x_dataStack[0].modalErrors = errors;
    }
  } else {
    showValidationModal([message, ...errors]);
  }
}

function showValidationModal(errors) {
  window.dispatchEvent(new CustomEvent('validation-error', { detail: { errors: errors } }));
}

function showToast(message, type = 'info', duration = 4000) {
  const existingToast = document.querySelector('.validation-toast');
  if (existingToast && existingToast.parentElement) {
    existingToast.parentElement.removeChild(existingToast);
  }

  const toast = document.createElement('div');
  toast.className = 'validation-toast fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transition-all duration-300 transform';

  const config = {
    warning: { bg: 'bg-orange-500', icon: '⚠️' },
    error: { bg: 'bg-red-500', icon: '❌' },
    success: { bg: 'bg-green-500', icon: '✅' },
    info: { bg: 'bg-blue-500', icon: 'ℹ️' }
  };

  const { bg, icon } = config[type] || config.info;
  toast.classList.add(bg, 'text-white');

  toast.innerHTML = `
    <div class="flex items-start">
      <span class="text-lg mr-3">${icon}</span>
      <div class="flex-1"><p class="text-sm font-medium">${message}</p></div>
      <button type="button" class="ml-4 text-white hover:text-gray-200"
              onclick="this.closest('.validation-toast')?.remove()">×</button>
    </div>
  `;

  if (!document.body) return;
  document.body.appendChild(toast);
  if (!toast) return;

  toast.style.transform = 'translateX(100%)';
  toast.style.opacity = '0';

  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      if (document.body.contains(toast)) {
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
      }
    });
  });

  if (duration > 0) {
    setTimeout(() => {
      if (!document.body.contains(toast)) return;
      toast.style.transform = 'translateX(100%)';
      toast.style.opacity = '0';
      setTimeout(() => {
        if (document.body.contains(toast)) toast.remove();
      }, 300);
    }, duration);
  }
}

function initializeValidationStyles() {
  const style = document.createElement('style');
  style.textContent = `
    .validation-toast { transform: translateX(100%); opacity: 0; }
    .border-orange-400 { border-color: #fb923c !important; }
    .bg-orange-50 { background-color: #fff7ed !important; }
  `;
  if (!document.head.querySelector('#validation-styles')) {
    style.id = 'validation-styles';
    document.head.appendChild(style);
  }
}

function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func(...args), wait);
  };
}

function cleanupValidationListeners() {
  document.querySelectorAll('input[uniqueValidationObserver]').forEach(input => {
    if (input.uniqueValidationObserver) {
      input.uniqueValidationObserver.disconnect();
      delete input.uniqueValidationObserver;
    }
  });
}

// ============================================================
// ABSEN SUMMARY UPDATE
// ============================================================
function updateAbsenSummary() {
  const mandorId = document.querySelector('input[name="mandor_id"]')?.value;
  
  if (!mandorId || !window.absenData) {
    document.getElementById('summary-laki').textContent = '0';
    document.getElementById('summary-perempuan').textContent = '0';
    document.getElementById('summary-total').textContent = '0';
    return;
  }

  const filteredAbsen = window.absenData.filter(absen => absen.mandorid === mandorId);
  let lakiCount = 0, perempuanCount = 0;

  filteredAbsen.forEach(absen => {
    if (absen.gender === 'L') lakiCount++;
    else if (absen.gender === 'P') perempuanCount++;
  });

  document.getElementById('summary-laki').textContent = lakiCount;
  document.getElementById('summary-perempuan').textContent = perempuanCount;
  document.getElementById('summary-total').textContent = lakiCount + perempuanCount;
}

// ✅ Call on page load
document.addEventListener('DOMContentLoaded', function() {
  updateAbsenSummary(); // Auto-update absen summary
  initializeRowValidation();
  initializeValidationStyles();
  initializeFormSubmit();
});

// ============================================================
// PLOT AUTO-UPDATE (Updated - dari AJAX)
// ============================================================
function updateLuasFromPlotInfo(luasSisa, rowIndex) {
  const luasInput = document.querySelector(`input[name="rows[${rowIndex}][luas]"]`);
  if (luasInput && luasSisa > 0) {
    luasInput.value = luasSisa;
    luasInput.setAttribute('max', luasSisa);
    showToast(`Luas sisa: ${luasSisa} Ha`, 'success', 2000);
  }
}

// ============================================================
// MATERIAL PICKER (SIMPLIFIED FOR DYNAMIC ROWS)
// ============================================================
function materialPicker(rowIndex) {
  return {
    open: false,
    rowIndex: rowIndex,
    currentActivityCode: '',
    selectedGroup: null,

    get hasMaterial() {
      return this.currentActivityCode && this.availableGroups.length > 0;
    },

    get availableGroups() {
      if (!this.currentActivityCode || !window.herbisidaData) return [];
      const groups = {};
      window.herbisidaData.forEach(item => {
        if (item.activitycode === this.currentActivityCode) {
          if (!groups[item.herbisidagroupid]) {
            groups[item.herbisidagroupid] = {
              herbisidagroupid: item.herbisidagroupid,
              herbisidagroupname: item.herbisidagroupname,
              showDetails: false,
              items: []
            };
          }
          groups[item.herbisidagroupid].items.push(item);
        }
      });
      return Object.values(groups);
    },

    checkMaterial() {
      if (this.hasMaterial) {
        this.open = true;
      } else {
        this.selectedGroup = null;
      }
    },

    selectGroup(group) {
      this.selectedGroup = {
        herbisidagroupid: group.herbisidagroupid,
        herbisidagroupname: group.herbisidagroupname
      };
      console.log('✅ Material group selected:', this.selectedGroup);
    },

    clearSelection() {
      this.selectedGroup = null;
    },

    confirmSelection() {
      console.log('✅ Material confirmed for row', this.rowIndex, ':', this.selectedGroup);
      this.open = false;
    },

    init() {
      this.$watch('rowIndex', (newIndex) => {
        const activityInput = document.querySelector(`input[name="rows[${newIndex}][nama]"]`);
        if (activityInput) {
          const observer = new MutationObserver(() => {
            const newActivity = activityInput.value || '';
            if (this.currentActivityCode !== newActivity) {
              this.currentActivityCode = newActivity;
              this.selectedGroup = null;
            }
          });
          
          observer.observe(activityInput, { attributes: true, attributeFilter: ['value'] });
          this.currentActivityCode = activityInput.value || '';
        }
      });
    }
  }
}


/**
 * Get max luas for specific row from plotInfo
 */
function getMaxLuas(rowIndex) {
    const plotInfoElement = document.querySelector(`[x-data*="plotInfoPicker"][x-init*="rowIndex = ${rowIndex}"]`);
    if (plotInfoElement && plotInfoElement._x_dataStack && plotInfoElement._x_dataStack[0]) {
        const luasSisa = plotInfoElement._x_dataStack[0].plotInfo.luassisa;
        return luasSisa || 999; // Default 999 kalau belum ada data
    }
    return 999;
}

/**
 * Validate luas input tidak melebihi luas sisa
 */
function validateLuasInput(event, rowIndex) {
    const input = event.target;
    const value = parseFloat(input.value) || 0;
    const maxLuas = parseFloat(input.getAttribute('max')) || 999;
    
    if (value > maxLuas) {
        input.value = maxLuas;
        input.classList.add('border-red-500', 'bg-red-50');
        showToast(`Luas tidak boleh melebihi ${maxLuas} Ha (luas sisa)`, 'warning', 3000);
        
        // Remove error styling after 2 seconds
        setTimeout(() => {
            input.classList.remove('border-red-500', 'bg-red-50');
            input.classList.add('border-gray-200');
        }, 2000);
    } else {
        input.classList.remove('border-red-500', 'bg-red-50');
        input.classList.add('border-gray-200');
    }
}

/**
 * Check if plot or blok (ALL) is selected untuk enable luas input
 */
function isPlotOrBlokSelected(rowIndex) {
  // Check if plot selected
  const plotInput = document.querySelector(`input[name="rows[${rowIndex}][plot]"]`);
  if (plotInput && plotInput.value) {
    return true;
  }
  
  // Check if blok = "ALL" (blok activity)
  const blokInput = document.querySelector(`input[name="rows[${rowIndex}][blok]"]`);
  if (blokInput && blokInput.value === 'ALL') {
    return true;
  }
  
  return false;
}

// ============================================================
// CLEANUP
// ============================================================
window.addEventListener('beforeunload', cleanupValidationListeners);
</script>

</x-layout>