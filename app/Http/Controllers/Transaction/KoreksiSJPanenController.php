<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class KoreksiSJPanenController extends Controller
{
    // Field → label display
    private const FIELD_LABELS = [
        'plot'               => 'Plot',
        'varietas'           => 'Varietas',
        'kodetebang'         => 'Kode Tebang',
        'langsir'            => 'Langsir',
        'tebusulit'          => 'Tebu Sulit',
        'kendaraankontraktor'=> 'Kendaraan Kontraktor',
        'muatgl'             => 'Muat GL',
        'nomorkendaraan'     => 'Nomor Kendaraan',
        'nomorpolisi'        => 'Nomor Polisi',
        'namasupir'          => 'Nama Supir',
        'namakontraktor'     => 'Kontraktor',
        'namasubkontraktor'  => 'Sub Kontraktor',
    ];

    public function index(Request $request)
    {
        $companycode = Session::get('companycode');
        $perPage     = (int) $request->input('perPage', 10);
        $search      = $request->input('search');

        $query = DB::table('koreksisuratjalanpanen as k')
            ->leftJoin('approvaltransaction as at', function ($join) use ($companycode) {
                $join->on('k.transactionnumber', '=', 'at.transactionnumber')
                    ->where('at.companycode', '=', $companycode);
            })
            ->where('k.companycode', $companycode)
            ->select([
                'k.*',
                'at.approvalno',
                'at.approvalstatus',
                'at.approval1flag',
                'at.approval2flag',
                'at.approval3flag',
                'at.jumlahapproval',
                DB::raw("DATE_FORMAT(k.createdat, '%d/%m/%Y %H:%i') as formatted_createdat"),
            ])
            ->orderBy('k.createdat', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('k.suratjalanno', 'like', "%{$search}%")
                    ->orWhere('k.transactionnumber', 'like', "%{$search}%")
                    ->orWhere('k.inputby', 'like', "%{$search}%");
            });
        }

        $koreksiList = $query->paginate($perPage);

        // Decode perubahan JSON untuk tiap row
        $koreksiList->getCollection()->transform(function ($item) {
            $item->perubahan_decoded = $item->perubahan ? json_decode($item->perubahan, true) : [];
            $item->field_labels      = self::FIELD_LABELS;
            return $item;
        });

        return view('transaction.koreksi-sj-panen.index', [
            'title'       => 'Koreksi SJ Panen',
            'navbar'      => 'Transaction',
            'nav'         => 'Koreksi SJ Panen',
            'koreksiList' => $koreksiList,
            'perPage'     => $perPage,
            'search'      => $search,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'suratjalanno'        => 'required|string|max:30',
            'alasan'              => 'nullable|string|max:500',
            'plot'                => 'nullable|string|max:5',
            'varietas'            => 'nullable|string|max:10',
            'kodetebang'          => 'nullable|string|max:15',
            'langsir'             => 'nullable|integer',
            'tebusulit'           => 'nullable|integer',
            'kendaraankontraktor' => 'nullable|integer',
            'muatgl'              => 'nullable|integer',
            'nomorkendaraan'      => 'nullable|string|max:11',
            'nomorpolisi'         => 'nullable|string|max:11',
            'namasupir'           => 'nullable|string|max:25',
            'namakontraktor'      => 'nullable|string|max:10',
            'namasubkontraktor'   => 'nullable|string|max:10',
        ]);

        $companycode = Session::get('companycode');

        try {
            DB::beginTransaction();

            $sj = DB::table('suratjalanpos')
                ->where('companycode', $companycode)
                ->where('suratjalanno', $request->suratjalanno)
                ->first();

            if (!$sj) {
                return response()->json(['success' => false, 'message' => 'Surat Jalan tidak ditemukan'], 422);
            }

            // Build perubahan — hanya field yang benar-benar berubah
            $editableFields = array_keys(self::FIELD_LABELS);
            $perubahan      = [];

            foreach ($editableFields as $field) {
                if (!$request->has($field)) {
                    continue;
                }

                $newVal = $request->input($field);
                $oldVal = $sj->$field;

                // Normalkan null & string kosong supaya perbandingan konsisten
                $newNorm = ($newVal === null || $newVal === '') ? null : (string) $newVal;
                $oldNorm = ($oldVal === null || $oldVal === '') ? null : (string) $oldVal;

                if ($newNorm !== $oldNorm) {
                    $perubahan[$field] = ['lama' => $oldVal, 'baru' => $newVal];
                }
            }

            if (empty($perubahan)) {
                return response()->json(['success' => false, 'message' => 'Tidak ada perubahan yang terdeteksi'], 422);
            }

            // Validasi plot baru ke masterlist
            if (isset($perubahan['plot'])) {
                $plotBaru = strtoupper(trim($perubahan['plot']['baru']));
                $plotExists = DB::table('masterlist')
                    ->where('companycode', $companycode)
                    ->where('plot', $plotBaru)
                    ->exists();

                if (!$plotExists) {
                    return response()->json(['success' => false, 'message' => "Plot {$plotBaru} tidak ditemukan di masterlist"], 422);
                }

                $perubahan['plot']['baru'] = $plotBaru;
            }

            $transactionNumber = $this->generateTransactionNumber($companycode, now());

            DB::table('koreksisuratjalanpanen')->insert([
                'transactionnumber' => $transactionNumber,
                'companycode'       => $companycode,
                'suratjalanno'      => $request->suratjalanno,
                'perubahan'         => json_encode($perubahan),
                'alasan'            => $request->alasan,
                'inputby'           => Auth::user()->userid,
                'createdat'         => now(),
            ]);

            $approvalMaster = DB::table('approval')
                ->where('companycode', $companycode)
                ->where('category', 'Approval Koreksi Surat Jalan Panen')
                ->first();

            if (!$approvalMaster) {
                throw new \Exception('Approval master "Approval Koreksi Surat Jalan Panen" belum di-setup. Hubungi administrator.');
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
                'inputby'            => Auth::user()->userid,
                'createdat'          => now(),
            ]);

            DB::commit();

            $fieldCount = count($perubahan);
            return response()->json([
                'success' => true,
                'message' => "Koreksi [{$transactionNumber}] berhasil dibuat ({$fieldCount} field). Menunggu approval.",
                'data'    => [
                    'transactionnumber' => $transactionNumber,
                    'approvalno'        => $approvalNo,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KoreksiSJPanen store failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal membuat koreksi: ' . $e->getMessage()], 500);
        }
    }

    public function getSuratJalanDetail(Request $request)
    {
        $companycode  = Session::get('companycode');
        $suratjalanno = trim($request->input('suratjalanno', ''));

        if (!$suratjalanno) {
            return response()->json(['success' => false, 'message' => 'Nomor SJ diperlukan'], 422);
        }

        $sj = DB::table('suratjalanpos')
            ->where('companycode', $companycode)
            ->where('suratjalanno', $suratjalanno)
            ->select([
                'suratjalanno', 'mandorid', 'plot', 'varietas', 'kodetebang',
                'langsir', 'tebusulit', 'kendaraankontraktor', 'muatgl',
                'nomorkendaraan', 'nomorpolisi', 'namasupir',
                'namakontraktor', 'namasubkontraktor',
                'tanggaltebang', 'koreksi',
            ])
            ->first();

        if (!$sj) {
            return response()->json(['success' => false, 'message' => 'Surat Jalan tidak ditemukan'], 404);
        }

        // Load list kontraktor & subkontraktor
        $kontraktors = DB::table('kontraktor')
            ->where('companycode', $companycode)
            ->select(['id', 'namakontraktor'])
            ->orderBy('namakontraktor')
            ->get();

        $subkontraktors = DB::table('subkontraktor')
            ->where('companycode', $companycode)
            ->select(['id', 'namasubkontraktor'])
            ->orderBy('namasubkontraktor')
            ->get();

        return response()->json([
            'success'       => true,
            'data'          => $sj,
            'kontraktors'   => $kontraktors,
            'subkontraktors'=> $subkontraktors,
        ]);
    }

    public function checkPlot(Request $request)
    {
        $companycode = Session::get('companycode');
        $plot        = strtoupper(trim($request->input('plot', '')));

        if (!$plot) {
            return response()->json(['exists' => false]);
        }

        $exists = DB::table('masterlist')
            ->where('companycode', $companycode)
            ->where('plot', $plot)
            ->exists();

        return response()->json(['exists' => $exists, 'plot' => $plot]);
    }

    private function generateTransactionNumber(string $companycode, $date): string
    {
        $dateStr  = $date->format('ymd');
        $sequence = DB::table('koreksisuratjalanpanen')
            ->where('companycode', $companycode)
            ->whereDate('createdat', $date)
            ->count() + 1;

        return 'KSJ' . $dateStr . str_pad($sequence, 2, '0', STR_PAD_LEFT);
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
}
