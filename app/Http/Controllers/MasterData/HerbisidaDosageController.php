<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use App\Models\MasterData\HerbisidaDosage;

class HerbisidaDosageController extends Controller
{
    
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');
        $companycode = Session::get('companycode');

        $qb = DB::table('herbisidadosage as d')
            ->join('herbisida as h', function($join){
                $join->on('d.companycode', '=', 'h.companycode')
                     ->on('d.itemcode',    '=', 'h.itemcode');
            })
            ->join('herbisidagroup as g', 'd.herbisidagroupid', '=', 'g.herbisidagroupid')
            ->join('activity as a', 'a.activitycode', '=', 'g.activitycode')
            ->where('d.companycode', $companycode)
            ->select('d.*', 'h.itemname', 'h.measure', 'g.herbisidagroupid', 'g.herbisidagroupname', 'g.activitycode', 'a.activityname');

        if ($search) {
            $qb->where(function($q) use ($search) {
                $q->where('d.herbisidagroupid', 'like', "%{$search}%")
                  ->orWhere('d.itemcode',     'like', "%{$search}%")
                  ->orWhere('h.itemname',     'like', "%{$search}%")
                  ->orWhere('g.herbisidagroupname', 'like', "%{$search}%");
            });
        }

        $herbisidaDosages = $qb
            ->orderBy('g.herbisidagroupid')
            ->orderBy('d.itemcode')
            ->paginate($perPage)
            ->appends(compact('perPage', 'search'));

        return view('masterdata.herbisidadosage.index', [
            'herbisidaDosages' => $herbisidaDosages,
            'perPage'          => $perPage,
            'search'           => $search,
            'title'            => 'Data Dosis Herbisida',
            'navbar'           => 'Master',
            'nav'              => 'Dosis Herbisida',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'herbisidagroupid' => 'required|string|max:4',
            'itemcode'         => 'required|string|max:30',
            'dosageperha'      => 'required|numeric',
        ]);

        $companycode = Session::get('companycode');

        $exists = HerbisidaDosage::where('companycode', $companycode)
            ->where('herbisidagroupid', intval($request->herbisidagroupid))
            ->where('itemcode', $request->itemcode)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'herbisidagroupid' => 'Duplicate Entry, Data already exists'
                ]);
        }
       
        HerbisidaDosage::create([
            'companycode'      => $companycode,
            'herbisidagroupid' => intval($request->input('herbisidagroupid')),
            'itemcode'         => $request->input('itemcode'),
            'dosageperha'      => $request->input('dosageperha'),
            'inputby'          => Auth::user()->userid,
            'createdat'        => now(),
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, $herbisidagroupid, $itemcode)
    {   
        $companycode = Session::get('companycode');

        $dosage = HerbisidaDosage::where([
            ['companycode', $companycode],
            ['herbisidagroupid', $herbisidagroupid],
            ['itemcode', $itemcode]
        ])->firstOrFail();
        
        $validated = $request->validate([
            'herbisidagroupid' => 'required|string|max:4',
            'itemcode'         => 'required|string|max:30',
            'dosageperha'      => 'required|numeric',
        ]);

        // Check duplicate if group or itemcode changed
        if (intval($request->herbisidagroupid) !== $dosage->herbisidagroupid ||
            $request->itemcode !== $dosage->itemcode) {
            
            $exists = HerbisidaDosage::where('companycode', $companycode)
                ->where('herbisidagroupid', $request->herbisidagroupid)
                ->where('itemcode', $request->itemcode)
                ->exists();
            
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'herbisidagroupid' => 'Duplicate Entry, Data already exists'
                    ]);
            }
        } 
        
        HerbisidaDosage::where([
            ['companycode',      $companycode],
            ['herbisidagroupid', $herbisidagroupid],
            ['itemcode',         $itemcode],
        ])->update([
            'herbisidagroupid' => $validated['herbisidagroupid'],
            'itemcode'         => $validated['itemcode'],
            'dosageperha'      => $validated['dosageperha'],
            'updateby'         => Auth::user()->userid,
            'updatedat'        => now(),
        ]);

        return redirect()->back()->with('success', 'Data berhasil diupdate.');
    }
    
    public function destroy($herbisidagroupid, $itemcode)
    { 
        $companycode = Session::get('companycode');

        HerbisidaDosage::where([
            ['companycode', $companycode],
            ['herbisidagroupid', $herbisidagroupid],
            ['itemcode', $itemcode]
        ])->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }
}