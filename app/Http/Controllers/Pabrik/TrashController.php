<?php

namespace App\Http\Controllers\Pabrik;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Trash;
use App\Models\MasterData\Company;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


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

            // Calculate percentages based on berat kotor (ambil 4 desimal, bulatkan ke 3 via string)
            $tebumatiPct = $beratKotor > 0 ? $this->round3(($tebumati / $beratKotor) * 100) : 0;
            $daunPct     = $beratKotor > 0 ? $this->round3(($daunGulma / $beratKotor) * 100) : 0;
            $pucukPct    = $beratKotor > 0 ? $this->round3(($pucuk / $beratKotor) * 100) : 0;
            $sogolanPct  = $beratKotor > 0 ? $this->round3(($sogolan / $beratKotor) * 100) : 0;
            $siwlanPct   = $beratKotor > 0 ? $this->round3(($siwilan / $beratKotor) * 100) : 0;
            $tanahEtc3   = $this->round3($tanahEtc);

            // Calculate totals (ambil 4 desimal, bulatkan ke 3 via string)
            $totalTrash = $this->round3($tebumatiPct + $daunPct + $pucukPct + $sogolanPct + $siwlanPct + $tanahEtc3);
            $nettoTrash = $this->round3($totalTrash - $toleransi);

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

            // Calculate percentages based on berat kotor (ambil 4 desimal, bulatkan ke 3 via string)
            $tebumatiPct = $beratKotor > 0 ? $this->round3(($tebumati / $beratKotor) * 100) : 0;
            $daunPct     = $beratKotor > 0 ? $this->round3(($daunGulma / $beratKotor) * 100) : 0;
            $pucukPct    = $beratKotor > 0 ? $this->round3(($pucuk / $beratKotor) * 100) : 0;
            $sogolanPct  = $beratKotor > 0 ? $this->round3(($sogolan / $beratKotor) * 100) : 0;
            $siwlanPct   = $beratKotor > 0 ? $this->round3(($siwilan / $beratKotor) * 100) : 0;
            $tanahEtc3   = $this->round3($tanahEtc);

            // Calculate totals (ambil 4 desimal, bulatkan ke 3 via string)
            $totalTrash = $this->round3($tebumatiPct + $daunPct + $pucukPct + $sogolanPct + $siwlanPct + $tanahEtc3);
            $nettoTrash = $this->round3($totalTrash - $toleransi);

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
     * Round to 3 decimal places using the 4th decimal digit (string-based, avoids float precision issues).
     * Step 1: Format to string with 10 decimal places.
     * Step 2: Read the 4th decimal digit directly from the string.
     * Step 3: Truncate to 3 decimal places, then add 0.001 if digit >= 5.
     * Example: 1.07956 -> digit4='5' -> 1.079 + 0.001 = 1.080
     * Example: 1.07944 -> digit4='4' -> 1.079 (no change)
     */
    private function round3($value)
    {
        $str = number_format((float) $value, 10, '.', '');
        $dot = strpos($str, '.');

        // Read 4th decimal digit directly from string (avoids float precision bug)
        $digit4 = isset($str[$dot + 4]) ? (int) $str[$dot + 4] : 0;

        // Truncate to 3 decimal places
        $base = (float) substr($str, 0, $dot + 4);

        // Round up if 4th digit is 5-9
        if ($digit4 >= 5) {
            $base = round($base + 0.001, 3);
        }

        return $base;
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

    public function exportExcel(Request $request)
    {
        try {
            $reportType = $request->report_type;
            $company    = $request->company;

            if ($reportType === 'harian' || $reportType === 'bulanan') {
                $company = 'all';
            }

            if ($reportType === 'bulanan') {
                $month     = (int) $request->month;
                $year      = (int) $request->year;
                $startDate = sprintf('%04d-%02d-01', $year, $month);
                $endDate   = date('Y-m-t', strtotime($startDate));
            } else {
                $startDate = $request->start_date;
                $endDate   = $request->end_date;
                $month     = null;
                $year      = null;
            }

            // — sama persis dengan generateReport —
            $query = DB::table('trash as t')
                ->select([
                    't.suratjalanno', 't.companycode', 't.jenis', 't.createddate',
                    't.pucuk', 't.daungulma', 't.sogolan', 't.siwilan',
                    't.tebumati', 't.tanahetc', 't.total', 't.toleransi', 't.nettotrash',
                    'sj.plot', 'sj.varietas', 'sj.kategori', 'sj.nomorpolisi',
                    'sj.tanggalangkut', 'k.namakontraktor', 'sk.namasubkontraktor',
                    'tp.netto as tonase_netto',
                ])
                ->leftJoin('suratjalanpos as sj', 't.suratjalanno', '=', 'sj.suratjalanno')
                ->leftJoin('kontraktor as k', 'sj.namakontraktor', '=', 'k.id')
                ->leftJoin('subkontraktor as sk', 'sj.namasubkontraktor', '=', 'sk.id')
                ->leftJoin('timbanganpayload as tp', 'sj.suratjalanno', '=', 'tp.suratjalanno')
                ->whereBetween(DB::raw('DATE(sj.tanggalangkut)'), [$startDate, $endDate])
                ->whereNotNull('sj.tanggalangkut');

            if ($company !== 'all' && !empty($company)) {
                if ($reportType === 'mingguan') {
                    // company bisa berupa comma-separated string hasil implode dari array (blade)
                    if (strpos($company, ',') !== false) {
                        $query->whereIn('t.companycode', explode(',', $company));
                    } elseif ($company === 'BNIL') {
                        $query->where('t.companycode', 'LIKE', 'BNL%');
                    } elseif ($company === 'SILVA') {
                        $query->where('t.companycode', 'LIKE', 'SIL%');
                    } else {
                        $query->where('t.companycode', 'LIKE', $company . '%');
                    }
                } else {
                    $query->where('t.companycode', $company);
                }
            }

            $data = $query->orderBy('sj.tanggalangkut')->orderBy('t.companycode')->get();

            if ($data->isEmpty()) {
                return redirect()->route('pabrik.trash.index')->with('error', 'Tidak ada data untuk diekspor.');
            }

            $rows = [];
            foreach ($data as $item) {
                $rows[] = [
                    'suratjalanno'      => $item->suratjalanno ?? '',
                    'companycode'       => $item->companycode ?? '',
                    'jenis'             => $item->jenis ?? '',
                    'tanggalangkut'     => $item->tanggalangkut ?? '',
                    'nomorpolisi'       => $item->nomorpolisi ?? '',
                    'plot'              => $item->plot ?? '',
                    'namakontraktor'    => $item->namakontraktor ?? '',
                    'namasubkontraktor' => $item->namasubkontraktor ?? '',
                    'pucuk'             => (float)($item->pucuk ?? 0),
                    'daungulma'         => (float)($item->daungulma ?? 0),
                    'sogolan'           => (float)($item->sogolan ?? 0),
                    'siwilan'           => (float)($item->siwilan ?? 0),
                    'tebumati'          => (float)($item->tebumati ?? 0),
                    'tanahetc'          => (float)($item->tanahetc ?? 0),
                    'total'             => (float)($item->total ?? 0),
                    'toleransi'         => (float)($item->toleransi ?? 0),
                    'nettotrash'        => (float)($item->nettotrash ?? 0),
                    'tonase_netto'      => (float)($item->tonase_netto ?? 0),
                ];
            }

            $spreadsheet = new Spreadsheet();
            $monthNames  = [
                '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember',
            ];

            // ── helper closures ──────────────────────────────────────────────
            $fmt3  = fn($v) => number_format((float)$v, 3, '.', '');
            $bold  = ['font' => ['bold' => true]];
            $center = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
            $right  = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]];
            $wrapCenter = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true, 'vertical' => Alignment::VERTICAL_CENTER]];

            $headerFill = fn(string $hex) => [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $hex]],
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ];
            $dataBorder = [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ];

            $applyRange = function($sheet, $range, $style) {
                $sheet->getStyle($range)->applyFromArray($style);
            };

            // ════════════════════════════════════════════════════════════════
            if ($reportType === 'harian' || $reportType === 'mingguan') {

                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle($reportType === 'harian' ? 'Harian' : 'Mingguan');

                $title    = $reportType === 'harian' ? 'LAPORAN HARIAN DATA TRASH' : 'LAPORAN MINGGUAN DATA TRASH';
                $colCount = 17; // A–Q
                $lastCol  = 'Q';

                // Judul
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', $title);
                $sheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'SUNGAI BUDI GROUP');
                $sheet->getStyle('A2')->applyFromArray(['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

                $sheet->mergeCells("A3:{$lastCol}3");
                $period = 'Periode: ' . date('d/m/Y', strtotime($startDate)) . ' s/d ' . date('d/m/Y', strtotime($endDate));
                $sheet->setCellValue('A3', $period);
                $sheet->getStyle('A3')->applyFromArray($center);

                $sheet->mergeCells("A4:{$lastCol}4");
                $sheet->setCellValue('A4', 'Dicetak: ' . date('d/m/Y H:i'));
                $sheet->getStyle('A4')->applyFromArray($center);

                $colHeaders = ['No','Jenis','Surat Jalan','No. Polisi','Asal Tebu','Plot','Kontraktor','Sub Kontraktor','Pucuk (%)','Daun Gulma (%)','Sogolan (%)','Siwilan (%)','Tebu Mati (%)','Tanah dll (%)','Total (%)','Toleransi (%)','Netto (%)'];
                $colWidths  = [5, 10, 20, 14, 12, 10, 22, 22, 11, 14, 11, 11, 13, 13, 11, 13, 11];

                foreach ($colWidths as $i => $w) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i + 1))->setWidth($w);
                }
                $sheet->getDefaultRowDimension()->setRowHeight(16);

                // Grouping
                $grouped = [];
                if ($reportType === 'harian') {
                    foreach ($rows as $r) {
                        $key = date('Y-m-d', strtotime($r['tanggalangkut']));
                        $grouped[$key][] = $r;
                    }
                } else {
                    foreach ($rows as $r) {
                        $grouped[$r['jenis']][$r['companycode']][] = $r;
                    }
                    foreach ($grouped as $j => $c) { ksort($grouped[$j]); }
                }

                $row = 5;

                if ($reportType === 'harian') {
                    foreach ($grouped as $tanggal => $items) {
                        // Group header
                        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                        $sheet->setCellValue("A{$row}", 'TANGGAL: ' . date('d/m/Y', strtotime($tanggal)));
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($headerFill('DBEAFE'));
                        $sheet->getRowDimension($row)->setRowHeight(18);
                        $row++;

                        // Column headers
                        foreach ($colHeaders as $ci => $ch) {
                            $sheet->setCellValue([$ci + 1, $row], $ch);
                        }
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($headerFill('E5E7EB'));
                        $sheet->getRowDimension($row)->setRowHeight(30);
                        $row++;

                        foreach ($items as $no => $item) {
                            $vals = [
                                $no + 1, ucfirst($item['jenis']), $item['suratjalanno'],
                                $item['nomorpolisi'], $item['companycode'], $item['plot'],
                                $item['namakontraktor'], $item['namasubkontraktor'],
                                $fmt3($item['pucuk']), $fmt3($item['daungulma']), $fmt3($item['sogolan']),
                                $fmt3($item['siwilan']), $fmt3($item['tebumati']), $fmt3($item['tanahetc']),
                                $fmt3($item['total']), $fmt3($item['toleransi']), $fmt3($item['nettotrash']),
                            ];
                            foreach ($vals as $ci => $v) {
                                $sheet->setCellValue([$ci + 1, $row], $v);
                            }
                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($dataBorder);
                            // right-align numeric cols I–Q
                            $sheet->getStyle("I{$row}:{$lastCol}{$row}")->applyFromArray($right);
                            $row++;
                        }
                        $row++; // blank line between dates
                    }
                } else {
                    // Mingguan
                    foreach ($grouped as $jenis => $companies) {
                        $jenisColor = $jenis === 'manual' ? 'D1FAE5' : 'DBEAFE';
                        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                        $sheet->setCellValue("A{$row}", 'JENIS: ' . strtoupper($jenis));
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($headerFill($jenisColor));
                        $row++;

                        foreach ($companies as $compCode => $items) {
                            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                            $sheet->setCellValue("A{$row}", $compCode);
                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($headerFill('F3F4F6'));
                            $row++;

                            foreach ($colHeaders as $ci => $ch) {
                                $sheet->setCellValue([$ci + 1, $row], $ch);
                            }
                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($headerFill('E5E7EB'));
                            $sheet->getRowDimension($row)->setRowHeight(30);
                            $row++;

                            foreach ($items as $no => $item) {
                                $vals = [
                                    $no + 1, date('d/m/Y', strtotime($item['tanggalangkut'])),
                                    $item['suratjalanno'], $item['nomorpolisi'],
                                    $item['companycode'], $item['plot'],
                                    $item['namakontraktor'], $item['namasubkontraktor'],
                                    $fmt3($item['pucuk']), $fmt3($item['daungulma']), $fmt3($item['sogolan']),
                                    $fmt3($item['siwilan']), $fmt3($item['tebumati']), $fmt3($item['tanahetc']),
                                    $fmt3($item['total']), $fmt3($item['toleransi']), $fmt3($item['nettotrash']),
                                ];
                                foreach ($vals as $ci => $v) {
                                    $sheet->setCellValue([$ci + 1, $row], $v);
                                }
                                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($dataBorder);
                                $sheet->getStyle("I{$row}:{$lastCol}{$row}")->applyFromArray($right);
                                $row++;
                            }
                            $row++;
                        }
                        $row++;
                    }
                }

                $filename = 'Trash_' . ucfirst($reportType) . '_' . $startDate . '_' . $endDate . '.xlsx';

            // ════════════════════════════════════════════════════════════════
            } else { // bulanan

                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Bulanan');

                $mn = str_pad((string)$month, 2, '0', STR_PAD_LEFT);
                $sheet->mergeCells('A1:M1');
                $sheet->setCellValue('A1', 'RATA-RATA TRASH KEBUN ' . strtoupper($monthNames[$mn] ?? $mn) . ' ' . $year);
                $sheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

                $sheet->mergeCells('A2:M2');
                $sheet->setCellValue('A2', 'SUNGAI BUDI GROUP');
                $sheet->getStyle('A2')->applyFromArray(['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

                $sheet->mergeCells('A3:M3');
                $sheet->setCellValue('A3', 'Periode: ' . ($monthNames[$mn] ?? $mn) . ' ' . $year);
                $sheet->getStyle('A3')->applyFromArray($center);

                $sheet->mergeCells('A4:M4');
                $sheet->setCellValue('A4', 'Dicetak: ' . date('d/m/Y H:i'));
                $sheet->getStyle('A4')->applyFromArray($center);

                // Header row 1 (row 5)
                $h1 = ['Asal Tebu','Tonase','Pucuk (%)','Daun Gulma (%)','Sogolan (%)','Siwilan (%)','Tebu Mati (%)','Tanah dll (%)','Total Trash','Trash % Bruto','Trash % Netto (Pot 5%)','KG Trash Bruto','KG Trash Netto'];
                $colW = [18, 12, 11, 14, 11, 11, 13, 13, 12, 13, 18, 14, 14];
                foreach ($h1 as $ci => $h) {
                    $sheet->setCellValue([$ci + 1, 5], $h);
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($ci + 1))->setWidth($colW[$ci]);
                }
                $sheet->getStyle('A5:M5')->applyFromArray($headerFill('E5E7EB'));
                $sheet->getRowDimension(5)->setRowHeight(30);

                // Build grouped by company (same as blade)
                $companyGroups = [];
                foreach ($rows as $r) {
                    $cc = $r['companycode'];
                    if (str_starts_with($cc, 'TBL'))      $prefix = 'TBL';
                    elseif (str_starts_with($cc, 'BNL'))  $prefix = 'BNL';
                    elseif (str_starts_with($cc, 'SIL'))  $prefix = 'SIL';
                    else                                   $prefix = $cc;
                    $companyGroups[$prefix][$cc][] = $r;
                }

                $row = 6;
                foreach ($companyGroups as $groupName => $companies) {
                    $groupTotals = array_fill_keys(['pucuk','daun','sogolan','siwilan','tebumati','tanah','tonase','count'], 0);

                    foreach ($companies as $compCode => $items) {
                        $byJenis = [];
                        foreach ($items as $it) {
                            $byJenis[$it['jenis']][] = $it;
                        }
                        // manual first
                        if (isset($byJenis['mesin'])) { $tmp = $byJenis['mesin']; unset($byJenis['mesin']); $byJenis['mesin'] = $tmp; }
                        uksort($byJenis, fn($a,$b) => $a === 'manual' ? -1 : 1);

                        foreach ($byJenis as $jenis => $jenisItems) {
                            $cnt = count($jenisItems);
                            $tp = $td = $ts = $tsw = $ttm = $ttn = $ttonase = 0;
                            foreach ($jenisItems as $it) {
                                $tp += $it['pucuk']; $td += $it['daungulma'];
                                $ts += $it['sogolan']; $tsw += $it['siwilan'];
                                $ttm += $it['tebumati']; $ttn += $it['tanahetc'];
                                $ttonase += $it['tonase_netto'];
                            }
                            $ap = $cnt > 0 ? $tp / $cnt : 0;
                            $ad = $cnt > 0 ? $td / $cnt : 0;
                            $as = $cnt > 0 ? $ts / $cnt : 0;
                            $asw = $cnt > 0 ? $tsw / $cnt : 0;
                            $atm = $cnt > 0 ? $ttm / $cnt : 0;
                            $atn = $cnt > 0 ? $ttn / $cnt : 0;
                            $totalT = $ap + $ad + $as + $asw + $atm + $atn;
                            $nettoT = max(0, $totalT - 5);
                            $kgBruto = ($totalT / 100) * $ttonase;
                            $kgNetto = ($nettoT / 100) * $ttonase;

                            $groupTotals['pucuk']   += $ap; $groupTotals['daun']   += $ad;
                            $groupTotals['sogolan'] += $as; $groupTotals['siwilan'] += $asw;
                            $groupTotals['tebumati'] += $atm; $groupTotals['tanah'] += $atn;
                            $groupTotals['tonase']  += $ttonase; $groupTotals['count']++;

                            $vals = [
                                $compCode . ' (' . ucfirst($jenis) . ')',
                                $ttonase,
                                $fmt3($ap), $fmt3($ad), $fmt3($as), $fmt3($asw), $fmt3($atm), $fmt3($atn),
                                $fmt3($totalT), $fmt3($totalT), $fmt3($nettoT),
                                number_format($kgBruto, 0, '.', ''), number_format($kgNetto, 0, '.', ''),
                            ];
                            foreach ($vals as $ci => $v) {
                                $sheet->setCellValue([$ci + 1, $row], $v);
                            }
                            $sheet->getStyle("A{$row}:M{$row}")->applyFromArray($dataBorder);
                            $sheet->getStyle("B{$row}:M{$row}")->applyFromArray($right);
                            $row++;
                        }
                    }

                    // Group total row
                    $gc = $groupTotals['count'];
                    $gAP = $gc > 0 ? $groupTotals['pucuk'] / $gc : 0;
                    $gAD = $gc > 0 ? $groupTotals['daun'] / $gc : 0;
                    $gAS = $gc > 0 ? $groupTotals['sogolan'] / $gc : 0;
                    $gASW = $gc > 0 ? $groupTotals['siwilan'] / $gc : 0;
                    $gATM = $gc > 0 ? $groupTotals['tebumati'] / $gc : 0;
                    $gATN = $gc > 0 ? $groupTotals['tanah'] / $gc : 0;
                    $gTotal = $gAP + $gAD + $gAS + $gASW + $gATM + $gATN;
                    $gNetto = max(0, $gTotal - 5);
                    $gTonase = $groupTotals['tonase'];
                    $gKgBruto = ($gTonase * $gTotal) / 100;
                    $gKgNetto = ($gTonase * $gNetto) / 100;
                    $gPctBruto = $gTonase > 0 ? ($gKgBruto / $gTonase) * 100 : 0;
                    $gPctNetto = $gTonase > 0 ? ($gKgNetto / $gTonase) * 100 : 0;

                    $totalVals = [
                        $groupName, number_format($gTonase, 0, '.', ''),
                        $fmt3($gAP), $fmt3($gAD), $fmt3($gAS), $fmt3($gASW), $fmt3($gATM), $fmt3($gATN),
                        $fmt3($gTotal), $fmt3($gPctBruto), $fmt3($gPctNetto),
                        number_format($gKgBruto, 0, '.', ''), number_format($gKgNetto, 0, '.', ''),
                    ];
                    foreach ($totalVals as $ci => $v) {
                        $sheet->setCellValue([$ci + 1, $row], $v);
                    }
                    $sheet->getStyle("A{$row}:M{$row}")->applyFromArray($headerFill('D1D5DB'));
                    $sheet->getStyle("B{$row}:M{$row}")->applyFromArray($right);
                    $row += 2; // blank line between groups
                }

                $filename = 'Trash_Bulanan_' . ($monthNames[$mn] ?? $mn) . '_' . $year . '.xlsx';
            }

            // ── freeze header & output ────────────────────────────────────
            $writer = new Xlsx($spreadsheet);

            $headers = [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control'       => 'max-age=0',
            ];

            return response()->stream(function () use ($writer) {
                $writer->save('php://output');
            }, 200, $headers);

        } catch (\Exception $e) {
            return redirect()->route('pabrik.trash.index')->with('error', 'Gagal export Excel: ' . $e->getMessage());
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
