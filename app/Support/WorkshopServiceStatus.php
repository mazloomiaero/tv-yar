<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Workshop-path status names (Persian catalog labels).
 * Still catalog rows + service_type_statuses — not a workflow engine.
 */
final class WorkshopServiceStatus
{
    public const DeviceReceived = 'دستگاه دریافت شد';

    public const InWorkshop = 'در کارگاه';

    public const ReadyForDelivery = 'آماده تحویل';

    public const Delivered = 'تحویل شده';
}
