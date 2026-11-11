<?php

declare(strict_types=1);

namespace App\Notifications;

final class ServiceRequestCreatedNotification
{
    public const NAME = 'ServiceRequestCreatedNotification';

    public function __construct(public int $entityId) {}
}
