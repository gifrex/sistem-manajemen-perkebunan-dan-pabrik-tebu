<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuratJalanTimbanganReportController extends Controller
{
    public function index()
    {
        $companyCode = session('companycode');
        
        $title = 'Report Surat Jalan & Timbangan';
        $navbar = 'Report';
        $nav = 'Surat Jalan & Timbangan';

        return view('report.surat-jalan-timbangan.index', compact('title', 'navbar', 'nav'));
    }

    public function getData(Request $request)
    {
        try {
            $companyCode = session('companycode');
            
            // Parse date filters
            $startDate = $request->start_date ?: Carbon::today()->format('Y-m-d');
            $endDate = $request->end_date ?: Carbon::today()->format('Y-m-d');
            
            // Get group filter
            $filterGroup = $request->input('group');
            
            // Check if single day
            $isSingleDay = $startDate === $endDate;
            
            // Get filter options first (PASS filterGroup)
            $filterOptions = $this->getFilterOptions($companyCode, $startDate, $endDate, $filterGroup);
            
            // Build query with filters
            $query = DB::table('suratjalanpos as sj')
                ->leftJoin('timbanganpayload as tp', function($join) {
                    $join->on('sj.companycode', '=', 'tp.companycode')
                        ->on('sj.suratjalanno', '=', 'tp.suratjalanno');
                })
                ->leftJoin('user as mandor', function($join) {
                    $join->on('sj.mandorid', '=', 'mandor.userid')
                        ->where('mandor.idjabatan', '=', 5);
                })
                ->leftJoin('kontraktor as k', function($join) {
                    $join->on('sj.namakontraktor', '=', 'k.id')
                        ->on('sj.companycode', '=', 'k.companycode');
                })
                ->leftJoin('subkontraktor as sk', function($join) {
                    $join->on('sj.namasubkontraktor', '=', 'sk.id')
                        ->on('sj.companycode', '=', 'sk.companycode');
                })
                ->whereBetween('sj.tanggalangkut', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

            // Apply group filter
            if ($filterGroup === 'all-tbl') {
                $query->whereIn('sj.companycode', ['TBL1', 'TBL2', 'TBL3']);
            } elseif ($filterGroup === 'all-divisi') {
                // No company filter - show all
            } else {
                // Default: current session company only
                $query->where('sj.companycode', $companyCode);
            }

            // Apply other filters
            if ($request->filled('mandor')) {
                $query->where('sj.mandorid', $request->mandor);
            }
            if ($request->filled('plot')) {
                $query->where('sj.plot', $request->plot);
            }
            if ($request->filled('kontraktor')) {
                $query->where('sj.namakontraktor', $request->kontraktor);
            }
            if ($request->filled('nopol')) {
                $query->where('sj.nomorpolisi', $request->nopol);
            }
            if ($request->filled('status')) {
                if ($request->status === 'sudah') {
                    $query->whereNotNull('tp.suratjalanno');
                } elseif ($request->status === 'pending') {
                    $query->whereNull('tp.suratjalanno');
                }
            }
            if ($request->filled('subkontraktor')) {
                $query->where('sj.namasubkontraktor', $request->subkontraktor);
            }

            // Get details
            $details = $query->select(
                'sj.companycode',
                'sj.suratjalanno',
                'sj.mandorid',
                'mandor.name as nama_mandor',
                'sj.plot',
                'sj.varietas',
                'sj.kategori',
                'sj.umur',
                'sj.kodetebang',
                'sj.muatgl',
                'sj.langsir',
                'sj.tebusulit',
                'sj.kendaraankontraktor',
                'sj.nomorkendaraan',
                'sj.nomorpolisi',
                'sj.namasupir',
                'sj.namakontraktor',
                'k.namakontraktor as nama_kontraktor_lengkap',
                'sj.namasubkontraktor',
                'sk.namasubkontraktor as nama_subkontraktor_lengkap',
                'sj.tanggaltebang',
                'sj.tanggalangkut',
                'sj.tanggalcetakpossecurity',
                'tp.bruto',
                'tp.netto',
                'tp.tgl1',
                'tp.jam1',
                'tp.tgl2',
                'tp.jam2',
                DB::raw('CASE WHEN tp.suratjalanno IS NULL THEN "Pending" ELSE "Sudah Timbang" END as status')
            )
            ->orderBy('sj.tanggalangkut', 'desc')
            ->get()
            ->map(function($item) {
                // Calculate durasi deload (jam masuk ke jam keluar timbangan)
                $item->durasi_deload = null;
                if ($item->jam1 && $item->jam2) {
                    try {
                        $masuk = Carbon::parse($item->tgl1 . ' ' . $item->jam1);
                        $keluar = Carbon::parse($item->tgl2 . ' ' . $item->jam2);
                        $item->durasi_deload = $masuk->diffInMinutes($keluar, true);
                    } catch (\Exception $e) {
                        $item->durasi_deload = null;
                    }
                }
                
                // Calculate durasi pos ke timbangan (jam cetak pos ke jam1 timbangan)
                $item->durasi_pos_timbangan = null;
                if ($item->tanggalcetakpossecurity && $item->tgl1 && $item->jam1) {
                    try {
                        $cetak = Carbon::parse($item->tanggalcetakpossecurity);
                        $masuk = Carbon::parse($item->tgl1 . ' ' . $item->jam1);
                        $item->durasi_pos_timbangan = $cetak->diffInMinutes($masuk, true);
                    } catch (\Exception $e) {
                        $item->durasi_pos_timbangan = null;
                    }
                }
                
                return $item;
            });

            // Calculate summary
            $summary = [
                'total_sj' => $details->count(),
                'total_netto' => $details->whereNotNull('netto')->sum('netto'),
                'pending_timbangan' => $details->where('status', 'Pending')->count(),
                'sudah_timbang' => $details->where('status', 'Sudah Timbang')->count(),
            ];

            // Hourly trend (only for single day)
            $hourlyTrend = $isSingleDay ? $this->getHourlyTrend($details, $startDate) : [];

            // Status breakdown
            $statusBreakdown = [
                ['name' => 'Sudah Timbang', 'value' => $summary['sudah_timbang']],
                ['name' => 'Pending', 'value' => $summary['pending_timbangan']],
            ];

            // Vehicle performance (ALL vehicles, not just top 10)
            $vehiclePerformance = $details->whereNotNull('netto')
                ->groupBy('nomorpolisi')
                ->map(function($group) {
                    $avgDeload = $group->whereNotNull('durasi_deload')->avg('durasi_deload');
                    $avgPosToTimbang = $group->whereNotNull('durasi_pos_timbangan')->avg('durasi_pos_timbangan');
                    return [
                        'nopol' => $group->first()->nomorpolisi,
                        'trip_count' => $group->count(),
                        'total_netto' => $group->sum('netto'),
                        'avg_durasi_deload' => $avgDeload ? round($avgDeload, 2) : null,
                        'avg_durasi_pos_timbang' => $avgPosToTimbang ? round($avgPosToTimbang, 2) : null,
                    ];
                })
                ->sortByDesc('trip_count')
                ->values();

            // SJ per tanggal - daily and monthly data
            $sjDaily = $this->getSJDaily($details, $startDate, $endDate);
            $sjMonthly = $this->getSJMonthly($details, $endDate);
            
            // Tonase per tanggal - daily and monthly data
            $tonaseDaily = $this->getTonaseDaily($details, $startDate, $endDate);
            $tonaseMonthly = $this->getTonaseMonthly($details, $endDate);
            
            // Durasi perjalanan distribution
            $durasiPerjalanan = $this->getDurasiPerjalananChart($details);

            // Additional analytics
            $totalSJ = $details->count();
            $langsirCount = $details->where('langsir', 1)->count();
            $langsirPercentage = $totalSJ > 0 ? round(($langsirCount / $totalSJ) * 100, 1) : 0;
            
            $tebuSulitCount = $details->where('tebusulit', 1)->count();
            $tebuSulitPercentage = $totalSJ > 0 ? round(($tebuSulitCount / $totalSJ) * 100, 1) : 0;
            
            $kodeTebangBreakdown = $details->groupBy('kodetebang')->map(function($group) {
                return $group->count();
            });
            $premiumCount = $kodeTebangBreakdown->get('Premium', 0);
            $nonPremiumCount = $kodeTebangBreakdown->get('Non-Premium', 0);
            
            $kendaraanWL = $details->where('kendaraankontraktor', 0)->count();
            $kendaraanUmum = $details->where('kendaraankontraktor', 1)->count();
            
            // Kontraktor by tonase
            $kontraktorTonase = $details->whereNotNull('netto')
                ->groupBy('nama_kontraktor_lengkap')
                ->map(function($group) {
                    return [
                        'name' => $group->first()->nama_kontraktor_lengkap ?: 'Unknown',
                        'value' => round($group->sum('netto') / 1000, 2) // Convert to ton
                    ];
                })
                ->sortByDesc('value')
                ->values();
            
            // Subkontraktor by tonase
            $subkontraktorTonase = $details->whereNotNull('netto')
                ->whereNotNull('nama_subkontraktor_lengkap')
                ->groupBy('nama_subkontraktor_lengkap')
                ->map(function($group) {
                    return [
                        'name' => $group->first()->nama_subkontraktor_lengkap,
                        'value' => round($group->sum('netto') / 1000, 2) // Convert to ton
                    ];
                })
                ->sortByDesc('value')
                ->values();

            // Rit per kontraktor (ALL kontraktors)
            $ritPerKontraktor = $details->groupBy('nama_kontraktor_lengkap')
                ->map(function($group) {
                    return [
                        'kontraktor' => $group->first()->nama_kontraktor_lengkap ?: 'Unknown',
                        'total_rit' => $group->count(),
                        'sudah_timbang' => $group->whereNotNull('netto')->count(),
                        'pending' => $group->whereNull('netto')->count(),
                        'total_netto' => $group->whereNotNull('netto')->sum('netto'),
                    ];
                })
                ->sortByDesc('total_rit')
                ->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'details' => $details,
                    'filterOptions' => $filterOptions,
                    'hourlyTrend' => $hourlyTrend,
                    'statusBreakdown' => $statusBreakdown,
                    'vehiclePerformance' => $vehiclePerformance,
                    'ritPerKontraktor' => $ritPerKontraktor,
                    'sjDaily' => $sjDaily,
                    'sjMonthly' => $sjMonthly,
                    'tonaseDaily' => $tonaseDaily,
                    'tonaseMonthly' => $tonaseMonthly,
                    'durasiPerjalanan' => $durasiPerjalanan,
                    'langsirCount' => $langsirCount,
                    'langsirPercentage' => $langsirPercentage,
                    'nonLangsirCount' => $totalSJ - $langsirCount,
                    'tebuSulitCount' => $tebuSulitCount,
                    'tebuSulitPercentage' => $tebuSulitPercentage,
                    'tebuNormalCount' => $totalSJ - $tebuSulitCount,
                    'premiumCount' => $premiumCount,
                    'nonPremiumCount' => $nonPremiumCount,
                    'kendaraanWL' => $kendaraanWL,
                    'kendaraanUmum' => $kendaraanUmum,
                    'kontraktorTonase' => $kontraktorTonase,
                    'subkontraktorTonase' => $subkontraktorTonase,
                    'isSingleDay' => $isSingleDay,
                    'dateRange' => [
                        'start' => Carbon::parse($startDate)->format('d M y'),
                        'end' => Carbon::parse($endDate)->format('d M y')
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getFilterOptions($companyCode, $startDate, $endDate, $filterGroup = null)
    {
        // Base query for filter options
        $baseQuery = DB::table('suratjalanpos as sj')
            ->whereBetween('sj.tanggalangkut', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        // Apply same group filter
        if ($filterGroup === 'all-tbl') {
            $baseQuery->whereIn('sj.companycode', ['TBL1', 'TBL2', 'TBL3']);
        } elseif ($filterGroup === 'all-divisi') {
            // No filter
        } else {
            $baseQuery->where('sj.companycode', $companyCode);
        }

        // Get mandor list
        $mandors = (clone $baseQuery)
            ->leftJoin('user as mandor', function($join) {
                $join->on('sj.mandorid', '=', 'mandor.userid')
                    ->where('mandor.idjabatan', '=', 5);
            })
            ->select('sj.mandorid', 'mandor.name as nama_mandor')
            ->distinct()
            ->orderBy('sj.mandorid')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->mandorid,
                    'name' => $item->nama_mandor ?: $item->mandorid
                ];
            });

        // Get plots
        $plots = (clone $baseQuery)
            ->distinct()
            ->pluck('sj.plot')
            ->sort()
            ->values();

        // Get kontraktor
        $kontraktors = (clone $baseQuery)
            ->whereNotNull('sj.namakontraktor')
            ->distinct()
            ->pluck('sj.namakontraktor')
            ->sort()
            ->values();

        // Get nopol
        $nopols = (clone $baseQuery)
            ->whereNotNull('sj.nomorpolisi')
            ->distinct()
            ->pluck('sj.nomorpolisi')
            ->sort()
            ->values();

        // Get subkontraktor
        $subkontraktors = (clone $baseQuery)
            ->leftJoin('subkontraktor as sk', function($join) {
                $join->on('sj.namasubkontraktor', '=', 'sk.id')
                    ->on('sj.companycode', '=', 'sk.companycode');
            })
            ->whereNotNull('sj.namasubkontraktor')
            ->select('sj.namasubkontraktor as id', 'sk.namasubkontraktor as name')
            ->distinct()
            ->orderBy('sk.namasubkontraktor')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name ?: $item->id
                ];
            });

        return [
            'mandors' => $mandors,
            'plots' => $plots,
            'kontraktors' => $kontraktors,
            'nopols' => $nopols,
            'subkontraktors' => $subkontraktors,
        ];
    }

    private function getHourlyTrend($details, $date)
    {
        $timbangData = $details->whereNotNull('jam2')->sortBy('jam2');
        $currentHour = Carbon::now()->format('H');
        $isToday = Carbon::parse($date)->isToday();
        
        $hourlyData = [];
        $maxHour = $isToday ? (int)$currentHour : 23;
        
        for ($i = 0; $i <= $maxHour; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
            $hourlyData[$hour] = 0;
        }

        foreach ($timbangData as $item) {
            if ($item->jam2) {
                $hour = substr($item->jam2, 0, 2) . ':00';
                if (isset($hourlyData[$hour])) {
                    $hourlyData[$hour] += $item->netto ?? 0;
                }
            }
        }

        // Make cumulative
        $cumulative = 0;
        foreach ($hourlyData as $hour => $value) {
            $cumulative += $value;
            $hourlyData[$hour] = $cumulative;
        }

        return collect($hourlyData)->map(function($value, $hour) {
            return ['hour' => $hour, 'netto' => $value];
        })->values();
    }
    
    private function getSJDaily($details, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Create all dates in range
        $allDates = [];
        $current = $start->copy();
        while ($current <= $end) {
            $dateKey = $current->format('Y-m-d');
            $allDates[$dateKey] = 0;
            $current->addDay();
        }
        
        // Fill with actual data
        $grouped = $details->groupBy(function($item) {
            return Carbon::parse($item->tanggalangkut)->format('Y-m-d');
        });
        
        foreach ($grouped as $date => $group) {
            if (isset($allDates[$date])) {
                $allDates[$date] = $group->count();
            }
        }
        
        return collect($allDates)->map(function($value, $date) {
            return [
                'label' => Carbon::parse($date)->format('d M'),
                'value' => $value
            ];
        })->values();
    }
    
    private function getSJMonthly($details, $endDate)
    {
        $end = Carbon::parse($endDate);
        
        // Get 6 months back from end date
        $allMonths = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $end->copy()->subMonths($i)->startOfMonth();
            $monthKey = $month->format('Y-m');
            $allMonths[$monthKey] = 0;
        }
        
        // Fill with actual data
        $grouped = $details->groupBy(function($item) {
            return Carbon::parse($item->tanggalangkut)->format('Y-m');
        });
        
        foreach ($grouped as $month => $group) {
            if (isset($allMonths[$month])) {
                $allMonths[$month] = $group->count();
            }
        }
        
        return collect($allMonths)->map(function($value, $month) {
            return [
                'label' => Carbon::parse($month . '-01')->format('M Y'),
                'value' => $value
            ];
        })->values();
    }
    
    private function getTonaseDaily($details, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $timbangData = $details->whereNotNull('netto');
        
        // Create all dates in range
        $allDates = [];
        $current = $start->copy();
        while ($current <= $end) {
            $dateKey = $current->format('Y-m-d');
            $allDates[$dateKey] = 0;
            $current->addDay();
        }
        
        // Fill with actual data
        $grouped = $timbangData->groupBy(function($item) {
            return Carbon::parse($item->tanggalangkut)->format('Y-m-d');
        });
        
        foreach ($grouped as $date => $group) {
            if (isset($allDates[$date])) {
                $allDates[$date] = $group->sum('netto');
            }
        }
        
        return collect($allDates)->map(function($value, $date) {
            return [
                'label' => Carbon::parse($date)->format('d M'),
                'value' => $value
            ];
        })->values();
    }
    
    private function getTonaseMonthly($details, $endDate)
    {
        $end = Carbon::parse($endDate);
        
        $timbangData = $details->whereNotNull('netto');
        
        // Get 6 months back from end date
        $allMonths = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $end->copy()->subMonths($i)->startOfMonth();
            $monthKey = $month->format('Y-m');
            $allMonths[$monthKey] = 0;
        }
        
        // Fill with actual data
        $grouped = $timbangData->groupBy(function($item) {
            return Carbon::parse($item->tanggalangkut)->format('Y-m');
        });
        
        foreach ($grouped as $month => $group) {
            if (isset($allMonths[$month])) {
                $allMonths[$month] = $group->sum('netto');
            }
        }
        
        return collect($allMonths)->map(function($value, $month) {
            return [
                'label' => Carbon::parse($month . '-01')->format('M Y'),
                'value' => $value
            ];
        })->values();
    }
    
    private function getDurasiPerjalananChart($details)
    {
        $validData = $details->whereNotNull('durasi_pos_timbangan')
            ->where('durasi_pos_timbangan', '>', 0);
        
        if ($validData->isEmpty()) {
            return [];
        }
        
        // Group by time ranges: <30min, 30-60min, 1-2hr, 2-3hr, >3hr
        $ranges = [
            '<30 min' => 0,
            '30-60 min' => 0,
            '1-2 jam' => 0,
            '2-3 jam' => 0,
            '>3 jam' => 0
        ];
        
        foreach ($validData as $item) {
            $durasi = $item->durasi_pos_timbangan;
            if ($durasi < 30) {
                $ranges['<30 min']++;
            } elseif ($durasi < 60) {
                $ranges['30-60 min']++;
            } elseif ($durasi < 120) {
                $ranges['1-2 jam']++;
            } elseif ($durasi < 180) {
                $ranges['2-3 jam']++;
            } else {
                $ranges['>3 jam']++;
            }
        }
        
        return collect($ranges)->map(function($value, $label) {
            return ['label' => $label, 'value' => $value];
        })->values();
    }

    // Detail page methods
    public function show($suratjalanno)
    {
        $companyCode = session('companycode');
        
        $title = 'Detail Surat Jalan';
        $navbar = 'Report';
        $nav = 'Detail Surat Jalan';

        return view('report.surat-jalan-timbangan.show', compact('title', 'navbar', 'nav', 'suratjalanno'));
    }

    public function getDetail($suratjalanno)
    {
        try {
            $companyCode = session('companycode');
            
            // Get surat jalan detail
            $detail = DB::table('suratjalanpos as sj')
                ->leftJoin('timbanganpayload as tp', function($join) {
                    $join->on('sj.companycode', '=', 'tp.companycode')
                         ->on('sj.suratjalanno', '=', 'tp.suratjalanno');
                })
                ->leftJoin('user as mandor', function($join) {
                    $join->on('sj.mandorid', '=', 'mandor.userid')
                         ->where('mandor.idjabatan', '=', 5);
                })
                ->leftJoin('kontraktor as k', function($join) {
                    $join->on('sj.namakontraktor', '=', 'k.id')
                         ->on('sj.companycode', '=', 'k.companycode');
                })
                ->leftJoin('subkontraktor as sk', function($join) {
                    $join->on('sj.namasubkontraktor', '=', 'sk.id')
                         ->on('sj.companycode', '=', 'sk.companycode');
                })
                ->where('sj.companycode', $companyCode)
                ->where('sj.suratjalanno', $suratjalanno)
                ->select(
                    'sj.suratjalanno',
                    'sj.mandorid',
                    'mandor.name as nama_mandor',
                    'sj.plot',
                    'sj.varietas',
                    'sj.kategori',
                    'sj.umur',
                    'sj.kodetebang',
                    'sj.muatgl',
                    'sj.langsir',
                    'sj.tebusulit',
                    'sj.kendaraankontraktor',
                    'sj.nomorkendaraan',
                    'sj.nomorpolisi',
                    'sj.namasupir',
                    'sj.namakontraktor',
                    'k.namakontraktor as nama_kontraktor_lengkap',
                    'sj.namasubkontraktor',
                    'sk.namasubkontraktor as nama_subkontraktor_lengkap',
                    'sj.tanggaltebang',
                    'sj.tanggalangkut',
                    'sj.tanggalcetakpossecurity',
                    'tp.bruto',
                    'tp.netto',
                    'tp.tgl1',
                    'tp.jam1',
                    'tp.tgl2',
                    'tp.jam2',
                    DB::raw('CASE WHEN tp.suratjalanno IS NULL THEN "Pending" ELSE "Sudah Timbang" END as status')
                )
                ->first();

            if (!$detail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Surat jalan tidak ditemukan'
                ], 404);
            }

            // Calculate durations
            $detail->durasi_deload = null;
            if ($detail->jam1 && $detail->jam2) {
                try {
                    $masuk = Carbon::parse($detail->tgl1 . ' ' . $detail->jam1);
                    $keluar = Carbon::parse($detail->tgl2 . ' ' . $detail->jam2);
                    $detail->durasi_deload = $masuk->diffInMinutes($keluar, true);
                } catch (\Exception $e) {
                    $detail->durasi_deload = null;
                }
            }
            
            $detail->durasi_pos_timbangan = null;
            if ($detail->tanggalcetakpossecurity && $detail->tgl1 && $detail->jam1) {
                try {
                    $cetak = Carbon::parse($detail->tanggalcetakpossecurity);
                    $masuk = Carbon::parse($detail->tgl1 . ' ' . $detail->jam1);
                    $detail->durasi_pos_timbangan = $cetak->diffInMinutes($masuk, true);
                } catch (\Exception $e) {
                    $detail->durasi_pos_timbangan = null;
                }
            }

            $detail->durasi_angkut_pos = null;
            if ($detail->tanggalangkut && $detail->tanggalcetakpossecurity) {
                try {
                    $angkut = Carbon::parse($detail->tanggalangkut);
                    $cetak = Carbon::parse($detail->tanggalcetakpossecurity);
                    $detail->durasi_angkut_pos = $angkut->diffInMinutes($cetak, true);
                } catch (\Exception $e) {
                    $detail->durasi_angkut_pos = null;
                }
            }

            // Calculate total duration (from angkut to keluar timbangan)
            $detail->total_durasi = null;
            if ($detail->tanggalangkut && $detail->tgl2 && $detail->jam2) {
                try {
                    $angkut = Carbon::parse($detail->tanggalangkut);
                    $keluar = Carbon::parse($detail->tgl2 . ' ' . $detail->jam2);
                    $detail->total_durasi = $angkut->diffInMinutes($keluar, true);
                } catch (\Exception $e) {
                    $detail->total_durasi = null;
                }
            }

             // Get BSM data
            $bsmData = DB::table('lkhdetailbsm')
                ->where('suratjalanno', $suratjalanno)
                ->select(
                    'nilaibersih',
                    'nilaisegar',
                    'nilaimanis',
                    'averagescore',
                    'grade',
                    'keterangan',
                    'createdat'
                )
                ->first();

            return response()->json([
                'success' => true,
                'data' => $detail,
                'bsm' => $bsmData
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getDetail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}