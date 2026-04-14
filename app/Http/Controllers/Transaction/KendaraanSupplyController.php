<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

/**
 * KendaraanSupplyController
 *
 * Modul Mandor Kendaraan — pencatatan surat jalan kendaraan supply.
 * Kendaraan supply = angkut/antar material, obat, BBM ke plot.
 * Bukan kendaraan kerja di plot (itu dari LKH).
 *
 * Status flow SJS (hanya berlaku untuk sumberbbm = STOCK):
 *   DRAFT      → bisa diedit / dihapus
 *   SUBMITTED  → terkunci, masuk antrian Order BBM Admin Kendaraan
 *
 * Kendaraan sumberbbm = SPBU tidak perlu submit (hanya catatan operasional).
 */
class KendaraanSupplyController extends Controller
{
    // -----------------------------------------------------------------------
    // INDEX
    // -----------------------------------------------------------------------
    public function index(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            $search      = $request->input('search');
            $filterDate  = $request->input('filter_date', now()->format('Y-m-d'));
            $showAllDate = $request->boolean('show_all_date');

            $query = DB::table('kendaraansupply as ks')
                ->join('kendaraan as k', fn($j) => $j
                    ->on('ks.nokendaraan', '=', 'k.nokendaraan')
                    ->where('k.companycode', $companycode)
                    ->where('k.isactive', 1)
                )
                ->leftJoin('tenagakerja as tk_op', fn($j) => $j
                    ->on('ks.operatorid', '=', 'tk_op.tenagakerjaid')
                    ->where('tk_op.companycode', $companycode)
                )
                ->leftJoin('tenagakerja as tk_hp', fn($j) => $j
                    ->on('ks.helperid', '=', 'tk_hp.tenagakerjaid')
                    ->where('tk_hp.companycode', $companycode)
                )
                ->leftJoin('user as u', 'ks.mandorkendaraanid', '=', 'u.userid')
                ->where('ks.companycode', $companycode)
                ->select([
                    'ks.*',
                    'k.jenis',
                    'tk_op.nama as operator_nama',
                    'tk_hp.nama as helper_nama',
                    'u.name as mandor_nama',
                ]);

            if ($search) {
                $query->where(fn($q) => $q
                    ->where('ks.sjsno',       'like', "%{$search}%")
                    ->orWhere('ks.nokendaraan', 'like', "%{$search}%")
                    ->orWhere('tk_op.nama',     'like', "%{$search}%")
                    ->orWhere('ks.tujuan',      'like', "%{$search}%")
                );
            }

            if ($filterDate && !$showAllDate) {
                $query->whereDate('ks.sjsdate', $filterDate);
            }

            $supplyData = $query
                ->orderBy('ks.sjsdate', 'desc')
                ->orderBy('ks.sjsno',   'desc')
                ->paginate(20);

            // Stats
            $base = DB::table('kendaraansupply')->where('companycode', $companycode);
            if ($filterDate && !$showAllDate) {
                $base->whereDate('sjsdate', $filterDate);
            }

            $stats = [
                'total'     => (clone $base)->count(),
                'stock'     => (clone $base)->where('sumberbbm', 'STOCK')->count(),
                'spbu'      => (clone $base)->where('sumberbbm', 'SPBU')->count(),
                'submitted' => (clone $base)->where('status', 'SUBMITTED')->count(),
                'draft'     => (clone $base)->where('status', 'DRAFT')->count(),
            ];

            return view('transaction.kendaraan-supply.index', compact(
                'supplyData', 'stats', 'search', 'filterDate', 'showAllDate'
            ))->with([
                'title'  => 'Surat Jalan Kendaraan Supply',
                'navbar' => 'Kendaraan Supply',
                'nav'    => 'Supply',
            ]);
        } catch (\Exception $e) {
            Log::error('KendaraanSupplyController@index', ['msg' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // -----------------------------------------------------------------------
    // STORE
    // -----------------------------------------------------------------------
    public function store(Request $request)
    {
        try {
            $request->validate([
                'sjsdate'             => 'required|date',
                'nokendaraan'         => 'required|string',
                'operatorid'          => 'required|string',
                'helperid'            => 'nullable|string',
                'tujuan'              => 'required|string|max:255',
                'keteranganaktivitas' => 'required|string|max:255',
                'jumlahrit'           => 'required|integer|min:1',
                'jammulai'            => 'nullable|date_format:H:i',
                'jamselesai'          => 'nullable|date_format:H:i',
                'sumberbbm'           => 'required|in:STOCK,SPBU',
                'catatan'             => 'nullable|string',
            ]);

            $companycode = Session::get('companycode');
            $user        = auth()->user();

            DB::beginTransaction();

            $sjsno = $this->generateSjsNo($companycode, $request->sjsdate);

            $kendaraanid = DB::table('kendaraan')
                ->where('companycode', $companycode)
                ->where('nokendaraan', $request->nokendaraan)
                ->where('isactive', 1)
                ->value('id');

            DB::table('kendaraansupply')->insert([
                'companycode'          => $companycode,
                'sjsno'                => $sjsno,
                'sjsdate'              => $request->sjsdate,
                'mandorkendaraanid'    => $user->userid,
                'nokendaraan'          => $request->nokendaraan,
                'kendaraanid'          => $kendaraanid,
                'operatorid'           => $request->operatorid,
                'helperid'             => $request->helperid,
                'tujuan'               => $request->tujuan,
                'keteranganaktivitas'  => $request->keteranganaktivitas,
                'jumlahrit'            => $request->jumlahrit,
                'jammulai'             => $request->jammulai,
                'jamselesai'           => $request->jamselesai,
                'sumberbbm'            => $request->sumberbbm,
                'status'               => 'DRAFT',   // selalu mulai DRAFT
                'catatan'              => $request->catatan,
                'inputby'              => $user->name,
                'createdat'            => now(),
            ]);

            DB::commit();

            Log::info('SJS created', [
                'sjsno'      => $sjsno,
                'nokendaraan'=> $request->nokendaraan,
                'sumberbbm'  => $request->sumberbbm,
                'user'       => $user->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Surat jalan {$sjsno} berhasil dibuat",
                'sjsno'   => $sjsno,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi: ' . implode(', ', $e->validator->errors()->all())
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KendaraanSupplyController@store', ['msg' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------------------
    // UPDATE
    // -----------------------------------------------------------------------
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'nokendaraan'         => 'required|string',
                'operatorid'          => 'required|string',
                'helperid'            => 'nullable|string',
                'tujuan'              => 'required|string|max:255',
                'keteranganaktivitas' => 'required|string|max:255',
                'jumlahrit'           => 'required|integer|min:1',
                'jammulai'            => 'nullable|date_format:H:i',
                'jamselesai'          => 'nullable|date_format:H:i',
                'sumberbbm'           => 'required|in:STOCK,SPBU',
                'catatan'             => 'nullable|string',
            ]);

            $companycode = Session::get('companycode');
            $user        = auth()->user();

            $sjs = DB::table('kendaraansupply')
                ->where('id', $id)
                ->where('companycode', $companycode)
                ->first();

            if (!$sjs) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan']);
            }

            // Tidak bisa edit jika sudah SUBMITTED
            if ($sjs->status === 'SUBMITTED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat diedit, SJS sudah disubmit ke antrian Order BBM'
                ]);
            }

            $kendaraanid = DB::table('kendaraan')
                ->where('companycode', $companycode)
                ->where('nokendaraan', $request->nokendaraan)
                ->where('isactive', 1)
                ->value('id');

            DB::table('kendaraansupply')
                ->where('id', $id)
                ->where('companycode', $companycode)
                ->update([
                    'nokendaraan'         => $request->nokendaraan,
                    'kendaraanid'         => $kendaraanid,
                    'operatorid'          => $request->operatorid,
                    'helperid'            => $request->helperid,
                    'tujuan'              => $request->tujuan,
                    'keteranganaktivitas' => $request->keteranganaktivitas,
                    'jumlahrit'           => $request->jumlahrit,
                    'jammulai'            => $request->jammulai,
                    'jamselesai'          => $request->jamselesai,
                    'sumberbbm'           => $request->sumberbbm,
                    'catatan'             => $request->catatan,
                    'updateby'            => $user->name,
                    'updatedat'           => now(),
                ]);

            return response()->json(['success' => true, 'message' => 'Data berhasil diupdate']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi: ' . implode(', ', $e->validator->errors()->all())
            ]);
        } catch (\Exception $e) {
            Log::error('KendaraanSupplyController@update', ['msg' => $e->getMessage(), 'id' => $id]);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------------------
    // DESTROY
    // -----------------------------------------------------------------------
    public function destroy($id)
    {
        try {
            $companycode = Session::get('companycode');

            $sjs = DB::table('kendaraansupply')
                ->where('id', $id)
                ->where('companycode', $companycode)
                ->first();

            if (!$sjs) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan']);
            }

            if ($sjs->status === 'SUBMITTED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat dihapus, SJS sudah disubmit ke antrian Order BBM'
                ]);
            }

