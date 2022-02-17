<?php


namespace App\Services;


use App\Enums\MailingType;
use App\Enums\PostcardStatus;
use App\Models\GeoData;
use App\Models\MediaContent;
use App\Models\Postcard;
use App\Models\TagData;
use App\Models\TextData;
use App\Models\Device;
use Carbon\Carbon;
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

        if($request->input('additional_postcard_id'))
            $this->postcard->additional_postcard_id = $request->input('additional_postcard_id');

        $this->postcard->status = PostcardStatus::LOADING;

        $this->postcard->draft = $request->input('status')==PostcardStatus::ACTIVE?false:true;

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

        if($request->input('media_content_sort_orders')){
                    foreach ($request->input('media_content_sort_orders') as $mediaContentSortOrder){
                        $this->postcard->mediaContents()->where('media_contents.id',$mediaContentSortOrder['id'])->update(['sort_order' => $mediaContentSortOrder['sort_order']]);
                    }
        }

        $this->postcard->save();

    }

    public function deletePostcard()
    {
        $this->postcard->delete();
    }
}
