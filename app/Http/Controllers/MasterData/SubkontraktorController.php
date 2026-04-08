<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class SubkontraktorController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');
        $status  = $request->input('status'); // '', '1', '0'
        $companycode = Session::get('companycode');

        $query = DB::table('subkontraktor')
            ->leftJoin('kontraktor', function($join) {
                $join->on('subkontraktor.kontraktorid', '=', 'kontraktor.id')
                     ->on('subkontraktor.companycode', '=', 'kontraktor.companycode');
            })
            ->select(
                'subkontraktor.*',
                'kontraktor.namakontraktor'
            )
            ->where('subkontraktor.companycode', $companycode);

        // Filter by status
        if ($status !== null && $status !== '') {
            $query->where('subkontraktor.isactive', (int) $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('subkontraktor.id', 'like', "%{$search}%")
                  ->orWhere('subkontraktor.namasubkontraktor', 'like', "%{$search}%")
                  ->orWhere('subkontraktor.kontraktorid', 'like', "%{$search}%")
                  ->orWhere('kontraktor.namakontraktor', 'like', "%{$search}%");
            });
        }

        $subkontraktor = $query
            ->orderBy('subkontraktor.id')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search'  => $search,
                'status'  => $status,
            ]);

        // Get list kontraktor untuk dropdown (hanya yang active)
        $kontraktorList = DB::table('kontraktor')
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->orderBy('id')
            ->get();

        return view('masterdata.subkontraktor.index', [
            'subkontraktor' => $subkontraktor,
            'kontraktorList' => $kontraktorList,
            'title'      => 'Data Subkontraktor',
            'navbar'     => 'Master',
            'nav'        => 'Subkontraktor',
            'perPage'    => $perPage,
            'search'     => $search,
        ]);
    }

    public function store(Request $request)
    {
        $companycode = Session::get('companycode');
        
        $request->validate([
            'id' => 'required|string|max:10',
            'kontraktorid' => 'required|string|max:10',
            'namasubkontraktor' => 'required|string|max:100',
        ]);

        // Cek apakah kontraktor exists
        $kontraktorExists = DB::table('kontraktor')
            ->where('companycode', $companycode)
            ->where('id', $request->kontraktorid)
            ->exists();

        if (!$kontraktorExists) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'kontraktorid' => 'Kontraktor tidak ditemukan'
                ]);
        }

        // Cek duplicate ID
        $exists = DB::table('subkontraktor')
            ->where('companycode', $companycode)
            ->where('id', $request->id)
            ->exists();
            
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'id' => 'Duplicate Entry, ID Subkontraktor sudah ada'
                ]);
        }

        DB::table('subkontraktor')->insert([
            'companycode' => $companycode,
            'id' => strtoupper($request->input('id')),
            'kontraktorid' => $request->input('kontraktorid'),
            'namasubkontraktor' => $request->input('namasubkontraktor'),
            'isactive' => 1,
            'inputby' => Auth::user()->userid,
            'createdat' => now(),
        ]);

        return redirect()->back()->with('success', 'Data subkontraktor berhasil disimpan.');
    }

    public function update(Request $request, $companycode, $id)
    {   
        $sessionCompanycode = Session::get('companycode');
        
        if ($companycode !== $sessionCompanycode) {
            abort(403, 'Unauthorized access to company data');
        }
        
        // Cek apakah data exists
        $subkontraktor = DB::table('subkontraktor')
            ->where('companycode', $companycode)
            ->where('id', $id)
            ->first();

        if (!$subkontraktor) {
            abort(404, 'Data not found');
        }

        $validated = $request->validate([
            // 'id' => 'required|string|max:10', // ID subkontraktor tidak bisa diubah
            'kontraktorid' => 'required|string|max:10',
            'namasubkontraktor' => 'required|string|max:100',
        ]);

        // Cek apakah kontraktor exists
        $kontraktorExists = DB::table('kontraktor')
            ->where('companycode', $companycode)
            ->where('id', $validated['kontraktorid'])
            ->exists();

        if (!$kontraktorExists) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'kontraktorid' => 'Kontraktor tidak ditemukan'
                ]);
        }

        DB::table('subkontraktor')
            ->where('companycode', $companycode)
            ->where('id', $id)
            ->update([
                // 'id' tidak diubah
                'kontraktorid' => $validated['kontraktorid'],
                'namasubkontraktor' => $validated['namasubkontraktor'],
                'updateby' => Auth::user()->userid,
                'updatedat' => now(),
            ]);
    
        return redirect()->back()->with('success', 'Data subkontraktor berhasil di-update.');
    }

    public function toggleActive(Request $request, $companycode, $id)
    {
        $sessionCompanycode = Session::get('companycode');
        
        if ($companycode !== $sessionCompanycode) {
            abort(403, 'Unauthorized access to company data');
        }

        $subkontraktor = DB::table('subkontraktor')
            ->where('companycode', $companycode)
            ->where('id', $id)
            ->first();

        if (!$subkontraktor) {
            abort(404, 'Data not found');
        }

        $newStatus = $subkontraktor->isactive ? 0 : 1;

        // Jika mau mengaktifkan, cek dulu kontraktor parentnya aktif atau tidak
        if ($newStatus === 1) {
            $kontraktorActive = DB::table('kontraktor')
                ->where('companycode', $companycode)
                ->where('id', $subkontraktor->kontraktorid)
                ->where('isactive', 1)
                ->exists();

            if (!$kontraktorActive) {
                return redirect()->back()->withErrors([
                    'toggle' => 'Tidak dapat mengaktifkan subkontraktor karena kontraktor induknya masih non-aktif.'
                ]);
            }
        }

        DB::table('subkontraktor')
            ->where('companycode', $companycode)
            ->where('id', $id)
            ->update([
                'isactive' => $newStatus,
                'updateby' => Auth::user()->userid,
                'updatedat' => now(),
            ]);

        $message = $newStatus 
            ? 'Subkontraktor berhasil diaktifkan kembali.' 
            : 'Subkontraktor berhasil dinonaktifkan.';

        return redirect()->back()->with('success', $message);
    }

    // destroy method dihapus - gunakan toggleActive sebagai gantinya
}