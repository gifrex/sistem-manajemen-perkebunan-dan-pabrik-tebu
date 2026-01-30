{{-- resources/views/approval/index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="min-h-screen bg-gray-50" x-data="approvalData()">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Approval Center</h1>
                        <p class="text-sm text-gray-600 mt-1">{{ $userInfo['jabatan_name'] }}</p>
                    </div>

                    <!-- Date Filter -->
                    <form action="{{ route('approval.index') }}" method="GET" id="filterForm" class="flex items-center gap-3">
                        <input type="date"
                               name="filter_date"
                               value="{{ $filterDate }}"
                               @change="document.getElementById('filterForm').submit()"
                               :disabled="allDateChecked"
                               class="text-sm border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100">

                        <label class="flex items-center text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox"
                                   name="all_date"
                                   value="1"
                                   x-model="allDateChecked"
                                   @change="document.getElementById('filterForm').submit()"
                                   {{ $allDate ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                            All Time
                        </label>

                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded-md transition-colors">
                            Apply
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="max-w-7xl mx-auto px-6 mt-4">
            <div class="bg-green-50 border-l-4 border-green-400 p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="ml-3 text-sm text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="max-w-7xl mx-auto px-6 mt-4">
            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="ml-3 text-sm text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Main Content with Sidebar -->
        <div class="max-w-7xl mx-auto px-6 py-6">
            <div class="flex gap-6">
                <!-- Sidebar Navigation -->
                <div class="w-64 flex-shrink-0">
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                        <nav class="p-2 space-y-1">
                            <!-- RKH -->
                            <button @click="activeTab = 'rkh'"
                                    :class="activeTab === 'rkh' ? 'bg-blue-50 text-blue-700 border-blue-600' : 'text-gray-700 hover:bg-gray-50 border-transparent'"
                                    class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-md border-l-4 transition-colors">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    RKH Approval
                                </span>
                                <span x-show="rkhCount > 0"
                                      class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-600"
                                      x-text="rkhCount"></span>
                            </button>

                            <!-- LKH -->
                            <button @click="activeTab = 'lkh'"
                                    :class="activeTab === 'lkh' ? 'bg-blue-50 text-blue-700 border-blue-600' : 'text-gray-700 hover:bg-gray-50 border-transparent'"
                                    class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-md border-l-4 transition-colors">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    LKH Approval
                                </span>
                                <span x-show="lkhCount > 0"
                                      class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-600"
                                      x-text="lkhCount"></span>
                            </button>

                            <!-- Absen -->
                            <button @click="activeTab = 'absen'"
                                    :class="activeTab === 'absen' ? 'bg-blue-50 text-blue-700 border-blue-600' : 'text-gray-700 hover:bg-gray-50 border-transparent'"
                                    class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-md border-l-4 transition-colors">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                    Absen Approval
                                </span>
                                <span x-show="absenCount > 0"
                                      class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-600"
                                      x-text="absenCount"></span>
                            </button>

                            <!-- Divider -->
                            <div class="border-t border-gray-200 my-2"></div>

                            <!-- Other Approvals -->
                            <button @click="activeTab = 'other'"
                                    :class="activeTab === 'other' ? 'bg-blue-50 text-blue-700 border-blue-600' : 'text-gray-700 hover:bg-gray-50 border-transparent'"
                                    class="w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-md border-l-4 transition-colors">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                    </svg>
                                    Lainnya
                                </span>
                                <span x-show="otherCount > 0"
                                      class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-600"
                                      x-text="otherCount"></span>
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="flex-1 min-w-0">
                    <!-- RKH Tab -->
                    <div x-show="activeTab === 'rkh'">
                        @if($pendingRKH->isEmpty())
                        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-gray-500 font-medium">Tidak ada RKH yang perlu diapprove</p>
                        </div>
                        @else
                        <div class="space-y-4">
                            @foreach($pendingRKH as $rkh)
                            <div class="bg-white rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                                <!-- Card Header -->
                                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <a href="{{ route('transaction.rencanakerjaharian.show', $rkh->rkhno) }}"
                                               target="_blank"
                                               class="text-lg font-semibold text-blue-600 hover:text-blue-700">
                                                {{ $rkh->rkhno }}
                                            </a>
                                            <p class="text-sm text-gray-600 mt-1">
                                                {{ \Carbon\Carbon::parse($rkh->rkhdate)->format('d M Y') }}
                                            </p>
                                        </div>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Level {{ $rkh->approval_level }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="px-6 py-4 space-y-3">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <span class="text-xs text-gray-500 uppercase tracking-wide">Mandor</span>
                                            <p class="text-sm font-medium text-gray-900 mt-1">{{ $rkh->mandor_nama ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500 uppercase tracking-wide">Luas Area</span>
                                            <p class="text-sm font-medium text-gray-900 mt-1">{{ number_format($rkh->totalluas, 2) }} ha</p>
                                        </div>
                                    </div>

                                    <div>
                                        <span class="text-xs text-gray-500 uppercase tracking-wide">Activities</span>
                                        <p class="text-sm text-gray-700 mt-1">{{ $rkh->activities_list }}</p>
                                    </div>
                                </div>

                                <!-- Card Footer -->
                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex gap-3">
                                    <form action="{{ route('approval.rkh.process') }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="rkhno" value="{{ $rkh->rkhno }}">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="level" value="{{ $rkh->approval_level }}">
                                        <button type="submit"
                                                onclick="return confirm('Approve RKH {{ $rkh->rkhno }}?')"
                                                class="w-full py-2 px-4 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors">
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('approval.rkh.process') }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="rkhno" value="{{ $rkh->rkhno }}">
                                        <input type="hidden" name="action" value="decline">
                                        <input type="hidden" name="level" value="{{ $rkh->approval_level }}">
                                        <button type="submit"
                                                onclick="return confirm('Decline RKH {{ $rkh->rkhno }}?')"
                                                class="w-full py-2 px-4 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-md border border-gray-300 transition-colors">
                                            Decline
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <!-- LKH Tab -->
                    <div x-show="activeTab === 'lkh'">
                        @if($pendingLKH->isEmpty())
                        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-gray-500 font-medium">Tidak ada LKH yang perlu diapprove</p>
                        </div>
                        @else
                        <div class="space-y-4">
                            @foreach($pendingLKH as $lkh)
                            <div class="bg-white rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <a href="{{ route('transaction.rencanakerjaharian.showLKH', $lkh->lkhno) }}"
                                               target="_blank"
                                               class="text-lg font-semibold text-blue-600 hover:text-blue-700">
                                                {{ $lkh->lkhno }}
                                            </a>
                                            <p class="text-sm text-gray-600 mt-1">
                                                {{ \Carbon\Carbon::parse($lkh->lkhdate)->format('d M Y') }}
                                            </p>
                                        </div>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Level {{ $lkh->approval_level }}
                                        </span>
                                    </div>
                                </div>

                                <div class="px-6 py-4 space-y-3">
                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <span class="text-xs text-gray-500 uppercase tracking-wide">Mandor</span>
                                            <p class="text-sm font-medium text-gray-900 mt-1">{{ $lkh->mandor_nama ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500 uppercase tracking-wide">Hasil</span>
                                            <p class="text-sm font-medium text-gray-900 mt-1">{{ number_format($lkh->totalhasil, 2) }} ha</p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500 uppercase tracking-wide">Pekerja</span>
                                            <p class="text-sm font-medium text-gray-900 mt-1">{{ $lkh->totalworkers }} orang</p>
                                        </div>
                                    </div>

                                    <div>
                                        <span class="text-xs text-gray-500 uppercase tracking-wide">Activity</span>
                                        <p class="text-sm text-gray-700 mt-1">{{ $lkh->activityname ?? '-' }}</p>
                                    </div>
                                </div>

                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex gap-3">
                                    <form action="{{ route('approval.lkh.process') }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="lkhno" value="{{ $lkh->lkhno }}">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="level" value="{{ $lkh->approval_level }}">
                                        <button type="submit"
                                                onclick="return confirm('Approve LKH {{ $lkh->lkhno }}?')"
                                                class="w-full py-2 px-4 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors">
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('approval.lkh.process') }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="lkhno" value="{{ $lkh->lkhno }}">
                                        <input type="hidden" name="action" value="decline">
                                        <input type="hidden" name="level" value="{{ $lkh->approval_level }}">
                                        <button type="submit"
                                                onclick="return confirm('Decline LKH {{ $lkh->lkhno }}?')"
                                                class="w-full py-2 px-4 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-md border border-gray-300 transition-colors">
                                            Decline
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <!-- Absen Tab -->
                    <div x-show="activeTab === 'absen'">
                        @if($pendingAbsen->isEmpty())
                        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-gray-500 font-medium">Tidak ada absen yang perlu diapprove</p>
                        </div>
                        @else
                        <div class="space-y-4">
                            @foreach($pendingAbsen as $absen)
                            <div class="bg-white rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <a href="{{ route('report.absen.show', $absen->absenno) }}"
                                            target="_blank"
                                            class="text-lg font-semibold text-blue-600 hover:text-blue-700 hover:underline">
                                                {{ $absen->absenno }}
                                            </a>
                                            <p class="text-sm text-gray-600 mt-1">
                                                {{ \Carbon\Carbon::parse($absen->uploaddate)->format('d M Y H:i') }}
                                            </p>
                                        </div>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    </div>
                                </div>

                                <div class="px-6 py-4 space-y-3">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <span class="text-xs text-gray-500 uppercase tracking-wide">Mandor</span>
                                            <p class="text-sm font-medium text-gray-900 mt-1">{{ $absen->mandor_nama ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500 uppercase tracking-wide">Total Pekerja</span>
                                            <p class="text-sm font-medium text-gray-900 mt-1">{{ $absen->totalpekerja }} orang</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex gap-3">
                                    <a href="{{ route('report.absen.show', $absen->absenno) }}"
                                    target="_blank"
                                    class="py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors text-center">
                                        Lihat Detail
                                    </a>
                                    <form action="{{ route('approval.absen.process') }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="absenno" value="{{ $absen->absenno }}">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit"
                                                onclick="return confirm('Approve Absen {{ $absen->absenno }}?')"
                                                class="w-full py-2 px-4 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors">
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('approval.absen.process') }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="absenno" value="{{ $absen->absenno }}">
                                        <input type="hidden" name="action" value="decline">
                                        <button type="submit"
                                                onclick="return confirm('Decline Absen {{ $absen->absenno }}?')"
                                                class="w-full py-2 px-4 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-md border border-gray-300 transition-colors">
                                            Decline
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <!-- Other Tab -->
                    <div x-show="activeTab === 'other'">
                        @if($pendingOther->isEmpty())
                        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-gray-500 font-medium">Tidak ada approval lainnya</p>
                        </div>
                        @else
                        <div class="space-y-4">
                            @foreach($pendingOther as $approval)
                            <div class="bg-white rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ $approval->transactionnumber }}</h3>
                                            <p class="text-sm text-gray-600 mt-1">
                                                {{ $approval->formatted_date ?? '-' }} • {{ $approval->category }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Level {{ $approval->approval_level }}
                                            </span>
                                            @if($approval->transactiontype)
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $approval->transactiontype === 'SPLIT' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $approval->transactiontype }}
                                            </span>
                                            @endif

                                        </div>
                                    </div>
                                    @if( isset($otherDetail[$approval->approvalno]) )
                                        @if( $approval->category == "Use Material" )
                                              <div class="text-sm font-medium text-gray-900 mt-1">
                                                <table width = "100%">
                                                  <tr>
                                                    <td colspan="2">BEFORE</td>
                                                    <td></td>
                                                    <td colspan="2">AFTER</td>
                                                  </tr>
                                                @foreach($otherDetail[$approval->approvalno] as $item)
                                                  @if( $item->old_qty - $item->new_qty != 0 )
                                                    <tr>
                                                      <td>{{ $item->old_itemcode }} <br>{{ $item->old_itemname }}</td>
                                                      <td>{{ $item->old_qty }} {{ $item->old_measure }}</td>
                                                      <td>-></td>
                                                      <td>{{ $item->new_itemcode }} <br>{{ $item->new_itemname }}</td>
                                                      <td>{{ $item->new_qty }} {{ $item->new_measure }}</td>
                                                    </tr>
                                                    @endif
                                                @endforeach
                                              </table>
                                              </div>
                                        @endif
                                    @endif
                                </div>

                                <div class="px-6 py-4">
                                    @if($approval->transactiontype)
                                        <div class="space-y-3">
                                            <div>
                                                <span class="text-xs text-gray-500 uppercase tracking-wide">{{ $approval->transactiontype === 'SPLIT' ? 'Plot Asal' : 'Plot Merge' }}</span>
                                                <div class="flex flex-wrap gap-2 mt-2">
                                                    @if(isset($approval->sourceplots_array))
                                                        @foreach($approval->sourceplots_array as $plot)
                                                            <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-medium">
                                                                {{ $plot }}
                                                                @if(isset($approval->real_batch_areas[$plot]))
                                                                    ({{ number_format($approval->real_batch_areas[$plot], 2) }} Ha)
                                                                @endif
                                                            </span>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="text-center">
                                                <svg class="w-5 h-5 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                                </svg>
                                            </div>

                                            <div>
                                                <span class="text-xs text-gray-500 uppercase tracking-wide">Plot Hasil</span>
                                                <div class="flex flex-wrap gap-2 mt-2">
                                                    @if(isset($approval->resultplots_array))
                                                        @foreach($approval->resultplots_array as $plot)
                                                            <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-semibold">
                                                                {{ $plot }}
                                                                @if(isset($approval->areamap_array[$plot]))
                                                                    ({{ number_format($approval->areamap_array[$plot], 2) }} Ha)
                                                                @endif
                                                            </span>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-600">
                                            <p>Open Rework Request</p>
                                        </div>
                                    @endif
                                </div>

                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex gap-3">
                                    <form action="{{ route('approval.other.process') }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="approvalno" value="{{ $approval->approvalno }}">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="level" value="{{ $approval->approval_level }}">
                                        <button type="submit"
                                                onclick="return confirm('Approve {{ $approval->category }}?')"
                                                class="w-full py-2 px-4 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors">
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('approval.other.process') }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="approvalno" value="{{ $approval->approvalno }}">
                                        <input type="hidden" name="action" value="decline">
                                        <input type="hidden" name="level" value="{{ $approval->approval_level }}">
                                        <button type="submit"
                                                onclick="return confirm('Decline {{ $approval->category }}?')"
                                                class="w-full py-2 px-4 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-md border border-gray-300 transition-colors">
                                            Decline
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function approvalData() {
            return {
                activeTab: 'rkh',
                rkhCount: {{ $pendingRKH->count() }},
                lkhCount: {{ $pendingLKH->count() }},
                absenCount: {{ $pendingAbsen->count() }},
                otherCount: {{ $pendingOther->count() }},
                allDateChecked: {{ $allDate ? 'true' : 'false' }}
            };
        }
    </script>
</x-layout>
