<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    <x-slot:navnav>{{ $title }}</x-slot:navnav>

    @php
        $isEdit = isset($header);
    @endphp

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

        table {
            white-space: nowrap;
        }

        #scrollToTop.show {
            display: block !important;
        }
    </style>

    <!-- Success Notification -->
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
                class="ml-auto text-green-800 hover:text-green-900">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    @endif

    <!-- Error Notification -->
    @if (session('error'))
        <div id="errorAlert"
            class="mx-2 md:mx-4 mb-3 flex items-center gap-3 p-3 md:p-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200 shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd" />
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
            <button type="button" onclick="closeAlert('errorAlert')" class="ml-auto text-red-800 hover:text-red-900">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    @endif

    <!-- Validation Errors -->
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
                <button type="button" onclick="closeAlert('validationAlert')" class="text-red-800 hover:text-red-900">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <form action="{{ $url }}" method="POST">
        @csrf
        @method($method)

        <!-- Header Section -->
        <div class="mx-2 md:mx-4 mb-3 p-3 md:p-4 glass-card rounded-xl shadow-xl border border-white/50">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200">
                <h2 class="text-base md:text-lg font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Informasi Header
                </h2>
                <div class="flex flex-row gap-2">
                    <a href="{{ route('transaction.hpt.index') }}"
                        class="btn-hover flex items-center justify-center gap-1.5 bg-red-600 text-white px-3 py-2 rounded-lg shadow-md hover:bg-red-700 font-medium text-sm">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18 17.94 6M18 18 6.06 6" />
                        </svg>
                        <span>Batal</span>
                    </a>
                    <button type="submit"
                        class="btn-hover flex items-center justify-center gap-1.5 bg-green-600 text-white px-3 py-2 rounded-lg shadow-md hover:bg-green-700 font-medium text-sm">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 11.917 9.724 16.5 19 7.5" />
                        </svg>
                        <span>{{ $buttonSubmit }}</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 md:gap-3">
                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-700">
                        Nomor Sample <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="nosample"
                        class="input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        autocomplete="off" maxlength="4" value="{{ old('nosample', $header->nosample ?? '') }}"
                        placeholder="Masukkan nomor sample" required>
                </div>
                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-700">
                        Plot <span class="text-red-600">*</span>
                    </label>
                    <input id="plot" type="text" name="plot" maxlength="10"
                        class="input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase placeholder:capitalize"
                        autocomplete="off" value="{{ old('plot', $header->plot ?? '') }}"
                        placeholder="Masukkan plot" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 md:gap-3 mt-3">
                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-700">
                        Blok <span class="text-xs text-gray-500 italic">(Otomatis)</span>
                    </label>
                    <input id="blok" type="text" name="blok" maxlength="2"
                        class="border rounded-lg cursor-not-allowed focus:ring-0 focus:border-gray-300 border-gray-300 p-2 w-full text-sm bg-gray-50"
                        autocomplete="off" value="{{ old('blok', $header->blok ?? '') }}" placeholder="-" readonly>
                </div>
                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-700">
                        Company <span class="text-xs text-gray-500 italic">(Otomatis)</span>
                    </label>
                    <input id="companycode" type="text" name="companycode" maxlength="6"
                        class="border rounded-lg cursor-not-allowed focus:ring-0 focus:border-gray-300 border-gray-300 p-2 w-full text-sm bg-gray-50"
                        autocomplete="off"
                        value="{{ old('companycode', $header->companycode ?? session('companycode')) }}" readonly>
                </div>
                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-700">
                        Varietas <span class="text-xs text-gray-500 italic">(Otomatis)</span> <span
                            class="text-red-600">*</span>
                    </label>
                    <input type="text" name="varietas" id="varietas"
                        class="input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        autocomplete="off" maxlength="10" value="{{ old('varietas', $header->varietas ?? '') }}"
                        placeholder="Masukkan Varietas" required>
                </div>
                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-700">
                        Kategori <span class="text-xs text-gray-500 italic">(Otomatis)</span> <span
                            class="text-red-600">*</span>
                    </label>
                    <input type="text" name="kat" id="kat"
                        class="input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        autocomplete="off" maxlength="3" value="{{ old('kat', $header->kat ?? '') }}"
                        placeholder="Masukkan Kategori" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 md:gap-3 mt-3">
                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-700">
                        Tanggal Tanam <span class="text-xs text-gray-500 italic">(Otomatis)</span> <span
                            class="text-red-600">*</span>
                    </label>
                    <input type="date" name="tanggaltanam" id="tanggaltanam"
                        value="{{ old('tanggaltanam', $header->tanggaltanam ?? '') }}"
                        class="input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-400 focus:text-black valid:text-black"
                        required>
                </div>
                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-700">
                        Tanggal Pengamatan <span class="text-red-600">*</span>
                    </label>
                    <input type="date" name="tanggalpengamatan"
                        value="{{ old('tanggalpengamatan', $header->tanggalpengamatan ?? now()->toDateString()) }}"
                        class="input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-400 focus:text-black valid:text-black"
                        required>
                </div>
            </div>
        </div>

        <!-- Detail Section -->
        <div class="mx-2 md:mx-4 mb-3 p-3 md:p-4 glass-card rounded-xl shadow-xl border border-white/50">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200">
                <h2 class="text-base md:text-lg font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Detail Pengamatan (ni)
                </h2>
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

            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full bg-white" id="listTable">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">ni
                            </th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">PPT
                                Aktif</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">PBT
                                Aktif</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Skor 0
                            </th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Skor 1
                            </th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Skor 2
                            </th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Skor 3
                            </th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Skor 4
                            </th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Telur
                                PPT</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Larva
                                PPT 1</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Larva
                                PPT 2</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Larva
                                PPT 3</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Larva
                                PPT 4</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Pupa
                                PPT</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">
                                Ngengat PPT</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Kosong
                                PPT</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Telur
                                PBT</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Larva
                                PBT 1</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Larva
                                PBT 2</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Larva
                                PBT 3</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Larva
                                PBT 4</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Pupa
                                PBT</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">
                                Ngengat PBT</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Kosong
                                PBT</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">DH
                            </th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">DT
                            </th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">KBP
                            </th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">KBB
                            </th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">KP
                            </th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Cabuk
                            </th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">
                                Belalang</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">BTG
                                Terserang Ul.Grayak</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">Jumlah
                                Ul.Grayak</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">BTG
                                Terserang SMUT</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">SMUT
                                Stadia 1</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">SMUT
                                Stadia 2</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">SMUT
                                Stadia 3</th>
                            <th
                                class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200 text-center sticky right-0 bg-gradient-to-r from-gray-50 to-gray-100">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php
                            $lists = old('lists', $isEdit ? $header->lists : [[]]);
                        @endphp
                        @foreach ($lists as $index => $list)
                            <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50">
                                <td class="px-3 py-2">
                                    <input type="text" name="lists[{{ $index }}][nourut]"
                                        value="{{ $list->nourut ?? $index + 1 }}"
                                        class="w-full px-2 py-1.5 border border-gray-300 rounded text-center bg-gradient-to-br from-gray-50 to-gray-100 text-gray-600 text-sm"
                                        readonly>
                                </td>
                                @php
                                    $fields = [
                                        'ppt_aktif',
                                        'pbt_aktif',
                                        'skor0',
                                        'skor1',
                                        'skor2',
                                        'skor3',
                                        'skor4',
                                        'telur_ppt',
                                        'larva_ppt1',
                                        'larva_ppt2',
                                        'larva_ppt3',
                                        'larva_ppt4',
                                        'pupa_ppt',
                                        'ngengat_ppt',
                                        'kosong_ppt',
                                        'telur_pbt',
                                        'larva_pbt1',
                                        'larva_pbt2',
                                        'larva_pbt3',
                                        'larva_pbt4',
                                        'pupa_pbt',
                                        'ngengat_pbt',
                                        'kosong_pbt',
                                        'dh',
                                        'dt',
                                        'kbp',
                                        'kbb',
                                        'kp',
                                        'cabuk',
                                        'belalang',
                                        'serang_grayak',
                                        'jum_grayak',
                                        'serang_smut',
                                        'smut_stadia1',
                                        'smut_stadia2',
                                        'smut_stadia3',
                                    ];
                                @endphp
                                @foreach ($fields as $field)
                                    <td class="px-3 py-2">
                                        <input type="number" name="lists[{{ $index }}][{{ $field }}]"
                                            min="0" value="{{ $list->$field ?? 0 }}"
                                            class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                            autocomplete="off" required>
                                    </td>
                                @endforeach
                                <td class="px-3 py-2 text-center sticky right-0 bg-white">
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

    <!-- Scroll to Top Button -->
    <button onclick="scrollToTop()" id="scrollToTop"
        class="fixed bottom-6 right-6 p-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg shadow-lg hover:from-blue-700 hover:to-blue-800 transition-all z-50 hidden">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>

    <script>
        // Close Alert Function
        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            }
        }

        // Auto-hide success alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.getElementById('successAlert');
            if (successAlert) {
                setTimeout(() => closeAlert('successAlert'), 5000);
            }
        });

        // Add Row Function
        document.getElementById('addRow').addEventListener('click', () => {
            const table = document.getElementById('listTable').querySelector('tbody');
            const rowCount = table.rows.length + 1;
            const fields = ['ppt_aktif', 'pbt_aktif', 'skor0', 'skor1', 'skor2', 'skor3', 'skor4',
                'telur_ppt', 'larva_ppt1', 'larva_ppt2', 'larva_ppt3', 'larva_ppt4',
                'pupa_ppt', 'ngengat_ppt', 'kosong_ppt', 'telur_pbt', 'larva_pbt1',
                'larva_pbt2', 'larva_pbt3', 'larva_pbt4', 'pupa_pbt', 'ngengat_pbt',
                'kosong_pbt', 'dh', 'dt', 'kbp', 'kbb', 'kp', 'cabuk', 'belalang',
                'serang_grayak', 'jum_grayak', 'serang_smut', 'smut_stadia1',
                'smut_stadia2', 'smut_stadia3'
            ];

            let inputs = '';
            fields.forEach(field => {
                inputs += `
                <td class="px-3 py-2">
                    <input type="number" name="lists[${rowCount}][${field}]" min="0" value="0"
                        class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                        autocomplete="off" required>
                </td>`;
            });

            const newRow = `
            <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50">
                <td class="px-3 py-2">
                    <input type="text" name="lists[${rowCount}][nourut]" value="${rowCount}"
                        class="w-full px-2 py-1.5 border border-gray-300 rounded text-center bg-gradient-to-br from-gray-50 to-gray-100 text-gray-600 text-sm" readonly>
                </td>
                ${inputs}
                <td class="px-3 py-2 text-center sticky right-0 bg-white">
                    <button type="button" 
                        class="inline-flex items-center gap-1 px-2 py-1.5 text-red-600 hover:bg-red-50 border border-red-200 hover:border-red-300 rounded transition-colors remove-row"
                        title="Hapus baris">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span class="text-xs font-medium hidden lg:inline">Hapus</span>
                    </button>
                </td>
            </tr>`;
            table.insertAdjacentHTML('beforeend', newRow);
        });

        // Remove Row Function
        document.getElementById('listTable').addEventListener('click', (e) => {
            if (e.target.closest('.remove-row')) {
                e.target.closest('tr').remove();
                resetRowNumbers();
            }
        });

        // Auto Clear Zero on Focus
        document.addEventListener('focusin', function(e) {
            if (e.target.matches('.auto-clear-zero') && e.target.value === '0') {
                e.target.value = '';
            }
        });

        document.addEventListener('focusout', function(e) {
            if (e.target.matches('.auto-clear-zero') && e.target.value.trim() === '') {
                e.target.value = '0';
            }
        });

        // Reset Row Numbers
        function resetRowNumbers() {
            const rows = document.querySelectorAll('#listTable tbody tr');
            rows.forEach((row, index) => {
                const noUrutInput = row.querySelector('input[name^="lists"][name$="[nourut]"]');
                noUrutInput.value = index + 1;
            });
        }

        // Scroll to Top Function
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Show/Hide Scroll to Top Button
        window.addEventListener('scroll', function() {
            const scrollBtn = document.getElementById('scrollToTop');
            if (window.pageYOffset > 300) {
                scrollBtn.classList.remove('hidden');
                scrollBtn.classList.add('show');
            } else {
                scrollBtn.classList.add('hidden');
                scrollBtn.classList.remove('show');
            }
        });

        // Plot to Uppercase
        document.getElementById('plot').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });

        // Function to show dynamic notifications
        function showNotification(message, type = 'error') {
            const iconSuccess = `<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>`;

            const iconError = `<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>`;

            const bgColor = type === 'success' ? 'bg-green-50 border-green-200 text-green-800' :
                'bg-red-50 border-red-200 text-red-800';
            const icon = type === 'success' ? iconSuccess : iconError;

            const notification = document.createElement('div');
            notification.className =
                `mx-2 md:mx-4 mb-3 flex items-center gap-3 p-3 md:p-4 text-sm rounded-lg border shadow-sm ${bgColor}`;
            notification.style.animation = 'slideDown 0.3s ease-out';
            notification.innerHTML = `
                ${icon}
                <span class="font-medium">${message}</span>
                <button type="button" onclick="this.parentElement.remove()" class="ml-auto hover:opacity-75">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            `;

            document.querySelector('form').insertAdjacentElement('beforebegin', notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-10px)';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }
    </script>

    <script>
        $(document).ready(function() {
            // === SCRIPT: GET BLOK & GET VARIETAS ===
            $('#plot').change(function() {
                const plot = $(this).val();

                if (plot) {
                    // === AJAX 1: GET BLOK ===
                    $.ajax({
                        url: "{{ route('transaction.hpt.getBlok') }}",
                        type: "POST",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            plot: plot
                        },
                        success: function(response) {
                            $('#blok').val(response.blok);
                        },
                        error: function() {
                            showNotification('Data Mapping tidak ditemukan', 'error');
                            $('#blok').val('');
                        }
                    });

                    // === AJAX 2: GET VARIETAS, KAT, TANGGAL TANAM ===
                    $.ajax({
                        url: "{{ route('transaction.hpt.getVar') }}",
                        type: "POST",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            plot: plot
                        },
                        success: function(response) {
                            $('#varietas').val(response.varietas);
                            $('#kat').val(response.kat);
                            $('#tanggaltanam').val(response.tanggaltanam);
                        },
                        error: function() {
                            showNotification(
                                'Varietas, Kategori, dan Tanggal Tanam tidak ditemukan',
                                'error');
                            $('#varietas').val('');
                            $('#kat').val('');
                            $('#tanggaltanam').val('');
                        }
                    });

                } else {
                    // Jika plot kosong, kosongkan semua field terkait
                    $('#blok, #varietas, #kat, #tanggaltanam').val('');
                }
            });
        });
    </script>

</x-layout>
