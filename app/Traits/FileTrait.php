<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Webpatser\Uuid\Uuid;
use Intervention\Image\Facades\Image;
use Illuminate\Filesystem\Filesystem;
use App\Enums\SizeImage;
use App\Enums\MediaContentType;


trait FileTrait
{
    private function _createDir($file)
    {
        return Storage::disk('public')->makeDirectory($file);
    }

    public function saveMediaContent($image, $folder = 'postcard', $type = null): string
    {
        if ($image->isValid() && $image->getSize() !== 0) {

            if (isset($type) && MediaContentType::PHOTO == $type) {
                $imgName =  $image->getClientOriginalName();
                $img = Image::make($image);

                foreach (SizeImage::keys() as $value) {
                    $this->_createDir($folder."/$value/");
                    $size = explode('x' , $value)[0];
                    $img->resize($size, $size);
                    $img->save('storage/'.$folder."/$value/".$imgName);
                }
                $imageName = Storage::disk('public')->putFile($folder, $image);
            }

            $imageName = Storage::disk('public')->putFile($folder, $image);

            return $imageName;
        }

        return '';

    }

}
