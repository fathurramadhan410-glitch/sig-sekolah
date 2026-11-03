<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Hanya akun aktif yang boleh login
        if (Auth::attempt(
            $credentials + ['is_active' => true],
            $request->boolean('remember')
        )) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        // Pesan khusus hanya jika kata sandi benar tetapi akun nonaktif
        $user = User::where('email', $credentials['email'])->first();
        if ($user && ! $user->is_active
            && Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'Akun Anda dinonaktifkan. Hubungi admin.'])
                ->onlyInput('email');
        }

        return back()
            ->withErrors(['email' => 'Email atau kata sandi salah.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('peta.index');
    }
}