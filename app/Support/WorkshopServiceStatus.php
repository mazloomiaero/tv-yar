<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Workshop-path statuses. Codes are stable lookup keys.
 * The Persian constants are display labels, not lookup keys.
 * Still catalog rows + service_type_statuses — not a workflow engine.
 */
final class WorkshopServiceStatus
{
    public const DeviceReceived = 'دستگاه دریافت شد';

    public const DeviceReceivedCode = 'device_received';

    public const InWorkshop = 'در کارگاه';

    public const InWorkshopCode = 'in_workshop';

    public const ReadyForDelivery = 'آماده تحویل';

    public const ReadyForDeliveryCode = 'ready_for_delivery';

    public const Delivered = 'تحویل شده';

    public const DeliveredCode = 'delivered';
}
