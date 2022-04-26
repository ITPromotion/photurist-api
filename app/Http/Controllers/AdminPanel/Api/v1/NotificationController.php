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
use App\Traits\FileTrait;

class NotificationController extends Controller
{
    use FileTrait;

    public function sendNotificationUser (NotificationRequest $request)
    {
        $link = null;
        if ($request->hasFile('file')) {
            $link = $this->saveMediaContent($request->file('file'), 'notification');
        }
        $notification = [
            'tokens' => NotificationService::getTokenUsers($request->user_id),
            'title' => $request->title,
            'body' => $request->body,
            'img' => $link,
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
            'tokens' => NotificationService::getTokenUsers($request->user_id),
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
