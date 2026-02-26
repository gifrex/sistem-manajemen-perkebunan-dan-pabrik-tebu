<!-- resources\views\usermanagement\permission\index.blade.php -->
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded-md">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="flex-1 text-sm font-medium text-green-800">{{ session('success') }}</p>
            <button @click="show = false" class="text-green-400 hover:text-green-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    @endif

    @if (session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded-md">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="flex-1 text-sm font-medium text-red-800">{{ session('error') }}</p>
            <button @click="show = false" class="text-red-400 hover:text-red-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    @endif

    <div x-data="{
        open: false,
        mode: 'create',
        editUrl: '',
        selectedModules: @js(request('modules', [])),
        selectedModule: '',
        customModule: '',
        form: {
            id: '',
            module: '',
            resource: '',
            action: '',
            displayname: '',
            description: '',
            isactive: 1
        },
        resetForm() {
            this.mode = 'create';
            this.editUrl = '';
            this.selectedModule = '';
            this.customModule = '';
            this.form = {
                id: '',
                module: '',
                resource: '',
                action: '',
                displayname: '',
                description: '',
                isactive: 1
            };
            this.open = true;
        },
        editForm(data, url) {
            this.mode = 'edit';
            this.editUrl = url;
            this.customModule = '';
            this.selectedModule = data.module;
            this.form = {
                id: data.id,
                module: data.module,
                resource: data.resource,
                action: data.action,
                displayname: data.displayname,
                description: data.description,
                isactive: data.isactive
            };
            this.open = true;
        },
        onModuleChange(val) {
            this.selectedModule = val;
            if (val !== '__custom__') {
                this.form.module = val;
                this.customModule = '';
            } else {
                this.form.module = '';
            }
        },
        onCustomModuleInput(val) {
            this.customModule = val;
            this.form.module = val;
        },
        toggleModule(module) {
            const idx = this.selectedModules.indexOf(module);
            if (idx > -1) this.selectedModules.splice(idx, 1);
            else this.selectedModules.push(module);
        },
        isModuleSelected(module) {
            return this.selectedModules.includes(module);
        },
        applyFilter() {
            const url = new URL(window.location.href);
            if (this.selectedModules.length > 0) url.searchParams.set('modules', this.selectedModules.join(','));
            else url.searchParams.delete('modules');
            window.location.href = url.toString();
        },
        clearAllFilters() {
            window.location.href = '{{ route('usermanagement.permission.index') }}';
        }
    }" class="bg-white">

        {{-- Page Header --}}
        <div class="border-b border-gray-200 bg-white px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Permissions</h1>
                    <p class="mt-1 text-sm text-gray-600">Manage system permissions ({{ $result->total() }} total)</p>
                </div>
                @can('usermanagement.permission.create')
                <button @click="resetForm()"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="hidden sm:inline">Tambah Permission</span>
                    <span class="sm:hidden">Tambah</span>
                </button>
                @endcan
            </div>
        </div>

        {{-- Filters Bar --}}
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                {{-- Search --}}
                <div class="flex-1 max-w-lg">
                    <form method="GET" action="{{ url()->current() }}">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Search permissions..."
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500 text-sm"
                                   onkeydown="if(event.key==='Enter') this.form.submit()">
                            @if(request('modules'))
                                <input type="hidden" name="modules" value="{{ request('modules') }}">
                            @endif
                            @if(request('perPage'))
                                <input type="hidden" name="perPage" value="{{ request('perPage') }}">
                            @endif
                        </div>
                    </form>
                </div>

                <div class="flex items-center gap-3">

                    {{-- Module Filter --}}
                    <div x-data="{ dropdownOpen: false }" class="relative">
                        <button @click="dropdownOpen = !dropdownOpen" type="button"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            <span>Module</span>
                            <template x-if="selectedModules.length > 0">
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" x-text="selectedModules.length"></span>
                            </template>
                            <svg class="ml-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="dropdownOpen" @click.away="dropdownOpen = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="origin-top-right absolute right-0 mt-2 w-72 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50"
                             x-cloak>
                            <div class="p-3">
                                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Filter by module</div>
                                <div class="space-y-2 max-h-64 overflow-y-auto">
                                    @foreach($modules as $module)
                                    <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox"
                                               :checked="isModuleSelected('{{ $module }}')"
                                               @change="toggleModule('{{ $module }}')"
                                               class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                                        <span class="ml-3 text-sm text-gray-700">{{ $module }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                <div class="mt-3 pt-3 border-t border-gray-200 flex gap-2">
                                    <button @click="applyFilter(); dropdownOpen = false"
                                            class="flex-1 bg-orange-600 text-white px-3 py-2 text-sm rounded-md hover:bg-orange-700 transition-colors">
                                        Apply
                                    </button>
                                    <button @click="selectedModules = []; applyFilter(); dropdownOpen = false"
                                            class="px-3 py-2 text-sm text-gray-700 hover:text-gray-900 transition-colors">
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Clear Filters --}}
                    @if(request('search') || request('modules'))
                    <button @click="clearAllFilters()"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Clear filters
                    </button>
                    @endif

                    {{-- Per Page --}}
                    <form method="GET" action="{{ url()->current() }}">
                        <select name="perPage" onchange="this.form.submit()"
                                class="block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 rounded-md">
                            <option value="20" {{ ($perPage ?? 20) == 20 ? 'selected' : '' }}>20 / page</option>
                            <option value="50" {{ ($perPage ?? 20) == 50 ? 'selected' : '' }}>50 / page</option>
                            <option value="100" {{ ($perPage ?? 20) == 100 ? 'selected' : '' }}>100 / page</option>
                        </select>
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('modules'))
                            <input type="hidden" name="modules" value="{{ request('modules') }}">
                        @endif
                    </form>
                </div>
            </div>

            {{-- Active Filters --}}
            @if(request('search') || request('modules'))
            <div class="mt-3 flex items-center gap-2 flex-wrap">
                <span class="text-xs font-medium text-gray-500">Active filters:</span>
                @if(request('search'))
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Search: "{{ request('search') }}"
                    <a href="{{ route('usermanagement.permission.index', array_filter(['modules' => request('modules'), 'perPage' => request('perPage')])) }}"
                       class="ml-1 inline-flex items-center text-blue-600 hover:text-blue-800">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                </span>
                @endif
                @if(request('modules'))
                    @foreach(explode(',', request('modules')) as $mod)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        Module: {{ $mod }}
                    </span>
                    @endforeach
                @endif
            </div>
            @endif
        </div>

        {{-- Table --}}
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permission Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Display Name / Description</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($result as $permission)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <code class="text-sm font-mono text-gray-900">
                                {{ $permission->module }}.{{ $permission->resource }}.{{ $permission->action }}
                            </code>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $permission->displayname }}</div>
                            @if($permission->description)
                            <div class="text-xs text-gray-500 mt-1">{{ $permission->description }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-2">
                                @can('usermanagement.permission.edit')
                                <button @click='editForm({
                                        id: {{ $permission->id }},
                                        module: {{ json_encode($permission->module) }},
                                        resource: {{ json_encode($permission->resource) }},
                                        action: {{ json_encode($permission->action) }},
                                        displayname: {{ json_encode($permission->displayname) }},
                                        description: {{ json_encode($permission->description) }},
                                        isactive: {{ $permission->isactive ?? 0 }}
                                    }, {{ json_encode(route('usermanagement.permission.update', $permission->id)) }})'
                                    class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md p-2 transition-all duration-150"
                                    title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @endcan

                                @can('usermanagement.permission.delete')
                                <form action="{{ route('usermanagement.permission.destroy', $permission->id) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Yakin ingin menghapus permission {{ $permission->displayname }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md p-2 transition-all duration-150"
                                        title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data permission</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if(request('search') || request('modules'))
                                    Tidak ada hasil untuk filter yang diterapkan
                                @else
                                    Belum ada permission yang terdaftar
                                @endif
                            </p>
                            @if(request('search') || request('modules'))
                            <div class="mt-4">
                                <button @click="clearAllFilters()" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                    Clear all filters
                                </button>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($result->hasPages())
        <div class="bg-white px-6 py-4 border-t border-gray-200">
            {{ $result->appends(request()->query())->links() }}
        </div>
        @else
        <div class="bg-white px-6 py-4 border-t border-gray-200">
            <div class="text-sm text-gray-700">
                Showing <span class="font-medium">{{ $result->count() }}</span> of <span class="font-medium">{{ $result->total() }}</span> results
            </div>
        </div>
        @endif

        {{-- Modal --}}
        <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" x-cloak @keydown.window.escape="open = false">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

                <div x-show="open"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                     @click="open = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="open"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

                    <form :action="mode === 'create' ? '{{ route('usermanagement.permission.store') }}' : editUrl" method="POST">
                        @csrf
                        <template x-if="mode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        {{-- Hidden input untuk submit nilai module yang benar --}}
                        <input type="hidden" name="module" :value="form.module">

                        {{-- Modal Header --}}
                        <div class="bg-white px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900"
                                    x-text="mode === 'create' ? 'Create permission' : 'Edit permission'"></h3>
                                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Modal Body --}}
                        <div class="bg-white px-6 py-5 space-y-5">

                            {{-- Display Name --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Display name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="displayname" x-model="form.displayname"
                                       placeholder="e.g., Create User, Edit Company, View Dashboard"
                                       required maxlength="100"
                                       class="shadow-sm focus:ring-orange-500 focus:border-orange-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                <p class="mt-1 text-xs text-gray-500">A human-readable name for this permission</p>
                            </div>

                            {{-- Module / Resource / Action --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                                {{-- Module --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Module <span class="text-red-500">*</span>
                                    </label>
                                    <select :value="selectedModule"
                                            @change="onModuleChange($event.target.value)"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        <option value="">Select...</option>
                                        @foreach($modules as $mod)
                                        <option value="{{ $mod }}">{{ $mod }}</option>
                                        @endforeach
                                        <option value="__custom__">+ Custom</option>
                                    </select>
                                    {{-- Custom input, hanya muncul kalau pilih custom --}}
                                    <div x-show="selectedModule === '__custom__'" x-transition class="mt-2">
                                        <input type="text"
                                               :value="customModule"
                                               @input="onCustomModuleInput($event.target.value.toLowerCase()); $event.target.value = $event.target.value.toLowerCase()"
                                               placeholder="Enter module name"
                                               maxlength="30"
                                               class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">e.g., masterdata, input, report</p>
                                </div>

                                {{-- Resource --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Resource <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="resource"
                                           :value="form.resource"
                                           @input="form.resource = $event.target.value.toLowerCase(); $event.target.value = form.resource"
                                           placeholder="e.g., user, company"
                                           required maxlength="50"
                                           class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <p class="mt-1 text-xs text-gray-500">What entity</p>
                                </div>

                                {{-- Action --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Action <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="action"
                                           :value="form.action"
                                           @input="form.action = $event.target.value.toLowerCase(); $event.target.value = form.action"
                                           placeholder="e.g., view, create"
                                           required maxlength="30"
                                           class="shadow-sm focus:ring-orange-500 focus:border-orange-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <p class="mt-1 text-xs text-gray-500">What action</p>
                                </div>
                            </div>

                            {{-- Permission Preview --}}
                            <div class="bg-gray-50 rounded-md p-3 border border-gray-200">
                                <div class="text-xs font-medium text-gray-500 mb-1">Permission identifier:</div>
                                <code class="text-sm font-mono text-gray-900">
                                    <span x-text="form.module || '[module]'"></span>.<span x-text="form.resource || '[resource]'"></span>.<span x-text="form.action || '[action]'"></span>
                                </code>
                            </div>

                            {{-- Description --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="description" x-model="form.description" rows="3"
                                          placeholder="Detailed description of what this permission allows users to do..."
                                          class="shadow-sm focus:ring-orange-500 focus:border-orange-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                <p class="mt-1 text-xs text-gray-500">Explain what this permission grants access to</p>
                            </div>

                            {{-- Active --}}
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="isactive"
                                           :checked="form.isactive == 1"
                                           value="1"
                                           class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label class="font-medium text-gray-700">Active</label>
                                    <p class="text-gray-500">Inactive permissions will not appear in assignment lists</p>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3">
                            <button type="submit"
                                class="inline-flex justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                x-text="mode === 'create' ? 'Simpan' : 'Perbarui'">
                            </button>
                            <button type="button" @click="open = false"
                                    class="inline-flex justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>

</x-layout>