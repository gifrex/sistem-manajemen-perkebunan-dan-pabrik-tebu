<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Berhasil!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-green-200 rounded" @click="show = false">&times;</span>
    </div>
    @endif

    @if (session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-red-200 rounded" @click="show = false">&times;</span>
    </div>
    @endif

    <div x-data="{
        open: false,
        mode: 'create',
        editUrl: '',
        form: {
            kodeharga: '',
            periode: '',
            active: 1,
            manualtebang: '',
            manualmuat: '',
            manualangkutan: '',
            manualfeekont: '',
            manualnonpremi: '',
            manualbsm: '',
            manualtebusulit: '',
            manualpremiton: '',
            glkebuntebang: '',
            glkebunmuat: '',
            glkebunangkutan: '',
            glkebunfeekont: '',
            glkebunnonpremi: '',
            glkebunbsm: '',
            glkebuntebusulit: '',
            glkebunpremiton: '',
            glkontraktortebang: '',
            glkontraktormuat: '',
            glkontraktorangkutan: '',
            glkontraktorfeekont: '',
            glkontraktornonpremi: '',
            glkontraktorbsm: '',
            glkontraktortebusulit: '',
            glkontraktorpremiton: '',
            extrafooding: '',
            tebutdkseset: '',
            langsir: ''
        },
        resetForm() {
            this.mode = 'create';
            this.editUrl = '';
            this.form = {
                kodeharga: '',
                periode: '',
                active: 1,
                manualtebang: '',
                manualmuat: '',
                manualangkutan: '',
                manualfeekont: '',
                manualnonpremi: '',
                manualbsm: '',
                manualtebusulit: '',
                manualpremiton: '',
                glkebuntebang: '',
                glkebunmuat: '',
                glkebunangkutan: '',
                glkebunfeekont: '',
                glkebunnonpremi: '',
                glkebunbsm: '',
                glkebuntebusulit: '',
                glkebunpremiton: '',
                glkontraktortebang: '',
                glkontraktormuat: '',
                glkontraktorangkutan: '',
                glkontraktorfeekont: '',
                glkontraktornonpremi: '',
                glkontraktorbsm: '',
                glkontraktortebusulit: '',
                glkontraktorpremiton: '',
                extrafooding: '',
                tebutdkseset: '',
                langsir: ''
            };
            this.open = true;
        },
        editForm(data, url) {
            this.mode = 'edit';
            this.editUrl = url;
            this.form = data;
            this.open = true;
        }
    }" class="mx-auto py-4 bg-white rounded-md shadow-md">

        <!-- Header Controls -->
        <div class="px-4 py-4 border-b border-gray-200">
            <div class="flex flex-col space-y-4 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">
                <div class="flex flex-wrap gap-2">
                    <button @click="resetForm()"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span class="hidden sm:inline">Tambah Harga Panen</span>
                        <span class="sm:hidden">Tambah</span>
                    </button>
                </div>

                <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="search" class="text-xs font-medium text-gray-700 whitespace-nowrap">Cari:</label>
                        <input type="text" name="search" id="search"
                            value="{{ request('search') }}"
                            placeholder="Kode Harga..."
                            class="text-xs w-full sm:w-48 md:w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                            onkeydown="if(event.key==='Enter') this.form.submit()" />
                        @if(request('search'))
                        <a href="{{ route('settings.harga-panen.index') }}" class="text-gray-500 hover:text-gray-700 px-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </a>
                        @endif
                        @if(request('perPage'))
                        <input type="hidden" name="perPage" value="{{ request('perPage') }}">
                        @endif
                    </form>

                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="perPage" class="text-xs font-medium text-gray-700 whitespace-nowrap">Per halaman:</label>
                        <select name="perPage" id="perPage" onchange="this.form.submit()"
                            class="text-xs w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="px-4 py-4">
            <div class="overflow-x-auto rounded-md border border-gray-300">
                <table class="min-w-full bg-white text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th rowspan="2" class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap border-b border-gray-200">Kode Harga</th>
                            <th rowspan="2" class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Periode</th>
                            <th rowspan="2" class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Status</th>
                            <!-- Group headers -->
                            <th colspan="8" class="py-2 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-l border-gray-200 bg-blue-50">Manual</th>
                            <th colspan="8" class="py-2 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-l border-gray-200 bg-green-50">GL Kebun</th>
                            <th colspan="8" class="py-2 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-l border-gray-200 bg-yellow-50">GL Kontraktor</th>
                            <th colspan="3" class="py-2 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-l border-gray-200 bg-purple-50">Extra</th>
                            <th rowspan="2" class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap border-b border-l border-gray-200">Created By</th>
                            <th rowspan="2" class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap border-b border-gray-200">Created Date</th>
                            <th rowspan="2" class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap border-b border-gray-200">Updated By</th>
                            <th rowspan="2" class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap border-b border-gray-200">Updated Date</th>
                            <th rowspan="2" class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-l border-gray-200">Aksi</th>
                        </tr>
                        <tr class="bg-gray-50 text-xs text-gray-500">
                            @php
                            $manualCols = ['Tebang','Muat','Angkutan','Fee Kont','Non Premi','BSM','Tebu Sulit','Premi Ton'];
                            $kebunCols = $manualCols;
                            $kontrCols = $manualCols;
                            $extraCols = ['Extra Fooding','Tebu Tdk Seset','Langsir'];
                            @endphp
                            @foreach($manualCols as $col)
                            <th class="py-2 px-3 text-right font-medium uppercase tracking-wider whitespace-nowrap border-b border-gray-200 {{ $loop->first ? 'border-l' : '' }} bg-blue-50">
                                {{ $col }}<br><span class="text-gray-400 normal-case font-normal">Rp/kg</span>
                            </th>
                            @endforeach
                            @foreach($kebunCols as $col)
                            <th class="py-2 px-3 text-right font-medium uppercase tracking-wider whitespace-nowrap border-b border-gray-200 {{ $loop->first ? 'border-l' : '' }} bg-green-50">
                                {{ $col }}<br><span class="text-gray-400 normal-case font-normal">Rp/kg</span>
                            </th>
                            @endforeach
                            @foreach($kontrCols as $col)
                            <th class="py-2 px-3 text-right font-medium uppercase tracking-wider whitespace-nowrap border-b border-gray-200 {{ $loop->first ? 'border-l' : '' }} bg-yellow-50">
                                {{ $col }}<br><span class="text-gray-400 normal-case font-normal">Rp/kg</span>
                            </th>
                            @endforeach
                            @foreach($extraCols as $col)
                            <th class="py-2 px-3 text-right font-medium uppercase tracking-wider whitespace-nowrap border-b border-gray-200 {{ $loop->first ? 'border-l' : '' }} bg-purple-50">
                                {{ $col }}<br><span class="text-gray-400 normal-case font-normal">Rp/kg</span>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($result as $item)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-3 px-3 text-sm font-medium text-gray-900 whitespace-nowrap">{{ $item->kodeharga }}</td>
                            <td class="py-3 px-3 text-sm text-gray-700">{{ $item->periode }}</td>
                            <td class="py-3 px-3 text-center text-sm">
                                @if($item->active == 1)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                                @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Nonaktif</span>
                                @endif
                            </td>
                            <!-- Manual -->
                            <td class="py-3 px-3 text-sm text-right text-gray-700 border-l border-gray-100">{{ $item->manualtebang ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->manualmuat ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->manualangkutan ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->manualfeekont ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->manualnonpremi ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->manualbsm ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->manualtebusulit ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->manualpremiton ?? '-' }}</td>
                            <!-- Kebun -->
                            <td class="py-3 px-3 text-sm text-right text-gray-700 border-l border-gray-100">{{ $item->glkebuntebang ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->glkebunmuat ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->glkebunangkutan ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->glkebunfeekont ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->glkebunnonpremi ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->glkebunbsm ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->glkebuntebusulit ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->glkebunpremiton ?? '-' }}</td>
                            <!-- Kontraktor -->
                            <td class="py-3 px-3 text-sm text-right text-gray-700 border-l border-gray-100">{{ $item->glkontraktortebang ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->glkontraktormuat ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->glkontraktorangkutan ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->glkontraktorfeekont ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->glkontraktornonpremi ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->glkontraktorbsm ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->glkontraktortebusulit ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->glkontraktorpremiton ?? '-' }}</td>
                            <!-- Extra -->
                            <td class="py-3 px-3 text-sm text-right text-gray-700 border-l border-gray-100">{{ $item->extrafooding ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->tebutdkseset ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-right text-gray-700">{{ $item->langsir ?? '-' }}</td>
                            <!-- Audit -->
                            <td class="py-3 px-3 text-sm text-gray-700 whitespace-nowrap border-l border-gray-100">{{ $item->createdby ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-gray-700 whitespace-nowrap">{{ $item->createddate ? \Carbon\Carbon::parse($item->createddate)->format('d/m/Y H:i') : '-' }}</td>
                            <td class="py-3 px-3 text-sm text-gray-700 whitespace-nowrap">{{ $item->updateby ?? '-' }}</td>
                            <td class="py-3 px-3 text-sm text-gray-700 whitespace-nowrap">{{ $item->updateddate ? \Carbon\Carbon::parse($item->updateddate)->format('d/m/Y H:i') : '-' }}</td>
                            <td class="py-3 px-3 border-l border-gray-100">
                                <div class="flex items-center justify-center space-x-2">
                                    <button @click='editForm({
                                            kodeharga: "{{ $item->kodeharga }}",
                                            periode: "{{ $item->periode }}",
                                            active: {{ $item->active ?? 0 }},
                                            manualtebang: "{{ $item->manualtebang }}",
                                            manualmuat: "{{ $item->manualmuat }}",
                                            manualangkutan: "{{ $item->manualangkutan }}",
                                            manualfeekont: "{{ $item->manualfeekont }}",
                                            manualnonpremi: "{{ $item->manualnonpremi }}",
                                            manualbsm: "{{ $item->manualbsm }}",
                                            manualtebusulit: "{{ $item->manualtebusulit }}",
                                            manualpremiton: "{{ $item->manualpremiton }}",
                                            glkebuntebang: "{{ $item->glkebuntebang }}",
                                            glkebunmuat: "{{ $item->glkebunmuat }}",
                                            glkebunangkutan: "{{ $item->glkebunangkutan }}",
                                            glkebunfeekont: "{{ $item->glkebunfeekont }}",
                                            glkebunnonpremi: "{{ $item->glkebunnonpremi }}",
                                            glkebunbsm: "{{ $item->glkebunbsm }}",
                                            glkebuntebusulit: "{{ $item->glkebuntebusulit }}",
                                            glkebunpremiton: "{{ $item->glkebunpremiton }}",
                                            glkontraktortebang: "{{ $item->glkontraktortebang }}",
                                            glkontraktormuat: "{{ $item->glkontraktormuat }}",
                                            glkontraktorangkutan: "{{ $item->glkontraktorangkutan }}",
                                            glkontraktorfeekont: "{{ $item->glkontraktorfeekont }}",
                                            glkontraktornonpremi: "{{ $item->glkontraktornonpremi }}",
                                            glkontraktorbsm: "{{ $item->glkontraktorbsm }}",
                                            glkontraktortebusulit: "{{ $item->glkontraktortebusulit }}",
                                            glkontraktorpremiton: "{{ $item->glkontraktorpremiton }}",
                                            extrafooding: "{{ $item->extrafooding }}",
                                            tebutdkseset: "{{ $item->tebutdkseset }}",
                                            langsir: "{{ $item->langsir }}"
                                        }, "{{ route('settings.harga-panen.update', $item->kodeharga) }}")'
                                        class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md p-2 transition-all duration-150"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    @can('settings.harga-panen.delete')
                                    <form action="{{ route('settings.harga-panen.destroy', $item->kodeharga) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus {{ $item->kodeharga }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md p-2 transition-all duration-150"
                                            title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="39" class="py-8 px-4 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Tidak ada data harga panen</p>
                                    <p class="text-sm">{{ request('search') ? 'Tidak ada hasil untuk pencarian "'.request('search').'"' : 'Belum ada harga panen yang terdaftar' }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

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

        <!-- Modal Add/Edit -->
        <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" x-cloak
            @keydown.window.escape="open = false">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">

                <div class="flex items-center justify-between p-6 border-b border-gray-200 sticky top-0 bg-white z-10">
                    <h3 class="text-xl font-semibold text-gray-900" x-text="mode === 'create' ? 'Tambah Harga Panen' : 'Edit Harga Panen'"></h3>
                    <button @click="open = false"
                        class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <form :action="mode === 'create' ? '{{ route('settings.harga-panen.store') }}' : editUrl" method="POST">
                        @csrf
                        <template x-if="mode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <template x-if="mode === 'edit'">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kode Harga</label>
                                <input type="text" x-model="form.kodeharga" name="kodeharga"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600 cursor-not-allowed"
                                    readonly>
                            </div>
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Periode <span class="text-red-500">*</span></label>
                                <input type="number" name="periode" x-model="form.periode"
                                    placeholder="contoh: 2025"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required maxlength="4">
                            </div>
                            <div class="flex items-center pt-6">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="active" value="1"
                                        :checked="form.active == 1"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                                    <span class="text-sm font-medium text-gray-700">Status Aktif</span>
                                </label>
                            </div>
                        </div>

                        @php
                        $modalSections = [
                        ['label' => 'Manual', 'color' => 'blue', 'prefix' => 'manual', 'fields' => ['tebang','muat','angkutan','feekont','nonpremi','bsm','tebusulit','premiton']],
                        ['label' => 'GL Kebun', 'color' => 'green', 'prefix' => 'glkebun', 'fields' => ['tebang','muat','angkutan','feekont','nonpremi','bsm','tebusulit','premiton']],
                        ['label' => 'GL Kontraktor', 'color' => 'yellow', 'prefix' => 'glkontraktor', 'fields' => ['tebang','muat','angkutan','feekont','nonpremi','bsm','tebusulit','premiton']],
                        ];
                        $fieldLabels = [
                        'tebang' => 'Tebang',
                        'muat' => 'Muat',
                        'angkutan' => 'Angkutan',
                        'feekont' => 'Fee Kontraktor',
                        'nonpremi' => 'Non Premi',
                        'bsm' => 'BSM',
                        'tebusulit' => 'Tebu Sulit',
                        'premiton' => 'Premi Ton',
                        ];
                        $colorMap = [
                        'blue' => 'bg-blue-50 border-blue-200 text-blue-800',
                        'green' => 'bg-green-50 border-green-200 text-green-800',
                        'yellow' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
                        ];
                        @endphp

                        @foreach($modalSections as $section)
                        <div class="mb-5">
                            <h4 class="text-sm font-semibold mb-3 pb-1 border-b flex items-center gap-2 {{ $colorMap[$section['color']] }} px-2 py-1 rounded">
                                {{ $section['label'] }}
                            </h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                @foreach($section['fields'] as $field)
                                @php $name = $section['prefix'] . $field; @endphp
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">
                                        {{ $fieldLabels[$field] }}
                                        <span class="text-gray-400 font-normal">(Rp/kg)</span>
                                    </label>
                                    <input type="number" step="0.01" name="{{ $name }}" x-model="form.{{ $name }}"
                                        class="w-full px-2 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        <!-- Extra -->
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold mb-3 pb-1 border-b flex items-center gap-2 bg-purple-50 border-purple-200 text-purple-800 px-2 py-1 rounded">
                                Extra
                            </h4>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Extra Fooding <span class="text-gray-400 font-normal">(Rp/kg)</span></label>
                                    <input type="number" step="0.01" name="extrafooding" x-model="form.extrafooding"
                                        class="w-full px-2 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Tebu Tidak Seset <span class="text-gray-400 font-normal">(Rp/kg)</span></label>
                                    <input type="number" step="0.01" name="tebutdkseset" x-model="form.tebutdkseset"
                                        class="w-full px-2 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Langsir <span class="text-gray-400 font-normal">(Rp/kg)</span></label>
                                    <input type="number" step="0.01" name="langsir" x-model="form.langsir"
                                        class="w-full px-2 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0">
                            <button type="button" @click="open = false"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150">
                                Batal
                            </button>
                            <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-150">
                                <span x-text="mode === 'create' ? 'Simpan' : 'Perbarui'"></span>
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