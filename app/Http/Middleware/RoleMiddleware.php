<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        // Cek jika pengguna belum login atau perannya tidak sesuai
        if (!Auth::check() || Auth::user()->roles !== $role) {
            // Jika tidak sesuai, kembalikan ke halaman sebelumnya atau beri error 403 (Forbidden)
            abort(403, 'AKSES DITOLAK: ANDA TIDAK MEMILIKI HAK UNTUK MENGAKSES HALAMAN INI.');
        }

        return $next($request);
    }
}
