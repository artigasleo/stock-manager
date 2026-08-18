<?php

namespace App\Http\Controllers;

use App\Actions\User\CreateUser;
use App\Actions\User\DeleteUser;
use App\Actions\User\ListUser;
use App\Actions\User\UpdateUser;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:users.view', only: ['index']),
            new Middleware('permission:users.edit', only: ['store', 'update', 'destroy']),
        ];
    }

    public function index(ListUser $action): View
    {
        return view('users.index', [
            'users' => $action->execute(),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function store(
        StoreUserRequest $request,
        CreateUser $action
    ): RedirectResponse {
        $action->execute($request);

        return redirect()->route('users.index')->with('success', 'Usuário criado.');
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        UpdateUser $action
    ): RedirectResponse {
        $action->execute($request, $user);

        return redirect()->route('users.index')->with('success', 'Usuário atualizado.');
    }

    public function destroy(
        User $user,
        DeleteUser $action
    ): RedirectResponse {
        $action->execute($user, auth()->user());

        return redirect()->route('users.index')->with('success', 'Usuário excluído.');
    }
}
