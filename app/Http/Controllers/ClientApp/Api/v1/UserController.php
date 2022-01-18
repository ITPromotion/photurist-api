<?php

namespace App\Http\Controllers\ClientApp\Api\v1;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClientApp\User\CheckContactsRequest;
use App\Http\Requests\ClientApp\User\SetGeoDataRequest;
use App\Http\Requests\ClientApp\User\SaveDeviceRequest;
use App\Http\Requests\ClientApp\User\DeleteDeviceRequest;
use App\Http\Resources\UserPhoneResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Device;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function setGeoData(SetGeoDataRequest $request)
    {
        Auth::user()->update($request->all());
    }

    public function saveDevice (SaveDeviceRequest $request) {
        Device::where('token', $request->token)->delete();
        return Auth::user()->device()->updateOrCreate($request->all());
    }
    public function deleteDevice (DeleteDeviceRequest $request) {
        Auth::user()->device()->where('token', $request->token)->delete();
    }

    public function checkContacts(CheckContactsRequest $request){
        $users = User::where('status', UserStatus::ACTIVE)
                        ->whereIn('phone', $request->phones)
                        ->select('phone')
                        ->get();
        $phones = [];

        if($users->isNotEmpty()){
            $phones = $users->pluck('phone');
        }
        return new UserPhoneResource(['phones' => $phones]);
    }
}
