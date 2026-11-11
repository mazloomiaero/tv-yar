<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Sample permission names from docs/CONTRACTS.md.
 */
enum PermissionName: string
{
    case CustomersView = 'customers.view';
    case CustomersCreate = 'customers.create';
    case CustomersEdit = 'customers.edit';
    case CustomersDelete = 'customers.delete';

    case DevicesView = 'devices.view';
    case DevicesCreate = 'devices.create';
    case DevicesEdit = 'devices.edit';

    case RequestsView = 'requests.view';
    case RequestsCreate = 'requests.create';
    case RequestsEdit = 'requests.edit';
    case RequestsAssign = 'requests.assign';
    case RequestsChangeStatus = 'requests.change_status';
    case RequestsClose = 'requests.close';

    case ReportsView = 'reports.view';
    case ReportsCreate = 'reports.create';
    case ReportsEdit = 'reports.edit';

    case InvoicesView = 'invoices.view';
    case InvoicesCreate = 'invoices.create';
    case InvoicesEdit = 'invoices.edit';

    case PaymentsView = 'payments.view';
    case PaymentsCreate = 'payments.create';
    case PaymentsVerify = 'payments.verify';

    case FeedbackView = 'feedback.view';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
