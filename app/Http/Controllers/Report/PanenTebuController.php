<?php

namespace App\Http\Controllers\Report;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use App\Models\Timbangan;
use App\Models\PanenTebuHistory;
use Illuminate\Support\Facades\Auth;

class PanenTebuController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Report',
        ]);
    }

    public function index(Request $request)
    {
        $title = "Report Panen Tebu";
        $nav = "Panen Tebu";
        $kontraktor = DB::table('kontraktor')->where('companycode', session('companycode'))->get();
        $tabel_harga = DB::table('hargapanentebu')->where('companycode', session('companycode'))->get();
        
        // Get history data for DataTables (all data, no pagination)
        $history = PanenTebuHistory::where('companycode', session('companycode'))
                    ->orderBy('createdat', 'desc')
                    ->get();
        
        return view('report.panen-tebu.index', compact('title', 'nav', 'kontraktor', 'tabel_harga', 'history'));
    }

    public function proses(Request $request)
    {
        $companycode = session('companycode');
        
        $timbangan = new Timbangan;
        $data = $timbangan->getData($companycode, $request->idkontraktor, $request->start_date, $request->end_date);
        
        // Check if data is empty
        if (empty($data) || count($data) == 0) {
            return redirect()->back()->with('error', 'Data tidak ditemukan untuk periode dan kontraktor yang dipilih. Tidak ada data untuk di-generate.');
        }
        
        $tabel_harga = DB::table('hargapanentebu')->where('companycode', session('companycode'))->where('kodeharga', $request->kode_harga)->get();

        // Get kontraktor name for history
        $kontraktor_data = DB::table('kontraktor')->where('companycode', $companycode)->where('id', $request->idkontraktor)->first();
        
        // Calculate grand total for history
        $grandTotalPremiumManual = 0;
        $grandTotalNonPremiumManual = 0;
        $grandTotalPremiumGL = 0;
        $grandTotalNonPremiumGL = 0;
        $grandTotalKirimKontraktor = 0;
        $grandTotalExtraFooding = 0;
        $grandTotalTebuTidakSeset = 0;
        $grandTotalTebuSulit = 0;
        $grandTotalLangsir = 0;
        $grandTotalInsentifBSM = 0;
        
        foreach ($data as $dt) {
            $trashKebunCalc = ($dt->trash_percentage > 3) ? $dt->trash_percentage - 3 : 0;
            $potKgCalc = ($trashKebunCalc > 0) ? round($dt->netto * $trashKebunCalc / 100, 0, PHP_ROUND_HALF_UP) : 0;
            $beratBersihCalc = $dt->netto - $potKgCalc;
            
            if($dt->kodetebang == 'Premium' && $dt->muatgl == '0') $grandTotalPremiumManual += $beratBersihCalc;
            if($dt->kodetebang != 'Premium' && $dt->muatgl == '0') $grandTotalNonPremiumManual += $beratBersihCalc;
            if($dt->kodetebang == 'Premium' && $dt->muatgl == '1') $grandTotalPremiumGL += $beratBersihCalc;
            if($dt->kodetebang != 'Premium' && $dt->muatgl == '1') $grandTotalNonPremiumGL += $beratBersihCalc;
            if($dt->kendaraankontraktor == 1) $grandTotalKirimKontraktor += $dt->netto;
            if($dt->kendaraankontraktor == 0) $grandTotalExtraFooding += $dt->netto;
            $grandTotalTebuTidakSeset += $beratBersihCalc;
            if($dt->tebusulit == 1) $grandTotalTebuSulit += $beratBersihCalc;
            if($dt->langsir == 1) $grandTotalLangsir += $beratBersihCalc;
            if(!empty($dt->averagescore) && $dt->averagescore < 1200) $grandTotalInsentifBSM += $beratBersihCalc;
        }
        
        $finalTotal = 
            ($grandTotalPremiumManual * (($tabel_harga[0]->manualtebang ?? 0) + ($tabel_harga[0]->manualmuat ?? 0))) +
            ($grandTotalNonPremiumManual * ($tabel_harga[0]->manualnonpremi ?? 0)) +
            ($grandTotalPremiumGL * (($tabel_harga[0]->glkebuntebang ?? 0) + ($tabel_harga[0]->glkebunmuat ?? 0))) +
            ($grandTotalNonPremiumGL * ($tabel_harga[0]->glkebunnonpremi ?? 0)) +
            ($grandTotalKirimKontraktor * 35) +
            ($grandTotalExtraFooding * ($tabel_harga[0]->extrafooding ?? 0)) +
            ($grandTotalTebuTidakSeset * ($tabel_harga[0]->tebutdkseset ?? 0)) +
            ($grandTotalTebuSulit * ($tabel_harga[0]->manualtebusulit ?? 0)) +
            ($grandTotalLangsir * ($tabel_harga[0]->langsir ?? 0)) +
            ($grandTotalInsentifBSM * ($tabel_harga[0]->manualbsm ?? 0));

        // ============================================================
        // PERHITUNGAN HARGA PER PLOT
        // ============================================================
        
        // Group data by plot
        $dataGroupedByPlot = collect($data)->groupBy('plot');
        
        $hargaPerPlot = [];
        
        foreach ($dataGroupedByPlot as $plot => $dataPerPlot) {
            // Initialize variables per plot
            $plotPremiumManual = 0;
            $plotNonPremiumManual = 0;
            $plotPremiumGL = 0;
            $plotNonPremiumGL = 0;
            $plotKirimKontraktor = 0;
            $plotExtraFooding = 0;
            $plotTebuTidakSeset = 0;
            $plotTebuSulit = 0;
            $plotLangsir = 0;
            $plotInsentifBSM = 0;
            
            // Calculate totals per plot
            foreach ($dataPerPlot as $dt) {
                $trashKebunCalc = ($dt->trash_percentage > 3) ? $dt->trash_percentage - 3 : 0;
                $potKgCalc = ($trashKebunCalc > 0) ? round($dt->netto * $trashKebunCalc / 100, 0, PHP_ROUND_HALF_UP) : 0;
                $beratBersihCalc = $dt->netto - $potKgCalc;
                
                if($dt->kodetebang == 'Premium' && $dt->muatgl == '0') $plotPremiumManual += $beratBersihCalc;
                if($dt->kodetebang != 'Premium' && $dt->muatgl == '0') $plotNonPremiumManual += $beratBersihCalc;
                if($dt->kodetebang == 'Premium' && $dt->muatgl == '1') $plotPremiumGL += $beratBersihCalc;
                if($dt->kodetebang != 'Premium' && $dt->muatgl == '1') $plotNonPremiumGL += $beratBersihCalc;
                if($dt->kendaraankontraktor == 1) $plotKirimKontraktor += $dt->netto;
                if($dt->kendaraankontraktor == 0) $plotExtraFooding += $dt->netto;
                $plotTebuTidakSeset += $beratBersihCalc;
                if($dt->tebusulit == 1) $plotTebuSulit += $beratBersihCalc;
                if($dt->langsir == 1) $plotLangsir += $beratBersihCalc;
                if(!empty($dt->averagescore) && $dt->averagescore < 1200) $plotInsentifBSM += $beratBersihCalc;
            }
            
            // Calculate grand total per plot (same logic as overall grand total)
            $plotGrandTotal = 
                ($plotPremiumManual * (($tabel_harga[0]->manualtebang ?? 0) + ($tabel_harga[0]->manualmuat ?? 0))) +
                ($plotNonPremiumManual * ($tabel_harga[0]->manualnonpremi ?? 0)) +
                ($plotPremiumGL * (($tabel_harga[0]->glkebuntebang ?? 0) + ($tabel_harga[0]->glkebunmuat ?? 0))) +
                ($plotNonPremiumGL * ($tabel_harga[0]->glkebunnonpremi ?? 0)) +
                ($plotKirimKontraktor * 35) +
                ($plotExtraFooding * ($tabel_harga[0]->extrafooding ?? 0)) +
                ($plotTebuTidakSeset * ($tabel_harga[0]->tebutdkseset ?? 0)) +
                ($plotTebuSulit * ($tabel_harga[0]->manualtebusulit ?? 0)) +
                ($plotLangsir * ($tabel_harga[0]->langsir ?? 0)) +
                ($plotInsentifBSM * ($tabel_harga[0]->manualbsm ?? 0));
            
            // Add to array
            $hargaPerPlot[] = [
                'plot' => $plot,
                'grandtotal' => number_format($plotGrandTotal, 2, '.', '')
            ];
        }
        
        // Sort by plot name (optional, for better readability)
        usort($hargaPerPlot, function($a, $b) {
            return strcmp($a['plot'], $b['plot']);
        });
        
        // ============================================================
        // END PERHITUNGAN HARGA PER PLOT
        // ============================================================

        // Save to history - nodoc will be auto-generated
        $history = PanenTebuHistory::create([
            'companycode' => $companycode,
            'userid' => Auth::user()->name ?? 'System',
            'idkontraktor' => $request->idkontraktor,
            'namakontraktor' => $kontraktor_data->namakontraktor ?? '',
            'kodeharga' => $request->kode_harga,
            'startdate' => $request->start_date,
            'enddate' => $request->end_date,
            'grandtotal' => $finalTotal,
            'dataresult' => $data,
            'hargasnapshot' => $tabel_harga->toArray(),
            'hargaperplot' => $hargaPerPlot
        ]);

        $viewData = [
            'data' => collect($data),
            'kontraktor' => $request->idkontraktor,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'tabelharga' => collect($tabel_harga),
            'history_nodoc' => $history->nodoc
        ];
        
        return view('report.panen-tebu.result', $viewData);
    }

    // Method to show history result
    public function show($nodoc)
    {
        $companycode = session('companycode');
        $history = PanenTebuHistory::where('companycode', $companycode)->findOrFail($nodoc);
        
        // Convert array data to object to maintain consistency with fresh data
        $dataResult = collect($history->dataresult)->map(function($item) {
            return is_array($item) ? (object) $item : $item;
        });
        
        $viewData = [
            'data' => $dataResult,
            'kontraktor' => $history->idkontraktor,
            'startDate' => $history->startdate->format('Y-m-d'),
            'endDate' => $history->enddate->format('Y-m-d'),
            'tabelharga' => collect($history->hargasnapshot)->map(function($item) {
                return is_array($item) ? (object) $item : $item;
            }),
            'history_nodoc' => $history->nodoc,
            'is_history' => true
        ];
        
        return view('report.panen-tebu.result', $viewData);
    }

    // Method to delete history
    public function destroy($nodoc)
    {
        try {
            $companycode = session('companycode');
            $history = PanenTebuHistory::where('companycode', $companycode)->findOrFail($nodoc);
            
            // Store info for success message
            $historyInfo = "No Doc: " . $history->nodoc . 
                          " (" . $history->namakontraktor . " - " . 
                          $history->startdate->format('d M Y') . " s/d " . 
                          $history->enddate->format('d M Y') . ")";
            
            $history->delete();
            
            return redirect()->route('report.panen-tebu-report.index')
                           ->with('success', 'History report berhasil dihapus: ' . $historyInfo);
        } catch (\Exception $e) {
            return redirect()->route('report.panen-tebu-report.index')
                           ->with('error', 'Gagal menghapus history report: ' . $e->getMessage());
        }
    }
}