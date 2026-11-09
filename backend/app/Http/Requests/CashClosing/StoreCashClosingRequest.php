<?php

namespace App\Http\Requests\CashClosing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCashClosingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'moms_registered' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.payment_method' => [
                'required',
                'string',
                Rule::in(array_keys(config('payments.methods'))),
                'distinct',
            ],

            'items.*.counted_amount' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'A data é obrigatória.',
            'date.before_or_equal' => 'Não é possível fechar uma data futura.',
            'notes.max' => 'A observação deve ter no máximo 1000 caracteres.',
            'moms_registered.integer' => 'O número de cadastros deve ser um número inteiro.',
            'moms_registered.min' => 'O número de cadastros não pode ser negativo.',
            'items.*.counted_amount.required' => 'Informe o valor contado.',
            'items.*.counted_amount.min' => 'O valor contado não pode ser negativo.',
        ];
    }
}
