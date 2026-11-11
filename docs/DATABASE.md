# DATABASE

Source of truth: architecture document (`index.html`). **41 tables.** Columns below are copied from that document. Tables without a field list in the source are named only — **do not invent columns**.

## Standards

| Rule | Value |
|---|---|
| PK | `BIGINT UNSIGNED` |
| Calendar in DB | Gregorian |
| Money | `DECIMAL(15,2)` |
| Quantity | `DECIMAL(12,3)` |
| Status/type | `VARCHAR` (no MySQL ENUM) |
| Charset | `utf8mb4` |
| Timestamps | main entities have `created_at` / `updated_at` |
| Soft delete | master data listed under Relations |

Numbering: `document_sequences` with `lockForUpdate` on `document_type` + year. Unique `(document_type, year)`. Prefixes: REQUEST `SR`, INVOICE `INV`, RECEPTION `REC` (example year 1405 in the source).

## Table catalog (41)

| # | Table | Purpose |
|---|---|---|
| 1 | `company` | Company information |
| 2 | `settings` | System settings |
| 3 | `users` | Users |
| 4 | `roles` | Roles |
| 5 | `permissions` | Permissions |
| 6 | `user_roles` | User–role link |
| 7 | `role_permissions` | Role–permission link |
| 8 | `customers` | Customers |
| 9 | `customer_addresses` | Customer addresses |
| 10 | `devices` | Customer devices |
| 11 | `device_types` | Device types |
| 12 | `brands` | Brands |
| 13 | `service_types` | Service types |
| 14 | `service_type_device_types` | Service–device type link |
| 15 | `custom_fields` | Custom fields |
| 16 | `custom_field_options` | Select options |
| 17 | `device_type_custom_fields` | Fields per device type |
| 18 | `service_type_custom_fields` | Fields per service type |
| 19 | `device_custom_field_values` | Device field values |
| 20 | `service_request_custom_field_values` | Request field values |
| 21 | `service_statuses` | Service statuses |
| 22 | `service_type_statuses` | Allowed statuses per service type |
| 23 | `service_requests` | Service requests (process core) |
| 24 | `service_status_history` | Status history |
| 25 | `technicians` | Technicians |
| 26 | `skills` | Skills |
| 27 | `technician_skills` | Technician skills |
| 28 | `service_assignments` | Assignment and schedule |
| 29 | `service_reports` | Repair reports |
| 30 | `service_report_parts` | Parts used on a report |
| 31 | `service_report_images` | Report images |
| 32 | `device_receptions` | Device reception receipts |
| 33 | `invoices` | Invoices |
| 34 | `invoice_items` | Invoice lines |
| 35 | `payments` | Payments |
| 36 | `payment_transactions` | Gateway transactions |
| 37 | `feedbacks` | Customer feedback |
| 38 | `notifications` | Notifications |
| 39 | `document_sequences` | Document numbering |
| 40 | `branches` | Company branches |
| 41 | `parts` | Parts catalog and base price |

## Columns specified in the source

### `company`

`id` BIGINT UNSIGNED; `name` VARCHAR(150); `trade_name` VARCHAR(150); `logo` VARCHAR(255); `phone` VARCHAR(30); `mobile` VARCHAR(30); `email` VARCHAR(150); `website` VARCHAR(255); `province` VARCHAR(100); `city` VARCHAR(100); `address` TEXT; `postal_code` VARCHAR(20); `invoice_title` VARCHAR(255); `invoice_description` TEXT; `primary_color` VARCHAR(20); `secondary_color` VARCHAR(20); `status` VARCHAR(20).

### `branches`

`id` BIGINT UNSIGNED; `name` VARCHAR(150); `province` VARCHAR(100); `city` VARCHAR(100); `address` TEXT; `phone` VARCHAR(30); `is_default` BOOLEAN; `status` VARCHAR(20).

### `customers`

