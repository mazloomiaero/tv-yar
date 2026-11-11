<?php

declare(strict_types=1);

namespace App\Support;

/**
 * payment_transactions.status VARCHAR values (not enumerated in the source).
 * failed is a row status after Verify returns not-ok — not a PaymentFailed event.
 */
enum PaymentTransactionStatus: string
{
    case Initiated = 'initiated';
    case Verified = 'verified';
    case Failed = 'failed';
}
