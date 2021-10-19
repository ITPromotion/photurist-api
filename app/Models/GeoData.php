<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoData extends Model
{
    use HasFactory;

    protected $fillable = [
      'lat',
      'lng',
      'address',
      'media_content_id',
      'postcard_id',
    ];
}
