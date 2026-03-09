<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Activity;
use App\Models\MasterData\ActivityGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 50);
        $search = $request->input('search');

        $query = Activity::with('group');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('activitycode', 'like', "%{$search}%")
                  ->orWhere('activityname', 'like', "%{$search}%")
                  ->orWhere('activitygroup', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $activities = $query
            ->orderBy('activitygroup')
            ->orderBy('activitycode')
            ->paginate($perPage)
            ->appends($request->only(['perPage', 'search']));

        $activityGroups = ActivityGroup::orderBy('activitygroup')->get();

        return view('masterdata.activity.index', [
            'title' => 'Daftar Aktivitas',
            'navbar' => 'Master',
            'nav' => 'aktivitas',
            'perPage' => $perPage,
            'search' => $search,
            'activities' => $activities,
            'activityGroups' => $activityGroups,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateActivity($request);

        if (Activity::where('activitycode', $validated['kodeaktivitas'])->exists()) {
            return back()->withInput()
                ->withErrors(['kodeaktivitas' => 'Kode aktivitas sudah ada dalam database']);
        }

        try {
            DB::transaction(function () use ($validated) {
                Activity::create($this->buildData($validated, true));
            });

            return back()->with('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $activityCode)
    {
        $activity = Activity::where('activitycode', $activityCode)->firstOrFail();
        $validated = $this->validateActivity($request);

        try {
            DB::transaction(function () use ($activity, $validated) {
                $activity->update($this->buildData($validated, false));
            });

            return back()->with('success', 'Data berhasil di-update');
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function destroy($activityCode)
    {
        try {
            Activity::where('activitycode', $activityCode)->delete();

            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }

    // ─── Private ─────────────────────────────────────────

    private function validateActivity(Request $request): array
    {
        return $request->validate([
            'grupaktivitas'    => 'required|exists:activitygroup,activitygroup',
            'kodeaktivitas'    => 'required|string|max:50',
            'namaaktivitas'    => 'required|string|max:150',
            'namaaktivitas2'   => 'nullable|string|max:150',
            'keterangan'       => 'nullable|string|max:150',
            'jenistenagakerja' => 'required|in:1,2',
            'material'         => 'required|in:0,1',
            'vehicle'          => 'required|in:0,1',
            'isblokactivity'   => 'required|in:0,1',
            'active'           => 'required|in:0,1',
            'accno'            => 'nullable|string|max:25',
            'var'              => 'nullable|array|max:5',
            'var.*'            => 'required|string|max:30',
            'satuan'           => 'nullable|array|max:5',
            'satuan.*'         => 'required|string|max:30',
        ], [
            'kodeaktivitas.max' => 'Kode aktivitas maksimal 50 karakter',
            'var.*.required'    => 'Variable hasil aktivitas wajib diisi',
            'satuan.*.required' => 'Satuan hasil aktivitas wajib diisi',
        ]);
    }

    private function buildData(array $validated, bool $isCreate): array
    {
        $vars = $validated['var'] ?? [];
        $satuans = $validated['satuan'] ?? [];

        $data = [
            'activitycode'     => $validated['kodeaktivitas'],
            'activitygroup'    => $validated['grupaktivitas'],
            'activityname'     => $validated['namaaktivitas'],
            'activityname2'    => $validated['namaaktivitas2'] ?? null,
            'description'      => $validated['keterangan'] ?? null,
            'jenistenagakerja' => $validated['jenistenagakerja'],
            'usingmaterial'    => $validated['material'],
            'usingvehicle'     => $validated['vehicle'],
            'isblokactivity'   => $validated['isblokactivity'],
            'active'           => $validated['active'],
            'accno'            => $validated['accno'] ?? null,
            'jumlahvar'        => count($vars),
        ];

        // Reset all var/satuan columns
        for ($i = 1; $i <= 5; $i++) {
            $data["var{$i}"] = null;
            $data["satuan{$i}"] = null;
        }

        // Fill var/satuan
        foreach ($vars as $index => $value) {
            $data['var' . ($index + 1)] = $value;
            $data['satuan' . ($index + 1)] = $satuans[$index] ?? null;
        }

        if ($isCreate) {
            $data['inputby'] = Auth::user()->userid;
            $data['createdat'] = now();
        } else {
            $data['updatedby'] = Auth::user()->userid;
            $data['updatedat'] = now();
        }

        return $data;
    }
}