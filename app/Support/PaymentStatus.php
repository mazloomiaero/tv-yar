<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Payment status VARCHAR values. Source enumerates pending_confirmation
 * for manual rows; gateway initiated/completed are the gap fill used here
 * (docs/domains/payment.md). Not a PaymentFailed event.
 */
enum PaymentStatus: string
{
    case Initiated = 'initiated';
    case PendingConfirmation = 'pending_confirmation';
    case Completed = 'completed';
}
