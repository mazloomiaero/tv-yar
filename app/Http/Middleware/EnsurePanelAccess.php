<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\OpsRoute;
use App\Support\Panel;
use App\Support\RoleName;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePanelAccess
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $panel): Response
    {
        $panelEnum = Panel::from($panel);
        $requiredGuard = $panelEnum->guard();
        $otherGuard = $requiredGuard === 'web' ? 'customer' : 'web';

        if ($request->user($requiredGuard) === null) {
            if ($request->user($otherGuard) !== null) {
                abort(403);
            }

            return redirect()->guest(
                $requiredGuard === 'customer'
                    ? route('customer.login')
                    : route('login')
            );
        }

        $role = $panelEnum->role();
        $staff = $request->user('web');

        if ($role !== null) {
            if (! $staff instanceof User) {
                abort(403);
            }

            $allowed = $staff->hasRole($role)
                || (
                    $staff->hasRole(RoleName::CompanyAdmin)
                    && in_array($panelEnum, [Panel::Operator, Panel::Technical, Panel::Accountant], true)
                );

            if (! $allowed) {
                abort(403);
            }
        }

        if (
            $panelEnum === Panel::Operator
            && $staff instanceof User
            && $staff->hasRole(RoleName::CompanyAdmin)
            && $request->isMethod('GET')
        ) {
            $currentName = $request->route()?->getName();

            if (is_string($currentName)) {
                $adminName = OpsRoute::adminEquivalent($currentName);

                if ($adminName !== null) {
                    $parameters = $request->route()?->parameters() ?? [];

                    return redirect()->route($adminName, array_merge($parameters, $request->query()));
                }
            }
        }

        return $next($request);
    }
}
