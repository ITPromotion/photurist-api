<?php

namespace App\Http\Requests\ClientApp\Postcard;

use App\Http\Requests\ApiRequest;

class SetStatusPostcardRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status:in:active,draft'
        ];
    }
}
