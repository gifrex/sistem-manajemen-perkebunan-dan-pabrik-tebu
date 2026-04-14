<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto px-6 py-4">
        <div class="flex flex-wrap gap-4">
            <!-- Filter Card -->
            <div class="w-full bg-white shadow-lg rounded-xl border border-gray-200">
                <form id="filterForm">
                    @csrf
                    <div
                        class="bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-3 flex items-center justify-between rounded-t-xl">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            <h3 class="text-base font-bold text-white">Filter Data Agronomi</h3>
                        </div>
                    </div>

                    <div class="px-4 py-4 bg-gray-50 flex items-center gap-3 justify-start flex-wrap rounded-b-xl">
                        <!-- Average of -->
                        <div class="filter-item-compact">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Average of</label>
                            <div class="relative">
                                <button type="button" id="dropdownButtonAvg"
                                    class="w-[180px] flex items-center justify-between rounded-lg border border-gray-300 shadow-sm hover:border-emerald-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 bg-white px-3 py-2 text-sm font-medium text-gray-800 transition-all">
                                    <span id="dropdownLabelAvg" class="truncate">% Germinasi</span>
                                    <svg class="ml-2 w-4 h-4 text-emerald-500 flex-shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="m19 9-7 7-7-7" />
                                    </svg>
                                </button>
                                <div id="dropdownContentAvg"
                                    class="z-50 absolute hidden mt-1 w-full text-sm bg-white border border-gray-200 shadow-xl rounded-lg p-2 max-h-[280px] overflow-auto">
                                    <div class="flex flex-col space-y-0.5">
                                        @foreach ($verticalLabels as $key => $label)
                                            <button type="button"
                                                class="text-left w-full px-3 py-2 rounded-md hover:bg-emerald-50 text-gray-700 text-sm transition-colors"
                                                onclick="selectAverage('{{ $key }}', '{{ $label }}')">{{ $label }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="vertical" id="selectedAverage" value="per_germinasi">
                        </div>

                        <!-- Kebun Filter -->
                        <div class="filter-item">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Kebun</label>
                            <div class="relative">
                                <button type="button" id="dropdownButtonComp"
                                    class="flex items-center justify-between rounded-lg border border-gray-300 shadow-sm hover:border-emerald-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 bg-white px-3 py-2 text-sm font-medium text-gray-800 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-emerald-500"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium">Kebun</span>
                                    <svg class="ml-1.5 w-4 h-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="m19 9-7 7-7-7" />
                                    </svg>
                                </button>
                                <div id="dropdownContentComp"
                                    class="z-50 absolute hidden mt-1 w-[280px] text-sm bg-white border border-gray-200 shadow-xl rounded-lg p-3 max-h-[380px]">
                                    <div class="flex flex-col space-y-2">
                                        <h6 class="text-xs font-bold text-gray-900 border-b pb-2">Pilih Kebun</h6>
                                        <div class="relative">
                                            <input type="text" id="searchComp" placeholder="Search..."
                                                class="w-full pl-8 pr-3 py-1.5 border border-gray-300 rounded-md focus:border-emerald-400 focus:ring-1 focus:ring-emerald-200 text-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 absolute left-2.5 top-2 text-gray-400" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                        <div class="max-h-[250px] overflow-auto">
                                            <label
                                                class="inline-flex items-center hover:bg-emerald-50 p-2 rounded-md cursor-pointer border-b border-gray-100">
                                                <input type="checkbox" id="selectAllComp"
                                                    class="form-checkbox w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500" />
                                                <span class="ml-2 font-semibold text-gray-700 text-sm">(Select
                                                    All)</span>
                                            </label>
                                            @foreach ($kdCompAgroOpt as $comp)
                                                <label
                                                    class="inline-flex items-center hover:bg-emerald-50 p-2 rounded-md cursor-pointer comp-item"
                                                    data-comp-name="{{ strtolower($comp->name) }}">
                                                    <input type="checkbox" name="companycode[]"
                                                        value="{{ $comp->companycode }}"
                                                        class="form-checkbox comp-checkbox w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500" />
                                                    <span class="ml-2 text-gray-700 text-sm">{{ $comp->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Blok Filter -->
                        <div class="filter-item">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Blok</label>
                            <div class="relative">
                                <button type="button" id="dropdownButtonBlok"
                                    class="flex items-center justify-between rounded-lg border border-gray-300 shadow-sm hover:border-emerald-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 bg-white px-3 py-2 text-sm font-medium text-gray-800 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-emerald-500"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" />
                                    </svg>
                                    <span class="font-medium">Blok</span>
                                    <svg class="ml-1.5 w-4 h-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="m19 9-7 7-7-7" />
                                    </svg>
                                </button>
                                <div id="dropdownContentBlok"
                                    class="z-50 absolute hidden mt-1 w-[170px] text-sm bg-white border border-gray-200 shadow-xl rounded-lg p-3 max-h-[380px]">
                                    <div class="flex flex-col space-y-2">
                                        <h6 class="text-xs font-bold text-gray-900 border-b pb-2">Pilih Blok</h6>
                                        <div class="relative">
                                            <input type="text" id="searchBlok" placeholder="Search..."
                                                class="w-full pl-8 pr-3 py-1.5 border border-gray-300 rounded-md focus:border-emerald-400 focus:ring-1 focus:ring-emerald-200 text-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 absolute left-2.5 top-2 text-gray-400" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                        <div class="max-h-[250px] overflow-auto">
                                            <label
                                                class="flex items-center hover:bg-emerald-50 p-2 rounded-md cursor-pointer border-b border-gray-100 w-full">
                                                <input type="checkbox" id="selectAllBlok"
                                                    class="form-checkbox w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500" />
                                                <span class="ml-2 font-semibold text-emerald-700 text-sm">(Select
                                                    All)</span>
                                            </label>
                                            @foreach ($kdBlokAgroOpt as $blok)
                                                <label
                                                    class="flex items-center hover:bg-emerald-50 p-2 rounded-md cursor-pointer blok-item w-full"
                                                    data-blok-name="{{ strtolower($blok->blok) }}">
                                                    <input type="checkbox" name="blok[]"
                                                        value="{{ $blok->blok }}"
                                                        class="form-checkbox blok-checkbox w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500" />
                                                    <span
                                                        class="ml-2 text-gray-700 text-sm">{{ $blok->blok }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Plot Filter -->
                        <div class="filter-item">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Plot</label>
                            <div class="relative">
                                <button type="button" id="dropdownButtonPlot"
                                    class="flex items-center justify-between rounded-lg border border-gray-300 shadow-sm hover:border-emerald-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 bg-white px-3 py-2 text-sm font-medium text-gray-800 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-emerald-500"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" />
                                    </svg>
                                    <span class="font-medium">Plot</span>
                                    <svg class="ml-1.5 w-4 h-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="m19 9-7 7-7-7" />
                                    </svg>
                                </button>
                                <div id="dropdownContentPlot"
                                    class="z-50 absolute hidden mt-1 w-[170px] text-sm bg-white border border-gray-200 shadow-xl rounded-lg p-3 max-h-[380px]">
                                    <div class="flex flex-col space-y-2">
                                        <h6 class="text-xs font-bold text-gray-900 border-b pb-2">Pilih Plot</h6>
                                        <div class="relative">
                                            <input type="text" id="searchPlot" placeholder="Search..."
                                                class="w-full pl-8 pr-3 py-1.5 border border-gray-300 rounded-md focus:border-emerald-400 focus:ring-1 focus:ring-emerald-200 text-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 absolute left-2.5 top-2 text-gray-400" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                        <div class="max-h-[250px] overflow-auto">
                                            <label
                                                class="inline-flex items-center hover:bg-emerald-50 p-2 rounded-md cursor-pointer border-b border-gray-100">
                                                <input type="checkbox" id="selectAllPlot"
                                                    class="form-checkbox w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500" />
                                                <span class="ml-2 font-semibold text-emerald-700 text-sm">(Select
                                                    All)</span>
                                            </label>
                                            @foreach ($kdPlotAgroOpt as $plot)
                                                <label
                                                    class="flex items-center hover:bg-emerald-50 p-2 rounded-md cursor-pointer plot-item w-full"
                                                    data-plot-name="{{ strtolower($plot->plot) }}">
                                                    <input type="checkbox" name="plot[]"
                                                        value="{{ $plot->plot }}"
                                                        class="form-checkbox plot-checkbox w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500" />
                                                    <span
                                                        class="ml-2 text-gray-700 text-sm">{{ $plot->plot }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Waktu Pengamatan -->
                        <div class="filter-item">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Waktu Pengamatan</label>
                            <div class="relative">
                                <button type="button"
                                    class="inline-flex items-center rounded-lg border border-gray-300 shadow-sm px-3 py-2 bg-white text-sm font-medium text-gray-800 hover:border-emerald-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all"
                                    id="menu-button" onclick="toggleDropdown()">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-emerald-500"
                                        viewbox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium">Waktu</span>
                                    <svg class="ml-1.5 h-4 w-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div class="absolute z-50 mt-1 w-[250px] rounded-lg bg-white border border-gray-200 shadow-xl hidden"
                                    id="menu-dropdown">
                                    <div class="py-3 px-3">
                                        <div class="flex items-center gap-2 mb-2">
                                            <div class="flex-1">
                                                <label for="start_month"
                                                    class="block text-xs font-semibold text-gray-700 mb-1">Start</label>
                                                <select id="start_month" name="start_month"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-400 focus:ring-1 focus:ring-emerald-200 text-xs py-1.5 px-2">
                                                    <option value="">Month</option>
                                                    @foreach ($monthsLabel as $month)
                                                        <option value="{{ $month }}">{{ $month }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <span class="text-gray-400 mt-5">/</span>
                                            <div class="w-20">
                                                <label for="start_year"
                                                    class="block text-xs font-semibold text-gray-700 mb-1">Year</label>
                                                <input type="text" name="start_year" id="start_year"
                                                    oninput="validateNumber(this)"
                                                    class="border rounded-md border-gray-300 p-1.5 w-full text-xs focus:border-emerald-400 focus:ring-1 focus:ring-emerald-200"
                                                    maxlength="4" value="{{ now()->format('Y') }}">
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 mb-3">
                                            <div class="flex-1">
                                                <label for="end_month"
                                                    class="block text-xs font-semibold text-gray-700 mb-1">End</label>
                                                <select id="end_month" name="end_month"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-400 focus:ring-1 focus:ring-emerald-200 text-xs py-1.5 px-2">
                                                    <option value="">Month</option>
                                                    @foreach ($monthsLabel as $month)
                                                        <option value="{{ $month }}">{{ $month }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <span class="text-gray-400 mt-5">/</span>
                                            <div class="w-20">
                                                <label for="end_year"
                                                    class="block text-xs font-semibold text-gray-700 mb-1">Year</label>
                                                <input type="text" name="end_year" id="end_year"
                                                    oninput="validateNumber(this)"
                                                    class="border rounded-md border-gray-300 p-1.5 w-full text-xs focus:border-emerald-400 focus:ring-1 focus:ring-emerald-200"
                                                    maxlength="4" value="{{ now()->format('Y') }}">
                                            </div>
                                        </div>
                                        <button type="button" onclick="applyFilters()"
                                            class="w-full py-2 px-3 border border-transparent shadow-sm text-xs font-semibold rounded-lg text-white bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 focus:outline-none focus:ring-2 focus:ring-emerald-300 transition-all">Apply</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Umur Tanaman -->
                        <div class="filter-item">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Umur Tanaman</label>
                            <div class="relative">
                                <button type="button"
                                    class="inline-flex items-center rounded-lg border border-gray-300 shadow-sm px-3 py-2 bg-white text-sm font-medium text-gray-800 hover:border-emerald-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all"
                                    id="age-menu-button" onclick="toggleAgeDropdown()">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-emerald-500"
                                        viewbox="0 0 20 20" fill="currentColor">
                                        <path
                                            d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                                    </svg>
                                    <span class="font-medium">Umur</span>
                                    <svg class="ml-1.5 h-4 w-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div class="absolute z-50 mt-1 w-[180px] rounded-lg bg-white border border-gray-200 shadow-xl hidden"
                                    id="age-menu-dropdown">
                                    <div class="py-3 px-3">
                                        <div class="flex items-center gap-2 mb-2">
                                            <div class="flex-1">
                                                <label for="min_age"
                                                    class="block text-xs font-semibold text-gray-700 mb-1">Min
                                                    Age</label>
                                                <input type="number" name="min_age" id="min_age"
                                                    class="border rounded-md border-gray-300 p-1.5 w-full text-xs focus:border-emerald-400 focus:ring-1 focus:ring-emerald-200"
                                                    min="0" placeholder="Min">
                                            </div>
                                            <span class="text-gray-400 mt-5">-</span>
                                            <div class="flex-1">
                                                <label for="max_age"
                                                    class="block text-xs font-semibold text-gray-700 mb-1">Max
                                                    Age</label>
                                                <input type="number" name="max_age" id="max_age"
                                                    class="border rounded-md border-gray-300 p-1.5 w-full text-xs focus:border-emerald-400 focus:ring-1 focus:ring-emerald-200"
                                                    min="0" placeholder="Max">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="age_unit"
                                                class="block text-xs font-semibold text-gray-700 mb-1">Unit</label>
                                            <select id="age_unit" name="age_unit"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-400 focus:ring-1 focus:ring-emerald-200 text-xs py-1.5 px-2">
                                                <option value="bulan" selected>Bulan</option>
                                                <option value="tahun">Tahun</option>
                                            </select>
                                        </div>
                                        <button type="button" onclick="applyFilters()"
                                            class="w-full py-2 px-3 border border-transparent shadow-sm text-xs font-semibold rounded-lg text-white bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 focus:outline-none focus:ring-2 focus:ring-emerald-300 transition-all">Apply</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Chart Card -->
            <div class="w-full">
                <div class="bg-white shadow-lg rounded-xl p-5 border border-gray-200">
                    <div class="mb-4 pb-3 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <div class="p-1.5 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            Data Visualization
                        </h3>
                    </div>

                    <!-- Chart Container with relative positioning -->
                    <div class="relative min-h-[400px]">
                        <!-- Empty State (shown when no filter applied) -->
                        <div id="emptyState" class="flex flex-col items-center justify-center py-20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-gray-300 mb-4"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <h4 class="text-lg font-bold text-gray-500 mb-2">Belum ada Filter yang diterapkan</h4>
                            <p class="text-gray-400 text-center max-w-md">
                                Silakan pilih filter (Kebun, Blok, Plot, Waktu Pengamatan, atau Umur Tanaman) untuk
                                menampilkan data visualisasi.
                            </p>
                        </div>

                        <!-- Loading Overlay (shown when loading with transparent white background) -->
                        <div id="loadingOverlay"
                            class="absolute inset-0 bg-white bg-opacity-90 items-center justify-center rounded-lg z-10 hidden">
                            <div class="text-center">
                                <svg class="animate-spin h-12 w-12 mx-auto text-emerald-500 mb-4"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <p class="text-gray-600 font-medium">Memuat data...</p>
                            </div>
                        </div>

                        <!-- Chart Canvas -->
                        <canvas id="pivotChart" class="hidden"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let myChart = null;
        let currentVerticalLabel = '% Germinasi';
        let currentHorizontalLabel = '';
        let hasData = false; // Track if chart has data

        function initChart(data) {
            const ctx = document.getElementById('pivotChart').getContext('2d');
            if (myChart) myChart.destroy();

            const rawLabels = data.xAxis || [];
            const rawDatasets = data.chartData || [];

            const sortedIndices = rawLabels.map((label, index) => {
                const parts = label.split(' - ');
                return {
                    index,
                    category: parts[1] || '',
                    age: parseInt(parts[0]) || 0,
                    blokName: parts.splice(-2, 1)[0] || '',
                    companyName: parts.pop()
                };
            }).sort((a, b) => {
                if (a.companyName !== b.companyName) return a.companyName.localeCompare(b.companyName);
                if (a.blokName !== b.blokName) return a.blokName.localeCompare(b.blokName);
                if (a.category !== b.category) return a.category.localeCompare(b.category);
                return a.age - b.age;
            });

            const sortedLabels = sortedIndices.map(item => rawLabels[item.index]);
            const sortedDatasets = rawDatasets.map(dataset => ({
                ...dataset,
                data: sortedIndices.map(item => dataset.data[item.index])
            }));

            currentVerticalLabel = data.verticalLabel || currentVerticalLabel;
            currentHorizontalLabel = data.horizontalLabel || currentHorizontalLabel;

            myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: sortedLabels,
                    datasets: sortedDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const val = context.raw;
                                    return (currentVerticalLabel === 'Populasi' || currentVerticalLabel ===
                                        'pH Tanah') ? val : `${val}%`;
                                }
                            }
                        },
                        legend: {
                            position: 'right'
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: currentHorizontalLabel
                            },
                            stacked: false
                        },
                        y: {
                            title: {
                                display: true,
                                text: currentVerticalLabel
                            },
                            ticks: {
                                callback: (val) => (currentVerticalLabel === 'Populasi' || currentVerticalLabel ===
                                    'pH Tanah') ? val : `${val}%`
                            }
                        }
                    }
                }
            });

            // Update visibility
            hasData = true;
            document.getElementById('emptyState').classList.add('hidden');
            document.getElementById('pivotChart').classList.remove('hidden');
            document.getElementById('pivotChart').style.height = '400px';
        }

        function applyFilters() {
            const formData = new FormData(document.getElementById('filterForm'));

            // Show overlay loading (transparent white background over chart)
            const overlay = document.getElementById('loadingOverlay');
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');

            fetch('{{ route('dashboard.agronomi') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    // Hide loading overlay
                    overlay.classList.add('hidden');
                    overlay.classList.remove('flex');

                    // Update chart
                    initChart(data);

                    // Close dropdowns
                    document.getElementById('menu-dropdown')?.classList.add('hidden');
                    document.getElementById('age-menu-dropdown')?.classList.add('hidden');
                })
                .catch(err => {
                    console.error('Error:', err);
                    overlay.classList.add('hidden');
                    overlay.classList.remove('flex');
                    alert('Terjadi kesalahan saat memuat data');
                });
        }

        document.querySelectorAll('.comp-checkbox, .blok-checkbox, .plot-checkbox').forEach(cb => {
            cb.addEventListener('change', applyFilters);
        });

        ['searchComp', 'searchBlok', 'searchPlot'].forEach(id => {
            document.getElementById(id).addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                const type = id.replace('search', '').toLowerCase();
                document.querySelectorAll(`.${type}-item`).forEach(item => {
                    const name = item.getAttribute(`data-${type}-name`);
                    item.style.display = name.includes(term) ? '' : 'none';
                });
            });
        });

        document.addEventListener("DOMContentLoaded", () => {
            ["Comp", "Blok", "Plot"].forEach(type => {
                const btn = document.getElementById(`dropdownButton${type}`);
                const content = document.getElementById(`dropdownContent${type}`);
                const selectAll = document.getElementById(`selectAll${type}`);
                const checkboxes = document.querySelectorAll(`.${type.toLowerCase()}-checkbox`);

                btn.addEventListener("click", () => content.classList.toggle("hidden"));
                document.addEventListener("click", (e) => {
                    if (!btn.contains(e.target) && !content.contains(e.target)) {
                        content.classList.add("hidden");
                    }
                });
                selectAll.addEventListener("change", (e) => {
                    checkboxes.forEach(cb => cb.checked = e.target.checked);
                    applyFilters();
                });
                checkboxes.forEach(cb => {
                    cb.addEventListener("change", () => {
                        selectAll.checked = [...checkboxes].every(c => c.checked);
                    });
                });
            });
        });

        function toggleAgeDropdown() {
            document.getElementById('age-menu-dropdown').classList.toggle('hidden');
            document.getElementById('menu-dropdown').classList.add('hidden');
        }

        function toggleDropdown() {
            document.getElementById('menu-dropdown').classList.toggle('hidden');
            document.getElementById('age-menu-dropdown').classList.add('hidden');
        }

        document.addEventListener('click', (e) => {
            const ageBtn = document.getElementById('age-menu-button');
            const ageDrop = document.getElementById('age-menu-dropdown');
            const timeBtn = document.getElementById('menu-button');
            const timeDrop = document.getElementById('menu-dropdown');

            if (!ageBtn.contains(e.target) && !ageDrop.contains(e.target)) ageDrop.classList.add('hidden');
            if (!timeBtn.contains(e.target) && !timeDrop.contains(e.target)) timeDrop.classList.add('hidden');
        });

        const btnAvg = document.getElementById("dropdownButtonAvg");
        const menuAvg = document.getElementById("dropdownContentAvg");
        const labelAvg = document.getElementById("dropdownLabelAvg");
        const inputAvg = document.getElementById("selectedAverage");

        btnAvg.addEventListener("click", () => menuAvg.classList.toggle("hidden"));

        function selectAverage(value, label) {
            inputAvg.value = value;
            labelAvg.textContent = label;
            menuAvg.classList.add("hidden");
            applyFilters();
        }

        document.addEventListener("click", (e) => {
            if (!btnAvg.contains(e.target) && !menuAvg.contains(e.target)) menuAvg.classList.add("hidden");
        });

        function validateNumber(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
        }
    </script>

    <style>
        .filter-item-compact {
            min-width: 140px;
        }

        button,
        label {
            transition: all 0.2s ease-in-out;
        }

        .overflow-auto::-webkit-scrollbar {
            width: 6px;
        }

        .overflow-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .overflow-auto::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #c7fed3 0%, #a5e0fc 100%);
            border-radius: 10px;
        }

        .overflow-auto::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #a5fcb8 0%, #81e0f8 100%);
        }
    </style>
</x-layout>
