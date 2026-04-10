<?php
// App\Http\Controllers\Transaction\NfcController.php

namespace App\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * NfcController
 *
 * Manages NFC card inventory with 3-wallet system:
 * - KANTOR (warehouse): physical cards in office
 * - MANDOR: cards mandor is responsible for (in hand + at POS)
 * - POS: derived from suratjalanpos (auto when SJ created)
 *
 * Each transaction tracks: from_wallet → to_wallet, balance before/after
 */
class NfcController extends Controller
{
    public function index(Request $request)
    {
        $companycode = Session::get('companycode');

        // Kantor balance
        $kantorBalance = DB::table('nfc')
            ->where('companycode', $companycode)
            ->whereNull('mandorid')
            ->value('balance') ?? 0;

        // POS: total SJ - total POS returns
        $totalSJ = DB::table('suratjalanpos')
            ->where('companycode', $companycode)
            ->count();

        $totalPosReturned = DB::table('nfctransaction')
            ->where('companycode', $companycode)
            ->where('transactiontype', 'IN')
            ->where('source', 'POS')
            ->sum('qty');

        $posBalance = max(0, $totalSJ - $totalPosReturned);

        // SJ per mandor
        $sjByMandor = DB::table('suratjalanpos')
            ->where('companycode', $companycode)
            ->groupBy('mandorid')
            ->select(['mandorid', DB::raw('COUNT(*) as total_sj')])
            ->get()
            ->keyBy('mandorid');

        // POS returns per mandor
        $posReturnByMandor = DB::table('nfctransaction')
            ->where('companycode', $companycode)
            ->where('transactiontype', 'IN')
            ->where('source', 'POS')
            ->groupBy('mandorid')
            ->select(['mandorid', DB::raw('SUM(qty) as returned_qty')])
            ->get()
            ->keyBy('mandorid');

        // Mandor balances
        $mandorBalances = DB::table('nfc as n')
            ->leftJoin('user as u', 'n.mandorid', '=', 'u.userid')
            ->where('n.companycode', $companycode)
            ->whereNotNull('n.mandorid')
            ->select([
                'n.id', 'n.mandorid', 'u.name as mandorname',
                'n.balance', 'n.lasttransaction'
            ])
            ->orderBy('u.name')
            ->get()
            ->map(function ($m) use ($sjByMandor, $posReturnByMandor) {
                $sj = $sjByMandor[$m->mandorid]->total_sj ?? 0;
                $ret = $posReturnByMandor[$m->mandorid]->returned_qty ?? 0;
                $m->cards_at_pos = max(0, $sj - $ret);
                $m->in_hand = $m->balance - $m->cards_at_pos;
                return $m;
            });

        $totalInHand = $mandorBalances->sum('in_hand');
        $totalAtPos  = $mandorBalances->sum('cards_at_pos');
        $totalMandor = $mandorBalances->sum('balance');

        // Total kartu = from EXTERNAL net
        $totalKartu = DB::table('nfctransaction')
            ->where('companycode', $companycode)
            ->where('mandorid', 'EXTERNAL')
            ->selectRaw("COALESCE(SUM(CASE WHEN transactiontype='IN' THEN qty WHEN transactiontype='OUT' THEN -qty ELSE 0 END), 0) as net")
            ->value('net') ?? 0;

        // Mandor list for dropdowns
        $mandorList = DB::table('user')
            ->where('companycode', $companycode)
            ->where('idjabatan', 5)
            ->where('isactive', 1)
            ->select('userid', 'name')
            ->orderBy('name')
            ->get();

        // Transaction filters
        $filterDateFrom = $request->get('date_from');
        $filterDateTo   = $request->get('date_to');
        $filterMandor   = $request->get('mandorid');

        $recentTransactions = DB::table('nfctransaction as nt')
            ->leftJoin('user as u', 'nt.mandorid', '=', 'u.userid')
            ->where('nt.companycode', $companycode)
            ->when($filterDateFrom, fn($q) => $q->whereDate('nt.transactiondate', '>=', $filterDateFrom))
            ->when($filterDateTo,   fn($q) => $q->whereDate('nt.transactiondate', '<=', $filterDateTo))
            ->when($filterMandor,   fn($q) => $q->where('nt.mandorid', $filterMandor))
            ->select([
                'nt.id', 'nt.transactionno', 'nt.transactiondate',
                'nt.transactiontype', 'nt.mandorid', 'nt.source',
                'nt.from_wallet', 'nt.to_wallet',
                'nt.from_balance_before', 'nt.from_balance_after',
                'nt.to_balance_before', 'nt.to_balance_after',
                'u.name as mandorname',
                'nt.qty', 'nt.notes', 'nt.inputby', 'nt.createdat'
            ])
            ->orderBy('nt.createdat', 'desc')
            ->limit(100)
            ->get();

        return view('transaction.nfc.index', [
            'title'              => 'NFC Card Management',
            'navbar'             => 'Input',
            'nav'                => 'NFC',
            'totalKartu'         => $totalKartu,
            'kantorBalance'      => $kantorBalance,
            'totalInHand'        => $totalInHand,
            'totalAtPos'         => $totalAtPos,
            'totalMandor'        => $totalMandor,
            'posBalance'         => $posBalance,
            'mandorBalances'     => $mandorBalances,
            'recentTransactions' => $recentTransactions,
            'mandorList'         => $mandorList,
            'filterDateFrom'     => $filterDateFrom,
            'filterDateTo'       => $filterDateTo,
            'filterMandor'       => $filterMandor,
        ]);
    }

