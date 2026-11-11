<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Invoice;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\PermissionName;

final class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::InvoicesView->value);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission(PermissionName::InvoicesView->value);
    }

    public function create(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->hasPermission(PermissionName::InvoicesCreate->value)
            && $serviceRequest->exists;
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission(PermissionName::InvoicesEdit->value);
    }
}
