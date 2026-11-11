<?php

declare(strict_types=1);

use App\Http\Middleware\EnsurePanelAccess;
use App\Http\Middleware\EnsurePermission;
use App\Models\User;
use App\Support\StaffHome;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')->group(base_path('routes/auth.php'));

            Route::middleware(['web', 'panel:admin'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            Route::middleware(['web', 'panel:operator'])
                ->prefix('operator')
                ->name('operator.')
                ->group(base_path('routes/operator.php'));

            Route::middleware(['web', 'panel:technical'])
                ->prefix('technical')
                ->name('technical.')
                ->group(base_path('routes/technical.php'));

            Route::middleware(['web', 'panel:accountant'])
                ->prefix('accountant')
                ->name('accountant.')
                ->group(base_path('routes/accountant.php'));

            Route::middleware(['web', 'panel:technician'])
                ->prefix('technician')
                ->name('technician.')
                ->group(base_path('routes/technician.php'));

            Route::middleware(['web', 'panel:customer'])
                ->prefix('customer')
                ->name('customer.')
                ->group(base_path('routes/customer.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'panel' => EnsurePanelAccess::class,
            'permission' => EnsurePermission::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request): string {
            if ($request->is('customer') || $request->is('customer/*') || $request->routeIs('customer.*')) {
                return route('customer.login');
            }

            return route('login');
        });

        $middleware->redirectUsersTo(function (Request $request): string {
            if (Auth::guard('customer')->check() && ! Auth::guard('web')->check()) {
                return route('customer.dashboard');
            }

            $staff = Auth::guard('web')->user();

            if ($staff instanceof User) {
                return app(StaffHome::class)->url($staff);
            }

            return url('/');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReportDuplicates();

        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
            'otp',
            'card_number',
            'cvv',
            'cvv2',
            'pin',
        ]);

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->expectsJson(),
        );
    })->create();
