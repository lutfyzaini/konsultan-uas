<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    // Menampilkan daftar expert beserta profil dan kategorinya
    $experts = DB::table('expert_profiles')
                ->join('users', 'users.id', '=', 'expert_profiles.user_id')
                ->join('categories', 'categories.id', '=', 'expert_profiles.category_id')
                ->select('users.username', 'expert_profiles.title', 'categories.name as category')
                ->get();

    return response()->json($experts);
});

