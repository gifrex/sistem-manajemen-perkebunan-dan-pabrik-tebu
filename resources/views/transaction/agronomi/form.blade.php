<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    <x-slot:navnav>{{ $title }}</x-slot:navnav>

    @php $isEdit = isset($header); @endphp

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        .input-focus {
            transition: all 0.2s;
        }

        .input-focus:focus {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-hover {
            transition: all 0.2s;
        }

        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px -2px rgba(0, 0, 0, 0.15);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        [id$="Alert"] {
            animation: slideDown 0.3s ease-out;
        }

        html {
            scroll-behavior: smooth;
        }

        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            opacity: 1;
        }

        #scrollToTop.show {
            display: block !important;
        }

        /* Sticky delete column */
        .table-wrapper {
            overflow-x: auto;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }

        #listTable {
            white-space: nowrap;
            border-collapse: separate;
            border-spacing: 0;
        }

        #listTable th.col-aksi,
        #listTable td.col-aksi {
            position: sticky;
            right: 0;
            z-index: 2;
            background: #fff;
            border-left: 2px solid #e5e7eb;
            box-shadow: -2px 0 4px rgba(0, 0, 0, 0.06);
        }

        #listTable thead th.col-aksi {
            background: linear-gradient(to right, #f3f4f6, #f9fafb);
            z-index: 3;
        }

        #listTable tbody tr:hover td.col-aksi {
            background: linear-gradient(to left, #eff6ff, #eef2ff);
        }

        /* Group header styling */
        #listTable thead tr.row-group th {
            background: linear-gradient(to right, #e0e7ff, #dbeafe);
            font-size: 0.7rem;
            font-weight: 700;
            color: #3730a3;
            letter-spacing: 0.03em;
            border-bottom: 1px solid #c7d2fe;
        }

        #listTable thead tr.row-group th.group-green {
            background: linear-gradient(to right, #dcfce7, #d1fae5);
            color: #166534;
            border-bottom: 1px solid #a7f3d0;
        }

        #listTable thead tr.row-group th.group-orange {
            background: linear-gradient(to right, #ffedd5, #fef3c7);
            color: #92400e;
            border-bottom: 1px solid #fcd34d;
        }

        #listTable thead tr.row-group th.group-purple {
            background: linear-gradient(to right, #f3e8ff, #ede9fe);
            color: #6b21a8;
            border-bottom: 1px solid #d8b4fe;
        }

        #listTable thead tr.row-sub th {
            font-size: 0.7rem;
        }

        /* Cursor-not-allowed untuk readonly field */
        input.field-disabled {
            background-color: #f3f4f6 !important;
            cursor: not-allowed !important;
            color: #9ca3af !important;
            opacity: 0.7;
            pointer-events: none;
        }

        /* Toggle switch */
        .toggle-switch {
            position: relative;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
            position: absolute;
        }

        .toggle-track {
            width: 40px;
            height: 22px;
            background: #d1d5db;
            border-radius: 9999px;
            transition: background 0.2s;
            position: relative;
            flex-shrink: 0;
        }

        .toggle-thumb {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 16px;
            height: 16px;
            background: #fff;
            border-radius: 9999px;
            transition: transform 0.2s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .toggle-switch input:checked~.toggle-track {
            background: #2563eb;
        }

        .toggle-switch input:checked~.toggle-track .toggle-thumb {
            transform: translateX(18px);
        }
    </style>

    {{-- ── Alerts ── --}}
    @if (session('success'))
        <div id="successAlert"
            class="mx-2 md:mx-4 mb-3 flex items-center gap-3 p-3 md:p-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200 shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
            <button type="button" onclick="closeAlert('successAlert')"
                class="ml-auto text-green-800 hover:text-green-900"><svg class="w-4 h-4" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg></button>
        </div>
    @endif
    @if (session('error'))
        <div id="errorAlert"
            class="mx-2 md:mx-4 mb-3 flex items-center gap-3 p-3 md:p-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200 shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd" />
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
            <button type="button" onclick="closeAlert('errorAlert')"
                class="ml-auto text-red-800 hover:text-red-900"><svg class="w-4 h-4" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg></button>
        </div>
    @endif
    @if ($errors->any())
        <div id="validationAlert"
            class="mx-2 md:mx-4 mb-3 p-3 md:p-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200 shadow-sm">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
                <div class="flex-1">
                    <p class="font-semibold mb-2">Terdapat kesalahan validasi:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" onclick="closeAlert('validationAlert')"
                    class="text-red-800 hover:text-red-900"><svg class="w-4 h-4" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg></button>
            </div>
        </div>
    @endif
    @error('duplicate')
        <div id="duplicateAlert"
            class="mx-2 md:mx-4 mb-3 flex items-center gap-3 p-3 md:p-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200 shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd" />
            </svg>
            <span class="font-medium">{{ $message }}</span>
            <button type="button" onclick="closeAlert('duplicateAlert')"
                class="ml-auto text-red-800 hover:text-red-900"><svg class="w-4 h-4" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg></button>
        </div>
    @enderror

    <form action="{{ $url }}" method="POST">
        @csrf
        @method($method)

        {{-- ══════════════════════════════════════════════
             ROW: Header Utama + Opsional
        ══════════════════════════════════════════════ --}}
        <div class="mx-2 md:mx-4 mb-3 flex flex-col lg:flex-row gap-3 items-stretch">

            {{-- CARD: Header Utama --}}
            <div class="flex-1 p-3 md:p-4 glass-card rounded-xl shadow-xl border border-white/50">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200">
                    <h2 class="text-base md:text-lg font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Informasi Header
                    </h2>
                    <div class="flex gap-2">
                        <a href="{{ route('transaction.agronomi.index') }}"
                            class="btn-hover flex items-center gap-1.5 bg-red-600 text-white px-3 py-2 rounded-lg shadow-md hover:bg-red-700 font-medium text-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18 17.94 6M18 18 6.06 6" />
                            </svg>
                            <span>Batal</span>
                        </a>
                        <button type="submit"
                            class="btn-hover flex items-center gap-1.5 bg-green-600 text-white px-3 py-2 rounded-lg shadow-md hover:bg-green-700 font-medium text-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 11.917 9.724 16.5 19 7.5" />
                            </svg>
                            <span>{{ $buttonSubmit }}</span>
                        </button>
                    </div>
                </div>

                {{-- Row 1: No Sample + Plot --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 md:gap-3">
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-gray-700">Nomor Sample <span
                                class="text-red-600">*</span></label>
                        <input type="text" name="nosample"
                            class="input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            autocomplete="off" maxlength="4" value="{{ old('nosample', $header->nosample ?? '') }}"
                            placeholder="Masukkan nomor sample" required>
                    </div>
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-gray-700">Plot <span
                                class="text-red-600">*</span></label>
                        <input id="plot" type="text" name="plot" maxlength="10"
                            class="input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase placeholder:capitalize"
                            autocomplete="off" value="{{ old('plot', $header->plot ?? '') }}"
                            placeholder="Masukkan plot" required>
                    </div>
                </div>

                {{-- Row 2: Blok, Company, Varietas, Kategori --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 md:gap-3 mt-3">
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-gray-700">Blok <span
                                class="text-xs text-gray-500 italic">(Otomatis)</span></label>
                        <input id="blok" type="text" name="blok" maxlength="2"
                            class="border rounded-lg cursor-not-allowed focus:ring-0 focus:border-gray-300 border-gray-300 p-2 w-full text-sm bg-gray-50"
                            autocomplete="off" value="{{ old('blok', $header->blok ?? '') }}" placeholder="-"
                            readonly>
                    </div>
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-gray-700">Company <span
                                class="text-xs text-gray-500 italic">(Otomatis)</span></label>
                        <input id="companycode" type="text" name="companycode" maxlength="6"
                            class="border rounded-lg cursor-not-allowed focus:ring-0 focus:border-gray-300 border-gray-300 p-2 w-full text-sm bg-gray-50"
                            autocomplete="off"
                            value="{{ old('companycode', $header->companycode ?? session('companycode')) }}" readonly>
                    </div>
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-gray-700">Varietas <span
                                class="text-xs text-gray-500 italic">(Otomatis)</span> <span
                                class="text-red-600">*</span></label>
                        <input type="text" name="varietas" id="varietas"
                            class="input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            autocomplete="off" maxlength="10" value="{{ old('varietas', $header->varietas ?? '') }}"
                            placeholder="Varietas" required>
                    </div>
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-gray-700">Kategori <span
                                class="text-xs text-gray-500 italic">(Otomatis)</span> <span
                                class="text-red-600">*</span></label>
                        <input type="text" name="kat" id="kat"
                            class="input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            autocomplete="off" maxlength="3" value="{{ old('kat', $header->kat ?? '') }}"
                            placeholder="Kategori" required>
                    </div>
                </div>

                {{-- Row 3: PKP + Tanggal Tanam + Tanggal Pengamatan --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 md:gap-3 mt-3">
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-gray-700">
                            PKP / Jarak Tanam <span class="text-xs text-gray-500 italic">(Otomatis)</span>
                        </label>
                        <input type="number" step="any" name="pkp" id="pkp"
                            class="border rounded-lg cursor-not-allowed focus:ring-0 focus:border-gray-300 border-gray-300 p-2 w-full text-sm bg-gray-50"
                            autocomplete="off" value="{{ old('pkp', $header->pkp ?? '') }}" placeholder="-"
                            readonly>
                    </div>
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-gray-700">Tanggal Tanam <span
                                class="text-xs text-gray-500 italic">(Otomatis)</span> <span
                                class="text-red-600">*</span></label>
                        <input type="date" name="tanggaltanam" id="tanggaltanam"
                            value="{{ old('tanggaltanam', $header->tanggaltanam ?? '') }}"
                            class="input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-400 focus:text-black valid:text-black"
                            required>
                    </div>
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-gray-700">Tanggal Pengamatan <span
                                class="text-red-600">*</span></label>
                        <input type="date" name="tanggalpengamatan"
                            value="{{ old('tanggalpengamatan', $header->tanggalpengamatan ?? now()->toDateString()) }}"
                            class="input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-400 focus:text-black valid:text-black"
                            required>
                    </div>
                </div>
            </div>

            {{-- CARD: Opsional --}}
            <div
                class="lg:w-64 xl:w-72 p-3 md:p-4 glass-card rounded-xl shadow-xl border border-amber-200/80 flex flex-col">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-amber-100">
                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="text-sm font-bold text-gray-700 leading-tight">Informasi Tambahan</h2>
                    <span
                        class="ml-auto text-xs font-medium text-amber-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full flex-shrink-0">Opsional</span>
                </div>
                <div class="flex flex-col gap-2 md:gap-3 flex-1">
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-gray-700">Bulan Panen</label>
                        <select name="bulanpanen"
                            class="input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-amber-400 focus:border-amber-400 text-gray-700">
                            <option value="">-- Pilih Bulan --</option>
                            @php $bulanList = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; @endphp
                            @foreach ($bulanList as $bulan)
                                <option value="{{ $bulan }}"
                                    {{ old('bulanpanen', $header->bulanpanen ?? '') === $bulan ? 'selected' : '' }}>
                                    {{ $bulan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-gray-700">Umur Panen <span
                                class="text-xs text-gray-500 italic">(bulan)</span></label>
                        <input type="number" name="umurpanen" min="0"
                            class="input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-amber-400 focus:border-amber-400"
                            autocomplete="off" value="{{ old('umurpanen', $header->umurpanen ?? '') }}"
                            placeholder="Umur panen">
                    </div>
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-gray-700">Tanggal ZPK</label>
                        <input type="date" name="tanggalzpk"
                            value="{{ old('tanggalzpk', $header->tanggalzpk ?? '') }}"
                            class="input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-amber-400 focus:border-amber-400 text-gray-400 focus:text-black valid:text-black">
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════
             CARD: Detail Pengamatan
        ══════════════════════════════════════════════ --}}
        <div class="mx-2 md:mx-4 mb-3 p-3 md:p-4 glass-card rounded-xl shadow-xl border border-white/50">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200">
                <h2 class="text-base md:text-lg font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Detail Pengamatan (ni)
                </h2>
                <div class="flex items-center gap-3">
                    {{-- Switch Taksasi --}}
                    <label class="toggle-switch gap-2" title="Switch Taksasi">
                        <input type="checkbox" id="switchTaksasi">
                        <span class="toggle-track"><span class="toggle-thumb"></span></span>
                        <span class="text-sm font-semibold text-gray-600" id="switchLabel">
                            Taksasi <span class="text-gray-400 font-normal">OFF</span>
                        </span>
                    </label>
                    <button type="button" id="addRow"
                        class="btn-hover flex items-center gap-1.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-3 py-1.5 rounded-lg shadow-md hover:from-blue-700 hover:to-blue-800 text-sm font-medium">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd"
                                d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4.243a1 1 0 1 0-2 0V11H7.757a1 1 0 1 0 0 2H11v3.243a1 1 0 1 0 2 0V13h3.243a1 1 0 1 0 0-2H13V7.757Z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="hidden sm:inline">Tambah Baris</span>
                    </button>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="min-w-full bg-white" id="listTable">
                    <thead>
                        {{-- ROW 1: Group headers --}}
                        <tr class="row-group">
                            <th rowspan="2"
                                class="px-3 py-2 text-xs font-semibold text-gray-700 border-b-2 border-gray-300 bg-gradient-to-r from-gray-50 to-gray-100">
                                ni</th>
                            <th rowspan="2"
                                class="px-3 py-2 border-b-2 border-gray-300 bg-gradient-to-r from-gray-50 to-gray-100 text-xs font-semibold text-gray-700">
                                Jml Batang</th>
                            <th colspan="4" class="px-3 py-2 text-center border-b border-r border-indigo-200">
                                Jumlah Batang</th>
                            <th rowspan="2"
                                class="px-3 py-2 border-b-2 border-gray-300 bg-gradient-to-r from-gray-50 to-gray-100 text-xs font-semibold text-gray-700">
                                Panjang GAP</th>
                            <th rowspan="2"
                                class="px-3 py-2 border-b-2 border-gray-300 bg-gradient-to-r from-gray-50 to-gray-100 text-xs font-semibold text-gray-700">
                                pH Tanah</th>
                            <th rowspan="2"
                                class="px-3 py-2 border-b-2 border-gray-300 bg-gradient-to-r from-gray-50 to-gray-100 text-xs font-semibold text-gray-700">
                                Ktk Gulma</th>
                            <th colspan="4" class="px-3 py-2 text-center border-b border-r group-green">Tinggi
                                Batang</th>
                            <th colspan="4" class="px-3 py-2 text-center border-b border-r group-orange">Diameter
                                Batang</th>
                            <th colspan="4" class="px-3 py-2 text-center border-b border-r group-purple">Berat
                                Batang</th>
                            <th colspan="4" class="px-3 py-2 text-center border-b group-purple"
                                style="background:linear-gradient(to right,#fce7f3,#fdf2f8);color:#9d174d;border-bottom-color:#f9a8d4;">
                                Brix Batang</th>
                            <th rowspan="2"
                                class="col-aksi px-3 py-2 text-xs font-semibold text-gray-700 border-b-2 border-gray-300 text-center"
                                style="background:linear-gradient(to right,#f3f4f6,#f9fafb);">Aksi</th>
                        </tr>
                        {{-- ROW 2: Sub headers --}}
                        <tr class="row-sub">
                            <th
                                class="px-3 py-2 text-xs font-semibold text-indigo-700 border-b-2 border-indigo-300 bg-indigo-50">
                                Primer</th>
                            <th
                                class="px-3 py-2 text-xs font-semibold text-indigo-700 border-b-2 border-indigo-300 bg-indigo-50">
                                Sekunder</th>
                            <th
                                class="px-3 py-2 text-xs font-semibold text-indigo-700 border-b-2 border-indigo-300 bg-indigo-50">
                                Tersier</th>
                            <th
                                class="px-3 py-2 text-xs font-semibold text-indigo-700 border-b-2 border-indigo-300 border-r bg-indigo-50">
                                Kuarter</th>
                            <th
                                class="px-3 py-2 text-xs font-semibold text-green-700 border-b-2 border-green-300 bg-green-50">
                                Primer</th>
                            <th
                                class="px-3 py-2 text-xs font-semibold text-green-700 border-b-2 border-green-300 bg-green-50">
                                Sekunder</th>
                            <th
                                class="px-3 py-2 text-xs font-semibold text-green-700 border-b-2 border-green-300 bg-green-50">
                                Tersier</th>
                            <th
                                class="px-3 py-2 text-xs font-semibold text-green-700 border-b-2 border-green-300 border-r bg-green-50">
                                Kuarter</th>
                            <th
                                class="px-3 py-2 text-xs font-semibold text-orange-700 border-b-2 border-orange-300 bg-orange-50">
                                Primer</th>
                            <th
                                class="px-3 py-2 text-xs font-semibold text-orange-700 border-b-2 border-orange-300 bg-orange-50">
                                Sekunder</th>
                            <th
                                class="px-3 py-2 text-xs font-semibold text-orange-700 border-b-2 border-orange-300 bg-orange-50">
                                Tersier</th>
                            <th
                                class="px-3 py-2 text-xs font-semibold text-orange-700 border-b-2 border-orange-300 border-r bg-orange-50">
                                Kuarter</th>
                            <th
                                class="px-3 py-2 text-xs font-semibold text-purple-700 border-b-2 border-purple-300 bg-purple-50">
                                Primer</th>
                            <th
                                class="px-3 py-2 text-xs font-semibold text-purple-700 border-b-2 border-purple-300 bg-purple-50">
                                Sekunder</th>
                            <th
                                class="px-3 py-2 text-xs font-semibold text-purple-700 border-b-2 border-purple-300 bg-purple-50">
                                Tersier</th>
                            <th
                                class="px-3 py-2 text-xs font-semibold text-purple-700 border-b-2 border-purple-300 border-r bg-purple-50">
                                Kuarter</th>
                            <th class="px-3 py-2 text-xs font-semibold border-b-2 bg-pink-50"
                                style="color:#9d174d;border-color:#f9a8d4;">Primer</th>
                            <th class="px-3 py-2 text-xs font-semibold border-b-2 bg-pink-50"
                                style="color:#9d174d;border-color:#f9a8d4;">Sekunder</th>
                            <th class="px-3 py-2 text-xs font-semibold border-b-2 bg-pink-50"
                                style="color:#9d174d;border-color:#f9a8d4;">Tersier</th>
                            <th class="px-3 py-2 text-xs font-semibold border-b-2 bg-pink-50"
                                style="color:#9d174d;border-color:#f9a8d4;">Kuarter</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php
                            $niValues = [1, 3, 5, 7, 9];
                            $lists = $isEdit ? old('lists', $header->lists) : old('lists', array_fill(0, 5, []));
                        @endphp
                        @foreach ($lists as $index => $list)
                            @php $ni = $list->nourut ?? $niValues[$index] ?? (($index * 2) + 1); @endphp
                            <tr class="hover:bg-blue-50/40">
                                {{-- ni --}}
                                <td class="px-3 py-2">
                                    <input type="text" name="lists[{{ $index }}][nourut]"
                                        value="{{ $ni }}"
                                        class="w-12 px-2 py-1.5 border border-gray-300 rounded text-center bg-gray-50 text-gray-600 text-sm"
                                        readonly>
                                </td>
                                {{-- Jumlah Batang (manual, aktif saat Taksasi OFF) --}}
                                <td class="px-3 py-2">
                                    <input type="number" name="lists[{{ $index }}][jumlahbatang]"
                                        min="0" value="{{ $list->jumlahbatang ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm field-jmlbatang"
                                        autocomplete="off" required>
                                </td>
                                {{-- Jumlah Batang Primer–Kuarter (aktif saat Taksasi ON) --}}
                                <td class="px-3 py-2">
                                    <input type="number" name="lists[{{ $index }}][bat_primer]"
                                        min="0" value="{{ $list->bat_primer ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm field-taksasi"
                                        autocomplete="off">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" name="lists[{{ $index }}][bat_sekunder]"
                                        min="0" value="{{ $list->bat_sekunder ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm field-taksasi"
                                        autocomplete="off">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" name="lists[{{ $index }}][bat_tersier]"
                                        min="0" value="{{ $list->bat_tersier ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm field-taksasi"
                                        autocomplete="off">
                                </td>
                                <td class="px-3 py-2 border-r border-gray-200">
                                    <input type="number" name="lists[{{ $index }}][bat_kuarter]"
                                        min="0" value="{{ $list->bat_kuarter ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm field-taksasi"
                                        autocomplete="off">
                                </td>
                                {{-- Panjang GAP --}}
                                <td class="px-3 py-2">
                                    <input type="number" name="lists[{{ $index }}][pan_gap]" min="0"
                                        value="{{ $list->pan_gap ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                        autocomplete="off" required>
                                </td>
                                {{-- pH Tanah --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="0.1" name="lists[{{ $index }}][ph_tanah]"
                                        min="0" max="999.9" value="{{ $list->ph_tanah ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                        autocomplete="off" required>
                                </td>
                                {{-- Ktk Gulma --}}
                                <td class="px-3 py-2">
                                    <input type="number" name="lists[{{ $index }}][ktk_gulma]"
                                        min="0" value="{{ $list->ktk_gulma ?? 0 }}"
                                        class="w-16 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                        autocomplete="off" required>
                                </td>
                                {{-- Tinggi --}}
                                <td class="px-3 py-2">
                                    <input type="number" name="lists[{{ $index }}][t_primer]" min="0"
                                        value="{{ $list->t_primer ?? 0 }}"
                                        class="w-16 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                        autocomplete="off" required>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" name="lists[{{ $index }}][t_sekunder]"
                                        min="0" value="{{ $list->t_sekunder ?? 0 }}"
                                        class="w-16 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                        autocomplete="off" required>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" name="lists[{{ $index }}][t_tersier]"
                                        min="0" value="{{ $list->t_tersier ?? 0 }}"
                                        class="w-16 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                        autocomplete="off" required>
                                </td>
                                <td class="px-3 py-2 border-r border-gray-200">
                                    <input type="number" name="lists[{{ $index }}][t_kuarter]"
                                        min="0" value="{{ $list->t_kuarter ?? 0 }}"
                                        class="w-16 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                        autocomplete="off" required>
                                </td>
                                {{-- Diameter --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="any" name="lists[{{ $index }}][d_primer]"
                                        min="0" max="999.9" value="{{ $list->d_primer ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                        autocomplete="off" required>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" step="any"
                                        name="lists[{{ $index }}][d_sekunder]" min="0" max="999.99"
                                        value="{{ $list->d_sekunder ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                        autocomplete="off" required>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" step="any"
                                        name="lists[{{ $index }}][d_tersier]" min="0" max="999.999"
                                        value="{{ $list->d_tersier ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                        autocomplete="off" required>
                                </td>
                                <td class="px-3 py-2 border-r border-gray-200">
                                    <input type="number" step="any"
                                        name="lists[{{ $index }}][d_kuarter]" min="0" max="999.9999"
                                        value="{{ $list->d_kuarter ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                        autocomplete="off" required>
                                </td>
                                {{-- Berat (aktif saat Taksasi ON) --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="any"
                                        name="lists[{{ $index }}][berat_primer]" min="0"
                                        value="{{ $list->berat_primer ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm field-berat"
                                        autocomplete="off">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" step="any"
                                        name="lists[{{ $index }}][berat_sekunder]" min="0"
                                        value="{{ $list->berat_sekunder ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm field-berat"
                                        autocomplete="off">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" step="any"
                                        name="lists[{{ $index }}][berat_tersier]" min="0"
                                        value="{{ $list->berat_tersier ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm field-berat"
                                        autocomplete="off">
                                </td>
                                <td class="px-3 py-2 border-r border-gray-200">
                                    <input type="number" step="any"
                                        name="lists[{{ $index }}][berat_kuarter]" min="0"
                                        value="{{ $list->berat_kuarter ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm field-berat"
                                        autocomplete="off">
                                </td>
                                {{-- Brix (aktif saat Taksasi ON) --}}
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01"
                                        name="lists[{{ $index }}][brix_primer]" min="0"
                                        value="{{ $list->brix_primer ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm field-brix"
                                        autocomplete="off">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01"
                                        name="lists[{{ $index }}][brix_sekunder]" min="0"
                                        value="{{ $list->brix_sekunder ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm field-brix"
                                        autocomplete="off">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01"
                                        name="lists[{{ $index }}][brix_tersier]" min="0"
                                        value="{{ $list->brix_tersier ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm field-brix"
                                        autocomplete="off">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01"
                                        name="lists[{{ $index }}][brix_kuarter]" min="0"
                                        value="{{ $list->brix_kuarter ?? 0 }}"
                                        class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm field-brix"
                                        autocomplete="off">
                                </td>
                                {{-- Sticky Aksi --}}
                                <td class="col-aksi px-3 py-2 text-center">
                                    <button type="button"
                                        class="inline-flex items-center gap-1 px-2 py-1.5 text-red-600 hover:bg-red-50 border border-red-200 hover:border-red-300 rounded transition-colors remove-row"
                                        title="Hapus baris">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span class="text-xs font-medium hidden lg:inline">Hapus</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    <button onclick="scrollToTop()" id="scrollToTop"
        class="fixed bottom-6 right-6 p-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg shadow-lg hover:from-blue-700 hover:to-blue-800 transition-all z-50 hidden">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>

    <script>
        /* ── Helpers ── */
        function closeAlert(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.style.opacity = '0';
            el.style.transform = 'translateY(-10px)';
            setTimeout(() => el.remove(), 300);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const s = document.getElementById('successAlert');
            if (s) setTimeout(() => closeAlert('successAlert'), 5000);
            applyTaksasiState();
        });

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        window.addEventListener('scroll', () => {
            const btn = document.getElementById('scrollToTop');
            window.pageYOffset > 300 ?
                (btn.classList.remove('hidden'), btn.classList.add('show')) :
                (btn.classList.add('hidden'), btn.classList.remove('show'));
        });

        document.getElementById('plot').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });

        document.addEventListener('focusin', e => {
            if (e.target.matches('.auto-clear-zero') && e.target.value === '0') e.target.value = '';
        });
        document.addEventListener('focusout', e => {
            if (e.target.matches('.auto-clear-zero') && e.target.value.trim() === '') e.target.value = '0';
        });

        /* ── Taksasi Switch ── */
        const switchEl = document.getElementById('switchTaksasi');
        const switchLabel = document.getElementById('switchLabel');

        function setFieldState(inp, disabled) {
            // Jangan gunakan inp.disabled agar value tetap terkirim saat submit
            inp.readOnly = disabled;
            if (disabled) {
                inp.classList.add('field-disabled');
                inp.removeAttribute('required');
            } else {
                inp.classList.remove('field-disabled');
                // required hanya untuk field yang memang wajib (bukan berat/brix)
                if (inp.classList.contains('field-taksasi') || inp.classList.contains('field-jmlbatang')) {
                    inp.setAttribute('required', '');
                }
            }
        }

        function applyTaksasiState() {
            const on = switchEl.checked;
            switchLabel.innerHTML = on ?
                'Taksasi <span class="text-blue-600 font-semibold">ON</span>' :
                'Taksasi <span class="text-gray-400 font-normal">OFF</span>';

            document.querySelectorAll('#listTable tbody tr').forEach(row => {
                const jml = row.querySelector('.field-jmlbatang');
                const taks = row.querySelectorAll('.field-taksasi');
                const berat = row.querySelectorAll('.field-berat');
                const brix = row.querySelectorAll('.field-brix');

                setFieldState(jml, on); // Jml Batang: disabled saat ON
                taks.forEach(inp => setFieldState(inp, !on)); // Taksasi: disabled saat OFF
                berat.forEach(inp => setFieldState(inp, !on)); // Berat: disabled saat OFF
                brix.forEach(inp => setFieldState(inp, !on)); // Brix:  disabled saat OFF

                if (on) recalcJmlBatang(row);
                else taks.forEach(inp => {
                    if (inp.value === '') inp.value = '0';
                });
            });
        }

        function recalcJmlBatang(row) {
            const taks = row.querySelectorAll('.field-taksasi');
            const total = Array.from(taks).reduce((s, inp) => s + (parseFloat(inp.value) || 0), 0);
            row.querySelector('.field-jmlbatang').value = total;
        }

        switchEl.addEventListener('change', applyTaksasiState);

        document.getElementById('listTable').addEventListener('input', e => {
            if (e.target.classList.contains('field-taksasi') && switchEl.checked) {
                recalcJmlBatang(e.target.closest('tr'));
            }
        });

        /* ── Row Numbering ── */
        function resetRowNumbers() {
            document.querySelectorAll('#listTable tbody tr').forEach((row, i) => {
                const ni = row.querySelector('input[name$="[nourut]"]');
                if (ni) ni.value = (i * 2) + 1;
                row.querySelectorAll('input').forEach(inp => {
                    inp.name = inp.name.replace(/lists\[\d+\]/, `lists[${i}]`);
                });
            });
        }

        /* ── Build Row ── */
        function buildRow(index, ni) {
            const taksOn = switchEl.checked;

            // Gunakan readonly saja (bukan disabled) agar value tetap terkirim saat submit
            const jmlAttr = taksOn ?
                'readonly class="w-20 px-2 py-1.5 border border-gray-300 rounded auto-clear-zero text-sm field-jmlbatang field-disabled"' :
                'class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm field-jmlbatang" required';
            const taksAttr = !taksOn ?
                'readonly class="w-20 px-2 py-1.5 border border-gray-300 rounded auto-clear-zero text-sm field-taksasi field-disabled"' :
                'class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm field-taksasi" required';
            const beratAttr = !taksOn ?
                'readonly class="w-20 px-2 py-1.5 border border-gray-300 rounded auto-clear-zero text-sm field-berat field-disabled"' :
                'class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm field-berat"';
            const brixAttr = !taksOn ?
                'readonly class="w-20 px-2 py-1.5 border border-gray-300 rounded auto-clear-zero text-sm field-brix field-disabled"' :
                'class="w-20 px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm field-brix"';

            const std = (name, w = 'w-20', extra = '') =>
                `<td class="px-3 py-2 ${extra}"><input type="number" name="lists[${index}][${name}]" min="0" value="0" class="${w} px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm" autocomplete="off" required></td>`;
            const stdStep = (name, step, max = '', w = 'w-20', extra = '') =>
                `<td class="px-3 py-2 ${extra}"><input type="number" step="${step}" ${max} name="lists[${index}][${name}]" min="0" value="0" class="${w} px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm" autocomplete="off" required></td>`;

            return `<tr class="hover:bg-blue-50/40">
                <td class="px-3 py-2"><input type="text" name="lists[${index}][nourut]" value="${ni}" class="w-12 px-2 py-1.5 border border-gray-300 rounded text-center bg-gray-50 text-gray-600 text-sm" readonly></td>
                <td class="px-3 py-2"><input type="number" name="lists[${index}][jumlahbatang]" min="0" value="0" autocomplete="off" ${jmlAttr}></td>
                <td class="px-3 py-2"><input type="number" name="lists[${index}][bat_primer]"   min="0" value="0" autocomplete="off" ${taksAttr}></td>
                <td class="px-3 py-2"><input type="number" name="lists[${index}][bat_sekunder]" min="0" value="0" autocomplete="off" ${taksAttr}></td>
                <td class="px-3 py-2"><input type="number" name="lists[${index}][bat_tersier]"  min="0" value="0" autocomplete="off" ${taksAttr}></td>
                <td class="px-3 py-2 border-r border-gray-200"><input type="number" name="lists[${index}][bat_kuarter]" min="0" value="0" autocomplete="off" ${taksAttr}></td>
                ${std('pan_gap')}
                ${stdStep('ph_tanah', '0.1', 'max="999.9"')}
                ${std('ktk_gulma', 'w-16')}
                ${std('t_primer', 'w-16')}
                ${std('t_sekunder', 'w-16')}
                ${std('t_tersier', 'w-16')}
                ${std('t_kuarter', 'w-16', 'border-r border-gray-200')}
                ${stdStep('d_primer',   'any', 'max="999.9"')}
                ${stdStep('d_sekunder', 'any', 'max="999.99"')}
                ${stdStep('d_tersier',  'any', 'max="999.999"')}
                ${stdStep('d_kuarter',  'any', 'max="999.9999"', 'w-20', 'border-r border-gray-200')}
                <td class="px-3 py-2"><input type="number" step="any" name="lists[${index}][berat_primer]"   min="0" value="0" autocomplete="off" ${beratAttr}></td>
                <td class="px-3 py-2"><input type="number" step="any" name="lists[${index}][berat_sekunder]" min="0" value="0" autocomplete="off" ${beratAttr}></td>
                <td class="px-3 py-2"><input type="number" step="any" name="lists[${index}][berat_tersier]"  min="0" value="0" autocomplete="off" ${beratAttr}></td>
                <td class="px-3 py-2 border-r border-gray-200"><input type="number" step="any" name="lists[${index}][berat_kuarter]" min="0" value="0" autocomplete="off" ${beratAttr}></td>
                <td class="px-3 py-2"><input type="number" step="0.01" name="lists[${index}][brix_primer]"   min="0" value="0" autocomplete="off" ${brixAttr}></td>
                <td class="px-3 py-2"><input type="number" step="0.01" name="lists[${index}][brix_sekunder]" min="0" value="0" autocomplete="off" ${brixAttr}></td>
                <td class="px-3 py-2"><input type="number" step="0.01" name="lists[${index}][brix_tersier]"  min="0" value="0" autocomplete="off" ${brixAttr}></td>
                <td class="px-3 py-2"><input type="number" step="0.01" name="lists[${index}][brix_kuarter]"  min="0" value="0" autocomplete="off" ${brixAttr}></td>
                <td class="col-aksi px-3 py-2 text-center">
                    <button type="button" class="inline-flex items-center gap-1 px-2 py-1.5 text-red-600 hover:bg-red-50 border border-red-200 hover:border-red-300 rounded transition-colors remove-row" title="Hapus baris">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span class="text-xs font-medium hidden lg:inline">Hapus</span>
                    </button>
                </td>
            </tr>`;
        }

        document.getElementById('addRow').addEventListener('click', () => {
            const tbody = document.querySelector('#listTable tbody');
            const i = tbody.rows.length;
            tbody.insertAdjacentHTML('beforeend', buildRow(i, (i * 2) + 1));
        });

        document.getElementById('listTable').addEventListener('click', e => {
            if (e.target.closest('.remove-row')) {
                e.target.closest('tr').remove();
                resetRowNumbers();
            }
        });

        /* ── Notification ── */
        function showNotification(message, type = 'error') {
            const ok = type === 'success';
            const color = ok ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
            const icon = ok ?
                `<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>` :
                `<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>`;
            const el = document.createElement('div');
            el.className =
                `mx-2 md:mx-4 mb-3 flex items-center gap-3 p-3 md:p-4 text-sm rounded-lg border shadow-sm ${color}`;
            el.style.animation = 'slideDown 0.3s ease-out';
            el.innerHTML =
                `${icon}<span class="font-medium">${message}</span><button type="button" onclick="this.parentElement.remove()" class="ml-auto hover:opacity-75"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>`;
            document.querySelector('form').insertAdjacentElement('beforebegin', el);
            setTimeout(() => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(-10px)';
                setTimeout(() => el.remove(), 300);
            }, 5000);
        }
    </script>

    <script>
        $(document).ready(function() {
            $('#plot').on('change', function() {
                const plot = $(this).val();
                const token = $('meta[name="csrf-token"]').attr('content');

                if (!plot) {
                    $('#blok, #varietas, #kat, #tanggaltanam, #pkp').val('');
                    return;
                }

                // Ambil blok
                $.ajax({
                    url: "{{ route('transaction.agronomi.getBlok') }}",
                    type: 'POST',
                    data: {
                        _token: token,
                        plot
                    },
                    success: r => $('#blok').val(r.blok),
                    error: () => {
                        showNotification('Data Mapping tidak ditemukan', 'error');
                        $('#blok').val('');
                    }
                });

                // Ambil varietas, kategori, tanggal tanam, pkp
                $.ajax({
                    url: "{{ route('transaction.agronomi.getVar') }}",
                    type: 'POST',
                    data: {
                        _token: token,
                        plot
                    },
                    success: r => {
                        $('#varietas').val(r.varietas);
                        $('#kat').val(r.kat);
                        $('#tanggaltanam').val(r.tanggaltanam);
                        $('#pkp').val(r.pkp ?? '');
                    },
                    error: () => {
                        showNotification(
                            'Varietas, Kategori, Tanggal Tanam, dan PKP tidak ditemukan',
                            'error');
                        $('#varietas, #kat, #tanggaltanam, #pkp').val('');
                    }
                });
            });
        });
    </script>

</x-layout>
