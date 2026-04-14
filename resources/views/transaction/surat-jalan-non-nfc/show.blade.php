<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div class="mx-auto py-1">

    {{-- Back --}}
    <div class="mb-3">
      <a href="{{ route('transaction.surat-jalan-non-nfc.index') }}"
         class="inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke daftar
      </a>
    </div>

    <div class="bg-white rounded-md shadow-md overflow-hidden">

      {{-- Header card --}}
      <div class="px-5 py-4 border-b flex items-start justify-between">
        <div>
          <div class="flex items-center gap-2 mb-1">
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 text-[11px] font-semibold border border-indigo-200">
              NON-NFC / WEB INPUT
            </span>
            @if(is_null($record->approvalstatus))
              <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-600 text-[11px] font-semibold border border-amber-200">Menunggu Approval</span>
            @elseif($record->approvalstatus == 1)
              @if($record->nonnfc_printed)
                <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 text-[11px] font-semibold border border-blue-200">Sudah Cetak</span>
              @else
                <span class="px-2 py-0.5 rounded-full bg-green-50 text-green-700 text-[11px] font-semibold border border-green-200">Approved</span>
              @endif
            @else
              <span class="px-2 py-0.5 rounded-full bg-red-50 text-red-600 text-[11px] font-semibold border border-red-200">Ditolak</span>
            @endif
          </div>
          <h1 class="text-lg font-bold text-gray-900 font-mono">{{ $record->suratjalanno }}</h1>
          <p class="text-xs text-gray-400 mt-0.5">
            Dibuat: {{ $record->formatted_createdat }} oleh <span class="font-medium text-gray-600">{{ $record->nonnfc_createdby }}</span>
            @if($record->approvalstatus == 1 && $record->approved_by)
              &nbsp;·&nbsp; Disetujui: {{ $record->formatted_approvedat }} oleh <span class="font-medium text-gray-600">{{ $record->approved_by }}</span>
            @endif
            @if($record->rejection_reason)
              &nbsp;·&nbsp; <span class="text-red-500">Ditolak: {{ $record->rejection_reason }}</span>
            @endif
          </p>
        </div>
        <div class="text-xs text-gray-400 font-mono">{{ $record->transactionnumber }}</div>
      </div>

      {{-- Body: 2 kolom --}}
      <div class="px-5 py-5 grid grid-cols-2 gap-x-10 gap-y-1 text-sm">

        {{-- Kolom kiri --}}
        <div class="space-y-3">

          <div>
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Identitas SJ</p>
            <dl class="space-y-1 text-xs">
              <div class="flex gap-2"><dt class="w-36 text-gray-500 flex-shrink-0">Mandor</dt><dd class="font-medium text-gray-800">{{ $record->mandorid ?? '-' }}</dd></div>
              <div class="flex gap-2"><dt class="w-36 text-gray-500 flex-shrink-0">No. Surat Jalan</dt><dd class="font-semibold font-mono text-gray-800">{{ $record->suratjalanno }}</dd></div>
            </dl>
          </div>

          <div>
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 border-t pt-2">Plot & Tanaman</p>
            <dl class="space-y-1 text-xs">
              <div class="flex gap-2"><dt class="w-36 text-gray-500 flex-shrink-0">Plot</dt><dd class="text-gray-800">{{ $record->plot ?? '-' }}</dd></div>
              <div class="flex gap-2"><dt class="w-36 text-gray-500 flex-shrink-0">Varietas</dt><dd class="text-gray-800">{{ $record->varietas ?? '-' }}</dd></div>
              <div class="flex gap-2"><dt class="w-36 text-gray-500 flex-shrink-0">Kategori</dt><dd class="text-gray-800">{{ $record->kategori ?? '-' }}</dd></div>
              <div class="flex gap-2"><dt class="w-36 text-gray-500 flex-shrink-0">Umur</dt><dd class="text-gray-800">{{ $record->umur ? $record->umur . ' bulan' : '-' }}</dd></div>
              <div class="flex gap-2"><dt class="w-36 text-gray-500 flex-shrink-0">Kode Tebang</dt><dd class="text-gray-800">{{ $record->kodetebang ?? '-' }}</dd></div>
              <div class="flex gap-2"><dt class="w-36 text-gray-500 flex-shrink-0">Tgl Tebang</dt><dd class="text-gray-800">{{ $record->tanggaltebang ? date('d/m/Y', strtotime($record->tanggaltebang)) : '-' }}</dd></div>
              <div class="flex gap-2"><dt class="w-36 text-gray-500 flex-shrink-0">Tgl Angkut</dt><dd class="text-gray-800">{{ $record->tanggalangkut ? date('d/m/Y H:i', strtotime($record->tanggalangkut)) : '-' }}</dd></div>
            </dl>
          </div>

        </div>

        {{-- Kolom kanan --}}
        <div class="space-y-3">

          <div>
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Kendaraan & Personil</p>
            <dl class="space-y-1 text-xs">
              <div class="flex gap-2"><dt class="w-36 text-gray-500 flex-shrink-0">No. Kendaraan</dt><dd class="text-gray-800">{{ $record->nomorkendaraan ?? '-' }}</dd></div>
              <div class="flex gap-2"><dt class="w-36 text-gray-500 flex-shrink-0">No. Polisi</dt><dd class="font-semibold text-gray-800">{{ $record->nomorpolisi ?? '-' }}</dd></div>
              <div class="flex gap-2"><dt class="w-36 text-gray-500 flex-shrink-0">Nama Supir</dt><dd class="text-gray-800">{{ $record->namasupir ?? '-' }}</dd></div>
              <div class="flex gap-2"><dt class="w-36 text-gray-500 flex-shrink-0">Kontraktor</dt><dd class="text-gray-800">{{ $record->namakontraktor ?? '-' }}</dd></div>
              <div class="flex gap-2"><dt class="w-36 text-gray-500 flex-shrink-0">Sub Kontraktor</dt><dd class="text-gray-800">{{ $record->namasubkontraktor ?? '-' }}</dd></div>
            </dl>
          </div>

          <div>
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 border-t pt-2">Info Teknis</p>
            <dl class="space-y-1 text-xs">
              <div class="flex gap-2"><dt class="w-36 text-gray-500 flex-shrink-0">Langsir</dt><dd class="text-gray-800">{{ $record->langsir ? 'Ya' : 'Tidak' }}</dd></div>
              <div class="flex gap-2"><dt class="w-36 text-gray-500 flex-shrink-0">Tebu Sulit</dt><dd class="text-gray-800">{{ $record->tebusulit ? 'Ya' : 'Tidak' }}</dd></div>
              <div class="flex gap-2"><dt class="w-36 text-gray-500 flex-shrink-0">Kend. Kontraktor</dt><dd class="text-gray-800">{{ $record->kendaraankontraktor ? 'Ya' : 'Tidak' }}</dd></div>
              <div class="flex gap-2"><dt class="w-36 text-gray-500 flex-shrink-0">Muat GL</dt><dd class="text-gray-800">{{ $record->muatgl ? 'Ya' : 'Tidak' }}</dd></div>
            </dl>
          </div>

          @if($record->keterangan)
          <div>
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 border-t pt-2">Keterangan</p>
            <p class="text-xs text-gray-700">{{ $record->keterangan }}</p>
          </div>
          @endif

          <div>
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 border-t pt-2">Lampiran</p>
            @if($lampiranUrl)
              @php $ext = strtolower(pathinfo($record->attachment, PATHINFO_EXTENSION)); @endphp
              @if($ext === 'pdf')
                <iframe src="{{ $lampiranUrl }}" class="w-full rounded-md border border-gray-200" style="height:500px;"></iframe>
              @else
                <img src="{{ $lampiranUrl }}" alt="Lampiran" class="max-w-full rounded-md border border-gray-200 shadow-sm">
              @endif
            @else
              <p class="text-xs text-gray-400 italic">Tidak ada lampiran.</p>
            @endif
          </div>

        </div>

      </div>

      @if($record->approvalstatus == 1 && $record->nonnfc_printed && $record->tanggalcetakpossecurity)
      <div class="mx-5 mb-4 px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-700">
        Dicetak pada: <span class="font-semibold">{{ date('d/m/Y H:i', strtotime($record->tanggalcetakpossecurity)) }}</span>
      </div>
      @endif

    </div>
  </div>

</x-layout>
