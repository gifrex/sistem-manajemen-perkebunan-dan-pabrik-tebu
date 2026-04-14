<?php

namespace App\Http\Controllers\Transaction;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use App\Models\usematerialhdr;
use App\Models\usemateriallst;
use App\Models\MasterData\HerbisidaDosage;
use App\Models\MasterData\Herbisida;
use App\Models\Transaction\RkhHdr;
use App\Models\Transaction\RkhLst;
use App\Models\piashdr;
use App\Models\piaslst;
use Carbon\Carbon;

class PiasController extends Controller
{
 
    public function __construct()
    {
        View::share([
            'navbar' => 'Input',
            'nav' => 'gudang',
            'routeName' => route('transaction.gudang.index'),
        ]);
    }
 
    public function home(Request $request)
{
    $perPage = (int) $request->input('perPage', 15);
    $startDate = $request->input('start_date', now()->subMonths(2)->format('Y-m-d'));
    $endDate = $request->input('end_date', now()->format('Y-m-d'));
    $search = $request->input('search');

    Log::info('PIAS HOME DEBUG PARAMS', [
        'user' => optional(auth()->user())->userid ?? null,
        'companycode' => session('companycode'),
        'perPage' => $perPage,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'search' => $search,
        'query_string' => $request->query(),
    ]);

    try {
        $rkhhdr = new Rkhhdr;

        $selected = $rkhhdr::query()
            ->leftJoin('user as u', 'u.userid', '=', 'rkhhdr.mandorid')
            ->leftJoin('piashdr as ph', function ($join) {
                $join->on('ph.rkhno', '=', 'rkhhdr.rkhno')
                     ->on('ph.companycode', '=', 'rkhhdr.companycode');
            })
            ->where('rkhhdr.companycode', session('companycode'))
            ->where('rkhhdr.approvalstatus', 1)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('rkhlst')
                      ->whereColumn('rkhlst.rkhno', 'rkhhdr.rkhno')
                      ->whereColumn('rkhlst.companycode', 'rkhhdr.companycode')
                      ->where('rkhlst.activitycode', '5.2.1');
            })
            ->whereDate('rkhhdr.rkhdate', '>=', $startDate)
            ->whereDate('rkhhdr.rkhdate', '<=', $endDate);

        if ($search) {
            $selected->where(function($query) use ($search) {
                $query->where('rkhhdr.rkhno', 'like', "%{$search}%")
                      ->orWhere('u.name', 'like', "%{$search}%");
            });
        }

        $selected->select([
                'rkhhdr.*',
                DB::raw('u.name as mandor_name'),
            ])
            ->selectRaw('CASE WHEN ph.rkhno IS NULL THEN 0 ELSE 1 END as is_generated')
            ->orderByDesc('rkhhdr.rkhdate');

        // LOG SQL sebelum dieksekusi
        Log::info('PIAS HOME SQL', [
            'sql' => $selected->toSql(),
            'bindings' => $selected->getBindings(),
        ]);

        $data = $selected->paginate($perPage)->appends($request->query());

        Log::info('PIAS HOME RESULT', [
            'total' => $data->total(),
            'count' => $data->count(),
        ]);

