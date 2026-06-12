<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ExpertProfile;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ── SHOW LOGIN ──────────────────────────────────────────
    public function showLogin()
    {
        // kalau sudah login, langsung redirect ke dashboard
        if (Auth::check()) {
            return $this->redirectToDashboard(auth()->user());
        }
        return view('auth.login');
    }

    // ── PROSES LOGIN ────────────────────────────────────────
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->withInput($request->only('email'));
        }

        $user = Auth::user();

        // cek akun suspended
        if ($user->status === 'suspended') {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Akun kamu telah disuspend. Hubungi admin.',
            ]);
        }

        $request->session()->regenerate();

        return $this->redirectToDashboard($user);
    }

    // ── SHOW REGISTER ───────────────────────────────────────
    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard(Auth::user());
        }
        return view('auth.register');
    }

    // ── PROSES REGISTER ─────────────────────────────────────
    public function register(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:100',
            'username'  => 'required|string|max:50|unique:users,username|alpha_dash',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8|confirmed',
            'role'      => 'required|in:client,expert',
        ], [
            'username.unique'    => 'Username sudah dipakai.',
            'username.alpha_dash'=> 'Username hanya boleh huruf, angka, dan underscore.',
            'email.unique'       => 'Email sudah terdaftar.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.in'            => 'Pilih role yang valid.',
        ]);

        DB::transaction(function () use ($request) {
            // 1. buat user
            $user = User::create([
                'username' => $request->username,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => $request->role,
                'status'   => 'active',
            ]);

            // 2. buat profil
            UserProfile::create([
                'user_id' => $user->id,
                'name'    => $request->name,
            ]);

            // 3. buat wallet dengan saldo awal
            Wallet::create([
                'user_id' => $user->id,
                'balance' => $request->role === 'client' ? 100000 : 0, // client dapat saldo awal 100rb
            ]);

            // 4. kalau expert, buat profil expert kosong dulu
            if ($request->role === 'expert') {
                ExpertProfile::create([
                    'user_id'             => $user->id,
                    'category_id'         => 1, // default, akan dilengkapi saat onboarding
                    'hourly_rate'         => 0,
                    'verification_status' => 'pending',
                ]);
            }

            Auth::login($user);
        });

        $user = Auth::user();

        // expert baru diarahkan ke halaman lengkapi profil
        if ($user->role === 'expert') {
            return redirect()->route('expert.profile.edit')
                ->with('success', 'Akun berhasil dibuat! Lengkapi profil kamu untuk mulai menerima pesanan.');
        }

        return $this->redirectToDashboard($user)
            ->with('success', 'Selamat datang, ' . $user->profile->name . '!');
    }

    // ── LOGOUT ──────────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Kamu berhasil logout.');
    }

    // ── HELPER: redirect berdasarkan role ───────────────────
    private function redirectToDashboard(User $user)
    {
        return redirect()->intended(match($user->role) {
            'admin'  => route('admin.dashboard'),
            'expert' => route('expert.dashboard'),
            default  => route('client.dashboard'),
        });
    }
}