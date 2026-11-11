<?php

declare(strict_types=1);

namespace App\Notifications;

final class FeedbackRequestNotification
{
    public const NAME = 'FeedbackRequestNotification';

    public function __construct(public int $entityId) {}
}
