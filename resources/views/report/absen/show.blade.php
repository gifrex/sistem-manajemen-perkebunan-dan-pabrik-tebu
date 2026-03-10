{{-- resources/views/report/absen/show.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="min-h-screen bg-gray-50" x-data="{ photoModal: false, currentPhoto: null }">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <button onclick="history.back()"
                                class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                            </button>
                            <div>
                                <h1 class="text-2xl font-semibold text-gray-900">{{ $absen->absenno }}</h1>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ \Carbon\Carbon::parse($absen->uploaddate)->format('d F Y, H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Badge -->
                    <div>
                        @if($absen->approvalstatus === '1')
                        <span class="px-4 py-2 text-sm font-semibold rounded-md bg-green-100 text-green-800">
                            ✓ Approved
                        </span>
                        @elseif($absen->approvalstatus === '0')
                        <span class="px-4 py-2 text-sm font-semibold rounded-md bg-red-100 text-red-800">
                            ✗ Rejected
                        </span>
                        @else
                        <span class="px-4 py-2 text-sm font-semibold rounded-md bg-yellow-100 text-yellow-800">
                            ⏱ Pending
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 py-6">
            <!-- Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- Total Workers -->
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-500">Total Pekerja</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_workers'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- With Foto Masuk -->
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-500">Foto Masuk</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['with_foto_masuk'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Foto Approved -->
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-500">Foto Approved</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['foto_approved'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Foto Rejected -->
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-500">Foto Rejected</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['foto_rejected'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Absen Info -->
            <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Absen</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-gray-500">Mandor</span>
                        <p class="text-sm font-medium text-gray-900 mt-1">{{ $absen->mandor_nama ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Total Pekerja</span>
                        <p class="text-sm font-medium text-gray-900 mt-1">{{ $absen->totalpekerja }} orang</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Approved By</span>
                        <p class="text-sm font-medium text-gray-900 mt-1">{{ $absen->approval_user_nama ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Tanggal Approval</span>
                        <p class="text-sm font-medium text-gray-900 mt-1">
                            {{ $absen->approvaldate ? \Carbon\Carbon::parse($absen->approvaldate)->format('d M Y H:i') : '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Worker List -->
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Daftar Pekerja</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIK</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis Tenaga</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jam Masuk</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Foto Masuk</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Foto Pulang</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status Foto</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($workers as $index => $worker)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $worker->worker_nik ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $worker->worker_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $worker->jenis_tenagakerja_nama ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $worker->absenmasuk ? \Carbon\Carbon::parse($worker->absenmasuk)->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($worker->fotoabsenmasuk_url)
                                    <button @click="currentPhoto = '{{ $worker->fotoabsenmasuk_url }}'; photoModal = true"
                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Lihat Foto
                                    </button>
                                    @else
                                    <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($worker->fotoabsenpulang_url)
                                    <button @click="currentPhoto = '{{ $worker->fotoabsenpulang_url }}'; photoModal = true"
                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Lihat Foto
                                    </button>
                                    @else
                                    <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($worker->fotomasukapprovalstatus === '1')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Approved
                                    </span>
                                    @elseif($worker->fotomasukapprovalstatus === '0')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800" 
                                          title="{{ $worker->fotomasukapprovalreason }}">
                                        Rejected
                                    </span>
                                    @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                        -
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Photo Modal -->
        <div x-show="photoModal" 
            x-cloak
            x-data="{ zoom: 1 }"
            @click.self="photoModal = false; zoom = 1"
            @keydown.escape.window="photoModal = false; zoom = 1"
            @keydown.plus.window="zoom = Math.min(zoom + 0.25, 3)"
            @keydown.minus.window="zoom = Math.max(zoom - 0.25, 0.5)"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4">
            
            <!-- Close Button -->
            <button @click="photoModal = false; zoom = 1" 
                    class="absolute top-4 right-4 text-white hover:text-gray-300 z-20 bg-black bg-opacity-50 rounded-full p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            
            <!-- Zoom Controls -->
            <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 z-20 flex items-center gap-3 bg-black bg-opacity-50 rounded-full px-4 py-2">
                <button @click="zoom = Math.max(zoom - 0.25, 0.5)" 
                        class="text-white hover:text-gray-300 p-1"
                        :class="{ 'opacity-50 cursor-not-allowed': zoom <= 0.5 }">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"></path>
                    </svg>
                </button>
                <span class="text-white text-sm font-medium min-w-[60px] text-center" x-text="Math.round(zoom * 100) + '%'"></span>
                <button @click="zoom = Math.min(zoom + 0.25, 3)" 
                        class="text-white hover:text-gray-300 p-1"
                        :class="{ 'opacity-50 cursor-not-allowed': zoom >= 3 }">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path>
                    </svg>
                </button>
                <div class="w-px h-6 bg-white bg-opacity-30 mx-1"></div>
                <button @click="zoom = 1" 
                        class="text-white hover:text-gray-300 text-sm font-medium px-2">
                    Reset
                </button>
            </div>
            
            <!-- Image Container -->
            <div class="w-screen h-screen overflow-auto flex items-center justify-center">
                <img :src="currentPhoto" 
                    :style="'transform: scale(' + zoom + ')'"
                    class="max-w-full max-h-[88vh] w-auto h-auto object-contain rounded-lg shadow-2xl transition-transform duration-200 origin-center"
                    alt="Foto Absen">
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-layout>