<?php

namespace App\Http\Controllers\Pabrik;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Trash;
use App\Models\MasterData\Company;


class TrashController extends Controller
{
    public function index(Request $request)
    {
        $search      = $request->get('search');
        $perPage     = $request->get('perPage', 10);
        $companycode = session('companycode');
        $date        = $request->get('date', now()->format('Y-m-d'));

        $query = Trash::query();

        // Filter by company
        if ($companycode) {
            $query->where('companycode', $companycode);
        }

        // Filter by createddate (default: hari ini)
        $query->whereDate('createddate', $date);

        // Search functionality
        if ($search) {
            $query->where('suratjalanno', 'like', "%{$search}%");
        }

        $query->orderBy('createddate', 'desc');

        $data = $query->paginate($perPage)->appends($request->query());

        $companies = DB::table('company')
            ->select('companycode', 'name')
            ->orderBy('companycode')
            ->get();

        return view('pabrik.trash.index', [
            'title'     => 'Trash Pabrik',
            'navbar'    => 'Pabrik',
            'nav'       => 'Trash',
            'companies' => $companies,
            'data'      => $data,
            'date'      => $date,
        ]);
    }

    public function checkSuratJalan(Request $request)
    {
        try {
            $noSuratJalan = $request->get('no');

            if (empty($noSuratJalan)) {
                return response()->json([
                    'exists' => false,
                    'message' => 'Nomor surat jalan harus diisi'
                ]);
            }

            // ? Cari berdasarkan nomor surat jalan saja
            $suratJalan = DB::table('suratjalanpos as sj')
                ->leftJoin('subkontraktor as sk', function ($join) {
                    $join->on('sk.id', '=', 'sj.namasubkontraktor')
                         ->on('sk.companycode', '=', 'sj.companycode');
                })
                ->where('sj.suratjalanno', $noSuratJalan)
                ->where('sj.companycode', session('companycode'))
                ->select([
                    'sj.suratjalanno',
                    'sj.companycode',
                    'sj.plot',
                    'sj.varietas',
                    'sj.kategori',
                    'sj.namasubkontraktor as kodesubkontraktor',
                    'sk.namasubkontraktor as namasubkontraktor',
                    'sj.nomorpolisi',
                ])
                ->first();

            if ($suratJalan) {
                $subkontraktor = ($suratJalan->kodesubkontraktor)
                    ? ($suratJalan->kodesubkontraktor . ' - ' . ($suratJalan->namasubkontraktor ?? '-'))
                    : '-';

                return response()->json([
                    'exists' => true,
                    'message' => 'Surat jalan ditemukan dan siap digunakan',
                    'data' => [
                        'suratjalanno' => $suratJalan->suratjalanno,
                        'companycode' => $suratJalan->companycode,
                        'plot' => $suratJalan->plot,
                        'varietas' => $suratJalan->varietas,
                        'kategori' => $suratJalan->kategori,
                        'namasubkontraktor' => $subkontraktor,
                        'nomorpolisi' => $suratJalan->nomorpolisi,
                    ]
                ]);
            } else {
                return response()->json([
                    'exists' => false,
                    'message' => 'Nomor surat jalan tidak ditemukan dalam sistem'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'exists' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // Tambahkan method ini ke TrashController.php setelah method checkSuratJalan()
    public function searchSuratJalanByDate(Request $request)
    {
        try {
            $company = $request->get('company');
            $date = date('Y-m-d', strtotime($request->get('date')));

            if (empty($company) || empty($date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company dan tanggal harus diisi',
                    'data' => []
                ]);
            }

            // DEBUG: Cek dulu ada berapa surat jalan TANPA filter trash
            $totalSuratJalan = DB::table('suratjalanpos')
                ->where('companycode', $company)
                ->whereDate('tanggalangkut', $date)
                ->count();

            // DEBUG: Cek berapa yang sudah ada di trash
            $sudahAdaTrash = DB::table('suratjalanpos as sj')
                ->join('trash as t', function ($join) {
                    $join->on('t.suratjalanno', '=', 'sj.suratjalanno')
                        ->on('t.companycode', '=', 'sj.companycode');
                })
                ->where('sj.companycode', $company)
                ->whereDate('sj.tanggalangkut', $date)
                ->count();

            // Query utama
            $suratJalanList = DB::table('suratjalanpos as sj')
                ->leftJoin('subkontraktor as sk', function ($join) {
                    $join->on('sk.id', '=', 'sj.namasubkontraktor')
                         ->on('sk.companycode', '=', 'sj.companycode');
                })
                ->select([
                    'sj.suratjalanno',
                    'sj.companycode',
                    'sj.plot',
                    'sj.varietas',
                    'sj.kategori',
                    'sj.tanggalangkut',
                    'sj.nomorpolisi',
                    DB::raw("CASE WHEN sj.namasubkontraktor IS NOT NULL AND sj.namasubkontraktor != '' THEN CONCAT(sj.namasubkontraktor, ' - ', COALESCE(sk.namasubkontraktor, '-')) ELSE '-' END as namasubkontraktor"),
                ])
                ->where('sj.companycode', $company)
                ->whereDate('sj.tanggalangkut', $date)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('trash')
                        ->whereColumn('trash.suratjalanno', 'sj.suratjalanno')
                        ->whereColumn('trash.companycode', 'sj.companycode');
                })
                ->orderBy('sj.suratjalanno')
                ->get();

            if ($suratJalanList->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => "Total SJ: {$totalSuratJalan}, Sudah ada trash: {$sudahAdaTrash}. Tidak ada surat jalan yang tersedia.",
                    'data' => []
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Surat jalan ditemukan',
                'data' => $suratJalanList->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function searchSuratJalanByNopol(Request $request)
    {
        try {
            $company = $request->get('company');
            $nopol   = trim($request->get('nopol'));

            if (empty($company) || empty($nopol)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company dan nomor polisi harus diisi',
                    'data'    => []
                ]);
            }

            // Normalisasi nopol: hilangkan spasi dan uppercase untuk perbandingan
            $nopolNormalized = strtoupper(str_replace(' ', '', $nopol));

            // DEBUG: total SJ dengan nopol tersebut
            $totalSuratJalan = DB::table('suratjalanpos')
                ->where('companycode', $company)
                ->whereRaw('UPPER(REPLACE(nomorpolisi, " ", "")) = ?', [$nopolNormalized])
                ->count();

            // DEBUG: berapa yang sudah ada di trash
            $sudahAdaTrash = DB::table('suratjalanpos as sj')
                ->join('trash as t', function ($join) {
                    $join->on('t.suratjalanno', '=', 'sj.suratjalanno')
                         ->on('t.companycode', '=', 'sj.companycode');
                })
                ->where('sj.companycode', $company)
                ->whereRaw('UPPER(REPLACE(sj.nomorpolisi, " ", "")) = ?', [$nopolNormalized])
                ->count();

            $suratJalanList = DB::table('suratjalanpos as sj')
                ->leftJoin('subkontraktor as sk', function ($join) {
                    $join->on('sk.id', '=', 'sj.namasubkontraktor')
                         ->on('sk.companycode', '=', 'sj.companycode');
                })
                ->select([
                    'sj.suratjalanno',
                    'sj.companycode',
                    'sj.plot',
                    'sj.varietas',
                    'sj.kategori',
                    'sj.tanggalangkut',
                    'sj.nomorpolisi',
                    DB::raw("CASE WHEN sj.namasubkontraktor IS NOT NULL AND sj.namasubkontraktor != '' THEN CONCAT(sj.namasubkontraktor, ' - ', COALESCE(sk.namasubkontraktor, '-')) ELSE '-' END as namasubkontraktor"),
                ])
                ->where('sj.companycode', $company)
                ->whereRaw('UPPER(REPLACE(sj.nomorpolisi, " ", "")) = ?', [$nopolNormalized])
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('trash')
                        ->whereColumn('trash.suratjalanno', 'sj.suratjalanno')
                        ->whereColumn('trash.companycode', 'sj.companycode');
                })
                ->orderBy('sj.tanggalangkut', 'desc')
                ->get();

            if ($suratJalanList->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => "Total SJ: {$totalSuratJalan}, Sudah ada trash: {$sudahAdaTrash}. Tidak ada surat jalan yang tersedia untuk nomor polisi tersebut.",
                    'data'    => []
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Surat jalan ditemukan',
                'data'    => $suratJalanList->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => []
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validation
            $request->validate([
                'companycode' => 'required|string',
                'no_surat_jalan' => 'required|string',
                'jenis' => 'required|in:manual,mesin',
                'berat_bersih' => 'required|string',
                'toleransi' => 'required|string',
                'pucuk' => 'nullable|string',
                'daun_gulma' => 'nullable|string',
                'sogolan' => 'nullable|string',
                'siwilan' => 'nullable|string',
                'tebumati' => 'nullable|string',
                'tanah_etc' => 'nullable|string'
            ], [
                'companycode.required' => 'Company code wajib diisi',
                'no_surat_jalan.required' => 'Nomor surat jalan wajib diisi',
                'jenis.required' => 'Jenis trash wajib dipilih',
                'jenis.in' => 'Jenis trash harus manual atau mesin',
                'berat_bersih.required' => 'Berat bersih wajib diisi',
                'toleransi.required' => 'Toleransi wajib diisi'
            ]);

            // Check if combination already exists
            $exists = DB::table('trash')
                ->where('suratjalanno', $request->no_surat_jalan)
                ->where('companycode', $request->companycode)

                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->with('error', 'Data trash untuk nomor surat jalan ini dengan jenis yang sama sudah ada!')
                    ->withInput();
            }
            
            // Convert comma format to decimal for calculation
            $beratBersih = $this->parseDecimal($request->berat_bersih);
            $toleransi = $this->parseDecimal($request->toleransi);
            $pucuk = $this->parseDecimal($request->pucuk ?? '0');
            $daunGulma = $this->parseDecimal($request->daun_gulma ?? '0');
            $sogolan = $this->parseDecimal($request->sogolan ?? '0');
            $siwilan = $this->parseDecimal($request->siwilan ?? '0');
            $tebumati = $this->parseDecimal($request->tebumati ?? '0');
            $tanahEtc = $this->parseDecimal($request->tanah_etc ?? '0');
            $beratKotor = $this->parseDecimal($request->berat_kotor);

            // Calculate percentages based on berat kotor (truncate 3 desimal, tanpa pembulatan)
            $tebumatiPct = $beratKotor > 0 ? $this->truncate3(($tebumati / $beratKotor) * 100) : 0;
            $daunPct     = $beratKotor > 0 ? $this->truncate3(($daunGulma / $beratKotor) * 100) : 0;
            $pucukPct    = $beratKotor > 0 ? $this->truncate3(($pucuk / $beratKotor) * 100) : 0;
            $sogolanPct  = $beratKotor > 0 ? $this->truncate3(($sogolan / $beratKotor) * 100) : 0;
            $siwlanPct   = $beratKotor > 0 ? $this->truncate3(($siwilan / $beratKotor) * 100) : 0;
            $tanahEtc3   = $this->truncate3($tanahEtc);

            // Calculate totals (truncate 3 desimal, tanpa pembulatan)
            $totalTrash = $this->truncate3($tebumatiPct + $daunPct + $pucukPct + $sogolanPct + $siwlanPct + $tanahEtc3);
            $nettoTrash = $this->truncate3($totalTrash - $toleransi);

            // Pastikan netto trash tidak kurang dari 0
            if ($nettoTrash < 0) {
                $nettoTrash = 0;
            }

            // Insert trash record using DB
            DB::table('trash')->insert([
                'suratjalanno' => $request->no_surat_jalan,
                'companycode' => $request->companycode,
                'jenis' => $request->jenis,
                'toleransi' => $toleransi,
                'pucuk' => $pucukPct,
                'daungulma' => $daunPct,
                'sogolan' => $sogolanPct,
                'siwilan' => $siwlanPct,
                'tebumati' => $tebumatiPct,
                'tanahetc' => $tanahEtc3,
                'total' => $totalTrash,
                'nettotrash' => $nettoTrash,
                'createdby' => Auth::user()->userid,
                'createddate' => now()->format('Y-m-d H:i:s'),
            ]);

            DB::commit();

            return redirect()->route('pabrik.trash.index')
                ->with('success', 'Data trash berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, $suratjalanno, $companycode, $jenis)
    {
        try {
            DB::beginTransaction();

            // Validation
            $request->validate([
                'companycode' => 'required|string',
                'no_surat_jalan' => 'required|string',
                'jenis' => 'required|in:manual,mesin',
                'berat_bersih' => 'required|string',
                'toleransi' => 'required|string',
                'pucuk' => 'nullable|string',
                'daun_gulma' => 'nullable|string',
                'sogolan' => 'nullable|string',
                'siwilan' => 'nullable|string',
                'tebumati' => 'nullable|string',
                'tanah_etc' => 'nullable|string'
            ], [
                'companycode.required' => 'Company code wajib diisi',
                'no_surat_jalan.required' => 'Nomor surat jalan wajib diisi',
                'jenis.required' => 'Jenis trash wajib dipilih',
                'jenis.in' => 'Jenis trash harus manual atau mesin',
                'berat_bersih.required' => 'Berat bersih wajib diisi',
                'toleransi.required' => 'Toleransi wajib diisi'
            ]);

            // Check if record exists
            $exists = DB::table('trash')
                ->where('suratjalanno', $suratjalanno)
                ->where('companycode', $companycode)
                ->where('jenis', $jenis)
                ->exists();

            if (!$exists) {
                return redirect()->back()
                    ->with('error', 'Data trash tidak ditemukan!');
            }

            // Convert comma format to decimal for calculation (SAMA SEPERTI STORE)
            $beratBersih = $this->parseDecimal($request->berat_bersih);
            $toleransi = $this->parseDecimal($request->toleransi);
            $pucuk = $this->parseDecimal($request->pucuk ?? '0');
            $daunGulma = $this->parseDecimal($request->daun_gulma ?? '0');
            $sogolan = $this->parseDecimal($request->sogolan ?? '0');
            $siwilan = $this->parseDecimal($request->siwilan ?? '0');
            $tebumati = $this->parseDecimal($request->tebumati ?? '0');
            $tanahEtc = $this->parseDecimal($request->tanah_etc ?? '0');
            $beratKotor = $this->parseDecimal($request->berat_kotor);

            // Calculate percentages based on berat kotor (truncate 3 desimal, tanpa pembulatan)
            $tebumatiPct = $beratKotor > 0 ? $this->truncate3(($tebumati / $beratKotor) * 100) : 0;
            $daunPct     = $beratKotor > 0 ? $this->truncate3(($daunGulma / $beratKotor) * 100) : 0;
            $pucukPct    = $beratKotor > 0 ? $this->truncate3(($pucuk / $beratKotor) * 100) : 0;
            $sogolanPct  = $beratKotor > 0 ? $this->truncate3(($sogolan / $beratKotor) * 100) : 0;
            $siwlanPct   = $beratKotor > 0 ? $this->truncate3(($siwilan / $beratKotor) * 100) : 0;
            $tanahEtc3   = $this->truncate3($tanahEtc);

            // Calculate totals (truncate 3 desimal, tanpa pembulatan)
            $totalTrash = $this->truncate3($tebumatiPct + $daunPct + $pucukPct + $sogolanPct + $siwlanPct + $tanahEtc3);
            $nettoTrash = $this->truncate3($totalTrash - $toleransi);

            // Pastikan netto trash tidak kurang dari 0
            if ($nettoTrash < 0) {
                $nettoTrash = 0;
            }

            // Update the record
            DB::table('trash')
                ->where('suratjalanno', $suratjalanno)
                ->where('companycode', $companycode)
                ->where('jenis', $jenis)
                ->update([
                    'suratjalanno' => $request->no_surat_jalan,
                    'companycode' => $request->companycode,
                    'jenis' => $request->jenis,
                    'toleransi' => $toleransi,
                    'pucuk' => $pucukPct,
                    'daungulma' => $daunPct,
                    'sogolan' => $sogolanPct,
                    'siwilan' => $siwlanPct,
                    'tebumati' => $tebumatiPct,
                    'tanahetc' => $tanahEtc3,
                    'total' => $totalTrash,
                    'nettotrash' => $nettoTrash,
                    // Note: createdby dan createddate tidak diupdate karena ini adalah data audit
                ]);

            DB::commit();

            return redirect()->route('pabrik.trash.index')
                ->with('success', 'Data trash berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Tambah method destroy juga:
    public function destroy($suratjalanno, $companycode, $jenis)
    {
        try {
            DB::beginTransaction();

            $deleted = DB::table('trash')
                ->where('suratjalanno', $suratjalanno)
                ->where('companycode', $companycode)
                ->where('jenis', $jenis)
                ->delete();

            if ($deleted) {
                DB::commit();
                return redirect()->route('pabrik.trash.index')
                    ->with('success', 'Data trash berhasil dihapus!');
            } else {
                return redirect()->back()
                    ->with('error', 'Data trash tidak ditemukan!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Truncate to 3 decimal places WITHOUT rounding
     * Example: 8.8388888 -> 8.838  (bukan 8.839)
     */
    private function truncate3($value)
    {
        $str = number_format((float) $value, 10, '.', '');
        $dot = strpos($str, '.');
        return (float) substr($str, 0, $dot + 4);
    }

    /**
     * Parse decimal from comma format (Indonesian) to dot format
     * Example: "45,680" -> 45.680
     */
    private function parseDecimal($value)
    {
        if (empty($value) || $value === '') {
            return 0;
        }

        // Remove any spaces and convert comma to dot
        $cleaned = str_replace([' ', ','], ['', '.'], $value);

        return (float) $cleaned;
    }


    public function generateReport(Request $request)
    {
        try {
            $reportType = $request->report_type;
            $company = $request->company;

            // Auto set company ke 'all' untuk harian dan bulanan
            if ($reportType === 'harian' || $reportType === 'bulanan') {
                $company = 'all';
            }

            // Handle date range - perbaikan untuk bulanan
            if ($reportType === 'bulanan') {
                $month = (int) $request->month;
                $year = (int) $request->year;

                // Buat start dan end date dengan format yang benar
                $startDate = sprintf('%04d-%02d-01', $year, $month);
                $endDate = date('Y-m-t', strtotime($startDate)); // Last day of month
            } else {
                $startDate = $request->start_date;
                $endDate = $request->end_date;
                $month = null;
                $year = null;
            }

            // Query dengan error handling yang lebih baik
            $query = DB::table('trash as t')
                ->select([
                    't.suratjalanno',
                    't.companycode',
                    't.jenis',
                    't.createddate',
                    't.pucuk',
                    't.daungulma',
                    't.sogolan',
                    't.siwilan',
                    't.tebumati',
                    't.tanahetc',
                    't.total',
                    't.toleransi',
                    't.nettotrash',
                    'sj.plot',
                    'sj.varietas',
                    'sj.kategori',
                    'sj.nomorpolisi',
                    'sj.tanggalangkut',
                    'k.namakontraktor',
                    'sk.namasubkontraktor',
                    'tp.netto as tonase_netto'
                ])
                ->leftJoin('suratjalanpos as sj', 't.suratjalanno', '=', 'sj.suratjalanno')
                ->leftJoin('kontraktor as k', 'sj.namakontraktor', '=', 'k.id')
                ->leftJoin('subkontraktor as sk', 'sj.namasubkontraktor', '=', 'sk.id')
                ->leftJoin('timbanganpayload as tp', 'sj.suratjalanno', '=', 'tp.suratjalanno');

            // Filter tanggal - konsisten menggunakan tanggalangkut
            $query->whereBetween(DB::raw('DATE(sj.tanggalangkut)'), [$startDate, $endDate]);

            // Tambah filter untuk memastikan tanggalangkut tidak null
            $query->whereNotNull('sj.tanggalangkut');

            // Apply company filter - KOMBINASI LOGIC TERBAIK
            if ($company !== 'all' && !empty($company)) {
                if (is_array($company)) {
                    $query->whereIn('t.companycode', $company);
                } else {
                    if ($reportType === 'mingguan') {
                        // MINGGUAN: Mapping company untuk mingguan (LOGIC TERBARU)
                        if ($company === 'BNIL') {
                            $query->where('t.companycode', 'LIKE', 'BNL%');
                        } elseif ($company === 'SILVA') {
                            $query->where('t.companycode', 'LIKE', 'SIL%');
                        } else {
                            $query->where('t.companycode', 'LIKE', $company . '%');
                        }
                    } else {
                        // HARIAN/BULANAN: Exact match (LOGIC LAMA YANG SUDAH BENAR)
                        $query->where('t.companycode', $company);
                    }
                }
            }

            // Execute query
            if ($reportType === 'harian') {
                $data = $query->orderBy('sj.tanggalangkut')->orderBy('t.companycode', 'asc')->get();
            } else {
                $data = $query->orderBy('sj.tanggalangkut')->get();
            }

            if ($data->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada data ditemukan.');
            }

            // Convert to array dengan safe type conversion
            $simpleData = [];
            foreach ($data as $item) {
                $simpleData[] = [
                    'suratjalanno' => $item->suratjalanno ?? '',
                    'companycode' => $item->companycode ?? '',
                    'jenis' => $item->jenis ?? '',
                    'createddate' => $item->createddate ?? '',
                    'tanggalangkut' => $item->tanggalangkut ?? '',
                    'pucuk' => is_numeric($item->pucuk) ? (float)$item->pucuk : 0,
                    'daungulma' => is_numeric($item->daungulma) ? (float)$item->daungulma : 0,
                    'sogolan' => is_numeric($item->sogolan) ? (float)$item->sogolan : 0,
                    'siwilan' => is_numeric($item->siwilan) ? (float)$item->siwilan : 0,
                    'tebumati' => is_numeric($item->tebumati) ? (float)$item->tebumati : 0,
                    'tanahetc' => is_numeric($item->tanahetc) ? (float)$item->tanahetc : 0,
                    'total' => is_numeric($item->total) ? (float)$item->total : 0,
                    'toleransi' => is_numeric($item->toleransi) ? (float)$item->toleransi : 0,
                    'nettotrash' => is_numeric($item->nettotrash) ? (float)$item->nettotrash : 0,
                    'tonase_netto' => is_numeric($item->tonase_netto) ? (float)$item->tonase_netto : 0,
                    // Data dari JOIN
                    'plot' => $item->plot ?? '',
                    'varietas' => $item->varietas ?? '',
                    'kategori' => $item->kategori ?? '',
                    'nomorpolisi' => $item->nomorpolisi ?? '',
                    'namakontraktor' => $item->namakontraktor ?? '',
                    'namasubkontraktor' => $item->namasubkontraktor ?? ''
                ];
            }

            // Grouping logic yang diperbaiki untuk handle "all" company (LOGIC LAMA YANG SUDAH BAGUS)
            $dataGrouped = [];
            $isAllCompanies = ($company === 'all') ||
                (is_array($company) && in_array('all', $company)) ||
                (is_array($company) && count($company) > 1);

            switch ($reportType) {
                case 'bulanan':
                    if ($isAllCompanies) {
                        // BULANAN ALL COMPANIES: Group by company code
                        foreach ($simpleData as $item) {
                            $companyCode = $item['companycode'];
                            if (!isset($dataGrouped[$companyCode])) {
                                $dataGrouped[$companyCode] = [];
                            }
                            $dataGrouped[$companyCode][] = $item;
                        }
                    } else {
                        // BULANAN SINGLE COMPANY: Group by date
                        foreach ($simpleData as $item) {
                            $date = date('Y-m-d', strtotime($item['tanggalangkut']));
                            if (!isset($dataGrouped[$date])) {
                                $dataGrouped[$date] = [];
                            }
                            $dataGrouped[$date][] = $item;
                        }
                    }
                    break;

                case 'harian':
                    // HARIAN: Always group by date, regardless of company selection
                    foreach ($simpleData as $item) {
                        $date = date('Y-m-d', strtotime($item['tanggalangkut']));
                        if (!isset($dataGrouped[$date])) {
                            $dataGrouped[$date] = [];
                        }
                        $dataGrouped[$date][] = $item;
                    }
                    break;

                case 'mingguan':
                    // MINGGUAN: Group by jenis then company
                    foreach ($simpleData as $item) {
                        $jenis = $item['jenis'];
                        $companyCode = $item['companycode'];

                        if (!isset($dataGrouped[$jenis])) {
                            $dataGrouped[$jenis] = [];
                        }
                        if (!isset($dataGrouped[$jenis][$companyCode])) {
                            $dataGrouped[$jenis][$companyCode] = [];
                        }
                        $dataGrouped[$jenis][$companyCode][] = $item;
                    }

                    // Sort company codes dalam setiap jenis (ascending)
                    foreach ($dataGrouped as $jenis => $companies) {
                        ksort($dataGrouped[$jenis]);
                    }
                    break;
            }

            // Get actual companies yang lebih robust (LOGIC LAMA YANG SUDAH BAGUS)
            $actualCompanies = [];

            if ($reportType === 'bulanan' && $isAllCompanies) {
                // For bulanan all companies, companies are top-level keys
                $actualCompanies = array_keys($dataGrouped);
            } elseif ($reportType === 'harian' || ($reportType === 'bulanan' && !$isAllCompanies)) {
                // For harian or bulanan single company, collect companies from each date
                foreach ($dataGrouped as $dateItems) {
                    if (is_array($dateItems)) {
                        foreach ($dateItems as $item) {
                            if (isset($item['companycode']) && !in_array($item['companycode'], $actualCompanies)) {
                                $actualCompanies[] = $item['companycode'];
                            }
                        }
                    }
                }
            } else {
                // For mingguan, collect companies from nested structure
                foreach ($dataGrouped as $jenisData) {
                    if (is_array($jenisData)) {
                        foreach ($jenisData as $companyCode => $items) {
                            if (!in_array($companyCode, $actualCompanies)) {
                                $actualCompanies[] = $companyCode;
                            }
                        }
                    }
                }
            }

            // Sort companies
            sort($actualCompanies);

            // Prepare view data
            $viewData = [
                'dataGrouped' => $dataGrouped,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'month' => $month,
                'year' => $year,
                'reportType' => $reportType,
                'company' => $company,
                'user' => Auth::user()->userid,
                'actualCompanies' => $actualCompanies,
                'isAllCompanies' => $isAllCompanies,
                'totalRecords' => count($simpleData)
            ];

            // Route to view
            switch ($reportType) {
                case 'harian':
                    return view('pabrik.trash.report-harian', $viewData);
                case 'mingguan':
                    return view('pabrik.trash.report-mingguan', $viewData);
                case 'bulanan':
                    return view('pabrik.trash.report-bulanan', $viewData);
                default:
                    return view('pabrik.trash.report-harian', $viewData);
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function reportPreview(Request $request)
    {
        try {
            $reportType = $request->report_type;
            $company = $request->company;

            // Auto set company ke 'all' untuk harian dan bulanan
            if ($reportType === 'harian' || $reportType === 'bulanan') {
                $company = 'all';
            }

            // Handle date range - perbaikan untuk bulanan
            if ($reportType === 'bulanan') {
                $month = (int) $request->month;
                $year = (int) $request->year;

                // Buat start dan end date dengan format yang benar
                $startDate = sprintf('%04d-%02d-01', $year, $month);
                $endDate = date('Y-m-t', strtotime($startDate)); // Last day of month
            } else {
                $startDate = $request->start_date;
                $endDate = $request->end_date;
                $month = null;
                $year = null;
            }

            // Query dengan error handling yang lebih baik
            $query = DB::table('trash as t')
                ->select([
                    't.suratjalanno',
                    't.companycode',
                    't.jenis',
                    't.createddate',
                    't.pucuk',
                    't.daungulma',
                    't.sogolan',
                    't.siwilan',
                    't.tebumati',
                    't.tanahetc',
                    't.total',
                    't.toleransi',
                    't.nettotrash',
                    'sj.plot',
                    'sj.varietas',
                    'sj.kategori',
                    'sj.nomorpolisi',
                    'sj.tanggalangkut',
                    'k.namakontraktor',
                    'sk.namasubkontraktor',
                    'tp.netto as tonase_netto'
                ])
                ->leftJoin('suratjalanpos as sj', 't.suratjalanno', '=', 'sj.suratjalanno')
                ->leftJoin('kontraktor as k', 'sj.namakontraktor', '=', 'k.id')
                ->leftJoin('subkontraktor as sk', 'sj.namasubkontraktor', '=', 'sk.id')
                ->leftJoin('timbanganpayload as tp', 'sj.suratjalanno', '=', 'tp.suratjalanno');

            // Filter tanggal - konsisten menggunakan tanggalangkut
            $query->whereBetween(DB::raw('DATE(sj.tanggalangkut)'), [$startDate, $endDate]);

            // Tambah filter untuk memastikan tanggalangkut tidak null
            $query->whereNotNull('sj.tanggalangkut');

            // Apply company filter - KOMBINASI LOGIC TERBAIK
            if ($company !== 'all' && !empty($company)) {
                if (is_array($company)) {
                    $query->whereIn('t.companycode', $company);
                } else {
                    if ($reportType === 'mingguan') {
                        // MINGGUAN: Mapping company untuk mingguan (LOGIC TERBARU)
                        if ($company === 'BNIL') {
                            $query->where('t.companycode', 'LIKE', 'BNL%');
                        } elseif ($company === 'SILVA') {
                            $query->where('t.companycode', 'LIKE', 'SIL%');
                        } else {
                            $query->where('t.companycode', 'LIKE', $company . '%');
                        }
                    } else {
                        // HARIAN/BULANAN: Exact match (LOGIC LAMA YANG SUDAH BENAR)
                        $query->where('t.companycode', $company);
                    }
                }
            }

            // Execute query
            if ($reportType === 'harian') {
                $data = $query->orderBy('sj.tanggalangkut')->orderBy('t.companycode', 'asc')->get();
            } else {
                $data = $query->orderBy('sj.tanggalangkut')->get();
            }

            if ($data->isEmpty()) {
                return response('<div class="text-center py-8"><p class="text-gray-500">Tidak ada data untuk ditampilkan</p></div>', 200);
            }

            // Convert to array dengan safe type conversion
            $simpleData = [];
            foreach ($data as $item) {
                $simpleData[] = [
                    'suratjalanno' => $item->suratjalanno ?? '',
                    'companycode' => $item->companycode ?? '',
                    'jenis' => $item->jenis ?? '',
                    'createddate' => $item->createddate ?? '',
                    'tanggalangkut' => $item->tanggalangkut ?? '',
                    'pucuk' => is_numeric($item->pucuk) ? (float)$item->pucuk : 0,
                    'daungulma' => is_numeric($item->daungulma) ? (float)$item->daungulma : 0,
                    'sogolan' => is_numeric($item->sogolan) ? (float)$item->sogolan : 0,
                    'siwilan' => is_numeric($item->siwilan) ? (float)$item->siwilan : 0,
                    'tebumati' => is_numeric($item->tebumati) ? (float)$item->tebumati : 0,
                    'tanahetc' => is_numeric($item->tanahetc) ? (float)$item->tanahetc : 0,
                    'total' => is_numeric($item->total) ? (float)$item->total : 0,
                    'toleransi' => is_numeric($item->toleransi) ? (float)$item->toleransi : 0,
                    'nettotrash' => is_numeric($item->nettotrash) ? (float)$item->nettotrash : 0,
                    'tonase_netto' => is_numeric($item->tonase_netto) ? (float)$item->tonase_netto : 0,
                    // Data dari JOIN
                    'plot' => $item->plot ?? '',
                    'varietas' => $item->varietas ?? '',
                    'kategori' => $item->kategori ?? '',
                    'nomorpolisi' => $item->nomorpolisi ?? '',
                    'namakontraktor' => $item->namakontraktor ?? '',
                    'namasubkontraktor' => $item->namasubkontraktor ?? ''
                ];
            }

            // Grouping logic yang diperbaiki untuk handle "all" company (LOGIC LAMA YANG SUDAH BAGUS)
            $dataGrouped = [];
            $isAllCompanies = ($company === 'all') ||
                (is_array($company) && in_array('all', $company)) ||
                (is_array($company) && count($company) > 1);

            switch ($reportType) {
                case 'bulanan':
                    if ($isAllCompanies) {
                        // BULANAN ALL COMPANIES: Group by company code
                        foreach ($simpleData as $item) {
                            $companyCode = $item['companycode'];
                            if (!isset($dataGrouped[$companyCode])) {
                                $dataGrouped[$companyCode] = [];
                            }
                            $dataGrouped[$companyCode][] = $item;
                        }
                    } else {
                        // BULANAN SINGLE COMPANY: Group by date
                        foreach ($simpleData as $item) {
                            $date = date('Y-m-d', strtotime($item['tanggalangkut']));
                            if (!isset($dataGrouped[$date])) {
                                $dataGrouped[$date] = [];
                            }
                            $dataGrouped[$date][] = $item;
                        }
                    }
                    break;

                case 'harian':
                    // HARIAN: Always group by date, regardless of company selection
                    foreach ($simpleData as $item) {
                        $date = date('Y-m-d', strtotime($item['tanggalangkut']));
                        if (!isset($dataGrouped[$date])) {
                            $dataGrouped[$date] = [];
                        }
                        $dataGrouped[$date][] = $item;
                    }
                    break;

                case 'mingguan':
                    // MINGGUAN: Group by jenis then company
                    foreach ($simpleData as $item) {
                        $jenis = $item['jenis'];
                        $companyCode = $item['companycode'];

                        if (!isset($dataGrouped[$jenis])) {
                            $dataGrouped[$jenis] = [];
                        }
                        if (!isset($dataGrouped[$jenis][$companyCode])) {
                            $dataGrouped[$jenis][$companyCode] = [];
                        }
                        $dataGrouped[$jenis][$companyCode][] = $item;
                    }

                    // Sort company codes dalam setiap jenis (ascending)
                    foreach ($dataGrouped as $jenis => $companies) {
                        ksort($dataGrouped[$jenis]);
                    }
                    break;
            }

            // Get actual companies yang lebih robust (LOGIC LAMA YANG SUDAH BAGUS)
            $actualCompanies = [];

            if ($reportType === 'bulanan' && $isAllCompanies) {
                // For bulanan all companies, companies are top-level keys
                $actualCompanies = array_keys($dataGrouped);
            } elseif ($reportType === 'harian' || ($reportType === 'bulanan' && !$isAllCompanies)) {
                // For harian or bulanan single company, collect companies from each date
                foreach ($dataGrouped as $dateItems) {
                    if (is_array($dateItems)) {
                        foreach ($dateItems as $item) {
                            if (isset($item['companycode']) && !in_array($item['companycode'], $actualCompanies)) {
                                $actualCompanies[] = $item['companycode'];
                            }
                        }
                    }
                }
            } else {
                // For mingguan, collect companies from nested structure
                foreach ($dataGrouped as $jenisData) {
                    if (is_array($jenisData)) {
                        foreach ($jenisData as $companyCode => $items) {
                            if (!in_array($companyCode, $actualCompanies)) {
                                $actualCompanies[] = $companyCode;
                            }
                        }
                    }
                }
            }

            // Sort companies
            sort($actualCompanies);

            // Return view untuk preview (tanpa print button dan signature)
            return view('pabrik.trash.report-preview', [
                'dataGrouped' => $dataGrouped,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'month' => $month,
                'year' => $year,
                'reportType' => $reportType,
                'company' => $company,
                'user' => Auth::user()->userid,
                'actualCompanies' => $actualCompanies,
                'isAllCompanies' => $isAllCompanies,
                'totalRecords' => count($simpleData)
            ])->render();
        } catch (\Exception $e) {
            return response('<div class="text-center py-12"><div class="text-red-600 text-lg">Terjadi kesalahan: ' . $e->getMessage() . '</div></div>', 500);
        }
    }
}
