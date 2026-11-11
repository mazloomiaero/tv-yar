<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use App\Support\PermissionName;

final class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::CustomersView->value);
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->hasPermission(PermissionName::CustomersView->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::CustomersCreate->value);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->hasPermission(PermissionName::CustomersEdit->value);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->hasPermission(PermissionName::CustomersDelete->value);
    }
}
