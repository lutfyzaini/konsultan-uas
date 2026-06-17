<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Client\BookingController;
use App\Http\Controllers\Client\ExpertController;
use App\Http\Controllers\HomeController;
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

// ── HOMEPAGE & PUBLIC ROUTES ─────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');

// Halaman publik — katalog & detail expert (bisa diakses tanpa login)
Route::get('/experts', [ExpertController::class, 'index'])->name('experts.index');
Route::get('/experts/{id}', [ExpertController::class, 'show'])->name('experts.show');

// ── CLIENT ROUTES ────────────────────────────────────────────────
Route::middleware(['auth', 'role:client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', fn() => view('client.dashboard'))->name('dashboard');

    // ── BOOKING FLOW ──
    Route::get('/booking',                 [BookingController::class, 'index'])->name('booking.index');
    Route::post('/booking',                [BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking/{id}/payment',    [BookingController::class, 'payment'])->name('booking.payment');
    Route::post('/booking/{id}/pay',       [BookingController::class, 'pay'])->name('booking.pay');
    Route::post('/booking/{id}/cancel',    [BookingController::class, 'cancel'])->name('booking.cancel');
    Route::get('/booking/{id}',            [BookingController::class, 'show'])->name('booking.show');
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