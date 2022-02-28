<?php

namespace App\Http\Requests\ClientApp\Postcard;

use App\Http\Requests\ApiRequest;

class SendPostcardToContactRequest extends ApiRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'postcard_id'   => 'required|numeric|exists:postcards,id',
            'contact_id'    => 'required|numeric|exists:users,id',
        ];
    }
}
