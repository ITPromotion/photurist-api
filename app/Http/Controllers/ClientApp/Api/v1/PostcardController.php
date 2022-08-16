<?php

namespace App\Http\Controllers\ClientApp\Api\v1;

use App\Enums\MailingType;
use App\Enums\PostcardStatus;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClientApp\Postcard\AddPostcardToGalleryRequest;
use App\Http\Requests\ClientApp\Postcard\GetGalleryRequest;
use App\Http\Requests\ClientApp\Postcard\GetPostcardsFromIdsRequest;
use App\Http\Requests\ClientApp\Postcard\PostcardResendRequest;
use App\Http\Requests\ClientApp\Postcard\SendPostcardToContactRequest;
use App\Http\Requests\ClientApp\Postcard\SetStatusPostcardRequest;
use App\Http\Requests\ClientApp\Postcard\SetViewAdditionallyFromIdsRequest;
use App\Http\Resources\MediaContentResource;
use App\Http\Resources\PostcardCollection;
use App\Http\Resources\PostcardResource;
use App\Http\Resources\TagDataCollection;
use App\Http\Resources\UserSearchCollection;
use App\Jobs\MediaContentCrop;
use App\Models\AdditionallyView;
use App\Models\AudioData;
use App\Models\MediaContent;
use App\Models\Postcard;
use App\Models\TagData;
use App\Models\TextData;
use App\Models\User;
use App\Services\PostcardService;
use App\Traits\FileTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Device;
use App\Services\NotificationService;
use App\Enums\ActionLocKey;
use App\Jobs\NotificationJob;

class PostcardController extends Controller
{

    use FileTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $postCardsQuery = Postcard::with(
                    'user:id,login',
                            'textData',
                            'geoData',
                            'tagData',
                            'audioData',
                            'mediaContents.textData',
                            'mediaContents.geoData',
                            'mediaContents.audioData',
                        );



        if(is_numeric($request->input('offset')))
            $postCardsQuery->offset($request->input('offset'));

        if(is_numeric($request->input('limit')))
            $postCardsQuery->limit($request->input('limit'));

          $postCards = $postCardsQuery->orderBy('created_at','desc')->get();

