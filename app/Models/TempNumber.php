<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempNumber extends Model
{
    use HasFactory;

    protected $fillable = ['otp_id', 'phone'];
}
