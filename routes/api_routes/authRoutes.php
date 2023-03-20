<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'createUser']);

Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyRegisteredEmail'])
    ->name('verification.verify')
    ->middleware('signed');

Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
Route::post('verify-pin', [ForgotPasswordController::class, 'verifyPin']);
Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('logout', [AuthController::class, 'logoutUser']);
    Route::post('reset-logged-in-password', [AuthController::class, 'resetLoggedInPassword']);
});
