<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
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
                'max:100',
                Rule::unique('roles', 'name')
                    ->ignore($this->route('role')),
            ],

            'permissions' => [
                'sometimes',
                'array',
            ],

            'permissions.*' => [
                'string',
                'exists:permissions,name',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do papel é obrigatório.',
            'name.unique' => 'Já existe um papel com esse nome.',
        ];
    }
}
