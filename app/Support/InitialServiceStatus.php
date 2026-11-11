<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Seeded catalog status used when a service request is created.
 * Lookup is by CODE. NAME is only the default display label.
 */
final class InitialServiceStatus
{
    public const CODE = 'new';

    public const NAME = 'جدید';
}
