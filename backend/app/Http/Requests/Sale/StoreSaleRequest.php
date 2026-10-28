<?php

namespace App\Http\Requests\Sale;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit_id' => [
                'required',
                'integer',
                'exists:units,id',
            ],

            'customer_id' => [
                'nullable',
                'integer',
                'exists:customers,id',
            ],

            'seller_id' => [
                'required',
                'integer',
                'exists:sellers,id',
            ],

            'payments' => [
                'required',
                'array',
                'min:1',
            ],

            'payments.*.payment_method' => [
                'required',
                'string',
                Rule::in(array_keys(config('payments.methods'))),
            ],

            'payments.*.amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
                'distinct',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.discount_percent' => [
                'nullable',
                'numeric',
                'between:0,100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'unit_id.required' => 'A unidade é obrigatória.',
            'unit_id.exists' => 'A unidade informada não existe.',
            'customer_id.exists' => 'O cliente informado não existe.',
            'seller_id.required' => 'O vendedor é obrigatório.',
            'seller_id.exists' => 'O vendedor informado não existe.',
            'payments.required' => 'Adicione ao menos uma forma de pagamento.',
            'payments.min' => 'Adicione ao menos uma forma de pagamento.',
            'payments.*.payment_method.required' => 'A forma de pagamento é obrigatória.',
            'payments.*.payment_method.in' => 'Forma de pagamento inválida.',
            'payments.*.amount.required' => 'O valor do pagamento é obrigatório.',
            'payments.*.amount.min' => 'O valor do pagamento deve ser maior que zero.',
            'items.required' => 'Adicione ao menos um item.',
            'items.min' => 'Adicione ao menos um item.',
            'items.*.product_id.required' => 'O produto é obrigatório.',
            'items.*.product_id.exists' => 'O produto informado não existe.',
            'items.*.product_id.distinct' => 'Cada produto pode aparecer apenas uma vez na venda.',
            'items.*.quantity.required' => 'A quantidade é obrigatória.',
            'items.*.quantity.min' => 'A quantidade deve ser maior que zero.',
            'items.*.discount_percent.between' => 'O desconto deve estar entre 0 e 100%.',
        ];
    }
}
