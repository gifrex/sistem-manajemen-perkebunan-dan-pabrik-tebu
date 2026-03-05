{{-- resources/views/approval/upahmingguan/detail.blade.php --}}
{{-- Modal partial: di-include dari approval/index.blade.php --}}

<div x-show="upahModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
    @keydown.escape.window="upahModal.open = false">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="upahModal.open = false"></div>

    {{-- Panel --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100">

        {{-- Modal header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Detail Approval Upah Mingguan</h2>
                <p class="text-xs text-slate-400 mt-0.5" x-text="upahModal.transno"></p>
            </div>
            <button @click="upahModal.open = false"
                class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Loading state --}}
        <div x-show="upahModal.loading" class="flex items-center justify-center py-16">
            <svg class="w-8 h-8 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>

        {{-- Error state --}}
        <div x-show="!upahModal.loading && upahModal.error" class="px-6 py-8 text-center text-sm text-red-600">
            <p x-text="upahModal.error"></p>
        </div>

        {{-- Content --}}
        <div x-show="!upahModal.loading && !upahModal.error && upahModal.data" class="overflow-y-auto flex-1">
            <template x-if="upahModal.data">
                <div class="px-6 py-5 space-y-5">

                    {{-- Info header --}}
                    <div
                        class="bg-slate-50 rounded-xl border border-slate-200 p-4 grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                        <div>
                            <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">Jenis TK</p>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded border"
                                :class="upahModal.data.header?.jenistenagakerja == 1 ?
                                    'bg-blue-50 text-blue-600 border-blue-200' :
                                    'bg-orange-50 text-orange-600 border-orange-200'"
                                x-text="upahModal.data.header?.jenistenagakerja == 1 ? 'Harian' : 'Borongan'">
                            </span>
                        </div>
                        <div>
                            <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">Generate
                                Date</p>
                            <p class="font-medium text-slate-800 text-xs"
                                x-text="upahModal.data.header?.generatedate
                                    ? new Date(upahModal.data.header.generatedate).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'})
                                    : '-'">
                            </p>
                        </div>
                        <div>
                            <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">Periode</p>
                            <p class="font-medium text-slate-800 text-xs">
                                <span
                                    x-text="upahModal.data.header?.startdate
                                    ? new Date(upahModal.data.header.startdate).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'})
                                    : '-'">
                                </span>
                                <span class="text-slate-400"> s/d </span>
                                <span
                                    x-text="upahModal.data.header?.enddate
                                    ? new Date(upahModal.data.header.enddate).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'})
                                    : '-'">
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">Grand Total
                            </p>
                            <p class="font-bold text-emerald-700 text-xs"
                                x-text="new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', minimumFractionDigits:0}).format(upahModal.data.header?.grandtotal ?? 0)">
                            </p>
                        </div>
                    </div>

                    {{-- Status badge --}}
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold px-3 py-1 rounded-full border"
                            :class="{
                                'bg-emerald-50 text-emerald-700 border-emerald-200': upahModal.data.status
                                    ?.status === 'approved',
                                'bg-red-50 text-red-700 border-red-200': upahModal.data.status?.status === 'declined',
                                'bg-amber-50 text-amber-700 border-amber-200': upahModal.data.status
                                    ?.status === 'waiting',
                                'bg-slate-50 text-slate-600 border-slate-200': !['approved', 'declined', 'waiting']
                                    .includes(upahModal.data.status?.status)
                            }"
                            x-text="(upahModal.data.status?.message ?? '').toUpperCase()">
                        </span>
                    </div>

                    {{-- Approval history --}}
                    <template x-if="upahModal.data.history && upahModal.data.history.jumlahapproval">
                        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <h3 class="text-sm font-semibold text-slate-800">Riwayat Approval</h3>
                            </div>
                            <div class="divide-y divide-slate-100">
                                <template x-for="lvl in upahModal.data.history.levels" :key="lvl.level">
                                    <div class="px-4 py-3 flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500 flex-shrink-0"
                                                x-text="lvl.level"></div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-800" x-text="lvl.jabatan"></p>
                                                <p class="text-xs text-slate-400 mt-0.5">
                                                    <span x-show="lvl.user" x-text="lvl.user"></span>
                                                    <span x-show="lvl.user && lvl.date"> &middot; </span>
                                                    <span x-show="lvl.date"
                                                        x-text="lvl.date ? new Date(lvl.date).toLocaleString('id-ID', {day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) : ''">
                                                    </span>
                                                    <span x-show="!lvl.user">Menunggu tindakan</span>
                                                </p>
                                            </div>
                                        </div>
                                        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full border"
                                            :class="{
                                                'bg-emerald-50 text-emerald-700 border-emerald-200': lvl
                                                    .flag === '1',
                                                'bg-red-50 text-red-700 border-red-200': lvl.flag === '0',
                                                'bg-amber-50 text-amber-600 border-amber-200': lvl.flag === null || lvl
                                                    .flag === undefined
                                            }"
                                            x-text="lvl.flag === '1' ? 'Approved' : (lvl.flag === '0' ? 'Declined' : 'Pending')">
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Worker list --}}
                    <template x-if="upahModal.data.workers && upahModal.data.workers.length > 0">
                        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-slate-800">Daftar Tenaga Kerja</h3>
                                <span class="text-xs text-slate-500"
                                    x-text="upahModal.data.workers.length + ' orang'"></span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-slate-50 border-b border-slate-100">
                                            <th
                                                class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                                No</th>
                                            <th
                                                class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                                ID TK</th>
                                            <th
                                                class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                                Nama</th>
                                            <th
                                                class="px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                                Upah</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="(w, i) in upahModal.data.workers" :key="w.tenagakerjaid">
                                            <tr class="hover:bg-slate-50/50">
                                                <td class="px-4 py-2.5 text-slate-400 text-xs" x-text="i + 1"></td>
                                                <td class="px-4 py-2.5 text-slate-600 font-mono text-xs"
                                                    x-text="w.tenagakerjaid"></td>
                                                <td class="px-4 py-2.5 font-medium text-slate-800 text-sm"
                                                    x-text="w.worker_name"></td>
                                                <td class="px-4 py-2.5 text-right font-medium text-slate-800 text-xs"
                                                    x-text="w.totalupah_fmt"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>

                </div>
            </template>
        </div>

    </div>
</div>
