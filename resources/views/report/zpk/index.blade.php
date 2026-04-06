<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="zpk-report">
        <!-- Header -->
        <div class="zpk-header">
            <div class="zpk-header-left">
                <div class="zpk-header-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <h1 class="zpk-title">Report ZPK</h1>
                    <p class="zpk-subtitle">Zat Pemacu Kemasakan — Monitoring aplikasi & jadwal panen</p>
                </div>
            </div>
            <div class="zpk-header-right">
                @can('report.zpk.export')
                    <button id="btn-export" data-base-url="{{ route('report.report-zpk.exportExcel') }}"
                        class="zpk-btn-export" onclick="startExport()">
                        <svg id="icon-export" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <svg id="icon-spin" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span id="label-export">Export Excel</span>
                    </button>
                @endcan
            </div>
        </div>

        <!-- Filters -->
        <form method="POST" action="{{ route('report.report-zpk.index') }}" id="filter-form">
            @csrf
            <div class="zpk-filters">
                <div class="zpk-filter-row">
                    <!-- Date Range -->
                    <div class="zpk-filter-group">
                        <label class="zpk-filter-label">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Periode
                        </label>
                        <div class="zpk-date-inputs">
                            <input type="date" id="start_date" name="start_date"
                                value="{{ old('start_date', $startDate ?? '') }}" class="zpk-input zpk-input-date" />
                            <span class="zpk-date-sep">—</span>
                            <input type="date" id="end_date" name="end_date"
                                value="{{ old('end_date', $endDate ?? '') }}" class="zpk-input zpk-input-date" />
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="zpk-filter-group zpk-filter-search">
                        <label class="zpk-filter-label">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Pencarian
                        </label>
                        <input type="text" id="search" autocomplete="off" name="search"
                            value="{{ old('search', $search) }}" class="zpk-input"
                            placeholder="Blok, Plot, Varietas, Kategori..." />
                    </div>

                    <!-- Per Page -->
                    <div class="zpk-filter-group zpk-filter-perpage">
                        <label class="zpk-filter-label">Tampilkan</label>
                        <select name="perPage" id="perPage" class="zpk-input zpk-input-select">
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    <!-- Apply Button -->
                    <div class="zpk-filter-group zpk-filter-apply">
                        <label class="zpk-filter-label">&nbsp;</label>
                        <button type="submit" class="zpk-btn-apply">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Terapkan
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Summary Cards -->
        @if ($zpk->count() > 0)
            <div class="zpk-summary">
                <div class="zpk-card">
                    <div class="zpk-card-value">{{ $zpk->total() }}</div>
                    <div class="zpk-card-label">Total Plot</div>
                </div>
                <div class="zpk-card">
                    <div class="zpk-card-value">{{ number_format($zpk->sum('batcharea'), 2) }}</div>
                    <div class="zpk-card-label">Total Luas (Ha)</div>
                </div>
                <div class="zpk-card">
                    <div class="zpk-card-value">{{ $zpk->pluck('kodevarietas')->unique()->count() }}</div>
                    <div class="zpk-card-label">Varietas</div>
                </div>
            </div>
        @endif

        <!-- Table -->
        <div class="zpk-table-wrap">
            <div class="zpk-table-scroll">
                <table class="zpk-table" id="tables">
                    <thead>
                        <tr>
                            <th class="zpk-th zpk-th-fixed">No</th>
                            <th class="zpk-th zpk-th-sortable" data-sort="blok">
                                Blok <span class="zpk-sort-icon">↕</span>
                            </th>
                            <th class="zpk-th zpk-th-sortable" data-sort="plot">
                                Plot <span class="zpk-sort-icon">↕</span>
                            </th>
                            <th class="zpk-th zpk-th-sortable" data-sort="tanggal_zpk">
                                Tanggal ZPK <span class="zpk-sort-icon">↕</span>
                            </th>
                            <th class="zpk-th zpk-th-sortable" data-sort="batcharea">
                                Luas (Ha) <span class="zpk-sort-icon">↕</span>
                            </th>
                            <th class="zpk-th">Bulan Tanam</th>
                            <th class="zpk-th zpk-th-sortable" data-sort="umur">
                                Umur <span class="zpk-sort-icon">↕</span>
                            </th>
                            <th class="zpk-th zpk-th-sortable" data-sort="lifecyclestatus">
                                Kategori <span class="zpk-sort-icon">↕</span>
                            </th>
                            <th class="zpk-th zpk-th-sortable" data-sort="kodevarietas">
                                Varietas <span class="zpk-sort-icon">↕</span>
                            </th>
                            <th class="zpk-th">PKP</th>
                            <th class="zpk-th">Perkiraan Panen</th>
                            <th class="zpk-th zpk-th-sortable" data-sort="status">
                                Status <span class="zpk-sort-icon">↕</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($zpk as $item)
                            <tr class="zpk-row">
                                <td class="zpk-td zpk-td-fixed zpk-td-num">{{ $item->no }}</td>
                                <td class="zpk-td">{{ $item->blok ?? '—' }}</td>
                                <td class="zpk-td zpk-td-mono">{{ $item->plot ?? '—' }}</td>
                                <td class="zpk-td">{{ $item->tanggal_zpk ?? '—' }}</td>
                                <td class="zpk-td">
                                    {{ $item->batcharea ? number_format($item->batcharea, 2) : '—' }}
                                </td>
                                <td class="zpk-td">{{ $item->bulantanam ?? '—' }}</td>
                                <td class="zpk-td">
                                    @if ($item->umur !== null)
                                        <span class="zpk-badge zpk-badge-umur
                                            @if ($item->umur >= 10) zpk-badge-umur-mature @elseif($item->umur >= 6) zpk-badge-umur-mid @else zpk-badge-umur-young @endif">
                                            {{ round($item->umur) }} bln
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="zpk-td">
                                    @if ($item->lifecyclestatus)
                                        <span class="zpk-badge zpk-badge-kategori">{{ $item->lifecyclestatus }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="zpk-td zpk-td-mono">{{ $item->kodevarietas ?? '—' }}</td>
                                <td class="zpk-td">{{ $item->pkp ?? '—' }}</td>
                                <td class="zpk-td">
                                    @if ($item->perkiraan_panen_awal)
                                        <div class="zpk-panen-range">
                                            <span class="zpk-panen-date">{{ $item->perkiraan_panen_awal }}</span>
                                            <span class="zpk-panen-sep">→</span>
                                            <span class="zpk-panen-date">{{ $item->perkiraan_panen_akhir }}</span>
                                        </div>
                                    @else
                                        <span class="zpk-td-empty">—</span>
                                    @endif
                                </td>
                                <td class="zpk-td">
                                    @if ($item->status_panen)
                                        <span class="zpk-status zpk-status-{{ $item->status_panen_type }}">
                                            {{ $item->status_panen }}
                                            @if ($item->status_panen_hari !== null)
                                                <span class="zpk-status-hari">(H+{{ $item->status_panen_hari }})</span>
                                            @endif
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="zpk-empty-state">
                                    <div class="zpk-empty-inner">
                                        <svg class="zpk-empty-icon" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <p class="zpk-empty-title">Data tidak ditemukan</p>
                                        <p class="zpk-empty-desc">Sesuaikan periode atau kata kunci pencarian</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Info Note -->
        <div class="zpk-note">
            <div class="zpk-note-icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="zpk-note-content">
                <p class="zpk-note-title">Keterangan Perhitungan Perkiraan Panen</p>
                <div class="zpk-note-body">
                    <p>Perkiraan panen dihitung berdasarkan <strong>tanggal aplikasi ZPK</strong> (Zat Pemacu Kemasakan):</p>
                    <div class="zpk-note-calc">
                        <div class="zpk-note-calc-item">
                            <span class="zpk-note-calc-label">Batas Awal</span>
                            <span class="zpk-note-calc-formula">Tanggal ZPK + <strong>26 hari</strong> (H+26)</span>
                            <span class="zpk-note-calc-desc">Waktu minimum reaksi kemasakan tebu</span>
                        </div>
                        <div class="zpk-note-calc-divider">→</div>
                        <div class="zpk-note-calc-item">
                            <span class="zpk-note-calc-label">Batas Akhir</span>
                            <span class="zpk-note-calc-formula">Tanggal ZPK + <strong>35 hari</strong> (H+35)</span>
                            <span class="zpk-note-calc-desc">Batas optimal sebelum kadar gula menurun</span>
                        </div>
                    </div>
                    <p class="zpk-note-footer">Rentang <strong>9 hari</strong> (H+26 s/d H+35) merupakan jendela panen optimal di mana kadar sukrosa tebu berada pada titik tertinggi setelah aplikasi ZPK. Panen di luar rentang ini dapat mengurangi rendemen gula.</p>
                </div>
                <div class="zpk-note-legend">
                    <p class="zpk-note-legend-title">Keterangan Status:</p>
                    <div class="zpk-note-legend-items">
                        <span class="zpk-status zpk-status-waiting">Belum Boleh Panen</span>
                        <span class="zpk-note-legend-desc">— Belum mencapai H+26, tebu belum cukup matang</span>
                    </div>
                    <div class="zpk-note-legend-items">
                        <span class="zpk-status zpk-status-ready">Menunggu Panen</span>
                        <span class="zpk-note-legend-desc">— Sudah dalam jendela panen optimal (H+26 s/d H+35)</span>
                    </div>
                    <div class="zpk-note-legend-items">
                        <span class="zpk-status zpk-status-overdue">Lewat Batas</span>
                        <span class="zpk-note-legend-desc">— Melewati H+35, rendemen gula mulai menurun</span>
                    </div>
                    <div class="zpk-note-legend-items">
                        <span class="zpk-status zpk-status-done">Sudah Panen</span>
                        <span class="zpk-note-legend-desc">— Tanggal panen sudah tercatat di sistem</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="zpk-pagination" id="pagination-links">
            @if ($zpk->hasPages())
                {{ $zpk->appends(['perPage' => $zpk->perPage(), 'start_date' => $startDate ?? '', 'end_date' => $endDate ?? ''])->links() }}
            @else
                <div class="zpk-pagination-info">
                    Menampilkan <strong>{{ $zpk->count() }}</strong> dari <strong>{{ $zpk->total() }}</strong> data
                </div>
            @endif
        </div>
    </div>

    <style>
        /* ═══════════════════════════════════════════
           ZPK Report — Clean Professional Theme
           ═══════════════════════════════════════════ */

        .zpk-report {
            --c-bg: #ffffff;
            --c-surface: #f8fafc;
            --c-border: #e2e8f0;
            --c-border-light: #f1f5f9;
            --c-text: #1e293b;
            --c-text-secondary: #64748b;
            --c-text-muted: #94a3b8;
            --c-primary: #3b82f6;
            --radius: 8px;
            --radius-sm: 6px;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.04);
            --shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --font-mono: 'JetBrains Mono', 'Fira Code', 'SF Mono', monospace;

            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            color: var(--c-text);
            background: var(--c-bg);
            border: 1px solid var(--c-border);
            border-radius: 12px;
            overflow: hidden;
        }

        /* ── Header ── */
        .zpk-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid var(--c-border);
            flex-wrap: wrap;
            gap: 16px;
        }

        .zpk-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .zpk-header-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .zpk-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--c-text);
            line-height: 1.2;
            margin: 0;
        }

        .zpk-subtitle {
            font-size: 0.8rem;
            color: var(--c-text-secondary);
            margin: 2px 0 0 0;
        }

        .zpk-btn-export {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: #16a34a;
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 0.813rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: var(--shadow-sm);
        }

        .zpk-btn-export:hover { background: #15803d; box-shadow: var(--shadow); }
        .zpk-btn-export:disabled { opacity: 0.6; cursor: not-allowed; }

        /* ── Filters ── */
        .zpk-filters {
            padding: 16px 24px;
            background: var(--c-surface);
            border-bottom: 1px solid var(--c-border);
        }

        .zpk-filter-row {
            display: flex;
            align-items: flex-end;
            gap: 16px;
            flex-wrap: wrap;
        }

        .zpk-filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .zpk-filter-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--c-text-secondary);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .zpk-input {
            height: 36px;
            padding: 0 12px;
            border: 1px solid var(--c-border);
            border-radius: var(--radius-sm);
            font-size: 0.813rem;
            color: var(--c-text);
            background: white;
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
        }

        .zpk-input:focus {
            border-color: var(--c-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .zpk-input-date { width: 150px; }

        .zpk-date-inputs {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .zpk-date-sep {
            color: var(--c-text-muted);
            font-size: 0.75rem;
        }

        .zpk-filter-search {
            flex: 1;
            min-width: 200px;
        }

        .zpk-filter-search .zpk-input { width: 100%; }

        .zpk-input-select {
            width: auto;
            min-width: 72px;
            padding: 0 28px 0 12px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            cursor: pointer;
        }

        .zpk-btn-apply {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 16px;
            background: var(--c-primary);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 0.813rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
        }

        .zpk-btn-apply:hover { background: #2563eb; }

        /* ── Summary Cards ── */
        .zpk-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            padding: 16px 24px;
            border-bottom: 1px solid var(--c-border);
        }

        .zpk-card {
            padding: 14px 16px;
            border-radius: var(--radius);
            border: 1px solid var(--c-border);
            background: white;
        }

        .zpk-card-value {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
            font-variant-numeric: tabular-nums;
            color: var(--c-text);
        }

        .zpk-card-label {
            font-size: 0.7rem;
            font-weight: 500;
            color: var(--c-text-secondary);
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        /* ── Table ── */
        .zpk-table-wrap {
            padding: 0 24px 16px;
            margin-top: 16px;
        }

        .zpk-table-scroll {
            overflow-x: auto;
            border: 1px solid var(--c-border);
            border-radius: var(--radius);
        }

        .zpk-table-scroll::-webkit-scrollbar { height: 6px; }
        .zpk-table-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
        .zpk-table-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

        .zpk-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.813rem;
        }

        .zpk-th {
            padding: 10px 14px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--c-text-secondary);
            background: var(--c-surface);
            border-bottom: 2px solid var(--c-border);
            white-space: nowrap;
            text-align: center;
            user-select: none;
        }

        .zpk-th-sortable { cursor: pointer; transition: background 0.1s; }
        .zpk-th-sortable:hover { background: #e2e8f0; }

        .zpk-th-sortable.sort-asc .zpk-sort-icon,
        .zpk-th-sortable.sort-desc .zpk-sort-icon {
            color: var(--c-primary);
            font-size: 0;
        }

        .zpk-th-sortable.sort-asc .zpk-sort-icon::after { content: '↑'; font-size: 0.65rem; }
        .zpk-th-sortable.sort-desc .zpk-sort-icon::after { content: '↓'; font-size: 0.65rem; }

        .zpk-sort-icon {
            font-size: 0.65rem;
            color: var(--c-text-muted);
            margin-left: 2px;
        }

        .zpk-td {
            padding: 10px 14px;
            text-align: center;
            white-space: nowrap;
            color: var(--c-text);
            border-bottom: 1px solid var(--c-border-light);
        }

        .zpk-row:hover .zpk-td { background: #f8fafc; }

        .zpk-td-fixed, .zpk-th-fixed {
            position: sticky;
            left: 0;
            z-index: 10;
            background: white;
        }

        .zpk-th-fixed { background: var(--c-surface); z-index: 11; }
        .zpk-row:hover .zpk-td-fixed { background: #f8fafc; }

        .zpk-td-num {
            color: var(--c-text-muted);
            font-size: 0.75rem;
            font-variant-numeric: tabular-nums;
        }

        .zpk-td-mono {
            font-family: var(--font-mono);
            font-size: 0.78rem;
            letter-spacing: -0.01em;
        }

        .zpk-td-empty { color: var(--c-text-muted); }

        /* ── Badges ── */
        .zpk-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.5;
        }

        .zpk-badge-kategori { background: #f1f5f9; color: #475569; font-size: 0.7rem; }

        .zpk-badge-umur { font-variant-numeric: tabular-nums; }
        .zpk-badge-umur-young { background: #dcfce7; color: #166534; }
        .zpk-badge-umur-mid { background: #fef9c3; color: #854d0e; }
        .zpk-badge-umur-mature { background: #fee2e2; color: #991b1b; }

        /* ── Status Panen ── */
        .zpk-status {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .zpk-status-hari {
            font-weight: 500;
            opacity: 0.8;
        }

        .zpk-status-waiting {
            background: #f1f5f9;
            color: #64748b;
        }

        .zpk-status-ready {
            background: #fef9c3;
            color: #854d0e;
        }

        .zpk-status-overdue {
            background: #fee2e2;
            color: #991b1b;
        }

        .zpk-status-done {
            background: #dcfce7;
            color: #166534;
        }

        /* ── Panen range ── */
        .zpk-panen-range {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.75rem;
        }

        .zpk-panen-date { font-weight: 500; }
        .zpk-panen-sep { color: var(--c-text-muted); font-size: 0.65rem; }

        /* ── Empty State ── */
        .zpk-empty-state { padding: 0 !important; border: none !important; }

        .zpk-empty-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 64px 24px;
            position: sticky;
            left: 0;
            width: calc(100vw - 330px);
            max-width: calc(100vw - 330px);
        }

        .zpk-empty-icon { width: 40px; height: 40px; color: var(--c-text-muted); margin-bottom: 12px; }
        .zpk-empty-title { font-weight: 600; color: var(--c-text-secondary); font-size: 0.875rem; margin: 0; }
        .zpk-empty-desc { color: var(--c-text-muted); font-size: 0.75rem; margin: 4px 0 0 0; }

        /* ── Note ── */
        .zpk-note {
            margin: 0 24px 16px;
            display: flex;
            gap: 14px;
            padding: 16px 20px;
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: var(--radius);
        }

        .zpk-note-icon {
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--c-primary);
            box-shadow: var(--shadow-sm);
        }

        .zpk-note-content { flex: 1; min-width: 0; }

        .zpk-note-title {
            font-size: 0.813rem;
            font-weight: 700;
            color: var(--c-text);
            margin: 0 0 8px 0;
        }

        .zpk-note-body {
            font-size: 0.78rem;
            color: var(--c-text-secondary);
            line-height: 1.6;
        }

        .zpk-note-body p { margin: 0 0 8px 0; }

        .zpk-note-calc {
            display: flex;
            align-items: stretch;
            gap: 12px;
            margin: 12px 0;
            flex-wrap: wrap;
        }

        .zpk-note-calc-item {
            flex: 1;
            min-width: 180px;
            background: white;
            border: 1px solid var(--c-border);
            border-radius: 6px;
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .zpk-note-calc-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--c-primary);
        }

        .zpk-note-calc-formula { font-size: 0.85rem; color: var(--c-text); font-weight: 500; }
        .zpk-note-calc-desc { font-size: 0.7rem; color: var(--c-text-muted); }
        .zpk-note-calc-divider { display: flex; align-items: center; color: var(--c-text-muted); font-size: 1.2rem; }

        .zpk-note-footer {
            font-size: 0.75rem;
            color: var(--c-text-secondary);
            margin: 4px 0 0 0 !important;
            padding-top: 8px;
            border-top: 1px solid var(--c-border);
            line-height: 1.6;
        }

        .zpk-note-legend {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--c-border);
        }

        .zpk-note-legend-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--c-text);
            margin: 0 0 8px 0;
        }

        .zpk-note-legend-items {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .zpk-note-legend-desc {
            font-size: 0.72rem;
            color: var(--c-text-secondary);
        }

        /* ── Pagination ── */
        .zpk-pagination { padding: 12px 24px 20px; }

        .zpk-pagination-info {
            font-size: 0.8rem;
            color: var(--c-text-secondary);
            background: var(--c-surface);
            padding: 10px 16px;
            border-radius: var(--radius-sm);
        }

        .zpk-pagination-info strong { color: var(--c-text); }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .zpk-header { padding: 16px; }
            .zpk-filters { padding: 12px 16px; }
            .zpk-filter-row { flex-direction: column; align-items: stretch; }
            .zpk-filter-search { min-width: unset; }
            .zpk-summary { grid-template-columns: 1fr; padding: 12px 16px; }
            .zpk-table-wrap { padding: 0 16px 12px; }
            .zpk-note { margin: 0 16px 12px; flex-direction: column; }
            .zpk-note-calc { flex-direction: column; }
            .zpk-note-calc-divider { justify-content: center; transform: rotate(90deg); }
            .zpk-pagination { padding: 8px 16px 16px; }
        }
    </style>

    <script>
        // ── Client-side sorting ──
        document.addEventListener('DOMContentLoaded', function () {
            const table = document.getElementById('tables');
            if (!table) return;

            const headers = table.querySelectorAll('.zpk-th-sortable');
            let currentSort = { col: null, dir: null };

            headers.forEach(function (th) {
                th.addEventListener('click', function () {
                    const sortKey = th.getAttribute('data-sort');
                    const allTh = Array.from(table.querySelectorAll('thead th'));
                    const realIndex = allTh.indexOf(th);

                    if (currentSort.col === sortKey && currentSort.dir === 'asc') {
                        currentSort = { col: sortKey, dir: 'desc' };
                    } else {
                        currentSort = { col: sortKey, dir: 'asc' };
                    }

                    headers.forEach(function (h) { h.classList.remove('sort-asc', 'sort-desc'); });
                    th.classList.add(currentSort.dir === 'asc' ? 'sort-asc' : 'sort-desc');

                    const tbody = table.querySelector('tbody');
                    const rows = Array.from(tbody.querySelectorAll('tr.zpk-row'));
                    if (rows.length === 0) return;

                    rows.sort(function (a, b) {
                        let aVal = a.cells[realIndex]?.textContent?.trim() || '';
                        let bVal = b.cells[realIndex]?.textContent?.trim() || '';

                        // Try to extract numeric value
                        let aClean = aVal.replace(/\s*(Ha|bln|Bulan|H\+\d+)\s*/gi, '').replace(/[^\d.\-]/g, '');
                        let bClean = bVal.replace(/\s*(Ha|bln|Bulan|H\+\d+)\s*/gi, '').replace(/[^\d.\-]/g, '');

                        const aNum = parseFloat(aClean);
                        const bNum = parseFloat(bClean);

                        let cmp;
                        if (aClean && bClean && !isNaN(aNum) && !isNaN(bNum)) {
                            cmp = aNum - bNum;
                        } else {
                            cmp = aVal.localeCompare(bVal, 'id');
                        }

                        return currentSort.dir === 'asc' ? cmp : -cmp;
                    });

                    rows.forEach(function (row, i) {
                        row.querySelector('.zpk-td-num').textContent = i + 1;
                        tbody.appendChild(row);
                    });
                });
            });
        });

        // ── Export ──
        function startExport() {
            const btn = document.getElementById('btn-export');
            const iconExport = document.getElementById('icon-export');
            const iconSpin = document.getElementById('icon-spin');
            const label = document.getElementById('label-export');

            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const baseUrl = btn.getAttribute('data-base-url');

            const params = new URLSearchParams();
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
            const url = baseUrl + '?' + params.toString();

            btn.disabled = true;
            iconExport.classList.add('hidden');
            iconSpin.classList.remove('hidden');
            label.textContent = 'Mengekspor...';

            fetch(url)
                .then(function (response) {
                    const contentType = response.headers.get('Content-Type') || '';
                    if (contentType.includes('application/json')) {
                        return response.json().then(function (json) {
                            showToast('error', json.error || 'Tidak ada data untuk diekspor.');
                        });
                    }
                    return response.blob().then(function (blob) {
                        const disposition = response.headers.get('Content-Disposition') || '';
                        const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                        const filename = match ? match[1].replace(/['"]/g, '') : 'ZPKReport.xlsx';
                        const a = document.createElement('a');
                        a.href = URL.createObjectURL(blob);
                        a.download = filename;
                        a.click();
                        URL.revokeObjectURL(a.href);
                    });
                })
                .catch(function () {
                    showToast('error', 'Gagal mengekspor data. Silakan coba lagi.');
                })
                .finally(function () {
                    btn.disabled = false;
                    iconExport.classList.remove('hidden');
                    iconSpin.classList.add('hidden');
                    label.textContent = 'Export Excel';
                });
        }

        function showToast(type, msg) {
            const isSuccess = type === 'success';
            const t = document.createElement('div');
            t.style.cssText = 'position:fixed;top:24px;right:24px;z-index:9999;display:flex;align-items:center;gap:10px;padding:12px 20px;border-radius:8px;font-size:0.813rem;font-weight:500;color:white;max-width:360px;box-shadow:0 10px 25px rgba(0,0,0,0.15);opacity:0;transform:translateY(-8px);transition:all 0.3s ease;';
            t.style.background = isSuccess ? '#16a34a' : '#dc2626';
            t.innerHTML = '<span>' + msg + '</span>';
            document.body.appendChild(t);
            requestAnimationFrame(function () {
                t.style.opacity = '1';
                t.style.transform = 'translateY(0)';
            });
            setTimeout(function () {
                t.style.opacity = '0';
                t.style.transform = 'translateY(-8px)';
                setTimeout(function () { t.remove(); }, 300);
            }, 4000);
        }
    </script>

</x-layout>