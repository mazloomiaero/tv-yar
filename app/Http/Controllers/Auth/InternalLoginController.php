<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginInternalUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\StaffHome;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InternalLoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, LoginInternalUser $login, StaffHome $staffHome): RedirectResponse
    {
        $user = $login->execute([
            'username' => $request->validated('username'),
            'password' => $request->validated('password'),
        ]);

        $request->session()->regenerate();

        return redirect()->intended($staffHome->url($user));
    }
}
