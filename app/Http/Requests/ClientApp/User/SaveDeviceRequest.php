<?php

namespace App\Http\Requests\ClientApp\User;

use App\Http\Requests\ApiRequest;

class SaveDeviceRequest extends ApiRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'token' => 'required',
            'type' => 'required|in:ios,android',
        ];
    }
}
