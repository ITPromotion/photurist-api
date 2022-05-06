<?php

use App\Http\Controllers\ClientApp\Api\v1\LoginController;
use App\Http\Controllers\ClientApp\Api\v1\PostcardController as PostcardControllerAlias;
use App\Http\Controllers\ClientApp\Api\v1\UserController;
use Illuminate\Http\Request;
/* Checking OTP code */
Route::post('/active-user', [LoginController::class,'activeUser']);



/* Save media */
Route::post('/save-media', [PostcardControllerAlias::class,'saveMedia']);

/* Remove media */
Route::put('/remove-media/{id}', [PostcardControllerAlias::class,'removeMedia']);

/* Remove audio */
Route::put('/remove-audio/{id}', [PostcardControllerAlias::class,'removeAudio']);

/* Get gallery */
Route::get('/get-gallery', [PostcardControllerAlias::class,'getGallery']);


/* Resend postcard */
Route::post('/postcard-resend', [PostcardControllerAlias::class,'postcardResend']);



Route::group(['middleware' => 'block_user'], function () {
    /* Resource Api */
    Route::apiResources([
        'postcard' => PostcardController::class,
    ]);

    /* Postcard update */
    Route::put('/postcard-update/{id}', [PostcardControllerAlias::class,'update']);
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

    Route::get('/get-contacts', [UserController::class, 'getContactsActive']);

    Route::get('/get-users', [UserController::class, 'getUsers']);

    Route::post('/add-block-contacts', [UserController::class, 'addContactsBlock']);

    Route::get('/get-block-contacts', [UserController::class, 'getContactsBlock']);

    Route::post('/add-ignore-contacts', [UserController::class, 'addContactsIgnore']);

    Route::get('/get-ignore-contacts', [UserController::class, 'getContactsIgnore']);

    Route::put('/remove-contacts', [UserController::class, 'removeContacts']);


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

    Route::put('/duplicate-postcard/{id}', [PostcardControllerAlias::class, 'duplicate']);

    Route::post('/save-avatar', [PostcardControllerAlias::class, 'saveAvatar']);

    /* Send postcard for contact */

    Route::put('/send-postcard-to-contact', [PostcardControllerAlias::class, 'sendPostcardToContact']);

    /* Postcard info */

    Route::get('/postcard-info/{id}', [PostcardControllerAlias::class, 'postcardInfo']);

});

require "profile.php";
