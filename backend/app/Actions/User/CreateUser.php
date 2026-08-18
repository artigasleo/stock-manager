<?php

namespace App\Actions\User;

use App\Http\Requests\User\StoreUserRequest;
use App\Models\User;

class CreateUser
{
    public function execute(StoreUserRequest $request): User
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);

        $user->syncRoles($request->validated('roles') ?? []);

        return $user;
    }
}
