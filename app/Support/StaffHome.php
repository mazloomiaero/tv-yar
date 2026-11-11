<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;

final class StaffHome
{
    public function routeName(User $user): ?string
    {
        foreach (Panel::staff() as $panel) {
            $role = $panel->role();

            if ($role !== null && $user->hasRole($role)) {
                return $panel->value.'.dashboard';
            }
        }

        return null;
    }

    public function url(User $user): string
    {
        $routeName = $this->routeName($user);

        return $routeName !== null ? route($routeName) : url('/');
    }
}
