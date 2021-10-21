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
use Illuminate\Support\Facades\Storage;


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

        $postcardsQuery = Postcard::query()
            ->leftjoin('postcards_mailings','postcards.id','=', 'postcards_mailings.postcard_id')
            ->leftjoin('postcards_users','postcards.id','=', 'postcards_users.postcard_id')

            ->where(function ($query) use ($user){
                $query->where('postcards_mailings.start','<',Carbon::now())
                        ->where('postcards_mailings.stop','>',Carbon::now())
                        ->where('postcards_mailings.user_id',$user->id);
            })
            ->orWhere('postcards.user_id', $user->id)
            ->orWhere('postcards_users.user_id', $user->id)
            ->selectRaw(
                'postcards.*, postcards_mailings.start, postcards_mailings.stop,
                IFNULL(postcards_mailings.start, postcards.created_at) as sort,
                IF(postcards.user_id="'.$user->id.'", 1, 0) as author
            ');
        $postcardsQuery->with(
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

        $postcards = $postcardsQuery->get();

        return new PostcardCollection($postcards);

        $postcardsQuery = $user->postcards()
            ->whereIn('status',[PostcardStatus::CREATED, PostcardStatus::ARCHIVE])
            ->with(
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

        $postcardFavorites = $user->postcardFavorites()
            ->with(
                'user:id,login',
                'textData',
                'geoData',
                'tagData',
                'audioData',
                'mediaContents.textData',
                'mediaContents.geoData',
                'mediaContents.audioData',
            )->get();
        $postCards->concat($postcardFavorites);

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
                'media_content_id' => $request->input('media_content_id')?0:null,
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
