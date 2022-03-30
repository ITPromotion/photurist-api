<?php

namespace App\Http\Requests\ClientApp\User;

use Illuminate\Foundation\Http\FormRequest;

class AddContactsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ids' => 'array|required',
            'ids.*' => 'required|exists:users,id',
        ];
    }
}
