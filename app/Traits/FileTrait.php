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
            $imageName = Storage::disk('public')->putFile($folder, $image);

            if (isset($type) && MediaContentType::PHOTO == $type) {
                $imgName = explode('image/', $imageName)[1];
                $img = Image::make($image);

                foreach (SizeImage::keys() as $value) {
                    $this->_createDir($folder."/$value/");
                    $size = explode('x' , $value)[0];
                    $img->resize($size, $size);
                    $img->save('storage/'.$folder."/$value/".$imgName);
                }
            }

            return $imageName;
        }

        return '';

    }

}
