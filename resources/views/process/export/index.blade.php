<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $title }}</x-slot:nav>

    <div class="max-w-5xl mx-auto p-6">
        <!-- Header Section -->
        <div class="mb-8 text-center">
            <div
                class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl shadow-lg mb-4">
                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Export KML File</h1>
            <p class="text-gray-600">Generate KML visualization for Google Earth</p>
        </div>

        <!-- Main Form Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <div class="bg-gradient-to-r from-blue-50 to-cyan-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                    <h2 class="text-lg font-semibold text-gray-800">Configuration Settings</h2>
                </div>
            </div>

            <form action="{{ route('process.exportkml.submit') }}" method="post" id="kmlForm" class="p-6">
                @csrf

                <!-- Observation Selection -->
                <div class="mb-6">
                    <label for="observe" class="flex items-center gap-2 font-semibold text-gray-800 mb-3">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Pengamatan
                    </label>
                    <select name="observe" id="observe"
                        class="w-full block border-2 border-gray-200 rounded-xl px-4 py-3 text-gray-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200 hover:border-gray-300"
                        required>
                        <option value="" disabled
                            {{ old('observe', session('observe')) == null ? 'selected' : '' }} class="text-gray-400">
                            -- Pilih Pengamatan --
                        </option>
                        <option value="Agronomi"
                            {{ old('observe', session('observe')) == 'Agronomi' ? 'selected' : '' }}
                            class="text-gray-800">
                            🌱 Agronomi
                        </option>
                        <option value="HPT" {{ old('observe', session('observe')) == 'HPT' ? 'selected' : '' }}
                            class="text-gray-800">
                            🐛 HPT (Hama & Penyakit Tanaman)
                        </option>
                    </select>
                </div>

                <!-- Variable Selection -->
                <div class="mb-6">
                    <label for="variable" class="flex items-center gap-2 font-semibold text-gray-800 mb-3">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Variabel
                    </label>
                    <select id="variable" name="variable" disabled
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-gray-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200 disabled:bg-gray-50 disabled:cursor-not-allowed">
                        <option value="" class="text-gray-400">🚫 Pengamatan belum dipilih</option>
                    </select>
                    <p class="text-sm text-gray-500 mt-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        Opsi variabel akan ditampilkan berdasarkan pengamatan yang dipilih
                    </p>
                </div>

                <!-- Color Parameters Section -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 mb-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                            <h3 class="font-bold text-lg text-gray-800">Parameter Warna</h3>
                        </div>
                        <p id="param-warning" class="text-sm text-red-500 font-medium flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Pengamatan belum dipilih
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Red Parameter -->
                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border-2 border-gray-200 hover:border-red-300 transition-colors">
                            <label for="par1" class="flex items-center gap-2 text-gray-700 mb-2 font-semibold">
                                <div class="w-6 h-6 bg-red-500 rounded-lg shadow-md"></div>
                                <span>Merah</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="par1" id="par1" disabled
                                    class="w-full rounded-lg border-2 border-gray-200 px-3 py-2 text-center font-semibold text-lg focus:border-red-400 focus:ring-4 focus:ring-red-100 disabled:bg-gray-100 disabled:cursor-not-allowed transition-all"
                                    min="0" max="100" autocomplete="off" required placeholder="0">
                                <span
                                    class="suffix hidden absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">%</span>
                            </div>
                        </div>

                        <!-- Yellow Parameter -->
                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border-2 border-gray-200 hover:border-yellow-300 transition-colors">
                            <label for="par2" class="flex items-center gap-2 text-gray-700 mb-2 font-semibold">
                                <div class="w-6 h-6 bg-yellow-400 rounded-lg shadow-md"></div>
                                <span>Kuning</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="par2" id="par2" disabled
                                    class="w-full rounded-lg border-2 border-gray-200 px-3 py-2 text-center font-semibold text-lg focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 disabled:bg-gray-100 disabled:cursor-not-allowed transition-all"
                                    min="0" max="100" autocomplete="off" required placeholder="0">
                                <span
                                    class="suffix hidden absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">%</span>
                            </div>
                        </div>

                        <!-- Light Green Parameter -->
                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border-2 border-gray-200 hover:border-green-300 transition-colors">
                            <label for="par3" class="flex items-center gap-2 text-gray-700 mb-2 font-semibold">
                                <div class="w-6 h-6 bg-green-400 rounded-lg shadow-md"></div>
                                <span>Hijau Muda</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="par3" id="par3" disabled
                                    class="w-full rounded-lg border-2 border-gray-200 px-3 py-2 text-center font-semibold text-lg focus:border-green-400 focus:ring-4 focus:ring-green-100 disabled:bg-gray-100 disabled:cursor-not-allowed transition-all"
                                    min="0" max="100" autocomplete="off" required placeholder="0">
                                <span
                                    class="suffix hidden absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">%</span>
                            </div>
                        </div>

                        <!-- Dark Green Parameter -->
                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border-2 border-gray-200 hover:border-green-600 transition-colors">
                            <label for="par4" class="flex items-center gap-2 text-gray-700 mb-2 font-semibold">
                                <div class="w-6 h-6 bg-green-700 rounded-lg shadow-md"></div>
                                <span>Hijau Tua</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="par4" id="par4" disabled
                                    class="w-full rounded-lg border-2 border-gray-200 px-3 py-2 text-center font-semibold text-lg focus:border-green-600 focus:ring-4 focus:ring-green-100 disabled:bg-gray-100 disabled:cursor-not-allowed transition-all"
                                    min="0" max="100" autocomplete="off" required placeholder="0">
                                <span
                                    class="suffix hidden absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Color Scale Preview -->
                    <div class="mt-4 bg-white rounded-lg p-3 border border-gray-200">
                        <p class="text-xs text-gray-600 mb-2 font-medium">Preview Skala Warna:</p>
                        <div class="flex h-8 rounded-lg overflow-hidden shadow-md">
                            <div class="flex-1 bg-red-500"></div>
                            <div class="flex-1 bg-yellow-400"></div>
                            <div class="flex-1 bg-green-400"></div>
                            <div class="flex-1 bg-green-700"></div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit"
                        class="group relative px-8 py-3 bg-gradient-to-r from-blue-500 to-cyan-600 rounded-xl shadow-lg text-white font-semibold hover:from-blue-600 hover:to-cyan-700 transition-all duration-300 hover:shadow-xl hover:scale-105 overflow-hidden">

                        <!-- Button shimmer effect -->
                        <div
                            class="absolute inset-0 w-1/2 h-full bg-gradient-to-r from-transparent via-white to-transparent opacity-0 group-hover:opacity-30 transform -skew-x-12 group-hover:animate-shimmer">
                        </div>

                        <span class="flex items-center gap-3 relative z-10">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Generate KML File</span>
                            <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Info Section -->
        <div class="mt-8 bg-gradient-to-r from-amber-50 to-orange-50 rounded-xl p-6 border border-amber-200">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-800 mb-2">How to Use</h3>
                    <ul class="text-sm text-gray-700 space-y-2">
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span><strong>Pilih Pengamatan:</strong> Tentukan tipe pengamatan (Agronomi atau HPT)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span><strong>Pilih Variabel:</strong> Pilih parameter yang ingin divisualisasikan</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span><strong>Set Parameter Warna:</strong> Tentukan threshold untuk setiap tingkat
                                warna</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span><strong>Generate:</strong> Klik tombol generate untuk membuat file KML</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <style>
        select:invalid {
            color: #9ca3af;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%) skewX(-12deg);
            }

            100% {
                transform: translateX(300%) skewX(-12deg);
            }
        }

        .group:hover .group-hover\:animate-shimmer {
            animation: shimmer 1.5s infinite;
        }
    </style>

    <script>
        const observeSelect = document.getElementById("observe");
        const variableSelect = document.getElementById("variable");
        const kmlForm = document.getElementById("kmlForm");
        const paramWarning = document.getElementById("param-warning");
        const parInputs = ["par1", "par2", "par3", "par4"].map(id => document.getElementById(id));

        const options = {
            Agronomi: [{
                    value: "per_germinasi",
                    label: "% Germinasi"
                },
                {
                    value: "per_gap",
                    label: "% GAP"
                },
                {
                    value: "populasi",
                    label: "Populasi"
                },
                {
                    value: "per_gulma",
                    label: "% Penutupan Gulma"
                },
                {
                    value: "ph_tanah",
                    label: "pH Tanah"
                }
            ],
            HPT: [{
                    value: "per_ppt",
                    label: "% PPT"
                },
                {
                    value: "per_ppt_aktif",
                    label: "% PPT Aktif"
                },
                {
                    value: "per_pbt",
                    label: "% PBT"
                },
                {
                    value: "per_pbt_aktif",
                    label: "% PBT Aktif"
                },
                {
                    value: "int_rusak",
                    label: "% Intensitas Kerusakan"
                },
                {
                    value: "dh",
                    label: "Dead Heart"
                },
                {
                    value: "dt",
                    label: "Dead Top"
                },
                {
                    value: "kbp",
                    label: "Kutu Bulu Putih"
                },
                {
                    value: "kbb",
                    label: "Kutu Bulu Babi"
                },
                {
                    value: "kp",
                    label: "Kutu Perisai"
                },
                {
                    value: "cabuk",
                    label: "Cabuk"
                },
                {
                    value: "belalang",
                    label: "Belalang"
                },
                {
                    value: "serang_grayak",
                    label: "BTG Terserang Ulat Grayak"
                },
                {
                    value: "jum_grayak",
                    label: "Jumlah Ulat Grayak"
                },
                {
                    value: "serang_smut",
                    label: "BTG Terserang SMUT"
                },
                {
                    value: "jum_larva_ppt",
                    label: "Jumlah Larva PPT"
                },
                {
                    value: "jum_larva_pbt",
                    label: "Jumlah Larva PBT"
                }
            ]
        };

        function togglePercent() {
            const selectedText = variableSelect.options[variableSelect.selectedIndex]?.textContent || "";
            const showPercent = selectedText.includes("%");
            document.querySelectorAll(".suffix").forEach(span => {
                span.classList.toggle("hidden", !showPercent);
            });
        }

        observeSelect.addEventListener("change", function() {
            const selected = this.value;
            variableSelect.innerHTML = "";

            if (options[selected]) {
                variableSelect.disabled = false;
                options[selected].forEach(opt => {
                    const option = document.createElement("option");
                    option.value = opt.value;
                    option.textContent = opt.label;
                    variableSelect.appendChild(option);
                });
                parInputs.forEach(inp => inp.disabled = false);
                paramWarning.classList.add("hidden");
            } else {
                variableSelect.disabled = true;
                const option = document.createElement("option");
                option.value = "";
                option.textContent = "-- Pilih pengamatan dulu --";
                variableSelect.appendChild(option);
                parInputs.forEach(inp => inp.disabled = true);
                paramWarning.classList.remove("hidden");
            }
            togglePercent();
        });

        variableSelect.addEventListener("change", togglePercent);

        window.addEventListener("DOMContentLoaded", () => {
            togglePercent();
            if (!observeSelect.value) {
                parInputs.forEach(inp => inp.disabled = true);
                paramWarning.classList.remove("hidden");
            }
        });

        kmlForm.addEventListener("submit", function(e) {
            const selectedText = variableSelect.options[variableSelect.selectedIndex]?.textContent || "";
            const isPercent = selectedText.includes("%");

            if (isPercent) {
                // Create hidden inputs untuk nilai yang sudah dikonversi
                parInputs.forEach(input => {
                    if (input.value) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = input.name;
                        hiddenInput.value = parseFloat(input.value) / 100;
                        kmlForm.appendChild(hiddenInput);

                        // Disable input asli agar tidak terkirim
                        input.disabled = true;
                    }
                });
            }
        });

        // Limit input length to 3 digits
        document.querySelectorAll('input[type="number"]').forEach(function(input) {
            input.addEventListener('input', function() {
                if (this.value.length > 3) {
                    this.value = this.value.slice(0, 3);
                }
            });
        });
    </script>
</x-layout>
