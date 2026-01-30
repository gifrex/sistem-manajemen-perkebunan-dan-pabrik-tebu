<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use App\Models\MasterData\MasterList;
use App\Models\MasterData\Batch;

class MasterListController extends Controller
{
    
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');
        $companycode = Session::get('companycode');
    
        $query = MasterList::where('companycode', $companycode)
            ->with('activeBatch'); // Eager load active batch relationship
    
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('plot', 'like', "%{$search}%")
                  ->orWhere('blok', 'like', "%{$search}%")
                  ->orWhere('activebatchno', 'like', "%{$search}%");
            });
        }

        $masterlist = $query
            ->orderBy('plot')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search'  => $search,
            ]);
    
        return view('masterdata.masterlist.index', [
            'masterlist' => $masterlist,
            'title'     => 'Data Master List',
            'navbar'    => 'Master',
            'nav'       => 'Master List',
            'perPage'   => $perPage,
            'search'    => $search,
        ]);
    }

    public function store(Request $request)
    {
        $companycode = Session::get('companycode');
        
        $request->validate([
            'plot' => 'required|string|max:5',
            'blok' => 'nullable|string|max:2',
            'activebatchno' => 'nullable|string|max:20',
        ]);

        $exists = MasterList::where('companycode', $companycode)
            ->where('plot', $request->plot)
            ->exists();
            
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'plot' => 'Duplicate Entry, Plot already exists'
                ]);
        }

        // Validate activebatchno if provided
        if ($request->activebatchno) {
            $batchExists = Batch::where('batchno', $request->activebatchno)
                ->where('companycode', $companycode)
                ->exists();
            
            if (!$batchExists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'activebatchno' => 'Batch number does not exist'
                    ]);
            }
        }

        MasterList::create([
            'companycode' => $companycode,
            'plot' => $request->input('plot'),
            'blok' => $request->input('blok'),
            'activebatchno' => $request->input('activebatchno'),
            'isactive' => 1,
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, $companycode, $plot)
    {   
        $sessionCompanycode = Session::get('companycode');
        
        // Security check - ensure user can only edit their company data
        if ($companycode !== $sessionCompanycode) {
            abort(403, 'Unauthorized access to company data');
        }
        
        $masterlist = MasterList::where([
            ['companycode', $companycode],
            ['plot', $plot]
        ])->firstOrFail();

        $validated = $request->validate([
            'plot' => 'required|string|max:5',
            'blok' => 'nullable|string|max:2',
            'activebatchno' => 'nullable|string|max:20',
            'isactive' => 'required|boolean',
        ]);
        
        // Check duplicate if plot changed
        if ($request->plot !== $masterlist->plot) {
            $exists = MasterList::where('companycode', $companycode)
                ->where('plot', $request->plot)
                ->exists();
    
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'plot' => 'Duplicate Entry, Plot already exists'
                    ]);
            }
        }

        // Validate activebatchno if provided
        if ($request->activebatchno) {
            $batchExists = Batch::where('batchno', $request->activebatchno)
                ->where('companycode', $companycode)
                ->where('plot', $request->plot)
                ->exists();
            
            if (!$batchExists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'activebatchno' => 'Batch number does not exist for this plot'
                    ]);
            }
        }
        
        MasterList::where('companycode', $companycode)
             ->where('plot', $plot)
             ->update([
                'plot' => $validated['plot'],
                'blok' => $validated['blok'],
                'activebatchno' => $validated['activebatchno'],
                'isactive' => $validated['isactive'],
             ]);
    
        return redirect()->back()->with('success', 'Data berhasil di‑update.');
    }

    public function destroy(Request $request, $companycode, $plot)
    {
        $sessionCompanycode = Session::get('companycode');
        
        // Security check - ensure user can only delete their company data
        if ($companycode !== $sessionCompanycode) {
            abort(403, 'Unauthorized access to company data');
        }

        MasterList::where([
            ['companycode', $companycode],
            ['plot', $plot]
        ])->delete();

        return redirect()->back()->with('success','Data berhasil di‑hapus.');
    }
}