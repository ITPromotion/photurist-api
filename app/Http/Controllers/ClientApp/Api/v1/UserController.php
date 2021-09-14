<?php

namespace App\Http\Controllers\ClientApp\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientApp\User\SetGeoDataRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function setGeoData(SetGeoDataRequest $request)
    {
        Auth::user()->update($request->all());
    }
}
