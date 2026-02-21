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

class HPTController extends Controller
{

    public function __construct()
    {
        View::share([
            'navbar' => 'Input Data',
            'nav' => 'HPT',
            'routeName' => route('transaction.hpt.index'),
        ]);
    }

    protected function requestValidated(): array
    {
        return [
            'nosample' => 'required',
            'companycode' => 'required',
            'blok' => 'required',
            'plot' => 'required',
            // 'idblokplot' => 'required|exists:mapping,idblokplot',
            'varietas' => 'required',
            'kat' => 'required',
            'tanggaltanam' => 'required',
            'tanggalpengamatan' => 'required',
            'lists.*.nourut' => 'required',
            'lists.*.ppt_aktif' => 'required',
            'lists.*.pbt_aktif' => 'required',
            'lists.*.skor0' => 'required',
            'lists.*.skor1' => 'required',
            'lists.*.skor2' => 'required',
            'lists.*.skor3' => 'required',
            'lists.*.skor4' => 'required',
            'lists.*.telur_ppt' => 'required',
            'lists.*.larva_ppt1' => 'required',
            'lists.*.larva_ppt2' => 'required',
            'lists.*.larva_ppt3' => 'required',
            'lists.*.larva_ppt4' => 'required',
            'lists.*.pupa_ppt' => 'required',
            'lists.*.ngengat_ppt' => 'required',
            'lists.*.kosong_ppt' => 'required',
            'lists.*.telur_pbt' => 'required',
            'lists.*.larva_pbt1' => 'required',
            'lists.*.larva_pbt2' => 'required',
            'lists.*.larva_pbt3' => 'required',
            'lists.*.larva_pbt4' => 'required',
            'lists.*.pupa_pbt' => 'required',
            'lists.*.ngengat_pbt' => 'required',
            'lists.*.kosong_pbt' => 'required',
            'lists.*.dh' => 'required',
            'lists.*.dt' => 'required',
            'lists.*.kbp' => 'required',
            'lists.*.kbb' => 'required',
            'lists.*.kp' => 'required',
            'lists.*.cabuk' => 'required',
            'lists.*.belalang' => 'required',
            'lists.*.serang_grayak' => 'required',
            'lists.*.jum_grayak' => 'required',
            'lists.*.serang_smut' => 'required',
            'lists.*.smut_stadia1' => 'required',
            'lists.*.smut_stadia2' => 'required',
            'lists.*.smut_stadia3' => 'required',
        ];
    }

