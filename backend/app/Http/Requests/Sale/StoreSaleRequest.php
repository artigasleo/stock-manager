<?php

namespace App\Http\Requests\Sale;

use Illuminate\Foundation\Http\FormRequest;

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

            'payment_method' => [
                'nullable',
                'string',
                'in:cash,pix,debit_card,credit_card,other',
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

            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'unit_id.required' => 'A unidade é obrigatória.',
            'unit_id.exists' => 'A unidade informada não existe.',
            'customer_id.exists' => 'O cliente informado não existe.',
            'payment_method.in' => 'Forma de pagamento inválida.',
            'items.required' => 'Adicione ao menos um item.',
            'items.min' => 'Adicione ao menos um item.',
            'items.*.product_id.required' => 'O produto é obrigatório.',
            'items.*.product_id.exists' => 'O produto informado não existe.',
            'items.*.product_id.distinct' => 'Cada produto pode aparecer apenas uma vez na venda.',
            'items.*.quantity.required' => 'A quantidade é obrigatória.',
            'items.*.quantity.min' => 'A quantidade deve ser maior que zero.',
            'items.*.unit_price.required' => 'O preço é obrigatório.',
        ];
    }
}
