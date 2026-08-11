<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
                'max:150',
            ],

            'document' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('customers', 'document')
                    ->ignore($this->route('customer')),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'email' => [
                'nullable',
                'email',
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
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
            'name.required' => 'O nome do cliente é obrigatório.',
            'document.unique' => 'Já existe um cliente com esse CPF.',
            'email.email' => 'Informe um e-mail válido.',
        ];
    }
}
