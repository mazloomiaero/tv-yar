<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Seeded catalog name used as the create-request status when mapped
 * on service_type_statuses. There is no is_initial column.
 */
final class InitialServiceStatus
{
    public const NAME = 'جدید';
}
