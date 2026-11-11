<?php

declare(strict_types=1);

namespace App\Notifications;

final class DeviceReadyNotification
{
    public const NAME = 'DeviceReadyNotification';

    public function __construct(public int $entityId) {}
}
