{{-- resources/views/approval/index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="min-h-screen bg-slate-50/60" x-data="approvalData()">

        {{-- ========== HEADER ========== --}}
        <div class="bg-white border-b border-slate-200/80">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h1 class="text-lg font-semibold text-slate-900 tracking-tight">Approval Center</h1>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $userInfo['jabatan_name'] }}
                            @if ($userActivityGroups->isNotEmpty())
                                <span class="text-slate-400">&middot;
                                    {{ $userActivityGroups->pluck('activitygroup')->implode(', ') }}</span>
                            @else
                                <span class="inline-flex items-center gap-1 text-amber-600 font-medium">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    No activity access
                                </span>
                            @endif
                        </p>
                    </div>

                    {{-- Date Filter --}}
                    <form action="{{ route('approval.index') }}" method="GET" id="filterForm"
                        class="flex items-center gap-2.5">
                        <div
                            class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5">
                            <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor"
                                stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            <input type="date" name="filter_date" value="{{ $filterDate }}"
                                :disabled="allDateChecked"
                                class="text-sm bg-transparent border-none p-0 focus:ring-0 text-slate-700 disabled:text-slate-300 w-36">
                        </div>
                        <label
                            class="flex items-center gap-1.5 text-sm text-slate-600 cursor-pointer select-none whitespace-nowrap bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 hover:bg-slate-100 transition-colors">
                            <input type="checkbox" name="all_date" value="1" x-model="allDateChecked"
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0 w-3.5 h-3.5">
                            <span class="text-xs font-medium">All Time</span>
                        </label>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-xs font-medium rounded-lg transition-colors shadow-sm shadow-blue-200 active:scale-[0.98]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                            </svg>
                            Apply
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ========== FLASH MESSAGES ========== --}}
        @if (session('success'))
            <div class="max-w-7xl mx-auto px-6 mt-4">
                <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 px-4 py-3 rounded-xl text-sm text-emerald-800"
                    x-data="{ show: true }" x-show="show" x-transition>
                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <span class="flex-1 font-medium">{{ session('success') }}</span>
                    <button @click="show = false" class="text-emerald-400 hover:text-emerald-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="max-w-7xl mx-auto px-6 mt-4">
                <div class="flex items-center gap-3 bg-red-50 border border-red-200 px-4 py-3 rounded-xl text-sm text-red-800"
                    x-data="{ show: true }" x-show="show" x-transition>
                    <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <span class="flex-1 font-medium">{{ session('error') }}</span>
                    <button @click="show = false" class="text-red-400 hover:text-red-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- ========== MAIN CONTENT ========== --}}
        <div class="max-w-7xl mx-auto px-6 py-5">

            {{-- Tab Navigation --}}
            <div class="flex items-center gap-2 mb-5 bg-white rounded-xl border border-slate-200 p-1.5">

                @foreach ([['tab' => 'rkh', 'label' => 'RKH', 'count' => $pendingRKH->count()], ['tab' => 'lkh', 'label' => 'LKH', 'count' => $pendingLKH->count()], ['tab' => 'absen', 'label' => 'Absen', 'count' => $pendingAbsen->count()], ['tab' => 'upah', 'label' => 'Upah Mingguan', 'count' => $pendingUpah->count()], ['tab' => 'bbm', 'label' => 'BBM', 'count' => $pendingBBM->count()], ['tab' => 'other', 'label' => 'Lainnya', 'count' => $pendingOther->count()]] as $item)
                    <button @click="activeTab = '{{ $item['tab'] }}'"
                        :class="activeTab === '{{ $item['tab'] }}'
                            ?
                            'bg-slate-600 text-white shadow-sm' :
                            'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                        class="relative flex-1 flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200">
                        <span>{{ $item['label'] }}</span>
                        @if ($item['count'] > 0)
                            <span
                                :class="activeTab === '{{ $item['tab'] }}' ? 'bg-white/20 text-white' :
                                    'bg-red-500 text-white'"
                                class="min-w-[20px] text-center px-1.5 py-0.5 text-[11px] font-bold rounded-full leading-none">
                                {{ $item['count'] }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- ==================== RKH TAB ==================== --}}
            <div x-show="activeTab === 'rkh'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                @if ($pendingRKH->isEmpty())
                    <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor"
                                stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-sm text-slate-400 font-medium">Tidak ada RKH yang perlu diapprove</p>
                        <p class="text-xs text-slate-300 mt-1">Semua sudah diproses</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($pendingRKH as $rkh)
                            <div
                                class="bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-slate-300 hover:shadow-sm transition-all duration-200">
                                {{-- Header --}}
                                <div class="px-5 py-3.5 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <a href="{{ route('transaction.rencanakerjaharian.show', $rkh->rkhno) }}"
                                                class="font-semibold text-slate-900 hover:text-blue-600 text-sm transition-colors">{{ $rkh->rkhno }}</a>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span
                                                    class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($rkh->rkhdate)->format('d M Y') }}</span>
                                                @if ($rkh->activity_group_name)
                                                    <span class="text-slate-200">&middot;</span>
                                                    <span
                                                        class="text-xs text-slate-400">{{ $rkh->activity_group_name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <span
                                        class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-amber-50 text-amber-600 border border-amber-200 whitespace-nowrap tracking-wide">
                                        LEVEL {{ $rkh->approval_level }}
                                    </span>
                                </div>
                                {{-- Body --}}
                                <div class="px-5 py-3 border-t border-slate-100 grid grid-cols-3 gap-5 text-sm">
                                    <div>
                                        <p
                                            class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">
                                            Mandor</p>
                                        <p class="font-medium text-slate-800">{{ $rkh->mandor_nama ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">
                                            Luas Area</p>
                                        <p class="font-medium text-slate-800">{{ number_format($rkh->totalluas, 2) }}
                                            <span class="text-slate-400 text-xs">ha</span>
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">
                                            Activities</p>
                                        <p class="text-slate-600 leading-relaxed">{{ $rkh->activities_list }}</p>
                                    </div>
                                </div>
                                {{-- Actions --}}
                                <div class="px-5 py-3 bg-slate-50/70 border-t border-slate-100 flex gap-2">
                                    <form action="{{ route('approval.rkh.process') }}" method="POST"
                                        class="flex-1">
                                        @csrf
                                        <input type="hidden" name="rkhno" value="{{ $rkh->rkhno }}">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="level" value="{{ $rkh->approval_level }}">
                                        <button type="submit"
                                            onclick="return confirm('Approve RKH {{ $rkh->rkhno }}?')"
                                            class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm shadow-emerald-200 active:scale-[0.98]">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('approval.rkh.process') }}" method="POST"
                                        class="flex-1">
                                        @csrf
                                        <input type="hidden" name="rkhno" value="{{ $rkh->rkhno }}">
                                        <input type="hidden" name="action" value="decline">
                                        <input type="hidden" name="level" value="{{ $rkh->approval_level }}">
                                        <button type="submit"
                                            onclick="return confirm('Decline RKH {{ $rkh->rkhno }}?')"
                                            class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-4 bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-lg border border-slate-200 transition-colors active:scale-[0.98]">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Decline
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ==================== LKH TAB ==================== --}}
            <div x-show="activeTab === 'lkh'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                @if ($pendingLKH->isEmpty())
                    <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor"
                                stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-sm text-slate-400 font-medium">Tidak ada LKH yang perlu diapprove</p>
                        <p class="text-xs text-slate-300 mt-1">Semua sudah diproses</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($pendingLKH as $lkh)
                            <div
                                class="bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-slate-300 hover:shadow-sm transition-all duration-200">
                                <div class="px-5 py-3.5 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" />
                                            </svg>
                                        </div>
                                        <div>
                                            <a href="{{ route('transaction.rencanakerjaharian.showLKH', $lkh->lkhno) }}"
                                                class="font-semibold text-slate-900 hover:text-blue-600 text-sm transition-colors">{{ $lkh->lkhno }}</a>
                                            <p class="text-xs text-slate-400 mt-0.5">
                                                {{ \Carbon\Carbon::parse($lkh->lkhdate)->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                    <span
                                        class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-amber-50 text-amber-600 border border-amber-200 whitespace-nowrap tracking-wide">
                                        LEVEL {{ $lkh->approval_level }}
                                    </span>
                                </div>
                                <div class="px-5 py-3 border-t border-slate-100 grid grid-cols-4 gap-5 text-sm">
                                    <div>
                                        <p
                                            class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">
                                            Mandor</p>
                                        <p class="font-medium text-slate-800">{{ $lkh->mandor_nama ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">
                                            Hasil</p>
                                        <p class="font-medium text-slate-800">{{ number_format($lkh->totalhasil, 2) }}
                                            <span class="text-slate-400 text-xs">ha</span>
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">
                                            Pekerja</p>
                                        <p class="font-medium text-slate-800">{{ $lkh->totalworkers }} <span
                                                class="text-slate-400 text-xs">org</span></p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">
                                            Activity</p>
                                        <p class="text-slate-600">{{ $lkh->activityname ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="px-5 py-3 bg-slate-50/70 border-t border-slate-100 flex gap-2">
                                    <form action="{{ route('approval.lkh.process') }}" method="POST"
                                        class="flex-1">
                                        @csrf
                                        <input type="hidden" name="lkhno" value="{{ $lkh->lkhno }}">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="level" value="{{ $lkh->approval_level }}">
                                        <button type="submit"
                                            onclick="return confirm('Approve LKH {{ $lkh->lkhno }}?')"
                                            class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm shadow-emerald-200 active:scale-[0.98]">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('approval.lkh.process') }}" method="POST"
                                        class="flex-1">
                                        @csrf
                                        <input type="hidden" name="lkhno" value="{{ $lkh->lkhno }}">
                                        <input type="hidden" name="action" value="decline">
                                        <input type="hidden" name="level" value="{{ $lkh->approval_level }}">
                                        <button type="submit"
                                            onclick="return confirm('Decline LKH {{ $lkh->lkhno }}?')"
                                            class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-4 bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-lg border border-slate-200 transition-colors active:scale-[0.98]">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Decline
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ==================== ABSEN TAB ==================== --}}
            <div x-show="activeTab === 'absen'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                @if ($pendingAbsen->isEmpty())
                    <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor"
                                stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-sm text-slate-400 font-medium">Tidak ada absen yang perlu diapprove</p>
                        <p class="text-xs text-slate-300 mt-1">Semua sudah diproses</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($pendingAbsen as $absen)
                            <div
                                class="bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-slate-300 hover:shadow-sm transition-all duration-200">
                                <div class="px-5 py-3.5 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <a href="{{ route('report.absen.show', $absen->absenno) }}"
                                                class="font-semibold text-slate-900 hover:text-blue-600 text-sm transition-colors">{{ $absen->absenno }}</a>
                                            <p class="text-xs text-slate-400 mt-0.5">
                                                {{ \Carbon\Carbon::parse($absen->uploaddate)->format('d M Y H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                    <span
                                        class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-amber-50 text-amber-600 border border-amber-200 tracking-wide">PENDING</span>
                                </div>
                                <div class="px-5 py-3 border-t border-slate-100 grid grid-cols-2 gap-5 text-sm">
                                    <div>
                                        <p
                                            class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">
                                            Mandor</p>
                                        <p class="font-medium text-slate-800">{{ $absen->mandor_nama ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">
                                            Total Pekerja</p>
                                        <p class="font-medium text-slate-800">{{ $absen->totalpekerja }} <span
                                                class="text-slate-400 text-xs">org</span></p>
                                    </div>
                                </div>
                                <div class="px-5 py-3 bg-slate-50/70 border-t border-slate-100 flex gap-2">
                                    <a href="{{ route('report.absen.show', $absen->absenno) }}"
                                        class="inline-flex items-center justify-center gap-1.5 py-2 px-4 bg-slate-700 hover:bg-slate-800 text-white text-sm font-medium rounded-lg transition-colors active:scale-[0.98]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Detail
                                    </a>
                                    <form action="{{ route('approval.absen.process') }}" method="POST"
                                        class="flex-1">
                                        @csrf
                                        <input type="hidden" name="absenno" value="{{ $absen->absenno }}">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit"
                                            onclick="return confirm('Approve Absen {{ $absen->absenno }}?')"
                                            class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm shadow-emerald-200 active:scale-[0.98]">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('approval.absen.process') }}" method="POST"
                                        class="flex-1">
                                        @csrf
                                        <input type="hidden" name="absenno" value="{{ $absen->absenno }}">
                                        <input type="hidden" name="action" value="decline">
                                        <button type="submit"
                                            onclick="return confirm('Decline Absen {{ $absen->absenno }}?')"
                                            class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-4 bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-lg border border-slate-200 transition-colors active:scale-[0.98]">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Decline
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ==================== UPAH MINGGUAN TAB ==================== --}}
            <div x-show="activeTab === 'upah'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                @if ($pendingUpah->isEmpty())
                    <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor"
                                stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-sm text-slate-400 font-medium">Tidak ada Upah Mingguan yang perlu diapprove</p>
                        <p class="text-xs text-slate-300 mt-1">Semua sudah diproses</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($pendingUpah as $upah)
                            @php
                                $transnoListStr = implode(',', $upah->transno_list);
                                $mandorLabel = $upah->mandorname ?? '-';
                                $isMulti = $upah->transno_count > 1;
                            @endphp
                            <div
                                class="bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-slate-300 hover:shadow-sm transition-all duration-200">

                                {{-- Header --}}
                                <div class="px-5 py-3.5 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-emerald-500" fill="none"
                                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <button type="button"
                                                @click="openUpahDetailGroup('{{ $transnoListStr }}')"
                                                class="font-semibold text-slate-900 hover:text-blue-600 text-sm transition-colors text-left">
                                                {{ $mandorLabel }}
                                            </button>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                @if ($isMulti)
                                                    <span
                                                        class="text-xs font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 px-1.5 py-0.5 rounded">
                                                        {{ $upah->transno_count }} transaksi
                                                    </span>
                                                    <span class="text-slate-200">&middot;</span>
                                                @endif
                                                <span class="text-xs text-slate-400">
                                                    Periode:
                                                    {{ \Carbon\Carbon::parse($upah->startdate)->format('d M Y') }}
                                                    s/d {{ \Carbon\Carbon::parse($upah->enddate)->format('d M Y') }}
                                                </span>
                                                <span class="text-slate-200">&middot;</span>
                                                <span
                                                    class="text-[10px] font-semibold px-1.5 py-0.5 rounded
                                            {{ $upah->jenistenagakerja == 1
                                                ? 'bg-blue-50 text-blue-600 border border-blue-200'
                                                : 'bg-orange-50 text-orange-600 border border-orange-200' }}">
                                                    {{ $upah->jenis_label }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <span
                                        class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-amber-50 text-amber-600 border border-amber-200 whitespace-nowrap tracking-wide">
                                        LEVEL {{ $upah->approval_level }}
                                    </span>
                                </div>

                                {{-- Daftar Transaksi --}}
                                @if ($isMulti)
                                    <div class="px-5 py-2.5 border-t border-slate-100 bg-indigo-50/30">
                                        <p
                                            class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-2">
                                            Aktivitas</p>
                                        <div class="space-y-1">
                                            @foreach ($upah->transactions as $trx)
                                                <div class="flex items-center justify-between gap-2 text-xs">
                                                    <span
                                                        class="font-mono text-slate-500 shrink-0">{{ $trx->transno }}</span>
                                                    <span
                                                        class="text-slate-700 font-medium flex-1 truncate px-2">{{ $trx->activityname ?? '-' }}</span>
                                                    <span class="text-slate-400 shrink-0">
                                                        {{ \Carbon\Carbon::parse($trx->startdate)->format('d M') }}
                                                        –
                                                        {{ \Carbon\Carbon::parse($trx->enddate)->format('d M Y') }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    {{-- Body single transaksi --}}
                                    <div class="px-5 py-3 border-t border-slate-100 grid grid-cols-3 gap-5 text-sm">
                                        <div>
                                            <p
                                                class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">
                                                Aktivitas</p>
                                            <p class="font-medium text-slate-800">
                                                {{ $upah->transactions->first()->activityname ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p
                                                class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">
                                                Periode LKH</p>
                                            <p class="font-medium text-slate-800 text-xs">
                                                {{ \Carbon\Carbon::parse($upah->startdate)->format('d M Y') }}
                                                <span class="text-slate-400">s/d</span>
                                                {{ \Carbon\Carbon::parse($upah->enddate)->format('d M Y') }}
                                            </p>
                                        </div>
                                        <div>
                                            <p
                                                class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">
                                                {{ $upah->jenistenagakerja == 1 ? 'Jml. TKH' : 'Jml. Plot' }}
                                            </p>
                                            <p class="font-medium text-slate-800">
                                                {{ $upah->totalworkers }}
                                                <span
                                                    class="text-slate-400 text-xs">{{ $upah->jenistenagakerja == 1 ? 'org' : 'plot' }}</span>
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                {{-- Grand Total strip --}}
                                <div
                                    class="px-5 py-2.5 border-t border-slate-100 bg-slate-50/40 flex items-center justify-between">
                                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Grand
                                        Total
                                        @if ($isMulti)
                                            <span
                                                class="text-indigo-400 normal-case font-normal">({{ $upah->transno_count }}
                                                transaksi)</span>
                                        @endif
                                    </p>
                                    <p class="text-sm font-bold text-emerald-700">
                                        {{ \Illuminate\Support\Number::currency($upah->grandtotal, 'IDR', 'id') }}
                                    </p>
                                </div>

                                {{-- Actions --}}
                                <div class="px-5 py-3 bg-slate-50/70 border-t border-slate-100 flex gap-2">
                                    <form action="{{ route('approval.upah-mingguan.process-group') }}" method="POST"
                                        class="flex-1">
                                        @csrf
                                        @foreach ($upah->transno_list as $tn)
                                            <input type="hidden" name="transno_list[]" value="{{ $tn }}">
                                        @endforeach
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="level" value="{{ $upah->approval_level }}">
                                        <button type="submit"
                                            onclick="return confirm('Approve {{ $isMulti ? $upah->transno_count . ' transaksi' : 'transaksi' }} upah mingguan mandor {{ $mandorLabel }}?')"
                                            class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm shadow-emerald-200 active:scale-[0.98]">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                            Approve{{ $isMulti ? ' (' . $upah->transno_count . ')' : '' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('approval.upah-mingguan.process-group') }}" method="POST"
                                        class="flex-1">
                                        @csrf
                                        @foreach ($upah->transno_list as $tn)
                                            <input type="hidden" name="transno_list[]" value="{{ $tn }}">
                                        @endforeach
                                        <input type="hidden" name="action" value="decline">
                                        <input type="hidden" name="level" value="{{ $upah->approval_level }}">
                                        <button type="submit"
                                            onclick="return confirm('Decline {{ $isMulti ? $upah->transno_count . ' transaksi' : 'transaksi' }} upah mingguan mandor {{ $mandorLabel }}?')"
                                            class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-4 bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-lg border border-slate-200 transition-colors active:scale-[0.98]">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Decline
                                        </button>
                                    </form>
                                    <button type="button" @click="openUpahDetailGroup('{{ $transnoListStr }}')"
                                        class="inline-flex items-center justify-center gap-1.5 py-2 px-3 bg-white hover:bg-slate-50 text-slate-500 hover:text-slate-700 text-sm font-medium rounded-lg border border-slate-200 transition-colors active:scale-[0.98]"
                                        title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                </div>

                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ==================== BBM TAB ==================== --}}
            <div x-show="activeTab === 'bbm'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                @if ($pendingBBM->isEmpty())
                    <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor"
                                stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-sm text-slate-400 font-medium">Tidak ada Order BBM yang perlu diapprove</p>
                        <p class="text-xs text-slate-300 mt-1">Semua sudah diproses</p>
                    </div>
                @else
                    {{-- Sub-tab filter --}}
                    @php
                        $bbmPermintaan = $pendingBBM->where('approval_type', 'PERMINTAAN');
                        $bbmPengeluaran = $pendingBBM->where('approval_type', 'PENGELUARAN');
                    @endphp
                    <div class="flex items-center gap-2 mb-4" x-data="{ bbmSub: 'all' }">
                        <button @click="bbmSub = 'all'"
                            :class="bbmSub === 'all' ? 'bg-slate-700 text-white' :
                                'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors">
                            Semua <span class="ml-1 opacity-70">({{ $pendingBBM->count() }})</span>
                        </button>
                        @if ($bbmPermintaan->isNotEmpty())
                            <button @click="bbmSub = 'permintaan'"
                                :class="bbmSub === 'permintaan' ? 'bg-blue-600 text-white' :
                                    'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                                class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors">
                                Permintaan BBM <span class="ml-1 opacity-70">({{ $bbmPermintaan->count() }})</span>
                            </button>
                        @endif
                        @if ($bbmPengeluaran->isNotEmpty())
                            <button @click="bbmSub = 'pengeluaran'"
                                :class="bbmSub === 'pengeluaran' ? 'bg-emerald-600 text-white' :
                                    'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                                class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors">
                                Pengeluaran BBM <span class="ml-1 opacity-70">({{ $bbmPengeluaran->count() }})</span>
                            </button>
                        @endif

                        <div class="space-y-3 mt-1 w-full">
                            @foreach ($pendingBBM as $bbm)
                                @php
                                    $isPermintaan = ($bbm->approval_type ?? 'PERMINTAAN') === 'PERMINTAAN';
                                @endphp
                                <div x-show="bbmSub === 'all' || bbmSub === '{{ $isPermintaan ? 'permintaan' : 'pengeluaran' }}'"
                                    class="bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-slate-300 hover:shadow-sm transition-all duration-200">

                                    {{-- Header --}}
                                    <div class="px-5 py-3.5 flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2.5">
                                            <div
                                                class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                                            {{ $isPermintaan ? 'bg-blue-50' : 'bg-emerald-50' }}">
                                                @if ($isPermintaan)
                                                    <svg class="w-4 h-4 text-blue-500" fill="none"
                                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 text-emerald-500" fill="none"
                                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                    </svg>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ route('transaction.order-bbm.show', $bbm->orderno) }}"
                                                        class="font-semibold text-slate-900 hover:text-blue-600 text-sm transition-colors">
                                                        Order #{{ $bbm->orderno }}
                                                    </a>
                                                    <span
                                                        class="text-[10px] font-bold px-2 py-0.5 rounded-full border
                                                    {{ $isPermintaan
                                                        ? 'bg-blue-50 text-blue-600 border-blue-200'
                                                        : 'bg-emerald-50 text-emerald-600 border-emerald-200' }}">
                                                        {{ $isPermintaan ? 'PERMINTAAN' : 'PENGELUARAN' }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span
                                                        class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($bbm->orderdate)->format('d M Y') }}</span>
                                                    <span class="text-slate-200">&middot;</span>
                                                    <span
                                                        class="text-[10px] font-semibold px-1.5 py-0.5 rounded
                                                    {{ $bbm->sourcetype === 'LKH' ? 'bg-purple-50 text-purple-600 border border-purple-200' : 'bg-teal-50 text-teal-600 border border-teal-200' }}">
                                                        {{ $bbm->sourcetype }}
                                                    </span>
                                                    <span
                                                        class="text-xs text-slate-400 font-mono">{{ $bbm->sourceno }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span
                                            class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-amber-50 text-amber-600 border border-amber-200 whitespace-nowrap tracking-wide">
                                            LEVEL {{ $bbm->approval_level ?? 1 }}
                                        </span>
                                    </div>

                                    {{-- Body --}}
                                    <div
                                        class="px-5 py-3 border-t border-slate-100 grid {{ $isPermintaan ? 'grid-cols-3' : 'grid-cols-4' }} gap-5 text-sm">
                                        <div>
                                            <p
                                                class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">
                                                Kendaraan</p>
                                            <p class="font-medium text-slate-800">{{ $bbm->jumlahkendaraan }} <span
                                                    class="text-slate-400 text-xs">unit</span></p>
                                            @if ($bbm->daftarkendaraan)
                                                <p class="text-xs text-slate-400 mt-0.5 truncate"
                                                    title="{{ $bbm->daftarkendaraan }}">{{ $bbm->daftarkendaraan }}
                                                </p>
                                            @endif
                                        </div>
                                        <div>
                                            <p
                                                class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">
                                                Solar Diminta</p>
                                            <p class="font-medium text-slate-800">
                                                {{ number_format($bbm->totalsolarrequested, 2) }} <span
                                                    class="text-slate-400 text-xs">L</span></p>
                                        </div>
                                        @if (!$isPermintaan)
                                            <div>
                                                <p
                                                    class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">
                                                    Solar Real</p>
                                                <p class="font-bold text-emerald-700">
                                                    {{ number_format($bbm->totalsolarreal ?? 0, 2) }} <span
                                                        class="text-emerald-500 text-xs font-medium">L</span></p>
                                                @if ($bbm->totalsolarreal && $bbm->totalsolarrequested > 0)
                                                    @php $diff = $bbm->totalsolarrequested - $bbm->totalsolarreal; @endphp
                                                    @if ($diff > 0)
                                                        <p class="text-[10px] text-slate-400 mt-0.5">Selisih:
                                                            -{{ number_format($diff, 2) }} L</p>
                                                    @endif
                                                @endif
                                            </div>
                                        @endif
                                        <div>
                                            <p
                                                class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1">
                                                {{ $isPermintaan ? 'Dibuat Oleh' : 'Dikonfirmasi Oleh' }}
                                            </p>
                                            <p class="font-medium text-slate-800">
                                                {{ $isPermintaan ? $bbm->inputby ?? '-' : $bbm->gudangconfirmedby ?? '-' }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="px-5 py-3 bg-slate-50/70 border-t border-slate-100 flex gap-2">
                                        <form action="{{ route('approval.order-bbm.process') }}" method="POST"
                                            class="flex-1">
                                            @csrf
                                            <input type="hidden" name="orderno" value="{{ $bbm->orderno }}">
                                            <input type="hidden" name="approval_type"
                                                value="{{ $bbm->approval_type ?? 'PERMINTAAN' }}">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit"
                                                onclick="return confirm('Approve {{ $isPermintaan ? 'Permintaan' : 'Pengeluaran' }} BBM #{{ $bbm->orderno }}?')"
                                                class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm shadow-emerald-200 active:scale-[0.98]">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                                Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('approval.order-bbm.process') }}" method="POST"
                                            class="flex-1">
                                            @csrf
                                            <input type="hidden" name="orderno" value="{{ $bbm->orderno }}">
                                            <input type="hidden" name="approval_type"
                                                value="{{ $bbm->approval_type ?? 'PERMINTAAN' }}">
                                            <input type="hidden" name="action" value="decline">
                                            <button type="submit"
                                                onclick="return confirm('Decline {{ $isPermintaan ? 'Permintaan' : 'Pengeluaran' }} BBM #{{ $bbm->orderno }}?')"
                                                class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-4 bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-lg border border-slate-200 transition-colors active:scale-[0.98]">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Decline
                                            </button>
                                        </form>
                                        <a href="{{ route('transaction.order-bbm.show', $bbm->orderno) }}"
                                            class="inline-flex items-center justify-center gap-1.5 py-2 px-3 bg-white hover:bg-slate-50 text-slate-500 hover:text-slate-700 text-sm font-medium rounded-lg border border-slate-200 transition-colors active:scale-[0.98]"
                                            title="Lihat Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- ==================== OTHER TAB ==================== --}}
            <div x-show="activeTab === 'other'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                @if ($pendingOther->isEmpty())
                    <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor"
                                stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-sm text-slate-400 font-medium">Tidak ada approval lainnya</p>
                        <p class="text-xs text-slate-300 mt-1">Semua sudah diproses</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($pendingOther as $approval)
                            <div
                                class="bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-slate-300 hover:shadow-sm transition-all duration-200">
                                {{-- Header --}}
                                <div class="px-5 py-3.5">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2.5">
                                            <div
                                                class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-4 h-4 text-slate-500" fill="none"
                                                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <a href="{{ route('transaction.gudang.detail', ['rkhno' => $approval->transactionnumber]) }}"
                                                    class="font-semibold text-slate-900 hover:text-blue-600 text-sm transition-colors">{{ $approval->transactionnumber }}</a>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span
                                                        class="text-xs text-slate-400">{{ $approval->formatted_date ?? '-' }}</span>
                                                    <span class="text-slate-200">&middot;</span>
                                                    <span
                                                        class="text-xs text-slate-400">{{ $approval->category }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span
                                                class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-amber-50 text-amber-600 border border-amber-200 whitespace-nowrap tracking-wide">
                                                LEVEL {{ $approval->approval_level }}
                                            </span>
                                            @if ($approval->transactiontype)
                                                <span
                                                    class="text-[11px] font-semibold px-2.5 py-1 rounded-full whitespace-nowrap tracking-wide
                                        {{ $approval->transactiontype === 'SPLIT' ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200' }}">
                                                    {{ $approval->transactiontype }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Use Material / Koreksi detail table --}}
                                    @if (isset($otherDetail[$approval->approvalno]) &&
                                            in_array($approval->category, ['Use Material', 'Use Koreksi', 'Retur Koreksi']))
                                        @php $firstDetail = $otherDetail[$approval->approvalno][0]; @endphp
                                        <div class="mt-3 overflow-x-auto rounded-lg border border-slate-100">
                                            <table class="w-full text-xs">
                                                <thead>
                                                    <tr class="bg-slate-50 text-slate-500">
                                                        <td class="px-3 py-2 font-medium" colspan="2">
                                                            Luas: <span
                                                                class="font-semibold text-slate-700">{{ $firstDetail->totalluas }}
                                                                HA</span>
                                                            <span class="mx-2 text-slate-200">|</span>
                                                            Mandor: <span
                                                                class="font-semibold text-slate-700">{{ $firstDetail->name }}</span>
                                                        </td>
                                                    </tr>
                                                    <tr
                                                        class="bg-slate-50/60 text-[10px] text-slate-400 uppercase tracking-wider">
                                                        <td class="px-3 py-1.5 w-20 font-medium">Plot</td>
                                                        <td class="px-3 py-1.5 font-medium">Before → After</td>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-50">
                                                    @foreach ($otherDetail[$approval->approvalno] as $item)
                                                        @if ($item->old_qty - $item->new_qty != 0)
                                                            <tr class="hover:bg-slate-50/50 transition-colors">
                                                                <td class="px-3 py-2 align-top">
                                                                    <span
                                                                        class="font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">{{ $item->plot }}</span>
                                                                </td>
                                                                <td class="px-3 py-2 align-top">
                                                                    <span
                                                                        class="text-slate-700">{{ $item->old_itemcode }}</span>
                                                                    <span
                                                                        class="text-slate-400 mx-1">{{ $item->old_qty }}
                                                                        {{ $item->old_measure }}</span>
                                                                    <span class="text-slate-300 mx-1.5">→</span>
                                                                    <span
                                                                        class="text-slate-700">{{ $item->new_itemcode }}</span>
                                                                    <span class="text-slate-400 mx-1">
                                                                        @if ($item->type == 'USE')
                                                                            {{ $item->old_qty + $item->new_qty }}
                                                                        @elseif($item->type == 'RTR')
                                                                            —
                                                                        @else
                                                                            {{ $item->new_qty }}
                                                                        @endif
                                                                        {{ $item->new_measure }}
                                                                    </span>
                                                                    @if ($item->type == 'USE')
                                                                        <span class="text-slate-300 text-[10px]">(req:
                                                                            {{ $item->new_qty }}, exist:
                                                                            {{ $item->old_qty }})</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>

                                {{-- Split / Merge / Rework body --}}
                                @if ($approval->transactiontype)
                                    <div class="px-5 py-3 border-t border-slate-100 text-sm">
                                        <div class="flex items-center gap-4">
                                            <div class="flex-1">
                                                <p
                                                    class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1.5">
                                                    {{ $approval->transactiontype === 'SPLIT' ? 'Plot Asal' : 'Plot Merge' }}
                                                </p>
                                                <div class="flex flex-wrap gap-1.5">
                                                    @foreach ($approval->sourceplots_array ?? [] as $plot)
                                                        <span
                                                            class="px-2 py-1 bg-slate-100 text-slate-700 rounded-md text-xs font-medium">
                                                            {{ $plot }}@if (isset($approval->real_batch_areas[$plot]))
                                                                <span
                                                                    class="text-slate-400 font-normal">({{ number_format($approval->real_batch_areas[$plot], 2) }})</span>
                                                            @endif
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <svg class="w-5 h-5 text-slate-300" fill="none"
                                                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p
                                                    class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-1.5">
                                                    Plot Hasil</p>
                                                <div class="flex flex-wrap gap-1.5">
                                                    @foreach ($approval->resultplots_array ?? [] as $plot)
                                                        <span
                                                            class="px-2 py-1 bg-purple-50 text-purple-700 rounded-md text-xs font-semibold border border-purple-100">
                                                            {{ $plot }}@if (isset($approval->areamap_array[$plot]))
                                                                <span
                                                                    class="text-purple-400 font-normal">({{ number_format($approval->areamap_array[$plot], 2) }})</span>
                                                            @endif
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div
                                        class="px-5 py-3 border-t border-slate-100 text-sm text-slate-500 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                        </svg>
                                        Open Rework Request
                                    </div>
                                @endif

                                {{-- Actions --}}
                                <div class="px-5 py-3 bg-slate-50/70 border-t border-slate-100 flex gap-2">
                                    <form action="{{ route('approval.other.process') }}" method="POST"
                                        class="flex-1">
                                        @csrf
                                        <input type="hidden" name="approvalno"
                                            value="{{ $approval->approvalno }}">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="level"
                                            value="{{ $approval->approval_level }}">
                                        <button type="submit"
                                            onclick="return confirm('Approve {{ $approval->category }}?')"
                                            class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm shadow-emerald-200 active:scale-[0.98]">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                            Approve
                                        </button>
                                    </form>
                                    @if ($approval->category !== 'Use Material')
                                        <form action="{{ route('approval.other.process') }}" method="POST"
                                            class="flex-1">
                                            @csrf
                                            <input type="hidden" name="approvalno"
                                                value="{{ $approval->approvalno }}">
                                            <input type="hidden" name="action" value="decline">
                                            <input type="hidden" name="level"
                                                value="{{ $approval->approval_level }}">
                                            <button type="submit"
                                                onclick="return confirm('Decline {{ $approval->category }}?')"
                                                class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-4 bg-white hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-lg border border-slate-200 transition-colors active:scale-[0.98]">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Decline
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        @include('approval.upahmingguan.detail')
    </div>

    <script>
        const upahDetailGroupUrl = '{{ route('approval.upah-mingguan.detail-group') }}';

        function approvalData() {
            return {
                activeTab: '{{ $pendingRKH->count() > 0 ? 'rkh' : ($pendingLKH->count() > 0 ? 'lkh' : ($pendingAbsen->count() > 0 ? 'absen' : ($pendingUpah->count() > 0 ? 'upah' : ($pendingBBM->count() > 0 ? 'bbm' : ($pendingOther->count() > 0 ? 'other' : 'rkh'))))) }}',
                rkhCount: {{ $pendingRKH->count() }},
                lkhCount: {{ $pendingLKH->count() }},
                absenCount: {{ $pendingAbsen->count() }},
                upah: {{ $pendingUpah->count() }},
                bbmCount: {{ $pendingBBM->count() }},
                otherCount: {{ $pendingOther->count() }},
                allDateChecked: {{ $allDate ? 'true' : 'false' }},

                upahModal: {
                    open: false,
                    loading: false,
                    error: null,
                    transno_list: null,
                    data: null,
                },

                openUpahDetailGroup(transnoListStr) {
                    this.upahModal.open = true;
                    this.upahModal.loading = true;
                    this.upahModal.error = null;
                    this.upahModal.data = null;
                    this.upahModal.transno_list = transnoListStr;

                    const url = upahDetailGroupUrl + '?transno_list=' + encodeURIComponent(transnoListStr);

                    fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(r => r.json())
                        .then(json => {
                            if (json.success) {
                                this.upahModal.data = json;
                            } else {
                                this.upahModal.error = json.message || 'Gagal memuat data';
                            }
                        })
                        .catch(() => {
                            this.upahModal.error = 'Terjadi kesalahan jaringan';
                        })
                        .finally(() => {
                            this.upahModal.loading = false;
                        });
                },
            };
        }
    </script>
</x-layout>
