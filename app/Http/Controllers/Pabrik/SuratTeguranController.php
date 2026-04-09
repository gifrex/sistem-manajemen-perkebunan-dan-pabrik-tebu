<?php

namespace App\Http\Controllers\Pabrik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SuratTeguranController extends Controller
{
    public function index(Request $request)
    {
        $companycode = session('companycode');
        $search      = $request->get('search');
        $perPage     = $request->get('perPage', 10);

        $query = DB::table('suratteguran as st')
            ->leftJoin('company as c', 'c.companycode', '=', 'st.targetcompany')
            ->select('st.*', 'c.name as targetcompanyname')
            ->orderBy('st.createdat', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('st.stgno', 'like', "%{$search}%")
                  ->orWhere('st.perihal', 'like', "%{$search}%")
                  ->orWhere('st.targetcompany', 'like', "%{$search}%");
            });
        }

        $data = $query->paginate($perPage);

        return view('pabrik.surat-teguran.index', [
            'title'  => 'Surat Teguran',
            'navbar' => 'Pabrik',
            'nav'    => 'Surat Teguran',
            'data'   => $data,
        ]);
    }

    public function create()
    {
        $companies = DB::table('company')
            ->select('companycode', 'name')
            ->orderBy('companycode')
            ->get();

        return view('pabrik.surat-teguran.create', [
            'title'     => 'Buat Surat Teguran',
            'navbar'    => 'Pabrik',
            'nav'       => 'Surat Teguran',
            'companies' => $companies,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'targetcompany' => 'required|string|max:4',
            'jenisteguran'  => 'required|in:REJECT,KOTOR',
            'perihal'       => 'required|string|max:255',
            'isiteguran'    => 'required|string',
            'lampiran'      => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ], [
            'targetcompany.required' => 'Company tujuan harus dipilih.',
            'jenisteguran.required'  => 'Jenis teguran harus dipilih.',
            'jenisteguran.in'        => 'Jenis teguran tidak valid.',
            'perihal.required'       => 'Perihal harus diisi.',
            'isiteguran.required'    => 'Isi teguran harus diisi.',
            'lampiran.mimes'         => 'Lampiran harus berformat JPEG, JPG, PNG, atau PDF.',
            'lampiran.max'           => 'Ukuran lampiran maksimal 5 MB.',
        ]);

        $companycode = session('companycode');
        $now         = now();
        $user        = Auth::user()->userid;
        $stgno       = $this->generateStgNo($companycode, $now->year);

        $lampiranPath = $this->uploadLampiran($request, $companycode, $request->input('targetcompany'), $stgno, $now);

        DB::table('suratteguran')->insert([
            'companycode'   => $companycode,
            'stgno'         => $stgno,
            'stgdate'       => $now->toDateString(),
            'targetcompany' => $request->input('targetcompany'),
            'jenisteguran'  => $request->input('jenisteguran'),
            'perihal'       => $request->input('perihal'),
            'isiteguran'    => $request->input('isiteguran'),
            'lampiran'      => $lampiranPath,
            'status'        => 'draft',
            'inputby'       => $user,
            'createdat'     => $now,
            'updateby'      => $user,
            'updatedat'     => $now,
        ]);

        return redirect()->route('pabrik.surat-teguran.index')
            ->with('success', "Surat teguran {$stgno} berhasil disimpan sebagai draft.");
    }

    public function show($id)
    {
        $companycode = session('companycode');

        $surat = DB::table('suratteguran')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->first();

        abort_if(!$surat, 404);

        $targetCompany = DB::table('company')
            ->where('companycode', $surat->targetcompany)
            ->first();

        $lampiranUrl = $surat->lampiran
            ? Storage::disk('s3')->temporaryUrl($surat->lampiran, now()->addMinutes(30))
            : null;

        return view('pabrik.surat-teguran.show', [
            'title'         => 'Detail Surat Teguran',
            'navbar'        => 'Pabrik',
            'nav'           => 'Surat Teguran',
            'surat'         => $surat,
            'targetCompany' => $targetCompany,
            'lampiranUrl'   => $lampiranUrl,
        ]);
    }

    public function edit($id)
    {
        $companycode = session('companycode');

        $surat = DB::table('suratteguran')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->where('status', 'draft')
            ->first();

        abort_if(!$surat, 404);

        $companies = DB::table('company')
            ->select('companycode', 'name')
            ->orderBy('companycode')
            ->get();

        $lampiranUrl = $surat->lampiran
            ? Storage::disk('s3')->temporaryUrl($surat->lampiran, now()->addMinutes(30))
            : null;

        return view('pabrik.surat-teguran.edit', [
            'title'       => 'Edit Surat Teguran',
            'navbar'      => 'Pabrik',
            'nav'         => 'Surat Teguran',
            'surat'       => $surat,
            'companies'   => $companies,
            'lampiranUrl' => $lampiranUrl,
        ]);
    }

    public function update(Request $request, $id)
    {
        $companycode = session('companycode');

        $surat = DB::table('suratteguran')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->where('status', 'draft')
            ->first();

        abort_if(!$surat, 404);

        $request->validate([
            'targetcompany' => 'required|string|max:4',
            'jenisteguran'  => 'required|in:REJECT,KOTOR',
            'perihal'       => 'required|string|max:255',
            'isiteguran'    => 'required|string',
            'lampiran'      => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ], [
            'targetcompany.required' => 'Company tujuan harus dipilih.',
            'jenisteguran.required'  => 'Jenis teguran harus dipilih.',
            'jenisteguran.in'        => 'Jenis teguran tidak valid.',
            'perihal.required'       => 'Perihal harus diisi.',
            'isiteguran.required'    => 'Isi teguran harus diisi.',
            'lampiran.mimes'         => 'Lampiran harus berformat JPEG, JPG, PNG, atau PDF.',
            'lampiran.max'           => 'Ukuran lampiran maksimal 5 MB.',
        ]);

        $now  = now();
        $user = Auth::user()->userid;

        $lampiranPath = $surat->lampiran;
        if ($request->hasFile('lampiran')) {
            // Hapus lampiran lama dari S3
            if ($surat->lampiran && Storage::disk('s3')->exists($surat->lampiran)) {
                Storage::disk('s3')->delete($surat->lampiran);
            }
            $lampiranPath = $this->uploadLampiran($request, $companycode, $request->input('targetcompany'), $surat->stgno, $now);
        }

        DB::table('suratteguran')->where('id', $id)->update([
            'targetcompany' => $request->input('targetcompany'),
            'jenisteguran'  => $request->input('jenisteguran'),
            'perihal'       => $request->input('perihal'),
            'isiteguran'    => $request->input('isiteguran'),
            'lampiran'      => $lampiranPath,
            'updateby'      => $user,
            'updatedat'     => $now,
        ]);

        return redirect()->route('pabrik.surat-teguran.show', $id)
            ->with('success', 'Surat teguran berhasil diperbarui.');
    }

    public function send($id)
    {
        $companycode = session('companycode');

        $surat = DB::table('suratteguran')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->where('status', 'draft')
            ->first();

        abort_if(!$surat, 404);

        DB::table('suratteguran')->where('id', $id)->update([
            'status'    => 'sent',
            'stgdate'   => now()->toDateString(),
            'updateby'  => Auth::user()->userid,
            'updatedat' => now(),
        ]);

        return redirect()->route('pabrik.surat-teguran.show', $id)
            ->with('success', "Surat teguran {$surat->stgno} berhasil dikirim.");
    }

    public function destroy($id)
    {
        $companycode = session('companycode');

        $surat = DB::table('suratteguran')
            ->where('id', $id)
            ->where('companycode', $companycode)
            ->where('status', 'draft')
            ->first();

        abort_if(!$surat, 404);

        if ($surat->lampiran && Storage::disk('s3')->exists($surat->lampiran)) {
            Storage::disk('s3')->delete($surat->lampiran);
        }

        DB::table('suratteguran')->where('id', $id)->delete();

        return redirect()->route('pabrik.surat-teguran.index')
            ->with('success', 'Draft surat teguran berhasil dihapus.');
    }

    private function uploadLampiran(Request $request, string $companycode, string $targetcompany, string $stgno, $now): ?string
    {
        if (!$request->hasFile('lampiran')) {
            return null;
        }

        $file      = $request->file('lampiran');
        $ext       = $file->getClientOriginalExtension() ?: ($file->guessExtension() ?? 'pdf');
        $safeStgno = str_replace('/', '-', $stgno);
        $filename  = sprintf('%s_%s_%s.%s', $companycode, $safeStgno, Str::random(6), $ext);
        $path      = sprintf(
            'suratteguran/%s/%s/%s/%s/%s',
            $now->format('Y'),
            $now->format('m'),
            $companycode,
            $targetcompany,
            $filename
        );

        Storage::disk('s3')->put($path, file_get_contents($file));

        return $path;
    }

    private function generateStgNo(string $companycode, int $year): string
    {
        $yearShort = substr((string) $year, 2);
        $prefix    = "STG/{$yearShort}/";

        $last = DB::table('suratteguran')
            ->where('companycode', $companycode)
            ->where('stgno', 'like', $prefix . '%')
            ->orderBy('stgno', 'desc')
            ->value('stgno');

        $seq = 1;
        if ($last) {
            $parts = explode('/', $last);
            $seq   = (int) end($parts) + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
