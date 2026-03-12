<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\Models\User;

class UpahController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('upah')->select('*');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('activitygroup', 'like', "%{$search}%")
                    ->orWhere('wagetype', 'like', "%{$search}%")
                    ->orWhere('parameter', 'like', "%{$search}%");
            });
        }

        // Filter by activitygroup
        if ($request->filled('activitygroup')) {
            $query->where('activitygroup', $request->activitygroup);
        }

        // Filter by wagetype
        if ($request->filled('wagetype')) {
            $query->where('wagetype', $request->wagetype);
        }

        // Filter by status (active/expired)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where(function ($q) {
                    $q->whereNull('enddate')
                        ->orWhere('enddate', '>=', now()->format('Y-m-d'));
                });
            } elseif ($request->status === 'expired') {
                $query->where('enddate', '<', now()->format('Y-m-d'));
            }
        }

        $query->where('companycode', Session::get('companycode'));

        $perPage = $request->get('perPage', 10);
        $data = $query->orderBy('effectivedate', 'DESC')->paginate($perPage);

        $user = Auth::user()->userid;
        $userdata = User::where('userid', $user)->firstOrFail();

        // Read from activitygroup table
        $activityGroups = DB::table('activitygroup')
            ->select('activitygroup', 'groupname')
            ->orderBy('activitygroup')
            ->get();

        $wageTypes = [
            'DAILY' => 'Harian',
            'HOURLY' => 'Per Jam', 
            'OVERTIME' => 'Lembur',
            'WEEKEND_SATURDAY' => 'Weekend Sabtu',
            'WEEKEND_SUNDAY' => 'Weekend Minggu',
            'PER_HECTARE' => 'Per Hektar',
            'PER_KG' => 'Per Kilogram'
        ];

        return view('masterdata.upah.index', [
            'title' => 'Upah',
            'navbar' => 'Master Data', 
            'nav' => 'Upah',
            'data' => $data,
            'perPage' => $perPage,
            'userdata' => $userdata,
            'activityGroups' => $activityGroups,
            'wageTypes' => $wageTypes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'activitygroup' => 'required',
            'wagetype' => 'required',
            'amount' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/|min:0',
            'effectivedate' => 'required|date',
            'enddate' => 'nullable|date|after_or_equal:effectivedate',
        ], [
            'activitygroup.required' => 'Grup aktivitas harus diisi',
            'wagetype.required' => 'Jenis upah harus dipilih',
            'amount.required' => 'Nominal upah harus diisi',
            'amount.numeric' => 'Nominal upah harus berupa angka',
            'amount.min' => 'Nominal upah tidak boleh negatif',
            'effectivedate.required' => 'Tanggal mulai berlaku harus diisi',
            'enddate.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai berlaku',
        ]);

        $companycode = Session::get('companycode');

        // ✅ VALIDASI: Cek OVERLAP periode dengan upah existing
        $overlapping = DB::table('upah')
            ->where('companycode', $companycode)
            ->where('activitygroup', $request->activitygroup)
            ->where('wagetype', $request->wagetype)
            ->where('parameter', $request->parameter)
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
            $wageTypes = [
                'DAILY' => 'Harian',
                'HOURLY' => 'Per Jam', 
                'OVERTIME' => 'Lembur',
                'WEEKEND_SATURDAY' => 'Weekend Sabtu',
                'WEEKEND_SUNDAY' => 'Weekend Minggu',
                'PER_HECTARE' => 'Per Hektar',
                'PER_KG' => 'Per Kilogram'
            ];

            $endDateText = $overlapping->enddate 
                ? date('d-m-Y', strtotime($overlapping->enddate))
                : 'sekarang';

            return redirect()->route('masterdata.upah.index')
                ->with('error', "Tidak dapat menambah upah! Periode OVERLAP dengan upah existing untuk [{$request->activitygroup} - {$wageTypes[$request->wagetype]}] dengan nominal Rp " . number_format($overlapping->amount, 2, ',', '.') . " yang berlaku dari " . date('d-m-Y', strtotime($overlapping->effectivedate)) . " sampai {$endDateText}. Silakan sesuaikan tanggal atau edit upah yang sudah ada.");
        }

        // Insert data
        DB::table('upah')->insert([
            'companycode' => $companycode,
            'activitygroup' => $request->activitygroup,
            'wagetype' => $request->wagetype,
            'amount' => $request->amount,
            'effectivedate' => $request->effectivedate,
            'enddate' => $request->enddate,
            'parameter' => $request->parameter,
            'inputby' => Auth::user()->userid,
            'createdat' => now(),
        ]);

        return redirect()->route('masterdata.upah.index')
            ->with('success', 'Data upah berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'activitygroup' => 'required',
            'wagetype' => 'required',
            'amount' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/|min:0',
            'effectivedate' => 'required|date',
            'enddate' => 'nullable|date|after_or_equal:effectivedate',
        ], [
            'activitygroup.required' => 'Grup aktivitas harus diisi',
            'wagetype.required' => 'Jenis upah harus dipilih',
            'amount.required' => 'Nominal upah harus diisi',
            'amount.numeric' => 'Nominal upah harus berupa angka',
            'amount.min' => 'Nominal upah tidak boleh negatif',
            'effectivedate.required' => 'Tanggal mulai berlaku harus diisi',
            'enddate.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai berlaku',
        ]);

        $companycode = Session::get('companycode');

        // Cek apakah data exist
        $existing = DB::table('upah')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->first();

        if (!$existing) {
            return redirect()->route('masterdata.upah.index')
                ->with('error', 'Data upah tidak ditemukan.');
        }

        // ✅ VALIDASI: Cek OVERLAP dengan record lain (exclude current record)
        $overlapping = DB::table('upah')
            ->where('companycode', $companycode)
            ->where('activitygroup', $request->activitygroup)
            ->where('wagetype', $request->wagetype)
            ->where('parameter', $request->parameter)
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
            $wageTypes = [
                'DAILY' => 'Harian',
                'HOURLY' => 'Per Jam', 
                'OVERTIME' => 'Lembur',
                'WEEKEND_SATURDAY' => 'Weekend Sabtu',
                'WEEKEND_SUNDAY' => 'Weekend Minggu',
                'PER_HECTARE' => 'Per Hektar',
                'PER_KG' => 'Per Kilogram'
            ];

            $endDateText = $overlapping->enddate 
                ? date('d-m-Y', strtotime($overlapping->enddate))
                : 'sekarang';

            return redirect()->route('masterdata.upah.index')
                ->with('error', "Tidak dapat mengupdate upah! Periode OVERLAP dengan upah lain untuk [{$request->activitygroup} - {$wageTypes[$request->wagetype]}] dengan nominal Rp " . number_format($overlapping->amount, 2, ',', '.') . " yang berlaku dari " . date('d-m-Y', strtotime($overlapping->effectivedate)) . " sampai {$endDateText}.");
        }

        // Update data
        DB::table('upah')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->update([
                'activitygroup' => $request->activitygroup,
                'wagetype' => $request->wagetype,
                'amount' => $request->amount,
                'effectivedate' => $request->effectivedate,
                'enddate' => $request->enddate,
                'parameter' => $request->parameter,
                'updatedat' => now(),
            ]);

        return redirect()->route('masterdata.upah.index')
            ->with('success', 'Data upah berhasil diupdate');
    }

    public function destroy($id)
    {
        $companycode = Session::get('companycode');

        $deleted = DB::table('upah')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->delete();

        if ($deleted) {
            return redirect()->route('masterdata.upah.index')
                ->with('success', 'Data berhasil dihapus.');
        }

        return redirect()->route('masterdata.upah.index')
            ->with('error', 'Data upah tidak ditemukan.');
    }

    /**
     * Get current wage untuk reference (Optional untuk future enhancement)
     */
    public function getCurrentWage(Request $request)
    {
        $activitygroup = $request->get('activitygroup');
        $wagetype = $request->get('wagetype');
        $parameter = $request->get('parameter');
        $companycode = Session::get('companycode');

        $currentWage = DB::table('upah')
            ->where('companycode', $companycode)
            ->where('activitygroup', $activitygroup)
            ->where('wagetype', $wagetype)
            ->where('parameter', $parameter)
            ->where(function ($q) {
                $q->whereNull('enddate')
                    ->orWhere('enddate', '>=', now()->format('Y-m-d'));
            })
            ->orderBy('effectivedate', 'DESC')
            ->first();

        return response()->json($currentWage);
    }
}