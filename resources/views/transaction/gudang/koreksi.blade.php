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

        @if(session('warning'))
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">{{ session('warning') }}</p>
                    </div>
                </div>
            </div>
        @endif

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
                                    <option value="{{ $rkh->rkhno }}">{{ $rkh->companycode }} {{ $rkh->rkhno }} - {{ $rkh->mandor_name ?? '' }} - {{ $rkh->nouse }} </option>
                                @endforeach
                            </select>
                        </div>

                        
                        




                        <!-- LIST ITEMS (tampil semua item setelah pilih RKH) -->
                        <div x-show="rkhno && items.length" x-transition>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Daftar Item (isi Qty untuk diproses)
                                </h3>
                                <p class="text-xs text-gray-500">
                                    Kosongkan qty = skip baris itu
                                </p>
                            </div>

                            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Seq</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">LKH</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Plot</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Item Original</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600">Qty Original</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600" x-text="tipeTransaksi==='USE' ? 'Item Baru (USE)' : 'Item Retur (fixed)'"></th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600" x-text="tipeTransaksi==='USE' ? 'Qty Baru' : 'Qty Retur'"></th>
                                        </tr>
                                    </thead>

                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="(row, idx) in items" :key="row.itemseq">
                                            <tr>
                                                <td class="px-3 py-2 text-sm text-gray-800" x-text="row.itemseq"></td>
                                                <td class="px-3 py-2 text-sm text-gray-800" x-text="row.lkhno"></td>
                                                <td class="px-3 py-2 text-sm text-gray-800" x-text="row.plot"></td>
                                                <td class="px-3 py-2 text-sm">
                                                    <div class="font-semibold text-gray-900" x-text="row.itemcode"></div>
                                                    <div class="text-gray-500 text-xs" x-text="row.itemname || '-'"></div>

                                                    <!-- hidden baseline -->
                                                    <input type="hidden" :name="`rows[${idx}][itemseq]`" :value="row.itemseq">
                                                    <input type="hidden" :name="`rows[${idx}][orig_itemcode]`" :value="row.itemcode">
                                                    <input type="hidden" :name="`rows[${idx}][orig_qty]`" :value="row.qty">
                                                </td>

                                                <td class="px-3 py-2 text-sm text-gray-800">
                                                    <span x-text="row.qty"></span>
                                                    <span class="text-gray-500" x-text="row.uom ? ` ${row.uom}` : ''"></span>
                                                </td>

                                                <!-- Item baru / fixed -->
                                                <td class="px-3 py-2 text-sm">
                                                    <template x-if="tipeTransaksi === 'USE'">
                                                        <select class="block w-72 rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                                x-model="row.new_itemcode"
                                                                :name="`rows[${idx}][itemcode]`">
                                                            <option value="">-- Pilih Item --</option>
                                                            @foreach($itemList as $item)
                                                                <option value="{{ $item->itemcode }}">{{ $item->itemcode }} - {{ $item->itemname ?? '' }}</option>
                                                            @endforeach
                                                        </select>
                                                    </template>

                                                    <template x-if="tipeTransaksi === 'RETUR'">
                                                        <div class="text-gray-600">
                                                            <span class="font-semibold" x-text="row.itemcode"></span>
                                                            <span class="text-xs text-gray-500 block" x-text="row.itemname || '-'"></span>
                                                            <!-- kirim itemcode juga biar controller gampang -->
                                                            <input type="hidden" :name="`rows[${idx}][itemcode]`" :value="row.itemcode">
                                                        </div>
                                                    </template>
                                                </td>

                                                <!-- Qty baru/retur: isi = proses -->
                                                <td class="px-3 py-2 text-sm">
                                                    <input type="number" step="0.01"
                                                        class="block w-40 rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                        x-model="row.new_qty"
                                                        :name="`rows[${idx}][qty]`"
                                                        :max="tipeTransaksi === 'RETUR' ? row.qty : null"
                                                        placeholder="kosong = skip">

                                                    <div class="text-xs text-red-600 mt-1"
                                                        x-show="tipeTransaksi==='RETUR' && row.new_qty && parseFloat(row.new_qty) > parseFloat(row.qty)">
                                                        Qty retur tidak boleh > qty pemakaian
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
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
    items: [],
    loading: false,
    tipeHint: '',

    onTipeChange() {
      this.rkhno = '';
      this.items = [];

      if (this.tipeTransaksi === 'RETUR') {
        this.tipeHint = 'RETUR: isi qty retur (max = qty pemakaian). Item tidak bisa diganti.';
      } else if (this.tipeTransaksi === 'USE') {
        this.tipeHint = 'USE: isi qty pemakaian baru. Boleh ganti item.';
      } else {
        this.tipeHint = '';
      }
    },

    async onRkhChange() {
      this.items = [];
      if (!this.rkhno) return;

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

        if (data.success && Array.isArray(data.items) && data.items.length > 0) {
          this.items = data.items.map(x => ({
            ...x,
            new_itemcode: x.itemcode, // default item baru = item original
            new_qty: ''              // kosong = skip
          }));
        } else {
          alert('Tidak ada item ditemukan pada RKH ini.');
        }
      } catch (err) {
        console.error(err);
        alert('Gagal mengambil data item: ' + err.message);
      } finally {
        this.loading = false;
      }
    },

    handleSubmit(event) {
      const picked = this.items.filter(r => r.new_qty && parseFloat(r.new_qty) > 0);

      if (!this.tipeTransaksi || !this.rkhno) {
        event.preventDefault();
        alert('Pilih tipe transaksi dan RKH terlebih dahulu.');
        return false;
      }

      if (picked.length === 0) {
        event.preventDefault();
        alert('Isi minimal 1 Qty untuk diproses.');
        return false;
      }

      if (this.tipeTransaksi === 'USE') {
        for (const r of picked) {
          if (!r.new_itemcode) {
            event.preventDefault();
            alert(`Item baru wajib dipilih untuk Seq ${r.itemseq}`);
            return false;
          }
        }
      }

      if (this.tipeTransaksi === 'RETUR') {
        for (const r of picked) {
          if (parseFloat(r.new_qty) > parseFloat(r.qty)) {
            event.preventDefault();
            alert(`Qty retur Seq ${r.itemseq} tidak boleh > qty pemakaian`);
            return false;
          }
        }
      }

      const tipeText = this.tipeTransaksi === 'RETUR' ? 'Retur' : 'Koreksi Pemakaian';
      if (!confirm(`Simpan ${tipeText} untuk ${picked.length} item?`)) {
        event.preventDefault();
        return false;
      }
    },

    get canSubmit() {
      if (this.loading) return false;
      if (!this.tipeTransaksi || !this.rkhno) return false;
      if (!this.items.length) return false;

      const picked = this.items.filter(r => r.new_qty && parseFloat(r.new_qty) > 0);
      if (!picked.length) return false;

      for (const r of picked) {
        if (this.tipeTransaksi === 'USE' && !r.new_itemcode) return false;
        if (this.tipeTransaksi === 'RETUR' && parseFloat(r.new_qty) > parseFloat(r.qty)) return false;
      }
      return true;
    }
  }
}
</script>


</x-layout>