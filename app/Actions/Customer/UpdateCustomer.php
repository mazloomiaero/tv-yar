<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Models\Customer;
use App\Support\LoginAccount;
use Illuminate\Support\Facades\DB;

final class UpdateCustomer
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
    public function execute(Customer $customer, array $attributes): Customer
    {
        return DB::transaction(function () use ($customer, $attributes) {
            $customer->fill([
                'customer_code' => $attributes['customer_code'],
                'first_name' => $attributes['first_name'],
                'last_name' => $attributes['last_name'],
                'mobile' => $attributes['mobile'],
                'mobile_secondary' => $attributes['mobile_secondary'],
                'email' => $attributes['email'],
                'national_id' => $attributes['national_id'],
                'notes' => $attributes['notes'],
                'status' => $attributes['status'],
            ]);
            $customer->save();

            $username = $attributes['portal_username'] ?? null;
            $password = $attributes['portal_password'] ?? null;
            $displayName = trim($attributes['first_name'].' '.$attributes['last_name']);
            $email = $attributes['email'] !== '' ? $attributes['email'] : null;

            if ($customer->user_id !== null) {
                $user = $customer->user;

                if ($user !== null && is_string($username) && $username !== '') {
                    $this->loginAccount->update(
                        $user,
                        $displayName,
                        $username,
                        is_string($password) && $password !== '' ? $password : null,
                        $email,
                    );
                }

                return $customer;
            }

            if (is_string($username) && $username !== '' && is_string($password) && $password !== '') {
                $user = $this->loginAccount->createPortal($displayName, $username, $password, $email);
                $customer->user_id = $user->id;
                $customer->save();
            }

            return $customer;
        });
    }
}
