{{-- resources/views/transaction/order-bbm/index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

<div x-data="orderBbmIndex()" class="mx-auto bg-white rounded-lg shadow-sm border border-gray-200">

    {{-- HEADER --}}
    <div class="px-6 py-4 border-b border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Order Pengeluaran BBM</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola order pengisian solar kendaraan dari LKH & SJS</p>
            </div>
            @if(($pendingLkh->count() + $pendingSjs->count()) > 0)
            <button @click="activeTab = 'antrian'"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-orange-50 border border-orange-200 text-orange-700 rounded-lg text-xs font-medium hover:bg-orange-100 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $pendingLkh->count() + $pendingSjs->count() }} antrian pending
            </button>
            @endif
        </div>
    </div>

    {{-- TABS --}}
    <div class="px-6 border-b border-gray-200 bg-gray-50/50">
        <nav class="flex space-x-1 -mb-px">
            <button @click="activeTab = 'list'"
                    :class="activeTab === 'list' ? 'border-blue-500 text-blue-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-2.5 px-4 border-b-2 font-medium text-sm rounded-t-lg transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Daftar Order BBM
            </button>
            <button @click="activeTab = 'antrian'"
                    :class="activeTab === 'antrian' ? 'border-blue-500 text-blue-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-2.5 px-4 border-b-2 font-medium text-sm rounded-t-lg transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Antrian Pembuatan Order
                @if(($pendingLkh->count() + $pendingSjs->count()) > 0)
                    <span class="bg-orange-500 text-white text-xs min-w-[20px] h-5 flex items-center justify-center px-1.5 rounded-full font-bold">{{ $pendingLkh->count() + $pendingSjs->count() }}</span>
                @endif
            </button>
        </nav>
    </div>

    {{-- ================================================================ --}}
    {{-- TAB: DAFTAR ORDER --}}
    {{-- ================================================================ --}}
    <div x-show="activeTab === 'list'" x-cloak class="p-6">

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-5">
            <div class="rounded-lg p-3 bg-white border border-gray-200 hover:shadow-sm transition">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 rounded-md bg-gray-100 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    </div>
                    <span class="text-xs text-gray-500 font-medium">Total</span>
                </div>
                <div class="text-2xl font-bold text-gray-800 pl-1">{{ $stats['total'] }}</div>
            </div>
            <div class="rounded-lg p-3 bg-white border border-yellow-200 hover:shadow-sm transition">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 rounded-md bg-yellow-50 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <span class="text-xs text-gray-500 font-medium">Draft</span>
                </div>
                <div class="text-2xl font-bold text-yellow-700 pl-1">{{ $stats['draft'] }}</div>
            </div>
            <div class="rounded-lg p-3 bg-white border border-orange-200 hover:shadow-sm transition">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 rounded-md bg-orange-50 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-xs text-gray-500 font-medium">Pending</span>
                </div>
                <div class="text-2xl font-bold text-orange-700 pl-1">{{ $stats['pending'] }}</div>
            </div>
            <div class="rounded-lg p-3 bg-white border border-green-200 hover:shadow-sm transition">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 rounded-md bg-green-50 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-xs text-gray-500 font-medium">Approved</span>
                </div>
                <div class="text-2xl font-bold text-green-700 pl-1">{{ $stats['approved'] }}</div>
            </div>
            <div class="rounded-lg p-3 bg-white border border-blue-200 hover:shadow-sm transition">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 rounded-md bg-blue-50 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-xs text-gray-500 font-medium">Confirmed</span>
                </div>
                <div class="text-2xl font-bold text-blue-700 pl-1">{{ $stats['confirmed'] }}</div>
            </div>
        </div>

        {{-- Filter --}}
        <form class="flex flex-wrap items-end gap-2 mb-4 p-3 bg-gray-50 rounded-lg border border-gray-100" action="{{ route('transaction.order-bbm.index') }}" method="GET">
            <input type="hidden" name="tab" value="list"/>
            <div>
                <label class="block text-xs text-gray-500 mb-1 font-medium">Pencarian</label>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Order no, source..."
                       class="text-sm border border-gray-300 rounded-md px-3 py-2 w-48 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1 font-medium">Tanggal</label>
                <input type="date" name="filter_date" value="{{ $filterDate ?? '' }}"
                       id="od_filter_date" {{ $showAllDate ? 'disabled' : '' }}
                       class="text-sm border border-gray-300 rounded-md px-3 py-2 disabled:bg-gray-100 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"/>
            </div>
            <div class="pb-0.5">
                <label class="flex items-center text-sm gap-1.5 cursor-pointer bg-white border border-gray-300 rounded-md px-3 py-2">
                    <input type="checkbox" name="show_all_date" value="1"
                           onchange="document.getElementById('od_filter_date').disabled=this.checked; this.form.submit();"
                           {{ $showAllDate ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"/>
                    <span class="whitespace-nowrap text-xs font-medium text-gray-600">Semua Tanggal</span>
                </label>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1 font-medium">Status</label>
                <select name="filter_status" class="text-sm border border-gray-300 rounded-md px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="DRAFT"     {{ ($filterStatus??'') === 'DRAFT'     ? 'selected' : '' }}>Draft</option>
                    <option value="SUBMITTED" {{ ($filterStatus??'') === 'SUBMITTED' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="1"         {{ ($filterStatus??'') === '1'         ? 'selected' : '' }}>Approved</option>
                    <option value="0"         {{ ($filterStatus??'') === '0'         ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded-md font-medium transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Cari
            </button>
            @if($search || $filterStatus)
            <a href="{{ route('transaction.order-bbm.index', ['tab' => 'list', 'filter_date' => $filterDate]) }}"
               class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition">Reset</a>
            @endif
        </form>

        {{-- Table --}}
        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Order No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Source</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Unit</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Solar Diminta</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Solar Real</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Gudang</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($orderData as $item)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('transaction.order-bbm.show', $item->orderno) }}"
                               class="text-sm font-mono font-bold text-blue-700 hover:text-blue-900 hover:underline">#{{ $item->orderno }}</a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ \Carbon\Carbon::parse($item->orderdate)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex items-center text-xs px-2 py-0.5 rounded font-semibold {{ $item->sourcetype === 'LKH' ? 'bg-purple-100 text-purple-700' : 'bg-teal-100 text-teal-700' }}">{{ $item->sourcetype }}</span>
                                <span class="text-xs text-gray-500 font-mono">{{ $item->sourceno }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-700">{{ $item->jumlahkendaraan }}</td>
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
                            @if($item->status === 'DRAFT')
                                <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-full font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span> Draft
                                </span>
                            @elseif($item->approvalstatus === '1')
                                <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 bg-green-50 text-green-700 border border-green-200 rounded-full font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Approved
                                </span>
                            @elseif($item->approvalstatus === '0')
                                <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 bg-red-50 text-red-700 border border-red-200 rounded-full font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rejected
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 bg-orange-50 text-orange-700 border border-orange-200 rounded-full font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span> Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($item->gudangconfirm)
                                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 bg-green-50 text-green-600 border border-green-200 rounded-full">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Confirmed
                                </span>
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                @if($item->status === 'DRAFT')
                                    <button @click="openEditModal('{{ $item->orderno }}')"
                                            class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 border border-yellow-200 rounded-md transition font-medium">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </button>
                                    <button @click="submitOrder('{{ $item->orderno }}')"
                                            class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-md transition font-medium">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        Submit
                                    </button>
                                @endif
                                <a href="{{ route('transaction.order-bbm.show', $item->orderno) }}"
                                   class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-white hover:bg-gray-50 text-gray-600 border border-gray-200 rounded-md transition font-medium">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <p class="text-sm text-gray-400 font-medium">Tidak ada data order</p>
                                <p class="text-xs text-gray-300 mt-0.5">Cek filter atau buat order baru dari tab Antrian</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if(method_exists($orderData, 'links'))
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50">{{ $orderData->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- TAB: ANTRIAN --}}
    {{-- ================================================================ --}}
    <div x-show="activeTab === 'antrian'" x-cloak class="p-6">

        @if($pendingLkh->isEmpty() && $pendingSjs->isEmpty())
        <div class="text-center py-16">
            <svg class="w-14 h-14 text-green-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm font-medium text-gray-500">Semua antrian sudah diproses</p>
            <p class="text-xs text-gray-400 mt-1">Tidak ada LKH atau SJS yang menunggu pembuatan order BBM</p>
        </div>
        @endif

        {{-- LKH --}}
        @if($pendingLkh->isNotEmpty())
        <div class="mb-8">
            <div class="flex items-center gap-2 mb-3">
                <span class="inline-flex items-center gap-1.5 bg-purple-100 text-purple-700 text-xs font-bold px-2.5 py-1 rounded-md">LKH</span>
                <h2 class="text-sm font-semibold text-gray-700">LKH Approved — Belum Ada Order BBM</h2>
                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">{{ $pendingLkh->count() }}</span>
            </div>
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr class="bg-purple-50/50 border-b border-gray-200">
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">LKH No</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Activity</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Mandor</th>
                            <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Hasil Kerja</th>
                            <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Kendaraan</th>
                            <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($pendingLkh as $lkh)
                        <tr class="hover:bg-purple-50/30 transition-colors">
                            <td class="px-4 py-2.5 text-sm font-mono font-bold text-purple-700">{{ $lkh->lkhno }}</td>
                            <td class="px-4 py-2.5 text-sm text-gray-600">{{ \Carbon\Carbon::parse($lkh->lkhdate)->format('d/m/Y') }}</td>
                            <td class="px-4 py-2.5 text-sm text-gray-700">{{ $lkh->activityname ?? '-' }}</td>
                            <td class="px-4 py-2.5 text-sm text-gray-600">{{ $lkh->mandor_nama ?? '-' }}</td>
                            <td class="px-4 py-2.5 text-sm text-center">
                                <span class="font-semibold text-gray-800">{{ number_format((float)($lkh->total_luashasil ?? 0), 2) }}</span>
                                <span class="text-xs text-gray-400 ml-0.5">Ha</span>
                            </td>
                            <td class="px-4 py-2.5 text-sm text-center">
                                <span class="font-medium text-gray-700">{{ $lkh->jumlahkendaraan }}</span>
                                <span class="text-xs text-gray-400 ml-0.5">unit</span>
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                <button @click="openModal('LKH', '{{ $lkh->lkhno }}', null)"
                                        class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-3.5 py-1.5 text-xs rounded-md font-medium transition shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    Buat Order
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- SJS --}}
        @if($pendingSjs->isNotEmpty())
        <div>
            <div class="flex items-center gap-2 mb-3">
                <span class="inline-flex items-center gap-1.5 bg-teal-100 text-teal-700 text-xs font-bold px-2.5 py-1 rounded-md">SJS</span>
                <h2 class="text-sm font-semibold text-gray-700">Surat Jalan Supply — Belum Ada Order BBM</h2>
                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">{{ $pendingSjs->count() }}</span>
            </div>
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr class="bg-teal-50/50 border-b border-gray-200">
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">SJS No</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kendaraan</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Mandor</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tujuan</th>
                            <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Rit</th>
                            <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($pendingSjs as $sjs)
                        <tr class="hover:bg-teal-50/30 transition-colors">
                            <td class="px-4 py-2.5 text-sm font-mono font-bold text-teal-700">{{ $sjs->sjsno }}</td>
                            <td class="px-4 py-2.5 text-sm text-gray-600">{{ \Carbon\Carbon::parse($sjs->sjsdate)->format('d/m/Y') }}</td>
                            <td class="px-4 py-2.5">
                                <div class="text-sm font-medium text-gray-800">{{ $sjs->nokendaraan }}</div>
                                <div class="text-xs text-gray-400">{{ $sjs->jenis }}</div>
                            </td>
                            <td class="px-4 py-2.5 text-sm text-gray-600">{{ $sjs->mandor_nama ?? '-' }}</td>
                            <td class="px-4 py-2.5 text-sm text-gray-600">{{ $sjs->tujuan }}</td>
                            <td class="px-4 py-2.5 text-sm text-center">
                                <span class="inline-flex items-center font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded">{{ $sjs->jumlahrit }}</span>
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                <button @click="openModal('SJS', '{{ $sjs->sjsno }}', @js($sjs))"
                                        class="inline-flex items-center gap-1.5 bg-teal-600 hover:bg-teal-700 text-white px-3.5 py-1.5 text-xs rounded-md font-medium transition shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    Buat Order
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- ================================================================ --}}
    {{-- MODAL: INPUT / EDIT KALIBRASI BBM --}}
    {{-- ================================================================ --}}
    <div x-show="showModal" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
         @keydown.escape.window="showModal = false">
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[92vh] flex flex-col ring-1 ring-gray-200" @click.stop>

            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                                 :class="sourceType === 'LKH' ? 'bg-purple-100' : 'bg-teal-100'">
                                <svg class="w-5 h-5" :class="sourceType === 'LKH' ? 'text-purple-600' : 'text-teal-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900" x-text="isEdit ? 'Edit Kalibrasi BBM' : 'Input Kalibrasi BBM'"></h2>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-xs px-2 py-0.5 rounded font-bold"
                                          :class="sourceType === 'LKH' ? 'bg-purple-100 text-purple-700' : 'bg-teal-100 text-teal-700'"
                                          x-text="sourceType"></span>
                                    <span class="text-sm font-mono text-gray-500" x-text="sourceno"></span>
                                    <template x-if="isEdit">
                                        <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded font-medium border border-yellow-200">DRAFT — Editing</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button @click="showModal = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition -mt-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Summary Bar --}}
                <div class="mt-4 flex items-center gap-4 p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 font-medium leading-none">Kendaraan</div>
                            <div class="text-sm font-bold text-gray-800" x-text="orderItems.length + ' unit'"></div>
                        </div>
                    </div>
                    <div class="w-px h-8 bg-gray-200"></div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 font-medium leading-none">Total Solar Diminta</div>
                            <div class="text-sm font-bold text-green-700" x-text="totalSolar.toFixed(2) + ' Liter'"></div>
                        </div>
                    </div>
                    <div class="w-px h-8 bg-gray-200"></div>
                    <div class="flex items-center gap-2" x-show="overrideCount > 0">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 font-medium leading-none">Override</div>
                            <div class="text-sm font-bold text-amber-700" x-text="overrideCount + ' item'"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="overflow-y-auto flex-1 px-6 py-4">
                {{-- Loading --}}
                <div x-show="loadingItems" class="text-center py-16">
                    <svg class="animate-spin h-8 w-8 text-blue-500 mx-auto mb-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="text-sm text-gray-400">Memuat data kendaraan...</p>
                </div>

                {{-- Table-style items --}}
                <div x-show="!loadingItems">
                    {{-- Table Header --}}
                    <div class="hidden md:grid grid-cols-12 gap-2 px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-200 mb-2">
                        <div class="col-span-3">Kendaraan</div>
                        <div class="col-span-2 text-right">Hasil Kerja</div>
                        <div class="col-span-2 text-right">Kalibrasi</div>
                        <div class="col-span-2 text-right">Solar Hitung</div>
                        <div class="col-span-2 text-right">Solar Diminta</div>
                        <div class="col-span-1 text-center">Override</div>
                    </div>

                    {{-- Items --}}
                    <div class="space-y-2">
                        <template x-for="(item, idx) in orderItems" :key="idx">
                            <div class="rounded-xl border transition-all duration-200"
                                 :class="item.ismanualoverride
                                    ? 'border-gray-300 bg-white shadow-sm'
                                    : 'border-gray-200 bg-white hover:border-gray-300'">

                                {{-- Main Row --}}
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-2 items-center px-4 py-3">
                                    {{-- Kendaraan Info --}}
                                    <div class="col-span-3 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500 shrink-0"
                                             x-text="idx + 1"></div>
                                        <div class="min-w-0">
                                            <div class="font-mono font-bold text-gray-800 text-sm truncate" x-text="item.nokendaraan"></div>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="text-xs text-gray-400 truncate" x-text="item.jenis || '-'" x-show="item.jenis"></span>
                                                <span class="text-xs text-gray-300" x-show="item.jenis && item.operator_nama">·</span>
                                                <span class="text-xs text-gray-400 truncate" x-text="item.operator_nama" x-show="item.operator_nama"></span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Hasil Kerja --}}
                                    <div class="col-span-2">
                                        <label class="block text-xs text-gray-400 mb-1 md:hidden font-medium">Hasil Kerja</label>
                                        <div class="flex items-center gap-1">
                                            <input type="number" step="0.01" min="0" x-model.number="item.hasilkerja" @input="calcSolar(idx)"
                                                   class="w-full px-2.5 py-2 border border-gray-200 rounded-lg text-sm text-right font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 bg-gray-50/50"/>
                                            <span class="text-xs text-gray-400 font-medium w-7 text-right shrink-0" x-text="item.satuanhasil"></span>
                                        </div>
                                    </div>

                                    {{-- Kalibrasi --}}
                                    <div class="col-span-2">
                                        <label class="block text-xs text-gray-400 mb-1 md:hidden font-medium">Kalibrasi</label>
                                        <div class="flex items-center gap-1">
                                            <input type="number" step="0.01" min="0" x-model.number="item.nilaikalibrasi" @input="calcSolar(idx)"
                                                   class="w-full px-2.5 py-2 border border-blue-200 rounded-lg text-sm text-right font-medium bg-blue-50/50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400"/>
                                            <span class="text-xs text-gray-400 font-medium w-9 text-right shrink-0" x-text="item.satuankalibrasi"></span>
                                        </div>
                                    </div>

                                    {{-- Solar Hitung --}}
                                    <div class="col-span-2">
                                        <label class="block text-xs text-gray-400 mb-1 md:hidden font-medium">Solar Hitung</label>
                                        <div class="px-2.5 py-2 bg-gray-50 border border-gray-100 rounded-lg text-sm font-semibold text-right"
                                             :class="item.ismanualoverride ? 'text-gray-400 line-through' : 'text-blue-600'"
                                             x-text="item.solarcalculated.toFixed(2) + ' L'"></div>
                                    </div>

                                    {{-- Solar Diminta --}}
                                    <div class="col-span-2">
                                        <label class="block text-xs text-gray-400 mb-1 md:hidden font-medium">Solar Diminta</label>
                                        <div class="px-2.5 py-2 rounded-lg text-sm font-bold text-right border"
                                             :class="item.ismanualoverride
                                                ? 'bg-gray-50 border-gray-300 text-gray-800'
                                                : 'bg-green-50 border-green-200 text-green-700'"
                                             x-text="item.solarrequested.toFixed(2) + ' L'"></div>
                                    </div>

                                    {{-- Override Toggle --}}
                                    <div class="col-span-1 flex justify-center">
                                        <button type="button" @click="item.ismanualoverride = !item.ismanualoverride; calcSolar(idx)"
                                                class="relative w-9 h-5 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1"
                                                :class="item.ismanualoverride ? 'bg-amber-500 focus:ring-amber-400' : 'bg-gray-300 focus:ring-gray-400'">
                                            <span class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"
                                                  :class="item.ismanualoverride ? 'translate-x-4' : 'translate-x-0'"></span>
                                        </button>
                                    </div>
                                </div>

                                {{-- Override Panel (expanded) --}}
                                <div x-show="item.ismanualoverride" x-collapse.duration.200ms
                                     class="border-t border-gray-200 bg-gray-50/80 px-4 py-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-7 h-7 rounded-md bg-gray-200 flex items-center justify-center shrink-0 mt-0.5">
                                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-xs font-semibold text-gray-600 mb-2">Manual Override — Nilai solar ditentukan manual</div>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1 font-medium">Solar Override (Liter)</label>
                                                    <input type="number" step="0.01" min="0" x-model.number="item.solaroverride" @input="calcSolar(idx)"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-right font-semibold text-gray-800 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 placeholder-gray-300"
                                                           placeholder="0.00"/>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label class="block text-xs text-gray-500 mb-1 font-medium">Alasan <span class="text-red-500">*</span></label>
                                                    <input type="text" x-model="item.overridereason"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 placeholder-gray-300"
                                                           placeholder="Contoh: Kendaraan pindah activity di pertengahan hari, kondisi lapangan berbeda"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Catatan Row (always visible, minimal) --}}
                                <div class="px-4 py-2 border-t border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                        <input type="text" x-model="item.catatan" placeholder="Catatan tambahan (opsional)"
                                               class="w-full px-0 py-1 border-0 text-sm text-gray-600 placeholder-gray-300 focus:ring-0 bg-transparent"/>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Empty state --}}
                    <div x-show="orderItems.length === 0 && !loadingItems" class="text-center py-12 text-gray-400 text-sm">
                        Tidak ada data kendaraan
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30 rounded-b-2xl">
                <div class="flex items-center justify-between">
                    <div class="text-xs text-gray-400">
                        <span x-show="!isEdit">Order akan dibuat sebagai <strong class="text-yellow-600">DRAFT</strong> — bisa diedit sebelum submit</span>
                        <span x-show="isEdit">Perubahan hanya berlaku selama status masih <strong class="text-yellow-600">DRAFT</strong></span>
                    </div>
                    <div class="flex gap-2">
                        <button @click="showModal = false"
                                class="px-4 py-2.5 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-100 text-sm font-medium transition">
                            Batal
                        </button>
                        <button @click="saveOrder()" :disabled="isLoading || orderItems.length === 0 || loadingItems"
                                class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-semibold transition flex items-center gap-2 shadow-sm">
                            <svg x-show="isLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-show="isLoading">Menyimpan...</span>
                            <span x-show="!isLoading" x-text="isEdit ? 'Simpan Perubahan' : 'Buat Order BBM (Draft)'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function orderBbmIndex() {
    return {
        activeTab: new URLSearchParams(window.location.search).get('tab') || 'list',
        showModal: false,
        isEdit: false,
        editOrderno: null,
        loadingItems: false,
        isLoading: false,
        sourceType: '',
        sourceno: '',
        orderItems: [],

        get totalSolar() {
            return this.orderItems.reduce((s, i) => s + (i.solarrequested || 0), 0);
        },

        get overrideCount() {
            return this.orderItems.filter(i => i.ismanualoverride).length;
        },

        async openModal(type, no, sjsData) {
            this.isEdit      = false;
            this.editOrderno = null;
            this.sourceType  = type;
            this.sourceno    = no;
            this.orderItems  = [];
            this.showModal   = true;
            this.loadingItems = true;

            try {
                if (type === 'LKH') {
                    const res  = await fetch(`{{ url('transaction/order-bbm/lkh') }}/${no}/kendaraan`);
                    const data = await res.json();
                    if (!data.success) { alert(data.message); this.showModal = false; return; }
                    this.orderItems = data.data.map(k => this.makeItem(k, 'LKH'));
                } else {
                    this.orderItems = [this.makeItemSjs(sjsData)];
                }
            } catch (e) {
                alert('Gagal memuat data kendaraan');
                this.showModal = false;
            } finally {
                this.loadingItems = false;
            }
        },

        async openEditModal(orderno) {
            this.isEdit      = true;
            this.editOrderno = orderno;
            this.showModal   = true;
            this.loadingItems = true;
            this.orderItems  = [];

            try {
                const res  = await fetch(`{{ url('transaction/order-bbm') }}/${orderno}/items`);
                const data = await res.json();
                if (!data.success) { alert(data.message); this.showModal = false; return; }

                this.sourceType = data.header.sourcetype;
                this.sourceno   = data.header.sourceno;
                this.orderItems = data.items.map(i => ({
                    id:               i.id,
                    nokendaraan:      i.nokendaraan,
                    kendaraanid:      i.kendaraanid,
                    operatorid:       i.operatorid,
                    operator_nama:    i.operator_nama || '',
                    jenis:            i.jenis || '',
                    hasilkerja:       parseFloat(i.hasilkerja) || 0,
                    satuanhasil:      i.satuanhasil,
                    nilaikalibrasi:   parseFloat(i.nilaikalibrasi) || 0,
                    satuankalibrasi:  i.satuankalibrasi,
                    solarcalculated:  parseFloat(i.solarcalculated) || 0,
                    ismanualoverride: !!i.ismanualoverride,
                    solaroverride:    parseFloat(i.solaroverride) || 0,
                    overridereason:   i.overridereason || '',
                    solarrequested:   parseFloat(i.solarrequested) || 0,
                    catatan:          i.catatan || '',
                }));
            } catch (e) {
                alert('Gagal memuat data');
                this.showModal = false;
            } finally {
                this.loadingItems = false;
            }
        },

        makeItem(k, type) {
            return {
                nokendaraan: k.nokendaraan, kendaraanid: k.kendaraanid,
                operatorid: k.operatorid,   operator_nama: k.operator_nama || '',
                jenis: k.jenis || '',
                hasilkerja: parseFloat(k.total_luashasil) || 0,
                satuanhasil: type === 'LKH' ? 'HA' : 'RIT',
                nilaikalibrasi: 0, satuankalibrasi: type === 'LKH' ? 'L/HA' : 'L/RIT',
                solarcalculated: 0, ismanualoverride: false,
                solaroverride: 0, overridereason: '', solarrequested: 0, catatan: '',
            };
        },

        makeItemSjs(sjs) {
            return {
                nokendaraan: sjs.nokendaraan, kendaraanid: sjs.kendaraanid,
                operatorid: sjs.operatorid,   operator_nama: sjs.operator_nama || '',
                jenis: sjs.jenis || '',        hasilkerja: sjs.jumlahrit,
                satuanhasil: 'RIT',            nilaikalibrasi: 0,
                satuankalibrasi: 'L/RIT',      solarcalculated: 0,
                ismanualoverride: false,        solaroverride: 0,
                overridereason: '',             solarrequested: 0, catatan: '',
            };
        },

        calcSolar(idx) {
            const item = this.orderItems[idx];
            item.solarcalculated = parseFloat(((item.hasilkerja || 0) * (item.nilaikalibrasi || 0)).toFixed(3));
            item.solarrequested  = item.ismanualoverride ? (item.solaroverride || 0) : item.solarcalculated;
        },

        async saveOrder() {
            for (const item of this.orderItems) {
                if (item.ismanualoverride && !item.overridereason.trim()) {
                    alert(`Alasan override wajib diisi untuk ${item.nokendaraan}`); return;
                }
                if (item.solarrequested <= 0) {
                    alert(`Solar diminta untuk ${item.nokendaraan} harus lebih dari 0`); return;
                }
            }

            const label = this.isEdit
                ? 'Simpan perubahan kalibrasi?'
                : `Buat Order BBM (DRAFT) untuk ${this.orderItems.length} kendaraan — ${this.totalSolar.toFixed(2)} L?`;
            if (!confirm(label)) return;

            this.isLoading = true;
            try {
                const url    = this.isEdit
                    ? `{{ url('transaction/order-bbm') }}/${this.editOrderno}`
                    : '{{ route("transaction.order-bbm.store") }}';
                const method = this.isEdit ? 'PUT' : 'POST';

                const body = this.isEdit
                    ? { items: this.orderItems }
                    : { sourcetype: this.sourceType, sourceno: this.sourceno, orderdate: new Date().toISOString().split('T')[0], items: this.orderItems };

                const res  = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(body),
                });
                const data = await res.json();
                if (data.success) { alert(data.message); location.reload(); }
                else { alert('Gagal: ' + data.message); }
            } catch (e) {
                alert('Terjadi kesalahan');
            } finally {
                this.isLoading = false;
            }
        },

        async submitOrder(orderno) {
            if (!confirm(`Submit Order #${orderno} ke antrian approval?\n\nSetelah disubmit, order tidak dapat diedit lagi.`)) return;
            try {
                const res  = await fetch(`{{ url('transaction/order-bbm') }}/${orderno}/submit`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({}),
                });
                const data = await res.json();
                if (data.success) { alert(data.message); location.reload(); }
                else { alert('Gagal: ' + data.message); }
            } catch (e) { alert('Terjadi kesalahan'); }
        },
    };
}
</script>
</x-layout>