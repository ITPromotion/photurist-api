<?php

namespace App\Http\Requests\ClientApp\Postcard;

use App\Http\Requests\ApiRequest;

class SetViewAdditionallyFromIdsRequest extends ApiRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'postcard_ids' => 'required|array',
            'postcard_ids.*' => 'required|numeric|exists:postcards,id',
        ];
    }
}
