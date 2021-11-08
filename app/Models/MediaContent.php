<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\SizeImage;

class MediaContent extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'link',
        'media_content_type',
        'postcard_id',
    ];

    protected $appends = ['small', 'midle', 'large'];

    public function getSmallAttribute () {
        try {
            $url = explode('image/', $this->link);
            $small = SizeImage::SMALL;
            return $url[0]."image/$small/".$url[1];
        } catch (\Throwable $th) {
            return '';

        }
    }

    public function getMidleAttribute () {
        try {
            $url = explode('image/', $this->link);
            $midle = SizeImage::MIDLE;
            return $url[0]."image/$midle/".$url[1];
        } catch (\Throwable $th) {
            return '';

        }

    }

    public function getLargeAttribute () {
        try {
            $url = explode('image/', $this->link);
            $large = SizeImage::LARGE;
            return $url[0]."image/$large/".$url[1];
        } catch (\Throwable $th) {
            return '';

        }
    }



    public function textData():HasOne
    {
        return $this->hasOne(TextData::class);
    }

    public function geoData():HasOne
    {
        return $this->hasOne(GeoData::class);
    }

    public function audioData():HasOne
    {
        return $this->hasOne(AudioData::class);
    }


}
