<?php

namespace App\Http\Requests\ClientApp\User;

use App\Http\Requests\ApiRequest;

class SetGeoDataRequest extends ApiRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'country_id' => 'required|string',
            'country_name' => 'required|string',
        ];
    }
}
