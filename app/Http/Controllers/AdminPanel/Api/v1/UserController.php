<?php

namespace App\Http\Controllers\AdminPanel\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdmpnPanel\UserCollection;
use App\Services\AdminPanel\AdminPanelUsersService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function getUsers(Request $request) {

        $adminPanelUsersService = new AdminPanelUsersService();

        return new UserCollection($adminPanelUsersService->getUsers($request));
    }

    public function getInfoUser($id) {
        $user = User::where('id', $id)->with('postcards', 'mediaContents')->first();

        return new UserResource($user);
    }

    public function updateStatusUser(Request $request, $id) {
        $user = User::find($id);
        $user->update($request->all());
        return new UserResource($user);
    }
}