        return new PostcardCollection($postCards);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getGallery(Request $request)
    {

        $postcardService = new PostcardService();

        $postcards = $postcardService->getGallery($request);



        /*$postcardsQuery->with(
                'user:id,login',
                'textData',
                'geoData',
                'tagData',
                'audioData',
                'mediaContents.textData',
                'mediaContents.geoData',
                'mediaContents.audioData',
            );

        if(is_numeric($request->input('offset')))
            $postcardsQuery->offset($request->input('offset'));

        if(is_numeric($request->input('limit')))
            $postcardsQuery->limit($request->input('limit'));

        $postcards = $postcardsQuery->orderBy('sort', 'desc')->get();*/

        return new PostcardCollection($postcards);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $postcard = Postcard::create(
            [
                'user_id' => Auth::id(),
                'status'  => PostcardStatus::CREATED,
            ],
        );
        $postcard->delete();
        return new PostcardResource($postcard);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Postcard  $postcard
     * @return \Illuminate\Http\Response
     */
    public function show(Postcard $postcard)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Postcard  $postcard
     * @return \Illuminate\Http\Response
     */
    public function edit(Postcard $postcard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Postcard  $postcard
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $user = Auth::user();

        if(
            ($user->status == UserStatus::BLOCKED)&&
            ($request->input('status')==PostcardStatus::ACTIVE)
        ){
            return abort('403');
        }

        $postcard = Postcard::withTrashed()->findOrFail($id);

        $postcardService = new PostcardService($postcard);

        $postcard = $postcardService->updatePostcard($request);



        return new PostcardResource($postcard);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Postcard  $postcard
     * @return \Illuminate\Http\Response
     */
    public function destroy(Postcard $postcard)
    {
        $postcardService = new PostcardService($postcard);

        try {
            $userIds = $postcard->allMailingsUserIds();
            foreach ($userIds as $id) {
                // (new NotificationService)->send([
                //     'users' => Device::getTokenUsers([$id]),
                //     'title' => $postcard->user->login,
                //     'body' => __('notifications.delete_postcard_text'),
                //     'img' => $postcard->mediaContents[0]->link,
                //     'postcard_id' => $postcard->id,
                //     'action_loc_key' => ActionLocKey::POSTCARD_DELETE,
                //     'badge' => DB::table('postcards_mailings')
                //                     ->where('view', 0)
                //                     ->where('user_id', $id)
                //                     ->where('status', PostcardStatus::ACTIVE)
                //                     ->count()
                // ]);
                $notification = [
                    'tokens' => Device::getTokenUsers([$id]),
                    'title' => $postcard->user->login,
                    'body' => __('notifications.delete_postcard_text'),
                    'img' => NotificationService::img($postcard),
                    'action_loc_key' => ActionLocKey::POSTCARD_DELETE,
                    'user_id' => $id,
                    'postcard_id' => $postcard->id,
                    'main_postcard_id' => $postcard->additional_postcard_id,
                ];
                dispatch(new NotificationJob($notification));
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        $postcard->delete();


    }

    /**
     * Save media to storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saveMedia(Request $request)
    {
        $link = $this->saveMediaContent($request->file('file'), 'postcard/'.$request->input('postcard_id').'/image', $request->input('media_content_type'));
        $mediaContent = MediaContent::create([
                'link' => $link,
                'postcard_id' => $request->input('postcard_id'),
                'media_content_type' => $request->input('media_content_type')
        ]);

        $mediaContentCropJob = new MediaContentCrop($mediaContent);
        $this->dispatch($mediaContentCropJob);

        return new MediaContentResource($mediaContent);

    }

    public function saveAvatar(Request $request)
    {
        $link = $this->saveMediaContent($request->file('file'), 'user/'.\Auth::user()->id.'/avatar', $request->input('media_content_type'));
        if (\Auth::user()->avatar) {
            Storage::disk('public')->delete(\Auth::user()->avatar);
        }

        $mediaContent = \Auth::user()->update([
                'avatar' => $link,
        ]);

        return new MediaContentResource(\Auth::user());
    }

    /**
     * Save audio to storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function saveAudio(Request $request)
    {
        $link = $this->saveMediaContent($request->file('file'), 'postcard/'.$request->input('postcard_id').'/audio');


        $data = [
                'link' => $link,
                'postcard_id' => $request->input('media_content_id')?null:$request->input('postcard_id'),
                'media_content_id' => $request->input('media_content_id')?$request->input('media_content_id'):null,
            ];

        $audio = AudioData::create($data);

        return new MediaContentResource($audio);

    }

    /**
     * Save audio to storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function removeMedia($id)
    {
        $mediaContent = MediaContent::withoutTrashed()->where('id',$id)->first();

        if(!$mediaContent)
            return;

        Storage::disk('public')->delete($mediaContent->link);

        $mediaContent->forceDelete();

        return ;

    }

    public function removeAudio($id)
    {
        $audioData = AudioData::where('id',$id)->first();

        if(!$audioData)
            return;

        Storage::disk('public')->delete($audioData->link);

        $audioData->forceDelete();

        return ;

    }

    public function addPostcardToGallery(AddPostcardToGalleryRequest $request)
    {
        $postcardId = $request->input('postcard_id');
        Auth::user()->postcardFavorites()->sync($postcardId,false);
        DB::table('postcards_mailings')
            ->where('postcard_id',$postcardId)
            ->where('user_id', Auth::id())
            ->update([
                'stop'=> Carbon::now(),
                'status'=> MailingType::CLOSED,
            ]);
        $this->setView($request->input('postcard_id'));
    }

    public function removePostcardFromList($id)
    {
        Auth::user()->postcardFavorites()->detach($id);

        DB::table('postcards_mailings')
            ->where('postcard_id', $id)
            ->where('user_id',Auth::id())
            ->update([
                'stop'=> Carbon::now(),
                'status'=> MailingType::CLOSED,
            ]);
    }

    public function addFavorite (AddPostcardToGalleryRequest $request) {
        $favorites = Auth::user()->favorites();
        $favorites->attach($request->input('postcard_id'));
        return true;
    }

    public function deleteFavorite (AddPostcardToGalleryRequest $request) {
        $favorites = Auth::user()->favorites();
        if ($favorites->wherePivot('postcard_id',$request->input('postcard_id'))->first()) {
            $favorites->detach($request->input('postcard_id'));
            return true;
        }
        return false;
    }

    public function setStatusPostcard($id, SetStatusPostcardRequest $request)
    {
        $user = Auth::user();

        if(
            ($user->status == UserStatus::BLOCKED)&&
            ($request->input('status')==PostcardStatus::ACTIVE)
        ){
            return abort('403');
        }

        $postcard = Postcard::FindOrFail($id);

        $postcard->status = $request->input('status');

        $postcard->save();

         $postcard->load('user:id,login',
            'textData',
            'geoData',
            'tagData',
            'audioData',
            'mediaContents.textData',
            'mediaContents.geoData',
            'mediaContents.audioData',
        );
         return new PostcardResource($postcard);
    }

    public function getPostcardFromIds(GetPostcardsFromIdsRequest $request)
    {
        $postcardService = new PostcardService();

        $postcards = $postcardService->getPostcardFromIds($request);

        return new PostcardCollection($postcards);
    }

    public function stopMailings($id)
    {
        $postcard = Postcard::findOrFail($id);
        $postcard->status = PostcardStatus::ARCHIVE;
        $postcard->save();
        DB::table('postcards_mailings')
            ->where('postcard_id',$id)
            ->update([
                'stop'=> Carbon::now(),
                'status'=> MailingType::CLOSED,
            ]);
        return new PostcardResource($postcard);
    }

    public function deletePostcard($id)
    {
        $postcard = Postcard::findOrFail($id);

        $postcardService = new PostcardService($postcard);

        try {
            $userIds = $postcard->allMailingsUserIds();
            foreach ($userIds as $id) {
                // (new NotificationService)->send([
                //     'users' => Device::getTokenUsers([$id]),
                //     'title' => $postcard->user->login,
                //     'body' => __('notifications.delete_postcard_text'),
                //     'img' => $postcard->mediaContents[0]->large,
                //     'media_type' => $postcard->mediaContents[0]->media_content_type,
                //     'postcard_id' => $postcard->id,
                //     'action_loc_key' => ActionLocKey::POSTCARD_DELETE,
                //     'badge' => DB::table('postcards_mailings')
                //                     ->where('view', 0)
                //                     ->where('user_id', $id)
                //                     ->where('status', PostcardStatus::ACTIVE)
                //                     ->count()
                // ]);

                $notification = [
                    'tokens' => Device::getTokenUsers([$id]),
                    'title' => $postcard->user->login,
                    'body' => __('notifications.delete_postcard_text'),
                    'img' => NotificationService::img($postcard),
                    'action_loc_key' => ActionLocKey::POSTCARD_DELETE,
                    'user_id' => $id,
                    'postcard_id' => $postcard->id,
                ];
                dispatch(new NotificationJob($notification));
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
        $postcardService->deletePostcard();
    }

    public function setView($id)
    {
        DB::table('postcards_mailings')
            ->where('postcard_id', $id)
            ->where('user_id',Auth::id())
            ->update([
                'view' => true,
            ]);
    }

    public function setViewAdditionally($id)
    {
        AdditionallyView::updateOrCreate(
            ['postcard_id'=> $id, 'user_id' => Auth::id()],
            ['view' => true]
        );

    }

    public function setViewAdditionallyFromIds(SetViewAdditionallyFromIdsRequest $request)
    {
        $postcardService = new PostcardService();

        return $postcardService->setViewAdditionallyFromIds($request);
    }

    public function notViewQuantity()
    {
        $postcardService = new PostcardService();

        return [
            'not_view' => $postcardService->notViewQuantity()
        ];

        return [
            'not_view' => DB::table('postcards_mailings')
            ->where('view', 0)
            ->where('user_id',Auth::id())
            ->where('status', PostcardStatus::ACTIVE)
            ->count()
            ];
    }

    public function offUserPostcardNotification ($id)
    {
        $userPostcardNotification = Auth::user()->userPostcardNotifications();
        $userPostcardNotification->syncWithoutDetaching($id);
        return true;
    }

    public function onUserPostcardNotification ($id)
    {
        $userPostcardNotification = Auth::user()->userPostcardNotifications();
        $userPostcardNotification->detach($id);
        return true;
    }

    public function duplicate (Request $request, $id) {
        $postcard = Postcard::find($id);
        $clone = $postcard->duplicate();
        $clone->update([
            'status' => PostcardStatus::DRAFT,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'additional_postcard_id' => null,
        ]);
        try {
            $this->copyMediaContent($clone);

        } catch (\Throwable $th) {
            //throw $th;
        }
        return new PostcardResource($clone);
    }

    public function sendPostcardToContact(SendPostcardToContactRequest $request)
    {
        $user = Auth::user();

        if($user->status == UserStatus::BLOCKED) {
            return abort('403');
        }

        $contact = $user->contacts()->where('users.status', PostcardStatus::ACTIVE)->findOrFail($request->input('contact_id'));

        $postcard = Postcard::findOrFail($request->input('postcard_id'));

        $postcardService = new PostcardService($postcard);

        $postcardService->sendPostcard($contact);
    }

    public function postcardInfo($id)
    {
        $postcard = Auth::user()->postcards()->findOrFail($id);
        $postcardService = new PostcardService($postcard);
        return $postcardService->postcardInfo();
    }

    public function postcardResend(PostcardResendRequest $request)
    {
        $user = Auth::user();

        $receiver = $user->find($request->input('receiver_id'));

        if(($user->status == UserStatus::BLOCKED)&&
            (!$receiver->blockContacts->contains('id', $user->id))) {
            return abort('403');
        }

        $postcard = Postcard::find($request->input('postcard_id'));
        $postcardService = new PostcardService($postcard);

        $postcardService->postcardResend($request->input('receiver_id'));
    }

    public function getTagData(Request $request)
    {
        $postcardService = new PostcardService();

        return new TagDataCollection($postcardService->getTagData($request));
    }

    public function getPostcardByTag(Request $request)
    {
        $postcardService = new PostcardService();

        return new TagDataCollection($postcardService->getPostcardByTag($request));
    }
    public function getUsersForPostcard(Request $request)
    {
        $postcardService = new PostcardService();

        return new UserSearchCollection($postcardService->getUsersForPostcard($request));
    }

    public function getPostcardByUser(Request $request)
    {
        $postcardService = new PostcardService();

        return new UserSearchCollection($postcardService->getPostcardByUser($request));
    }


}
