{{--resources\views\masterdata\upah-borongan\index.blade.php--}}

<x-layout>
    <x-slot:title>Upah Borongan</x-slot:title>
    <x-slot:navbar>Upah Borongan Navbar</x-slot:navbar>
    <x-slot:nav>Upah Borongan Navigation</x-slot:nav>

    <link rel="stylesheet" href="{{ asset('asset/font-awesome-6.5.1-all.min.css') }}">

    <!-- Success Alert -->
    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Berhasil!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-green-200 rounded"
            @click="show = false">&times;</span>
    </div>
    @endif

    <!-- Error Alert -->
    @if (session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Gagal!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-red-200 rounded"
            @click="show = false">&times;</span>
    </div>
    @endif

    <!-- Validation Errors -->
    @if ($errors->any())
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
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

    <div x-data="{
        open: false,
        mode: 'create',
        editUrl: '',
        originalId: '',
        selectedGroup: '',
        currentWage: null,
        loadingActivities: false,
        form: { 
            activitygroup: '',
            activitycode: '', 
            amount: '', 
            effectivedate: '',
            enddate: ''
        },
        filteredActivities: [],
        resetForm() {
            this.mode = 'create';
            this.editUrl = '';
            this.originalId = '';
            this.selectedGroup = '';
            this.currentWage = null;
            this.loadingActivities = false;
            this.form = { 
                activitygroup: '',
                activitycode: '', 
                amount: '', 
                effectivedate: '',
                enddate: ''
            };
            this.filteredActivities = [];
            this.open = true;
        },
        async editForm(data, url) {
            this.mode = 'edit';
            this.editUrl = url;
            this.originalId = data.id;
            this.selectedGroup = data.activitygroup;
            this.form = {
                activitygroup: data.activitygroup,
                activitycode: data.activitycode,
                amount: data.amount,
                effectivedate: data.effectivedate,
                enddate: data.enddate || ''
            };
            await this.loadActivitiesByGroup(data.activitygroup);
            this.open = true;
        },
        async loadActivitiesByGroup(group) {
            if (!group) {
                this.filteredActivities = [];
                return;
            }
            
            this.loadingActivities = true;
            try {
                const response = await fetch('{{ route('masterdata.upah-borongan.activities-by-group') }}?activitygroup=' + encodeURIComponent(group));
                const data = await response.json();
                this.filteredActivities = data;
                this.selectedGroup = group;
            } catch (error) {
                console.error('Error loading activities:', error);
                this.filteredActivities = [];
                alert('Gagal memuat data aktivitas. Silakan coba lagi.');
            } finally {
                this.loadingActivities = false;
            }
        },
        async loadCurrentWage() {
            if (!this.form.activitycode) {
                this.currentWage = null;
                return;
            }
            
            try {
                const response = await fetch('{{ route('masterdata.upah-borongan.current-wage') }}?activitycode=' + encodeURIComponent(this.form.activitycode));
                const data = await response.json();
                this.currentWage = data;
            } catch (error) {
                console.error('Error loading current wage:', error);
                this.currentWage = null;
            }
        }
    }" class="mx-auto py-4 bg-white rounded-md shadow-md">

        <!-- Header Section with Controls -->
        <div class="px-4 py-4 border-b border-gray-200">
            <div class="flex flex-col space-y-4 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">

                <!-- New Data Button -->
                <div class="flex justify-start">
                    <button @click="resetForm()"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2 transition-colors duration-200">
                        <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5" />
                        </svg>
                        <span class="hidden sm:inline">Tambah Upah Borongan</span>
                        <span class="sm:hidden">Tambah</span>
                    </button>
                </div>

                <!-- Search and Filters -->
                <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                    <!-- Filter by Group -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="perPage" value="{{ request('perPage', $perPage) }}">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <label for="activitygroup_filter" class="text-xs font-medium text-gray-700 whitespace-nowrap">Grup:</label>
                        <select name="activitygroup" id="activitygroup_filter"
                            onchange="this.form.submit()"
                            class="text-xs w-32 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                            <option value="">Semua</option>
                            @foreach($activityGroups as $group)
                            <option value="{{ $group }}" {{ request('activitygroup') === $group ? 'selected' : '' }}>{{ $group }}</option>
                            @endforeach
                        </select>
                    </form>

                    <!-- Filter by Status -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="perPage" value="{{ request('perPage', $perPage) }}">
                        <input type="hidden" name="activitygroup" value="{{ request('activitygroup') }}">
                        <label for="status_filter" class="text-xs font-medium text-gray-700 whitespace-nowrap">Status:</label>
                        <select name="status" id="status_filter"
                            onchange="this.form.submit()"
                            class="text-xs w-28 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                            <option value="">Semua</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </form>

                    <!-- Search Form -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <input type="hidden" name="activitygroup" value="{{ request('activitygroup') }}">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <input type="hidden" name="perPage" value="{{ request('perPage', $perPage) }}">
                        <label for="search" class="text-xs font-medium text-gray-700 whitespace-nowrap">Search:</label>
                        <input type="text" name="search" id="search"
                            value="{{ request('search') }}"
                            placeholder="Cari aktivitas..."
                            class="text-xs w-full sm:w-48 md:w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                            onkeydown="if(event.key==='Enter') this.form.submit()" />
                        <button type="submit" class="sm:hidden bg-blue-500 text-white px-3 py-2 rounded-md hover:bg-blue-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </form>

                    <!-- Per Page Form -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="activitygroup" value="{{ request('activitygroup') }}">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <label for="perPage" class="text-xs font-medium text-gray-700 whitespace-nowrap">Per page:</label>
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
        </div>

        <!-- Table Section -->
        <div class="px-4 py-4">
            <div class="overflow-x-auto rounded-md border border-gray-300">
                <table class="min-w-full bg-white text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="py-3 px-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
                                Grup
                            </th>
                            <th class="py-3 px-2 sm:px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kode Aktivitas
                            </th>
                            <th class="py-3 px-2 sm:px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nama Aktivitas
                            </th>
                            <th class="py-3 px-2 sm:px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nominal
                            </th>
                            <th class="py-3 px-2 sm:px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                                Tgl Mulai
                            </th>
                            <th class="py-3 px-2 sm:px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                                Tgl Berakhir
                            </th>
                            <th class="py-3 px-2 sm:px-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="py-3 px-2 sm:px-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($data as $d)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-3 px-2 text-sm font-medium text-gray-900 text-center w-16">
                                <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">
                                    {{ $d->activitygroup }}
                                </span>
                            </td>
                            <td class="py-3 px-2 sm:px-4 text-sm">
                                <code class="bg-gray-100 px-2 py-1 rounded text-xs font-mono">{{ $d->activitycode }}</code>
                            </td>
                            <td class="py-3 px-2 sm:px-4 text-sm text-gray-700">
                                <div class="font-medium">{{ $d->activityname }}</div>
                            </td>
                            <td class="py-3 px-2 sm:px-4 text-sm text-gray-700">
                                <div class="font-semibold text-green-700">Rp {{ number_format($d->amount, 2, ',', '.') }}</div>
                            </td>
                            <td class="py-3 px-2 sm:px-4 text-sm text-gray-700 hidden lg:table-cell">
                                <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ date('d-m-Y', strtotime($d->effectivedate)) }}</code>
                            </td>
                            <td class="py-3 px-2 sm:px-4 text-sm text-gray-700 hidden lg:table-cell">
                                @if($d->enddate)
                                    <code class="bg-red-100 px-2 py-1 rounded text-xs text-red-800">{{ date('d-m-Y', strtotime($d->enddate)) }}</code>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-2 sm:px-4 text-center">
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
                            <td class="py-3 px-2 sm:px-4">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Edit Button -->
                                    <button
                                        @click='editForm({
                                            id: "{{ $d->id }}",
                                            activitygroup: "{{ $d->activitygroup }}",
                                            activitycode: "{{ $d->activitycode }}",
                                            amount: "{{ $d->amount }}",
                                            effectivedate: "{{ $d->effectivedate }}",
                                            enddate: "{{ $d->enddate }}"
                                        }, "{{ route('masterdata.upah-borongan.update', $d->id) }}")'
                                        class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md p-2 transition-all duration-150"
                                        title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="w-4 h-4 fill-current">
                                            <path d="M441 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152L0 424c0 48.6 39.4 88 88 88l272 0c48.6 0 88-39.4 88-88l0-112c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 112c0 22.1-17.9 40-40 40L88 464c-22.1 0-40-17.9-40-40l0-272c0-22.1 17.9-40 40-40l112 0c13.3 0 24-10.7 24-24s-10.7-24-24-24L88 64z" />
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('masterdata.upah-borongan.destroy', $d->id) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus upah borongan untuk {{ $d->activityname }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md p-2 transition-all duration-150"
                                            title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="w-4 h-4 fill-current">
                                                <path d="M135.2 17.7L128 32 32 32C14.3 32 0 46.3 0 64S14.3 96 32 96l384 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0-7.2-14.3C307.4 6.8 296.3 0 284.2 0L163.8 0c-12.1 0-23.2 6.8-28.6 17.7zM416 128L32 128 53.2 467c1.6 25.3 22.6 45 47.9 45l245.8 0c25.3 0 46.3-19.7 47.9-45L416 128z" />
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
                                <p class="mt-2">Tidak ada data upah borongan</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $data->appends(request()->query())->links() }}
            </div>
        </div>

        <!-- Modal -->
        <div x-show="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" x-cloak
            @keydown.window.escape="open = false">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-auto max-h-[90vh] overflow-y-auto">

                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900" x-text="mode === 'create' ? 'Tambah Upah Borongan' : 'Edit Upah Borongan'"></h2>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <form :action="mode === 'create' ? '{{ route('masterdata.upah-borongan.store') }}' : editUrl" method="POST">
                        @csrf
                        <template x-if="mode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <!-- Grup Aktivitas -->
                        <div class="mb-4">
                            <label for="activitygroup" class="block text-sm font-medium text-gray-700 mb-2">
                                Grup Aktivitas <span class="text-red-500">*</span>
                            </label>
                            <select id="activitygroup" x-model="form.activitygroup" 
                                @change="loadActivitiesByGroup($event.target.value); form.activitycode = ''; currentWage = null;"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">-- Pilih Grup Aktivitas --</option>
                                @foreach($activityGroups as $group)
                                <option value="{{ $group }}">{{ $group }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Aktivitas (Cascading) -->
                        <div class="mb-4">
                            <label for="activitycode" class="block text-sm font-medium text-gray-700 mb-2">
                                Aktivitas <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select id="activitycode" name="activitycode" x-model="form.activitycode" 
                                    @change="loadCurrentWage()"
                                    required
                                    :disabled="!form.activitygroup || loadingActivities"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed">
                                    <option value="">-- Pilih Aktivitas --</option>
                                    <template x-for="activity in filteredActivities" :key="activity.activitycode">
                                        <option :value="activity.activitycode" x-text="activity.activitycode + ' - ' + activity.activityname"></option>
                                    </template>
                                </select>
                                <div x-show="loadingActivities" class="absolute right-3 top-3">
                                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Pilih grup aktivitas terlebih dahulu</p>
                        </div>

                        <!-- Current Wage Info (jika ada) -->
                        <div x-show="currentWage && mode === 'create'" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-blue-800">Upah Aktif Saat Ini:</p>
                                    <p class="text-sm text-blue-700 mt-1">
                                        <span class="font-semibold">Rp <span x-text="currentWage ? new Intl.NumberFormat('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(currentWage.amount) : '-'"></span></span>
                                        <span class="text-xs ml-2">
                                            (berlaku sejak <span x-text="currentWage ? new Date(currentWage.effectivedate).toLocaleDateString('id-ID') : '-'"></span>)
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Nominal -->
                        <div class="mb-4">
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Nominal <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                                <input type="number" id="amount" name="amount" x-model="form.amount" required
                                    class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="0.00" min="0" step="0.01"
                                    oninput="this.value = this.value.match(/^\d+\.?\d{0,2}/) ? this.value : this.value.slice(0, -1)" />
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Format: gunakan titik untuk desimal (contoh: 50000.50)</p>
                        </div>

                        <!-- Tanggal Mulai Berlaku -->
                        <div class="mb-4">
                            <label for="effectivedate" class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Mulai Berlaku <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="effectivedate" name="effectivedate" x-model="form.effectivedate" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        </div>

                        <!-- Tanggal Berakhir -->
                        <div class="mb-6">
                            <label for="enddate" class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Berakhir
                            </label>
                            <input type="date" id="enddate" name="enddate" x-model="form.enddate"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            <p class="mt-1 text-xs text-gray-500">Kosongkan jika upah masih berlaku sampai sekarang</p>
                        </div>

                        <!-- Modal Actions -->
                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0">
                            <button type="button" @click="open = false"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                Batal
                            </button>
                            <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                <span x-text="mode === 'create' ? 'Simpan' : 'Update'"></span>
                            </button>
                        </div>
                    </form>
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