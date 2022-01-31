<?php


namespace App\Services;


use App\Enums\ClientStatus;
use App\Enums\UserStatus;
use App\Http\Requests\ClientApp\User\AddClientsActiveRequest;
use App\Http\Requests\ClientApp\User\CheckContactsRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use phpDocumentor\Reflection\Types\Boolean;
use App\Enums\ActionLocKey;
use Illuminate\Support\Facades\DB;
use App\Enums\PostcardStatus;
use App\Jobs\NotificationJob;

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
            ->select('id','phone', 'avatar')
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

        $this->user->clients()->sync($ids,false);

        foreach ($ids as  $key => $value) {
            // (new NotificationService)->send([
            //     'users' => User::find($key)->device()->pluck('token')->toArray(),
            //     'title' => $this->user->login,
            //     'body' => __('notifications.add_contacts'),
            //     'img' => $this->user->avatar,
            //     'user_id' => $this->user->id,
            //     'action_loc_key' => ActionLocKey::ADD_CONTACTS,
            //     'badge' => DB::table('postcards_mailings')
            //         ->where('view', 0)
            //         ->where('user_id', $key)
            //         ->where('status', PostcardStatus::ACTIVE)
            //         ->count()
            // ]);
            $notification = [
                'token' => User::find($key)->device()->pluck('token')->toArray(),
                'title' => $this->user->login,
                'body' => __('notifications.add_contacts'),
                'img' => $this->user->avatar,
                'action_loc_key' => ActionLocKey::POSTCARD_DELETE,
                'user_id' => $key,
                'postcard_id' => $this->user->id,
            ];
            dispatch(new NotificationJob($notification));
        }
        return true;
    }

    public function getContactsActive(Request $request):Collection
    {

        $clientsQuery = $this->user->clients()->wherePivot('status', ClientStatus::ACTIVE);

        if(is_numeric($request->input('offset')))
            $clientsQuery->offset($request->input('offset'));

        if(is_numeric($request->input('limit')))
            $clientsQuery->limit($request->input('limit'));

        return $clientsQuery->select('users.id','users.phone', 'users.login', 'users.avatar')->get();
    }

    public function addContactsBlock(AddClientsActiveRequest $request):bool
    {

        foreach($request->input('ids') as $id){
            $ids[$id]=  [
                'status' => ClientStatus::BLOCK,
            ];
        }
        $this->user->clients()->sync($ids,false );

        return true;
    }

    public function getContactsBlock(Request $request):Collection
    {

        $clientsQuery = $this->user->clients()->wherePivot('status', ClientStatus::BLOCK);

        if(is_numeric($request->input('offset')))
            $clientsQuery->offset($request->input('offset'));

        if(is_numeric($request->input('limit')))
            $clientsQuery->limit($request->input('limit'));

        return $clientsQuery->select('users.id','users.phone', 'users.login', 'users.avatar')->get();
    }

    public function removeContacts(AddClientsActiveRequest $request)
    {
        $this->user->clients()->detach($request->input('ids'));
        return true;
    }
}
