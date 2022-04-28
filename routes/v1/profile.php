<?php
use App\Http\Controllers\ClientApp\Api\v1\ProfileController;

/* User save media */
Route::post('/user-save-media', [ProfileController::class,'userSaveMedia']);

/* User save audio */
Route::post('/user-save-audio', [ProfileController::class,'userSaveAudio']);

/* Get profile */
Route::get('/get-profile', [ProfileController::class,'getProfile']);
