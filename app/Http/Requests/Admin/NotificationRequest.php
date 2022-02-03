<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiRequest;

class NotificationRequest extends ApiRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|max:255',
            'body' => 'required',
            'user_id' => 'required|exists:users,id',
        ];
    }
}
