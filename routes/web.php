<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index'])->middleware('auth');
Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/log-login', [AuthController::class, 'logsLogin']);
Route::get('/logout', function () {
    return view('logout');
})->middleware('auth');
Route::post('/log-logout', [AuthController::class, 'logsLogout']);

Route::get('/email-verified', function () {
    return view('email_verified');
});
