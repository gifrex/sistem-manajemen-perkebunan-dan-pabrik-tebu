<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * AbsenReportController
 * 
 * View-only report untuk absen
 * Features: List absen, detail dengan foto gallery dari S3
 */
class AbsenReportController extends Controller
{
    /**
     * Index - List absen dengan filter
     * GET /report/absen
     */
    public function index(Request $request)
    {
        $companycode = Session::get('companycode');
        
        // Get filter parameters
        $filterDateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $filterDateTo = $request->input('date_to', now()->format('Y-m-d'));
        $filterMandor = $request->input('mandor');
        $filterStatus = $request->input('status'); // null, '1' (approved), '0' (rejected)
        
        // Build query
        $query = DB::table('absenhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->where('h.companycode', $companycode);
        
        // Apply filters
        if ($filterDateFrom) {
            $query->whereDate('h.uploaddate', '>=', $filterDateFrom);
        }
        
        if ($filterDateTo) {
            $query->whereDate('h.uploaddate', '<=', $filterDateTo);
        }
        
        if ($filterMandor) {
            $query->where('h.mandorid', $filterMandor);
        }
        
        if ($filterStatus !== null && $filterStatus !== '') {
            $query->where('h.approvalstatus', $filterStatus);
        }
        
        // Get data
        $absenList = $query->select([
                'h.absenno',
                'h.mandorid',
                'h.totalpekerja',
                'h.uploaddate',
                'h.approvalstatus',
                'h.approvaldate',
                'h.approvaluserid',
                'm.name as mandor_nama'
            ])
            ->orderBy('h.uploaddate', 'desc')
            ->paginate(20);
        
        // Get mandor list for filter dropdown
        $mandorList = DB::table('absenhdr as h')
            ->join('user as m', 'h.mandorid', '=', 'm.userid')
            ->where('h.companycode', $companycode)
            ->select('h.mandorid', 'm.name as mandor_nama')
            ->distinct()
            ->orderBy('m.name')
            ->get();
        
        return view('report.absen.index', [
            'title' => 'Laporan Absen',
            'navbar' => 'Report',
            'nav' => 'Absen',
            'absenList' => $absenList,
            'mandorList' => $mandorList,
            'filters' => [
                'date_from' => $filterDateFrom,
                'date_to' => $filterDateTo,
                'mandor' => $filterMandor,
                'status' => $filterStatus
            ]
        ]);
    }
    
    /**
     * Show - Detail absen dengan foto gallery
     * GET /report/absen/{absenno}
     */
    public function show(string $absenno)
    {
        $companycode = Session::get('companycode');
        
        // Get absen header
        $absen = DB::table('absenhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('user as au', 'h.approvaluserid', '=', 'au.userid')
            ->where('h.companycode', $companycode)
            ->where('h.absenno', $absenno)
            ->select([
                'h.*',
                'm.name as mandor_nama',
                'au.name as approval_user_nama'
            ])
            ->first();
        
        if (!$absen) {
            return back()->with('error', 'Data absen tidak ditemukan');
        }
        
        // Get absen detail (workers)
        $workers = DB::table('absenlst as l')
            ->leftJoin('tenagakerja as tk', function($join) use ($companycode) {
                $join->on('l.tenagakerjaid', '=', 'tk.tenagakerjaid')
                    ->where('tk.companycode', '=', $companycode);
            })
            ->leftJoin('jenistenagakerja as jtk', 'tk.jenistenagakerja', '=', 'jtk.idjenistenagakerja')
            ->where('l.companycode', $companycode)
            ->where('l.absenno', $absenno)
            ->select([
                'l.*',
                'tk.nama as worker_name',
                'tk.nik as worker_nik',
                'tk.gender as worker_gender',
                'jtk.nama as jenis_tenagakerja_nama'
            ])
            ->orderBy('l.id')
            ->get();
        
        // Generate S3 URLs for photos
        $workers = $workers->map(function($worker) {
            $worker->fotoabsenmasuk_url = $worker->fotoabsenmasuk 
                ? Storage::disk('s3')->temporaryUrl($worker->fotoabsenmasuk, now()->addHours(1))
                : null;
            
            $worker->fotoabsenpulang_url = $worker->fotoabsenpulang 
                ? Storage::disk('s3')->temporaryUrl($worker->fotoabsenpulang, now()->addHours(1))
                : null;
            
            return $worker;
        });
        
        // Count statistics
        $stats = [
            'total_workers' => $workers->count(),
            'with_foto_masuk' => $workers->whereNotNull('fotoabsenmasuk')->count(),
            'with_foto_pulang' => $workers->whereNotNull('fotoabsenpulang')->count(),
            'foto_approved' => $workers->where('fotomasukapprovalstatus', '1')->count(),
            'foto_rejected' => $workers->where('fotomasukapprovalstatus', '0')->count(),
            'foto_pending' => $workers->whereNull('fotomasukapprovalstatus')->whereNotNull('fotoabsenmasuk')->count()
        ];
        
        return view('report.absen.show', [
            'title' => 'Detail Absen - ' . $absenno,
            'navbar' => 'Report',
            'nav' => 'Absen',
            'absen' => $absen,
            'workers' => $workers,
            'stats' => $stats
        ]);
    }
    
    /**
     * Get foto gallery data (AJAX)
     * GET /report/absen/{absenno}/gallery
     */
    public function gallery(string $absenno)
    {
        $companycode = Session::get('companycode');
        
        $photos = DB::table('absenlst as l')
            ->leftJoin('tenagakerja as tk', function($join) use ($companycode) {
                $join->on('l.tenagakerjaid', '=', 'tk.tenagakerjaid')
                    ->where('tk.companycode', '=', $companycode);
            })
            ->where('l.companycode', $companycode)
            ->where('l.absenno', $absenno)
            ->whereNotNull('l.fotoabsenmasuk') // Only workers with foto masuk
            ->select([
                'l.tenagakerjaid',
                'l.fotoabsenmasuk',
                'l.fotoabsenpulang',
                'l.fotomasukapprovalstatus',
                'l.fotomasukapprovalreason',
                'tk.nama as worker_name',
                'tk.nik as worker_nik'
            ])
            ->get();
        
        // Generate S3 URLs
        $photos = $photos->map(function($photo) {
            return [
                'tenagakerjaid' => $photo->tenagakerjaid,
                'worker_name' => $photo->worker_name,
                'worker_nik' => $photo->worker_nik,
                'foto_masuk_url' => $photo->fotoabsenmasuk 
                    ? Storage::disk('s3')->temporaryUrl($photo->fotoabsenmasuk, now()->addHours(1)) 
                    : null,
                'foto_pulang_url' => $photo->fotoabsenpulang 
                    ? Storage::disk('s3')->temporaryUrl($photo->fotoabsenpulang, now()->addHours(1)) 
                    : null,
                'approval_status' => $photo->fotomasukapprovalstatus,
                'rejection_reason' => $photo->fotomasukapprovalreason
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $photos
        ]);
    }
    
    /**
     * Export to Excel (optional - future enhancement)
     */
    public function exportExcel(Request $request)
    {
        // TODO: Implement Excel export if needed
        return back()->with('info', 'Fitur export Excel akan segera tersedia');
    }
}