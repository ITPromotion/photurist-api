<?php


namespace App\Services;


use App\Enums\ClientStatus;
use App\Enums\UserStatus;
use App\Http\Requests\ClientApp\User\AddClientsActiveRequest;
use App\Http\Requests\ClientApp\User\CheckContactsRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use phpDocumentor\Reflection\Types\Boolean;

class UserService
{
    private $user;

    public function __construct(User $user)
    {
       $this->user = $user;
    }

    public function checkContacts(CheckContactsRequest $request):Collection
    {
        $users = User::where('status', UserStatus::ACTIVE)
            ->whereIn('phone', $request->phones)
            ->select('id','phone')
            ->get();

        return $users;
    }

    public function addContactsActive(AddClientsActiveRequest $request):bool
    {

        foreach($request->input('ids') as $id){
            $ids[$id]=  [
                    'status' => ClientStatus::ACTIVE
            ];
        }
        $this->user->clients()->sync($ids,false );

        return true;
    }
}
