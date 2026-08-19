<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ImportProductsXlsxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'xlsx_file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'xlsx_file.required' => 'Selecione um arquivo XLSX.',
            'xlsx_file.mimes' => 'O arquivo precisa ser uma planilha XLSX.',
        ];
    }
}
