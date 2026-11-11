<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Feedback;
use App\Models\User;
use App\Support\PermissionName;

final class FeedbackPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionName::FeedbackView->value);
    }

    public function view(User $user, Feedback $feedback): bool
    {
        return $user->hasPermission(PermissionName::FeedbackView->value);
    }
}
