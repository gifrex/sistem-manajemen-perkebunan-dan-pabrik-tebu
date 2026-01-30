<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardPanenController extends Controller
{
    public function index()
    {
        $title = 'Dashboard Panen';
        $navbar = 'Dashboard';
        $nav = 'Panen';

        return view('dashboard.panen.index', compact('title', 'navbar', 'nav'));
    }

    public function getData(Request $request)
    {
        try {
            $startDate = $request->start_date ?: Carbon::today()->format('Y-m-d');
            $endDate = $request->end_date ?: Carbon::today()->format('Y-m-d');
            
            // Get ALL companies from company table
            $companies = $this->getCompanies();
            
            // Get data for each company (even if empty)
            $companiesData = [];
            foreach ($companies as $company) {
                $companiesData[] = $this->getCompanyData($company, $startDate, $endDate);
            }
            
            // Calculate grand total
            $grandTotal = [
                'total_sj' => array_sum(array_column($companiesData, 'total_sj')),
                'total_netto' => array_sum(array_column($companiesData, 'total_netto')),
                'sudah_timbang' => array_sum(array_column($companiesData, 'sudah_timbang')),
                'pending_timbangan' => array_sum(array_column($companiesData, 'pending_timbangan')),
                'avg_durasi_deload' => $this->calculateWeightedAverage($companiesData, 'avg_durasi_deload', 'sudah_timbang'),
                'avg_durasi_pos_timbang' => $this->calculateWeightedAverage($companiesData, 'avg_durasi_pos_timbang', 'sudah_timbang'),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'companies' => $companiesData,
                    'grandTotal' => $grandTotal,
                    'dateRange' => [
                        'start' => Carbon::parse($startDate)->format('d M Y'),
                        'end' => Carbon::parse($endDate)->format('d M Y')
                    ],
                    'isSingleDay' => $startDate === $endDate
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getCompanies()
    {
        // Get ALL companies from company table
        return DB::table('company')
            ->select('companycode', 'name')
            ->orderBy('companycode')
            ->get()
            ->map(function($item) {
                return [
                    'companycode' => $item->companycode,
                    'name' => $item->name
                ];
            })
            ->toArray();
    }

    private function getCompanyData($company, $startDate, $endDate)
    {
        $companyCode = $company['companycode'];
        $companyName = $company['name'];
        
        // Get surat jalan data for this company
        $query = DB::table('suratjalanpos as sj')
            ->leftJoin('timbanganpayload as tp', function($join) {
                $join->on('sj.companycode', '=', 'tp.companycode')
                    ->on('sj.suratjalanno', '=', 'tp.suratjalanno');
            })
            ->where('sj.companycode', $companyCode)
            ->whereBetween('sj.tanggalangkut', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        $details = $query->select(
            'sj.suratjalanno',
            'sj.tanggalangkut',
            'sj.tanggalcetakpossecurity',
            'tp.netto',
            'tp.tgl1',
            'tp.jam1',
            'tp.tgl2',
            'tp.jam2',
            DB::raw('CASE WHEN tp.suratjalanno IS NULL THEN "Pending" ELSE "Sudah Timbang" END as status')
        )
        ->get()
        ->map(function($item) {
            $item->durasi_deload = null;
            if ($item->jam1 && $item->jam2) {
                try {
                    $masuk = Carbon::parse($item->tgl1 . ' ' . $item->jam1);
                    $keluar = Carbon::parse($item->tgl2 . ' ' . $item->jam2);
                    $item->durasi_deload = $masuk->diffInMinutes($keluar, true);
                } catch (\Exception $e) {}
            }
            
            $item->durasi_pos_timbangan = null;
            if ($item->tanggalcetakpossecurity && $item->tgl1 && $item->jam1) {
                try {
                    $cetak = Carbon::parse($item->tanggalcetakpossecurity);
                    $masuk = Carbon::parse($item->tgl1 . ' ' . $item->jam1);
                    $item->durasi_pos_timbangan = $cetak->diffInMinutes($masuk, true);
                } catch (\Exception $e) {}
            }
            
            return $item;
        });

        $total_sj = $details->count();
        $sudah_timbang = $details->where('status', 'Sudah Timbang')->count();
        $pending_timbangan = $details->where('status', 'Pending')->count();
        $total_netto = $details->whereNotNull('netto')->sum('netto');
        
        $timbangData = $details->where('status', 'Sudah Timbang');
        $avg_durasi_deload = $timbangData->whereNotNull('durasi_deload')->avg('durasi_deload');
        $avg_durasi_pos_timbang = $timbangData->whereNotNull('durasi_pos_timbangan')->avg('durasi_pos_timbangan');

        return [
            'companycode' => $companyCode,
            'companyname' => $companyName,
            'total_sj' => $total_sj,
            'total_netto' => $total_netto,
            'sudah_timbang' => $sudah_timbang,
            'pending_timbangan' => $pending_timbangan,
            'percentage_done' => $total_sj > 0 ? round(($sudah_timbang / $total_sj) * 100, 1) : 0,
            'avg_durasi_deload' => $avg_durasi_deload ? round($avg_durasi_deload, 2) : null,
            'avg_durasi_pos_timbang' => $avg_durasi_pos_timbang ? round($avg_durasi_pos_timbang, 2) : null,
        ];
    }

    private function calculateWeightedAverage($data, $field, $weight)
    {
        $totalWeight = 0;
        $weightedSum = 0;
        
        foreach ($data as $item) {
            if ($item[$field] !== null && $item[$weight] > 0) {
                $weightedSum += $item[$field] * $item[$weight];
                $totalWeight += $item[$weight];
            }
        }
        
        return $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : null;
    }
}