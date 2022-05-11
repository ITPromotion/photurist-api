<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionallyView extends Model
{
    use HasFactory;

    protected $fillable = [
        'postcard_id',
        'user_id',
        'view',
    ];
}
