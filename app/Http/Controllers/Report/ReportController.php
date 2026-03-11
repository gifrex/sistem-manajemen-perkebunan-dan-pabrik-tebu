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

        $querys = DB::table('batch')
            ->join('lkhdetailplot', 'batch.plot', '=', 'lkhdetailplot.plot')
            ->join('lkhhdr', 'lkhhdr.lkhno', '=', 'lkhdetailplot.lkhno')
            ->where('batch.companycode', '=', session('companycode'))
            ->where('lkhhdr.activitycode', '=', '4.2.1')
            ->where('batch.isactive', '=', 1)
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('lkhhdr.lkhdate', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('lkhhdr.lkhdate', '<=', $endDate);
            });
        if (!empty($search)) {
            $querys->where(function ($query) use ($search) {
                $query->where('kodevarietas', 'like', '%' . $search . '%')
                    ->orWhere('plot', 'like', '%' . $search . '%')
                    ->orWhere('kodestatus', 'like', '%' . $search . '%');
            });
        }

        $zpk = $querys->select('batch.*', 'lkhhdr.lkhdate')
            ->paginate($perPage);

        foreach ($zpk as $item) {
            $item->umur = Carbon::parse($item->batchdate)->diffInMonths(Carbon::now());
            $tanggaltanam = Carbon::parse($item->batchdate);
            $item->bulantanam = $tanggaltanam->locale('id')->translatedFormat('F');
            if ($item->lkhdate) {
                $lkhdate = Carbon::parse($item->lkhdate);
                $item->perkiraan_panen_awal = $lkhdate->copy()->addDays(28)->format('d/m/Y');
                $item->perkiraan_panen_akhir = $lkhdate->copy()->addDays(35)->format('d/m/Y');
            } else {
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

        $querys = DB::table('batch')
            ->join('lkhdetailplot', 'batch.plot', '=', 'lkhdetailplot.plot')
            ->join('lkhhdr', 'lkhhdr.lkhno', '=', 'lkhdetailplot.lkhno')
            ->where('batch.companycode', '=', session('companycode'))
            ->where('lkhhdr.activitycode', '=', '4.2.1')
            ->where('batch.isactive', '=', 1)
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('lkhhdr.lkhdate', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('lkhhdr.lkhdate', '<=', $endDate);
            })
            ->orderBy('batch.plot', 'desc');

        $zpk = $querys->select('batch.*', 'lkhhdr.lkhdate')->get();

        $now = Carbon::now();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Kebun');
        $sheet->setCellValue('B1', 'Blok');
        $sheet->setCellValue('C1', 'Plot');
        $sheet->setCellValue('D1', 'Luas (Ha)');
        $sheet->setCellValue('E1', 'Bulan Tanam');
        $sheet->setCellValue('F1', 'Umur');
        $sheet->setCellValue('G1', 'Kategori');
        $sheet->setCellValue('H1', 'Varietas');
        $sheet->setCellValue('I1', 'PKP');
        $sheet->setCellValue('J1', 'Tanggal ZPK');
        $sheet->setCellValue('K1', 'Perkiraan Panen Awal');
        $sheet->setCellValue('L1', 'Perkiraan Panen Akhir');

        $sheet->getStyle('A1:L1')->getFont()->setBold(true);
        $sheet->freezePane('A2');

        $row = 2;
        foreach ($zpk as $list) {
            $tanggaltanam = Carbon::parse($list->batchdate);
            $umur = $tanggaltanam->diffInMonths($now);
            $bulantanam = $tanggaltanam->locale('id')->translatedFormat('F');

            if ($list->lkhdate) {
                $lkhdate = Carbon::parse($list->lkhdate);
                $perkiraan_panen_awal = $lkhdate->copy()->addDays(28)->format('d/m/Y');
                $perkiraan_panen_akhir = $lkhdate->copy()->addDays(35)->format('d/m/Y');
            } else {
                $perkiraan_panen_awal = '';
                $perkiraan_panen_akhir = '';
            }

            $sheet->setCellValue('A' . $row, $list->companycode);
            $sheet->setCellValue('B' . $row, $list->blok);
            $sheet->setCellValue('C' . $row, $list->plot);
            $sheet->setCellValue('D' . $row, $list->batcharea);
            $sheet->setCellValue('E' . $row, $bulantanam);
            $sheet->setCellValue('F' . $row, round($umur) . ' Bulan');
            $sheet->setCellValue('G' . $row, $list->kodestatus);
            $sheet->setCellValue('H' . $row, $list->kodevarietas);
            $sheet->setCellValue('I' . $row, $list->pkp);
            $sheet->setCellValue('J' . $row, $list->lkhdate ?? '');
            $sheet->setCellValue('K' . $row, $perkiraan_panen_awal);
            $sheet->setCellValue('L' . $row, $perkiraan_panen_akhir);

            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = "ZPKReport.xlsx";
        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment;filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }
}
