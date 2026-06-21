<?php

use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Client\BookingController;
use App\Http\Controllers\Client\ExpertController;
use App\Http\Controllers\Client\InstantConsultationController;
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
    Route::get('/booking/{id}/room',       [BookingController::class, 'room'])->name('booking.room');
    Route::post('/booking/{id}/message',   [BookingController::class, 'sendMessage'])->name('booking.message');
    Route::get('/booking/{id}/status',     [BookingController::class, 'checkStatus'])->name('booking.status');
    Route::get('/booking/{id}',            [BookingController::class, 'show'])->name('booking.show');
    Route::post('/booking/{id}/review',    [BookingController::class, 'storeReview'])->name('booking.review');

    // ── INSTANT CONSULTATION FLOW ──
    Route::post('/instant/{expertId}',      [InstantConsultationController::class, 'create'])->name('instant.create');
    Route::get('/instant/{id}/payment',     [InstantConsultationController::class, 'payment'])->name('instant.payment');
    Route::post('/instant/{id}/pay',        [InstantConsultationController::class, 'pay'])->name('instant.pay');
    Route::get('/instant/{id}/room',        [InstantConsultationController::class, 'room'])->name('instant.room');
    Route::post('/instant/{id}/message',    [InstantConsultationController::class, 'sendMessage'])->name('instant.message');
    Route::get('/instant/{id}/result',      [InstantConsultationController::class, 'result'])->name('instant.result');
    Route::get('/instant/{id}/status',      [InstantConsultationController::class, 'checkStatus'])->name('instant.status');
});

// ── EXPERT ROUTES ────────────────────────────────────────────────
Route::middleware(['auth', 'role:expert'])->prefix('expert')->name('expert.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Expert\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/toggle-online', [App\Http\Controllers\Expert\DashboardController::class, 'toggleOnline'])->name('toggle-online');

    Route::get('/profile/edit', [App\Http\Controllers\Expert\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [App\Http\Controllers\Expert\ProfileController::class, 'update'])->name('profile.update');

    Route::get('/slots', [App\Http\Controllers\Expert\SlotController::class, 'index'])->name('slots.index');
    Route::post('/slots', [App\Http\Controllers\Expert\SlotController::class, 'store'])->name('slots.store');
    Route::delete('/slots/{id}', [App\Http\Controllers\Expert\SlotController::class, 'destroy'])->name('slots.destroy');

    Route::get('/withdrawals', [App\Http\Controllers\Expert\WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('/withdrawals', [App\Http\Controllers\Expert\WithdrawalController::class, 'store'])->name('withdrawals.store');

    Route::get('/consultation/{id}/room', [App\Http\Controllers\Expert\ConsultationController::class, 'room'])->name('consultation.room');
    Route::post('/consultation/{id}/message', [App\Http\Controllers\Expert\ConsultationController::class, 'sendMessage'])->name('consultation.message');
    Route::get('/consultation/{id}/status', [App\Http\Controllers\Expert\ConsultationController::class, 'checkStatus'])->name('consultation.status');
    Route::post('/consultation/{id}/end', [App\Http\Controllers\Expert\ConsultationController::class, 'endSession'])->name('consultation.end');
});

// ── ADMIN ROUTES ─────────────────────────────────────────────────
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\PlatformSettingsController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
    Route::resource('categories', CategoryController::class);
    Route::resource('skills', SkillController::class);
    Route::get('/verifications',[VerificationController::class, 'index'])->name('verifications.index');
    Route::post('/verifications/{expert}/approve',[VerificationController::class, 'approve'])->name('verifications.approve');
    Route::post('/verifications/{expert}/reject',[VerificationController::class, 'reject'])->name('verifications.reject');
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/toggle-status',[UserManagementController::class, 'toggleStatus'])->name('users.toggle');
    Route::get('/payments',[PaymentController::class,'index'])->name('payments.index');
    Route::get('/bookings',[AdminBookingController::class,'index'])->name('bookings.index');
    Route::get('/settings', [PlatformSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [PlatformSettingsController::class, 'update'])->name('settings.update');
    Route::get('/withdrawals', [\App\Http\Controllers\Admin\WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('/withdrawals/{id}/approve', [\App\Http\Controllers\Admin\WithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('/withdrawals/{id}/reject', [\App\Http\Controllers\Admin\WithdrawalController::class, 'reject'])->name('withdrawals.reject');
    // nanti ditambah: verification, user management, finance
});