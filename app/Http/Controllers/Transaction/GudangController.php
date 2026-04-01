<?php

namespace App\Http\Controllers\Transaction;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Models\MasterData\Company;
use App\Models\usematerialhdr;
use App\Models\usemateriallst;
use App\Models\MasterData\HerbisidaDosage;
use App\Models\MasterData\Herbisida;

class GudangController extends Controller
{

    public function __construct()
    {
        View::share([
            'navbar' => 'Input',
            'nav' => 'gudang',
            'routeName' => route('transaction.gudang.index'),
        ]);
    }

    //dummy
    public function index(Request $request)
    {
        $title = "Gudang";

        if ($request->isMethod('post')) {
            $request->validate(['perPage' => 'required|integer|min:1']);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        return view('transaction.gudang.index')->with([
            'title' => 'Gudang',
            'perPage' => $perPage
        ]);
    }

    public function home(Request $request)
    { 
        // if (hasPermission('Menu Gudang')) {
            $usematerialhdr = new usematerialhdr;
            $usehdr2 = $usematerialhdr->selectuse(session('companycode'));

            // Validasi perPage
            if ($request->isMethod('post')) {
                $request->validate(['perPage' => 'required|integer|min:1']);
                $request->session()->put('perPage', $request->input('perPage'));
            }

            $perPage = $request->session()->get('perPage', 10);

            // Filter parameters
            $search = $request->input('search');
            $startDate = $request->input('start_date', now()->subMonths(2)->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));

            // Query dengan filter
            $usehdr = usematerialhdr::from('usematerialhdr as a')
                ->join('rkhhdr as b', function ($join) {
                    $join->on('a.rkhno', '=', 'b.rkhno')
                        ->on('a.companycode', '=', 'b.companycode');
                })
                ->join('user as c', 'b.mandorid', '=', 'c.userid')
                ->leftJoinSub(
                    usemateriallst::select('rkhno', 'companycode', DB::raw('MAX(nouse) as nouse'))
                        ->groupBy('rkhno', 'companycode'),
                    'd',
                    function($join){
                        $join->on('a.rkhno','=','d.rkhno')
                            ->on('a.companycode','=','d.companycode');
                    }
                )
                ->where('a.companycode', session('companycode'))
                ->whereDate('a.createdat', '>=', $startDate)
                ->whereDate('a.createdat', '<=', $endDate);

            // Filter search
            if ($search) {
                $usehdr->where(function ($q) use ($search) {
                    $q->where('a.rkhno', 'like', "%{$search}%")
                        ->orWhere('c.name', 'like', "%{$search}%");
                });
            }

            $usehdr = $usehdr->select('a.*', 'c.name', 'd.nouse', 'b.rkhdate')
                ->orderBy('a.createdat', 'desc')
                ->paginate($perPage)
                ->appends($request->query());

            return view('transaction.gudang.home')->with([
                'title' => 'Gudang',
                'usehdr' => $usehdr,
                'perPage' => $perPage,
                'search' => $search,
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);
        // } else {
        //     return redirect()->back()->with('error', 'Tidak Memiliki Izin Menu!');
        // }
    }

    

    public function report(Request $request)
{
    $title = "Gudang - Report";

    $search    = $request->input('search');
    $startDate = $request->input('start_date', now()->subDays(7)->format('Y-m-d'));
    $endDate   = $request->input('end_date', now()->format('Y-m-d'));

    $company = session('companycode');

    $itemMaster = Herbisida::where('companycode', $company)
        ->select('itemcode', 'itemname', 'measure')
        ->get()
        ->keyBy('itemcode');

    // OUT (USE) -> tanggal pakai rkhhdr.rkhdate
    $out = usemateriallst::from('usemateriallst as u')
        ->join('rkhhdr as b', function($join){
            $join->on('u.rkhno','=','b.rkhno')
                 ->on('u.companycode','=','b.companycode');
        })
        ->where('u.companycode', $company)
        ->whereNotNull('u.nouse')
        ->whereDate('b.rkhdate', '>=', $startDate)
        ->whereDate('b.rkhdate', '<=', $endDate)
        ->when($search, function($q) use ($search){
            $q->where('u.rkhno', 'like', "%{$search}%")
              ->orWhere('u.nouse', 'like', "%{$search}%");
        })
        ->groupBy('u.itemcode', 'u.nouse')
        ->selectRaw("
            u.itemcode,
            u.nouse as docno,
            MIN(b.rkhdate) as dt,
            SUM(u.qty) as qty
        ")
        ->get()
        ->map(fn($r) => (object)[
            'itemcode' => $r->itemcode,
            'type'     => 'U',
            'docno'    => $r->docno,
            'dt'       => $r->dt,
            'masuk'    => null,
            'keluar'   => (float)$r->qty,
        ]);

    // IN (RETUR) -> tanggal pakai rkhhdr.rkhdate
    $in = usemateriallst::from('usemateriallst as u')
        ->join('rkhhdr as b', function($join){
            $join->on('u.rkhno','=','b.rkhno')
                 ->on('u.companycode','=','b.companycode');
        })
        ->where('u.companycode', $company)
        ->whereNotNull('u.noretur')
        ->whereDate('b.rkhdate', '>=', $startDate)
        ->whereDate('b.rkhdate', '<=', $endDate)
        ->when($search, function($q) use ($search){
            $q->where('u.rkhno', 'like', "%{$search}%")
              ->orWhere('u.noretur', 'like', "%{$search}%");
        })
        ->groupBy('u.itemcode', 'u.noretur')
        ->selectRaw("
            u.itemcode,
            u.noretur as docno,
            MIN(b.rkhdate) as dt,
            SUM(u.qtyretur) as qty
        ")
        ->get()
        ->map(fn($r) => (object)[
            'itemcode' => $r->itemcode,
            'type'     => 'R',
            'docno'    => $r->docno,
            'dt'       => $r->dt,
            'masuk'    => (float)$r->qty,
            'keluar'   => null,
        ]);

    $events = $out->concat($in);

    // kalau tidak ada data, langsung return kosong
    if ($events->isEmpty()) {
        return view('transaction.gudang.report')->with([
            'title' => $title,
            'report' => [],
            'search' => $search,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    $itemcodes = $events->pluck('itemcode')->unique()->values();

    $report = [];

    foreach ($itemcodes as $code) {
        $itemEvents = $events->where('itemcode', $code)
            ->sortBy('dt')
            ->values();

        $rows = [];
        $uNo = 0; $rNo = 0;

        foreach ($itemEvents as $ev) {
            if ($ev->type === 'U') $uNo++;
            if ($ev->type === 'R') $rNo++;

            $no = $ev->type === 'U' ? "U-{$uNo}" : "R-{$rNo}";

            $rows[] = (object)[
                'no'     => $no,
                'tgl'    => $ev->dt,
                'ket'    => $ev->docno,
                'masuk'  => $ev->masuk,
                'keluar' => $ev->keluar,
                'type'   => $ev->type,
            ];
        }

        $meta = $itemMaster->get($code);

        $report[] = (object)[
            'itemcode' => $code,
            'itemname' => $meta->itemname ?? '-',
            'unit'     => $meta->measure ?? '-',
            'rows'     => $rows,
        ];
    }

    usort($report, fn($a,$b) => strcmp($a->itemcode, $b->itemcode));

    return view('transaction.gudang.report')->with([
        'title' => $title,
        'report' => $report,
        'search' => $search,
        'startDate' => $startDate,
        'endDate' => $endDate,
    ]);
}

 

    public function detail(Request $request)
    {   
        if( request()->getHost() == 'sugarcane.sblampung.com' ){$islokal = 'LIVE';}else{$islokal = 'TESTING';}
        //tambahan koreksi
        
        $koreksiSummary = DB::table('usematerialapproval')
            ->where('companycode', session('companycode'))
            ->where('rkhno', $request->rkhno)
            ->select('approvalno', 'itemcode')
            ->selectRaw("
                MAX(itemname) as itemname,
                SUM(CASE WHEN LOWER(type) = 'use' THEN qty ELSE 0 END) as qty_use,
                SUM(CASE WHEN LOWER(type) = 'retur' THEN qty ELSE 0 END) as qty_retur,
                flagstatus
            ")
            ->groupBy('approvalno', 'itemcode', 'flagstatus')
            ->orderBy('approvalno', 'asc')
            ->orderBy('itemcode', 'asc')
            ->get();

        $totByItem = collect();
        $koreksiRows = collect();

        if ($koreksiSummary->isNotEmpty()) {
            $totByItem = $koreksiSummary
                ->groupBy('itemcode')
                ->map(function ($rows) {
                    return (object)[
                        'itemcode'  => $rows->first()->itemcode,
                        'itemname'  => $rows->first()->itemname ?? '',
                        'qty_use'   => $rows->sum(fn($r) => (float)$r->qty_use),
                        'qty_retur' => $rows->sum(fn($r) => (float)$r->qty_retur),
                        'qty_netto' => $rows->sum(fn($r) => (float)$r->qty_use) - $rows->sum(fn($r) => (float)$r->qty_retur),
                    ];
                })
                ->sortBy('itemcode')
                ->values();

            $koreksiRows = DB::table('usematerialapproval')
                ->where('companycode', session('companycode'))
                ->where('rkhno', $request->rkhno)
                ->orderBy('itemcode')
                ->orderBy('type')
                ->orderBy('approvalno')
                ->get();
        }
        //tambahan koreksi

        $base = DB::table('usemateriallst')
        ->where('companycode', session('companycode'))
        ->where('rkhno', $request->rkhno)
        ->selectRaw("
            itemcode,
            MAX(itemname) as itemname,
            SUM(qty) as base_use,
            SUM(COALESCE(qtyretur,0)) as base_retur
        ")
        ->groupBy('itemcode')
        ->get()
        ->keyBy('itemcode');

        $koreksi = DB::table('usematerialapproval')
        ->where('companycode', session('companycode'))
        ->where('rkhno', $request->rkhno)
        ->selectRaw("
            itemcode,
            MAX(itemname) as itemname,
            SUM(CASE WHEN type='USE' THEN qty ELSE 0 END) as koreksi_use,
            SUM(CASE WHEN type='RETUR' THEN qty ELSE 0 END) as koreksi_retur
        ")
        ->groupBy('itemcode')
        ->get()
        ->keyBy('itemcode');
        $allCodes = $base->keys()->merge($koreksi->keys())->unique()->sort()->values();

        $finalSummary = $allCodes->map(function($code) use ($base, $koreksi){
            $b = $base->get($code);
            $k = $koreksi->get($code);

            $baseUse   = (float)($b->base_use ?? 0);
            $baseRetur = (float)($b->base_retur ?? 0);

            $korUse    = (float)($k->koreksi_use ?? 0);
            $korRetur  = (float)($k->koreksi_retur ?? 0);

            $useFinal   = $baseUse + $korUse;
            $returFinal = $baseRetur + $korRetur;

            return (object)[
                'itemcode'      => $code,
                'itemname'      => $k->itemname ?? $b->itemname ?? '-',
                'base_use'      => $baseUse,
                'base_retur'    => $baseRetur,
                'koreksi_use'   => $korUse,
                'koreksi_retur' => $korRetur,
                'use_final'     => $useFinal,
                'retur_final'   => $returFinal,
                'pemakaian'     => $useFinal - $returFinal,
            ];
        });
        //tambahan koreksi
        $usematerialhdr = new usematerialhdr;
        $usemateriallst = new usemateriallst;
        $dosage = new HerbisidaDosage;
        $herbisida = new Herbisida;

        $dosage = HerbisidaDosage::get();

        $validItemCodes = HerbisidaDosage::get()->pluck('itemcode')->unique();

        $itemlist = DB::table('herbisidadosage as d')
            ->join('herbisida as h', function ($join) {
                $join->on('d.itemcode', '=', 'h.itemcode')
                    ->on('d.companycode', '=', 'h.companycode');
            })
            ->join('herbisidagroup as hg', 'd.herbisidagroupid', '=', 'hg.herbisidagroupid')
            ->where('d.companycode', session('companycode'))
            ->select(
                'd.itemcode',
                'd.dosageperha',
                'h.itemname',
                'h.measure',
                'd.herbisidagroupid',
                'hg.herbisidagroupname',
                'hg.activitycode',
                'hg.description',
                'hg.rounddosage'
            )
            ->orderBy('d.herbisidagroupid')
            ->orderBy('d.itemcode')
            ->orderBy('d.dosageperha')
            ->get();
            // dd($itemlist->where('activitycode','5.2.3a'));

        // $details = collect($usematerialhdr->selectusematerial(session('companycode'), $request->rkhno, 1));
        // $first = $details->first();
        //3.8
        $details = collect($usematerialhdr->selectusematerial(session('companycode'), $request->rkhno, 1));
        $ccMap = DB::table('costcenter')
            ->where('companycode', session('companycode'))
            ->pluck('costcenter', 'herbisidagroupid');
        $details = $details->map(function ($row) use ($ccMap) {
            $row->costcenter = $ccMap[$row->herbisidagroupid] ?? null;
            return $row;
        });
        $first = $details->first();
        //3.8


        Log::info('SUBMIT DEBUG CONTEXT', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'session_companycode' => session('companycode'),
            'req_rkhno' => $request->rkhno,
            'req_approvalno' => $request->approvalno ?? null,
            'req_costcenter' => $request->costcenter ?? null,
            'host' => $request->getHost(),
            'user_userid' => Auth::user()->userid ?? null,
            'first_hdr_companycode' => $first->companycode ?? null,
            'first_hdr_flagstatus' => $first->flagstatus ?? null,
            'first_hdr_factoryinv' => $first->factoryinv ?? null,
            'first_hdr_companyinv' => $first->companyinv ?? null,
        ]);

        // $detailmaterial2 = collect($usemateriallst->where('rkhno', $request->rkhno)->where('companycode', session('companycode'))->orderBy('lkhno')->orderBy('plot')->get());
        $detailmaterial = collect($usemateriallst->select('usemateriallst.*', 'lkhdetailplot.luasrkh')
            ->leftJoin('lkhdetailplot', function ($join) {
                $join->on('usemateriallst.lkhno', '=', 'lkhdetailplot.lkhno')
                    ->on('usemateriallst.plot', '=', 'lkhdetailplot.plot')
                    ->on('usemateriallst.companycode', '=', 'lkhdetailplot.companycode');
            })
            ->where('rkhno', $request->rkhno)->where('usemateriallst.companycode', session('companycode'))->orderBy('lkhno')->orderBy('plot')->get());
            //group (FIX: unique + trim supaya tidak ketiban & tidak miss karena spasi)
            $plotsUnique = $details->unique(function($x){
                return trim((string)$x->lkhno).'|'.trim((string)$x->plot);
            });

            $groupMap = $plotsUnique->mapWithKeys(function($x){
                return [trim((string)$x->lkhno).'|'.trim((string)$x->plot) => $x->herbisidagroupid];
            });

            $detailmaterial = $detailmaterial->map(function($d) use ($groupMap) {
                $k = trim((string)$d->lkhno).'|'.trim((string)$d->plot);
                $d->herbisidagroupid = $groupMap[$k] ?? null;
                return $d;
            });
            //
        $groupIds = $details->pluck('herbisidagroupid')->unique();
        $lst = usemateriallst::where('rkhno', $request->rkhno)->where('companycode', session('companycode'))->get();

        Log::info('DETAIL DEBUG USEMATERIALLST', [
            'rkhno' => $request->rkhno,
            'session_companycode' => session('companycode'),
            'lst_count' => $lst->count(),
            'lst_max_nouse' => $lst->max('nouse'),
            'lst_nouse_sample' => $lst->pluck('nouse')->filter()->unique()->take(5)->values()->toArray(),
            'lst_itemcodes_sample' => $lst->pluck('itemcode')->take(10)->values()->toArray(),
        ]);        

        //3.8
        //api_costcenter
        // $companyinv = Company::where('companycode', session('companycode'))->first();
        // $response = Http::withoutVerifying()->withOptions(['headers' => ['Accept' => 'application/json']])
        //     ->asJson()
        //     ->get('https://rosebrand.sungaibudigroup.com/app/im-purchasing/purchasing/bpb/costcenter_api', [
        //         'connection' => '172.17.1.39',
        //         'company' => $companyinv->companyinventory,
        //         'factory' => $first->factoryinv
        //     ]);
        // $costcenter = collect($response->json('costcenter'));
        //3.8
        
        $usematerialapproval=null;
        if (strtoupper($details->first()->flagstatus ?? '') === 'WAIT_APPROVAL') {
            $ap = DB::table('usematerialapproval')
            ->where('companycode', session('companycode'))
            ->where('rkhno', $request->rkhno);
            $usematerialapproval = $ap->get();
            //3.8
            // if($details[0]->costcenter == NULL){
            // $details[0]->costcenter = $ap
            //     ->value('costcenter');
            // }
            //3.8
            if ($usematerialapproval) {
                $usematerialapproval = $usematerialapproval->keyBy(function($r){
                    return trim($r->lkhno).'|'.trim($r->plot).'|'.trim($r->itemcode);
                });
            }
        }


        return view('transaction.gudang.detail')->with([
            'title' => 'Gudang',
            'details' => $details,
            'dosage' => $dosage,
            'lst' => $lst,
            'itemlist' => $itemlist,
            // 'costcenter' => $costcenter, 3.8
            'detailmaterial' => $detailmaterial,
            'islokal' => $islokal,
            'usematerialapproval' => $usematerialapproval,
            'finalSummary' => $finalSummary,
            'koreksiSummary' => $koreksiSummary,
            'koreksiRows' => $koreksiRows,
            'totByItem' => $totByItem
        ]);
    }
    

    public function koreksi(Request $request)
    {   
        if( request()->getHost() == 'sugarcane.sblampung.com' ){$islokal = 'LIVE';}else{$islokal = 'TESTING';}

        $usematerialhdr = new usematerialhdr;
        $usemateriallst = new usemateriallst;
        $dosage = new HerbisidaDosage;
        $herbisida = new Herbisida;

        $dosage = HerbisidaDosage::get();

        $validItemCodes = HerbisidaDosage::get()->pluck('itemcode')->unique();

        $itemlist = DB::table('herbisidadosage as d')
            ->join('herbisida as h', function ($join) {
                $join->on('d.itemcode', '=', 'h.itemcode')
                    ->on('d.companycode', '=', 'h.companycode');
            })
            ->join('herbisidagroup as hg', 'd.herbisidagroupid', '=', 'hg.herbisidagroupid')
            ->where('d.companycode', session('companycode'))
            ->select(
                'd.itemcode',
                'd.dosageperha',
                'h.itemname',
                'h.measure',
                'd.herbisidagroupid',
                'hg.herbisidagroupname',
                'hg.activitycode',
                'hg.description',
                'hg.rounddosage'
            )
            ->orderBy('d.herbisidagroupid')
            ->orderBy('d.itemcode')
            ->orderBy('d.dosageperha')
            ->get();

        $details = collect($usematerialhdr->selectusematerial(session('companycode'), $request->rkhno, 1));
        $first = $details->first();

        return view('transaction.gudang.detail')->with([
            'title' => 'Gudang',
            'details' => $details,
            'dosage' => $dosage,
            'itemlist' => $itemlist,
            'islokal' => $islokal
        ]);
    }

    
    //koreksi
    public function koreksi_insert(Request $request)
{
    // Tentukan koneksi berdasarkan host
    if (request()->getHost() == 'sugarcane.sblampung.com') {
        $islokal = 'LIVE';
    } else {
        $islokal = 'TESTING';
    }
    $connection = 'IP' . $islokal;
    $rkhList = usematerialhdr::from('usematerialhdr as a')
    ->join('rkhhdr as b', function ($join) {
        $join->on('a.rkhno','=','b.rkhno')
             ->on('a.companycode','=','b.companycode');
    })
    ->join('user as c','b.mandorid','=','c.userid')
    ->leftJoinSub(
        usemateriallst::select('rkhno','companycode', DB::raw('MAX(nouse) as nouse'))
            ->groupBy('rkhno','companycode'),
        'd',
        fn($join)=>$join->on('a.rkhno','=','d.rkhno')->on('a.companycode','=','d.companycode')
    )
    ->where('a.companycode', session('companycode'))->where('d.nouse', '!=', NULL)
    ->select('a.companycode','a.rkhno','a.flagstatus','b.rkhdate','c.name as mandor_name','d.nouse')
    ->orderBy('b.rkhdate','desc')
    ->get();

    // Ambil daftar semua item untuk dropdown pemakaian baru
    $itemList = Herbisida::where('companycode', session('companycode'))
                         ->orderBy('itemcode')
                         ->get();

    // Return view koreksi untuk menampilkan form
    return view('transaction.gudang.koreksi')->with([
        'rkhList' => $rkhList,
        'itemList' => $itemList,
        'title' => 'Koreksi Stok',
        'navbar' => 'Input',
        'nav' => 'gudang',
    ]);
}

//AJAX
public function getItemsByRkh(Request $request)
{
    $rkhno = $request->input('rkhno');
    $companycode = session('companycode');

    $items = usemateriallst::where('companycode', $companycode)
        ->where('rkhno', $rkhno)
        ->orderBy('itemseq')
        ->get();

    $formattedItems = $items->map(function($item) {
        return [
            'itemseq'  => $item->itemseq,
            'itemcode' => $item->itemcode,
            'itemname' => $item->itemname ?? '',
            'plot'     => $item->plot,
            'lkhno'    => $item->lkhno,
            'qty'      => $item->qty,
            'uom'      => $item->uom ?? '',
            'flagstatus' => $first->flagstatus ?? ''
        ];
    });

    // ✅ Ambil “header view” dari sumber yang sudah pasti punya factoryinv/costcenter/nouse (selectusematerial)
    $first = collect((new usematerialhdr)->selectusematerial($companycode, $rkhno, 1))->first();
    if (!$first) {
        return response()->json([
            'success' => false,
            'message' => 'Data RKH tidak ditemukan.',
            'items' => [],
            'hdr' => null,
            'costcenter' => [],
        ], 404);
    }

    $oldCC = $first->costcenter ?? '';
    $nouse = $first->nouse ?? '';
    $factoryinv = $first->factoryinv ?? null;

    $costcenterList = [];
    $companyinv = null;

    if ($factoryinv) {
        $companyinv = Company::where('companycode', $companycode)->first();

        $resp = Http::withoutVerifying()->asJson()->get(
            'https://rosebrand.sungaibudigroup.com/app/im-purchasing/purchasing/bpb/costcenter_api',
            [
                'connection' => '172.17.1.39',
                'company' => $companyinv->companyinventory ?? null,
                'factory' => $factoryinv,
            ]
        );

        if ($resp->successful()) {
            $costcenterList = $resp->json('costcenter') ?? [];
        } else {
            Log::warning('getItemsByRkh costcenter_api failed', [
                'status' => $resp->status(),
                'body' => substr($resp->body(), 0, 200),
            ]);
        }
    }

    Log::info('getItemsByRkh debug (after fix)', [
        'rkhno' => $rkhno,
        'companycode' => $companycode,
        'factoryinv' => $factoryinv,
        'companyinventory' => $companyinv->companyinventory ?? null,
        'costcenter_count' => count($costcenterList),
        'items_count' => $items->count(),
    ]);

    return response()->json([
        'success' => true,
        'items' => $formattedItems,
        'hdr' => [
            'old_costcenter' => $oldCC,
            'new_costcenter' => $oldCC,
            'nouse' => $nouse,
            'factoryinv' => $factoryinv,
        ],
        'costcenter' => $costcenterList,
    ]);
}


// AJAX: Get item detail by itemseq (untuk tipe USE)
public function getItemDetail(Request $request)
{
    $rkhno = $request->rkhno;
    $itemseq = $request->itemseq;
    $companycode = session('companycode');

    // Ambil detail item berdasarkan itemseq
    $item = usemateriallst::where('companycode', $companycode)
                          ->where('rkhno', $rkhno)
                          ->where('itemseq', $itemseq)
                          ->first();

    if (!$item) {
        return response()->json([
            'success' => false,
            'message' => 'Item tidak ditemukan'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'item' => [
            'itemseq' => $item->itemseq,
            'itemcode' => $item->itemcode,
            'itemname' => $item->itemname ?? '',
            'qty' => $item->qty,
            'uom' => $item->uom ?? '',
        ]
    ]);
}


//koreksi
public function koreksi_submit(Request $request)
{
    $tipe = strtoupper($request->tipe_transaksi ?? '');

    // ✅ VALIDASI UNTUK MULTIPLE ROWS
    $request->validate([
        'rkhno'          => 'required',
        'tipe_transaksi' => 'required|in:USE,RETUR',
        'rows'           => 'required|array|min:1',
        'rows.*.itemseq' => 'required|integer',
        'rows.*.itemcode' => 'required',
        'rows.*.qty'     => 'nullable|numeric|min:0', // nullable karena bisa kosong
    ]);

    $companycode = session('companycode');
    $userid = Auth::user()->userid ?? session('userid');

    // ✅ FILTER: Hanya ambil row yang qty-nya ada (tidak null/kosong)
    $validRows = collect($request->rows)->filter(function($row) {
        return !empty($row['qty']) && (float)$row['qty'] > 0;
    });

    if ($validRows->isEmpty()) {
        return back()->with('error', 'Tidak ada item dengan qty yang valid untuk dikoreksi.');
    }

    // Ambil header RKH
    $header = usematerialhdr::where('companycode', $companycode)
        ->where('rkhno', $request->rkhno)
        ->first();

    if (!$header) {
        return back()->with('error', 'RKH tidak ditemukan.');
    }

    // ✅ CEK APPROVAL MASTER
    $approvalMaster = DB::table('approval')
        ->where('companycode', $companycode)
        ->where('category', 'Use Material')
        ->first();

    if (!$approvalMaster) {
        return back()->with('error', 'Approval master "Use Material" belum di-setup');
    }

    // ✅ GENERATE APPROVALNO: {COMP}{RKH}-{U/R}{01..99}
    $prefix = $companycode . $request->rkhno . '-' . ($tipe === 'RETUR' ? 'R' : 'U');

    $last = DB::table('usematerialapproval')
        ->where('companycode', $companycode)
        ->where('rkhno', $request->rkhno)
        ->where('approvalno', 'like', $prefix . '%')
        ->orderBy('approvalno', 'desc')
        ->value('approvalno');

    $nextNo = 1;
    if ($last) {
        $tail = substr($last, -2);
        if (ctype_digit($tail)) $nextNo = ((int)$tail) + 1;
    }
    
    if ($nextNo > 99) {
        return back()->with('error', 'Nomor dokumen koreksi sudah penuh (maks 99).');
    }

    $approvalno = $prefix . str_pad((string)$nextNo, 2, '0', STR_PAD_LEFT);

    try {
        DB::beginTransaction();

        // ✅ CEK DUPLICATE
        $exists = DB::table('approvaltransaction')
            ->where('companycode', $companycode)
            ->where('transactionnumber', $approvalno)
            ->exists();

        if ($exists) {
            DB::rollBack();
            return back()->with('warning', "Dokumen koreksi {$approvalno} sudah ada.");
        }

        // ✅ 1) INSERT APPROVALTRANSACTION (WORKFLOW)
        DB::table('approvaltransaction')->insert([
            'approvalno' => $approvalno,
            'companycode' => $companycode,
            'approvalcategoryid' => $approvalMaster->id,
            'transactionnumber' => $approvalno,
            'jumlahapproval' => $approvalMaster->jumlahapproval,
            'approval1idjabatan' => $approvalMaster->idjabatanapproval1,
            'approval2idjabatan' => $approvalMaster->idjabatanapproval2,
            'approval3idjabatan' => $approvalMaster->idjabatanapproval3,
            'approvalstatus' => null,
            'inputby' => $userid,
            'createdat' => now(),
        ]);

        // ✅ 2) INSERT MULTIPLE ROWS KE USEMATERIALAPPROVAL
        foreach ($validRows as $row) {
            $itemseq = (int)$row['itemseq'];
            $newItemcode = preg_replace('/\s+/', '', trim((string)$row['itemcode']));
            $qty = (float)$row['qty'];

            // Ambil item original dari usemateriallst
            $orig = usemateriallst::where('companycode', $companycode)
                ->where('rkhno', $request->rkhno)
                ->where('itemseq', $itemseq)
                ->first();

            if (!$orig) {
                Log::warning('KOREKSI_ITEM_NOT_FOUND', [
                    'rkhno' => $request->rkhno,
                    'itemseq' => $itemseq,
                ]);
                continue; // skip item ini
            }

            // Validasi RETUR
            if ($tipe === 'RETUR' && $qty > (float)$orig->qty) {
                DB::rollBack();
                return back()->with('error', 
                    "Item seq {$itemseq}: Qty retur ({$qty}) tidak boleh melebihi qty pemakaian ({$orig->qty})."
                );
            }

            // Ambil item master untuk nama
            $hm = Herbisida::where('companycode', $companycode)
                ->where('itemcode', $newItemcode)
                ->first();

            // Insert ke usematerialapproval
            DB::table('usematerialapproval')->insert([
                'companycode'  => $companycode,
                'approvalno'   => $approvalno,
                'rkhno'        => $request->rkhno,
                'lkhno'        => $orig->lkhno,
                'plot'         => $orig->plot,
                'itemseq'      => $itemseq,
                'itemcode'     => $newItemcode,
                'itemname'     => $hm->itemname ?? $orig->itemname,
                'dosageperha'  => $orig->dosageperha,
                'unit'         => $orig->unit ?? $hm->measure,
                'qty'          => $qty,
                'flagstatus'   => 'WAIT_APPROVAL',
                'type' => $tipe,
                'costcenter'   => $orig->costcenter,
                'createdat'    => now(),
            ]);

            // Log perubahan item (untuk USE)
            if ($tipe === 'USE' && $newItemcode != $orig->itemcode) {
                Log::info('KOREKSI_ITEM_CHANGE', [
                    'approvalno' => $approvalno,
                    'itemseq' => $itemseq,
                    'orig_item' => $orig->itemcode,
                    'new_item' => $newItemcode,
                    'qty' => $qty,
                ]);
            }
        }

        DB::commit();

        Log::info('KOREKSI_APPROVAL_CREATED', [
            'approvalno' => $approvalno,
            'tipe' => $tipe,
            'rkhno' => $request->rkhno,
            'rows_count' => $validRows->count(),
            'user' => $userid,
        ]);

        $message = $tipe === 'RETUR' 
            ? "⚠️ Dokumen retur membutuhkan approval. ApprovalNo: {$approvalno}" 
            : "⚠️ Dokumen koreksi membutuhkan approval. ApprovalNo: {$approvalno}";

        return redirect()
            ->route('transaction.gudang.koreksi')
            ->with('warning', $message);

    } catch (\Throwable $e) {
        DB::rollBack();
        
        Log::error('KOREKSI_SUBMIT_FAILED', [
            'approvalno' => $approvalno ?? null,
            'tipe' => $tipe,
            'rkhno' => $request->rkhno,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return back()->with('error', 'Gagal membuat approval: ' . $e->getMessage());
    }
}

//koreksi



    public function retur(Request $request)
    {   
        Log::info('RETUR DEBUG CONTEXT', [
            'url' => $request->fullUrl(),
            'session_companycode' => session('companycode'),
            'req_rkhno' => $request->rkhno,
            'req_lkhno' => $request->lkhno,
            'req_itemcode' => $request->itemcode,
            'req_plot' => $request->plot,
            'user_userid' => Auth::user()->userid ?? null,
            'host' => $request->getHost(),
        ]);
        
        $usematerialhdr = new usematerialhdr;
        $usemateriallst = new usemateriallst;
        $header = $usematerialhdr->selectuse(session('companycode'), $request->rkhno, 1)->get();
        $hfirst = $header->first();

        // ✅ Pastikan LKH yang diretur sudah approved
        $approvalStatus = DB::table('lkhhdr')
        ->where('companycode', session('companycode'))
        ->where('lkhno', $request->lkhno)
        ->value('approvalstatus');

        if ((int)$approvalStatus !== 1) {
        return back()->with(
            'error',
            "Tidak bisa retur: LKH {$request->lkhno} belum approved (approvalstatus=" . ($approvalStatus ?? 'NULL') . ")."
        );
        }


        Log::info('RETUR DEBUG HEADER', [
            'header_count' => $header->count(),
            'hfirst_exists' => (bool) $hfirst,
            'hfirst_rkhno' => $hfirst->rkhno ?? null,
            'hfirst_companycode' => $hfirst->companycode ?? null,
            'hfirst_flagstatus' => $hfirst->flagstatus ?? null,
            'hfirst_companyinv' => $hfirst->companyinv ?? null,
            'hfirst_factoryinv' => $hfirst->factoryinv ?? null,
            'hfirst_mandorname' => $hfirst->mandorname ?? null,
        ]);        

        $details = usemateriallst::where('companycode', session('companycode'))->where('rkhno', $hfirst->rkhno)->where('lkhno', $request->lkhno)->where('itemcode', $request->itemcode)->where('plot', $request->plot);
        $first = $details->first();

        Log::info('RETUR DEBUG LST ROW', [
            'first_exists' => (bool) $first,
            'first_itemcode' => $first->itemcode ?? null,
            'first_plot' => $first->plot ?? null,
            'first_lkhno' => $first->lkhno ?? null,
            'first_qty' => $first->qty ?? null,
            'first_qtyretur' => $first->qtyretur ?? null,
            'first_nouse' => $first->nouse ?? null,
            'first_noretur' => $first->noretur ?? null,
            'first_costcenter' => $first->costcenter ?? null,
        ]);
        //debug
        $group = DB::table('usemateriallst')
            ->where('rkhno', $request->rkhno)
            ->where('lkhno', $request->lkhno)
            ->where('itemcode', $request->itemcode)
            ->where('plot', $request->plot)
            ->select('companycode', DB::raw('COUNT(*) as cnt'), DB::raw('MAX(nouse) as max_nouse'))
            ->groupBy('companycode')
            ->get();

        Log::info('RETUR DEBUG SAME ROW GROUP BY COMPANY', [
            'rkhno' => $request->rkhno,
            'lkhno' => $request->lkhno,
            'itemcode' => $request->itemcode,
            'plot' => $request->plot,
            'group' => $group
        ]);

        //

        if (strtoupper($hfirst->flagstatus) == 'COMPLETED') {
            return redirect()->back()->with('error', 'Status Barang Sudah Selesai');
        }
        // Validasi status HARUS salah satu dari ini untuk bisa retur
        $allowedStatuses = ['UPLOADED', 'RECEIVED_BY_MANDOR', 'RETURNED_BY_MANDOR', 'RETURN_RECEIVED'];
        if (!in_array(strtoupper($hfirst->flagstatus), $allowedStatuses)) {
            return redirect()->back()->with('error', 'Tidak Bisa Retur! Status harus UPLOAD atau RECEIVED. Status sekarang: ' . $hfirst->flagstatus);
        }
        if (empty($first)) {
            return redirect()->back()->with('error', 'Item Tidak ditemukan!');
        }
        if (!filled($first->nouse)) {
            return back()->with('error', 'Tidak bisa retur: Nomor USE (nouse) kosong.');
        }
        if ($first->qtyretur <= 0) {
            return redirect()->back()->with('error', 'Tidak Bisa Retur! Qty Retur Kosong');
        }
        if ($first->qtyretur > $first->qty) {
            return redirect()->back()->with('error', 'Tidak Bisa Retur! Qty Retur' . $first->qtyretur . ' Lebih Besar Dari Qty Kirim' . $first->qty);
        }
        if ($first->noretur != null) {
            return redirect()->back()->with('error', 'Cant Retur! No Retur Not Empty');
        }

        $rkhdate = DB::table('rkhhdr')
        ->where('companycode', session('companycode'))
        ->where('rkhno', $request->rkhno)
        ->value('rkhdate');

        if (!$rkhdate) {
            return back()->with('error', 'RKH Date tidak ditemukan.');
        }



        $isi = collect();
        $isi->push((object) [
            'CompCodeTerima' => $hfirst->companyinv,
            'FactoryTerima' => $hfirst->factoryinv,
            'ItemGrup' => substr($first->itemcode, 0, 2),
            'CompItemcode' => substr($first->itemcode, 2),
            'prunit' => $first->unit,
            'itemprice' => 0,
            'currcode' => 'IDR',
            'itemnote' => $first->itemname,
            'qtybpb' => $first->qtyretur,
            'Keterangan' => 'Rkhno: ' . $first->rkhno . ', Mandor: ' . ($hfirst->mandorname ?? ''). ' | rkhno:' . $first->rkhno . ' company:' . session('companycode'),
            'vehiclenumber' => '',
            'flagstatus' => 'ACTIVE'
        ]);
        
        $companyinv = Company::where('companycode', session('companycode'))->first();
        if( request()->getHost() == 'sugarcane.sblampung.com' ){$koneksi = '172.17.1.39';}else{$koneksi = 'TESTING';}
        if( session('companycode') == 'TBL4' ){$koneksi = 'TESTING';}
        Log::info('RETUR API PAYLOAD SUMMARY', [
            'connection' => $koneksi ?? null,
            'company_inventory' => $companyinv->companyinventory ?? null,
            'companytebu' => session('companycode'),
            'rkhno' => $request->rkhno,
            'factory' => $hfirst->factoryinv ?? null,
            'nouse' => $first->nouse ?? null,
            'rkhdate' => $rkhdate ?? null,
            'qtyretur' => $first->qtyretur ?? null,
            'itemcode' => $first->itemcode ?? null,
        ]);        

        $response = Http::withoutVerifying()->withOptions([
            'headers' => ['Accept' => 'application/json']
        ])->asJson()
            ->post('https://rosebrand.sungaibudigroup.com/app/im-purchasing/purchasing/bpb/returuse_api', [
                'connection' => $koneksi,
                'company' => $companyinv->companyinventory,
                'companytebu'  => session('companycode'),  // ✅ tambah (atau sumber yg benar)
                'rkhno'        => $request->rkhno,
                'factory' => $hfirst->factoryinv,
                'isi' => $isi,
                'userid' => auth::user()->userid,
                'nouse' => $first->nouse,
                'rkhdate'    => $rkhdate
            ]);

        //log
        Log::info('RETUR API RESPONSE', [
            'http_status' => $response->status(),
            'body' => $response->json(),
        ]);        
        if ($response->successful()) {
            Log::info('API success:', $response->json());
        } else {
            Log::error('API error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        }
        //success update nouse
        if ($response->status() == 200) {
            if ($response->json()['status'] == 1) {
                usemateriallst::where('rkhno', $request->rkhno)->where('companycode', session('companycode'))->where('itemcode', $first->itemcode)
                    ->where('lkhno', $first->lkhno)->where('plot', $first->plot)->update(['noretur' => $response->json()['noretur'],'tglretur'  => now()]);

                    Log::info('RETUR DB UPDATED', [
                        'rkhno' => $request->rkhno,
                        'companycode' => session('companycode'),
                        'itemcode' => $first->itemcode,
                        'plot' => $first->plot,
                        'noretur' => $response->json()['noretur'] ?? null,
                    ]);                    
            }
        } else {
            dd($response->json(), $response->body(), $response->status());
        }

        return redirect()->back()->with('success1', 'Sukses Membuat Dokumen Retur ' . $response->json()['noretur']);
    }

    // Panggil fungsi retur yang SUDAH ADA, satu per baris usemateriallst
    public function returAll(Request $request)
    {
        $rows = usemateriallst::where('companycode', session('companycode'))
            ->where('rkhno', $request->rkhno)
            ->whereNull('noretur')
            ->where('qtyretur', '>', 0)
            ->get(['rkhno', 'lkhno', 'itemcode', 'plot']);
    
        $ok = 0;
        $fail = 0;
        $reject = 0;
    
        foreach ($rows as $row) {
    
            // ✅ REJECT: LKH belum approved
            $approvalStatus = DB::table('lkhhdr')->where('companycode', session('companycode'))
                ->where('lkhno', $row->lkhno)->value('approvalstatus');
    
            if ((int)$approvalStatus !== 1) {
                $reject++;
                continue;
            }
    
            $sub = new \Illuminate\Http\Request([
                'rkhno' => $row->rkhno,
                'lkhno' => $row->lkhno,
                'itemcode' => $row->itemcode,
                'plot' => $row->plot,
            ]);
    
            try {
                $this->retur($sub);  // proses normal
                $ok++;
            } catch (\Throwable $e) {
                \Log::error('retur_bulk error', [
                    'rkhno' => $row->rkhno,
                    'lkhno' => $row->lkhno,
                    'plot' => $row->plot,
                    'itemcode' => $row->itemcode,
                    'err' => $e->getMessage()
                ]);
                $fail++;
            }
        }
    
        return back()->with(
            ($fail > 0 ? 'warning' : 'success1'),
            "Retur massal selesai. Sukses: {$ok}, Gagal: {$fail}, Ditolak: {$reject}"
        );
    }
    


public function submit(Request $request)
{   
    Log::info('SUBMIT_REQUEST_ROWS_FLATTENED', [
        'rkhno' => $request]);
    //kunci proses di cache agar ga dobel submit 
    $lockKey = 'submit_lock_' . session('companycode') . '_' . $request->rkhno;
    if (Cache::has($lockKey)) {
        return redirect()->back()->with('error', 'Sedang memproses request sebelumnya. Mohon tunggu...');
    }
    Cache::put($lockKey, true, 20);
    //tambahan locked
    $releaseLockAndBack = function(string $type, string $msg, int $step) use ($lockKey) {
        Cache::forget($lockKey);
        Log::warning('SUBMIT_EARLY_EXIT', [
            'step' => $step,
            'lockKey' => $lockKey,
            'type' => $type,
            'msg' => $msg,
            'companycode' => session('companycode'),
            'rkhno' => request()->rkhno ?? null,
        ]);
        return back()->with($type, $msg);
    };
    
    //


    // Validasi basic
    $details = collect((new usematerialhdr)->selectusematerial(session('companycode'), $request->rkhno, 1));
    $first = $details->first();
    if (!$first) {
        return $releaseLockAndBack('error', 'Header usematerial tidak ditemukan.', 1);
    }

    $roundingByGroup = DB::table('herbisidagroup')
    ->pluck('rounddosage', 'herbisidagroupid');

    // tambahan cek standar part 2 
    $isFromApproval = $request->filled('approvalno') && DB::table('usematerialapproval')
    ->where('companycode', session('companycode'))
    ->where('approvalno', $request->approvalno)
    ->where('rkhno', $request->rkhno)
    ->exists();
    //

    if (!$isFromApproval && strtoupper($first->flagstatus) != 'ACTIVE') {
        return $releaseLockAndBack('error', 'Tidak Dapat Edit! Item Sudah Tidak Lagi ACTIVE'.$isFromApproval.' | '.strtoupper($first->flagstatus).'', 2);
    }

    // tambahan cek standar part 2 
    if ($isFromApproval && strtoupper($first->flagstatus) != 'WAIT_APPROVAL') {
        return $releaseLockAndBack('error', 'Execute approval hanya boleh saat status WAIT_APPROVAL', 3);
    }    

    if ($details->whereNotNull('nouse')->count() >= 1) {
        return $releaseLockAndBack('error', 'Tidak Dapat Edit! Silahkan Retur', 4);
    }
    
    $rkhdate = DB::table('rkhhdr')
    ->where('companycode', session('companycode'))
    ->where('rkhno', $request->rkhno)
    ->value('rkhdate');

    if (!$rkhdate) {
        return $releaseLockAndBack('error', 'RKH Date tidak ditemukan.', 5);
    }    

    //tambahan cek standar
    $isApproval = false;
    $approvalReasons = [];
    $EPS = 0.0001;
    $stdMap = [];
    
    if (!$isFromApproval) {
        $stdMap = DB::table('herbisidadosage')
            ->where('companycode', session('companycode'))
            ->select('herbisidagroupid','itemcode','dosageperha')
            ->get()
            ->mapWithKeys(fn($r)=>[($r->herbisidagroupid.'|'.$r->itemcode) => (float)$r->dosageperha])
            ->all();
    } 

    //

    // Validasi duplikat: lkhno + plot + itemcode
    foreach ($request->itemcode as $lkhno => $items) {
        foreach ($items as $itemcode => $plots) {
            // Group by plot untuk itemcode tertentu di lkhno tertentu
            $plotsForThisItem = array_keys($plots);
            $uniquePlots = array_unique($plotsForThisItem);

            if (count($plotsForThisItem) !== count($uniquePlots)) {
                // Ada duplikat plot untuk itemcode yang sama di lkhno yang sama
                $duplicatePlots = array_diff_assoc($plotsForThisItem, $uniquePlots);
                $duplicatePlot = reset($duplicatePlots);

                Cache::forget($lockKey);
                return redirect()->back()->withInput()
                    ->with('error', "Duplikat! LKH $lkhno, Plot $duplicatePlot dengan Item $itemcode tidak boleh diinput lebih dari 1 kali.");
            }
        }
    }

    // Get existing data dengan key lkhno-itemcode
    $existingData = usemateriallst::where('rkhno', $request->rkhno)
        ->where('companycode', session('companycode'))
        ->get()
        ->keyBy(function ($item) {
            return $item->lkhno . '-' . $item->itemcode;
        });

    // Key details by lkhno untuk lookupa
    $detailsByKey = $details->keyBy(function($x){
        return trim((string)$x->lkhno).'|'.trim((string)$x->plot);
    });
    $herbisidaItems = Herbisida::where('companycode', session('companycode'))->get()->keyBy('itemcode');

    //3.8
    $costcenterByGroup = DB::table('costcenter')
        ->where('companycode', session('companycode'))
        ->pluck('costcenter', 'herbisidagroupid');
    //3.8

    $insertData = [];
    $apiPayload = [];
    $qtyByItemcode = [];
    $itemDetails = [];
    $seq = 1;
    
    // Process flat - langsung dari request
    foreach ($request->itemcode as $lkhno => $items) {
        foreach ($items as $itemcode => $keys) {

            // hilangin item newline spasi gajelas
            $itemcode = preg_replace('/\s+/', '', trim($itemcode));
            foreach ($keys as $key => $val) {

                $detail = $detailsByKey[trim((string)$lkhno).'|'.trim((string)$key)] ?? null;
                if (!$detail) {
                    Cache::forget($lockKey);
                    throw new \Exception("Detail tidak ditemukan untuk $lkhno plot $key");
                }

                $dosage = floatval($request->dosage[$lkhno][$itemcode][$key] ?? 0);
                $unit = $request->unit[$lkhno][$itemcode][$key] ?? null;
                $luas = $request->luas[$lkhno][$itemcode][$key] ?? 0;
                $qtyraw = $luas * $dosage ?? 0;
                Log::info('BEFORE_ROUNDING', [
                    'lkhno' => $lkhno,
                    'itemcode' => $itemcode,
                    'plot' => $key,
                    'luas' => $luas,
                    'dosage' => $dosage,
                    'qtyraw' => $qtyraw,
                    'groupId' => $detail->herbisidagroupid ?? null,
                ]);

                //tambahan cek standar cek dosage standard 
                if (!$isFromApproval) {
                    $groupId = $detail->herbisidagroupid ?? null;
                    if ($groupId) {
                        $kstd = $groupId.'|'.$itemcode;

                        // itemcode tidak ada di standar untuk group tsb
                        if (!isset($stdMap[$kstd])) {
                            $isApproval = true;
                            $approvalReasons[] = [
                                'type' => 'INVALID_ITEMCODE',
                                'lkhno' => $lkhno,
                                'plot' => $key,
                                'group' => $groupId,
                                'itemcode' => $itemcode,
                                'dosage_input' => $dosage,
                            ];
                            Log::info('APPROVAL_INVALID_ITEMCODE', [
                                'rkhno' => $request->rkhno,
                                'lkhno' => $lkhno,
                                'plot' => $key,
                                'group' => $groupId,
                                'itemcode' => $itemcode,
                                'dosage_input' => $dosage,
                                'kstd' => $kstd,
                            ]);
                        } else {
                            // dosage berbeda dari standar
                            $stdDos = (float)$stdMap[$kstd];
                            if (abs($dosage - $stdDos) > $EPS) {
                                $isApproval = true;
                                $approvalReasons[] = [
                                    'type' => 'DOSAGE_CHANGED',
                                    'lkhno' => $lkhno,
                                    'plot' => $key,
                                    'group' => $groupId,
                                    'itemcode' => $itemcode,
                                    'dosage_input' => $dosage,
                                    'dosage_std' => $stdDos,
                                ];
                                Log::info('APPROVAL_DOSAGE_CHANGED', [
                                    'rkhno' => $request->rkhno,
                                    'lkhno' => $lkhno,
                                    'plot' => $key,
                                    'group' => $groupId,
                                    'itemcode' => $itemcode,
                                    'dosage_input' => $dosage,
                                    'dosage_std' => $stdDos,
                                    'diff' => abs($dosage - $stdDos),
                                    'kstd' => $kstd,
                                ]);
                            }
                        }
                    }
                } 
                // <-- ini closing bungkus cek standar

                // ambil group & flag rounding
                $groupId     = $detail->herbisidagroupid ?? null;
               // $rounddosage = $groupId !== null ? ($roundingByGroup[$groupId] ?? 1) : 1; // default: masih rounded seperti lama
                //    $itemMeta = $herbisidaItems[$itemcode] ?? null;
                //    $rounddosage = $itemMeta->rounddosage ?? 0; 
                $groupId = $detail->herbisidagroupid ?? null;
                $rounddosage = $groupId !== null ? (int)($roundingByGroup[$groupId] ?? 0) : 0;
               Log::info('ROUNDING_CHECK', [
                    'rkhno' => $request->rkhno,
                    'lkhno' => $lkhno,
                    'plot' => $key,
                    'itemcode' => $itemcode,
                    'herbisidagroupid' => $groupId,
                    'rounddosage' => $rounddosage,
                    'qtyraw' => $qtyraw,
                ]);
                //3.8
                $rowCostcenter = $groupId !== null ? ($costcenterByGroup[$groupId] ?? null) : null;
                if (!$rowCostcenter) {
                    return $releaseLockAndBack(
                        'error',
                        "Costcenter belum diset untuk herbisidagroup {$groupId} (LKH {$lkhno}, Plot {$key})",
                        6
                    );
                }
                //3.8
                // ✅ ROUNDING HANYA SEKALI (sama seperti di view)
                if ($qtyraw > 0) {
                    if ($rounddosage) {
                        // truncate dulu ke 2 desimal
                        $truncated = floor($qtyraw * 100) / 100;
                
                        // rounding ke kelipatan 0.05
                        $qty = round($truncated / 0.05) * 0.05;
                
                        if ($qty == 0) {
                            $qty = 0.05;
                        }
                    } else {
                        // normal round 2 desimal
                        $qty = round($qtyraw, 2);
                
                        if ($qty == 0) {
                            $qty = 0.01;
                        }
                    }
                } else {
                    $qty = 0;
                }
                
                $qty = round($qty, 2);

                Log::info('ROUNDING_DEBUG',[
                    'itemcode'=>$itemcode,
                    'qtyraw'=>$qtyraw,
                    'rounddosage'=>$rounddosage,
                    'qty'=>$qty
                ]);
                $existingKey = $lkhno . '-' . $itemcode . '-' . $key;
                $existing = $existingData->get($existingKey);
                Log::info('QTY_FINAL_DEBUG', [
                    'itemcode' => $itemcode,
                    'plot' => $key,
                    'qtyraw' => $qtyraw,
                    'rounddosage' => $rounddosage,
                    'qty_final' => $qty,
                ]);

                $insertData[] = [
                    'companycode' => session('companycode'),
                    'rkhno' => $request->rkhno,
                    'lkhno' => $lkhno,
                    'itemcode' => $itemcode,
                    'qty' => $qty,
                    'unit' => $unit,
                    'qtyretur' => $existing?->qtyretur ?? 0,
                    'itemname' => $herbisidaItems[$itemcode]->itemname ?? '',
                    'dosageperha' => $dosage,
                    'nouse' => $existing?->nouse ?? null,
                    'plot' => $key,
                    'itemseq' => $seq++,
                    //3.8
                    'costcenter' => $rowCostcenter,
                    //3.8
                ];

                // Jumlahkan qty per itemcode
                $qtyByItemcode[$itemcode] = ($qtyByItemcode[$itemcode] ?? 0) + $qty;

                // Simpan detail itemcode (ambil yang pertama aja)
                if (!isset($itemDetails[$itemcode])) {
                    $itemDetails[$itemcode] = [
                        'detail' => $detail,
                        'unit' => $unit,
                        //3.8
                        'costcenter' => $rowCostcenter,
                        //3.8
                    ];
                }
            }
        }
    }

    //tambahan cek standar cek is approval
    // =====================================
    // STOP & CREATE APPROVAL DOC
    // =====================================

    if (!$isFromApproval && $isApproval) {
        try {
            $companycode = session('companycode');

            // ambil master approval
            $approvalMaster = DB::table('approval')
                ->where('companycode', $companycode)
                ->where('category', 'Use Material') // pastikan sama persis
                ->first();

            if (!$approvalMaster) {
                Cache::forget($lockKey);
                return back()->with('error', 'Approval master "Use Material" belum di-setup');
            }

            $approvalNo = $request->rkhno; // approvalno = rkhno
            
            DB::beginTransaction();

                $exists = DB::table('approvaltransaction')
                ->where('companycode', $companycode)
                ->where('transactionnumber', $request->rkhno)
                ->exists();

                if ($exists) {
                    DB::rollBack();
                    Cache::forget($lockKey);
                    return back()->with('warning', "RKH {$request->rkhno} sudah punya approval. Tidak boleh buat lagi.");
                }



            // 1) insert approvaltransaction (workflow)
            DB::table('approvaltransaction')->insert([
                'approvalno' => $companycode.$approvalNo,
                'companycode' => $companycode,
                'approvalcategoryid' => $approvalMaster->id,
                'transactionnumber' => $request->rkhno, // tampil di approval center
                'jumlahapproval' => $approvalMaster->jumlahapproval,
                'approval1idjabatan' => $approvalMaster->idjabatanapproval1,
                'approval2idjabatan' => $approvalMaster->idjabatanapproval2,
                'approval3idjabatan' => $approvalMaster->idjabatanapproval3,
                'approvalstatus' => null,
                'inputby' => Auth::user()->userid,
                'createdat' => now(),
            ]);
            

            // 2) insert snapshot ke usematerialapproval (detail-only)
            $rows = []; $seq = 1;
            foreach ($insertData as $row) {
                $rows[] = [
                    'companycode' => $companycode,
                    'approvalno' => $companycode.$approvalNo,
                    'rkhno' => $request->rkhno,
                    'lkhno' => $row['lkhno'],
                    'plot' => $row['plot'],
                    'itemcode' => $row['itemcode'],
                    'itemname' => $row['itemname'] ?? null,
                    'dosageperha' => $row['dosageperha'],
                    'unit' => $row['unit'],
                    'qty' => $row['qty'],
                    'flagstatus' => 'WAIT_APPROVAL',
                    'costcenter' => $row['costcenter'] ?? null,
                    'createdat' => now(),
                    'itemseq'     => $seq++,
                ];
            }
            DB::table('usematerialapproval')->insert($rows);

            usematerialhdr::where('companycode', $companycode)
            ->where('rkhno', $request->rkhno)
            ->update([
                'flagstatus' => 'WAIT_APPROVAL',
                'updatedat' => now(),
                'updateby' => Auth::user()->userid
            ]);

            DB::commit();
            Cache::forget($lockKey);

            // optional: tampilkan alasan ringkas
            $msg = "Butuh approval. ApprovalNo: {$approvalNo}";
            return back()->with('warning', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            Cache::forget($lockKey);
            Log::error('Create approval failed', ['error' => $e->getMessage(),'trace' => $e->getTraceAsString(),]);
            return back()->with('error', 'Gagal membuat approval: ' . $e->getMessage());
        }
    }
    //
    Log::info('GUDANG_TERIMA_QTY', [
        'rkhno' => $request->rkhno,
        'qtyByItemcode' => $qtyByItemcode,
    ]);
    foreach ($qtyByItemcode as $itemcode => $totalQty) {
        $detail = $itemDetails[$itemcode]['detail'];
        $unit = $itemDetails[$itemcode]['unit'];

        $apiPayload[$itemcode] = [
            'CompCodeTerima' => $detail->companyinv,
            'FactoryTerima' => $detail->factoryinv,
            'ItemGrup' => substr($itemcode, 0, 2),
            'CompItemcode' => substr($itemcode, 2),
            'prunit' => $unit,
            'itemprice' => 0,
            'currcode' => 'IDR',
            'itemnote' => $detail->herbisidagroupname,
            'qtybpb' => $totalQty,
            'Keterangan' => $detail->herbisidagroupname . ' - ' . $detail->name. ' | rkhno:' . $request->rkhno . ' company:' . session('companycode'),
            'vehiclenumber' => '',
            'flagstatus' => $isFromApproval ? 'ACTIVE' : $detail->flagstatus,
            'qtydigunakan' => $detail->qtydigunakan,
            //3.8
            'costcenter' => $itemDetails[$itemcode]['costcenter'] ?? null,
            //3.8
        ];
    }

    // Gunakan DB Transaction untuk keamanan
    DB::beginTransaction();

    try {

        if (usemateriallst::where('rkhno', $request->rkhno)->where('companycode', session('companycode'))->whereNotNull('nouse')->exists()) {
            throw new \Exception('Data sudah diproses oleh user lain!');
        }
        
        // Delete existing records
        usemateriallst::where('rkhno', $request->rkhno)
            ->where('companycode', session('companycode'))
            ->delete();
        
            //tambahan cio
            // DEBUG FK herbisida mismatch (lihat itemcode sebenarnya)
            $debugItems = collect($insertData)->take(10)->map(function($r){
                $ic = (string)($r['itemcode'] ?? '');
                return [
                    'companycode' => $r['companycode'] ?? null,
                    'itemcode_raw' => $ic,
                    'itemcode_hex' => bin2hex($ic),
                    'itemcode_trim' => rtrim($ic),
                    'itemcode_trim_hex' => bin2hex(rtrim($ic)),
                    'itemname_from_lookup' => $r['itemname'] ?? null,
                ];
            })->toArray();

            
            $missing = [];
            foreach ($insertData as $r) {
                $ic = (string)($r['itemcode'] ?? '');
                $exists = DB::table('herbisida')
                    ->where('companycode', $r['companycode'])
                    ->where('itemcode', $ic)
                    ->exists();

                if (!$exists) {
                    $missing[] = [
                        'companycode' => $r['companycode'],
                        'itemcode_raw' => $ic,
                        'itemcode_hex' => bin2hex($ic),
                    ];
                }
            }


            //
        // Bulk insert
        usemateriallst::insert($insertData);

        // ✅ COMMIT - Semua operasi DB sudah selesai
        DB::commit();

    } catch (\Exception $e) {
        DB::rollback();
        Cache::forget($lockKey);
        Log::error('Submit error before API', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    }

    // ✅ API Call - SETELAH COMMIT
    try {
        $companyinv = Company::where('companycode', session('companycode'))->first();
        if( request()->getHost() == 'sugarcane.sblampung.com' ){$koneksi = '172.17.1.39';}else{$koneksi = 'TESTING';}
        if( session('companycode') == 'TBL4' ){$koneksi = 'TESTING';}
        Log::info('SUBMIT API PAYLOAD SUMMARY', [
            'connection' => $koneksi ?? null,
            'company_inventory' => $first->companyinv ?? null,
            'companytebu' => session('companycode'),
            'rkhno' => $request->rkhno,
            'factory' => $first->factoryinv ?? null,
            'costcenter_items' => collect($apiPayload)->pluck('costcenter')->filter()->unique()->values()->toArray(),
            'rkhdate' => $rkhdate ?? null,
            'items_count' => is_array($apiPayload) ? count($apiPayload) : null,
            'api_itemcodes' => array_slice(array_keys($apiPayload ?? []), 0, 10),
        ]);
        //cek apakah koreksi atau tidak standard
        $isKoreksi = $isFromApproval && str_contains($request->approvalno, '-U');

        $response = 
        // Http::withoutVerifying()
        Http::withOptions([
                'verify' => false,
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                ]
            ])
            ->asJson()
            ->timeout(30)
            ->post('https://rosebrand.sungaibudigroup.com/app/im-purchasing/purchasing/bpb/use_api', [
                'connection'   => $koneksi,
                'company'      => $first->companyinv,
                'companytebu'  => session('companycode'),
                'rkhno'        => $request->rkhno,
                'factory'      => $first->factoryinv,
                'costcenter'   => null,
                'isi'          => array_values($apiPayload),
                'userid'       => substr(auth()->user()->userid, 0, 10),
                'rkhdate'      => $rkhdate,
                // 'type'         => $isFromApproval ? 'KS' : ''
                'type'         => $isKoreksi ? 'KS' : ''
            ]);

        // Check jika API gagal
        if (!$response->successful()) {
            Cache::forget($lockKey);
            Log::error('API use_api failed after commit', [
                'status' => $response->status(),
                'body' => $response->body(),
                'rkhno' => $request->rkhno,
                'payload_sent' => [
                    'company' => $companyinv->companyinventory,
                    'factory' => $first->factoryinv,
                    'costcenter_items' => collect($apiPayload)->pluck('costcenter')->filter()->unique()->values()->toArray(),
                    'isi' => array_values($apiPayload),
                    'userid' => substr(auth()->user()->userid, 0, 10)
                ]
            ]);
            
            $msg = 'Data tersimpan, tapi API gagal. Status: ' . $response->status();
            return $releaseLockAndBack('warning', $msg, 80);
        }

        $responseData = $response->json();
        Log::info('SUBMIT API RESPONSE DETAIL', [
            'http_status' => $response->status(),
            'api_status' => $responseData['status'] ?? null,
            'api_code' => $responseData['code'] ?? null,
            'api_message' => $responseData['message'] ?? null,
            'api_errors_sample' => is_array($responseData['errors'] ?? null) ? array_slice($responseData['errors'], 0, 5) : null,
        ]);
        
        
        // Check response
        if ($response->status() == 200 && isset($responseData['status']) && $responseData['status'] == 1) {
            //new
            
            // ===== FIX: stockitem dari API use_api adalah associative array (key = itemcode) =====
            $itemPriceMap = [];
            foreach (($responseData['stockitem'] ?? []) as $itemcode => $row) {

                // $row kadang object, kadang array
                if (is_object($row)) {
                    $row = (array) $row;
                }

                $itemPriceMap[$itemcode] = [
                    'itemprice'  => $row['Itemprice'] ?? 0,
                    'startstock' => $row['StartStock'] ?? 0,
                    'endstock'   => $row['EndStock'] ?? 0,
                ];
            }

            // Update nouse & itemprice
            foreach ($itemPriceMap as $itemcode => $val) {

                Log::info("Before DB update:", [
                    'itemcode' => $itemcode,
                    'itemprice' => $val['itemprice'],
                    'type' => gettype($val['itemprice']),
                    'startstock' => $val['startstock'],
                    'endstock' => $val['endstock'],
                ]);

                $affected = usemateriallst::where('rkhno', $request->rkhno)
                    ->where('companycode', session('companycode'))
                    ->where('itemcode', $itemcode)
                    ->update([
                        'nouse' => $responseData['noUse'] ?? null,
                        'itemprice' => $val['itemprice'],
                        'costcenter' => $itemDetails[$itemcode]['costcenter'] ?? null,
                        'startstock' => $val['startstock'],
                        'endstock' => $val['endstock'],
                        'tgluse'    => now()
                    ]);

                // Cek hasil di database
                $saved = usemateriallst::where('rkhno', $request->rkhno)
                    ->where('companycode', session('companycode'))
                    ->where('itemcode', $itemcode)
                    ->value('itemprice');

                Log::info("After DB update:", [
                    'itemcode' => $itemcode,
                    'itemprice_saved' => $saved,
                    'affected_rows' => $affected,
                    'type' => gettype($saved)
                ]);

                if ($affected === 0) {
                    Log::warning('usemateriallst not updated (possible itemcode mismatch)', [
                        'rkhno' => $request->rkhno,
                        'companycode' => session('companycode'),
                        'itemcode' => $itemcode,
                        'noUse' => $responseData['noUse'] ?? null
                    ]);
                }
            }

            Log::info('SUBMIT AFTER UPDATE USEMATERIALLST', [
                'rkhno' => $request->rkhno,
                'session_companycode' => session('companycode'),
                'db_lst_max_nouse' => usemateriallst::where('rkhno', $request->rkhno)
                    ->where('companycode', session('companycode'))
                    ->max('nouse'),
                'db_lst_nouse_distinct' => usemateriallst::where('rkhno', $request->rkhno)
                    ->where('companycode', session('companycode'))
                    ->whereNotNull('nouse')
                    ->distinct()
                    ->pluck('nouse')
                    ->take(5)
                    ->toArray(),
                'db_lst_notnull_nouse_count' => usemateriallst::where('rkhno', $request->rkhno)
                    ->where('companycode', session('companycode'))
                    ->whereNotNull('nouse')
                    ->count(),
            ]);

            usematerialhdr::where('rkhno', $request->rkhno)
            ->where('companycode', session('companycode'))
            ->update([
                'errorcode' => $responseData['code'] ?? 'UNKNOWN',
                'flagstatus' => 'DISPATCHED',
                'updatedat' => now(),
                'updateby' => Auth::user()->userid
            ]);

            // ✅ Update approval snapshot - DI SINI setelah API sukses!
            if ($isFromApproval) {
                DB::table('usematerialapproval')
                    ->where('companycode', session('companycode'))
                    ->where('rkhno', $request->rkhno)
                    ->update([
                        'errorcode' => null,
                        'approved' => 1,                    
                        'approvedat' => now(),              
                        'approvedby' => Auth::user()->userid, 
                        'flagstatus' => 'DISPATCHED',
                    ]);
                
                Log::info('APPROVAL_FINALIZED_AFTER_API_SUCCESS', [
                    'rkhno' => $request->rkhno,
                    'approvalno' => $request->approvalno ?? $request->rkhno,
                ]);
            }
            
            Cache::forget($lockKey);
            return redirect()->route('transaction.gudang.detail', ['rkhno' => $request->rkhno])->with('success', 'Data berhasil disimpan! NoUse: ' . ($responseData['noUse'] ?? 'N/A'));
            

        } else {
            $errorCode = $responseData['code'] ?? 'UNKNOWN';
            $errorMsg  = $responseData['message'] ?? 'API error';
            $errors    = $responseData['errors'] ?? [];
        
            Log::error('API response invalid after commit', [
                'status' => $response->status(),
                'code' => $errorCode,
                'message' => $errorMsg,
                'rkhno' => $request->rkhno,
                'errors' => $errors,
            ]);
        
            // ✅ user-friendly message (pakai message dari API untuk stock karena sudah flashMessages)
            if ($errorCode === 'INSUFFICIENT_STOCK') {
                $msg = "⚠️ Stok tidak mencukupi. " . ($errorMsg ?: '');
            } elseif ($errorCode === 'CHECKSP_EMPTY') {
                $msg = "⚠️ Gagal cek stok inventory. " . ($errorMsg ?: '');
            } elseif ($errorCode === 'DUPLICATE') {
                $msg = "⚠️ Data duplikat terdeteksi. " . ($errorMsg ?: '');
            } elseif ($errorCode === 'FACTORY_NOT_FOUND') {
                $msg = "⚠️ Factory tidak valid. " . ($errorMsg ?: '');
            } else {
                // fallback: tetap tampilkan code biar bisa ditelusuri
                $msg = "⚠️ {$errorCode}: " . ($errorMsg ?: 'API error');
            }
        
            return $releaseLockAndBack('warning', trim($msg), 90);
        }
        

    } catch (\Exception $e) {
        Cache::forget($lockKey);
        Log::error('API error after commit', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'rkhno' => $request->rkhno
        ]);
        
        return redirect()->back()->with('warning', 'Data tersimpan, tapi error pada proses API: ' . $e->getMessage());
    }
}

    public function handle(Request $request)
    {
        if ($request->has('perPage')) {
            return $this->index($request);
        }

        return $this->store($request);
    }

    protected function requestValidated(): array
    {
        return [
            'kodeaktivitas' => 'required',
            'grupaktivitas' => 'required|exists:activitygroup,activitygroup',
            'namaaktivitas' => 'required',
            'keterangan' => 'max:150',
            'var.*' => 'required',
            'satuan.*' => 'required'
        ];
    }

    public function store(Request $request)
    {
        $request->validate($this->requestValidated());
        $exists = DB::table('activity')->where('activitycode', $request->kodeaktivitas)->exists();

        if ($exists) {
            parent::h_flash('Kode aktivitas sudah ada dalam database.', 'danger');
            return redirect()->back()->withInput();
        }

        $hasil = array();
        $inputVar = $request->var;
        $inputSatuan = $request->satuan;
        $input = [
            'activitycode' => $request->kodeaktivitas,
            'activitygroup' => $request->grupaktivitas,
            'activityname' => $request->namaaktivitas,
            'description' => $request->keterangan,
            'usingmaterial' => $request->material,
            'usingvehicle' => $request->vehicle,
            'jumlahvar' => count($request->var),
            'createdat' => date("Y-m-d H:i"),
            'inputby' => Auth::user()->userid
        ];
        foreach ($request->var as $index => $value) {
            $hasil["var" . $index + 1] = $value;
            $hasil["satuan" . $index + 1] = $inputSatuan[$index];
        }

        $input = array_merge($input, $hasil);

        try {
            DB::transaction(function () use ($input) {
                DB::table('activity')->insert($input);
            });
            parent::h_flash('Berhasil menambahkan data.', 'success');
            return redirect()->back();
        } catch (\Exception $e) {
            parent::h_flash('Error pada database, hubungi IT.', 'danger');
            return redirect()->back()->withInput();
            ;
        }

        return redirect()->back();
    }

    public function update(Request $request, $activityCode)
    {
        $request->validate($this->requestValidated());

        $exists = DB::table('activity')->where('activitycode', $request->kodeaktivitas)->exists();

        if (!$exists) {
            parent::h_flash('Data Tidak Ditemukan.', 'danger');
            return redirect()->back()->withInput();
        }

        DB::transaction(function () use ($request, $activityCode) {

            $input = [
                'activitycode' => $request->kodeaktivitas,
                'activitygroup' => $request->grupaktivitas,
                'activityname' => $request->namaaktivitas,
                'description' => $request->keterangan,
                'jumlahvar' => count($request->var),
                'usingmaterial' => $request->material,
                'usingvehicle' => $request->vehicle,
                'updatedat' => date("Y-m-d H:i"),
                'updatedby' => Auth::user()->userid
            ];
            $hasil = array();
            $inputSatuan = $request->satuan;
            foreach ($request->var as $index => $value) {
                $hasil["var" . $index + 1] = $value;
                $hasil["satuan" . $index + 1] = $inputSatuan[$index];
            }

            $input = array_merge($input, $hasil);

            DB::table('activity')->where('activitycode', $activityCode)->update($input);

        });

        return redirect()->route('masterdata.aktivitas.index')->with('success1', 'Data updated successfully.');
    }

    public function destroy($activityCode)
    {
        DB::transaction(function () use ($activityCode) {
            DB::table('activity')->where('activitycode', $activityCode)->delete();
        });
        parent::h_flash('Berhasil menghapus data.', 'success');
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }








}