<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Operator create sources from docs/domains/service-request.md.
 * Future `api` is stored as VARCHAR elsewhere; it is not offered in the Operator UI.
 */
enum ServiceRequestSource: string
{
    case Website = 'website';
    case Phone = 'phone';
    case WalkIn = 'walk-in';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
