<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use App\Models\MasterData\Herbisida;
use App\Models\MasterData\HerbisidaGroup;

class HerbisidaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');
        $companycode = Session::get('companycode');

        $query = Herbisida::where('companycode', $companycode);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('itemcode', 'like', "%{$search}%")
                  ->orWhere('itemname', 'like', "%{$search}%");
            });
        }

        $herbisida = $query
            ->orderBy('itemcode')
            ->paginate($perPage)
            ->appends(compact('perPage', 'search'));

        return view('masterdata.herbisida.index', [
            'herbisida' => $herbisida,
            'title'     => 'Data Herbisida',
            'navbar'    => 'Master',
            'nav'       => 'Herbisida',
            'perPage'   => $perPage,
            'search'    => $search,
        ]);
    }

    public function store(Request $request)
    {
        $companycode = Session::get('companycode');

        $request->validate([
            'itemcode' => 'required|string|max:30',
            'itemname' => 'required|string|max:50',
            'measure'  => 'required|string|max:10',
        ]);

        $exists = Herbisida::where('companycode', $companycode)
            ->where('itemcode', $request->itemcode)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['itemcode' => 'Duplicate Entry, Item Code already exists']);
        }

        Herbisida::create([
            'companycode' => $companycode,
            'itemcode'    => $request->input('itemcode'),
            'itemname'    => $request->input('itemname'),
            'measure'     => $request->input('measure'),
            'isactive'    => 1,
            'inputby'     => Auth::user()->userid,
            'createdat'   => now(),
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, $companycode, $itemcode)
    {
        $sessionCompanycode = Session::get('companycode');

        if ($companycode !== $sessionCompanycode) {
            abort(403, 'Unauthorized access to company data');
        }

        $herbi = Herbisida::where([
            ['companycode', $companycode],
            ['itemcode', $itemcode]
        ])->firstOrFail();

        $validated = $request->validate([
            'itemcode' => 'required|string|max:30',
            'itemname' => 'required|string|max:50',
            'measure'  => 'required|string|max:10',
        ]);

        if ($request->itemcode !== $herbi->itemcode) {
            $exists = Herbisida::where('companycode', $companycode)
                ->where('itemcode', $request->itemcode)
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['itemcode' => 'Duplicate Entry, Item Code already exists']);
            }
        }

        Herbisida::where('companycode', $companycode)
            ->where('itemcode', $itemcode)
            ->update([
                'itemcode' => $validated['itemcode'],
                'itemname' => $validated['itemname'],
                'measure'  => $validated['measure'],
                'updateby' => Auth::user()->userid,
                'updatedat' => now(),
            ]);

        return redirect()->back()->with('success', 'Data berhasil di‑update.');
    }

    public function destroy($companycode, $itemcode)
    {
        $sessionCompanycode = Session::get('companycode');

        if ($companycode !== $sessionCompanycode) {
            abort(403, 'Unauthorized access to company data');
        }

        Herbisida::where([
            ['companycode', $companycode],
            ['itemcode', $itemcode]
        ])->delete();

        return redirect()->back()->with('success', 'Data berhasil di‑hapus.');
    }

    /**
     * API: Get herbisida items for current company (used by dropdowns)
     */
    public function items()
    {
        $companycode = Session::get('companycode');

        return Herbisida::where('companycode', $companycode)
            ->select('itemcode', 'itemname')
            ->orderBy('itemcode')
            ->get();
    }

    /**
     * API: Get all herbisida groups (used by dropdowns)
     */
    public function group()
    {
        return HerbisidaGroup::select('herbisidagroupid', 'herbisidagroupname', 'activitycode')
            ->orderBy('herbisidagroupid')
            ->get();
    }
}