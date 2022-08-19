<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RegisterIndividualRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' =>'required|regex:/^[A-Za-z. ]{2,255}$/',
            'last_name' =>'required|regex:/^[A-Za-z. ]{2,255}$/',
            'email' => 'required|email|max:256|unique:users',
            'password' => 'required|min:8|max:80',
            'pseudonym' => 'required|alpha_num|max:200|unique:users',
            'telegram' => 'nullable|regex:/^[@][a-zA-Z0-9_-]+$/',
            // 'validatorAddress' => 'required',
        ];
    }
}
