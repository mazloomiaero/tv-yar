<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Device;
use App\Models\User;
use App\Support\PermissionName;

final class DevicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::DevicesView->value);
    }

    public function view(User $user, Device $device): bool
    {
        return $user->hasPermission(PermissionName::DevicesView->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::DevicesCreate->value);
    }

    public function update(User $user, Device $device): bool
    {
        return $user->hasPermission(PermissionName::DevicesEdit->value);
    }
}
