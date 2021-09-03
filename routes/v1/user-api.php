<?php

use App\Http\Controllers\ClientApp\Api\v1\LoginController;
use App\Http\Controllers\ClientApp\Api\v1\PostcardController as PostcardControllerAlias;
use App\Http\Controllers\ClientApp\Api\v1\ProfileController;

/* Checking OTP code */
Route::post('/active-user', [LoginController::class,'activeUser']);

/* Get profile */
Route::get('/get-profile', [ProfileController::class,'getProfile']);

/* Resource Api */
Route::apiResources([
    'postcard' => PostcardController::class,
]);

/* Save media */
Route::post('/save-media', [PostcardControllerAlias::class,'saveMedia']);

/* Save audio */
Route::post('/save-audio', [PostcardControllerAlias::class,'saveAudio']);
