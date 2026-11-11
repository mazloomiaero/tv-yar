<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\PermissionName;

final class ServiceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::RequestsView->value);
    }

    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->hasPermission(PermissionName::RequestsView->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(PermissionName::RequestsCreate->value);
    }

    public function update(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->hasPermission(PermissionName::RequestsEdit->value);
    }

    public function changeStatus(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->hasPermission(PermissionName::RequestsChangeStatus->value);
    }

    public function close(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->hasPermission(PermissionName::RequestsClose->value);
    }

    public function assign(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->hasPermission(PermissionName::RequestsAssign->value);
    }
}