`id` BIGINT UNSIGNED; `customer_code` VARCHAR(30) unique customer code; `first_name` VARCHAR(100); `last_name` VARCHAR(100); `mobile` VARCHAR(30) unique among non-deleted rows (Iranian `09#########`); `mobile_secondary` VARCHAR(30); `email` VARCHAR(150); `national_id` VARCHAR(20) optional; `notes` TEXT; `status` VARCHAR(20); `user_id` BIGINT UNSIGNED optional customer user account.

### `device_types`

`id` BIGINT UNSIGNED; `name` VARCHAR(150) catalog identifier (Admin DeviceType controller). Source examples: TV, Air Conditioner, Mobile. No other catalog columns.

### `brands`

`id` BIGINT UNSIGNED; `name` VARCHAR(150) catalog identifier (Admin Brand controller). No other catalog columns.

### `service_types`

`id` BIGINT UNSIGNED; `name` VARCHAR(150) catalog identifier (Admin ServiceType controller). No other catalog columns (no code/color/order).

### `custom_fields`

`id` BIGINT UNSIGNED; `name` VARCHAR(150) catalog identifier (Admin CustomField controller). Source has no type/required/sort_order columns. Select vs free text is inferred from `custom_field_options` rows (`docs/domains/custom-fields.md`).

### `custom_field_options`

`id` BIGINT UNSIGNED; `custom_field_id`; `name` VARCHAR(150) option label. Table purpose in the catalog: Select options.

### `device_custom_field_values`

`id` BIGINT UNSIGNED; `device_id`; `custom_field_id`; `value` VARCHAR(255). Table purpose: Device field values. Real FKs, not polymorphic (`docs/ARCHITECTURE.md`).

### `service_request_custom_field_values`

`id` BIGINT UNSIGNED; `service_request_id`; `custom_field_id`; `value` VARCHAR(255). Table purpose: Request field values. Real FKs, not polymorphic (`docs/ARCHITECTURE.md`).

### `service_statuses`

`id` BIGINT UNSIGNED; `name` VARCHAR(150) display label (Admin ServiceStatus controller); `code` VARCHAR(50) nullable stable lookup key (unique when set); `is_system` BOOLEAN default false (seed/system rows cannot be deleted). Sample labels in `docs/ARCHITECTURE.md` (main + side). No color/order/`is_terminal` columns. Business lookups use `code`, not `name`.

### `devices`

`id`; `customer_id`; `device_type_id`; `brand_id`; `model` VARCHAR(150); `serial_number` VARCHAR(150); `title` VARCHAR(150); `purchase_date` DATE; `warranty_status` VARCHAR(30); `warranty_until` DATE; `notes` TEXT.

### `service_requests`

`id`; `request_number` VARCHAR(40); `customer_id`; `device_id`; `service_type_id`; `address_id`; `status_id`; `source` VARCHAR(30); `priority` VARCHAR(20); `problem_description` TEXT; `preferred_date` DATE; `preferred_start_time` TIME; `preferred_end_time` TIME; `notes` TEXT; `created_by`; `closed_at` DATETIME.

### `service_assignments`

`id`; `service_request_id`; `branch_id` nullable; `technician_id`; `is_suggested` BOOLEAN; `assigned_by`; `scheduled_date` DATE; `start_time` TIME; `end_time` TIME; `status` VARCHAR(30); `technician_response` VARCHAR(30); `accepted_at`; `started_at`; `completed_at` DATETIME.

### `invoices`

`id`; `invoice_number` VARCHAR(40); `service_request_id` unique (one invoice per service request); `customer_id`; `customer_name` VARCHAR(200) snapshot; `customer_mobile` VARCHAR(30) snapshot; `customer_address` TEXT snapshot; `subtotal` DECIMAL(15,2); `discount` DECIMAL(15,2); `tax` DECIMAL(15,2); `total` DECIMAL(15,2); `status` VARCHAR(30); `issued_at`; `paid_at` DATETIME.

### `payments`

`id`; `invoice_id`; `amount` DECIMAL(15,2); `method` VARCHAR(30) (`gateway` / `card_transfer` / `cash`); `status` VARCHAR(30); `paid_at`; `reference_number` VARCHAR(150); `recorded_by` (card_transfer/cash: the technician); `receipt_image` VARCHAR(255) (required for card_transfer, optional for cash).

