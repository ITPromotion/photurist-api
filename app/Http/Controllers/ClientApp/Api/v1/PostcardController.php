<?php

namespace App\Http\Controllers\ClientApp\Api\v1;

use App\Enums\PostcardStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClientApp\Postcard\AddPostcardToGalleryRequest;
use App\Http\Requests\ClientApp\Postcard\GetGalleryRequest;
use App\Http\Resources\MediaContentResource;
use App\Http\Resources\PostcardCollection;
use App\Http\Resources\PostcardResource;
use App\Models\AudioData;
use App\Models\MediaContent;
use App\Models\Postcard;
use App\Models\TextData;
use App\Services\PostcardService;
use App\Traits\FileTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Device;
use App\Services\NotificationService;

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
        $user = Auth::user();

        $postcardsQuery = DB::query()
            ->selectRaw('
            *  from (select postcards.*, postcards_mailings.start, postcards_mailings.stop,
                IFNULL(postcards_mailings.start, postcards.created_at) as sort,
                IF(postcards.user_id=?, 1, 0) as author
             from `postcards` left join `postcards_mailings` on `postcards`.`id` = `postcards_mailings`.`postcard_id`
             where ((`postcards_mailings`.`start` < ? and `postcards_mailings`.`stop` > ? and `postcards_mailings`.`user_id` = ?) )
             and `postcards`.`deleted_at` is null
					UNION
select pc1.*, postcards_mailings.start, postcards_mailings.stop,
                IFNULL(postcards_mailings.start, pc1.created_at) as sort,
                IF(pc1.user_id=?, 1, 0) as author
             from `postcards` as pc1
						 LEFT join `postcards_users` on `pc1`.`id` = `postcards_users`.`postcard_id`
						 left join `postcards_mailings` on `pc1`.`id` = `postcards_mailings`.`postcard_id`
						 where (`postcards_users`.`user_id` = ? ) and postcards_mailings.user_id = ? 	and
						`pc1`.`deleted_at` is null
		UNION
select pc1.*, null, null,
                IFNULL(pc1.start_mailing, pc1.created_at) as sort,
                IF(pc1.user_id=?, 1, 0) as author
             from `postcards` as pc1 where (`pc1`.`user_id` = ?) 	and
						`pc1`.`deleted_at` is null

			ORDER BY `sort` desc) as res

WHERE res.user_id <> ? or (user_id = ? and start is NULL)
    LIMIT ?, ?'
                ,[
                    $user->id, Carbon::now(), Carbon::now(), $user->id, $user->id, $user->id, $user->id, $user->id, $user->id, $user->id, $user->id, $request->input('offset'), $request->input('limit')
            ]);

        $postcards = array();

        $postcardCollections = $postcardsQuery->get();

        foreach ($postcardCollections as $postcardCollection){
            $postcard = Postcard::find($postcardCollection->id);
            if(($postcard->user_id==$user->id)&&($postcard->status==PostcardStatus::ACTIVE)){
                $postcard->start = Carbon::parse($postcard->updated_at)->format('Y-m-d h:i:s');
                $postcard->stop = Carbon::parse($postcard->updated_at)->addMinutes($postcard->interval_send)->format('Y-m-d h:i:s');
            }else {
                $postcard->start = $postcardCollection->start;
                $postcard->stop = $postcardCollection->stop;
            }
            $postcard->author = $postcardCollection->author;
            $postcard->load('user:id,login',
                'textData',
                'geoData',
                'tagData',
                'audioData',
                'mediaContents.textData',
                'mediaContents.geoData',
                'mediaContents.audioData',
            );

            $postcards[] = $postcard;
        }

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
        $postcard = Postcard::withTrashed()->findOrFail($id);
        $postcardService = new PostcardService($postcard);

        $postcardService->updatePostcard($request);

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
            (new NotificationService)->send([
                'users' => Device::getTokenUsers($userIds),
                'title' => $postcard->user->login,
                'body' => 'Открытка удалена',
                'img' => $postcard->mediaContents[0]->link,
            ]);
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
        $link = $this->saveMediaContent($request->file('file'), 'postcard/'.$request->input('postcard_id').'/image');

        $mediaContent = MediaContent::create([
                'link' => $link,
                'postcard_id' => $request->input('postcard_id'),
            ]);
        return new MediaContentResource($mediaContent);

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
        Auth::user()->postCardFavorites()->sync($request->input('postcard_id'),false);
    }


}
