<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $notificationName,
        public int $entityId,
    ) {}

    public function handle(): void
    {
        Log::info('SMS stub (no vendor)', [
            'notification' => $this->notificationName,
            'entity_id' => $this->entityId,
        ]);
    }
}