### `payment_transactions`

`id`; `payment_id`; `gateway` VARCHAR(50); `authority` VARCHAR(255); `transaction_id` VARCHAR(255); `reference_id` VARCHAR(255); `request_data` JSON; `response_data` JSON; `status` VARCHAR(30).

### `parts`

`id`; `name` VARCHAR(150); `code` VARCHAR(50) optional; `default_unit_price` DECIMAL(15,2); `unit` VARCHAR(20); `status` VARCHAR(20).

### `document_sequences` (numbering section)

`document_type`, `prefix`, `year`, `current_number`. UNIQUE `(document_type, year)`.

## Partial field notes (not a full dictionary)

These appear in narrative sections, **not** as typed dictionaries. DATABASE agent must not fill gaps without an architecture update.

- **`invoice_items` snapshot fields:** `title`, `description`, `quantity`, `unit_price`, `total` (types recorded under “Invoice item snapshot columns”).
- **`service_report_parts`:** `part_id` nullable; `name`; `quantity`; `unit_price` (default from `parts`, editable); `total_price`.
- **`device_receptions` concerns:** receipt number, request, customer, device, receiving user, received date, appearance, accompanying items, reason, notes, delivery date. Types recorded under “Device reception receipt columns”.
- **Relations imply** technicians belong to branches; `service_report_parts.part_id` nullable.

## Tables with **no** column list in the source

`settings`, `users`, `roles`, `permissions`, `user_roles`, `role_permissions`, `customer_addresses`, `service_type_device_types`, `device_type_custom_fields`, `service_type_custom_fields`, `service_type_statuses`, `service_status_history`, `technicians`, `skills`, `technician_skills`, `service_reports`.

`feedbacks` and `notifications` remain without a source dictionary except the score/comment and name columns recorded under “Feedback score/comment and notification name” below.

`service_report_images` remains without a source dictionary except the storage path/kind columns recorded under “Service report part and image columns” below. `service_reports` still has **no** body dictionary (no diagnosis/result/notes).

`custom_fields`, `custom_field_options`, `device_custom_field_values`, and `service_request_custom_field_values` remain without a source dictionary except the identifier/value columns recorded under “Custom-field identifier and value columns” below.

## Relations (source tree)

```
branches
    └── technicians / service_assignments (nullable)

parts
    └── service_report_parts (part_id nullable)

customers
    ├── customer_addresses
    ├── devices (device_type, brand)
    └── service_requests
            ├── service_type, address, status, status_history
            ├── assignments (branch nullable, technician)
            ├── service_reports (parts, images)
            ├── device_receptions
            ├── invoices → invoice_items, payments
            │                 └── payment_transactions (gateway only)
            └── feedbacks
```

### Delete policy

- Soft delete: users, customers, addresses, devices, device types, brands, service types, custom fields, statuses, technicians, skills, branches, parts.
- No physical delete of: service requests, service reports, device receptions, invoices, payments, financial transactions.
- FK: pivot/link CASCADE; business entity RESTRICT; actor/user SET NULL.

## Explicitly not in schema

Warehouse tables, work_orders, appointments, checklists, tenant_id, independent calendar table.

## Migration notes (sourced FKs and remaining gaps)

The architecture HTML (`index.html`) was **not** in this repository when migrations were written. Column dictionaries below are only from this file and `docs/ARCHITECTURE.md` (custom-field FKs).

### FKs taken from the relation tree (this file)

| Table | FK | On delete |
|---|---|---|
| `customer_addresses` | `customer_id` | RESTRICT |
| `devices` | `customer_id`, `device_type_id`, `brand_id` | RESTRICT |
| `service_requests` | `customer_id`, `device_id`, `service_type_id`, `address_id` → `customer_addresses`, `status_id` → `service_statuses` | RESTRICT |
| `service_status_history` | `service_request_id`, `service_status_id` | RESTRICT |
| `technicians` | `branch_id` | RESTRICT |
| `service_assignments` | `service_request_id`, `technician_id`, nullable `branch_id` | RESTRICT |
| `service_reports` | `service_request_id` | RESTRICT |
| `service_report_parts` | `service_report_id`; nullable `part_id` | RESTRICT |
| `service_report_images` | `service_report_id` | RESTRICT |
| `device_receptions` | `service_request_id` | RESTRICT |
| `invoices` | `service_request_id`, `customer_id` | RESTRICT |
| `invoice_items` | `invoice_id` | RESTRICT |
| `payments` | `invoice_id` | RESTRICT |
| `payment_transactions` | `payment_id` | RESTRICT |
| `feedbacks` | `service_request_id` | RESTRICT |

