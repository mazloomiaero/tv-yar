<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class LoginCustomerPassword
{
    /**
     * @param  array{username: string, password: string}  $credentials
     */
    public function execute(array $credentials): Customer
    {
        $user = User::query()
            ->where('username', $credentials['username'])
            ->first();

        if (! $user instanceof User || ! Hash::check($credentials['password'], (string) $user->password)) {
            $this->fail($credentials['username']);
        }

        $customer = Customer::query()->where('user_id', $user->id)->first();

        if (! $customer instanceof Customer) {
            $this->fail($credentials['username']);
        }

        Auth::guard('web')->logout();
        Auth::guard('customer')->login($customer);

        return $customer;
    }

    private function fail(string $username): never
    {
        Log::notice('Customer password login failed.', [
            'username' => $username,
        ]);

        throw ValidationException::withMessages([
            'username' => __('These credentials do not match our records.'),
        ]);
    }
}
