{{-- resources/views/transaction/kendaraan-supply/index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

<div x-data="supplyData()" class="relative">
<div class="mx-auto bg-white rounded-md shadow-md p-6">

    {{-- ================================================================ --}}
    {{-- HEADER --}}
    {{-- ================================================================ --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Surat Jalan Kendaraan Supply</h1>
            <p class="text-gray-600 mt-1">Pencatatan kendaraan supply / angkut material ke plot</p>
        </div>
        <button @click="openCreateModal()"
                class="mt-4 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded">
            + Buat Surat Jalan
        </button>
    </div>

    {{-- ================================================================ --}}
    {{-- FILTER --}}
    {{-- ================================================================ --}}
    <div class="mb-6">
        <form class="flex flex-wrap items-center gap-2"
              action="{{ route('transaction.kendaraan-supply.index') }}" method="GET">
            <input type="text" name="search" value="{{ $search ?? '' }}"
                   placeholder="Cari SJS No, Kendaraan, Tujuan..."
                   class="text-sm border border-gray-300 rounded px-3 py-2"/>
            <input type="date" name="filter_date" value="{{ $filterDate ?? '' }}"
                   id="filter_date" {{ $showAllDate ? 'disabled' : '' }}
                   class="text-sm border border-gray-300 rounded px-3 py-2 disabled:bg-gray-100"/>
            <label class="flex items-center text-sm space-x-1 cursor-pointer">
                <input type="checkbox" name="show_all_date" value="1"
                       onchange="document.getElementById('filter_date').disabled=this.checked; this.form.submit();"
                       {{ $showAllDate ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600"/>
                <span class="text-gray-700 whitespace-nowrap">All Date</span>
            </label>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded">Cari</button>
        </form>
    </div>

    {{-- ================================================================ --}}
    {{-- STATS --}}
    {{-- ================================================================ --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        <div class="bg-gray-50 border rounded-lg p-3 text-center">
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
            <div class="text-xs text-gray-500">Total SJS</div>
        </div>
        <div class="bg-yellow-50 border border-yellow-100 rounded-lg p-3 text-center">
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['draft'] }}</div>
            <div class="text-xs text-gray-500">Draft</div>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-lg p-3 text-center">
            <div class="text-2xl font-bold text-green-600">{{ $stats['submitted'] }}</div>
            <div class="text-xs text-gray-500">Submitted</div>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ $stats['stock'] }}</div>
            <div class="text-xs text-gray-500">Sumber Stock</div>
        </div>
        <div class="bg-orange-50 border border-orange-100 rounded-lg p-3 text-center">
            <div class="text-2xl font-bold text-orange-600">{{ $stats['spbu'] }}</div>
            <div class="text-xs text-gray-500">Sumber SPBU</div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- TABLE --}}
    {{-- ================================================================ --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SJS No</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kendaraan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Operator</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tujuan / Aktivitas</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Rit</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Jam</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">BBM</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($supplyData as $item)
                <tr class="hover:bg-gray-50 {{ $item->status === 'SUBMITTED' ? 'bg-green-50/30' : '' }}">
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $loop->iteration }}</td>

                    {{-- SJS No --}}
                    <td class="px-4 py-3 text-sm font-mono font-medium text-blue-700">
                        {{ $item->sjsno }}
                    </td>

                    {{-- Tanggal --}}
                    <td class="px-4 py-3 text-sm">
                        {{ \Carbon\Carbon::parse($item->sjsdate)->format('d/m/Y') }}
                    </td>

                    {{-- Kendaraan --}}
                    <td class="px-4 py-3 text-sm">
                        <div class="font-medium">{{ $item->nokendaraan }}</div>
                        <div class="text-xs text-gray-400">{{ $item->jenis }}</div>
                    </td>

                    {{-- Operator --}}
                    <td class="px-4 py-3 text-sm">
                        <div>{{ $item->operator_nama ?? '-' }}</div>
                        @if($item->helper_nama)
                            <div class="text-xs text-gray-400">Helper: {{ $item->helper_nama }}</div>
                        @endif
                    </td>

                    {{-- Tujuan / Aktivitas --}}
                    <td class="px-4 py-3 text-sm max-w-[180px]">
                        <div class="truncate font-medium" title="{{ $item->tujuan }}">{{ $item->tujuan }}</div>
                        <div class="truncate text-xs text-gray-400" title="{{ $item->keteranganaktivitas }}">
                            {{ $item->keteranganaktivitas }}
                        </div>
                    </td>

                    {{-- Rit --}}
                    <td class="px-4 py-3 text-sm text-center font-bold">{{ $item->jumlahrit }}</td>

                    {{-- Jam --}}
                    <td class="px-4 py-3 text-sm text-center">
                        @if($item->jammulai && $item->jamselesai)
                            <div class="text-xs">{{ substr($item->jammulai, 0, 5) }}</div>
                            <div class="text-xs text-gray-400">{{ substr($item->jamselesai, 0, 5) }}</div>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>

                    {{-- Sumber BBM --}}
                    <td class="px-4 py-3 text-sm text-center">
                        @if($item->sumberbbm === 'STOCK')
                            <span class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full font-medium">Stock</span>
                        @else
                            <span class="text-xs px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full font-medium">SPBU</span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="px-4 py-3 text-sm text-center">
                        @if($item->status === 'SUBMITTED')
                            <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium">
                                ✓ Submitted
                            </span>
                            @if($item->submittedat)
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($item->submittedat)->format('d/m H:i') }}
                                </div>
                            @endif
                        @elseif($item->sumberbbm === 'SPBU')
                            {{-- SPBU tidak perlu submit --}}
                            <span class="text-xs text-gray-400">Tidak perlu</span>
                        @else
                            <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full font-medium">Draft</span>
                        @endif
                    </td>

                    {{-- Aksi --}}
                    <td class="px-4 py-3 text-sm text-center">
                        <div class="flex items-center justify-center gap-1">
                            @if($item->status === 'DRAFT')
                                {{-- Edit --}}
                                <button @click="openEditModal(@js($item))"
                                        class="p-1 text-blue-600 hover:text-blue-800" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                {{-- Hapus --}}
                                <button @click="deleteItem({{ $item->id }})"
                                        class="p-1 text-red-600 hover:text-red-800" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                                {{-- Submit (hanya STOCK) --}}
                                @if($item->sumberbbm === 'STOCK')
                                    <button @click="submitSjs({{ $item->id }}, '{{ $item->sjsno }}')"
                                            class="px-2 py-1 text-xs bg-green-600 hover:bg-green-700 text-white rounded"
                                            title="Submit ke antrian Order BBM">
                                        Submit
                                    </button>
                                @endif
                            @else
                                {{-- Sudah SUBMITTED: semua aksi dikunci --}}
                                <span class="text-gray-300 text-xs">Terkunci</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="px-4 py-8 text-center text-gray-400">
                        Tidak ada data surat jalan supply
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if(method_exists($supplyData, 'links'))
        <div class="bg-white px-4 py-3 border-t border-gray-200">
            {{ $supplyData->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ================================================================ --}}
{{-- MODAL CREATE / EDIT --}}
{{-- ================================================================ --}}
<div x-show="showModal" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
     @keydown.escape.window="closeModal()">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col" @click.stop>

        {{-- Modal Header --}}
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900"
                x-text="isEdit ? 'Edit Surat Jalan' : 'Buat Surat Jalan Baru'"></h3>
            <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
        </div>

        {{-- Modal Body --}}
        <div class="overflow-y-auto flex-1 px-6 py-4 space-y-4">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" x-model="form.sjsdate" required :disabled="isEdit"
                           class="w-full px-3 py-2 border border-gray-300 rounded text-sm disabled:bg-gray-100"/>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sumber BBM <span class="text-red-500">*</span></label>
                    <select x-model="form.sumberbbm" required
                            class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                        <option value="STOCK">Stock Kantor</option>
                        <option value="SPBU">SPBU</option>
                    </select>
                </div>
            </div>

            {{-- Info SPBU --}}
            <div x-show="form.sumberbbm === 'SPBU'"
                 class="p-3 bg-orange-50 border border-orange-200 rounded text-xs text-orange-700">
                Kendaraan isi di SPBU — hanya catatan operasional, <strong>tidak masuk</strong> ke Order Pengeluaran BBM.
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Kendaraan <span class="text-red-500">*</span></label>
                <select x-model="form.nokendaraan" required
                        @change="onKendaraanChange()"
                        class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                    <option value="">-- Pilih --</option>
                    <template x-for="k in kendaraanList" :key="k.nokendaraan">
                        <option :value="k.nokendaraan" x-text="k.nokendaraan + ' (' + k.jenis + ')'"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Operator</label>
                <input type="text" 
                    :value="form.operatorid 
                        ? form.operatorid + ' — ' + selectedOperatorNama 
                        : (form.nokendaraan ? '(Belum ada operator)' : '')" 
                    readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded text-sm bg-gray-100 text-gray-700"/>
                <p class="text-xs text-gray-400 mt-0.5">Otomatis dari master kendaraan</p>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tujuan <span class="text-red-500">*</span></label>
                <input type="text" x-model="form.tujuan" required
                       placeholder="Plot tujuan atau lokasi"
                       class="w-full px-3 py-2 border border-gray-300 rounded text-sm"/>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan Aktivitas <span class="text-red-500">*</span></label>
                <input type="text" x-model="form.keteranganaktivitas" required
                       placeholder="Contoh: Angkut herbisida ke Blok A"
                       class="w-full px-3 py-2 border border-gray-300 rounded text-sm"/>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah Rit <span class="text-red-500">*</span></label>
                    <input type="number" x-model.number="form.jumlahrit" required min="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-center"/>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jam Mulai</label>
                    <input type="time" x-model="form.jammulai"
                           class="w-full px-3 py-2 border border-gray-300 rounded text-sm"/>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jam Selesai</label>
                    <input type="time" x-model="form.jamselesai"
                           class="w-full px-3 py-2 border border-gray-300 rounded text-sm"/>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                <textarea x-model="form.catatan" rows="2" placeholder="Opsional"
                          class="w-full px-3 py-2 border border-gray-300 rounded text-sm resize-none"></textarea>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl flex justify-end space-x-2">
            <button type="button" @click="closeModal()"
                    class="px-4 py-2 border border-gray-300 text-gray-600 rounded hover:bg-gray-100 text-sm">
                Batal
            </button>
            <button @click="submitForm()" :disabled="isLoading"
                    class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 text-sm font-medium">
                <span x-show="isLoading">Menyimpan...</span>
                <span x-show="!isLoading" x-text="isEdit ? 'Update' : 'Simpan'"></span>
            </button>
        </div>
    </div>
