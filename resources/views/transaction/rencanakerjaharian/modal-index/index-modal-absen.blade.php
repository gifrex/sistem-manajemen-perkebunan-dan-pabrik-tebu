{{-- resources\views\input\rencanakerjaharian\modal-index\index-modal-absen.blade.php --}}

{{-- ABSEN MODAL --}}
<div x-show="showAbsenModal" x-cloak x-transition.opacity
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div x-show="showAbsenModal" x-transition.scale
         class="bg-white rounded-lg shadow-lg w-11/12 md:w-3/4 lg:w-2/3 max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-b bg-gradient-to-r from-blue-50 to-indigo-50">
            <h2 class="text-lg font-semibold text-gray-900">Data Absen Tenaga Kerja</h2>
            <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2">
                    <label for="absen_date" class="text-sm font-medium">Tanggal:</label>
                    <input type="date" id="absen_date" x-model="absenDate" @change="loadAbsenData(absenDate)"
                           class="text-sm border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500"/>
                </div>
                <a :href="`{{ url('report/absen') }}?date_from=${absenDate}&date_to=${absenDate}${selectedMandor ? '&mandor=' + selectedMandor : ''}`"
                   target="_blank"
                   class="inline-flex items-center space-x-1 text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    <span>Report Absen</span>
                </a>
            </div>
            <button @click="showAbsenModal = false" class="text-gray-600 hover:text-gray-800 text-2xl leading-none">&times;</button>
        </div>

        <!-- Filters -->
        <div class="flex items-center justify-between p-4 border-b bg-gray-50">
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <label for="mandor_filter" class="text-sm font-medium">Mandor:</label>
                    <select id="mandor_filter" x-model="selectedMandor" @change="loadAbsenData(absenDate, selectedMandor)"
                            class="text-sm border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Mandor</option>
                        <template x-for="mandor in mandorList" :key="mandor.mandorid">
                            <option :value="mandor.mandorid" x-text="`${mandor.mandorid} - ${mandor.mandor_name}`"></option>
                        </template>
                    </select>
                </div>
            </div>
            
            <div class="text-sm text-gray-600">
                <span class="font-medium">Total:</span> <span x-text="absenList.length"></span> orang
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="isAbsenLoading" class="p-6 text-center">
            <div class="loading-spinner mx-auto"></div>
            <p class="mt-2 text-gray-500 loading-dots">Loading absen data</p>
        </div>

        <!-- Body -->
        <div x-show="!isAbsenLoading" class="p-4 overflow-hidden flex-grow">
            <div class="overflow-x-auto">
                <div class="max-h-[400px] overflow-y-auto">
                    <table class="min-w-full table-auto text-sm">
                        <thead class="bg-gray-100 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left">No. Absen</th>
                                <th class="px-3 py-2 text-left">ID Tenaga Kerja</th>
                                <th class="px-3 py-2 text-left">Nama</th>
                                <th class="px-3 py-2 text-center">Jenis TK</th>
                                <th class="px-3 py-2 text-center">Jam Masuk</th>
                                <th class="px-3 py-2 text-left">ID Mandor</th>
                                <th class="px-3 py-2 text-left">Nama Mandor</th>
                                <th class="px-3 py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="person in absenList" :key="person.tenagakerjaid">
                                <tr class="hover:bg-gray-50">
                                    <td class="border px-3 py-2 font-mono text-xs">
                                        <a :href="`{{ url('report/absen') }}/${person.absenno}`"
                                           target="_blank"
                                           class="text-indigo-600 hover:text-indigo-800 hover:underline font-medium"
                                           x-text="person.absenno"></a>
                                    </td>
                                    <td class="border px-3 py-2 font-mono text-xs" x-text="person.tenagakerjaid"></td>
                                    <td class="border px-3 py-2" x-text="person.nama"></td>
                                    <td class="border px-3 py-2 text-center" x-text="{
                                        1: 'Harian',
                                        2: 'Borongan',
                                        3: 'Operator',
                                        4: 'Helper'
                                    }[person.jenistenagakerja] || '-'"></td>
                                    <td class="border px-3 py-2 text-center font-mono text-xs" x-text="person.jam_masuk ? person.jam_masuk.substring(0, 5) : '-'"></td>
                                    <td class="border px-3 py-2 font-mono text-xs" x-text="person.mandorid"></td>
                                    <td class="border px-3 py-2" x-text="person.mandor_name || '-'"></td>
                                    <td class="border px-3 py-2 text-center">
                                        <span class="px-2 py-1 text-xs rounded-full font-medium"
                                              :class="{
                                                  'bg-green-100 text-green-800': person.approvalstatus === '1',
                                                  'bg-red-100 text-red-800':    person.approvalstatus === '0',
                                                  'bg-yellow-100 text-yellow-800': person.approvalstatus !== '1' && person.approvalstatus !== '0'
                                              }"
                                              x-text="person.approvalstatus === '1' ? 'Approved' : person.approvalstatus === '0' ? 'Rejected' : 'Pending'">
                                        </span>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="absenList.length === 0">
                                <td colspan="8" class="border px-3 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <p>Tidak ada data absen yang tersedia</p>
                                        <p class="text-xs text-gray-400 mt-1">untuk tanggal dan mandor yang dipilih</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div x-show="!isAbsenLoading" class="flex justify-between items-center p-4 border-t bg-gray-50">
            <div class="flex items-center space-x-6 text-sm">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 bg-blue-100 rounded-full"></span>
                    <span>Laki-laki: <span class="font-medium" x-text="absenList.filter(p => p.gender==='L').length"></span></span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 bg-pink-100 rounded-full"></span>
                    <span>Perempuan: <span class="font-medium" x-text="absenList.filter(p => p.gender==='P').length"></span></span>
                </div>
                <div class="font-medium">
                    Total: <span x-text="absenList.length"></span> orang
                </div>
            </div>
            <button @click="showAbsenModal = false"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded">Close</button>
        </div>
    </div>
</div>