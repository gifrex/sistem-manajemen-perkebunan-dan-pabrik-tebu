<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-6 bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-lg border border-gray-200">
        <!-- Header Section -->
        <div class="px-6 pb-4 border-b border-gray-200">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <!-- Title & Info -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Report Agronomi
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Data pengamatan dan analisis pertanian</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    @can('report.agronomi.export')
                        <button data-export="agronomi"
                            class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd"
                                    d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v9.293l-2-2a1 1 0 0 0-1.414 1.414l.293.293h-6.586a1 1 0 1 0 0 2h6.586l-.293.293A1 1 0 0 0 18 16.707l2-2V20a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Export Excel</span>
                        </button>
                    @endcan
                    @can('dashboard.agronomi.pivot')
                        <button
                            class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2"
                            onclick="window.location.href='{{ route('report.pivotTableAgronomi', ['start_date' => old('start_date', request()->start_date), 'end_date' => old('end_date', request()->end_date)]) }}'">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd"
                                    d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Zm-1 9a1 1 0 1 0-2 0v2a1 1 0 1 0 2 0v-2Zm2-5a1 1 0 0 1 1 1v6a1 1 0 1 1-2 0v-6a1 1 0 0 1 1-1Zm4 4a1 1 0 1 0-2 0v3a1 1 0 1 0 2 0v-3Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Export Pivot</span>
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <form method="POST" action="{{ route('report.agronomi.index') }}">
            @csrf
            <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <div class="flex items-center gap-4 flex-wrap justify-between">
                    <!-- Date Filter -->
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Range Tanggal:
                        </label>
                        <div class="relative">
                            <button type="button"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-sm font-medium text-gray-700"
                                id="menu-button" onclick="toggleDropdown()">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span id="date-label">
                                    {{ $startDate }} s/d {{ $endDate }}
                                </span>
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
                                            class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Mulai</label>
                                        <input type="date" id="start_date" name="start_date" required
                                            value="{{ old('start_date', $startDate ?? '') }}"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm transition-all duration-200">
                                    </div>

                                    <div>
                                        <label for="end_date"
                                            class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Akhir</label>
                                        <input type="date" id="end_date" name="end_date" required
                                            value="{{ old('end_date', $endDate ?? '') }}"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm transition-all duration-200">
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

                    <!-- Items per page & Search -->
                    <div class="flex items-center gap-4 flex-wrap">
                        <div id="ajax-data" data-url="{{ route('report.agronomi.index') }}">
                            <div class="flex items-center gap-2">
                                <label for="perPage"
                                    class="text-sm font-semibold text-gray-700 whitespace-nowrap">Items per
                                    page:</label>
                                <input type="text" name="perPage" id="perPage" value="{{ $perPage }}"
                                    min="1" autocomplete="off"
                                    class="w-16 px-3 py-2 border border-gray-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-all duration-200" />
                            </div>
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
                                class="w-80 pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                placeholder="Cari Sample, Plot, Variety, Category..." />
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Table Section -->
        <div class="px-6 py-5">
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full bg-white text-sm" id="tables">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-100 to-gray-50">
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                No.</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                No. Sample</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Blok</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Plot</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Luas</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Varietas</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Kategori</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Tanggal Tanam</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Umur Tanam</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Jarak Tanam</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Tanggal Pengamatan</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Bulan Pengamatan</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Bulan Panen</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Umur Panen</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Tanggal ZPK</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                No. Urut</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Jumlah Batang</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Jumlah Batang Primer</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Jumlah Batang Sekunder</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Jumlah Batang Tersier</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Jumlah Batang Kuarter</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Panjang GAP</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                %GAP</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                %Germinasi</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                pH Tanah</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Populasi</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Kotak Gulma</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                %Penutupan Gulma</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Tinggi Primer</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Tinggi Sekunder</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Tinggi Tersier</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Tinggi Kuarter</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Diameter Primer</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Diameter Sekunder</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Diameter Tersier</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Diameter Kuarter</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Berat Batang Primer</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Berat Batang Sekunder</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Berat Batang Tersier</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Berat Batang Kuarter</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Brix Batang Primer</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Brix Batang Sekunder</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Brix Batang Tersier</th>
                            <th
                                class="py-3 px-4 border-b-2 border-gray-300 text-gray-700 font-bold text-center whitespace-nowrap">
                                Brix Batang Kuarter</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($agronomi as $item)
                            <tr class="hover:bg-blue-50 transition-colors duration-150">
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->no }}.</td>
                                <td class="py-3 px-4 text-center text-gray-700 font-medium">{{ $item->nosample }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->blokName ?? '-' }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->plotName ?? '-' }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->luasarea ?? '-' }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->varietas ?? '-' }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->kat ?? '-' }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->tanggaltanam ?? '-' }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ round($item->umur_tanam) }} Bulan
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->jaraktanam ?? '-' }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->tanggalpengamatan ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->bulanPengamatan }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->bulanpanen ?? '-' }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->umurpanen ?? '-' }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->tanggalzpk ?? '-' }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->nourut }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->jumlahbatang }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->bat_primer }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->bat_sekunder }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->bat_tersier }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->bat_kuarter }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->pan_gap }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $item->per_gap * 100 }}%
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center text-gray-700">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $item->per_germinasi * 100 }}%
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->ph_tanah }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->populasi }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->ktk_gulma }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ $item->per_gulma * 100 }}%
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->t_primer }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->t_sekunder }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->t_tersier }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->t_kuarter }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->d_primer }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->d_sekunder }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->d_tersier }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->d_kuarter }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->berat_primer }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->berat_sekunder }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->berat_tersier }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->berat_kuarter }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->brix_primer }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->brix_sekunder }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->brix_tersier }}</td>
                                <td class="py-3 px-4 text-center text-gray-700">{{ $item->brix_kuarter }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="29" class="p-0 border-0">
                                    <div
                                        style="
                                            position: sticky;
                                            left: 0;
                                            width: calc(100vw - 330px);
                                            max-width: calc(100vw - 330px);
                                            display: flex;
                                            flex-direction: column;
                                            align-items: center;
                                            justify-content: center;
                                            gap: 12px;
                                            padding: 64px 16px;
                                        ">
                                        <div
                                            style="
                                                width: 64px;
                                                height: 64px;
                                                border-radius: 9999px;
                                                background-color: #f3f4f6;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                            ">
                                            <svg style="width:32px;height:32px;color:#9ca3af" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div style="text-align:center">
                                            <p style="color:#4b5563;font-weight:600;font-size:0.875rem">Tidak ada data
                                                ditemukan</p>
                                            <p style="color:#9ca3af;font-size:0.75rem;margin-top:4px">Coba ubah rentang
                                                tanggal atau kata kunci pencarian</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Section -->
        <div class="px-6 pb-2" id="pagination-links">
            @if ($agronomi->hasPages())
                {{ $agronomi->appends(['perPage' => $agronomi->perPage(), 'start_date' => $startDate, 'end_date' => $endDate])->links() }}
            @else
                <div class="flex items-center justify-between bg-gray-50 px-4 py-3 rounded-lg">
                    <p class="text-sm text-gray-600">
                        Menampilkan <span class="font-semibold text-gray-800">{{ $agronomi->count() }}</span> dari
                        <span class="font-semibold text-gray-800">{{ $agronomi->total() }}</span> hasil
                    </p>
                </div>
            @endif
        </div>
    </div>

    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('menu-dropdown');
            dropdown.classList.toggle('hidden');
        }

        document.addEventListener("click", function(event) {
            const dropdown = document.getElementById("menu-dropdown");
            const button = document.getElementById("menu-button");

            if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                dropdown.classList.add("hidden");
            }
        });
    </script>

    <style>
        th,
        td {
            white-space: nowrap;
        }

        /* Custom scrollbar */
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

</x-layout>
