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
use App\Enums\PostcardStatus;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

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


Route::get('/test-push', function (Request $request) {
    $postcards = Postcard::where('status',PostcardStatus::ACTIVE)->get();
        foreach($postcards as $postcard){
            $user = User::find($request->user_id);
            if ($user->id != $postcard->user_id) {

                DB::table('postcards_mailings')->insert([
                    'user_id' => $user->id,
                    'postcard_id' => $postcard->id,
                    'status' => MailingType::ACTIVE,
                    'start' => Carbon::now(),
                    'stop' => Carbon::now()->addMinutes($postcard->interval_wait),
                ]);

                try {
                    if ($postcard->user_id != $user->id) {
                        (new NotificationService)->send([
                            'users' => $user->device->pluck('token')->toArray(),
                            'title' => $postcard->user->login,
                            'body' => ActionLocKey::GALLERY_TEXT,
                            'img' => $postcard->mediaContents[0]->link,
                            'postcard_id' => $postcard->id,
                            'action_loc_key' => ActionLocKey::GALLERY,
                            'badge' => DB::table('postcards_mailings')
                                ->where('view', 0)
                                ->where('user_id',$user->id)
                                ->where('status', PostcardStatus::ACTIVE)
                                ->count()
                        ]);
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
        }
        return 1;
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
