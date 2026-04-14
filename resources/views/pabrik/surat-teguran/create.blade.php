<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-4">

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

        <div class="bg-white rounded-md shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-800">Buat Surat Teguran Baru</h2>
                <p class="text-xs text-gray-500 mt-1">Surat akan langsung terkirim ke kebun tujuan setelah disimpan.</p>
            </div>

            <form method="POST" action="{{ route('pabrik.surat-teguran.store') }}"
                enctype="multipart/form-data" x-data="suratTeguranForm()" class="px-6 py-6 space-y-5">
                @csrf

                @if($errors->any())
                <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Company Tujuan -->
                <div>
                    <label for="targetcompany" class="block text-sm font-medium text-gray-700 mb-1">
                        Company Tujuan <span class="text-red-500">*</span>
                    </label>
                    <select name="targetcompany" id="targetcompany"
                        class="w-full sm:w-72 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2 @error('targetcompany') border-red-500 @enderror">
                        <option value="">-- Pilih Company --</option>
                        @foreach($companies as $company)
                        <option value="{{ $company->companycode }}"
                            {{ old('targetcompany') === $company->companycode ? 'selected' : '' }}>
                            {{ $company->companycode }} - {{ $company->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('targetcompany')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenis Teguran -->
                <div>
                    <label for="jenisteguran" class="block text-sm font-medium text-gray-700 mb-1">
                        Jenis Teguran <span class="text-red-500">*</span>
                    </label>
                    <select name="jenisteguran" id="jenisteguran"
                        class="w-full sm:w-72 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2 @error('jenisteguran') border-red-500 @enderror">
                        <option value="">-- Pilih Jenis --</option>
                        <option value="REJECT" {{ old('jenisteguran') === 'REJECT' ? 'selected' : '' }}>REJECT</option>
                        <option value="KOTOR" {{ old('jenisteguran') === 'KOTOR' ? 'selected' : '' }}>KOTOR</option>
                    </select>
                    @error('jenisteguran')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Perihal -->
                <div>
                    <label for="perihal" class="block text-sm font-medium text-gray-700 mb-1">
                        Perihal <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="perihal" id="perihal"
                        value="{{ old('perihal') }}"
                        placeholder="Perihal surat teguran"
                        maxlength="255"
                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2 @error('perihal') border-red-500 @enderror">
                    @error('perihal')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Isi Teguran -->
                <div>
                    <label for="isiteguran" class="block text-sm font-medium text-gray-700 mb-1">
                        Isi Teguran <span class="text-red-500">*</span>
                    </label>
                    <textarea name="isiteguran" id="isiteguran" rows="6"
                        placeholder="Tuliskan isi surat teguran di sini..."
                        class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2 @error('isiteguran') border-red-500 @enderror">{{ old('isiteguran') }}</textarea>
                    @error('isiteguran')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Lampiran -->
                <div>
                    <label for="lampiran" class="block text-sm font-medium text-gray-700 mb-1">
                        Lampiran
                        <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <div class="flex items-start gap-4">
                        <div>
                            <input type="file" name="lampiran" id="lampiran"
                                accept=".jpeg,.jpg,.png,.pdf"
                                @change="handleFile($event)"
                                class="block text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('lampiran') border border-red-500 rounded @enderror">
                            <p class="mt-1 text-xs text-gray-500">Format: JPEG, JPG, PNG, PDF &bull; Maks. 5 MB</p>
                            @error('lampiran')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div x-show="fileName" x-transition class="text-sm text-gray-600 flex items-center gap-1 mt-2">
                            <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            <span x-text="fileName" class="truncate max-w-xs"></span>
                            <span x-text="fileSize" class="text-gray-400 text-xs shrink-0"></span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium transition-colors">
                        Kirim Surat Teguran
                    </button>
                    <a href="{{ route('pabrik.surat-teguran.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>

    <script>
        function suratTeguranForm() {
            return {
                fileName: '',
                fileSize: '',
                handleFile(event) {
                    const file = event.target.files[0];
                    if (!file) { this.fileName = ''; this.fileSize = ''; return; }
                    this.fileName = file.name;
                    const kb = file.size / 1024;
                    this.fileSize = kb > 1024
                        ? `(${(kb / 1024).toFixed(1)} MB)`
                        : `(${kb.toFixed(0)} KB)`;
                }
            }
        }
    </script>
</x-layout>
