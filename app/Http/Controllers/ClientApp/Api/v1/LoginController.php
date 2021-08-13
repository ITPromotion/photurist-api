<?php

namespace App\Http\Controllers\Admin\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Sto\Sto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $login = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        if(!Auth::attempt($login)){
            return response(['message' => 'Invalid login credentials.'],403);
        }

        $accessToken = Auth::user()->createToken('authToken')->accessToken;

        return response(['user' => Auth::user(), 'access_token' => $accessToken]);
    }

    public function userInfo()
    {

        //app()->setLocale('nl');
        $sto  = new Sto();
        $sto
            ->setTranslation('name', 'en', 'Name in English')
            ->setTranslation('name', 'nl', 'Naam in het Nederlands')
            ->save();

        $stos = Sto::get();
        return $sto->name;
    }
}
