<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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

            'code' => [
                'required',
                'string',
                'max:50',
                'unique:products,code',
            ],

            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],

            'supplier_id' => [
                'nullable',
                'integer',
                'exists:suppliers,id',
            ],

            'quantity' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'min_stock' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'expiration_date' => [
                'nullable',
                'date',
            ],

            'cost_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'sale_price' => [
                'required',
                'numeric',
                'min:0',
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
            'name.required' => 'O nome do produto é obrigatório.',
            'code.required' => 'O código do produto é obrigatório.',
            'code.unique' => 'Já existe um produto com esse código.',
            'category_id.required' => 'A categoria é obrigatória.',
            'category_id.exists' => 'A categoria informada não existe.',
            'supplier_id.exists' => 'O fornecedor informado não existe.',
            'cost_price.required' => 'O preço de custo é obrigatório.',
            'sale_price.required' => 'O preço de venda é obrigatório.',
        ];
    }
}
