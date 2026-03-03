<?php

namespace App\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class RencanaKerjaMingguanController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Transaction',
            'nav' => 'Rencana Kerja Mingguan',
        ]);
    }

    protected function requestValidated(): array
    {
        return [
            'rkmno' => 'required',
            'rkmdate' => 'required',
            'startdate' => 'required',
            'enddate' => 'required',
            'activitycode' => 'required',
            'lists.*.blok' => 'required',
            'lists.*.plot' => 'required',
            'lists.*.totalluasactual' => 'required',
            'lists.*.totalestimasi' => 'required',
        ];
    }

    public function index(Request $request)
    {
        $title = 'Rencana Kerja Mingguan';

        $search = $request->input('search', '');
        $isClosing = $request->input('isclosing', 0);
        $startDate = $request->input('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfWeek()->format('Y-m-d'));

        $userid = Auth::user()->userid;

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        $querys = DB::table('rkmhdr')
            ->join('activity', 'activity.activitycode', '=', 'rkmhdr.activitycode')
            ->where('rkmhdr.companycode', '=', session('companycode'))
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('rkmhdr.rkmdate', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('rkmhdr.rkmdate', '<=', $endDate);
            });

        if ($userid != 'Admin') {
            $querys->where('rkmhdr.inputby', '=', $userid);
        }


        if (!empty($search)) {
            $querys->where(function ($query) use ($search) {
                $query->where('rkmhdr.rkmno', 'like', '%' . $search . '%')
                    ->orWhere('rkmhdr.activitycode', 'like', '%' . $search . '%')
                    ->orWhere('activity.activityname', 'like', '%' . $search . '%');
            });
        }

        $rkm = $querys->select('rkmhdr.*', 'activity.activityname')
            ->orderBy('rkmdate', 'desc')
            ->orderBy('createdat', 'desc')
            ->paginate($perPage);

        foreach ($rkm as $index => $item) {
            $item->no = ($rkm->currentPage() - 1) * $rkm->perPage() + $index + 1;
        }

        if ($request->ajax()) {
            return view('transaction.rkm.index', compact('title', 'search', 'perPage', 'rkm', 'startDate', 'endDate'));
        }

        return view('transaction.rkm.index', compact('title', 'search', 'perPage', 'rkm', 'startDate', 'endDate'));
    }

    public function create(Request $request)
    {
        $title = "Create RKM";
        $activity = DB::table('activity')->orderBy('activitycode', 'asc')->get();
        $bloks = DB::table('blok')->where('companycode', '=', session('companycode'))->orderBy('blok', 'asc')->get();
        $method = 'POST';
        $url = route('transaction.rencana-kerja-mingguan.store');
        $buttonSubmit = 'Submit';
        $selectedDate = $request->input('targetDate');

        if (!$this->validateDateRange($selectedDate)) {
            return redirect()->route('transaction.rencana-kerja-mingguan.index')
                ->with('error', 'Tanggal harus dalam rentang hari ini sampai 7 hari ke depan');
        }

        $targetDate = Carbon::parse($selectedDate);
        $rkmno = $this->generatePreviewRkmNo($targetDate, session('companycode'));
        return view('transaction.rkm.form', compact('buttonSubmit', 'activity', 'title', 'method', 'url', 'rkmno', 'selectedDate', 'bloks'));
    }

    private function generatePreviewRkmNo($targetDate, $companycode)
    {
        $day = $targetDate->format('d');
        $month = $targetDate->format('m');
        $year = $targetDate->format('y');

        $lastRkm = DB::table('rkmhdr')
            ->where('companycode', $companycode)
            ->whereDate('rkmdate', $targetDate)
            ->where('rkmno', 'like', "RKM{$day}{$month}%" . $year)
            ->orderBy(DB::raw('CAST(SUBSTRING(rkmno, 8, 2) AS UNSIGNED)'), 'desc')
            ->first();

        $newNumber = $lastRkm ? str_pad(((int) substr($lastRkm->rkmno, 7, 2)) + 1, 2, '0', STR_PAD_LEFT) : '01';
        return "RKM{$day}{$month}{$newNumber}{$year}";
    }

    private function validateDateRange($selectedDate)
    {
        $targetDate = Carbon::parse($selectedDate);
        $today = Carbon::today();
        $maxDate = Carbon::today()->addDays(7);

        return $targetDate->gte($today) && $targetDate->lte($maxDate);
    }

    public function getPlot($blok)
    {
        $comp = session('companycode');
        $plots = DB::table('masterlist')
            ->where('companycode', $comp)

            ->where('plot', 'like', $blok . '%')
            ->orderByRaw("LEFT(plot, 1), CAST(SUBSTRING(plot, 2) AS UNSIGNED)")
            ->pluck('plot');

        return response()->json($plots);
    }

    public function getData(Request $request)
    {
        $plot = $request->input('plot');
        $luas = DB::table('lkhdetailplot')
            ->where('plot', $plot)
            ->where('companycode', session('companycode'))
            ->orderBy('createdat', 'desc')
            ->first();
        $luasActual = DB::table('batch')
            ->where('plot', $plot)
            ->where('isactive', 1)
            ->where('companycode', session('companycode'))
            ->first();

        if ($luas && $luas->luassisa != 0) {
            return response()->json([
                'luasarea' => $luas->luassisa,
            ]);
        }
        if ((!$luas) || $luas && $luas->luassisa == 0) {
            return response()->json([
                'luasarea' => $luasActual ? $luasActual->batcharea : 0,
            ]);
        }

        return response()->json(['message' => 'Data not found'], 404);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->requestValidated());

        $existsInHeader = DB::table('rkmhdr')->where('rkmno', $request->rkmno)
            ->where('companycode', $request->companycode)
            ->exists();

        $existsInLists = DB::table('rkmlst')->where('rkmno', $request->rkmno)
            ->where('companycode', $request->companycode)
            ->exists();

        if ($existsInHeader || $existsInLists) {
            return back()->with([
                'success1' => 'Data sudah ada di salah satu tabel, silahkan coba dengan data yang berbeda.',
            ])->withInput();
        }

        DB::beginTransaction();

        try {

            DB::table('rkmhdr')->insert([
                'rkmno' => $validated['rkmno'],
                'rkmdate' => $validated['rkmdate'],
                'companycode' => session('companycode'),
                'startdate' => $validated['startdate'],
                'enddate' => $validated['enddate'],
                'activitycode' => $validated['activitycode'],
                'inputby' => Auth::user()->userid,
                'createdat' => now(),
                'updateby' => Auth::user()->userid,
                'updatedat' => now(),
            ]);

            foreach ($validated['lists'] as $list) {

                DB::table('rkmlst')->insert([
                    'rkmno' => $validated['rkmno'],
                    'companycode' => session('companycode'),
                    'blok' => $list['blok'],
                    'plot' => $list['plot'],
                    'totalluasactual' => $list['totalluasactual'],
                    'totalestimasi' => $list['totalestimasi'],
                ]);
            }

            DB::commit();
            return redirect()->route('transaction.rencana-kerja-mingguan.index')
                ->with('success1', 'Data created successfully.');
        } catch (\Exception $e) {

            DB::rollBack();
            return redirect()->route('transaction.rencana-kerja-mingguan.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function show($rkmno)
    {
        $companyCode = session('companycode');

        $rkm = DB::table('rkmhdr')
            ->where('companycode', $companyCode)
            ->where('rkmno', $rkmno)
            ->first();

        if (!$rkm) {
            abort(404, 'RKM header not found');
        }

        $rkmLists = DB::table('rkmhdr as a')
            ->leftJoin('rkmlst as b', function ($join) {
                $join->on('a.companycode', '=', 'b.companycode')
                    ->on('a.rkmno', '=', 'b.rkmno');
            })
            ->leftJoin('lkhhdr as c', function ($join) {
                $join->on('a.companycode', '=', 'c.companycode')
                    ->on('a.activitycode', '=', 'c.activitycode')
                    ->whereBetween('c.lkhdate', [DB::raw('a.startdate'), DB::raw('a.enddate')]);
            })
            ->leftJoin('lkhdetailplot as d', function ($join) {
                $join->on('c.lkhno', '=', 'd.lkhno')
                    ->on('c.companycode', '=', 'd.companycode')
                    ->on('b.plot', '=', 'd.plot');
            })
            ->leftJoin('activity as act', 'a.activitycode', '=', 'act.activitycode')
            ->select(
                'a.rkmno',
                'a.startdate',
                'a.enddate',
                'a.activitycode',
                'act.activityname',
                'b.totalestimasi',
                'b.blok',
                'b.plot',
                'b.totalluasactual',
                DB::raw('COALESCE(SUM(d.luashasil), 0) AS hasil'),
                DB::raw('COALESCE(b.totalestimasi - SUM(d.luashasil), b.totalestimasi) AS sisa')
            )
            ->where('a.companycode', $companyCode)
            ->where('a.rkmno', $rkmno)
            ->orderBy('b.blok', 'asc')
            ->orderBy('b.plot', 'asc')
            ->groupBy(
                'a.rkmno',
                'a.startdate',
                'a.enddate',
                'a.activitycode',
                'act.activityname',
                'b.totalestimasi',
                'b.blok',
                'b.plot',
                'b.totalluasactual'
            )
            ->get();

        foreach ($rkmLists as $index => $item) {
            $item->no = $index + 1;
        }

        return response()->json($rkmLists);
    }


    public function edit($rkmno)
    {
        $title = 'Edit Data';
        $activity = DB::table('activity')->orderBy('activitycode', 'asc')->get();
        $bloks = DB::table('blok')->where('companycode', '=', session('companycode'))->orderBy('blok', 'asc')->get();

        $header = DB::table('rkmhdr')->where('rkmno', $rkmno)
            ->where('companycode', session('companycode'))
            ->first();
        $lists = DB::table('rkmlst')
            ->where('rkmno', $rkmno)
            ->where('companycode', session('companycode'))
            ->get();
        $list = $lists->first();
        $header->lists = $lists;

        $method = 'PUT';
        $buttonSubmit = 'Update';
        $url = route('transaction.rencana-kerja-mingguan.update', ['rkmno' => $rkmno]);

        if ($header->isclosing === "1") {
            return redirect()->route('transaction.rencana-kerja-mingguan.index')->with('success1', 'Data telah di closing, tidak dapat di edit.');
        }

        return view('transaction.rkm.form', compact('buttonSubmit', 'header', 'list', 'title', 'method', 'url', 'activity', 'bloks'));
    }

    public function update(Request $request, $rkmno)
    {
        $validated = $request->validate($this->requestValidated());

        DB::beginTransaction();

        try {
            DB::table('rkmhdr')
                ->where('rkmno', $rkmno)
                ->where('companycode', session('companycode'))
                ->update([
                    'rkmno' => $validated['rkmno'],
                    'rkmdate' => $validated['rkmdate'],
                    'companycode' => session('companycode'),
                    'startdate' => $validated['startdate'],
                    'enddate' => $validated['enddate'],
                    'activitycode' => $validated['activitycode'],
                    'updateby' => Auth::user()->userid,
                    'updatedat' => now(),
                ]);

            $lists = DB::table('rkmlst')
                ->where('rkmno', $rkmno)
                ->where('companycode', session('companycode'));

            $lists->delete();

            foreach ($validated['lists'] as $list) {
                $data = [
                    'rkmno' => $validated['rkmno'],
                    'companycode' => session('companycode'),
                    'blok' => $list['blok'],
                    'plot' => $list['plot'],
                    'totalluasactual' => $list['totalluasactual'],
                    'totalestimasi' => $list['totalestimasi'],
                ];
                DB::table('rkmlst')->insert($data);
            }

            DB::commit();

            return redirect()->route('transaction.rencana-kerja-mingguan.index')
                ->with('success1', 'Data updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('transaction.rencana-kerja-mingguan.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function destroy($rkmno)
    {
        DB::transaction(function () use ($rkmno) {
            DB::table('rkmhdr')
                ->where('rkmno', $rkmno)
                ->where('companycode', session('companycode'))
                ->delete();
            DB::table('rkmlst')
                ->where('rkmno', $rkmno)
                ->where('companycode', session('companycode'))
                ->delete();
        });
        return redirect()->route('transaction.rencana-kerja-mingguan.index')
            ->with('success1', 'Data deleted successfully.');
    }

    public function excel(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfWeek()->format('Y-m-d'));
        $search = $request->input('search');
        $companyCode = session('companycode');

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'search' => 'nullable|string'
        ]);

        $query = DB::table('rkmhdr as a')
            ->leftJoin('rkmlst as b', function ($join) {
                $join->on('a.companycode', '=', 'b.companycode')
                    ->on('a.rkmno', '=', 'b.rkmno');
            })
            ->leftJoin('lkhhdr as c', function ($join) {
                $join->on('a.companycode', '=', 'b.companycode')
                    ->on('a.activitycode', '=', 'c.activitycode')
                    ->whereBetween('c.lkhdate', [DB::raw('a.startdate'), DB::raw('a.enddate')]);
            })
            ->leftJoin('lkhdetailplot as d', function ($join) {
                $join->on('c.lkhno', '=', 'd.lkhno')
                    ->on('c.companycode', '=', 'd.companycode')
                    ->on('b.plot', '=', 'd.plot');
            })
            ->leftJoin('activity as act', 'a.activitycode', '=', 'act.activitycode')
            ->select(
                'a.rkmno',
                'a.startdate',
                'a.enddate',
                'a.activitycode',
                'a.rkmdate',
                'a.inputby',
                'act.activityname',
                'b.totalestimasi',
                'b.blok',
                'b.plot',
                'b.totalluasactual',
                DB::raw('COALESCE(SUM(d.luashasil), 0) AS hasil'),
                DB::raw('COALESCE(b.totalestimasi - SUM(d.luashasil), b.totalestimasi) AS sisa')
            )
            ->where('a.companycode', $companyCode)
            ->groupBy(
                'a.rkmno',
                'a.startdate',
                'a.enddate',
                'a.activitycode',
                'a.rkmdate',
                'a.inputby',
                'act.activityname',
                'b.totalestimasi',
                'b.blok',
                'b.plot',
                'b.totalluasactual'
            )
            ->orderBy('a.rkmno', 'desc');

        if ($startDate) {
            $query->whereDate('a.rkmdate', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('a.rkmdate', '<=', $endDate);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('b.rkmno', 'like', "%{$search}%")
                    ->orWhere('a.activitycode', 'like', "%{$search}%")
                    ->orWhere('act.activityname', 'like', "%{$search}%");
            });
        }

        $now = Carbon::now();


        if ($startDate && $endDate) {
            $filename = "RKMReport_{$startDate}_sd_{$endDate}.xlsx";
        } else {
            $filename = "RKMReport.xlsx";
        }


        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempFile = $tempDir . '/' . $filename;


        $writer = WriterEntityFactory::createXLSXWriter();


        $writer->setTempFolder($tempDir);

        $writer->openToFile($tempFile);


        $headerStyle = (new StyleBuilder())
            ->setFontBold()
            ->build();


        $headerCells = [
            WriterEntityFactory::createCell('No. RKM'),
            WriterEntityFactory::createCell('RKM Date'),
            WriterEntityFactory::createCell('Start Date'),
            WriterEntityFactory::createCell('End Date'),
            WriterEntityFactory::createCell('Blok'),
            WriterEntityFactory::createCell('Plot'),
            WriterEntityFactory::createCell('Luas Plot (Ha)'),
            WriterEntityFactory::createCell('Estimasi Pengerjaan (Ha)'),
            WriterEntityFactory::createCell('Aktual Pengerjaan (Ha)'),
            WriterEntityFactory::createCell('Sisa (Ha)'),
            WriterEntityFactory::createCell('Kode Aktivitas'),
            WriterEntityFactory::createCell('Nama Aktivitas'),
            WriterEntityFactory::createCell('Dibuat Oleh'),
        ];

        $headerRow = WriterEntityFactory::createRow($headerCells, $headerStyle);
        $writer->addRow($headerRow);


        $query->chunk(1000, function ($rkmChunk) use ($writer, $now) {
            $rows = [];

            foreach ($rkmChunk as $list) {
                $decimalStyle = (new StyleBuilder())
                    ->setFormat('0.00')
                    ->build();

                $cells = [
                    WriterEntityFactory::createCell($list->rkmno),
                    WriterEntityFactory::createCell($list->rkmdate),
                    WriterEntityFactory::createCell($list->startdate),
                    WriterEntityFactory::createCell($list->enddate),
                    WriterEntityFactory::createCell($list->blok),
                    WriterEntityFactory::createCell($list->plot),
                    WriterEntityFactory::createCell($list->totalluasactual),
                    WriterEntityFactory::createCell($list->totalestimasi),
                    WriterEntityFactory::createCell($list->hasil),
                    WriterEntityFactory::createCell($list->sisa),
                    WriterEntityFactory::createCell($list->activitycode),
                    WriterEntityFactory::createCell($list->activityname),
                    WriterEntityFactory::createCell($list->inputby),
                ];

                $rows[] = WriterEntityFactory::createRow($cells);
            }

            $writer->addRows($rows);

            unset($rows);
            gc_collect_cycles();
        });

        $writer->close();


        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ])->deleteFileAfterSend(true);
    }
}
