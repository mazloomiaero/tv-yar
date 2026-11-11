<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Payment methods from docs/CONTRACTS.md.
 */
enum PaymentMethod: string
{
    case Gateway = 'gateway';
    case CardTransfer = 'card_transfer';
    case Cash = 'cash';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return list<string>
     */
    public static function manualValues(): array
    {
        return [
            self::CardTransfer->value,
            self::Cash->value,
        ];
    }
}
