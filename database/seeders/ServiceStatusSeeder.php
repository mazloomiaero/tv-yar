<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ServiceStatus;
use App\Support\SampleServiceStatus;
use Illuminate\Database\Seeder;

class ServiceStatusSeeder extends Seeder
{
    /**
     * Seed documented main, side, and workshop-path status names.
     */
    public function run(): void
    {
        foreach (SampleServiceStatus::englishToPersian() as $english => $persian) {
            ServiceStatus::query()->where('name', $english)->update(['name' => $persian]);
        }

        foreach (SampleServiceStatus::names() as $name) {
            ServiceStatus::query()->firstOrCreate(['name' => $name]);
        }
    }
}
