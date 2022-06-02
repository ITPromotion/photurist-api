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
// use App\Services\Notification\Sms\RegisterSms;
use Spatie\Permission\Models\Role;
use App\Models\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    public function checkMobile(Request $request)
    {
        $rules = [
            'phone' => 'sometimes|numeric|nullable',
            'login' => 'sometimes|alpha_num|nullable|exists:users,login|max:255',
        ];

        $validator = $this->validator->make(['phone' => $request->input('phone'), 'login' => $request->input('login')], $rules);

        if (($validator->fails())||(!$request->input('login')&&!($request->input('phone')))) {
            return response()->json(['errorCode' => 'VALIDATION_ERROR'], 203);
        }

        $otp = new OTP();

        $phoneNumber = $request->input('phone');

        if($request->input('login')){
            $user = User::where('login', $request->input('login'))->first();

            if(!$user){
                return response()->json(['errorCode' => 'USER_NOT_FOUND'], 208);
            }

            $phoneNumber = $user->phone;

        }

        if ($request->input('admin_panel')) {
            $admin = Admin::where('phone', $request->input('phone'))->first();
            if (!$admin || !$admin->hasAnyRole(Role::all())) {
                return response()->json(['errorCode' => 'permission denied'], 403);
            }
        }
        $result =true;
        // if(env('APP_DEBUG')!='true') {
        //     $result = (new RegisterSms())->send($phoneNumber, $otp->otp);
        // }else{
        //     $result =true;
        // }



        if ($result !== true) {
            return response()->json(['errorCode' => 'SMS not send'], 204);
        }

        $otp->status = 'SENT';
        $otp->save();

        TempNumber::updateOrCreate(
            ['phone' => $phoneNumber],
            ['otp_id' => $otp->id]
        );
        // if(env('APP_DEBUG')!='true'){
        //     return  response()->json([],201);
        // }
        $user = User::where('phone', $phoneNumber)->first();
        return response()->json([
            'codeOTP'   => $otp->otp,
            'user' => $user?true:false,
        ], 201);

    }

    public function checkOTP(CheckOtpRequest $request)
    {
        if($request->input('login')){
            $user = User::whereLogin($request->input('login'))->first();
            if(!$user){
                return response()->json(['errorCode' => 'USER_NOT_FOUND'], 208);
            }
            $phoneNumber = $user->phone;
        }elseif ($request->input('phone_number')){
            $phoneNumber = $request->input('phone_number');
        }




        $codeOTP = $request->input('code_otp');

        $tempNumber = TempNumber::where('phone', $phoneNumber)->first();

        $otp = OTP::find($tempNumber->otp_id);

        if ($otp !== null) {
            $result = $otp->validate($codeOTP);

            $user = User::where('phone', $phoneNumber)->where('status','=',UserStatus::ACTIVE)->first();

            if ($result === true) {

                if(!$user)
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

    public function checkOTPAdmin (CheckOtpRequest $request) {
        $codeOTP = $request->input('code_otp');
        $phoneNumber = $request->input('phone_number');

        $tempNumber = TempNumber::where('phone', $phoneNumber)->first();
        $otp = OTP::find($tempNumber->otp_id);

        if ($otp !== null) {
            $result = $otp->validate($codeOTP);
            $admin = Admin::where('phone', $phoneNumber)->with('roles.permissions')->first();

            if ($result === true) {
                if(!$admin || !$admin->hasAnyRole(Role::all())) {
                    return response()->json(['errorCode' => 'permission denied'], 403);
                }

                Auth::login($admin);

                $accessToken = Auth::user()->createToken('authToken')->accessToken;
                // dd($accessToken);
                return response(['user' => $admin, 'access_token' => $accessToken]);
            }
        }
        return response()->json([
            'errorCode' => $result['short_desc'],
            'description' => $result['long_desc']
        ], 203);
    }
}
