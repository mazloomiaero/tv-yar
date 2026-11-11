<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Support\PermissionName;

final class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::PaymentsView->value);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->hasPermission(PermissionName::PaymentsView->value);
    }

    public function create(User $user, Invoice $invoice): bool
    {
        $serviceRequest = $invoice->serviceRequest;

        return $user->hasPermission(PermissionName::PaymentsCreate->value)
            && $serviceRequest !== null
            && $serviceRequest->isAssignedToUser($user);
    }

    public function verify(User $user, Payment $payment): bool
    {
        return $user->hasPermission(PermissionName::PaymentsVerify->value);
    }
}
