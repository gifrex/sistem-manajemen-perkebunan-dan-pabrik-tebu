<?php

namespace App\Http\Controllers\Process;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\LengthAwarePaginator;

class UnpostController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Process',
            'nav' => 'Unposting',
        ]);
    }

    public function index(Request $request)
    {
        $title = "Unposting";
        $search = $request->input('search', '');
        $unposting = $request->input('unposting', '');

        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $userid = Auth::user()->userid;
        $companycode = DB::table('usercompany')
            ->where('userid', $userid)
            ->value('companycode');
        $companyArray = $companycode ? explode(',', $companycode) : [];

        if ($request->isMethod('post') && $request->has('perPage')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);

            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);
        $companysession = session('companycode');

        // Jika belum memilih jenis pengamatan, return dengan data kosong
        if (empty($unposting)) {
            $unposts = new LengthAwarePaginator([], 0, $perPage, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
            return view('process.unposting.index', compact('unposts', 'perPage', 'startDate', 'endDate', 'title', 'search', 'unposting'));
        }

        // Tentukan tabel berdasarkan input unposting
        $table = $unposting === 'Agronomi' ? 'agrohdr' : 'hpthdr';

        $unposts = DB::table($table)
            ->orderBy('createdat', 'desc')
            ->where('companycode', '=', $companysession)
            ->where('status', '=', 'Posted')
            ->when($startDate, fn($query) => $query->whereDate('tanggalpengamatan', '>=', $startDate))
            ->when($endDate, fn($query) => $query->whereDate('tanggalpengamatan', '<=', $endDate));

        if (!empty($search)) {
            $unposts->where(function ($query) use ($search) {
                $query->where('nosample', 'like', '%' . $search . '%')
                    ->orWhere('varietas', 'like', '%' . $search . '%')
                    ->orWhere('kat', 'like', '%' . $search . '%');
            });
        }

        $unposts = $unposts->paginate($perPage);

        foreach ($unposts as $index => $item) {
            if (!empty($item->tanggaltanam)) {
                $item->umur_tanam = Carbon::parse($item->tanggaltanam)->diffInMonths(Carbon::now());
            } else {
                $item->umur_tanam = null;
            }

            $item->no = ($unposts->currentPage() - 1) * $unposts->perPage() + $index + 1;
        }

        if ($request->ajax()) {
            return view('process.unposting.index', compact('unposts', 'perPage', 'startDate', 'endDate', 'title', 'search', 'unposting'));
        }

        return view('process.unposting.index', compact('unposts', 'perPage', 'startDate', 'endDate', 'title', 'search', 'unposting'));
    }

    public function unposting(Request $request)
    {
        $selectedItems = json_decode($request->selected_items, true);
        $unposting = $request->input('unposting_type'); // Ambil dari hidden input

        $selectedItems = array_map(function ($item) {
            $parts = explode(',', $item);
            return [
                'nosample' => $parts[0] ?? null,
                'companycode' => $parts[1] ?? null,
                'tanggalpengamatan' => $parts[2] ?? null,
            ];
        }, $selectedItems);

        if (!$selectedItems) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        $tables = $unposting === 'Agronomi'
            ? ['agrohdr', 'agrolst']
            : ['hpthdr', 'hptlst'];

        foreach ($tables as $table) {
            DB::table($table)
                ->whereIn('nosample', array_column($selectedItems, 'nosample'))
                ->whereIn('companycode', array_column($selectedItems, 'companycode'))
                ->whereIn('tanggalpengamatan', array_column($selectedItems, 'tanggalpengamatan'))
                ->update(['status' => 'Unposted']);
        }

        return redirect()->back()->with('success1', 'Data telah di unposting.');
    }
}