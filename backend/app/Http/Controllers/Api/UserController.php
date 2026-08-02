<?php

namespace App\Http\Controllers\Api;

use App\Actions\User\CreateUser;
use App\Actions\User\DeleteUser;
use App\Actions\User\ListUser;
use App\Actions\User\UpdateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function index(ListUser $action): AnonymousResourceCollection
    {
        return UserResource::collection($action->execute());
    }

    public function store(
        StoreUserRequest $request,
        CreateUser $action
    ): UserResource
    {
        $user = $action->execute($request);

        return new UserResource($user);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user->load('roles'));
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        UpdateUser $action
    ): UserResource
    {
        $user = $action->execute($request, $user);

        return new UserResource($user);
    }

    public function destroy(
        User $user,
        DeleteUser $action
    ): Response
    {
        $action->execute($user);

        return response()->noContent();
    }
}
