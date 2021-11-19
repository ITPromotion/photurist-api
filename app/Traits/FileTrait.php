<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Webpatser\Uuid\Uuid;
use Intervention\Image\Facades\Image;
use Illuminate\Filesystem\Filesystem;
use App\Enums\SizeImage;
use App\Enums\Video;
use App\Enums\MediaContentType;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use \FFMpeg\Coordinate\TimeCode;


trait FileTrait
{
    private function _createDir($file)
    {
        return Storage::disk('public')->makeDirectory($file);
    }

    public function saveMediaContent($image, $folder = 'postcard', $type = null): string
    {
        if ($image->isValid() && $image->getSize() !== 0) {
            $imageName = Storage::disk('public')->putFile($folder, $image);

            if (isset($type) && MediaContentType::PHOTO == $type) {
                $imgName = explode('image/', $imageName)[1];
                $img = Image::make($image);
                $img->backup();
                foreach (SizeImage::keys() as $value) {
                    $this->_createDir($folder."/$value/");
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
            } else if (isset($type) && MediaContentType::VIDEO == $type) {
                $videoName = explode('image/', $imageName)[1];
                $ffmpeg = FFMpeg::create();

                $this->_createDir($folder."/clip/");
                $ffprobe = FFProbe::create();
                $video_dimensions = $ffprobe->
                streams('storage/'.$imageName)   // extracts streams informations
                ->videos()                      // filters video streams
                ->first()                       // returns the first video stream
                ->getDimensions();

                $duration = $ffprobe->
                streams('storage/'.$imageName)
                ->videos()
                ->first()->get('duration');
                $width = $video_dimensions->getWidth();
                $height =  $video_dimensions->getHeight();
                $xy =  (int)$width < $height ? ($height - $width) / 2 : ($width - $height) / 2;
                $fullHDW = $height < $width ? 1920 :'trunc(oh*a/2)*2';
                $fullHDH = $height > $width  ? 1080 : 'trunc(ow/a/2)*2';

                $vidos = $ffmpeg->open('storage/'.$imageName);
                $format = new \FFMpeg\Format\Video\X264();
                $format->setAudioCodec("aac");
                // $vidos->filters()->custom("scale=w=$fullHDW:h=$fullHDH");
                // if (explode('.', $videoName)[1] != 'mp4') {
                    $newVideoName = explode('.', $videoName)[0].'s.mp4';
                    $vidos->save(new \FFMpeg\Format\Video\X264('aac', 'libx264'), 'storage/'.explode('image/', $imageName)[0].'image/'.$newVideoName);
                // } else {
                //     $newVideoName = $videoName;
                // }

                foreach (SizeImage::keys() as $value) {
                    try {
                        $video = $ffmpeg->open('storage/'.explode('image/', $imageName)[0].'image/'.$newVideoName);
                        $this->_createDir($folder."/$value/");
                        $size = (integer)explode('x' , $value)[0];
                        $size = $size % 2 ? $size + 1 : $size;
                        $scaleW = $height < $width  ? 'trunc(oh*a/2)*2' : $size;
                        $scaleH = $height > $width  ? 'trunc(ow/a/2)*2' : $size;


                        $video->filters()->custom("scale=w=$scaleW:h=$scaleH,crop=$size:$size")->framerate(new \FFMpeg\Coordinate\FrameRate(Video::FRAME),4)->clip(TimeCode::fromSeconds(Video::START), TimeCode::fromSeconds($duration >= Video::DURATION ? Video::DURATION : $duration ));
                        $video->save($format, 'storage/'.$folder."/$value/".$newVideoName);

                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
                $clip = $video->clip(TimeCode::fromSeconds(Video::START), TimeCode::fromSeconds(Video::DURATION));
                $clip->save($format, 'storage/'.$folder."/clip/".$newVideoName);
                return explode('image/', $imageName)[0].'image/'.$newVideoName;
            }

            return $imageName;
        }

        return '';

    }

}
