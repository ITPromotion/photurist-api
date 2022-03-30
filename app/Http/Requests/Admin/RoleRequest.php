<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiRequest;

class RoleRequest extends ApiRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:roles|max:255'
        ];
    }
}
