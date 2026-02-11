<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\TenagaKerja;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TenagaKerjaController extends Controller
{
    /**
     * Display a listing of tenaga kerja.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search = $request->input('search');
        $companycode = session('companycode');

        // Query dengan explicit SELECT dan proper JOIN
        $query = DB::table('tenagakerja as tk')
            ->leftJoin('jenistenagakerja as jtk', 'tk.jenistenagakerja', '=', 'jtk.idjenistenagakerja')
            ->leftJoin('user as u', 'tk.mandoruserid', '=', 'u.userid')
            ->where('tk.companycode', $companycode)
            ->select([
                'tk.tenagakerjaid',
                'tk.mandoruserid',
                'tk.companycode',
                'tk.nama',
                'tk.nik',
                'tk.gender',
                'tk.jenistenagakerja',
                'tk.isactive',
                'jtk.nama as jenis_nama',
                'u.name as mandor_nama'
            ]);

        // Search functionality
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('tk.nama', 'like', "%{$search}%")
                    ->orWhere('tk.nik', 'like', "%{$search}%")
                    ->orWhere('tk.tenagakerjaid', 'like', "%{$search}%")
                    ->orWhere('u.name', 'like', "%{$search}%")
                    ->orWhere('jtk.nama', 'like', "%{$search}%");
            });
        }

        $result = $query
            ->orderBy('tk.isactive', 'desc')
            ->orderBy('tk.tenagakerjaid', 'asc')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search' => $search,
            ]);

        // Get mandor list (idjabatan = 5, active only, filtered by company)
        $mandor = User::where('idjabatan', 5)
            ->where('isactive', 1)
            ->where('companycode', $companycode)
            ->orderBy('name')
            ->get();

        // Get jenis tenaga kerja list (include all types)
        $jenistenagakerja = DB::table('jenistenagakerja')
            ->orderBy('idjenistenagakerja')
            ->get();

        return view('masterdata.tenagakerja.index', [
            'result' => $result,
            'title' => 'Data Tenaga Kerja',
            'navbar' => 'Master',
            'nav' => 'Tenaga Kerja',
            'perPage' => $perPage,
            'search' => $search,
            'mandor' => $mandor,
            'jenistenagakerja' => $jenistenagakerja,
            'companycode' => $companycode
        ]);
    }

    /**
     * Generate next tenaga kerja ID with M0001, M0002 format
     */
    private function generateNextId($companycode)
    {
        // Get the latest ID for this company
        $latestRecord = TenagaKerja::where('companycode', $companycode)
            ->orderByRaw('CAST(SUBSTRING(tenagakerjaid, 2) AS UNSIGNED) DESC')
            ->first();

        if (!$latestRecord) {
            // No existing record for this company, start with M0001
            return 'M0001';
        }

        // Extract the numeric part and increment
        $idNumber = (int) substr($latestRecord->tenagakerjaid, 1);
        $nextNumber = $idNumber + 1;

        // Format as M0001, M0002, etc.
        return 'M' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Download template Excel untuk bulk upload
     */
    public function downloadTemplate()
    {
        $companycode = session('companycode');

        // Get data untuk dropdown
        $mandor = User::where('idjabatan', 5)
            ->where('isactive', 1)
            ->where('companycode', $companycode)
            ->orderBy('userid')
            ->get();

        $jenistenagakerja = DB::table('jenistenagakerja')
            ->orderBy('idjenistenagakerja')
            ->get();

        $spreadsheet = new Spreadsheet();

        // Sheet 1: Template untuk input data
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Data');

        // Keterangan/Instruksi di bagian atas
        $sheet->setCellValue('A1', 'KETERANGAN:');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('FF0000');

        $sheet->setCellValue('A2', '- Untuk cek Mandor User ID, klik tab dibawah "Daftar Mandor"');
        $sheet->setCellValue('A3', '- Sebelum mengisi Jenis Tenaga Kerja ID, harap melihat terlebih dahulu tab "Jenis Tenaga Kerja" untuk mencocokan ID nya');
        $sheet->setCellValue('A4', '- Pastikan semua data diisi dengan benar sesuai format');
        $sheet->setCellValue('A5', '- NIK harus berisi 16 digit angka saja (contoh: 3201234567890123)');

        $sheet->getStyle('A2:A5')->getFont()->setItalic(true)->setSize(10);
        $sheet->getStyle('A2:A5')->getFont()->getColor()->setRGB('666666');

        // Merge cells untuk keterangan
        $sheet->mergeCells('A2:E2');
        $sheet->mergeCells('A3:E3');
        $sheet->mergeCells('A4:E4');
        $sheet->mergeCells('A5:E5');

        // Header tabel mulai dari row 7
        $headers = ['Mandor User ID', 'Nama Tenaga Kerja', 'NIK', 'Gender (L/P)', 'Jenis Tenaga Kerja ID'];
        $sheet->fromArray($headers, null, 'A7');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('A7:E7')->applyFromArray($headerStyle);

        // SET FORMAT KOLOM NIK (KOLOM C) SEBAGAI TEXT
        // Format dari row 8 sampai 1000 (atau sesuai kebutuhan)
        $sheet->getStyle('C8:C1000')
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // Contoh data di row 8
        $sheet->setCellValue('A8', $mandor->first()->userid ?? 'MDR001');
        $sheet->setCellValue('B8', 'Contoh: Budi Santoso');
        // Set NIK dengan setCellValueExplicit untuk pastikan format text
        $sheet->setCellValueExplicit('C8', '3201234567890123', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('D8', 'L');
        $sheet->setCellValue('E8', '1');

        // Style untuk contoh data
        $exampleStyle = [
            'font' => ['italic' => true, 'color' => ['rgb' => '999999']],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ];
        $sheet->getStyle('A8:E8')->applyFromArray($exampleStyle);

        // Auto width
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set minimum width untuk kolom
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(25);

        // Sheet 2: Daftar Mandor
        $mandorSheet = $spreadsheet->createSheet();
        $mandorSheet->setTitle('Daftar Mandor');
        $mandorSheet->fromArray(['User ID', 'Nama Mandor'], null, 'A1');
        $mandorSheet->getStyle('A1:B1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($mandor as $m) {
            $mandorSheet->setCellValue('A' . $row, $m->userid);
            $mandorSheet->setCellValue('B' . $row, $m->name);
            $row++;
        }
        $mandorSheet->getColumnDimension('A')->setAutoSize(true);
        $mandorSheet->getColumnDimension('B')->setAutoSize(true);

        // Sheet 3: Daftar Jenis Tenaga Kerja
        $jenisSheet = $spreadsheet->createSheet();
        $jenisSheet->setTitle('Jenis Tenaga Kerja');
        $jenisSheet->fromArray(['ID', 'Nama Jenis'], null, 'A1');
        $jenisSheet->getStyle('A1:B1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($jenistenagakerja as $jenis) {
            $jenisSheet->setCellValue('A' . $row, $jenis->idjenistenagakerja);
            $jenisSheet->setCellValue('B' . $row, $jenis->nama);
            $row++;
        }
        $jenisSheet->getColumnDimension('A')->setAutoSize(true);
        $jenisSheet->getColumnDimension('B')->setAutoSize(true);

        // Set active sheet kembali ke template
        $spreadsheet->setActiveSheetIndex(0);

        // Download file
        $writer = new Xlsx($spreadsheet);
        $filename = 'Template_Upload_Tenaga_Kerja_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Bulk upload tenaga kerja dari Excel
     */
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048'
        ]);

        $companycode = session('companycode');
        $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Remove keterangan (row 1-7) dan header row (row 7)
            // Data dimulai dari row 8, tapi karena array index 0, maka row 8 = index 7
            $dataRows = array_slice($rows, 7); // Ambil dari index 7 ke bawah (row 8 dst)
            
            if ($dataRows === null || count($dataRows) == 0) {
                return redirect()->back()->with('error', 'File tidak berisi data.');
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            DB::beginTransaction();
            foreach ($dataRows as $index => $row) {
                $rowNumber = $index + 8; // +8 karena data mulai row 8 di Excel

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                $mandoruserid = trim($row[0] ?? '');
                $nama = trim($row[1] ?? '');
                $nik = trim($row[2] ?? '');
                $gender = strtoupper(trim($row[3] ?? ''));
                $jenistenagakerja = trim($row[4] ?? '');

                // Validation
                if (empty($nama)) {
                    $errors[] = "Baris $rowNumber: Nama tidak boleh kosong";
                    $errorCount++;
                    continue;
                }

                if (empty($mandoruserid)) {
                    $errors[] = "Baris $rowNumber: Mandor UserID tidak boleh kosong";
                    $errorCount++;
                    continue;
                }
                

                if (!in_array($gender, ['L', 'P'])) {
                    $errors[] = "Baris $rowNumber: Gender harus L atau P";
                    $errorCount++;
                    continue;
                }

                // Check if mandor exists
                $mandorExists = User::where('userid', $mandoruserid)
                    ->where('idjabatan', 5)
                    ->exists();

                if (!$mandorExists) {
                    $errors[] = "Baris $rowNumber: Mandor dengan UserID '$mandoruserid' tidak ditemukan";
                    $errorCount++;
                    continue;
                }

                // Check if jenis tenaga kerja exists
                $jenisExists = DB::table('jenistenagakerja')
                    ->where('idjenistenagakerja', $jenistenagakerja)
                    ->exists();

                if (!$jenisExists) {
                    $errors[] = "Baris $rowNumber: Jenis Tenaga Kerja ID '$jenistenagakerja' tidak ditemukan";
                    $errorCount++;
                    continue;
                }

                // Check if NIK already exists (hanya cek yang active)
                if (!empty($nik)) {
                    $nikExists = TenagaKerja::where('nik', $nik)
                        ->where('isactive', 1)
                        ->exists();
                    if ($nikExists) {
                        $errors[] = "Baris $rowNumber: NIK '$nik' sudah terdaftar dan masih aktif";
                        $errorCount++;
                        continue;
                    }
                }


                $cekmandor = TenagaKerja::where('mandoruserid', $mandoruserid)
                    ->where('isactive', 1)
                    ->exists();
                if ($cekmandor) {
                    $errors[] = "Baris $rowNumber: Mandor tersebut sudah terdaftar";
                    $errorCount++;
                    continue;
                }
                

                // Generate next ID
                $nextId = $this->generateNextId($companycode);
                
                // Insert data
                TenagaKerja::create([
                    'tenagakerjaid' => $nextId,
                    'mandoruserid' => $mandoruserid,
                    'companycode' => $companycode,
                    'nama' => $nama,
                    'nik' => $nik ?: null,
                    'gender' => $gender,
                    'jenistenagakerja' => $jenistenagakerja,
                    'inputby' => Auth::user()->userid,
                    'createdat' => now(),
                    'isactive' => 1
                ]);

                $successCount++;
            }

            DB::commit();

            $message = "Upload selesai: $successCount data berhasil";
            $errormessage = "Upload Tidak Berhasil";
            if ($errorCount > 0) {
                $message .= ", $errorCount data gagal";
            }

            if (!empty($errors)) {
                return redirect()->back()
                    ->with('error', $errormessage)
                    ->with('upload_errors', $errors);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created tenaga kerja in storage.
     */
    public function store(Request $request)
    {
        

        $companycode = session('companycode');

        // Check NIK yang masih aktif
        $ceknik = TenagaKerja::where('nik', $request->nik)
            ->where('isactive', 1)
            ->first();
        if ($ceknik) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['nik' => 'NIK sudah terdaftar dan masih aktif']);
        }

        $cekmandor = TenagaKerja::where('mandoruserid', $request->mandor)
            ->where('isactive', 1)
            ->first();

        if ($cekmandor) {
            return redirect()->back()s
                ->withInput()
                ->withErrors(['mandor' => 'Mandor tersebut sudah terdaftar']);
        }

        $nextId = $this->generateNextId($companycode);

        // Double check if the ID already exists
        $exists = TenagaKerja::where('companycode', $companycode)
            ->where('tenagakerjaid', $nextId)
            ->exists();
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['id' => 'Gagal mendapatkan ID unik']);
        }

        TenagaKerja::create([
            'tenagakerjaid' => $nextId,
            'mandoruserid' => $request->mandor,
            'companycode' => $companycode,
            'nama' => $request->name,
            'nik' => $request->nik,
            'gender' => $request->gender,
            'jenistenagakerja' => $request->jenis,
            'inputby' => Auth::user()->userid,
            'createdat' => now(),
            'isactive' => 1
        ]);

        return redirect()->back()->with('success', 'Data tenaga kerja berhasil ditambahkan.');
    }

    /**
     * Update the specified tenaga kerja in storage.
     */
    public function update(Request $request, $companycode, $id)
    { 
        $tenagaKerja = TenagaKerja::where('companycode', $companycode)
            ->where('tenagakerjaid', $id)
            ->firstOrFail();

        // Check NIK yang sama tapi beda ID dan masih aktif
        $ceknik = TenagaKerja::where('nik', $request->nik)
            ->where('tenagakerjaid', '!=', $id)
            ->where('isactive', 1)
            ->first();
        if ($ceknik) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['nik' => 'NIK sudah terdaftar dan masih aktif']);
        }

        $tenagaKerja->update([
            'mandoruserid' => $request->mandor,
            'nama' => $request->name,
            'nik' => $request->nik,
            'gender' => $request->gender,
            'jenistenagakerja' => $request->jenis,
            'updateby' => Auth::user()->userid,
            'updatedat' => now(),
            'isactive' => $request->has('isactive') ? 1 : 0
        ]);

        return redirect()->back()->with('success', 'Data tenaga kerja berhasil diperbarui.');
    }

    /**
     * Soft delete (set inactive) the specified tenaga kerja.
     */
    public function destroy($companycode, $id)
    {
        $tenagaKerja = TenagaKerja::where('companycode', $companycode)
            ->where('tenagakerjaid', $id)
            ->firstOrFail();

        $tenagaKerja->update([
            'isactive' => 0,
            'updateby' => Auth::user()->userid,
            'updatedat' => now()
        ]);

        return redirect()->back()->with('success', 'Data tenaga kerja berhasil dinonaktifkan.');
    }
}