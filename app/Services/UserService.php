<?php


namespace App\Services;


use App\Enums\UserStatus;
use App\Http\Requests\ClientApp\User\CheckContactsRequest;
use App\Models\User;

class UserService
{
    private $user;

    public function __construct(User $user)
    {
       $this->user = $user;
    }

    public function checkContacts(CheckContactsRequest $request)
    {
        $users = User::where('status', UserStatus::ACTIVE)
            ->whereIn('phone', $request->phones)
            ->select('phone')
            ->get();

        $phones = [];

        if($users->isNotEmpty()){
            $phones = $users->pluck('phone');
        }

        return $phones;
    }
}
