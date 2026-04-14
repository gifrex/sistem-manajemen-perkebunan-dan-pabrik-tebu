{{-- resources\views\transaction\rencanakerjaharian\modal-index\index-modal-create-rkh.blade.php --}}

{{-- DATE & MANDOR SELECTION MODAL FOR CREATE RKH --}}
<div x-data="createRkhModal()" x-cloak>
    
    <!-- Main Modal -->
    <div x-show="showDateModal" x-transition.opacity
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div x-show="showDateModal" x-transition.scale 
             class="bg-white rounded-lg shadow-lg w-11/12 md:w-2/3 lg:w-1/2 max-h-[90vh] overflow-hidden">
            
            <!-- Header -->
            <div class="flex justify-between items-center p-4 border-b bg-gradient-to-r from-green-50 to-emerald-50">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900">Create RKH Baru</h2>
                </div>
                <button @click="closeModal()" 
                        class="text-gray-600 hover:text-gray-800 text-2xl leading-none">&times;</button>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-4 overflow-y-auto max-h-[60vh]">
                
                <!-- Tanggal Input -->
                <div>
                    <label for="create_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal RKH <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="create_date" x-model="selectedDate" :max="maxDate" :min="minDate"
                           class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500"/>
                    <p class="text-xs text-gray-500 mt-1">Pilih tanggal untuk membuat RKH (hari ini sampai maksimal 7 hari ke depan)</p>
                </div>

                <!-- Mandor Selection (Hidden for Mandor users) -->
                <template x-if="!isMandorUser">
                    <div>
                        <label for="mandor_select" class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih Mandor <span class="text-red-500">*</span>
                        </label>
                        
                        <!-- Mandor Display Input -->
                        <div class="relative">
                            <input
                                type="text"
                                readonly
                                @click="openMandorModal = true"
                                :value="selectedMandor.userid && selectedMandor.name ? `${selectedMandor.userid} - ${selectedMandor.name}` : ''"
                                placeholder="Klik untuk memilih mandor..."
                                class="w-full cursor-pointer bg-white hover:bg-gray-50 border-2 border-gray-200 rounded-lg px-4 py-3 text-sm font-medium transition-colors focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            />
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Mandor Info (Auto-selected for Mandor users) -->
                <template x-if="isMandorUser">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mandor</label>
                        <div class="p-4 bg-gray-100 border-2 border-gray-300 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900" x-text="`${selectedMandor.userid} - ${selectedMandor.name}`"></p>
                                    <p class="text-xs text-gray-500">Auto-selected (Logged in as Mandor)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

            </div>

            <!-- Footer -->
            <div class="flex justify-end space-x-2 p-4 border-t bg-gray-50">
                <button @click="closeModal()" 
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded-lg transition-colors">
                    Cancel
                </button>
                <button @click="proceedToCreate()" 
                        :disabled="!canProceed || isChecking"
                        class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-6 py-2 text-sm rounded-lg transition-colors flex items-center">
                    <svg x-show="isChecking" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="isChecking ? 'Memeriksa...' : 'Lanjutkan'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Mandor Selection Modal (Nested) -->
    <template x-if="!isMandorUser">
        <div x-show="openMandorModal" x-cloak
             class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70 z-[60]"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            <div @click.away="openMandorModal = false"
                 class="bg-white rounded-lg shadow-2xl w-full max-w-md max-h-[70vh] flex flex-col overflow-hidden"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Pilih Mandor</h3>
                        </div>
                        <button @click="openMandorModal = false" type="button"
                            class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" placeholder="Cari nama atau ID mandor..." x-model="mandorSearchQuery"
                            class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Mandor List -->
                <div class="flex-1 overflow-y-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Mandor</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="mandor in filteredMandors" :key="mandor.userid">
                                <tr @click="selectMandor(mandor)"
                                    class="hover:bg-blue-50 cursor-pointer transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900" x-text="mandor.userid"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-medium" x-text="mandor.name"></div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <template x-if="filteredMandors.length === 0">
                        <div class="text-center py-12">
                            <h3 class="text-sm font-medium text-gray-900">Tidak ada mandor ditemukan</h3>
                            <p class="mt-1 text-sm text-gray-500">Coba ubah kata kunci pencarian.</p>
                        </div>
                    </template>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                    <p class="text-xs text-gray-500" x-text="`${filteredMandors.length} mandor tersedia`"></p>
                </div>
            </div>
        </div>
    </template>

    <!-- Outstanding RKH Error Modal -->
    <div x-show="showOutstandingModal" x-cloak x-transition.opacity
         class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-[70] p-4">
        <div x-show="showOutstandingModal" x-transition.scale
             class="bg-white rounded-lg shadow-2xl w-full max-w-md">
            
            <!-- Header -->
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                
                <h3 class="text-lg font-medium text-gray-900 mb-2">RKH Masih Outstanding</h3>
                <p class="text-sm text-gray-600 mb-4">Mandor ini masih memiliki RKH yang belum diselesaikan</p>

                <!-- Outstanding Details -->
                <div class="text-left bg-red-50 rounded-lg p-4 mb-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Mandor:</span>
                        <span class="font-semibold text-gray-900" x-text="outstandingDetails.mandor_name"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">No RKH:</span>
                        <span class="font-semibold text-red-700" x-text="outstandingDetails.rkhno"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tanggal:</span>
                        <span class="font-semibold text-gray-900" x-text="outstandingDetails.rkhdate"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Status:</span>
                        <span class="px-2 py-1 text-xs font-semibold rounded bg-yellow-100 text-yellow-800" 
                              x-text="outstandingDetails.status"></span>
                    </div>
                </div>

                <p class="text-xs text-gray-500 mb-4">
                    Selesaikan RKH ini terlebih dahulu sebelum membuat RKH baru untuk mandor yang sama.
                </p>

                <button @click="showOutstandingModal = false"
                        class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors font-medium">
                    OK, Saya Mengerti
                </button>
            </div>
        </div>
    </div>

    <!-- Duplicate RKH Error Modal -->
    <div x-show="showDuplicateModal" x-cloak x-transition.opacity
         class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-[70] p-4">
        <div x-show="showDuplicateModal" x-transition.scale
             class="bg-white rounded-lg shadow-2xl w-full max-w-md">
            
            <!-- Header -->
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 mb-4">
                    <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                
                <h3 class="text-lg font-medium text-gray-900 mb-2">RKH Sudah Ada</h3>
                <p class="text-sm text-gray-600 mb-4">RKH untuk mandor ini pada tanggal tersebut sudah dibuat</p>

                <!-- Duplicate Details -->
                <div class="text-left bg-orange-50 rounded-lg p-4 mb-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">No RKH:</span>
                        <span class="font-semibold text-orange-700" x-text="duplicateDetails.rkhno"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tanggal:</span>
                        <span class="font-semibold text-gray-900" x-text="duplicateDetails.rkhdate"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Status:</span>
                        <span class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800" 
                              x-text="duplicateDetails.status"></span>
                    </div>
                </div>

                <p class="text-xs text-gray-500 mb-4">
                    Silakan pilih tanggal lain atau edit RKH yang sudah ada.
                </p>

                <button @click="showDuplicateModal = false"
                        class="w-full bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors font-medium">
                    OK, Saya Mengerti
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function createRkhModal() {
    return {
        // Date & Mandor selection
        selectedDate: new Date().toISOString().split('T')[0],
        selectedMandor: { userid: '', name: '' },
        
        // Modal states
        openMandorModal: false,
        isChecking: false,
        showOutstandingModal: false,
        showDuplicateModal: false,
        
        // Error details
        outstandingDetails: {},
        duplicateDetails: {},
        
        // Mandor management
        mandors: @json($mandors ?? []),
        mandorSearchQuery: '',
        isMandorUser: false,

        init() {
            this.checkIfUserIsMandor();
        },

        get minDate() {
            // Today's date
            return new Date().toISOString().split('T')[0];
        },

        get maxDate() {
            // Today + 7 days
            const date = new Date();
            date.setDate(date.getDate() + 7);
            return date.toISOString().split('T')[0];
        },

        get canProceed() {
            if (this.isMandorUser) {
                // Mandor user: only need date
                return this.selectedDate !== '';
            } else {
                // Non-mandor user: need date AND mandor
                return this.selectedDate !== '' && this.selectedMandor.userid !== '';
            }
        },

        get filteredMandors() {
            if (!this.mandorSearchQuery) return this.mandors;
            const q = this.mandorSearchQuery.toUpperCase();
            return this.mandors.filter(m =>
                m.name.toUpperCase().includes(q) ||
                m.userid.toString().toUpperCase().includes(q)
            );
        },

        checkIfUserIsMandor() {
            // Check if current user is Mandor (idjabatan = 5)
            if (window.currentUser && window.currentUser.idjabatan === 5) {
                this.isMandorUser = true;
                
                // Auto-select current user as mandor
                this.selectedMandor = {
                    userid: window.currentUser.userid,
                    name: window.currentUser.name
                };
            }
        },

        selectMandor(mandor) {
            this.selectedMandor = {
                userid: mandor.userid,
                name: mandor.name
            };
            this.openMandorModal = false;
        },

        clearMandor() {
            this.selectedMandor = { userid: '', name: '' };
            this.mandorSearchQuery = '';
        },

        async proceedToCreate() {
            if (!this.canProceed || this.isChecking) return;

            this.isChecking = true;

            try {
                const response = await fetch('{{ route("transaction.rencanakerjaharian.checkOutstanding") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        mandor_id: this.selectedMandor.userid,
                        date: this.selectedDate
                    })
                });

                const data = await response.json();

                // ✅ Handle Outstanding Error
                if (data.hasOutstanding) {
                    this.outstandingDetails = data.details;
                    this.showOutstandingModal = true;
                    return;
                }

                // ✅ Handle Duplicate Error
                if (data.hasDuplicate) {
                    this.duplicateDetails = data.details;
                    this.showDuplicateModal = true;
                    return;
                }

                // ✅ Handle Date Validation Error
                if (!data.success && data.message) {
                    alert(data.message);
                    return;
                }

                // ✅ All validations passed - proceed to wizard (V2 only)
                if (data.success) {
                    Alpine.store('loading').start();
                    
                    const route = '{{ route("transaction.rencanakerjaharian.create-v2") }}';
                    window.location.href = `${route}?date=${this.selectedDate}&mandor_id=${this.selectedMandor.userid}`;
                }

            } catch (error) {
                console.error('Error checking outstanding RKH:', error);
                alert('Terjadi kesalahan sistem. Silakan coba lagi.');
            } finally {
                this.isChecking = false;
            }
        },

        closeModal() {
            this.showDateModal = false;
            this.selectedDate = new Date().toISOString().split('T')[0];
            if (!this.isMandorUser) {
                this.selectedMandor = { userid: '', name: '' };
            }
            this.mandorSearchQuery = '';
        }
    }
}
</script>
@endpush