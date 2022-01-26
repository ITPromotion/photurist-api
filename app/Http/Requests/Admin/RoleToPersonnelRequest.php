<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiRequest;

class RoleToPersonnelRequest extends ApiRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'admin_id' => 'required|exists:admins,id',
            'role_id' => 'required|exists:roles,id',
        ];
    }
}
