<?php

use App\Http\Controllers\ClientApp\Api\v1\LoginController;
use App\Http\Controllers\ClientApp\Api\v1\PostcardController as PostcardControllerAlias;
use App\Http\Controllers\ClientApp\Api\v1\ProfileController;
use App\Http\Controllers\ClientApp\Api\v1\UserController;
use Illuminate\Http\Request;
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

Route::post('/add-favorites', [PostcardControllerAlias::class, 'addFavorite']);

Route::delete('/delete-favorites', [PostcardControllerAlias::class, 'deleteFavorite']);

Route::put('/check-contacts', [UserController::class, 'checkContacts']);

Route::post('/add-contacts', [UserController::class, 'addContactsActive']);

Route::post('/get-contacts', [UserController::class, 'getContactsActive']);


/* Remove postcard from list */

Route::delete('/remove-postcard-from-list/{id}', [PostcardControllerAlias::class, 'removePostcardFromList']);


/* set status */

Route::put('/set-status-postcard/{id}', [PostcardControllerAlias::class, 'setStatusPostcard']);

/* get postcards from ids */

Route::post('/get-postcards-from-ids', [PostcardControllerAlias::class, 'getPostcardFromIds']);

/* stop mailings */

Route::put('/stop-mailings/{id}', [PostcardControllerAlias::class, 'stopMailings']);

/* set view postcard mailings */

Route::put('/set-view/{id}', [PostcardControllerAlias::class, 'setView']);

/* delete postcards */

Route::put('/delete-postcard/{id}', [PostcardControllerAlias::class, 'deletePostcard']);

/* off user postcard notification */

Route::put('/off-user-postcard-notification/{id}', [PostcardControllerAlias::class, 'offUserPostcardNotification']);

/* off user postcard notification */

Route::put('/on-user-postcard-notification/{id}', [PostcardControllerAlias::class, 'onUserPostcardNotification']);

/* not view quantity */

Route::get('/not-view-quantity', [PostcardControllerAlias::class, 'notViewQuantity']);

Route::get('/duplicate-postcard/{id}', [PostcardControllerAlias::class, 'duplicate']);


Route::post('/test-push', function (Request $request) {
    return (new \App\Services\NotificationService)->send([
        'users' => [$request->fcm],
        'title' => 'test-push',
        'body' => 'test-push',
        'img' => 'https://dev.photurist.com/storage/postcard/43/image/183x183/gjyatbtWy87xN7LHvGCcCcmr7pwOh1BKuhCisdzD.jpg',
        'postcard_id' => $request->id,
        'action_loc_key' => 'test-push',
        'badge' => \Illuminate\Support\Facades\DB::table('postcards_mailings')
                                    ->where('view', 0)
                                    ->where('user_id',\Auth::id())
                                    ->where('status', \App\Enums\PostcardStatus::ACTIVE)
                                    ->count()
    ]);
});