            DB::table('kendaraansupply')
                ->where('id', $id)
                ->where('companycode', $companycode)
                ->delete();

            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            Log::error('KendaraanSupplyController@destroy', ['msg' => $e->getMessage(), 'id' => $id]);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------------------
    // SUBMIT — DRAFT → SUBMITTED (terkunci, masuk antrian Order BBM)
    // -----------------------------------------------------------------------
    public function submit(Request $request, $id)
    {
        try {
            $companycode = Session::get('companycode');
            $user        = auth()->user();

            $sjs = DB::table('kendaraansupply')
                ->where('id', $id)
                ->where('companycode', $companycode)
                ->first();

            if (!$sjs) {
                return response()->json(['success' => false, 'message' => 'SJS tidak ditemukan']);
            }

            if ($sjs->status !== 'DRAFT') {
                return response()->json([
                    'success' => false,
                    'message' => 'SJS sudah disubmit sebelumnya'
                ]);
            }

            // Hanya STOCK yang perlu submit (SPBU = hanya catatan, tidak masuk Order BBM)
            if ($sjs->sumberbbm !== 'STOCK') {
                return response()->json([
                    'success' => false,
                    'message' => 'SJS dengan sumber BBM SPBU tidak perlu disubmit'
                ]);
            }

            DB::table('kendaraansupply')
                ->where('id', $id)
                ->where('companycode', $companycode)
                ->update([
                    'status'      => 'SUBMITTED',
                    'submittedby' => $user->name,
                    'submittedat' => now(),
                    'updateby'    => $user->name,
                    'updatedat'   => now(),
                ]);

            Log::info('SJS submitted', [
                'id'          => $id,
                'sjsno'       => $sjs->sjsno,
                'nokendaraan' => $sjs->nokendaraan,
                'submittedby' => $user->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => "SJS {$sjs->sjsno} berhasil disubmit dan masuk antrian Order BBM",
            ]);
        } catch (\Exception $e) {
            Log::error('KendaraanSupplyController@submit', ['msg' => $e->getMessage(), 'id' => $id]);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------------------
    // API: Dropdown kendaraan
    // -----------------------------------------------------------------------
    public function getKendaraanList()
    {
        $companycode = Session::get('companycode');

        $data = DB::table('kendaraan as k')
            ->leftJoin('tenagakerja as tk', function ($j) use ($companycode) {
                $j->on('k.idtenagakerja', '=', 'tk.tenagakerjaid')
                ->where('tk.companycode', $companycode);
            })
            ->where('k.companycode', $companycode)
            ->where('k.isactive', 1)
            ->select('k.id', 'k.nokendaraan', 'k.jenis', 'k.idtenagakerja', 'tk.nama as operator_nama')
            ->orderBy('k.nokendaraan')
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    // -----------------------------------------------------------------------
    // API: Dropdown operator/tenaga kerja
    // -----------------------------------------------------------------------
    public function getOperatorList()
    {
        $companycode = Session::get('companycode');

        $data = DB::table('tenagakerja')
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->select('tenagakerjaid', 'nama', 'nik')
            ->orderBy('nama')
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    // -----------------------------------------------------------------------
    // API: SJS pending untuk Order BBM (belum ada order, sudah SUBMITTED)
    // -----------------------------------------------------------------------
    public function getPendingForOrder()
    {
        $companycode = Session::get('companycode');

        $data = DB::table('kendaraansupply as ks')
            ->leftJoin('orderbbmhdr as oh', fn($j) => $j
                ->on('ks.sjsno', '=', 'oh.sourceno')
                ->where('oh.companycode', $companycode)
                ->where('oh.sourcetype', 'SJS')
            )
            ->leftJoin('kendaraan as k', fn($j) => $j
                ->on('ks.nokendaraan', '=', 'k.nokendaraan')
                ->where('k.companycode', $companycode)
                ->where('k.isactive', 1)
            )
            ->leftJoin('tenagakerja as tk', fn($j) => $j
                ->on('ks.operatorid', '=', 'tk.tenagakerjaid')
                ->where('tk.companycode', $companycode)
            )
            ->where('ks.companycode', $companycode)
            ->where('ks.sumberbbm', 'STOCK')
            ->where('ks.status', 'SUBMITTED')
            ->whereNull('oh.id')
            ->select('ks.*', 'k.jenis', 'tk.nama as operator_nama')
            ->orderBy('ks.sjsdate', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    // -----------------------------------------------------------------------
    // HELPER: generate nomor SJS
    // -----------------------------------------------------------------------
    private function generateSjsNo($companycode, $date): string
    {
        $dateStr = Carbon::parse($date)->format('ymd');
        $prefix  = 'SJS' . $dateStr;

        $last = DB::table('kendaraansupply')
            ->where('companycode', $companycode)
            ->where('sjsno', 'like', $prefix . '%')
            ->orderByDesc('sjsno')
            ->value('sjsno');

        $next = $last ? ((int) substr($last, -4) + 1) : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}