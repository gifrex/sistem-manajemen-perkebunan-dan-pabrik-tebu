<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  @once
  <style>
    .toast-center{
      position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);
      background:#fff9c4; border:1px solid #f6e27c; /* success: soft yellow */
      border-radius:12px; padding:32px 40px;
      box-shadow:0 12px 32px rgba(0,0,0,.28);
      z-index:9999; text-align:center; color:#3b3b3b;
      font:600 16px/1.35 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;
    }
    .toast-error{ background:#ffe4e6; border-color:#fda4af; } /* soft red */
      @media print {
        .toast-center { display:none !important; }
        .print-only { display:inline !important; }
        .no-print { display:none !important; }

        table {
          border-collapse: collapse !important;
          width: 100% !important;
        }

        .border {
          border: none !important;
        }

        th, td {
          border: 1px solid #666 !important;
          padding: 6px 8px !important;
          vertical-align: middle !important;
        }

        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }

        tr { page-break-inside: avoid !important; }

        .tj-print, .tc-print, .tv-print {
          display: inline-block;
          min-width: 24px;
          text-align: right;
          font-weight: 600;
        }

        .tj-result,
        .tc-result,
        .tv-result {
          all: unset;
          text-align: right;
          width: 100%;
        }
      }
    @media screen { .print-only { display:none; } }
  </style>
@endonce

@if (session('success'))
  <div class="toast-center" role="alert" aria-live="assertive" onclick="this.remove()">
    {{ session('success') }}
  </div>
  <script>setTimeout(()=>document.querySelector('.toast-center')?.remove(),3000)</script>
@endif

{{-- Opsional: versi error (pakai if ($errors->any())) --}}
@if ($errors->any())
  <div class="toast-center toast-error" role="alert" aria-live="assertive" onclick="this.remove()">
    Terjadi kesalahan:
    <ul style="text-align:left;margin:6px 0 0;padding-left:18px;">
      @foreach ($errors->all() as $err)
        <li>{{ $err }}</li>
      @endforeach
    </ul>
  </div>
  <script>setTimeout(()=>document.querySelector('.toast-error')?.remove(),6000)</script>