        return view('transaction.pias.home', [
            'title'     => 'Pias',
            'data'      => $data,
            'perPage'   => $perPage,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'search'    => $search,
        ]);
    } catch (\Throwable $e) {
        Log::error('PIAS HOME ERROR', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        abort(500, 'PIAS HOME ERROR (cek laravel.log)');
    }
}

    public function detail(Request $request)
    {   
        $rkhhdr = new Rkhhdr;
        $rkhlst = new RkhLst;
        $piashdr = new piashdr;
        $piaslst = new piaslst;

        $hdr = $piashdr->where('rkhno', $request->input('rkhno'))
                        ->where('companycode', session('companycode'))
                        ->first();
        $lst = $piaslst->where('rkhno', $request->input('rkhno'))
                        ->where('companycode', session('companycode'))
                        ->get();

        $data = $rkhhdr
        ->leftJoin('user as u', 'u.userid', '=', 'rkhhdr.mandorid')
        ->leftJoin('lkhhdr', function($join) {
            $join->on('lkhhdr.rkhno', '=', 'rkhhdr.rkhno')
                 ->on('lkhhdr.companycode', '=', 'rkhhdr.companycode');
        })
        ->leftJoin('lkhdetailplot', function($join) {
            $join->on('lkhdetailplot.lkhno', '=', 'lkhhdr.lkhno')
                 ->on('lkhdetailplot.companycode', '=', 'lkhhdr.companycode');
        })
        ->leftJoin('masterlist', function($join) {
            $join->on('masterlist.companycode', '=', 'rkhhdr.companycode')
                 ->on('masterlist.blok', '=', 'lkhdetailplot.blok')
                 ->on('masterlist.plot', '=', 'lkhdetailplot.plot');
        })
        // TAMBAHKAN JOIN KE BATCH
        ->leftJoin('batch', function($join) {
            $join->on('batch.companycode', '=', 'masterlist.companycode')
                 ->on('batch.plot', '=', 'masterlist.plot')
                 ->on('batch.batchno', '=', 'masterlist.activebatchno');
        })
        ->where('rkhhdr.rkhno', $request->input('rkhno'))
        ->where('rkhhdr.companycode', session('companycode'))
        ->where('rkhhdr.approvalstatus', 1)
        ->select(
            'rkhhdr.*', 
            'u.name as mandor_name',
            'lkhhdr.lkhno',
            'lkhdetailplot.blok',
            'lkhdetailplot.plot',
            'lkhdetailplot.luasrkh',
            // GANTI DARI masterlist KE batch
            'batch.tanggalpanen as tanggalulangtahun',
            'batch.kodevarietas',
            'batch.lifecyclestatus as kodestatus',
            'batch.batchno'
        )
        ->get();
            
        return view('transaction.pias.detail')->with([
            'title' => 'Pias',
            'data'  => $data,
            'hdr'   => $hdr,
            'lst'   => $lst
        ]);
    }

    public function submit(Request $request)
{   
    // 1) Validasi dasar + baris input
    $data = $request->validate([
        'rkhno'                 => 'required|string|max:20',
        'inputTJ'               => 'required|numeric|min:0',
        'inputTC'               => 'required|numeric|min:0',
        'inputTV'               => 'required|numeric|min:0',
        'rows'                  => 'required|array|min:1',
        'rows.*.blok'           => 'required|string|max:50',
        'rows.*.plot'           => 'required|string|max:50',
        'rows.*.lkhno'          => 'required|string|max:20',
        'rows.*.tj'             => 'nullable|numeric|min:0',
        'rows.*.tc'             => 'nullable|numeric|min:0',
        'rows.*.tv'             => 'nullable|numeric|min:0',
        'dosage'                => 'required|integer|min:10|max:25',
        'totalNeedTJ'           => 'required|integer|min:0',  
        'totalNeedTC'           => 'required|integer|min:0',
        'totalNeedTV'           => 'required|integer|min:0'  
    ], [
        'rows.required'         => 'Detail baris wajib ada.',
    ]); 

    $rkhno   = $data['rkhno'];
    $stokTJ  = (float) $data['inputTJ'];
    $stokTC  = (float) $data['inputTC'];
    $stokTV  = (float) $data['inputTV'];
    $rowsIn  = $data['rows'];
    $dosage   = (int) $data['dosage'];

    // 2) Hitung total yang diketik user
    $sumTJ = 0; $sumTC = 0; $sumTV = 0;

    foreach ($rowsIn as $r) {
        $sumTJ += (float) ($r['tj'] ?? 0);
        $sumTC += (float) ($r['tc'] ?? 0);
        $sumTV += (float) ($r['tv'] ?? 0);
    }
    
    if ($sumTJ > $stokTJ) {
        return back()
            ->withErrors(['rows' => "Total TJ yang diinput ($sumTJ) melebihi stok TJ ($stokTJ)."])
            ->withInput();
    }
    if ($sumTC > $stokTC) {
        return back()
            ->withErrors(['rows' => "Total TC yang diinput ($sumTC) melebihi stok TC ($stokTC)."])
            ->withInput();
    }
    if ($sumTV > $stokTV) {
    return back()
        ->withErrors(['rows' => "Total TV yang diinput ($sumTV) melebihi stok TV ($stokTV)."])
        ->withInput();
    }

    // 3) Validasi kombinasi lkhno|blok|plot milik RKH ini
    $validKeys = DB::table('rkhhdr')
        ->leftJoin('lkhhdr', function($join) {
            $join->on('lkhhdr.rkhno', '=', 'rkhhdr.rkhno')
                 ->on('lkhhdr.companycode', '=', 'rkhhdr.companycode');
        })
        ->leftJoin('lkhdetailplot', function($join) {
            $join->on('lkhdetailplot.lkhno', '=', 'lkhhdr.lkhno')
                 ->on('lkhdetailplot.companycode', '=', 'lkhhdr.companycode');
        })
        ->where('rkhhdr.rkhno', $rkhno)
        ->whereNotNull('lkhdetailplot.blok')
        ->whereNotNull('lkhdetailplot.plot')
        ->whereNotNull('lkhhdr.lkhno')
        ->select(DB::raw("CONCAT(lkhhdr.lkhno,'|',lkhdetailplot.blok,'|',lkhdetailplot.plot) as plot_key"))
        ->pluck('plot_key')
        ->toArray();
    
    $validMap = array_flip($validKeys);

    // Ambil companycode
    $companycode = DB::table('rkhhdr')->where('rkhno', $rkhno)->where('companycode', session('companycode'))->value('companycode');

    
    return DB::transaction(function () use ($rkhno, $companycode, $rowsIn, $validMap, $stokTJ, $stokTC, $stokTV, $sumTJ, $sumTC, $sumTV, $dosage, $data) {

        // Hapus detail lama
        $q = DB::table('piaslst')->where('rkhno', $rkhno);
        if ($companycode) $q->where('companycode', $companycode);
        $q->delete();

        // Siapkan rows untuk insert
        $now = now();
        $detail = [];
        
        foreach ($rowsIn as $r) {
            $blok  = trim((string)($r['blok'] ?? ''));
            $plot  = trim((string)($r['plot'] ?? ''));
            $lkhno = trim((string)($r['lkhno'] ?? ''));

            // Validasi: cek kombinasi lkhno|blok|plot
            $key = "{$lkhno}|{$blok}|{$plot}";
            if (!empty($validMap) && !isset($validMap[$key])) {
                \Log::warning("Invalid lkhno|blok|plot submitted", [
                    'rkhno' => $rkhno,
                    'lkhno' => $lkhno,
                    'blok' => $blok,
                    'plot' => $plot,
                    'key' => $key
                ]);
                continue;
            }

            $detail[] = array_filter([
                'companycode' => $companycode ?: null,
                'rkhno'       => $rkhno,
                'lkhno'       => $lkhno,
                'blok'        => $blok,
                'plot'        => $plot,
                'tj'          => (int) ($r['tj'] ?? 0),
                'tc'          => (int) ($r['tc'] ?? 0),
                'tv'          => (int) ($r['tv'] ?? 0),
            ], fn($v) => $v !== null);
        }

        if (!empty($detail)) {
            DB::table('piaslst')->insert($detail);
        }

        //hitung status 
            $totalNeedTJ = (int) $data['totalNeedTJ'];
            $totalNeedTC = (int) $data['totalNeedTC'];
            $totalNeedTV = (int) $data['totalNeedTV'];
        //
        //

        // Upsert header
        $header = [
            'tj'          => $stokTJ,
            'tc'          => $stokTC,
            'tv'          => $stokTV,
            'sisatj'      => (int) floor($stokTJ - $sumTJ),
            'sisatc'      => (int) floor($stokTC - $sumTC),
            'sisatv'      => (int) floor($stokTV - $sumTV),
            'dosage'      => $dosage,
            'totalneedtj' => $totalNeedTJ,
            'totalneedtc' => $totalNeedTC,
            'totalneedtv' => $totalNeedTV,
            'statustj'    => $sumTJ >= $totalNeedTJ ? 1 : 0,
            'statustc'    => $sumTC >= $totalNeedTC ? 1 : 0,
            'statustv'    => $sumTV >= $totalNeedTV ? 1 : 0
        ];

        $keys = array_filter([
            'companycode' => $companycode ?: null,
            'rkhno'       => $rkhno,
        ], fn($v) => $v !== null);

        $exists = DB::table('piashdr')->where($keys)->exists();
        if (!$exists) {
            DB::table('piashdr')->insert($keys + $header + [
                'generateddate' => $now,
                'inputby'       => auth()->user()->name ?? 'System',
            ]);
        } else {
            DB::table('piashdr')->where($keys)->update($header + [
                'updateddate' => $now,
                'updateby'    => auth()->user()->name ?? 'System',
            ]);
        }

        return back()->with('success', 'Data PIAS berhasil disimpan.');
    });
}
    

    public function report(Request $request)
    {
        $title     = 'Pias - Report';
        $search    = $request->input('search');
        $startDate = $request->input('start_date', now()->subDays(7)->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->format('Y-m-d'));
        $company   = session('companycode');

        $rows = DB::table('piaslst as pl')
            ->join('piashdr as ph', function ($j) {
                $j->on('ph.rkhno', '=', 'pl.rkhno')
                  ->on('ph.companycode', '=', 'pl.companycode');
            })
            ->join('rkhhdr as r', function ($j) {
                $j->on('r.rkhno', '=', 'pl.rkhno')
                  ->on('r.companycode', '=', 'pl.companycode');
            })
            ->join('lkhhdr as lh', function ($j) {
                $j->on('lh.lkhno', '=', 'pl.lkhno')
                  ->on('lh.companycode', '=', 'pl.companycode');
            })
            ->join('lkhdetailplot as ldp', function ($j) {
                $j->on('ldp.lkhno', '=', 'pl.lkhno')
                  ->on('ldp.companycode', '=', 'pl.companycode')
                  ->on('ldp.plot', '=', 'pl.plot')
                  ->on('ldp.blok', '=', 'pl.blok');
            })
            ->join('masterlist as ml', function ($j) {
                $j->on('ml.companycode', '=', 'pl.companycode')
                  ->on('ml.blok', '=', 'pl.blok')
                  ->on('ml.plot', '=', 'pl.plot');
            })
            ->join('batch as b', function ($j) {
                $j->on('b.companycode', '=', 'ml.companycode')
                  ->on('b.plot', '=', 'ml.plot')
                  ->on('b.batchno', '=', 'ml.activebatchno');
            })
            ->where('pl.companycode', $company)
            ->whereDate('r.rkhdate', '>=', $startDate)
            ->whereDate('r.rkhdate', '<=', $endDate)
            ->when($search, fn($q) =>
                $q->where(fn($qq) =>
                    $qq->where('pl.rkhno', 'like', "%{$search}%")
                       ->orWhere('pl.blok', 'like', "%{$search}%")
                       ->orWhere('pl.plot', 'like', "%{$search}%")
                )
            )
            ->select(
                'r.rkhdate as tgl',
                'pl.blok',
                'pl.plot',
                'ldp.luasrkh as ha',
                'b.tanggalpanen as tgl_tanam',
                'b.lifecyclestatus as kategori',
                'b.kodevarietas as varietas',
                'pl.tj',
                'pl.tc',
                'pl.tv'
            )
            ->orderBy('r.rkhdate')
            ->orderBy('pl.blok')
            ->orderBy('pl.plot')
            ->get()
            ->map(function ($row) {
                $tanam = $row->tgl_tanam ? Carbon::parse($row->tgl_tanam) : null;
                $tgl   = Carbon::parse($row->tgl);
                $bulan = $tanam ? (int) ceil(abs($tgl->diffInDays($tanam)) / 30) : '-';

                return (object) [
                    'tgl'      => $row->tgl,
                    'blok'     => $row->blok,
                    'plot'     => $row->plot,
                    'ha'       => $row->ha,
                    'tgl_tanam'=> $row->tgl_tanam,
                    'bulan'    => $bulan,
                    'kategori' => $row->kategori,
                    'varietas' => $row->varietas,
                    'tj'       => (int) ($row->tj ?? 0),
                    'tc'       => (int) ($row->tc ?? 0),
                    'tv'       => (int) ($row->tv ?? 0),
                ];
            });

        // Group per tanggal untuk rowspan
        $grouped = $rows->groupBy('tgl');

        return view('transaction.pias.report')->with([
            'title'     => $title,
            'grouped'   => $grouped,
            'search'    => $search,
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(7)->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->format('Y-m-d'));
        $search    = $request->input('search');
        $company   = session('companycode');

        $rows = DB::table('piaslst as pl')
            ->join('piashdr as ph', fn($j) => $j->on('ph.rkhno','=','pl.rkhno')->on('ph.companycode','=','pl.companycode'))
            ->join('rkhhdr as r',   fn($j) => $j->on('r.rkhno','=','pl.rkhno')->on('r.companycode','=','pl.companycode'))
            ->join('lkhhdr as lh',  fn($j) => $j->on('lh.lkhno','=','pl.lkhno')->on('lh.companycode','=','pl.companycode'))
            ->join('lkhdetailplot as ldp', fn($j) =>
                $j->on('ldp.lkhno','=','pl.lkhno')
                  ->on('ldp.companycode','=','pl.companycode')
                  ->on('ldp.plot','=','pl.plot')
                  ->on('ldp.blok','=','pl.blok')
            )
            ->join('masterlist as ml', fn($j) =>
                $j->on('ml.companycode','=','pl.companycode')
                  ->on('ml.blok','=','pl.blok')
                  ->on('ml.plot','=','pl.plot')
            )
            ->join('batch as b', fn($j) =>
                $j->on('b.companycode','=','ml.companycode')
                  ->on('b.plot','=','ml.plot')
                  ->on('b.batchno','=','ml.activebatchno')
            )
            ->where('pl.companycode', $company)
            ->whereDate('r.rkhdate', '>=', $startDate)
            ->whereDate('r.rkhdate', '<=', $endDate)
            ->when($search, fn($q) =>
                $q->where(fn($qq) =>
                    $qq->where('pl.rkhno','like',"%{$search}%")
                       ->orWhere('pl.blok','like',"%{$search}%")
                       ->orWhere('pl.plot','like',"%{$search}%")
                )
            )
            ->select('r.rkhdate as tgl','pl.blok','pl.plot','ldp.luasrkh as ha',
                     'b.tanggalpanen as tgl_tanam','b.lifecyclestatus as kategori',
                     'b.kodevarietas as varietas','pl.tj','pl.tc','pl.tv')
            ->orderBy('r.rkhdate')->orderBy('pl.blok')->orderBy('pl.plot')
            ->get();

        if ($rows->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diekspor.');
        }

        $grouped = $rows->groupBy('tgl');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pias Report');

        // ── Style helpers ──
        $center  = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
        $right   = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT,  'vertical' => Alignment::VERTICAL_CENTER]];
        $bold    = ['font' => ['bold' => true]];
        $borders = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

        $hdrStyle = fn(string $hex) => [
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $hex]],
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $lastCol = 'K';

        // ── Judul ──
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'LAPORAN PIAS HARIAN');
        $sheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($startDate)) . ' s/d ' . date('d/m/Y', strtotime($endDate)));
        $sheet->getStyle('A2')->applyFromArray($center);

        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', 'Dicetak: ' . now()->format('d/m/Y H:i'));
        $sheet->getStyle('A3')->applyFromArray($center);

        // ── Column widths ──
        $widths = ['A'=>14,'B'=>8,'C'=>10,'D'=>8,'E'=>13,'F'=>8,'G'=>10,'H'=>12,'I'=>8,'J'=>8,'K'=>8];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $row = 5;

        // ── Grand totals ──
        $grandTJ = 0; $grandTC = 0; $grandTV = 0;

        foreach ($grouped as $tgl => $items) {
            // ── Tanggal header ──
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->setCellValue("A{$row}", 'TANGGAL: ' . date('d/m/Y', strtotime($tgl)));
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($hdrStyle('3B5998'));
            $sheet->getRowDimension($row)->setRowHeight(18);
            $row++;

            // ── Column headers ──
            $headers = ['TANGGAL','BLOK','PLOT','HA','TGL TANAM','BULAN','KATEGORI','VARIETAS','TJ','TC','TV'];
            foreach ($headers as $ci => $h) {
                $sheet->setCellValue([$ci + 1, $row], $h);
            }
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($hdrStyle('374151'));
            $sheet->getRowDimension($row)->setRowHeight(24);
            $row++;

            $dayTJ = 0; $dayTC = 0; $dayTV = 0;

            foreach ($items as $item) {
                $tanam = $item->tgl_tanam ? \Carbon\Carbon::parse($item->tgl_tanam) : null;
                $t     = \Carbon\Carbon::parse($item->tgl);
                $bulan = $tanam ? (int) ceil(abs($t->diffInDays($tanam)) / 30) : '-';

                $sheet->setCellValue("A{$row}", date('d/m/Y', strtotime($item->tgl)));
                $sheet->setCellValue("B{$row}", $item->blok);
                $sheet->setCellValue("C{$row}", $item->plot);
                $sheet->setCellValue("D{$row}", (float) $item->ha);
                $sheet->setCellValue("E{$row}", $item->tgl_tanam ? date('d/m/Y', strtotime($item->tgl_tanam)) : '-');
                $sheet->setCellValue("F{$row}", $bulan);
                $sheet->setCellValue("G{$row}", $item->kategori ?? '-');
                $sheet->setCellValue("H{$row}", $item->varietas ?? '-');
                $sheet->setCellValue("I{$row}", (int) ($item->tj ?? 0));
                $sheet->setCellValue("J{$row}", (int) ($item->tc ?? 0));
                $sheet->setCellValue("K{$row}", (int) ($item->tv ?? 0));

                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($borders);
                $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($center);
                $sheet->getStyle("D{$row}")->applyFromArray($right);
                $sheet->getStyle("I{$row}:K{$row}")->applyFromArray($right);

                // Warna kolom TJ/TC/TV
                $sheet->getStyle("I{$row}")->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']]]);
                $sheet->getStyle("J{$row}")->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCFCE7']]]);
                $sheet->getStyle("K{$row}")->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF9C3']]]);

                $dayTJ += (int)($item->tj ?? 0);
                $dayTC += (int)($item->tc ?? 0);
                $dayTV += (int)($item->tv ?? 0);
                $row++;
            }

            // ── Subtotal per tanggal ──
            $sheet->mergeCells("A{$row}:H{$row}");
            $sheet->setCellValue("A{$row}", 'Subtotal ' . date('d/m/Y', strtotime($tgl)));
            $sheet->setCellValue("I{$row}", $dayTJ);
            $sheet->setCellValue("J{$row}", $dayTC);
            $sheet->setCellValue("K{$row}", $dayTV);
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray(array_merge($borders, $bold));
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($right);
            $sheet->getStyle("I{$row}:K{$row}")->applyFromArray($right);
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BFDBFE']],
            ]);
            $row++;

            $grandTJ += $dayTJ;
            $grandTC += $dayTC;
            $grandTV += $dayTV;
        }

        // ── Grand total ──
        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL');
        $sheet->setCellValue("I{$row}", $grandTJ);
        $sheet->setCellValue("J{$row}", $grandTC);
        $sheet->setCellValue("K{$row}", $grandTV);
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray(array_merge($borders, $bold, [
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '374151']],
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]));
        $sheet->getStyle("I{$row}:K{$row}")->applyFromArray($right);

        $filename = 'Pias_Report_' . $startDate . '_' . $endDate . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

