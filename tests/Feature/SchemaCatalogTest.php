<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaCatalogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return list<string>
     */
    private function documentedTables(): array
    {
        return [
            'company',
            'settings',
            'users',
            'roles',
            'permissions',
            'user_roles',
            'role_permissions',
            'customers',
            'customer_addresses',
            'devices',
            'device_types',
            'brands',
            'service_types',
            'service_type_device_types',
            'custom_fields',
            'custom_field_options',
            'device_type_custom_fields',
            'service_type_custom_fields',
            'device_custom_field_values',
            'service_request_custom_field_values',
            'service_statuses',
            'service_type_statuses',
            'service_requests',
            'service_status_history',
            'technicians',
            'skills',
            'technician_skills',
            'service_assignments',
            'service_reports',
            'service_report_parts',
            'service_report_images',
            'device_receptions',
            'invoices',
            'invoice_items',
            'payments',
            'payment_transactions',
            'feedbacks',
            'notifications',
            'document_sequences',
            'branches',
            'parts',
        ];
    }

    public function test_the_documented_forty_one_tables_exist(): void
    {
        $tables = $this->documentedTables();

        $this->assertCount(41, $tables);
        $this->assertCount(41, array_unique($tables));

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table [{$table}].");
        }
    }

    public function test_laravel_support_tables_are_kept(): void
    {
        $this->assertTrue(Schema::hasTable('password_reset_tokens'));
        $this->assertTrue(Schema::hasTable('sessions'));
        $this->assertTrue(Schema::hasTable('cache'));
        $this->assertTrue(Schema::hasTable('jobs'));
    }

    public function test_users_has_soft_deletes_and_no_tenant_id(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'deleted_at'));
        $this->assertTrue(Schema::hasColumn('users', 'username'));
        $this->assertTrue(Schema::hasColumn('roles', 'name'));
        $this->assertTrue(Schema::hasColumn('permissions', 'name'));
        $this->assertTrue(Schema::hasColumn('device_types', 'name'));
        $this->assertTrue(Schema::hasColumn('brands', 'name'));
        $this->assertTrue(Schema::hasColumn('service_types', 'name'));
        $this->assertTrue(Schema::hasColumn('service_statuses', 'name'));
        $this->assertTrue(Schema::hasColumn('custom_fields', 'name'));
        $this->assertTrue(Schema::hasColumn('custom_field_options', 'name'));
        $this->assertTrue(Schema::hasColumn('device_custom_field_values', 'value'));
        $this->assertTrue(Schema::hasColumn('service_request_custom_field_values', 'value'));
        $this->assertFalse(Schema::hasColumn('custom_fields', 'type'));
        $this->assertFalse(Schema::hasColumn('custom_fields', 'required'));
        $this->assertFalse(Schema::hasColumn('custom_fields', 'sort_order'));
        $this->assertFalse(Schema::hasColumn('custom_fields', 'options'));
        $this->assertFalse(Schema::hasColumn('device_custom_field_values', 'customable_type'));
        $this->assertFalse(Schema::hasColumn('service_types', 'code'));
        $this->assertFalse(Schema::hasColumn('service_types', 'color'));
        $this->assertFalse(Schema::hasColumn('service_types', 'order'));
        $this->assertTrue(Schema::hasColumn('service_statuses', 'code'));
        $this->assertTrue(Schema::hasColumn('service_statuses', 'is_system'));
        $code = collect(Schema::getColumns('service_statuses'))->firstWhere('name', 'code');
        $this->assertNotNull($code);
        $this->assertTrue($code['nullable']);
        $isSystem = collect(Schema::getColumns('service_statuses'))->firstWhere('name', 'is_system');
        $this->assertNotNull($isSystem);
        $this->assertFalse($isSystem['nullable']);
        $this->assertFalse(Schema::hasColumn('service_statuses', 'color'));
        $this->assertFalse(Schema::hasColumn('service_statuses', 'order'));
        $this->assertFalse(Schema::hasColumn('service_statuses', 'is_terminal'));
        $this->assertFalse(Schema::hasColumn('devices', 'imei'));
        $this->assertFalse(Schema::hasColumn('users', 'tenant_id'));
        $this->assertFalse(Schema::hasColumn('service_requests', 'tenant_id'));
        $this->assertFalse(Schema::hasColumn('customers', 'tenant_id'));
        $this->assertTrue(Schema::hasColumn('service_status_history', 'service_status_id'));
        $this->assertTrue(Schema::hasColumn('service_status_history', 'changed_by'));
        $this->assertFalse(Schema::hasColumn('service_status_history', 'reason'));
        $this->assertFalse(Schema::hasColumn('service_status_history', 'comment'));
        $this->assertFalse(Schema::hasColumn('service_status_history', 'payload'));
        $this->assertFalse(Schema::hasColumn('customer_addresses', 'city'));
        $this->assertFalse(Schema::hasColumn('customer_addresses', 'street'));
    }

    public function test_assignment_branch_id_is_nullable_and_not_on_every_table(): void
    {
        $this->assertTrue(Schema::hasColumn('service_assignments', 'branch_id'));
        $this->assertTrue(Schema::hasColumn('skills', 'name'));
        $this->assertTrue(Schema::hasColumn('technicians', 'name'));
        $this->assertTrue(Schema::hasColumn('technicians', 'user_id'));
        $this->assertFalse(Schema::hasColumn('technicians', 'phone'));
        $this->assertFalse(Schema::hasColumn('technicians', 'employee_code'));
        $userId = collect(Schema::getColumns('technicians'))->firstWhere('name', 'user_id');
        $this->assertNotNull($userId);
        $this->assertTrue($userId['nullable']);
        $branchId = collect(Schema::getColumns('service_assignments'))->firstWhere('name', 'branch_id');
        $this->assertNotNull($branchId);
        $this->assertTrue($branchId['nullable']);

        $this->assertFalse(Schema::hasColumn('service_requests', 'branch_id'));
        $this->assertFalse(Schema::hasColumn('customers', 'branch_id'));
        $this->assertFalse(Schema::hasColumn('invoices', 'branch_id'));
        $this->assertTrue(Schema::hasColumn('technicians', 'branch_id'));
    }

    public function test_forbidden_tables_are_absent(): void
    {
        foreach ([
            'work_orders',
            'appointments',
            'checklists',
            'warehouses',
            'calendars',
            'skill_service_type',
            'skill_service_types',
            'tenants',
        ] as $table) {
            $this->assertFalse(Schema::hasTable($table), "Forbidden table [{$table}] exists.");
        }
    }

    public function test_document_sequences_are_unique_per_type_and_year(): void
    {
        $this->assertTrue(Schema::hasColumns('document_sequences', [
            'document_type',
            'prefix',
            'year',
            'current_number',
        ]));
    }

    public function test_service_report_parts_part_id_is_nullable(): void
    {
        $columns = Schema::getColumns('service_report_parts');
        $partId = collect($columns)->firstWhere('name', 'part_id');

        $this->assertNotNull($partId);
        $this->assertTrue($partId['nullable']);
        $this->assertTrue(Schema::hasColumns('service_report_parts', [
            'name',
            'quantity',
            'unit_price',
            'total_price',
        ]));
        $this->assertFalse(Schema::hasColumn('service_report_parts', 'is_confirmed'));
        $this->assertTrue(Schema::hasColumns('service_report_images', ['path', 'kind']));
        $this->assertFalse(Schema::hasColumn('service_reports', 'diagnosis'));
        $this->assertFalse(Schema::hasColumn('service_reports', 'result'));
        $this->assertFalse(Schema::hasColumn('service_reports', 'notes'));
        $this->assertTrue(Schema::hasColumns('parts', [
            'name',
            'code',
            'default_unit_price',
            'unit',
            'status',
            'deleted_at',
        ]));
        $this->assertTrue(Schema::hasColumns('device_receptions', [
            'service_request_id',
            'receipt_number',
            'customer_id',
            'device_id',
            'received_by',
            'received_date',
            'appearance',
            'accompanying_items',
            'reason',
            'notes',
            'delivery_date',
        ]));
        $this->assertFalse(Schema::hasColumn('device_receptions', 'shelf'));
        $this->assertFalse(Schema::hasColumn('device_receptions', 'bin'));
        $this->assertFalse(Schema::hasColumn('device_receptions', 'warehouse_id'));
        $this->assertFalse(Schema::hasColumn('device_receptions', 'stock_qty'));
        $this->assertTrue(Schema::hasColumns('invoice_items', [
            'title',
            'description',
            'quantity',
            'unit_price',
            'total',
        ]));
        $this->assertFalse(Schema::hasColumn('invoice_items', 'part_id'));
        $this->assertFalse(Schema::hasColumn('service_report_parts', 'is_confirmed'));
        $this->assertFalse(Schema::hasColumn('invoice_items', 'is_confirmed'));
        $this->assertTrue(Schema::hasColumns('feedbacks', ['service_request_id', 'score', 'comment']));
        $this->assertFalse(Schema::hasColumn('feedbacks', 'nps'));
        $this->assertFalse(Schema::hasColumn('feedbacks', 'survey'));
        $this->assertTrue(Schema::hasColumn('notifications', 'name'));
        $this->assertFalse(Schema::hasColumn('notifications', 'uuid'));
        $this->assertFalse(Schema::hasColumn('notifications', 'notifiable_type'));
        $this->assertFalse(Schema::hasColumn('notifications', 'notifiable_id'));
        $this->assertFalse(Schema::hasColumn('notifications', 'data'));
        $this->assertTrue(Schema::hasColumn('invoices', 'invoice_number'));
        $indexes = collect(Schema::getIndexes('invoices'));
        $this->assertTrue(
            $indexes->contains(fn (array $index): bool => in_array('invoice_number', $index['columns'], true) && $index['unique']),
        );
    }
}
