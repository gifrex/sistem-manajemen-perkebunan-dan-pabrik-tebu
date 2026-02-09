<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="min-h-screen bg-gray-50" x-data="koreksiData()">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $title }}</h1>
                <p class="text-sm text-gray-600 mt-1">Proses koreksi pemakaian atau retur item</p>
            </div>
        </div>

        <!-- Alert Messages -->
        <div class="max-w-7xl mx-auto px-6 py-4">
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <ul class="text-sm text-red-700 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Form Input Koreksi -->
        <div class="max-w-7xl mx-auto px-6 py-2">
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <form action="{{ route('transaction.gudang.koreksi.submit') }}" method="POST" @submit="handleSubmit">
                    @csrf
                    <div class="p-6 space-y-6">
                        
                        <!-- Tipe Transaksi -->
                        <div>
                            <label for="tipe_transaksi" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipe Transaksi <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="tipe_transaksi" 
                                name="tipe_transaksi" 
                                x-model="tipeTransaksi"
                                @change="onTipeChange"
                                required 
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                                <option value="">-- Pilih Tipe Transaksi --</option>
                                <option value="RETUR">RETUR (Pengembalian Pemakaian)</option>
                                <option value="USE">USE (Koreksi Pemakaian)</option>
                            </select>
                            <p class="mt-2 text-sm text-gray-500" x-show="tipeTransaksi" x-text="tipeHint"></p>
                        </div>

                        <!-- RKH Number -->
                        <div>
                            <label for="rkhno" class="block text-sm font-medium text-gray-700 mb-2">
                                No. RKH <span class="text-red-500">*</span>
                            </label>@php //dd($rkhList[0]->name); 
                            // @endphp
                            <select 
                                id="rkhno" 
                                name="rkhno" 
                                x-model="rkhno"
                                @change="onRkhChange"
                                :disabled="!tipeTransaksi"
                                required 
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                            > 
                                <option value="">-- Pilih RKH --</option>
                                @foreach($rkhList as $rkh)
                                    <option value="{{ $rkh->rkhno }}">{{ $rkh->companycode }} {{ $rkh->rkhno }} - {{ $rkh->name ?? '' }} - {{ $rkh->costcenter }} </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Item Sequence -->
                        <div>
                            <label for="itemseq" class="block text-sm font-medium text-gray-700 mb-2">
                                Item Urutan (Seq) <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="itemseq" 
                                name="itemseq" 
                                x-model="itemseq"
                                @change="onItemseqChange"
                                :disabled="!rkhno || loading"
                                required 
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                            >
                                <option value="">-- Pilih Item Urutan --</option>
                                <template x-for="item in items" :key="item.itemseq">
                                    <option :value="item.itemseq" x-text="`Seq ${item.itemseq} - ${item.itemcode} (Qty: ${item.qty} ${item.uom})`"></option>
                                </template>
                            </select>
                            <p class="mt-2 text-sm text-gray-500">Pilih urutan item yang akan dikoreksi</p>
                        </div>

                        <!-- Info Item Original -->
                        <div 
                            x-show="currentItem" 
                            x-transition
                            class="bg-blue-50 border-l-4 border-blue-400 p-4"
                        >
                            <h5 class="text-sm font-semibold text-blue-900 mb-3">Informasi Item Original</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-600">Item Code:</p>
                                    <p class="font-semibold text-blue-700" x-text="currentItem?.itemcode"></p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Item Name:</p>
                                    <p class="font-semibold" x-text="currentItem?.itemname || '-'"></p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Qty Original:</p>
                                    <p class="font-semibold text-green-600" x-text="currentItem ? `${currentItem.qty} ${currentItem.uom}` : '-'"></p>
                                </div>
                                <div>
                                    <p class="text-gray-600">UOM:</p>
                                    <p class="font-semibold" x-text="currentItem?.uom || '-'"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Item Code (untuk tipe USE) -->
                        <div x-show="tipeTransaksi === 'USE' && currentItem" x-transition>
                            <label for="itemcode" class="block text-sm font-medium text-gray-700 mb-2">
                                Item Code <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="itemcode" 
                                name="itemcode" 
                                x-model="itemcode"
                                @change="onItemcodeChange"
                                :disabled="!currentItem"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:bg-gray-100"
                            >
                                <option value="">-- Pilih Item --</option>
                                @foreach($itemList as $item)
                                    <option value="{{ $item->itemcode }}">{{ $item->itemcode }} - {{ $item->itemname ?? '' }}</option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-sm text-yellow-600">
                                <svg class="inline w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                Anda bisa mengganti item berbeda dari item original
                            </p>
                        </div>

                        <!-- Quantity -->
                        <div>
                            <label for="qty" class="block text-sm font-medium text-gray-700 mb-2">
                                Quantity <span class="text-red-500">*</span>
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    name="qty" 
                                    id="qty" 
                                    x-model="qty"
                                    @input="validateQty"
                                    :disabled="!currentItem"
                                    :max="tipeTransaksi === 'RETUR' && currentItem ? currentItem.qty : null"
                                    required 
                                    placeholder="0.00"
                                    class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                                    :class="{ 'border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500': qtyError }"
                                >
                            </div>
                            <p class="mt-2 text-sm" :class="qtyError ? 'text-red-600' : 'text-gray-500'" x-text="qtyHint"></p>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 border-t border-gray-200">
                        <a 
                            href="{{ route('transaction.gudang.index') }}" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <svg class="mr-2 -ml-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Kembali
                        </a>
                        <button 
                            type="submit" 
                            :disabled="!canSubmit"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-gray-400 disabled:cursor-not-allowed"
                        >
                            <svg class="mr-2 -ml-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Simpan Koreksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function koreksiData() {
            return {
                tipeTransaksi: '',
                rkhno: '',
                itemseq: '',
                itemcode: '',
                qty: '',
                items: [],
                currentItem: null,
                loading: false,
                qtyError: false,
                tipeHint: '',
                qtyHint: '',

                onTipeChange() {
                    this.resetForm();
                    
                    if (this.tipeTransaksi === 'RETUR') {
                        this.tipeHint = 'Pengembalian pemakaian yang sudah tercatat. Qty tidak boleh melebihi qty original.';
                        this.qtyHint = 'Masukkan qty yang akan diretur (max: qty original)';
                    } else if (this.tipeTransaksi === 'USE') {
                        this.tipeHint = 'Koreksi pemakaian. Anda bisa mengubah item dan qty tanpa batasan.';
                        this.qtyHint = 'Masukkan qty koreksi (tanpa batasan)';
                    }
                },

                async onRkhChange() {
                    if (!this.rkhno) {
                        this.items = [];
                        this.currentItem = null;
                        return;
                    }

                    this.loading = true;
                    
                    try {
                        const response = await fetch('{{ route("transaction.gudang.getItemsByRkh") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ rkhno: this.rkhno })
                        });

                        const data = await response.json();
                        
                        if (data.success && data.items.length > 0) {
                            this.items = data.items;
                        } else {
                            this.items = [];
                            alert('Tidak ada item ditemukan pada RKH ini.');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Gagal mengambil data item: ' + error.message);
                    } finally {
                        this.loading = false;
                    }
                },

                async onItemseqChange() {
                    if (!this.itemseq) {
                        this.currentItem = null;
                        return;
                    }

                    try {
                        const response = await fetch('{{ route("transaction.gudang.getItemDetail") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ 
                                rkhno: this.rkhno,
                                itemseq: this.itemseq 
                            })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.currentItem = data.item;
                            
                            // Set default itemcode untuk USE
                            if (this.tipeTransaksi === 'USE') {
                                this.itemcode = data.item.itemcode;
                            }
                            
                            this.qty = '';
                            this.qtyError = false;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Gagal mengambil detail item: ' + error.message);
                    }
                },

                onItemcodeChange() {
                    if (this.tipeTransaksi === 'USE' && this.currentItem && this.itemcode !== this.currentItem.itemcode) {
                        if (!confirm(`Anda akan mengubah item dari ${this.currentItem.itemcode} menjadi ${this.itemcode}. Lanjutkan?`)) {
                            this.itemcode = this.currentItem.itemcode;
                        }
                    }
                },

                validateQty() {
                    if (this.tipeTransaksi === 'RETUR' && this.currentItem) {
                        const qtyValue = parseFloat(this.qty);
                        const maxQty = parseFloat(this.currentItem.qty);
                        
                        if (qtyValue > maxQty) {
                            this.qtyError = true;
                            this.qtyHint = `Qty retur tidak boleh melebihi ${maxQty}`;
                        } else {
                            this.qtyError = false;
                            this.qtyHint = `Masukkan qty yang akan diretur (max: ${maxQty})`;
                        }
                    }
                },

                handleSubmit(event) {
                    if (this.tipeTransaksi === 'RETUR' && this.currentItem) {
                        const qtyValue = parseFloat(this.qty);
                        const maxQty = parseFloat(this.currentItem.qty);
                        
                        if (qtyValue > maxQty) {
                            event.preventDefault();
                            alert(`Qty retur (${qtyValue}) tidak boleh melebihi qty original (${maxQty})`);
                            return false;
                        }
                    }
                    
                    const tipeText = this.tipeTransaksi === 'RETUR' ? 'Retur' : 'Koreksi Pemakaian';
                    if (!confirm(`Apakah Anda yakin akan menyimpan ${tipeText} ini?`)) {
                        event.preventDefault();
                        return false;
                    }
                },

                resetForm() {
                    this.rkhno = '';
                    this.itemseq = '';
                    this.itemcode = '';
                    this.qty = '';
                    this.items = [];
                    this.currentItem = null;
                    this.qtyError = false;
                },

                get canSubmit() {
                    if (!this.tipeTransaksi || !this.rkhno || !this.itemseq || !this.qty) {
                        return false;
                    }
                    
                    if (this.tipeTransaksi === 'USE' && !this.itemcode) {
                        return false;
                    }
                    
                    if (this.qtyError) {
                        return false;
                    }
                    
                    return true;
                }
            }
        }
    </script>
</x-layout>