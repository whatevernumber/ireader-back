<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class UpdateUserRequest extends UserRequest
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
            'email' => [
                Rule::unique('users')->ignore($this->user),
            ],
            'old_password' => 'sometimes|current_password',
            'password' => 'exclude_without:old_password|confirmed|min:5',
        ];
    }

    public function messages()
    {
        return parent::messages() + [
            'email.unique' => 'Пользователь с такой электронной почтой уже существует',
            'old_password.current_password' => 'Пароли не совпадают',
        ];
    }
}
