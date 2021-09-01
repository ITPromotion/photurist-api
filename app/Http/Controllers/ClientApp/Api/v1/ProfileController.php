<?php

namespace App\Http\Controllers\ClientApp\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function getProfile()
    {
       return Auth::user();
    }
}
