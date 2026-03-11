{{-- resources\views\usermanagement\jabatan\index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition.opacity.duration.300ms
        x-init="setTimeout(() => show = false, 5000)"
        class="mb-5 flex items-center gap-3 bg-green-50 border-l-4 border-green-500 text-green-800 px-4 py-3 rounded-r-lg shadow-sm">
        <svg class="w-5 h-5 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span class="text-sm font-medium flex-1">{{ session('success') }}</span>
        <button @click="show = false" class="text-green-400 hover:text-green-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    @endif

    @if (session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition.opacity.duration.300ms
        class="mb-5 flex items-center gap-3 bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-r-lg shadow-sm">
        <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span class="text-sm font-medium flex-1">{{ session('error') }}</span>
        <button @click="show = false" class="text-red-400 hover:text-red-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">

        {{-- Page Header --}}
        <div class="px-6 py-5 border-b border-gray-200">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">Jabatan</h1>
                    <p class="mt-1 text-sm text-gray-500">Kelola jabatan dan default permission untuk setiap role.</p>
                </div>
                @can('usermanagement.jabatan.create')
                <button onclick="window.jabatanApp.openCreate()"
                    class="inline-flex items-center gap-2 bg-gray-900 text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Jabatan
                </button>
                @endcan
            </div>
        </div>

        {{-- Filters --}}
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-200 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" action="{{ url()->current() }}" class="relative flex-1 max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari jabatan..."
                    class="w-full pl-9 pr-8 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-400 focus:border-gray-400 bg-white"
                    onkeydown="if(event.key==='Enter') this.form.submit()">
                @if(request('search'))
                <a href="{{ route('usermanagement.jabatan.index', request()->only('perPage')) }}"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
                @endif
                @if(request('perPage'))
                <input type="hidden" name="perPage" value="{{ request('perPage') }}">
                @endif
            </form>
            <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                <label for="perPage" class="text-xs text-gray-500 whitespace-nowrap">Tampilkan</label>
                <select name="perPage" id="perPage" onchange="this.form.submit()"
                    class="text-sm border border-gray-300 rounded-lg px-2.5 py-2 bg-white focus:ring-2 focus:ring-gray-400 focus:border-gray-400">
                    @foreach([10, 20, 50] as $pp)
                    <option value="{{ $pp }}" {{ ($perPage ?? 10) == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                    @endforeach
                </select>
                <label class="text-xs text-gray-500">per halaman</label>
                @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/50">
                        <th class="py-3 px-6 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-20">ID</th>
                        <th class="py-3 px-6 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Jabatan</th>
                        <th class="py-3 px-6 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-44">Permissions</th>
                        <th class="py-3 px-6 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-56">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($result as $jabatan)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="py-3.5 px-6 text-sm text-gray-400 font-mono">{{ $jabatan->idjabatan }}</td>
                        <td class="py-3.5 px-6 text-sm font-medium text-gray-900">{{ $jabatan->namajabatan }}</td>
                        <td class="py-3.5 px-6">
                            <span class="inline-flex items-center text-xs font-medium rounded-full px-2.5 py-1 {{ $jabatan->jabatan_permissions_count > 0 ? 'text-gray-700 bg-gray-100' : 'text-gray-400 bg-gray-50' }}">
                                {{ $jabatan->jabatan_permissions_count }} assigned
                            </span>
                        </td>
                        <td class="py-3.5 px-6">
                            <div class="flex items-center justify-end gap-1">
                                <button onclick="window.jabatanApp.openPermModal({ idjabatan: {{ $jabatan->idjabatan }}, namajabatan: '{{ addslashes($jabatan->namajabatan) }}' })"
                                    class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 border border-gray-200 rounded-lg px-3 py-1.5 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Permissions
                                </button>
                                @can('usermanagement.jabatan.edit')
                                <button onclick="window.jabatanApp.openEdit({{ $jabatan->idjabatan }}, '{{ addslashes($jabatan->namajabatan) }}')"
                                    class="inline-flex items-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg p-1.5 transition-all" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @endcan
                                @can('usermanagement.jabatan.delete')
                                <form method="POST" action="{{ route('usermanagement.jabatan.destroy', $jabatan->idjabatan) }}"
                                    onsubmit="return confirm('Hapus jabatan {{ addslashes($jabatan->namajabatan) }}?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg p-1.5 transition-all" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-900">Tidak ada data jabatan</p>
                                <p class="text-xs text-gray-500 mt-1">{{ request('search') ? 'Tidak ditemukan untuk "'.request('search').'"' : 'Belum ada jabatan terdaftar.' }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 rounded-b-lg">
            @if ($result->hasPages())
                {{ $result->appends(request()->query())->links() }}
            @else
            <p class="text-xs text-gray-500">
                Menampilkan <span class="font-semibold text-gray-700">{{ $result->count() }}</span> dari
                <span class="font-semibold text-gray-700">{{ $result->total() }}</span> jabatan
            </p>
            @endif
        </div>
    </div>

    {{-- ==================== CRUD Modal ==================== --}}
    <div id="crudModal" style="display:none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 id="crudModalTitle" class="text-lg font-semibold text-gray-900">Tambah Jabatan Baru</h3>
                <p id="crudModalDesc" class="text-xs text-gray-500 mt-0.5">Buat jabatan baru untuk organisasi Anda.</p>
            </div>
            <form id="crudForm" method="POST" class="p-6">
                @csrf
                <input type="hidden" id="crudMethod" name="_method" value="POST" disabled>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Jabatan <span class="text-red-500">*</span></label>
                    <input type="text" name="namajabatan" id="crudNamaJabatan"
                        placeholder="Contoh: Manager, Supervisor, Staff"
                        class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-400 focus:border-gray-400"
                        maxlength="30" required>
                    <p class="mt-1.5 text-xs text-gray-400">Maksimal 30 karakter</p>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <button type="button" onclick="window.jabatanApp.closeCrud()"
                        class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                    <button type="submit" id="crudSubmitBtn"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition-colors shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== Permission Modal — Split Panel ==================== --}}
    <div id="permModal" style="display:none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white rounded-xl shadow-2xl w-[96vw] max-w-6xl h-[88vh] flex flex-col border border-gray-200 overflow-hidden">

            {{-- Header --}}
            <div class="flex-shrink-0 px-6 py-4 border-b border-gray-200 bg-white flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Permission Management</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Jabatan: <span id="permJabatanName" class="font-medium text-gray-700"></span></p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="hidden sm:inline-flex items-center text-xs font-medium text-gray-500 bg-gray-100 rounded-md px-2.5 py-1.5">
                        <span id="permSelectedCount">0</span>&nbsp;terpilih
                    </span>
                    <button onclick="window.jabatanApp.closePerm()" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-1.5 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Loading --}}
            <div id="permLoading" class="flex-1 flex items-center justify-center">
                <div class="inline-flex items-center gap-2 text-gray-400">
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span class="text-sm">Memuat permissions...</span>
                </div>
            </div>

            {{-- Split Panel --}}
            <div id="permBody" style="display:none;" class="flex-1 flex overflow-hidden">
                <form action="{{ route('usermanagement.jabatan.assign-permissions') }}" method="POST" id="permissionForm" class="flex flex-1 overflow-hidden">
                    @csrf
                    <input type="hidden" name="idjabatan" id="permJabatanId">

                    {{-- Sidebar --}}
                    <div class="w-52 lg:w-60 flex-shrink-0 border-r border-gray-200 bg-gray-50 flex flex-col overflow-hidden">
                        <div class="px-4 pt-4 pb-2 flex-shrink-0">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Modules</p>
                        </div>
                        <nav id="permSidebar" class="flex-1 overflow-y-auto px-2 pb-3 space-y-0.5">
                            @foreach($permissions as $module => $modulePermissions)
                            <button type="button" data-sidebar-module="{{ $module }}"
                                class="perm-sidebar-btn w-full flex items-center justify-between px-3 py-2 rounded-md text-left transition-all text-gray-600 hover:bg-gray-200/60 hover:text-gray-900">
                                <span class="text-xs font-semibold uppercase tracking-wide truncate">{{ $module }}</span>
                                <span class="perm-sidebar-count text-[10px] font-bold rounded px-1.5 py-0.5 ml-2 flex-shrink-0 tabular-nums bg-gray-200 text-gray-400"
                                    data-total="{{ $modulePermissions->count() }}">0/{{ $modulePermissions->count() }}</span>
                            </button>
                            @endforeach
                        </nav>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 flex flex-col overflow-hidden bg-white">
                        {{-- Search --}}
                        <div class="flex-shrink-0 px-5 py-3 border-b border-gray-100">
                            <div class="relative max-w-md">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" id="permSearchInput" placeholder="Filter permissions..."
                                    class="w-full pl-9 pr-8 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-400 focus:border-gray-400 bg-white">
                                <button type="button" id="permSearchClear" style="display:none;"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Permission List --}}
                        <div id="permContent" class="flex-1 overflow-y-auto">
                            @foreach($permissions as $module => $modulePermissions)
                            <div data-module-section="{{ $module }}">
                                {{-- Section Header --}}
                                <div class="sticky top-0 z-10 bg-gray-50 border-b border-gray-200 px-5 py-2 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wide">{{ $module }}</h4>
                                        <span class="module-count text-[10px] font-semibold text-gray-400 tabular-nums" data-module="{{ $module }}">0 / {{ $modulePermissions->count() }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" data-select-all="{{ $module }}"
                                            class="text-[11px] font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-200 px-2 py-0.5 rounded transition-colors">Select all</button>
                                        <button type="button" data-clear-all="{{ $module }}"
                                            class="text-[11px] font-medium text-gray-400 hover:text-red-600 hover:bg-red-50 px-2 py-0.5 rounded transition-colors">Clear</button>
                                    </div>
                                </div>

                                {{-- Rows --}}
                                @foreach($modulePermissions as $permission)
                                <label data-perm-row="{{ $module }}"
                                    data-perm-slug="{{ strtolower($permission->module . '.' . $permission->resource . '.' . $permission->action) }}"
                                    data-perm-name="{{ strtolower($permission->displayname ?? '') }}"
                                    class="flex items-center gap-3 px-5 py-2 cursor-pointer border-b border-gray-50 transition-colors hover:bg-gray-50/50">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                        class="perm-checkbox h-3.5 w-3.5 rounded text-gray-900 border-gray-300 focus:ring-gray-500 flex-shrink-0"
                                        data-module="{{ $module }}">
                                    <div class="flex-1 min-w-0">
                                        <code class="text-xs font-mono font-medium text-gray-800">{{ $permission->module }}.{{ $permission->resource }}.{{ $permission->action }}</code>
                                        @if($permission->displayname)
                                        <span class="text-[11px] text-gray-400 ml-2">{{ $permission->displayname }}</span>
                                        @endif
                                    </div>
                                </label>
                                @endforeach
                            </div>
                            @endforeach
                        </div>
                    </div>
                </form>
            </div>

            {{-- Footer --}}
            <div class="flex-shrink-0 px-6 py-3.5 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3">
                <button type="button" onclick="window.jabatanApp.closePerm()"
                    class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                <button type="submit" form="permissionForm"
                    class="px-5 py-2.5 text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800 focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-colors shadow-sm">Simpan Permissions</button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const baseUrl = @json(url('/'));
        const storeUrl = @json(route('usermanagement.jabatan.store'));

        // Elements
        const crudModal = document.getElementById('crudModal');
        const crudForm = document.getElementById('crudForm');
        const crudMethod = document.getElementById('crudMethod');
        const crudTitle = document.getElementById('crudModalTitle');
        const crudDesc = document.getElementById('crudModalDesc');
        const crudInput = document.getElementById('crudNamaJabatan');
        const crudSubmitBtn = document.getElementById('crudSubmitBtn');

        const permModal = document.getElementById('permModal');
        const permLoading = document.getElementById('permLoading');
        const permBody = document.getElementById('permBody');
        const permJabatanId = document.getElementById('permJabatanId');
        const permJabatanName = document.getElementById('permJabatanName');
        const permSelectedCount = document.getElementById('permSelectedCount');
        const permContent = document.getElementById('permContent');
        const permSearchInput = document.getElementById('permSearchInput');
        const permSearchClear = document.getElementById('permSearchClear');
        const allCheckboxes = document.querySelectorAll('.perm-checkbox');
        const sidebarBtns = document.querySelectorAll('.perm-sidebar-btn');

        let activeModule = '';

        // === CRUD ===
        window.jabatanApp = {
            openCreate() {
                crudForm.action = storeUrl;
                crudMethod.disabled = true;
                crudTitle.textContent = 'Tambah Jabatan Baru';
                crudDesc.textContent = 'Buat jabatan baru untuk organisasi Anda.';
                crudSubmitBtn.textContent = 'Simpan';
                crudInput.value = '';
                crudModal.style.display = '';
                crudInput.focus();
            },
            openEdit(id, nama) {
                crudForm.action = '/usermanagement/jabatan/' + id;
                crudMethod.disabled = false;
                crudMethod.value = 'PUT';
                crudTitle.textContent = 'Edit Jabatan';
                crudDesc.textContent = 'Perbarui nama jabatan.';
                crudSubmitBtn.textContent = 'Perbarui';
                crudInput.value = nama;
                crudModal.style.display = '';
                crudInput.focus();
            },
            closeCrud() {
                crudModal.style.display = 'none';
            },

            // === PERMISSION ===
            openPermModal(jabatan) {
                permJabatanId.value = jabatan.idjabatan;
                permJabatanName.textContent = jabatan.namajabatan;
                permSearchInput.value = '';
                permSearchClear.style.display = 'none';

                // Reset
                allCheckboxes.forEach(cb => { cb.checked = false; });
                permLoading.style.display = '';
                permBody.style.display = 'none';
                permModal.style.display = '';

                // Load
                fetch(baseUrl + '/usermanagement/ajax/jabatan/' + jabatan.idjabatan + '/permissions')
                    .then(r => r.json())
                    .then(data => {
                        const ids = data.permissions.map(p => p.id || p.permissionid);
                        allCheckboxes.forEach(cb => {
                            cb.checked = ids.includes(parseInt(cb.value));
                            cb.closest('label').classList.toggle('bg-gray-50', cb.checked);
                        });
                        updateCounts();
                        permLoading.style.display = 'none';
                        permBody.style.display = '';
                        setActiveModule(sidebarBtns[0]?.dataset.sidebarModule || '');
                    })
                    .catch(err => {
                        console.error(err);
                        permLoading.style.display = 'none';
                        permBody.style.display = '';
                    });
            },
            closePerm() {
                permModal.style.display = 'none';
            }
        };

        // Close modals on backdrop click
        crudModal.addEventListener('click', function (e) { if (e.target === crudModal) window.jabatanApp.closeCrud(); });
        permModal.addEventListener('click', function (e) { if (e.target === permModal) window.jabatanApp.closePerm(); });

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                if (permModal.style.display !== 'none') window.jabatanApp.closePerm();
                else if (crudModal.style.display !== 'none') window.jabatanApp.closeCrud();
            }
        });

        // === Checkbox change ===
        allCheckboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                this.closest('label').classList.toggle('bg-gray-50', this.checked);
                updateCounts();
            });
        });

        // === Select All / Clear ===
        document.querySelectorAll('[data-select-all]').forEach(btn => {
            btn.addEventListener('click', function () {
                const mod = this.dataset.selectAll;
                document.querySelectorAll('.perm-checkbox[data-module="' + mod + '"]').forEach(cb => {
                    cb.checked = true;
                    cb.closest('label').classList.add('bg-gray-50');
                });
                updateCounts();
            });
        });
        document.querySelectorAll('[data-clear-all]').forEach(btn => {
            btn.addEventListener('click', function () {
                const mod = this.dataset.clearAll;
                document.querySelectorAll('.perm-checkbox[data-module="' + mod + '"]').forEach(cb => {
                    cb.checked = false;
                    cb.closest('label').classList.remove('bg-gray-50');
                });
                updateCounts();
            });
        });

        // === Sidebar click → scroll ===
        sidebarBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const mod = this.dataset.sidebarModule;
                permSearchInput.value = '';
                permSearchClear.style.display = 'none';
                filterPermissions('');
                setActiveModule(mod);
                const section = permContent.querySelector('[data-module-section="' + mod + '"]');
                if (section) {
                    permContent.scrollTo({ top: section.offsetTop - permContent.offsetTop, behavior: 'smooth' });
                }
            });
        });

        // === Scroll tracking ===
        let scrollTick = false;
        permContent.addEventListener('scroll', function () {
            if (scrollTick) return;
            scrollTick = true;
            requestAnimationFrame(function () {
                if (permSearchInput.value.trim() === '') {
                    const sections = permContent.querySelectorAll('[data-module-section]');
                    let current = '';
                    const scrollTop = permContent.scrollTop;
                    const offset = permContent.offsetTop;
                    sections.forEach(s => {
                        if (s.offsetTop - offset - scrollTop <= 60) {
                            current = s.dataset.moduleSection;
                        }
                    });
                    if (current && current !== activeModule) setActiveModule(current);
                }
                scrollTick = false;
            });
        });

        // === Search ===
        permSearchInput.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            permSearchClear.style.display = q ? '' : 'none';
            filterPermissions(q);
        });
        permSearchClear.addEventListener('click', function () {
            permSearchInput.value = '';
            permSearchClear.style.display = 'none';
            filterPermissions('');
        });

        function filterPermissions(q) {
            document.querySelectorAll('[data-module-section]').forEach(section => {
                const mod = section.dataset.moduleSection;
                const rows = section.querySelectorAll('[data-perm-row]');
                let visibleCount = 0;
                rows.forEach(row => {
                    if (!q) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        const slug = row.dataset.permSlug || '';
                        const name = row.dataset.permName || '';
                        const match = slug.includes(q) || name.includes(q);
                        row.style.display = match ? '' : 'none';
                        if (match) visibleCount++;
                    }
                });
                section.style.display = visibleCount > 0 ? '' : 'none';
            });
        }

        // === Helpers ===
        function setActiveModule(mod) {
            activeModule = mod;
            sidebarBtns.forEach(btn => {
                const isCurrent = btn.dataset.sidebarModule === mod;
                btn.classList.toggle('bg-gray-900', isCurrent);
                btn.classList.toggle('text-white', isCurrent);
                btn.classList.toggle('text-gray-600', !isCurrent);
            });
        }

        function updateCounts() {
            let total = 0;
            const moduleCounts = {};

            allCheckboxes.forEach(cb => {
                const mod = cb.dataset.module;
                if (!moduleCounts[mod]) moduleCounts[mod] = { checked: 0, total: 0 };
                moduleCounts[mod].total++;
                if (cb.checked) {
                    moduleCounts[mod].checked++;
                    total++;
                }
            });

            permSelectedCount.textContent = total;

            // Update sidebar counts
            sidebarBtns.forEach(btn => {
                const mod = btn.dataset.sidebarModule;
                const countEl = btn.querySelector('.perm-sidebar-count');
                const mc = moduleCounts[mod] || { checked: 0, total: 0 };
                countEl.textContent = mc.checked + '/' + mc.total;

                // Style
                countEl.className = 'perm-sidebar-count text-[10px] font-bold rounded px-1.5 py-0.5 ml-2 flex-shrink-0 tabular-nums';
                const isActive = btn.classList.contains('bg-gray-900');
                if (isActive) {
                    countEl.classList.add(mc.checked === mc.total && mc.total > 0 ? 'bg-green-400' : 'bg-white/20', mc.checked === mc.total && mc.total > 0 ? 'text-green-950' : 'text-white/80');
                } else if (mc.checked === mc.total && mc.total > 0) {
                    countEl.classList.add('bg-green-100', 'text-green-700');
                } else if (mc.checked > 0) {
                    countEl.classList.add('bg-gray-300', 'text-gray-700');
                } else {
                    countEl.classList.add('bg-gray-200', 'text-gray-400');
                }
            });

            // Update content section counts
            document.querySelectorAll('.module-count').forEach(el => {
                const mod = el.dataset.module;
                const mc = moduleCounts[mod] || { checked: 0, total: 0 };
                el.textContent = mc.checked + ' / ' + mc.total;
            });
        }
    });
    </script>
</x-layout>