<?php


namespace App\Services;


use App\Enums\ActionLocKey;
use App\Enums\MailingType;
use App\Enums\PostcardStatus;
use App\Enums\UserStatus;
use App\Http\Requests\ClientApp\Postcard\GetPostcardsFromIdsRequest;
use App\Http\Requests\ClientApp\Postcard\SetViewAdditionallyFromIdsRequest;
use App\Http\Resources\PostcardCollection;
use App\Jobs\NotificationJob;
use App\Models\AdditionallyView;
use App\Models\GeoData;
use App\Models\MediaContent;
use App\Models\Postcard;
use App\Models\TagData;
use App\Models\TextData;
use App\Models\Device;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PostcardService
{
    private $postcard;

    public function __construct(Postcard $postcard = null)
    {
        $this->postcard = $postcard;
    }

    public function getGallery(Request $request)
    {
        $user = Auth::user();
        if($request->input('sort')) {
            $sort = $request->input('sort');
        }else{
            $sort = 'desc';
        }

        $queryStringUnionDistinct = ' UNION DISTINCT ';

        $queryStringInMailing = '(select postcards.*, postcards_mailings.start, postcards_mailings.stop,
                IFNULL(postcards_mailings.start, postcards.created_at) as sort,
                IF(postcards.user_id='.$user->id.', 1, 0) as author,
                postcards_mailings.view
             from `postcards` left join `postcards_mailings` on `postcards`.`id` = `postcards_mailings`.`postcard_id`
             where ((`postcards_mailings`.`start` < "'.Carbon::now().'" and `postcards_mailings`.`stop` > "'.Carbon::now().'" and `postcards_mailings`.`user_id` = '.$user->id.') )
             or (`postcards`.`user_id` ='.$user->id.' and `postcards`.`start_mailing` < "'.Carbon::now().'" and date_add(`postcards`.`start_mailing`,interval `postcards`.`interval_send` minute) > "'.Carbon::now().'")
             and `postcards`.`deleted_at` is null) ';


         $queryStringSaved = 'select pc1.*, postcards_mailings.start, postcards_mailings.stop,
                IFNULL(postcards_mailings.start, pc1.updated_at) as sort,
                IF(pc1.user_id='.$user->id.', 1, 0) as author,
                postcards_mailings.view
                 from `postcards` as pc1
                             LEFT join `postcards_users` on `pc1`.`id` = `postcards_users`.`postcard_id`
                             left join `postcards_mailings` on `pc1`.`id` = `postcards_mailings`.`postcard_id`
                             where (`postcards_users`.`user_id` = '.$user->id.' ) and postcards_mailings.user_id = '.$user->id.' 	and
                            `pc1`.`deleted_at` is null';


         $queryStringMyPostcards = 'select pc1.*, null, null,
                IFNULL(pc1.start_mailing, pc1.updated_at) as sort,
                IF(pc1.user_id='.$user->id.', 1, 0) as author,
                 1
             from `postcards` as pc1 where (`pc1`.`user_id` = '.$user->id.') and
						`pc1`.`deleted_at` is null';

         if(($request->input('state')=='all')||(!$request->input('state'))){
             $queryString = $queryStringInMailing.$queryStringUnionDistinct.$queryStringSaved.$queryStringUnionDistinct.$queryStringMyPostcards;

         }elseif($request->input('state')=='in_mailing'){
             $queryString = $queryStringInMailing;

         }elseif($request->input('state')=='saved'){
             $queryString = $queryStringSaved;

         }




        $postcardsQuery = DB::query()

            ->selectRaw('
           DISTINCT  id, user_id, start, stop, sender_id, additional_postcard_id, status, view, start_mailing, author, sort
           from ('.$queryString.' ORDER BY `sort` '.$sort.') as res')
            ->where(function ($query) use ($user){
                $query->where('res.user_id','!=', $user->id)
                        ->orWhere(function ($query) use ($user){
                            $query->where('res.user_id','=', $user->id)
                                  ->whereNull('start');
                        });

            })
            ->where(function ($query) use ($user){
                $query
                    ->where('res.sender_id','!=', $user->id)
                    ->orWhereNull('res.sender_id');
            })
            ->whereNull('additional_postcard_id');

        if($request->input('status')=='marked') {
            $postcardsQuery
            ->leftJoin('favorites', 'res.id', '=', 'favorites.postcard_id')
            ->where('favorites.user_id','=', $user->id);
        };

        if($request->input('status')=='new'){
            $postcardsQuery
                ->where('res.view', 0);
        }

        if($request->input('author')=='my'){
            $postcardsQuery->where('res.user_id', $user->id);
        }

        if($request->input('author')=='other'){
            $postcardsQuery->where('res.user_id','!=', $user->id);
        }

        $postcardsQuery
            ->orderBy('sort', $sort)
            ->offset($request->input('offset'))->limit($request->input('limit'));

        $postcards = array();

        $postcardCollections = $postcardsQuery->get();


        foreach ($postcardCollections as $postcardCollection){

            $postcard = Postcard::find($postcardCollection->id);
            if(($postcard->user_id==$user->id)&&($postcard->status==PostcardStatus::ACTIVE)){
                $postcard->start = Carbon::parse($postcard->start_mailing)->format('Y-m-d h:i:s');
                $postcard->stop = Carbon::parse($postcard->start_mailing)->addMinutes($postcard->interval_send)->format('Y-m-d h:i:s');
            }else {
                $postcard->start = $postcardCollection->start;
                $postcard->stop = $postcardCollection->stop;
            }
            $postcard->view = 'asdasdasdasdd';
            $postcard->postcard_view = $postcardCollection->view;
            $postcard->author = $postcardCollection->author;
            $postcard->sort = $postcardCollection->sort;

            $usersIds = $postcard->users()->pluck('user_id');

            if($usersIds->search($user->id)!==false){
                $postcard->save = 1;
            } else {
                $postcard->save = 0;
            };

            $postcard->load('user:id,login',
                'textData',
                'geoData',
                'tagData',
                'audioData',
                'mediaContents.textData',
                'mediaContents.geoData',
                'mediaContents.audioData',
                'additionally.textData',
                'additionally.geoData',
                'additionally.tagData',
                'additionally.audioData',
                'additionally.mediaContents.textData',
                'additionally.mediaContents.geoData',
                'additionally.mediaContents.audioData',
                'additionally.user:id,login',
                'userPostcardNotifications',
            );

            if($postcard->additionally){
                $newAdditionallyCount = $postcard->additionally()->count();
            } else {
                $newAdditionallyCount = 0;
            }

            foreach ($postcard->additionally as $additionalPostcard){

                if(AdditionallyView::where('postcard_id', $additionalPostcard->id)
                    ->where('user_id', Auth::id())->first()){
                    $newAdditionallyCount--;
                }

                if($additionalPostcard->user_id==Auth::id()){
                    $additionalPostcard->author = true;
                } else {
                    $additionalPostcard->author = false;
                }

                if($postcard->user_id==Auth::id()){
                    $additionalPostcard->moderator = true;
                } else {
                    $additionalPostcard->moderator = false;
                }

                $usersIds = $additionalPostcard->users()->pluck('user_id');

                if($usersIds->search($user->id)!==false){
                    $additionalPostcard->save = 1;
                } else {
                    $additionalPostcard->save = 0;
                };

            }
            $postcard->new_additionally_count = $newAdditionallyCount;

            $postcards[] = $postcard;
        }

        return $postcards;
    }

    public function updatePostcard(Request $request)
    {

        $textData = $request->input('text_data');

        $flag = false;

        $this->postcard->textData()->delete();

        foreach ($this->postcard->mediaContents as  $mediaContent){
            $mediaContent->textData()->delete();
        }

        foreach ($textData as $text) {
            if (!$text['media_content_id']) {
                $text['postcard_id'] = $this->postcard->id;
                $flag = true;
            }

            textData::create($text);

            if($flag) break;
        }

        $geoData = $request->post('geo_data');

        $this->postcard->geoData()->delete();

        foreach ($this->postcard->mediaContents as  $mediaContent){
            $mediaContent->geoData()->delete();
        }

        foreach ($geoData as $geo) {
            $flag = false;

            if (!$geo['media_content_id']) {
                $geo['postcard_id'] = $this->postcard->id;
                $flag = true;
            }
            GeoData::create($geo);
            if($flag) break;
        }

        $tagData = $request->post('tag_data');

        $this->postcard->tagData()->delete();

        foreach ($tagData as $tag){
            TagData::create([
                'tag' => $tag,
                'postcard_id' => $this->postcard->id,
            ]);
        }
        $this->postcard->restore();

        if($request->input('additional_postcard_id')){
            $this->postcard->additional_postcard_id = $request->input('additional_postcard_id');
            $setViewAdditionallyFromIdsRequest = new SetViewAdditionallyFromIdsRequest();


            $setViewAdditionallyFromIdsRequest->replace(['postcard_ids' => [$this->postcard->id]]);

            Log::info($setViewAdditionallyFromIdsRequest->all());

            $this->setViewAdditionallyFromIds($setViewAdditionallyFromIdsRequest);
        }

        $this->postcard->status = PostcardStatus::LOADING;

        $this->postcard->finally_status = $request->input('status');

        if($this->postcard->status == MailingType::ACTIVE)
                 $this->postcard->start_mailing = Carbon::now();
        $this->postcard->interval_send = $request->input('interval_send');
        $this->postcard->interval_wait = $request->input('interval_wait');
        $this->postcard->radius = $request->input('radius');
        $this->postcard->lat = $request->input('lat');
        $this->postcard->lng = $request->input('lng');
        $this->postcard->countries = $request->input('countries');
        $this->postcard->regions = $request->input('regions');
        $this->postcard->cities = $request->input('cities');

        if($request->input('media_content_ids')){
            $media_content_ids = $request->input('media_content_ids');
            foreach ($this->postcard->mediaContents as $mediaContent){
                if(!in_array($mediaContent->id, $media_content_ids)){
                    $mediaContent->delete();
                };
            }
        }
        $this->postcard->load('mediaContents');

        if($request->input('media_content_sort_orders')){
                    foreach ($request->input('media_content_sort_orders') as $mediaContentSortOrder){
                        $this->postcard->mediaContents()->where('media_contents.id',$mediaContentSortOrder['id'])->update(['sort_order' => $mediaContentSortOrder['sort_order']]);
                    }
        }

        $this->postcard->save();

        return $this->postcard;
    }

    public function deletePostcard()
    {
        $this->postcard->delete();
    }

    public function sendPostcard(User $user)
    {
        $postcardsMailing = DB::table('postcards_mailings')->where('postcard_id',$this->postcard->id)->where('user_id',$user->id)->first();

        if ($postcardsMailing) return;

        DB::table('postcards_mailings')->insert([
            'user_id' => $user->id,
            'postcard_id' => $this->postcard->id,
            'status' => MailingType::ACTIVE,
            'start' => Carbon::now(),
            'stop' => Carbon::now()->addMinutes($this->postcard->interval_wait),
        ]);

        try {
            if ($this->postcard->user_id != $user->id) {
                $notification = [
                    'tokens' => $user->device->pluck('token')->toArray(),
                    'title' => $this->postcard->user->login,
                    'body' => __('notifications.gallery_text'),
                    'img' => NotificationService::img($this->postcard),
                    'action_loc_key' => ActionLocKey::GALLERY,
                    'user_id' => $this->postcard->user_id,
                    'postcard_id' => $this->postcard->id,
                ];
                dispatch(new NotificationJob($notification));

            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function postcardResend($receiverId)
    {
        $clonePostcard = $this->postcard->duplicate();
        $clonePostcard->update([
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'sender_id'  => Auth::id(),
            'status'     => PostcardStatus::ACTIVE,
        ]);
        try {
            $this->copyMediaContent($clonePostcard);

        } catch (\Throwable $th) {
            //throw $th;
        }

        $postcardsMailing = DB::table('postcards_mailings')->where('postcard_id',$clonePostcard->id)->where('user_id',$receiverId)->first();

        if ($postcardsMailing) return;

        DB::table('postcards_mailings')->insert([
            'user_id' => $receiverId,
            'postcard_id' => $clonePostcard->id,
            'status' => MailingType::ACTIVE,
            'start' => Carbon::now(),
            'stop' => Carbon::now()->addMinutes($this->postcard->interval_wait),
        ]);

        $user = User::findOrFail($receiverId);

        try {
            if ($this->postcard->user_id != $user->id) {
                $notification = [
                    'tokens' => $user->device->pluck('token')->toArray(),
                    'title' => $this->postcard->user->login,
                    'body' => __('notifications.gallery_text'),
                    'img' => NotificationService::img($this->postcard),
                    'action_loc_key' => ActionLocKey::GALLERY,
                    'user_id' => $this->postcard->user_id,
                    'postcard_id' => $this->postcard->id,
                ];
                dispatch(new NotificationJob($notification));

            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function setViewAdditionallyFromIds(SetViewAdditionallyFromIdsRequest $request)
    {
        foreach($request->input('postcard_ids') as $id){

            AdditionallyView::updateOrCreate(
                ['postcard_id' => $id, 'user_id' => Auth::id()],
                ['view' => true]
            );
        }
    }

    public function getTagData(Request $request)
    {
        $postcardArrayIds = $this->getGalleryPostcardsIds();

        $tagDataQuery = TagData::query()
                            ->select('tag', DB::raw('count(*) as total'))
                            ->where('tag', 'LIKE', "%{$request->input('search')}%")
                            ->whereIn('postcard_id', $postcardArrayIds)
                            ->groupBy('tag');
                   if(is_numeric($request->input('offset')))
                       $tagDataQuery->offset($request->input('offset'));

                   if(is_numeric($request->input('limit')))
                        $tagDataQuery->limit($request->input('limit'));

        return $tagDataQuery->get();

    }

    public function getPostcardByTag(Request $request)
    {
        $postcardArrayIds = $this->getGalleryPostcardsIds();



        $tagData = TagData::where('tag', $request->input('search'))
                            ->whereIn('postcard_id', $postcardArrayIds)->get();

        $ids = [];

        if($tagData->isNotEmpty()){
            foreach ($tagData->pluck('postcard_id') as $id){
                $ids[] = $id;
            };
        }

        $getPostcardsFromIdsRequest = new GetPostcardsFromIdsRequest();

        $getPostcardsFromIdsRequest->replace(['postcard_ids' => $ids]);

        return $this->getPostcardFromIds($getPostcardsFromIdsRequest);
    }

    public function getUsersForPostcard(Request $request)
    {
       $postcardArrayIds = $this->getGalleryPostcardsIds();
        $userCollectionIds = Postcard::whereIn('id', $postcardArrayIds)->groupBy('user_id')->get();
        $userArrayIds = [];
        if($userCollectionIds->isNotEmpty()){
            $userArrayIds = $userCollectionIds->pluck('user_id')->toArray();
        }

        $tagDataQuery = User::query()
            ->select('id','login', DB::raw('count(*) as total'))
            ->whereIn('id', $userArrayIds)
            ->where('status', UserStatus::ACTIVE)
            ->where('login', 'LIKE', "%{$request->input('search')}%")
            ->groupBy('login');

        if(is_numeric($request->input('offset')))
            $tagDataQuery->offset($request->input('offset'));

        if(is_numeric($request->input('limit')))
            $tagDataQuery->limit($request->input('limit'));

        return $tagDataQuery->get();

    }

    public function getPostcardByUser(Request $request)
    {

        $postcardArrayIds = $this->getGalleryPostcardsIds();

        $user = User::where('id', $request->input('user_id'))->first();

        if($user){

           $postcards = $user->postcards()->whereIn('id', $postcardArrayIds)->get();


            foreach ($postcards as $postcard){

                $ids[] = $postcard->id;
            };
        }

        $getPostcardsFromIdsRequest = new GetPostcardsFromIdsRequest();

        $getPostcardsFromIdsRequest->replace(['postcard_ids' => $ids]);

        return $this->getPostcardFromIds($getPostcardsFromIdsRequest);
    }

    public function getPostcardFromIds(GetPostcardsFromIdsRequest $request)
    {
        $user = Auth::user();
        $postcards = Postcard::whereIn('id', $request->input('postcard_ids'))
            ->with(
                'user:id,login',
                'textData',
                'geoData',
                'tagData',
                'audioData',
                'mediaContents.textData',
                'mediaContents.geoData',
                'mediaContents.audioData',
                'additionally.textData',
                'additionally.geoData',
                'additionally.tagData',
                'additionally.audioData',
                'additionally.mediaContents.textData',
                'additionally.mediaContents.geoData',
                'additionally.mediaContents.audioData',
                'additionally.user:id,login',
            )->get();

        foreach ($postcards as $postcard) {
            $usersIds = $postcard->users()->pluck('user_id');

            if($usersIds->search($user->id)!==false){
                $postcard->save = 1;
            } else {
                $postcard->save = 0;
            };

            if($usersIds->search($user->id)!==false){
                $postcard->save = 1;
            } else {
                $postcard->save = 0;
            };

            if ($postcard->user_id == Auth::id()) {
                $postcard->author = true;
            }else{
                $postcard->author = false;
            }
            if($postcard->additionally){
                $newAdditionallyCount = $postcard->additionally()->count();
            } else {
                $newAdditionallyCount = 0;
            }
            foreach ($postcard->additionally as $additionalPostcard) {

                if(AdditionallyView::where('postcard_id', $additionalPostcard->id)
                    ->where('user_id', Auth::id())->first()){
                    $newAdditionallyCount --;
                }

                if ($additionalPostcard->user_id == Auth::id()) {
                    $additionalPostcard->author = true;
                } else {
                    $additionalPostcard->author = false;
                }

                if ($postcard->user_id == Auth::id()) {
                    $additionalPostcard->moderator = true;
                } else {
                    $additionalPostcard->moderator = false;
                }

                $usersIds = $additionalPostcard->users()->pluck('user_id');

                if($usersIds->search($user->id)!==false){
                    $additionalPostcard->save = 1;
                } else {
                    $additionalPostcard->save = 0;
                };
            }
            $postcard->new_additionally_count = $newAdditionallyCount;
        }

        return $postcards;
    }

    public function getGalleryPostcardsIds()
    {

        $user = Auth::user();


        $queryStringUnionDistinct = ' UNION DISTINCT ';


        $queryStringInMailing = '(select postcards.*, postcards_mailings.start, postcards_mailings.stop,
                IFNULL(postcards_mailings.start, postcards.created_at) as sort,
                IF(postcards.user_id='.$user->id.', 1, 0) as author,
                postcards_mailings.view
             from `postcards` left join `postcards_mailings` on `postcards`.`id` = `postcards_mailings`.`postcard_id`
             where ((`postcards_mailings`.`start` < "'.Carbon::now().'" and `postcards_mailings`.`stop` > "'.Carbon::now().'" and `postcards_mailings`.`user_id` = '.$user->id.') )
             and `postcards`.`deleted_at` is null) ';


        $queryStringSaved = 'select pc1.*, postcards_mailings.start, postcards_mailings.stop,
                IFNULL(postcards_mailings.start, pc1.updated_at) as sort,
                IF(pc1.user_id='.$user->id.', 1, 0) as author,
                postcards_mailings.view
                 from `postcards` as pc1
                             LEFT join `postcards_users` on `pc1`.`id` = `postcards_users`.`postcard_id`
                             left join `postcards_mailings` on `pc1`.`id` = `postcards_mailings`.`postcard_id`
                             where (`postcards_users`.`user_id` = '.$user->id.' ) and postcards_mailings.user_id = '.$user->id.' 	and
                            `pc1`.`deleted_at` is null';


        $queryStringMyPostcards = 'select pc1.*, null, null,
                IFNULL(pc1.start_mailing, pc1.updated_at) as sort,
                IF(pc1.user_id='.$user->id.', 1, 0) as author,
                 1
             from `postcards` as pc1 where (`pc1`.`user_id` = '.$user->id.') and
						`pc1`.`deleted_at` is null';


            $queryString = $queryStringInMailing.$queryStringUnionDistinct.$queryStringSaved.$queryStringUnionDistinct.$queryStringMyPostcards;





        $postcardsQuery = DB::query()

            ->selectRaw('
           DISTINCT  *  from ('.$queryString.' ) as res')
            ->where(function ($query) use ($user){
                $query->where('res.user_id','!=', $user->id)
                    ->orWhere(function ($query) use ($user){
                        $query->where('res.user_id','=', $user->id)
                            ->whereNull('start');
                    })
                    ->whereNull('additional_postcard_id');
            });

        $postcardCollectionsIds = $postcardsQuery->pluck('id');

        $postcardArrayIds = [];

        if($postcardCollectionsIds->isNotEmpty()){
            $postcardArrayIds = $postcardCollectionsIds->toArray();
        };

        return $postcardArrayIds;
    }

    public function notViewQuantity()
    {
        $notViewQuantity = 0;

        $postcardIds = $this->getGalleryPostcardsIds();



        foreach ($postcardIds as $postcardId){
            $notViewMain = DB::table('postcards_mailings')
                ->where('view', 0)
                ->where('user_id',Auth::id())
                ->where('status', PostcardStatus::ACTIVE)
                ->where('postcard_id', $postcardId)
                ->first();
            if($notViewMain){
                $notViewQuantity++;
                continue;
            }

            $postcard = Postcard::find($postcardId);

            $postcard->load('additionally');

            $newAdditionallyCount = $postcard->additionally()->count();

            foreach ($postcard->additionally as $additionalPostcard){

                if(AdditionallyView::where('postcard_id', $additionalPostcard->id)
                    ->where('user_id', Auth::id())->first()){
                    $newAdditionallyCount --;
                }
            }

            $notViewQuantity += $newAdditionallyCount;

        }

        return $notViewQuantity;
    }


}
