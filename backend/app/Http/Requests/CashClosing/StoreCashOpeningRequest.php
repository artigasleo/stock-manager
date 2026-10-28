<?php

namespace App\Http\Requests\CashClosing;

use Illuminate\Foundation\Http\FormRequest;

class StoreCashOpeningRequest extends FormRequest
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

            'opening_cash' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'opening_cash_usd' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'A data é obrigatória.',
            'date.before_or_equal' => 'Não é possível abrir uma data futura.',
            'opening_cash.min' => 'O valor de abertura em reais não pode ser negativo.',
            'opening_cash_usd.min' => 'O valor de abertura em dólar não pode ser negativo.',
        ];
    }
}
