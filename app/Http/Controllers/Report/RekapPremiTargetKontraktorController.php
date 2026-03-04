<?php
namespace App\Http\Controllers\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use App\Models\PanenTebuHistory;

class RekapPremiTargetKontraktorController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Report',
        ]);
    }

    public function index()
    {
        $title       = "Rekapitulasi Premi Target Kontraktor";
        $nav         = "Rekapitulasi Premi Target Kontraktor";
        $kontraktor  = DB::table('kontraktor')->where('companycode', session('companycode'))->get();
        $tabel_harga = DB::table('hargapanentebu')->where('companycode', session('companycode'))->get();
        $history     = PanenTebuHistory::where('companycode', session('companycode'))->orderBy('createdat', 'desc')->get();

        $searchResults  = null;
        $searchParams   = null;
        $missingDates   = [];   // tanggal yang tidak ada data di dokumen
        $jumlahHariBulan = 0;

        return view('report.rekapitulasi-premi.index', compact(
            'title', 'nav', 'kontraktor', 'tabel_harga', 'history',
            'searchResults', 'searchParams', 'missingDates', 'jumlahHariBulan'
        ));
    }

    public function search(Request $request)
    {
        $request->validate([
            'idkontraktor' => 'required',
            'bulan'        => 'required|string|size:2',
            'tahun'        => 'required|string|size:4',
        ]);

        $idkontraktor = $request->idkontraktor;
        $bulan        = $request->bulan;
        $tahun        = $request->tahun;

        $startOfMonth = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->toDateString();
        $endOfMonth   = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();

        $searchResults = PanenTebuHistory::where('companycode', session('companycode'))
            ->where('idkontraktor', $idkontraktor)
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('startdate', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('enddate', [$startOfMonth, $endOfMonth])
                    ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                        $q->where('startdate', '<=', $startOfMonth)
                          ->where('enddate', '>=', $endOfMonth);
                    });
            })
            ->orderBy('createdat', 'desc')
            ->get();

        $searchParams = [
            'idkontraktor'   => $idkontraktor,
            'bulan'          => $bulan,
            'tahun'          => $tahun,
            'namakontraktor' => $searchResults->first()?->namakontraktor ?? '',
        ];

        // -------------------------------------------------------
        // Hitung tanggal yang TIDAK ADA data di seluruh dokumen
        // -------------------------------------------------------
        $missingDates    = [];
        $jumlahHariBulan = 0;

        if ($searchResults->isNotEmpty()) {
            // Kumpulkan semua tanggal yang ada di dataresult seluruh dokumen
            $tanggalAda = collect();
            foreach ($searchResults as $doc) {
                $rows = is_array($doc->dataresult)
                    ? $doc->dataresult
                    : json_decode($doc->dataresult, true);

                if (is_array($rows)) {
                    foreach ($rows as $row) {
                        $tgl = is_array($row) ? ($row['tanggalangkut'] ?? null) : ($row->tanggalangkut ?? null);
                        if ($tgl) {
                            $tanggalAda->push(Carbon::parse($tgl)->format('Y-m-d'));
                        }
                    }
                }
            }
            $tanggalAda = $tanggalAda->unique()->values();

            // Generate semua hari dalam bulan yang dipilih
            $jumlahHariBulan = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;
            for ($day = 1; $day <= $jumlahHariBulan; $day++) {
                $tglCheck = Carbon::createFromDate($tahun, $bulan, $day)->format('Y-m-d');
                if (!$tanggalAda->contains($tglCheck)) {
                    $missingDates[] = $tglCheck;
                }
            }
        }

        $title       = "Rekapitulasi Premi Target Kontraktor";
        $nav         = "Rekapitulasi Premi Target Kontraktor";
        $kontraktor  = DB::table('kontraktor')->where('companycode', session('companycode'))->get();
        $tabel_harga = DB::table('hargapanentebu')->where('companycode', session('companycode'))->get();
        $history     = PanenTebuHistory::where('companycode', session('companycode'))->orderBy('createdat', 'desc')->get();

        return view('report.rekapitulasi-premi.index', compact(
            'title', 'nav', 'kontraktor', 'tabel_harga', 'history',
            'searchResults', 'searchParams', 'missingDates', 'jumlahHariBulan'
        ));
    }

    public function proses(Request $request)
    {
        $request->validate([
            'idkontraktor' => 'required',
            'bulan'        => 'required|string|size:2',
            'tahun'        => 'required|string|size:4',
        ]);

        $idkontraktor = $request->idkontraktor;
        $bulan        = $request->bulan;
        $tahun        = $request->tahun;

        // Ambil alasan tanggal kosong dari form (array: ['2025-11-30' => 'Libur nasional', ...])
        $alasanTanggal = $request->input('alasan', []);

        $startOfMonth = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->toDateString();
        $endOfMonth   = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();

        $documents = PanenTebuHistory::where('companycode', session('companycode'))
            ->where('idkontraktor', $idkontraktor)
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('startdate', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('enddate', [$startOfMonth, $endOfMonth])
                    ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                        $q->where('startdate', '<=', $startOfMonth)
                          ->where('enddate', '>=', $endOfMonth);
                    });
            })
            ->orderBy('startdate', 'asc')
            ->get();

        if ($documents->isEmpty()) {
            return redirect()
                ->route('report.rekapitulasi-premi-report.index')
                ->with('error', 'Tidak ada data ditemukan untuk kontraktor dan periode yang dipilih.');
        }

        // Gabungkan semua dataresult
        $allData = collect();
        foreach ($documents as $doc) {
            $rows = is_array($doc->dataresult)
                ? $doc->dataresult
                : json_decode($doc->dataresult, true);

            if (is_array($rows)) {
                $allData = $allData->merge($rows);
            }
        }

        // Handle hargasnapshot
        $firstDoc = $documents->first();
        $hargaRaw = is_array($firstDoc->hargasnapshot)
            ? $firstDoc->hargasnapshot
            : json_decode($firstDoc->hargasnapshot, true);

        if (is_array($hargaRaw) && isset($hargaRaw[0])) {
            $tabelharga = collect($hargaRaw);
        } else {
            $tabelharga = collect([$hargaRaw]);
        }

        $namaKontraktor = $firstDoc->namakontraktor;

        // Ambil target dari tabel targetkontraktor (companycode + idkontraktor + bulan + tahun)
        $targetRow = DB::table('targetkontraktor')
            ->where('companycode', session('companycode'))
            ->where('idkontraktor', $idkontraktor)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();
        $targetPerHari = $targetRow ? (float) $targetRow->target : 0;

        $namaBulan = [
            '01' => 'Januari',  '02' => 'Februari', '03' => 'Maret',
            '04' => 'April',    '05' => 'Mei',       '06' => 'Juni',
            '07' => 'Juli',     '08' => 'Agustus',   '09' => 'September',
            '10' => 'Oktober',  '11' => 'November',  '12' => 'Desember',
        ];
        $periodeLabel = ($namaBulan[$bulan] ?? $bulan) . ' ' . $tahun;

        $data = $allData->sortBy('tanggalangkut')->values();

        // Bangun array alasan yang bersih untuk dikirim ke view
        // Format: [ ['tanggal' => '2025-11-30', 'alasan' => 'Libur nasional'], ... ]
        $alasanList = [];
        foreach ($alasanTanggal as $tgl => $alasan) {
            $alasan = trim($alasan);
            if ($alasan !== '') {
                $alasanList[] = [
                    'tanggal' => $tgl,
                    'alasan'  => $alasan,
                ];
            }
        }
        // Sort by tanggal ascending
        usort($alasanList, fn($a, $b) => strcmp($a['tanggal'], $b['tanggal']));

        return view('report.rekapitulasi-premi.result', compact(
            'data',
            'tabelharga',
            'idkontraktor',
            'namaKontraktor',
            'periodeLabel',
            'bulan',
            'tahun',
            'documents',
            'alasanList',
            'targetPerHari'
        ));
    }
}