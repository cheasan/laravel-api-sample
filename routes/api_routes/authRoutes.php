<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'createUser']);

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])
->name('verification.verify')
->middleware('signed');

// protected by Sanctum

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('logout', [AuthController::class, 'logoutUser']);
});
