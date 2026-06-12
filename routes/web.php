<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ── AUTH (guest only) ────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ── ROOT REDIRECT ────────────────────────────────────────────────
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route(match (Auth::user()->role) {
            'admin'  => 'admin.dashboard',
            'expert' => 'expert.dashboard',
            default  => 'client.dashboard',
        });
    }
    return redirect()->route('login');
});

// ── CLIENT ROUTES ────────────────────────────────────────────────
Route::middleware(['auth', 'role:client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', fn() => view('client.dashboard'))->name('dashboard');
    // nanti ditambah: discovery, booking, payment, chat, review
});

// ── EXPERT ROUTES ────────────────────────────────────────────────
Route::middleware(['auth', 'role:expert'])->prefix('expert')->name('expert.')->group(function () {
    Route::get('/dashboard', fn() => view('expert.dashboard'))->name('dashboard');
    Route::get('/profile/edit', fn() => view('expert.profile.edit'))->name('profile.edit');
    // nanti ditambah: slots, consultation, finance
});

// ── ADMIN ROUTES ─────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
    // nanti ditambah: verification, user management, finance
});