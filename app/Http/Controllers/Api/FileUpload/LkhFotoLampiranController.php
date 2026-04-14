<?php

namespace App\Http\Controllers\Api\FileUpload;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LkhFotoLampiranController extends Controller
{
    /**
     * Upload foto lampiran LKH
     * POST /api/fileupload/lkh-foto-lampiran
     * 
     * Struktur folder: lkh-lampiran/YYYY/MM/DD/COMPANY/filename.jpg
     * 
     * FIXED:
     * - Add extension fallback to 'jpg'
     * - Use LKH date for folder structure (not upload date)
     */
    public function upload(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validate([
                'foto' => 'required|image|mimes:jpeg,jpg,png|max:5120',
                'lkhno' => 'required|string|max:15',
                'companycode' => 'required|string|max:4',
                'blok' => 'nullable|string|max:3',
                'plot' => 'nullable|string|max:10',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'accuracy' => 'nullable|numeric'
            ]);
            
            $file = $request->file('foto');
            $now = now();
            
            // FIX: Get extension dengan fallback
            $extension = $file->getClientOriginalExtension();
            if (empty($extension)) {
                $extension = $file->guessExtension() ?? 'jpg';
            }
            
            // Get lkhhdrid dan tanggal LKH from lkhhdr
            $lkhhdr = DB::table('lkhhdr')
                ->where('lkhno', $validated['lkhno'])
                ->where('companycode', $validated['companycode'])
                ->first();
            
            if (!$lkhhdr) {
                throw new \Exception('LKH tidak ditemukan');
            }
            
            $lkhhdrid = $lkhhdr->id;
            
            // FIX: Generate S3 path menggunakan tanggal LKH (bukan tanggal upload)
            // Gunakan lkhdate dari tabel lkhhdr
            $tanggalLkh = $lkhhdr->lkhdate ?? $lkhhdr->createdat ?? $now;
            $year = date('Y', strtotime($tanggalLkh));
            $month = date('m', strtotime($tanggalLkh));
            $day = date('d', strtotime($tanggalLkh));
            
            // Build filename dengan blok-plot jika ada
            $blokPlot = '';
            if (!empty($validated['blok'])) {
                $blokPlot = '_' . $validated['blok'];
                if (!empty($validated['plot'])) {
                    $blokPlot .= '_' . $validated['plot'];
                }
            }
            
            // Format: LKHNO_BLOK_PLOT_YYYYMMDDHHmmss_RANDOM.ext
            $filename = sprintf(
                '%s%s_%s_%s.%s',
                $validated['lkhno'],
                $blokPlot,
                $now->format('YmdHis'),
                Str::random(6),
                $extension
            );
            
            // Path structure: lkh-lampiran/YYYY/MM/DD/COMPANY/filename.jpg
            $path = sprintf(
                'lkh-lampiran/%s/%s/%s/%s/%s',
                $year,
                $month,
                $day,
                $validated['companycode'],
                $filename
            );
            
            // Upload to S3
            Storage::disk('s3')->put($path, file_get_contents($file));
            $url = Storage::disk('s3')->url($path);
            
            // Insert to database
            $photoId = DB::table('lkhfotolampiran')->insertGetId([
                'lkhno' => $validated['lkhno'],
                'lkhhdrid' => $lkhhdrid,
                'companycode' => $validated['companycode'],
                'blok' => $validated['blok'] ?? null,
                'plot' => $validated['plot'] ?? null,
                'photopath' => $path,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'accuracy' => $validated['accuracy'] ?? null,
                'uploadfrom' => 'mobile',
                'createdat' => $now,
                'updatedat' => $now
            ]);
            
            DB::commit();
            
            return response()->json([
                'status' => 1,
                'description' => 'Foto lampiran LKH berhasil diupload',
                'data' => [
                    'id' => $photoId,
                    'lkhno' => $validated['lkhno'],
                    'lkhhdrid' => $lkhhdrid,
                    'companycode' => $validated['companycode'],
                    'path' => $path,
                    'url' => $url,
                    'filename' => $filename,
                    'blok' => $validated['blok'] ?? null,
                    'plot' => $validated['plot'] ?? null,
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                    'accuracy' => $validated['accuracy'] ?? null,
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
            
            \Log::error('Error uploading LKH foto lampiran', [
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
     * Get all photos for specific LKH
     * GET /api/fileupload/lkh-foto-lampiran/{lkhno}
     */
    public function getPhotos(Request $request, string $lkhno)
    {
        try {
            $companycode = $request->query('companycode');
            
            if (!$companycode) {
                return response()->json([
                    'status' => 0,
                    'description' => 'Company code is required'
                ], 400);
            }
            
            $photos = DB::table('lkhfotolampiran')
                ->where('lkhno', $lkhno)
                ->where('companycode', $companycode)
                ->orderBy('createdat', 'desc')
                ->get();
            
            // Generate URLs for all photos
            $photosWithUrls = $photos->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'lkhno' => $photo->lkhno,
                    'blok' => $photo->blok,
                    'plot' => $photo->plot,
                    'photopath' => $photo->photopath,
                    'url' => Storage::disk('s3')->url($photo->photopath),
                    'latitude' => $photo->latitude,
                    'longitude' => $photo->longitude,
                    'accuracy' => $photo->accuracy,
                    'uploadfrom' => $photo->uploadfrom,
                    'created_at' => $photo->createdat
                ];
            });
            
            return response()->json([
                'status' => 1,
                'description' => 'Success',
                'data' => [
                    'lkhno' => $lkhno,
                    'total' => $photos->count(),
                    'photos' => $photosWithUrls
                ]
            ], 200);
            
        } catch (\Exception $e) {
            \Log::error('Error getting LKH photos', [
                'error' => $e->getMessage(),
                'lkhno' => $lkhno
            ]);
            
            return response()->json([
                'status' => 0,
                'description' => 'Failed to get photos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete specific photo
     * DELETE /api/fileupload/lkh-foto-lampiran/{id}
     */
    public function delete(Request $request, int $id)
    {
        DB::beginTransaction();
        
        try {
            $companycode = $request->query('companycode');
            
            if (!$companycode) {
                return response()->json([
                    'status' => 0,
                    'description' => 'Company code is required'
                ], 400);
            }
            
            $photo = DB::table('lkhfotolampiran')
                ->where('id', $id)
                ->where('companycode', $companycode)
                ->first();
            
            if (!$photo) {
                throw new \Exception('Photo not found');
            }
            
            // Delete from S3
            if (Storage::disk('s3')->exists($photo->photopath)) {
                Storage::disk('s3')->delete($photo->photopath);
            }
            
            // Delete from database
            DB::table('lkhfotolampiran')
                ->where('id', $id)
                ->delete();
            
            DB::commit();
            
            return response()->json([
                'status' => 1,
                'description' => 'Photo deleted successfully',
                'data' => [
                    'id' => $id,
                    'lkhno' => $photo->lkhno
                ]
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error deleting LKH photo', [
                'error' => $e->getMessage(),
                'photo_id' => $id
            ]);
            
            return response()->json([
                'status' => 0,
                'description' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }
}