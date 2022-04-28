<?php

namespace App\Http\Controllers\ClientApp\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaContentResource;
use App\Models\AudioData;
use App\Models\MediaContent;
use App\Traits\FileTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    use FileTrait;

    public function getProfile()
    {
       return Auth::user();
    }

    public function userSaveMedia(Request $request)
    {
        $user = Auth::user();
        $link = $this->saveMediaContent($request->file('file'), 'users/'.$user->id.'/image', $request->input('media_content_type'));
        $mediaContent = MediaContent::create([
            'link' => $link,
            'user_id' => $user->id,
            'media_content_type' => $request->input('media_content_type')
        ]);

        return new MediaContentResource($mediaContent);

    }

    public function userSaveAudio(Request $request)
    {
        $user = Auth::user();

        $link = $this->saveMediaContent($request->file('file'), 'users/'.$user->id.'/audio');


        $data = [
            'link' => $link,
            'media_content_id' => $request->input('media_content_id')?$request->input('media_content_id'):null,
        ];

        $audio = AudioData::create($data);

        return new MediaContentResource($audio);

    }
}
