<?php

use App\Http\Controllers\ClientApp\Api\v1\LoginController;

/* Checking OTP code */
Route::post('/active-user', [LoginController::class,'activeUser']);
