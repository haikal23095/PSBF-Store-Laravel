<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Menampilkan view untuk form login.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        // Mengembalikan view yang berada di resources/views/auth/login.blade.php
        return view('auth.login');
    }

    /**
     * Menangani permintaan login dari pengguna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Pengecekan peran dan pengalihan (redirect)
            if ($user->roles === 'penjual') {
                return redirect()->intended(route('penjual.dashboard'));
            } elseif ($user->roles === 'pembeli') {
                return redirect()->intended(route('pembeli.store'));
            }

            // Fallback jika peran tidak terdefinisi
            return redirect('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Menangani permintaan login dari pengguna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

}
