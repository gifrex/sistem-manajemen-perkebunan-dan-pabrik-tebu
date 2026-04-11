<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div x-data="sjNonNfcData()">

  {{-- Flash --}}
  @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-800">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800">{{ session('error') }}</div>
  @endif

  <div class="mx-auto py-1 bg-white rounded-md shadow-md">

    {{-- Header --}}
    <div class="flex items-center justify-between px-4 py-2 border-b">
      <button @click="openModal()"
              class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center gap-2 text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah SJ Non-NFC
      </button>

      <div class="flex items-center gap-4">
        <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
          <label class="text-xs font-medium text-gray-700">Search:</label>
          <input type="text" name="search" value="{{ request('search') }}"
                 class="text-xs border border-gray-300 rounded-md px-2 py-1 w-52 focus:ring-blue-500 focus:border-blue-500"
                 onkeydown="if(event.key==='Enter') this.form.submit()" />
        </form>
        <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
          <label class="text-xs font-medium text-gray-700">Per halaman:</label>
          <select name="perPage" onchange="this.form.submit()"
                  class="text-xs border border-gray-300 rounded-md px-2 py-1 focus:ring-blue-500 focus:border-blue-500">
            @foreach([10, 20, 50] as $opt)
              <option value="{{ $opt }}" {{ (int)request('perPage', $perPage) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
          </select>
        </form>
      </div>
    </div>

    {{-- Table --}}
    <div class="mx-4 my-3">
      <div class="overflow-x-auto border border-gray-300 rounded-md">
        <table class="min-w-full bg-white text-xs">
          <thead>
            <tr class="bg-gray-100 text-gray-700">
              <th class="py-2 px-3 border-b text-center w-8">No.</th>
              <th class="py-2 px-3 border-b text-center">No. Transaksi</th>
              <th class="py-2 px-3 border-b text-left">No. Surat Jalan</th>
              <th class="py-2 px-3 border-b text-left">Mandor</th>
              <th class="py-2 px-3 border-b text-left">Plot</th>
              <th class="py-2 px-3 border-b text-left">No. Polisi</th>
              <th class="py-2 px-3 border-b text-left">Supir</th>
              <th class="py-2 px-3 border-b text-left">Input By</th>
              <th class="py-2 px-3 border-b text-center">Status</th>
              <th class="py-2 px-3 border-b text-center">Dibuat</th>
              <th class="py-2 px-3 border-b text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($list as $index => $item)
              <tr class="hover:bg-gray-50 border-b">
                <td class="py-2 px-3 text-center text-gray-500">{{ $list->firstItem() + $index }}</td>
                <td class="py-2 px-3 text-center font-mono font-semibold text-gray-700">{{ $item->transactionnumber ?? '-' }}</td>
                <td class="py-2 px-3">
                  <a href="{{ route('transaction.surat-jalan-non-nfc.show', $item->id) }}"
                     class="font-medium text-blue-600 hover:text-blue-800 underline underline-offset-2">{{ $item->suratjalanno }}</a>
                </td>
                <td class="py-2 px-3 text-gray-700">{{ $item->mandorid ?? '-' }}</td>
                <td class="py-2 px-3 text-gray-700">{{ $item->plot ?? '-' }}</td>
                <td class="py-2 px-3 text-gray-700">{{ $item->nomorpolisi ?? '-' }}</td>
                <td class="py-2 px-3 text-gray-700">{{ $item->namasupir ?? '-' }}</td>
                <td class="py-2 px-3 text-gray-600">{{ $item->nonnfc_createdby ?? '-' }}</td>

                {{-- Status --}}
                <td class="py-2 px-3 text-center">
                  @if(is_null($item->approvalstatus))
                    <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-600 text-[11px] font-semibold border border-amber-200">Menunggu Approval</span>
                  @elseif($item->approvalstatus == 1)
                    @if($item->nonnfc_printed)
                      <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 text-[11px] font-semibold border border-blue-200">Sudah Cetak</span>
                    @else
                      <span class="px-2 py-0.5 rounded-full bg-green-50 text-green-700 text-[11px] font-semibold border border-green-200">Approved</span>
                    @endif
                  @else
                    <span class="px-2 py-0.5 rounded-full bg-red-50 text-red-600 text-[11px] font-semibold border border-red-200">Ditolak</span>
                  @endif
                </td>

                <td class="py-2 px-3 text-center text-gray-500 whitespace-nowrap">{{ $item->formatted_createdat }}</td>

                {{-- Aksi --}}
                <td class="py-2 px-3 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    {{-- Detail --}}
                    <button @click="openDetail({{ json_encode($item) }})"
                            class="px-2 py-1 text-[11px] font-medium rounded bg-gray-100 text-gray-700 hover:bg-gray-200">
                      Detail
                    </button>

                    {{-- Print - hanya jika approved dan belum cetak --}}
                    @if($item->approvalstatus == 1 && !$item->nonnfc_printed)
                      <button @click="openPrint({{ json_encode($item) }})"
                              class="px-2 py-1 text-[11px] font-medium rounded bg-indigo-600 text-white hover:bg-indigo-700">
                        Print
                      </button>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="10" class="py-8 text-center text-gray-400">Belum ada data SJ Non-NFC</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($list->hasPages())
        <div class="mt-3">{{ $list->appends(request()->query())->links() }}</div>
      @endif
    </div>
  </div>

  {{-- ===== MODAL BUAT SJ NON-NFC ===== --}}
  <div x-show="showModal" x-cloak
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
       @keydown.escape.window="closeModal()">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col" @click.stop>

      {{-- Modal Header --}}
      <div class="flex items-center justify-between px-5 py-3 border-b flex-shrink-0">
        <h2 class="text-sm font-semibold text-gray-800">Input SJ Non-NFC</h2>
        <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      {{-- Modal Body --}}
      <form id="formSJNonNfc" enctype="multipart/form-data">
        @csrf
        <div class="px-5 py-3 overflow-y-auto flex-1 space-y-3">

          {{-- Row 1: No SJ + Mandor + Tgl Angkut --}}
          <div class="grid grid-cols-12 gap-2">
            <div class="col-span-5">
              <label class="block text-xs font-medium text-gray-700 mb-1">Nomor Surat Jalan <span class="text-red-500">*</span></label>
              <input type="text" name="suratjalanno" x-model="form.suratjalanno"
                     placeholder="SJ-A006-LKH08040126-1-10"
                     maxlength="30"
                     class="w-full text-xs border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500 font-mono" />
              <p x-show="errors.suratjalanno" class="text-red-500 text-[11px] mt-0.5" x-text="errors.suratjalanno"></p>
            </div>
            <div class="col-span-4">
              <label class="block text-xs font-medium text-gray-700 mb-1">Mandor <span class="text-red-500">*</span></label>
              <select name="mandorid" x-model="form.mandorid"
                      class="w-full text-xs border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500">
                <option value="">-- pilih mandor --</option>
                <template x-for="m in formData.mandors" :key="m.mandorid">
                  <option :value="m.mandorid" x-text="m.mandorid + ' — ' + m.name"></option>
                </template>
              </select>
              <p x-show="errors.mandorid" class="text-red-500 text-[11px] mt-0.5" x-text="errors.mandorid"></p>
            </div>
            <div class="col-span-3">
              <label class="block text-xs font-medium text-gray-700 mb-1">Tgl Angkut</label>
              <input type="datetime-local" name="tanggalangkut" x-model="form.tanggalangkut"
                     class="w-full text-xs border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500" />
            </div>
          </div>

          <div class="border-t pt-2">
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Plot & Tanaman</p>
            {{-- Row 2: Plot + Varietas + Kategori + Umur + Kode Tebang + Tgl Tebang --}}
            <div class="grid grid-cols-6 gap-2">
              <div class="col-span-1">
                <label class="block text-xs font-medium text-gray-700 mb-1">Plot <span class="text-red-500">*</span></label>
                <div class="flex gap-1">
                  <input type="text" name="plot" x-model="form.plot"
                         @input="form.plot = form.plot.toUpperCase(); plotValid = null"
                         maxlength="5" placeholder="A006"
                         class="w-full text-xs border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500 uppercase" />
                  <button type="button" @click="validatePlot()" :disabled="loadingPlot"
                          class="flex-shrink-0 px-1.5 py-1 bg-slate-600 text-white text-[10px] rounded hover:bg-slate-700 disabled:opacity-50">
                    <span x-show="!loadingPlot">Cek</span>
                    <span x-show="loadingPlot">...</span>
                  </button>
                </div>
                <div class="flex items-center gap-1 mt-0.5 h-3">
                  <span x-show="plotValid === true" class="text-green-600 text-[10px]">✓ Valid</span>
                  <span x-show="plotValid === false" class="text-red-500 text-[10px]">✗ Tidak ada</span>
                  <span x-show="errors.plot" class="text-red-500 text-[10px]" x-text="errors.plot"></span>
                </div>
              </div>
              <div class="col-span-1">
                <label class="block text-xs font-medium text-gray-700 mb-1">Varietas <span class="text-red-500">*</span></label>
                <input type="text" name="varietas" x-model="form.varietas" maxlength="10" readonly
                       class="w-full text-xs border border-gray-200 rounded-md px-2 py-1.5 bg-gray-100 text-gray-500 cursor-not-allowed" />
                <p x-show="errors.varietas" class="text-red-500 text-[10px] mt-0.5" x-text="errors.varietas"></p>
              </div>
              <div class="col-span-1">
                <label class="block text-xs font-medium text-gray-700 mb-1">Kategori</label>
                <select name="kategori" x-model="form.kategori"
                        class="w-full text-xs border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500">
                  <option value="">-- pilih --</option>
                  <option value="PC">PC</option>
                  <option value="RC1">RC1</option>
                  <option value="RC2">RC2</option>
                  <option value="RC3">RC3</option>
                </select>
              </div>
              <div class="col-span-1">
                <label class="block text-xs font-medium text-gray-700 mb-1">Umur (bln)</label>
                <select name="umur" x-model="form.umur"
                        class="w-full text-xs border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500">
                  <option value="">--</option>
                  @for($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                  @endfor
                </select>
              </div>
              <div class="col-span-1">
                <label class="block text-xs font-medium text-gray-700 mb-1">Kode Tebang</label>
                <input type="text" name="kodetebang" x-model="form.kodetebang" maxlength="15"
                       class="w-full text-xs border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500" />
              </div>
              <div class="col-span-1">
                <label class="block text-xs font-medium text-gray-700 mb-1">Tgl Tebang</label>
                <input type="date" name="tanggaltebang" x-model="form.tanggaltebang"
                       class="w-full text-xs border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500" />
              </div>
            </div>
          </div>

          {{-- Row 3: Kendaraan & Personil --}}
          <div class="border-t pt-2">
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Kendaraan & Personil</p>
            <div class="grid grid-cols-4 gap-2">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">No. Kendaraan</label>
                <input type="text" name="nomorkendaraan" x-model="form.nomorkendaraan" maxlength="6"
                       class="w-full text-xs border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">No. Polisi <span class="text-red-500">*</span></label>
                <input type="text" name="nomorpolisi" x-model="form.nomorpolisi" maxlength="11"
                       class="w-full text-xs border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500" />
                <p x-show="errors.nomorpolisi" class="text-red-500 text-[10px] mt-0.5" x-text="errors.nomorpolisi"></p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Nama Supir <span class="text-red-500">*</span></label>
                <input type="text" name="namasupir" x-model="form.namasupir" maxlength="25"
                       class="w-full text-xs border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500" />
                <p x-show="errors.namasupir" class="text-red-500 text-[10px] mt-0.5" x-text="errors.namasupir"></p>
              </div>
              <div>
                {{-- placeholder for alignment --}}
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Kontraktor</label>
                <select name="namakontraktor" x-model="form.namakontraktor"
                        class="w-full text-xs border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500">
                  <option value="">-- pilih --</option>
                  <template x-for="k in formData.kontraktors" :key="k.id">
                    <option :value="k.id" x-text="k.id + ' — ' + k.namakontraktor"></option>
                  </template>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Sub Kontraktor</label>
                <select name="namasubkontraktor" x-model="form.namasubkontraktor"
                        class="w-full text-xs border border-gray-300 rounded-md px-2 py-1.5 focus:ring-blue-500 focus:border-blue-500">
                  <option value="">-- pilih --</option>
                  <template x-for="sk in formData.subkontraktors" :key="sk.id">
                    <option :value="sk.id" x-text="sk.id + ' — ' + sk.namasubkontraktor"></option>
                  </template>
                </select>
              </div>
            </div>
          </div>

          {{-- Row 4: Info Teknis (4 checkbox-style) + Keterangan + Lampiran --}}
          <div class="border-t pt-2 grid grid-cols-2 gap-4">
            {{-- Info Teknis --}}
            <div>
              <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Info Teknis</p>
              <div class="grid grid-cols-2 gap-2">
                @foreach(['langsir' => 'Langsir', 'tebusulit' => 'Tebu Sulit', 'kendaraankontraktor' => 'Kend. Kontraktor', 'muatgl' => 'Muat GL'] as $field => $label)
                <div class="flex items-center gap-2">
                  <select name="{{ $field }}" x-model="form.{{ $field }}"
                          class="text-xs border border-gray-300 rounded-md px-2 py-1 focus:ring-blue-500 focus:border-blue-500 w-20">
                    <option value="0">Tidak</option>
                    <option value="1">Ya</option>
                  </select>
                  <label class="text-xs text-gray-600">{{ $label }}</label>
                </div>
                @endforeach
              </div>
            </div>
            {{-- Keterangan & Lampiran --}}
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="keterangan" x-model="form.keterangan" rows="3"
                          class="w-full text-xs border border-gray-300 rounded-md px-2 py-1.5 resize-none focus:ring-blue-500 focus:border-blue-500"></textarea>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Lampiran <span class="text-gray-400 font-normal">(opsional)</span></label>
                <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf"
                       class="w-full text-[11px] border border-gray-300 rounded-md px-2 py-1.5" />
                <p class="text-[10px] text-gray-400 mt-1">JPG, PNG, PDF · maks 5MB</p>
              </div>
            </div>
          </div>

          {{-- General error --}}
          <div x-show="generalError" class="px-3 py-2 bg-red-50 border border-red-200 rounded-md text-xs text-red-700" x-text="generalError"></div>

        </div>

        {{-- Modal Footer --}}
        <div class="px-5 py-3 border-t flex justify-end items-center gap-2 bg-gray-50 rounded-b-xl flex-shrink-0">
          <button type="button" @click="closeModal()" class="px-4 py-1.5 text-xs text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
            Batal
          </button>
          <button type="button" @click="submitForm()"
                  :disabled="submitting"
                  class="px-4 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2">
            <svg x-show="submitting" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span x-text="submitting ? 'Menyimpan...' : 'Simpan & Ajukan'"></span>
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ===== MODAL DETAIL ===== --}}
  <div x-show="showDetail" x-cloak
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
       @keydown.escape.window="showDetail = false">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] flex flex-col" @click.stop>
      <div class="flex items-center justify-between px-5 py-4 border-b flex-shrink-0">
        <h2 class="text-base font-semibold text-gray-800">Detail SJ Non-NFC</h2>
        <button @click="showDetail = false" class="text-gray-400 hover:text-gray-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
      <div class="px-5 py-4 overflow-y-auto flex-1 text-sm space-y-1" x-show="detailItem">
        <template x-if="detailItem">
          <div class="space-y-1.5 text-xs">
            <div class="grid grid-cols-2 gap-x-4 gap-y-1.5">
              <div><span class="text-gray-500">No. SJ:</span> <span class="font-semibold font-mono" x-text="detailItem.suratjalanno"></span></div>
              <div><span class="text-gray-500">Mandor:</span> <span class="font-medium" x-text="detailItem.mandorid ?? '-'"></span></div>
              <div><span class="text-gray-500">Plot:</span> <span x-text="detailItem.plot ?? '-'"></span></div>
              <div><span class="text-gray-500">Varietas:</span> <span x-text="detailItem.varietas ?? '-'"></span></div>
              <div><span class="text-gray-500">Kategori:</span> <span x-text="detailItem.kategori ?? '-'"></span></div>
              <div><span class="text-gray-500">Umur:</span> <span x-text="detailItem.umur ? detailItem.umur + ' bln' : '-'"></span></div>
              <div><span class="text-gray-500">Kode Tebang:</span> <span x-text="detailItem.kodetebang ?? '-'"></span></div>
              <div><span class="text-gray-500">Tgl Tebang:</span> <span x-text="detailItem.tanggaltebang ?? '-'"></span></div>
              <div><span class="text-gray-500">Tgl Angkut:</span> <span x-text="detailItem.tanggalangkut ?? '-'"></span></div>
              <div><span class="text-gray-500">No. Kendaraan:</span> <span x-text="detailItem.nomorkendaraan ?? '-'"></span></div>
              <div><span class="text-gray-500">No. Polisi:</span> <span class="font-semibold" x-text="detailItem.nomorpolisi ?? '-'"></span></div>
              <div><span class="text-gray-500">Supir:</span> <span x-text="detailItem.namasupir ?? '-'"></span></div>
              <div><span class="text-gray-500">Kontraktor:</span> <span x-text="detailItem.namakontraktor ?? '-'"></span></div>
              <div><span class="text-gray-500">Sub Kontraktor:</span> <span x-text="detailItem.namasubkontraktor ?? '-'"></span></div>
              <div><span class="text-gray-500">Langsir:</span> <span x-text="detailItem.langsir == 1 ? 'Ya' : 'Tidak'"></span></div>
              <div><span class="text-gray-500">Tebu Sulit:</span> <span x-text="detailItem.tebusulit == 1 ? 'Ya' : 'Tidak'"></span></div>
              <div><span class="text-gray-500">Kend. Kontraktor:</span> <span x-text="detailItem.kendaraankontraktor == 1 ? 'Ya' : 'Tidak'"></span></div>
              <div><span class="text-gray-500">Muat GL:</span> <span x-text="detailItem.muatgl == 1 ? 'Ya' : 'Tidak'"></span></div>
            </div>
            <div x-show="detailItem.keterangan" class="pt-1 border-t">
              <span class="text-gray-500">Keterangan:</span> <span x-text="detailItem.keterangan"></span>
            </div>
            <div x-show="detailItem.rejection_reason" class="px-3 py-2 bg-red-50 border border-red-200 rounded-md mt-2">
              <span class="text-red-600 font-medium">Alasan Ditolak:</span> <span class="text-red-700" x-text="detailItem.rejection_reason"></span>
            </div>
          </div>
        </template>
      </div>
      <div class="px-5 py-3 border-t flex justify-end bg-gray-50 rounded-b-xl flex-shrink-0">
        <button @click="showDetail = false" class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
          Tutup
        </button>
      </div>
    </div>
  </div>

  {{-- ===== MODAL PRINT ===== --}}
  <div x-show="showPrint" x-cloak
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
       @keydown.escape.window="showPrint = false">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4" @click.stop>
      <div class="flex items-center justify-between px-5 py-4 border-b">
        <h2 class="text-base font-semibold text-gray-800">Print Surat Jalan</h2>
        <button @click="showPrint = false" class="text-gray-400 hover:text-gray-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
      <div class="px-5 py-4 space-y-4">

        {{-- Info SJ --}}
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs">
          <div class="grid grid-cols-2 gap-1.5">
            <div><span class="text-gray-500">No. SJ:</span> <span class="font-semibold font-mono" x-text="printItem?.suratjalanno"></span></div>
            <div><span class="text-gray-500">No. Polisi:</span> <span class="font-semibold" x-text="printItem?.nomorpolisi"></span></div>
            <div><span class="text-gray-500">Supir:</span> <span x-text="printItem?.namasupir"></span></div>
            <div><span class="text-gray-500">Plot:</span> <span x-text="printItem?.plot"></span></div>
          </div>
        </div>

        {{-- Status Bluetooth --}}
        <div>
          <p class="text-xs font-medium text-gray-700 mb-2">Printer Bluetooth</p>
          <div x-show="!btConnected" class="space-y-2">
            <p class="text-xs text-gray-500">Pastikan printer menyala dan sudah di-pair dengan perangkat ini.</p>
            <button @click="connectBluetooth()"
                    :disabled="btConnecting"
                    class="w-full py-2 px-4 bg-slate-700 text-white text-sm font-medium rounded-lg hover:bg-slate-800 disabled:opacity-50 flex items-center justify-center gap-2">
              <svg x-show="btConnecting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <svg x-show="!btConnecting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/>
              </svg>
              <span x-text="btConnecting ? 'Menghubungkan...' : 'Cari & Hubungkan Printer'"></span>
            </button>
          </div>
          <div x-show="btConnected" class="flex items-center gap-2 px-3 py-2 bg-green-50 border border-green-200 rounded-lg text-xs text-green-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>Printer terhubung: <strong x-text="btDeviceName"></strong></span>
            <button @click="disconnectBluetooth()" class="ml-auto text-gray-400 hover:text-gray-600 underline">Putus</button>
          </div>
        </div>

        <div x-show="btError" class="px-3 py-2 bg-red-50 border border-red-200 rounded-md text-xs text-red-700" x-text="btError"></div>
        <div x-show="printSuccess" class="px-3 py-2 bg-green-50 border border-green-200 rounded-md text-xs text-green-700">
          Surat jalan berhasil dicetak.
        </div>
      </div>

      <div class="px-5 py-3 border-t flex justify-between gap-2 bg-gray-50 rounded-b-xl">
        <button @click="openPreview()" class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
          </svg>
          Preview
        </button>
        <div class="flex gap-2">
          <button @click="showPrint = false" class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
            Tutup
          </button>
          <button @click="doPrint()"
                  :disabled="!btConnected || printing"
                  class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-2">
            <svg x-show="printing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span x-text="printing ? 'Mencetak...' : 'Cetak'"></span>
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- ===== MODAL PRINT PREVIEW ===== --}}
  <div x-show="showPreview" x-cloak
       class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50"
       @keydown.escape.window="showPreview = false">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 max-h-[90vh] flex flex-col" @click.stop>
      <div class="flex items-center justify-between px-5 py-3 border-b flex-shrink-0">
        <h2 class="text-sm font-semibold text-gray-800">Preview Struk Cetak</h2>
        <button @click="showPreview = false" class="text-gray-400 hover:text-gray-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
      <div class="overflow-y-auto flex-1 p-4">
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 shadow-inner">
          <pre class="font-mono text-[11px] leading-snug text-gray-800 whitespace-pre-wrap" x-html="previewHtml"></pre>
          <div class="flex justify-center my-2">
            <canvas id="qrPreviewCanvas" style="image-rendering:pixelated;width:160px;height:160px;"></canvas>
          </div>
          <pre class="font-mono text-[11px] leading-snug text-gray-800 whitespace-pre-wrap" x-html="previewHtmlAfterQr"></pre>
        </div>
        <p class="text-[10px] text-gray-400 text-center mt-2">* Simulasi tampilan thermal printer 32 karakter</p>
      </div>
      <div class="px-5 py-3 border-t flex justify-end bg-gray-50 rounded-b-xl flex-shrink-0">
        <button @click="showPreview = false" class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
          Tutup
        </button>
      </div>
    </div>
  </div>

  </div>{{-- end x-data --}}

  <script>
  const _routeStore       = '{{ route('transaction.surat-jalan-non-nfc.store') }}';
  const _routeFormData    = '{{ route('transaction.surat-jalan-non-nfc.form-data') }}';
  const _routePlotData    = '{{ route('transaction.surat-jalan-non-nfc.plot-data') }}';
  const _routeMarkPrinted = '{{ route('transaction.surat-jalan-non-nfc.mark-printed') }}';
  const _routeAttachment  = '{{ route('transaction.surat-jalan-non-nfc.attachment', ':id') }}';
  const _companycode      = '{{ Session::get('companycode') }}';

  function sjNonNfcData() {
    return {
      // Modal state
      showModal:   false,
      showDetail:  false,
      showPrint:   false,
      showPreview: false,
      previewHtml: '',
      previewHtmlAfterQr: '',
      submitting:  false,
      loadingPlot: false,
      plotValid:   null,
      generalError: null,
      errors: {},

      // Form data
      form: {
        suratjalanno: '', mandorid: '', plot: '', varietas: '',
        kategori: '', umur: '', kodetebang: 'Premium',
        langsir: '0', tebusulit: '0', kendaraankontraktor: '0', muatgl: '0',
        nomorkendaraan: '', nomorpolisi: '', namasupir: '',
        namakontraktor: '', namasubkontraktor: '',
        tanggaltebang: '', tanggalangkut: '',
        keterangan: '',
      },

      // Form dropdown data
      formData: { mandors: [], kontraktors: [], subkontraktors: [] },

      // Detail & print
      detailItem:  null,
      printItem:   null,

      // Bluetooth
      btConnected:  false,
      btConnecting: false,
      btDeviceName: '',
      btDevice:     null,
      btCharacteristic: null,
      btError:      null,
      printing:     false,
      printSuccess: false,

      async openModal() {
        this.resetForm();
        this.showModal = true;
        if (!this.formData.mandors.length) {
          await this.loadFormData();
        }
      },

      closeModal() {
        this.showModal = false;
        this.resetForm();
      },

      resetForm() {
        this.form = {
          suratjalanno: '', mandorid: '', plot: '', varietas: '',
          kategori: '', umur: '', kodetebang: 'Premium',
          langsir: '0', tebusulit: '0', kendaraankontraktor: '0', muatgl: '0',
          nomorkendaraan: '', nomorpolisi: '', namasupir: '',
          namakontraktor: '', namasubkontraktor: '',
          tanggaltebang: '', tanggalangkut: '',
          keterangan: '',
        };
        this.plotValid   = null;
        this.generalError = null;
        this.errors      = {};
        this.submitting  = false;
      },

      async loadFormData() {
        try {
          const res  = await fetch(_routeFormData, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
          const data = await res.json();
          if (data.success) {
            this.formData.mandors       = data.mandors;
            this.formData.kontraktors   = data.kontraktors;
            this.formData.subkontraktors= data.subkontraktors;
          }
        } catch (e) { console.error('loadFormData failed', e); }
      },

      async validatePlot() {
        const plot = (this.form.plot ?? '').trim().toUpperCase();
        if (!plot) return;
        this.loadingPlot = true;
        this.plotValid   = null;
        try {
          const res  = await fetch(`${_routePlotData}?plot=${encodeURIComponent(plot)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
          });
          const data = await res.json();
          this.plotValid = data.exists === true;
          if (data.exists && data.varietas) {
            this.form.varietas = data.varietas;
          }
        } catch (e) { this.plotValid = null; }
        finally     { this.loadingPlot = false; }
      },

      async submitForm() {
        this.errors       = {};
        this.generalError = null;

        // Client-side required
        if (!this.form.suratjalanno.trim()) { this.errors.suratjalanno = 'Nomor SJ wajib diisi'; return; }
        if (!this.form.mandorid)            { this.errors.mandorid = 'Mandor wajib dipilih'; return; }
        if (!this.form.plot.trim())         { this.errors.plot = 'Plot wajib diisi'; return; }
        if (!this.form.varietas.trim())     { this.errors.varietas = 'Varietas wajib diisi'; return; }
        if (!this.form.nomorpolisi.trim())  { this.errors.nomorpolisi = 'No. Polisi wajib diisi'; return; }
        if (!this.form.namasupir.trim())    { this.errors.namasupir = 'Nama Supir wajib diisi'; return; }

        if (this.plotValid !== true) {
          this.errors.plot = 'Plot belum divalidasi. Klik tombol "Cek".';
          return;
        }

        this.submitting = true;
        try {
          const formEl  = document.getElementById('formSJNonNfc');
          const fd      = new FormData(formEl);

          // Sync alpine model values ke FormData
          Object.entries(this.form).forEach(([k, v]) => {
            if (v !== null && v !== undefined && v !== '') fd.set(k, v);
          });

          const res  = await fetch(_routeStore, {
            method : 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body   : fd,
          });
          const data = await res.json();

          if (data.success) {
            this.closeModal();
            window.location.reload();
          } else {
            this.generalError = data.message;
          }
        } catch (e) {
          this.generalError = 'Terjadi kesalahan, coba lagi.';
        } finally {
          this.submitting = false;
        }
      },

      openDetail(item) {
        this.detailItem = item;
        this.showDetail = true;
      },

      async openAttachment(id) {
        const url = _routeAttachment.replace(':id', id);
        try {
          const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
          const data = await res.json();
          if (data.url) window.open(data.url, '_blank');
        } catch (e) { alert('Gagal membuka lampiran.'); }
      },

      openPrint(item) {
        this.printItem   = item;
        this.btError     = null;
        this.printSuccess= false;
        this.showPrint   = true;
      },

      // =================== BLUETOOTH ===================
      async connectBluetooth() {
        if (!navigator.bluetooth) {
          this.btError = 'Browser ini tidak mendukung Web Bluetooth. Gunakan Chrome/Edge versi terbaru.';
          return;
        }

        this.btConnecting = true;
        this.btError      = null;

        try {
          const device = await navigator.bluetooth.requestDevice({
            filters: [
              { namePrefix: 'printer' }, { namePrefix: 'Printer' },
              { namePrefix: 'POS' },     { namePrefix: 'pos' },
              { namePrefix: 'RPP' },     { namePrefix: 'rpp' },
              { namePrefix: 'MTP' },     { namePrefix: 'PRJ' },
              { namePrefix: 'Panda' },   { namePrefix: 'thermal' },
              { namePrefix: '80BT' },    { namePrefix: 'THERMAL' },
            ],
            optionalServices: [
              '000018f0-0000-1000-8000-00805f9b34fb',
              'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
              '49535343-fe7d-4ae5-8fa9-9fafd205e455',
            ],
          });

          const server  = await device.gatt.connect();
          let   service = null;

          const serviceUUIDs = [
            '000018f0-0000-1000-8000-00805f9b34fb',
            'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
            '49535343-fe7d-4ae5-8fa9-9fafd205e455',
          ];

          for (const uuid of serviceUUIDs) {
            try   { service = await server.getPrimaryService(uuid); break; }
            catch { /* try next */ }
          }

          if (!service) throw new Error('Service printer BLE tidak ditemukan. Pastikan printer mendukung BLE.');

          const characteristics = await service.getCharacteristics();
          // Ambil characteristic yang bisa write
          const writable = characteristics.find(c =>
            c.properties.write || c.properties.writeWithoutResponse
          );

          if (!writable) throw new Error('Write characteristic tidak ditemukan pada printer.');

          this.btDevice         = device;
          this.btCharacteristic = writable;
          this.btDeviceName     = device.name || 'Unknown Printer';
          this.btConnected      = true;

          device.addEventListener('gattserverdisconnected', () => {
            this.btConnected      = false;
            this.btCharacteristic = null;
            this.btDevice         = null;
          });

        } catch (e) {
          if (e.name !== 'NotFoundError') {
            this.btError = 'Gagal menghubungkan: ' + e.message;
          }
        } finally {
          this.btConnecting = false;
        }
      },

      disconnectBluetooth() {
        if (this.btDevice && this.btDevice.gatt.connected) {
          this.btDevice.gatt.disconnect();
        }
        this.btConnected      = false;
        this.btCharacteristic = null;
        this.btDevice         = null;
      },

      async writeToCharacteristic(data) {
        const CHUNK = 256;

        // Temukan akhir blok raster QR (GS v 0 = 1D 76 30 00) untuk drain delay
        let rasterEndIdx = -1;
        for (let i = 0; i < data.length - 7; i++) {
          if (data[i]===0x1D && data[i+1]===0x76 && data[i+2]===0x30 && data[i+3]===0x00) {
            const wBytes = data[i+4] + (data[i+5] << 8);
            const rows   = data[i+6] + (data[i+7] << 8);
            rasterEndIdx = i + 8 + wBytes * rows;
            break;
          }
        }

        for (let i = 0; i < data.length; i += CHUNK) {
          const chunk = data.slice(i, i + CHUNK);
          // writeValue (with response) lebih reliable untuk printer SPP
          await this.btCharacteristic.writeValue(chunk);
          await new Promise(r => setTimeout(r, 20));
          // Drain 500ms tepat setelah seluruh raster terkirim
          if (rasterEndIdx > 0 && i < rasterEndIdx && (i + CHUNK) >= rasterEndIdx) {
            await new Promise(r => setTimeout(r, 500));
          }
        }
      },

      async doPrint() {
        if (!this.btConnected || !this.btCharacteristic || !this.printItem) return;

        this.printing = true;
        this.btError  = null;

        try {
          const sj    = this.printItem;
          const bytes = this.buildEscPosData(sj);
          await this.writeToCharacteristic(bytes);

          // Mark printed di server
          const res  = await fetch(_routeMarkPrinted, {
            method : 'POST',
            headers: {
              'Content-Type'    : 'application/json',
              'X-CSRF-TOKEN'    : document.querySelector('meta[name="csrf-token"]').content,
              'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ suratjalanno: sj.suratjalanno }),
          });
          const data = await res.json();

          if (data.success) {
            this.printSuccess = true;
            setTimeout(() => { this.showPrint = false; window.location.reload(); }, 1500);
          } else {
            this.btError = 'Print berhasil, tapi gagal update status: ' + data.message;
          }
        } catch (e) {
          this.btError = 'Gagal mencetak: ' + e.message;
        } finally {
          this.printing = false;
        }
      },

      buildEscPosData(sj) {
        const enc = new TextEncoder();

        // ESC/POS helper
        const bytes   = [];
        const addBytes = b => b.forEach(v => bytes.push(v));
        const addText  = t => enc.encode(t).forEach(v => bytes.push(v));
        const LF       = 0x0A;
        const CR       = 0x0D;
        const CRLF     = () => { bytes.push(CR); bytes.push(LF); };

        const ESC_INIT     = [0x1B, 0x40];
        const ALIGN_LEFT   = [0x1B, 0x61, 0x00];
        const ALIGN_CENTER = [0x1B, 0x61, 0x01];
        const BOLD_ON      = [0x1B, 0x45, 0x01];
        const BOLD_OFF     = [0x1B, 0x45, 0x00];
        const SIZE_2X      = [0x1D, 0x21, 0x22];
        const SIZE_NORMAL  = [0x1D, 0x21, 0x00];
        const CUT          = [0x1D, 0x56, 0x00];
        const SEP          = '--------------------------------';

        const yesNo = v => v == 1 ? 'Ya' : 'Tidak';
        const label = (l, v) => {
          const line = (l + ' ').padEnd(16, ' ') + ': ' + (v ?? '-');
          addText(line); CRLF();
        };

        // Init
        addBytes(ESC_INIT);
        addBytes(ALIGN_CENTER);
        addBytes(BOLD_ON); addBytes(SIZE_2X);
        addText('SURAT JALAN'); bytes.push(LF);
        addBytes(SIZE_NORMAL); addBytes(BOLD_OFF);
        bytes.push(LF);

        // NON-NFC marker
        addBytes(BOLD_ON);
        addText('[NON-NFC / WEB INPUT]'); bytes.push(LF);
        addBytes(BOLD_OFF);
        bytes.push(LF);

        // Nomor & Polisi
        addBytes(BOLD_ON);
        addText('Nomor :'); bytes.push(LF);
        addText(sj.suratjalanno ?? '-'); bytes.push(LF);
        bytes.push(LF);
        addText('No. Polisi :'); bytes.push(LF);
        addText(sj.nomorpolisi ?? '-'); bytes.push(LF);
        addBytes(BOLD_OFF);
        bytes.push(LF);

        // QR Code (GS v 0 raster — required for Panda PRJ-R80B)
        try {
          addBytes(ALIGN_CENTER);
          addBytes(this.buildQRRaster(this.buildQRData(sj)));
          // Post-raster recovery — sama seperti softFlushAfterGraphic() di Android
          bytes.push(0x0A);                          // LF — release print head
          addBytes([0x1B, 0x64, 0x01]);              // ESC d 1 — force head advance
          addBytes([0x1B, 0x32]);                    // restore default line spacing
          addBytes([0x1D, 0x21, 0x00]);              // GS ! 0 — normal char size
          addBytes([0x1B, 0x21, 0x00]);              // ESC ! 0 — cancel double-size
          addBytes(ALIGN_CENTER); addBytes(BOLD_OFF);
          addText('Scan untuk detail lengkap'); bytes.push(LF);
          bytes.push(LF);
        } catch(e) { console.warn('QR failed', e); bytes.push(LF); }

        // Separator
        addBytes(ALIGN_LEFT);
        addText(SEP); CRLF();
        bytes.push(LF);

        // Detail
        addBytes(ALIGN_CENTER); addBytes(BOLD_ON);
        addText('DETAIL DATA'); bytes.push(LF);
        addBytes(BOLD_OFF); addBytes(ALIGN_LEFT);
        bytes.push(LF);

        label('Mandor',        sj.mandorid);
        label('Plot',          sj.plot);
        label('Varietas',      sj.varietas);
        label('Kategori',      sj.kategori);
        label('Umur',          sj.umur ? sj.umur + ' bulan' : null);
        label('Kode Tebang',   sj.kodetebang);
        label('Langsir',       yesNo(sj.langsir));
        label('Tebu Sulit',    yesNo(sj.tebusulit));
        label('Kend. Kontr.',  yesNo(sj.kendaraankontraktor));
        label('Muat GL',       yesNo(sj.muatgl));
        label('Tgl Tebang',    sj.tanggaltebang ? sj.tanggaltebang.split('T')[0] : null);
        label('Tgl Angkut',    sj.tanggalangkut ? sj.tanggalangkut.split('T')[0] : null);
        label('No Kendaraan',  sj.nomorkendaraan);
        label('No Polisi',     sj.nomorpolisi);
        label('Nama Supir',    sj.namasupir);
        label('Kontraktor',    sj.namakontraktor);
        label('Sub Kontr.',    sj.namasubkontraktor);
        label('Dibuat Oleh',   sj.nonnfc_createdby);

        bytes.push(LF);
        addText(SEP); CRLF();
        bytes.push(LF);

        // Footer
        addBytes(ALIGN_CENTER);
        addText('DiPrint: ' + new Date().toLocaleString('id-ID')); bytes.push(LF);
        addText('(Web - Non NFC)'); bytes.push(LF);

        // Feed & cut
        bytes.push(LF); bytes.push(LF); bytes.push(LF);
        addBytes(CUT);

        return new Uint8Array(bytes);
      },

      openPreview() {
        const sj = this.printItem;
        if (!sj) return;

        const esc = t => String(t ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        const W = 32;
        const center = t => { const s=String(t??''); const pad=Math.max(0,Math.floor((W-s.length)/2)); return ' '.repeat(pad)+s; };
        const sep = '-'.repeat(W);
        const label = (l,v) => (l+' ').padEnd(16,' ')+': '+(v??'-');
        const yesNo = v => v==1?'Ya':'Tidak';
        const ln = (lines, t) => lines.push(esc(t??''));

        // Before-QR section
        const before = [];
        ln(before, center('SURAT JALAN'));
        ln(before, '');
        ln(before, center('[NON-NFC / WEB INPUT]'));
        ln(before, '');
        ln(before, center('Nomor :'));
        ln(before, center(sj.suratjalanno??'-'));
        ln(before, '');
        ln(before, center('No. Polisi :'));
        ln(before, center(sj.nomorpolisi??'-'));
        ln(before, '');
        this.previewHtml = before.join('\n');

        // After-QR section
        const after = [];
        ln(after, center('Scan untuk detail lengkap'));
        ln(after, '');
        ln(after, sep);
        ln(after, '');
        ln(after, center('DETAIL DATA'));
        ln(after, '');
        ln(after, label('Mandor',       sj.mandorid));
        ln(after, label('Plot',         sj.plot));
        ln(after, label('Varietas',     sj.varietas));
        ln(after, label('Kategori',     sj.kategori));
        ln(after, label('Umur',         sj.umur ? sj.umur+' bulan' : null));
        ln(after, label('Kode Tebang',  sj.kodetebang));
        ln(after, label('Langsir',      yesNo(sj.langsir)));
        ln(after, label('Tebu Sulit',   yesNo(sj.tebusulit)));
        ln(after, label('Kend. Kontr.', yesNo(sj.kendaraankontraktor)));
        ln(after, label('Muat GL',      yesNo(sj.muatgl)));
        ln(after, label('Tgl Tebang',   sj.tanggaltebang ? sj.tanggaltebang.split('T')[0] : null));
        ln(after, label('Tgl Angkut',   sj.tanggalangkut ? sj.tanggalangkut.split('T')[0] : null));
        ln(after, label('No Kendaraan', sj.nomorkendaraan));
        ln(after, label('No Polisi',    sj.nomorpolisi));
        ln(after, label('Nama Supir',   sj.namasupir));
        ln(after, label('Kontraktor',   sj.namakontraktor));
        ln(after, label('Sub Kontr.',   sj.namasubkontraktor));
        ln(after, label('Dibuat Oleh',  sj.nonnfc_createdby));
        ln(after, '');
        ln(after, sep);
        ln(after, '');
        ln(after, center('DiPrint: '+new Date().toLocaleString('id-ID')));
        ln(after, center('(Web - Non NFC)'));
        this.previewHtmlAfterQr = after.join('\n');

        this.showPreview = true;

        // Draw QR on canvas after modal renders
        this.$nextTick(() => {
          try {
            const canvas = document.getElementById('qrPreviewCanvas');
            if (!canvas || typeof qrcode === 'undefined') return;
            const qr = qrcode(0, 'M');
            qr.addData(this.buildQRData(sj));
            qr.make();
            const mc = qr.getModuleCount();
            const quiet = 4;
            const total = mc + quiet * 2;
            const scale = Math.floor(160 / total) || 1;
            canvas.width  = total * scale;
            canvas.height = total * scale;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#000000';
            for (let r = 0; r < mc; r++) {
              for (let c = 0; c < mc; c++) {
                if (qr.isDark(r, c)) {
                  ctx.fillRect((c + quiet) * scale, (r + quiet) * scale, scale, scale);
                }
              }
            }
          } catch(e) { console.warn('QR preview draw failed', e); }
        });
      },

      buildQRData(sj) {
        return [
          _companycode,
          sj.suratjalanno   ?? '',
          sj.plot           ?? '',
          sj.varietas       ?? '',
          sj.kategori       ?? '',
          sj.umur           ?? '',
          sj.kodetebang     ?? '',
          sj.langsir        ?? '0',
          sj.tebusulit      ?? '0',
          sj.kendaraankontraktor ?? '0',
          sj.muatgl         ?? '0',
          sj.tanggaltebang  ?? '',
          sj.tanggalangkut  ?? '',
          sj.nomorkendaraan ?? '',
          sj.nomorpolisi    ?? '',
          sj.namasupir      ?? '',
          sj.namakontraktor ?? '',
          sj.namasubkontraktor ?? '',
          sj.mandorid       ?? '',
        ].join('::');
      },

      buildQRRaster(data) {
        const qr = qrcode(0, 'M');
        qr.addData(data);
        qr.make();
        const mc         = qr.getModuleCount();
        const quiet      = 4;
        const total      = mc + quiet * 2;
        // Adaptive scale: max 320px (safe margin for 80mm printer any firmware), min 3px/module
        const scale      = Math.max(3, Math.min(7, Math.floor(320 / total)));
        const totalPx    = total * scale;
        const widthBytes = Math.ceil(totalPx / 8);
        const raster     = new Uint8Array(widthBytes * totalPx);

        for (let py = 0; py < totalPx; py++) {
          const mr = Math.floor(py / scale) - quiet;
          for (let bIdx = 0; bIdx < widthBytes; bIdx++) {
            let byte = 0;
            for (let bit = 0; bit < 8; bit++) {
              const px  = bIdx * 8 + bit;
              const mc2 = Math.floor(px / scale) - quiet;
              const dark = (mr >= 0 && mr < mc && mc2 >= 0 && mc2 < mc && px < totalPx)
                           ? qr.isDark(mr, mc2) : false;
              byte = (byte << 1) | (dark ? 1 : 0);
            }
            raster[py * widthBytes + bIdx] = byte;
          }
        }

        const xL = widthBytes & 0xFF, xH = (widthBytes >> 8) & 0xFF;
        const yL = totalPx   & 0xFF, yH = (totalPx   >> 8) & 0xFF;
        const header = new Uint8Array([0x1D,0x76,0x30,0x00,xL,xH,yL,yH]);
        const out = new Uint8Array(header.length + raster.length);
        out.set(header); out.set(raster, header.length);
        return out;
      },
    };
  }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
</x-layout>
