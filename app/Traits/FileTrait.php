<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Webpatser\Uuid\Uuid;
use Intervention\Image\Facades\Image;


trait FileTrait
{
    public function saveMediaContent($image, $folder = 'postcard'): string
    {
        if ($image->isValid() && $image->getSize() !== 0) {

            $imageName = Storage::putFile('public/'.$folder, $image);

            return $imageName;
        }

        return '';

    }

}
