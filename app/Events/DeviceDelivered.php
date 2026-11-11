<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceDelivered
{
    use Dispatchable, SerializesModels;

    public function __construct(public int $deviceReceptionId) {}
}
