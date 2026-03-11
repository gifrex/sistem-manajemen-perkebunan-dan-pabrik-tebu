<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use App\Models\MasterData\Herbisida;
use App\Models\MasterData\HerbisidaGroup;
use App\Models\MasterData\HerbisidaDosage;
use Illuminate\Support\Facades\DB;

class HerbisidaGroupController extends Controller
{
    
    public function home(Request $request)
    {
        $companycode = Session::get('companycode');
        $search = $request->get('search');
        $perPage = (int)$request->get('perPage', 10);
        
        $nextId = (Herbisidagroup::max('herbisidagroupid') ?? 0) + 1;
        
        $activities = DB::table('activity')
            ->select('activitycode', 'activityname')
            ->orderBy('activitycode')
            ->get();
        
        $herbisidaItems = Herbisida::where('companycode', $companycode)
            ->select('itemcode', 'itemname', 'measure')
            ->orderBy('itemname')
            ->get();

        // Show ALL groups (not just ones with dosage for this company)
        $query = Herbisidagroup::query();
        
        if ($search) {
            $query->where(function($q) use ($search, $companycode) {
                $q->where('herbisidagroupname', 'like', "%{$search}%")
                  ->orWhere('activitycode', 'like', "%{$search}%")
                  ->orWhere('herbisidagroupid', 'like', "%{$search}%")
                  ->orWhereExists(function($subq) use ($search, $companycode) {
                      $subq->select(DB::raw(1))
                           ->from('herbisidadosage as b')
                           ->leftJoin('herbisida as c', function($join) use ($companycode) {
                               $join->on('b.itemcode', '=', 'c.itemcode')
                                    ->where('c.companycode', '=', $companycode);
                           })
                           ->whereColumn('b.herbisidagroupid', 'herbisidagroup.herbisidagroupid')
                           ->where('b.companycode', $companycode)
                           ->where(function($w) use ($search) {
                               $w->where('b.itemcode', 'like', "%{$search}%")
                                 ->orWhere('c.itemname', 'like', "%{$search}%");
                           });
                  });
            });
        }
        
        $groupIds = $query->orderBy('herbisidagroupid')->paginate($perPage);
        
        // LEFT JOIN dosage — groups without dosage will have NULL item columns
        $grouping = HerbisidaGroup::query()
            ->leftJoin('herbisidadosage as b', function($join) use ($companycode) {
                $join->on('herbisidagroup.herbisidagroupid', '=', 'b.herbisidagroupid')
                     ->where('b.companycode', '=', $companycode);
            })
            ->leftJoin('herbisida as c', function($join) use ($companycode) {
                $join->on('b.itemcode', '=', 'c.itemcode')
                     ->where('c.companycode', '=', $companycode);
            })
            ->select('herbisidagroup.*', 'b.itemcode', 'b.dosageperha', 'c.itemname')
            ->whereIn('herbisidagroup.herbisidagroupid', $groupIds->pluck('herbisidagroupid'))
            ->orderBy('herbisidagroup.herbisidagroupid')
            ->orderBy('b.itemcode')
            ->get();
        
        $grouping = new \Illuminate\Pagination\LengthAwarePaginator(
            $grouping,
            $groupIds->total(),
            $groupIds->perPage(),
            $groupIds->currentPage(),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('masterdata.herbisidagroup.index', [
            'title'           => 'Data Herbisida Group',
            'navbar'          => 'Master',
            'nav'             => 'Herbisida Group',
            'grouping'        => $grouping,
            'herbisidaItems'  => $herbisidaItems,
            'activities'      => $activities,
            'nextId'          => $nextId
        ]);
    }

    public function insert(Request $request)
    {
        $companycode = Session::get('companycode');
        
        $request->validate([
            'herbisidagroupid'    => 'required|integer',
            'herbisidagroupname'  => 'required|max:100',
            'activitycode'        => 'required|exists:activity,activitycode',
            'description'         => 'nullable',
            'rounddosage'         => 'nullable|in:0,1',
            'items'               => 'required|array|min:1',
            'items.*.itemcode'    => 'required|exists:herbisida,itemcode',
            'items.*.dosageperha' => 'required|numeric|min:0'
        ]);

        // Check duplicate group ID
        if (Herbisidagroup::where('herbisidagroupid', $request->herbisidagroupid)->exists()) {
            return back()->withInput()->with('error', 'Group ID sudah ada!');
        }

        // Check duplicate name + activity (enforced by DB unique constraint too)
        $duplicateName = Herbisidagroup::where('herbisidagroupname', $request->herbisidagroupname)
            ->where('activitycode', $request->activitycode)
            ->exists();

        if ($duplicateName) {
            return back()->withInput()->with('error', 
                'Group dengan nama "' . $request->herbisidagroupname . '" untuk aktivitas ' . $request->activitycode . ' sudah ada! Gunakan group yang sudah ada dan tambahkan dosage untuk company Anda.');
        }

        // Check duplicate item codes within submitted items
        $itemCodes = array_column($request->items, 'itemcode');
        if (count($itemCodes) !== count(array_unique($itemCodes))) {
            return back()->withInput()->with('error', 'Kode Item Duplikat!');
        }

        $rounddosage = (int) $request->input('rounddosage', 0) === 1 ? 1 : 0;

        DB::beginTransaction();
        try {
            $group = Herbisidagroup::create([
                'herbisidagroupid'   => $request->herbisidagroupid,
                'herbisidagroupname' => $request->herbisidagroupname,
                'activitycode'       => $request->activitycode,
                'description'        => $request->description,
                'rounddosage'        => $rounddosage,
            ]);
            
            foreach ($request->items as $item) {
                Herbisidadosage::create([
                    'herbisidagroupid' => $group->herbisidagroupid,
                    'itemcode'         => $item['itemcode'],
                    'dosageperha'      => $item['dosageperha'],
                    'companycode'      => $companycode,
                    'inputby'          => Auth::user()->userid,
                    'createdat'        => now(),
                ]);
            }
            
            DB::commit();
            return redirect()->route('masterdata.herbisida-group.index')->with('success', 'Grup Berhasil Dibuat!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal Buat Grup: ' . $e->getMessage());
        }
    }

    public function edit(Request $request, $id)
    {
        $companycode = Session::get('companycode');
        $group = Herbisidagroup::findOrFail($id);

        $request->validate([
            'herbisidagroupname'  => 'required|max:100',
            'activitycode'        => 'required|exists:activity,activitycode',
            'description'         => 'nullable',
            'rounddosage'         => 'nullable|in:0,1',
            'items'               => 'required|array|min:1',
            'items.*.itemcode'    => 'required|exists:herbisida,itemcode',
            'items.*.dosageperha' => 'required|numeric|min:0'
        ]);

        // Check duplicate name + activity (exclude current group)
        $duplicateName = Herbisidagroup::where('herbisidagroupname', $request->herbisidagroupname)
            ->where('activitycode', $request->activitycode)
            ->where('herbisidagroupid', '!=', $id)
            ->exists();

        if ($duplicateName) {
            return back()->withInput()->with('error', 
                'Group dengan nama "' . $request->herbisidagroupname . '" untuk aktivitas ' . $request->activitycode . ' sudah ada!');
        }

        // Check duplicate item codes
        $itemCodes = array_column($request->items, 'itemcode');
        if (count($itemCodes) !== count(array_unique($itemCodes))) {
            return back()->withInput()->with('error', 'Kode Item Duplikat!');
        }

        $rounddosage = (int) $request->input('rounddosage', 0) === 1 ? 1 : 0;

        DB::beginTransaction();
        try {
            DB::table('herbisidagroup')
                ->where('herbisidagroupid', $id)
                ->update([
                    'herbisidagroupname' => $request->herbisidagroupname,
                    'activitycode'       => $request->activitycode,
                    'description'        => $request->description,
                    'rounddosage'        => $rounddosage,
                ]);
            
            // Only delete dosage for current company (don't touch other companies!)
            Herbisidadosage::where('herbisidagroupid', $id)
                ->where('companycode', $companycode)
                ->delete();
            
            foreach ($request->items as $item) {
                Herbisidadosage::create([
                    'herbisidagroupid' => $id,
                    'itemcode'         => $item['itemcode'],
                    'dosageperha'      => $item['dosageperha'],
                    'companycode'      => $companycode,
                    'updateby'         => Auth::user()->userid,
                    'updatedat'        => now(),
                ]);
            }
            
            DB::commit();
            return redirect()->route('masterdata.herbisida-group.index')->with('success', 'Edit Sukses!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal Update Group: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $companycode = Session::get('companycode');
        $group = Herbisidagroup::findOrFail($id);
        
        // Check if used in rkhlst
        $isUsed = DB::table('rkhlst')
                    ->where('activitycode', $group->activitycode)
                    ->where('herbisidagroupid', $group->herbisidagroupid)
                    ->exists();
        
        if ($isUsed) {
            return back()->with('error', 'Gagal Hapus! Sudah ada di RKH!');
        }

        // Check if other companies still use this group's dosage
        $otherCompanyCount = Herbisidadosage::where('herbisidagroupid', $id)
            ->where('companycode', '!=', $companycode)
            ->count();

        DB::beginTransaction();
        try {
            // Always delete current company's dosage
            Herbisidadosage::where('herbisidagroupid', $id)
                ->where('companycode', $companycode)
                ->delete();

            // Only delete the group header if NO other company uses it
            if ($otherCompanyCount === 0) {
                // Also clean up any remaining dosage (safety)
                Herbisidadosage::where('herbisidagroupid', $id)->delete();
                $group->delete();
            }
            
            DB::commit();

            $msg = $otherCompanyCount > 0 
                ? 'Dosage untuk company Anda berhasil dihapus. Group masih digunakan oleh company lain.'
                : 'Group berhasil dihapus sepenuhnya.';

            return redirect()->route('masterdata.herbisida-group.index')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal Hapus Group: ' . $e->getMessage());
        }
    }
}