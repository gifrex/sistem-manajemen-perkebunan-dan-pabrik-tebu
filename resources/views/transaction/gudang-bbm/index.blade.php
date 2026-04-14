{{-- resources/views/transaction/gudang-bbm/index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

<div x-data="gudangBbmIndex()" class="mx-auto bg-white rounded-lg shadow-sm border border-gray-200">

    {{-- Header --}}
    <div class="px-6 py-4 border-b border-gray-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Gudang BBM — Konfirmasi Pengeluaran</h1>
                <p class="text-xs text-gray-500 mt-0.5">Input solar real, submit ke approval, lalu sync ke Citrix</p>
            </div>
            <form class="flex flex-wrap items-end gap-2" action="{{ route('transaction.gudang-bbm.index') }}" method="GET">
                <div>
                    <label class="block text-xs text-gray-500 mb-1 font-medium">Pencarian</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Order no..."
                           class="text-sm border border-gray-300 rounded-md px-3 py-2 w-40 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1 font-medium">Tanggal</label>
                    <input type="date" name="filter_date" value="{{ $filterDate ?? '' }}"
                           id="gd_filter_date" {{ ($showAll ?? false) ? 'disabled' : '' }}
                           class="text-sm border border-gray-300 rounded-md px-3 py-2 disabled:bg-gray-100 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
                </div>
                <div class="pb-0.5">
                    <label class="flex items-center text-sm gap-1.5 cursor-pointer bg-white border border-gray-300 rounded-md px-3 py-2">
                        <input type="checkbox" name="show_all" value="1"
                               onchange="document.getElementById('gd_filter_date').disabled=this.checked; this.form.submit();"
                               {{ ($showAll ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"/>
                        <span class="whitespace-nowrap text-xs font-medium text-gray-600">Semua Tanggal</span>
                    </label>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded-md font-medium transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Cari
                </button>
            </form>
        </div>
    </div>

    {{-- Stats --}}
    <div class="px-6 pt-5">
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-5">
            <div class="rounded-lg p-3 bg-white border border-orange-200">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 rounded-md bg-orange-50 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <span class="text-xs text-gray-500 font-medium">Perlu Input</span>
                </div>
                <div class="text-2xl font-bold text-orange-600 pl-1">{{ $stats['pending_input'] ?? 0 }}</div>
            </div>
            <div class="rounded-lg p-3 bg-white border border-yellow-200">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 rounded-md bg-yellow-50 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-xs text-gray-500 font-medium">Pending Approval</span>
                </div>
                <div class="text-2xl font-bold text-yellow-600 pl-1">{{ $stats['pending_approval'] ?? 0 }}</div>
            </div>
            <div class="rounded-lg p-3 bg-white border border-green-200">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 rounded-md bg-green-50 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-xs text-gray-500 font-medium">Approved</span>
                </div>
                <div class="text-2xl font-bold text-green-600 pl-1">{{ $stats['approved'] ?? 0 }}</div>
            </div>
            <div class="rounded-lg p-3 bg-white border border-red-200">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 rounded-md bg-red-50 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-xs text-gray-500 font-medium">Rejected</span>
                </div>
                <div class="text-2xl font-bold text-red-500 pl-1">{{ $stats['rejected'] ?? 0 }}</div>
            </div>
            <div class="rounded-lg p-3 bg-white border border-blue-200">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 rounded-md bg-blue-50 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <span class="text-xs text-gray-500 font-medium">Solar Diminta</span>
                </div>
                <div class="text-xl font-bold text-blue-600 pl-1">{{ number_format($stats['total_solar_requested'] ?? 0, 1) }} <span class="text-sm font-medium">L</span></div>
            </div>
            <div class="rounded-lg p-3 bg-white border border-purple-200">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 rounded-md bg-purple-50 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-xs text-gray-500 font-medium">Solar Real</span>
                </div>
                <div class="text-xl font-bold text-purple-600 pl-1">{{ number_format($stats['total_solar_real'] ?? 0, 1) }} <span class="text-sm font-medium">L</span></div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="px-6 pb-6">
        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Order No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Source</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kendaraan</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Solar Diminta</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Solar Real</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($bbmData as $item)
                    @php
                        // Determine status: pending_input, pending_approval, approved, rejected
                        if ($item->gudangconfirm == 0) {
                            $statusKey = 'pending_input';
                        } elseif ($item->gudangapprovalstatus === '1') {
                            $statusKey = 'approved';
                        } elseif ($item->gudangapprovalstatus === '0') {
                            $statusKey = 'rejected';
                        } else {
                            $statusKey = 'pending_approval';
                        }
                    @endphp
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('transaction.gudang-bbm.show', $item->orderno) }}"
                               class="text-sm font-mono font-bold text-blue-700 hover:text-blue-900 hover:underline">#{{ $item->orderno }}</a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ \Carbon\Carbon::parse($item->orderdate)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs px-2 py-0.5 rounded font-semibold {{ $item->sourcetype === 'LKH' ? 'bg-purple-100 text-purple-700' : 'bg-teal-100 text-teal-700' }}">{{ $item->sourcetype }}</span>
                                <span class="text-xs text-gray-500 font-mono">{{ $item->sourceno }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-700">{{ $item->jumlahkendaraan }} unit</div>
                            <div class="text-xs text-gray-400 truncate max-w-[160px]" title="{{ $item->daftarkendaraan }}">{{ $item->daftarkendaraan }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            <span class="font-semibold text-blue-700">{{ number_format($item->totalsolarrequested, 2) }}</span>
                            <span class="text-xs text-gray-400 ml-0.5">L</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            @if($item->totalsolarreal !== null)
                                <span class="font-semibold text-green-700">{{ number_format($item->totalsolarreal, 2) }}</span>
                                <span class="text-xs text-gray-400 ml-0.5">L</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($statusKey === 'pending_input')
                                <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 bg-orange-50 text-orange-700 border border-orange-200 rounded-full font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span> Perlu Input
                                </span>
                            @elseif($statusKey === 'pending_approval')
                                <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-full font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 animate-pulse"></span> Pending Approval
                                </span>
                            @elseif($statusKey === 'approved')
                                <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 bg-green-50 text-green-700 border border-green-200 rounded-full font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Approved
                                </span>
                            @elseif($statusKey === 'rejected')
                                <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 bg-red-50 text-red-700 border border-red-200 rounded-full font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rejected
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                {{-- Input Solar Real — hanya jika belum difinalisasi --}}
                                @if($statusKey === 'pending_input')
                                    <button @click="openConfirmModal('{{ $item->orderno }}')"
                                            class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-md transition font-medium">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Input Solar
                                    </button>
                                @endif

                                {{-- Sync Citrix — hanya jika gudangapprovalstatus = '1' --}}
                                @if($statusKey === 'approved')
                                    <button @click="syncCitrix('{{ $item->orderno }}')"
                                            :disabled="syncing === '{{ $item->orderno }}'"
                                            class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 rounded-md font-medium transition
                                                   {{ $item->issyncedcitrix
                                                       ? 'bg-gray-100 text-gray-500 border border-gray-200'
                                                       : 'bg-indigo-600 hover:bg-indigo-700 text-white' }}"
                                            title="{{ $item->issyncedcitrix ? 'Synced: ' . ($item->citrixsyncedat ?? '') : 'Sync ke Citrix' }}">
                                        <span x-show="syncing === '{{ $item->orderno }}'">
                                            <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        </span>
                                        <span x-show="syncing !== '{{ $item->orderno }}'">
                                            @if($item->issyncedcitrix)
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            @else
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            @endif
                                        </span>
                                        {{ $item->issyncedcitrix ? 'Synced' : 'Citrix' }}
                                    </button>
                                @endif

                                {{-- Detail --}}
                                <a href="{{ route('transaction.gudang-bbm.show', $item->orderno) }}"
                                   class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-white hover:bg-gray-50 text-gray-600 border border-gray-200 rounded-md transition font-medium">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <p class="text-sm text-gray-400 font-medium">Tidak ada order BBM yang perlu dikonfirmasi</p>
                                <p class="text-xs text-gray-300 mt-0.5">Order akan muncul setelah diapprove oleh atasan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if(method_exists($bbmData, 'links'))
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50">{{ $bbmData->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- MODAL: INPUT SOLAR REAL --}}
    {{-- ================================================================ --}}
    <div x-show="showModal" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
         @keydown.escape.window="closeModal()">
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[92vh] flex flex-col ring-1 ring-gray-200" @click.stop>

            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-900">Input Solar Real — Konfirmasi Pengeluaran</h2>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-sm font-mono font-bold text-blue-700" x-text="'Order #' + activeOrderno"></span>
                                <span class="text-xs px-2 py-0.5 rounded font-bold"
                                      :class="activeSourceType === 'LKH' ? 'bg-purple-100 text-purple-700' : 'bg-teal-100 text-teal-700'"
                                      x-text="activeSourceType"></span>
                                <span class="text-xs text-gray-500 font-mono" x-text="activeSourceno"></span>
                            </div>
                        </div>
                    </div>
                    <button @click="closeModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Summary --}}
                <div class="mt-4 flex items-center gap-4 p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 font-medium leading-none">Solar Diminta</div>
                            <div class="text-sm font-bold text-blue-700" x-text="totalRequestedModal.toFixed(2) + ' L'"></div>
                        </div>
                    </div>
                    <div class="w-px h-8 bg-gray-200"></div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 font-medium leading-none">Solar Real</div>
                            <div class="text-sm font-bold text-green-700" x-text="totalRealModal.toFixed(2) + ' L'"></div>
                        </div>
                    </div>
                    <div class="w-px h-8 bg-gray-200"></div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                             :class="allConfirmed ? 'bg-green-100' : 'bg-gray-100'">
                            <span class="text-xs font-bold" :class="allConfirmed ? 'text-green-600' : 'text-gray-500'"
                                  x-text="confirmedCount + '/' + modalItems.length"></span>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 font-medium leading-none">Terisi</div>
                            <div class="text-sm font-medium" :class="allConfirmed ? 'text-green-700' : 'text-gray-600'"
                                 x-text="allConfirmed ? 'Semua terisi' : 'Belum lengkap'"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="overflow-y-auto flex-1 px-6 py-4">
                <div x-show="loadingItems" class="text-center py-16">
                    <svg class="animate-spin h-8 w-8 text-blue-500 mx-auto mb-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="text-sm text-gray-400">Memuat data kendaraan...</p>
                </div>

                <div x-show="!loadingItems">
                    {{-- Table header --}}
                    <div class="hidden md:grid grid-cols-12 gap-2 px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-200 mb-2">
                        <div class="col-span-4">Kendaraan</div>
                        <div class="col-span-2 text-right">Solar Diminta (Maks)</div>
                        <div class="col-span-3 text-right">Solar Real</div>
                        <div class="col-span-2 text-right">Selisih</div>
                        <div class="col-span-1 text-center">Status</div>
                    </div>

                    <div class="space-y-2">
                        <template x-for="(item, idx) in modalItems" :key="item.id">
                            <div class="rounded-xl border transition-all duration-200"
                                 :class="item.confirmed ? 'border-green-200 bg-green-50/30' : 'border-gray-200 bg-white'">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-2 items-center px-4 py-3">
                                    {{-- Kendaraan info --}}
                                    <div class="col-span-4 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold shrink-0"
                                             :class="item.confirmed ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-500'"
                                             x-text="idx + 1"></div>
                                        <div class="min-w-0">
                                            <div class="font-mono font-bold text-gray-800 text-sm" x-text="item.nokendaraan"></div>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="text-xs text-gray-400" x-text="item.jenis" x-show="item.jenis"></span>
                                                <span class="text-xs text-gray-300" x-show="item.jenis && item.operator_nama">·</span>
                                                <span class="text-xs text-gray-400" x-text="item.operator_nama" x-show="item.operator_nama"></span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Solar diminta --}}
                                    <div class="col-span-2 text-right">
                                        <label class="block text-xs text-gray-400 mb-1 md:hidden font-medium">Solar Diminta</label>
                                        <div class="text-sm font-semibold text-blue-700" x-text="item.solarrequested.toFixed(2) + ' L'"></div>
                                    </div>

                                    {{-- Solar real input --}}
                                    <div class="col-span-3">
                                        <label class="block text-xs text-gray-400 mb-1 md:hidden font-medium">Solar Real</label>
                                        <div class="flex items-center gap-1.5 justify-end">
                                            <input type="number" step="0.01" min="0.01"
                                                   :max="item.solarrequested"
                                                   x-model.number="item.solarreal_input"
                                                   @blur="autoSaveItem(idx)"
                                                   @keydown.enter="autoSaveItem(idx)"
                                                   class="w-28 px-2.5 py-2 border rounded-lg text-sm text-right font-semibold focus:ring-2 focus:ring-green-500/20 focus:border-green-400"
                                                   :class="item.confirmed ? 'border-green-300 bg-green-50 text-green-800' : 'border-gray-300 bg-white text-gray-800'"/>
                                            <span class="text-xs text-gray-400 w-3">L</span>
                                        </div>
                                    </div>

                                    {{-- Selisih --}}
                                    <div class="col-span-2 text-right">
                                        <label class="block text-xs text-gray-400 mb-1 md:hidden font-medium">Selisih</label>
                                        <template x-if="item.solarreal_input > 0">
                                            <span class="text-xs font-medium"
                                                  :class="(item.solarrequested - item.solarreal_input) > 0 ? 'text-orange-600' : 'text-green-600'"
                                                  x-text="((item.solarrequested - item.solarreal_input) > 0 ? '-' : '') + Math.abs(item.solarrequested - item.solarreal_input).toFixed(2) + ' L'">
                                            </span>
                                        </template>
                                        <template x-if="!item.solarreal_input || item.solarreal_input <= 0">
                                            <span class="text-gray-300">—</span>
                                        </template>
                                    </div>

                                    {{-- Status --}}
                                    <div class="col-span-1 flex justify-center">
                                        <span x-show="item.saving" class="text-gray-400">
                                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        </span>
                                        <span x-show="item.confirmed && !item.saving" class="text-green-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                        <span x-show="!item.confirmed && !item.saving" class="w-5 h-5 rounded-full border-2 border-gray-200"></span>
                                    </div>
                                </div>

                                {{-- Error message --}}
                                <div x-show="item.error" x-transition class="px-4 pb-3">
                                    <div class="flex items-center gap-2 text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span x-text="item.error"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30 rounded-b-2xl">
                <div class="flex items-center justify-between">
                    <div class="text-xs text-gray-400">
                        Setelah submit, data akan masuk antrian approval pengeluaran
                    </div>
                    <div class="flex gap-2">
                        <button @click="closeModal()"
                                class="px-4 py-2.5 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-100 text-sm font-medium transition">
                            Tutup
                        </button>
                        <button @click="finalizeOrder()"
                                :disabled="!allConfirmed || isFinalizing"
                                class="px-6 py-2.5 text-white rounded-lg text-sm font-semibold transition flex items-center gap-2 shadow-sm"
                                :class="allConfirmed ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-300 cursor-not-allowed'">
                            <svg x-show="isFinalizing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-show="isFinalizing">Memproses...</span>
                            <span x-show="!isFinalizing">Submit ke Approval</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function gudangBbmIndex() {
    return {
        showModal:    false,
        loadingItems: false,
        isFinalizing: false,
        syncing:      null,
        activeOrderno:   '',
        activeSourceType:'',
        activeSourceno:  '',
        modalItems:   [],

        get confirmedCount() { return this.modalItems.filter(i => i.confirmed).length; },
        get allConfirmed() { return this.modalItems.length > 0 && this.modalItems.every(i => i.confirmed); },
        get totalRealModal() { return this.modalItems.reduce((s, i) => s + (i.solarreal_input || 0), 0); },
        get totalRequestedModal() { return this.modalItems.reduce((s, i) => s + (i.solarrequested || 0), 0); },

        async openConfirmModal(orderno) {
            this.activeOrderno = orderno;
            this.showModal     = true;
            this.loadingItems  = true;
            this.modalItems    = [];

            try {
                const res  = await fetch(`{{ url('transaction/order-bbm') }}/${orderno}/items`);
                const data = await res.json();
                if (!data.success) { alert(data.message); this.showModal = false; return; }

                this.activeSourceType = data.header.sourcetype;
                this.activeSourceno   = data.header.sourceno;
                this.modalItems = data.items.map(i => ({
                    id:             i.id,
                    nokendaraan:    i.nokendaraan,
                    jenis:          i.jenis || '',
                    operator_nama:  i.operator_nama || '',
                    solarrequested: parseFloat(i.solarrequested) || 0,
                    solarreal_input: i.solarreal !== null ? parseFloat(i.solarreal) : parseFloat(i.solarrequested),
                    confirmed:      i.solarreal !== null,
                    saving:         false,
                    error:          '',
                }));
            } catch (e) {
                alert('Gagal memuat data');
                this.showModal = false;
            } finally {
                this.loadingItems = false;
            }
        },

        closeModal() {
            this.showModal = false;
            this.modalItems = [];
        },

        async autoSaveItem(idx) {
            const item = this.modalItems[idx];
            item.error = '';

            if (!item.solarreal_input || item.solarreal_input <= 0) {
                item.error = 'Solar real harus lebih dari 0';
                return;
            }
            if (item.solarreal_input > item.solarrequested) {
                item.error = `Melebihi batas maks (${item.solarrequested.toFixed(2)} L)`;
                return;
            }

            item.saving = true;
            try {
                const res  = await fetch(`{{ url('transaction/gudang-bbm') }}/${this.activeOrderno}/confirm-item`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ item_id: item.id, solarreal: item.solarreal_input }),
                });
                const data = await res.json();
                if (data.success) {
                    item.confirmed = true;
                } else {
                    item.error = data.message;
                }
            } catch (e) {
                item.error = 'Gagal menyimpan';
            } finally {
                item.saving = false;
            }
        },

        async finalizeOrder() {
            if (!this.allConfirmed) return;
            if (!confirm(`Submit konfirmasi Order #${this.activeOrderno} ke approval pengeluaran?\n\nTotal solar real: ${this.totalRealModal.toFixed(2)} L`)) return;

            this.isFinalizing = true;
            try {
                const res  = await fetch(`{{ url('transaction/gudang-bbm') }}/${this.activeOrderno}/finalize`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({}),
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + data.message);
                }
            } catch (e) {
                alert('Terjadi kesalahan');
            } finally {
                this.isFinalizing = false;
            }
        },

        async syncCitrix(orderno) {
            if (!confirm(`Sync Order #${orderno} ke Citrix?`)) return;
            this.syncing = orderno;
            try {
                const res  = await fetch(`{{ url('transaction/gudang-bbm') }}/${orderno}/sync-citrix`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({}),
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + data.message);
                }
            } catch (e) {
                alert('Terjadi kesalahan');
            } finally {
                this.syncing = null;
            }
        },
    };
}
</script>
</x-layout>