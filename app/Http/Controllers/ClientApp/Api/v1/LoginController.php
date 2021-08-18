<?php

namespace App\Http\Controllers\ClientApp\Api\v1;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClientApp\Authorization\ActiveUserRequest;
use App\Http\Requests\ClientApp\Authorization\CheckOtpRequest;
use App\Models\OTP;
use App\Models\Sto\Sto;
use App\Models\TempNumber;
use App\Models\User;
use App\Models\User\UserNotificationToken;
use App\Services\Notification\Sms\RegisterSms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    public function checkMobile($phoneNumber)
    {

        $rules = [
            'phoneNumber' => 'required|numeric'
        ];

        $validator = $this->validator->make(['phoneNumber' => $phoneNumber], $rules);

        if ($validator->fails()) {
            return response()->json(['errorCode' => 'VALIDATION_ERROR'], 203);
        }

        $otp = new OTP();

        if(env('APP_DEBUG')!='true') {
            $result = (new RegisterSms())->send($phoneNumber, $otp->otp);
        }else{
            $result =true;
        }



        if ($result !== true) {
            return response()->json(['errorCode' => 'SMS not send'], 204);
        }

        $otp->status = 'SENT';
        $otp->save();

        TempNumber::updateOrCreate(
            ['phone' => $phoneNumber],
            ['otp_id' => $otp->id]
        );
        if(env('APP_DEBUG')!='true'){
            return  response()->json([],201);
        }
        return response()->json([
            'codeOTP'   => $otp->otp
        ], 201);

    }

    public function checkOTP(CheckOtpRequest $request)
    {
        $user = User::wherePhone($request->input('phone_number'))->first();

        $phoneNumber = $request->input('phone_number');

        $codeOTP = $request->input('code_otp');

        $tempNumber = TempNumber::where('phone', $phoneNumber)->first();

        $otp = OTP::find($tempNumber->otp_id);

        if ($otp !== null) {
            $result = $otp->validate($codeOTP);

            if ($result === true) {

                    $user = User::updateOrCreate(
                        [
                            'phone' => $phoneNumber
                        ],
                        [
                            'phone' => $phoneNumber,
                            'status' => UserStatus::CREATED,
                        ]
                    );

/*                if (isset($request->push_notification_token) && !empty($request->push_notification_token)) {
                    DeviceTokensFcms::where('user_id', $user->id)
                        ->updateOrCreate([
                            'token'      => $request->push_notification_token,
                        ],[
                            'user_id' => $user->id,
                            'token'      => $request->push_notification_token,
                            'token_type' => $request->input('token_type')
                        ]);
                }*/

                Auth::login($user);

                $accessToken = Auth::user()->createToken('authToken')->accessToken;

                return response(['user' => Auth::user(), 'access_token' => $accessToken]);

                $data = [
                    'user' => $user,
                    'token_type' => 'Bearer',
                    'access_token' => $accessToken,
                    //'refresh_token' => $user->generateRefreshToken()
                ];

                return $data;
            }
        }

        return response()->json([
            'errorCode' => $result['short_desc'],
            'description' => $result['long_desc']
        ], 203);


    }

    public function activeUser(ActiveUserRequest $request)
    {
        $user = Auth::user();
        $user->login = $request->input('login');
        $user->status = UserStatus::ACTIVE;
        $user->save();

        return response()->json($user);

    }
}
