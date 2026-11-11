<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Public-disk location for workshop reception PDFs (docs/ARCHITECTURE.md).
 */
final class ReceptionReceipt
{
    public const DISK = 'public';

    public const DIRECTORY = 'receptions';

    public static function relativePath(string $receiptNumber): string
    {
        return self::DIRECTORY.'/'.$receiptNumber.'.pdf';
    }
}
