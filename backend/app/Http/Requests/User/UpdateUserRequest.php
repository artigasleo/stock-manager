<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'email',
                Rule::unique('users', 'email')
                    ->ignore($this->route('user')),
            ],

            'password' => [
                'sometimes',
                'nullable',
                'string',
                'min:8',
            ],

            'roles' => [
                'sometimes',
                'array',
            ],

            'roles.*' => [
                'string',
                'exists:roles,name',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Já existe um usuário com esse e-mail.',
            'password.min' => 'A senha deve possuir no mínimo 8 caracteres.',
            'roles.*.exists' => 'Um dos papéis informados não existe.',
        ];
    }
}
