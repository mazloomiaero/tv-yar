<?php

declare(strict_types=1);

namespace App\Notifications;

final class TechnicianAssignedNotification
{
    public const NAME = 'TechnicianAssignedNotification';

    public function __construct(public int $entityId) {}
}
