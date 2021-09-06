<?php

namespace App\Http\Requests\ClientApp\Authorization;

use App\Http\Requests\ApiRequest;

class CheckOtpRequest extends ApiRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'phone_number' => 'sometimes|numeric|exists:temp_numbers,phone',
            'code_otp'     => 'required|digits:6',
            'login'        => 'sometimes|string|nullable|exists:users,login',
        ];
    }
}
