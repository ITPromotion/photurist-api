<?php
use App\Http\Controllers\AdminPanel\Api\v1\ProfileController;

Route::get('/get-profile', [ProfileController::class,'getProfile']);

