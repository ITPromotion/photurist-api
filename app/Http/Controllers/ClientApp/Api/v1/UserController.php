<?php

namespace App\Http\Controllers\ClientApp\Api\v1;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClientApp\User\AddClientsActiveRequest;
use App\Http\Requests\ClientApp\User\CheckContactsRequest;
use App\Http\Requests\ClientApp\User\SetGeoDataRequest;
use App\Http\Requests\ClientApp\User\SaveDeviceRequest;
use App\Http\Requests\ClientApp\User\DeleteDeviceRequest;
use App\Http\Resources\UserPhoneResource;
use App\Models\User;
use App\Services\UserService;
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

    public function checkContacts(CheckContactsRequest $request):UserPhoneResource
    {
        $userService = new UserService(Auth::user());

        $users = $userService->checkContacts($request);

        return new UserPhoneResource(['users' => $users]);
    }

    public function addContactsActive(AddClientsActiveRequest $request):UserPhoneResource
    {
        $userService = new UserService(Auth::user());

        $users = $userService->addContactsActive($request);

        return new UserPhoneResource(['users' => $users]);
    }

    public function getContactsActive(Request $request):UserPhoneResource
    {
        $userService = new UserService(Auth::user());

        $users = $userService->getContactsActive($request);

        return new UserPhoneResource(['users' => $users]);
    }

    public function addContactsBlock(AddClientsActiveRequest $request):UserPhoneResource
    {
        $userService = new UserService(Auth::user());

        $users = $userService->addContactsBlock($request);

        return new UserPhoneResource(['users' => $users]);
    }

    public function getContactsBlock(Request $request):UserPhoneResource
    {
        $userService = new UserService(Auth::user());

        $users = $userService->getContactsBlock($request);

        return new UserPhoneResource(['users' => $users]);
    }
}
