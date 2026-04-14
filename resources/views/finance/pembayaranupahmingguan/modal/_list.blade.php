<div id="listModal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm p-4 invisible opacity-0 transition-all duration-300">
    <div
        class="bg-white w-11/12 max-w-7xl max-h-[90vh] flex flex-col rounded-2xl shadow-2xl transform scale-95 transition-transform duration-300">

        <!-- Modal Header -->
        <div
            class="flex items-center justify-between p-6 border-b bg-gradient-to-r from-indigo-50 to-purple-50 rounded-t-2xl flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                        <path fill-rule="evenodd"
                            d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Detail Pembayaran</h2>
                    <p class="text-xs text-indigo-500 font-mono" id="modal-transno"></p>
                </div>
            </div>

            <!-- Status badge: tampil saat loading -->
            <div id="modal-loading-badge"
                class="hidden items-center gap-2 px-3 py-1.5 bg-indigo-50 border border-indigo-200 rounded-full text-xs font-semibold text-indigo-600">
                <svg class="w-3.5 h-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                Memuat data...
            </div>

            <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg transition-all duration-200">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="overflow-auto flex-1 p-6">

            {{-- Info Cards (Harian only) --}}
            @if (session('tenagakerjarum') == 'Harian')
                <div class="mb-4 grid grid-cols-3 gap-3" id="modal-info-cards">
                    <!-- Skeleton info cards (tampil saat loading) -->
                    <div id="modal-info-skeleton" class="col-span-3 grid grid-cols-3 gap-3">
                        @for ($i = 0; $i < 3; $i++)
                            <div class="bg-gray-100 border border-gray-200 rounded-lg px-4 py-3 animate-pulse">
                                <div class="h-3 bg-gray-300 rounded w-16 mb-2"></div>
                                <div class="h-4 bg-gray-300 rounded w-24"></div>
                            </div>
                        @endfor
                    </div>
                    <!-- Real info cards (tampil setelah data load) -->
                    <div id="modal-info-real" class="col-span-3 grid-cols-3 gap-3" style="display:none">
                        <div class="bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-3">
                            <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wider mb-1">Plot</p>
                            <p class="text-sm font-medium text-gray-800" id="modal-plot">-</p>
                        </div>
                        <div class="bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-3">
                            <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wider mb-1">Luasan
                                (Ha)</p>
                            <p class="text-sm font-medium text-gray-800" id="modal-luasan">-</p>
                        </div>
                        <div class="bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-3">
                            <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wider mb-1">Hasil
                                (Ha)</p>
                            <p class="text-sm font-medium text-gray-800" id="modal-hasil">-</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Info Kegiatan -->
            <div id="modal-activity-info" class="mb-4 hidden">
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-2.5 flex items-center gap-3">
                    <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                            clip-rule="evenodd" />
                    </svg>
                    <div class="flex gap-4 text-xs flex-wrap">
                        <span><span class="text-indigo-500 font-semibold">Kegiatan:</span> <span id="modal-activityname"
                                class="text-gray-800 font-medium">-</span></span>
                        <span><span class="text-indigo-500 font-semibold">Mandor:</span> <span id="modal-mandorname"
                                class="text-gray-800 font-medium">-</span></span>
                        <span><span class="text-indigo-500 font-semibold">Periode:</span> <span id="modal-periode"
                                class="text-gray-800 font-medium">-</span></span>
                    </div>
                </div>
            </div>

            <!-- Table wrapper -->
            <div class="rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-100 to-gray-200 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                No.</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Tanggal</th>
                            @if (session('tenagakerjarum') == 'Harian')
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Tenaga Kerja</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Biaya / Hari (Rp)</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Total (Rp)</th>
                            @else
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Plot</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Upah / Ha (Rp)</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Luasan (Ha)</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Hasil (Ha)</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Total (Rp)</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="listTableBody" class="bg-white divide-y divide-gray-200">
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
