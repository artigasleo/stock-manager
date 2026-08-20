<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSellerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('sellers', 'name')
                    ->ignore($this->route('seller')),
            ],

            'active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do vendedor é obrigatório.',
            'name.unique' => 'Já existe um vendedor com esse nome.',
            'name.max' => 'O nome deve possuir no máximo 100 caracteres.',

            'active.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',
        ];
    }
}
