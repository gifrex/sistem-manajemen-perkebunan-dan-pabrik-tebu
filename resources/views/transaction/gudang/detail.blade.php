
{{-- resources\views\input\gudang\detail.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>   
    @php 
    $returEligible = collect($detailmaterial)
        ->filter(fn($d) => (float)($d->qtyretur ?? 0) > 0 && empty($d->noretur))
        ->count();
    
    @endphp
    
    <style>
    @media print {
    select.item-select { display: none !important; }
    .print-label { display: inline !important; }
    table th, table td {
        border: 1px solid #d1d5db; /* abu Tailwind gray-300 */
      }
    }
    @media screen {
    .print-label { display: none; }
    table th, table td {
        border: 1px solid #d1d5db; /* abu Tailwind gray-300 */
      }
    }
    
    </style>
    
        @if(strtoupper($details[0]->flagstatus) == 'ACTIVE')
            <x-slot:title>Penyiapan RKH Herbisida</x-slot:title>
        @elseif(strtoupper($details[0]->flagstatus) == 'RECEIVED_BY_MANDOR')
            <x-slot:title>RKH Herbisida Diterima</x-slot:title>
        @else
            <x-slot:title>RKH Herbisida Selesai</x-slot:title>
        @endif
        
        <div class="p-4 max-w-screen-xl mx-auto">
            <!-- Status Badge -->
            <div class="flex justify-center mb-3">
                @if(strtoupper($details[0]->flagstatus) == 'ACTIVE')
                    <div class="bg-green-100 text-green-800 px-3 mr-2 py-1 rounded shadow text-sm no-print">ACTIVE</div>
                    <div class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded shadow text-sm no-print">
                        <i class="bi bi-clock me-1"></i>Apabila Sudah Diperiksa, Klik Button Penyerahan Di Bawah
                    </div>
                @elseif(strtoupper($details[0]->flagstatus) == 'DISPATCHED')
                    <div class="bg-green-100 text-green-800 px-3 mr-2 py-1 rounded shadow text-sm no-print">DISPATCHED</div>
                    <div class="bg-green-100 text-green-800 px-3 py-1 rounded shadow text-sm no-print">
                        <i class="bi bi-check-circle me-1"></i>Barang Diserahkan, Menunggu Feedback Mandor.
                    </div>
                @elseif(strtoupper($details[0]->flagstatus) == 'RECEIVED_BY_MANDOR')
                    <div class="bg-green-100 text-green-800 px-3 mr-2 py-1 rounded shadow text-sm no-print">RECEIVED BY MANDOR</div>
                    <div class="bg-green-100 text-green-800 px-3 py-1 rounded shadow text-sm no-print">
                        <i class="bi bi-check-circle me-1"></i>Barang Sudah Diterima Mandor. Untuk Retur, Ajukan Dokumen Retur.
                    </div>
                @elseif(strtoupper($details[0]->flagstatus) == 'UPLOADED')
                    <div class="bg-green-100 text-green-800 px-3 mr-2 py-1 rounded shadow text-sm no-print">UPLOADED</div>
                    <div class="bg-green-100 text-green-800 px-3 py-1 rounded shadow text-sm no-print">
                        <i class="bi bi-check-circle me-1"></i>Barang Sudah Di Upload Mandor.
                    </div>
                @elseif(strtoupper($details[0]->flagstatus) == 'RETURNED_BY_MANDOR')
                    <div class="bg-green-100 text-green-800 px-3 mr-2 py-1 rounded shadow text-sm no-print">RETURNED BY MANDOR</div>
                    <div class="bg-green-100 text-green-800 px-3 py-1 rounded shadow text-sm no-print">
                        <i class="bi bi-check-circle me-1"></i>Barang Sudah Diretur.
                    </div>
                @elseif(strtoupper($details[0]->flagstatus) == 'RETURN_RECEIVED')
                    <div class="bg-green-100 text-green-800 px-3 mr-2 py-1 rounded shadow text-sm no-print">RETURN RECEIVED</div>
                    <div class="bg-green-100 text-green-800 px-3 py-1 rounded shadow text-sm no-print">
                        <i class="bi bi-check-circle me-1"></i>Barang Retur Sudah Diterima.
                    </div>
                @elseif(strtoupper($details[0]->flagstatus) == 'WAIT_APPROVAL')
                    <div class="bg-yellow-100 text-yellow-800 px-3 mr-2 py-1 rounded shadow text-sm no-print">WAIT APPROVAL</div>
                    <div class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded shadow text-sm no-print">
                        <i class="bi bi-clock me-1"></i>Menunggu Approval (Use Material)
                    </div>
                @elseif(strtoupper($details[0]->flagstatus) == 'CANCEL')
                    <div class="bg-blue-100 text-yellow-800 px-3 py-1 rounded shadow text-sm no-print">
                        <i class="bi bi-check-all me-1"></i>Dokumen RKH Herbisida Telah Dibatalkan
                    </div>
                @else
                    <div class="bg-blue-100 text-blue-800 px-3 py-1 rounded shadow text-sm no-print">
                        <i class="bi bi-check-all me-1"></i>Dokumen RKH Herbisida Telah Diselesaikan
                    </div>
                @endif
            </div>
        
            <!-- Header Section - Compact -->
            <div class="w-full bg-white shadow rounded mb-4">
                <table class="w-full text-xs">
                    <thead class="text-gray-700">
                        <tr>
                            <th class="py-1 px-2 text-left border-0" colspan="5">
                                <div class="space-y-1 mb-3">
                                    <div class="grid grid-cols-3 gap-4">
                                        <span class="text-left"><b>Company:</b> {{session('companycode')}} </span>
                                        <span class="text-center"><b>RKH:</b> {{ $details[0]->rkhno }}</span>
                                        <span class="text-center"><b>Inventory:</b> {{ $islokal }}</span>
                                        {{-- <span class="text-right"><b>Tanggal:</b> {{ \Carbon\Carbon::parse($details[0]->createdat)->format('d/m/y') }}</span> --}}
                                    </div>
                                    <div class="grid grid-cols-3 gap-4">
                                        <span class="text-left"><b>Mandor:</b> {{ $details[0]->name }}</span>
                                        <span class="text-center"><b>USE:</b> {{ $details[0]->nouse }}</span>
                                        <span class="text-center"><b>Status:</b> {{str_replace('_', ' ', $details[0]->flagstatus)}}</span> <!-- Empty but maintains alignment -->
                                    </div>
                                </div>
                            </th>
                        </tr>
                        <tr class="border-b">
                            <th class="py-1 px-2">LKH</th>
                            <th class="py-1 px-2">Blok</th>
                            <th class="py-1 px-2">Plot</th>
                            <th class="py-1 px-2">Luas (HA)</th>
                            <th class="py-1 px-2">Activity</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600">
                        @php 
                        $totalLuas = 0; 
                        $plots = $details->unique(function($item) {
                            return $item->lkhno . '|' . $item->blok . '|' . $item->plot;
                        });
                        @endphp
                        
                        @foreach($plots as $d)  
                            <tr class="border-b">
                                <td class="py-1 px-2">{{ $d->lkhno }}</td>
                                <td class="py-1 px-2">{{ $d->blok }}</td>
                                <td class="py-1 px-2">{{ $d->plot }}</td>
                                <td class="py-1 px-2 text-right">{{ $d->luasrkh }} HA</td>
                                <td class="py-1 px-2 bg-green-100">{{ $d->activitycode }} {{ $d->herbisidagroupname }}</td>
                            </tr>
                            @php $totalLuas += floatval($d->luasrkh); @endphp
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="text-gray-800 bg-gray-100">
                            <td colspan="3" class="py-1 px-2 font-semibold text-right border-t">Total Luas</td>
                            <td class="py-1 px-2 font-semibold text-right border-t">{{ $totalLuas }} HA</td>
                            <td class="py-1 px-2 border-t"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        
            <!-- Form -->
            <form action="{{ route('transaction.gudang.submit', ['rkhno' => $details[0]->rkhno]) }}" method="POST">
                @csrf
                  
                @if (strtoupper($details[0]->flagstatus ?? '') === 'WAIT_APPROVAL' && !empty($usematerialapproval))
                    {{-- TABLE KHUSUS WAIT_APPROVAL (simple, minimal) --}}
                    @php
                    // old (usemateriallst) map untuk compare
                    $oldMap = collect($detailmaterial)->keyBy(fn($x)=> trim($x->lkhno).'|'.trim($x->plot).'|'.trim($x->itemcode));
                    @endphp

                    <table class="min-w-full p-2 bg-white shadow rounded text-xs no-print">
                    <thead class="text-gray-700">
                    <tr>
                        <th class="py-0.5 px-2 border-b text-left">Item</th>
                        <th class="py-0.5 px-2 border-b text-center">Plot</th>
                        <th class="py-0.5 px-2 border-b text-right">Luas</th>
                        <th class="py-0.5 px-2 border-b text-right">Dosage</th>
                        <th class="py-0.5 px-2 border-b text-right">Qty</th>
                        <th class="py-0.5 px-2 border-b text-center">LKH</th>
                    </tr>
                    </thead>

                    <tbody class="text-gray-600">
                    @foreach ($usematerialapproval as $k => $ap)
                        @php
                        $old = $oldMap[$k] ?? null;

                        $oldDos = (float)($old->dosageperha ?? 0);
                        $newDos = (float)($ap->dosageperha ?? 0);

                        $oldQty = (float)($old->qty ?? 0);
                        $newQty = (float)($ap->qty ?? 0);

                        $dosChanged = abs($oldDos - $newDos) > 0.00001;
                        $qtyChanged = abs($oldQty - $newQty) > 0.00001;

                        // luas untuk submit (qty/dosage) supaya sesuai snapshot
                        $luasSubmit = $newDos > 0 ? ($newQty / $newDos) : 0;
                        @endphp

                        <tr class="border-b hover:bg-gray-50">
                        <td class="py-0.5 px-2">
                            <b>{{ $ap->itemcode }}</b> — {{ $ap->itemname ?? '' }}
                        </td>

                        <td class="py-0.5 px-2 text-center">{{ $ap->plot }}</td>

                        <td class="py-0.5 px-2 text-right">
                            {{ $old->luasrkh ?? '-' }}
                        </td>

                        <td class="py-0.5 px-2 text-right">
                            @if($dosChanged)
                            {{ number_format($oldDos, 2, '.', '') }} → {{ number_format($newDos, 2, '.', '') }}
                            @else
                            {{ number_format($newDos, 2, '.', '') }}
                            @endif
                        </td>

                        <td class="py-0.5 px-2 text-right">
                            @if($qtyChanged)
                            {{ number_format($oldQty, 2, '.', '') }} → {{ number_format($newQty, 2, '.', '') }}
                            @else
                            {{ number_format($newQty, 2, '.', '') }}
                            @endif
                        </td>

                        <td class="py-0.5 px-2 text-center">{{ $ap->lkhno }}</td>
                        </tr>

                     @endforeach
                    </tbody>
                    </table>

                
                @else

                
                <table class='min-w-full md:w-1/3 p-2 bg-white shadow rounded text-xs no-print'>
                    <thead class="text-gray-700">
                        <tr>
                            <th class="py-0.5 px-2 border-b text-center">Herbisida - Item</th>
                            <th class="py-0.5 px-2 border-b text-center">Plot</th>
                            <th class="py-0.5 px-2 border-b text-center">Luas</th>
                            <th class="py-0.5 px-2 border-b text-center">Dosage (HA)</th>
                            <th class="py-0.5 px-2 border-b text-center">Qty Disiapkan</th>
                            <th class="py-0.5 px-2 border-b text-center">Qty Retur</th>
                            <th class="py-0.5 px-2 border-b text-center">Nomor LKH</th>
                            <th class="py-0.5 px-2 border-b text-center">Nomor Retur</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600">
                        @php
                        $stdDosage = collect($itemlist)->mapWithKeys(fn($x)=>[
                            ($x->itemcode.'|'.$x->activitycode) => (float)$x->dosageperha
                        ]);
                        @endphp
                        @foreach ($detailmaterial as $d)
                        @php
                            // Hitung total luas semua blok untuk lkhno ini
                            $plotsInLkh = $plots->where('lkhno', $d->lkhno);
                            $totalLuas = $plotsInLkh->sum('luasrkh');
                            
                            // Hitung total qty = dosage × total luas semua blok
                            $totalQty = $d->dosageperha * $totalLuas;
                        @endphp
                    
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-0.5 px-2">
                                @php
                                // Ambil activitycode dari $plots jika $d->activitycode kosong
                                if (empty($d->activitycode)) {
                                    $plotData = $plots->where('lkhno', $d->lkhno)
                                                    ->where('plot', $d->plot)
                                                    ->first();
                                    $activitycode = $plotData->activitycode ?? null;
                                } else {
                                    $activitycode = $d->activitycode;
                                }

                                @endphp
                                
                                <select
                                    @if (strtoupper($details[0]->flagstatus) != 'ACTIVE') disabled @endif
                                    name="itemcode[{{ $d->lkhno }}][{{ $d->itemcode }}][{{ $d->plot }}]"
                                    class="item-select w-full border-none bg-yellow-100 text-xs"
                                    data-luas="{{ $totalLuas }}"
                                    data-lkhno="{{ $d->lkhno }}"
                                > 
                                    @foreach ($itemlist->where('herbisidagroupid', $d->herbisidagroupid); as $item) 
                                    <option value="{{ $item->itemcode }}" 
                                        {{ $item->itemcode == (
                                            (strtoupper($details[0]->flagstatus ?? '') === 'WAIT_APPROVAL' && isset($usematerialapproval))
                                                ? (collect($usematerialapproval)->where('lkhno', $d->lkhno)->where('plot', $d->plot)->first()->itemcode ?? $d->itemcode)
                                                : $d->itemcode
                                        ) ? 'selected' : '' }}
                                        data-dosage="{{$item->dosageperha}}" 
                                        data-measure="{{ $item->measure }}" 
                                        data-itemname="{{ $item->itemname }}"
                                        data-rounddosage="{{ $item->rounddosage ?? 1 }}">
                                        {{$item->activitycode}} • {{ $item->itemcode }} • {{ $item->itemname }} • {{$item->dosageperha}} ({{$item->measure}})
                                    </option>
                                    @endforeach
                                </select>
                            
                                <span class="print-label text-xs">
                                    Herbisida {{ $d->herbisidagroupid }} - {{ $d->itemcode }} - {{ $d->itemname ?? '[Nama Item]' }} - {{ $d->dosageperha }} ({{ $d->unit }}) (Total: {{ $totalLuas }} HA)
                                </span>
                                <input type="hidden" name="unit[{{ $d->lkhno }}][{{ $d->itemcode }}][{{ $d->plot }}]"
                                    class="selected-unit" value="{{ $d->unit }}">
                                <input type="hidden" name="luas[{{ $d->lkhno }}][{{ $d->itemcode }}][{{ $d->plot }}]"
                                        class="selected-luas" value="{{ $d->luasrkh }}">
                                <input type="hidden" name="itemcodelist[{{ $d->lkhno }}][]"
                                    class="selected-itemcode" value="{{ $d->itemcode }}">
                            </td>
                    
                            <td class="py-0.5 px-2 text-center text-right">
                                <span class="labelplot">{{ $d->plot }}</span>
                            </td>
                    
                            <td class="py-0.5 px-2 text-center text-right">
                                <span class="labelplot">{{ $d->luasrkh }}</span>
                            </td>
                    
                            <td class="py-0.5 px-2">
                                <div class="flex justify-end items-center">
                                    <input type="text" name="dosage[{{ $d->lkhno }}][{{ $d->itemcode }}][{{ $d->plot }}]"
                                    value="{{ number_format($d->dosageperha, 2) }}"
                                    data-orig="{{ number_format($d->dosageperha, 2, '.', '') }}"
                                    class="w-full selected-dosage border-none bg-yellow-100 text-xs text-right w-20">
                                </div>
                            </td>
                    
                            <td class="py-0.5 px-2 text-center text-right">
                                @php
                                $qtyRawExp = (float)($stdDosage[$d->itemcode.'|'.$activitycode] ?? 0) * (float)($d->luasrkh ?? 0);

                                // Pembulatan ke 0.05
                                if ($qtyRawExp > 0) {
                                    $truncatedExp = floor($qtyRawExp * 100) / 100; // truncate 2 digit

                                    if ($truncatedExp <= 0) {
                                        $exp = 0.05;
                                    } else {
                                        $exp = ceil($truncatedExp / 0.05) * 0.05;  // Pembulatan ke kelipatan 0.05
                                    }
                                } else {
                                    $exp = 0;
                                }

                                $qty = (float)($d->qty ?? 0);
                                $diff = $qty - $exp;
                            @endphp
                            
                            <span class="ml-2 text-[10px] font-semibold
                                {{ abs($diff) > 0.00001 ? ($diff > 0 ? 'text-orange-600' : 'text-green-600') : 'hidden' }}">
                                • 
                            </span>
                            <span class="labelqty">{{ $d->qty }}</span>
                                        
                            </td>
                    
                            <td class="py-0.5 px-2 text-center text-right">
                                {{ $d->qtyretur ?? 0 }}
                            </td>
                    
                            <td class="py-0.5 px-2 text-center">
                                {{ $d->lkhno }}
                            </td>
                    
                            <td class="py-0.5 px-2 text-center">
                                @if (empty($d->noretur) && $d->qtyretur>0 && strtoupper($details[0]->flagstatus) != 'ACTIVE')
                                    <a href="{{ route('transaction.gudang.retur', [
                                            'retur' => $d->qtyretur,
                                            'itemcode' => $d->itemcode,
                                            'rkhno' => $details[0]->rkhno,
                                            'lkhno' => $d->lkhno,
                                            'plot' => $d->plot
                                        ]) }}"
                                    class="inline-block bg-yellow-100 text-gray-800 hover:bg-blue-600 hover:text-white text-xs py-1 px-2 rounded shadow transition no-print"
                                    onclick="return confirm('Proses Retur Barang ini ?')">
                                        Retur ?
                                    </a>
                                @else
                                    {{ $d->noretur ?? '-' }}
                                @endif
                            </td>
                    
                        </tr>
                    @endforeach
                    </tbody>
                     
                    @endif

                </table>
    
    
    
    
            @php
                $itemMeta = collect($itemlist)->keyBy('itemcode');
            
                $totals = [];
                foreach ($detailmaterial as $d) {
                    // langsung ambil luas per plot
                    $luas = (float) $d->luasrkh;
                    $qty  = (float) $d->dosageperha * $luas;
                    $code = $d->itemcode;
            
                    if (!isset($totals[$code])) {
                        $meta = $itemMeta->get($code);
                        $totals[$code] = [
                            'itemname' => $meta->itemname ?? $d->itemname ?? '-',
                            'unit'     => $meta->measure  ?? $d->unit     ?? '-',
                            'qty'      => 0,
                            'parts'    => [],
                        ];
                    }
            
                    $totals[$code]['qty'] += $qty;
                    $totals[$code]['parts'][] = number_format($qty, 2);
                }
            @endphp
            
            
            <table class="w-full md:w-2/3 mx-auto mt-6 bg-white shadow rounded text-xs border border-gray-200" @if(strtoupper($details[0]->flagstatus ?? '') === 'WAIT_APPROVAL') hidden @endif>
                <thead class="bg-gray-100 text-gray-700 uppercase">
                    <tr>
                        <th class="py-0.5 px-3 border-b">Itemcode</th>
                        <th class="py-0.5 px-3 border-b">Item Name</th>
                        <th class="py-0.5 px-3 border-b">Unit</th>
                        <th class="py-0.5 px-3 border-b">Total Qty</th>
                        <th class="py-0.5 px-3 border-b">Perhitungan</th>
                        <th class="py-0.5 px-3 border-b bg-red-50">Total Retur</th>
                        <th class="py-0.5 px-3 border-b bg-green-50">Total Pemakaian</th>
                    </tr>
                </thead>
                <tbody id="totals-body" class="divide-y divide-gray-200 text-gray-700">
                    @foreach($totals as $code => $row)
                        <tr class="hover:bg-gray-50 align-top">
                            <td class="py-0.5 px-3 font-medium">{{ $code }}</td>
                            <td class="py-0.5 px-3">{{ $row['itemname'] }}</td>
                            <td class="py-0.5 px-3">{{ $row['unit'] }}</td>
                            <td class="py-0.5 px-3 text-right">{{ number_format($row['qty'], 2) }}</td>
                            <td class="py-0.5 px-3 text-center text-gray-500">
                                {{ implode(' + ', $row['parts']) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            
            <div class="flex justify-center mt-4 no-print">
            <select @if( !empty($details[0]->costcenter)) disabled @endif
                name="costcenter"
                class="w-full max-w-md border rounded px-3 py-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                aria-label="Pilih cost center"
                required
            >
                @if($costcenter->isEmpty())
                <option value="">— Cost center tidak tersedia —</option>
                @else
                <option value="" disabled {{ old('costcenter') ? '' : 'selected' }}>Pilih cost center…</option>
                @foreach ($costcenter->sortBy('costcenterdesc') as $c)
                    <option value="{{ $c['costcentercode'] }}" {{ $details[0]->costcenter == $c['costcentercode'] ? 'selected' : '' }}>
                        {{ $c['costcenterdesc'] }} ({{ $c['costcentercode'] }})
                    </option>
                @endforeach
                @endif
            </select>
            </div>
                <!-- Submit Button -->
                @if(strtoupper($details[0]->flagstatus) == 'ACTIVE' )
                <div class="flex justify-center mt-4">
                    <button @if($details->whereNotNull('nouse')->count()<1 == false) @endif 
                        type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-0.5 px-6 rounded shadow transition"
                    >
                        Penyerahan
                    </button>
                </div>
                @endif
            </form>
            
            <!-- Kembali Button - Moved inside container with closer spacing -->
            <div class="flex justify-center mt-3">
                <a href="{{ route('transaction.gudang.index') }}" 
                   class="bg-white inline-block bg-gray-200 text-gray-800 hover:bg-gray-300 font-semibold py-0.5 px-4 rounded shadow transition no-print">
                    ← Kembali
                </a>&nbsp;
                @if(strtoupper($details[0]->flagstatus) != 'ACTIVE' && strtoupper($details[0]->flagstatus) != 'WAIT_APPROVAL')
                <button type="button"
                    onclick="window.print()"
                    class="bg-white border border-gray-300 hover:bg-gray-100 text-gray-800 font-semibold py-0.5 px-4 rounded shadow no-print">
                    🖨️ Cetak
                </button>&nbsp;
                @endif
                @if (strtoupper($details[0]->flagstatus) != 'ACTIVE' && $returEligible > 0)
                <form action="{{ route('transaction.gudang.returall') }}" method="POST"
                      onsubmit="return confirm('Proses retur massal untuk {{ $returEligible }} item?')">
                  @csrf
                  <input type="hidden" name="rkhno" value="{{ $details[0]->rkhno }}">
                  <button type="submit"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-0.5 px-4 rounded shadow transition no-print">
                    Retur Semua ({{ $returEligible }})
                  </button>
                </form>
                @endif
            </div>
            
        <!-- koreksi -->
        {{-- ✅ PANEL DOKUMEN KOREKSI (dari usematerialapproval) --}}
        @if($koreksiSummary->isNotEmpty())
        @php
          $docs = $koreksiSummary->groupBy('approvalno');
        @endphp
        
        <div class="mt-6 bg-white shadow rounded p-3 no-print">
          <div class="font-semibold text-sm mb-2 text-center">
            Dokumen Koreksi — {{ $docs->count() }} Dokumen
          </div>
        
          <table class="w-full text-xs border border-gray-200">
            <thead class="bg-gray-50 text-gray-700">
              <tr>
                <th class="py-0.5 px-2 border text-left">ApprovalNo</th>
                <th class="py-0.5 px-2 border text-left">Itemcode</th>
                <th class="py-0.5 px-2 border text-left">Itemname</th>
                <th class="py-0.5 px-2 border text-right bg-green-50 text-green-700">USE</th>
                <th class="py-0.5 px-2 border text-right bg-red-50 text-red-700">RETUR</th>
              </tr>
            </thead>
        
            <tbody class="text-gray-700">
              @foreach($docs as $approvalno => $rows)
                @php $rowspan = $rows->count(); $i = 0; @endphp
        
                @foreach($rows as $r)
                <tr class="hover:bg-gray-50">
                  @if($i === 0)
                    <td class="py-0.5 px-2 border font-semibold align-top" rowspan="{{ $rowspan }}">
                      {{ $approvalno }} 
                        <span class="ml-2 inline-block px-2 py-0.5 rounded bg-gray-100 text-gray-700 text-[10px] font-semibold">
                            {{ str_replace('_',' ', $r->flagstatus ?? '-') }}
                        </span>
                      <div class="text-[10px] text-gray-500">{{ $rowspan }} item</div>
                    </td>
                  @endif
        
                  <td class="py-0.5 px-2 border font-medium">{{ $r->itemcode }}</td>
                  <td class="py-0.5 px-2 border">{{ $r->itemname ?? '-' }}</td>
                  <td class="py-0.5 px-2 border text-right">{{ number_format((float)$r->qty_use, 2) }}</td>
                  <td class="py-0.5 px-2 border text-right">{{ number_format((float)$r->qty_retur, 2) }}</td>
                </tr>
                @php $i++; @endphp
                @endforeach
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
        

        {{-- ✅ RINGKASAN TOTAL PER ITEM (gabungan semua dokumen koreksi) --}}
        @if($totByItem->isNotEmpty())
        <div class="mt-4 bg-white shadow rounded p-3 no-print">
            <div class="font-semibold text-sm mb-2 text-center">
            TOTAL KOREKSI
            </div>

            <table class="w-full text-xs border border-gray-200">
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                <th class="py-0.5 px-2 border text-left">Itemcode</th>
                <th class="py-0.5 px-2 border text-left">Itemname</th>
                <th class="py-0.5 px-2 border text-right bg-green-50 text-green-700">Total USE</th>
                <th class="py-0.5 px-2 border text-right bg-red-50 text-red-700">Total RETUR</th>
                <th class="py-0.5 px-2 border text-right bg-blue-50 text-blue-700">Netto</th>
                </tr>
            </thead>

            <tbody class="text-gray-700">
                @foreach($totByItem as $t)
                <tr class="hover:bg-gray-50">
                    <td class="py-0.5 px-2 border font-medium">{{ $t->itemcode }}</td>
                    <td class="py-0.5 px-2 border">{{ $t->itemname }}</td>
                    <td class="py-0.5 px-2 border text-right">{{ number_format((float)$t->qty_use, 2) }}</td>
                    <td class="py-0.5 px-2 border text-right">{{ number_format((float)$t->qty_retur, 2) }}</td>
                    <td class="py-0.5 px-2 border text-right font-semibold">
                    {{ number_format((float)$t->qty_netto, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </div>
        @endif

        <!-- koreksi -->
        </div>
        
        </x-layout>
        
        <script>
            // recalculate total
            const fmt2 = n => (Number(n)||0).toFixed(2);
    
            const roundto5 = (num) => {
                if (num <= 0) return 0;

                const truncated = Math.floor(num * 100) / 100;
                if (truncated <= 0) return 0.05;

                return Math.ceil(truncated / 0.05) * 0.05;
            };
            
    
    function recalcTotals(){
      const totals = {};
    
      $('.item-select').each(function(){
        const $tr  = $(this).closest('tr');
        const $opt = $(this).find('option:selected');
    
        const code = $tr.find('.selected-itemcode').val() || $(this).val();
        const name = $opt.data('itemname') || '';
        const unit = $tr.find('.selected-unit').val() || $opt.data('measure') || '';
    
        const dosage = parseFloat(String($tr.find('.selected-dosage').val()).replace(/,/g,'')) || 0;
        const luas   = parseFloat($tr.find('.selected-luas').val()) || 0;
        const qtyRaw = dosage * luas;
    
        const rounddosage = parseInt($opt.data('rounddosage')) || 0;
    
        let qty;
        if (rounddosage) {
          qty = roundto5(qtyRaw);
        } else {
          qty = qtyRaw;
        }
    
        // ✅ BACA RETUR dari kolom "Qty Retur" (kolom index 5)
        const returText = $tr.find('td').eq(5).text().trim();
        const retur = parseFloat(returText) || 0;
    
        // ✅ INIT dengan retur & pemakaian
        if (!totals[code]) {
          totals[code] = {
            itemname: name,
            unit: unit,
            qty: 0,
            retur: 0,
            pemakaian: 0,
            parts: []
          };
        }
    
        totals[code].qty   += qty;
        totals[code].retur += retur;
        totals[code].parts.push(fmt2(qty));
      });
    
      // ✅ HITUNG PEMAKAIAN
      Object.keys(totals).forEach(code => {
        totals[code].pemakaian = totals[code].qty - totals[code].retur;
      });
    
      // ✅ RENDER 7 KOLOM
      $('#totals-body').html(
        Object.entries(totals).map(([code, r]) => `
          <tr class="hover:bg-gray-50 align-top">
            <td class="py-0.5 px-3 font-medium">${code}</td>
            <td class="py-0.5 px-3">${r.itemname || '-'}</td>
            <td class="py-0.5 px-3">${r.unit || '-'}</td>
            <td class="py-0.5 px-3 text-right">${fmt2(r.qty)}</td>
            <td class="py-0.5 px-3 text-center text-gray-500">${r.parts.join(' + ')}</td>
            <td class="py-0.5 px-3 text-right bg-red-50 font-semibold text-red-700">${fmt2(r.retur)}</td>
            <td class="py-0.5 px-3 text-right bg-green-50 font-semibold text-green-700">${fmt2(r.pemakaian)}</td>
          </tr>
        `).join('')
      );
    }
    
    
    
    // panggil sekali saat load
    $(document).ready(function(){
      $('.item-select').each(function(){ recalcRowQty($(this).closest('tr')); });
      recalcTotals();
    });
    
    // NEBENG handler yang sudah ada → cukup tambahkan recalcTotals()
    $('.item-select').on('change', function () {
      const row = $(this).closest('tr');
      const sel = $(this).find('option:selected');
    
      // (punya kamu) update dosage/itemcode/unit/name attr...
      const newItemcode = sel.val();
      const dosage      = sel.data('dosage');
      const measure     = sel.data('measure');
    
      const { lkhno, itemcode, plot } = (function(name){
        const m = [...name.matchAll(/\[([^\]]+)\]/g)].map(x=>x[1]);
        return { lkhno: m[0], itemcode: m[1], plot: m[2] };
      })($(this).attr('name'));
    
      row.find('.selected-dosage').val(dosage);
      row.find('.selected-itemcode').val(newItemcode);
      row.find('.selected-unit').val(measure);
    
      $(this).attr('name',                 `itemcode[${lkhno}][${newItemcode}][${plot}]`);
      row.find('.selected-dosage').attr('name', `dosage[${lkhno}][${newItemcode}][${plot}]`);
      row.find('.selected-unit').attr('name',   `unit[${lkhno}][${newItemcode}][${plot}]`);
      row.find('.selected-luas').attr('name',   `luas[${lkhno}][${newItemcode}][${plot}]`);
    
      recalcRowQty(row);
      recalcTotals(); // <<< cukup tambahkan ini
    });
    
    $(document).on('input', '.selected-dosage', function(){
      recalcRowQty($(this).closest('tr'));
      recalcTotals(); // <<< dan ini
    });
            // tutup recalculate total 
    
            function parsePath(name){
              const m = [...name.matchAll(/\[([^\]]+)\]/g)].map(x=>x[1]);
              return { lkhno: m[0], itemcode: m[1], plot: m[2] };
            }
            
            function recalcRowQty(row){
              const dosage = parseFloat(String(row.find('.selected-dosage').val()).replace(/,/g,'')) || 0;
              const luas   = parseFloat(row.find('.selected-luas').val()) || 0;
              const qtyRaw    = dosage * luas;
              const opt         = row.find('.item-select option:selected');
              const rounddosage = parseInt(opt.data('rounddosage')) || 0;
    
                let qty;
                if (rounddosage) {
                    qty = roundto5(qtyRaw);     // dibulatkan
                } else {
                    qty = qtyRaw;                // tidak dibulatkan
                }
    
             console.log('recalcRowQty:', {dosage, luas, qtyRaw, qty});
    
              row.find('.labelqty').text(qty.toFixed(2));
            }
            
            $(document).ready(function(){
              $('.item-select').each(function(){ recalcRowQty($(this).closest('tr')); });
            });
            
            $('.item-select').on('change', function () {
              const row = $(this).closest('tr');
              const selected    = $(this).find('option:selected');
              const newItemcode = selected.val();
              const dosage      = selected.data('dosage');
              const { lkhno, itemcode, plot } = parsePath($(this).attr('name'));
            
              row.find('.selected-dosage').val(dosage);
              row.find('.selected-itemcode').val(newItemcode);
            
              $(this).attr('name',                 `itemcode[${lkhno}][${newItemcode}][${plot}]`);
              row.find('.selected-dosage').attr('name', `dosage[${lkhno}][${newItemcode}][${plot}]`);
              row.find('.selected-unit').attr('name',   `unit[${lkhno}][${newItemcode}][${plot}]`);
              row.find('.selected-luas').attr('name',   `luas[${lkhno}][${newItemcode}][${plot}]`);
            
              
              recalcRowQty(row);
            });
            
            $(document).on('input', '.selected-dosage', function(){
              recalcRowQty($(this).closest('tr'));
            });
        </script>
            