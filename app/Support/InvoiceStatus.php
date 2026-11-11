<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Invoice statuses from docs/ARCHITECTURE.md / docs/CONTRACTS.md.
 */
enum InvoiceStatus: string
{
    case PendingPayment = 'pending_payment';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
}
