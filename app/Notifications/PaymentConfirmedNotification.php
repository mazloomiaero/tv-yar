<?php

declare(strict_types=1);

namespace App\Notifications;

final class PaymentConfirmedNotification
{
    public const NAME = 'PaymentConfirmedNotification';

    public function __construct(public int $entityId) {}
}
