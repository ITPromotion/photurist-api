<?php

namespace App\Models;

use App\Enums\ContactStatuses;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;


class User extends Authenticatable
{
    use HasApiTokens,HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'login',
        'status',
        'lat',
        'lng',
        'country_id',
        'country_name',
        'region_id',
        'region_name',
        'city_id',
        'city_name',
        'avatar'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function postcards():HasMany
    {
        return $this->hasMany(Postcard::class);
    }

    public function postcardFavorites():BelongsToMany
    {
        return $this->belongsToMany(Postcard::class, 'postcards_users');
    }

    public function device ():HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function favorites():BelongsToMany {
        return $this->BelongsToMany(Postcard::class, 'favorites')->withPivot('user_id', 'postcard_id');
    }

    public function userPostcardNotifications():BelongsToMany
    {
        return $this->BelongsToMany(Postcard::class, 'user_postcard_notifications');
    }

    public function textData():HasOne
    {
        return $this->hasOne(TextData::class);
    }

    public function audioData():HasOne
    {
        return $this->hasOne(AudioData::class);
    }

    public function mediaContents():HasMany
    {
        return $this->hasMany(MediaContent::class);
    }

    public function contacts():BelongsToMany
    {
        return $this->belongsToMany(User::class, 'contacts', 'user_id', 'contact_id')->withPivot('status');
    }

    public function blockContacts():BelongsToMany
    {
        return $this->contacts()->wherePivot('status', ContactStatuses::BLOCK);
    }

}