</div>

</div>{{-- end x-data --}}

<script>
function supplyData() {
    return {
        showModal: false,
        isEdit: false,
        isLoading: false,
        editId: null,
        kendaraanList: [],
        selectedOperatorNama: '',

        form: {
            sjsdate: '{{ now()->format("Y-m-d") }}',
            nokendaraan: '',
            operatorid: '',
            helperid: '',
            tujuan: '',
            keteranganaktivitas: '',
            jumlahrit: 1,
            jammulai: '',
            jamselesai: '',
            sumberbbm: 'STOCK',
            catatan: '',
        },

        async init() {
            try {
                const kRes = await fetch('{{ route("transaction.kendaraan-supply.kendaraan-list") }}').then(r => r.json());
                this.kendaraanList = kRes.data || [];
            } catch (e) {
                console.error('Gagal load dropdown:', e);
            }
        },

        onKendaraanChange() {
            const found = this.kendaraanList.find(k => k.nokendaraan === this.form.nokendaraan);
            if (found) {
                this.form.operatorid = found.idtenagakerja || '';
                this.selectedOperatorNama = found.operator_nama || '(Belum ada operator)';
            } else {
                this.form.operatorid = '';
                this.selectedOperatorNama = '';
            }
        },

        openCreateModal() {
            this.isEdit = false;
            this.editId = null;
            this.selectedOperatorNama = '';
            this.form = {
                sjsdate: '{{ now()->format("Y-m-d") }}',
                nokendaraan: '', operatorid: '', helperid: '',
                tujuan: '', keteranganaktivitas: '',
                jumlahrit: 1, jammulai: '', jamselesai: '',
                sumberbbm: 'STOCK', catatan: '',
            };
            this.showModal = true;
        },

        openEditModal(item) {
            this.isEdit = true;
            this.editId = item.id;
            this.form = {
                sjsdate:              item.sjsdate,
                nokendaraan:          item.nokendaraan,
                operatorid:           item.operatorid,
                helperid:             item.helperid || '',
                tujuan:               item.tujuan,
                keteranganaktivitas:  item.keteranganaktivitas,
                jumlahrit:            item.jumlahrit,
                jammulai:             item.jammulai ? item.jammulai.substring(0, 5) : '',
                jamselesai:           item.jamselesai ? item.jamselesai.substring(0, 5) : '',
                sumberbbm:            item.sumberbbm,
                catatan:              item.catatan || '',
            };
            const found = this.kendaraanList.find(k => k.nokendaraan === item.nokendaraan);
            this.selectedOperatorNama = found?.operator_nama || item.operator_nama || '-';
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
        },

        async submitForm() {
            if (!this.form.nokendaraan || !this.form.tujuan) {
                alert('Kendaraan dan Tujuan wajib diisi');
                return;
            }
            if (!this.form.operatorid) {
                alert('Kendaraan ini belum punya operator di master data. Hubungi admin.');
                return;
            }
            this.isLoading = true;
            try {
                const url = this.isEdit
                    ? `{{ url('transaction/kendaraan-supply') }}/${this.editId}`
                    : '{{ route("transaction.kendaraan-supply.store") }}';
                const method = this.isEdit ? 'PUT' : 'POST';

                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.form),
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + data.message);
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan');
            } finally {
                this.isLoading = false;
            }
        },

        async deleteItem(id) {
            if (!confirm('Yakin ingin menghapus surat jalan ini?')) return;
            try {
                const res = await fetch(`{{ url('transaction/kendaraan-supply') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + data.message);
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan');
            }
        },

        async submitSjs(id, sjsno) {
            if (!confirm(
                `Submit SJS ${sjsno} ke antrian Order BBM?\n\n` +
                `Setelah disubmit, dokumen tidak dapat diedit atau dihapus.`
            )) return;

            try {
                const res = await fetch(`{{ url('transaction/kendaraan-supply') }}/${id}/submit`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({}),
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + data.message);
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan');
            }
        },
    };
}
</script>
</x-layout>