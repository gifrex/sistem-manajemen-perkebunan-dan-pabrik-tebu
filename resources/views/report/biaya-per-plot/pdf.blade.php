<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Biaya Per Plot</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1f2937;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #1f2937;
        }
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #111827;
        }
        .header p {
            font-size: 10px;
            color: #6b7280;
            margin: 2px 0;
        }
        .summary-grand {
            background: #1f2937;
            color: white;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .summary-grand h2 {
            font-size: 14px;
            margin-bottom: 8px;
        }
        .summary-grand-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }
        .summary-grand-item {
            text-align: center;
        }
        .summary-grand-item .label {
            font-size: 9px;
            opacity: 0.8;
            margin-bottom: 3px;
        }
        .summary-grand-item .value {
            font-size: 13px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table thead {
            background: #374151;
            color: white;
        }
        table th {
            padding: 8px 6px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            border: 1px solid #4b5563;
        }
        table td {
            padding: 6px 6px;
            border: 1px solid #d1d5db;
            font-size: 10px;
        }
        table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        table tbody tr:hover {
            background: #f3f4f6;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        tfoot {
            background: #e5e7eb;
            font-weight: bold;
        }
        tfoot td {
            padding: 8px 6px;
            border: 2px solid #9ca3af;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            padding: 10px 0;
            border-top: 1px solid #e5e7eb;
        }
        @page {
            margin: 15mm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORT BIAYA PER PLOT - SUMMARY</h1>
        <p>Cycle: {{ $generationLabel }} | Blok: {{ implode(', ', $selectedBloks) }}</p>
        <p>Tanggal: {{ $tanggal }}</p>
    </div>

    <div class="summary-grand">
        <h2>Grand Total</h2>
        <div class="summary-grand-grid">
            <div class="summary-grand-item">
                <div class="label">Biaya TK</div>
                <div class="value">Rp {{ number_format($grandTotal['biaya_tk'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-grand-item">
                <div class="label">Biaya Material</div>
                <div class="value">Rp {{ number_format($grandTotal['biaya_material'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-grand-item">
                <div class="label">Total Biaya</div>
                <div class="value">Rp {{ number_format($grandTotal['total_biaya'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-grand-item">
                <div class="label">Total Panen</div>
                <div class="value">{{ number_format($grandTotal['total_ton'], 2, ',', '.') }} ton</div>
            </div>
            <div class="summary-grand-item">
                <div class="label">Avg YPH</div>
                <div class="value">{{ number_format($grandTotal['avg_yph'], 2, ',', '.') }} ton/ha</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Blok</th>
                <th class="text-center">Total Plot</th>
                <th class="text-right">Biaya TK</th>
                <th class="text-right">Biaya Material</th>
                <th class="text-right">Total Biaya</th>
                <th class="text-right">Total Ton</th>
                <th class="text-right">Avg YPH</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summaryPerBlok as $summary)
            <tr>
                <td class="font-bold">{{ $summary['blok'] }}</td>
                <td class="text-center">{{ $summary['total_plot'] }}</td>
                <td class="text-right">Rp {{ number_format($summary['biaya_tk'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($summary['biaya_material'], 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp {{ number_format($summary['total_biaya'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($summary['total_ton'], 2, ',', '.') }}</td>
                <td class="text-right font-bold">{{ number_format($summary['avg_yph'], 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right">TOTAL / AVERAGE</td>
                <td class="text-right">Rp {{ number_format($grandTotal['biaya_tk'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($grandTotal['biaya_material'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($grandTotal['total_biaya'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($grandTotal['total_ton'], 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($grandTotal['avg_yph'], 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated on {{ $tanggal }} | Report Biaya Per Plot
    </div>
</body>
</html>