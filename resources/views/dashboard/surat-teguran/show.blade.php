<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div x-data="suratTeguranDetail()" x-init="init()" class="mx-auto py-4">

        <!-- Back -->
        <div class="mb-4">
            <a href="{{ route('dashboard.surat-teguran.index') }}"
                class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Daftar
            </a>
        </div>

        <div class="bg-white rounded-md shadow-md">

            <!-- Title Bar -->
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">{{ $surat->stgno }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Diterima dari <span class="font-medium">{{ $surat->companycode }}</span>
                        @if($fromCompany) ({{ $fromCompany->name }}) @endif
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    @if($surat->status === 'read')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Sudah Dibaca
                    </span>
                    @else
                    <button @click="markAsRead()"
                        :disabled="loading"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span x-show="!loading">Tandai Sudah Dibaca</span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                    @endif
                </div>
            </div>

            <!-- Alert -->
            <div x-show="alertMsg" x-transition class="mx-6 mt-4">
                <div :class="alertSuccess ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'"
                    class="border px-4 py-3 rounded relative text-sm">
                    <span x-text="alertMsg"></span>
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
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Jenis Teguran</p>
                        <p class="text-sm text-gray-900">{{ $surat->jenisteguran ?? '-' }}</p>
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

                @if($surat->status === 'read')
                <div class="pt-3 border-t border-gray-100">
                    <p class="text-xs text-gray-400">
                        Dibaca oleh <span class="font-medium text-gray-600">{{ $surat->readby }}</span>
                        pada {{ $surat->readat ? date('d/m/Y H:i', strtotime($surat->readat)) : '-' }}
                    </p>
                </div>
                @endif

            </div>
        </div>
    </div>

    <script>
        function suratTeguranDetail() {
            return {
                loading: false,
                alertMsg: '',
                alertSuccess: true,
                init() {},
                async markAsRead() {
                    this.loading = true;
                    this.alertMsg = '';
                    try {
                        const res = await fetch('{{ route('dashboard.surat-teguran.mark-read', $surat->id) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                        });
                        const json = await res.json();
                        this.alertSuccess = json.success;
                        this.alertMsg = json.message;
                        if (json.success) {
                            setTimeout(() => window.location.reload(), 800);
                        }
                    } catch (e) {
                        this.alertSuccess = false;
                        this.alertMsg = 'Terjadi kesalahan. Silakan coba lagi.';
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</x-layout>
