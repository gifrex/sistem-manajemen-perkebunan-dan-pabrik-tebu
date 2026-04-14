{{--resources\views\masterdata\upah\index.blade.php--}}

<x-layout>
    <x-slot:title>Upah</x-slot:title>
    <x-slot:navbar>Master Data</x-slot:navbar>
    <x-slot:nav>Upah</x-slot:nav>

    <link rel="stylesheet" href="{{ asset('asset/font-awesome-6.5.1-all.min.css') }}">

    <div x-data="{
        open: @json($errors->any()),
        mode: 'create',
        editUrl: '',
        originalId: '',
        form: { 
            activitygroup: '', 
            wagetype: '',
            amount: '', 
            effectivedate: '',
            enddate: '',
            parameter: ''
        },
        resetForm() {
            this.mode = 'create';
            this.editUrl = '';
            this.originalId = '';
            this.form = { 
                activitygroup: '', 
                wagetype: '',
                amount: '', 
                effectivedate: '',
                enddate: '',
                parameter: ''
            };
            this.open = true;
        },
        editForm(data, url) {
            this.mode = 'edit';
            this.editUrl = url;
            this.originalId = data.id;
            this.form = {
                activitygroup: data.activitygroup,
                wagetype: data.wagetype,
                amount: data.amount,
                effectivedate: data.effectivedate,
                enddate: data.enddate || '',
                parameter: data.parameter || ''
            };
            this.open = true;
        }
    }" class="mx-auto py-1 bg-white rounded-md shadow-md">

        <!-- Header Section with Controls -->
        <div class="flex items-center justify-between px-4 py-2">

            <!-- New Data Button -->
            <button @click="resetForm()"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2 transition-colors duration-200">
                <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5" />
                </svg>
                New Data
            </button>

            <!-- Filters -->
            <div class="flex items-center gap-2">
                <!-- Filter by Group -->
                <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="wagetype" value="{{ request('wagetype') }}">
                    <input type="hidden" name="perPage" value="{{ request('perPage', $perPage) }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <label for="activitygroup_filter" class="text-xs font-medium text-gray-700">Grup:</label>
                    <select name="activitygroup" id="activitygroup_filter"
                        onchange="this.form.submit()"
                        class="text-xs w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                        <option value="">Semua</option>
                        @foreach($activityGroups as $group)
                        <option value="{{ $group->activitygroup }}" {{ request('activitygroup') === $group->activitygroup ? 'selected' : '' }}>{{ $group->activitygroup }}</option>
                        @endforeach
                    </select>
                </form>

                <!-- Filter by Wage Type -->
                <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="activitygroup" value="{{ request('activitygroup') }}">
                    <input type="hidden" name="perPage" value="{{ request('perPage', $perPage) }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <label for="wagetype_filter" class="text-xs font-medium text-gray-700">Jenis:</label>
                    <select name="wagetype" id="wagetype_filter"
                        onchange="this.form.submit()"
                        class="text-xs w-32 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                        <option value="">Semua</option>
                        @foreach($wageTypes as $key => $label)
                        <option value="{{ $key }}" {{ request('wagetype') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>

                <!-- Filter by Status -->
                <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="activitygroup" value="{{ request('activitygroup') }}">
                    <input type="hidden" name="wagetype" value="{{ request('wagetype') }}">
                    <input type="hidden" name="perPage" value="{{ request('perPage', $perPage) }}">
                    <label for="status_filter" class="text-xs font-medium text-gray-700">Status:</label>
                    <select name="status" id="status_filter"
                        onchange="this.form.submit()"
                        class="text-xs w-24 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                        <option value="">Semua</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </form>

                <!-- Search Form -->
                <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                    <input type="hidden" name="activitygroup" value="{{ request('activitygroup') }}">
                    <input type="hidden" name="wagetype" value="{{ request('wagetype') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="perPage" value="{{ request('perPage', $perPage) }}">
                    <label for="search" class="text-xs font-medium text-gray-700">Search:</label>
                    <input type="text" name="search" id="search"
                        value="{{ request('search') }}"
                        placeholder="Cari..."
                        class="text-xs w-48 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                        onkeydown="if(event.key==='Enter') this.form.submit()" />
                </form>

                <!-- Per Page Form -->
                <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="activitygroup" value="{{ request('activitygroup') }}">
                    <input type="hidden" name="wagetype" value="{{ request('wagetype') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <label for="perPage" class="text-xs font-medium text-gray-700">Items per page:</label>
                    <select name="perPage" id="perPage"
                        onchange="this.form.submit()"
                        class="text-xs w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                        <option value="10" {{ (int)request('perPage', $perPage) === 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ (int)request('perPage', $perPage) === 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ (int)request('perPage', $perPage) === 50 ? 'selected' : '' }}>50</option>
                    </select>
                </form>
            </div>

        </div>

        <!-- Success/Error Alerts -->
        @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition
            class="mx-4 mb-2 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            <strong class="font-bold">Berhasil!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-green-200 rounded"
                @click="show = false">&times;</span>
        </div>
        @endif

        @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition
            class="mx-4 mb-2 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <strong class="font-bold">Gagal!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-red-200 rounded"
                @click="show = false">&times;</span>
        </div>
        @endif

        @if ($errors->any())
        <div x-data="{ show: true }" x-show="show" x-transition
            class="mx-4 mb-2 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <strong class="font-bold">Error!</strong>
            <ul class="mt-2 ml-4 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-red-200 rounded"
                @click="show = false">&times;</span>
        </div>
        @endif

        <!-- Table Section -->
        <div class="mx-auto px-4 py-2">
            <div class="overflow-x-auto border border-gray-300 rounded-md">
                <table class="min-w-full bg-white text-sm text-center">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700">
                            <th class="py-2 px-4 border-b">Grup</th>
                            <th class="py-2 px-4 border-b">Jenis Upah</th>
                            <th class="py-2 px-4 border-b">Nominal</th>
                            <th class="py-2 px-4 border-b">Parameter</th>
                            <th class="py-2 px-4 border-b">Tgl Mulai</th>
                            <th class="py-2 px-4 border-b">Tgl Berakhir</th>
                            <th class="py-2 px-4 border-b">Status</th>
                            <th class="py-2 px-4 border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $d)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-2 px-4 border-b font-medium text-gray-900">
                                <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">
                                    {{ $d->activitygroup }}
                                </span>
                            </td>
                            <td class="py-2 px-4 border-b text-gray-700">
                                {{ $wageTypes[$d->wagetype] ?? $d->wagetype }}
                            </td>
                            <td class="py-2 px-4 border-b text-gray-700">
                                <div class="font-semibold text-green-700">Rp {{ number_format($d->amount, 2, ',', '.') }}</div>
                            </td>
                            <td class="py-2 px-4 border-b text-gray-700">
                                @if($d->parameter)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ $d->parameter }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="py-2 px-4 border-b text-gray-700">
                                <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ date('d-m-Y', strtotime($d->effectivedate)) }}</code>
                            </td>
                            <td class="py-2 px-4 border-b text-gray-700">
                                @if($d->enddate)
                                    <code class="bg-red-100 px-2 py-1 rounded text-xs text-red-800">{{ date('d-m-Y', strtotime($d->enddate)) }}</code>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-2 px-4 border-b text-center">
                                @if(!$d->enddate || strtotime($d->enddate) >= strtotime(date('Y-m-d')))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        Expired
                                    </span>
                                @endif
                            </td>
                            <td class="py-2 px-4 border-b">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Edit Button -->
                                    <button
                                        @click='editForm({
                                            id: "{{ $d->id }}",
                                            activitygroup: "{{ $d->activitygroup }}",
                                            wagetype: "{{ $d->wagetype }}",
                                            amount: "{{ $d->amount }}",
                                            effectivedate: "{{ $d->effectivedate }}",
                                            enddate: "{{ $d->enddate }}",
                                            parameter: "{{ $d->parameter }}"
                                        }, "{{ route('masterdata.upah.update', $d->id) }}")'
                                        class="group flex items-center text-blue-600 hover:text-blue-800 focus:ring-2 focus:ring-blue-500 rounded-md px-2 py-1 text-sm">
                                        <svg class="w-6 h-6 text-blue-500 dark:text-white group-hover:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <use xlink:href="#icon-edit-outline" />
                                        </svg>
                                        <svg class="w-6 h-6 text-blue-500 dark:text-white hidden group-hover:block" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                            <use xlink:href="#icon-edit-solid" />
                                            <use xlink:href="#icon-edit-solid2" />
                                        </svg>
                                        <span class="w-0.5"></span>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('masterdata.upah.destroy', $d->id) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus data upah {{ $d->activitygroup }} - {{ $wageTypes[$d->wagetype] ?? $d->wagetype }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="group flex items-center text-red-600 hover:text-red-800 focus:ring-2 focus:ring-red-500 rounded-md px-2 py-1 text-sm">
                                            <svg class="w-6 h-6 text-red-500 dark:text-white group-hover:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <use xlink:href="#icon-trash-outline" />
                                            </svg>
                                            <svg class="w-6 h-6 text-red-500 dark:text-white hidden group-hover:block" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                                <use xlink:href="#icon-trash-solid" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="mt-2">Tidak ada data upah</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mx-4 my-1">
                @if ($data->hasPages())
                    {{ $data->appends(request()->query())->links() }}
                @else
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium">{{ $data->count() }}</span> of <span class="font-medium">{{ $data->total() }}</span> results
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Modal -->
        <div x-show="open" x-cloak class="relative" style="z-index: 9999;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-500/75" aria-hidden="true"></div>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="open"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg">

                        <!-- Modal Body -->
                        <form :action="mode === 'create' ? '{{ route('masterdata.upah.store') }}' : editUrl" method="POST"
                            class="bg-white px-4 pt-2 pb-4 sm:p-6 sm:pt-1 sm:pb-4 space-y-6">
                            @csrf
                            <template x-if="mode === 'edit'">
                                <input type="hidden" name="_method" value="PUT">
                            </template>

                            <div class="text-center sm:text-left">
                                <h3 class="text-lg font-medium text-gray-900" id="modal-title" 
                                    x-text="mode === 'create' ? 'Tambah Data Upah' : 'Edit Data Upah'">
                                </h3>

                                <div class="mt-4 space-y-4">
                                    <!-- Grup Aktivitas -->
                                    <div>
                                        <label for="activitygroup" class="block text-sm font-medium text-gray-700">
                                            Grup Aktivitas <span class="text-red-500">*</span>
                                        </label>
                                        <select id="activitygroup" name="activitygroup" x-model="form.activitygroup" required
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">-- Pilih Grup Aktivitas --</option>
                                            @foreach($activityGroups as $group)
                                            <option value="{{ $group->activitygroup }}">{{ $group->activitygroup }} - {{ $group->groupname }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Jenis Upah -->
                                    <div>
                                        <label for="wagetype" class="block text-sm font-medium text-gray-700">
                                            Jenis Upah <span class="text-red-500">*</span>
                                        </label>
                                        <select id="wagetype" name="wagetype" x-model="form.wagetype" required
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">-- Pilih Jenis Upah --</option>
                                            @foreach($wageTypes as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Nominal -->
                                    <div>
                                        <label for="amount" class="block text-sm font-medium text-gray-700">
                                            Nominal <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative mt-1">
                                            <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                                            <input type="number" id="amount" name="amount" x-model="form.amount" required
                                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                                placeholder="0.00" min="0" step="0.01" 
                                                oninput="this.value = this.value.match(/^\d+\.?\d{0,2}/) ? this.value : this.value.slice(0, -1)" />
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">Format: gunakan titik untuk desimal (contoh: 50000.50)</p>
                                    </div>

                                    <!-- Parameter -->
                                    <div>
                                        <label for="parameter" class="block text-sm font-medium text-gray-700">
                                            Parameter
                                        </label>
                                        <select id="parameter" name="parameter" x-model="form.parameter"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">-- Pilih Parameter (Opsional) --</option>
                                            <option value="MAINTENANCE">MAINTENANCE</option>
                                            <option value="HARVEST">HARVEST</option>
                                            <option value="TRANSPORT">TRANSPORT</option>
                                        </select>
                                        <p class="mt-1 text-xs text-gray-500">Parameter untuk membedakan upah pada activity group yang sama</p>
                                    </div>

                                    <!-- Tanggal Mulai Berlaku -->
                                    <div>
                                        <label for="effectivedate" class="block text-sm font-medium text-gray-700">
                                            Tanggal Mulai Berlaku <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" id="effectivedate" name="effectivedate" x-model="form.effectivedate" required
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                                    </div>

                                    <!-- Tanggal Berakhir -->
                                    <div>
                                        <label for="enddate" class="block text-sm font-medium text-gray-700">
                                            Tanggal Berakhir
                                        </label>
                                        <input type="date" id="enddate" name="enddate" x-model="form.enddate"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                                        <p class="mt-1 text-xs text-gray-500">Kosongkan jika upah masih berlaku sampai sekarang</p>
                                    </div>

                                </div>
                            </div>

                            <!-- Modal Actions -->
                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="submit"
                                    class="inline-flex w-full justify-center rounded-md bg-blue-600 px-4 py-2 text-white text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto"
                                    x-text="mode === 'create' ? 'Simpan' : 'Update'">
                                </button>
                                <button @click.prevent="open = false" type="button"
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-gray-700 text-sm font-medium shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</x-layout>