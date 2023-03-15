<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('/auth')->group(__DIR__ . '/api_routes/authRoutes.php');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
