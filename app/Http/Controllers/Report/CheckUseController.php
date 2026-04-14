<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\MasterData\Company;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class CheckUseController extends Controller
{
    // Ganti dengan URL API penerima yang sudah dibuat
    const API_URL = 'https://rosebrand.sungaibudigroup.com/app/im-purchasing/purchasing/bpb/check_use_api';

    public function __construct()
    {
        View::share(['navbar' => 'Report']);
    }

    public function index()
    {
        $title  = 'Cek Sinkronisasi No Use';
        $nav    = 'Cek Use';

        return view('report.check-use.index', compact('title', 'nav'));
    }

    public function check(Request $request)
    {
        $title  = 'Cek Sinkronisasi No Use';
        $nav    = 'Cek Use';

        // Ambil daftar nouse dari lokal
        $localData = DB::table('usemateriallst')
            ->select('nouse', 'companycode', 'rkhno')
            ->selectRaw('MAX(costcenter) as costcenter')
            ->where('nouse', '!=', '')
            ->where('companycode', session('companycode'))
            ->groupBy('nouse', 'companycode', 'rkhno')
            ->orderBy('rkhno')
            ->get();

        if ($localData->isEmpty()) {
            return view('report.check-use.index', compact('title', 'nav'))
                ->with('warning', 'Tidak ada data No Use di lokal.');
        }

        // Tentukan koneksi
        if (request()->getHost() == 'sugarcane.sblampung.com') {
            $koneksi = '172.17.1.39';
        } else {
            $koneksi = 'TESTING';
        }
        if (session('companycode') == 'TBL4') {
            $koneksi = 'TESTING';
        }

        $payload = [
            'connection'  => $koneksi,
            'companytebu' => session('companycode'),
            'data'        => $localData->map(fn($r) => [
                'nouse'       => $r->nouse,
                'companycode' => $r->companycode,
                'rkhno'       => $r->rkhno,
                'costcenter'  => $r->costcenter,
            ])->values()->toArray(),
        ];

        Log::info('CHECK_USE_API_SEND', [
            'companycode' => session('companycode'),
            'count'       => $localData->count(),
        ]);

        try {
            $response = Http::withOptions([
                'verify' => false,
                'curl'   => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                ],
            ])
                ->asJson()
                ->timeout(30)
                ->post(self::API_URL, $payload);

            if (!$response->successful()) {
                Log::error('CHECK_USE_API_ERROR', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return view('report.check-use.index', compact('title', 'nav'))
                    ->with('error', 'API penerima error: HTTP ' . $response->status());
            }

            $responseData = $response->json();

            Log::info('CHECK_USE_API_RESPONSE', ['response' => $responseData]);

            // Hasil mismatch dari response API
            // Ekspektasi response:
            // {
            //   "status": 1,
            //   "missing_in_receiver": [{"nouse":"...","rkhno":"...","reason":"..."}],
            //   "missing_in_sender":   [{"nouse":"...","rkhno":"...","reason":"..."}]
            // }
            $missingInReceiver = $responseData['missing_in_receiver'] ?? [];
            $missingInSender   = $responseData['missing_in_sender']   ?? [];
            $apiStatus         = $responseData['status']              ?? 0;
            $apiMessage        = $responseData['message']             ?? '';

            return view('report.check-use.index', compact(
                'title', 'nav',
                'missingInReceiver', 'missingInSender',
                'apiStatus', 'apiMessage',
                'localData'
            ));
        } catch (\Throwable $e) {
            Log::error('CHECK_USE_API_EXCEPTION', ['err' => $e->getMessage()]);

            return view('report.check-use.index', compact('title', 'nav'))
                ->with('error', 'Gagal menghubungi API: ' . $e->getMessage());
        }
    }
}
