<?php

namespace App\Http\Controllers\Api\FileUpload;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FotoAbsenController extends Controller
{
    /**
     * Upload foto absen masuk
     * POST /api/fileupload/foto-absen-masuk
     */
    public function uploadMasuk(Request $request)
    {
        return $this->uploadFoto($request, 'masuk');
    }
    
    /**
     * Upload foto absen pulang
     * POST /api/fileupload/foto-absen-pulang
     */
    public function uploadPulang(Request $request)
    {
        return $this->uploadFoto($request, 'pulang');
    }
    
    /**
     * Private method untuk handle upload
     * Struktur folder: absensi/YYYY/MM/DD/COMPANY/filename.jpg
     * 
     * FIXED:
     * - Allow re-upload (delete old photo first)
     * - Use absen date for folder structure (not upload date)
     */
    private function uploadFoto(Request $request, string $tipeAbsen)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validate([
                'foto' => 'required|image|mimes:jpeg,jpg,png|max:5120',
                'absenno' => 'required|string|max:20',
                'tenagakerjaid' => 'required|string|max:11',
                'companycode' => 'required|string|max:4'
            ]);
            
            $file = $request->file('foto');
            $now = now();
            
            // Get extension dengan fallback
            $extension = $file->getClientOriginalExtension();
            if (empty($extension)) {
                $extension = $file->guessExtension() ?? 'jpg';
            }
            
            // Cek record berdasarkan absenno, tenagakerjaid, companycode
            $absen = DB::table('absenlst')
                ->where('absenno', $validated['absenno'])
                ->where('tenagakerjaid', $validated['tenagakerjaid'])
                ->where('companycode', $validated['companycode'])
                ->first();
            
            if (!$absen) {
                throw new \Exception('Data absen tidak ditemukan');
            }
            
            // Tentukan kolom
            $columnName = $tipeAbsen === 'masuk' ? 'fotoabsenmasuk' : 'fotoabsenpulang';
            
            // FIX: Jika sudah ada foto lama, hapus dulu dari S3
            $isReupload = false;
            if (!empty($absen->$columnName)) {
                $isReupload = true;
                if (Storage::disk('s3')->exists($absen->$columnName)) {
                    Storage::disk('s3')->delete($absen->$columnName);
                    \Log::info('Deleted old photo from S3', [
                        'path' => $absen->$columnName,
                        'absenno' => $validated['absenno'],
                        'tipe' => $tipeAbsen
                    ]);
                }
            }
            
            // FIX: Generate S3 path menggunakan tanggal absen (bukan tanggal upload)
            $tanggalAbsen = $absen->absenmasuk ?? $now;
            $year = date('Y', strtotime($tanggalAbsen));
            $month = date('m', strtotime($tanggalAbsen));
            $day = date('d', strtotime($tanggalAbsen));
            
            // Format filename: TENAGAKERJAID_TIPE_YYYYMMDD_RANDOM.ext
            $filename = sprintf(
                '%s_%s_%s_%s.%s',
                $validated['tenagakerjaid'],
                $tipeAbsen,
                date('Ymd', strtotime($tanggalAbsen)),
                Str::random(6),
                $extension
            );
            
            // Path structure: absensi/YYYY/MM/DD/COMPANY/filename.jpg
            $path = sprintf(
                'absensi/%s/%s/%s/%s/%s',
                $year,
                $month,
                $day,
                $validated['companycode'],
                $filename
            );
            
            // Upload to S3
            Storage::disk('s3')->put($path, file_get_contents($file));
            $url = Storage::disk('s3')->url($path);
            
            // Update database (hanya update foto path dan timestamp)
            DB::table('absenlst')
                ->where('absenno', $validated['absenno'])
                ->where('tenagakerjaid', $validated['tenagakerjaid'])
                ->where('companycode', $validated['companycode'])
                ->update([
                    $columnName => $path,
                    'updatedat' => $now
                ]);
            
            DB::commit();
            
            return response()->json([
                'status' => 1,
                'description' => $isReupload 
                    ? 'Foto absen ' . $tipeAbsen . ' berhasil diupdate (menggantikan foto lama)'
                    : 'Foto absen ' . $tipeAbsen . ' berhasil diupload',
                'data' => [
                    'absenno' => $validated['absenno'],
                    'tenagakerjaid' => $validated['tenagakerjaid'],
                    'companycode' => $validated['companycode'],
                    'tipeabsen' => $tipeAbsen,
                    'path' => $path,
                    'url' => $url,
                    'filename' => $filename,
                    'is_reupload' => $isReupload,
                    'uploaded_at' => $now->toIso8601String()
                ]
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'description' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Rollback S3 upload jika sudah ter-upload
            if (isset($path) && Storage::disk('s3')->exists($path)) {
                Storage::disk('s3')->delete($path);
            }
            
            \Log::error('Error uploading foto absen ' . $tipeAbsen, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->except('foto')
            ]);
            
            return response()->json([
                'status' => 0,
                'description' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Helper method untuk cleanup foto lama (optional)
     * Bisa dipanggil via scheduled task untuk archive/delete
     */
    public function cleanupOldPhotos(int $monthsOld = 24)
    {
        try {
            $cutoffDate = now()->subMonths($monthsOld);
            $year = $cutoffDate->format('Y');
            $month = $cutoffDate->format('m');
            
            // Get all files older than cutoff
            $prefix = sprintf('absensi/%s/%s/', $year, $month);
            $files = Storage::disk('s3')->allFiles($prefix);
            
            \Log::info('Cleanup old photos', [
                'cutoff_date' => $cutoffDate->toDateString(),
                'prefix' => $prefix,
                'files_count' => count($files)
            ]);
            
            // Ini bisa dipindah ke Glacier atau dihapus
            // Untuk sekarang hanya log saja
            
            return response()->json([
                'status' => 1,
                'description' => 'Cleanup check completed',
                'data' => [
                    'cutoff_date' => $cutoffDate->toDateString(),
                    'files_found' => count($files)
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in cleanup process', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 0,
                'description' => 'Cleanup failed: ' . $e->getMessage()
            ], 500);
        }
    }
}