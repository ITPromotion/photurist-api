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
Route::get('/test', function () {
    $postcards = DB::table('postcards_mailings')
            ->where('status', MailingType::ACTIVE)
            ->where('stop','<', Carbon::now());
            return $postcards->get();
        $postcards->update(['status' => MailingType::CLOSED]);

        foreach ($postcards->get() as $postcard) {
            \Illuminate\Support\Facades\Log::info('waiting_time_text');
            if (!Postcard::where($postcard->postcard_id)->first()->userPostcardNotifications()->where('user_id', $postcard->user_id)->first())
            (new \App\Services\NotificationService)->send([
                'users' =>  User::find($postcard->user_id)->device->pluck('token')->toArray(),
                'title' => User::find($postcard->user_id)->login,
                'body' => __('notifications.waiting_time_text'),
                'img' => Postcard::find($postcard->postcard_id)->first()->mediaContents[0]->link,
                'postcard_id' => $postcard->postcard_id,
                'action_loc_key' => ActionLocKey::WAITING_TIME,
            ]);
        }



});
/* Checking OTP code */
Route::post('/check-otp', [LoginController::class,'checkOTP']);

Route::prefix('/user')->as('.user.')->group(function (){
    Route::post('/login', [LoginController::class, 'login'])->name('login');

    Route::group([
        'middleware' => 'auth:api',
        'namespace' => 'App\Http\Controllers\ClientApp\Api\v1',
    ], function () {

        require 'user-api.php';

    });


});
