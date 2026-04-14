{{-- resources\views\transaction\nfc\index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div x-data="nfcData()" class="space-y-4">

        {{-- ── MAIN CARD ─────────────────────────────────────────────────── --}}
        <div class="bg-white border border-gray-200 rounded-sm">

            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h1 class="text-base font-semibold text-gray-900">NFC Card Management</h1>
                        <p class="text-xs text-gray-500 mt-0.5">Kelola distribusi kartu NFC ke mandor & POS</p>
                    </div>

                    {{-- Action dropdown --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.outside="open = false"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-normal text-white bg-[#0073bb] rounded-sm hover:bg-[#005f99] border border-[#005f99] transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Transaksi Baru
                            <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-75"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-50"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="absolute right-0 mt-1 w-52 bg-white rounded-sm shadow-md border border-gray-200 py-1 z-30">

                            <div class="px-3 py-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Mandor</div>
                            <button @click="showOutModal = true; open = false"
                                    class="w-full flex items-center gap-2.5 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"></span>
                                Out ke Mandor
                            </button>
                            <button @click="showInModal = true; open = false"
                                    class="w-full flex items-center gap-2.5 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"></span>
                                In dari Mandor
                            </button>

                            <div class="my-1 border-t border-gray-100"></div>
                            <div class="px-3 py-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">POS</div>
                            <button @click="showPosInModal = true; open = false"
                                    class="w-full flex items-center gap-2.5 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"></span>
                                Return dari POS
                            </button>

                            <div class="my-1 border-t border-gray-100"></div>
                            <div class="px-3 py-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Warehouse</div>
                            <button @click="showExternalInModal = true; open = false"
                                    class="w-full flex items-center gap-2.5 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"></span>
                                Stock In
                            </button>
                            <button @click="showExternalOutModal = true; open = false"
                                    class="w-full flex items-center gap-2.5 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"></span>
                                Stock Out
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary Stats --}}
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

                    <div class="border border-gray-200 rounded-sm p-4 bg-white">
                        <div class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1.5">Total Kartu</div>
                        <div class="text-2xl font-semibold text-gray-900 tabular-nums">{{ $totalKartu }}</div>
                        <div class="text-xs text-gray-400 mt-1">total kartu perusahaan</div>
                    </div>

                    <div class="border border-gray-200 rounded-sm p-4 bg-white">
                        <div class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1.5">Kantor</div>
                        <div class="text-2xl font-semibold tabular-nums {{ $kantorBalance < 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $kantorBalance }}</div>
                        <div class="text-xs text-gray-400 mt-1">kartu di warehouse</div>
                    </div>

                    <div class="border border-gray-200 rounded-sm p-4 bg-white">
                        <div class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1.5">Di Mandor</div>
                        <div class="text-2xl font-semibold tabular-nums {{ $totalInHand < 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $totalInHand }}</div>
                        <div class="text-xs text-gray-400 mt-1">kartu di tangan mandor</div>
                    </div>

                    <div class="border border-gray-200 rounded-sm p-4 bg-white">
                        <div class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1.5">Di POS</div>
                        <div class="text-2xl font-semibold text-gray-900 tabular-nums">{{ $totalAtPos }}</div>
                        <div class="text-xs text-gray-400 mt-1">kartu di lapangan</div>
                    </div>

                </div>
            </div>

            {{-- Mandor Balance Table --}}
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Posisi Kartu per Mandor</div>
                <div class="overflow-x-auto border border-gray-200 rounded-sm">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-8">#</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mandor</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Di Tangan</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Di POS</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($mandorBalances as $i => $mandor)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-2.5 text-xs text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="font-medium text-gray-800">{{ $mandor->mandorname ?? $mandor->mandorid }}</span>
                                    <span class="ml-1.5 text-xs text-gray-400">{{ $mandor->mandorid }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-right tabular-nums">
                                    @if($mandor->in_hand > 0)
                                        <span class="font-medium text-gray-800">{{ $mandor->in_hand }}</span>
                                    @elseif($mandor->in_hand < 0)
                                        <span class="font-medium text-red-600">{{ $mandor->in_hand }}</span>
                                    @else
                                        <span class="text-gray-300">0</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-right tabular-nums">
                                    @if($mandor->cards_at_pos > 0)
                                        <span class="font-medium text-gray-600">{{ $mandor->cards_at_pos }}</span>
                                    @else
                                        <span class="text-gray-300">0</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-right tabular-nums">
                                    @if($mandor->balance > 0)
                                        <span class="font-semibold text-gray-900">{{ $mandor->balance }}</span>
                                    @elseif($mandor->balance < 0)
                                        <span class="font-semibold text-red-600">{{ $mandor->balance }}</span>
                                    @else
                                        <span class="text-gray-300">0</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">Belum ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($mandorBalances->count() > 0)
                        <tfoot>
                            <tr class="bg-gray-50 border-t border-gray-200">
                                <td colspan="2" class="px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</td>
                                <td class="px-4 py-2.5 text-right text-sm font-semibold tabular-nums {{ $totalInHand < 0 ? 'text-red-600' : 'text-gray-800' }}">{{ $totalInHand }}</td>
                                <td class="px-4 py-2.5 text-right text-sm font-semibold tabular-nums text-gray-600">{{ $totalAtPos }}</td>
                                <td class="px-4 py-2.5 text-right text-sm font-bold tabular-nums text-gray-900">{{ $totalMandor }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Transaction History --}}
            <div class="px-6 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-3">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide shrink-0">Riwayat Transaksi</div>
                    <form method="GET" action="{{ request()->url() }}" class="flex flex-wrap gap-1.5 items-center sm:ml-auto">
                        <input type="date" name="date_from" value="{{ $filterDateFrom }}"
                               class="text-sm border border-gray-300 rounded-sm px-2.5 py-1.5 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                        <input type="date" name="date_to" value="{{ $filterDateTo }}"
                               class="text-sm border border-gray-300 rounded-sm px-2.5 py-1.5 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                        <select name="mandorid" class="text-sm border border-gray-300 rounded-sm px-2.5 py-1.5 min-w-[140px] focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                            <option value="">Semua Mandor</option>
                            <option value="EXTERNAL" {{ $filterMandor === 'EXTERNAL' ? 'selected' : '' }}>External</option>
                            @foreach($mandorList as $m)
                            <option value="{{ $m->userid }}" {{ $filterMandor === $m->userid ? 'selected' : '' }}>
                                {{ $m->userid }} – {{ $m->name }}
                            </option>
                            @endforeach
                        </select>
                        <button type="submit"
                                class="bg-[#0073bb] hover:bg-[#005f99] border border-[#005f99] text-white px-3 py-1.5 text-sm rounded-sm transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Filter
                        </button>
                        @if($filterDateFrom || $filterDateTo || $filterMandor)
                        <a href="{{ request()->url() }}"
                           class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-sm hover:bg-gray-50 transition">
                            Reset
                        </a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto border border-gray-200 rounded-sm">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">No. Transaksi</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Tanggal</th>
                                <th class="px-3 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Flow</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mandor</th>
                                <th class="px-3 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Qty</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider min-w-[200px]">Perubahan Saldo</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Keterangan</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Input</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($recentTransactions as $tx)
                            <tr class="hover:bg-gray-50 transition-colors">

                                <td class="px-3 py-2.5 font-mono text-xs text-gray-500 whitespace-nowrap">{{ $tx->transactionno }}</td>

                                <td class="px-3 py-2.5 text-xs text-gray-600 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($tx->transactiondate)->format('d M Y') }}
                                </td>

                                <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                    @if($tx->from_wallet && $tx->to_wallet)
                                        <span class="inline-flex items-center gap-1 text-xs">
                                            <span class="px-1.5 py-0.5 rounded-sm font-medium text-[11px] bg-gray-100 text-gray-600 border border-gray-200">{{ $tx->from_wallet }}</span>
                                            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            <span class="px-1.5 py-0.5 rounded-sm font-medium text-[11px] bg-gray-100 text-gray-600 border border-gray-200">{{ $tx->to_wallet }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-[11px] px-1.5 py-0.5 bg-gray-100 text-gray-600 border border-gray-200 rounded-sm font-medium">
                                            {{ $tx->transactiontype }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-3 py-2.5">
                                    @if($tx->mandorid === 'EXTERNAL')
                                        <span class="text-xs text-gray-400 italic">External</span>
                                    @else
                                        <span class="text-sm text-gray-800">{{ $tx->mandorname ?? $tx->mandorid }}</span>
                                        <span class="ml-1 text-xs text-gray-400">{{ $tx->mandorid }}</span>
                                        @if($tx->source === 'POS')
                                            <span class="ml-1 text-[10px] bg-gray-100 text-gray-500 border border-gray-200 rounded-sm px-1 py-0.5 font-medium">POS</span>
                                        @endif
                                    @endif
                                </td>

                                <td class="px-3 py-2.5 text-right font-semibold tabular-nums {{ $tx->transactiontype === 'OUT' ? 'text-red-600' : 'text-gray-800' }}">
                                    {{ $tx->transactiontype === 'OUT' ? '−' : '+' }}{{ $tx->qty }}
                                </td>

                                <td class="px-3 py-2.5">
                                    @if($tx->from_balance_before !== null || $tx->to_balance_before !== null)
                                        <div class="space-y-1">
                                            @if($tx->from_balance_before !== null)
                                                <div class="flex items-center gap-1.5 text-xs">
                                                    <span class="inline-block px-1 py-0.5 rounded-sm text-[10px] font-semibold bg-gray-100 text-gray-500 border border-gray-200 shrink-0 leading-none">{{ $tx->from_wallet }}</span>
                                                    <span class="font-mono text-gray-400 tabular-nums">{{ $tx->from_balance_before }}</span>
                                                    <svg class="w-2.5 h-2.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                    <span class="font-mono font-semibold tabular-nums {{ $tx->from_balance_after < $tx->from_balance_before ? 'text-red-600' : 'text-gray-800' }}">{{ $tx->from_balance_after }}</span>
                                                </div>
                                            @endif
                                            @if($tx->to_balance_before !== null)
                                                <div class="flex items-center gap-1.5 text-xs">
                                                    <span class="inline-block px-1 py-0.5 rounded-sm text-[10px] font-semibold bg-gray-100 text-gray-500 border border-gray-200 shrink-0 leading-none">{{ $tx->to_wallet }}</span>
                                                    <span class="font-mono text-gray-400 tabular-nums">{{ $tx->to_balance_before }}</span>
                                                    <svg class="w-2.5 h-2.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                    <span class="font-mono font-semibold tabular-nums {{ $tx->to_balance_after > $tx->to_balance_before ? 'text-gray-800' : 'text-red-600' }}">{{ $tx->to_balance_after }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2.5 text-xs text-gray-500 max-w-[150px] truncate" title="{{ $tx->notes }}">
                                    {{ $tx->notes ?? '—' }}
                                </td>

                                <td class="px-3 py-2.5 whitespace-nowrap">
                                    <div class="text-xs text-gray-700">{{ $tx->inputby }}</div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($tx->createdat)->format('d/m H:i') }}</div>
                                </td>

                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center">
                                    <p class="text-sm text-gray-400">Tidak ada transaksi ditemukan</p>
                                    @if($filterDateFrom || $filterDateTo || $filterMandor)
                                        <a href="{{ request()->url() }}" class="text-xs text-[#0073bb] hover:underline mt-1 inline-block">Reset filter</a>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ═══ MODALS ══════════════════════════════════════════════════════ --}}

        {{-- ── Out ke Mandor (KANTOR → MANDOR) ── --}}
        <template x-teleport="body">
            <div x-show="showOutModal" x-cloak
                 class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4"
                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="bg-white rounded-sm shadow-xl w-full max-w-md border border-gray-200" @click.outside="showOutModal = false">
                    <div class="px-5 py-3.5 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Out ke Mandor</div>
                            <div class="text-xs text-gray-500 mt-0.5">KANTOR → MANDOR</div>
                        </div>
                        <button @click="showOutModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-sm hover:bg-gray-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="processOut" class="px-5 py-4 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                                <input type="date" x-model="outForm.transactiondate" required
                                       class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Qty <span class="text-red-500">*</span></label>
                                <input type="number" x-model="outForm.qty" min="1" required
                                       class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Mandor <span class="text-red-500">*</span></label>
                            <select x-model="outForm.mandorid" required
                                    class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                                <option value="">Pilih Mandor</option>
                                @foreach($mandorList as $m)
                                <option value="{{ $m->userid }}">{{ $m->userid }} – {{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan</label>
                            <input type="text" x-model="outForm.notes" placeholder="Opsional"
                                   class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                        </div>
                        <div class="flex gap-2 pt-1">
                            <button type="button" @click="showOutModal = false"
                                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-600 rounded-sm hover:bg-gray-50 text-sm transition">Batal</button>
                            <button type="submit" :disabled="isProcessing"
                                    class="flex-1 px-4 py-2 text-white rounded-sm transition disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium bg-[#0073bb] hover:bg-[#005f99] border border-[#005f99]">
                                <template x-if="!isProcessing"><span>Proses OUT</span></template>
                                <template x-if="isProcessing">
                                    <span class="flex items-center justify-center gap-1.5">
                                        <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                        Memproses...
                                    </span>
                                </template>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- ── In dari Mandor (MANDOR → KANTOR) ── --}}
        <template x-teleport="body">
            <div x-show="showInModal" x-cloak
                 class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4"
                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="bg-white rounded-sm shadow-xl w-full max-w-md border border-gray-200" @click.outside="showInModal = false">
                    <div class="px-5 py-3.5 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">In dari Mandor</div>
                            <div class="text-xs text-gray-500 mt-0.5">MANDOR → KANTOR · kartu sisa</div>
                        </div>
                        <button @click="showInModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-sm hover:bg-gray-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="processIn" class="px-5 py-4 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                                <input type="date" x-model="inForm.transactiondate" required
                                       class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Qty <span class="text-red-500">*</span></label>
                                <input type="number" x-model="inForm.qty" min="1" required
                                       class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Mandor <span class="text-red-500">*</span></label>
                            <select x-model="inForm.mandorid" required
                                    class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                                <option value="">Pilih Mandor</option>
                                @foreach($mandorList as $m)
                                <option value="{{ $m->userid }}">{{ $m->userid }} – {{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan</label>
                            <input type="text" x-model="inForm.notes" placeholder="Opsional"
                                   class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                        </div>
                        <div class="flex gap-2 pt-1">
                            <button type="button" @click="showInModal = false"
                                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-600 rounded-sm hover:bg-gray-50 text-sm transition">Batal</button>
                            <button type="submit" :disabled="isProcessing"
                                    class="flex-1 px-4 py-2 text-white rounded-sm transition disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium bg-[#0073bb] hover:bg-[#005f99] border border-[#005f99]">
                                <template x-if="!isProcessing"><span>Proses IN</span></template>
                                <template x-if="isProcessing">
                                    <span class="flex items-center justify-center gap-1.5">
                                        <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                        Memproses...
                                    </span>
                                </template>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- ── Return dari POS (POS → KANTOR) ── --}}
        <template x-teleport="body">
            <div x-show="showPosInModal" x-cloak
                 class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4"
                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="bg-white rounded-sm shadow-xl w-full max-w-md border border-gray-200" @click.outside="showPosInModal = false">
                    <div class="px-5 py-3.5 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Return dari POS</div>
                            <div class="text-xs text-gray-500 mt-0.5">POS → KANTOR · wajib pilih mandor</div>
                        </div>
                        <button @click="showPosInModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-sm hover:bg-gray-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="processPosIn" class="px-5 py-4 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                                <input type="date" x-model="posInForm.transactiondate" required
                                       class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Qty <span class="text-red-500">*</span></label>
                                <input type="number" x-model="posInForm.qty" min="1" required
                                       class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Mandor <span class="text-red-500">*</span></label>
                            <select x-model="posInForm.mandorid" required
                                    class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                                <option value="">Pilih Mandor</option>
                                @foreach($mandorList as $m)
                                <option value="{{ $m->userid }}">{{ $m->userid }} – {{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan</label>
                            <input type="text" x-model="posInForm.notes" placeholder="POS Return"
                                   class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                        </div>
                        <div class="flex gap-2 pt-1">
                            <button type="button" @click="showPosInModal = false"
                                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-600 rounded-sm hover:bg-gray-50 text-sm transition">Batal</button>
                            <button type="submit" :disabled="isProcessing"
                                    class="flex-1 px-4 py-2 text-white rounded-sm transition disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium bg-[#0073bb] hover:bg-[#005f99] border border-[#005f99]">
                                <template x-if="!isProcessing"><span>Proses Return</span></template>
                                <template x-if="isProcessing">
                                    <span class="flex items-center justify-center gap-1.5">
                                        <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                        Memproses...
                                    </span>
                                </template>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- ── Stock In (EXTERNAL → KANTOR) ── --}}
        <template x-teleport="body">
            <div x-show="showExternalInModal" x-cloak
                 class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4"
                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="bg-white rounded-sm shadow-xl w-full max-w-md border border-gray-200" @click.outside="showExternalInModal = false">
                    <div class="px-5 py-3.5 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Stock In</div>
                            <div class="text-xs text-gray-500 mt-0.5">EXTERNAL → KANTOR</div>
                        </div>
                        <button @click="showExternalInModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-sm hover:bg-gray-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="processExternalIn" class="px-5 py-4 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                                <input type="date" x-model="externalInForm.transactiondate" required
                                       class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Qty <span class="text-red-500">*</span></label>
                                <input type="number" x-model="externalInForm.qty" min="1" required
                                       class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan / Sumber <span class="text-red-500">*</span></label>
                            <input type="text" x-model="externalInForm.notes" required placeholder="cth: Pembelian dari supplier XYZ"
                                   class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                        </div>
                        <div class="flex gap-2 pt-1">
                            <button type="button" @click="showExternalInModal = false"
                                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-600 rounded-sm hover:bg-gray-50 text-sm transition">Batal</button>
                            <button type="submit" :disabled="isProcessing"
                                    class="flex-1 px-4 py-2 text-white rounded-sm transition disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium bg-[#0073bb] hover:bg-[#005f99] border border-[#005f99]">
                                <template x-if="!isProcessing"><span>Tambah Stock</span></template>
                                <template x-if="isProcessing">
                                    <span class="flex items-center justify-center gap-1.5">
                                        <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                        Memproses...
                                    </span>
                                </template>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- ── Stock Out (KANTOR → EXTERNAL) ── --}}
        <template x-teleport="body">
            <div x-show="showExternalOutModal" x-cloak
                 class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4"
                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="bg-white rounded-sm shadow-xl w-full max-w-md border border-gray-200" @click.outside="showExternalOutModal = false">
                    <div class="px-5 py-3.5 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Stock Out</div>
                            <div class="text-xs text-gray-500 mt-0.5">KANTOR → EXTERNAL · rusak / hilang</div>
                        </div>
                        <button @click="showExternalOutModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-sm hover:bg-gray-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="processExternalOut" class="px-5 py-4 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                                <input type="date" x-model="externalOutForm.transactiondate" required
                                       class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Qty <span class="text-red-500">*</span></label>
                                <input type="number" x-model="externalOutForm.qty" min="1" required
                                       class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Alasan <span class="text-red-500">*</span></label>
                            <select x-model="externalOutForm.reason" required
                                    class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                                <option value="">Pilih alasan</option>
                                <option value="DAMAGED">Rusak</option>
                                <option value="LOST">Hilang</option>
                                <option value="DISPOSAL">Disposal</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan <span class="text-red-500">*</span></label>
                            <input type="text" x-model="externalOutForm.notes" required
                                   class="w-full text-sm border border-gray-300 rounded-sm px-2.5 py-2 focus:ring-1 focus:ring-[#0073bb] focus:border-[#0073bb] outline-none">
                        </div>
                        <div class="flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-sm px-3 py-2.5 text-xs text-amber-800">
                            <svg class="w-3.5 h-3.5 mt-0.5 shrink-0 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.168 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                            <span>Tindakan ini mengurangi stok warehouse secara permanen dan tidak dapat dibatalkan.</span>
                        </div>
                        <div class="flex gap-2 pt-1">
                            <button type="button" @click="showExternalOutModal = false"
                                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-600 rounded-sm hover:bg-gray-50 text-sm transition">Batal</button>
                            <button type="submit" :disabled="isProcessing"
                                    class="flex-1 px-4 py-2 text-white rounded-sm transition disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium bg-red-600 hover:bg-red-700 border border-red-700">
                                <template x-if="!isProcessing"><span>Kurangi Stock</span></template>
                                <template x-if="isProcessing">
                                    <span class="flex items-center justify-center gap-1.5">
                                        <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                        Memproses...
                                    </span>
                                </template>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

    </div>{{-- end x-data --}}

    <script>
    function nfcData() {
        const today = new Date().toISOString().split('T')[0];
        return {
            showOutModal: false, showInModal: false, showPosInModal: false,
            showExternalInModal: false, showExternalOutModal: false,
            isProcessing: false,

            outForm:         { mandorid: '', qty: '', transactiondate: today, notes: '' },
            inForm:          { mandorid: '', qty: '', transactiondate: today, notes: '' },
            posInForm:       { mandorid: '', qty: '', transactiondate: today, notes: '' },
            externalInForm:  { qty: '',              transactiondate: today, notes: '' },
            externalOutForm: { reason: '', qty: '',  transactiondate: today, notes: '' },

            async _post(url, payload, msg) {
                if (!confirm(msg)) return;
                this.isProcessing = true;
                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (e) {
                    alert('Gagal terhubung ke server');
                } finally {
                    this.isProcessing = false;
                }
            },

            processOut()         { this._post('{{ route("transaction.nfc.transaction-out") }}', this.outForm,         'Konfirmasi OUT ke mandor?'); },
            processIn()          { this._post('{{ route("transaction.nfc.transaction-in") }}',  this.inForm,          'Konfirmasi IN dari mandor?'); },
            processPosIn()       { this._post('{{ route("transaction.nfc.pos-in") }}',          this.posInForm,       'Konfirmasi return dari POS?'); },
            processExternalIn()  { this._post('{{ route("transaction.nfc.external-in") }}',     this.externalInForm,  'Konfirmasi penambahan stok?'); },
            processExternalOut() { this._post('{{ route("transaction.nfc.external-out") }}',    this.externalOutForm, 'PERHATIAN: Konfirmasi pengurangan stok permanen?'); },
        };
    }
    </script>
</x-layout>