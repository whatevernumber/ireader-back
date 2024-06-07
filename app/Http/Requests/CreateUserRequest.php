<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends UserRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return parent::rules() + [
            'email' => 'required|unique:users',
            'password' => 'required|confirmed|min:5',
        ];
    }

    public function messages()
    {
        return parent::messages() + [
            'email.required' => 'Укажите электронную почту',
            'email.unique' => 'Пользователь с такой электронной почтой уже существует',
        ];
    }
}
