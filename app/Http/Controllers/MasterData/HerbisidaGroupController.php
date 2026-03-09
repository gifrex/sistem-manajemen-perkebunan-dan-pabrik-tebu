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

        $query = Herbisidagroup::query()
            ->whereExists(function($q) use ($companycode) {
                $q->select(DB::raw(1))
                  ->from('herbisidadosage')
                  ->whereColumn('herbisidadosage.herbisidagroupid', 'herbisidagroup.herbisidagroupid')
                  ->where('herbisidadosage.companycode', $companycode);
            });
        
        if ($search) {
            $query->where(function($q) use ($search, $companycode) {
                $q->where('herbisidagroupname', 'like', "%{$search}%")
                  ->orWhere('activitycode', 'like', "%{$search}%")
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
        
        $grouping = HerbisidaGroup::query()
            ->leftJoin('herbisidadosage as b', 'herbisidagroup.herbisidagroupid', '=', 'b.herbisidagroupid')
            ->leftJoin('herbisida as c', function($join) use ($companycode) {
                $join->on('b.itemcode', '=', 'c.itemcode')
                     ->where('c.companycode', '=', $companycode);
            })
            ->select('herbisidagroup.*', 'b.itemcode', 'b.dosageperha', 'c.itemname')
            ->where('b.companycode', $companycode)
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
        
        $existingId = Herbisidagroup::where('herbisidagroupid', $request->herbisidagroupid)->first();
        if ($existingId) {
            return back()->withInput()->with('error', 'Group ID sudah ada!');
        }

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

        $itemCodes = array_column($request->items, 'itemcode');
        if (count($itemCodes) !== count(array_unique($itemCodes))) {
            return back()->withInput()->with('error', 'Kode Item Duplikat!');
        }

        $rounddosage = (int) $request->input('rounddosage', 0) === 1 ? 1 : 0;

        \Log::info('HerbisidaGroup Insert', [
            'rounddosage_raw'    => $request->input('rounddosage'),
            'rounddosage_final'  => $rounddosage,
        ]);

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
                    'companycode'      => $companycode
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

        $isUsed = DB::table('rkhlst')
            ->where('activitycode', $group->activitycode)
            ->where('herbisidagroupid', $group->herbisidagroupid)
            ->exists();

        if ($isUsed) {
            return back()->with('error', 'Gagal Edit! Group ini sudah digunakan di RKH dan tidak bisa diubah!');
        }

        $itemCodes = array_column($request->items, 'itemcode');
        if (count($itemCodes) !== count(array_unique($itemCodes))) {
            return back()->withInput()->with('error', 'Kode Item Duplikat!');
        }

        $rounddosage = (int) $request->input('rounddosage', 0) === 1 ? 1 : 0;

        \Log::info('HerbisidaGroup Edit', [
            'id'                 => $id,
            'rounddosage_raw'    => $request->input('rounddosage'),
            'rounddosage_final'  => $rounddosage,
        ]);

        DB::beginTransaction();
        try {
            // Force update with query builder to bypass any Eloquent issues
            DB::table('herbisidagroup')
                ->where('herbisidagroupid', $id)
                ->update([
                    'herbisidagroupname' => $request->herbisidagroupname,
                    'activitycode'       => $request->activitycode,
                    'description'        => $request->description,
                    'rounddosage'        => $rounddosage,
                ]);
            
            Herbisidadosage::where('herbisidagroupid', $id)->delete();
            
            foreach ($request->items as $item) {
                Herbisidadosage::create([
                    'herbisidagroupid' => $id,
                    'itemcode'         => $item['itemcode'],
                    'dosageperha'      => $item['dosageperha'],
                    'companycode'      => $companycode
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
        $group = Herbisidagroup::findOrFail($id);
        
        $isUsed = DB::table('rkhlst')
                    ->where('activitycode', $group->activitycode)
                    ->where('herbisidagroupid', $group->herbisidagroupid)
                    ->exists();
        
        if ($isUsed) {
            return back()->with('error', 'Gagal Hapus! Sudah ada di RKH!');
        }
        
        DB::beginTransaction();
        try {
            Herbisidadosage::where('herbisidagroupid', $id)->delete();
            $group->delete();
            
            DB::commit();
            return redirect()->route('masterdata.herbisida-group.index')->with('success', 'Group Sukses Dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal Hapus Group: ' . $e->getMessage());
        }
    }
}