<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
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
            'isbn' => [
                'regex:/^(?:ISBN(?:-1[03])?:? )?(?=[0-9X]{10}$|(?=(?:[0-9]+[- ]){3})[- 0-9X]{13}$|97[89][0-9]{10}$|(?=(?:[0-9]+[- ]){4})[- 0-9]{17}$)(?:97[89][- ]?)?[0-9]{1,5}[- ]?[0-9]+[- ]?[0-9]+[- ]?[0-9X]$/'
                ],
            'title' => 'required|string',
            'description' => 'required|string',
            'authors' => 'required|array',
            'genres' => 'required|array',
            'price' => 'required|int',
            'published_year' => 'required|int',
            'pages' => 'sometimes|int',
        ];
    }

    public function messages()
    {
        return [
            'isbn.regex' => 'Некорректный формат ISBN',
            'title.required' => 'Введите название',
            'description.required' => 'Укажите описание',
            'author.required' => 'Укажите автора',
            'price.required' => 'Укажите стоимость',
            'published_year.required' => 'Укажите год публикации'
        ];
    }
}