    /**
     * OUT: Kantor → Mandor
     */
    public function transactionOut(Request $request)
    {
        $request->validate([
            'mandorid' => 'required|exists:user,userid',
            'qty' => 'required|integer|min:1',
            'transactiondate' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user()->userid;

            DB::beginTransaction();

            $kantorBefore = $this->getBalance($companycode, null);

            if ($kantorBefore < $request->qty) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok kantor tidak cukup. Tersedia: {$kantorBefore} kartu"
                ], 400);
            }

            $mandorBefore = $this->getBalance($companycode, $request->mandorid);
            $transactionNo = $this->generateTransactionNo($companycode);

            DB::table('nfctransaction')->insert([
                'transactionno'      => $transactionNo,
                'companycode'        => $companycode,
                'transactiondate'    => $request->transactiondate,
                'transactiontype'    => 'OUT',
                'mandorid'           => $request->mandorid,
                'from_wallet'        => 'KANTOR',
                'to_wallet'          => 'MANDOR',
                'from_balance_before' => $kantorBefore,
                'from_balance_after'  => $kantorBefore - $request->qty,
                'to_balance_before'   => $mandorBefore,
                'to_balance_after'    => $mandorBefore + $request->qty,
                'qty'                => $request->qty,
                'notes'              => $request->notes,
                'inputby'            => $currentUser,
                'createdat'          => now()
            ]);

