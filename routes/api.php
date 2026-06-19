<?php

use App\Http\Controllers\Api\ExpertApiController;
use Illuminate\Support\Facades\Route;

// ────────────────────────────────────────────────────────────
// PUBLIC API — tanpa auth (untuk keperluan Postman demo)
// ────────────────────────────────────────────────────────────

// GET /api/categories — daftar semua kategori
Route::get('/categories', [ExpertApiController::class, 'categories']);

// GET /api/experts     — daftar semua expert approved
Route::get('/experts', [ExpertApiController::class, 'index']);

// GET /api/experts/{id} — detail satu expert
Route::get('/experts/{id}', [ExpertApiController::class, 'show']);