/**
 * Equal-first + Group-fair (CRC32; target = sum(round(need)))
 */
private function allocateInt(array $kebutuhan, float $stok, int $seed = 0, ?array $ids = null): array
{
    $n = count($kebutuhan);
    if ($n === 0) return [];

    // Normalisasi
    for ($i=0; $i<$n; $i++) {
        if (!is_finite($kebutuhan[$i]) || $kebutuhan[$i] < 0) $kebutuhan[$i] = 0.0;
    }
    if ($ids === null || count($ids) !== $n) {
        $ids = array_map(fn($i)=> (string)$i, range(0,$n-1));
    }

    // --- target & cap sinkron dgn DB ---
    $sumNeedInt = array_sum(array_map(static fn($v) => (int) round($v), $kebutuhan));
    $target     = min((int) floor($stok), (int) $sumNeedInt);
    if ($target <= 0 || $sumNeedInt <= 0) return array_fill(0, $n, 0);

    $cap = array_map(static fn($v) => (int) round($v), $kebutuhan);

    // Kuota proporsional (prioritas sekunder)
    $totalFloat = array_sum($kebutuhan);
    $kuota = [];
    for ($i=0; $i<$n; $i++) $kuota[$i] = ($totalFloat > 0) ? ($kebutuhan[$i] / $totalFloat) * $target : 0.0;

    // 1) Baseline equal split (adil), hormati cap
    $base  = intdiv($target, $n);
    $alok  = array_fill(0, $n, 0);
    for ($i=0; $i<$n; $i++) $alok[$i] = min($base, $cap[$i]);
    $remain = $target - array_sum($alok);
    if ($remain <= 0) return $alok;

    // 2) Kelompokkan berdasarkan need dibulatkan (group fair)
    $needInt = array_map(static fn($v)=>(int)round($v), $kebutuhan);
    $groups  = []; // needInt => [idx...]
    for ($i=0; $i<$n; $i++) $groups[$needInt[$i]][] = $i;
    krsort($groups, SORT_NUMERIC); // need terbesar dahulu

    // helper: urut dalam grup pakai CRC32(id)^seed, tie: pecahan kuota desc, tie: index asc
    $orderGroup = function(array $idxs) use($ids,$seed,$kuota) {
        usort($idxs, function($a,$b) use($ids,$seed,$kuota){
            $ha = (crc32($ids[$a]) ^ $seed);
            $hb = (crc32($ids[$b]) ^ $seed);
            if ($ha === $hb) {
                $fa = $kuota[$a] - floor($kuota[$a]);
                $fb = $kuota[$b] - floor($kuota[$b]);
                if ($fa === $fb) return $a <=> $b;
                return ($fa < $fb) ? 1 : -1; // frac desc
            }
            return ($ha < $hb) ? -1 : 1;   // hash asc
        });
        return $idxs;
    };

    // 3) Bagi sisa per GRUP need (merata; selisih dalam grup ≤ 1)
    while ($remain > 0) {
        $progress = false;

        foreach ($groups as $needVal => $idxsAll) {
            if ($remain <= 0) break;

            // kandidat yang masih punya ruang
            $idxs = array_values(array_filter($idxsAll, fn($i)=> $alok[$i] < $cap[$i]));
            if (empty($idxs)) continue;

            $idxs = $orderGroup($idxs);

            if ($remain >= count($idxs)) {
                foreach ($idxs as $i) $alok[$i] += 1;
                $remain -= count($idxs);
                $progress = true;
                continue;
            }

            for ($k=0; $k<$remain; $k++) {
                $i = $idxs[$k];
                $alok[$i] += 1;
            }
            $remain = 0;
            $progress = true;
            break;
        }

        if (!$progress) break;
    }

    return $alok;
}




}
