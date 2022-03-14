<?php


namespace App\Services;


use App\Enums\ActionLocKey;
use App\Enums\MailingType;
use App\Enums\PostcardStatus;
use App\Jobs\NotificationJob;
use App\Models\GeoData;
use App\Models\MediaContent;
use App\Models\Postcard;
use App\Models\TagData;
use App\Models\TextData;
use App\Models\Device;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


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
                    'token' => $user->device->pluck('token')->toArray(),
                    'title' => $this->postcard->user->login,
                    'body' => __('notifications.gallery_text'),
                    'img' => NotificationService::img($this->postcard),
                    'action_loc_key' => ActionLocKey::GALLERY_TEXT,
                    'user_id' => $this->postcard->user_id,
                    'postcard_id' => $this->postcard->id,
                ];
                dispatch(new NotificationJob($notification));

            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }


}
