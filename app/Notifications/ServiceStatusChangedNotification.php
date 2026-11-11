<?php

declare(strict_types=1);

namespace App\Notifications;

final class ServiceStatusChangedNotification
{
    public const NAME = 'ServiceStatusChangedNotification';

    public function __construct(public int $entityId) {}
}
