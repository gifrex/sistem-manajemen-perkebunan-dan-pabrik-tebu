<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <style>
        .cu-wrap { padding: 1.5rem; }
        .cu-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
        .cu-icon { width: 44px; height: 44px; border-radius: 10px; background: #EFF6FF; display: flex; align-items: center; justify-content: center; color: #3B82F6; flex-shrink: 0; }
        .cu-title { font-size: 1.25rem; font-weight: 700; color: #111827; margin: 0; }
        .cu-subtitle { font-size: 0.8rem; color: #6B7280; margin: 0; }

        .cu-card { background: #fff; border: 1px solid #E5E7EB; border-radius: 10px; padding: 1.25rem; margin-bottom: 1.25rem; }

        .cu-btn-check { display: inline-flex; align-items: center; gap: 0.5rem; background: #3B82F6; color: #fff; border: none; border-radius: 8px; padding: 0.55rem 1.25rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: background .15s; }
        .cu-btn-check:hover { background: #2563EB; }
        .cu-btn-check:disabled { opacity: .6; cursor: not-allowed; }

        .cu-alert { padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .85rem; }
        .cu-alert-error   { background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; }
        .cu-alert-warning { background: #FFFBEB; color: #92400E; border: 1px solid #FDE68A; }
        .cu-alert-success { background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; }

        .cu-section-title { font-size: .9rem; font-weight: 700; margin: 0 0 .75rem; display: flex; align-items: center; gap: .5rem; }
        .cu-badge { display: inline-block; font-size: .7rem; font-weight: 700; padding: .15rem .5rem; border-radius: 999px; }
        .cu-badge-red    { background: #FEE2E2; color: #B91C1C; }
        .cu-badge-yellow { background: #FEF9C3; color: #854D0E; }
        .cu-badge-green  { background: #DCFCE7; color: #166534; }

        .cu-table-wrap { overflow-x: auto; }
        .cu-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
        .cu-table th { background: #F9FAFB; padding: .55rem .75rem; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #E5E7EB; white-space: nowrap; }
        .cu-table td { padding: .5rem .75rem; border-bottom: 1px solid #F3F4F6; color: #374151; vertical-align: top; }
        .cu-table tr:last-child td { border-bottom: none; }
        .cu-table tr:hover td { background: #F9FAFB; }

        .cu-empty { text-align: center; padding: 2rem; color: #9CA3AF; font-size: .85rem; }
        .cu-summary-row { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
        .cu-summary-box { flex: 1; min-width: 140px; border-radius: 8px; padding: .75rem 1rem; border: 1px solid #E5E7EB; }
        .cu-summary-box .num { font-size: 1.5rem; font-weight: 800; }
        .cu-summary-box .lbl { font-size: .72rem; color: #6B7280; }
    </style>

    <div class="cu-wrap">
        <!-- Header -->
        <div class="cu-header">
            <div class="cu-icon">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="cu-title">Cek Sinkronisasi No Use</p>
                <p class="cu-subtitle">Verifikasi data BPB (No Use) antara sistem lokal dan sistem penerima</p>
            </div>
        </div>

        {{-- Alert --}}
        @if(session('error'))
            <div class="cu-alert cu-alert-error">{{ session('error') }}</div>
        @endif
        @if(session('warning'))
            <div class="cu-alert cu-alert-warning">{{ session('warning') }}</div>
        @endif

        {{-- Form trigger --}}
        <div class="cu-card">
            <p style="font-size:.85rem;color:#374151;margin:0 0 .75rem;">
                Klik tombol di bawah untuk mengambil daftar <strong>No Use</strong> dari lokal, lalu mengirimkannya ke sistem penerima untuk dicek kecocokannya.
            </p>
            <form method="POST" action="{{ route('report.check-use.check') }}" id="form-check">
                @csrf
                <button type="submit" class="cu-btn-check" id="btn-check">
                    <svg id="icon-cek" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <svg id="icon-spin" class="hidden animate-spin" width="16" height="16" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span id="label-btn">Cek Sekarang</span>
                </button>
            </form>
        </div>

        {{-- Results --}}
        @if(isset($missingInReceiver) || isset($missingInSender))
            {{-- Summary --}}
            <div class="cu-summary-row">
                <div class="cu-summary-box" style="background:#EFF6FF;border-color:#BFDBFE;">
                    <div class="num" style="color:#1D4ED8;">{{ isset($localData) ? $localData->count() : 0 }}</div>
                    <div class="lbl">Total No Use Lokal</div>
                </div>
                <div class="cu-summary-box" style="background:#FEF2F2;border-color:#FECACA;">
                    <div class="num" style="color:#B91C1C;">{{ count($missingInReceiver ?? []) }}</div>
                    <div class="lbl">Ada di Lokal, Tidak di Penerima</div>
                </div>
                <div class="cu-summary-box" style="background:#FFFBEB;border-color:#FDE68A;">
                    <div class="num" style="color:#B45309;">{{ count($missingInSender ?? []) }}</div>
                    <div class="lbl">Ada di Penerima, Tidak di Lokal</div>
                </div>
            </div>

            {{-- Ada di lokal, tidak di penerima --}}
            <div class="cu-card">
                <p class="cu-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
                    </svg>
                    Ada di Lokal &mdash; Tidak Ada di Penerima
                    <span class="cu-badge cu-badge-red">{{ count($missingInReceiver ?? []) }}</span>
                </p>
                <div class="cu-table-wrap">
                    <table class="cu-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>No RKH</th>
                                <th>No Use</th>
                                <th>Cost Center</th>
                                <th>Alasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($missingInReceiver ?? [] as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $row['rkhno'] ?? '-' }}</td>
                                    <td><strong>{{ $row['nouse'] ?? '-' }}</strong></td>
                                    <td>{{ $row['costcenter'] ?? '-' }}</td>
                                    <td>{{ $row['reason'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="cu-empty">Semua No Use lokal ditemukan di penerima</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Ada di penerima, tidak di lokal --}}
            <div class="cu-card">
                <p class="cu-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
                    </svg>
                    Ada di Penerima &mdash; Tidak Ada di Lokal
                    <span class="cu-badge cu-badge-yellow">{{ count($missingInSender ?? []) }}</span>
                </p>
                <div class="cu-table-wrap">
                    <table class="cu-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>No RKH</th>
                                <th>No Use</th>
                                <th>Cost Center</th>
                                <th>Alasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($missingInSender ?? [] as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $row['rkhno'] ?? '-' }}</td>
                                    <td><strong>{{ $row['nouse'] ?? '-' }}</strong></td>
                                    <td>{{ $row['costcenter'] ?? '-' }}</td>
                                    <td>{{ $row['reason'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="cu-empty">Tidak ada data di penerima yang tidak ditemukan di lokal</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <script>
        document.getElementById('form-check').addEventListener('submit', function () {
            document.getElementById('btn-check').disabled = true;
            document.getElementById('icon-cek').classList.add('hidden');
            document.getElementById('icon-spin').classList.remove('hidden');
            document.getElementById('label-btn').textContent = 'Memproses...';
        });
    </script>
</x-layout>
