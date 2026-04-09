<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuratTeguranController extends Controller
{
    public function index(Request $request)
    {
        $companycode   = session('companycode');
        $search        = $request->get('search');
        $perPage       = $request->get('perPage', 10);
        $filterStatus  = $request->get('status');

        $query = DB::table('suratteguran as st')
            ->leftJoin('company as c', 'c.companycode', '=', 'st.companycode')
            ->select('st.*', 'c.name as fromcompanyname')
            ->where('st.targetcompany', $companycode)
            ->whereIn('st.status', ['sent', 'read'])
            ->orderBy('st.createdat', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('st.stgno', 'like', "%{$search}%")
                  ->orWhere('st.perihal', 'like', "%{$search}%");
            });
        }

        if ($filterStatus) {
            $query->where('st.status', $filterStatus);
        }

        $data = $query->paginate($perPage);

        $unreadCount = DB::table('suratteguran')
            ->where('targetcompany', $companycode)
            ->where('status', 'sent')
            ->count();

        return view('dashboard.surat-teguran.index', [
            'title'       => 'Surat Teguran',
            'navbar'      => 'Dashboard',
            'nav'         => 'Surat Teguran',
            'data'        => $data,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function show($id)
    {
        $companycode = session('companycode');

        $surat = DB::table('suratteguran')
            ->where('id', $id)
            ->where('targetcompany', $companycode)
            ->first();

        abort_if(!$surat, 404);

        $fromCompany = DB::table('company')
            ->where('companycode', $surat->companycode)
            ->first();

        $lampiranUrl = $surat->lampiran
            ? Storage::disk('s3')->temporaryUrl($surat->lampiran, now()->addMinutes(30))
            : null;

        return view('dashboard.surat-teguran.show', [
            'title'       => 'Detail Surat Teguran',
            'navbar'      => 'Dashboard',
            'nav'         => 'Surat Teguran',
            'surat'       => $surat,
            'fromCompany' => $fromCompany,
            'lampiranUrl' => $lampiranUrl,
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $companycode = session('companycode');

        $surat = DB::table('suratteguran')
            ->where('id', $id)
            ->where('targetcompany', $companycode)
            ->where('status', 'sent')
            ->first();

        if (!$surat) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        DB::table('suratteguran')->where('id', $id)->update([
            'status'    => 'read',
            'readat'    => now(),
            'readby'    => Auth::user()->userid,
            'updatedat' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Surat teguran ditandai sudah dibaca.']);
    }
}
