<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ServiceStatus;
use App\Support\SampleServiceStatus;
use Illuminate\Database\Seeder;

class ServiceStatusSeeder extends Seeder
{
    /**
     * Seed documented main, side, and workshop-path statuses.
     * Existing display names are kept once a stable code is set.
     */
    public function run(): void
    {
        foreach (SampleServiceStatus::definitions() as $definition) {
            $existing = ServiceStatus::query()->where('code', $definition['code'])->first();

            if ($existing instanceof ServiceStatus) {
                if (! $existing->is_system) {
                    $existing->is_system = true;
                    $existing->save();
                }

                continue;
            }

            $legacy = ServiceStatus::query()
                ->whereNull('code')
                ->where('name', $definition['name'])
                ->orderBy('id')
                ->first();

            if (! $legacy instanceof ServiceStatus) {
                $legacy = ServiceStatus::query()
                    ->whereNull('code')
                    ->where('name', $definition['english'])
                    ->orderBy('id')
                    ->first();
            }

            if ($legacy instanceof ServiceStatus) {
                $legacy->forceFill([
                    'name' => $definition['name'],
                    'code' => $definition['code'],
                    'is_system' => true,
                ])->save();

                continue;
            }

            ServiceStatus::query()->create([
                'name' => $definition['name'],
                'code' => $definition['code'],
                'is_system' => true,
            ]);
        }
    }
}
