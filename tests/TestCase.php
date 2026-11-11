<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Role;
use App\Models\User;
use App\Support\RoleName;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function seedRbac(): void
    {
        $this->seed(RolePermissionSeeder::class);
    }

    protected function staffUser(RoleName $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        $user->roles()->attach(
            Role::query()->where('name', $role->value)->firstOrFail()
        );

        return $user;
    }
}
