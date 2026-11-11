<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Document types and prefixes named in the architecture numbering section.
 */
enum DocumentType: string
{
    case Request = 'REQUEST';
    case Invoice = 'INVOICE';
    case Reception = 'RECEPTION';

    public function prefix(): string
    {
        return match ($this) {
            self::Request => 'SR',
            self::Invoice => 'INV',
            self::Reception => 'REC',
        };
    }
}
