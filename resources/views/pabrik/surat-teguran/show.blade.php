<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div x-data="{ confirmDelete: false }" class="mx-auto py-4">

        <!-- Back -->
        <div class="mb-4">
            <a href="{{ route('pabrik.surat-teguran.index') }}"
                class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Daftar
            </a>
        </div>

        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition
            class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            <strong class="font-bold">Berhasil!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" @click="show = false">&times;</span>
        </div>
        @endif

        <div class="bg-white rounded-md shadow-md">

            <!-- Title Bar -->
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">{{ $surat->stgno }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Dibuat oleh {{ $surat->inputby }} pada {{ $surat->createdat ? date('d/m/Y H:i', strtotime($surat->createdat)) : '-' }}</p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    @if($surat->status === 'draft')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Draft</span>
                        <!-- Edit -->
                        <a href="{{ route('pabrik.surat-teguran.edit', $surat->id) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </a>
                        <!-- Kirim -->
                        <form action="{{ route('pabrik.surat-teguran.send', $surat->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                Kirim
                            </button>
                        </form>
                        <!-- Hapus -->
                        <button @click="confirmDelete = true"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus
                        </button>
                    @elseif($surat->status === 'sent')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Terkirim</span>
                    @elseif($surat->status === 'read')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Sudah Dibaca</span>
                    @endif
                </div>
            </div>

            <!-- Detail Fields -->
            <div class="px-6 py-6 space-y-5">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Tanggal Surat</p>
                        <p class="text-sm text-gray-900">{{ $surat->stgdate ? date('d/m/Y', strtotime($surat->stgdate)) : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Company Tujuan</p>
                        <p class="text-sm text-gray-900 font-medium">{{ $surat->targetcompany }}</p>
                        @if($targetCompany)
                        <p class="text-xs text-gray-500">{{ $targetCompany->name }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Jenis Teguran</p>
                        <p class="text-sm text-gray-900">{{ $surat->jenisteguran ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Status Baca</p>
                        @if($surat->status === 'read')
                        <p class="text-sm text-gray-900">
                            Dibaca oleh <span class="font-medium">{{ $surat->readby }}</span>
                            pada {{ $surat->readat ? date('d/m/Y H:i', strtotime($surat->readat)) : '-' }}
                        </p>
                        @else
                        <p class="text-sm text-gray-400 italic">Belum dibaca</p>
                        @endif
                    </div>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Perihal</p>
                    <p class="text-sm text-gray-900">{{ $surat->perihal ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Isi Teguran</p>
                    <div class="bg-gray-50 border border-gray-200 rounded-md px-4 py-3 text-sm text-gray-800 whitespace-pre-wrap">{{ $surat->isiteguran ?? '-' }}</div>
                </div>

                <!-- Lampiran -->
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Lampiran</p>
                    @if($lampiranUrl)
                        @php $ext = strtolower(pathinfo($surat->lampiran, PATHINFO_EXTENSION)); @endphp
                        @if($ext === 'pdf')
                        <iframe src="{{ $lampiranUrl }}" class="w-full rounded-md border border-gray-200" style="height: 600px;"></iframe>
                        @else
                        <img src="{{ $lampiranUrl }}" alt="Lampiran" class="max-w-full rounded-md border border-gray-200 shadow-sm">
                        @endif
                        <div class="mt-2">
                            <a href="{{ $lampiranUrl }}" target="_blank"
                                class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Unduh Lampiran
                            </a>
                        </div>
                    @else
                    <p class="text-sm text-gray-400 italic">Tidak ada lampiran.</p>
                    @endif
                </div>

            </div>
        </div>

        <!-- Confirm Delete Modal -->
        <div x-show="confirmDelete" x-transition
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-sm w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Konfirmasi Hapus</h3>
                <p class="text-sm text-gray-600 mb-6">Hapus draft <strong>{{ $surat->stgno }}</strong>? Lampiran juga akan ikut terhapus.</p>
                <div class="flex justify-end gap-3">
                    <button @click="confirmDelete = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors">
                        Batal
                    </button>
                    <form action="{{ route('pabrik.surat-teguran.destroy', $surat->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-layout>
