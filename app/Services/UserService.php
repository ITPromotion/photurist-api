<?php


namespace App\Services;


use App\Enums\ContactStatuses;
use App\Enums\UserStatus;
use App\Http\Requests\ClientApp\User\AddContactsRequest;
use App\Http\Requests\ClientApp\User\CheckContactsRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
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

        if($users->isNotEmpty()) {

            foreach ($users as $user) {
                $ids[$user->id] = [
                    'phone_book' => true,
                ];
            }

            $this->user->contacts()->update([
                'new' => false,
            ]);
        }

        return $users;
    }

    public function addContactsActive(AddContactsRequest $request):bool
    {

        foreach($request->input('ids') as $id){
            $ids[$id]=  [
                    'status' => ContactStatuses::ACTIVE,
                    'contact' => true,
            ];
        }
        $this->user->contacts()->sync($ids,false );

        $users = User::whereIn('id',$request->input('ids'))->get();

        foreach ($users as $user){
            $ids =[];
            $ids[$this->user->id] =  [
                'status' => ContactStatuses::ACTIVE,
                'contact' => true,
            ];
            $user->contacts()->sync($ids,false );
        }

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

        $request->validate([
            'search'    => 'nullable|string',
            'sort'      => 'nullable|in:asc,desc|nullable',
            'sort_field'=> 'in:login,id,created_at|nullable',
        ]);

        $contactsQuery = $this->user->contacts()->where('users.id','!=',Auth::id())->withPivot('contact', 'blocked', 'ignored', 'phone_book', 'new', 'status', 'created_at');

        if(is_numeric($request->input('offset')))
            $contactsQuery->offset($request->input('offset'));

        if(is_numeric($request->input('limit')))
            $contactsQuery->limit($request->input('limit'));

        if($request->input('search')){

            $search = $request->input('search');

            $contactsQuery
                ->where('phone', 'LIKE', "%{$search}%")
                ->orWhere('login', 'LIKE', "%{$search}%");
        }

        $contactsQuery->select('users.id','users.phone', 'users.login', 'users.avatar');

        if($request->input('sort')&&$request->input('sort_field')== 'created_at'){
            $contactsQuery->orderBy('contacts_users.'.$request->input('sort_field'), $request->input('sort'));
        }

        if($request->input('sort')&&$request->input('sort_field')){
            if($request->input('sort_field') == 'login'){
                $contactsQuery->orderBy($request->input('sort_field'), $request->input('sort'));
            }
        }


        return $contactsQuery->get();
    }

    public function addContactsBlock(AddContactsRequest $request):bool
    {

        foreach($request->input('ids') as $id){
            $ids[$id]=  [
                'blocked' => true,
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

        if($request->input('search')){

            $search = $request->input('search');

            $contactsQuery
                ->where('phone', 'LIKE', "%{$search}%")
                ->orWhere('login', 'LIKE', "%{$search}%");
        }

        return $contactsQuery->select('users.id','users.phone', 'users.login', 'users.avatar')->get();
    }

    public function addContactsIgnore(AddContactsRequest $request):bool
    {

        foreach($request->input('ids') as $id){
            $ids[$id]=  [
                'ignored' => true,
            ];
        }
        $this->user->contacts()->sync($ids,false );

        return true;
    }

    public function getContactsIgnore(Request $request):Collection
    {

        $contactsQuery = $this->user->contacts()->wherePivot('ignored', true);

        if(is_numeric($request->input('offset')))
            $contactsQuery->offset($request->input('offset'));

        if(is_numeric($request->input('limit')))
            $contactsQuery->limit($request->input('limit'));

        if($request->input('search')){

            $search = $request->input('search');

            $contactsQuery
                ->where('phone', 'LIKE', "%{$search}%")
                ->orWhere('login', 'LIKE', "%{$search}%");
        }

        return $contactsQuery->select('users.id','users.phone', 'users.login', 'users.avatar')->get();
    }

    public function removeContacts(AddContactsRequest $request):bool
    {
        $this->user->contacts()->detach($request->input('ids'));

        $userId = Auth::id();
        foreach ($request->input('ids') as $id){
            $contact = User::findOrFail($id);
            $contact->contacts()->detach($userId);
        }

        foreach ($request->input('ids') as  $id) {
            (new NotificationService)->send([
                'tokens' => User::find($id)->device()->pluck('token')->toArray(),
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

    public function removeIgnoreContacts(AddContactsRequest $request):bool
    {
        $contacts = User::whereIn('id', $request->input('ids'))->get();

        $this->user->contacts()->updateExistingPivot($contacts, array('ignored' => false), false);
        return true;
    }

    public function removeBlockedContacts(AddContactsRequest $request):bool
    {
        foreach ($request->input('ids') as $id){
            $contact = User::findOrfail($id);
            $contact->contacts()->detach($this->user);
        }

        $this->user->contacts()->detach($request->input('ids'));
        return true;
    }

    public function getContactsCount():int
    {
        return $this->user->contacts()->wherePivot('status','active')->count();
    }


    public function getContactsBlockedCount():int
    {
        return $this->user->contacts()->wherePivot('blocked', true)->count();
    }

    public function getContactsIgnoredCount():int
    {
        return $this->user->contacts()->wherePivot('ignored', true)->count();
    }

    public function getUsers(Request $request):Collection
    {

        $UsersQuery = User::query();

        if(is_numeric($request->input('offset')))
            $UsersQuery->offset($request->input('offset'));

        if(is_numeric($request->input('limit')))
            $UsersQuery->limit($request->input('limit'));

        if($request->input('search')){

            $search = $request->input('search');

            $UsersQuery
                ->where('phone', 'LIKE', "%{$search}%")
                ->orWhere('login', 'LIKE', "%{$search}%");
        }

        return $UsersQuery->select('users.id','users.phone', 'users.login', 'users.avatar')->get();
    }

}
