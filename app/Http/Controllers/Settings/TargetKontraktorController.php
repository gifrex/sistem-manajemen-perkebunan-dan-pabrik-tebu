<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TargetKontraktorController extends Controller
{
    public function index(Request $request)
    {
        $perPage     = (int) $request->input('perPage', 10);
        $search      = $request->input('search');
        $companycode = session('companycode');

        $query = DB::table('targetkontraktor as tk')
            ->join('kontraktor as k', 'tk.idkontraktor', '=', 'k.id')
            ->where('tk.companycode', $companycode)
            ->select(
                'tk.id',
                'tk.idkontraktor',
                'tk.bulan',
                'tk.tahun',
                'tk.target',
                'tk.companycode',
                'k.namakontraktor', // <-- sesuaikan nama field nama di tabel kontraktor
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('k.namakontraktor', 'like', "%{$search}%") // <-- sesuaikan
                    ->orWhere('tk.bulan', 'like', "%{$search}%")
                    ->orWhere('tk.tahun', 'like', "%{$search}%");
            });
        }

        $result = $query
            ->orderBy('tk.tahun', 'desc')
            ->orderBy('tk.bulan', 'asc')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage, 'search' => $search]);

        // Dropdown kontraktor sesuai companycode
        $kontraktorList = DB::table('kontraktor')
            ->where('companycode', $companycode)
            ->select('id', 'namakontraktor') // <-- sesuaikan nama field
            ->orderBy('id', 'asc')
            ->get();

        return view('settings.targetkontraktor.index', [
            'result'          => $result,
            'kontraktorList'  => $kontraktorList,
            'search'          => $search,
            'perPage'         => $perPage,
            'title'           => 'Target Kontraktor',
            'navbar'          => 'Settings',
            'nav'             => 'Target Kontraktor',
            'companycode'     => $companycode,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'idkontraktor' => 'required',
            'bulan'        => 'required|integer|min:1|max:12',
            'tahun'        => 'required|integer|min:2000|max:2099',
            'target'       => 'required|numeric|min:0',
        ]);

        $companycode = session('companycode');

        // Cek duplikat: 1 kontraktor tidak boleh punya target bulan+tahun yang sama
        $exists = DB::table('targetkontraktor')
            ->where('companycode', $companycode)
            ->where('idkontraktor', $request->idkontraktor)
            ->where('bulan', $request->bulan)
            ->where('tahun', $request->tahun)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Target untuk kontraktor, bulan, dan tahun tersebut sudah ada.')
                ->withInput();
        }

        DB::table('targetkontraktor')->insert([
            'companycode'  => $companycode,
            'idkontraktor' => $request->idkontraktor,
            'bulan'        => $request->bulan,
            'tahun'        => $request->tahun,
            'target'       => $request->target,
        ]);

        return redirect()->back()->with('success', 'Target kontraktor berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'idkontraktor' => 'required',
            'bulan'        => 'required|integer|min:1|max:12',
            'tahun'        => 'required|integer|min:2000|max:2099',
            'target'       => 'required|numeric|min:0',
        ]);

        $companycode = session('companycode');

        // Cek duplikat saat update (exclude record saat ini)
        $exists = DB::table('targetkontraktor')
            ->where('companycode', $companycode)
            ->where('idkontraktor', $request->idkontraktor)
            ->where('bulan', $request->bulan)
            ->where('tahun', $request->tahun)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Target untuk kontraktor, bulan, dan tahun tersebut sudah ada.')
                ->withInput();
        }

        DB::table('targetkontraktor')
            ->where('id', $id)                      // pakai PK id, bukan idkontraktor
            ->where('companycode', $companycode)    // security: pastikan milik company ini
            ->update([
                'idkontraktor' => $request->idkontraktor,
                'bulan'        => $request->bulan,
                'tahun'        => $request->tahun,
                'target'       => $request->target,
            ]);

        return redirect()->back()->with('success', 'Target kontraktor berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DB::table('targetkontraktor')
            ->where('id', $id)
            ->where('companycode', session('companycode'))
            ->delete();

        return redirect()->back()->with('success', 'Target kontraktor berhasil dihapus.');
    }
}