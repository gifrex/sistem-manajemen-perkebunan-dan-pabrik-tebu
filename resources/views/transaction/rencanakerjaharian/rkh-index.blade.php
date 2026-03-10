{{--resources\views\transaction\rencanakerjaharian\rkh-index.blade.php--}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div x-data="mainData()" class="relative">
        <div class="mx-auto bg-white rounded-md shadow-md p-6">
            {{-- Search & Action Buttons --}}
            <div class="flex flex-col md:flex-row justify-between mb-4">
                <div class="flex flex-wrap gap-2 items-center">
                    <form class="flex items-center space-x-2" action="{{ route('transaction.rencanakerjaharian.index') }}" method="GET">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search No RKH..."
                            class="text-xs border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500"/>
                        <input type="hidden" name="filter_approval" value="{{ $filterApproval }}">
                        <input type="hidden" name="filter_status" value="{{ $filterStatus }}">
                        <input type="hidden" name="filter_date" value="{{ $filterDate }}">
                        <input type="hidden" name="all_date" value="{{ $allDate }}">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-xs rounded">Search</button>
                    </form>
                </div>

                <div class="mt-2 md:mt-0">
                    <button @click="showDateModal = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-xs rounded">Create RKH</button>
                </div>
            </div>

            {{-- Filters --}}
            <form action="{{ route('transaction.rencanakerjaharian.index') }}" method="GET" id="filterForm">
                <input type="hidden" name="search" value="{{ $search }}">
                
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2">
                        <select name="filter_approval" onchange="document.getElementById('filterForm').submit()"
                                class="text-xs border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Approval</option>
                            <option value="Approved" {{ $filterApproval == 'Approved' ? 'selected' : '' }}>Approved</option>
                            <option value="Waiting" {{ $filterApproval == 'Waiting' ? 'selected' : '' }}>Waiting</option>
                            <option value="Decline" {{ $filterApproval == 'Decline' ? 'selected' : '' }}>Decline</option>
                        </select>

                        <select name="filter_status" onchange="document.getElementById('filterForm').submit()"
                                class="text-xs border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="Completed" {{ $filterStatus == 'Completed' ? 'selected' : '' }}>Completed</option>
                            <option value="In Progress" {{ $filterStatus == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="Batal" {{ $filterStatus == 'Batal' ? 'selected' : '' }}>Batal</option>
                        </select>

                        <input type="date" name="filter_date" value="{{ $filterDate }}" onchange="document.getElementById('filterForm').submit()"
                            class="text-xs border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500"
                            {{ $allDate ? 'disabled' : '' }}/>

                        <label class="flex items-center text-xs space-x-1">
                            <input type="checkbox" name="all_date" value="1" onchange="toggleDateFilter(); document.getElementById('filterForm').submit();"
                                {{ $allDate ? 'checked' : '' }}/>
                            <span>Show All Date</span>
                        </label>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="button" @click="showAbsenModal = true; loadAbsenData(absenDate)"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-xs rounded">Check Data Absen</button>
                        <button type="button" @click="showGenerateDTHModal = true"
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 text-xs rounded">Generate DTH</button>
                        <button type="button" @click="showGenerateRekapLKHModal = true"
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 text-xs rounded">Daily Reports</button>
                    </div>
                </div>
            </form>

            {{-- Main Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-xs">
                            <th class="border px-2 py-1">No.</th>
                            <th class="border px-2 py-1">No RKH</th>
                            <th class="border px-2 py-1">Tanggal</th>
                            <th class="border px-2 py-1">Mandor</th>
                            <th class="border px-2 py-1">Approval</th>
                            <th class="border px-2 py-1">LKH</th>
                            <th class="border px-2 py-1">Status</th>
                            <th class="border px-2 py-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rkhData as $index => $rkh)
                        <tr class="text-xs">
                            <td class="border px-2 py-1">{{ $rkhData->firstItem() + $index }}</td>
                            <td class="border px-2 py-1">
                                <a href="{{ route('transaction.rencanakerjaharian.show', $rkh->rkhno) }}" 
                                class="text-blue-600 hover:text-blue-800 hover:underline font-medium">{{ $rkh->rkhno }}</a>
                            </td>
                            <td class="border px-2 py-1">{{ Carbon\Carbon::parse($rkh->rkhdate)->format('d/m/Y') }}</td>
                            <td class="border px-2 py-1">{{ $rkh->mandor_nama ?? '-' }}</td>

                            {{-- APPROVAL COLUMN --}}
                            <td class="border px-2 py-1 text-center">
                                @if($rkh->approval_status == 'Approved')
                                    <button @click="showRkhApprovalInfoModal = true; selectedRkhno = '{{ $rkh->rkhno }}'; loadRkhApprovalDetail('{{ $rkh->rkhno }}')"
                                            class="px-2 py-0.5 text-xs font-semibold text-green-800 bg-green-100 rounded hover:bg-green-200 cursor-pointer">
                                        Approved
                                    </button>
                                @elseif($rkh->approval_status == 'No Approval Required')
                                    <span class="px-2 py-0.5 text-xs font-semibold text-blue-800 bg-blue-100 rounded">No Approval Required</span>
                                @elseif(str_contains($rkh->approval_status, 'Declined'))
                                    <button @click="showRkhApprovalInfoModal = true; selectedRkhno = '{{ $rkh->rkhno }}'; loadRkhApprovalDetail('{{ $rkh->rkhno }}')"
                                            class="px-2 py-0.5 text-xs font-semibold text-red-800 bg-red-100 rounded hover:bg-red-200 cursor-pointer">
                                        {{ $rkh->approval_status }}
                                    </button>
                                @else
                                    @php
                                        $total = $rkh->jumlahapproval ?? 0;
                                        $completed = 0;
                                        if($rkh->approval1flag == '1') $completed++;
                                        if($rkh->approval2flag == '1') $completed++;
                                        if($rkh->approval3flag == '1') $completed++;
                                        $waitingText = $total == 0 ? "Waiting for Approval" : "Waiting for Approval ({$completed}/{$total})";
                                    @endphp
                                    <button @click="showRkhApprovalInfoModal = true; selectedRkhno = '{{ $rkh->rkhno }}'; loadRkhApprovalDetail('{{ $rkh->rkhno }}')"
                                            class="px-2 py-0.5 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded hover:bg-yellow-200 cursor-pointer">
                                        {{ $waitingText }}
                                    </button>
                                @endif
                            </td>

                            {{-- LKH BUTTON WITH PROGRESS STATUS --}}
                            <td class="border px-2 py-1 text-center">
                                @if($rkh->current_status == 'Batal')
                                    <button @click="showLKHModal = true; selectedRkhno = '{{ $rkh->rkhno }}'; loadLKHData('{{ $rkh->rkhno }}')"
                                            class="px-2 py-0.5 text-xs font-semibold text-red-800 bg-red-100 rounded hover:bg-red-200 transition-colors">
                                        LKH Dibatalkan
                                    </button>
                                @else
                                    {{-- Normal LKH Progress --}}
                                    <button @click="showLKHModal = true; selectedRkhno = '{{ $rkh->rkhno }}'; loadLKHData('{{ $rkh->rkhno }}')"
                                            class="px-2 py-0.5 text-xs font-semibold rounded transition-colors
                                                @if($rkh->lkh_progress_status['color'] === 'green')
                                                    bg-green-100 text-green-700 hover:bg-green-200
                                                @elseif($rkh->lkh_progress_status['color'] === 'yellow')
                                                    bg-yellow-100 text-yellow-700 hover:bg-yellow-200
                                                @else
                                                    bg-gray-100 text-gray-700 hover:bg-gray-200
                                                @endif">
                                        @if($rkh->lkh_progress_status['color'] === 'green')
                                            LKH
                                        @else
                                            {{ $rkh->lkh_progress_status['progress'] }}
                                        @endif
                                    </button>
                                @endif
                            </td>

                            {{-- STATUS BUTTON WITH LKH COMPLETION CHECK --}}
                            <td class="border px-2 py-1 text-center">
                                @if($rkh->current_status == 'Batal')
                                    <button @click="showBatalInfoModal = true; selectedRkhno = '{{ $rkh->rkhno }}'; loadBatalDetail('{{ $rkh->rkhno }}')"
                                            class="px-2 py-0.5 text-xs font-semibold text-red-800 bg-red-100 rounded hover:bg-red-200 cursor-pointer">
                                        Batal
                                    </button>
                                @elseif($rkh->current_status == 'Completed')
                                    <span class="px-2 py-0.5 text-xs font-semibold text-green-800 bg-green-100 rounded">Completed</span>
                                @else
                                    <button onclick="updateStatus('{{ $rkh->rkhno }}')"
                                            @if(!$rkh->lkh_progress_status['can_complete']) disabled @endif
                                            class="px-2 py-0.5 text-xs rounded font-semibold transition-colors
                                                @if($rkh->lkh_progress_status['can_complete'])
                                                    bg-yellow-100 hover:bg-yellow-200 text-yellow-800 cursor-pointer
                                                @else
                                                    bg-gray-200 text-gray-500 cursor-not-allowed
                                                @endif"
                                            @if(!$rkh->lkh_progress_status['can_complete'])
                                                title="Semua LKH harus selesai terlebih dahulu"
                                            @endif>
                                        In Progress
                                    </button>
                                @endif
                            </td>

                            {{-- ACTIONS COLUMN --}}
                            <td class="border px-2 py-1">
                                <div class="flex items-center justify-center space-x-2">
                                    @if($rkh->current_status == 'Batal')
                                        <span class="text-xs text-gray-400 italic">No Action Available</span>
                                        
                                    @elseif($rkh->current_status == 'Completed')
                                        <span class="text-xs text-gray-400 italic">No Action Available</span>
                                        
                                    @elseif($rkh->approvalstatus == '1' && $rkh->non_empty_lkh_count == 0)
                                        <button @click="showCancelModal = true; cancelRkhno = '{{ $rkh->rkhno }}'; cancelAlasan = ''"
                                                class="text-red-600 hover:text-red-800 px-2 py-1 group" title="Batalkan RKH">
                                            <svg class="w-5 h-5 text-red-600 group-hover:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <use href="#icon-cancel-outline"/>
                                            </svg>
                                            <svg class="w-5 h-5 text-red-600 hidden group-hover:block" fill="currentColor" viewBox="0 0 24 24">
                                                <use href="#icon-cancel-solid"/>
                                            </svg>
                                        </button>
                                        <div class="text-gray-400 px-2 py-1 cursor-not-allowed" title="Tidak dapat diedit (sudah approved)">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <use href="#icon-edit-outline"/>
                                            </svg>
                                        </div>
                                        
                                    @elseif($rkh->approvalstatus == '1' && $rkh->non_empty_lkh_count > 0)
                                        <div class="text-gray-400 px-2 py-1 cursor-not-allowed" title="Tidak dapat dibatalkan (LKH sudah dikerjakan)">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <use href="#icon-cancel-outline"/>
                                            </svg>
                                        </div>
                                        <div class="text-gray-400 px-2 py-1 cursor-not-allowed" title="Tidak dapat diedit (sudah approved)">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <use href="#icon-edit-outline"/>
                                            </svg>
                                        </div>
                                        
                                    @elseif($rkh->approvalstatus == '0')
                                        <button onclick="window.location.href='{{ route('transaction.rencanakerjaharian.edit', $rkh->rkhno) }}'"
                                                class="text-blue-600 hover:text-blue-800 px-2 py-1 group" title="Edit RKH (Submit Ulang)">
                                            <svg class="w-5 h-5 text-blue-600 group-hover:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <use href="#icon-edit-outline"/>
                                            </svg>
                                            <svg class="w-5 h-5 text-blue-600 hidden group-hover:block" fill="currentColor" viewBox="0 0 24 24">
                                                <use href="#icon-edit-solid"/>
                                            </svg>
                                        </button>
                                        <button onclick="deleteRKH('{{ $rkh->rkhno }}')"
                                                class="text-red-600 hover:text-red-800 px-2 py-1 group" title="Hapus RKH">
                                            <svg class="w-5 h-5 text-red-600 group-hover:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <use href="#icon-trash-outline"/>
                                            </svg>
                                            <svg class="w-5 h-5 text-red-600 hidden group-hover:block" fill="currentColor" viewBox="0 0 24 24">
                                                <use href="#icon-trash-solid"/>
                                            </svg>
                                        </button>
                                        
                                    @else
                                        @if($rkh->approval1flag == '1' || $rkh->approval2flag == '1' || $rkh->approval3flag == '1')
                                            <div class="text-gray-400 px-2 py-1 cursor-not-allowed" title="Tidak dapat diedit (sudah ada approval)">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <use href="#icon-edit-outline"/>
                                                </svg>
                                            </div>
                                            <div class="text-gray-400 px-2 py-1 cursor-not-allowed" title="Tidak dapat dihapus (sudah ada approval)">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <use href="#icon-trash-outline"/>
                                                </svg>
                                            </div>
                                        @elseif($rkh->approval1flag == '0' || $rkh->approval2flag == '0' || $rkh->approval3flag == '0')
                                            <button onclick="window.location.href='{{ route('transaction.rencanakerjaharian.edit', $rkh->rkhno) }}'"
                                                    class="text-blue-600 hover:text-blue-800 px-2 py-1 group" title="Edit RKH (Submit Ulang)">
                                                <svg class="w-5 h-5 text-blue-600 group-hover:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <use href="#icon-edit-outline"/>
                                                </svg>
                                                <svg class="w-5 h-5 text-blue-600 hidden group-hover:block" fill="currentColor" viewBox="0 0 24 24">
                                                    <use href="#icon-edit-solid"/>
                                                </svg>
                                            </button>
                                            <button onclick="deleteRKH('{{ $rkh->rkhno }}')"
                                                    class="text-red-600 hover:text-red-800 px-2 py-1 group" title="Hapus RKH">
                                                <svg class="w-5 h-5 text-red-600 group-hover:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <use href="#icon-trash-outline"/>
                                                </svg>
                                                <svg class="w-5 h-5 text-red-600 hidden group-hover:block" fill="currentColor" viewBox="0 0 24 24">
                                                    <use href="#icon-trash-solid"/>
                                                </svg>
                                            </button>
                                        @else
                                            <button onclick="window.location.href='{{ route('transaction.rencanakerjaharian.edit', $rkh->rkhno) }}'"
                                                    class="text-blue-600 hover:text-blue-800 px-2 py-1 group" title="Edit RKH">
                                                <svg class="w-5 h-5 text-blue-600 group-hover:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <use href="#icon-edit-outline"/>
                                                </svg>
                                                <svg class="w-5 h-5 text-blue-600 hidden group-hover:block" fill="currentColor" viewBox="0 0 24 24">
                                                    <use href="#icon-edit-solid"/>
                                                </svg>
                                            </button>
                                            <button onclick="deleteRKH('{{ $rkh->rkhno }}')"
                                                    class="text-red-600 hover:text-red-800 px-2 py-1 group" title="Hapus RKH">
                                                <svg class="w-5 h-5 text-red-600 group-hover:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <use href="#icon-trash-outline"/>
                                                </svg>
                                                <svg class="w-5 h-5 text-red-600 hidden group-hover:block" fill="currentColor" viewBox="0 0 24 24">
                                                    <use href="#icon-trash-solid"/>
                                                </svg>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="border px-2 py-4 text-center text-gray-500">Tidak ada data RKH ditemukan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
                @if($rkhData->hasPages())
                <div class="mt-4">{{ $rkhData->appends(request()->query())->links() }}</div>
                @endif
            </div>

            {{-- Include All Modals --}}
            @include('transaction.rencanakerjaharian.modal-index.index-modal-create-rkh')
            @include('transaction.rencanakerjaharian.modal-index.index-modal-lkh-list')
            @include('transaction.rencanakerjaharian.modal-index.index-modal-absen')
            @include('transaction.rencanakerjaharian.modal-index.index-modal-dth')
            @include('transaction.rencanakerjaharian.modal-index.index-modal-rekap')
            @include('transaction.rencanakerjaharian.modal-index.index-modal-rkh-approval-info')
            @include('transaction.rencanakerjaharian.modal-index.index-modal-lkh-approval-info')
            @include('transaction.rencanakerjaharian.modal-index.index-modal-batal')
        </div>
    </div>

    <script>
    function toggleDateFilter() {
        const dateInput = document.querySelector('input[name="filter_date"]');
        const checkbox = document.querySelector('input[name="all_date"]');
        dateInput.disabled = checkbox.checked;
    }

    // Enhanced validation for LKH completion check
    function updateStatus(rkhno) {
        const button = event.target.closest('button');
        if (button.disabled || button.classList.contains('cursor-not-allowed')) {
            alert('Semua LKH harus diselesaikan dan diapprove terlebih dahulu sebelum RKH dapat ditandai sebagai Completed');
            return;
        }

        if (!confirm('Apakah anda yakin ingin menandai RKH ini sebagai Completed? Pastikan semua LKH sudah approved.')) return;

        fetch('{{ route("transaction.rencanakerjaharian.updateStatus") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ rkhno: rkhno, status: 'Completed' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal mengupdate status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengupdate status');
        });
    }

    function deleteRKH(rkhno) {
        if (!confirm('Apakah anda yakin ingin menghapus RKH ini?')) return;

        fetch('{{ route("transaction.rencanakerjaharian.destroy", ":rkhno") }}'.replace(':rkhno', rkhno), {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal menghapus RKH: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus RKH');
        });
    }

    window.currentUser = {
        userid: '{{ Auth::user()->userid ?? '' }}',
        name: '{{ Auth::user()->name ?? '' }}',
        idjabatan: {{ Auth::user()->idjabatan ?? 'null' }}
    }

    function mainData() {
        return {
            // Modal states
            showLKHModal: false,
            showAbsenModal: false,
            showGenerateDTHModal: false,
            showGenerateRekapLKHModal: false,
            showDateModal: false,
            showRkhApprovalInfoModal: false,
            showLkhApprovalInfoModal: false,
            showOutstandingModal: false,
            showCancelModal: false,
            showBatalInfoModal: false,

            // Loading states
            isRkhInfoLoading: false,
            isLkhInfoLoading: false,
            isLkhModalLoading: false,
            isAbsenLoading: false,
            isCancelling: false,
            isBatalInfoLoading: false,

            // Data
            selectedRkhno: '',
            selectedLkhno: '',
            lkhData: [],
            absenDate: '{{ request('filter_date', date('Y-m-d')) }}',
            absenList: @json($absentenagakerja ?? []),
            selectedMandor: '',
            mandorList: [],
            createDate: new Date().toISOString().split('T')[0],
            dthDate: '{{ request('filter_date', date('Y-m-d')) }}',
            rekapLkhDate: '{{ request('filter_date', date('Y-m-d')) }}',
            
            // Cancel RKH states
            cancelRkhno: '',
            cancelAlasan: '',
            
            // Batal Info states
            batalDetail: null,
            
            // Outstanding RKH details
            outstandingDetails: {
                rkhno: '',
                rkhdate: '',
                status: '',
                mandor_name: '',
                mandor_id: ''
            },
            
            selectedReportType: '',
            selectedOperatorId: '',
            availableOperators: [],
            isLoadingOperators: false,

            rkhApprovalDetail: {},
            lkhApprovalDetail: {},
            
            get today() {
                return new Date().toISOString().split('T')[0];
            },
            
            get maxDate() {
                const date = new Date();
                date.setDate(date.getDate() + 7);
                return date.toISOString().split('T')[0];
            },

            get canGenerateReport() {
                if (!this.rekapLkhDate || !this.selectedReportType) return false;
                if (this.selectedReportType === 'operator_rekap') return true;
                if (this.selectedReportType === 'operator' && !this.selectedOperatorId) return false;
                return true;
            },

            // Initialize watchers
            init() {
                window.addEventListener('show-outstanding-error', (event) => {
                    this.outstandingDetails = event.detail;
                    this.showOutstandingModal = true;
                });

                this.$watch('selectedReportType', (newValue) => {
                    if (newValue === 'operator' && this.rekapLkhDate) {
                        this.loadOperatorsForDate();
                    } else if (newValue !== 'operator') {
                        this.selectedOperatorId = '';
                        this.availableOperators = [];
                    }
                });
            },

            // Create RKH
            proceedToCreate() {
                if (!this.createDate) {
                    alert('Silakan pilih tanggal terlebih dahulu');
                    return;
                }
                Alpine.store('loading').start();
                window.location.href = `{{ route('transaction.rencanakerjaharian.create') }}?date=${this.createDate}`;
            },

            // Reset report modal
            resetReportModal() {
                this.selectedReportType = '';
                this.selectedOperatorId = '';
                this.availableOperators = [];
                this.isLoadingOperators = false;
            },

            // Load operators for selected date
            async loadOperatorsForDate() {
                if (!this.rekapLkhDate) {
                    this.availableOperators = [];
                    return;
                }

                if (this.selectedReportType === 'operator') {
                    this.isLoadingOperators = true;
                    try {
                        const response = await fetch(`{{ route('transaction.rencanakerjaharian.getOperatorsForDate') }}?date=${this.rekapLkhDate}`);
                        const data = await response.json();
                        
                        if (data.success) {
                            this.availableOperators = data.operators || [];
                            this.selectedOperatorId = '';
                        } else {
                            alert('Gagal memuat data operator: ' + data.message);
                            this.availableOperators = [];
                        }
                    } catch (error) {
                        console.error('Error loading operators:', error);
                        alert('Terjadi kesalahan saat memuat data operator');
                        this.availableOperators = [];
                    } finally {
                        this.isLoadingOperators = false;
                    }
                }
            },

            // Generate selected report
            async generateSelectedReport() {
                if (!this.canGenerateReport) return;

                try {
                    let url, payload;
                    
                    if (this.selectedReportType === 'rekap') {
                        url = '{{ route("transaction.rencanakerjaharian.generateRekapLKH") }}';
                        payload = { date: this.rekapLkhDate };
                    } else if (this.selectedReportType === 'operator_rekap') {
                        url = '{{ route("transaction.rencanakerjaharian.generateOperatorRekapReport") }}';
                        payload = { date: this.rekapLkhDate };
                    } else if (this.selectedReportType === 'operator') {
                        url = '{{ route("transaction.rencanakerjaharian.generateOperatorReport") }}';
                        payload = { 
                            date: this.rekapLkhDate,
                            operator_id: this.selectedOperatorId 
                        };
                    } else {
                        alert('Pilih jenis laporan terlebih dahulu');
                        return;
                    }

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.showGenerateRekapLKHModal = false;
                        this.resetReportModal();
                        window.open(data.redirect_url, '_blank');
                    } else {
                        alert('Gagal generate laporan: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error generating report:', error);
                    alert('Terjadi kesalahan saat generate laporan');
                }
            },

            // LKH Modal Methods
            async loadLKHData(rkhno) {
                this.isLkhModalLoading = true;
                this.lkhData = [];
                
                try {
                    const response = await fetch(`{{ url('transaction/kerjaharian/rencanakerjaharian') }}/${rkhno}/lkh`);
                    const data = await response.json();
                    
                    if (data.success) {
                        this.lkhData = (data.lkh_data || []).map(lkh => ({
                            lkhno: lkh.lkhno || '',
                            activityname: lkh.activityname || 'Unknown Activity',
                            activitycode: lkh.activitycode || '',
                            plots: lkh.plots || 'No plots',
                            jenis_tenaga: lkh.jenistenagakerja == 1 ? 'Harian' : 'Borongan',
                            status: lkh.status || 'EMPTY',
                            approval_status: lkh.approval_status || 'No Approval Required',
                            workers_assigned: lkh.workers_assigned || 0,
                            totalhasil: lkh.totalhasil || 0,
                            totalsisa: lkh.totalsisa || 0,
                            totalupah: lkh.totalupah || 0,
                            material_count: lkh.material_count || 0,
                            issubmit: lkh.issubmit || false,
                            can_submit: lkh.can_submit || false,
                            can_edit: lkh.can_edit || false,
                            view_url: lkh.view_url || '#',
                            edit_url: lkh.edit_url || '#'
                        }));
                    } else {
                        alert('Gagal memuat data LKH: ' + data.message);
                        this.lkhData = [];
                    }
                } catch (error) {
                    console.error('LKH Loading Error:', error);
                    alert('Terjadi kesalahan saat memuat data LKH');
                    this.lkhData = [];
                } finally {
                    this.isLkhModalLoading = false;
                }
            },

            async submitLKH(lkhno) {
                if (!confirm('Apakah Anda yakin ingin mengirim LKH ini untuk approval?')) return;

                try {
                    const response = await fetch('{{ route("transaction.rencanakerjaharian.submitLKH") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ lkhno: lkhno })
                    });

                    const data = await response.json();
                    if (data.success) {
                        alert(data.message);
                        await this.loadLKHData(this.selectedRkhno);
                    } else {
                        alert('Gagal mengirim LKH: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengirim LKH');
                }
            },

            // Absen Methods
            async loadAbsenData(date, mandorId = '') {
                this.isAbsenLoading = true;
                try {
                    const response = await fetch(`{{ route('transaction.rencanakerjaharian.loadAbsenByDate') }}?date=${date}&mandor_id=${mandorId}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        this.absenList = data.data || [];
                        this.mandorList = data.mandor_list || [];
                    } else {
                        alert('Gagal memuat data absen: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memuat data absen');
                } finally {
                    this.isAbsenLoading = false;
                }
            },

            // Generate DTH
            async generateDTH() {
                if (!this.dthDate) {
                    alert('Silakan pilih tanggal terlebih dahulu');
                    return;
                }

                try {
                    const response = await fetch('{{ route("transaction.rencanakerjaharian.generateDTH") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ date: this.dthDate })
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.showGenerateDTHModal = false;
                        window.location.href = data.redirect_url;
                    } else {
                        alert('Gagal generate DTH: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat generate DTH');
                }
            },

            // Approval Detail Methods
            async loadRkhApprovalDetail(rkhno) {
                this.isRkhInfoLoading = true;
                try {
                    const response = await fetch(`{{ route("transaction.rencanakerjaharian.getApprovalDetail", ":rkhno") }}`.replace(':rkhno', rkhno));
                    const data = await response.json();
                    
                    if (data.success) {
                        this.rkhApprovalDetail = data.data;
                    } else {
                        alert('Gagal memuat detail approval: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memuat detail approval');
                } finally {
                    this.isRkhInfoLoading = false;
                }
            },

            async loadLkhApprovalDetail(lkhno) {
                this.isLkhInfoLoading = true;
                try {
                    const response = await fetch(`{{ route("transaction.rencanakerjaharian.getLkhApprovalDetail", ":lkhno") }}`.replace(':lkhno', lkhno));
                    const data = await response.json();
                    
                    if (data.success) {
                        this.lkhApprovalDetail = data.data;
                    } else {
                        alert('Gagal memuat detail approval LKH: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memuat detail approval LKH');
                } finally {
                    this.isLkhInfoLoading = false;
                }
            },

            async submitCancelRkh() {
                if (this.cancelAlasan.length < 10 || this.isCancelling) return;
                
                if (!confirm('Apakah Anda yakin ingin membatalkan RKH ini? Tindakan ini tidak dapat dibatalkan.')) {
                    return;
                }
                
                this.isCancelling = true;
                
                try {
                    const response = await fetch(`{{ url('transaction/kerjaharian/rencanakerjaharian') }}/${this.cancelRkhno}/cancel`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            alasan: this.cancelAlasan
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Gagal membatalkan RKH: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat membatalkan RKH');
                } finally {
                    this.isCancelling = false;
                }
            },

            async loadBatalDetail(rkhno) {
                this.isBatalInfoLoading = true;
                this.batalDetail = null;
                
                try {
                    const response = await fetch(`{{ url('transaction/kerjaharian/rencanakerjaharian') }}/${rkhno}/batal-detail`);
                    const data = await response.json();
                    
                    if (data.success) {
                        this.batalDetail = data.data;
                    } else {
                        alert('Gagal memuat detail pembatalan: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memuat detail pembatalan');
                } finally {
                    this.isBatalInfoLoading = false;
                }
            },
        };
    }

    // Initialize date filter on page load
    document.addEventListener('DOMContentLoaded', function() {
        const filterDate = document.querySelector('input[name="filter_date"]');
        if (!filterDate.value && !document.querySelector('input[name="all_date"]').checked) {
            filterDate.value = '{{ date("Y-m-d") }}';
        }
    });
    </script>
</x-layout>