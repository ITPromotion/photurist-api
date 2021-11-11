<?php

use App\Http\Controllers\ClientApp\Api\v1\LoginController;
use App\Http\Controllers\ClientApp\Api\v1\PostcardController as PostcardControllerAlias;
use App\Http\Controllers\ClientApp\Api\v1\ProfileController;
use App\Http\Controllers\ClientApp\Api\v1\UserController;

/* Checking OTP code */
Route::post('/active-user', [LoginController::class,'activeUser']);

/* Get profile */
Route::get('/get-profile', [ProfileController::class,'getProfile']);

/* Resource Api */
Route::apiResources([
    'postcard' => PostcardController::class,
]);

/* Postcard update */
Route::put('/postcard-update/{id}', [PostcardControllerAlias::class,'update']);

/* Save media */
Route::post('/save-media', [PostcardControllerAlias::class,'saveMedia']);

/* Remove media */
Route::put('/remove-media/{id}', [PostcardControllerAlias::class,'removeMedia']);

/* Remove audio */
Route::put('/remove-audio/{id}', [PostcardControllerAlias::class,'removeAudio']);

/* Get gallery */
Route::get('/get-gallery', [PostcardControllerAlias::class,'getGallery']);

/* Add postcard to gallery */
Route::post('/add-postcard-to-gallery', [PostcardControllerAlias::class,'addPostcardToGallery']);

/* Save audio */
Route::post('/save-audio', [PostcardControllerAlias::class,'saveAudio']);

/* Set geo data */
Route::post('/set-geo-data', [UserController::class,'setGeoData']);

/* Save device */

Route::post('/add-device', [UserController::class, 'saveDevice'])->name('saveDevice');

/* Delete device */

Route::delete('/delete-device', [UserController::class, 'deleteDevice'])->name('deleteDevice');


/* Remove postcard from list */

Route::delete('/remove-postcard-from-list/{id}', [PostcardControllerAlias::class, 'removePostcardFromList']);
