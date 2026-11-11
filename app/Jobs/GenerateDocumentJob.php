<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\DeviceReception;
use App\Services\WorkshopService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateDocumentJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $deviceReceptionId) {}

    public function handle(WorkshopService $workshop): void
    {
        $reception = DeviceReception::query()->find($this->deviceReceptionId);
        if (! $reception instanceof DeviceReception) {
            return;
        }

        $workshop->generatePdf($reception);
    }
}