            $this->updateBalance($companycode, null, -$request->qty);
            $this->updateBalance($companycode, $request->mandorid, $request->qty);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengeluarkan {$request->qty} kartu NFC untuk mandor",
                'transactionno' => $transactionNo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("NFC OUT Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * IN: Mandor → Kantor (kartu sisa, belum dipakai)
     */
    public function transactionIn(Request $request)
    {
        $request->validate([
            'mandorid' => 'required|exists:user,userid',
            'qty' => 'required|integer|min:1',
            'transactiondate' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user()->userid;

            DB::beginTransaction();

            $mandorBalance = $this->getBalance($companycode, $request->mandorid);
            $cardsAtPos    = $this->getCardsAtPos($companycode, $request->mandorid);
            $inHand        = $mandorBalance - $cardsAtPos;

            if ($inHand < $request->qty) {
                return response()->json([
                    'success' => false,
                    'message' => "Kartu di tangan mandor tidak cukup. Di tangan: {$inHand} (Saldo: {$mandorBalance}, Di POS: {$cardsAtPos})"
                ], 400);
            }

            $kantorBefore = $this->getBalance($companycode, null);
            $transactionNo = $this->generateTransactionNo($companycode);

            DB::table('nfctransaction')->insert([
                'transactionno'      => $transactionNo,
                'companycode'        => $companycode,
                'transactiondate'    => $request->transactiondate,
                'transactiontype'    => 'IN',
                'mandorid'           => $request->mandorid,
                'from_wallet'        => 'MANDOR',
                'to_wallet'          => 'KANTOR',
                'from_balance_before' => $mandorBalance,
                'from_balance_after'  => $mandorBalance - $request->qty,
                'to_balance_before'   => $kantorBefore,
                'to_balance_after'    => $kantorBefore + $request->qty,
                'qty'                => $request->qty,
                'notes'              => $request->notes,
                'inputby'            => $currentUser,
                'createdat'          => now()
            ]);

            $this->updateBalance($companycode, $request->mandorid, -$request->qty);
            $this->updateBalance($companycode, null, $request->qty);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menerima {$request->qty} kartu NFC dari mandor",
                'transactionno' => $transactionNo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("NFC IN Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POS Return: POS → Kantor (kartu dari pos kembali ke kantor)
     * Mandor balance juga berkurang (tanggung jawab mandor selesai)
     */
    public function posIn(Request $request)
    {
        $request->validate([
            'mandorid' => 'required|exists:user,userid',
            'qty' => 'required|integer|min:1',
            'transactiondate' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user()->userid;

            DB::beginTransaction();

            $cardsAtPos = $this->getCardsAtPos($companycode, $request->mandorid);

            if ($cardsAtPos < $request->qty) {
                return response()->json([
                    'success' => false,
                    'message' => "Kartu mandor di POS tidak cukup. Tersedia: {$cardsAtPos} kartu"
                ], 400);
            }

            $mandorBefore = $this->getBalance($companycode, $request->mandorid);
            $kantorBefore = $this->getBalance($companycode, null);
            $transactionNo = $this->generateTransactionNo($companycode);

            DB::table('nfctransaction')->insert([
                'transactionno'      => $transactionNo,
                'companycode'        => $companycode,
                'transactiondate'    => $request->transactiondate,
                'transactiontype'    => 'IN',
                'mandorid'           => $request->mandorid,
                'source'             => 'POS',
                'from_wallet'        => 'POS',
                'to_wallet'          => 'KANTOR',
                'from_balance_before' => $cardsAtPos,
                'from_balance_after'  => $cardsAtPos - $request->qty,
                'to_balance_before'   => $kantorBefore,
                'to_balance_after'    => $kantorBefore + $request->qty,
                'qty'                => $request->qty,
                'notes'              => $request->notes ?? 'POS Return',
                'inputby'            => $currentUser,
                'createdat'          => now()
            ]);

            // Mandor responsibility decreases + kantor increases
            $this->updateBalance($companycode, $request->mandorid, -$request->qty);
            $this->updateBalance($companycode, null, $request->qty);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menerima {$request->qty} kartu NFC dari POS (mandor: {$request->mandorid})",
                'transactionno' => $transactionNo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("NFC POS IN Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * External IN: EXTERNAL → Kantor (beli kartu baru)
     */
    public function externalIn(Request $request)
    {
        $request->validate([
            'qty' => 'required|integer|min:1',
            'transactiondate' => 'required|date',
            'notes' => 'required|string|max:500'
        ]);

        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user()->userid;

            DB::beginTransaction();

            $kantorBefore = $this->getBalance($companycode, null);
            $transactionNo = $this->generateTransactionNo($companycode);

            DB::table('nfctransaction')->insert([
                'transactionno'      => $transactionNo,
                'companycode'        => $companycode,
                'transactiondate'    => $request->transactiondate,
                'transactiontype'    => 'IN',
                'mandorid'           => 'EXTERNAL',
                'from_wallet'        => 'EXTERNAL',
                'to_wallet'          => 'KANTOR',
                'to_balance_before'  => $kantorBefore,
                'to_balance_after'   => $kantorBefore + $request->qty,
                'qty'                => $request->qty,
                'notes'              => 'EXTERNAL IN: ' . $request->notes,
                'inputby'            => $currentUser,
                'createdat'          => now()
            ]);

            $this->updateBalance($companycode, null, $request->qty);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menambah {$request->qty} kartu NFC ke stock kantor",
                'transactionno' => $transactionNo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("NFC External IN Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * External OUT: Kantor → EXTERNAL (rusak/hilang/disposal)
     */
    public function externalOut(Request $request)
    {
        $request->validate([
            'qty' => 'required|integer|min:1',
            'reason' => 'required|in:DAMAGED,LOST,DISPOSAL',
            'transactiondate' => 'required|date',
            'notes' => 'required|string|max:500'
        ]);

        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user()->userid;

            DB::beginTransaction();

            $kantorBefore = $this->getBalance($companycode, null);

            if ($kantorBefore < $request->qty) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok kantor tidak cukup. Tersedia: {$kantorBefore} kartu"
                ], 400);
            }

            $transactionNo = $this->generateTransactionNo($companycode);

            $reasonText = match($request->reason) {
                'DAMAGED' => 'Rusak', 'LOST' => 'Hilang', 'DISPOSAL' => 'Disposal', default => 'Unknown'
            };

            DB::table('nfctransaction')->insert([
                'transactionno'      => $transactionNo,
                'companycode'        => $companycode,
                'transactiondate'    => $request->transactiondate,
                'transactiontype'    => 'OUT',
                'mandorid'           => 'EXTERNAL',
                'from_wallet'        => 'KANTOR',
                'to_wallet'          => 'EXTERNAL',
                'from_balance_before' => $kantorBefore,
                'from_balance_after'  => $kantorBefore - $request->qty,
                'qty'                => $request->qty,
                'notes'              => "EXTERNAL OUT ({$reasonText}): " . $request->notes,
                'inputby'            => $currentUser,
                'createdat'          => now()
            ]);

            $this->updateBalance($companycode, null, -$request->qty);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengurangi {$request->qty} kartu NFC dari stock kantor ({$reasonText})",
                'transactionno' => $transactionNo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("NFC External OUT Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    // =====================================
    // PRIVATE HELPERS
    // =====================================

    private function getBalance($companycode, $mandorid)
    {
        $query = DB::table('nfc')->where('companycode', $companycode);

        if ($mandorid === null) {
            $query->whereNull('mandorid');
        } else {
            $query->where('mandorid', $mandorid);
        }

        return $query->value('balance') ?? 0;
    }

    private function getCardsAtPos($companycode, $mandorid)
    {
        $totalSJ = DB::table('suratjalanpos')
            ->where('companycode', $companycode)
            ->where('mandorid', $mandorid)
            ->count();

        $posReturned = DB::table('nfctransaction')
            ->where('companycode', $companycode)
            ->where('mandorid', $mandorid)
            ->where('transactiontype', 'IN')
            ->where('source', 'POS')
            ->sum('qty');

        return max(0, $totalSJ - $posReturned);
    }

    private function updateBalance($companycode, $mandorid, $qtyChange)
    {
        $existing = DB::table('nfc')
            ->where('companycode', $companycode)
            ->where(function ($q) use ($mandorid) {
                $mandorid === null ? $q->whereNull('mandorid') : $q->where('mandorid', $mandorid);
            })
            ->first();

        $currentUser = Auth::user()->userid ?? 'SYSTEM';

        if ($existing) {
            $newBalance = $existing->balance + $qtyChange;
            DB::table('nfc')
                ->where('id', $existing->id)
                ->update([
                    'balance'         => $newBalance,
                    'lasttransaction' => now(),
                    'updateby'        => $currentUser,
                    'updatedat'       => now()
                ]);
        } else {
            DB::table('nfc')->insert([
                'companycode'     => $companycode,
                'mandorid'        => $mandorid,
                'balance'         => max(0, $qtyChange),
                'lasttransaction' => now(),
                'inputby'         => $currentUser,
                'createdat'       => now()
            ]);
        }
    }

    private function generateTransactionNo($companycode)
    {
        $date = now()->format('Ymd');
        $prefix = "NFC{$date}";

        $lastTx = DB::table('nfctransaction')
            ->where('companycode', $companycode)
            ->where('transactionno', 'like', "{$prefix}%")
            ->orderBy('transactionno', 'desc')
            ->value('transactionno');

        $sequence = $lastTx ? (int) substr($lastTx, -4) + 1 : 1;

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
