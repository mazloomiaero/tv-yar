<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Folder kinds from docs/ARCHITECTURE.md storage paths.
 */
enum ServiceReportImageKind: string
{
    case Problems = 'problems';
    case Before = 'before';
    case After = 'after';
    case DamagedParts = 'damaged-parts';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
