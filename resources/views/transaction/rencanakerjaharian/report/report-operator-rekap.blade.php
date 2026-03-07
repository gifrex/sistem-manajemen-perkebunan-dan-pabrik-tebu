{{-- resources/views/transaction/rencanakerjaharian/report/report-operator-rekap.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <style>
        @media print {
            body * { visibility: hidden; }
            .print-container, .print-container * { visibility: visible; }
            .print-container { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
        }
    </style>

    <div class="print-container print:p-0 print:m-0 max-w-full mx-auto bg-white rounded-lg shadow-lg p-4">

        <h1 class="text-xl font-bold text-center text-gray-800 mb-4 uppercase tracking-wider">
            Rekap Laporan Operator Unit Alat
        </h1>

        {{-- Header Info --}}
        <div class="mb-4 p-3 bg-gray-50 rounded-lg print:bg-white">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <div class="flex items-center text-sm">
                        <span class="font-semibold text-gray-700 w-16">Divisi:</span>
                        <span id="company-info" class="text-gray-900">Loading...</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="font-semibold text-gray-700 w-16">Tanggal:</span>
                        <span id="report-date" class="text-gray-900">Loading...</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center text-sm">
                        <span class="font-semibold text-gray-700 w-28">Total Operator:</span>
                        <span id="total-operators" class="text-gray-900 font-semibold">Loading...</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="font-semibold text-gray-700 w-28">Total Aktivitas:</span>
                        <span id="total-activities" class="text-gray-900 font-semibold">Loading...</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Detail Kegiatan Semua Operator</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300" id="activities-table">
                    <thead class="bg-gray-100">
                        <tr class="text-sm">
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 3%">No</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 5%">Source</th>
                            <th class="border border-gray-300 px-2 py-2 text-left" style="width: 13%">Operator</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 9%">Unit Alat</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 6%">Jam Mulai</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 6%">Jam Selesai</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 6%">Durasi</th>
                            <th class="border border-gray-300 px-2 py-2 text-left" style="width: 15%">Kegiatan</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 10%">Plot / Tujuan</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 7%">Luas RKH<br><small>(ha)</small></th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 7%">Hasil</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 7%">Solar<br><small>(Liter)</small></th>
                        </tr>
                    </thead>
                    <tbody id="activities-tbody">
                        <tr>
                            <td colspan="12" class="border border-gray-300 px-2 py-6 text-center text-gray-500">
                                Memuat data kegiatan...
                            </td>
                        </tr>
                    </tbody>
                    <tfoot id="activities-tfoot" class="bg-gray-50 font-semibold"></tfoot>
                </table>
            </div>
        </div>

        {{-- Timestamp --}}
        <div class="mt-6 text-xs text-gray-500 text-center print:mt-8">
            <p>Dicetak pada: <span id="print-timestamp">Loading...</span></p>
        </div>

        {{-- Buttons --}}
        <div class="mt-4 flex justify-center space-x-4 no-print">
            <button onclick="window.print()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2 2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                    </path>
                </svg>
                Print
            </button>
            <button onclick="window.history.back()"
                class="bg-white border border-gray-300 hover:border-gray-400 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors hover:bg-gray-50 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </button>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const reportDate = urlParams.get('date') || '{{ $date }}';

        async function loadOperatorRekapReportData() {
            try {
                const response = await fetch(`{{ route('transaction.rencanakerjaharian.operator-rekap-report-data') }}?date=${reportDate}`);
                const data = await response.json();

                if (data.success) {
                    updateHeaderInfo(data);
                    displayAllActivities(data.all_activities || [], data.grand_totals);
                } else {
                    showError('Gagal memuat data: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                showError('Terjadi kesalahan: ' + error.message);
            }
        }

        function updateHeaderInfo(data) {
            document.getElementById('report-date').textContent = data.date_formatted || reportDate;
            document.getElementById('company-info').textContent = data.company_info || 'N/A';

            const gt = data.grand_totals;
            let activityText = `${gt?.total_activities || 0}`;
            if (gt?.count_lkh > 0 || gt?.count_sjs > 0) {
                const parts = [];
                if (gt.count_lkh > 0) parts.push(`${gt.count_lkh} LKH`);
                if (gt.count_sjs > 0) parts.push(`${gt.count_sjs} SJS`);
                activityText += ` (${parts.join(', ')})`;
            }

            document.getElementById('total-operators').textContent = gt?.total_operators || 0;
            document.getElementById('total-activities').textContent = activityText;
            document.getElementById('print-timestamp').textContent = data.generated_at || new Date().toLocaleString('id-ID');
        }

        function displayAllActivities(activities, grandTotals) {
            const tbody = document.getElementById('activities-tbody');

            if (!activities || activities.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="12" class="border border-gray-300 px-2 py-6 text-center text-gray-400 italic">
                            Tidak ada data aktivitas operator pada tanggal yang dipilih
                        </td>
                    </tr>`;
                document.getElementById('activities-tfoot').innerHTML = '';
                return;
            }

            tbody.innerHTML = '';
            let counter = 1;

            activities.forEach((act) => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';

                const isLkh = act.source_type === 'LKH';
                const isSjs = act.source_type === 'SJS';

                // Source badge
                const sourceBadge = isLkh
                    ? '<span class="inline-block px-2 py-0.5 text-xs font-semibold rounded bg-blue-100 text-blue-700">LKH</span>'
                    : '<span class="inline-block px-2 py-0.5 text-xs font-semibold rounded bg-amber-100 text-amber-700">SJS</span>';

                // Null display helper
                const nd = (val) => val
                    ? val
                    : '<span class="text-gray-400 italic">-</span>';

                // Kegiatan
                let kegiatanHtml = act.kegiatan || '-';
                if (act.kegiatan_code) {
                    kegiatanHtml = `<div class="font-medium">${act.kegiatan}</div><div class="text-xs text-gray-500">${act.kegiatan_code}</div>`;
                }

                // Luas Rencana (LKH only)
                const luasRencana = isLkh ? nd(act.luas_rencana) : '<span class="text-gray-300">-</span>';

                // Hasil + satuan
                let hasilDisplay = '<span class="text-gray-400 italic">-</span>';
                if (act.hasil_value) {
                    const satuan = act.hasil_satuan === 'rit' ? ' rit' : ' ha';
                    hasilDisplay = act.hasil_value + satuan;
                }

                // Solar
                let solarDisplay = '<span class="text-gray-400 italic">-</span>';
                if (act.solar_liter) {
                    solarDisplay = act.solar_liter + ' L';
                    if (act.solar_real) {
                        solarDisplay = `<span title="Real: ${act.solar_real} L | Requested: ${act.solar_requested || '-'} L">${act.solar_real} L</span>`;
                    } else if (act.solar_requested) {
                        solarDisplay = `<span class="text-gray-500" title="Belum ada realisasi, ini solar requested">${act.solar_requested} L <sup>*</sup></span>`;
                    }
                }

                row.innerHTML = `
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm">${counter++}</td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm">${sourceBadge}</td>
                    <td class="border border-gray-300 px-2 py-2 text-sm font-medium">${act.operator_name}</td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm">
                        <div class="font-medium">${act.nokendaraan}</div>
                        <div class="text-xs text-gray-500">${act.vehicle_type || ''}</div>
                    </td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm font-mono">${nd(act.jam_mulai)}</td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm font-mono">${nd(act.jam_selesai)}</td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm font-mono">${nd(act.durasi_kerja)}</td>
                    <td class="border border-gray-300 px-2 py-2 text-sm">${kegiatanHtml}</td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm">${act.plots_display || '-'}</td>
                    <td class="border border-gray-300 px-2 py-2 text-right text-sm">${luasRencana}</td>
                    <td class="border border-gray-300 px-2 py-2 text-right text-sm">${hasilDisplay}</td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm">${solarDisplay}</td>
                `;

                tbody.appendChild(row);
            });

            renderGrandTotalRow(grandTotals);
        }

        function renderGrandTotalRow(gt) {
            const tfoot = document.getElementById('activities-tfoot');
            tfoot.innerHTML = '';

            const row = document.createElement('tr');

            // Duration
            let durationText = '<span class="text-gray-400 italic">-</span>';
            if (gt.total_duration_minutes > 0) {
                const h = gt.total_duration_hours;
                const m = gt.total_duration_minutes_remainder;
                if (h === 0) durationText = `${m} menit`;
                else if (m === 0) durationText = `${h} jam`;
                else durationText = `${h}j ${m}m`;
            }

            // Hasil: show ha + rit separately
            let hasilParts = [];
            if (gt.total_hasil_ha_formatted) hasilParts.push(`${gt.total_hasil_ha_formatted} ha`);
            if (gt.total_hasil_rit_formatted) hasilParts.push(`${gt.total_hasil_rit_formatted} rit`);
            const hasilTotal = hasilParts.length > 0
                ? hasilParts.join('<br>')
                : '<span class="text-gray-400 italic">-</span>';

            const solarTotal = gt.total_solar_formatted
                ? gt.total_solar_formatted + ' L'
                : '<span class="text-gray-400 italic">-</span>';

            const luasTotal = gt.total_luas_rencana_formatted
                ? gt.total_luas_rencana_formatted
                : '<span class="text-gray-400 italic">-</span>';

            row.innerHTML = `
                <td colspan="6" class="border border-gray-300 px-2 py-2 text-center text-sm font-bold">GRAND TOTAL</td>
                <td class="border border-gray-300 px-2 py-2 text-center text-sm font-bold">${durationText}</td>
                <td class="border border-gray-300 px-2 py-2 text-center text-sm font-bold">-</td>
                <td class="border border-gray-300 px-2 py-2 text-center text-sm font-bold">-</td>
                <td class="border border-gray-300 px-2 py-2 text-right text-sm font-bold">${luasTotal}</td>
                <td class="border border-gray-300 px-2 py-2 text-right text-sm font-bold">${hasilTotal}</td>
                <td class="border border-gray-300 px-2 py-2 text-center text-sm font-bold">${solarTotal}</td>
            `;

            tfoot.appendChild(row);

            // Note for solar asterisk
            if (document.querySelector('[title]')) {
                const noteRow = document.createElement('tr');
                noteRow.innerHTML = `
                    <td colspan="12" class="border-0 px-2 py-1 text-xs text-gray-400 text-right">
                        <sup>*</sup> Solar requested (belum ada realisasi dari gudang)
                    </td>
                `;
                tfoot.appendChild(noteRow);
            }
        }

        function showError(message) {
            const tbody = document.getElementById('activities-tbody');
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="12" class="border border-gray-300 px-2 py-6 text-center text-red-500">
                            <svg class="w-12 h-12 mx-auto mb-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="font-semibold">${message}</div>
                        </td>
                    </tr>`;
            }
            document.getElementById('activities-tfoot').innerHTML = '';
            document.getElementById('company-info').textContent = 'Error';
            document.getElementById('report-date').textContent = 'Error';
            document.getElementById('total-operators').textContent = '0';
            document.getElementById('total-activities').textContent = '0';
            document.getElementById('print-timestamp').textContent = new Date().toLocaleString('id-ID');
        }

        document.addEventListener('DOMContentLoaded', function () {
            loadOperatorRekapReportData();
        });
    </script>
</x-layout>