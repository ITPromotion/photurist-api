<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextData extends Model
{
    use HasFactory;

    protected $fillable = [
        'data',
        'postcard_id',
        'media_content_id',
    ];
}
