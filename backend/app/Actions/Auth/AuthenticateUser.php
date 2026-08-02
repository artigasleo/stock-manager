<?php

namespace App\Actions\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticateUser
{
    public function execute(LoginRequest $request): User
    {
        if (! Auth::attempt($request->validated())) {
            throw ValidationException::withMessages([
                'email' => 'As credenciais informadas não conferem com nossos registros.',
            ]);
        }

        $request->session()->regenerate();

        return Auth::user();
    }
}