    public function index(Request $request)
    {
        $title = "Daftar HPT";
        $search = $request->input('search', '');

        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $userid = Auth::user()->userid;
        $companycode = DB::table('usercompany')
            ->where('userid', $userid)
            ->value('companycode');
        $companyArray = $companycode ? explode(',', $companycode) : [];

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);

            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        $hpt = DB::table('hpthdr')
            ->join('company', 'hpthdr.companycode', '=', 'company.companycode')
            ->where('hpthdr.companycode', '=', session('companycode'))
            ->where('hpthdr.closingperiode', '=', 'F')
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('hpthdr.createdat', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('hpthdr.createdat', '<=', $endDate);
            });

        if (!empty($search)) {
            $hpt->where(function ($query) use ($search) {
                $query->where('hpthdr.nosample', 'like', '%' . $search . '%')
                    ->orWhere('hpthdr.varietas', 'like', '%' . $search . '%')
                    ->orWhere('hpthdr.plot', 'like', '%' . $search . '%')
                    ->orWhere('hpthdr.kat', 'like', '%' . $search . '%');
            });
        }

        $hpt = $hpt->select(
            'hpthdr.*',
            'company.name as nama_comp'
        )
            ->orderBy('hpthdr.createdat', 'desc')
            ->paginate($perPage);

        foreach ($hpt as $item) {
            $item->umur_tanam = Carbon::parse($item->tanggaltanam)->diffInMonths(Carbon::now());
        }

        foreach ($hpt as $index => $item) {
            $item->no = ($hpt->currentPage() - 1) * $hpt->perPage() + $index + 1;
        }

        if ($request->ajax()) {
            return view('transaction.hpt.index', compact('hpt', 'perPage', 'startDate', 'endDate', 'title', 'search'));
        }
        return view('transaction.hpt.index', compact('hpt', 'perPage', 'startDate', 'endDate', 'title', 'search'));
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
        $mapping = DB::table('mapping')->where('companycode', '=', session('companycode'))->get();
        $method = 'POST';
        $url = route('transaction.hpt.handle');
        $buttonSubmit = 'Create';
        return view('transaction.hpt.form', compact('mapping', 'title', 'method', 'url', 'buttonSubmit'));
    }

    public function getBlokbyField(Request $request)
    {
        // $idblokplot = $request->input('idblokplot');
        $plot = $request->input('plot');
        $blok = DB::table('masterlist')->where('plot', $plot)
            ->where('companycode', session('companycode'))
            ->where('isactive', 1)
            ->first();

        if ($blok) {
            return response()->json([
                // 'idblokplot' => $blok->idblokplot,
                'blok' => $blok->blok,
            ]);
        }

        return response()->json(['message' => 'Data not found'], 404);
    }

    public function getVarietasandKategori(Request $request)
    {
        // $idblokplot = $request->input('idblokplot');
        $plot = $request->input('plot');
        $batch = DB::table('batch')->where('plot', $plot)
            ->where('companycode', session('companycode'))
            ->where('isactive', 1)
            ->first();

        if ($batch) {
            return response()->json([
                'varietas' => $batch->kodevarietas,
                'kat' => $batch->lifecyclestatus,
                'tanggaltanam' => $batch->tanggalulangtahun,
            ]);
        }

        return response()->json(['message' => 'Data not found'], 404);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->requestValidated());

        $existsInHeader = DB::table('hpthdr')->where('nosample', $request->nosample)
            ->where('companycode', $request->companycode)
            ->where('tanggalpengamatan', $request->tanggalpengamatan)
            ->exists();

        $existsInLists = DB::table('hptlst')->where('nosample', $request->nosample)
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

            DB::table('hpthdr')->insert([
                'nosample' => $validated['nosample'],
                'companycode' => $validated['companycode'],
                'blok' => $validated['blok'],
                'plot' => $validated['plot'],
                // 'idblokplot' => $validated['idblokplot'],
                'varietas' => $validated['varietas'],
                'kat' => $validated['kat'],
                'tanggaltanam' => $validated['tanggaltanam'],
                'tanggalpengamatan' => $validated['tanggalpengamatan'],
                'inputby' => Auth::user()->userid,
                'createdat' => now(),
                'updatedat' => now(),
            ]);

            $totalPerPPT = 0;
            $totalPerPBT = 0;
            $count = count($validated['lists']);

            foreach ($validated['lists'] as $list) {
                $jumlahbatang = $list['skor0'] + $list['skor1'] + $list['skor2'] + $list['skor3'] + $list['skor4'];
                $ppt = ($list['larva_ppt1'] + $list['larva_ppt2'] + $list['larva_ppt3'] + $list['larva_ppt4'] + $list['pupa_ppt']) + $list['kosong_ppt'];
                $pbt = $list['skor1'] + $list['skor2'] + $list['skor3'] + $list['skor4'];
                $sum_ni = $list['skor0'] * 0 + $list['skor1'] * 1 + $list['skor2'] * 2 + $list['skor3'] * 3 + $list['skor4'] * 4;
                $per_ppt = $jumlahbatang != 0 ? $ppt / $jumlahbatang : 0;
                $per_pbt = $jumlahbatang != 0 ? $pbt / $jumlahbatang : 0;
                // $umur_tanam = round($validated['tanggaltanam'] ? Carbon::parse($validated['tanggaltanam'])->diffInMonths(Carbon::now()) : null);

                DB::table('hptlst')->insert([
                    'nosample' => $validated['nosample'],
                    'companycode' => $validated['companycode'],
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'tanggalpengamatan' => $validated['tanggalpengamatan'],
                    'kat' => $validated['kat'],
                    'nourut' => $list['nourut'],
                    'jumlahbatang' => $jumlahbatang,
                    'ppt' => $ppt,
                    'ppt_aktif' => $list['ppt_aktif'],
                    'pbt' => $pbt,
                    'pbt_aktif' => $list['pbt_aktif'],
                    'skor0' => $list['skor0'],
                    'skor1' => $list['skor1'],
                    'skor2' => $list['skor2'],
                    'skor3' => $list['skor3'],
                    'skor4' => $list['skor4'],
                    'per_ppt' => $per_ppt,
                    'per_ppt_aktif' => $jumlahbatang != 0 ? $list['ppt_aktif'] / $jumlahbatang : 0,
                    'per_pbt' => $per_pbt,
                    'per_pbt_aktif' => $jumlahbatang != 0 ? $list['pbt_aktif'] / $jumlahbatang : 0,
                    'sum_ni' => $sum_ni,
                    'int_rusak' => $jumlahbatang != 0 ? $sum_ni / ($jumlahbatang * 4) : 0,
                    'telur_ppt' => $list['telur_ppt'],
                    'larva_ppt1' => $list['larva_ppt1'],
                    'larva_ppt2' => $list['larva_ppt2'],
                    'larva_ppt3' => $list['larva_ppt3'],
                    'larva_ppt4' => $list['larva_ppt4'],
                    'pupa_ppt' => $list['pupa_ppt'],
                    'ngengat_ppt' => $list['ngengat_ppt'],
                    'kosong_ppt' => $list['kosong_ppt'],
                    'telur_pbt' => $list['telur_pbt'],
                    'larva_pbt1' => $list['larva_pbt1'],
                    'larva_pbt2' => $list['larva_pbt2'],
                    'larva_pbt3' => $list['larva_pbt3'],
                    'larva_pbt4' => $list['larva_pbt4'],
                    'pupa_pbt' => $list['pupa_pbt'],
                    'ngengat_pbt' => $list['ngengat_pbt'],
                    'kosong_pbt' => $list['kosong_pbt'],
                    'dh' => $list['dh'],
                    'dt' => $list['dt'],
                    'kbp' => $list['kbp'],
                    'kbb' => $list['kbb'],
                    'kp' => $list['kp'],
                    'cabuk' => $list['cabuk'],
                    'belalang' => $list['belalang'],
                    'serang_grayak' => $list['serang_grayak'],
                    'jum_grayak' => $list['jum_grayak'],
                    'serang_smut' => $list['serang_smut'],
                    'smut_stadia1' => $list['smut_stadia1'],
                    'smut_stadia2' => $list['smut_stadia2'],
                    'smut_stadia3' => $list['smut_stadia3'],
                    'jum_larva_ppt' => $list['larva_ppt1'] + $list['larva_ppt2'] + $list['larva_ppt3'] + $list['larva_ppt4'],
                    'jum_larva_pbt' => $list['larva_pbt1'] + $list['larva_pbt2'] + $list['larva_pbt3'] + $list['larva_pbt4'],
                    'inputby' => Auth::user()->userid,
                    'createdat' => now(),
                    'updatedat' => now()
                ]);
                $totalPerPPT += $per_ppt;
                $totalPerPBT += $per_pbt;
            }

            $avgPPT = $count > 0 ? $totalPerPPT / $count : 0;
            $avgPBT = $count > 0 ? $totalPerPBT / $count : 0;
            $umurTanam = $validated['tanggaltanam'] ? Carbon::parse($validated['tanggaltanam'])->diffInMonths(Carbon::now()) : null;

            if (
                ($avgPBT > 0.03 && $umurTanam >= 1 && $umurTanam <= 3) ||
                ($avgPPT > 0.03 && $umurTanam >= 1 && $umurTanam <= 3) ||
                ($avgPBT > 0.05 && $umurTanam >= 4) ||
                ($avgPPT > 0.05 && $umurTanam >= 4)
            ) {
                Notification::createForHPT([
                    'plot' => $validated['plot'],
                    'companycode' => $validated['companycode'],
                    'condition' => [
                        'ppt' => $avgPPT,
                        'pbt' => $avgPBT,
                        'umur' => $umurTanam,
                    ]
                ]);
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Data created successfully.');
        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()->route('transaction.hpt.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }


    public function show($nosample, $companycode, $tanggalpengamatan)
    {
        $hpt = DB::table('hpthdr')
            ->where('companycode', '=', session('companycode'))
            ->where('nosample', $nosample)
            ->where('tanggalpengamatan', $tanggalpengamatan)
            ->first();

        if (!$hpt) {
            abort(404, 'HPT header not found');
        }

        $hptLists = DB::table('hptlst')
            ->leftJoin('hpthdr', function ($join) use ($hpt) {
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
            ->select(
                'hptlst.*',
                'hpthdr.varietas',
                'hpthdr.kat',
                'hpthdr.tanggaltanam',
                'company.name as compName',
                'blok.blok as blokName',
                'batch.plot as plotName',
                'batch.batcharea as luasarea',
            )
            ->where('hptlst.nosample', $nosample)
            ->where('hptlst.companycode', $companycode)
            ->where('hptlst.tanggalpengamatan', $tanggalpengamatan)
            ->where('batch.isactive', 1)
            ->orderBy('hptlst.nourut', 'asc')
            ->get();

        $now = Carbon::now();

        $hptLists = $hptLists->map(function ($item) use ($now) {
            $tgl_tanam = Carbon::parse($item->tanggaltanam);
            $item->umur_tanam = round($tgl_tanam->diffInMonths($now));
            return $item;
        });

        foreach ($hptLists as $index => $item) {
            $item->no = $index + 1;
        }

        return response()->json($hptLists);
    }

    public function edit($nosample, $companycode, $tanggalpengamatan)
    {
        $title = 'Edit Data';
        $header = DB::table('hpthdr')
            ->where('nosample', $nosample)
            ->where('companycode', $companycode)
            ->where('tanggalpengamatan', $tanggalpengamatan)
            ->firstOrFail();
        $lists = DB::table('hptlst')->where('nosample', $nosample)
            ->where('companycode', $companycode)
            ->where('tanggalpengamatan', $tanggalpengamatan)
            ->get();

        $list = $lists->first();
        $header->lists = $lists;

        $company = DB::table('company')->get();
        $mapping = DB::table('mapping')->get();
        $method = 'PUT';
        $buttonSubmit = 'Update';
        $url = route('transaction.hpt.update', ['nosample' => $nosample, 'companycode' => $companycode, 'tanggalpengamatan' => $tanggalpengamatan]);

        if ($header->status === "Posted") {
            return redirect()->route('transaction.hpt.index')->with('success', 'Data telah di posting, tidak dapat mengakses edit.');
        }

        return view('transaction.hpt.form', compact('buttonSubmit', 'header', 'list', 'company', 'mapping', 'title', 'method', 'url'));
    }

    public function update(Request $request, $nosample, $companycode, $tanggalpengamatan)
    {
        $validated = $request->validate($this->requestValidated());

        DB::beginTransaction();

        try {
            DB::table('hpthdr')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)
                ->update([
                    'nosample' => $validated['nosample'],
                    'companycode' => $validated['companycode'],
                    'blok' => $validated['blok'],
                    'plot' => $validated['plot'],
                    // 'idblokplot' => $validated['idblokplot'],
                    'varietas' => $validated['varietas'],
                    'kat' => $validated['kat'],
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'tanggalpengamatan' => $validated['tanggalpengamatan'],
                    'updatedat' => now(),
                ]);

            $lists = DB::table('hptlst')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan);

            $saved = DB::table('hptlst')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)
                ->first();

            $createdAt = $saved->createdat;
            $userInput = $saved->inputby;

            $lists->delete();

            foreach ($validated['lists'] as $list) {
                $jumlahbatang = $list['skor0'] + $list['skor1'] + $list['skor2'] + $list['skor3'] + $list['skor4'];
                $ppt = ($list['larva_ppt1'] + $list['larva_ppt2'] + $list['larva_ppt3'] + $list['larva_ppt4'] + $list['pupa_ppt']) + $list['kosong_ppt'];
                $pbt = $list['skor1'] + $list['skor2'] + $list['skor3'] + $list['skor4'];
                $sum_ni = $list['skor0'] * 0 + $list['skor1'] * 1 + $list['skor2'] * 2 + $list['skor3'] * 3 + $list['skor4'] * 4;
                $data = [
                    'nosample' => $validated['nosample'],
                    'companycode' => $validated['companycode'],
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'tanggalpengamatan' => $validated['tanggalpengamatan'],
                    'kat' => $validated['kat'],
                    'nourut' => $list['nourut'],
                    'jumlahbatang' => $jumlahbatang,
                    'ppt' => $ppt,
                    'ppt_aktif' => $list['ppt_aktif'],
                    'pbt' => $pbt,
                    'pbt_aktif' => $list['pbt_aktif'],
                    'skor0' => $list['skor0'],
                    'skor1' => $list['skor1'],
                    'skor2' => $list['skor2'],
                    'skor3' => $list['skor3'],
                    'skor4' => $list['skor4'],
                    'per_ppt' => $jumlahbatang != 0 ? $ppt / $jumlahbatang : 0,
                    'per_ppt_aktif' => $jumlahbatang != 0 ? $list['ppt_aktif'] / $jumlahbatang : 0,
                    'per_pbt' => $jumlahbatang != 0 ? $pbt / $jumlahbatang : 0,
                    'per_pbt_aktif' => $jumlahbatang != 0 ? $list['pbt_aktif'] / $jumlahbatang : 0,
                    'sum_ni' => $sum_ni,
                    'int_rusak' => $jumlahbatang != 0 ? $sum_ni / ($jumlahbatang * 4) : 0,
                    'telur_ppt' => $list['telur_ppt'],
                    'larva_ppt1' => $list['larva_ppt1'],
                    'larva_ppt2' => $list['larva_ppt2'],
                    'larva_ppt3' => $list['larva_ppt3'],
                    'larva_ppt4' => $list['larva_ppt4'],
                    'pupa_ppt' => $list['pupa_ppt'],
                    'ngengat_ppt' => $list['ngengat_ppt'],
                    'kosong_ppt' => $list['kosong_ppt'],
                    'telur_pbt' => $list['telur_pbt'],
                    'larva_pbt1' => $list['larva_pbt1'],
                    'larva_pbt2' => $list['larva_pbt2'],
                    'larva_pbt3' => $list['larva_pbt3'],
                    'larva_pbt4' => $list['larva_pbt4'],
                    'pupa_pbt' => $list['pupa_pbt'],
                    'ngengat_pbt' => $list['ngengat_pbt'],
                    'kosong_pbt' => $list['kosong_pbt'],
                    'dh' => $list['dh'],
                    'dt' => $list['dt'],
                    'kbp' => $list['kbp'],
                    'kbb' => $list['kbb'],
                    'kp' => $list['kp'],
                    'cabuk' => $list['cabuk'],
                    'belalang' => $list['belalang'],
                    'serang_grayak' => $list['serang_grayak'],
                    'jum_grayak' => $list['jum_grayak'],
                    'serang_smut' => $list['serang_smut'],
                    'smut_stadia1' => $list['smut_stadia1'],
                    'smut_stadia2' => $list['smut_stadia2'],
                    'smut_stadia3' => $list['smut_stadia3'],
                    'jum_larva_ppt' => $list['larva_ppt1'] + $list['larva_ppt2'] + $list['larva_ppt3'] + $list['larva_ppt4'],
                    'jum_larva_pbt' => $list['larva_pbt1'] + $list['larva_pbt2'] + $list['larva_pbt3'] + $list['larva_pbt4'],
                    'inputby' => $userInput,
                    'createdat' => $createdAt,
                    'updatedat' => now(),
                ];


                DB::table('hptlst')
                    ->where('nosample', $nosample)
                    ->where('companycode', $companycode)
                    ->where('tanggalpengamatan', $tanggalpengamatan)
                    ->where('nourut', $list['nourut'])
                    ->insert($data);
            }

            DB::commit();

            return redirect()->route('transaction.hpt.index')
                ->with('success', 'Data updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('transaction.hpt.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function destroy($nosample, $companycode, $tanggalpengamatan)
    {
        DB::transaction(function () use ($nosample, $companycode, $tanggalpengamatan) {
            DB::table('hpthdr')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)
                ->delete();
            DB::table('hptlst')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)
                ->delete();
        });
        return redirect()->route('transaction.hpt.index')
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
            'search' => 'nullable|string'
        ]);

        $query = DB::table('hptlst')
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
            ->select(
                'hptlst.*',
                'hpthdr.varietas',
                'hpthdr.kat',
                'hpthdr.tanggalpengamatan',
                'hpthdr.tanggaltanam',
                'company.name as compName',
                'blok.blok as blokName',
                'batch.plot as plotName',
                'batch.batcharea as luasarea',
            )
            ->orderBy('hpthdr.tanggalpengamatan', 'desc');

        if ($startDate) {
            $query->whereDate('hpthdr.tanggalpengamatan', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('hpthdr.tanggalpengamatan', '<=', $endDate);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('hptlst.nosample', 'like', "%{$search}%")
                    ->orWhere('plotting.plot', 'like', "%{$search}%")
                    ->orWhere('hpthdr.varietas', 'like', "%{$search}%")
                    ->orWhere('hpthdr.kat', 'like', "%{$search}%");
            });
        }

        $now = Carbon::now();

        // Tentukan nama file
        if ($startDate && $endDate) {
            $filename = "HPTReport_{$startDate}_sd_{$endDate}.xlsx";
        } else {
            $filename = "HPTReport.xlsx";
        }

        // Buat direktori temp jika belum ada
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempFile = $tempDir . '/' . $filename;

        // Buat writer dengan Spout dan set temp folder
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->setTempFolder($tempDir);
        $writer->openToFile($tempFile);

        // Style untuk header (bold)
        $headerStyle = (new StyleBuilder())
            ->setFontBold()
            ->build();

        // Buat header row
        $headerCells = [
            WriterEntityFactory::createCell('No. Sample'),
            WriterEntityFactory::createCell('Kebun'),
            WriterEntityFactory::createCell('Blok'),
            WriterEntityFactory::createCell('Plot'),
            WriterEntityFactory::createCell('Luas'),
            WriterEntityFactory::createCell('Tanggal Tanam'),
            WriterEntityFactory::createCell('Umur Tanam'),
            WriterEntityFactory::createCell('Varietas'),
            WriterEntityFactory::createCell('Kategori'),
            WriterEntityFactory::createCell('Tanggal Pengamatan'),
            WriterEntityFactory::createCell('Bulan Pengamatan'),
            WriterEntityFactory::createCell('No. Urut'),
            WriterEntityFactory::createCell('Jumlah Batang'),
            WriterEntityFactory::createCell('PPT'),
            WriterEntityFactory::createCell('PPT Aktif'),
            WriterEntityFactory::createCell('PBT'),
            WriterEntityFactory::createCell('PBT Aktif'),
            WriterEntityFactory::createCell('Skor 0'),
            WriterEntityFactory::createCell('Skor 1'),
            WriterEntityFactory::createCell('Skor 2'),
            WriterEntityFactory::createCell('Skor 3'),
            WriterEntityFactory::createCell('Skor 4'),
            WriterEntityFactory::createCell('%PPT'),
            WriterEntityFactory::createCell('%PPT Aktif'),
            WriterEntityFactory::createCell('%PBT'),
            WriterEntityFactory::createCell('%PBT Aktif'),
            WriterEntityFactory::createCell('Σni*vi'),
            WriterEntityFactory::createCell('Intensitas Kerusakan'),
            WriterEntityFactory::createCell('Telur PPT'),
            WriterEntityFactory::createCell('Larva PPT 1'),
            WriterEntityFactory::createCell('Larva PPT 2'),
            WriterEntityFactory::createCell('Larva PPT 3'),
            WriterEntityFactory::createCell('Larva PPT 4'),
            WriterEntityFactory::createCell('Pupa PPT'),
            WriterEntityFactory::createCell('Ngengat PPT'),
            WriterEntityFactory::createCell('Kosong PPT'),
            WriterEntityFactory::createCell('Telur PBT'),
            WriterEntityFactory::createCell('Larva PBT 1'),
            WriterEntityFactory::createCell('Larva PBT 2'),
            WriterEntityFactory::createCell('Larva PBT 3'),
            WriterEntityFactory::createCell('Larva PBT 4'),
            WriterEntityFactory::createCell('Pupa PBT'),
            WriterEntityFactory::createCell('Ngengat PBT'),
            WriterEntityFactory::createCell('Kosong PBT'),
            WriterEntityFactory::createCell('DH'),
            WriterEntityFactory::createCell('DT'),
            WriterEntityFactory::createCell('KBP'),
            WriterEntityFactory::createCell('KBB'),
            WriterEntityFactory::createCell('KP'),
            WriterEntityFactory::createCell('Cabuk'),
            WriterEntityFactory::createCell('Belalang'),
            WriterEntityFactory::createCell('BTG Terserang Ul.Grayak'),
            WriterEntityFactory::createCell('Jumlah Ul.Grayak'),
            WriterEntityFactory::createCell('BTG Terserang SMUT'),
            WriterEntityFactory::createCell('SMUT Stadia 1'),
            WriterEntityFactory::createCell('SMUT Stadia 2'),
            WriterEntityFactory::createCell('SMUT Stadia 3'),
            WriterEntityFactory::createCell('Jumlah Larva PPT'),
            WriterEntityFactory::createCell('Jumlah Larva PBT'),
        ];

        $headerRow = WriterEntityFactory::createRow($headerCells, $headerStyle);
        $writer->addRow($headerRow);

        // Proses data dalam chunk untuk efisiensi memori
        $query->chunk(1000, function ($hptChunk) use ($writer, $now) {
            $rows = [];

            foreach ($hptChunk as $list) {
                $tanggaltanam = Carbon::parse($list->tanggaltanam);
                $umurTanam = $tanggaltanam->diffInMonths($now);

                $tanggalpengamatan = Carbon::parse($list->tanggalpengamatan);
                $bulanPengamatan = $tanggalpengamatan->format('F');

                // // Format persentase (konversi ke desimal untuk Excel)
                // $perPpt = is_numeric($list->per_ppt) ? $list->per_ppt / 100 : $list->per_ppt;
                // $perPptAktif = is_numeric($list->per_ppt_aktif) ? $list->per_ppt_aktif / 100 : $list->per_ppt_aktif;
                // $perPbt = is_numeric($list->per_pbt) ? $list->per_pbt / 100 : $list->per_pbt;
                // $perPbtAktif = is_numeric($list->per_pbt_aktif) ? $list->per_pbt_aktif / 100 : $list->per_pbt_aktif;
                // $intRusak = is_numeric($list->int_rusak) ? $list->int_rusak / 100 : $list->int_rusak;

                $decimalStyle = (new StyleBuilder())
                    ->setFormat('0.000000000')
                    ->build();

                $cells = [
                    WriterEntityFactory::createCell($list->nosample),
                    WriterEntityFactory::createCell($list->compName),
                    WriterEntityFactory::createCell($list->blokName),
                    WriterEntityFactory::createCell($list->plotName),
                    WriterEntityFactory::createCell(round((float) $list->luasarea, 10)),
                    WriterEntityFactory::createCell($tanggaltanam->format('Y-m-d')),
                    WriterEntityFactory::createCell(round($umurTanam) . ' Bulan'),
                    WriterEntityFactory::createCell($list->varietas),
                    WriterEntityFactory::createCell($list->kat),
                    WriterEntityFactory::createCell($list->tanggalpengamatan),
                    WriterEntityFactory::createCell($bulanPengamatan),
                    WriterEntityFactory::createCell($list->nourut),
                    WriterEntityFactory::createCell($list->jumlahbatang),
                    WriterEntityFactory::createCell($list->ppt),
                    WriterEntityFactory::createCell($list->ppt_aktif),
                    WriterEntityFactory::createCell($list->pbt),
                    WriterEntityFactory::createCell($list->pbt_aktif),
                    WriterEntityFactory::createCell($list->skor0),
                    WriterEntityFactory::createCell($list->skor1),
                    WriterEntityFactory::createCell($list->skor2),
                    WriterEntityFactory::createCell($list->skor3),
                    WriterEntityFactory::createCell($list->skor4),
                    WriterEntityFactory::createCell(round((float) $list->per_ppt, 10), $decimalStyle),
                    WriterEntityFactory::createCell(round((float) $list->per_ppt_aktif, 10), $decimalStyle),
                    WriterEntityFactory::createCell(round((float) $list->per_pbt, 10), $decimalStyle),
                    WriterEntityFactory::createCell(round((float) $list->per_pbt_aktif, 10), $decimalStyle),
                    WriterEntityFactory::createCell($list->sum_ni),
                    WriterEntityFactory::createCell(round((float) $list->int_rusak, 10), $decimalStyle),
                    WriterEntityFactory::createCell($list->telur_ppt),
                    WriterEntityFactory::createCell($list->larva_ppt1),
                    WriterEntityFactory::createCell($list->larva_ppt2),
                    WriterEntityFactory::createCell($list->larva_ppt3),
                    WriterEntityFactory::createCell($list->larva_ppt4),
                    WriterEntityFactory::createCell($list->pupa_ppt),
                    WriterEntityFactory::createCell($list->ngengat_ppt),
                    WriterEntityFactory::createCell($list->kosong_ppt),
                    WriterEntityFactory::createCell($list->telur_pbt),
                    WriterEntityFactory::createCell($list->larva_pbt1),
                    WriterEntityFactory::createCell($list->larva_pbt2),
                    WriterEntityFactory::createCell($list->larva_pbt3),
                    WriterEntityFactory::createCell($list->larva_pbt4),
                    WriterEntityFactory::createCell($list->pupa_pbt),
                    WriterEntityFactory::createCell($list->ngengat_pbt),
                    WriterEntityFactory::createCell($list->kosong_pbt),
                    WriterEntityFactory::createCell($list->dh),
                    WriterEntityFactory::createCell($list->dt),
                    WriterEntityFactory::createCell($list->kbp),
                    WriterEntityFactory::createCell($list->kbb),
                    WriterEntityFactory::createCell($list->kp),
                    WriterEntityFactory::createCell($list->cabuk),
                    WriterEntityFactory::createCell($list->belalang),
                    WriterEntityFactory::createCell($list->serang_grayak),
                    WriterEntityFactory::createCell($list->jum_grayak),
                    WriterEntityFactory::createCell($list->serang_smut),
                    WriterEntityFactory::createCell($list->smut_stadia1),
                    WriterEntityFactory::createCell($list->smut_stadia2),
                    WriterEntityFactory::createCell($list->smut_stadia3),
                    WriterEntityFactory::createCell($list->jum_larva_ppt),
                    WriterEntityFactory::createCell($list->jum_larva_pbt),
                ];

                $rows[] = WriterEntityFactory::createRow($cells);
            }

            // Tulis semua rows dalam chunk sekaligus
            $writer->addRows($rows);

            // Bebaskan memori
            unset($rows);
            gc_collect_cycles();
        });

        $writer->close();

        // Return file sebagai download dan hapus setelah dikirim
        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ])->deleteFileAfterSend(true);
    }
}
