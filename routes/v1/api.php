<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientApp\Api\v1\LoginController;
use App\Enums\MailingType;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Postcard;
use App\Enums\ActionLocKey;
use App\Services\NotificationService;
use App\Enums\PostcardStatus;
use App\Jobs\NotificationJob;
use App\Jobs\MediaContentCrop;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/* Checking phone number */
Route::get('/check-mobile', [LoginController::class, 'checkMobile']);

Route::post('/test-push', function (Request $request) {
    return (new \App\Services\NotificationService)->send(
        [
            'users' => \App\Models\User::find($request->user_id)->device->pluck('token')->toArray(),
            'title' => $request->title,
            'body' =>  $request->title,
            'img' => null,
            'action_loc_key' =>  null,
            'user_id' => null,
            'postcard_id' => null,
        ]
    );
});

Route::post('/send', function (Request $request) {


    try {
        $postcard =  \App\Models\Postcard::find($request->postcard_id);
            $notification = [
                'tokens' => \App\Models\User::find($request->user_id)->device->pluck('token')->toArray(),
                'title' => $postcard->user->login,
                'body' => ActionLocKey::GALLERY,
                'img' => NotificationService::img($postcard),
                'action_loc_key' =>  ActionLocKey::GALLERY,
                'user_id' => $request->user_id,
                'postcard_id' => $postcard->id,
            ];
            dispatch(new App\Jobs\NotificationJob($notification));
    } catch (\Throwable $th) {
        //throw $th;
    }
});

/* Checking OTP code */
Route::post('/check-otp', [LoginController::class,'checkOTP']);

Route::post('/admin-check-otp', [LoginController::class, 'checkOTPAdmin']);

Route::prefix('/user')->as('.user.')->group(function (){
    Route::post('/login', [LoginController::class, 'login'])->name('login');

    Route::group([
        'middleware' => ['auth:api', 'block_user'],
        'namespace' => 'App\Http\Controllers\ClientApp\Api\v1',
    ], function () {

        require 'user-api.php';

    });


});
Route::prefix('/admin')->as('.admin.')->group(function (){
    Route::post('/login', [LoginController::class,'loginAdmin']);

    Route::group([
        'middleware' => 'auth:api-admin',
        'namespace' => 'App\Http\Controllers\AdminPanel\Api\v1',
    ], function () {

        require 'admin-api.php';

    });
});

