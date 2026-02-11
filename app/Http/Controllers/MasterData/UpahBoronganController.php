<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\Models\User;

class UpahBoronganController extends Controller
{
    public function index(Request $request)
    {
        // Query dengan join ke tabel activity untuk ambil activitygroup dan activityname
        $query = DB::table('upahborongan as ub')
            ->join('activity as a', 'ub.activitycode', '=', 'a.activitycode')
            ->select(
                'ub.id',
                'ub.companycode',
                'ub.activitycode',
                'a.activitygroup',
                'a.activityname',
                'ub.amount',
                'ub.effectivedate',
                'ub.enddate',
                'ub.inputby',
                'ub.createdat',
                'ub.updatedat'
            );

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ub.activitycode', 'like', "%{$search}%")
                    ->orWhere('a.activityname', 'like', "%{$search}%")
                    ->orWhere('a.activitygroup', 'like', "%{$search}%")
                    ->orWhere('ub.amount', 'like', "%{$search}%");
            });
        }

        // Filter by activitygroup
        if ($request->filled('activitygroup')) {
            $query->where('a.activitygroup', $request->activitygroup);
        }

        // Filter by status (active/expired)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where(function ($q) {
                    $q->whereNull('ub.enddate')
                        ->orWhere('ub.enddate', '>=', now()->format('Y-m-d'));
                });
            } elseif ($request->status === 'expired') {
                $query->where('ub.enddate', '<', now()->format('Y-m-d'));
            }
        }

        // Filter berdasarkan company user
        $query->where('ub.companycode', Session::get('companycode'));

        $perPage = $request->get('perPage', 10);
        $data = $query->orderBy('ub.effectivedate', 'DESC')
            ->orderBy('a.activitygroup', 'ASC')
            ->paginate($perPage);

        $user = Auth::user()->userid;
        $userdata = User::where('userid', $user)->firstOrFail();

        // Daftar activity groups untuk filter dropdown
        $activityGroups = DB::table('activity')
            ->select('activitygroup')
            ->whereNotNull('activitygroup')
            ->distinct()
            ->orderBy('activitygroup')
            ->pluck('activitygroup');

        return view('masterdata.upah-borongan.index', [
            'title' => 'Upah Borongan',
            'navbar' => 'Master Data',
            'nav' => 'Upah Borongan',
            'data' => $data,
            'perPage' => $perPage,
            'userdata' => $userdata,
            'activityGroups' => $activityGroups,
        ]);
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'activitycode' => 'required|exists:activity,activitycode',
            'amount' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/|min:0',
            'effectivedate' => 'required|date',
            'enddate' => 'nullable|date|after_or_equal:effectivedate',
        ], [
            'activitycode.required' => 'Aktivitas harus dipilih',
            'activitycode.exists' => 'Aktivitas tidak valid',
            'amount.required' => 'Nominal upah harus diisi',
            'amount.numeric' => 'Nominal upah harus berupa angka',
            'amount.min' => 'Nominal upah tidak boleh negatif',
            'effectivedate.required' => 'Tanggal mulai berlaku harus diisi',
            'effectivedate.date' => 'Format tanggal tidak valid',
            'enddate.date' => 'Format tanggal berakhir tidak valid',
            'enddate.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai berlaku',
        ]);

        $companycode = Session::get('companycode');

        // ✅ VALIDASI: Cek OVERLAP periode dengan upah existing
        $overlapping = DB::table('upahborongan')
            ->where('companycode', $companycode)
            ->where('activitycode', $request->activitycode)
            ->where(function($q) use ($request) {
                // Case 1: enddate baru NULL (unlimited) - harus cek ada ga yang overlap
                if (empty($request->enddate)) {
                    $q->where(function($q2) use ($request) {
                        // Existing yang enddate NULL atau >= effectivedate baru
                        $q2->whereNull('enddate')
                           ->orWhere('enddate', '>=', $request->effectivedate);
                    })
                    ->where(function($q2) use ($request) {
                        // Existing yang effectivedate <= effectivedate baru (artinya masih berlaku)
                        $q2->where('effectivedate', '<=', $request->effectivedate);
                    });
                } else {
                    // Case 2: enddate baru ADA (ada batas waktu)
                    $q->where(function($q2) use ($request) {
                        // Check overlap: existing.effectivedate <= new.enddate AND (existing.enddate >= new.effectivedate OR existing.enddate IS NULL)
                        $q2->where('effectivedate', '<=', $request->enddate)
                           ->where(function($q3) use ($request) {
                               $q3->whereNull('enddate')
                                  ->orWhere('enddate', '>=', $request->effectivedate);
                           });
                    });
                }
            })
            ->first();

        if ($overlapping) {
            $activityName = DB::table('activity')
                ->where('activitycode', $request->activitycode)
                ->value('activityname');

            $endDateText = $overlapping->enddate 
                ? date('d-m-Y', strtotime($overlapping->enddate))
                : 'sekarang';

            return redirect()->route('masterdata.upah-borongan.index')
                ->with('error', "Tidak dapat menambah upah! Periode OVERLAP dengan upah existing untuk [{$request->activitycode} - {$activityName}] dengan nominal Rp " . number_format($overlapping->amount, 2, ',', '.') . " yang berlaku dari " . date('d-m-Y', strtotime($overlapping->effectivedate)) . " sampai {$endDateText}. Silakan sesuaikan tanggal atau edit upah yang sudah ada.");
        }

        // Insert data
        DB::table('upahborongan')->insert([
            'companycode' => $companycode,
            'activitycode' => $request->activitycode,
            'amount' => $request->amount,
            'effectivedate' => $request->effectivedate,
            'enddate' => $request->enddate,
            'inputby' => Auth::user()->userid,
            'createdat' => now(),
            'updatedat' => now(),
        ]);

        return redirect()->route('masterdata.upah-borongan.index')
            ->with('success', 'Data upah borongan berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'activitycode' => 'required|exists:activity,activitycode',
            'amount' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/|min:0',
            'effectivedate' => 'required|date',
            'enddate' => 'nullable|date|after_or_equal:effectivedate',
        ], [
            'activitycode.required' => 'Aktivitas harus dipilih',
            'activitycode.exists' => 'Aktivitas tidak valid',
            'amount.required' => 'Nominal upah harus diisi',
            'amount.numeric' => 'Nominal upah harus berupa angka',
            'amount.min' => 'Nominal upah tidak boleh negatif',
            'effectivedate.required' => 'Tanggal mulai berlaku harus diisi',
            'effectivedate.date' => 'Format tanggal tidak valid',
            'enddate.date' => 'Format tanggal berakhir tidak valid',
            'enddate.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai berlaku',
        ]);

        $companycode = Session::get('companycode');

        // Cek apakah data exist
        $existing = DB::table('upahborongan')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->first();

        if (!$existing) {
            return redirect()->route('masterdata.upah-borongan.index')
                ->with('error', 'Data upah borongan tidak ditemukan.');
        }

        // ✅ VALIDASI: Cek OVERLAP dengan record lain (exclude current record)
        $overlapping = DB::table('upahborongan')
            ->where('companycode', $companycode)
            ->where('activitycode', $request->activitycode)
            ->where('id', '!=', $id)
            ->where(function($q) use ($request) {
                if (empty($request->enddate)) {
                    $q->where(function($q2) use ($request) {
                        $q2->whereNull('enddate')
                           ->orWhere('enddate', '>=', $request->effectivedate);
                    })
                    ->where(function($q2) use ($request) {
                        $q2->where('effectivedate', '<=', $request->effectivedate);
                    });
                } else {
                    $q->where(function($q2) use ($request) {
                        $q2->where('effectivedate', '<=', $request->enddate)
                           ->where(function($q3) use ($request) {
                               $q3->whereNull('enddate')
                                  ->orWhere('enddate', '>=', $request->effectivedate);
                           });
                    });
                }
            })
            ->first();

        if ($overlapping) {
            $activityName = DB::table('activity')
                ->where('activitycode', $request->activitycode)
                ->value('activityname');

            $endDateText = $overlapping->enddate 
                ? date('d-m-Y', strtotime($overlapping->enddate))
                : 'sekarang';

            return redirect()->route('masterdata.upah-borongan.index')
                ->with('error', "Tidak dapat mengupdate upah! Periode OVERLAP dengan upah lain untuk [{$request->activitycode} - {$activityName}] dengan nominal Rp " . number_format($overlapping->amount, 2, ',', '.') . " yang berlaku dari " . date('d-m-Y', strtotime($overlapping->effectivedate)) . " sampai {$endDateText}.");
        }

        // Update data
        DB::table('upahborongan')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->update([
                'activitycode' => $request->activitycode,
                'amount' => $request->amount,
                'effectivedate' => $request->effectivedate,
                'enddate' => $request->enddate,
                'updatedat' => now(),
            ]);

        return redirect()->route('masterdata.upah-borongan.index')
            ->with('success', 'Data upah borongan berhasil diupdate');
    }

    public function destroy($id)
    {
        $companycode = Session::get('companycode');

        // Cek apakah data exist
        $existing = DB::table('upahborongan')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->first();

        if (!$existing) {
            return redirect()->route('masterdata.upah-borongan.index')
                ->with('error', 'Data upah borongan tidak ditemukan.');
        }

        // Delete data
        DB::table('upahborongan')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->delete();

        return redirect()->route('masterdata.upah-borongan.index')
            ->with('success', 'Data upah borongan berhasil dihapus.');
    }

    /**
     * Get activities by group (untuk AJAX)
     */
    public function getActivitiesByGroup(Request $request)
    {
        $activitygroup = $request->get('activitygroup');

        $activities = DB::table('activity')
            ->select('activitycode', 'activityname')
            ->where('activitygroup', $activitygroup)
            ->where('active', 1)
            ->orderBy('activityname')
            ->get();

        return response()->json($activities);
    }

    /**
     * Get current wage for activity (untuk reference di form)
     */
    public function getCurrentWage(Request $request)
    {
        $activitycode = $request->get('activitycode');
        $companycode = Session::get('companycode');

        $currentWage = DB::table('upahborongan')
            ->where('companycode', $companycode)
            ->where('activitycode', $activitycode)
            ->where(function ($q) {
                $q->whereNull('enddate')
                    ->orWhere('enddate', '>=', now()->format('Y-m-d'));
            })
            ->orderBy('effectivedate', 'DESC')
            ->first();

        return response()->json($currentWage);
    }
}