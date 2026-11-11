<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Receipt uploads. Source storage list has no payments folder
 * (docs/ARCHITECTURE.md); files go under payment-receipts/ as a documented gap.
 */
final class PaymentReceiptRules
{
    public const DISK = 'public';

    public const DIRECTORY = 'payment-receipts';

    public const MAX_KILOBYTES = 5120;

    /**
     * @return list<string>
     */
    public static function extensions(): array
    {
        return ['jpeg', 'jpg', 'png', 'webp'];
    }
}
