<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
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

            'supplier_id' => [
                'required',
                'integer',
                'exists:suppliers,id',
            ],

            'invoice_number' => [
                'nullable',
                'string',
                'max:255',
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

            'items.*.unit_cost' => [
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
            'supplier_id.required' => 'O fornecedor é obrigatório.',
            'supplier_id.exists' => 'O fornecedor informado não existe.',
            'items.required' => 'Adicione ao menos um item.',
            'items.min' => 'Adicione ao menos um item.',
            'items.*.product_id.required' => 'O produto é obrigatório.',
            'items.*.product_id.exists' => 'O produto informado não existe.',
            'items.*.product_id.distinct' => 'Cada produto pode aparecer apenas uma vez na compra.',
            'items.*.quantity.required' => 'A quantidade é obrigatória.',
            'items.*.quantity.min' => 'A quantidade deve ser maior que zero.',
            'items.*.unit_cost.required' => 'O custo unitário é obrigatório.',
        ];
    }
}
