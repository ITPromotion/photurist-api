<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientApp\Api\v1\LoginController;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
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
Route::get('/test', function (Request $request) {
    $ffmpeg = FFMpeg::create();
    $ffprobe = FFProbe::create();
    $video = $ffmpeg->open('storage/postcard/1/image/pexels-anna-nekrashevich-7814905.mp4');
    $video_dimensions = $ffprobe->
    streams( 'storage/postcard/1/image/pexels-anna-nekrashevich-7814905.mp4' )   // extracts streams informations
    ->videos()                      // filters video streams
    ->first()                       // returns the first video stream
    ->getDimensions();
    $width = $video_dimensions->getHeight() < $video_dimensions->getWidth();
    $height = $video_dimensions->getHeight() > $video_dimensions->getWidth();

    $video->filters()
                    // ->resize(new \FFMpeg\Coordinate\Dimension(184, $video_dimensions->getWidth()), 'height', false)
                    ->crop(new \FFMpeg\Coordinate\Point("100", 100), new \FFMpeg\Coordinate\Dimension(320, 320))
                    ->synchronize();
    // $video->filters()

    $video->save(new \FFMpeg\Format\Video\X264(), 'storage/test231.mp4');
    return $height;
});
/* Checking phone number */
Route::get('/check-mobile', [LoginController::class, 'checkMobile']);

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
