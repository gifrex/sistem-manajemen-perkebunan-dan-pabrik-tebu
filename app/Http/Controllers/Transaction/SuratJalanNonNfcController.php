<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SuratJalanNonNfcController extends Controller
{
    public function index(Request $request)
    {
        $companycode = Session::get('companycode');
        $perPage     = (int) $request->input('perPage', 10);
        $search      = $request->input('search');

        $query = DB::table('suratjalanpostemp as sjt')
            ->leftJoin('suratjalanpos as sj', function ($join) {
                $join->on('sj.companycode', '=', 'sjt.companycode')
                    ->on('sj.suratjalanno', '=', 'sjt.suratjalanno')
                    ->where('sj.is_nonnfc', '=', 1);
            })
            ->where('sjt.companycode', $companycode)
            ->select([
                'sjt.*',
                'sj.nonnfc_printed',
                DB::raw("DATE_FORMAT(sjt.created_at, '%d/%m/%Y %H:%i') as formatted_createdat"),
            ])
            ->orderBy('sjt.created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('sjt.suratjalanno', 'like', "%{$search}%")
                    ->orWhere('sjt.transactionnumber', 'like', "%{$search}%")
                    ->orWhere('sjt.nonnfc_createdby', 'like', "%{$search}%")
                    ->orWhere('sjt.plot', 'like', "%{$search}%")
                    ->orWhere('sjt.nomorpolisi', 'like', "%{$search}%");
            });
        }

        $list = $query->paginate($perPage);

        return view('transaction.surat-jalan-non-nfc.index', [
            'title'   => 'Input SJ Non-NFC',
            'navbar'  => 'Transaction',
            'nav'     => 'Input SJ Non-NFC',
            'list'    => $list,
            'perPage' => $perPage,
            'search'  => $search,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'suratjalanno'        => 'required|string|max:30',
            'mandorid'            => 'required|string|max:10',
            'plot'                => 'required|string|max:5',
            'varietas'            => 'required|string|max:10',
            'kategori'            => 'nullable|string|max:10',
            'umur'                => 'nullable|integer|min:0',
            'kodetebang'          => 'nullable|string|max:15',
            'langsir'             => 'nullable|integer|in:0,1',
            'tebusulit'           => 'nullable|integer|in:0,1',
            'kendaraankontraktor' => 'nullable|integer|in:0,1',
            'muatgl'              => 'nullable|integer|in:0,1',
            'nomorkendaraan'      => 'nullable|string|max:20',
            'nomorpolisi'         => 'required|string|max:20',
            'namasupir'           => 'required|string|max:25',
            'namakontraktor'      => 'nullable|string|max:25',
            'namasubkontraktor'   => 'nullable|string|max:25',
            'tanggaltebang'       => 'nullable|date',
            'tanggalangkut'       => 'nullable|date',
            'keterangan'          => 'nullable|string',
            'attachment'          => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        $companycode = Session::get('companycode');

        try {
            DB::beginTransaction();

            // Cek duplikat suratjalanno
            $exists = DB::table('suratjalanpostemp')
                ->where('companycode', $companycode)
                ->where('suratjalanno', $request->suratjalanno)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => "Nomor SJ {$request->suratjalanno} sudah ada di sistem.",
                ], 422);
            }

            // Validasi plot
            $plotExists = DB::table('masterlist')
                ->where('companycode', $companycode)
                ->where('plot', strtoupper(trim($request->plot)))
                ->exists();

            if (!$plotExists) {
                return response()->json([
                    'success' => false,
                    'message' => "Plot {$request->plot} tidak ditemukan di masterlist.",
                ], 422);
            }

            $transactionNumber = $this->generateTransactionNumber($companycode, now());
            $user              = Auth::user()->userid;

            $lampiranPath = $this->uploadLampiran($request, $companycode, $transactionNumber, now());

            DB::table('suratjalanpostemp')->insert([
                'transactionnumber'   => $transactionNumber,
                'companycode'         => $companycode,
                'suratjalanno'        => $request->suratjalanno,
                'mandorid'            => $request->mandorid,
                'plot'                => strtoupper(trim($request->plot)),
                'varietas'            => $request->varietas,
                'kategori'            => $request->kategori,
                'umur'                => $request->umur,
                'kodetebang'          => $request->kodetebang,
                'langsir'             => $request->langsir ?? 0,
                'tebusulit'           => $request->tebusulit ?? 0,
                'kendaraankontraktor' => $request->kendaraankontraktor ?? 0,
                'muatgl'              => $request->muatgl ?? 0,
                'nomorkendaraan'      => $request->nomorkendaraan,
                'nomorpolisi'         => $request->nomorpolisi,
                'namasupir'           => $request->namasupir,
                'namakontraktor'      => $request->namakontraktor,
                'namasubkontraktor'   => $request->namasubkontraktor,
                'tanggaltebang'       => $request->tanggaltebang,
                'tanggalangkut'       => $request->tanggalangkut,
                'approvalstatus'      => null,
                'nonnfc_createdby'    => $user,
                'created_at'          => now(),
                'attachment'          => $lampiranPath,
                'keterangan'          => $request->keterangan,
            ]);

            $approvalMaster = DB::table('approval')
                ->where('companycode', $companycode)
                ->where('category', 'Input SJ Non-NFC')
                ->first();

            if (!$approvalMaster) {
                throw new \Exception('Approval master "Input SJ Non-NFC" belum di-setup. Hubungi administrator.');
            }

            $approvalNo = $this->generateApprovalNo($companycode, now());

            DB::table('approvaltransaction')->insert([
                'approvalno'         => $approvalNo,
                'companycode'        => $companycode,
                'approvalcategoryid' => $approvalMaster->id,
                'transactionnumber'  => $transactionNumber,
                'jumlahapproval'     => $approvalMaster->jumlahapproval,
                'approval1idjabatan' => $approvalMaster->idjabatanapproval1,
                'approval2idjabatan' => $approvalMaster->idjabatanapproval2,
                'approval3idjabatan' => $approvalMaster->idjabatanapproval3,
                'approvalstatus'     => null,
                'inputby'            => $user,
                'createdat'          => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "SJ Non-NFC [{$transactionNumber}] berhasil dibuat. Menunggu approval.",
                'data'    => [
                    'transactionnumber' => $transactionNumber,
                    'approvalno'        => $approvalNo,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SuratJalanNonNfc store failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $companycode = Session::get('companycode');

        $record = DB::table('suratjalanpostemp as sjt')
            ->leftJoin('suratjalanpos as sj', function ($join) {
                $join->on('sj.companycode', '=', 'sjt.companycode')
                    ->on('sj.suratjalanno', '=', 'sjt.suratjalanno')
                    ->where('sj.is_nonnfc', '=', 1);
            })
            ->where('sjt.id', $id)
            ->where('sjt.companycode', $companycode)
            ->select([
                'sjt.*',
                'sj.nonnfc_printed',
                'sj.tanggalcetakpossecurity',
                DB::raw("DATE_FORMAT(sjt.created_at, '%d/%m/%Y %H:%i') as formatted_createdat"),
                DB::raw("DATE_FORMAT(sjt.approved_at, '%d/%m/%Y %H:%i') as formatted_approvedat"),
            ])
            ->first();

        abort_if(!$record, 404);

        $lampiranUrl = $record->attachment
            ? Storage::disk('s3')->temporaryUrl($record->attachment, now()->addMinutes(30))
            : null;

        return view('transaction.surat-jalan-non-nfc.show', [
            'title'      => 'Detail SJ Non-NFC',
            'navbar'     => 'Transaction',
            'nav'        => 'Input SJ Non-NFC',
            'record'     => $record,
            'lampiranUrl'=> $lampiranUrl,
        ]);
    }

    public function markPrinted(Request $request)
    {
        $request->validate([
            'suratjalanno' => 'required|string',
        ]);

        $companycode = Session::get('companycode');

        $affected = DB::table('suratjalanpos')
            ->where('companycode', $companycode)
            ->where('suratjalanno', $request->suratjalanno)
            ->where('is_nonnfc', 1)
            ->where('nonnfc_printed', 0)
            ->update([
                'nonnfc_printed'            => 1,
                'tanggalcetakpossecurity'   => now(),
            ]);

        if (!$affected) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan atau belum diapprove'], 422);
        }

        return response()->json(['success' => true]);
    }

    public function getFormData()
    {
        $companycode = Session::get('companycode');

        $mandors = DB::table('user')
            ->where('companycode', $companycode)
            ->where('idjabatan', 5)
            ->where('isactive', 1)
            ->select(['userid as mandorid', 'name'])
            ->orderBy('userid')
            ->get();

        $kontraktors = DB::table('kontraktor')
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->select(['id', 'namakontraktor'])
            ->orderBy('id')
            ->get();

        $subkontraktors = DB::table('subkontraktor')
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->select(['id', 'namasubkontraktor'])
            ->orderBy('id')
            ->get();

        return response()->json([
            'success'       => true,
            'mandors'       => $mandors,
            'kontraktors'   => $kontraktors,
            'subkontraktors'=> $subkontraktors,
        ]);
    }

    public function getPlotData(Request $request)
    {
        $companycode = Session::get('companycode');
        $plot        = strtoupper(trim($request->input('plot', '')));

        if (!$plot) {
            return response()->json(['exists' => false, 'varietas' => null]);
        }

        $masterlist = DB::table('masterlist')
            ->where('companycode', $companycode)
            ->where('plot', $plot)
            ->select(['activebatchno'])
            ->first();

        if (!$masterlist || !$masterlist->activebatchno) {
            return response()->json(['exists' => false, 'varietas' => null]);
        }

        $batch = DB::table('batch')
            ->where('companycode', $companycode)
            ->where('batchno', $masterlist->activebatchno)
            ->select(['kodevarietas'])
            ->first();

        return response()->json([
            'exists'   => true,
            'varietas' => $batch->kodevarietas ?? null,
        ]);
    }

    public function getAttachment($id)
    {
        $companycode = Session::get('companycode');

        $record = DB::table('suratjalanpostemp')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->value('attachment');

        if (!$record) {
            abort(404);
        }

        $url = Storage::disk('s3')->temporaryUrl($record, now()->addMinutes(30));

        return response()->json(['url' => $url]);
    }

    private function generateTransactionNumber(string $companycode, $date): string
    {
        $dateStr  = $date->format('ymd');
        $sequence = DB::table('suratjalanpostemp')
            ->where('companycode', $companycode)
            ->whereDate('created_at', $date)
            ->count() + 1;

        return 'SNF' . $dateStr . str_pad($sequence, 2, '0', STR_PAD_LEFT);
    }

    private function generateApprovalNo(string $companycode, $date): string
    {
        $dateStr  = $date->format('ymd');
        $sequence = DB::table('approvaltransaction')
            ->where('companycode', $companycode)
            ->whereDate('createdat', $date)
            ->count() + 1;

        return 'APV' . $dateStr . str_pad($sequence, 2, '0', STR_PAD_LEFT);
    }

    private function uploadLampiran(Request $request, string $companycode, string $transactionNumber, $now): ?string
    {
        if (!$request->hasFile('attachment')) {
            return null;
        }

        $file      = $request->file('attachment');
        $ext       = $file->getClientOriginalExtension() ?: ($file->guessExtension() ?? 'jpg');
        $safeTrxNo = str_replace('/', '-', $transactionNumber);
        $filename  = sprintf('%s_%s_%s.%s', $companycode, $safeTrxNo, Str::random(6), $ext);
        $path      = sprintf(
            'surat-jalan-non-nfc/%s/%s/%s/%s',
            $now->format('Y'),
            $now->format('m'),
            $companycode,
            $filename
        );

        Storage::disk('s3')->put($path, file_get_contents($file));

        return $path;
    }
}
