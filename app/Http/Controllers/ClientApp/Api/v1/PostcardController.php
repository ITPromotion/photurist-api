<?php

namespace App\Http\Controllers\ClientApp\Api\v1;

use App\Enums\PostcardStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\MediaContentResource;
use App\Http\Resources\PostcardResource;
use App\Models\AudioData;
use App\Models\MediaContent;
use App\Models\Postcard;
use App\Models\TextData;
use App\Services\PostcardService;
use App\Traits\FileTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class PostcardController extends Controller
{

    use FileTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function update(Request $request, Postcard $postcard)
    {
        $postcardService = new PostcardService($postcard);

        $postcardService->updatePostcard($request);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Postcard  $postcard
     * @return \Illuminate\Http\Response
     */
    public function destroy(Postcard $postcard)
    {
        //
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


}
