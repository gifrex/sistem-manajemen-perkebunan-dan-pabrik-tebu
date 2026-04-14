{{-- resources/views/report/absen/index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Laporan Absensi</h1>
                        <p class="text-sm text-gray-600 mt-1">Daftar kehadiran pekerja</p>
                    </div>
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

        <!-- Filters -->
        <div class="max-w-7xl mx-auto px-6 py-6">
            <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
                <form action="{{ route('report.absen.index') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Date From -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Dari</label>
                            <input type="date" 
                                   name="date_from" 
                                   value="{{ $filters['date_from'] }}"
                                   class="w-full text-sm border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Date To -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Sampai</label>
                            <input type="date" 
                                   name="date_to" 
                                   value="{{ $filters['date_to'] }}"
                                   class="w-full text-sm border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Mandor -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mandor</label>
                            <select name="mandor" 
                                    class="w-full text-sm border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Mandor</option>
                                @foreach($mandorList as $mandor)
                                <option value="{{ $mandor->mandorid }}" {{ $filters['mandor'] == $mandor->mandorid ? 'selected' : '' }}>
                                    {{ $mandor->mandor_nama }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status Approval</label>
                            <select name="status" 
                                    class="w-full text-sm border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Status</option>
                                <option value="1" {{ $filters['status'] === '1' ? 'selected' : '' }}>Approved</option>
                                <option value="0" {{ $filters['status'] === '0' ? 'selected' : '' }}>Rejected</option>
                                <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 text-sm font-medium rounded-md transition-colors">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Filter
                        </button>
                        <a href="{{ route('report.absen.index') }}" 
                           class="bg-white hover:bg-gray-50 text-gray-700 px-6 py-2 text-sm font-medium rounded-md border border-gray-300 transition-colors">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Absen No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Upload</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mandor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pekerja</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Approval</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($absenList as $absen)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $absen->absenno }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($absen->uploaddate)->format('d M Y H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $absen->mandor_nama ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $absen->totalpekerja }} orang</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($absen->approvalstatus === '1')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Approved
                                    </span>
                                    @elseif($absen->approvalstatus === '0')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Rejected
                                    </span>
                                    @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $absen->approvaldate ? \Carbon\Carbon::parse($absen->approvaldate)->format('d M Y H:i') : '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('report.absen.show', $absen->absenno) }}" 
                                       class="text-blue-600 hover:text-blue-900 font-medium">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-gray-500 font-medium">Tidak ada data absen</p>
                                    <p class="text-sm text-gray-400 mt-1">Coba ubah filter pencarian</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($absenList->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $absenList->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>