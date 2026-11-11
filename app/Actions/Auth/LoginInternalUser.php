<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use App\Support\StaffHome;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class LoginInternalUser
{
    public function __construct(private StaffHome $staffHome) {}

    /**
     * @param  array{username: string, password: string}  $credentials
     */
    public function execute(array $credentials): User
    {
        $authenticated = Auth::guard('web')->attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ]);

        if (! $authenticated) {
            Log::notice('Internal login failed.', [
                'username' => $credentials['username'],
            ]);
            $this->fail();
        }

        Auth::guard('customer')->logout();

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($this->staffHome->routeName($user) === null) {
            Auth::guard('web')->logout();
            $this->fail();
        }

        return $user;
    }

    private function fail(): never
    {
        throw ValidationException::withMessages([
            'username' => __('These credentials do not match our records.'),
        ]);
    }
}
