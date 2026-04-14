<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MasterData\Approval;
use App\Models\MasterData\Jabatan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $perPage     = (int) $request->input('perPage', 10);
        $search      = $request->input('search');
        $companycode = Session::get('companycode');

        $query = Approval::with([
            'jabatanApproval1',
            'jabatanApproval2',
            'jabatanApproval3',
            'jabatanApproval4',
            'jabatanApproval5',
        ])->where('companycode', $companycode);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('category', 'like', "%{$search}%")
                  ->orWhere('activitygroup', 'like', "%{$search}%");
            });
        }

        $approval = $query
            ->orderBy('activitygroup')
            ->orderBy('category')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage, 'search' => $search]);

        $jabatan = Jabatan::orderBy('namajabatan', 'asc')->get();

        return view('masterdata.approval.index', [
            'approval' => $approval,
            'title'    => 'Data Approval',
            'navbar'   => 'Master',
            'nav'      => 'Approval',
            'perPage'  => $perPage,
            'search'   => $search,
            'jabatan'  => $jabatan,
        ]);
    }

    public function store(Request $request)
    {
        $companycode = Session::get('companycode');

        $request->validate([
            'category'           => 'required|string|max:150',
            'activitygroup'      => 'nullable|string|max:5',
            'jumlahapproval'     => 'required|integer|min:1|max:5',
            'idjabatanapproval1' => 'required|integer|exists:jabatan,idjabatan',
            'idjabatanapproval2' => 'nullable|integer|exists:jabatan,idjabatan',
            'idjabatanapproval3' => 'nullable|integer|exists:jabatan,idjabatan',
            'idjabatanapproval4' => 'nullable|integer|exists:jabatan,idjabatan',
            'idjabatanapproval5' => 'nullable|integer|exists:jabatan,idjabatan',
        ], $this->validationMessages());

        // Validasi jabatan sesuai jumlah approval
        $errors = $this->validateJabatanCount($request->jumlahapproval, $request->all());
        if ($errors) return redirect()->back()->withInput()->withErrors($errors);

        // Cek duplicate
        if (Approval::where('companycode', $companycode)->where('category', $request->category)->exists()) {
            return redirect()->back()->withInput()->withErrors(['category' => 'Duplicate Entry, Category already exists']);
        }

        if ($request->activitygroup && Approval::where('companycode', $companycode)->where('activitygroup', $request->activitygroup)->exists()) {
            return redirect()->back()->withInput()->withErrors(['activitygroup' => 'Duplicate Entry, Activity Group already exists']);
        }

        Approval::create($this->buildPayload($request, $companycode, 'create'));

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, $companycode, $category)
    {
        $sessionCompanycode = Session::get('companycode');

        if ($companycode !== $sessionCompanycode) abort(403, 'Unauthorized access to company data');

        $approval = Approval::where([['companycode', $companycode], ['category', $category]])->firstOrFail();

        $validated = $request->validate([
            'category'           => 'required|string|max:150',
            'activitygroup'      => 'nullable|string|max:5',
            'jumlahapproval'     => 'required|integer|min:1|max:5',
            'idjabatanapproval1' => 'required|integer|exists:jabatan,idjabatan',
            'idjabatanapproval2' => 'nullable|integer|exists:jabatan,idjabatan',
            'idjabatanapproval3' => 'nullable|integer|exists:jabatan,idjabatan',
            'idjabatanapproval4' => 'nullable|integer|exists:jabatan,idjabatan',
            'idjabatanapproval5' => 'nullable|integer|exists:jabatan,idjabatan',
        ], $this->validationMessages());

        // Validasi jabatan sesuai jumlah approval
        $errors = $this->validateJabatanCount($validated['jumlahapproval'], $validated);
        if ($errors) return redirect()->back()->withInput()->withErrors($errors);

        // Cek duplicate category jika berubah
        if ($request->category !== $approval->category) {
            if (Approval::where('companycode', $companycode)->where('category', $request->category)->exists()) {
                return redirect()->back()->withInput()->withErrors(['category' => 'Duplicate Entry, Category already exists']);
            }
        }

        // Cek duplicate activitygroup jika berubah
        if ($request->activitygroup && $request->activitygroup !== $approval->activitygroup) {
            if (Approval::where('companycode', $companycode)->where('activitygroup', $request->activitygroup)->exists()) {
                return redirect()->back()->withInput()->withErrors(['activitygroup' => 'Duplicate Entry, Activity Group already exists']);
            }
        }

        Approval::where([['companycode', $companycode], ['category', $category]])
            ->update($this->buildPayload($request, $companycode, 'update'));

        return redirect()->back()->with('success', 'Data berhasil di-update.');
    }

    public function destroy(Request $request, $companycode, $category)
    {
        if ($companycode !== Session::get('companycode')) abort(403, 'Unauthorized access to company data');

        Approval::where([['companycode', $companycode], ['category', $category]])->delete();

        return redirect()->back()->with('success', 'Data berhasil di-hapus.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function validationMessages(): array
    {
        return [
            'jumlahapproval.max'         => 'Jumlah approval maksimal 5',
            'idjabatanapproval1.required' => 'Jabatan approval 1 wajib diisi',
            'idjabatanapproval1.exists'   => 'Jabatan approval 1 tidak valid',
            'idjabatanapproval2.exists'   => 'Jabatan approval 2 tidak valid',
            'idjabatanapproval3.exists'   => 'Jabatan approval 3 tidak valid',
            'idjabatanapproval4.exists'   => 'Jabatan approval 4 tidak valid',
            'idjabatanapproval5.exists'   => 'Jabatan approval 5 tidak valid',
        ];
    }

    private function validateJabatanCount(int $jumlah, array $data): ?array
    {
        for ($i = 2; $i <= $jumlah; $i++) {
            $key = "idjabatanapproval{$i}";
            if (empty($data[$key])) {
                return [$key => "Jabatan approval {$i} wajib diisi untuk jumlah approval >= {$i}"];
            }
        }
        return null;
    }

    private function buildPayload(Request $request, string $companycode, string $mode): array
    {
        $jumlah  = (int) $request->jumlahapproval;
        $payload = [
            'companycode'        => $companycode,
            'category'           => $request->category,
            'activitygroup'      => $request->activitygroup,
            'jumlahapproval'     => $jumlah,
            'idjabatanapproval1' => $request->idjabatanapproval1,
            'idjabatanapproval2' => $jumlah >= 2 ? $request->idjabatanapproval2 : null,
            'idjabatanapproval3' => $jumlah >= 3 ? $request->idjabatanapproval3 : null,
            'idjabatanapproval4' => $jumlah >= 4 ? $request->idjabatanapproval4 : null,
            'idjabatanapproval5' => $jumlah >= 5 ? $request->idjabatanapproval5 : null,
        ];

        if ($mode === 'create') {
            $payload['inputby']   = Auth::user()->userid;
            $payload['createdat'] = now();
        } else {
            $payload['updateby']  = Auth::user()->userid;
            $payload['updatedat'] = now();
        }

        return $payload;
    }
}