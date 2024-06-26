<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        return [
            'name' => 'required|string',
            'birthday' => 'sometimes|date',
            'image' => 'sometimes|file|size:3000|extensions:jpg,jpeg|mimes:jpg'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Укажите электронную почту',
            'email.unique' => 'Пользователь с такой электронной почтой уже существует',
            'name.required' => 'Укажите имя',
            'birthday.date' => 'Невалидная дата',
            'password.required' => 'Введите пароль',
            'password.confirmed' => 'Пароли не совпадают',
            'password.min' => 'Пароль должен состоять минимум из 5 символов',
            'image.size,' => 'Размер слишком большой',

        ];
    }
}
