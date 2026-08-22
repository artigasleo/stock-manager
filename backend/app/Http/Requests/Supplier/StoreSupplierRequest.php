<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
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
                'unique:suppliers,document',
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

            'zip_code' => [
                'nullable',
                'string',
                'max:10',
            ],

            'street' => [
                'nullable',
                'string',
                'max:150',
            ],

            'number' => [
                'nullable',
                'string',
                'max:20',
            ],

            'complement' => [
                'nullable',
                'string',
                'max:100',
            ],

            'neighborhood' => [
                'nullable',
                'string',
                'max:100',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'state' => [
                'nullable',
                'string',
                'size:2',
            ],

            'country' => [
                'nullable',
                'string',
                'max:60',
            ],

            'instagram' => [
                'nullable',
                'string',
                'max:100',
            ],

            'state_registration' => [
                'nullable',
                'string',
                'max:30',
            ],

            'type' => [
                'nullable',
                'string',
                'max:50',
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
            'name.required' => 'O nome do fornecedor é obrigatório.',
            'document.unique' => 'Já existe um fornecedor com esse documento.',
            'email.email' => 'Informe um e-mail válido.',
            'state.size' => 'Use a sigla do estado com 2 letras (ex: SP).',
        ];
    }
}
