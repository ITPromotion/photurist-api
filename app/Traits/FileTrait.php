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

    public function copyMediaContent($postcard) {
        if (count($postcard->mediaContents)) {
            foreach ($postcard->mediaContents as $mediaContent) {
                $fileName = explode('image', $mediaContent->link)[1];
                $pathOrigin = 'postcard/'.$postcard->id.'/image/'.$fileName;
                Storage::disk('public')->copy($mediaContent->link, $pathOrigin);
                foreach (SizeImage::keys() as $value) {
                    $path = 'postcard/'.$postcard->id.'/image/'.$value.$fileName;
                    $link = explode('image', $mediaContent->link);
                    Storage::disk('public')->copy($link[0].'image/'.$value.$link[1], $path);
                    if ($mediaContent->media_content_type == MediaContentType::VIDEO) {
                        $path = 'postcard/'.$postcard->id.'/image/frame/'.$value.$fileName;
                        Storage::disk('public')->copy($link[0].'image/frame/'.$value.$link[1], $path);
                    }
                }
                $mediaContent->update(['link' => $pathOrigin]);
            }
        }

        if ($postcard->audioData && $postcard->audioData->link) {
            $fileName = explode('audio',$postcard->audioData->link)[1];
            $pathOrigin = 'postcard/'.$postcard->id.'/audio/'.$fileName;
            Storage::disk('public')->copy($postcard->audioData->link, $pathOrigin);
            $postcard->audioData->update(['link' => $pathOrigin]);
        }
        return $postcard->mediaContents;
    }

}
