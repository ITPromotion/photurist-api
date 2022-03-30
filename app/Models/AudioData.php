<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AudioData extends Model
{
    use HasFactory;

    protected $fillable = [
      'link',
      'postcard_id',
      'media_content_id',
    ];
}
