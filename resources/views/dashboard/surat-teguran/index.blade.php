<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div x-data="{}" class="mx-auto py-4 bg-white rounded-md shadow-md">

        <!-- Header -->
        <div class="px-4 py-4 border-b border-gray-200">
            <div class="flex flex-col space-y-4 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">

                <!-- Title + Unread Badge -->
                <div class="flex items-center gap-3">
                    <h2 class="text-base font-semibold text-gray-800">Daftar Surat Teguran Masuk</h2>
                    @if($unreadCount > 0)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                        {{ $unreadCount }} belum dibaca
                    </span>
                    @endif
                </div>

                <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">

                    <!-- Filter Status -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="status" class="text-xs font-medium text-gray-700 whitespace-nowrap">Status:</label>
                        <select name="status" id="status" onchange="this.form.submit()"
                            class="text-xs border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                            <option value="">Semua</option>
                            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Belum Dibaca</option>
                            <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Sudah Dibaca</option>
                        </select>
                        @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('perPage'))
                        <input type="hidden" name="perPage" value="{{ request('perPage') }}">
                        @endif
                    </form>

                    <!-- Search -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="search" class="text-xs font-medium text-gray-700 whitespace-nowrap">Cari:</label>
                        <input type="text" name="search" id="search"
                            value="{{ request('search') }}"
                            placeholder="No. surat / perihal..."
                            class="text-xs w-full sm:w-48 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                            onkeydown="if(event.key==='Enter') this.form.submit()" />
                        @if(request('search'))
                        <a href="{{ route('dashboard.surat-teguran.index') }}" class="text-gray-500 hover:text-gray-700 px-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </a>
                        @endif
                        @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        @if(request('perPage'))
                        <input type="hidden" name="perPage" value="{{ request('perPage') }}">
                        @endif
                    </form>

                    <!-- Per Page -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="perPage" class="text-xs font-medium text-gray-700 whitespace-nowrap">Per halaman:</label>
                        <select name="perPage" id="perPage" onchange="this.form.submit()"
                            class="text-xs w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                            <option value="10" {{ (int)request('perPage', 10) === 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ (int)request('perPage', 10) === 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ (int)request('perPage', 10) === 50 ? 'selected' : '' }}>50</option>
                        </select>
                        @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="px-4 py-4">
            <div class="overflow-x-auto rounded-md border border-gray-300">
                <table class="min-w-full bg-white text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Surat</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dari</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Teguran</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perihal</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($data ?? [] as $index => $item)
                        <tr class="hover:bg-gray-50 {{ $item->status === 'sent' ? 'font-medium' : '' }}">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $data->firstItem() + $index }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->stgno }}
                                @if($item->status === 'sent')
                                <span class="ml-1 inline-block w-2 h-2 bg-red-500 rounded-full align-middle" title="Belum dibaca"></span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                {{ $item->stgdate ? date('d/m/Y', strtotime($item->stgdate)) : '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                <span class="font-medium">{{ $item->companycode }}</span>
                                @if($item->fromcompanyname)
                                <span class="text-gray-500 text-xs block">{{ $item->fromcompanyname }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $item->jenisteguran ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 max-w-xs truncate">{{ $item->perihal ?? '-' }}</td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($item->status === 'read')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Sudah Dibaca</span>
                                @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Belum Dibaca</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <a href="{{ route('dashboard.surat-teguran.show', $item->id) }}"
                                    class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada surat teguran masuk.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $data->appends(request()->query())->links() }}
            </div>
        </div>

    </div>
</x-layout>
