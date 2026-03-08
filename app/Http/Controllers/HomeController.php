<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Navigation data sudah otomatis tersedia dari NavigationComposer
        // $navigationMenus, $allSubmenus, $userPermissions, $companyName, $user, $userCompanies
        
        $showPopup = !$request->session()->has('companycode');
        $now = Carbon::now()->toDateString();

        return view('home', [
            'title' => 'Home',
            'navbar' => '',
            'now' => $now,
            'showPopup' => $showPopup
        ]);
    }

    public function setSession(Request $request)
    {
        $request->validate([
            'dropdown_value' => 'required|string',
        ]);

        session(['companycode' => $request->dropdown_value]);
        
        // Set company name ke session
        $companyName = DB::connection('mariadb')->table('company')
            ->where('companycode', $request->dropdown_value)
            ->value('name');
        
        if ($companyName) {
            session(['companyname' => $companyName]);
        }

        return url()->previous() ? redirect()->back() : redirect()->route('home');
    }
}