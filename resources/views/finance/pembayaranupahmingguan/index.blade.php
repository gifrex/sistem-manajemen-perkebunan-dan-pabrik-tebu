<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $title }}</x-slot:nav>

    {{-- Penanda untuk global AJAX script --}}
    <div id="ajax-data" data-url="{{ route('finance.pembayaran-upah-mingguan.index') }}" class="hidden"></div>

    <div class="mx-auto py-6 bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-lg border border-gray-200">
        <!-- Header Section -->
        <div class="px-6 pb-4 border-b border-gray-200">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-7 h-7 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"
                                clip-rule="evenodd" />
                        </svg>
                        {{ $title }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola upah tenaga kerja mingguan</p>
                </div>

                <div class="flex gap-2 flex-wrap">
                    @if (session('tenagakerjarum') != null)
                        <button type="button" onclick="confirmGenerate()"
                            class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Generate
                        </button>
                    @endif

                    <button type="button" onclick="exportToExcel()"
                        class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd"
                                d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Zm2-2a1 1 0 1 0 0 2h3a1 1 0 1 0 0-2h-3Zm0 3a1 1 0 1 0 0 2h3a1 1 0 1 0 0-2h-3Zm-6 4a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-6Zm8 1v1h-2v-1h2Zm0 3h-2v1h2v-1Zm-4-3v1H9v-1h2Zm0 3H9v1h2v-1Z"
                                clip-rule="evenodd" />
                        </svg>
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <form method="POST" action="{{ route('finance.pembayaran-upah-mingguan.index') }}" id="filterForm">
            @csrf
            <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <div class="flex items-end gap-4 flex-wrap justify-between">
                    <div class="flex items-end gap-4 flex-wrap">

                        <!-- Jenis Tenaga Kerja -->
                        <div>
                            <label for="tenagakerjarum" class="block text-sm font-semibold text-gray-700 mb-2">
                                Tenaga Kerja <span class="text-red-500">*</span>
                            </label>
                            <select name="tenagakerjarum" id="tenagakerjarum"
                                onchange="Alpine.store('loading').start(); this.form.submit()"
                                class="px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-medium text-gray-700 bg-white transition-all duration-200"
                                required>
                                <option value="" disabled
                                    {{ old('tenagakerjarum', session('tenagakerjarum')) == null ? 'selected' : '' }}>--
                                    Pilih --</option>
                                <option value="Harian" class="text-gray-700"
                                    {{ old('tenagakerjarum', session('tenagakerjarum')) == 'Harian' ? 'selected' : '' }}>
                                    Harian</option>
                                <option value="Borongan" class="text-gray-700"
                                    {{ old('tenagakerjarum', session('tenagakerjarum')) == 'Borongan' ? 'selected' : '' }}>
                                    Borongan</option>
                            </select>
                        </div>

                        <!-- Filter Mandor -->
                        @if (session('tenagakerjarum') != null)
                            <div x-data="mandorDropdown()" class="relative">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Filter Mandor</label>
                                <button type="button" @click="open = !open"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 text-sm font-medium text-gray-700 min-w-[200px] justify-between">
                                    <span class="truncate max-w-[160px]" x-text="label"></span>
                                    <svg class="w-4 h-4 flex-shrink-0 transition-transform duration-200"
                                        :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" @click.outside="open = false" x-transition
                                    class="absolute left-0 z-20 mt-2 w-72 bg-white border border-gray-200 rounded-xl shadow-2xl">
                                    <div class="p-3 border-b border-gray-100">
                                        <div class="relative">
                                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                            <input type="text" x-model="search" placeholder="Cari mandor..."
                                                class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                                @click.stop />
                                        </div>
                                    </div>
                                    <div class="px-3 py-2 border-b border-gray-100 flex items-center justify-between">
                                        <label
                                            class="flex items-center gap-2 cursor-pointer select-none text-sm font-semibold text-gray-700">
                                            <input type="checkbox" :checked="isAllSelected" @change="toggleAll"
                                                class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" />
                                            Semua Mandor
                                        </label>
                                        <span class="text-xs text-gray-400"
                                            x-text="`${selected.length} dipilih`"></span>
                                    </div>
                                    <ul class="max-h-52 overflow-y-auto divide-y divide-gray-50">
                                        <template x-for="m in filtered" :key="m.userid">
                                            <li class="px-3 py-2 hover:bg-indigo-50 transition-colors duration-100">
                                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                                    <input type="checkbox" :value="m.userid" x-model="selected"
                                                        class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" />
                                                    <span class="text-sm text-gray-700" x-text="m.name"></span>
                                                </label>
                                            </li>
                                        </template>
                                        <li x-show="filtered.length === 0"
                                            class="px-3 py-4 text-center text-sm text-gray-400">
                                            Mandor tidak ditemukan
                                        </li>
                                    </ul>
                                    <div class="p-3 border-t border-gray-100 flex justify-end gap-2">
                                        <button type="button" @click="reset()"
                                            class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-all duration-150">
                                            Reset
                                        </button>
                                        <button type="button" @click="apply()"
                                            class="px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-all duration-150">
                                            Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Date Filter -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Tgl. Generate
                            </label>
                            <div class="relative">
                                <button type="button"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 text-sm font-medium text-gray-700"
                                    id="menu-button" onclick="toggleDropdown()">
                                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span id="date-label">{{ $startDate }} s/d {{ $endDate }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div class="absolute left-0 z-10 mt-2 w-56 rounded-lg bg-white border border-gray-200 shadow-xl hidden"
                                    id="menu-dropdown">
                                    <div class="p-4 space-y-4">
                                        <div>
                                            <label for="start_date"
                                                class="block text-sm font-semibold text-gray-700 mb-2">Dari</label>
                                            <input type="date" id="start_date" name="start_date" required
                                                value="{{ old('start_date', $startDate ?? now()->startOfWeek()->format('Y-m-d')) }}"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 text-sm transition-all duration-200">
                                        </div>
                                        <div>
                                            <label for="end_date"
                                                class="block text-sm font-semibold text-gray-700 mb-2">Sampai</label>
                                            <input type="date" id="end_date" name="end_date" required
                                                value="{{ old('end_date', $endDate ?? now()->endOfWeek()->format('Y-m-d')) }}"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 text-sm transition-all duration-200">
                                        </div>
                                        <button type="button" id="btn-apply-filter"
                                            onclick="
                                                document.getElementById('menu-dropdown').classList.add('hidden');
                                                document.getElementById('date-label').textContent =
                                                    document.getElementById('start_date').value + ' s/d ' +
                                                    document.getElementById('end_date').value;
                                            "
                                            class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-all duration-200">
                                            Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right Side Filters -->
                    <div class="flex items-center gap-4 flex-wrap">
                        <div class="flex items-center gap-2">
                            <label for="perPage" class="text-sm font-semibold text-gray-700 whitespace-nowrap">Items
                                per page:</label>
                            <input type="text" name="perPage" id="perPage" value="{{ $perPage }}"
                                autocomplete="off"
                                class="w-16 px-3 py-2 border border-gray-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-all duration-200" />
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" id="search" autocomplete="off" name="search"
                                value="{{ old('search', $search) }}"
                                class="w-80 pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                                placeholder="Search No. Transaksi / Kegiatan..." />
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Info Note -->
        @if (session('tenagakerjarum') != null)
            <div class="px-6 py-3 bg-indigo-50 border-b border-indigo-100">
                <p class="text-xs text-indigo-600 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>
                        Filter tanggal berdasarkan <strong>Periode (startdate s/d enddate)</strong>.
                        Klik tombol <strong>Detail</strong> untuk melihat rincian pembayaran per transaksi.
                    </span>
                </p>
            </div>
        @endif

        <!-- Table Section -->
        <div class="px-6 py-5">
            @if (session('tenagakerjarum') == null || !$startDate || !$endDate)
                <div class="text-center py-12">
                    <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Data</h3>
                    <p class="text-gray-500 text-sm">Silakan pilih Jenis Tenaga Kerja dan Range Tanggal</p>
                </div>
            @else
                <div class="rounded-lg border border-gray-200 shadow-sm" id="tables">
                    <table class="w-full bg-white text-xs">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-100 to-gray-50">
                                <th
                                    class="py-2 px-2 border-b-2 border-gray-300 text-gray-700 font-bold text-center w-8">
                                    No.</th>
                                <th
                                    class="py-2 px-2 border-b-2 border-gray-300 text-gray-700 font-bold text-center w-28">
                                    No. Transaksi</th>
                                <th class="py-2 px-2 border-b-2 border-gray-300 text-gray-700 font-bold text-left">
                                    Kegiatan</th>
                                <th class="py-2 px-2 border-b-2 border-gray-300 text-gray-700 font-bold text-left">
                                    Mandor</th>
                                <th
                                    class="py-2 px-2 border-b-2 border-gray-300 text-gray-700 font-bold text-left w-36">
                                    Plot</th>
                                <th
                                    class="py-2 px-2 border-b-2 border-gray-300 text-gray-700 font-bold text-center w-32">
                                    Periode LKH</th>
                                <th
                                    class="py-2 px-2 border-b-2 border-gray-300 text-gray-700 font-bold text-center w-24">
                                    Tgl. Generate</th>
                                <th
                                    class="py-2 px-2 border-b-2 border-gray-300 text-gray-700 font-bold text-center w-28">
                                    Grand Total (Rp)</th>
                                <th
                                    class="py-2 px-2 border-b-2 border-gray-300 text-gray-700 font-bold text-center w-16">
                                    {{ session('tenagakerjarum') == 'Harian' ? 'TKH' : 'Plot' }}
                                </th>
                                <th
                                    class="py-2 px-2 border-b-2 border-gray-300 text-gray-700 font-bold text-center w-36">
                                    Status Approval</th>
                                <th
                                    class="py-2 px-2 border-b-2 border-gray-300 text-gray-700 font-bold text-center w-16">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($rum as $item)
                                <tr class="hover:bg-indigo-50 transition-colors duration-150">
                                    <td class="py-2 px-2 text-center text-gray-700">{{ $item->no }}.</td>
                                    <td class="py-2 px-2 text-center font-mono font-semibold text-indigo-700">
                                        <span
                                            class="bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-200">{{ $item->transno }}</span>
                                    </td>
                                    <td class="py-2 px-2 text-left text-gray-700"
                                        title="{{ $item->activityname ?? $item->activitycode }}">
                                        {{ $item->activityname ?? $item->activitycode }}
                                    </td>
                                    <td class="py-2 px-2 text-left text-gray-700">
                                        <div class="max-w-[110px] truncate"
                                            title="{{ $item->mandorname ?? $item->mandoruserid }}">
                                            {{ $item->mandorname ?? $item->mandoruserid }}
                                        </div>
                                    </td>
                                    <td class="py-2 px-2 text-left text-gray-700">
                                        <div class="max-w-72 truncate" title="{{ $item->plots }}">
                                            {{ $item->plots ?: '-' }}
                                        </div>
                                    </td>
                                    <td class="py-2 px-2 text-center text-gray-700 whitespace-nowrap">
                                        {{ $item->startdate }} <span class="text-gray-400">s/d</span>
                                        {{ $item->enddate }}
                                    </td>
                                    <td class="py-2 px-2 text-center whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-1.5 py-0.5 rounded-full font-medium bg-emerald-100 text-emerald-700">
                                            {{ $item->generatedate ? \Carbon\Carbon::parse($item->generatedate)->format('d-m-Y') : '-' }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-2 text-center text-gray-700">{{ $item->grandtotal }}</td>
                                    <td class="py-2 px-2 text-center">
                                        <span
                                            class="inline-flex items-center px-1.5 py-0.5 rounded-full font-medium bg-indigo-100 text-indigo-800">
                                            {{ $item->totalworkers ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-2 text-center">
                                        @php
                                            $status = $item->approval_status ?? 'DRAFT';
                                            $progress = $item->approval_progress ?? null;
                                            $approvalClass = match ($status) {
                                                'APPROVED' => 'bg-green-100 text-green-800 border border-green-300',
                                                'DECLINED' => 'bg-red-100 text-red-800 border border-red-300',
                                                'DRAFT' => 'bg-yellow-100 text-yellow-800 border border-yellow-300',
                                                default => 'bg-gray-100 text-gray-500 border border-gray-300',
                                            };
                                            $label =
                                                $status === 'DRAFT' && $progress ? "Waiting ({$progress})" : $status;
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-1.5 py-0.5 rounded-full font-medium {{ $approvalClass }} whitespace-nowrap">
                                            {{ $label }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-2 text-center">
                                        <button onclick="showList('{{ $item->transno }}')"
                                            class="inline-flex items-center gap-1 px-2 py-1 font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg border border-indigo-200 transition-all duration-200"
                                            title="View Details">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                <path stroke-width="2"
                                                    d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z" />
                                            </svg>
                                            <span>Detail</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="py-12 text-center">
                                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-3" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-gray-500 text-sm font-medium">Belum ada data yang di-generate
                                        </p>
                                        <p class="text-gray-400 text-xs mt-1">Klik tombol <strong>Generate</strong>
                                            untuk memproses data LKH periode ini</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Pagination -->
        @if (session('tenagakerjarum') != null && $startDate && $endDate)
            <div class="px-6 pb-2" id="pagination-links">
                @if ($rum->hasPages())
                    {{ $rum->appends(['perPage' => $rum->perPage(), 'start_date' => $startDate, 'end_date' => $endDate, 'mandor_ids' => $filterMandors])->links() }}
                @else
                    <div class="flex items-center justify-between bg-gray-50 px-4 py-3 rounded-lg">
                        <p class="text-sm text-gray-600">
                            Menampilkan <span class="font-semibold text-gray-800">{{ $rum->count() }}</span> dari
                            <span class="font-semibold text-gray-800">{{ $rum->total() }}</span> hasil
                        </p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay"
        class="fixed inset-0 z-[9998] flex flex-col items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm invisible opacity-0 transition-all duration-300">
        <div class="bg-white rounded-2xl shadow-2xl px-10 py-8 flex flex-col items-center gap-4">
            <svg class="animate-spin w-12 h-12 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            <p class="text-base font-semibold text-gray-700" id="loadingText">Memproses...</p>
            <p class="text-xs text-gray-400">Mohon tunggu, jangan tutup halaman ini</p>
        </div>
    </div>

    @include('finance.pembayaranupahmingguan.modal._generate')
    @include('finance.pembayaranupahmingguan.modal._list')

    <style>
        .invisible {
            visibility: hidden;
            pointer-events: none;
        }

        .visible {
            visibility: visible;
            pointer-events: auto;
        }

        @keyframes shimmer {
            0% {
                background-position: -400px 0;
            }

            100% {
                background-position: 400px 0;
            }
        }

        .skeleton-row td {
            padding: 12px 16px;
        }

        .skeleton-cell {
            display: inline-block;
            height: 14px;
            border-radius: 6px;
            background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
            background-size: 400px 100%;
            animation: shimmer 1.4s infinite linear;
        }

        .overflow-auto::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .overflow-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .overflow-auto::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .overflow-auto::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 10px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
    </style>

    <script>
        const _rumAllMandors = @json($mandorList);
        let _rumMandorIds = @json(array_values((array) $filterMandors));
        if (!_rumMandorIds.length) _rumMandorIds = [];

        (function() {
            const OrigFD = window.FormData;

            function PatchedFD(form) {
                const fd = (form instanceof HTMLFormElement) ? new OrigFD(form) : new OrigFD();
                const ids = _rumMandorIds;
                if (ids && ids.length) {
                    const _orig = fd.append.bind(fd);
                    ids.forEach(id => _orig('mandor_ids[]', id));
                }
                return fd;
            }
            PatchedFD.prototype = OrigFD.prototype;
            Object.setPrototypeOf(PatchedFD, OrigFD);
            document.addEventListener('DOMContentLoaded', () => {
                window.FormData = PatchedFD;
            });
        })();

        function mandorDropdown() {
            return {
                open: false,
                search: '',
                selected: _rumMandorIds.length ? [..._rumMandorIds] : _rumAllMandors.map(m => m.userid),
                get filtered() {
                    const q = this.search.toLowerCase().trim();
                    return q ? _rumAllMandors.filter(m => m.name.toLowerCase().includes(q)) : _rumAllMandors;
                },
                get isAllSelected() {
                    return this.selected.length === _rumAllMandors.length;
                },
                get label() {
                    if (!this.selected.length) return 'Tidak ada mandor';
                    if (this.selected.length === _rumAllMandors.length) return 'Semua Mandor';
                    if (this.selected.length === 1) {
                        const m = _rumAllMandors.find(x => x.userid === this.selected[0]);
                        return m ? m.name : '1 mandor dipilih';
                    }
                    return `${this.selected.length} mandor dipilih`;
                },
                toggleAll() {
                    this.selected = this.isAllSelected ? [] : _rumAllMandors.map(m => m.userid);
                },
                reset() {
                    this.search = '';
                    this.selected = _rumAllMandors.map(m => m.userid);
                    this.open = false;
                    _rumMandorIds = [];
                    if (window._triggerAjaxFetch) window._triggerAjaxFetch();
                },
                apply() {
                    this.open = false;
                    _rumMandorIds = this.isAllSelected ? [] : [...this.selected];
                    if (window._triggerAjaxFetch) window._triggerAjaxFetch();
                },
            };
        }

        function toggleDropdown() {
            document.getElementById('menu-dropdown').classList.toggle('hidden');
        }
        document.addEventListener('click', e => {
            const dd = document.getElementById('menu-dropdown'),
                btn = document.getElementById('menu-button');
            if (dd && btn && !dd.contains(e.target) && !btn.contains(e.target)) dd.classList.add('hidden');
        });

        function showLoading(text = 'Memproses...') {
            document.getElementById('loadingText').textContent = text;
            const el = document.getElementById('loadingOverlay');
            el.classList.remove('invisible');
            el.classList.add('visible');
            setTimeout(() => el.style.opacity = '1', 10);
        }

        function hideLoading() {
            const el = document.getElementById('loadingOverlay');
            el.style.opacity = '0';
            setTimeout(() => {
                el.classList.remove('visible');
                el.classList.add('invisible');
            }, 300);
        }

        function formatDate(d) {
            return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
        }

        function onGenStartDateChange(val) {
            if (!val) {
                document.getElementById('gen_end_date').value = '';
                return;
            }
            const end = new Date(val);
            end.setDate(end.getDate() + 6);
            document.getElementById('gen_end_date').value = formatDate(end);
        }

        function parseIDR(s) {
            if (!s) return 0;
            return parseFloat(String(s).replace(/[^\d.,]/g, '').replace(/\./g, '').replace(',', '.')) || 0;
        }
        const fmtIDR = v => new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 2
        }).format(v);

        function confirmGenerate() {
            const tk = document.getElementById('tenagakerjarum').value;
            if (!tk) return alert('Harap pilih jenis tenaga kerja terlebih dahulu');
            document.getElementById('gen-tk').textContent = tk;
            const today = new Date(),
                dow = today.getDay();
            const monday = new Date(today);
            monday.setDate(today.getDate() + ((dow === 0) ? -6 : 1 - dow));
            const sv = formatDate(monday);
            document.getElementById('gen_start_date').value = sv;
            onGenStartDateChange(sv);
            openModal('generateModal');
        }

        function closeGenerateModal() {
            closeModal('generateModal');
        }

        function setGenerateLoading(on) {
            document.getElementById('btnDoGenerate').disabled = on;
            document.getElementById('gen-btn-text').textContent = on ? 'Memproses...' : 'Generate Sekarang';
            document.getElementById('gen-icon-bolt').classList.toggle('hidden', on);
            document.getElementById('gen-icon-spin').classList.toggle('hidden', !on);
        }

        function doGenerate() {
            const sd = document.getElementById('gen_start_date').value;
            const ed = document.getElementById('gen_end_date').value;
            const tk = document.getElementById('tenagakerjarum').value;
            if (!sd || !ed) return alert('Harap pilih tanggal awal periode generate terlebih dahulu');
            setGenerateLoading(true);
            const form = new FormData();
            form.append('_token', '{{ csrf_token() }}');
            form.append('start_date', sd);
            form.append('end_date', ed);
            form.append('tenagakerjarum', tk);
            fetch('{{ route('finance.pembayaran-upah-mingguan.generate') }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: form,
                })
                .then(async r => {
                    const ct = r.headers.get('content-type') || '';
                    if (!ct.includes('application/json')) throw new Error('Server error: HTTP ' + r.status);
                    return r.json();
                })
                .then(res => {
                    closeModal('generateModal');
                    if (res.success) {
                        showLoading('Generate berhasil, memuat ulang data...');
                        showToast('success', res.message);
                        setTimeout(() => {
                            hideLoading();
                            if (window._triggerAjaxFetch) window._triggerAjaxFetch();
                        }, 1500);
                    } else {
                        showToast('error', res.message);
                        setGenerateLoading(false);
                    }
                })
                .catch(err => {
                    closeModal('generateModal');
                    showToast('error', err.message);
                    setGenerateLoading(false);
                });
        }

        function showToast(type, msg) {
            const colors = type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white';
            const icon = type === 'success' ?
                '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>' :
                '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
            const t = document.createElement('div');
            t.className =
                `fixed top-6 right-6 z-[9999] flex items-start gap-3 px-5 py-4 rounded-xl shadow-xl ${colors} max-w-sm transition-all duration-300 opacity-0 translate-y-2`;
            t.innerHTML = `${icon}<span class="text-sm font-medium">${msg}</span>`;
            document.body.appendChild(t);
            setTimeout(() => {
                t.style.opacity = '1';
                t.style.transform = 'translateY(0)';
            }, 10);
            setTimeout(() => {
                t.style.opacity = '0';
                t.style.transform = 'translateY(-8px)';
                setTimeout(() => t.remove(), 300);
            }, 4000);
        }

        function openModal(id) {
            const m = document.getElementById(id);
            m.classList.remove('invisible');
            m.classList.add('visible');
            setTimeout(() => {
                m.style.opacity = '1';
                const inner = m.querySelector('.bg-white');
                if (inner) inner.style.transform = 'scale(1)';
            }, 10);
        }

        function closeModal(id) {

            const targetId = id || 'listModal';
            const m = document.getElementById(targetId);
            if (!m) return;
            m.style.opacity = '0';
            const inner = m.querySelector('.bg-white');
            if (inner) inner.style.transform = 'scale(0.95)';
            setTimeout(() => {
                m.classList.remove('visible');
                m.classList.add('invisible');
            }, 300);
        }

        const isHarian = {{ session('tenagakerjarum') == 'Harian' ? 'true' : 'false' }};
        const colCount = isHarian ? 5 : 7;

        function buildSkeletonRows(n = 5) {
            const widths = ['w-6', 'w-28', 'w-36', 'w-24', 'w-20', 'w-24', 'w-20'];
            return Array.from({
                    length: n
                }, (_, r) =>
                `<tr class="skeleton-row">` +
                Array.from({
                        length: colCount
                    }, (__, c) =>
                    `<td><span class="skeleton-cell ${widths[c] ?? 'w-20'}" style="animation-delay:${(r*colCount+c)*40}ms"></span></td>`
                ).join('') +
                `</tr>`
            ).join('');
        }

        function showList(transno) {
            const tbody = document.getElementById('listTableBody');
            const badge = document.getElementById('modal-loading-badge');
            const infoSkel = document.getElementById('modal-info-skeleton');
            const infoReal = document.getElementById('modal-info-real');
            const activityInfo = document.getElementById('modal-activity-info');

            document.getElementById('modal-transno').textContent = transno;
            if (activityInfo) activityInfo.classList.add('hidden');
            tbody.innerHTML = buildSkeletonRows(6);

            const _elPlot = document.getElementById('modal-plot');
            const _elLuasan = document.getElementById('modal-luasan');
            const _elHasil = document.getElementById('modal-hasil');
            if (_elPlot) _elPlot.textContent = '-';
            if (_elLuasan) _elLuasan.textContent = '-';
            if (_elHasil) _elHasil.textContent = '-';

            if (badge) {
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            }
            if (infoSkel) {
                infoSkel.style.display = '';
                infoSkel.classList.remove('hidden');
            }
            if (infoReal) {
                infoReal.style.display = 'none';
            }
            openModal('listModal');
            const url = `{{ route('finance.pembayaran-upah-mingguan.show', ['transno' => '__id__']) }}`
                .replace('__id__', transno);

            fetch(url)
                .then(r => {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(res => {

                    if (badge) {
                        badge.classList.add('hidden');
                        badge.classList.remove('flex');
                    }

                    // Tampilkan info kegiatan dari header
                    const hdr = res.header || {};
                    const elAct = document.getElementById('modal-activityname');
                    const elMandor = document.getElementById('modal-mandorname');
                    const elPeriode = document.getElementById('modal-periode');
                    if (activityInfo) {
                        if (elAct) elAct.textContent = hdr.activityname || hdr.activitycode || '-';
                        if (elMandor) elMandor.textContent = hdr.mandorname || hdr.mandoruserid || '-';
                        if (elPeriode) elPeriode.textContent = (hdr.startdate || '-') + ' s/d ' + (hdr.enddate || '-');
                        activityInfo.classList.remove('hidden');
                    }

                    if (res.error) {
                        tbody.innerHTML =
                            `<tr><td colspan="${colCount}" class="text-center py-8 text-red-600">${res.error}</td></tr>`;
                        return;
                    }
                    const data = res.data || [];
                    if (!data.length) {
                        tbody.innerHTML =
                            `<tr><td colspan="${colCount}" class="text-center py-8 text-gray-500">Tidak ada data</td></tr>`;
                        return;
                    }

                    tbody.innerHTML = '';

                    @if (session('tenagakerjarum') == 'Harian')

                        const _elPlot = document.getElementById('modal-plot');
                        const _elLuasan = document.getElementById('modal-luasan');
                        const _elHasil = document.getElementById('modal-hasil');
                        if (_elPlot) _elPlot.textContent = data[0].plot || '-';
                        if (_elLuasan) _elLuasan.textContent = data[0].luasrkh || '-';
                        if (_elHasil) _elHasil.textContent = data[0].luashasil || '-';
                        if (infoSkel) infoSkel.style.display = 'none';
                        if (infoReal) infoReal.style.display = 'grid';

                        const gMap = new Map();
                        data.forEach(item => {
                            const k = item.tenagakerjaid || '',
                                tot = parseIDR(item.total);
                            if (!gMap.has(k)) gMap.set(k, {
                                namatenagakerja: item.namatenagakerja || '-',
                                tanggal_min: item.tanggal || '',
                                tanggal_max: item.tanggal || '',
                                biaya_per_hari: parseIDR(item.upah),
                                total: tot
                            });
                            else {
                                const g = gMap.get(k);
                                if (item.tanggal) {
                                    if (!g.tanggal_min || item.tanggal < g.tanggal_min) g.tanggal_min = item
                                        .tanggal;
                                    if (!g.tanggal_max || item.tanggal > g.tanggal_max) g.tanggal_max = item
                                        .tanggal;
                                }
                                g.total += tot;
                            }
                        });
                        let no = 1,
                            gt = 0;
                        gMap.forEach(g => {
                            const per = (!g.tanggal_min || g.tanggal_min === g.tanggal_max) ?
                                (g.tanggal_min || '-') :
                                `${g.tanggal_min} s/d ${g.tanggal_max}`;
                            gt += g.total;
                            tbody.innerHTML += `<tr class="hover:bg-indigo-50 transition-colors duration-150">
                                <td class="px-4 py-3 text-sm text-gray-900">${no++}.</td>
                                <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">${per}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">${g.namatenagakerja}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700">${fmtIDR(g.biaya_per_hari)}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-indigo-700">${fmtIDR(g.total)}</td>
                            </tr>`;
                        });
                        tbody.innerHTML += `<tr class="font-bold bg-indigo-50">
                            <td colspan="4" class="px-4 py-3 text-right border-t-2 border-indigo-400 text-gray-900">Grand Total:</td>
                            <td class="px-4 py-3 border-t-2 border-indigo-400 text-right text-indigo-700">${fmtIDR(gt)}</td>
                        </tr>`;
                    @else
                        let no = 1,
                            gt = 0;
                        data.forEach(item => {
                            const tot = parseIDR(item.total);
                            gt += tot;
                            tbody.innerHTML += `<tr class="hover:bg-indigo-50 transition-colors duration-150">
                                <td class="px-4 py-3 text-sm text-gray-900">${no++}.</td>
                                <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">${item.tanggal||'-'}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">${item.plot||'-'}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700">${fmtIDR(parseIDR(item.upah))}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700">${item.luasrkh??'-'}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700">${item.luashasil??'-'}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-indigo-700">${fmtIDR(tot)}</td>
                            </tr>`;
                        });
                        tbody.innerHTML += `<tr class="font-bold bg-indigo-50">
                            <td colspan="6" class="px-4 py-3 text-right border-t-2 border-indigo-400 text-gray-900">Grand Total:</td>
                            <td class="px-4 py-3 border-t-2 border-indigo-400 text-right text-indigo-700">${fmtIDR(gt)}</td>
                        </tr>`;
                    @endif
                })
                .catch(err => {
                    if (badge) {
                        badge.classList.add('hidden');
                        badge.classList.remove('flex');
                    }
                    tbody.innerHTML = `<tr><td colspan="${colCount}" class="text-center py-8 text-red-600">
                        <svg class="w-8 h-8 mx-auto mb-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
                        </svg>
                        Gagal memuat data: ${err.message}
                    </td></tr>`;
                });
        }

        function exportToExcel() {
            const sd = document.getElementById('start_date').value;
            const ed = document.getElementById('end_date').value;
            if (!sd || !ed) return alert('Harap pilih range tanggal terlebih dahulu');
            window.location.href =
                `{{ route('finance.pembayaran-upah-mingguan.export-excel') }}?start_date=${sd}&end_date=${ed}`;
        }
    </script>
</x-layout>
