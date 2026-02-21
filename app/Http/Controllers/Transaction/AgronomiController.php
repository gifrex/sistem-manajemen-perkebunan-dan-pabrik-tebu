<?php

namespace App\Http\Controllers\Transaction;

use Carbon\Carbon;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class AgronomiController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Input Data',
            'nav' => 'Agronomi',
            'routeName' => route('transaction.agronomi.index'),
        ]);
    }

    protected function requestValidated(): array
    {
        return [
            'nosample' => 'required',
            'companycode' => 'required',
            'blok' => 'required',
            'plot' => 'required',
            'varietas' => 'required',
            'kat' => 'required',
            'pkp' => 'nullable|numeric',
            'tanggaltanam' => 'required',
            'tanggalpengamatan' => 'required',
            'bulanpanen' => 'nullable|string',
            'umurpanen' => 'nullable|integer',
            'tanggalzpk' => 'nullable|date',
            'lists.*.nourut' => 'required',
            'lists.*.jumlahbatang' => 'required',
            'lists.*.bat_primer' => 'required',
            'lists.*.bat_sekunder' => 'required',
            'lists.*.bat_tersier' => 'required',
            'lists.*.bat_kuarter' => 'required',
            'lists.*.pan_gap' => 'required',
            'lists.*.ph_tanah' => 'required|numeric',
            'lists.*.ktk_gulma' => 'required',
            'lists.*.t_primer' => 'required',
            'lists.*.t_sekunder' => 'required',
            'lists.*.t_tersier' => 'required',
            'lists.*.t_kuarter' => 'required',
            'lists.*.d_primer' => 'required',
            'lists.*.d_sekunder' => 'required',
            'lists.*.d_tersier' => 'required',
            'lists.*.d_kuarter' => 'required',
            'lists.*.berat_primer' => 'nullable',
            'lists.*.berat_sekunder' => 'nullable',
            'lists.*.berat_tersier' => 'nullable',
            'lists.*.berat_kuarter' => 'nullable',
            'lists.*.brix_primer' => 'nullable',
            'lists.*.brix_sekunder' => 'nullable',
            'lists.*.brix_tersier' => 'nullable',
            'lists.*.brix_kuarter' => 'nullable',
        ];
    }

    /**
     * Hitung per_gap berdasarkan nilai pkp (jarak tanam):
     * pkp 135 → dibagi 1000
     * pkp 150 → dibagi 1000
     * pkp 180 → dibagi 2000
     * default  → dibagi 1000
     */
    protected function calcPerGap(float $panGap, ?float $pkp): float
    {
        $divisor = match ((int) $pkp) {
            180 => 2000,
            default => 1000, // covers 135, 150, dan nilai lainnya
        };
        return $panGap / $divisor;
    }

    public function index(Request $request)
    {
        $title = "Daftar Agronomi";
        $search = $request->input('search', '');

        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        if ($request->isMethod('post')) {
            $request->validate(['perPage' => 'required|integer|min:1']);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        $agronomi = DB::table('agrohdr')
            ->join('company', 'agrohdr.companycode', '=', 'company.companycode')
            ->where('agrohdr.companycode', '=', session('companycode'))
            ->where('agrohdr.closingperiode', '=', 'F')
            ->when($startDate, fn($q) => $q->whereDate('agrohdr.tanggalpengamatan', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('agrohdr.tanggalpengamatan', '<=', $endDate));

        if (!empty($search)) {
            $agronomi->where(
                fn($q) => $q
                    ->where('agrohdr.nosample', 'like', "%$search%")
                    ->orWhere('agrohdr.varietas', 'like', "%$search%")
                    ->orWhere('agrohdr.plot', 'like', "%$search%")
                    ->orWhere('agrohdr.kat', 'like', "%$search%")
            );
        }

        $agronomi = $agronomi
            ->select('agrohdr.*', 'company.name as nama_comp')
            ->orderBy('agrohdr.createdat', 'desc')
            ->paginate($perPage);

        foreach ($agronomi as $index => $item) {
            $item->umur_tanam = Carbon::parse($item->tanggaltanam)->diffInMonths(Carbon::now());
            $item->no = ($agronomi->currentPage() - 1) * $agronomi->perPage() + $index + 1;
        }

        return view(
            'transaction.agronomi.index',
            compact('agronomi', 'perPage', 'startDate', 'endDate', 'title', 'search')
        );
    }

    public function handle(Request $request)
    {
        if ($request->has('filter') || $request->has('perPage')) {
            return $this->index($request);
        }
        return $this->store($request);
    }

    public function create()
    {
        $title = "Create Data";
        $method = 'POST';
        $url = route('transaction.agronomi.handle');
        $buttonSubmit = 'Create';
        return view('transaction.agronomi.form', compact('buttonSubmit', 'title', 'method', 'url'));
    }

    public function getBlokbyField(Request $request)
    {
        $plot = $request->input('plot');
        $blok = DB::table('masterlist')
            ->where('plot', $plot)
            ->where('companycode', session('companycode'))
            ->where('isactive', 1)
            ->first();

        if ($blok) {
            return response()->json(['blok' => $blok->blok]);
        }
        return response()->json(['message' => 'Data not found'], 404);
    }

    public function getVarietasandKategori(Request $request)
    {
        $plot = $request->input('plot');
        $batch = DB::table('batch')
            ->where('plot', $plot)
            ->where('companycode', session('companycode'))
            ->where('isactive', 1)
            ->first();

        if ($batch) {
            return response()->json([
                'varietas' => $batch->kodevarietas,
                'kat' => $batch->lifecyclestatus,
                'tanggaltanam' => $batch->tanggalulangtahun,
                'pkp' => $batch->pkp,          // ← tambahan
            ]);
        }
        return response()->json(['message' => 'Data not found'], 404);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->requestValidated());

        $pkp = isset($validated['pkp']) ? (float) $validated['pkp'] : null;

        $existsInHeader = DB::table('agrohdr')
            ->where('nosample', $request->nosample)
            ->where('companycode', $request->companycode)
            ->where('tanggalpengamatan', $request->tanggalpengamatan)
            ->exists();

        $existsInLists = DB::table('agrolst')
            ->where('nosample', $request->nosample)
            ->where('companycode', $request->companycode)
            ->where('tanggalpengamatan', $request->tanggalpengamatan)
            ->exists();

        if ($existsInHeader || $existsInLists) {
            return back()->with([
                'success' => 'Data sudah ada di salah satu tabel, silahkan coba dengan data yang berbeda.',
            ])->withInput();
        }

        DB::beginTransaction();
        try {
            DB::table('agrohdr')->insert([
                'nosample' => $validated['nosample'],
                'companycode' => $validated['companycode'],
                'blok' => $validated['blok'],
                'plot' => $validated['plot'],
                'varietas' => $validated['varietas'],
                'kat' => $validated['kat'],
                'pkp' => $pkp,                          // ← tambahan
                'tanggaltanam' => $validated['tanggaltanam'],
                'tanggalpengamatan' => $validated['tanggalpengamatan'],
                'bulanpanen' => $validated['bulanpanen'] ?? null,
                'umurpanen' => $validated['umurpanen'] ?? null,
                'tanggalzpk' => $validated['tanggalzpk'] ?? null,
                'inputby' => Auth::user()->userid,
                'createdat' => now(),
                'updatedat' => now(),
            ]);

            $totalPerGerminasi = 0;
            $totalPerGulma = 0;
            $count = count($validated['lists']);

            foreach ($validated['lists'] as $list) {
                $per_gap = $this->calcPerGap((float) $list['pan_gap'], $pkp);
                $per_germinasi = 1 - $per_gap;
                $per_gulma = $list['ktk_gulma'] ? $list['ktk_gulma'] / 16 : 0;

                DB::table('agrolst')->insert([
                    'nosample' => $validated['nosample'],
                    'companycode' => $validated['companycode'],
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'tanggalpengamatan' => $validated['tanggalpengamatan'],
                    'kat' => $validated['kat'],
                    'nourut' => $list['nourut'],
                    'jumlahbatang' => $list['jumlahbatang'],
                    'bat_primer' => $list['bat_primer'],
                    'bat_sekunder' => $list['bat_sekunder'],
                    'bat_tersier' => $list['bat_tersier'],
                    'bat_kuarter' => $list['bat_kuarter'],
                    'pan_gap' => $list['pan_gap'],
                    'per_gap' => $per_gap,
                    'per_germinasi' => $per_germinasi,
                    'ph_tanah' => $list['ph_tanah'],
                    'populasi' => round($list['jumlahbatang'] / 10),
                    'ktk_gulma' => $list['ktk_gulma'],
                    'per_gulma' => $per_gulma,
                    't_primer' => $list['t_primer'],
                    't_sekunder' => $list['t_sekunder'],
                    't_tersier' => $list['t_tersier'],
                    't_kuarter' => $list['t_kuarter'],
                    'd_primer' => $list['d_primer'],
                    'd_sekunder' => $list['d_sekunder'],
                    'd_tersier' => $list['d_tersier'],
                    'd_kuarter' => $list['d_kuarter'],
                    'berat_primer' => $list['berat_primer'] ?? 0,
                    'berat_sekunder' => $list['berat_sekunder'] ?? 0,
                    'berat_tersier' => $list['berat_tersier'] ?? 0,
                    'berat_kuarter' => $list['berat_kuarter'] ?? 0,
                    'brix_primer' => $list['brix_primer'] ?? 0,
                    'brix_sekunder' => $list['brix_sekunder'] ?? 0,
                    'brix_tersier' => $list['brix_tersier'] ?? 0,
                    'brix_kuarter' => $list['brix_kuarter'] ?? 0,
                    'inputby' => Auth::user()->userid,
                    'createdat' => now(),
                    'updatedat' => now(),
                ]);

                $totalPerGerminasi += $per_germinasi;
                $totalPerGulma += $per_gulma;
            }

            $avgPerGerminasi = $totalPerGerminasi / $count;
            $avgPerGulma = $totalPerGulma / $count;
            $umurTanam = Carbon::parse($validated['tanggaltanam'])->diffInMonths(Carbon::now());

            if ($avgPerGerminasi < 0.9 && $umurTanam == 1.0 || $avgPerGulma > 0.25) {
                Notification::createForAgronomi([
                    'plot' => $validated['plot'],
                    'companycode' => $validated['companycode'],
                    'condition' => [
                        'germinasi' => $avgPerGerminasi,
                        'gulma' => $avgPerGulma,
                        'umur' => $umurTanam,
                    ],
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Data created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('transaction.agronomi.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function show($nosample, $companycode, $tanggalpengamatan)
    {
        $companyCode = session('companycode') ?? $companycode;

        $agronomi = DB::table('agrohdr as h')
            ->where([
                ['h.companycode', '=', $companyCode],
                ['h.nosample', '=', $nosample],
                ['h.tanggalpengamatan', '=', $tanggalpengamatan],
            ])
            ->first();

        if (!$agronomi) {
            abort(404, 'Agronomi header not found');
        }

        $agronomiLists = DB::table('agrolst as l')
            ->join('agrohdr as h', function ($join) {
                $join->on('l.nosample', '=', 'h.nosample')
                    ->on('l.companycode', '=', 'h.companycode')
                    ->on('l.tanggalpengamatan', '=', 'h.tanggalpengamatan');
            })
            ->leftJoin('company as c', 'h.companycode', '=', 'c.companycode')
            ->leftJoin('blok as b', function ($join) {
                $join->on('h.blok', '=', 'b.blok')
                    ->on('h.companycode', '=', 'b.companycode');
            })
            ->leftJoin('batch as bt', function ($join) {
                $join->on('h.plot', '=', 'bt.plot')
                    ->on('h.companycode', '=', 'bt.companycode');
            })
            ->where([
                ['l.nosample', '=', $nosample],
                ['l.companycode', '=', $companyCode],
                ['l.tanggalpengamatan', '=', $tanggalpengamatan],
                ['bt.isactive', '=', 1],
            ])
            ->orderBy('l.nourut')
            ->select([
                'l.*',
                'h.varietas',
                'h.kat',
                'h.tanggaltanam',
                'h.pkp as jaraktanam',
                'c.name as compName',
                'b.blok as blokName',
                'bt.plot as plotName',
                'bt.batcharea as luasarea',
            ])
            ->get();

        $now = now();

        $agronomiLists->transform(function ($item, $i) use ($now) {
            $item->no = $i + 1;
            $item->umur_tanam = round(Carbon::parse($item->tanggaltanam)->diffInMonths($now));
            return $item;
        });

        return response()->json($agronomiLists);
    }

    public function edit($nosample, $companycode, $tanggalpengamatan)
    {
        $title = 'Edit Data';
        $header = DB::table('agrohdr')
            ->where('nosample', $nosample)
            ->where('companycode', $companycode)
            ->where('tanggalpengamatan', $tanggalpengamatan)
            ->first();

        $lists = DB::table('agrolst')
            ->where('nosample', $nosample)
            ->where('companycode', $companycode)
            ->where('tanggalpengamatan', $tanggalpengamatan)
            ->get();

        $list = $lists->first();
        $header->lists = $lists;

        $company = DB::table('company')->get();
        $mapping = DB::table('mapping')->get();
        $method = 'PUT';
        $buttonSubmit = 'Update';
        $url = route('transaction.agronomi.update', compact('nosample', 'companycode', 'tanggalpengamatan'));

        if ($header->status === "Posted") {
            return redirect()->route('transaction.agronomi.index')
                ->with('success', 'Data telah di posting, tidak dapat mengakses edit.');
        }

        return view(
            'transaction.agronomi.form',
            compact('buttonSubmit', 'header', 'list', 'company', 'mapping', 'title', 'method', 'url')
        );
    }

    public function update(Request $request, $nosample, $companycode, $tanggalpengamatan)
    {
        $validated = $request->validate($this->requestValidated());

        $pkp = isset($validated['pkp']) ? (float) $validated['pkp'] : null;

        DB::beginTransaction();
        try {
            DB::table('agrohdr')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)
                ->update([
                    'nosample' => $validated['nosample'],
                    'companycode' => $validated['companycode'],
                    'blok' => $validated['blok'],
                    'plot' => $validated['plot'],
                    'varietas' => $validated['varietas'],
                    'kat' => $validated['kat'],
                    'pkp' => $pkp,
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'tanggalpengamatan' => $validated['tanggalpengamatan'],
                    'bulanpanen' => $validated['bulanpanen'] ?? null,
                    'umurpanen' => $validated['umurpanen'] ?? null,
                    'tanggalzpk' => $validated['tanggalzpk'] ?? null,
                    'updatedat' => now(),
                ]);

            $saved = DB::table('agrolst')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)
                ->first();

            $createdAt = $saved->createdat;
            $userInput = $saved->inputby;

            DB::table('agrolst')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)
                ->delete();

            foreach ($validated['lists'] as $list) {
                $per_gap = $this->calcPerGap((float) $list['pan_gap'], $pkp);
                $per_germinasi = 1 - $per_gap;

                DB::table('agrolst')->insert([
                    'nosample' => $validated['nosample'],
                    'companycode' => $validated['companycode'],
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'tanggalpengamatan' => $validated['tanggalpengamatan'],
                    'kat' => $validated['kat'],
                    'nourut' => $list['nourut'],
                    'jumlahbatang' => $list['jumlahbatang'],
                    'bat_primer' => $list['bat_primer'],
                    'bat_sekunder' => $list['bat_sekunder'],
                    'bat_tersier' => $list['bat_tersier'],
                    'bat_kuarter' => $list['bat_kuarter'],
                    'pan_gap' => $list['pan_gap'],
                    'per_gap' => $per_gap,
                    'per_germinasi' => $per_germinasi,
                    'ph_tanah' => $list['ph_tanah'],
                    'populasi' => $list['jumlahbatang'] / 10,
                    'ktk_gulma' => $list['ktk_gulma'],
                    'per_gulma' => $list['ktk_gulma'] ? $list['ktk_gulma'] / 16 : 0,
                    't_primer' => $list['t_primer'],
                    't_sekunder' => $list['t_sekunder'],
                    't_tersier' => $list['t_tersier'],
                    't_kuarter' => $list['t_kuarter'],
                    'd_primer' => $list['d_primer'],
                    'd_sekunder' => $list['d_sekunder'],
                    'd_tersier' => $list['d_tersier'],
                    'd_kuarter' => $list['d_kuarter'],
                    'berat_primer' => $list['berat_primer'] ?? 0,
                    'berat_sekunder' => $list['berat_sekunder'] ?? 0,
                    'berat_tersier' => $list['berat_tersier'] ?? 0,
                    'berat_kuarter' => $list['berat_kuarter'] ?? 0,
                    'brix_primer' => $list['brix_primer'] ?? 0,
                    'brix_sekunder' => $list['brix_sekunder'] ?? 0,
                    'brix_tersier' => $list['brix_tersier'] ?? 0,
                    'brix_kuarter' => $list['brix_kuarter'] ?? 0,
                    'inputby' => $userInput,
                    'createdat' => $createdAt,
                    'updatedat' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('transaction.agronomi.index')
                ->with('success', 'Data updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('transaction.agronomi.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function destroy($nosample, $companycode, $tanggalpengamatan)
    {
        DB::transaction(function () use ($nosample, $companycode, $tanggalpengamatan) {
            DB::table('agrohdr')
                ->where('nosample', $nosample)->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)->delete();
            DB::table('agrolst')
                ->where('nosample', $nosample)->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)->delete();
        });
        return redirect()->route('transaction.agronomi.index')
            ->with('success', 'Data deleted successfully.');
    }

    public function excel(Request $request)
    {
        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $search = $request->input('search');

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'search' => 'nullable|string',
        ]);

        $query = DB::table('agrolst')
            ->leftJoin('agrohdr', fn($j) => $j
                ->on('agrolst.nosample', '=', 'agrohdr.nosample')
                ->whereColumn('agrolst.companycode', '=', 'agrohdr.companycode')
                ->whereColumn('agrolst.tanggalpengamatan', '=', 'agrohdr.tanggalpengamatan'))
            ->leftJoin('company', 'agrohdr.companycode', '=', 'company.companycode')
            ->leftJoin('blok', fn($j) => $j
                ->on('agrohdr.blok', '=', 'blok.blok')
                ->whereColumn('agrohdr.companycode', '=', 'blok.companycode'))
            ->leftJoin('batch', fn($j) => $j
                ->on('agrohdr.plot', '=', 'batch.plot')
                ->whereColumn('agrohdr.companycode', '=', 'batch.companycode'))
            ->where('agrolst.companycode', session('companycode'))
            ->where('agrohdr.companycode', session('companycode'))
            ->where('agrohdr.status', 'Posted')
            ->where('agrolst.status', 'Posted')
            ->select(
                'agrolst.*',
                'agrohdr.varietas',
                'agrohdr.kat',
                'agrohdr.pkp as jaraktanam',
                'agrohdr.tanggaltanam',
                'agrohdr.bulanpanen',
                'agrohdr.umurpanen',
                'agrohdr.tanggalzpk',
                'company.name as compName',
                'blok.blok as blokName',
                'batch.plot as plotName',
                'batch.batcharea as luasarea',
            )
            ->orderBy('agrohdr.tanggalpengamatan', 'desc');

        if ($startDate)
            $query->whereDate('agrohdr.tanggalpengamatan', '>=', $startDate);
        if ($endDate)
            $query->whereDate('agrohdr.tanggalpengamatan', '<=', $endDate);
        if ($search) {
            $query->where(
                fn($q) => $q
                    ->where('agrolst.nosample', 'like', "%$search%")
                    ->orWhere('agrohdr.varietas', 'like', "%$search%")
                    ->orWhere('agrohdr.kat', 'like', "%$search%")
            );
        }

        $filename = $startDate && $endDate
            ? "AgronomiReport_{$startDate}_sd_{$endDate}.xlsx"
            : "AgronomiReport.xlsx";

        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir))
            mkdir($tempDir, 0755, true);
        $tempFile = "$tempDir/$filename";

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->setTempFolder($tempDir);
        $writer->openToFile($tempFile);

        $headerStyle = (new StyleBuilder())->setFontBold()->build();

        $headerCells = array_map(
            fn($h) => WriterEntityFactory::createCell($h),
            [
                'No. Sample',
                'Kebun',
                'Blok',
                'Plot',
                'Luas',
                'Varietas',
                'Kategori',
                'Tanggal Tanam',
                'Umur Tanam',
                'Jarak Tanam',
                'Tanggal Pengamatan',
                'Bulan Pengamatan',
                'Bulan Panen',
                'Umur Panen',
                'Tanggal ZPK',
                'No. Urut',
                'Jumlah Batang',
                'Jumlah Batang Primer',
                'Jumlah Batang Sekunder',
                'Jumlah Batang Tersier',
                'Panjang GAP',
                '%GAP',
                '%Germinasi',
                'pH Tanah',
                'Populasi',
                'Kotak Gulma',
                '%Penutupan Gulma',
                'Tinggi Primer',
                'Tinggi Sekunder',
                'Tinggi Tersier',
                'Tinggi Kuarter',
                'Diameter Primer',
                'Diameter Sekunder',
                'Diameter Tersier',
                'Diameter Kuarter',
                'Berat Batang Primer',
                'Berat Batang Sekunder',
                'Berat Batang Tersier',
                'Berat Batang Kuarter',
                'Brix Batang Primer',
                'Brix Batang Sekunder',
                'Brix Batang Tersier',
                'Brix Batang Kuarter',
            ]
        );
        $writer->addRow(WriterEntityFactory::createRow($headerCells, $headerStyle));

        $now = Carbon::now();
        $query->chunk(1000, function ($chunk) use ($writer, $now) {
            $rows = [];
            foreach ($chunk as $list) {
                $tglTanam = Carbon::parse($list->tanggaltanam);
                $umurTanam = $tglTanam->diffInMonths($now);
                $bulanPengamatan = Carbon::parse($list->tanggalpengamatan)->format('F');
                $dec2 = (new StyleBuilder())->setFormat('0.00')->build();

                $rows[] = WriterEntityFactory::createRow([
                    WriterEntityFactory::createCell($list->nosample),
                    WriterEntityFactory::createCell($list->compName),
                    WriterEntityFactory::createCell($list->blokName),
                    WriterEntityFactory::createCell($list->plotName),
                    WriterEntityFactory::createCell(round((float) $list->luasarea, 10)),
                    WriterEntityFactory::createCell($list->varietas),
                    WriterEntityFactory::createCell($list->kat),
                    WriterEntityFactory::createCell($tglTanam->format('Y-m-d')),
                    WriterEntityFactory::createCell(round($umurTanam) . ' Bulan'),
                    WriterEntityFactory::createCell(round((float) $list->jaraktanam, 10)),
                    WriterEntityFactory::createCell($list->tanggalpengamatan),
                    WriterEntityFactory::createCell($bulanPengamatan),
                    WriterEntityFactory::createCell($list->bulanpanen),
                    WriterEntityFactory::createCell($list->umurpanen),
                    WriterEntityFactory::createCell($list->tanggalzpk),
                    WriterEntityFactory::createCell($list->nourut),
                    WriterEntityFactory::createCell($list->jumlahbatang),
                    WriterEntityFactory::createCell($list->bat_primer),
                    WriterEntityFactory::createCell($list->bat_sekunder),
                    WriterEntityFactory::createCell($list->bat_tersier),
                    WriterEntityFactory::createCell($list->bat_kuarter),
                    WriterEntityFactory::createCell($list->pan_gap),
                    WriterEntityFactory::createCell(round((float) $list->per_gap, 10), $dec2),
                    WriterEntityFactory::createCell(round((float) $list->per_germinasi, 10), $dec2),
                    WriterEntityFactory::createCell(round((float) $list->ph_tanah, 10)),
                    WriterEntityFactory::createCell(round((float) $list->populasi, 10)),
                    WriterEntityFactory::createCell($list->ktk_gulma),
                    WriterEntityFactory::createCell(round((float) $list->per_gulma, 10), $dec2),
                    WriterEntityFactory::createCell($list->t_primer),
                    WriterEntityFactory::createCell($list->t_sekunder),
                    WriterEntityFactory::createCell($list->t_tersier),
                    WriterEntityFactory::createCell($list->t_kuarter),
                    WriterEntityFactory::createCell(round((float) $list->d_primer, 1), (new StyleBuilder())->setFormat('0.0')->build()),
                    WriterEntityFactory::createCell(round((float) $list->d_sekunder, 2), $dec2),
                    WriterEntityFactory::createCell(round((float) $list->d_tersier, 3), (new StyleBuilder())->setFormat('0.000')->build()),
                    WriterEntityFactory::createCell(round((float) $list->d_kuarter, 4), (new StyleBuilder())->setFormat('0.0000')->build()),
                    WriterEntityFactory::createCell($list->berat_primer),
                    WriterEntityFactory::createCell($list->berat_sekunder),
                    WriterEntityFactory::createCell($list->berat_tersier),
                    WriterEntityFactory::createCell($list->berat_kuarter),
                    WriterEntityFactory::createCell(round((float) $list->brix_primer, 2), $dec2),
                    WriterEntityFactory::createCell(round((float) $list->brix_sekunder, 2), $dec2),
                    WriterEntityFactory::createCell(round((float) $list->brix_tersier, 2), $dec2),
                    WriterEntityFactory::createCell(round((float) $list->brix_kuarter, 2), $dec2),
                ]);
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