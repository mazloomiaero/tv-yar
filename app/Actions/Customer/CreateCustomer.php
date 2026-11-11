<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Models\Customer;
use App\Support\LoginAccount;
use Illuminate\Support\Facades\DB;

final class CreateCustomer
{
    public function __construct(private LoginAccount $loginAccount) {}

    /**
     * @param  array{
     *     customer_code: string,
     *     first_name: string,
     *     last_name: string,
     *     mobile: string,
     *     mobile_secondary: string,
     *     email: string,
     *     national_id: ?string,
     *     notes: string,
     *     status: string,
     *     portal_username?: ?string,
     *     portal_password?: ?string
     * }  $attributes
     */
    public function execute(array $attributes): Customer
    {
        return DB::transaction(function () use ($attributes) {
            $userId = null;
            $username = $attributes['portal_username'] ?? null;
            $password = $attributes['portal_password'] ?? null;

            if (is_string($username) && $username !== '' && is_string($password) && $password !== '') {
                $user = $this->loginAccount->createPortal(
                    trim($attributes['first_name'].' '.$attributes['last_name']),
                    $username,
                    $password,
                    $attributes['email'] !== '' ? $attributes['email'] : null,
                );
                $userId = $user->id;
            }

            return Customer::query()->create([
                'customer_code' => $attributes['customer_code'],
                'first_name' => $attributes['first_name'],
                'last_name' => $attributes['last_name'],
                'mobile' => $attributes['mobile'],
                'mobile_secondary' => $attributes['mobile_secondary'],
                'email' => $attributes['email'],
                'national_id' => $attributes['national_id'],
                'notes' => $attributes['notes'],
                'status' => $attributes['status'],
                'user_id' => $userId,
            ]);
        });
    }
}
