<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginCustomerPassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerPasswordLoginController extends Controller
{
    public function create(): View
    {
        return view('auth.customer-login');
    }

    public function store(LoginRequest $request, LoginCustomerPassword $login): RedirectResponse
    {
        $login->execute([
            'username' => $request->validated('username'),
            'password' => $request->validated('password'),
        ]);

        $request->session()->regenerate();

        return redirect()->intended(route('customer.dashboard'));
    }
}
