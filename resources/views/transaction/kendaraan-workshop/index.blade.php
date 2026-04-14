{{-- resources/views/transaction/kendaraan-workshop/index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

<div x-data="kendaraanData()" class="relative">
    <div class="mx-auto bg-white rounded-md shadow-md p-6">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Input Data Kendaraan</h1>
                <p class="text-gray-600 mt-1">Kelola data hourmeter dan solar kendaraan</p>
            </div>
            
            {{-- Search & Filter --}}
            <div class="flex items-center space-x-2 mt-4 md:mt-0">
                <form id="filterForm" class="flex items-center space-x-2" action="{{ route('transaction.kendaraan-workshop.index') }}" method="GET">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari LKH No atau Kendaraan..."
                           class="text-sm border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500"/>
                    
                    {{--  NEW: Date Filter with Toggle --}}
                    <input type="date" 
                           id="filter_date"
                           name="filter_date" 
                           value="{{ $filterDate ?? '' }}" 
                           {{ $showAllDate ? 'disabled' : '' }}
                           class="text-sm border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"/>
                    
                    {{--  NEW: Show All Date Checkbox --}}
                    <label class="flex items-center text-sm space-x-1 cursor-pointer">
                        <input type="checkbox" 
                               id="show_all_date"
                               name="show_all_date" 
                               value="1"
                               onchange="toggleDateFilter()"
                               {{ $showAllDate ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"/>
                        <span class="text-gray-700 whitespace-nowrap">Show All Date</span>
                    </label>
                    
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded">
                        Cari
                    </button>
                </form>
            </div>
        </div>

        {{-- Stats Summary - Minimalist --}}
        <div class="bg-white p-4 rounded-lg shadow-sm border mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div class="border-r border-gray-200 last:border-r-0">
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600">Total</div>
                </div>
                <div class="border-r border-gray-200 last:border-r-0">
                    <div class="text-2xl font-bold text-orange-600">{{ $stats['pending'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600">Belum Input</div>
                </div>
                <div class="border-r border-gray-200 last:border-r-0">
                    <div class="text-2xl font-bold text-green-600">{{ $stats['completed'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600">Sudah Input</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['printed'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600">Sudah Print</div>
                </div>
            </div>
        </div>

        {{-- Main Table --}}
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full table-auto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">LKH No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kendaraan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operator</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plot</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Kerja</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">HM Start/End</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solar (L)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($kendaraanData as $index => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 text-sm font-medium">
                            <a href="{{ route('transaction.rencanakerjaharian.showLKH', $item->lkhno) }}" 
                            class="text-blue-600 hover:text-blue-800 hover:underline font-medium"
                            target="_blank">
                                {{ $item->lkhno }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            @if($item->ordernumber)
                                <span class="font-mono text-green-600 font-medium">#{{ $item->ordernumber }}</span>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($item->lkh_date)->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div>
                                <div class="font-medium">{{ $item->activityname ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $item->activitycode }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div>
                                <div class="font-medium">{{ $item->nokendaraan }}</div>
                                <div class="text-xs text-gray-500">{{ $item->jenis }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ $item->operator_nama ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded">
                                {{ $item->plots }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div class="text-xs">
                                <div>{{ substr($item->jammulai, 0, 5) }} - {{ substr($item->jamselesai, 0, 5) }}</div>
                                <div class="text-gray-500">{{ $item->work_duration }} jam</div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            @if($item->hourmeterstart && $item->hourmeterend)
                                <div class="text-xs">
                                    <div><strong>Start:</strong> {{ number_format($item->hourmeterstart, 1) }}</div>
                                    <div><strong>End:</strong> {{ number_format($item->hourmeterend, 1) }}</div>
                                    <div class="text-green-600"><strong>Diff:</strong> {{ number_format($item->hourmeterend - $item->hourmeterstart, 1) }}</div>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">Belum diinput</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            @if($item->solar)
                                <div class="text-sm font-medium text-blue-600">{{ number_format($item->solar, 2) }} L</div>
                            @else
                                <span class="text-xs text-gray-400">Belum diinput</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($item->status === 'PRINTED')
                                <span class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded">Printed</span>
                            @elseif($item->status === 'INPUTTED')
                                <span class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded">Sudah Input</span>
                            @else
                                <span class="text-xs px-2 py-1 bg-yellow-100 text-yellow-700 rounded">Belum Input</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($item->status === 'PRINTED')
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('transaction.kendaraan-workshop.print', $item->lkhno) }}" target="_blank" 
                                       class="text-gray-600 hover:text-gray-800 p-1" title="Lihat/Print Ulang">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                </div>
                            @elseif($item->status === 'INPUTTED')
                                <div class="flex items-center space-x-2">
                                    <button @click="openEditModal(@js($item))" 
                                            class="text-blue-600 hover:text-blue-800 p-1" title="Edit Data">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button @click="printOrder('{{ $item->lkhno }}')" 
                                            class="text-gray-700 hover:text-gray-900 p-1" title="Print Order BBM">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <button @click="openInputModal(@js($item))" 
                                        class="bg-blue-600 text-white px-3 py-1 text-sm rounded hover:bg-blue-700">Input Data</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="px-4 py-8 text-center text-gray-500">
                            <div>Tidak ada data kendaraan yang perlu diinput</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if(method_exists($kendaraanData, 'links'))
            <div class="bg-white px-4 py-3 border-t border-gray-200">
                {{ $kendaraanData->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Input Modal --}}
    <div x-show="showInputModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>
            <div class="relative bg-white rounded-lg max-w-md w-full mx-auto shadow-xl">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Input Data Kendaraan</h3>
                    <p class="text-sm text-gray-600" x-text="selectedItem?.nokendaraan + ' - ' + selectedItem?.lkhno"></p>
                </div>
                
                <form @submit.prevent="submitInput">
                    <div class="px-6 py-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hour Meter Start</label>
                            <input type="number" step="0.1" x-model="formData.hourmeterstart" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Contoh: 1200.5"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hour Meter End</label>
                            <input type="number" step="0.1" x-model="formData.hourmeterend" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Contoh: 1208.5"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Solar (Liter)</label>
                            <input type="number" step="0.01" x-model="formData.solar" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Contoh: 25.50"/>
                        </div>
                        <div x-show="formData.hourmeterend && formData.hourmeterstart" class="p-3 bg-blue-50 rounded">
                            <div class="text-sm text-blue-800">
                                <strong>Selisih HM:</strong> <span x-text="(formData.hourmeterend - formData.hourmeterstart).toFixed(1)"></span> jam
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 border-t bg-gray-50 flex justify-end space-x-2">
                        <button type="button" @click="closeModal" class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="isLoading" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="isLoading">Menyimpan...</span>
                            <span x-show="!isLoading">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>
            <div class="relative bg-white rounded-lg max-w-md w-full mx-auto shadow-xl">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Edit Data Kendaraan</h3>
                    <p class="text-sm text-gray-600" x-text="selectedItem?.nokendaraan + ' - ' + selectedItem?.lkhno"></p>
                </div>
                
                <form @submit.prevent="submitEdit">
                    <div class="px-6 py-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hour Meter Start</label>
                            <input type="number" step="0.1" x-model="formData.hourmeterstart" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hour Meter End</label>
                            <input type="number" step="0.1" x-model="formData.hourmeterend" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Solar (Liter)</label>
                            <input type="number" step="0.01" x-model="formData.solar" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"/>
                        </div>
                        <div x-show="formData.hourmeterend && formData.hourmeterstart" class="p-3 bg-blue-50 rounded">
                            <div class="text-sm text-blue-800">
                                <strong>Selisih HM:</strong> <span x-text="(formData.hourmeterend - formData.hourmeterstart).toFixed(1)"></span> jam
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 border-t bg-gray-50 flex justify-end space-x-2">
                        <button type="button" @click="closeModal" class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="isLoading" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="isLoading">Mengupdate...</span>
                            <span x-show="!isLoading">Update</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
//  NEW: Toggle date filter function
function toggleDateFilter() {
    const dateInput = document.getElementById('filter_date');
    const checkbox = document.getElementById('show_all_date');
    dateInput.disabled = checkbox.checked;
    
    // Auto-submit form when toggling
    document.getElementById('filterForm').submit();
}

function kendaraanData() {
    return {
        showInputModal: false,
        showEditModal: false,
        isLoading: false,
        selectedItem: null,
        formData: {
            id: '',
            lkhno: '',
            plot: '',
            nokendaraan: '',
            hourmeterstart: '',
            hourmeterend: '',
            solar: ''
        },

        openInputModal(item) {
            this.selectedItem = item;
            this.formData = {
                id: item.id,
                lkhno: item.lkhno,
                plot: item.plot,
                nokendaraan: item.nokendaraan,
                hourmeterstart: '',
                hourmeterend: '',
                solar: ''
            };
            this.showInputModal = true;
        },

        openEditModal(item) {
            this.selectedItem = item;
            this.formData = {
                id: item.id,
                lkhno: item.lkhno,
                plot: item.plot,
                nokendaraan: item.nokendaraan,
                hourmeterstart: item.hourmeterstart || '',
                hourmeterend: item.hourmeterend || '',
                solar: item.solar || ''
            };
            this.showEditModal = true;
        },

        closeModal() {
            this.showInputModal = false;
            this.showEditModal = false;
            this.selectedItem = null;
            this.formData = {
                lkhno: '',
                plot: '',
                nokendaraan: '',
                hourmeterstart: '',
                hourmeterend: '',
                solar: ''
            };
        },

        async printOrder(lkhno) {
            try {
                const response = await fetch(`{{ url('transaction/kendaraan-workshop') }}/${lkhno}/mark-printed`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                if (data.success) {
                    window.open(`{{ url('transaction/kendaraan-workshop') }}/${lkhno}/print?ordernumber=${data.order_number}`, '_blank');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('Gagal generate order: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memproses order');
            }
        },

        async submitInput() {
            if (!this.validateForm()) return;

            this.isLoading = true;
            try {
                const response = await fetch('{{ route("transaction.kendaraan-workshop.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.formData)
                });

                const data = await response.json();
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Gagal menyimpan: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan data');
            } finally {
                this.isLoading = false;
            }
        },

        async submitEdit() {
            if (!this.validateForm()) return;

            this.isLoading = true;
            try {
                const response = await fetch('{{ route("transaction.kendaraan-workshop.update") }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.formData)
                });

                const data = await response.json();
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Gagal mengupdate: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengupdate data');
            } finally {
                this.isLoading = false;
            }
        },

        validateForm() {
            if (!this.formData.hourmeterstart || !this.formData.hourmeterend || !this.formData.solar) {
                alert('Semua field harus diisi');
                return false;
            }
            
            if (parseFloat(this.formData.hourmeterend) <= parseFloat(this.formData.hourmeterstart)) {
                alert('Hour Meter End harus lebih besar dari Hour Meter Start');
                return false;
            }
            
            if (parseFloat(this.formData.solar) <= 0) {
                alert('Solar harus lebih besar dari 0');
                return false;
            }
            
            return true;
        }
    };
}
</script>
</x-layout>