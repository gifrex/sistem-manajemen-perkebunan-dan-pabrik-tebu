<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PivotController extends Controller
{
    public function pivotTableAgronomi(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

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
            ->where('agrolst.companycode', session('companycode'))
            ->where('agrohdr.companycode', session('companycode'))
            ->where('agrohdr.status', '=', 'Posted')
            ->where('agrolst.status', '=', 'Posted')
            ->select(
                'company.name as company',
                'blok.blok as Blok',
                'batch.plot as Plot',
                DB::raw("MONTH(agrohdr.tanggalpengamatan) as Bulan"),
                DB::raw("AVG(per_germinasi) as Germinasi"),
                DB::raw("AVG(per_gap) as GAP"),
                DB::raw("AVG(populasi) as Populasi"),
                DB::raw("AVG(per_gulma) as PenutupanGulma"),
                DB::raw("AVG(ph_tanah) as PHTanah")
            )
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('agrohdr.tanggalpengamatan', [$startDate, $endDate]);
            })
            ->groupBy('Bulan', 'company', 'Blok', 'Plot')
            ->orderBy('Bulan', 'asc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pivot Table');

        $headers = ['Kebun', 'Blok', 'Plot', 'Bulan', 'Average of %Germinasi', 'Average of %GAP', 'Average of Populasi', 'Average of %Penutupan Gulma', 'Average of pH Tanah'];
        $sheet->fromArray($headers, NULL, 'A1');
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'ADD8E6'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        $columnIndex = 1;
        foreach ($headers as $header) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            $columnIndex++;
        }
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->freezePane('A2');

        $row = 2;
        $previousCompany = null;
        $previousBlok = null;
        $previousPlot = null;
        $previousMonth = null;
        $startMergeCompanyRow = 2;
        $startMergeBlokRow = 2;
        $startMergePlotRow = 2;
        $startMergeMonthRow = 2;
        foreach ($chartDataQuery as $index => $data) {
            $currentCompany = $data->company;
            $currentBlok = $data->Blok;
            $currentPlot = $data->Plot;
            $currentMonth = Carbon::createFromFormat('m', $data->Bulan)->translatedFormat('F');

            if ($previousCompany !== null && $currentCompany === $previousCompany) {
                $sheet->setCellValue("A$row", '');
            } else {
                $sheet->setCellValue("A$row", $currentCompany);
                $startMergeCompanyRow = $row;
            }

            if ($previousBlok !== null && $currentBlok === $previousBlok && $currentCompany === $previousCompany) {
                $sheet->setCellValue("B$row", '');
            } else {
                $sheet->setCellValue("B$row", $currentBlok);
                $startMergeBlokRow = $row;
            }

            if ($previousPlot !== null && $currentPlot === $previousPlot && $currentBlok === $previousBlok && $currentCompany === $previousCompany) {
                $sheet->setCellValue("C$row", '');
            } else {
                $sheet->setCellValue("C$row", $currentPlot);
                $startMergePlotRow = $row;
            }

            if ($previousMonth !== null && $currentMonth === $previousMonth) {
                $sheet->setCellValue("D$row", '');
            } else {
                $sheet->setCellValue("D$row", $currentMonth);
                $startMergeMonthRow = $row;
            }

            $sheet->fromArray([
                null,
                null,
                null,
                null,
            ], NULL, "A$row");

            $sheet->setCellValueExplicit("E$row", round($data->Germinasi ?? 0, 4), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("F$row", round($data->GAP ?? 0, 4), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("G$row", round($data->Populasi ?? 0, 4), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("H$row", round($data->PenutupanGulma ?? 0, 4), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("I$row", round($data->PHTanah ?? 0, 4), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

            if (!isset($chartDataQuery[$index + 1]) || $chartDataQuery[$index + 1]->company !== $currentCompany) {
                if ($row > $startMergeCompanyRow) {
                    $sheet->mergeCells("A{$startMergeCompanyRow}:A{$row}");
                    $sheet->getStyle("A{$startMergeCompanyRow}:A{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }
            }

            if (!isset($chartDataQuery[$index + 1]) || $chartDataQuery[$index + 1]->Blok !== $currentBlok || $chartDataQuery[$index + 1]->company !== $currentCompany) {
                if ($row > $startMergeBlokRow) {
                    $sheet->mergeCells("B{$startMergeBlokRow}:B{$row}");
                    $sheet->getStyle("B{$startMergeBlokRow}:B{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }
            }

            if (!isset($chartDataQuery[$index + 1]) || $chartDataQuery[$index + 1]->Plot !== $currentPlot || $chartDataQuery[$index + 1]->Blok !== $currentBlok || $chartDataQuery[$index + 1]->company !== $currentCompany) {
                if ($row > $startMergePlotRow) {
                    $sheet->mergeCells("C{$startMergePlotRow}:C{$row}");
                    $sheet->getStyle("C{$startMergePlotRow}:C{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }
            }

            if (!isset($chartDataQuery[$index + 1]) || Carbon::createFromFormat('m', $chartDataQuery[$index + 1]->Bulan)->translatedFormat('F') !== $currentMonth) {
                if ($row > $startMergeMonthRow) {
                    $sheet->mergeCells("D{$startMergeMonthRow}:D{$row}");
                    $sheet->getStyle("D{$startMergeMonthRow}:D{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }
            }

            $previousCompany = $currentCompany;
            $previousBlok = $currentBlok;
            $previousPlot = $currentPlot;
            $previousMonth = $currentMonth;

            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        if ($startDate && $endDate) {
            $fileName = "Agronomi_Pivot_Table_{$startDate}_sd_{$endDate}.xlsx";
        } else {
            $fileName = "Agronomi_Pivot_Table.xlsx";
        }
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function pivotTableHPT(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $chartDataQuery = DB::table('hpthdr')
            ->join('hptlst', function ($join) {
                $join->on('hpthdr.nosample', '=', 'hptlst.nosample')
                    ->on('hpthdr.companycode', '=', 'hptlst.companycode');
            })
            ->join('company', 'hpthdr.companycode', '=', 'company.companycode')
            ->join('batch', function ($join) {
                $join->on('hpthdr.plot', '=', 'batch.plot')
                    ->on('hpthdr.companycode', '=', 'batch.companycode');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('hpthdr.blok', '=', 'blok.blok')
                    ->whereColumn('hpthdr.companycode', '=', 'blok.companycode');
            })
            ->where('hptlst.companycode', session('companycode'))
            ->where('hpthdr.companycode', session('companycode'))
            ->where('hpthdr.status', '=', 'Posted')
            ->where('hptlst.status', '=', 'Posted')
            ->select(
                'company.name as company',
                'blok.blok as Blok',
                'batch.plot as Plot',
                DB::raw("MONTH(hpthdr.tanggalpengamatan) as Bulan"),
                DB::raw("AVG(per_ppt) as PersenPPT"),
                DB::raw("AVG(per_pbt) as PersenPBT"),
                DB::raw("AVG(per_ppt_aktif) as PersenPPTAktif"),
                DB::raw("AVG(per_pbt_aktif) as PersenPBTAktif"),
                DB::raw("AVG(int_rusak) as Intensitas")
            )
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('hpthdr.tanggalpengamatan', [$startDate, $endDate]);
            })
            ->groupBy('Bulan', 'company', 'Blok', 'Plot')
            ->orderBy('Bulan', 'asc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pivot Table');

        $headers = ['Kebun', 'Blok', 'Plot', 'Bulan', 'Average of %PPT', 'Average of %PBT', 'Average of %PPT Aktif', 'Average of %PBT Aktif', 'Average of Intensitas'];
        $sheet->fromArray($headers, NULL, 'A1');
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'ADD8E6'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        $columnIndex = 1;
        foreach ($headers as $header) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            $columnIndex++;
        }
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->freezePane('A2');

        $row = 2;
        $previousCompany = null;
        $previousBlok = null;
        $previousPlot = null;
        $previousMonth = null;
        $startMergeCompanyRow = 2;
        $startMergeBlokRow = 2;
        $startMergePlotRow = 2;
        $startMergeMonthRow = 2;
        foreach ($chartDataQuery as $index => $data) {
            $currentCompany = $data->company;
            $currentBlok = $data->Blok;
            $currentPlot = $data->Plot;
            $currentMonth = Carbon::createFromFormat('m', $data->Bulan)->translatedFormat('F');

            if ($previousCompany !== null && $currentCompany === $previousCompany) {
                $sheet->setCellValue("A$row", '');
            } else {
                $sheet->setCellValue("A$row", $currentCompany);
                $startMergeCompanyRow = $row;
            }

            if ($previousBlok !== null && $currentBlok === $previousBlok && $currentCompany === $previousCompany) {
                $sheet->setCellValue("B$row", '');
            } else {
                $sheet->setCellValue("B$row", $currentBlok);
                $startMergeBlokRow = $row;
            }

            if ($previousPlot !== null && $currentPlot === $previousPlot && $currentBlok === $previousBlok && $currentCompany === $previousCompany) {
                $sheet->setCellValue("C$row", '');
            } else {
                $sheet->setCellValue("C$row", $currentPlot);
                $startMergePlotRow = $row;
            }

            if ($previousMonth !== null && $currentMonth === $previousMonth) {
                $sheet->setCellValue("D$row", '');
            } else {
                $sheet->setCellValue("D$row", $currentMonth);
                $startMergeMonthRow = $row;
            }

            $sheet->fromArray([
                null,
                null,
                null,
                null,
            ], NULL, "A$row");

            $sheet->setCellValueExplicit("E$row", round($data->PersenPPT ?? 0, 4), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("F$row", round($data->PersenPBT ?? 0, 4), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("G$row", round($data->PersenPPTAktif ?? 0, 4), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("H$row", round($data->PersenPBTAktif ?? 0, 4), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("I$row", round($data->Intensitas ?? 0, 4), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

            if (!isset($chartDataQuery[$index + 1]) || $chartDataQuery[$index + 1]->company !== $currentCompany) {
                if ($row > $startMergeCompanyRow) {
                    $sheet->mergeCells("A{$startMergeCompanyRow}:A{$row}");
                    $sheet->getStyle("A{$startMergeCompanyRow}:A{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }
            }

            if (!isset($chartDataQuery[$index + 1]) || $chartDataQuery[$index + 1]->Blok !== $currentBlok || $chartDataQuery[$index + 1]->company !== $currentCompany) {
                if ($row > $startMergeBlokRow) {
                    $sheet->mergeCells("B{$startMergeBlokRow}:B{$row}");
                    $sheet->getStyle("B{$startMergeBlokRow}:B{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }
            }

            if (!isset($chartDataQuery[$index + 1]) || $chartDataQuery[$index + 1]->Plot !== $currentPlot || $chartDataQuery[$index + 1]->Blok !== $currentBlok || $chartDataQuery[$index + 1]->company !== $currentCompany) {
                if ($row > $startMergePlotRow) {
                    $sheet->mergeCells("C{$startMergePlotRow}:C{$row}");
                    $sheet->getStyle("C{$startMergePlotRow}:C{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }
            }

            if (!isset($chartDataQuery[$index + 1]) || Carbon::createFromFormat('m', $chartDataQuery[$index + 1]->Bulan)->translatedFormat('F') !== $currentMonth) {
                if ($row > $startMergeMonthRow) {
                    $sheet->mergeCells("D{$startMergeMonthRow}:D{$row}");
                    $sheet->getStyle("D{$startMergeMonthRow}:D{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }
            }

            $previousCompany = $currentCompany;
            $previousBlok = $currentBlok;
            $previousPlot = $currentPlot;
            $previousMonth = $currentMonth;

            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        if ($startDate && $endDate) {
            $fileName = "HPT_Pivot_Table_{$startDate}_sd_{$endDate}.xlsx";
        } else {
            $fileName = "HPT_Pivot_Table.xlsx";
        }
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
