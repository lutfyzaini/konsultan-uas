<?php

use App\Http\Controllers\Api\ExpertApiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\SlotApiController;
use App\Http\Controllers\Api\ConsultationApiController;

// ────────────────────────────────────────────────────────────
// PUBLIC API — tanpa auth (untuk keperluan Postman demo)
// ────────────────────────────────────────────────────────────

// GET /api/categories — daftar semua kategori
Route::get('/categories', [ExpertApiController::class, 'categories']);

// GET /api/experts     — daftar semua expert approved
Route::get('/experts', [ExpertApiController::class, 'index']);

// GET /api/experts/{id} — detail satu expert
Route::get('/experts/{id}', [ExpertApiController::class, 'show']);

// Booking
Route::get('/bookings', [BookingApiController::class, 'index']);
Route::post('/bookings', [BookingApiController::class, 'store']);
Route::get('/bookings/{id}', [BookingApiController::class, 'show']);
Route::post('/bookings/{id}/cancel', [BookingApiController::class, 'cancel']);

// Slot
Route::get('/slots', [SlotApiController::class, 'index']);
Route::post('/slots', [SlotApiController::class, 'store']);
Route::delete('/slots/{id}', [SlotApiController::class, 'destroy']);

// Consultation
Route::get('/consultations/{id}', [ConsultationApiController::class, 'show']);
Route::post('/consultations/{id}/message', [ConsultationApiController::class, 'sendMessage']);
Route::get('/consultations/{id}/status', [ConsultationApiController::class, 'status']);
Route::post('/consultations/{id}/end', [ConsultationApiController::class, 'endSession']);