<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ServiceReport;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\PermissionName;

final class ServiceReportPolicy
{
    public function view(User $user, ServiceReport $serviceReport): bool
    {
        return $user->hasPermission(PermissionName::ReportsView->value)
            && $this->isAssignedTechnician($user, $serviceReport->serviceRequest);
    }

    public function create(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->hasPermission(PermissionName::ReportsCreate->value)
            && $this->isAssignedTechnician($user, $serviceRequest);
    }

    public function update(User $user, ServiceReport $serviceReport): bool
    {
        return $user->hasPermission(PermissionName::ReportsEdit->value)
            && $this->isAssignedTechnician($user, $serviceReport->serviceRequest);
    }

    private function isAssignedTechnician(User $user, ?ServiceRequest $serviceRequest): bool
    {
        if (! $serviceRequest instanceof ServiceRequest) {
            return false;
        }

        return $serviceRequest->isAssignedToUser($user);
    }
}
