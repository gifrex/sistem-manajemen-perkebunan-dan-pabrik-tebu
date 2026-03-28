<?php

namespace App\Http\Controllers\Report;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Report',
        ]);
    }
    public function agronomi(Request $request)
    {
        $title = "Report Agronomi";
        $nav = "Agronomi";
        $search = $request->input('search', '');

        $company = DB::table('company')->get();

        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        $querys = DB::table('agrolst')
            ->leftJoin('agrohdr', function ($join) {
                $join->on('agrolst.nosample', '=', 'agrohdr.nosample')
                    ->whereColumn('agrolst.companycode', '=', 'agrohdr.companycode')
                    ->whereColumn('agrolst.tanggalpengamatan', '=', 'agrohdr.tanggalpengamatan');
            })
            ->leftJoin('company', function ($join) {
                $join->on('agrohdr.companycode', '=', 'company.companycode');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('agrohdr.blok', '=', 'blok.blok')
                    ->whereColumn('agrohdr.companycode', '=', 'blok.companycode');
            })
            ->leftJoin('batch', function ($join) {
                $join->on('agrohdr.plot', '=', 'batch.plot')
                    ->whereColumn('agrohdr.companycode', '=', 'batch.companycode');
            })
            ->where('agrolst.companycode', session('companycode'))
            ->where('agrohdr.companycode', session('companycode'))
            ->where('agrohdr.status', '=', 'Posted')
            ->where('agrolst.status', '=', 'Posted')
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('agrohdr.tanggalpengamatan', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('agrohdr.tanggalpengamatan', '<=', $endDate);
            });

        if (!empty($search)) {
            $querys->where(function ($query) use ($search) {
                $query->where('agrohdr.nosample', 'like', '%' . $search . '%')
                    ->orWhere('agrohdr.plot', 'like', '%' . $search . '%')
                    ->orWhere('agrohdr.varietas', 'like', '%' . $search . '%')
                    ->orWhere('agrohdr.kat', 'like', '%' . $search . '%');
            });
        }

        $querys = $querys->select(
            'agrolst.*',
            'agrohdr.varietas',
            'agrohdr.kat',
            'agrohdr.tanggaltanam',
            'agrohdr.bulanpanen',
            'agrohdr.umurpanen',
            'agrohdr.tanggalzpk',
            'agrohdr.tanggaltanam',
            'company.name as compName',
            'blok.blok as blokName',
            'batch.plot as plotName',
            'batch.batcharea as luasarea',
            'batch.pkp as jaraktanam',
        )
            ->orderBy('agrohdr.tanggalpengamatan', 'asc');

        $agronomi = $querys->paginate($perPage);

        foreach ($agronomi as $item) {
            $item->umur_tanam = Carbon::parse($item->tanggaltanam)->diffInMonths(Carbon::now());
            $dateInput = Carbon::parse($item->tanggalpengamatan);
            $item->bulanPengamatan = $dateInput->format('F');
            $item->tanggaltanam_fmt = Carbon::parse($item->tanggaltanam)->format('d-M-Y');
            $item->tanggalpengamatan_fmt = $dateInput->format('d-M-Y');
            $item->tanggalzpk_fmt = $item->tanggalzpk ? Carbon::parse($item->tanggalzpk)->format('d-M-Y') : '-';
        }

        foreach ($agronomi as $index => $item) {
            $item->no = ($agronomi->currentPage() - 1) * $agronomi->perPage() + $index + 1;
        }

        if ($request->ajax()) {
            return view('report.agronomi.index', compact('company', 'nav', 'agronomi', 'perPage', 'startDate', 'endDate', 'title', 'search'));
        }
        return view('report.agronomi.index', compact('company', 'nav', 'agronomi', 'perPage', 'startDate', 'endDate', 'title', 'search'));
    }

    public function hpt(Request $request)
    {
        $title = "Report HPT";
        $nav = "HPT";
        $search = $request->input('search', '');
        $company = DB::table('company')->get();

        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        $querys = DB::table('hptlst')
            ->leftJoin('hpthdr', function ($join) {
                $join->on('hptlst.nosample', '=', 'hpthdr.nosample')
                    ->whereColumn('hptlst.companycode', '=', 'hpthdr.companycode')
                    ->whereColumn('hptlst.tanggalpengamatan', '=', 'hpthdr.tanggalpengamatan');
            })
            ->leftJoin('company', function ($join) {
                $join->on('hpthdr.companycode', '=', 'company.companycode');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('hpthdr.blok', '=', 'blok.blok')
                    ->whereColumn('hpthdr.companycode', '=', 'blok.companycode');
            })
            ->leftJoin('batch', function ($join) {
                $join->on('hpthdr.plot', '=', 'batch.plot')
                    ->whereColumn('hpthdr.companycode', '=', 'batch.companycode');
            })
            ->where('hptlst.companycode', session('companycode'))
            ->where('hpthdr.companycode', session('companycode'))
            ->where('hpthdr.status', '=', 'Posted')
            ->where('hptlst.status', '=', 'Posted')
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('hpthdr.tanggalpengamatan', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('hpthdr.tanggalpengamatan', '<=', $endDate);
            });

        if (!empty($search)) {
            $querys->where(function ($query) use ($search) {
                $query->where('hpthdr.nosample', 'like', '%' . $search . '%')
                    ->orWhere('hpthdr.varietas', 'like', '%' . $search . '%')
                    ->orWhere('hpthdr.plot', 'like', '%' . $search . '%')
                    ->orWhere('hpthdr.kat', 'like', '%' . $search . '%');
            });
        }
        $querys = $querys->select(
            'hptlst.*',
            'hpthdr.varietas',
            'hpthdr.tanggaltanam',
            'company.name as compName',
            'blok.blok as blokName',
            'batch.plot as plotName',
            'batch.batcharea as luasarea',
        )
            ->orderBy('hpthdr.tanggalpengamatan', 'desc');

        $hpt = $querys->paginate($perPage);

        foreach ($hpt as $item) {
            $item->umur_tanam = Carbon::parse($item->tanggaltanam)->diffInMonths(Carbon::now());
            $dateInput = Carbon::parse($item->tanggalpengamatan);
            $item->bulanPengamatan = $dateInput->format('F');
            $item->tanggaltanam_fmt = Carbon::parse($item->tanggaltanam)->format('d-M-Y');
            $item->tanggalpengamatan_fmt = $dateInput->format('d-M-Y');
        }

        foreach ($hpt as $index => $item) {
            $item->no = ($hpt->currentPage() - 1) * $hpt->perPage() + $index + 1;
        }

        if ($request->ajax()) {
            return view('report.hpt.index', compact('company', 'nav', 'hpt', 'perPage', 'startDate', 'endDate', 'title', 'search'));
        }
        return view('report.hpt.index', compact('company', 'nav', 'hpt', 'perPage', 'startDate', 'endDate', 'title', 'search'));
    }

    public function zpk(Request $request)
    {
        $title = "Report ZPK";
        $nav = "ZPK";
        $search = $request->input('search', '');

        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        $querys = DB::table('batch as a')
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
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('c.lkhdate', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('c.lkhdate', '<=', $endDate);
            });
        if (!empty($search)) {
            $querys->where(function ($query) use ($search) {
                $query->where('a.kodevarietas', 'like', '%' . $search . '%')
                    ->orWhere('a.plot', 'like', '%' . $search . '%')
                    ->orWhere('a.kodestatus', 'like', '%' . $search . '%');
            });
        }

        $zpk = $querys->select('a.*', 'c.lkhdate', DB::raw('d.blok as blok'))
            ->paginate($perPage);

        foreach ($zpk as $item) {
            $item->umur = Carbon::parse($item->batchdate)->diffInMonths(Carbon::now());
            $tanggaltanam = Carbon::parse($item->batchdate);
            $item->bulantanam = $tanggaltanam->locale('id')->translatedFormat('F');
            if ($item->lkhdate) {
                $lkhdate = Carbon::parse($item->lkhdate);
                $item->tanggal_zpk = $lkhdate->locale('id')->translatedFormat('d F Y');
                $item->perkiraan_panen_awal = $lkhdate->copy()->addDays(28)->locale('id')->translatedFormat('d F Y');
                $item->perkiraan_panen_akhir = $lkhdate->copy()->addDays(35)->locale('id')->translatedFormat('d F Y');
            } else {
                $item->tanggal_zpk = null;
                $item->perkiraan_panen_awal = null;
                $item->perkiraan_panen_akhir = null;
            }
        }

        foreach ($zpk as $index => $item) {
            $item->no = ($zpk->currentPage() - 1) * $zpk->perPage() + $index + 1;
        }

        if ($request->ajax()) {
            return view('report.zpk.index', compact('title', 'nav', 'search', 'perPage', 'startDate', 'endDate', 'zpk'));
        }

        return view('report.zpk.index', compact('title', 'nav', 'search', 'perPage', 'startDate', 'endDate', 'zpk'));
    }

    public function trash(Request $request)
    {
        $title = "Report Trash";
        $nav = "Trash Report";
        $search = $request->input('search', '');

        $company = DB::table('company')->get();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $reportType = $request->input('report_type', 'summary');

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        return view('report.trash.index', compact('company', 'nav', 'perPage', 'startDate', 'endDate', 'title', 'search', 'reportType'));
    }

    public function excelZPK(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $zpk = DB::table('batch as a')
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
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('c.lkhdate', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('c.lkhdate', '<=', $endDate);
            })
            ->orderBy('a.plot', 'desc')
            ->select('a.*', 'c.lkhdate', DB::raw('d.blok as blok'))
            ->get();

        if ($zpk->isEmpty()) {
            return response()->json(['error' => 'Tidak ada data ZPK pada periode yang dipilih untuk diekspor.'], 200);
        }

        $spreadsheet = $this->buildZPKSpreadsheet($zpk, $startDate, $endDate);
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

    private function buildZPKSpreadsheet($zpk, ?string $startDate, ?string $endDate): Spreadsheet
    {
        $now = Carbon::now();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Report ZPK');

        $alignCenter = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        $alignVCenter = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        $fillSolid = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
        $borderThin = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;

        // ── Baris 1: Judul ───────────────────────────────────────────
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'Report ZPK (Zat Pemacu Kemasakan)');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => $alignCenter, 'vertical' => $alignVCenter],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // ── Baris 2: Periode ─────────────────────────────────────────
        $sheet->mergeCells('A2:L2');
        $rangeLabel = ($startDate && $endDate)
            ? "Periode: {$startDate} s/d {$endDate}"
            : 'Data aplikasi zat pemacu kemasakan dan jadwal panen tebu';
        $sheet->setCellValue('A2', $rangeLabel);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '6B7280']],
            'alignment' => ['horizontal' => $alignCenter],
        ]);

        // ── Baris 3: Kosong ──────────────────────────────────────────
        $sheet->getRowDimension(3)->setRowHeight(6);

        // ── Baris 4: Header kolom ────────────────────────────────────
        $headers = [
            'A' => ['No.', 'E5E7EB'],
            'B' => ['Kebun', 'E5E7EB'],
            'C' => ['Blok', 'E5E7EB'],
            'D' => ['Plot', 'E5E7EB'],
            'E' => ['Luas (Ha)', 'BFDBFE'],
            'F' => ['Bulan Tanam', 'E5E7EB'],
            'G' => ['Umur', 'BBF7D0'],
            'H' => ['Kategori', 'E5E7EB'],
            'I' => ['Varietas', 'E5E7EB'],
            'J' => ['PKP', 'FDE68A'],
            'K' => ['Tanggal ZPK', 'DDD6FE'],
            'L' => ['Perkiraan Panen', 'FECACA'],
        ];

        foreach ($headers as $col => [$label, $bg]) {
            $cell = "{$col}4";
            $sheet->setCellValue($cell, $label);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => $fillSolid, 'startColor' => ['rgb' => $bg]],
                'alignment' => ['horizontal' => $alignCenter, 'vertical' => $alignVCenter],
                'borders' => ['allBorders' => ['borderStyle' => $borderThin, 'color' => ['rgb' => 'D1D5DB']]],
            ]);
        }
        $sheet->getRowDimension(4)->setRowHeight(18);
        $sheet->freezePane('A5');

        // ── Baris 5+: Data ───────────────────────────────────────────
        $colColors = [
            'E' => 'EFF6FF',
            'G' => 'F0FDF4',
            'J' => 'FEFCE8',
            'K' => 'FAF5FF',
            'L' => 'FFF1F2',
        ];

        $row = 5;
        $no = 1;
        foreach ($zpk as $list) {
            $tanggaltanam = Carbon::parse($list->batchdate);
            $umur = $tanggaltanam->diffInMonths($now);
            $bulantanam = $tanggaltanam->locale('id')->translatedFormat('F');

            if ($list->lkhdate) {
                $lkhdate = Carbon::parse($list->lkhdate);
                $tanggalZpk = $lkhdate->locale('id')->translatedFormat('d F Y');
                $perkiraanPanen = $lkhdate->copy()->addDays(28)->locale('id')->translatedFormat('d F Y')
                    . ' – '
                    . $lkhdate->copy()->addDays(35)->locale('id')->translatedFormat('d F Y');
            } else {
                $tanggalZpk = '-';
                $perkiraanPanen = '-';
            }

            $rowData = [
                'A' => $no++,
                'B' => $list->companycode ?? '-',
                'C' => $list->blok ?? '-',
                'D' => $list->plot ?? '-',
                'E' => ($list->batcharea ?? '-') . ' Ha',
                'F' => $bulantanam,
                'G' => round($umur) . ' Bulan',
                'H' => $list->lifecyclestatus ?? '-',
                'I' => $list->kodevarietas ?? '-',
                'J' => $list->pkp ?? '-',
                'K' => $tanggalZpk,
                'L' => $perkiraanPanen,
            ];

            foreach ($rowData as $col => $value) {
                $cell = "{$col}{$row}";
                $style = [
                    'alignment' => ['horizontal' => $alignCenter, 'vertical' => $alignVCenter],
                    'borders' => ['allBorders' => ['borderStyle' => $borderThin, 'color' => ['rgb' => 'E5E7EB']]],
                ];
                if (isset($colColors[$col])) {
                    $style['fill'] = ['fillType' => $fillSolid, 'startColor' => ['rgb' => $colColors[$col]]];
                }
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
