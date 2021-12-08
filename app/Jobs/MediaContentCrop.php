<?php

namespace App\Jobs;

use App\Enums\MediaContentType;
use App\Enums\SizeImage;
use App\Enums\Video;
use App\Models\MediaContent;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class MediaContentCrop implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mediaContent;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MediaContent $mediaContent)
    {
        $this->mediaContent = $mediaContent;
    }

    // private function _createDir($file)
    // {
    //     return Storage::disk('public')->makeDirectory($file);
    //     Log::info('Storage');
    // }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('crop');
        $folder = "/postcard/".$this->mediaContent->postcard_id."/image";

        if (isset($this->mediaContent->media_content_type) && MediaContentType::PHOTO == $this->mediaContent->media_content_type) {
            $imgName = explode('image/', $this->mediaContent->link)[1];
            Log::info($this->mediaContent->link);
            $img = Image::make('storage/'.$this->mediaContent->link);
            $img->backup();

            foreach (SizeImage::keys() as $value) {
                Log::info(Storage::disk('public')->makeDirectory($folder."/$value/"));
                $size = explode('x' , $value)[0];
                $height = $img->height() > $img->width();
                $width = $img->height() < $img->width();
                $img->resize($height ? $size : null, $width ? $size : null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $img->crop($size, $size);
                $img->save('storage/'.$folder."/$value/".$imgName);
                $img->reset();
            }
        } elseif (isset($this->mediaContent->media_content_type) && MediaContentType::VIDEO == $this->mediaContent->media_content_type) {
            $videoName = explode('image/', $this->mediaContent->link)[1];
            $ffmpeg = FFMpeg::create();

            Storage::disk('public')->makeDirectory($folder."/clip/");
            $ffprobe = FFProbe::create();
            $video_dimensions = $ffprobe->
            streams('storage/'.$this->mediaContent->link)   // extracts streams informations
            ->videos()                      // filters video streams
            ->first()                       // returns the first video stream
            ->getDimensions();

            $duration = $ffprobe->
            streams('storage/'.$this->mediaContent->link)
                ->videos()
                ->first()->get('duration');
            $width = $video_dimensions->getWidth();
            $height =  $video_dimensions->getHeight();
            $xy =  (int)$width < $height ? ($height - $width) / 2 : ($width - $height) / 2;
            $fullHDW = $height < $width ? 1920 :'trunc(oh*a/2)*2';
            $fullHDH = $height > $width  ? 1080 : 'trunc(ow/a/2)*2';

            $vidos = $ffmpeg->open('storage/'.$this->mediaContent->link);
            $format = new \FFMpeg\Format\Video\X264();
            $format->setAudioCodec("aac");
            // $vidos->filters()->custom("scale=w=$fullHDW:h=$fullHDH");
            // if (explode('.', $videoName)[1] != 'mp4') {
            $newVideoName = explode('.', $videoName)[0].'s.mp4';
            $frameName = 'storage/'.$folder."/clip/".explode('.', $videoName)[0].'s.jpg';
            $vidos->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1))
                ->save($frameName);

            $vidos->save(new \FFMpeg\Format\Video\X264('aac', 'libx264'), 'storage/'.explode('image/', $this->mediaContent->link)[0].'image/'.$newVideoName);
            // } else {
            //     $newVideoName = $videoName;
            // }

            foreach (SizeImage::keys() as $value) {
                Log::info('crop2');
                try {
                    $video = $ffmpeg->open('storage/'.explode('image/', $this->mediaContent->link)[0].'image/'.$newVideoName);
                    Log::info(Storage::disk('public')->makeDirectory($folder."/$value/"));
                    Storage::disk('public')->makeDirectory($folder."/frame/$value/");
                    $size = (integer)explode('x' , $value)[0];

                    $img = Image::make($frameName);
                    $img->backup();
                    $height = $img->height() > $img->width();
                    $width = $img->height() < $img->width();
                    $img->resize($height ? $size : null, $width ? $size : null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $img->crop($size, $size);
                    $img->save('storage/'.$folder."/frame/$value/".explode('.', $videoName)[0].'s.jpg');
                    $img->reset();

                    $size = $size % 2 ? $size + 1 : $size;
                    $scaleW = $height < $width  ? 'trunc(oh*a/2)*2' : $size;
                    $scaleH = $height > $width  ? 'trunc(ow/a/2)*2' : $size;


                    $video->filters()->custom("scale=w=$scaleW:h=$scaleH,crop=$size:$size")->framerate(new \FFMpeg\Coordinate\FrameRate(Video::FRAME),4)->clip(TimeCode::fromSeconds(Video::START), TimeCode::fromSeconds($duration >= Video::DURATION ? Video::DURATION : $duration ));
                    $video->save($format, 'storage/'.$folder."/$value/".$newVideoName);

                } catch (\Throwable $th) {
                   return;
                }
            }
            // $clip = $video->clip(TimeCode::fromSeconds(Video::START), TimeCode::fromSeconds(Video::DURATION));
            // $clip->save($format, 'storage/'.$folder."/clip/".$newVideoName);
            $this->mediaContent->update([
                'link' =>  explode('image/', $this->mediaContent->link)[0].'image/'.$newVideoName,
                'loading' => true,
            ]);

            Log::info($this->mediaContent);



            return;
        }
    }
}
