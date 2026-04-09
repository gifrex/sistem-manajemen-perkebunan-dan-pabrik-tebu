<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function show()
    {
        return view('change-password', [
            'title'  => 'Ganti Password',
            'navbar' => 'Akun',
            'nav'    => 'Ganti Password',
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password'          => 'required|string',
            'new_password'              => ['required', 'string', 'min:6', 'confirmed', 'regex:/^(?=.*[a-zA-Z])(?=.*[0-9]).+$/'],
            'new_password_confirmation' => 'required|string',
        ], [
            'current_password.required'          => 'Password lama harus diisi.',
            'new_password.required'              => 'Password baru harus diisi.',
            'new_password.min'                   => 'Password baru minimal 6 karakter.',
            'new_password.regex'                 => 'Password baru harus mengandung minimal 1 huruf dan 1 angka.',
            'new_password.confirmed'             => 'Konfirmasi password baru tidak cocok.',
            'new_password_confirmation.required' => 'Konfirmasi password baru harus diisi.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.'])->withInput();
        }

        if ($request->input('current_password') === $request->input('new_password')) {
            return back()->withErrors(['new_password' => 'Password baru tidak boleh sama dengan password lama.'])->withInput();
        }

        DB::table('user')
            ->where('userid', $user->userid)
            ->update([
                'password'  => Hash::make($request->input('new_password')),
                'updatedat' => now()->toDateString(),
            ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
