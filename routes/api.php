<?php

use App\Http\Controllers\MonoController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('user', [UserController::class, 'getUser']);

Route::group(['prefix' => 'mono'], function () {
    Route::post('/initiate', [MonoController::class, 'initiateBvn']);
    Route::post('/verify', [MonoController::class, 'verifyBvnOtp']);
    Route::post('/details', [MonoController::class, 'getBvnDetails']);
});
