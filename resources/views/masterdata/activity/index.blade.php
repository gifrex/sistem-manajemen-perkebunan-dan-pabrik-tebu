<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div
        x-data="{
            open: @json($errors->any()),
            mode: 'create',
            form: {
                grupaktivitas: '',
                kodeaktivitas: '',
                namaaktivitas: '',
                namaaktivitas2: '',
                keterangan: '',
                jenistenagakerja: '1',
                material: '0',
                vehicle: '0',
                isblokactivity: '0',
                active: '1',
                accno: '',
                variables: [{ var: '', satuan: '' }]
            },
            resetForm() {
                this.mode = 'create';
                this.form = {
                    grupaktivitas: '',
                    kodeaktivitas: '',
                    namaaktivitas: '',
                    namaaktivitas2: '',
                    keterangan: '',
                    jenistenagakerja: '1',
                    material: '0',
                    vehicle: '0',
                    isblokactivity: '0',
                    active: '1',
                    accno: '',
                    variables: [{ var: '', satuan: '' }]
                };
                this.open = true;
            },
            addVariable() {
                if (this.form.variables.length < 5) {
                    this.form.variables.push({ var: '', satuan: '' });
                }
            },
            removeVariable(index) {
                this.form.variables.splice(index, 1);
            },
            editActivity(a) {
                this.mode = 'edit';
                this.form.grupaktivitas = a.activitygroup || '';
                this.form.kodeaktivitas = a.activitycode || '';
                this.form.namaaktivitas = a.activityname || '';
                this.form.namaaktivitas2 = a.activityname2 || '';
                this.form.keterangan = a.description || '';
                this.form.jenistenagakerja = String(a.jenistenagakerja ?? '1');
                this.form.material = String(a.usingmaterial ?? '0');
                this.form.vehicle = String(a.usingvehicle ?? '0');
                this.form.isblokactivity = String(a.isblokactivity ?? '0');
                this.form.active = String(a.active ?? '1');
                this.form.accno = a.accno || '';

                let vars = [];
                for (let i = 1; i <= 5; i++) {
                    if (a['var' + i]) {
                        vars.push({ var: a['var' + i], satuan: a['satuan' + i] || '' });
                    }
                }
                this.form.variables = vars.length ? vars : [{ var: '', satuan: '' }];
                this.open = true;
            },
            deleteActivity(code) {
                if (!confirm('Yakin ingin menghapus data ini?')) return;
                fetch('{{ url('masterdata/aktivitas') }}/' + code, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                })
                .then(r => r.json())
                .then(d => { if (d.success) location.reload(); else alert(d.message); })
                .catch(() => alert('Terjadi kesalahan'));
            }
        }"
        class="mx-auto py-1 bg-white shadow-md rounded-md"
    >

        {{-- Toolbar --}}
        <div class="flex items-center justify-between px-4 py-2">
            @can('masterdata.aktivitas.create')
                <button @click="resetForm()"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5" />
                    </svg>
                    New Data
                </button>
            @endcan

            <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                <label for="search" class="text-xs font-medium text-gray-700">Search:</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    placeholder="Kode, Nama, atau Group"
                    class="text-xs mt-1 block w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    onkeydown="if(event.key==='Enter') this.form.submit()">
            </form>

            <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                <label for="perPage" class="text-xs font-medium text-gray-700">Items per page:</label>
                <select name="perPage" id="perPage" onchange="this.form.submit()"
                    class="text-xs mt-1 block w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="10" {{ (int)request('perPage', $perPage) === 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ (int)request('perPage', $perPage) === 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ (int)request('perPage', $perPage) === 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ (int)request('perPage', $perPage) === 100 ? 'selected' : '' }}>100</option>
                </select>
            </form>
        </div>

        {{-- Table --}}
        <div class="mx-auto px-4 py-2">
            <div class="overflow-x-auto rounded-md border border-gray-300">
                <table class="min-w-full bg-white text-sm text-center">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700">
                            <th class="py-2 px-3 border-b">No.</th>
                            <th class="py-2 px-3 border-b">Group</th>
                            <th class="py-2 px-3 border-b">Kode</th>
                            <th class="py-2 px-3 border-b">Nama</th>
                            <th class="py-2 px-3 border-b">Nama 2</th>
                            <th class="py-2 px-3 border-b">Var 1</th>
                            <th class="py-2 px-3 border-b">Var 2</th>
                            <th class="py-2 px-3 border-b">Var 3</th>
                            <th class="py-2 px-3 border-b">Var 4</th>
                            <th class="py-2 px-3 border-b">Var 5</th>
                            <th class="py-2 px-3 border-b">Material</th>
                            <th class="py-2 px-3 border-b">Vehicle</th>
                            <th class="py-2 px-3 border-b">Jenis TK</th>
                            <th class="py-2 px-3 border-b">Blok Act.</th>
                            <th class="py-2 px-3 border-b">Acc No</th>
                            <th class="py-2 px-3 border-b">Status</th>
                            <th class="py-2 px-3 border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $index => $a)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-3 border-b">{{ $activities->firstItem() + $index }}</td>
                                <td class="py-2 px-3 border-b text-left">{{ $a->activitygroup }} - {{ $a->group->groupname ?? '-' }}</td>
                                <td class="py-2 px-3 border-b font-medium">{{ $a->activitycode }}</td>
                                <td class="py-2 px-3 border-b text-left">{{ $a->activityname }}</td>
                                <td class="py-2 px-3 border-b text-left">{{ $a->activityname2 ?? '-' }}</td>
                                <td class="py-2 px-3 border-b">{{ $a->var1 ? $a->var1 . ' (' . $a->satuan1 . ')' : '-' }}</td>
                                <td class="py-2 px-3 border-b">{{ $a->var2 ? $a->var2 . ' (' . $a->satuan2 . ')' : '-' }}</td>
                                <td class="py-2 px-3 border-b">{{ $a->var3 ? $a->var3 . ' (' . $a->satuan3 . ')' : '-' }}</td>
                                <td class="py-2 px-3 border-b">{{ $a->var4 ? $a->var4 . ' (' . $a->satuan4 . ')' : '-' }}</td>
                                <td class="py-2 px-3 border-b">{{ $a->var5 ? $a->var5 . ' (' . $a->satuan5 . ')' : '-' }}</td>
                                <td class="py-2 px-3 border-b">{{ $a->usingmaterial ? 'YA' : 'TIDAK' }}</td>
                                <td class="py-2 px-3 border-b">{{ $a->usingvehicle ? 'YA' : 'TIDAK' }}</td>
                                <td class="py-2 px-3 border-b">{{ $a->jenistenagakerja == 1 ? 'HARIAN' : 'BORONGAN' }}</td>
                                <td class="py-2 px-3 border-b">
                                    @if($a->isblokactivity)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">Per Blok</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">Per Plot</span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 border-b">{{ $a->accno ?? '-' }}</td>
                                <td class="py-2 px-3 border-b">
                                    @if($a->active)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 border-b">
                                    <div class="flex items-center justify-center space-x-2">
                                        @can('masterdata.aktivitas.edit')
                                            <button @click="editActivity({{ Js::from($a) }})"
                                                class="group flex items-center">
                                                <svg class="w-6 h-6 text-blue-500 group-hover:hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <use xlink:href="#icon-edit-outline" />
                                                </svg>
                                                <svg class="w-6 h-6 text-blue-500 hidden group-hover:block" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                    <use xlink:href="#icon-edit-solid" />
                                                    <use xlink:href="#icon-edit-solid2" />
                                                </svg>
                                            </button>
                                        @endcan
                                        @can('masterdata.aktivitas.delete')
                                            <button @click="deleteActivity('{{ $a->activitycode }}')"
                                                class="group flex items-center">
                                                <svg class="w-6 h-6 text-red-500 group-hover:hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <use xlink:href="#icon-trash-outline" />
                                                </svg>
                                                <svg class="w-6 h-6 text-red-500 hidden group-hover:block" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                    <use xlink:href="#icon-trash-solid" />
                                                </svg>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="17" class="py-4 text-center text-gray-500">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mx-4 my-1">
            {{ $activities->links() }}
        </div>

        {{-- Modal --}}
        <div x-show="open" x-cloak class="relative z-10" role="dialog" aria-modal="true">
            <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-500/75"></div>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="open"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                        class="relative transform rounded-lg bg-white text-left shadow-xl w-full max-w-4xl">

                        <form method="POST"
                            :action="mode === 'edit'
                                ? '{{ url('masterdata/aktivitas') }}/' + form.kodeaktivitas
                                : '{{ route('masterdata.aktivitas.store') }}'"
                            class="px-6 pt-4 pb-6 space-y-4">
                            @csrf
                            <template x-if="mode === 'edit'">
                                <input type="hidden" name="_method" value="PUT">
                            </template>

                            {{-- Header --}}
                            <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                                <h3 class="text-lg font-semibold text-gray-900"
                                    x-text="mode === 'edit' ? 'Edit Aktivitas' : 'Tambah Aktivitas'"></h3>
                                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Left Column --}}
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Grup Aktivitas <span class="text-red-500">*</span></label>
                                        <select name="grupaktivitas" x-model="form.grupaktivitas" required
                                            class="text-sm block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">-- Pilih Group --</option>
                                            @foreach($activityGroups as $g)
                                                <option value="{{ $g->activitygroup }}">{{ $g->activitygroup }} - {{ $g->groupname }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Kode Aktivitas <span class="text-red-500">*</span></label>
                                        <input type="text" name="kodeaktivitas" x-model="form.kodeaktivitas"
                                            @input="form.kodeaktivitas = form.kodeaktivitas.toUpperCase()"
                                            :readonly="mode === 'edit'"
                                            :class="mode === 'edit' ? 'bg-gray-100' : ''"
                                            maxlength="50" required
                                            class="text-sm block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 uppercase">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Nama Aktivitas <span class="text-red-500">*</span></label>
                                        <input type="text" name="namaaktivitas" x-model="form.namaaktivitas" required
                                            class="text-sm block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Nama Aktivitas 2</label>
                                        <input type="text" name="namaaktivitas2" x-model="form.namaaktivitas2" maxlength="150"
                                            class="text-sm block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Keterangan</label>
                                        <textarea name="keterangan" x-model="form.keterangan" rows="2" maxlength="150"
                                            class="text-sm block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Acc No</label>
                                        <input type="text" name="accno" x-model="form.accno" maxlength="25"
                                            class="text-sm block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Material? <span class="text-red-500">*</span></label>
                                            <div class="flex gap-3">
                                                <label class="inline-flex items-center text-sm">
                                                    <input type="radio" name="material" value="1" x-model="form.material" class="mr-1"> Ya
                                                </label>
                                                <label class="inline-flex items-center text-sm">
                                                    <input type="radio" name="material" value="0" x-model="form.material" class="mr-1"> Tidak
                                                </label>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Kendaraan? <span class="text-red-500">*</span></label>
                                            <div class="flex gap-3">
                                                <label class="inline-flex items-center text-sm">
                                                    <input type="radio" name="vehicle" value="1" x-model="form.vehicle" class="mr-1"> Ya
                                                </label>
                                                <label class="inline-flex items-center text-sm">
                                                    <input type="radio" name="vehicle" value="0" x-model="form.vehicle" class="mr-1"> Tidak
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Jenis TK <span class="text-red-500">*</span></label>
                                            <div class="flex gap-3">
                                                <label class="inline-flex items-center text-sm">
                                                    <input type="radio" name="jenistenagakerja" value="1" x-model="form.jenistenagakerja" class="mr-1"> Harian
                                                </label>
                                                <label class="inline-flex items-center text-sm">
                                                    <input type="radio" name="jenistenagakerja" value="2" x-model="form.jenistenagakerja" class="mr-1"> Borongan
                                                </label>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Tipe Activity <span class="text-red-500">*</span></label>
                                            <div class="flex gap-3">
                                                <label class="inline-flex items-center text-sm">
                                                    <input type="radio" name="isblokactivity" value="0" x-model="form.isblokactivity" class="mr-1"> Per Plot
                                                </label>
                                                <label class="inline-flex items-center text-sm">
                                                    <input type="radio" name="isblokactivity" value="1" x-model="form.isblokactivity" class="mr-1"> Per Blok
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                                        <select name="active" x-model="form.active"
                                            class="text-sm block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Right Column - Variables --}}
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-xs font-medium text-gray-700">Hasil Aktivitas (Max 5)</label>
                                        <button type="button" @click="addVariable()"
                                            x-show="form.variables.length < 5"
                                            class="text-xs bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Tambah
                                        </button>
                                    </div>

                                    <div class="space-y-2 max-h-96 overflow-y-auto pr-2">
                                        <template x-for="(v, i) in form.variables" :key="i">
                                            <div class="flex gap-2 items-start">
                                                <div class="flex-1">
                                                    <label class="block text-xs text-gray-600 mb-1">
                                                        Var <span x-text="i + 1"></span>
                                                    </label>
                                                    <input type="text" :name="'var[]'" x-model="v.var" required
                                                        class="text-sm block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                                <div class="flex-1">
                                                    <label class="block text-xs text-gray-600 mb-1">Satuan</label>
                                                    <input type="text" :name="'satuan[]'" x-model="v.satuan" required
                                                        class="text-sm block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                                <div class="pt-5">
                                                    <button type="button" @click="removeVariable(i)"
                                                        x-show="form.variables.length > 1"
                                                        class="text-red-500 hover:text-red-700 p-1">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <p class="mt-2 text-xs text-gray-500"
                                       x-text="form.variables.length + ' / 5 variable'"></p>
                                </div>
                            </div>

                            {{-- Footer --}}
                            <div class="flex justify-end gap-2 pt-4 border-t border-gray-200">
                                <button type="button" @click="open = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
                                    x-text="mode === 'edit' ? 'Update' : 'Create'">
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Toast --}}
        @if(session('success'))
            <div x-data x-init="alert('{{ session('success') }}')"></div>
        @endif
        @if($errors->any())
            <div x-data x-init="alert('{{ $errors->first() }}')"></div>
        @endif
    </div>
</x-layout>