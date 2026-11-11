<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

final class OpsRoute
{
    public static function prefix(): string
    {
        if (request()->routeIs('admin.*')) {
            return 'admin.';
        }

        $user = auth('web')->user();

        if ($user instanceof User && $user->hasRole(RoleName::CompanyAdmin)) {
            return 'admin.';
        }

        return 'operator.';
    }

    public static function name(string $suffix): string
    {
        return self::prefix().$suffix;
    }

    public static function to(string $suffix, mixed $parameters = []): string
    {
        return route(self::name($suffix), $parameters);
    }

    public static function layout(): string
    {
        return self::prefix() === 'admin.' ? 'layouts.admin' : 'layouts.operator';
    }

    public static function is(string $pattern): bool
    {
        return request()->routeIs('admin.'.$pattern) || request()->routeIs('operator.'.$pattern);
    }

    public static function adminEquivalent(string $operatorRouteName): ?string
    {
        if (! str_starts_with($operatorRouteName, 'operator.')) {
            return null;
        }

        $adminName = 'admin.'.substr($operatorRouteName, strlen('operator.'));

        return Route::has($adminName) ? $adminName : null;
    }
}
