<?php


namespace App\Services;


use App\Enums\ContactStatuses;
use App\Enums\UserStatus;
use App\Http\Requests\ClientApp\User\AddContactsRequest;
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
            ->select('id','phone', 'login', 'avatar')
            ->get();

        return $users;
    }

    public function addContactsActive(AddContactsRequest $request):bool
    {

        foreach($request->input('ids') as $id){
            $ids[$id]=  [
                    'status' => ContactStatuses::ACTIVE
            ];
        }
        $this->user->contacts()->sync($ids,false );

        foreach ($ids as  $key => $value) {
            (new NotificationService)->send([
                'tokens' => User::find($key)->device()->pluck('token')->toArray(),
                'title' => $this->user->login,
                'body' => __('notifications.add_contacts'),
                'img' => $this->user->avatar,
                'user_id' => $this->user->id,
                'action_loc_key' => ActionLocKey::ADD_CONTACTS,
                'badge' => DB::table('postcards_mailings')
                    ->where('view', 0)
                    ->where('user_id', $key)
                    ->where('status', PostcardStatus::ACTIVE)
                    ->count()
            ]);
        }
        return true;
    }

    public function getContactsActive(Request $request):Collection
    {

        $contactsQuery = $this->user->contacts()->wherePivot('status', ContactStatuses::ACTIVE);

        if(is_numeric($request->input('offset')))
            $contactsQuery->offset($request->input('offset'));

        if(is_numeric($request->input('limit')))
            $contactsQuery->limit($request->input('limit'));

        return $contactsQuery->select('users.id','users.phone', 'users.login', 'users.avatar')->get();
    }

    public function addContactsBlock(AddContactsRequest $request):bool
    {

        foreach($request->input('ids') as $id){
            $ids[$id]=  [
                'status' => ContactStatuses::BLOCK,
            ];
        }
        $this->user->contacts()->sync($ids,false );

        return true;
    }

    public function getContactsBlock(Request $request):Collection
    {

        $contactsQuery = $this->user->blockContacts();

        if(is_numeric($request->input('offset')))
            $contactsQuery->offset($request->input('offset'));

        if(is_numeric($request->input('limit')))
            $contactsQuery->limit($request->input('limit'));

        return $contactsQuery->select('users.id','users.phone', 'users.login', 'users.avatar')->get();
    }

    public function addContactsIgnore(AddContactsRequest $request):bool
    {

        foreach($request->input('ids') as $id){
            $ids[$id]=  [
                'status' => ContactStatuses::IGNORE,
            ];
        }
        $this->user->contacts()->sync($ids,false );

        return true;
    }

    public function getContactsIgnore(Request $request):Collection
    {

        $contactsQuery = $this->user->contacts()->wherePivot('status', ContactStatuses::IGNORE);

        if(is_numeric($request->input('offset')))
            $contactsQuery->offset($request->input('offset'));

        if(is_numeric($request->input('limit')))
            $contactsQuery->limit($request->input('limit'));

        return $contactsQuery->select('users.id','users.phone', 'users.login', 'users.avatar')->get();
    }

    public function removeContacts(AddContactsRequest $request)
    {
        $this->user->contacts()->detach($request->input('ids'));
        foreach ($request->input('ids') as  $id) {
            (new NotificationService)->send([
                'users' => User::find($id)->device()->pluck('token')->toArray(),
                'title' => $this->user->login,
                'body' => __('notifications.remov_contacts'),
                'img' => $this->user->avatar,
                'user_id' => $this->user->id,
                'action_loc_key' => ActionLocKey::REMOV_CONTACTS,
                'badge' => DB::table('postcards_mailings')
                    ->where('view', 0)
                    ->where('user_id', $id)
                    ->where('status', PostcardStatus::ACTIVE)
                    ->count()
            ]);
        }
        return true;
    }
}
