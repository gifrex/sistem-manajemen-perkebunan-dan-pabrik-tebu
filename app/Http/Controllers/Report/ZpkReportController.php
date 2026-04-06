<?php

namespace App\Http\Controllers\Report;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ZpkReportController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Report',
        ]);
    }

    /**
     * Display the ZPK report.
     */
    public function index(Request $request)
    {
        $title = "Report ZPK";
        $nav = "ZPK";
        $search = $request->input('search', '');

        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|in:25,50,100',
            ]);
            $request->session()->put('zpk_perPage', (int) $request->input('perPage'));
        }

        $perPage = $request->session()->get('zpk_perPage', 100);

        $query = $this->buildBaseQuery($startDate, $endDate, $search);

        $zpk = $query->select('a.*', 'c.lkhdate', DB::raw('d.blok as blok'))
            ->paginate($perPage);

        $this->transformItems($zpk);

        $view = 'report.zpk.index';

        return view($view, compact('title', 'nav', 'search', 'perPage', 'startDate', 'endDate', 'zpk'));
    }

    /**
     * Export ZPK data to Excel.
     */
    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $zpk = $this->buildBaseQuery($startDate, $endDate)
            ->orderBy('a.plot', 'desc')
            ->select('a.*', 'c.lkhdate', DB::raw('d.blok as blok'))
            ->get();

        if ($zpk->isEmpty()) {
            return response()->json([
                'error' => 'Tidak ada data ZPK pada periode yang dipilih untuk diekspor.'
            ], 200);
        }

        $spreadsheet = $this->buildSpreadsheet($zpk, $startDate, $endDate);
        $filename = 'ZPKReport' . ($startDate ? "_{$startDate}_sd_{$endDate}" : '') . '.xlsx';

        return response()->streamDownload(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    /**
     * Build the base query for ZPK data (reused by index & export).
     */
    private function buildBaseQuery(?string $startDate = null, ?string $endDate = null, ?string $search = null)
    {
        $query = DB::table('batch as a')
            ->join('lkhdetailplot as b', function ($join) {
                $join->on('b.plot', '=', 'a.plot')
                    ->on('b.companycode', '=', 'a.companycode');
            })
            ->join('lkhhdr as c', function ($join) {
                $join->on('c.lkhno', '=', 'b.lkhno')
                    ->on('c.companycode', '=', 'b.companycode');
            })
            ->leftJoin('masterlist as d', function ($join) {
                $join->on('a.plot', '=', 'd.plot')
                    ->on('a.companycode', '=', 'd.companycode')
                    ->where('d.isactive', '=', 1);
            })
            ->where('a.companycode', '=', session('companycode'))
            ->where('c.activitycode', '=', '4.2.2')
            ->where('a.isactive', '=', 1)
            ->when($startDate, fn($q) => $q->whereDate('c.lkhdate', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('c.lkhdate', '<=', $endDate));

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('a.kodevarietas', 'like', "%{$search}%")
                    ->orWhere('a.plot', 'like', "%{$search}%")
                    ->orWhere('a.lifecyclestatus', 'like', "%{$search}%")
                    ->orWhere('d.blok', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Transform paginated items with computed fields.
     *
     * Status Panen logic:
     * - Jika batch.tanggalpanen sudah terisi → "Sudah Panen"
     * - Jika hari ini > lkhdate + 35 hari    → "Lewat Batas (H+N)"
     * - Jika hari ini >= lkhdate + 26 hari   → "Menunggu Panen (H+N)"
     * - Jika hari ini <  lkhdate + 26 hari   → "Belum Boleh Panen (H+N)"
     */
    private function transformItems($zpk): void
    {
        $now = Carbon::now();
        $today = Carbon::today();

        foreach ($zpk as $index => $item) {
            $item->no = ($zpk->currentPage() - 1) * $zpk->perPage() + $index + 1;

            $tanggaltanam = Carbon::parse($item->batchdate);
            $item->umur = $tanggaltanam->diffInMonths($now);
            $item->bulantanam = $tanggaltanam->locale('id')->translatedFormat('F');

            if ($item->lkhdate) {
                $lkhdate = Carbon::parse($item->lkhdate);
                $item->tanggal_zpk = $lkhdate->locale('id')->translatedFormat('d F Y');
                $item->perkiraan_panen_awal = $lkhdate->copy()->addDays(26)->locale('id')->translatedFormat('d F Y');
                $item->perkiraan_panen_akhir = $lkhdate->copy()->addDays(35)->locale('id')->translatedFormat('d F Y');

                // Status panen
                $hariSejak = (int) $lkhdate->diffInDays($today);

                if (!empty($item->tanggalpanen)) {
                    $item->status_panen = 'Sudah Panen';
                    $item->status_panen_type = 'done';
                    $item->status_panen_hari = null;
                } elseif ($hariSejak > 35) {
                    $item->status_panen = 'Lewat Batas';
                    $item->status_panen_type = 'overdue';
                    $item->status_panen_hari = $hariSejak;
                } elseif ($hariSejak >= 26) {
                    $item->status_panen = 'Menunggu Panen';
                    $item->status_panen_type = 'ready';
                    $item->status_panen_hari = $hariSejak;
                } else {
                    $item->status_panen = 'Belum Boleh Panen';
                    $item->status_panen_type = 'waiting';
                    $item->status_panen_hari = $hariSejak;
                }
            } else {
                $item->tanggal_zpk = null;
                $item->perkiraan_panen_awal = null;
                $item->perkiraan_panen_akhir = null;
                $item->status_panen = null;
                $item->status_panen_type = null;
                $item->status_panen_hari = null;
            }
        }
    }

    /**
     * Build the Excel spreadsheet.
     */
    private function buildSpreadsheet($zpk, ?string $startDate, ?string $endDate): Spreadsheet
    {
        $now = Carbon::now();
        $today = Carbon::today();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Report ZPK');

        $hCenter = Alignment::HORIZONTAL_CENTER;
        $vCenter = Alignment::VERTICAL_CENTER;

        // ── Row 1: Title
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'Report ZPK (Zat Pemacu Kemasakan)');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => $hCenter, 'vertical' => $vCenter],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // ── Row 2: Period
        $sheet->mergeCells('A2:L2');
        $rangeLabel = ($startDate && $endDate)
            ? "Periode: {$startDate} s/d {$endDate}"
            : 'Data aplikasi zat pemacu kemasakan dan jadwal panen tebu';
        $sheet->setCellValue('A2', $rangeLabel);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '6B7280']],
            'alignment' => ['horizontal' => $hCenter],
        ]);

        // ── Row 3: Spacer
        $sheet->getRowDimension(3)->setRowHeight(6);

        // ── Row 4: Column Headers
        $headers = [
            'A' => 'No.',
            'B' => 'Blok',
            'C' => 'Plot',
            'D' => 'Tanggal ZPK',
            'E' => 'Luas (Ha)',
            'F' => 'Bulan Tanam',
            'G' => 'Umur',
            'H' => 'Kategori',
            'I' => 'Varietas',
            'J' => 'PKP',
            'K' => 'Perkiraan Panen',
            'L' => 'Status',
        ];

        foreach ($headers as $col => $label) {
            $cell = "{$col}4";
            $sheet->setCellValue($cell, $label);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E5E7EB']],
                'alignment' => ['horizontal' => $hCenter, 'vertical' => $vCenter],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
            ]);
        }
        $sheet->getRowDimension(4)->setRowHeight(18);
        $sheet->freezePane('A5');

        // ── Row 5+: Data
        $row = 5;
        $no = 1;
        foreach ($zpk as $item) {
            $tanggaltanam = Carbon::parse($item->batchdate);
            $umur = $tanggaltanam->diffInMonths($now);
            $bulantanam = $tanggaltanam->locale('id')->translatedFormat('F');

            $tanggalZpk = '-';
            $perkiraanPanen = '-';
            $statusPanen = '-';

            if ($item->lkhdate) {
                $lkhdate = Carbon::parse($item->lkhdate);
                $tanggalZpk = $lkhdate->locale('id')->translatedFormat('d F Y');
                $perkiraanPanen = $lkhdate->copy()->addDays(26)->locale('id')->translatedFormat('d F Y')
                    . ' – '
                    . $lkhdate->copy()->addDays(35)->locale('id')->translatedFormat('d F Y');

                $hariSejak = (int) $lkhdate->diffInDays($today);

                if (!empty($item->tanggalpanen)) {
                    $statusPanen = 'Sudah Panen';
                } elseif ($hariSejak > 35) {
                    $statusPanen = "Lewat Batas (H+{$hariSejak})";
                } elseif ($hariSejak >= 26) {
                    $statusPanen = "Menunggu Panen (H+{$hariSejak})";
                } else {
                    $statusPanen = "Belum Boleh Panen (H+{$hariSejak})";
                }
            }

            $rowData = [
                'A' => $no++,
                'B' => $item->blok ?? '-',
                'C' => $item->plot ?? '-',
                'D' => $tanggalZpk,
                'E' => ($item->batcharea ?? '-') . ' Ha',
                'F' => $bulantanam,
                'G' => round($umur) . ' Bulan',
                'H' => $item->lifecyclestatus ?? '-',
                'I' => $item->kodevarietas ?? '-',
                'J' => $item->pkp ?? '-',
                'K' => $perkiraanPanen,
                'L' => $statusPanen,
            ];

            foreach ($rowData as $col => $value) {
                $cell = "{$col}{$row}";
                $style = [
                    'alignment' => ['horizontal' => $hCenter, 'vertical' => $vCenter],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
                ];
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->applyFromArray($style);
            }

            $row++;
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}