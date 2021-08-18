<?php


namespace App\Http\Requests;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiRequest extends FormRequest
{
    public function after($validator): void
    {

    }

    protected function failedValidation(Validator $validator)
    {

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            throw new HttpResponseException(response()->json(['errors' => $errors], 203));
        }

    }
}