### Pivot FKs (CASCADE)

`user_roles`, `role_permissions`, `service_type_device_types`, `device_type_custom_fields`, `service_type_custom_fields`, `service_type_statuses`, `technician_skills`.

### Custom-field FKs from `docs/ARCHITECTURE.md` (“real FKs”, no polymorphic value table)

| Table | FKs |
|---|---|
| `custom_field_options` | `custom_field_id` CASCADE |
| `device_custom_field_values` | `device_id`, `custom_field_id` RESTRICT |
| `service_request_custom_field_values` | `service_request_id`, `custom_field_id` RESTRICT |

### Actor FKs (SET NULL → `users`)

`customers.user_id`, `technicians.user_id`, `service_requests.created_by`, `service_assignments.assigned_by`, `payments.recorded_by`, `service_status_history.changed_by`.

### Soft delete

`users.deleted_at` added onto Laravel’s default `users` table (that migration was **not** replaced). Other master-data tables from the list above received `deleted_at` in their create migrations.

### Auth identifier columns (not in the source field dictionary)

`users`, `roles`, and `permissions` have **no column list** in the architecture document. Login and RBAC still require identifiers:

| Table | Column | Why |
|---|---|---|
| `users` | `username` unique VARCHAR | Internal username/password login (`docs/ARCHITECTURE.md` Auth; `.cursor/rules/04-security.md`). Laravel `email` / `password` / `sessions` are kept. |
| `users` | `first_name`, `last_name`, `mobile` nullable | Staff create form (Admin). `name` stays the concatenated display identifier. `mobile` is 11 digits starting with `09`. |
| `roles` | `name` unique VARCHAR | Six roles named in `docs/CONTRACTS.md`. |
| `permissions` | `name` unique VARCHAR | Sample permission strings in `docs/CONTRACTS.md`. |

Pivot uniqueness (link tables, CASCADE already migrated): unique `(user_id, role_id)` on `user_roles`; unique `(role_id, permission_id)` on `role_permissions`.

SMS login codes are stored in **Cache**, not a 42nd table.

Migration: `2026_09_11_150000_add_auth_rbac_identifier_columns.php`.

### Catalog identifier columns (not in the source field dictionary)

`device_types` and `brands` have **no column list** in the architecture document. Admin DeviceType/Brand screens still need a catalog identifier. Source examples of type names: TV, Air Conditioner, Mobile (`docs/domains/custom-fields.md`). No other catalog columns (code, status, IMEI, screen size, …).

`service_types` and `service_statuses` likewise had **no column list** in the original architecture dictionary. Admin ServiceType/ServiceStatus screens still need a catalog identifier. Sample status labels: New, Waiting for Contact, Waiting for Assignment, Assigned, Technician Confirmed, On the Way, On Site, Inspecting, Repairing, Repaired, Closed; side: Cancelled, Customer Absent, Cannot Repair, Revisit Needed, Transfer to Workshop (`docs/ARCHITECTURE.md`). No color, order, or `is_terminal` columns. Stable `code` + `is_system` were added after production audit so renames/deletes cannot break request creation.

| Table | Column | Why |
|---|---|---|
| `device_types` | `name` VARCHAR(150) | Identifier for Admin catalog and `devices.device_type_id`. |
| `brands` | `name` VARCHAR(150) | Identifier for Admin catalog and `devices.brand_id`. |
| `service_types` | `name` VARCHAR(150) | Identifier for Admin catalog and `service_requests.service_type_id`. |
| `service_statuses` | `name` VARCHAR(150) | Display label for Admin catalog and UI. |
| `service_statuses` | `code` VARCHAR(50) nullable | Stable lookup key (e.g. `new`); unique when set. |
| `service_statuses` | `is_system` BOOLEAN | Protects seeded/system statuses from deletion. |

