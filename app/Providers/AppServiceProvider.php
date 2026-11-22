<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Customer;
use App\Models\Device;
use App\Models\Feedback;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ServiceReport;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Policies\CustomerPolicy;
use App\Policies\DevicePolicy;
use App\Policies\FeedbackPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ServiceReportPolicy;
use App\Policies\ServiceRequestPolicy;
use App\Services\Payment\FakeGateway;
use App\Services\Payment\GatewayInterface;
use App\Services\Payment\ZarinpalGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(GatewayInterface::class, function ($app): GatewayInterface {
            $driver = (string) config('services.payment.driver', 'zarinpal');

            if ($driver === 'fake' || $app->environment('testing')) {
                return $app->make(FakeGateway::class);
            }

            return $app->make(ZarinpalGateway::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            config(['app.debug' => false]);
        }

        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(Device::class, DevicePolicy::class);
        Gate::policy(ServiceRequest::class, ServiceRequestPolicy::class);
        Gate::policy(ServiceReport::class, ServiceReportPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Feedback::class, FeedbackPolicy::class);

        Gate::before(function (mixed $user, string $ability): ?bool {
            if (! $user instanceof User) {
                return null;
            }

            if (! str_contains($ability, '.')) {
                return null;
            }

            if ($user->hasPermission($ability)) {
                return true;
            }

            return null;
        });

        RateLimiter::for('internal-login', function (Request $request) {
            return Limit::perMinute(5)->by(
                strtolower((string) $request->input('username')).'|'.$request->ip()
            );
        });

        RateLimiter::for('internal-otp', function (Request $request) {
            return Limit::perMinute(5)->by(
                (string) $request->input('mobile').'|'.$request->ip()
            );
        });

        RateLimiter::for('customer-otp', function (Request $request) {
            return Limit::perMinute(5)->by(
                (string) $request->input('mobile').'|'.$request->ip()
            );
        });

        RateLimiter::for('website-intake', function (Request $request) {
            return Limit::perMinute(30)->by(
                (string) $request->header('X-Intake-Key', '').'|'.$request->ip()
            );
        });

        RateLimiter::for('customer-login', function (Request $request) {
            return Limit::perMinute(5)->by(
                strtolower((string) $request->input('username')).'|'.$request->ip()
            );
        });

        RateLimiter::for('operator-customers', function (Request $request) {
            $user = $request->user('web');
            $key = $user instanceof User ? (string) $user->getAuthIdentifier() : (string) $request->ip();

            return Limit::perMinute(60)->by($key.'|'.$request->ip());
        });

        RateLimiter::for('operator-devices', function (Request $request) {
            $user = $request->user('web');
            $key = $user instanceof User ? (string) $user->getAuthIdentifier() : (string) $request->ip();

            return Limit::perMinute(60)->by($key.'|'.$request->ip());
        });

        RateLimiter::for('operator-requests', function (Request $request) {
            $user = $request->user('web');
            $key = $user instanceof User ? (string) $user->getAuthIdentifier() : (string) $request->ip();

            return Limit::perMinute(60)->by($key.'|'.$request->ip());
        });

        RateLimiter::for('admin-catalog', function (Request $request) {
            $user = $request->user('web');
            $key = $user instanceof User ? (string) $user->getAuthIdentifier() : (string) $request->ip();

            return Limit::perMinute(60)->by($key.'|'.$request->ip());
        });

        RateLimiter::for('technical-requests', function (Request $request) {
            $user = $request->user('web');
            $key = $user instanceof User ? (string) $user->getAuthIdentifier() : (string) $request->ip();

            return Limit::perMinute(60)->by($key.'|'.$request->ip());
        });

        RateLimiter::for('technician-assignments', function (Request $request) {
            $user = $request->user('web');
            $key = $user instanceof User ? (string) $user->getAuthIdentifier() : (string) $request->ip();

            return Limit::perMinute(60)->by($key.'|'.$request->ip());
        });

        RateLimiter::for('technician-reports', function (Request $request) {
            $user = $request->user('web');
            $key = $user instanceof User ? (string) $user->getAuthIdentifier() : (string) $request->ip();

            return Limit::perMinute(60)->by($key.'|'.$request->ip());
        });

        RateLimiter::for('accountant-invoices', function (Request $request) {
            $user = $request->user('web');
            $key = $user instanceof User ? (string) $user->getAuthIdentifier() : (string) $request->ip();

            return Limit::perMinute(60)->by($key.'|'.$request->ip());
        });

        RateLimiter::for('accountant-payments', function (Request $request) {
            $user = $request->user('web');
            $key = $user instanceof User ? (string) $user->getAuthIdentifier() : (string) $request->ip();

            return Limit::perMinute(30)->by($key.'|'.$request->ip());
        });

        RateLimiter::for('technician-payments', function (Request $request) {
            $user = $request->user('web');
            $key = $user instanceof User ? (string) $user->getAuthIdentifier() : (string) $request->ip();

            return Limit::perMinute(30)->by($key.'|'.$request->ip());
        });

        RateLimiter::for('customer-payments', function (Request $request) {
            $user = $request->user('customer');
            $key = $user instanceof Customer ? (string) $user->getAuthIdentifier() : (string) $request->ip();

            return Limit::perMinute(10)->by($key.'|'.$request->ip());
        });

        RateLimiter::for('operator-feedback', function (Request $request) {
            $user = $request->user('web');
            $key = $user instanceof User ? (string) $user->getAuthIdentifier() : (string) $request->ip();

            return Limit::perMinute(60)->by($key.'|'.$request->ip());
        });

        RateLimiter::for('customer-portal', function (Request $request) {
            $user = $request->user('customer');
            $key = $user instanceof Customer ? (string) $user->getAuthIdentifier() : (string) $request->ip();

            return Limit::perMinute(60)->by($key.'|'.$request->ip());
        });
    }
}
