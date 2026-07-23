<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'NOME' => [
                'required',
                'string',
                'max:100',
                'unique:CATEGORIAS,NOME',
            ],

            'ATIVO' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'NOME.required' => 'O nome da categoria é obrigatório.',
            'NOME.unique' => 'Já existe uma categoria com esse nome.',
            'NOME.max' => 'O nome deve possuir no máximo 100 caracteres.',

            'ATIVO.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',
        ];
    }
}