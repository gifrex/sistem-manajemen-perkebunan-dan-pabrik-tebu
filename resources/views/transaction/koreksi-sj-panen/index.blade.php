{{-- resources/views/transaction/koreksi-sj-panen/index.blade.php --}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div x-data="koreksiSJData()">

  {{-- ===== WARNING BANNER ===== --}}
  <div class="mb-4 rounded-xl border-2 border-red-400 bg-red-50 shadow-sm overflow-hidden">
    {{-- Title bar --}}
    <div class="flex items-center gap-3 px-4 py-3 bg-red-600">
      <svg class="w-5 h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
      </svg>
      <p class="text-white font-bold text-sm tracking-wide">PERINGATAN — MODUL SENSITIF: KOREKSI DATA SURAT JALAN PANEN</p>
    </div>
    {{-- Body --}}
    <div class="px-5 py-4 space-y-3 text-sm text-red-900">

      <p class="font-semibold">
        Modul ini mengubah data Surat Jalan Panen yang sudah terjadi secara historis.
        Setiap perubahan memiliki risiko ketidakcocokan data yang semakin besar seiring bertambahnya jarak waktu antara tanggal SJ terbentuk dan tanggal koreksi dilakukan.
      </p>

      <div>
        <p class="font-semibold mb-1.5">Jika SJ yang dikoreksi sudah digunakan sebagai dasar perhitungan berikut, maka data tersebut <span class="underline underline-offset-2">wajib diperiksa dan di-generate ulang</span> setelah koreksi disetujui:</p>
        <ul class="list-disc list-inside space-y-0.5 text-red-800 pl-1">
          <li><span class="font-medium">Biaya Kontraktor &amp; Sub-Kontraktor</span> — berubah jika koreksi menyentuh field kontraktor, sub-kontraktor, atau kendaraan kontraktor.</li>
          <li><span class="font-medium">Saldo Panen / Rekap Panen</span> — berubah jika koreksi menyentuh plot, varietas, atau kode tebang.</li>
          <li><span class="font-medium">Laporan Premi &amp; Target Kontraktor</span> — berubah jika field kontraktor atau langsir berubah.</li>
          <li><span class="font-medium">Data terkait lainnya</span> yang sudah tercetak atau terkirim sebelum koreksi disetujui.</li>
        </ul>
      </div>

      <div class="flex items-start gap-2 bg-red-100 border border-red-300 rounded-lg px-4 py-3">
        <svg class="w-4 h-4 text-red-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
        </svg>
        <p class="text-red-800 text-xs leading-relaxed">
          <span class="font-bold">Jika setelah koreksi disetujui ditemukan ketidakcocokan data</span> — misalnya total biaya kontraktor berubah tidak sesuai ekspektasi, rekap panen tidak cocok, atau laporan menunjukkan anomali —
          <span class="font-bold">segera hubungi tim IT</span> untuk investigasi lebih lanjut sebelum data tersebut digunakan dalam laporan resmi.
        </p>
      </div>

    </div>
  </div>

  <div class="mx-auto py-1 bg-white rounded-md shadow-md">

    {{-- ===== HEADER ===== --}}
    <div class="flex items-center justify-between px-4 py-2 border-b">
      <button @click="openModal()"
              class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none flex items-center gap-2 text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Buat Koreksi Baru
      </button>

      <div class="flex items-center gap-4">
        <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
          <label class="text-xs font-medium text-gray-700">Search:</label>
          <input type="text" name="search" value="{{ request('search') }}"
                 class="text-xs border border-gray-300 rounded-md px-2 py-1 focus:ring-blue-500 focus:border-blue-500 w-52"
                 onkeydown="if(event.key==='Enter') this.form.submit()" />
        </form>
        <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
          <label class="text-xs font-medium text-gray-700">Per halaman:</label>
          <select name="perPage" onchange="this.form.submit()"
                  class="text-xs border border-gray-300 rounded-md px-2 py-1 focus:ring-blue-500 focus:border-blue-500">
            @foreach ([10, 20, 50] as $opt)
              <option value="{{ $opt }}" {{ (int)request('perPage', $perPage) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
          </select>
        </form>
      </div>
    </div>

    {{-- ===== FLASH ===== --}}
    @if(session('success'))
      <div class="mx-4 mt-3 px-4 py-2 bg-green-50 border border-green-200 rounded-md text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="mx-4 mt-3 px-4 py-2 bg-red-50 border border-red-200 rounded-md text-sm text-red-800">{{ session('error') }}</div>
    @endif

    {{-- ===== TABLE ===== --}}
    <div class="mx-4 my-3">
      <h3 class="text-sm font-semibold text-gray-700 mb-2">Riwayat Koreksi SJ Panen</h3>
      <div class="overflow-x-auto border border-gray-300 rounded-md">
        <table class="min-w-full bg-white text-xs">
          <thead>
            <tr class="bg-gray-100 text-gray-700">
              <th class="py-2 px-3 border-b text-center w-8">No.</th>
              <th class="py-2 px-3 border-b text-center">No. Transaksi</th>
              <th class="py-2 px-3 border-b text-center">No. Approval</th>
              <th class="py-2 px-3 border-b text-left">No. Surat Jalan</th>
              <th class="py-2 px-3 border-b text-left">Perubahan</th>
              <th class="py-2 px-3 border-b text-left">Alasan</th>
              <th class="py-2 px-3 border-b text-center">Status</th>
              <th class="py-2 px-3 border-b text-left">Input By</th>
              <th class="py-2 px-3 border-b text-center">Dibuat</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($koreksiList as $index => $item)
              <tr class="hover:bg-gray-50 border-b">
                <td class="py-2 px-3 text-center text-gray-500">{{ $koreksiList->firstItem() + $index }}</td>
                <td class="py-2 px-3 text-center font-mono font-semibold text-gray-800">{{ $item->transactionnumber }}</td>
                <td class="py-2 px-3 text-center font-mono text-gray-600">{{ $item->approvalno ?? '-' }}</td>
                <td class="py-2 px-3 font-medium text-gray-800">{{ $item->suratjalanno }}</td>

                {{-- Perubahan summary --}}
                <td class="py-2 px-3">
                  @if ($item->perubahan_decoded)
                    <div class="flex flex-wrap gap-1">
                      @foreach ($item->perubahan_decoded as $field => $vals)
                        @php $label = $item->field_labels[$field] ?? $field; @endphp
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-slate-50 border border-slate-200 rounded text-[11px]">
                          <span class="font-medium text-slate-600">{{ $label }}:</span>
                          <span class="text-red-600 line-through">{{ $vals['lama'] ?? 'null' }}</span>
                          <span class="text-slate-400">→</span>
                          <span class="text-green-700 font-semibold">{{ $vals['baru'] ?? 'null' }}</span>
                        </span>
                      @endforeach
                    </div>
                  @else
                    <span class="text-gray-400">-</span>
                  @endif
                </td>

                <td class="py-2 px-3 text-gray-600 max-w-[140px] truncate" title="{{ $item->alasan }}">{{ $item->alasan ?? '-' }}</td>

                {{-- Status approval --}}
                <td class="py-2 px-3 text-center">
                  @php
                    $declinedAny = ($item->approval1flag === '0' || $item->approval2flag === '0' || $item->approval3flag === '0');
                  @endphp
                  @if (!$item->approvalno)
                    <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 text-[11px] font-semibold border border-gray-200">No Approval</span>
                  @elseif ($declinedAny)
                    <span class="px-2 py-0.5 rounded-full bg-red-50 text-red-600 text-[11px] font-semibold border border-red-200">Declined</span>
                  @elseif ($item->approvalstatus === '1')
                    <span class="px-2 py-0.5 rounded-full bg-green-50 text-green-700 text-[11px] font-semibold border border-green-200">Approved</span>
                  @else
                    @php $level = 1; if ($item->approval1flag === '1') $level = 2; if ($item->approval2flag === '1') $level = 3; @endphp
                    <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-600 text-[11px] font-semibold border border-amber-200">Waiting L{{ $level }}</span>
                  @endif
                </td>

                <td class="py-2 px-3 text-gray-600">{{ $item->inputby }}</td>
                <td class="py-2 px-3 text-center text-gray-500 whitespace-nowrap">{{ $item->formatted_createdat }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="py-8 text-center text-gray-400">Belum ada data koreksi</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if ($koreksiList->hasPages())
        <div class="mt-3">{{ $koreksiList->appends(request()->query())->links() }}</div>
      @endif
    </div>

  </div>{{-- end white card --}}

  {{-- ===== MODAL BUAT KOREKSI ===== --}}
  <div x-show="showModal"
       x-cloak
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
       @keydown.escape.window="closeModal()">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] flex flex-col" @click.stop>

      {{-- Modal Header --}}
      <div class="flex items-center justify-between px-5 py-4 border-b flex-shrink-0">
        <h2 class="text-base font-semibold text-gray-800">Buat Koreksi SJ Panen</h2>
        <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      {{-- Modal Body --}}
      <div class="px-5 py-4 overflow-y-auto flex-1 space-y-4">

        {{-- Step 1: Cari SJ --}}
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Nomor Surat Jalan <span class="text-red-500">*</span></label>
          <div class="flex gap-2">
            <input type="text" x-model="form.suratjalanno"
                   @keydown.enter.prevent="fetchSJDetail()"
                   :disabled="sjDetail !== null"
                   placeholder="Contoh: SJ-E067-LKH08041026-1-6"
                   class="flex-1 text-sm border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-50" />
            <button @click="fetchSJDetail()" x-show="!sjDetail"
                    :disabled="loadingSJ"
                    class="px-3 py-2 bg-slate-600 text-white text-xs rounded-md hover:bg-slate-700 disabled:opacity-50 whitespace-nowrap">
              <span x-show="!loadingSJ">Cari SJ</span>
              <span x-show="loadingSJ">...</span>
            </button>
            <button @click="resetSJ()" x-show="sjDetail"
                    class="px-3 py-2 bg-gray-200 text-gray-700 text-xs rounded-md hover:bg-gray-300 whitespace-nowrap">
              Ganti SJ
            </button>
          </div>
          <p x-show="errors.suratjalanno" class="text-red-500 text-xs mt-1" x-text="errors.suratjalanno"></p>
        </div>

        {{-- Info SJ --}}
        <div x-show="sjDetail" class="bg-slate-50 border border-slate-200 rounded-md p-3 text-xs">
          <div class="grid grid-cols-3 gap-2">
            <div><span class="text-gray-500">No. SJ:</span> <span class="font-semibold" x-text="sjDetail?.suratjalanno"></span></div>
            <div><span class="text-gray-500">Mandor:</span> <span class="font-medium" x-text="sjDetail?.mandorid ?? '-'"></span></div>
            <div>
              <span class="text-gray-500">Status Koreksi:</span>
              <span :class="sjDetail?.koreksi == 1 ? 'text-amber-600 font-semibold' : 'text-green-600'"
                    x-text="sjDetail?.koreksi == 1 ? 'Pernah Dikoreksi' : 'Belum Dikoreksi'"></span>
            </div>
          </div>
        </div>

        {{-- Form Fields (muncul setelah SJ ditemukan) --}}
        <div x-show="sjDetail" class="space-y-4">

          {{-- Group 1: Identifikasi Plot & Tebang --}}
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2 border-b pb-1">Plot & Tebang</p>
            <div class="grid grid-cols-2 gap-3">

              {{-- Plot --}}
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Plot</label>
                <div class="flex gap-1.5">
                  <input type="text" x-model="fields.plot"
                         @input="fields.plot = fields.plot.toUpperCase(); plotValid = null"
                         maxlength="5"
                         class="w-24 text-sm border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500 uppercase"
                         :class="isChanged('plot') ? 'bg-yellow-50 border-yellow-400' : ''" />
                  <button @click="validatePlot()"
                          :disabled="loadingPlot"
                          class="px-2 py-1.5 bg-slate-500 text-white text-xs rounded hover:bg-slate-600 disabled:opacity-50">
                    <span x-show="!loadingPlot">Cek</span>
                    <span x-show="loadingPlot">...</span>
                  </button>
                  <span x-show="plotValid === true" class="flex items-center text-green-600 text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                  </span>
                  <span x-show="plotValid === false" class="flex items-center text-red-500 text-xs font-medium">Tidak ada</span>
                </div>
                <p class="text-[11px] text-gray-400 mt-0.5">Saat ini: <span class="font-semibold" x-text="sjDetail?.plot"></span></p>
              </div>

              {{-- Varietas --}}
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Varietas</label>
                <input type="text" x-model="fields.varietas" maxlength="10"
                       class="w-full text-sm border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500"
                       :class="isChanged('varietas') ? 'bg-yellow-50 border-yellow-400' : ''" />
                <p class="text-[11px] text-gray-400 mt-0.5">Saat ini: <span class="font-semibold" x-text="sjDetail?.varietas ?? '-'"></span></p>
              </div>

              {{-- Kode Tebang --}}
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Kode Tebang</label>
                <input type="text" x-model="fields.kodetebang" maxlength="15"
                       class="w-full text-sm border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500"
                       :class="isChanged('kodetebang') ? 'bg-yellow-50 border-yellow-400' : ''" />
                <p class="text-[11px] text-gray-400 mt-0.5">Saat ini: <span class="font-semibold" x-text="sjDetail?.kodetebang ?? '-'"></span></p>
              </div>

            </div>
          </div>

          {{-- Group 2: Info Teknis --}}
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2 border-b pb-1">Info Teknis</p>
            <div class="grid grid-cols-2 gap-3">

              {{-- Langsir --}}
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Langsir</label>
                <select x-model="fields.langsir"
                        class="w-full text-sm border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500"
                        :class="isChanged('langsir') ? 'bg-yellow-50 border-yellow-400' : ''">
                  <option value="">-- pilih --</option>
                  <option value="0">0 - Tidak</option>
                  <option value="1">1 - Ya</option>
                </select>
                <p class="text-[11px] text-gray-400 mt-0.5">Saat ini: <span class="font-semibold" x-text="sjDetail?.langsir == 1 ? '1 - Ya' : '0 - Tidak'"></span></p>
              </div>

              {{-- Tebu Sulit --}}
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Tebu Sulit</label>
                <select x-model="fields.tebusulit"
                        class="w-full text-sm border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500"
                        :class="isChanged('tebusulit') ? 'bg-yellow-50 border-yellow-400' : ''">
                  <option value="">-- pilih --</option>
                  <option value="0">0 - Tidak</option>
                  <option value="1">1 - Ya</option>
                </select>
                <p class="text-[11px] text-gray-400 mt-0.5">Saat ini: <span class="font-semibold" x-text="sjDetail?.tebusulit == 1 ? '1 - Ya' : '0 - Tidak'"></span></p>
              </div>

              {{-- Kendaraan Kontraktor --}}
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Kendaraan Kontraktor</label>
                <select x-model="fields.kendaraankontraktor"
                        class="w-full text-sm border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500"
                        :class="isChanged('kendaraankontraktor') ? 'bg-yellow-50 border-yellow-400' : ''">
                  <option value="">-- pilih --</option>
                  <option value="0">0 - Tidak</option>
                  <option value="1">1 - Ya</option>
                </select>
                <p class="text-[11px] text-gray-400 mt-0.5">Saat ini: <span class="font-semibold" x-text="sjDetail?.kendaraankontraktor == 1 ? '1 - Ya' : '0 - Tidak'"></span></p>
              </div>

              {{-- Muat GL --}}
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Muat GL</label>
                <select x-model="fields.muatgl"
                        class="w-full text-sm border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500"
                        :class="isChanged('muatgl') ? 'bg-yellow-50 border-yellow-400' : ''">
                  <option value="">-- pilih --</option>
                  <option value="0">0 - Tidak</option>
                  <option value="1">1 - Ya</option>
                </select>
                <p class="text-[11px] text-gray-400 mt-0.5">Saat ini: <span class="font-semibold" x-text="sjDetail?.muatgl == 1 ? '1 - Ya' : '0 - Tidak'"></span></p>
              </div>

            </div>
          </div>

          {{-- Group 3: Kendaraan & Personil --}}
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2 border-b pb-1">Kendaraan & Personil</p>
            <div class="grid grid-cols-2 gap-3">

              {{-- Nomor Kendaraan --}}
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Nomor Kendaraan</label>
                <input type="text" x-model="fields.nomorkendaraan" maxlength="11"
                       class="w-full text-sm border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500"
                       :class="isChanged('nomorkendaraan') ? 'bg-yellow-50 border-yellow-400' : ''" />
                <p class="text-[11px] text-gray-400 mt-0.5">Saat ini: <span class="font-semibold" x-text="sjDetail?.nomorkendaraan ?? '-'"></span></p>
              </div>

              {{-- Nomor Polisi --}}
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Nomor Polisi</label>
                <input type="text" x-model="fields.nomorpolisi" maxlength="11"
                       class="w-full text-sm border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500"
                       :class="isChanged('nomorpolisi') ? 'bg-yellow-50 border-yellow-400' : ''" />
                <p class="text-[11px] text-gray-400 mt-0.5">Saat ini: <span class="font-semibold" x-text="sjDetail?.nomorpolisi ?? '-'"></span></p>
              </div>

              {{-- Nama Supir --}}
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Nama Supir</label>
                <input type="text" x-model="fields.namasupir" maxlength="25"
                       class="w-full text-sm border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500"
                       :class="isChanged('namasupir') ? 'bg-yellow-50 border-yellow-400' : ''" />
                <p class="text-[11px] text-gray-400 mt-0.5">Saat ini: <span class="font-semibold" x-text="sjDetail?.namasupir ?? '-'"></span></p>
              </div>

              {{-- Nama Kontraktor --}}
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Kontraktor</label>
                <select x-model="fields.namakontraktor"
                        class="w-full text-sm border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500"
                        :class="isChanged('namakontraktor') ? 'bg-yellow-50 border-yellow-400' : ''">
                  <option value="">-- pilih --</option>
                  <template x-for="k in kontraktors" :key="k.id">
                    <option :value="k.id" x-text="k.namakontraktor"></option>
                  </template>
                </select>
                <p class="text-[11px] text-gray-400 mt-0.5">Saat ini: <span class="font-semibold" x-text="sjDetail?.namakontraktor ? (sjDetail.namakontraktor + ' – ' + (kontraktors.find(k => k.id === sjDetail.namakontraktor)?.namakontraktor ?? '?')) : '-'"></span></p>
              </div>

              {{-- Nama Sub Kontraktor --}}
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Sub Kontraktor</label>
                <select x-model="fields.namasubkontraktor"
                        class="w-full text-sm border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500"
                        :class="isChanged('namasubkontraktor') ? 'bg-yellow-50 border-yellow-400' : ''">
                  <option value="">-- pilih --</option>
                  <template x-for="sk in subkontraktors" :key="sk.id">
                    <option :value="sk.id" x-text="sk.namasubkontraktor"></option>
                  </template>
                </select>
                <p class="text-[11px] text-gray-400 mt-0.5">Saat ini: <span class="font-semibold" x-text="sjDetail?.namasubkontraktor ? (sjDetail.namasubkontraktor + ' – ' + (subkontraktors.find(sk => sk.id === sjDetail.namasubkontraktor)?.namasubkontraktor ?? '?')) : '-'"></span></p>
              </div>

            </div>
          </div>

          {{-- Alasan --}}
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Alasan Koreksi</label>
            <textarea x-model="form.alasan" rows="2" placeholder="Opsional..."
                      class="w-full text-sm border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>
          </div>

        </div>

        {{-- Error umum --}}
        <div x-show="generalError" class="px-3 py-2 bg-red-50 border border-red-200 rounded-md text-xs text-red-700" x-text="generalError"></div>

      </div>

      {{-- Modal Footer --}}
      <div class="px-5 py-3 border-t flex justify-between items-center bg-gray-50 rounded-b-xl flex-shrink-0">
        <p x-show="sjDetail" class="text-xs text-slate-500">
          <span class="font-medium text-slate-700" x-text="changedCount()"></span> field diubah
          <span x-show="changedCount() > 0" class="text-amber-600"> (highlight kuning)</span>
        </p>
        <div class="flex gap-2">
          <button @click="closeModal()" class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
            Batal
          </button>
          <button @click="submitKoreksi()"
                  :disabled="submitting || !sjDetail || changedCount() === 0"
                  class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2">
            <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span x-text="submitting ? 'Menyimpan...' : 'Simpan & Ajukan'"></span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    const _routeStore   = '{{ route('transaction.koreksi-sj-panen.store') }}';
    const _routeGetSJ   = '{{ route('transaction.koreksi-sj-panen.get-sj') }}';
    const _routePlot    = '{{ route('transaction.koreksi-sj-panen.check-plot') }}';

    // Semua field yang bisa diedit
    const EDITABLE_FIELDS = [
      'plot','varietas','kodetebang','langsir','tebusulit',
      'kendaraankontraktor','muatgl','nomorkendaraan','nomorpolisi',
      'namasupir','namakontraktor','namasubkontraktor'
    ];

    function koreksiSJData() {
      return {
        showModal:     false,
        loadingSJ:     false,
        loadingPlot:   false,
        submitting:    false,
        plotValid:     null,
        generalError:  null,
        sjDetail:      null,
        kontraktors:   [],
        subkontraktors:[],

        form: { suratjalanno: '', alasan: '' },

        // nilai fields yang bisa diedit (diisi setelah SJ ditemukan)
        fields: {},
        // snapshot nilai awal saat SJ di-load (untuk deteksi perubahan)
        original: {},

        openModal() { this.reset(); this.showModal = true; },
        closeModal() { this.showModal = false; this.reset(); },

        reset() {
          this.sjDetail      = null;
          this.kontraktors   = [];
          this.subkontraktors= [];
          this.plotValid     = null;
          this.generalError  = null;
          this.loadingSJ     = false;
          this.loadingPlot   = false;
          this.submitting    = false;
          this.form          = { suratjalanno: '', alasan: '' };
          this.fields        = {};
          this.original      = {};
        },

        resetSJ() {
          this.sjDetail   = null;
          this.fields     = {};
          this.original   = {};
          this.plotValid  = null;
          this.form.suratjalanno = '';
        },

        async fetchSJDetail() {
          const sj = this.form.suratjalanno.trim();
          if (!sj) return;

          this.loadingSJ = true;
          this.generalError = null;

          try {
            const res  = await fetch(`${_routeGetSJ}?suratjalanno=${encodeURIComponent(sj)}`, {
              headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (data.success) {
              this.sjDetail       = data.data;
              this.kontraktors    = data.kontraktors    ?? [];
              this.subkontraktors = data.subkontraktors ?? [];

              // Populate fields dengan nilai saat ini
              const f = {};
              EDITABLE_FIELDS.forEach(field => {
                const val = data.data[field];
                f[field]  = (val === null || val === undefined) ? '' : String(val);
              });
              this.fields   = { ...f };
              this.original = { ...f };
            } else {
              this.generalError = data.message;
            }
          } catch (e) {
            this.generalError = 'Gagal mengambil data SJ';
          } finally {
            this.loadingSJ = false;
          }
        },

        isChanged(field) {
          return this.original[field] !== undefined &&
                 String(this.fields[field] ?? '') !== String(this.original[field] ?? '');
        },

        changedCount() {
          return EDITABLE_FIELDS.filter(f => this.isChanged(f)).length;
        },

        async validatePlot() {
          const plot = (this.fields.plot ?? '').trim().toUpperCase();
          if (!plot) return;

          this.loadingPlot = true;
          this.plotValid   = null;

          try {
            const res  = await fetch(`${_routePlot}?plot=${encodeURIComponent(plot)}`, {
              headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            this.plotValid = data.exists === true;
          } catch (e) {
            this.plotValid = null;
          } finally {
            this.loadingPlot = false;
          }
        },

        async submitKoreksi() {
          if (!this.sjDetail || this.changedCount() === 0) return;

          // Kalau plot diubah, wajib divalidasi dulu
          if (this.isChanged('plot') && this.plotValid !== true) {
            this.generalError = 'Plot baru belum divalidasi. Klik tombol "Cek" terlebih dahulu.';
            return;
          }

          this.generalError = null;
          this.submitting   = true;

          try {
            // Kirim semua fields (server yang tentukan mana yang berubah)
            const payload = {
              suratjalanno: this.form.suratjalanno.trim(),
              alasan:       this.form.alasan.trim(),
            };
            EDITABLE_FIELDS.forEach(f => { payload[f] = this.fields[f]; });

            const res  = await fetch(_routeStore, {
              method : 'POST',
              headers: {
                'Content-Type'    : 'application/json',
                'X-CSRF-TOKEN'    : document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
              },
              body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (data.success) {
              this.closeModal();
              window.location.reload();
            } else {
              this.generalError = data.message;
            }
          } catch (e) {
            this.generalError = 'Terjadi kesalahan, coba lagi';
          } finally {
            this.submitting = false;
          }
        },
      };
    }
  </script>
  </div>{{-- end x-data --}}
</x-layout>
