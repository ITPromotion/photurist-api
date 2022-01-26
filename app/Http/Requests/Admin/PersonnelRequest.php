<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiRequest;

class PersonnelRequest extends ApiRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'name' => 'required|unique:admins|max:255',
            'phone' => 'required|unique:admins|max:20',
            'email' => 'required|unique:admins|max:255',
        ];
    }
}
