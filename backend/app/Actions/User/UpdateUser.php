<?php

namespace App\Actions\User;

use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;

class UpdateUser
{
    public function execute(UpdateUserRequest $request, User $user): User
    {
        $user->fill([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
        ]);

        if ($password = $request->validated('password')) {
            $user->password = $password;
        }

        $user->save();

        if ($role = $request->validated('role')) {
            $user->syncRoles([$role]);
        }

        return $user;
    }
}
