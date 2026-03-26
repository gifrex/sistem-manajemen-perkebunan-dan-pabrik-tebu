<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use App\Models\MasterData\Batch;

class BatchController extends Controller
{
    /**
     * Shared validation rules — single source of truth.
     */
    private function batchRules(bool $isUpdate = false): array
    {
        $rules = [
            'batchno'           => 'required|string|max:20',
            'plot'              => 'required|string|max:5',
            'plottype'          => 'nullable|in:KBD,KTG,KBI',
            'batchdate'         => 'required|date',
            'tanggalulangtahun' => 'nullable|date',
            'batcharea'         => 'required|numeric|min:0|max:9999.99',
            'kodevarietas'      => 'nullable|string|max:10',
            'lifecyclestatus'   => 'required|in:PC,RC1,RC2,RC3',
            'pkp'               => 'nullable|integer|min:0',
            'lastactivity'      => 'nullable|string|max:100',
            'plantinglkhno'     => 'nullable|string|max:15',
            'tanggalpanen'      => 'nullable|date',
        ];

        if ($isUpdate) {
            $rules['isactive'] = 'required|boolean';
        }

        return $rules;
    }

    public function index(Request $request)
    {
        $perPage     = (int) $request->input('perPage', 10);
        $search      = $request->input('search');
        $companycode = Session::get('companycode');

        $query = Batch::where('companycode', $companycode);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('batchno', 'like', "%{$search}%")
                  ->orWhere('plot', 'like', "%{$search}%")
                  ->orWhere('kodevarietas', 'like', "%{$search}%")
                  ->orWhere('lifecyclestatus', 'like', "%{$search}%")
                  ->orWhere('plantinglkhno', 'like', "%{$search}%")
                  ->orWhere('plottype', 'like', "%{$search}%");
            });
        }

        $batch = $query
            ->orderBy('batchdate', 'desc')
            ->orderBy('batchno', 'desc')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search'  => $search,
            ]);

        return view('masterdata.batch.index', [
            'batch'   => $batch,
            'title'   => 'Data Batch',
            'navbar'  => 'Master',
            'nav'     => 'Batch',
            'perPage' => $perPage,
            'search'  => $search,
        ]);
    }

    public function store(Request $request)
    {
        $companycode = Session::get('companycode');

        $validated = $request->validate($this->batchRules());

        // Duplicate check scoped to company (sesuai UK: batchno + companycode)
        $exists = Batch::where('batchno', $validated['batchno'])
            ->where('companycode', $companycode)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'batchno' => 'Duplicate Entry, Batch Number already exists',
                ]);
        }

        Batch::create([
            'batchno'           => $validated['batchno'],
            'companycode'       => $companycode,
            'plot'              => $validated['plot'],
            'plottype'          => $validated['plottype'] ?? null,
            'batchdate'         => $validated['batchdate'],
            'tanggalulangtahun' => $validated['tanggalulangtahun'] ?? null,
            'batcharea'         => $validated['batcharea'],
            'kodevarietas'      => $validated['kodevarietas'] ?? null,
            'lifecyclestatus'   => $validated['lifecyclestatus'],
            'pkp'               => $validated['pkp'] ?? null,
            'lastactivity'      => $validated['lastactivity'] ?? null,
            'isactive'          => 1,
            'plantinglkhno'     => $validated['plantinglkhno'] ?? null,
            'tanggalpanen'      => $validated['tanggalpanen'] ?? null,
            'inputby'           => Auth::user()->userid,
            'createdat'         => now(),
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, $batchno)
    {
        $companycode = Session::get('companycode');

        $batch = Batch::where('batchno', $batchno)
            ->where('companycode', $companycode)
            ->firstOrFail();

        $validated = $request->validate($this->batchRules(isUpdate: true));

        // Duplicate check only if batchno changed
        if ($validated['batchno'] !== $batch->batchno) {
            $exists = Batch::where('batchno', $validated['batchno'])
                ->where('companycode', $companycode)
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'batchno' => 'Duplicate Entry, Batch Number already exists',
                    ]);
            }
        }

        $batch->update([
            'batchno'           => $validated['batchno'],
            'plot'              => $validated['plot'],
            'plottype'          => $validated['plottype'] ?? null,
            'batchdate'         => $validated['batchdate'],
            'tanggalulangtahun' => $validated['tanggalulangtahun'] ?? null,
            'batcharea'         => $validated['batcharea'],
            'kodevarietas'      => $validated['kodevarietas'] ?? null,
            'lifecyclestatus'   => $validated['lifecyclestatus'],
            'pkp'               => $validated['pkp'] ?? null,
            'lastactivity'      => $validated['lastactivity'] ?? null,
            'isactive'          => $validated['isactive'],
            'plantinglkhno'     => $validated['plantinglkhno'] ?? null,
            'tanggalpanen'      => $validated['tanggalpanen'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Data berhasil di‑update.');
    }

    public function destroy(Request $request, $batchno)
    {
        $companycode = Session::get('companycode');

        Batch::where('batchno', $batchno)
            ->where('companycode', $companycode)
            ->delete();

        return redirect()->back()->with('success', 'Data berhasil di‑hapus.');
    }
}