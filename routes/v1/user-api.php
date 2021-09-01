<?php

use App\Http\Controllers\ClientApp\Api\v1\LoginController;
use App\Http\Controllers\ClientApp\Api\v1\ProfileController;

/* Checking OTP code */
Route::post('/active-user', [LoginController::class,'activeUser']);

/* Get profile */
Route::get('/get-profile', [ProfileController::class,'getProfile']);
