<?php

namespace App\Http\Controllers;

use App\Actions\Auth\AuthenticateUser;
use App\Actions\Auth\LogoutUser;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(
        LoginRequest $request,
        AuthenticateUser $action
    ): RedirectResponse {
        $action->execute($request);

        return redirect()->intended(route($this->landingRoute($request->user())));
    }

    // Cai pro dashboard por padrão, mas se o papel do usuário não tiver
    // dashboard.view, manda ele pra primeira tela que ele realmente pode ver.
    private function landingRoute(User $user): string
    {
        foreach (config('modules') as $slug => $module) {
            if ($user->can("{$slug}.view")) {
                return $slug === 'dashboard' ? 'dashboard' : "{$slug}.index";
            }
        }

        return 'dashboard';
    }

    public function destroy(
        Request $request,
        LogoutUser $action
    ): RedirectResponse {
        $action->execute($request);

        return redirect()->route('login');
    }
}
