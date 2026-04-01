<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

use App\Models\MasterData\CostCenter;
use App\Models\MasterData\HerbisidaGroup;

class CostcenterController extends Controller
{
    /**
     * Halaman utama / list cost center
     */
    public function index(Request $request)
    {
        $search      = $request->input('search');
        $companycode = Session::get('companycode');

        $query = HerbisidaGroup::query()
            ->leftJoin('costcenter as cc', function ($join) use ($companycode) {
                $join->on('herbisidagroup.herbisidagroupid', '=', 'cc.herbisidagroupid')
                     ->where('cc.companycode', '=', $companycode);
            })
            ->select([
                'herbisidagroup.herbisidagroupid',
                'herbisidagroup.herbisidagroupname',
                'herbisidagroup.activitycode',
                DB::raw('cc.costcenter as costcenter'),
                DB::raw('cc.description as description'),
            ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('herbisidagroup.herbisidagroupid', 'like', "%{$search}%")
                  ->orWhere('herbisidagroup.herbisidagroupname', 'like', "%{$search}%")
                  ->orWhere('herbisidagroup.activitycode', 'like', "%{$search}%")
                  ->orWhere('cc.costcenter', 'like', "%{$search}%")
                  ->orWhere('cc.description', 'like', "%{$search}%");
            });
        }

        $costcenter = $query
            ->orderBy('herbisidagroup.herbisidagroupid')
            ->get();

        return view('masterdata.costcenter.index', [
            'costcenter' => $costcenter,
            'title'      => 'Data Cost Center',
            'navbar'     => 'Master',
            'nav'        => 'Cost Center',
            'search'     => $search,
        ]);
    }

    /**
     * Simpan data baru
     */
    public function store(Request $request)
    {
        $companycode = Session::get('companycode');

        $request->validate([
            'herbisidagroupid' => 'required|string|max:10',
            'costcenter'       => 'required|string|max:20',
            'description'      => 'required|string|max:100',
        ]);

        $groupExists = HerbisidaGroup::where('herbisidagroupid', $request->herbisidagroupid)
            ->exists();

        if (!$groupExists) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'herbisidagroupid' => 'Herbisida Group tidak ditemukan'
                ]);
        }

        $exists = CostCenter::where('companycode', $companycode)
            ->where('herbisidagroupid', $request->herbisidagroupid)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'herbisidagroupid' => 'Data Cost Center untuk Herbisida Group ini sudah ada'
                ]);
        }

        CostCenter::create([
            'companycode'      => $companycode,
            'herbisidagroupid' => $request->input('herbisidagroupid'),
            'costcenter'       => strtoupper($request->input('costcenter')),
            'description'      => $request->input('description'),
            'inputby'          => Auth::user()->userid,
            'createdat'        => now(),
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }

    /**
     * Update data existing / simpan pertama kali jika belum ada
     */
    public function update(Request $request, $herbisidagroupid)
    {
        $companycode = Session::get('companycode');

        $request->validate([
            'costcenter'  => 'required|string|max:20',
            'description' => 'required|string|max:100',
        ]);

        $groupExists = HerbisidaGroup::where('herbisidagroupid', $herbisidagroupid)
            ->exists();

        if (!$groupExists) {
            abort(404, 'Herbisida Group tidak ditemukan');
        }

        $costCenter = CostCenter::where([
            ['companycode', $companycode],
            ['herbisidagroupid', $herbisidagroupid]
        ])->first();

        if (!$costCenter) {
            CostCenter::create([
                'companycode'      => $companycode,
                'herbisidagroupid' => $herbisidagroupid,
                'costcenter'       => strtoupper($request->input('costcenter')),
                'description'      => $request->input('description'),
                'inputby'          => Auth::user()->userid,
                'createdat'        => now(),
            ]);

            return redirect()->back()->with('success', 'Data berhasil disimpan.');
        }

        CostCenter::where('companycode', $companycode)
            ->where('herbisidagroupid', $herbisidagroupid)
            ->update([
                'costcenter' => strtoupper($request->input('costcenter')),
                'description' => $request->input('description'),
                'updateby'   => Auth::user()->userid,
                'updatedat'  => now(),
            ]);

        return redirect()->back()->with('success', 'Data berhasil di-update.');
    }

    /**
     * Hapus data
     */
    public function destroy($herbisidagroupid)
    {
        $companycode = Session::get('companycode');

        CostCenter::where([
            ['companycode', $companycode],
            ['herbisidagroupid', $herbisidagroupid]
        ])->delete();

        return redirect()->back()->with('success', 'Data berhasil di-hapus.');
    }

    /**
     * API: Get all herbisida group
     * sekaligus status costcenter untuk current company
     */
    public function groups()
    {
        $companycode = Session::get('companycode');

        return HerbisidaGroup::query()
            ->leftJoin('costcenter as cc', function ($join) use ($companycode) {
                $join->on('herbisidagroup.herbisidagroupid', '=', 'cc.herbisidagroupid')
                     ->where('cc.companycode', '=', $companycode);
            })
            ->select([
                'herbisidagroup.herbisidagroupid',
                'herbisidagroup.herbisidagroupname',
                'herbisidagroup.activitycode',
                DB::raw('cc.costcenter as costcenter'),
                DB::raw('cc.description as description'),
            ])
            ->orderBy('herbisidagroup.herbisidagroupid')
            ->get();
    }
}