<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\MasterData\Kendaraan;
use App\Models\TenagaKerja;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class KendaraanController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Master',
            'nav' => 'kendaraan',
            'routeName' => route('masterdata.kendaraan.index'),
        ]);
    }

    /**
     * Display a listing of kendaraan.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search = $request->input('search');
        $companycode = session('companycode');

        // Query kendaraan dengan join ke tenaga kerja (operator)
        $query = DB::table('kendaraan as k')
            ->leftJoin('tenagakerja as tk', function($join) {
                $join->on('k.idtenagakerja', '=', 'tk.tenagakerjaid')
                     ->on('k.companycode', '=', 'tk.companycode');
            })
            ->where('k.companycode', $companycode)
            ->select([
                'k.companycode',
                'k.nokendaraan',
                'k.idtenagakerja',
                'k.hourmeter',
                'k.jenis',
                'k.isactive',
                'tk.nama as operator_nama',
                'tk.nik as operator_nik',
                'tk.isactive as operator_isactive'
            ]);

        // Search functionality
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('k.nokendaraan', 'like', "%{$search}%")
                  ->orWhere('k.jenis', 'like', "%{$search}%")
                  ->orWhere('tk.nama', 'like', "%{$search}%")
                  ->orWhere('k.idtenagakerja', 'like', "%{$search}%");
            });
        }

        $result = $query
            ->orderBy('k.isactive', 'desc')
            ->orderBy('k.nokendaraan', 'asc')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search' => $search,
            ]);

        // Get available operators (jenis 3 = Operator, active only, belum ada kendaraan)
        $availableOperators = DB::table('tenagakerja')
            ->where('companycode', $companycode)
            ->where('jenistenagakerja', 3) // Operator only
            ->where('isactive', 1)
            ->select('tenagakerjaid', 'nama', 'nik')
            ->orderBy('nama')
            ->get();

        // Get all active operators for edit (including yang sudah punya kendaraan)
        $allOperators = DB::table('tenagakerja')
            ->where('companycode', $companycode)
            ->where('jenistenagakerja', 3)
            ->where('isactive', 1)
            ->select('tenagakerjaid', 'nama', 'nik')
            ->orderBy('nama')
            ->get();

        return view('masterdata.kendaraan.index', [
            'result' => $result,
            'title' => 'Data Kendaraan',
            'perPage' => $perPage,
            'search' => $search,
            'availableOperators' => $availableOperators,
            'allOperators' => $allOperators,
            'companycode' => $companycode
        ]);
    }

    /**
     * Store a newly created kendaraan in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nokendaraan' => 'required|string|max:10|unique:kendaraan,nokendaraan,NULL,id,companycode,' . session('companycode'),
            'operator' => 'nullable|exists:tenagakerja,tenagakerjaid',
            'jenis' => 'required|string|max:50',
            'hourmeter' => 'nullable|numeric|min:0|max:999999.99',
        ]);

        $companycode = session('companycode');

        Kendaraan::create([
            'companycode' => $companycode,
            'nokendaraan' => strtoupper($request->nokendaraan),
            'idtenagakerja' => $request->operator,
            'jenis' => $request->jenis,
            'hourmeter' => $request->hourmeter ?: 0,
            'inputby' => Auth::user()->userid,
            'createdat' => now(),
            'isactive' => 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kendaraan "' . strtoupper($request->nokendaraan) . '" berhasil ditambahkan!'
        ]);
    }

    /**
     * Update the specified kendaraan in storage.
     */
    public function update(Request $request, $companycode, $nokendaraan)
    {
        $kendaraan = Kendaraan::where('companycode', $companycode)
            ->where('nokendaraan', $nokendaraan)
            ->firstOrFail();

        $request->validate([
            'nokendaraan' => 'required|string|max:10|unique:kendaraan,nokendaraan,' . $nokendaraan . ',nokendaraan,companycode,' . $companycode,
            'operator' => 'nullable|exists:tenagakerja,tenagakerjaid',
            'jenis' => 'required|string|max:50',
            'hourmeter' => 'nullable|numeric|min:0|max:999999.99',
            'isactive' => 'nullable|boolean'
        ]);

        $kendaraan->update([
            'nokendaraan' => strtoupper($request->nokendaraan),
            'idtenagakerja' => $request->operator,
            'jenis' => $request->jenis,
            'hourmeter' => $request->hourmeter ?: 0,
            'updateby' => Auth::user()->userid,
            'updatedate' => now(),
            'isactive' => $request->has('isactive') ? 1 : 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kendaraan "' . strtoupper($request->nokendaraan) . '" berhasil diperbarui!'
        ]);
    }

    /**
     * Soft delete (set inactive) the specified kendaraan.
     */
    public function destroy($companycode, $nokendaraan)
    {
        $kendaraan = Kendaraan::where('companycode', $companycode)
            ->where('nokendaraan', $nokendaraan)
            ->firstOrFail();

        $kendaraan->update([
            'isactive' => 0,
            'updateby' => Auth::user()->userid,
            'updatedate' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kendaraan "' . $nokendaraan . '" berhasil dinonaktifkan!'
        ]);
    }

    /**
     * Handle form submissions
     */
    public function handle(Request $request)
    {
        if ($request->has('perPage')) {
            return $this->index($request);
        }

        return $this->store($request);
    }
}