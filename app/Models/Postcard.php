<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Postcard extends Model
{
    use HasFactory, SoftDeletes;

    protected  $fillable = [
            'user_id',
        ];

    public function textData():HasOne
    {
        return $this->hasOne(TextData::class);
    }

    public function geoData():HasOne
    {
        return $this->hasOne(GeoData::class);
    }

    public function tagData():HasOne
    {
        return $this->hasOne(TagData::class);
    }

    public function mediaContents():HasMany
    {
        return $this->hasMany(MediaContent::class);
    }
}
