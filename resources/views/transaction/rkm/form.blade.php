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

        .custom-dropdown {
            position: relative;
            z-index: 10;
        }

        .custom-dropdown.active {
            z-index: 1000;
        }

        .custom-dropdown-trigger {
            cursor: pointer;
            transition: all 0.2s;
        }

        .custom-dropdown-trigger:hover {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .custom-dropdown-menu {
            position: absolute;
            top: 100% !important;
            bottom: auto !important;
            left: 0;
            right: 0;
            z-index: 1001;
            margin-top: 0.25rem;
            max-height: 280px;
            overflow-y: auto;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            display: none;
        }

        .custom-dropdown-menu.active {
            display: block;
            animation: slideDown 0.2s ease-out;
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

        .custom-dropdown-search {
            position: sticky;
            top: 0;
            background: linear-gradient(to bottom, #fff 0%, #fafafa 100%);
            padding: 0.5rem;
            border-bottom: 1px solid #e5e7eb;
            z-index: 1;
            backdrop-filter: blur(8px);
        }

        .custom-dropdown-item {
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            transition: all 0.15s;
            border-bottom: 1px solid #f9fafb;
        }

        .custom-dropdown-item:last-child {
            border-bottom: none;
        }

        .custom-dropdown-item:hover {
            background: linear-gradient(to right, #eff6ff, #dbeafe);
            /* transform: translateX(2px); */
        }

        .custom-dropdown-item-code {
            font-weight: 600;
            color: #1f2937;
            font-size: 0.813rem;
        }

        .custom-dropdown-item-name {
            color: #6b7280;
            font-size: 0.75rem;
            margin-top: 0.125rem;
        }

        .custom-dropdown-no-results {
            padding: 1.5rem 1rem;
            text-align: center;
            color: #9ca3af;
            font-size: 0.813rem;
        }

        .custom-dropdown-menu::-webkit-scrollbar {
            width: 6px;
        }

        .custom-dropdown-menu::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-dropdown-menu::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .custom-dropdown-menu::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .checkbox-dropdown-item {
            padding: 0.4rem 0.75rem;
            cursor: pointer;
            transition: all 0.15s;
            border-bottom: 1px solid #f9fafb;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-dropdown-item:last-child {
            border-bottom: none;
        }

        .checkbox-dropdown-item:hover {
            background: #f9fafb;
        }

        .checkbox-dropdown-item input[type="checkbox"] {
            width: 0.875rem;
            height: 0.875rem;
            cursor: pointer;
            accent-color: #2563eb;
        }

        .checkbox-dropdown-item label {
            cursor: pointer;
            flex: 1;
            font-size: 0.813rem;
            color: #374151;
        }

        .helper-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            padding: 0.125rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.7rem;
            font-weight: 600;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        /* .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            position: relative;
            overflow: visible;
        } */

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

        .number-badge {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
        }
    </style>

    @error('duplicate')
        <div
            class="mx-2 md:mx-4 mb-3 p-3 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200 flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" />
            </svg>
            {{ $message }}
        </div>
    @enderror

    <form action="{{ $url }}" method="POST">
        @csrf
        @method($method)

        <div class="mx-2 md:mx-4 p-3 md:p-4 bg-white rounded-xl shadow-xl">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200">
                <h2 class="text-base md:text-lg font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Informasi Header
                </h2>
                <div class="flex flex-row gap-2">
                    <a href="{{ route('transaction.rencana-kerja-mingguan.index') }}"
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
            <div class="mb-4 flex flex-col-reverse md:flex-row md:justify-between gap-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 md:gap-3 flex-1">
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-gray-700">No. RKM</label>
                        <input type="text" name="rkmno"
                            class="input-focus border rounded-lg cursor-not-allowed focus:ring-0 focus:border-gray-300 border-gray-300 p-2 w-full text-sm bg-gray-50"
                            autocomplete="off" maxlength="4"
                            value="{{ old('rkmno', $rkmno ?? ($header->rkmno ?? '')) }}" readonly>
                    </div>
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-gray-700">Tanggal RKM</label>
                        <input type="date" name="rkmdate"
                            class="input-focus border rounded-lg cursor-not-allowed focus:ring-0 focus:border-gray-300 border-gray-300 p-2 w-full text-sm bg-gray-50"
                            autocomplete="off" value="{{ old('rkmdate', $selectedDate ?? ($header->rkmdate ?? '')) }}"
                            readonly>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 md:gap-3">
                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-700">Tanggal Mulai <span
                            class="text-red-600">*</span></label>
                    <input type="date" name="startdate" id="startdate"
                        value="{{ old('startdate', $header->startdate ?? '') }}"
                        class="input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>
                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-700">Tanggal Selesai</label>
                    <input type="date" name="enddate" id="enddate"
                        value="{{ old('enddate', $header->enddate ?? '') }}"
                        class="input-focus border rounded-lg cursor-not-allowed focus:ring-0 focus:border-gray-300 border-gray-300 p-2 w-full text-sm bg-gray-50"
                        readonly>
                </div>
                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-700">Aktivitas <span
                            class="text-red-600">*</span></label>
                    <div class="custom-dropdown">
                        <input type="hidden" name="activitycode" id="activitycode"
                            value="{{ old('activitycode', $header->activitycode ?? '') }}" required>
                        <div
                            class="custom-dropdown-trigger input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white flex items-center justify-between">
                            <span id="selected-activity" class="text-gray-400 text-sm">-- Pilih Aktivitas --</span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" id="dropdown-icon"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <div class="custom-dropdown-menu">
                            <div class="custom-dropdown-search">
                                <div class="relative">
                                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 transform -translate-y-1/2"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <input type="text" id="activity-search" placeholder="Cari aktivitas..."
                                        class="w-full pl-9 pr-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                            <div id="activity-list">
                                @foreach ($activity as $act)
                                    <div class="custom-dropdown-item" data-code="{{ $act->activitycode }}"
                                        data-name="{{ $act->activityname }}">
                                        <div class="custom-dropdown-item-code">{{ $act->activitycode }}</div>
                                        <div class="custom-dropdown-item-name">{{ $act->activityname }}</div>
                                    </div>
                                @endforeach
                            </div>
                            <div id="no-results" class="custom-dropdown-no-results" style="display: none;">Tidak ada
                                hasil</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex items-center gap-2 mb-2">
                    <span class="helper-badge">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        Helper
                    </span>
                    <span class="text-xs text-gray-600">Pilih blok & plot untuk auto-add ke list</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-3">
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-gray-700">Blok Helper</label>
                        <div class="custom-dropdown" id="helper-blok-dropdown">
                            <div
                                class="custom-dropdown-trigger input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white flex items-center justify-between">
                                <span id="helper-blok-selected-count" class="text-gray-400 text-xs">Pilih
                                    blok...</span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                    id="helper-blok-dropdown-icon" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            <div class="custom-dropdown-menu" id="helper-blok-menu">
                                <div class="custom-dropdown-search">
                                    <div class="relative">
                                        <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 transform -translate-y-1/2"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <input type="text" id="helper-blok-search" placeholder="Cari blok..."
                                            class="w-full pl-9 pr-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                                <div id="helper-blok-list">
                                    @foreach ($bloks as $blok)
                                        <div class="checkbox-dropdown-item">
                                            <input type="checkbox" id="helper-blok-{{ $blok->blok }}"
                                                value="{{ $blok->blok }}">
                                            <label for="helper-blok-{{ $blok->blok }}">{{ $blok->blok }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-gray-700">Plot Helper</label>
                        <div class="custom-dropdown" id="helper-plot-dropdown">
                            <div
                                class="custom-dropdown-trigger input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white flex items-center justify-between">
                                <span id="helper-selected-count" class="text-gray-400 text-xs">Pilih plot...</span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                    id="helper-dropdown-icon" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            <div class="custom-dropdown-menu" id="helper-plot-menu">
                                <div class="custom-dropdown-search">
                                    <div class="relative">
                                        <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 transform -translate-y-1/2"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <input type="text" id="helper-plot-search" placeholder="Cari plot..."
                                            class="w-full pl-9 pr-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                                <div id="helper-plot-list">
                                    <div class="custom-dropdown-no-results">Pilih blok terlebih dahulu</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-2 md:mx-4 p-3 md:p-4 bg-white rounded-xl shadow-xl mt-3">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200">
                <h2 class="text-base md:text-lg font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    List Aktivitas
                </h2>
                <button type="button" id="addRow"
                    class="btn-hover flex items-center gap-1.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-3 py-1.5 rounded-lg shadow-md hover:from-blue-700 hover:to-blue-800 text-sm font-medium">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd"
                            d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4.243a1 1 0 1 0-2 0V11H7.757a1 1 0 1 0 0 2H11v3.243a1 1 0 1 0 2 0V13h3.243a1 1 0 1 0 0-2H13V7.757Z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="hidden sm:inline">Tambah</span>
                </button>
            </div>

            @php
                $lists = old('lists', $isEdit ? $header->lists : [[]]);
            @endphp

            <div id="input-container" class="space-y-2.5">
                @foreach ($lists as $index => $list)
                    <div
                        class="input-row bg-gradient-to-r from-gray-50 to-blue-50/30 border border-gray-200 rounded-xl p-2.5 md:p-3 hover:border-blue-300 hover:shadow-md transition-all duration-200">
                        <div class="flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-2.5">
                            <div class="flex items-center justify-between w-full md:w-auto">
                                <div
                                    class="flex items-center justify-center w-8 h-8 number-badge text-white rounded-lg font-bold text-sm number-count">
                                    {{ $index + 1 }}</div>
                                @if ($index > 0)
                                    <button type="button"
                                        class="remove-row md:hidden text-red-600 hover:bg-red-50 p-1.5 rounded-lg transition-all">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd"
                                                d="M8.586 2.586A2 2 0 0 1 10 2h4a2 2 0 0 1 2 2v2h3a1 1 0 1 1 0 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V8a1 1 0 0 1 0-2h3V4a2 2 0 0 1 .586-1.414ZM10 6h4V4h-4v2Zm1 4a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Zm4 0a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @endif
                            </div>

                            <div class="flex-1 w-full md:w-auto">
                                <label class="block mb-1 text-xs font-semibold text-gray-700">Blok</label>
                                <select name="lists[{{ $index }}][blok]"
                                    class="blok-select input-focus border rounded-lg border-gray-300 p-2 w-full text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required>
                                    <option value="" disabled selected>-- Pilih Blok --</option>
                                    @foreach ($bloks as $blok)
                                        <option class="text-black" value="{{ $blok->blok }}"
                                            @selected(old("lists.$index.blok", $list->blok ?? '') == $blok->blok)>{{ $blok->blok }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex-1 w-full md:w-auto">
                                <label class="block mb-1 text-xs font-semibold text-gray-700">Plot</label>
                                <div class="custom-dropdown plot-dropdown">
                                    <input type="hidden" name="lists[{{ $index }}][plot]"
                                        class="plot-hidden" value="{{ old("lists.$index.plot", $list->plot ?? '') }}"
                                        required>
                                    <div
                                        class="custom-dropdown-trigger input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white flex items-center justify-between">
                                        <span class="selected-plot text-gray-400 text-xs">-- Pilih Plot --</span>
                                        <svg class="w-4 h-4 text-gray-500 transition-transform duration-200 dropdown-icon"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                    <div class="custom-dropdown-menu plot-menu">
                                        <div class="custom-dropdown-search">
                                            <div class="relative">
                                                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 transform -translate-y-1/2"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                <input type="text"
                                                    class="plot-search w-full pl-9 pr-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    placeholder="Cari plot...">
                                            </div>
                                        </div>
                                        <div class="plot-list"></div>
                                        <div class="custom-dropdown-no-results plot-no-results" style="display:none;">
                                            Tidak ada hasil</div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 w-full md:w-auto">
                                <div>
                                    <label class="block mb-1 text-xs font-semibold text-gray-700">Luas (Ha)</label>
                                    <input type="number" min="0" max="999.99" step="0.01"
                                        name="lists[{{ $index }}][totalluasactual]"
                                        value="{{ old("lists.$index.totalluasactual", $list->totalluasactual ?? '') }}"
                                        class="border rounded-lg bg-gray-100 border-gray-300 p-2 w-full text-sm cursor-not-allowed focus:ring-0 focus:border-gray-300"
                                        readonly />
                                </div>
                                <div>
                                    <label class="block mb-1 text-xs font-semibold text-gray-700">Estimasi (Ha)</label>
                                    <input type="number" min="0" max="999.99" step="0.01"
                                        name="lists[{{ $index }}][totalestimasi]"
                                        value="{{ old("lists.$index.totalestimasi", $list->totalestimasi ?? '') }}"
                                        class="input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required />
                                </div>
                            </div>

                            @if ($index > 0)
                                <div class="hidden md:flex items-end">
                                    <button type="button"
                                        class="remove-row btn-hover flex items-center gap-1.5 bg-white border-2 border-red-600 text-red-600 hover:bg-red-600 hover:text-white px-2.5 py-2 rounded-lg shadow-sm font-medium text-sm">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd"
                                                d="M8.586 2.586A2 2 0 0 1 10 2h4a2 2 0 0 1 2 2v2h3a1 1 0 1 1 0 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V8a1 1 0 0 1 0-2h3V4a2 2 0 0 1 .586-1.414ZM10 6h4V4h-4v2Zm1 4a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Zm4 0a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>Hapus</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </form>

    <script>
        // Tempatkan script ini di bagian bawah view Anda, ganti semua script yang ada

        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const routes = {
            getPlot: "{{ route('transaction.rencana-kerja-mingguan.getPlot', ':blok') }}",
            getData: "{{ route('transaction.rencana-kerja-mingguan.getData') }}"
        };

        function setupDropdown(trigger, menu, icon) {
            const dropdown = trigger.closest('.custom-dropdown');
            trigger.addEventListener('click', e => {
                e.stopPropagation();
                const wasActive = menu.classList.contains('active');
                document.querySelectorAll('.custom-dropdown-menu.active').forEach(m => {
                    m.classList.remove('active');
                    m.closest('.custom-dropdown').classList.remove('active');
                    const i = m.parentElement.querySelector(
                        '.dropdown-icon, #dropdown-icon, #helper-dropdown-icon, #helper-blok-dropdown-icon'
                    );
                    if (i) i.style.transform = 'rotate(0deg)';
                });
                if (!wasActive) {
                    menu.classList.add('active');
                    dropdown.classList.add('active');
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    menu.classList.remove('active');
                    dropdown.classList.remove('active');
                    icon.style.transform = 'rotate(0deg)';
                }
            });
            document.addEventListener('click', e => {
                if (!dropdown.contains(e.target)) {
                    menu.classList.remove('active');
                    dropdown.classList.remove('active');
                    icon.style.transform = 'rotate(0deg)';
                }
            });
        }

        function setupSearch(input, items, noResults, filterFn) {
            input.addEventListener('input', function() {
                const term = this.value.toLowerCase();
                let visible = 0;
                items.forEach(item => {
                    const match = filterFn(item, term);
                    item.style.display = match ? 'block' : 'none';
                    if (match) visible++;
                });
                if (noResults) noResults.style.display = visible ? 'none' : 'block';
            });
        }

        (function initActivityDropdown() {
            const dropdown = document.querySelector('.custom-dropdown');
            const trigger = dropdown.querySelector('.custom-dropdown-trigger');
            const menu = dropdown.querySelector('.custom-dropdown-menu');
            const search = document.getElementById('activity-search');
            const selected = document.getElementById('selected-activity');
            const hidden = document.getElementById('activitycode');
            const list = document.getElementById('activity-list');
            const noResults = document.getElementById('no-results');
            const icon = document.getElementById('dropdown-icon');
            const items = list.querySelectorAll('.custom-dropdown-item');

            setupDropdown(trigger, menu, icon);

            items.forEach(item => {
                item.addEventListener('click', function() {
                    const code = this.dataset.code;
                    const name = this.dataset.name;
                    hidden.value = code;
                    selected.textContent = `${code} - ${name}`;
                    selected.classList.remove('text-gray-400');
                    selected.classList.add('text-gray-900');
                    menu.classList.remove('active');
                    icon.style.transform = 'rotate(0deg)';
                    search.value = '';
                    items.forEach(i => i.style.display = 'block');
                });
            });

            setupSearch(search, items, noResults, (item, term) => {
                const code = item.dataset.code.toLowerCase();
                const name = item.dataset.name.toLowerCase();
                return code.includes(term) || name.includes(term);
            });

            if (hidden.value) {
                const selectedItem = list.querySelector(`[data-code="${hidden.value}"]`);
                if (selectedItem) {
                    selected.textContent = `${selectedItem.dataset.code} - ${selectedItem.dataset.name}`;
                    selected.classList.remove('text-gray-400');
                    selected.classList.add('text-gray-900');
                }
            }
        })();

        (function initHelperBlokPlot() {
            const blokDropdown = document.getElementById('helper-blok-dropdown');
            const blokTrigger = blokDropdown.querySelector('.custom-dropdown-trigger');
            const blokMenu = document.getElementById('helper-blok-menu');
            const blokSearch = document.getElementById('helper-blok-search');
            const blokList = document.getElementById('helper-blok-list');
            const blokSelectedCount = document.getElementById('helper-blok-selected-count');
            const blokIcon = document.getElementById('helper-blok-dropdown-icon');

            const plotDropdown = document.getElementById('helper-plot-dropdown');
            const plotTrigger = plotDropdown.querySelector('.custom-dropdown-trigger');
            const plotMenu = document.getElementById('helper-plot-menu');
            const plotSearch = document.getElementById('helper-plot-search');
            const plotList = document.getElementById('helper-plot-list');
            const plotSelectedCount = document.getElementById('helper-selected-count');
            const plotIcon = document.getElementById('helper-dropdown-icon');

            let selectedBloks = new Set();
            let selectedPlots = new Map(); // Map<plot, blok>
            let plotsByBlok = new Map(); // Map<blok, Set<plots>>

            setupDropdown(blokTrigger, blokMenu, blokIcon);
            setupDropdown(plotTrigger, plotMenu, plotIcon);

            // Setup blok checkboxes
            blokList.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                cb.addEventListener('change', function() {
                    const blok = this.value;
                    if (this.checked) {
                        selectedBloks.add(blok);
                        loadPlotsForBlok(blok);
                    } else {
                        selectedBloks.delete(blok);
                        removePlotsForBlok(blok);
                    }
                    updateBlokCount();
                    refreshPlotList();
                });
            });

            // Setup blok search
            blokSearch.addEventListener('input', function() {
                const term = this.value.toLowerCase();
                blokList.querySelectorAll('.checkbox-dropdown-item').forEach(item => {
                    const label = item.querySelector('label').textContent.toLowerCase();
                    item.style.display = label.includes(term) ? 'flex' : 'none';
                });
            });

            // Setup plot search
            plotSearch.addEventListener('input', function() {
                const term = this.value.toLowerCase();
                plotList.querySelectorAll('.checkbox-dropdown-item').forEach(item => {
                    const label = item.querySelector('label').textContent.toLowerCase();
                    item.style.display = label.includes(term) ? 'flex' : 'none';
                });
            });

            function loadPlotsForBlok(blok) {
                fetch(routes.getPlot.replace(':blok', blok))
                    .then(r => r.json())
                    .then(data => {
                        plotsByBlok.set(blok, new Set(data));
                        refreshPlotList();
                    });
            }

            function removePlotsForBlok(blok) {
                // Remove all plots for this blok from selection
                const plotsToRemove = [];
                selectedPlots.forEach((plotBlok, plot) => {
                    if (plotBlok === blok) {
                        plotsToRemove.push(plot);
                    }
                });

                plotsToRemove.forEach(plot => {
                    selectedPlots.delete(plot);
                    removeRowFromHelper(blok, plot);
                });

                plotsByBlok.delete(blok);
                updatePlotCount();
                refreshPlotList();
            }

            function refreshPlotList() {
                if (selectedBloks.size === 0) {
                    plotList.innerHTML = '<div class="custom-dropdown-no-results">Pilih blok terlebih dahulu</div>';
                    return;
                }

                const allPlots = [];
                selectedBloks.forEach(blok => {
                    const plots = plotsByBlok.get(blok);
                    if (plots) {
                        plots.forEach(plot => {
                            allPlots.push({
                                plot,
                                blok
                            });
                        });
                    }
                });

                if (allPlots.length === 0) {
                    plotList.innerHTML = '<div class="custom-dropdown-no-results">Tidak ada plot tersedia</div>';
                    return;
                }

                // Sort plots
                allPlots.sort((a, b) => {
                    const aBlok = a.blok;
                    const bBlok = b.blok;
                    if (aBlok !== bBlok) return aBlok.localeCompare(bBlok);

                    const aNum = parseInt(a.plot.replace(/\D/g, '')) || 0;
                    const bNum = parseInt(b.plot.replace(/\D/g, '')) || 0;
                    return aNum - bNum;
                });

                plotList.innerHTML = allPlots.map(({
                    plot,
                    blok
                }) => {
                    const isChecked = selectedPlots.has(plot) && selectedPlots.get(plot) === blok;
                    return `<div class="checkbox-dropdown-item">
                <input type="checkbox" id="helper-plot-${plot}" value="${plot}" data-blok="${blok}" ${isChecked ? 'checked' : ''}>
                <label for="helper-plot-${plot}">${plot} <span class="text-gray-400 text-xs">(${blok})</span></label>
            </div>`;
                }).join('');

                attachPlotCheckboxEvents();
            }

            function attachPlotCheckboxEvents() {
                plotList.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                    cb.addEventListener('change', function() {
                        const plot = this.value;
                        const blok = this.dataset.blok;
                        if (this.checked) {
                            selectedPlots.set(plot, blok);
                            addRowFromHelper(blok, plot);
                        } else {
                            selectedPlots.delete(plot);
                            removeRowFromHelper(blok, plot);
                        }
                        updatePlotCount();
                    });
                });
            }

            function updateBlokCount() {
                const count = selectedBloks.size;
                blokSelectedCount.textContent = count === 0 ? 'Pilih blok...' : `${count} blok terpilih`;
                blokSelectedCount.classList.toggle('text-gray-400', count === 0);
                blokSelectedCount.classList.toggle('text-gray-900', count > 0);
            }

            function updatePlotCount() {
                const count = selectedPlots.size;
                plotSelectedCount.textContent = count === 0 ? 'Pilih plot...' : `${count} plot terpilih`;
                plotSelectedCount.classList.toggle('text-gray-400', count === 0);
                plotSelectedCount.classList.toggle('text-gray-900', count > 0);
            }

            function addRowFromHelper(blok, plot) {
                const container = document.getElementById('input-container');
                const existingRows = container.querySelectorAll('.input-row');

                for (let row of existingRows) {
                    const bs = row.querySelector('.blok-select');
                    const ph = row.querySelector('.plot-hidden');
                    if (bs && ph && bs.value === blok && ph.value === plot) return;
                }

                const firstRow = existingRows[0];
                if (firstRow && existingRows.length === 1) {
                    const fbs = firstRow.querySelector('.blok-select');
                    const fph = firstRow.querySelector('.plot-hidden');
                    if (fbs && !fbs.value && fph && !fph.value) {
                        setRowData(firstRow, blok, plot);
                        return;
                    }
                }

                const newRow = createRow(existingRows.length, blok, plot);
                container.appendChild(newRow);
                initPlotDropdown(newRow);
                setRowData(newRow, blok, plot);
                updateRowNumbers();
            }

            function setRowData(row, blok, plot) {
                row.dataset.helperGenerated = 'true';
                row.dataset.helperBlok = blok;
                row.dataset.helperPlot = plot;
                const bs = row.querySelector('.blok-select');
                bs.value = blok;
                bs.dispatchEvent(new Event('change'));
                setTimeout(() => {
                    const ph = row.querySelector('.plot-hidden');
                    const sp = row.querySelector('.selected-plot');
                    ph.value = plot;
                    sp.textContent = plot;
                    sp.classList.remove('text-gray-400');
                    sp.classList.add('text-gray-900');
                    fetch(routes.getData, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            plot
                        })
                    }).then(r => r.json()).then(data => {
                        const li = row.querySelector('input[name$="[totalluasactual]"]');
                        if (li) {
                            li.value = data.luasarea;
                            setupEstimasiValidation(row);
                        }
                    });
                }, 300);
            }

            function removeRowFromHelper(blok, plot) {
                const rows = document.querySelectorAll('.input-row');
                rows.forEach(row => {
                    if (row.dataset.helperGenerated === 'true' &&
                        row.dataset.helperBlok === blok &&
                        row.dataset.helperPlot === plot) {
                        row.remove();
                        updateRowNumbers();
                    }
                });
            }

            // Expose fungsi untuk diakses dari luar
            window.helperPlotRemove = function(blok, plot) {
                selectedPlots.delete(plot);
                updatePlotCount();

                const checkbox = document.querySelector(`#helper-plot-${plot}[data-blok="${blok}"]`);
                if (checkbox) {
                    checkbox.checked = false;
                }
            };
        })();

        function setupEstimasiValidation(row) {
            const luasInput = row.querySelector('input[name$="[totalluasactual]"]');
            const estimasiInput = row.querySelector('input[name$="[totalestimasi]"]');

            if (!luasInput || !estimasiInput) return;

            estimasiInput.addEventListener('input', function() {
                const luas = parseFloat(luasInput.value) || 0;
                const estimasi = parseFloat(this.value) || 0;

                if (estimasi > luas) {
                    this.value = luas.toFixed(2);

                    const warning = document.createElement('div');
                    warning.className = 'text-red-600 text-xs mt-1 font-semibold';
                    warning.textContent = 'Estimasi tidak boleh melebihi Luas!';

                    const existingWarning = this.parentElement.querySelector('.text-red-600');
                    if (existingWarning) {
                        existingWarning.remove();
                    }

                    this.parentElement.appendChild(warning);
                    setTimeout(() => warning.remove(), 3000);
                }
            });

            estimasiInput.addEventListener('blur', function() {
                const luas = parseFloat(luasInput.value) || 0;
                const estimasi = parseFloat(this.value) || 0;

                if (estimasi > luas) {
                    this.value = luas.toFixed(2);
                }
            });
        }

        function initPlotDropdown(row) {
            const dropdown = row.querySelector('.plot-dropdown');
            const trigger = dropdown.querySelector('.custom-dropdown-trigger');
            const menu = dropdown.querySelector('.plot-menu');
            const search = dropdown.querySelector('.plot-search');
            const selected = dropdown.querySelector('.selected-plot');
            const hidden = dropdown.querySelector('.plot-hidden');
            const list = dropdown.querySelector('.plot-list');
            const noResults = dropdown.querySelector('.plot-no-results');
            const icon = dropdown.querySelector('.dropdown-icon');
            const blokSelect = row.querySelector('.blok-select');

            setupDropdown(trigger, menu, icon);

            blokSelect.addEventListener('change', function() {
                const blok = this.value;
                fetch(routes.getPlot.replace(':blok', blok))
                    .then(r => r.json())
                    .then(data => {
                        list.innerHTML = data.length ? data.map(plot =>
                            `<div class="custom-dropdown-item" data-code="${plot}"><div class="custom-dropdown-item-code">${plot}</div></div>`
                        ).join('') : '<div class="custom-dropdown-no-results">Tidak ada plot tersedia</div>';
                        attachPlotEvents();
                    });
            });

            search.addEventListener('input', () => {
                const term = search.value.toLowerCase();
                let visible = 0;
                list.querySelectorAll('.custom-dropdown-item').forEach(item => {
                    const match = item.dataset.code.toLowerCase().includes(term);
                    item.style.display = match ? 'block' : 'none';
                    if (match) visible++;
                });
                noResults.style.display = visible ? 'none' : 'block';
            });

            function attachPlotEvents() {
                list.querySelectorAll('.custom-dropdown-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const code = this.dataset.code;
                        hidden.value = code;
                        selected.textContent = code;
                        selected.classList.remove('text-gray-400');
                        selected.classList.add('text-gray-900');
                        menu.classList.remove('active');
                        icon.style.transform = 'rotate(0deg)';
                        fetch(routes.getData, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                plot: code
                            })
                        }).then(r => r.json()).then(data => {
                            const li = row.querySelector('input[name$="[totalluasactual]"]');
                            if (li) {
                                li.value = data.luasarea;
                                setupEstimasiValidation(row);
                            }
                        });
                    });
                });
            }
        }

        document.querySelectorAll('.input-row').forEach(row => {
            initPlotDropdown(row);
            setupEstimasiValidation(row);

            const blokSelect = row.querySelector('.blok-select');
            if (blokSelect.value) {
                blokSelect.dispatchEvent(new Event('change'));
                const plotHidden = row.querySelector('.plot-hidden');
                if (plotHidden.value) {
                    setTimeout(() => {
                        const selectedPlot = row.querySelector('.selected-plot');
                        selectedPlot.textContent = plotHidden.value;
                        selectedPlot.classList.remove('text-gray-400');
                        selectedPlot.classList.add('text-gray-900');
                    }, 300);
                }
            }
        });

        function createRow(index, blok = '', plot = '') {
            const row = document.createElement('div');
            row.className =
                'input-row bg-gradient-to-r from-gray-50 to-blue-50/30 border border-gray-200 rounded-xl p-2.5 md:p-3 hover:border-blue-300 hover:shadow-md transition-all duration-200';
            row.innerHTML =
                `<div class="flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-2.5"><div class="flex items-center justify-between w-full md:w-auto"><div class="flex items-center justify-center w-8 h-8 number-badge text-white rounded-lg font-bold text-sm number-count">${index + 1}</div><button type="button" class="remove-row md:hidden text-red-600 hover:bg-red-50 p-1.5 rounded-lg transition-all"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M8.586 2.586A2 2 0 0 1 10 2h4a2 2 0 0 1 2 2v2h3a1 1 0 1 1 0 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V8a1 1 0 0 1 0-2h3V4a2 2 0 0 1 .586-1.414ZM10 6h4V4h-4v2Zm1 4a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Zm4 0a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Z" clip-rule="evenodd"/></svg></button></div><div class="flex-1 w-full md:w-auto"><label class="block mb-1 text-xs font-semibold text-gray-700">Blok</label><select name="lists[${index}][blok]" class="blok-select input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required><option value="" disabled selected>-- Pilih Blok --</option>@foreach ($bloks as $blok)<option value="{{ $blok->blok }}" class="text-black">{{ $blok->blok }}</option>@endforeach</select></div><div class="flex-1 w-full md:w-auto"><label class="block mb-1 text-xs font-semibold text-gray-700">Plot</label><div class="custom-dropdown plot-dropdown"><input type="hidden" name="lists[${index}][plot]" class="plot-hidden" required><div class="custom-dropdown-trigger input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white flex items-center justify-between"><span class="selected-plot text-gray-400 text-xs">-- Pilih Plot --</span><svg class="w-4 h-4 text-gray-500 transition-transform duration-200 dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg></div><div class="custom-dropdown-menu plot-menu"><div class="custom-dropdown-search"><div class="relative"><svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg><input type="text" class="plot-search w-full pl-9 pr-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Cari plot..."></div></div><div class="plot-list"></div><div class="custom-dropdown-no-results plot-no-results" style="display:none;">Tidak ada hasil</div></div></div></div><div class="grid grid-cols-2 gap-2 w-full md:w-auto"><div><label class="block mb-1 text-xs font-semibold text-gray-700">Luas (Ha)</label><input type="number" min="0" max="999.99" step="0.01" name="lists[${index}][totalluasactual]" class="border rounded-lg bg-gray-100 border-gray-300 p-2 w-full text-sm cursor-not-allowed focus:ring-0 focus:border-gray-300" readonly/></div><div><label class="block mb-1 text-xs font-semibold text-gray-700">Estimasi (Ha)</label><input type="number" min="0" max="999.99" step="0.01" name="lists[${index}][totalestimasi]" class="input-focus border rounded-lg border-gray-300 p-2 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required/></div></div><div class="hidden md:flex items-end"><button type="button" class="remove-row btn-hover flex items-center gap-1.5 bg-white border-2 border-red-600 text-red-600 hover:bg-red-600 hover:text-white px-2.5 py-2 rounded-lg shadow-sm font-medium text-sm"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M8.586 2.586A2 2 0 0 1 10 2h4a2 2 0 0 1 2 2v2h3a1 1 0 1 1 0 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V8a1 1 0 0 1 0-2h3V4a2 2 0 0 1 .586-1.414ZM10 6h4V4h-4v2Zm1 4a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Zm4 0a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Z" clip-rule="evenodd"/></svg><span>Hapus</span></button></div></div>`;
            return row;
        }

        document.getElementById('addRow').addEventListener('click', () => {
            const container = document.getElementById('input-container');
            const newRow = createRow(container.querySelectorAll('.input-row').length);
            container.appendChild(newRow);
            initPlotDropdown(newRow);
            setupEstimasiValidation(newRow);
            updateRowNumbers();
        });

        document.addEventListener('click', e => {
            const btn = e.target.closest('.remove-row');
            if (btn) {
                const row = btn.closest('.input-row');
                if (row) {
                    if (row.dataset.helperGenerated === 'true') {
                        const blok = row.dataset.helperBlok;
                        const plot = row.dataset.helperPlot;

                        if (typeof window.helperPlotRemove === 'function') {
                            window.helperPlotRemove(blok, plot);
                        }
                    }
                    row.remove();
                    updateRowNumbers();
                }
            }
        });

        function updateRowNumbers() {
            document.querySelectorAll('.input-row').forEach((row, i) => {
                row.querySelectorAll('.number-count').forEach(div => div.textContent = i + 1);
                row.querySelectorAll('select, input').forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) input.setAttribute('name', name.replace(/lists\[\d+\]/, `lists[${i}]`));
                });
            });
        }

        document.getElementById('startdate').addEventListener('change', function() {
            const start = new Date(this.value);
            if (!isNaN(start)) {
                const end = new Date(start);
                end.setDate(start.getDate() + 7);
                document.getElementById('enddate').value = end.toISOString().split('T')[0];
            }
        });
    </script>
</x-layout>
