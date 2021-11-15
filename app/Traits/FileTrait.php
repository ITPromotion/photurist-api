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
                    $img->resize($height ? $size : null, $width ? $size : null, function ($constraint) {});
                    $img->crop($size, $size);
                    $img->save('storage/'.$folder."/$value/".$imgName);
                    $img->reset();
                }
            } else if (isset($type) && MediaContentType::VIDEO == $type) {
                $videoName = explode('image/', $imageName)[1];
                $ffmpeg = FFMpeg::create();
                $video = $ffmpeg->open('storage/'.$imageName);
                $this->_createDir($folder."/clip/");
                foreach (SizeImage::keys() as $value) {

                    try {
                        $this->_createDir($folder."/$value/");
                        $size = explode('x' , $value)[0];
                        $size = $size % 2 ? $size + 1 : $size;
                        $video->filters()
                        ->resize(new \FFMpeg\Coordinate\Dimension($size, $size))
                        ->synchronize();

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
