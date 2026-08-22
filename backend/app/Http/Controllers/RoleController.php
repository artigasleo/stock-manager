<?php

namespace App\Http\Controllers;

use App\Actions\Role\CreateRole;
use App\Actions\Role\DeleteRole;
use App\Actions\Role\ListRole;
use App\Actions\Role\UpdateRole;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:users.view', only: ['index']),
            new Middleware('permission:users.edit', only: ['store', 'update', 'destroy']),
        ];
    }

    public function index(ListRole $action): View
    {
        return view('roles.index', [
            'roles' => $action->execute(),
            'permissions' => Permission::pluck('name'),
            'modules' => config('modules'),
        ]);
    }

    public function store(
        StoreRoleRequest $request,
        CreateRole $action
    ): RedirectResponse {
        $action->execute($request);

        return redirect()->route('roles.index')->with('success', 'Função criada.');
    }

    public function update(
        UpdateRoleRequest $request,
        Role $role,
        UpdateRole $action
    ): RedirectResponse {
        $action->execute($request, $role);

        return redirect()->route('roles.index')->with('success', 'Função atualizada.');
    }

    public function destroy(
        Role $role,
        DeleteRole $action
    ): RedirectResponse {
        $action->execute($role);

        return redirect()->route('roles.index')->with('success', 'Função excluída.');
    }
}
