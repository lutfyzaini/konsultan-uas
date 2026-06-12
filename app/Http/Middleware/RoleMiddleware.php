<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Cara pakai di routes/web.php:
     *   ->middleware('role:admin')
     *   ->middleware('role:expert')
     *   ->middleware('role:client')
     *   ->middleware('role:admin,expert')  ← bisa lebih dari satu role
     */
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        // belum login
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // akun disuspend
        if ($user->status === 'suspended') {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Akun kamu telah disuspend. Hubungi admin.']);
        }

        // role tidak sesuai
        if (! in_array($user->role, $roles)) {
            abort(403, 'Kamu tidak punya akses ke halaman ini.');
        }

        return $next($request);
    }
}