Pivot uniqueness (link tables, CASCADE already migrated): unique `(service_type_id, device_type_id)` on `service_type_device_types`; unique `(service_type_id, service_status_id)` on `service_type_statuses`.

Migration: `2026_09_11_160000_add_name_to_device_types_and_brands.php`.

Migration: `2026_09_11_170000_add_name_to_service_types_and_service_statuses.php`.

### Skill and technician identifier columns (not in the source field dictionary)

`skills` and `technicians` have **no column list** in the architecture document. Admin Skill/Technician screens still need a catalog identifier. Source relations require `technicians.branch_id` (already migrated). Optional `technicians.user_id` → `users` SET NULL exists only so the Technician panel can list assignments for the internal login. No phone or employee_code columns.

| Table | Column | Why |
|---|---|---|
| `skills` | `name` VARCHAR(150) | Identifier for Admin catalog and `technician_skills`. |
| `technicians` | `name` VARCHAR(150) | Identifier for Admin catalog, assignment, and suggestion display. |
| `technicians` | `user_id` nullable | Link to internal `users` (SET NULL) for “my assignments”. |

Pivot uniqueness (link table, CASCADE already migrated): unique `(technician_id, skill_id)` on `technician_skills`.

Migration: `2026_09_11_200000_add_technician_skill_and_user_identifier_columns.php`.

### Assignment NOT NULL strings (schema gap — not a workflow)

`service_assignments.status` VARCHAR(30) and `service_assignments.technician_response` VARCHAR(30) are NOT NULL in the migrated table, but the architecture document does **not** define allowed values or an accept/reject/start workflow. `AssignTechnician` writes the minimum insert values `assigned` and `unspecified`. `accepted_at`, `started_at`, and `completed_at` stay null. Open assignments for suggestion ranking are rows with `completed_at IS NULL`. Do not treat these strings as a state machine.

### Custom-field identifier and value columns (not in the source field dictionary)

`custom_fields`, `custom_field_options`, `device_custom_field_values`, and `service_request_custom_field_values` have **no column list** in the architecture document. Admin Custom Field screens still need a catalog identifier. Select options and stored values are the documented table purposes (`docs/DATABASE.md` catalog; `docs/domains/custom-fields.md`). Two value tables keep real FKs (`docs/ARCHITECTURE.md`). No `type`, `required`, `sort_order`, or JSON columns. Industry examples (TV screen size, AC gas type, mobile IMEI) are illustrative labels, not seed data and not `devices` columns.

| Table | Column | Why |
|---|---|---|
| `custom_fields` | `name` VARCHAR(150) | Identifier for Admin catalog and mappings. |
| `custom_field_options` | `name` VARCHAR(150) | Select option labels (table purpose). Presence of options means Select; absence means free text. |
| `device_custom_field_values` | `value` VARCHAR(255) | Stored device field values (table purpose). |
| `service_request_custom_field_values` | `value` VARCHAR(255) | Stored request field values (table purpose). |

Pivot uniqueness (link tables, CASCADE already migrated): unique `(device_type_id, custom_field_id)` on `device_type_custom_fields`; unique `(service_type_id, custom_field_id)` on `service_type_custom_fields`. Value uniqueness: unique `(device_id, custom_field_id)`; unique `(service_request_id, custom_field_id)`. Option uniqueness: unique `(custom_field_id, name)`.

Migration: `2026_09_11_180000_add_custom_field_name_and_value_columns.php`.

### Status history columns (not in the source field dictionary)

`service_status_history` has **no column list** in the architecture document. ChangeServiceStatus still needs to record **status history** (`docs/CONTRACTS.md`, `docs/domains/service-request.md`): which status was written, and which user changed it (actor FK SET NULL). No reason, comment, payload JSON, or workflow-engine columns.

