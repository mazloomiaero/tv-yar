<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use App\Support\RoleName;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            ServiceStatusSeeder::class,
        ]);

        $admin = User::factory()->create([
            'name' => 'Roham',
            'username' => 'roham',
            'email' => 'roham@example.com',
            'password' => '900420271',
        ]);

        $admin->roles()->sync([
            Role::query()->where('name', RoleName::CompanyAdmin->value)->value('id'),
        ]);

        $operator = User::factory()->create([
            'name' => 'Operator',
            'username' => 'operator',
            'email' => 'operator@example.com',
        ]);

        $operator->roles()->sync([
            Role::query()->where('name', RoleName::Operator->value)->value('id'),
        ]);

        $portal = User::factory()->create([
            'name' => 'Sample Customer',
            'username' => 'test',
            'email' => 'customer-test@example.com',
            'password' => 'test',
        ]);

        Customer::query()->create([
            'customer_code' => 'TEST',
            'first_name' => 'مشتری',
            'last_name' => 'نمونه',
            'mobile' => '09120000000',
            'mobile_secondary' => '',
            'email' => '',
            'national_id' => null,
            'notes' => '',
            'status' => 'active',
            'user_id' => $portal->id,
        ]);
    }
}