@endif





  <!-- Form -->
  <form action="{{ route('transaction.pias.submit', ['rkhno' => $data[0]->rkhno]) }}" method="POST">
    @csrf
    <input type="hidden" name="rkhno" value="{{ $data[0]->rkhno }}"> {{-- ADDED: agar validasi rkhno di submit lolos --}}

  <div class="w-full p-4">
    <!-- Container full width tanpa center alignment -->
    <div class="w-full mb-4">
      <div class="grid grid-cols-4 gap-3">
        <!-- Card Input -->
        <div class="col-span-1 border rounded-md p-3 bg-white shadow-sm no-print">
          <h3 class="text-base font-bold mb-3">Input TJ, TC & TV</h3>
          <div class="space-y-3">
            <div>
              <label class="block mb-1 text-sm">Total TJ (stok opsional)</label>
              <input type="number" name="inputTJ" id="inputTJ" class="w-full border rounded-md p-1 bg-gray-50 text-sm" placeholder="Masukkan Total TJ" 
              value="{{ old('inputTJ', optional($hdr)->tj)*1 ?? ''}}" onchange="render()">
            </div>
            <div>
              <label class="block mb-1 text-sm">Total TC (stok opsional)</label>
              <input type="number" name="inputTC" id="inputTC" class="w-full border rounded-md p-1 bg-gray-50 text-sm" placeholder="Masukkan Total TC" 
              value="{{ old('inputTC', optional($hdr)->tc)*1 ?? '' }}" onchange="render()">
            </div>
            <div>
              <label class="block mb-1 text-sm">Total TV (stok opsional)</label>
              <input type="number" name="inputTV" id="inputTV" class="w-full border rounded-md p-1 bg-gray-50 text-sm" placeholder="Masukkan Total TV" 
              value="{{ old('inputTV', optional($hdr)->tv)*1 ?? '' }}">
            </div>
            <div style="text-align:right"> 
              <label class="text-sm font-medium">Dosis / Ha</label>
              <select name="dosage" id="dosage" class="border rounded p-2">
                @for($i=10;$i<=25;$i++)
                  <option value="{{ $i }}" {{ old('dosage', $hdr->dosage ?? 25)==$i ? 'selected':'' }}>{{ $i }}</option>
                @endfor
              </select>
            </div>
          </div>
        </div>
  
        <!-- Card Summary - lebih lebar -->
        <div id="summaryCard" class="col-span-2 border rounded-md p-1 bg-white shadow-sm">
        <h3 class="text-base font-bold mb-1 text-center">Ringkasan Kebutuhan</h3>
        <div class="space-y-2">
          <div class="border rounded p-1 bg-blue-50">
            <div class="text-xs text-gray-600 mb-1 text-center">Total Kebutuhan</div>
            <div class="flex items-center justify-center gap-1">
              <div class="text-base font-bold">TJ <span id="totalTJ">0</span></div>
              <div class="text-base font-bold">TC <span id="totalTC">0</span></div>
              <div class="text-base font-bold">TV <span id="totalTV">0</span></div>
            </div>
          </div>
          <div class="border rounded p-1 bg-gray-50">
            <div class="text-xs text-gray-600 mb-1 text-center">Stok & Sisa</div>
            <div class="grid grid-cols-3 gap-1">
              <div class="text-center">
                <div class="text-xs">Stok TJ</div>
                <div class="text-sm font-semibold" id="stokTJ">0</div>
                <div class="text-xs">Sisa: <span id="sisaTJ">0</span></div>
              </div>
              <div class="text-center">
                <div class="text-xs">Stok TC</div>
                <div class="text-sm font-semibold" id="stokTC">0</div>
                <div class="text-xs">Sisa: <span id="sisaTC">0</span></div>
              </div>
              <div class="text-center">
                <div class="text-xs">Stok TV</div>
                <div class="text-sm font-semibold" id="stokTV">0</div>
                <div class="text-xs">Sisa: <span id="sisaTV">0</span></div>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-3 gap-1">
            <div id="statusTJ" class="text-center text-xs font-medium rounded py-1"></div>
            <div id="statusTC" class="text-center text-xs font-medium rounded py-1"></div>
            <div id="statusTV" class="text-center text-xs font-medium rounded py-1"></div>
          </div>
        </div>
      </div>

  
        <!-- Card RKH Detail -->
        <div class="col-span-1 border rounded-md p-3 bg-white shadow-sm no-print">
          <h3 class="text-base font-bold mb-3">RKH Detail</h3>
          <div class="space-y-1 text-sm">
            <p><strong>RKH No:</strong> {{ $data[0]->rkhno }}</p>
            <p><strong>Mandor:</strong> {{ $data[0]->mandor_name }}</p>
            <p><strong>Total Luas:</strong> {{ $data[0]->totalluas }} Ha</p>
          </div>
        </div>
      </div>
    </div>
  
    <!-- Tabel plot tetap sama -->
    <div class="border rounded-md bg-white shadow-sm">
      <div class="p-4 bg-gray-100">
        <h3 class="text-lg font-bold">Detail Plot</h3>
        <h6 class="text-sm print-only">RKH {{ $data[0]->rkhno }} — Mandor {{ $data[0]->mandor_name }} — Total Luas {{ $data[0]->totalluas }} Ha</h6>
      </div>

      <div class="overflow-x-auto">

        <table class="w-full">
          <thead>
            <tr class="bg-gray-50">
              <th class="p-3 border-b">Plot</th>
              <th class="p-3 border-b">Bulan</th>
              <th class="p-3 border-b">Type</th>
              <th class="p-3 border-b">Varietas</th>
              <th class="p-3 border-b">Luas (Ha)</th>
              <th class="p-3 border-b bg-blue-50 font-semibold">TJ</th>
              <th class="p-3 border-b bg-green-50 font-semibold">TC</th>
              <th class="p-3 border-b bg-yellow-50 font-semibold">TV</th>
              <th class="p-3 border-b no-print">Pembagian</th>
            </tr>
          </thead>
          <tbody id="plotTable">
            @foreach($data as $item)
              @php
                $hari = (int) floor(abs($item->rkhdate->diffInRealDays($item->tanggalulangtahun)));
                $bulan = ceil($hari / 30);
                $exist = $lst->where('blok', $item->blok)->where('plot', $item->plot)->first();
                $existTJ = optional($exist)->tj_alloc ?? optional($exist)->tj ?? null;
                $existTC = optional($exist)->tc_alloc ?? optional($exist)->tc ?? null;
                $existTV = optional($exist)->tv_alloc ?? optional($exist)->tv ?? null;
              @endphp
              <tr class="hover:bg-gray-50"
                  data-luas="{{ $item->luasrkh }}" data-umur="{{ $hari }}" 
                  data-tj="{{ $existTJ ?? '' }}" data-tc="{{ $existTC ?? '' }}" 
                  data-tv="{{ $existTV ?? '' }}">
                <td class="p-3 border-b">{{ $item->plot }}</td>
                <td class="p-3 border-b">
                  <span class="inline-flex gap-1">
                    <span class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 text-xs font-semibold"> {{ $bulan }}</span>
                    {{-- <span class="text-gray-700 text-sm">({{ $hari }} hari sejak tanam)</span> --}}
                  </span>
                </td> 
                <td class="p-3 border-b text-center">{{ $item->kodestatus }}</td>
                <td class="p-3 border-b">{{ $item->kodevarietas }}</td>
                <td class="p-3 border-b text-right">{{ $item->luasrkh }}</td>
                
                {{-- ✅ INPUT TJ dengan hidden blok & plot --}}
                <td class="p-3 border-b bg-blue-50 font-semibold text-right">
                  <input type="hidden" name="rows[{{ $loop->index }}][blok]" value="{{ $item->blok }}">
                  <input type="hidden" name="rows[{{ $loop->index }}][plot]" value="{{ $item->plot }}">
                  <input type="hidden" name="rows[{{ $loop->index }}][lkhno]" value="{{ $item->lkhno }}">
                  <input
                    type="number" step="1" min="0"
                    class="tj-result w-full text-right border rounded px-2 py-1 bg-white"
                    value="{{ old("rows.$loop->index.tj", isset($existTJ) ? (int)$existTJ : '') }}"
                    name="rows[{{ $loop->index }}][tj]"
                  >
                </td>
                
                {{-- ✅ INPUT TC --}}
                <td class="p-3 border-b bg-green-50 font-semibold text-right">
                  <input
                    type="number" step="1" min="0"
                    class="tc-result w-full text-right border rounded px-2 py-1 bg-white"
                    value="{{ old("rows.$loop->index.tc", isset($existTC) ? (int)$existTC : '') }}"
                    name="rows[{{ $loop->index }}][tc]"
                  >
                </td>

                <td class="p-3 border-b bg-yellow-50 font-semibold text-right">
                  <input
                    type="number" step="1" min="0"
                    class="tv-result w-full text-right border rounded px-2 py-1 bg-white"
                    value="{{ old("rows.$loop->index.tv", isset($existTV) ? (int)$existTV : '') }}"
                    name="rows[{{ $loop->index }}][tv]"
                  >
                </td>
                
                {{-- ✅ FORMULA (tanpa hidden input lagi) --}}
                <td class="p-3 border-b pias-formula text-left text-sm no-print">
                  {{-- Konten formula akan di-generate oleh JavaScript --}}
                </td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr class="font-bold">
              <td class="p-3 border-t" colspan="5" style="text-align:right">TOTAL</td>
              <td class="p-3 border-t bg-blue-50 text-right">
                <span id="sumTJCell">0</span>
              </td>
              <td class="p-3 border-t bg-green-50 text-right">
                <span id="sumTCCell">0</span>
              </td>
              <td class="p-3 border-t bg-yellow-50 text-right">
                <span id="sumTVCell">0</span>
              </td>
              <td class="p-3 border-t no-print"></td>
            </tr>
          </tfoot>
        </table>

        @if($hdr)
        <div class="flex justify-center mt-2 text-sm">
          Sudah generated ({{ \Carbon\Carbon::parse($hdr->generateddate)->format('d M Y H:i') }})
        </div>
        @endif

        <div class="flex justify-center mt-2 mb-4">
          <a href="{{ route('transaction.pias.index') }}" 
          class="bg-white inline-block bg-gray-200 text-gray-800 hover:bg-gray-300 font-semibold py-2 px-4 rounded shadow transition no-print">
           ← Kembali
          </a> &nbsp;
          <button
              type="submit"
              class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded shadow transition no-print"
          >
            {{ $hdr ? 'Edit Data' : 'Generate' }}
          </button>
        </div>
        @if($hdr)
        <div class="flex justify-center mt-2 mb-6"> {{-- ADDED: tombol Print --}}
          <button
            type="button"
            onclick="window.print()"
            class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-6 rounded shadow transition no-print"
          >
            Print
          </button>
        </div>
        @endif

      </div>
    </div>
  </div>
  <input type="hidden" name="totalNeedTJ" id="totalNeedTJ_hidden" value="0">
  <input type="hidden" name="totalNeedTC" id="totalNeedTC_hidden" value="0">
  <input type="hidden" name="totalNeedTV" id="totalNeedTV_hidden" value="0">
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const inputTJ = document.getElementById('inputTJ');
  const inputTC = document.getElementById('inputTC');
  const inputTV = document.getElementById('inputTV');
  const plotTable = document.getElementById('plotTable');

  const totalTJEl = document.getElementById('totalTJ');
  const totalTCEl = document.getElementById('totalTC');
  const totalTVEl = document.getElementById('totalTV');

  const stokTJEl  = document.getElementById('stokTJ');
  const stokTCEl  = document.getElementById('stokTC');
  const stokTVEl  = document.getElementById('stokTV');

  const sisaTJEl  = document.getElementById('sisaTJ');
  const sisaTCEl  = document.getElementById('sisaTC');
  const sisaTVEl  = document.getElementById('sisaTV');

  const summary   = document.getElementById('summaryCard');
  const statusTJ  = document.getElementById('statusTJ');
  const statusTC  = document.getElementById('statusTC');
  const statusTV  = document.getElementById('statusTV');

  const sumTJCell = document.getElementById('sumTJCell');
  const sumTCCell = document.getElementById('sumTCCell');
  const sumTVCell = document.getElementById('sumTVCell');

  const dosageEl = document.getElementById('dosage');

  const pcts = [
    { tj: 0.70, tc: 0.165, tv: 0.135 },
    { tj: 0.70, tc: 0.165, tv: 0.135 },
    { tj: 0.60, tc: 0.22,  tv: 0.18  },
    { tj: 0.50, tc: 0.275, tv: 0.225 },
    { tj: 0.40, tc: 0.33,  tv: 0.27  },
    { tj: 0.30, tc: 0.385, tv: 0.315 },
    { tj: 0.30, tc: 0.385, tv: 0.315 },
    { tj: 0.30, tc: 0.385, tv: 0.315 },
    { tj: 0.30, tc: 0.385, tv: 0.315 },
    { tj: 0.30, tc: 0.385, tv: 0.315 }
  ];

  const NF0 = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
  const fmt0 = (x) => NF0.format(Math.round(x || 0));

  function getDosage(){
    return parseFloat(dosageEl?.value || '25');
  }

  function hasAllStocks() {
    const tj = parseFloat(inputTJ.value);
    const tc = parseFloat(inputTC.value);
    const tv = parseFloat(inputTV.value);

    return tj > 0 && Number.isFinite(tj) &&
           tc > 0 && Number.isFinite(tc) &&
           tv > 0 && Number.isFinite(tv);
  }

  function sumInputs(sel){
    return [...document.querySelectorAll(sel)].reduce((a, el) => a + (parseFloat(el.value) || 0), 0);
  }

  // ================= CRC32 (match PHP) =================
  const CRC_TABLE = (() => {
    const t = new Uint32Array(256);
    for (let n = 0; n < 256; n++) {
      let c = n;
      for (let k = 0; k < 8; k++) {
        c = (c & 1) ? (0xEDB88320 ^ (c >>> 1)) : (c >>> 1);
      }
      t[n] = c >>> 0;
    }
    return t;
  })();

  function crc32(str){
    let crc = 0 ^ (-1);
    for (let i = 0; i < str.length; i++) {
      crc = (crc >>> 8) ^ CRC_TABLE[(crc ^ str.charCodeAt(i)) & 0xFF];
    }
    return (crc ^ (-1)) >>> 0;
  }

  function allocateInt(needs, stock) {
    const n = needs.length;
    if (n === 0) return [];

    const sumNeedInt = needs.reduce((a,b) => a + Math.round(b), 0);
    const target = Math.min(Math.floor(stock || 0), sumNeedInt);

    if (target <= 0 || sumNeedInt <= 0) return Array(n).fill(0);

    const rkhInput = document.querySelector('input[name="rkhno"]');
    const seed = crc32(rkhInput?.value || '');

    const trs = Array.from(document.getElementById('plotTable').rows);
    const ids = trs.map(tr =>
      tr.getAttribute('data-id')?.trim() ||
      `${(tr.cells?.[0]?.textContent || '').trim()}|${(tr.cells?.[1]?.textContent || '').trim()}`
    );

    const cap = needs.map(v => Math.round(v));

    const sumFloat = needs.reduce((a,b)=>a+b, 0);
    const quotas = needs.map(v => (sumFloat > 0 ? (v / sumFloat * target) : 0));
    const fracs  = quotas.map(q => q - Math.floor(q));

    const base  = Math.floor(target / n);
    const alloc = Array(n).fill(0).map((_, i) => Math.min(base, cap[i]));
    let remain  = target - alloc.reduce((a,b)=>a+b,0);
    if (remain <= 0) return alloc;

    const needInt = needs.map(v => Math.round(v));
    const groups  = new Map();

    for (let i = 0; i < n; i++) {
      if (!groups.has(needInt[i])) groups.set(needInt[i], []);
      groups.get(needInt[i]).push(i);
    }

    const groupKeys = Array.from(groups.keys()).sort((a,b)=>b-a);

    const orderGroup = idxs => idxs.slice().sort((a,b)=>{
      const ha = (crc32(ids[a] || '') ^ seed) >>> 0;
      const hb = (crc32(ids[b] || '') ^ seed) >>> 0;
      if (ha === hb) {
        if (fracs[a] === fracs[b]) return a - b;
        return fracs[b] - fracs[a];
      }
      return ha - hb;
    });

    while (remain > 0) {
      let progressed = false;

      for (const k of groupKeys) {
        if (remain <= 0) break;

        const all = groups.get(k);
        const idxs = all.filter(i => alloc[i] < cap[i]);
        if (idxs.length === 0) continue;

        const ord = orderGroup(idxs);

        if (remain >= ord.length) {
          for (const i of ord) alloc[i] += 1;
          remain -= ord.length;
          progressed = true;
          continue;
        }

        for (let t = 0; t < remain; t++) alloc[ord[t]] += 1;
        remain = 0;
        progressed = true;
        break;
      }

      if (!progressed) break;
    }

    return alloc;
  }

  const rows = Array.from(plotTable.rows);
  const meta = [];

  let needsTJ = [];
  let needsTC = [];
  let needsTV = [];

  let sumNeedTJIntConst = 0;
  let sumNeedTCIntConst = 0;
  let sumNeedTVIntConst = 0;

  function rebuildNeedsAndFormula() {
    needsTJ = [];
    needsTC = [];
    needsTV = [];

    const dosage = getDosage();

    for (let i = 0; i < rows.length; i++) {
      const r = rows[i];
      const luas = parseFloat(r.dataset.luas) || 0;
      const umur = parseInt(r.dataset.umur) || 0;
      const bulan = Math.max(1, Math.ceil(umur / 30));
      const p = pcts[Math.min(bulan, 10) - 1] || { tj: 0.5, tc: 0.3, tv: 0.2 };

      const total = luas * dosage;
      const needTJ = total * p.tj;
      const needTC = total * p.tc;
      const needTV = total * p.tv;

      const tjEl = r.querySelector('.tj-result');
      const tcEl = r.querySelector('.tc-result');
      const tvEl = r.querySelector('.tv-result');
      const fEl  = r.querySelector('.pias-formula');

      if (!meta[i]) {
        meta[i] = { tjEl, tcEl, tvEl, fEl, total, bulan, needTJ, needTC, needTV };
      } else {
        meta[i].tjEl = tjEl;
        meta[i].tcEl = tcEl;
        meta[i].tvEl = tvEl;
        meta[i].fEl = fEl;
        meta[i].total = total;
        meta[i].bulan = bulan;
        meta[i].needTJ = needTJ;
        meta[i].needTC = needTC;
        meta[i].needTV = needTV;
      }

      needsTJ.push(needTJ);
      needsTC.push(needTC);
      needsTV.push(needTV);

      if (fEl) {
        fEl.innerHTML =
        `<span class="inline-block rounded px-0.5 py-0 text-xs bg-blue-100 font-semibold"> ${Math.round(p.tj * 100)}%</span>, ` +
        `<span class="inline-block rounded px-0.5 py-0 text-xs bg-green-100 font-semibold"> ${Math.round(p.tc * 100)}%</span>, ` +
        `<span class="inline-block rounded px-0.5 py-0 text-xs bg-yellow-100 font-semibold"> ${Math.round(p.tv * 100)}%</span> <br>` +
        `Butuh ${fmt0(total)}: <br>` +
        `<span class="inline-block rounded px-0 py-0 text-xs bg-blue-100 "> ${needTJ.toFixed(2)}</span>, ` +
        `<span class="inline-block rounded px-0 py-0 text-xs bg-green-100 "> ${needTC.toFixed(2)}</span>, ` +
        `<span class="inline-block rounded px-0 py-0 text-xs bg-yellow-100 "> ${needTV.toFixed(2)}</span>`;
      }
      
    }
    

    const needTJIntArr = needsTJ.map(v => Math.round(v));
    const needTCIntArr = needsTC.map(v => Math.round(v));
    const needTVIntArr = needsTV.map(v => Math.round(v));

    sumNeedTJIntConst = needTJIntArr.reduce((a,b)=>a+b,0);
    sumNeedTCIntConst = needTCIntArr.reduce((a,b)=>a+b,0);
    sumNeedTVIntConst = needTVIntArr.reduce((a,b)=>a+b,0);

    if (totalTJEl) totalTJEl.textContent = sumNeedTJIntConst.toLocaleString('id-ID');
    if (totalTCEl) totalTCEl.textContent = sumNeedTCIntConst.toLocaleString('id-ID');
    if (totalTVEl) totalTVEl.textContent = sumNeedTVIntConst.toLocaleString('id-ID');

    document.getElementById('totalNeedTJ_hidden').value = sumNeedTJIntConst;
    document.getElementById('totalNeedTC_hidden').value = sumNeedTCIntConst;
    document.getElementById('totalNeedTV_hidden').value = sumNeedTVIntConst;
  }

  let initTJ = 0, initTC = 0, initTV = 0;
  for (const r of rows) {
    initTJ += parseFloat(r.dataset.tj) || 0;
    initTC += parseFloat(r.dataset.tc) || 0;
    initTV += parseFloat(r.dataset.tv) || 0;
  }

  if (sumTJCell) sumTJCell.textContent = fmt0(initTJ);
  if (sumTCCell) sumTCCell.textContent = fmt0(initTC);
  if (sumTVCell) sumTVCell.textContent = fmt0(initTV);

  const stokTJ0 = parseFloat(inputTJ.value) || 0;
  const stokTC0 = parseFloat(inputTC.value) || 0;
  const stokTV0 = parseFloat(inputTV.value) || 0;

  if (stokTJEl) stokTJEl.textContent = fmt0(stokTJ0);
  if (stokTCEl) stokTCEl.textContent = fmt0(stokTC0);
  if (stokTVEl) stokTVEl.textContent = fmt0(stokTV0);

  if (sisaTJEl) sisaTJEl.textContent = fmt0(Math.floor(stokTJ0) - initTJ);
  if (sisaTCEl) sisaTCEl.textContent = fmt0(Math.floor(stokTC0) - initTC);
  if (sisaTVEl) sisaTVEl.textContent = fmt0(Math.floor(stokTV0) - initTV);

  rebuildNeedsAndFormula();

  let lastTJ = null;
  let lastTC = null;
  let lastTV = null;

  function render(){
    if (!hasAllStocks()) {
      resetUI();
      return;
    }

    const stokTJ = parseFloat(inputTJ.value) || 0;
    const stokTC = parseFloat(inputTC.value) || 0;
    const stokTV = parseFloat(inputTV.value) || 0;

    if (stokTJ === lastTJ && stokTC === lastTC && stokTV === lastTV) return;

    lastTJ = stokTJ;
    lastTC = stokTC;
    lastTV = stokTV;

    const allocTJ = allocateInt(needsTJ, stokTJ);
    const allocTC = allocateInt(needsTC, stokTC);
    const allocTV = allocateInt(needsTV, stokTV);

    let sumAllocTJ = 0;
    let sumAllocTC = 0;
    let sumAllocTV = 0;

    for (let i = 0; i < meta.length; i++) {
      const m = meta[i];
      const aTJ = allocTJ[i] | 0;
      const aTC = allocTC[i] | 0;
      const aTV = allocTV[i] | 0;

      sumAllocTJ += aTJ;
      sumAllocTC += aTC;
      sumAllocTV += aTV;

      if (m.tjEl) {
        if (m.tjEl.tagName === 'INPUT') m.tjEl.value = String(aTJ);
        else m.tjEl.textContent = fmt0(aTJ);
      }
      if (m.tcEl) {
        if (m.tcEl.tagName === 'INPUT') m.tcEl.value = String(aTC);
        else m.tcEl.textContent = fmt0(aTC);
      }
      if (m.tvEl) {
        if (m.tvEl.tagName === 'INPUT') m.tvEl.value = String(aTV);
        else m.tvEl.textContent = fmt0(aTV);
      }
    }

    if (sumTJCell) sumTJCell.textContent = fmt0(sumAllocTJ);
    if (sumTCCell) sumTCCell.textContent = fmt0(sumAllocTC);
    if (sumTVCell) sumTVCell.textContent = fmt0(sumAllocTV);

    totalTJEl.textContent = sumNeedTJIntConst.toLocaleString('id-ID');
    totalTCEl.textContent = sumNeedTCIntConst.toLocaleString('id-ID');
    totalTVEl.textContent = sumNeedTVIntConst.toLocaleString('id-ID');

    stokTJEl.textContent = fmt0(stokTJ);
    stokTCEl.textContent = fmt0(stokTC);
    stokTVEl.textContent = fmt0(stokTV);

    sisaTJEl.textContent = fmt0(Math.floor(stokTJ) - sumAllocTJ);
    sisaTCEl.textContent = fmt0(Math.floor(stokTC) - sumAllocTC);
    sisaTVEl.textContent = fmt0(Math.floor(stokTV) - sumAllocTV);

    const okTJ = sumAllocTJ >= sumNeedTJIntConst;
    const okTC = sumAllocTC >= sumNeedTCIntConst;
    const okTV = sumAllocTV >= sumNeedTVIntConst;

    statusTJ.textContent = okTJ ? 'TJ CUKUP' : 'TJ KURANG';
    statusTC.textContent = okTC ? 'TC CUKUP' : 'TC KURANG';
    statusTV.textContent = okTV ? 'TV CUKUP' : 'TV KURANG';

    statusTJ.className = `text-center text-sm font-medium rounded-md py-1 ${okTJ ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'}`;
    statusTC.className = `text-center text-sm font-medium rounded-md py-1 ${okTC ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'}`;
    statusTV.className = `text-center text-sm font-medium rounded-md py-1 ${okTV ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'}`;

    summary.classList.remove('hidden');
  }

  function resetUI(){
    for (const m of meta) {
      if (m.tjEl && m.tjEl.tagName === 'INPUT') m.tjEl.value = '';
      if (m.tcEl && m.tcEl.tagName === 'INPUT') m.tcEl.value = '';
      if (m.tvEl && m.tvEl.tagName === 'INPUT') m.tvEl.value = '';
    }
  }

  function recalcTotalsFromInputs(){
    const stokTJ = parseFloat(inputTJ.value) || 0;
    const stokTC = parseFloat(inputTC.value) || 0;
    const stokTV = parseFloat(inputTV.value) || 0;

    const sumTJ = sumInputs('.tj-result');
    const sumTC = sumInputs('.tc-result');
    const sumTV = sumInputs('.tv-result');

    if (sumTJCell) sumTJCell.textContent = fmt0(sumTJ);
    if (sumTCCell) sumTCCell.textContent = fmt0(sumTC);
    if (sumTVCell) sumTVCell.textContent = fmt0(sumTV);

    if (stokTJEl) stokTJEl.textContent = fmt0(stokTJ);
    if (stokTCEl) stokTCEl.textContent = fmt0(stokTC);
    if (stokTVEl) stokTVEl.textContent = fmt0(stokTV);

    sisaTJEl.textContent = fmt0(Math.floor(stokTJ) - sumTJ);
    sisaTCEl.textContent = fmt0(Math.floor(stokTC) - sumTC);
    sisaTVEl.textContent = fmt0(Math.floor(stokTV) - sumTV);

    if (totalTJEl) totalTJEl.textContent = sumNeedTJIntConst.toLocaleString('id-ID');
    if (totalTCEl) totalTCEl.textContent = sumNeedTCIntConst.toLocaleString('id-ID');
    if (totalTVEl) totalTVEl.textContent = sumNeedTVIntConst.toLocaleString('id-ID');

    document.getElementById('totalNeedTJ_hidden').value = sumNeedTJIntConst;
    document.getElementById('totalNeedTC_hidden').value = sumNeedTCIntConst;
    document.getElementById('totalNeedTV_hidden').value = sumNeedTVIntConst;

    const okTJ = sumTJ >= sumNeedTJIntConst;
    const okTC = sumTC >= sumNeedTCIntConst;
    const okTV = sumTV >= sumNeedTVIntConst;

    statusTJ.textContent = okTJ ? 'TJ CUKUP' : 'TJ KURANG';
    statusTC.textContent = okTC ? 'TC CUKUP' : 'TC KURANG';
    statusTV.textContent = okTV ? 'TV CUKUP' : 'TV KURANG';

    statusTJ.className = `text-center text-sm font-medium rounded-md py-1 ${okTJ ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'}`;
    statusTC.className = `text-center text-sm font-medium rounded-md py-1 ${okTC ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'}`;
    statusTV.className = `text-center text-sm font-medium rounded-md py-1 ${okTV ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'}`;
  }

  let timer;
  const IDLE = 500;

  function schedule(){
    clearTimeout(timer);
    if (hasAllStocks()) timer = setTimeout(render, IDLE);
    else resetUI();
  }

  inputTJ.addEventListener('input', schedule);
  inputTC.addEventListener('input', schedule);
  inputTV.addEventListener('input', schedule);

  inputTJ.addEventListener('change', schedule);
  inputTC.addEventListener('change', schedule);
  inputTV.addEventListener('change', schedule);

  plotTable.addEventListener('input', (e)=>{
    if (e.target.matches('.tj-result, .tc-result, .tv-result')) recalcTotalsFromInputs();
  });

  plotTable.addEventListener('change', (e)=>{
    if (e.target.matches('.tj-result, .tc-result, .tv-result')) recalcTotalsFromInputs();
  });

  inputTJ.addEventListener('change', recalcTotalsFromInputs);
  inputTC.addEventListener('change', recalcTotalsFromInputs);
  inputTV.addEventListener('change', recalcTotalsFromInputs);

  inputTJ.addEventListener('input', recalcTotalsFromInputs);
  inputTC.addEventListener('input', recalcTotalsFromInputs);
  inputTV.addEventListener('input', recalcTotalsFromInputs);

  dosageEl.addEventListener('change', function(){
    rebuildNeedsAndFormula();
    lastTJ = null;
    lastTC = null;
    lastTV = null;

    if (hasAllStocks()) render();
    else recalcTotalsFromInputs();
  });

  if (hasAllStocks()) {
    recalcTotalsFromInputs();
  } else {
    setTimeout(recalcTotalsFromInputs, 250);
  }
});
</script>



</x-layout>
