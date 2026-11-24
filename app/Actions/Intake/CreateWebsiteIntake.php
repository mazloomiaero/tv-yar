<?php

declare(strict_types=1);

namespace App\Actions\Intake;

use App\Actions\Customer\CreateCustomer;
use App\Actions\Device\CreateDevice;
use App\Actions\ServiceRequest\CreateServiceRequest;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\WebsiteIntakeSettingsStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

final class CreateWebsiteIntake
{
    public function __construct(
        private WebsiteIntakeSettingsStore $settings,
        private CreateCustomer $createCustomer,
        private CreateDevice $createDevice,
        private CreateServiceRequest $createServiceRequest,
    ) {}

    /**
     * @param  array{
     *     full_name: string,
     *     mobile: string,
     *     brand: string,
     *     problem: string,
     *     address?: string|null
     * }  $payload
     */
    public function execute(array $payload): ServiceRequest
    {
        $settings = $this->settings->resolved();

        if (! $this->settings->isConfigured()) {
            throw new RuntimeException('Website intake is not fully configured.');
        }

        $actor = User::query()->whereKey((int) $settings['actor_user_id'])->first();

        if (! $actor instanceof User) {
            throw new RuntimeException('Intake actor user was not found.');
        }

        return DB::transaction(function () use ($payload, $settings, $actor): ServiceRequest {
            $customer = $this->findOrCreateCustomer($payload);
            $brand = $this->findOrCreateBrand($payload['brand']);
            $device = $this->createDevice->execute($customer, [
                'device_type_id' => (int) $settings['device_type_id'],
                'brand_id' => (int) $brand->id,
                'model' => $brand->name,
                'serial_number' => 'WEB',
                'title' => $brand->name,
                'purchase_date' => now()->toDateString(),
                'warranty_status' => 'unknown',
                'warranty_until' => now()->toDateString(),
                'notes' => 'ثبت از فرم وب‌سایت',
            ], []);

            $addressNote = trim((string) ($payload['address'] ?? ''));
            $notes = $addressNote !== '' ? 'آدرس: '.$addressNote : '';

            return $this->createServiceRequest->execute(
                $customer,
                $actor,
                [
                    'device_id' => (int) $device->id,
                    'service_type_id' => (int) $settings['service_type_id'],
                    'create_address' => true,
                    'source' => 'website',
                    'priority' => 'normal',
                    'problem_description' => $payload['problem'],
                    'preferred_date' => now()->toDateString(),
                    'preferred_start_time' => '09:00',
                    'preferred_end_time' => '18:00',
                    'notes' => $notes,
                ],
                [],
                (int) config('document.year'),
            );
        });
    }

    /**
     * @param  array{full_name: string, mobile: string}  $payload
     */
    private function findOrCreateCustomer(array $payload): Customer
    {
        $existing = Customer::query()->where('mobile', $payload['mobile'])->first();

        if ($existing instanceof Customer) {
            return $existing;
        }

        [$firstName, $lastName] = $this->splitName($payload['full_name']);

        return $this->createCustomer->execute([
            'customer_code' => $this->uniqueCustomerCode(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'mobile' => $payload['mobile'],
            'mobile_secondary' => '',
            'email' => '',
            'national_id' => null,
            'notes' => 'ثبت از فرم وب‌سایت',
            'status' => 'active',
        ]);
    }

    private function findOrCreateBrand(string $brandName): Brand
    {
        $name = trim($brandName);

        if ($name === '') {
            throw new InvalidArgumentException('Brand is required.');
        }

        $existing = Brand::query()->where('name', $name)->first();

        if ($existing instanceof Brand) {
            return $existing;
        }

        return Brand::query()->create(['name' => $name]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/u', trim($fullName), 2) ?: [];
        $first = trim((string) ($parts[0] ?? ''));
        $last = trim((string) ($parts[1] ?? ''));

        if ($first === '') {
            $first = 'مشتری';
        }

        if ($last === '') {
            $last = '-';
        }

        return [$first, $last];
    }

    private function uniqueCustomerCode(): string
    {
        do {
            $code = 'WP-'.Str::upper(Str::random(8));
        } while (Customer::query()->where('customer_code', $code)->exists());

        return $code;
    }
}
