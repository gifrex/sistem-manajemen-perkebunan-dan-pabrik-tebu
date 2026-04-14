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
}
