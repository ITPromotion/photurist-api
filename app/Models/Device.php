<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'type',
    ];

    public function getTokenUsers ($userIds) {
        return self::whereIn('user_id', $userIds)->pluck('token')->toArray();
    }
}
