<?php

namespace App\Http\Controllers\AdminPanel\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\NotificationRequest;
use App\Http\Requests\Admin\GroupRequest;
use App\Models\Group;
use App\Http\Resources\RoleResource;
use App\Services\NotificationService;
use App\Jobs\NotificationJob;

class NotificationController extends Controller
{
    public function sendNotificationUser (NotificationRequest $request) {
        dd($request->all());
        $notification = [
            'token' => NotificationService::getTokenUsers($request->user_id),
            'title' => $request->title,
            'body' => $request->body,
        ];

        dispatch(new NotificationJob($notification));
        return new RoleResource([true]);
    }

    public function createGroup (GroupRequest $request) {
        return new RoleResource(Group::create($request->all()));
    }

    public function updateGroup(GroupRequest $request, $id) {
        return new RoleResource([Group::find($id)->update($request->all())]);
    }

    public function deleteGroup($id) {
        return new RoleResource([Group::find($id)->delete()]);
    }

    public function sendNotificationGroup (NotificationRequest $request) {
        $notification = [
            'token' => NotificationService::getTokenUsers($request->user_id),
            'title' => $request->title,
            'body' => $request->body,
        ];

        dispatch(new NotificationJob($notification));
        return new RoleResource([true]);
    }

    public function addUserToGroup(Request $request) {

    }

    public function getAllGroup () {
        return new RoleResource(Group::all());

    }
}
