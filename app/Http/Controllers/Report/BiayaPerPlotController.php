<?php

namespace App\Http\Controllers\Report;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Barryvdh\DomPDF\Facade\Pdf;

class BiayaPerPlotController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Report',
        ]);
    }

    /**
     * Halaman utama Report Biaya Per Plot
     */
    public function index()
    {
        $title = "Report Biaya Per Plot";
        $nav = "Biaya Per Plot";
        $companycode = session('companycode');

        $bloks = DB::table('masterlist as m')
            ->join('batch as b', function($join) {
                $join->on('m.activebatchno', '=', 'b.batchno')
                     ->on('m.companycode', '=', 'b.companycode');
            })
            ->where('m.companycode', $companycode)
            ->where('m.isactive', 1)
            ->where('b.isactive', 1)
            ->select('m.blok', DB::raw('COUNT(*) as plot_count'))
            ->groupBy('m.blok')
            ->orderBy('m.blok')
            ->get();

        return view('report.biaya-per-plot.index', compact('title', 'nav', 'bloks'));
    }

    /**
     * Get data biaya per plot berdasarkan blok yang dipilih
     */
    public function getData(Request $request)
    {
        $companycode = session('companycode');
        $selectedBloks = $request->bloks ?? [];
        $generation = (int) ($request->generation ?? 0);

        if (empty($selectedBloks)) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih minimal 1 blok'
            ]);
        }

        $plots = $this->getPlotsByGeneration($companycode, $selectedBloks, $generation);
        $batchnos = $plots->pluck('batchno')->whereNotNull()->unique()->toArray();

        $biayaTKPerBatch = $this->getBiayaTKPerBatch($companycode, $batchnos);
        $biayaMaterialPerBatch = $this->getBiayaMaterialPerBatch($companycode, $batchnos);
        $infoPanenPerBatch = $this->getInfoPanenPerBatch($companycode, $batchnos);

        foreach ($plots as $plot) {
            $bn = $plot->batchno;
            
            $plot->biaya_tk = $biayaTKPerBatch[$bn] ?? 0;
            $plot->biaya_material = $biayaMaterialPerBatch[$bn] ?? 0;
            $plot->biaya_panen = 0;
            $plot->total_biaya = $plot->biaya_tk + $plot->biaya_material + $plot->biaya_panen;

            $infoPanen = $infoPanenPerBatch[$bn] ?? null;
            $plot->total_ton = $infoPanen ? ($infoPanen->total_ton ?? 0) : 0;
            $plot->jumlah_sj = $infoPanen ? ($infoPanen->jumlah_sj ?? 0) : 0;
            
            if ($plot->total_ton > 0 && $plot->batcharea > 0) {
                $plot->yph = round($plot->total_ton / $plot->batcharea, 2);
            } else {
                $plot->yph = 0;
            }

            $plot->has_batch = !empty($plot->batchno);

            if ($plot->tanggalulangtahun) {
                $tglTanam = Carbon::parse($plot->tanggalulangtahun);
                $now = Carbon::now();
                $plot->umur_hari = (int) $tglTanam->diffInDays($now);
                $plot->umur_bulan = (int) $tglTanam->diffInMonths($now);
            } else {
                $plot->umur_hari = null;
                $plot->umur_bulan = null;
            }
        }

        $summaryPerBlok = $plots->groupBy('blok')->map(function($items, $blok) {
            $plotsWithHarvest = $items->where('total_ton', '>', 0);
            $avgYPH = $plotsWithHarvest->count() > 0 ? $plotsWithHarvest->avg('yph') : 0;

            return [
                'blok' => $blok,
                'total_plot' => $items->count(),
                'plots_with_batch' => $items->where('has_batch', true)->count(),
                'biaya_tk' => $items->sum('biaya_tk'),
                'biaya_material' => $items->sum('biaya_material'),
                'biaya_panen' => $items->sum('biaya_panen'),
                'total_biaya' => $items->sum('total_biaya'),
                'total_ton' => $items->sum('total_ton'),
                'avg_yph' => round($avgYPH, 2),
            ];
        })->values();

        $plotsWithHarvest = $plots->where('total_ton', '>', 0);
        $avgYPH = $plotsWithHarvest->count() > 0 ? $plotsWithHarvest->avg('yph') : 0;

        $grandTotal = [
            'total_plot' => $plots->count(),
            'plots_with_batch' => $plots->where('has_batch', true)->count(),
            'biaya_tk' => $plots->sum('biaya_tk'),
            'biaya_material' => $plots->sum('biaya_material'),
            'biaya_panen' => $plots->sum('biaya_panen'),
            'total_biaya' => $plots->sum('total_biaya'),
            'total_ton' => $plots->sum('total_ton'),
            'avg_yph' => round($avgYPH, 2),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'plots' => $plots,
                'summaryPerBlok' => $summaryPerBlok,
                'grandTotal' => $grandTotal,
                'generation' => $generation
            ]
        ]);
    }

    /**
     * Export to Excel
     */
    public function exportExcel(Request $request)
    {
        $companycode = session('companycode');
        $selectedBloks = json_decode($request->bloks, true) ?? [];
        $generation = (int) ($request->generation ?? 0);

        if (empty($selectedBloks)) {
            return back()->with('error', 'Pilih minimal 1 blok');
        }

        $plots = $this->getPlotsByGeneration($companycode, $selectedBloks, $generation);
        $batchnos = $plots->pluck('batchno')->whereNotNull()->unique()->toArray();

        $biayaTKPerBatch = $this->getBiayaTKPerBatch($companycode, $batchnos);
        $biayaMaterialPerBatch = $this->getBiayaMaterialPerBatch($companycode, $batchnos);
        $infoPanenPerBatch = $this->getInfoPanenPerBatch($companycode, $batchnos);

        foreach ($plots as $plot) {
            $bn = $plot->batchno;
            $plot->biaya_tk = $biayaTKPerBatch[$bn] ?? 0;
            $plot->biaya_material = $biayaMaterialPerBatch[$bn] ?? 0;
            $plot->biaya_panen = 0;
            $plot->total_biaya = $plot->biaya_tk + $plot->biaya_material + $plot->biaya_panen;

            $infoPanen = $infoPanenPerBatch[$bn] ?? null;
            $plot->total_ton = $infoPanen ? ($infoPanen->total_ton ?? 0) : 0;
            $plot->jumlah_sj = $infoPanen ? ($infoPanen->jumlah_sj ?? 0) : 0;
            $plot->yph = ($plot->total_ton > 0 && $plot->batcharea > 0) ? round($plot->total_ton / $plot->batcharea, 2) : 0;
            
            if ($plot->tanggalulangtahun) {
                $tglTanam = Carbon::parse($plot->tanggalulangtahun);
                $plot->umur_bulan = (int) $tglTanam->diffInMonths(Carbon::now());
            } else {
                $plot->umur_bulan = null;
            }
        }

        $summaryPerBlok = $plots->groupBy('blok')->map(function($items) {
            $plotsWithHarvest = $items->where('total_ton', '>', 0);
            return [
                'blok' => $items->first()->blok,
                'biaya_tk' => $items->sum('biaya_tk'),
                'biaya_material' => $items->sum('biaya_material'),
                'total_biaya' => $items->sum('total_biaya'),
                'total_ton' => $items->sum('total_ton'),
                'avg_yph' => $plotsWithHarvest->count() > 0 ? round($plotsWithHarvest->avg('yph'), 2) : 0,
            ];
        })->values();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $spreadsheet->getProperties()
            ->setCreator(session('username') ?? 'System')
            ->setTitle('Report Biaya Per Plot')
            ->setSubject('Report Biaya Per Plot');

        $generationLabel = ['0' => 'Current Cycle', '-1' => 'Last Cycle', '-2' => 'Cycle -2', '-3' => 'Cycle -3'][$generation] ?? 'Current Cycle';
        
        $sheet->setCellValue('A1', 'REPORT BIAYA PER PLOT');
        $sheet->setCellValue('A2', 'Cycle: ' . $generationLabel);
        $sheet->setCellValue('A3', 'Blok: ' . implode(', ', $selectedBloks));
        $sheet->setCellValue('A4', 'Tanggal: ' . Carbon::now()->format('d M Y H:i'));

        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row = 6;
        $headers = ['Blok', 'Plot', 'Status', 'Umur (Bln)', 'Luas (Ha)', 'Varietas', 
                   'Biaya TK', 'Biaya Material', 'Biaya Panen', 'Total Biaya', 'Total Ton', 'YPH'];
        
        foreach ($headers as $col => $header) {
            $cellAddress = chr(65 + $col) . $row;
            $sheet->setCellValue($cellAddress, $header);
        }

        $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4B5563']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        $row++;
        $totalBiayaTK = 0;
        $totalBiayaMaterial = 0;
        $totalBiayaPanen = 0;
        $totalBiaya = 0;
        $totalTon = 0;

        foreach ($plots as $plot) {
            $sheet->setCellValue("A{$row}", $plot->blok);
            $sheet->setCellValue("B{$row}", $plot->plot);
            $sheet->setCellValue("C{$row}", $plot->lifecyclestatus ?? '-');
            $sheet->setCellValue("D{$row}", $plot->umur_bulan ?? '-');
            $sheet->setCellValue("E{$row}", $plot->batcharea ?? 0);
            $sheet->setCellValue("F{$row}", $plot->kodevarietas ?? '-');
            $sheet->setCellValue("G{$row}", $plot->biaya_tk);
            $sheet->setCellValue("H{$row}", $plot->biaya_material);
            $sheet->setCellValue("I{$row}", $plot->biaya_panen);
            $sheet->setCellValue("J{$row}", $plot->total_biaya);
            $sheet->setCellValue("K{$row}", $plot->total_ton);
            $sheet->setCellValue("L{$row}", $plot->yph);

            $totalBiayaTK += $plot->biaya_tk;
            $totalBiayaMaterial += $plot->biaya_material;
            $totalBiayaPanen += $plot->biaya_panen;
            $totalBiaya += $plot->total_biaya;
            $totalTon += $plot->total_ton;

            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("G{$row}:J{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("K{$row}:L{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

            $row++;
        }

        $avgYPH = $plots->where('total_ton', '>', 0)->avg('yph') ?? 0;
        
        $sheet->setCellValue("A{$row}", 'TOTAL / AVERAGE');
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("G{$row}", $totalBiayaTK);
        $sheet->setCellValue("H{$row}", $totalBiayaMaterial);
        $sheet->setCellValue("I{$row}", $totalBiayaPanen);
        $sheet->setCellValue("J{$row}", $totalBiaya);
        $sheet->setCellValue("K{$row}", $totalTon);
        $sheet->setCellValue("L{$row}", round($avgYPH, 2));

        $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E5E7EB']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $sheet->getStyle("G{$row}:J{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("K{$row}:L{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

        $row += 3;
        $sheet->setCellValue("A{$row}", 'RINGKASAN PER BLOK');
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);

        $row++;
        $summaryHeaders = ['Blok', 'Biaya TK', 'Biaya Material', 'Total Biaya', 'Total Ton', 'Avg YPH'];
        foreach ($summaryHeaders as $col => $header) {
            $cellAddress = chr(65 + $col) . $row;
            $sheet->setCellValue($cellAddress, $header);
        }
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9CA3AF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        $row++;
        foreach ($summaryPerBlok as $summary) {
            $sheet->setCellValue("A{$row}", $summary['blok']);
            $sheet->setCellValue("B{$row}", $summary['biaya_tk']);
            $sheet->setCellValue("C{$row}", $summary['biaya_material']);
            $sheet->setCellValue("D{$row}", $summary['total_biaya']);
            $sheet->setCellValue("E{$row}", $summary['total_ton']);
            $sheet->setCellValue("F{$row}", $summary['avg_yph']);

            $sheet->getStyle("B{$row}:D{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $row++;
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Biaya_Per_Plot_' . Carbon::now()->format('YmdHis') . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Export to PDF
     */
    public function exportPdf(Request $request)
    {
        $companycode = session('companycode');
        $selectedBloks = json_decode($request->bloks, true) ?? [];
        $generation = (int) ($request->generation ?? 0);

        if (empty($selectedBloks)) {
            return back()->with('error', 'Pilih minimal 1 blok');
        }

        $plots = $this->getPlotsByGeneration($companycode, $selectedBloks, $generation);
        $batchnos = $plots->pluck('batchno')->whereNotNull()->unique()->toArray();

        $biayaTKPerBatch = $this->getBiayaTKPerBatch($companycode, $batchnos);
        $biayaMaterialPerBatch = $this->getBiayaMaterialPerBatch($companycode, $batchnos);
        $infoPanenPerBatch = $this->getInfoPanenPerBatch($companycode, $batchnos);

        foreach ($plots as $plot) {
            $bn = $plot->batchno;
            $plot->biaya_tk = $biayaTKPerBatch[$bn] ?? 0;
            $plot->biaya_material = $biayaMaterialPerBatch[$bn] ?? 0;
            $plot->biaya_panen = 0;
            $plot->total_biaya = $plot->biaya_tk + $plot->biaya_material + $plot->biaya_panen;

            $infoPanen = $infoPanenPerBatch[$bn] ?? null;
            $plot->total_ton = $infoPanen ? ($infoPanen->total_ton ?? 0) : 0;
            $plot->yph = ($plot->total_ton > 0 && $plot->batcharea > 0) ? round($plot->total_ton / $plot->batcharea, 2) : 0;
        }

        $summaryPerBlok = $plots->groupBy('blok')->map(function($items) {
            $plotsWithHarvest = $items->where('total_ton', '>', 0);
            return [
                'blok' => $items->first()->blok,
                'total_plot' => $items->count(),
                'biaya_tk' => $items->sum('biaya_tk'),
                'biaya_material' => $items->sum('biaya_material'),
                'total_biaya' => $items->sum('total_biaya'),
                'total_ton' => $items->sum('total_ton'),
                'avg_yph' => $plotsWithHarvest->count() > 0 ? round($plotsWithHarvest->avg('yph'), 2) : 0,
            ];
        })->values();

        $plotsWithHarvest = $plots->where('total_ton', '>', 0);
        $grandTotal = [
            'biaya_tk' => $plots->sum('biaya_tk'),
            'biaya_material' => $plots->sum('biaya_material'),
            'total_biaya' => $plots->sum('total_biaya'),
            'total_ton' => $plots->sum('total_ton'),
            'avg_yph' => $plotsWithHarvest->count() > 0 ? round($plotsWithHarvest->avg('yph'), 2) : 0,
        ];

        $generationLabel = ['0' => 'Current Cycle', '-1' => 'Last Cycle', '-2' => 'Cycle -2', '-3' => 'Cycle -3'][$generation] ?? 'Current Cycle';

        $pdf = Pdf::loadView('report.biaya-per-plot.pdf', [
            'summaryPerBlok' => $summaryPerBlok,
            'grandTotal' => $grandTotal,
            'generationLabel' => $generationLabel,
            'selectedBloks' => $selectedBloks,
            'tanggal' => Carbon::now()->format('d M Y H:i')
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Biaya_Per_Plot_Summary_' . Carbon::now()->format('YmdHis') . '.pdf');
    }

    /**
     * Detail biaya per batch
     */
    public function show($batchno, Request $request)
    {
        $title = "Detail Biaya Plot";
        $nav = "Biaya Per Plot";
        $companycode = session('companycode');
        $cycle = $request->query('cycle', '0'); // Get cycle from query param

        $batch = DB::table('batch as b')
            ->leftJoin('varietas as v', 'b.kodevarietas', '=', 'v.kodevarietas')
            ->leftJoin('masterlist as m', function($join) {
                $join->on('b.batchno', '=', 'm.activebatchno')
                     ->on('b.companycode', '=', 'm.companycode');
            })
            ->where('b.companycode', $companycode)
            ->where('b.batchno', $batchno)
            ->select(
                'b.batchno',
                'b.plot',
                'm.blok',
                'b.lifecyclestatus',
                'b.batchdate',
                'b.tanggalulangtahun',
                'b.kodevarietas',
                'v.description as varietas_name',
                'b.batcharea',
                'b.isactive'
            )
            ->first();

        if (!$batch) {
            abort(404, 'Batch tidak ditemukan');
        }

        return view('report.biaya-per-plot.show', compact('title', 'nav', 'batch', 'cycle'));
    }

    /**
     * API: Get detail biaya untuk batch tertentu
     */
    public function getDetail($batchno)
    {
        $companycode = session('companycode');

        $batch = DB::table('batch as b')
            ->leftJoin('varietas as v', 'b.kodevarietas', '=', 'v.kodevarietas')
            ->where('b.companycode', $companycode)
            ->where('b.batchno', $batchno)
            ->select(
                'b.batchno',
                'b.plot',
                'b.lifecyclestatus',
                'b.batchdate',
                'b.tanggalulangtahun',
                'b.kodevarietas',
                'v.description as varietas_name',
                'b.batcharea'
            )
            ->first();

        if (!$batch) {
            return response()->json(['success' => false, 'message' => 'Batch tidak ditemukan']);
        }

        $lkhDetails = $this->getLKHDetailForBatch($companycode, $batchno);
        $materialDetails = $this->getMaterialDetailForBatch($companycode, $batchno);

        $summary = [
            'biaya_tk' => collect($lkhDetails)->sum('biaya_proporsional'),
            'biaya_material' => collect($materialDetails)->sum('total_biaya'),
            'biaya_kontraktor' => 0,
        ];
        $summary['total'] = $summary['biaya_tk'] + $summary['biaya_material'] + $summary['biaya_kontraktor'];

        return response()->json([
            'success' => true,
            'data' => [
                'batch' => $batch,
                'lkh_details' => $lkhDetails,
                'material_details' => $materialDetails,
                'kontraktor_details' => [],
                'summary' => $summary
            ]
        ]);
    }

    /**
     * API: Get cycle comparison data for chart
     */
    public function getCycleComparison($batchno)
    {
        $companycode = session('companycode');

        // Get current batch info
        $currentBatch = DB::table('batch')
            ->where('companycode', $companycode)
            ->where('batchno', $batchno)
            ->select('plot', 'batchno', 'previousbatchno')
            ->first();

        if (!$currentBatch) {
            return response()->json(['success' => false, 'message' => 'Batch tidak ditemukan']);
        }

        $cyclesData = [];

        // Current cycle (0)
        $cyclesData[] = $this->getBatchCycleData($companycode, $currentBatch->batchno, 'Current Cycle');

        // Previous cycles (-1, -2, -3)
        $previousBatchno = $currentBatch->previousbatchno;
        $cycleLabels = ['Cycle -1', 'Cycle -2', 'Cycle -3'];
        
        for ($i = 0; $i < 3; $i++) {
            if ($previousBatchno) {
                $cyclesData[] = $this->getBatchCycleData($companycode, $previousBatchno, $cycleLabels[$i]);
                
                // Get next previous batch
                $previousBatchno = DB::table('batch')
                    ->where('companycode', $companycode)
                    ->where('batchno', $previousBatchno)
                    ->value('previousbatchno');
            } else {
                // No more previous batches
                break;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $cyclesData
        ]);
    }

    /**
     * Helper: Get batch cycle data
     */
    private function getBatchCycleData($companycode, $batchno, $label)
    {
        $biayaTK = $this->getBiayaTKPerBatch($companycode, [$batchno]);
        $biayaMaterial = $this->getBiayaMaterialPerBatch($companycode, [$batchno]);
        $infoPanen = $this->getInfoPanenPerBatch($companycode, [$batchno]);

        $batch = DB::table('batch')
            ->where('companycode', $companycode)
            ->where('batchno', $batchno)
            ->select('batcharea', 'tanggalpanen', 'closedat')
            ->first();

        $biayaTKValue = $biayaTK[$batchno] ?? 0;
        $biayaMaterialValue = $biayaMaterial[$batchno] ?? 0;
        $totalBiaya = $biayaTKValue + $biayaMaterialValue;

        $panenInfo = $infoPanen[$batchno] ?? null;
        $totalTon = $panenInfo ? ($panenInfo->total_ton ?? 0) : 0;
        $yph = ($totalTon > 0 && $batch && $batch->batcharea > 0) 
            ? round($totalTon / $batch->batcharea, 2) 
            : 0;

        return [
            'label' => $label,
            'batchno' => $batchno,
            'biaya_tk' => round($biayaTKValue, 2),
            'biaya_material' => round($biayaMaterialValue, 2),
            'total_biaya' => round($totalBiaya, 2),
            'total_ton' => round($totalTon, 2),
            'yph' => $yph,
            'tanggalpanen' => $batch->tanggalpanen ?? null,
            'closedat' => $batch->closedat ?? null,
        ];
    }

    // ========================================================================
    // PRIVATE HELPER METHODS
    // ========================================================================

    private function getPlotsByGeneration($companycode, $selectedBloks, $generation)
    {
        if ($generation == 0) {
            return DB::table('masterlist as m')
                ->join('batch as b', function($join) {
                    $join->on('m.activebatchno', '=', 'b.batchno')
                        ->on('m.companycode', '=', 'b.companycode');
                })
                ->leftJoin('varietas as v', 'b.kodevarietas', '=', 'v.kodevarietas')
                ->where('m.companycode', $companycode)
                ->whereIn('m.blok', $selectedBloks)
                ->where('m.isactive', 1)
                ->where('b.isactive', 1)
                ->select(
                    'm.plot',
                    'm.blok',
                    'b.batchno',
                    'b.lifecyclestatus',
                    'b.batchdate',
                    'b.tanggalulangtahun',
                    'b.kodevarietas',
                    'v.description as varietas_name',
                    'b.batcharea',
                    'b.tanggalpanen',
                    'b.closedat'
                )
                ->orderBy('m.blok')
                ->orderBy('m.plot')
                ->get();
        }

        $masterPlots = DB::table('masterlist as m')
            ->where('m.companycode', $companycode)
            ->whereIn('m.blok', $selectedBloks)
            ->where('m.isactive', 1)
            ->select('m.plot', 'm.blok', 'm.activebatchno')
            ->orderBy('m.blok')
            ->orderBy('m.plot')
            ->get();

        $result = collect();

        foreach ($masterPlots as $master) {
            $targetBatchno = $this->traverseBatchGeneration($companycode, $master->activebatchno, $generation);

            if ($targetBatchno) {
                $batch = DB::table('batch as b')
                    ->leftJoin('varietas as v', 'b.kodevarietas', '=', 'v.kodevarietas')
                    ->where('b.companycode', $companycode)
                    ->where('b.batchno', $targetBatchno)
                    ->select(
                        'b.batchno',
                        'b.lifecyclestatus',
                        'b.batchdate',
                        'b.tanggalulangtahun',
                        'b.kodevarietas',
                        'v.description as varietas_name',
                        'b.batcharea',
                        'b.tanggalpanen',
                        'b.closedat'
                    )
                    ->first();

                if ($batch) {
                    $result->push((object) [
                        'plot' => $master->plot,
                        'blok' => $master->blok,
                        'batchno' => $batch->batchno,
                        'lifecyclestatus' => $batch->lifecyclestatus,
                        'batchdate' => $batch->batchdate,
                        'tanggalulangtahun' => $batch->tanggalulangtahun,
                        'kodevarietas' => $batch->kodevarietas,
                        'varietas_name' => $batch->varietas_name,
                        'batcharea' => $batch->batcharea,
                        'tanggalpanen' => $batch->tanggalpanen,
                        'closedat' => $batch->closedat,
                    ]);
                }
            }
        }

        return $result;
    }

    private function traverseBatchGeneration($companycode, $startBatchno, $generation)
    {
        if (!$startBatchno || $generation >= 0) {
            return $startBatchno;
        }

        $currentBatchno = $startBatchno;
        $steps = abs($generation);

        for ($i = 0; $i < $steps; $i++) {
            $previousBatchno = DB::table('batch')
                ->where('companycode', $companycode)
                ->where('batchno', $currentBatchno)
                ->value('previousbatchno');

            if (!$previousBatchno) {
                return null;
            }

            $currentBatchno = $previousBatchno;
        }

        return $currentBatchno;
    }

    private function getInfoPanenPerBatch($companycode, $batchnos)
    {
        if (empty($batchnos)) return [];

        $batches = DB::table('batch')
            ->whereIn('batchno', $batchnos)
            ->where('companycode', $companycode)
            ->select('batchno', 'plot', 'tanggalpanen', 'closedat')
            ->get()
            ->keyBy('batchno');

        $result = [];

        foreach ($batchnos as $batchno) {
            $batch = $batches->get($batchno);
            if (!$batch) continue;

            if ($batch->tanggalpanen) {
                $data = $this->getPanenDataByDateRange($companycode, $batch);
            } else {
                $data = $this->getPanenDataByPlotOnly($companycode, $batch->plot);
            }

            $result[$batchno] = $data;
        }

        return $result;
    }

    private function getPanenDataByDateRange($companycode, $batch)
    {
        $startDate = $batch->tanggalpanen;
        $endDate = $batch->closedat ?? now();

        return DB::table('suratjalanpos as sj')
            ->join('timbanganpayload as tp', function($join) {
                $join->on('sj.companycode', '=', 'tp.companycode')
                     ->on('sj.suratjalanno', '=', 'tp.suratjalanno');
            })
            ->where('sj.companycode', $companycode)
            ->where('sj.plot', $batch->plot)
            ->whereBetween('sj.tanggalcetakpossecurity', [$startDate, $endDate])
            ->select(
                DB::raw('COUNT(DISTINCT sj.suratjalanno) as jumlah_sj'),
                DB::raw('SUM(tp.netto) / 1000 as total_ton')
            )
            ->first();
    }

    private function getPanenDataByPlotOnly($companycode, $plot)
    {
        return DB::table('suratjalanpos as sj')
            ->join('timbanganpayload as tp', function($join) {
                $join->on('sj.companycode', '=', 'tp.companycode')
                     ->on('sj.suratjalanno', '=', 'tp.suratjalanno');
            })
            ->where('sj.companycode', $companycode)
            ->where('sj.plot', $plot)
            ->whereNotNull('sj.tanggalcetakpossecurity')
            ->select(
                DB::raw('COUNT(DISTINCT sj.suratjalanno) as jumlah_sj'),
                DB::raw('SUM(tp.netto) / 1000 as total_ton')
            )
            ->first();
    }

    private function getBiayaTKPerBatch($companycode, $batchnos)
    {
        if (empty($batchnos)) return [];

        $data = DB::table('lkhdetailplot as ldp')
            ->join('lkhhdr as lh', 'ldp.lkhhdrid', '=', 'lh.id')
            ->where('lh.companycode', $companycode)
            ->whereIn('ldp.batchno', $batchnos)
            ->whereNotNull('ldp.batchno')
            ->select(
                'ldp.batchno',
                'lh.id as lkhid',
                'lh.lkhno',
                'lh.totalupahall',
                'ldp.luashasil',
                'ldp.plot'
            )
            ->get();

        if ($data->isEmpty()) return [];

        $lkhIds = $data->pluck('lkhid')->unique()->toArray();
        $totalLuasPerLKH = DB::table('lkhdetailplot')
            ->whereIn('lkhhdrid', $lkhIds)
            ->select('lkhhdrid', DB::raw('SUM(COALESCE(luashasil, 0)) as total_luas'))
            ->groupBy('lkhhdrid')
            ->pluck('total_luas', 'lkhhdrid')
            ->toArray();

        $biayaPerBatch = [];

        foreach ($data as $row) {
            $batchno = $row->batchno;
            $lkhid = $row->lkhid;
            $totalupahall = $row->totalupahall ?? 0;
            $luashasil = $row->luashasil ?? 0;
            $totalLuasLKH = $totalLuasPerLKH[$lkhid] ?? 0;

            $biayaProporsional = 0;
            if ($totalLuasLKH > 0) {
                $biayaProporsional = ($luashasil / $totalLuasLKH) * $totalupahall;
            }

            if (!isset($biayaPerBatch[$batchno])) {
                $biayaPerBatch[$batchno] = 0;
            }
            $biayaPerBatch[$batchno] += $biayaProporsional;
        }

        return $biayaPerBatch;
    }

    private function getBiayaMaterialPerBatch($companycode, $batchnos)
    {
        if (empty($batchnos)) return [];

        $data = DB::table('usemateriallst as uml')
            ->join('lkhhdr as lh', function($join) {
                $join->on('uml.lkhno', '=', 'lh.lkhno')
                     ->on('uml.companycode', '=', 'lh.companycode');
            })
            ->join('lkhdetailplot as ldp', function($join) {
                $join->on('lh.id', '=', 'ldp.lkhhdrid')
                     ->on('uml.plot', '=', 'ldp.plot');
            })
            ->where('uml.companycode', $companycode)
            ->whereIn('ldp.batchno', $batchnos)
            ->whereNotNull('ldp.batchno')
            ->select('ldp.batchno', DB::raw('SUM(COALESCE(uml.itemprice, 0) * COALESCE(uml.qtydigunakan, 0)) as total'))
            ->groupBy('ldp.batchno')
            ->pluck('total', 'batchno')
            ->toArray();

        return $data;
    }

    private function getLKHDetailForBatch($companycode, $batchno)
    {
        $data = DB::table('lkhdetailplot as ldp')
            ->join('lkhhdr as lh', 'ldp.lkhhdrid', '=', 'lh.id')
            ->join('activity as a', 'lh.activitycode', '=', 'a.activitycode')
            ->where('lh.companycode', $companycode)
            ->where('ldp.batchno', $batchno)
            ->select(
                'lh.id as lkhid',
                'lh.lkhno',
                'lh.lkhdate',
                'lh.activitycode',
                'a.activityname',
                'lh.jenistenagakerja',
                'lh.totalworkers',
                'lh.totalupahall',
                'ldp.plot',
                'ldp.luashasil',
                'ldp.luasrkh'
            )
            ->orderBy('lh.lkhdate')
            ->orderBy('lh.lkhno')
            ->get();

        if ($data->isEmpty()) return [];

        $lkhIds = $data->pluck('lkhid')->unique()->toArray();
        $totalLuasPerLKH = DB::table('lkhdetailplot')
            ->whereIn('lkhhdrid', $lkhIds)
            ->select('lkhhdrid', DB::raw('SUM(COALESCE(luashasil, 0)) as total_luas'))
            ->groupBy('lkhhdrid')
            ->pluck('total_luas', 'lkhhdrid')
            ->toArray();

        $result = [];
        foreach ($data as $row) {
            $totalLuasLKH = $totalLuasPerLKH[$row->lkhid] ?? 0;
            $proporsi = $totalLuasLKH > 0 ? ($row->luashasil / $totalLuasLKH) : 0;
            $biayaProporsional = $proporsi * ($row->totalupahall ?? 0);

            $result[] = [
                'lkhno' => $row->lkhno,
                'lkhdate' => $row->lkhdate,
                'activitycode' => $row->activitycode,
                'activityname' => $row->activityname,
                'jenistenagakerja' => $row->jenistenagakerja == 1 ? 'Harian' : 'Borongan',
                'totalworkers' => $row->totalworkers,
                'luashasil' => (float) $row->luashasil,
                'total_luas_lkh' => (float) $totalLuasLKH,
                'proporsi' => round($proporsi * 100, 2),
                'totalupahall' => (float) $row->totalupahall,
                'biaya_proporsional' => round($biayaProporsional, 2)
            ];
        }

        return $result;
    }

    private function getMaterialDetailForBatch($companycode, $batchno)
    {
        $data = DB::table('usemateriallst as uml')
            ->join('lkhhdr as lh', function($join) {
                $join->on('uml.lkhno', '=', 'lh.lkhno')
                     ->on('uml.companycode', '=', 'lh.companycode');
            })
            ->join('lkhdetailplot as ldp', function($join) {
                $join->on('lh.id', '=', 'ldp.lkhhdrid')
                     ->on('uml.plot', '=', 'ldp.plot');
            })
            ->join('herbisida as h', function($join) {
                $join->on('uml.itemcode', '=', 'h.itemcode')
                     ->on('uml.companycode', '=', 'h.companycode');
            })
            ->where('uml.companycode', $companycode)
            ->where('ldp.batchno', $batchno)
            ->select(
                'uml.lkhno',
                'lh.lkhdate',
                'uml.itemcode',
                'h.itemname',
                'uml.qtydigunakan',
                'h.measure',
                'uml.itemprice',
                DB::raw('COALESCE(uml.itemprice, 0) * COALESCE(uml.qtydigunakan, 0) as total_biaya')
            )
            ->orderBy('lh.lkhdate')
            ->get();

        return $data->toArray();
    }
}