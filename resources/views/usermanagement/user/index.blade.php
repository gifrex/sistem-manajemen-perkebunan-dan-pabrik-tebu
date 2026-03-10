<!-- resources\views\usermanagement\user\index.blade.php -->
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <!-- Success/Error Notifications -->
    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Berhasil!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-green-200 rounded"
            @click="show = false">&times;</span>
    </div>
    @endif

    @if (session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-red-200 rounded"
            @click="show = false">&times;</span>
    </div>
    @endif

    <div x-data="{
        open: false,
        permissionModal: false,
        companyAccessModal: false,
        mode: 'create',
        editUrl: '',
        selectedUser: null,
        userPermissions: {},
        userCompanies: [],
        selectedActivities: [],
        form: {
            userid: '',
            name: '',
            companycode: '',
            idjabatan: '',
            password: '',
            isactive: 1
        },
        resetForm() {
            this.mode = 'create';
            this.editUrl = '';
            this.selectedActivities = [];
            this.form = {
                userid: '',
                name: '',
                companycode: '',
                idjabatan: '',
                password: '',
                isactive: 1
            };
            this.open = true;
        },
        editForm(data, url, activities = '') {
            this.mode = 'edit';
            this.editUrl = url;
            this.selectedActivities = [];
            this.form = {
                userid: data.userid,
                name: data.name,
                companycode: data.companycode,
                idjabatan: data.idjabatan,
                password: '',
                isactive: data.isactive
            };

            if (activities && activities.trim()) {
                this.selectedActivities = activities.split(',')
                    .map(s => s.trim())
                    .filter(Boolean);
            } else {
                this.selectedActivities = [];
            }   
            this.open = true;
            },

                isActivitySelected(activityValue) {
                return this.selectedActivities.includes(activityValue);
            },
            async showPermissions(userid, name) {
            this.selectedUser = { userid, name };
            this.userPermissions = { loading: true };
            this.permissionModal = true;
            
            try {
                const baseUrl = '{{ url("/") }}';
                const response = await fetch(baseUrl + '/usermanagement/ajax/users/' + userid + '/permissions');
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const data = await response.json();
                this.userPermissions = data;
            } catch (error) {
                console.error('Error fetching permissions:', error);
                this.userPermissions = { error: 'Failed to load permissions. Please try again.' };
            }
        },
        showCompanyAccess(companies, userid, name) {
            this.selectedUser = { userid, name };
            this.userCompanies = companies || [];
            this.companyAccessModal = true;
        }
    }" class="mx-auto py-4 bg-white rounded-md shadow-md">

        <!-- Header Controls -->
        <div class="px-4 py-4 border-b border-gray-200">
            <div class="flex flex-col space-y-4 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">

                <!-- Tambah Data Button -->
                <div class="flex justify-start">
                    <button @click="resetForm()"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span class="hidden sm:inline">Tambah User</span>
                        <span class="sm:hidden">Tambah</span>
                    </button>
                </div>

                <!-- Search and Controls -->
                <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">

                    <!-- Search Form -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="search" class="text-xs font-medium text-gray-700 whitespace-nowrap">Cari:</label>
                        <input type="text" name="search" id="search"
                            value="{{ request('search') }}"
                            placeholder="User ID, Nama, Jabatan..."
                            class="text-xs w-full sm:w-48 md:w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                            onkeydown="if(event.key==='Enter') this.form.submit()" />
                        @if(request('search'))
                        <a href="{{ route('usermanagement.user.index') }}"
                            class="text-gray-500 hover:text-gray-700 px-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </a>
                        @endif
                        @if(request('perPage'))
                        <input type="hidden" name="perPage" value="{{ request('perPage') }}">
                        @endif
                    </form>

                    <!-- Per Page Form -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="perPage" class="text-xs font-medium text-gray-700 whitespace-nowrap">Per halaman:</label>
                        <select name="perPage" id="perPage" onchange="this.form.submit()"
                            class="text-xs w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                            <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ ($perPage ?? 10) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
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
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User ID</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Company Access</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Activity Permission</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($result as $user)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-3 px-3 text-sm font-medium text-gray-900">{{ $user->userid }}</td>
                            <td class="py-3 px-3 text-sm text-gray-700">{{ $user->name }}</td>
                            <td class="py-3 px-3 text-sm text-gray-700">{{ $user->companycode }}</td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $user->jabatan->namajabatan ?? 'No Jabatan' }}</span>
                                    <span class="text-xs text-gray-500">ID: {{ $user->idjabatan }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-center text-sm text-gray-700">
                                @if($user->userCompanies && $user->userCompanies->count() > 0)
                                <div x-data="{ dropdownOpen: false }" class="relative">
                                    <button @click="dropdownOpen = !dropdownOpen"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors duration-150 cursor-pointer">
                                        <span>{{ $user->userCompanies->count() }} Companies</span>
                                        <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    <!-- Dropdown -->
                                    <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-transition
                                        class="absolute z-50 mt-1 w-64 bg-white border border-gray-300 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                        <div class="py-1">
                                            @foreach ($user->userCompanies as $userCompany)
                                            <div class="px-4 py-2 text-xs text-gray-700 border-b border-gray-100 last:border-b-0">
                                                <div class="flex justify-between items-center">
                                                    <span class="font-medium">{{ $userCompany->companycode }}</span>
                                                    <span class="text-xs {{ $userCompany->isactive ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ $userCompany->isactive ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </div>
                                                @if($userCompany->grantedby)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Granted by: {{ $userCompany->grantedby }}
                                                </div>
                                                @endif
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @else
                                <span class="text-xs text-gray-500">No Company Access</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center text-sm text-gray-700">
                                @php
                                $uas = $user->userActivities ?? collect();
                                // Total izin (jumlah token grup) dari semua baris userActivities
                                $totalPermissions = $uas->sum(function ($ua) {
                                return count(array_filter(array_map('trim', explode(',', (string)$ua->activitygroup))));
                                });
                                @endphp

                                @if($uas->isNotEmpty() && $totalPermissions > 0)
                                <div x-data="{ dropdownOpen: false }" class="relative">
                                    <button @click="dropdownOpen = !dropdownOpen"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors duration-150 cursor-pointer">
                                        <span>{{ $totalPermissions }} Activity Permission</span>
                                        <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-transition
                                        class="absolute z-50 mt-1 w-64 bg-white border border-gray-300 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                        <div class="py-1">
                                            @foreach ($uas as $ua)
                                            @php
                                            $groups = array_filter(array_map('trim', explode(',', (string)$ua->activitygroup)));
                                            @endphp

                                            @if(count($groups))
                                            <div class="px-4 py-2 text-xs text-gray-700 border-b border-gray-100 last:border-b-0">
                                                <div class="flex justify-between items-center">
                                                    <span class="font-medium">
                                                        {{-- tampilkan sebagai badge kecil --}}
                                                        @foreach ($groups as $g)
                                                        @php $name = $activityGroupLookup[$g] ?? $g; @endphp
                                                        <span class="inline-block px-1.5 py-0.5 bg-gray-100 text-gray-700 rounded mr-1">
                                                            {{ $name }}
                                                        </span>
                                                        @endforeach
                                                    </span>
                                                    <span class="text-xs">{{ $ua->companycode }}</span>
                                                </div>
                                                @if(!empty($ua->grantedby))
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Granted by: {{ $ua->grantedby }}
                                                </div>
                                                @endif
                                            </div>
                                            @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @else
                                <span class="text-xs text-gray-500">No Activity Access</span>
                                @endif
                            </td>

                            <td class="py-3 px-3 text-center text-sm">
                                @if($user->isactive == 1)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Aktif
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Nonaktif
                                </span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- View Permissions Button -->
                                    <button @click="showPermissions('{{ $user->userid }}', '{{ $user->name }}')"
                                        class="text-green-600 hover:text-green-800 hover:bg-green-50 rounded-md p-2 transition-all duration-150"
                                        title="View Permissions">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                    </button>
                                    @php
                                    $userActivity = $user->userActivities
                                    ->where('companycode', $user->companycode)
                                    ->first();
                                    $activityString = $userActivity ? $userActivity->activitygroup : '';
                                    @endphp
                                    <!-- Edit Button -->
                                    <button @click='editForm({
                                            userid: "{{ $user->userid }}",
                                            name: "{{ $user->name }}",
                                            companycode: "{{ $user->companycode }}",
                                            idjabatan: "{{ $user->idjabatan }}",
                                            isactive: {{ $user->isactive ?? 0 }}
                                        }, 
                                        "{{ route('usermanagement.user.update', $user->userid) }}",
                                        "{{ $activityString }}"
                                        )'
                                        class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md p-2 transition-all duration-150"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>

                                    <!-- Deactivate Button -->
                                    <form action="{{ route('usermanagement.user.destroy', $user->userid) }}" method="POST"
                                        onsubmit="return confirm(' Yakin ingin menonaktifkan user {{ $user->name }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md p-2 transition-all duration-150"
                                            title="Nonaktifkan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 px-4 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Tidak ada data user</p>
                                    <p class="text-sm">{{ request('search') ? 'Tidak ada hasil untuk pencarian "'.request('search').'"' : 'Belum ada user yang terdaftar' }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($result->hasPages())
            <div class="mt-6">
                {{ $result->appends(request()->query())->links() }}
            </div>
            @else
            <div class="mt-4 flex items-center justify-between text-sm text-gray-700">
                <p>Menampilkan <span class="font-medium">{{ $result->count() }}</span> dari <span class="font-medium">{{ $result->total() }}</span> data</p>
            </div>
            @endif
        </div>

        <!-- Create/Edit User Modal -->
        <!-- Create/Edit User Modal - WIDER SCROLLABLE VERSION -->
        <!-- Replace the entire modal in your view -->

        <!-- Create/Edit User Modal - WIDER SCROLLABLE VERSION -->
        <!-- Replace the entire modal in your view -->

        <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" x-cloak
            @keydown.window.escape="open = false">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">

                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 sticky top-0 z-10">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900" x-text="mode === 'create' ? 'Tambah User Baru' : 'Edit User'"></h3>
                            <p class="text-sm text-gray-600">Complete the form below</p>
                        </div>
                    </div>
                    <button @click="open = false"
                        class="text-gray-400 bg-transparent hover:bg-white hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <form id="userForm" :action="mode === 'create' ? '{{ route('usermanagement.user.store') }}' : editUrl" method="POST">
                        @csrf
                        <template x-if="mode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <!-- TWO COLUMN LAYOUT -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                            <!-- LEFT COLUMN -->
                            <div class="space-y-4">
                                <!-- Basic Information Section -->
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Basic Information
                                    </h4>

                                    <!-- User ID -->
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            User ID <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="userid" x-model="form.userid"
                                            placeholder="Enter unique user ID"
                                            :readonly="mode === 'edit'"
                                            :class="mode === 'edit' ? 'bg-gray-100 text-gray-600 cursor-not-allowed' : ''"
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            required maxlength="50">
                                        <template x-if="mode === 'edit'">
                                            <p class="text-xs text-gray-500 mt-1">User ID cannot be changed</p>
                                        </template>
                                    </div>

                                    <!-- Nama -->
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Nama Lengkap <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="name" x-model="form.name"
                                            placeholder="Enter full name"
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            required maxlength="30">
                                    </div>

                                    <!-- Password -->
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Password
                                            <template x-if="mode === 'create'">
                                                <span class="text-red-500">*</span>
                                            </template>
                                            <template x-if="mode === 'edit'">
                                                <span class="text-gray-500 text-xs font-normal">(leave blank to keep current)</span>
                                            </template>
                                        </label>
                                        <input type="password" name="password" x-model="form.password"
                                            placeholder="Minimum 6 characters"
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            :required="mode === 'create'"
                                            minlength="6">
                                    </div>

                                    <!-- Status Active (Edit only) -->
                                    <template x-if="mode === 'edit'">
                                        <div class="mt-3">
                                            <label class="flex items-center space-x-2 cursor-pointer">
                                                <input type="checkbox" name="isactive"
                                                    :checked="form.isactive == 1"
                                                    value="1"
                                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                <span class="text-sm font-medium text-gray-700">Active User</span>
                                            </label>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- RIGHT COLUMN -->
                            <div class="space-y-4">
                                <!-- Organization Details Section -->
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        Organization Details
                                    </h4>

                                    <!-- Company -->
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Primary Company <span class="text-red-500">*</span>
                                        </label>
                                        <select name="companycode" x-model="form.companycode" required
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">-- Select Company --</option>
                                            @foreach($companies as $company)
                                            <option value="{{ $company->companycode }}">{{ $company->companycode }} - {{ $company->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Jabatan -->
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Jabatan (Role) <span class="text-red-500">*</span>
                                        </label>
                                        <select name="idjabatan" x-model="form.idjabatan" required
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">-- Select Jabatan --</option>
                                            @foreach($jabatan as $j)
                                            <option value="{{ $j->idjabatan }}">{{ $j->namajabatan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Activity Groups Section (Full Width) -->
                        <div class="mt-6 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                Activity Groups
                                <span class="ml-2 text-xs font-normal text-gray-500">(Optional)</span>
                            </h4>

                            @if($activityGroupOptions->isEmpty())
                            <div class="text-sm text-gray-500 bg-white rounded-md p-3 border border-gray-200 text-center">
                                <svg class="w-5 h-5 text-gray-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p>No activity groups available</p>
                            </div>
                            @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 max-h-56 overflow-y-auto bg-white border border-gray-200 rounded-md p-3">
                                @foreach($activityGroupOptions as $group)
                                <label class="flex items-start space-x-2 p-2 hover:bg-blue-50 rounded cursor-pointer transition-colors group">
                                    <input type="checkbox"
                                        name="activitygroups[]"
                                        value="{{ $group['value'] }}"
                                        :checked="isActivitySelected('{{ $group['value'] }}')"
                                        @change="if ($event.target.checked) { 
                                            if (!selectedActivities.includes('{{ $group['value'] }}')) {
                                                selectedActivities.push('{{ $group['value'] }}');
                                            }
                                                } else {
                                                    selectedActivities = selectedActivities.filter(a => a !== '{{ $group['value'] }}');
                                                }"
                                        class="h-4 w-4 mt-0.5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded flex-shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-xs text-gray-900 truncate">{{ $group['label'] }}</div>
                                        @if(!empty($group['groupname']))
                                        <div class="text-xs text-gray-500 truncate">{{ $group['groupname'] }}</div>
                                        @endif
                                    </div>
                                </label>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <!-- Modal Actions -->
                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0 mt-6 pt-4 border-t border-gray-200">
                            <button type="button" @click="open = false"
                                class="w-full sm:w-auto px-5 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                Cancel
                            </button>
                            <button type="submit"
                                class="w-full sm:w-auto px-5 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150 flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span x-text="mode === 'create' ? 'Create User' : 'Update User'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Permission Modal -->
        <div x-show="permissionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" x-cloak
            @keydown.window.escape="permissionModal = false">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">

                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">User Permissions</h3>
                            <p class="text-gray-600 text-sm" x-text="selectedUser ? selectedUser.name + ' (' + selectedUser.userid + ')' : ''"></p>
                        </div>
                    </div>
                    <button @click="permissionModal = false"
                        class="text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                    <div class="space-y-6">
                        <!-- Loading State -->
                        <template x-if="userPermissions.loading">
                            <div class="flex items-center justify-center py-8">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-600"></div>
                                <span class="ml-3 text-gray-600">Loading permissions...</span>
                            </div>
                        </template>

                        <!-- Error State -->
                        <template x-if="userPermissions.error">
                            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">Error loading permissions</h3>
                                        <p class="mt-1 text-sm text-red-700" x-text="userPermissions.error"></p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Permissions Content -->
                        <template x-if="!userPermissions.loading && !userPermissions.error && userPermissions.role">
                            <div class="space-y-6">
                                <!-- Default Role Permissions -->
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                            </svg>
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-800">Default Permissions</h4>
                                                <p class="text-sm text-gray-600">Based on role assignment</p>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                            <span x-text="userPermissions.role.count + ' permissions'"></span>
                                        </span>
                                    </div>

                                    <div class="mt-4 flex items-center justify-between bg-white rounded-md p-3 border border-gray-200">
                                        <div class="flex items-center space-x-3">
                                            <div>
                                                <h5 class="font-medium text-gray-900" x-text="userPermissions.role.namajabatan"></h5>
                                                <p class="text-sm text-gray-500">Role-based permissions</p>
                                            </div>
                                        </div>
                                        <a :href="'{{ route('usermanagement.jabatan.index') }}?search=' + encodeURIComponent(userPermissions.role.namajabatan)"
                                            class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150">
                                            View Details
                                        </a>
                                    </div>
                                </div>

                                <!-- User-Specific Permission Overrides -->
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                            </svg>
                                            Permission Overrides
                                        </h4>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            <span x-text="(userPermissions.overrides ? userPermissions.overrides.length : 0) + ' overrides'"></span>
                                        </span>
                                    </div>

                                    <!-- No Overrides -->
                                    <template x-if="!userPermissions.overrides || userPermissions.overrides.length === 0">
                                        <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <p class="text-gray-600 font-medium">No Permission Overrides</p>
                                            <p class="text-sm text-gray-500">This user follows default role permissions only</p>
                                        </div>
                                    </template>

                                    <!-- Permission Overrides List -->
                                    <template x-if="userPermissions.overrides && userPermissions.overrides.length > 0">
                                        <div class="space-y-3">
                                            <template x-for="override in userPermissions.overrides" :key="override.permission + override.companycode">
                                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center space-x-3">
                                                            <!-- Grant/Deny Icon -->
                                                            <template x-if="override.permissiontype === 'GRANT'">
                                                                <div class="flex-shrink-0">
                                                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                    </svg>
                                                                </div>
                                                            </template>
                                                            <template x-if="override.permissiontype === 'DENY'">
                                                                <div class="flex-shrink-0">
                                                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                    </svg>
                                                                </div>
                                                            </template>

                                                            <div>
                                                                <h6 class="font-medium text-gray-900" x-text="override.permission"></h6>
                                                                <div class="flex items-center space-x-2 mt-1">
                                                                    <span class="text-xs text-gray-500">Company:</span>
                                                                    <span class="text-xs font-medium text-gray-700" x-text="override.companycode"></span>
                                                                </div>
                                                                <template x-if="override.reason">
                                                                    <p class="text-xs text-gray-600 mt-1" x-text="override.reason"></p>
                                                                </template>
                                                            </div>
                                                        </div>

                                                        <div class="flex flex-col items-end space-y-1">
                                                            <!-- Permission Type Badge -->
                                                            <template x-if="override.permissiontype === 'GRANT'">
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                    GRANTED
                                                                </span>
                                                            </template>
                                                            <template x-if="override.permissiontype === 'DENY'">
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                    DENIED
                                                                </span>
                                                            </template>

                                                            <!-- Granted By Info -->
                                                            <template x-if="override.grantedby">
                                                                <div class="text-xs text-gray-500 text-right">
                                                                    by: <span x-text="override.grantedby"></span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex justify-end">
                        <button @click="permissionModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                            Close
                        </button>
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