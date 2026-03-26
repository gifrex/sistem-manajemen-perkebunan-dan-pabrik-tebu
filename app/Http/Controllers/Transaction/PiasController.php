<?php

namespace App\Http\Controllers\Transaction;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
