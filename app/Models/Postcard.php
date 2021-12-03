<?php

namespace App\Models;

use App\Enums\MailingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Postcard extends Model
{
    use HasFactory, SoftDeletes;

    protected  $fillable = [
            'user_id',
            'status',
            'start_mailing',
            'interval_send',
            'interval_wait',
            'radius',
            'lat',
            'lng',
            'countries',
            'regions',
            'cities',
        ];
    protected $appends = ['favorite'];

    public function getFavoriteAttribute() {
        $favorite = $this->favorites()->wherePivot('user_id', \Auth::user()->id ?? null)->first();
        if ($favorite) {
            return true;
        }
        return false;
    }
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function textData():HasOne
    {
        return $this->hasOne(TextData::class);
    }

    public function audioData():HasOne
    {
        return $this->hasOne(AudioData::class);
    }

    public function geoData():HasOne
    {
        return $this->hasOne(GeoData::class);
    }

    public function tagData():HasMany
    {
        return $this->hasMany(TagData::class);
    }

    public function mediaContents():HasMany
    {
        return $this->hasMany(MediaContent::class);
    }

    public function delete()
    {
        $this->textData()->delete();
        $this->geoData()->delete();
        $this->tagData()->delete();
        $this->mediaContents()->delete();
        DB::table('postcards_mailings')
            ->where('postcard_id',$this->id)->delete();
        $this->users()->detach();

        return parent::delete();
    }

    public function lastMailing()
    {
        return DB::table('postcards_mailings')
            ->where('postcard_id',$this->id)
            ->where('status',MailingType::ACTIVE)
            ->orderBy('start','desc')
            ->first();
    }

    public function allMailingsUserIds () {
        return DB::table('postcards_mailings')
        ->where('postcard_id',$this->id)
        ->where('status',MailingType::ACTIVE)
        ->pluck('user_id')->toArray();
    }

    public function firstMailing()
    {
        return DB::table('postcards_mailings')
            ->where('postcard_id',$this->id)
            ->where('status',MailingType::ACTIVE)
            ->orderBy('start','asc')
            ->first();
    }

    public function getDevice() {
        return Device::where('user_id', '!=', $this->user_id)->pluck('token')->toArray();
    }

    public function favorites() {
        return $this->BelongsToMany(User::class, 'favorites')->withPivot('user_id', 'postcard_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'postcards_users');
    }

    public function userPostcardNotifications()
    {
        return $this->BelongsToMany(User::class, 'user_postcard_notifications');
    }
}
