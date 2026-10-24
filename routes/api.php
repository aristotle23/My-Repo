<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('user', [UserController::class, 'getUser']);

Route::group(['prefix' => 'mono'], function () {
    Route::post('/initiate', '@initiateBvn');
    Route::post('/verify', '@verifyBvnOtp');
    Route::post('/details', '@getBvnDetails');
});
