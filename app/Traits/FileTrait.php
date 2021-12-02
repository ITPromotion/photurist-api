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

    public function saveMediaContent($image, $folder = 'postcard', $type = null): string
    {
        if ($image->isValid() && $image->getSize() !== 0) {
            $imageName = Storage::disk('public')->putFile($folder, $image);

            return $imageName;
        }

        return '';

    }

}
