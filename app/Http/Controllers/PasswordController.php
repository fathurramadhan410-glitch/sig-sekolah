<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PasswordController extends Controller
{
    public function edit()
    {
        return view('auth.ubah-sandi');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed',
                                   'different:current_password'],
        ], [
            'current_password.current_password' => 'Kata sandi lama salah.',
            'password.different' => 'Kata sandi baru harus berbeda dari yang lama.',
        ]);

        // Cast "hashed" pada model User meng-hash otomatis
        $request->user()->update(['password' => $request->password]);

        return back()->with('success', 'Kata sandi berhasil diubah.');
    }
}