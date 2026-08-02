<?php

namespace App\Http\Controllers\Api;

use App\Actions\Auth\AuthenticateUser;
use App\Actions\Auth\LogoutUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function login(
        LoginRequest $request,
        AuthenticateUser $action
    ): UserResource
    {
        $user = $action->execute($request);

        return new UserResource($user);
    }

    public function logout(
        Request $request,
        LogoutUser $action
    ): Response
    {
        $action->execute($request);

        return response()->noContent();
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
