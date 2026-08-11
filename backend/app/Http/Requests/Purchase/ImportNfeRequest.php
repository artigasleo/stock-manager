<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class ImportNfeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'xml_file' => [
                'required',
                'file',
                'mimes:xml',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'xml_file.required' => 'Selecione um arquivo XML.',
            'xml_file.mimes' => 'O arquivo precisa ser um XML.',
        ];
    }
}
