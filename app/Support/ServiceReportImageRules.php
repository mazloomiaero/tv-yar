<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Upload limits for service-report images. Type/size must be validated
 * (security rules); exact limits are not in the architecture dictionary.
 */
final class ServiceReportImageRules
{
    public const DISK = 'public';

    public const DIRECTORY_PREFIX = 'service-reports';

    public const MAX_KILOBYTES = 5120;

    /**
     * @return list<string>
     */
    public static function extensions(): array
    {
        return ['jpeg', 'jpg', 'png', 'webp'];
    }
}