| Table | Column | Why |
|---|---|---|
| `service_status_history` | `service_status_id` | Status written on that history row (RESTRICT). |
| `service_status_history` | `changed_by` nullable | Actor who changed status (`users`, SET NULL). |

`service_requests.request_number` is unique (source: unique request number / `SR-1405-000125` example).

Migration: `2026_09_11_190000_add_service_request_number_unique_and_status_history_columns.php`.

### Service report part and image columns (narrative names + storage folders)

`service_report_parts` line fields were named in this file’s narrative notes without a typed dictionary. CreateServiceReport still needs those fields. Types follow project standards (quantity DECIMAL(12,3), money DECIMAL(15,2)). `part_id` stays nullable (catalog or free-text line). **No** `is_confirmed` — confirmation is the later invoice step (`docs/domains/invoice.md`). For catalog lines (`part_id` set), `unit_price` is always taken from `parts.default_unit_price` server-side (client price ignored). Free-text lines keep a client-supplied `unit_price`. `total_price` is computed in the Action (quantity × unit_price).

`service_reports` stores one required work narrative: `description` TEXT (min length enforced in Form Request). Do **not** add a diagnosis/result/notes trio — only this single body field.

`service_report_images` has **no** source field dictionary. Storage folders in `docs/ARCHITECTURE.md` are `problems`, `before`, `after`, `damaged-parts`. Files live under `storage/app/public/service-reports/{kind}/`. The database stores path + folder kind only (not file bytes). Upload type/size is validated in the Form Request (jpeg/jpg/png/webp, max 5120 KB) — those limits are not in the architecture dictionary.

| Table | Column | Why |
|---|---|---|
| `service_reports` | `description` TEXT | Required work description written by the technician. |
| `service_report_parts` | `name` VARCHAR(150) | Line label (catalog snapshot or free-text name). |
| `service_report_parts` | `quantity` DECIMAL(12,3) | Quantity standard. |
| `service_report_parts` | `unit_price` DECIMAL(15,2) | Money standard; catalog lines locked to `parts.default_unit_price`. |
| `service_report_parts` | `total_price` DECIMAL(15,2) | Computed in Action; do not trust client totals. |
| `service_report_images` | `path` VARCHAR(255) | Relative path on the public disk. |
| `service_report_images` | `kind` VARCHAR(30) | Folder kind: `problems` / `before` / `after` / `damaged-parts`. |

Migration: `2026_09_11_210000_add_service_report_part_and_image_columns.php`.

Migration: `2026_09_11_262000_add_description_to_service_reports_table.php`.

### Device reception receipt columns (narrative concerns; types unspecified in source)

`device_receptions` concerns are named in this file without a typed dictionary. ReceiveDevice/DeliverDevice still need those fields. Types follow the rest of the schema (VARCHAR/TEXT/DATE). `customer_id` and `device_id` are copied from the service request at receive time (RESTRICT). Receiving user is `received_by` SET NULL. One receipt per request (`service_request_id` unique). PDF files live under `storage/app/public/receptions/` and are named from `receipt_number` — **no** `pdf_path`, shelf, bin, or stock columns.

| Table | Column | Why |
|---|---|---|
| `device_receptions` | `receipt_number` VARCHAR(40) unique | Number from `document_sequences` type RECEPTION (`REC-1405-000001`). |
| `device_receptions` | `customer_id` | Customer on the same request (RESTRICT). |
| `device_receptions` | `device_id` | Device on the same request (RESTRICT). |
| `device_receptions` | `received_by` nullable | Receiving user (`users`, SET NULL). |
| `device_receptions` | `received_date` DATE | Received date (Gregorian storage). |
| `device_receptions` | `appearance` TEXT | Device appearance on the receipt. |
| `device_receptions` | `accompanying_items` TEXT | Accompanying items on the receipt. |
| `device_receptions` | `reason` TEXT | Reception reason. |
| `device_receptions` | `notes` TEXT | Receipt notes. |
| `device_receptions` | `delivery_date` DATE nullable | Set by DeliverDevice. |

Migration: `2026_09_11_220000_add_device_reception_receipt_columns.php`.

### Invoice item snapshot columns and unique invoice number (narrative names)

