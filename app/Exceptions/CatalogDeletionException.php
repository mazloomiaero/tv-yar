<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class CatalogDeletionException extends RuntimeException
{
    public static function referenced(string $label, int $count): self
    {
        $noun = $count === 1 ? 'record' : 'records';
        $verb = $count === 1 ? 'references' : 'reference';

        return new self(
            "Cannot delete this {$label} because {$count} dependent {$noun} still {$verb} it.",
        );
    }

    public static function systemStatus(): self
    {
        return new self('Cannot delete this service status because it is a system status.');
    }
}
