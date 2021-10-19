<?php


namespace App\Services;


use App\Models\GeoData;
use App\Models\Postcard;
use App\Models\TagData;
use App\Models\TextData;
use Illuminate\Http\Request;


class PostcardService
{
    private $postcard;

    public function __construct(Postcard $postcard)
    {
        $this->postcard = $postcard;
    }

    public function updatePostcard(Request $request)
    {

        $textData = $request->input('text_data');

        dd($request->all());

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
        $this->postcard->status = $request->input('status');
        $this->postcard->interval_send = $request->input('interval_send');
        $this->postcard->interval_step = $request->input('interval_step');
        $this->postcard->radius = $request->input('radius');
        $this->postcard->lat = $request->input('lat');
        $this->postcard->lng = $request->input('lng');
        $this->postcard->countries = $request->input('countries');
        $this->postcard->regions = $request->input('regions');
        $this->postcard->cities = $request->input('cities');
        $this->postcard->save();
    }

    public function deletePostcard()
    {
        $this->postcard->delete();
    }
}