`invoice_items` snapshot fields were named in this file’s narrative notes without a typed dictionary. CreateInvoice still needs those fields. Types follow project standards (quantity DECIMAL(12,3), money DECIMAL(15,2)). Title is copied from `service_report_parts.name`. Description has no source field on report parts and is stored as empty text. **No** `part_id` or `is_confirmed` on invoice lines — confirmation is copying current report parts into `invoice_items`. Line `total` is computed in InvoiceService (quantity × unit_price); do not trust client totals.

`invoices.invoice_number` is unique (source: unique document numbers / `INV-1405-000001` pattern).

`invoices.service_request_id` is unique (one invoice per service request; CreateInvoice rejects duplicates and surfaces the existing invoice number).

`customer_addresses` still has no street/city columns. `invoices.customer_address` is snapshotted as whatever address text exists (currently empty).

| Table | Column | Why |
|---|---|---|
| `invoice_items` | `title` VARCHAR(150) | Line label snapshot from the report part name. |
| `invoice_items` | `description` TEXT | Snapshot field named in the source; empty until report parts have description text. |
| `invoice_items` | `quantity` DECIMAL(12,3) | Quantity standard. |
| `invoice_items` | `unit_price` DECIMAL(15,2) | Money standard; copied from the report part then used to compute total. |
| `invoice_items` | `total` DECIMAL(15,2) | Computed in InvoiceService; do not trust client totals. |

Migration: `2026_09_11_230000_add_invoice_item_snapshot_columns_and_invoice_number_unique.php`.

Migration: `2026_09_11_263000_add_unique_service_request_id_to_invoices.php`.

Migration: `2026_09_11_261000_add_code_and_is_system_to_service_statuses.php`.

Migration: `2026_09_11_264000_add_customers_mobile_active_unique_index.php`.

### Feedback score/comment and notification name (architecture narrative; no typed dictionary)

`feedbacks` has **no** source field dictionary beyond `service_request_id`. CreateFeedback still needs the customer satisfaction payload named in the architecture as «امتیاز و نظر». Columns are `score` and `comment` only. **No** mandatory 1–5 product scale, NPS, survey JSON, or extra question columns.

`notifications` is **not** Laravel’s morph notifications table (`uuid`, `notifiable_type`, `notifiable_id`, `data`). If a row is persisted, only a name/type identifier is stored. **No** recipient, channel, body, or payload schema (channel-to-role mapping is unspecified).

| Table | Column | Why |
|---|---|---|
| `feedbacks` | `score` VARCHAR(50) | امتیاز (score/rating text; no 1–5 ENUM). |
| `feedbacks` | `comment` TEXT nullable | نظر (opinion). |
| `notifications` | `name` VARCHAR(150) | Identifier for the named notification class (`ServiceRequestCreatedNotification`, …). |

Migration: `2026_09_11_240000_add_feedback_score_comment_and_notification_name.php`.

### Gaps (named in source, types missing — not migrated as business columns)

- **`document_sequences`:** names migrated; `document_type`/`prefix` use Laravel `string` (255); `year`/`current_number` use `unsignedInteger` — widths were not in the source.
- Tables listed under “no column list” have **no** name/code/status/value columns until the architecture dictionary is updated, except the auth identifier columns recorded above (`users.username`, `roles.name`, `permissions.name`), the catalog identifiers (`device_types.name`, `brands.name`, `service_types.name`, `service_statuses.name` plus `service_statuses.code` / `is_system`), the skill/technician identifiers (`skills.name`, `technicians.name`, optional `technicians.user_id`), the custom-field identifier/value columns (`custom_fields.name`, `custom_field_options.name`, `device_custom_field_values.value`, `service_request_custom_field_values.value`), the status-history columns (`service_status_history.service_status_id`, `service_status_history.changed_by`), the service-report image path/kind columns (`service_report_images.path`, `service_report_images.kind`), and `service_reports.description`.
- **`service_assignments.status` / `technician_response`:** NOT NULL VARCHAR(30) with **no** source dictionary. Insert placeholders `assigned` / `unspecified` are recorded above; they are not a workflow engine.
