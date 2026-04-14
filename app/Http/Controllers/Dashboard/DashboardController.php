<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Dashboard',
        ]);
    }

    public function agronomi(Request $request)
    {
        // Jika request AJAX, kembalikan data chart saja
        if ($request->ajax()) {
            return response()->json($this->getAgronomiChartData($request));
        }

        // Initial page load
        $title = "Dashboard Agronomi";
        $nav = "Agronomi";

        $verticalLabels = [
            'per_germinasi' => '% Germinasi',
            'per_gap' => '% GAP',
            'populasi' => 'Populasi',
            'per_gulma' => '% Penutupan Gulma',
            'ph_tanah' => 'pH Tanah',
        ];

        $monthsLabel = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];

        // Get filter options
        $kdCompAgroOpt = DB::table('company')
            ->join('agrohdr', 'company.companycode', '=', 'agrohdr.companycode')
            ->select('company.companycode', 'company.name')
            ->orderBy('company.companycode', 'ASC')
            ->distinct()
            ->get();

        $kdBlokAgroOpt = DB::table('blok')
            ->join('agrohdr', 'blok.blok', '=', 'agrohdr.blok')
            ->select('blok.blok')
            ->orderBy('blok.blok', 'asc')
            ->distinct()
            ->get();

        $kdPlotAgroOpt = DB::table('batch')
            ->join('agrohdr', 'batch.plot', '=', 'agrohdr.plot')
            ->select('batch.plot')
            ->orderByRaw("LEFT(batch.plot, 1), CAST(SUBSTRING(batch.plot, 2) AS UNSIGNED)")
            ->distinct()
            ->get();

        return view('dashboard.agronomi.index', compact(
            'verticalLabels',
            'kdCompAgroOpt',
            'kdBlokAgroOpt',
            'kdPlotAgroOpt',
            'title',
            'nav',
            'monthsLabel'
        ));
    }

    private function getAgronomiChartData(Request $request)
    {
        $kdCompAgronomi = $request->input('companycode', []);
        $kdBlokAgronomi = $request->input('blok', []);
        $kdPlotAgronomi = $request->input('plot', []);
        $startMonth = $request->input('start_month');
        $endMonth = $request->input('end_month');
        $startYear = $request->input('start_year', now()->format('Y'));
        $endYear = $request->input('end_year', now()->format('Y'));
        $minAge = $request->input('min_age');
        $maxAge = $request->input('max_age');
        $ageUnit = $request->input('age_unit', 'bulan');
        $verticalField = $request->input('vertical', 'per_germinasi');

        $verticalLabels = [
            'per_germinasi' => '% Germinasi',
            'per_gap' => '% GAP',
            'populasi' => 'Populasi',
            'per_gulma' => '% Penutupan Gulma',
            'ph_tanah' => 'pH Tanah',
        ];
        $verticalLabel = $verticalLabels[$verticalField] ?? ucfirst($verticalField);

        $chartData = [];
        $xAxis = [];
        $horizontalLabel = '';

        $months = [
            'January' => 1,
            'February' => 2,
            'March' => 3,
            'April' => 4,
            'May' => 5,
            'June' => 6,
            'July' => 7,
            'August' => 8,
            'September' => 9,
            'October' => 10,
            'November' => 11,
            'December' => 12
        ];

        $startMonthNum = $months[$startMonth] ?? null;
        $endMonthNum = $months[$endMonth] ?? null;

        if (
            !empty($kdCompAgronomi) || !empty($kdBlokAgronomi) || !empty($kdPlotAgronomi) ||
            ($startMonthNum && $endMonthNum) || ($minAge && $maxAge)
        ) {

            $chartDataQuery = DB::table('agrohdr')
                ->join('agrolst', function ($join) {
                    $join->on('agrohdr.nosample', '=', 'agrolst.nosample')
                        ->on('agrohdr.companycode', '=', 'agrolst.companycode');
                })
                ->join('company', 'agrohdr.companycode', '=', 'company.companycode')
                ->join('batch', function ($join) {
                    $join->on('agrohdr.plot', '=', 'batch.plot')
                        ->on('agrohdr.companycode', '=', 'batch.companycode');
                })
                ->leftJoin('blok', function ($join) {
                    $join->on('agrohdr.blok', '=', 'blok.blok')
                        ->whereColumn('agrohdr.companycode', '=', 'blok.companycode');
                })
                ->select(
                    DB::raw("MONTH(agrohdr.tanggalpengamatan) as bln_amat"),
                    DB::raw("MIN(agrohdr.tanggaltanam) as tanggaltanam"),
                    'agrohdr.kat',
                    DB::raw("CASE 
                        WHEN '$verticalField' IN ('populasi', 'ph_tanah') 
                        THEN AVG($verticalField) 
                        ELSE AVG($verticalField) * 100 
                    END as total"),
                    'company.name as company_nama',
                    'blok.blok as blok_nama',
                    'batch.plot as plot_nama',
                    DB::raw("ROUND(DATEDIFF(CURDATE(), MIN(agrohdr.tanggaltanam)) / 30) as umur_bulan")
                )
                ->when($kdCompAgronomi, fn($q) => $q->whereIn('agrohdr.companycode', $kdCompAgronomi))
                ->when($kdBlokAgronomi, fn($q) => $q->whereIn('agrohdr.blok', $kdBlokAgronomi))
                ->when($kdPlotAgronomi, fn($q) => $q->whereIn('agrohdr.plot', $kdPlotAgronomi))
                ->when($startMonthNum && $endMonthNum && $startYear && $endYear, function ($query) use ($startMonthNum, $endMonthNum, $startYear, $endYear) {
                    return $query->whereRaw("
                        (YEAR(agrohdr.tanggalpengamatan) > ? OR 
                        (YEAR(agrohdr.tanggalpengamatan) = ? AND MONTH(agrohdr.tanggalpengamatan) >= ?)) 
                        AND 
                        (YEAR(agrohdr.tanggalpengamatan) < ? OR 
                        (YEAR(agrohdr.tanggalpengamatan) = ? AND MONTH(agrohdr.tanggalpengamatan) <= ?))
                    ", [$startYear, $startYear, $startMonthNum, $endYear, $endYear, $endMonthNum]);
                })
                ->when($minAge && $maxAge, function ($query) use ($minAge, $maxAge, $ageUnit) {
                    if ($ageUnit === 'tahun') {
                        $minAgeMonths = $minAge * 12;
                        $maxAgeMonths = $maxAge * 12;
                        return $query->whereRaw(
                            "ROUND(DATEDIFF(CURDATE(), agrohdr.tanggaltanam) / 30) BETWEEN ? AND ?",
                            [$minAgeMonths, $maxAgeMonths]
                        );
                    }
                    return $query->whereRaw(
                        "ROUND(DATEDIFF(CURDATE(), agrohdr.tanggaltanam) / 30) BETWEEN ? AND ?",
                        [$minAge, $maxAge]
                    );
                })
                ->groupBy('bln_amat', 'kat', 'company.name', 'blok.blok', 'batch.plot')
                ->orderBy('kat');

            $chartDataResult = $chartDataQuery->get();
            $chartDataResult->transform(function ($item) {
                $item->umur_tanam = $item->umur_bulan . ' Bulan';
                return $item;
            });

            // Generate xAxis based on filters
            $xAxis = $chartDataResult->map(function ($item) use ($kdCompAgronomi, $kdBlokAgronomi, $kdPlotAgronomi) {
                if (!empty($kdCompAgronomi) && empty($kdBlokAgronomi) && empty($kdPlotAgronomi)) {
                    return $item->umur_tanam . ' - ' . $item->kat . ' - ' . $item->company_nama;
                } elseif (empty($kdCompAgronomi) && !empty($kdBlokAgronomi) && empty($kdPlotAgronomi)) {
                    return $item->umur_tanam . ' - ' . $item->kat . ' - ' . $item->blok_nama;
                } elseif (empty($kdCompAgronomi) && empty($kdBlokAgronomi) && !empty($kdPlotAgronomi)) {
                    return $item->plot_nama . ' - ' . $item->umur_tanam . ' - ' . $item->kat;
                } elseif (!empty($kdCompAgronomi) && empty($kdBlokAgronomi) && !empty($kdPlotAgronomi)) {
                    return $item->plot_nama . ' - ' . $item->umur_tanam . ' - ' . $item->kat . ' - ' . $item->company_nama;
                } elseif (empty($kdCompAgronomi) && !empty($kdBlokAgronomi) && !empty($kdPlotAgronomi)) {
                    return $item->plot_nama . ' - ' . $item->umur_tanam . ' - ' . $item->kat . ' - ' . $item->blok_nama;
                } elseif (!empty($kdCompAgronomi) && !empty($kdBlokAgronomi) && empty($kdPlotAgronomi)) {
                    return $item->umur_tanam . ' - ' . $item->kat . ' - ' . $item->blok_nama . ' - ' . $item->company_nama;
                } else {
                    return $item->plot_nama . ' - ' . $item->umur_tanam . ' - ' . $item->kat . ' - ' . $item->blok_nama . ' - ' . $item->company_nama;
                }
            })->unique()->values();

            $legends = $chartDataResult->pluck('bln_amat')->sort()->unique();

            foreach ($legends as $legend) {
                $data = [];
                foreach ($xAxis as $x) {
                    $data[] = round(
                        $chartDataResult->filter(function ($item) use ($legend, $x, $kdCompAgronomi, $kdBlokAgronomi, $kdPlotAgronomi) {
                            $parts = explode(' - ', $x);

                            if (empty($kdCompAgronomi) && !empty($kdBlokAgronomi) && empty($kdPlotAgronomi)) {
                                return $item->bln_amat == $legend &&
                                    $item->umur_tanam == $parts[0] &&
                                    $item->kat == $parts[1] &&
                                    $item->blok_nama == $parts[2];
                            } elseif (!empty($kdCompAgronomi) && empty($kdBlokAgronomi) && empty($kdPlotAgronomi)) {
                                return $item->bln_amat == $legend &&
                                    $item->umur_tanam == $parts[0] &&
                                    $item->kat == $parts[1] &&
                                    $item->company_nama == $parts[2];
                            } elseif (empty($kdCompAgronomi) && empty($kdBlokAgronomi) && !empty($kdPlotAgronomi)) {
                                return $item->bln_amat == $legend &&
                                    $item->plot_nama == $parts[0] &&
                                    $item->umur_tanam == $parts[1] &&
                                    $item->kat == $parts[2];
                            } elseif (!empty($kdCompAgronomi) && empty($kdBlokAgronomi) && !empty($kdPlotAgronomi)) {
                                return $item->bln_amat == $legend &&
                                    $item->plot_nama == $parts[0] &&
                                    $item->umur_tanam == $parts[1] &&
                                    $item->kat == $parts[2] &&
                                    $item->company_nama == $parts[3];
                            } elseif (empty($kdCompAgronomi) && !empty($kdBlokAgronomi) && !empty($kdPlotAgronomi)) {
                                return $item->bln_amat == $legend &&
                                    $item->plot_nama == $parts[0] &&
                                    $item->umur_tanam == $parts[1] &&
                                    $item->kat == $parts[2] &&
                                    $item->blok_nama == $parts[3];
                            } elseif (!empty($kdCompAgronomi) && !empty($kdBlokAgronomi) && empty($kdPlotAgronomi)) {
                                return $item->bln_amat == $legend &&
                                    $item->umur_tanam == $parts[0] &&
                                    $item->kat == $parts[1] &&
                                    $item->blok_nama == $parts[2] &&
                                    $item->company_nama == $parts[3];
                            } else {
                                return $item->bln_amat == $legend &&
                                    $item->plot_nama == $parts[0] &&
                                    $item->umur_tanam == $parts[1] &&
                                    $item->kat == $parts[2] &&
                                    $item->blok_nama == $parts[3] &&
                                    $item->company_nama == $parts[4];
                            }
                        })->avg('total'),
                        2
                    );
                }

                $monthName = Carbon::createFromFormat('m', $legend)->translatedFormat('F');
                $chartData[] = [
                    'label' => $monthName,
                    'data' => $data,
                ];
            }

            // Determine horizontal label
            if (!empty($kdCompAgronomi) && empty($kdBlokAgronomi) && empty($kdPlotAgronomi)) {
                $horizontalLabel = 'Umur - Kategori - Kebun';
            } elseif (empty($kdCompAgronomi) && !empty($kdBlokAgronomi) && empty($kdPlotAgronomi)) {
                $horizontalLabel = 'Umur - Kategori - Blok';
            } elseif (empty($kdCompAgronomi) && empty($kdBlokAgronomi) && !empty($kdPlotAgronomi)) {
                $horizontalLabel = 'Plot - Umur - Kategori';
            } elseif (!empty($kdCompAgronomi) && empty($kdBlokAgronomi) && !empty($kdPlotAgronomi)) {
                $horizontalLabel = 'Plot - Umur - Kategori - Kebun';
            } elseif (empty($kdCompAgronomi) && !empty($kdBlokAgronomi) && !empty($kdPlotAgronomi)) {
                $horizontalLabel = 'Plot - Umur - Kategori - Blok';
            } elseif (!empty($kdCompAgronomi) && !empty($kdBlokAgronomi) && empty($kdPlotAgronomi)) {
                $horizontalLabel = 'Umur - Kategori - Blok - Kebun';
            } else {
                $horizontalLabel = 'Plot - Umur - Kategori - Blok - Kebun';
            }
        }

        return [
            'chartData' => $chartData,
            'xAxis' => $xAxis,
            'verticalLabel' => $verticalLabel,
            'horizontalLabel' => $horizontalLabel
        ];
    }

    public function hpt(Request $request)
    {
        // Jika request AJAX, kembalikan data chart saja
        if ($request->ajax()) {
            return response()->json($this->getHPTChartData($request));
        }

        // Initial page load
        $title = "Dashboard HPT";
        $nav = "HPT";

        $verticalLabels = [
            'per_ppt' => '% PPT',
            'per_ppt_aktif' => '% PPT Aktif',
            'per_pbt' => '% PBT',
            'per_pbt_aktif' => '% PBT Aktif',
            'int_rusak' => '% Intensitas Kerusakan',
            'dh' => 'Dead Heart',
            'dt' => 'Dead Top',
            'kbp' => 'Kutu Bulu Putih',
            'kbb' => 'Kutu Bulu Babi',
            'kp' => 'Kutu Perisai',
            'cabuk' => 'Cabuk',
            'belalang' => 'Belalang',
            'serang_grayak' => 'BTG Terserang Ulat Grayak',
            'jum_grayak' => 'Jumlah Ulat Grayak',
            'serang_smut' => 'BTG Terserang SMUT',
            'jum_larva_ppt' => 'Jumlah Larva PPT',
            'jum_larva_pbt' => 'Jumlah Larva PBT',
        ];

        $monthsLabel = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];

        // Get filter options
        $kdCompHPTOpt = DB::table('company')
            ->join('hpthdr', 'company.companycode', '=', 'hpthdr.companycode')
            ->select('company.companycode', 'company.name')
            ->orderBy('company.companycode', 'ASC')
            ->distinct()
            ->get();

        $kdBlokHPTOpt = DB::table('blok')
            ->join('hpthdr', 'blok.blok', '=', 'hpthdr.blok')
            ->select('blok.blok')
            ->orderBy('blok.blok', 'asc')
            ->distinct()
            ->get();

        $kdPlotHPTOpt = DB::table('batch')
            ->join('hpthdr', 'batch.plot', '=', 'hpthdr.plot')
            ->select('batch.plot')
            ->orderByRaw("LEFT(batch.plot, 1), CAST(SUBSTRING(batch.plot, 2) AS UNSIGNED)")
            ->distinct()
            ->get();

        return view('dashboard.hpt.index', compact(
            'verticalLabels',
            'kdCompHPTOpt',
            'kdBlokHPTOpt',
            'kdPlotHPTOpt',
            'title',
            'nav',
            'monthsLabel'
        ));
    }

    private function getHPTChartData(Request $request)
    {
        $kdCompHPT = $request->input('companycode', []);
        $kdBlokHPT = $request->input('blok', []);
        $kdPlotHPT = $request->input('plot', []);
        $startMonth = $request->input('start_month');
        $endMonth = $request->input('end_month');
        $startYear = $request->input('start_year', now()->format('Y'));
        $endYear = $request->input('end_year', now()->format('Y'));
        $minAge = $request->input('min_age');
        $maxAge = $request->input('max_age');
        $ageUnit = $request->input('age_unit', 'bulan');
        $verticalField = $request->input('vertical', 'per_ppt');

        $verticalLabels = [
            'per_ppt' => '% PPT',
            'per_ppt_aktif' => '% PPT Aktif',
            'per_pbt' => '% PBT',
            'per_pbt_aktif' => '% PBT Aktif',
            'int_rusak' => '% Intensitas Kerusakan',
            'dh' => 'Dead Heart',
            'dt' => 'Dead Top',
            'kbp' => 'Kutu Bulu Putih',
            'kbb' => 'Kutu Bulu Babi',
            'kp' => 'Kutu Perisai',
            'cabuk' => 'Cabuk',
            'belalang' => 'Belalang',
            'serang_grayak' => 'BTG Terserang Ulat Grayak',
            'jum_grayak' => 'Jumlah Ulat Grayak',
            'serang_smut' => 'BTG Terserang SMUT',
            'jum_larva_ppt' => 'Jumlah Larva PPT',
            'jum_larva_pbt' => 'Jumlah Larva PBT',
        ];

        $verticalLabel = $verticalLabels[$verticalField] ?? ucfirst($verticalField);

        $chartData = [];
        $xAxis = [];
        $horizontalLabel = '';

        $months = [
            'January' => 1,
            'February' => 2,
            'March' => 3,
            'April' => 4,
            'May' => 5,
            'June' => 6,
            'July' => 7,
            'August' => 8,
            'September' => 9,
            'October' => 10,
            'November' => 11,
            'December' => 12
        ];

        $startMonthNum = $months[$startMonth] ?? null;
        $endMonthNum = $months[$endMonth] ?? null;

        if (
            !empty($kdCompHPT) || !empty($kdBlokHPT) || !empty($kdPlotHPT) ||
            ($startMonthNum && $endMonthNum) || ($minAge && $maxAge)
        ) {

            $chartDataQuery = DB::table('hpthdr')
                ->join('hptlst', function ($join) {
                    $join->on('hpthdr.nosample', '=', 'hptlst.nosample')
                        ->on('hpthdr.companycode', '=', 'hptlst.companycode');
                })
                ->join('batch', function ($join) {
                    $join->on('hpthdr.plot', '=', 'batch.plot')
                        ->on('hpthdr.companycode', '=', 'batch.companycode');
                })
                ->join('company', 'hpthdr.companycode', '=', 'company.companycode')
                ->leftJoin('blok', function ($join) {
                    $join->on('hpthdr.blok', '=', 'blok.blok')
                        ->whereColumn('hpthdr.companycode', '=', 'blok.companycode');
                })
                ->select(
                    DB::raw("MONTH(hpthdr.tanggalpengamatan) as bln_amat"),
                    DB::raw("MIN(hpthdr.tanggaltanam) as tanggaltanam"),
                    DB::raw("CASE 
                        WHEN '$verticalField' IN ('per_ppt', 'per_pbt', 'per_ppt_aktif', 'per_pbt_aktif', 'int_rusak') 
                        THEN AVG($verticalField) * 100
                        ELSE AVG($verticalField) 
                    END as total"),
                    'company.name as company_nama',
                    'blok.blok as blok_nama',
                    'batch.plot as plot_nama',
                    DB::raw("ROUND(DATEDIFF(CURDATE(), MIN(hpthdr.tanggaltanam)) / 30) as umur_bulan")
                )
                ->when($kdCompHPT, fn($q) => $q->whereIn('hpthdr.companycode', $kdCompHPT))
                ->when($kdBlokHPT, fn($q) => $q->whereIn('hpthdr.blok', $kdBlokHPT))
                ->when($kdPlotHPT, fn($q) => $q->whereIn('hpthdr.plot', $kdPlotHPT))
                ->when($startMonthNum && $endMonthNum && $startYear && $endYear, function ($query) use ($startMonthNum, $endMonthNum, $startYear, $endYear) {
                    return $query->whereRaw("
                        (YEAR(hpthdr.tanggalpengamatan) > ? OR 
                        (YEAR(hpthdr.tanggalpengamatan) = ? AND MONTH(hpthdr.tanggalpengamatan) >= ?)) 
                        AND 
                        (YEAR(hpthdr.tanggalpengamatan) < ? OR 
                        (YEAR(hpthdr.tanggalpengamatan) = ? AND MONTH(hpthdr.tanggalpengamatan) <= ?))
                    ", [$startYear, $startYear, $startMonthNum, $endYear, $endYear, $endMonthNum]);
                })
                ->when($minAge && $maxAge, function ($query) use ($minAge, $maxAge, $ageUnit) {
                    if ($ageUnit === 'tahun') {
                        $minAgeMonths = $minAge * 12;
                        $maxAgeMonths = $maxAge * 12;
                        return $query->whereRaw(
                            "ROUND(DATEDIFF(CURDATE(), hpthdr.tanggaltanam) / 30) BETWEEN ? AND ?",
                            [$minAgeMonths, $maxAgeMonths]
                        );
                    }
                    return $query->whereRaw(
                        "ROUND(DATEDIFF(CURDATE(), hpthdr.tanggaltanam) / 30) BETWEEN ? AND ?",
                        [$minAge, $maxAge]
                    );
                })
                ->groupBy('company.name', 'blok.blok', 'batch.plot', 'bln_amat')
                ->orderBy('plot_nama');

            $chartDataResult = $chartDataQuery->get();
            $chartDataResult->transform(function ($item) {
                $item->umur_tanam = round(Carbon::parse($item->tanggaltanam)->diffInMonths(Carbon::now())) . ' Bulan';
                return $item;
            });

            // Generate xAxis based on filters
            $xAxis = $chartDataResult->map(function ($item) use ($kdCompHPT, $kdBlokHPT, $kdPlotHPT) {
                if (!empty($kdCompHPT) && empty($kdBlokHPT) && empty($kdPlotHPT)) {
                    return $item->umur_tanam . ' - ' . $item->company_nama;
                } elseif (empty($kdCompHPT) && !empty($kdBlokHPT) && empty($kdPlotHPT)) {
                    return $item->umur_tanam . ' - ' . $item->blok_nama;
                } elseif (empty($kdCompHPT) && empty($kdBlokHPT) && !empty($kdPlotHPT)) {
                    return $item->plot_nama . ' - ' . $item->umur_tanam;
                } elseif (!empty($kdCompHPT) && empty($kdBlokHPT) && !empty($kdPlotHPT)) {
                    return $item->plot_nama . ' - ' . $item->umur_tanam . ' - ' . $item->company_nama;
                } elseif (empty($kdCompHPT) && !empty($kdBlokHPT) && !empty($kdPlotHPT)) {
                    return $item->plot_nama . ' - ' . $item->umur_tanam . ' - ' . $item->blok_nama;
                } elseif (!empty($kdCompHPT) && !empty($kdBlokHPT) && empty($kdPlotHPT)) {
                    return $item->umur_tanam . ' - ' . $item->blok_nama . ' - ' . $item->company_nama;
                } else {
                    return $item->plot_nama . ' - ' . $item->umur_tanam . ' - ' . $item->blok_nama . ' - ' . $item->company_nama;
                }
            })->unique()->values();

            $legends = $chartDataResult->pluck('bln_amat')->sort()->unique();

            foreach ($legends as $legend) {
                $data = [];
                foreach ($xAxis as $x) {
                    $data[] = round(
                        $chartDataResult->filter(function ($item) use ($legend, $x, $kdCompHPT, $kdBlokHPT, $kdPlotHPT) {
                            $parts = explode(' - ', $x);

                            if (empty($kdCompHPT) && !empty($kdBlokHPT) && empty($kdPlotHPT)) {
                                return $item->bln_amat == $legend &&
                                    $item->umur_tanam == $parts[0] &&
                                    $item->blok_nama == $parts[1];
                            } elseif (!empty($kdCompHPT) && empty($kdBlokHPT) && empty($kdPlotHPT)) {
                                return $item->bln_amat == $legend &&
                                    $item->umur_tanam == $parts[0] &&
                                    $item->company_nama == $parts[1];
                            } elseif (empty($kdCompHPT) && empty($kdBlokHPT) && !empty($kdPlotHPT)) {
                                return $item->bln_amat == $legend &&
                                    $item->plot_nama == $parts[0] &&
                                    $item->umur_tanam == $parts[1];
                            } elseif (!empty($kdCompHPT) && empty($kdBlokHPT) && !empty($kdPlotHPT)) {
                                return $item->bln_amat == $legend &&
                                    $item->plot_nama == $parts[0] &&
                                    $item->umur_tanam == $parts[1] &&
                                    $item->company_nama == $parts[2];
                            } elseif (empty($kdCompHPT) && !empty($kdBlokHPT) && !empty($kdPlotHPT)) {
                                return $item->bln_amat == $legend &&
                                    $item->plot_nama == $parts[0] &&
                                    $item->umur_tanam == $parts[1] &&
                                    $item->blok_nama == $parts[2];
                            } elseif (!empty($kdCompHPT) && !empty($kdBlokHPT) && empty($kdPlotHPT)) {
                                return $item->bln_amat == $legend &&
                                    $item->umur_tanam == $parts[0] &&
                                    $item->blok_nama == $parts[1] &&
                                    $item->company_nama == $parts[2];
                            } else {
                                return $item->bln_amat == $legend &&
                                    $item->plot_nama == $parts[0] &&
                                    $item->umur_tanam == $parts[1] &&
                                    $item->blok_nama == $parts[2] &&
                                    $item->company_nama == $parts[3];
                            }
                        })->avg('total'),
                        2
                    );
                }

                $monthName = Carbon::createFromFormat('m', $legend)->translatedFormat('F');
                $chartData[] = [
                    'label' => $monthName,
                    'data' => $data,
                ];
            }

            // Determine horizontal label
            if (!empty($kdCompHPT) && empty($kdBlokHPT) && empty($kdPlotHPT)) {
                $horizontalLabel = 'Umur - Kebun';
            } elseif (empty($kdCompHPT) && !empty($kdBlokHPT) && empty($kdPlotHPT)) {
                $horizontalLabel = 'Umur - Blok';
            } elseif (empty($kdCompHPT) && empty($kdBlokHPT) && !empty($kdPlotHPT)) {
                $horizontalLabel = 'Plot - Umur';
            } elseif (!empty($kdCompHPT) && empty($kdBlokHPT) && !empty($kdPlotHPT)) {
                $horizontalLabel = 'Plot - Umur - Kebun';
            } elseif (empty($kdCompHPT) && !empty($kdBlokHPT) && !empty($kdPlotHPT)) {
                $horizontalLabel = 'Plot - Umur - Blok';
            } elseif (!empty($kdCompHPT) && !empty($kdBlokHPT) && empty($kdPlotHPT)) {
                $horizontalLabel = 'Umur - Blok - Kebun';
            } else {
                $horizontalLabel = 'Plot - Umur - Blok - Kebun';
            }
        }

        return [
            'chartData' => $chartData,
            'xAxis' => $xAxis,
            'verticalLabel' => $verticalLabel,
            'horizontalLabel' => $horizontalLabel
        ];
    }
}