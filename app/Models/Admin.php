<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;



class Admin extends Authenticatable
{
    use HasApiTokens,HasFactory, Notifiable, HasFactory,HasRoles;

    protected $fillable = [
        'name',
        'email',
        'phone',
    ];
}
