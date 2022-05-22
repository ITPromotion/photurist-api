<?php

namespace App\Http\Controllers\ClientApp\Api\v1;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClientApp\User\AddContactsRequest;
use App\Http\Requests\ClientApp\User\CheckContactsRequest;
use App\Http\Requests\ClientApp\User\SetGeoDataRequest;
use App\Http\Requests\ClientApp\User\SaveDeviceRequest;
use App\Http\Requests\ClientApp\User\DeleteDeviceRequest;
use App\Http\Resources\MediaContentResource;
use App\Http\Resources\UserPhoneResource;
use App\Jobs\MediaContentCrop;
use App\Models\AudioData;
use App\Models\MediaContent;
use App\Models\User;
use App\Services\UserService;
use App\Traits\FileTrait;
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

    public function addContactsActive(AddContactsRequest $request):UserPhoneResource
    {
        $userService = new UserService(Auth::user());

        $result = $userService->addContactsActive($request);

        return new UserPhoneResource(['result' => $result]);
    }

    public function getContactsActive(Request $request):UserPhoneResource
    {
        $userService = new UserService(Auth::user());

        $users = $userService->getContactsActive($request);

        $contactCount = $userService->getContactsCount();

        return new UserPhoneResource([
            'users' => $users,
            'contacts_count' => $contactCount,
        ]);
    }

    public function addContactsBlock(AddContactsRequest $request):UserPhoneResource
    {
        $userService = new UserService(Auth::user());

        $result = $userService->addContactsBlock($request);

        return new UserPhoneResource(['result' => $result]);
    }

    public function getContactsBlock(Request $request):UserPhoneResource
    {
        $userService = new UserService(Auth::user());

        $users = $userService->getContactsBlock($request);

        $contactsBlockedCount = $userService->getContactsBlockedCount();

        return new UserPhoneResource([
            'users' => $users,
            'contact_blocked_count' =>  $contactsBlockedCount,
        ]);
    }

    public function addContactsIgnore(AddContactsRequest $request):UserPhoneResource
    {
        $userService = new UserService(Auth::user());

        $result = $userService->addContactsIgnore($request);

        return new UserPhoneResource(['result' => $result]);
    }

    public function getContactsIgnore(Request $request):UserPhoneResource
    {
        $userService = new UserService(Auth::user());

        $users = $userService->getContactsIgnore($request);

        $contactsIgnoredCount = $userService->getContactsIgnoredCount();

        return new UserPhoneResource([
            'users' => $users,
            'contacts_ignored_count' => $contactsIgnoredCount,
        ]);
    }

    public function removeContacts(AddContactsRequest $request):UserPhoneResource
    {
        $userService = new UserService(Auth::user());

        $users = $userService->removeContacts($request);

        return new UserPhoneResource(['result' => $users]);
    }

    public function removeIgnoredContacts(AddContactsRequest $request)
    {
        $userService = new UserService(Auth::user());

        $users = $userService->removeIgnoreContacts($request);

        return new UserPhoneResource(['result' => $users]);
    }

    public function removeBlockedContacts(AddContactsRequest $request)
    {
        $userService = new UserService(Auth::user());

        $users = $userService->removeBlockedContacts($request);

        return new UserPhoneResource(['result' => $users]);
    }

    public function getUsers(Request $request)
    {
        $userService = new UserService(Auth::user());

        $users = $userService->getUsers($request);

        return new UserPhoneResource([
            'users' => $users,
        ]);
    }
}
