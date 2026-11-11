<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\PermissionName;
use App\Support\RoleName;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the six documented roles and sample permissions.
     *
     * Role–permission mapping is not specified in the source except that
     * Company Admin/Manager is the administration role; that role receives
     * every sample permission. Operator receives customer view/create/edit/delete,
     * devices view/create/edit, and requests view/create/edit/assign/change_status/close.
     * Technical Supervisor receives requests view/assign/change_status
     * (workshop receive/deliver reuse change_status; no workshop.* names).
     * Catalog screens use the Admin panel role, not extra permission names.
     * Technician receives reports.view/create/edit for CreateServiceReport
     * and payments.create for RecordManualPayment.
     * Assignment listing still uses technicians.user_id, not extra permissions.
     * Accountant receives invoices.view/create/edit for CreateInvoice
     * and payments.view/verify for confirmation. Customer gateway uses the
     * customer guard (no extra permission names).
     * Operator receives feedback.view for staff feedback listing. Customers
     * submit via the customer guard (no feedback.create permission).
     */
    public function run(): void
    {
        foreach (RoleName::values() as $name) {
            Role::query()->firstOrCreate(['name' => $name]);
        }

        foreach (PermissionName::values() as $name) {
            Permission::query()->firstOrCreate(['name' => $name]);
        }

        $admin = Role::query()->where('name', RoleName::CompanyAdmin->value)->firstOrFail();
        $admin->permissions()->sync(Permission::query()->pluck('id'));

        $operator = Role::query()->where('name', RoleName::Operator->value)->firstOrFail();
        $operator->permissions()->syncWithoutDetaching(
            Permission::query()
                ->whereIn('name', [
                    PermissionName::CustomersView->value,
                    PermissionName::CustomersCreate->value,
                    PermissionName::CustomersEdit->value,
                    PermissionName::CustomersDelete->value,
                    PermissionName::DevicesView->value,
                    PermissionName::DevicesCreate->value,
                    PermissionName::DevicesEdit->value,
                    PermissionName::RequestsView->value,
                    PermissionName::RequestsCreate->value,
                    PermissionName::RequestsEdit->value,
                    PermissionName::RequestsAssign->value,
                    PermissionName::RequestsChangeStatus->value,
                    PermissionName::RequestsClose->value,
                    PermissionName::FeedbackView->value,
                ])
                ->pluck('id')
        );

        $technical = Role::query()->where('name', RoleName::TechnicalSupervisor->value)->firstOrFail();
        $technical->permissions()->syncWithoutDetaching(
            Permission::query()
                ->whereIn('name', [
                    PermissionName::RequestsView->value,
                    PermissionName::RequestsAssign->value,
                    PermissionName::RequestsChangeStatus->value,
                ])
                ->pluck('id')
        );

        $technician = Role::query()->where('name', RoleName::Technician->value)->firstOrFail();
        $technician->permissions()->syncWithoutDetaching(
            Permission::query()
                ->whereIn('name', [
                    PermissionName::ReportsView->value,
                    PermissionName::ReportsCreate->value,
                    PermissionName::ReportsEdit->value,
                    PermissionName::PaymentsCreate->value,
                ])
                ->pluck('id')
        );

        $accountant = Role::query()->where('name', RoleName::Accountant->value)->firstOrFail();
        $accountant->permissions()->syncWithoutDetaching(
            Permission::query()
                ->whereIn('name', [
                    PermissionName::InvoicesView->value,
                    PermissionName::InvoicesCreate->value,
                    PermissionName::InvoicesEdit->value,
                    PermissionName::PaymentsView->value,
                    PermissionName::PaymentsVerify->value,
                ])
                ->pluck('id')
        );
    }
}
