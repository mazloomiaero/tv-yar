<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Catalog status used when an open service request is cancelled from the list.
 * Lookup is by CODE. NAME is only the default display label.
 */
final class CancelledServiceStatus
{
    public const CODE = 'cancelled';

    public const NAME = 'لغو شده';
}
