<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class HargaPanenController extends Controller
{
    public function index(Request $request)
    {
        $perPage    = (int) $request->input('perPage', 10);
        $search     = $request->input('search');
        $companycode = session('companycode');

        $query = DB::table('hargapanentebu')
            ->where('companycode', $companycode);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kodeharga', 'like', "%{$search}%")
                    ->orWhere('companycode', 'like', "%{$search}%");
            });
        }

        $result = $query
            ->orderBy('kodeharga', 'asc')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage, 'search' => $search]);

        return view('settings.hargapanen.index', [
            'result'      => $result,
            'search'      => $search,
            'perPage'     => $perPage,
            'title'       => 'Harga Panen',
            'navbar'      => 'Settings',
            'nav'         => 'Harga Panen',
            'companycode' => $companycode,
        ]);
    }

    public function store(Request $request)
    {
        $companycode = session('companycode');

        // Auto-generate kodeharga: {companycode}-{nomor urut 4 digit}
        $last = DB::table('hargapanentebu')
            ->where('companycode', $companycode)
            ->orderByRaw('kodeharga DESC')
            ->value('kodeharga');

        $nextNum = 1;
        if ($last) {
            // ambil bagian belakang setelah tanda '-' terakhir
            $parts   = explode('-', $last);
            $nextNum = ((int) end($parts)) + 1;
        }
        $kodeharga = $companycode . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        $user = Auth::user()->userid;

        $userdata = User::where('userid', $user)->firstOrFail();
        DB::table('hargapanentebu')->insert([
            'kodeharga'            => $kodeharga,
            'companycode'          => $companycode,
            'periode'              => $request->periode,
            'active'               => $request->has('active') ? 1 : 0,
            'manualtebang'         => $request->manualtebang ?: null,
            'manualmuat'           => $request->manualmuat ?: null,
            'manualangkutan'       => $request->manualangkutan ?: null,
            'manualfeekont'        => $request->manualfeekont ?: null,
            'manualnonpremi'       => $request->manualnonpremi ?: null,
            'manualbsm'            => $request->manualbsm ?: null,
            'manualtebusulit'      => $request->manualtebusulit ?: null,
            'manualpremiton'       => $request->manualpremiton ?: null,
            'glkebuntebang'        => $request->glkebuntebang ?: null,
            'glkebunmuat'          => $request->glkebunmuat ?: null,
            'glkebunangkutan'      => $request->glkebunangkutan ?: null,
            'glkebunfeekont'       => $request->glkebunfeekont ?: null,
            'glkebunnonpremi'      => $request->glkebunnonpremi ?: null,
            'glkebunbsm'           => $request->glkebunbsm ?: null,
            'glkebuntebusulit'     => $request->glkebuntebusulit ?: null,
            'glkebunpremiton'      => $request->glkebunpremiton ?: null,
            'glkontraktortebang'   => $request->glkontraktortebang ?: null,
            'glkontraktormuat'     => $request->glkontraktormuat ?: null,
            'glkontraktorangkutan' => $request->glkontraktorangkutan ?: null,
            'glkontraktorfeekont'  => $request->glkontraktorfeekont ?: null,
            'glkontraktornonpremi' => $request->glkontraktornonpremi ?: null,
            'glkontraktorbsm'      => $request->glkontraktorbsm ?: null,
            'glkontraktortebusulit' => $request->glkontraktortebusulit ?: null,
            'glkontraktorpremiton' => $request->glkontraktorpremiton ?: null,
            'extrafooding'         => $request->extrafooding ?: null,
            'tebutdkseset'         => $request->tebutdkseset ?: null,
            'langsir'              => $request->langsir ?: null,
            'createdby'            => $user,
            'createddate'        => now(),  
        ]);

        return redirect()->route('settings.harga-panen.index')
            ->with('success', "Harga Panen {$kodeharga} berhasil ditambahkan.");
    }

    public function update(Request $request, $id)
    {
        $companycode = session('companycode');

        DB::table('hargapanentebu')
            ->where('kodeharga', $id)->where('companycode', $companycode)
            ->update([
                'periode'              => $request->periode,
                'active'               => $request->has('active') ? 1 : 0,
                'manualtebang'         => $request->manualtebang ?: null,
                'manualmuat'           => $request->manualmuat ?: null,
                'manualangkutan'       => $request->manualangkutan ?: null,
                'manualfeekont'        => $request->manualfeekont ?: null,
                'manualnonpremi'       => $request->manualnonpremi ?: null,
                'manualbsm'            => $request->manualbsm ?: null,
                'manualtebusulit'      => $request->manualtebusulit ?: null,
                'manualpremiton'       => $request->manualpremiton ?: null,
                'glkebuntebang'        => $request->glkebuntebang ?: null,
                'glkebunmuat'          => $request->glkebunmuat ?: null,
                'glkebunangkutan'      => $request->glkebunangkutan ?: null,
                'glkebunfeekont'       => $request->glkebunfeekont ?: null,
                'glkebunnonpremi'      => $request->glkebunnonpremi ?: null,
                'glkebunbsm'           => $request->glkebunbsm ?: null,
                'glkebuntebusulit'     => $request->glkebuntebusulit ?: null,
                'glkebunpremiton'      => $request->glkebunpremiton ?: null,
                'glkontraktortebang'   => $request->glkontraktortebang ?: null,
                'glkontraktormuat'     => $request->glkontraktormuat ?: null,
                'glkontraktorangkutan' => $request->glkontraktorangkutan ?: null,
                'glkontraktorfeekont'  => $request->glkontraktorfeekont ?: null,
                'glkontraktornonpremi' => $request->glkontraktornonpremi ?: null,
                'glkontraktorbsm'      => $request->glkontraktorbsm ?: null,
                'glkontraktortebusulit' => $request->glkontraktortebusulit ?: null,
                'glkontraktorpremiton' => $request->glkontraktorpremiton ?: null,
                'extrafooding'         => $request->extrafooding ?: null,
                'tebutdkseset'         => $request->tebutdkseset ?: null,
                'langsir'              => $request->langsir ?: null,
                'updateby'            => Auth::user()->userid,
                'updateddate'          => now(),
            ]);

        return redirect()->route('settings.harga-panen.index')
            ->with('success', "Harga Panen {$id} berhasil diperbarui.");
    }

    public function destroy($id)
    {
        DB::table('hargapanentebu')->where('kodeharga', $id)->delete();

        return redirect()->route('settings.harga-panen.index')
            ->with('success', "Harga Panen {$id} berhasil dihapus.");
    }
}
