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
                $width = $video_dimensions->getWidth();
                $height =  $video_dimensions->getHeight();
                $xy =  (int)$width < $height ? ($height - $width) / 2 : ($width - $height) / 2;
                foreach (SizeImage::keys() as $value) {
                    try {
                        $video = $ffmpeg->open('storage/'.$imageName);
                        $this->_createDir($folder."/$value/");
                        $size = (integer)explode('x' , $value)[0];
                        $size = $size % 2 ? $size + 1 : $size;
                        $scaleW = (integer)$height < $width  ? $width : $size;
                        $scaleH = (integer)$height > $width  ? $height : $size;


                        $video->filters()->custom("crop=$size:$size,scale=w=$width:h=$hight");

                        $video->save(new \FFMpeg\Format\Video\X264(), 'storage/'.$folder."/$value/".$videoName);

                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
                $clip = $video->clip(TimeCode::fromSeconds(Video::START), TimeCode::fromSeconds(Video::DURATION));
                $clip->save(new \FFMpeg\Format\Video\X264(), 'storage/'.$folder."/clip/".$videoName);
            }

            return $imageName;
        }

        return '';

    }

}
