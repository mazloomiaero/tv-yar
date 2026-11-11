# ARCHITECTURE

Source of truth: architecture document (`index.html`). Do not invent modules.

## Independent installation

```
One Laravel Core
        │
        ├── Company A — Domain A / Database A
        ├── Company B — Domain B / Database B
        └── Company C — Domain C / Database C
```

Each company has its own database. **No business table has `tenant_id`.**

`branches` is internal to one install (physical sites). It is not multi-company tenancy. Assignments may have nullable `branch_id`. Technicians relate to branches.

## Request pipeline

```
HTTP Request
      ↓
Form Request
      ↓
Controller
      ↓
Action / Service
      ↓
Model
      ↓
Database
      ↓
Event → Notification / SMS / History
```

Future API controllers must call the same Actions/Services as Web controllers.

## Laravel tree (source)

```
app/
├── Events/
├── Http/Controllers/{Admin,Operator,Technical,Accountant,Technician,Customer}/
├── Http/Middleware/
├── Http/Requests/
├── Jobs/
├── Listeners/
├── Models/
├── Notifications/
├── Policies/
├── Providers/
├── Services/
├── Actions/
├── Support/
└── ViewModels/

routes/
├── web.php, auth.php
├── admin.php, operator.php, technical.php
├── accountant.php, technician.php, customer.php
```

## Models (one per main entity)

Company, Branch, Setting, Part, User, Role, Permission, Customer, CustomerAddress, Device, DeviceType, Brand, ServiceType, ServiceTypeDeviceType, CustomField, CustomFieldOption, DeviceTypeCustomField, ServiceTypeCustomField, DeviceCustomFieldValue, ServiceRequestCustomFieldValue, ServiceStatus, ServiceTypeStatus, ServiceRequest, ServiceStatusHistory, Technician, Skill, TechnicianSkill, ServiceAssignment, ServiceReport, ServiceReportPart, ServiceReportImage, DeviceReception, Invoice, InvoiceItem, Payment, PaymentTransaction, Feedback, Notification, DocumentSequence.

## Actions and Services

See `.cursor/rules/01-architecture.mdc` for the closed lists. Do not add WorkOrder/Warehouse actions.

## Status workflow

Statuses are data (`service_statuses` + `service_type_statuses`), not a workflow engine.

Sample statuses: New, Waiting for Contact, Waiting for Assignment, Assigned, Technician Confirmed, On the Way, On Site, Inspecting, Repairing, Repaired, Closed.

Side statuses: Cancelled, Customer Absent, Cannot Repair, Revisit Needed, Transfer to Workshop.

Workshop path (still the same request): Transfer to Workshop → Device Received → In Workshop → Repairing → Ready for Delivery → Delivered.

## Custom fields

Industry-specific attributes must **not** become extra columns on `devices`. Use `custom_fields` with real FKs (separate value tables for device and service request — no polymorphic value table).

## Financial outline

Service Request → determine amount (from confirmed `service_report_parts`) → Invoice → Payment → Gateway callback → Verify → Confirmed.

Invoice statuses: `pending_payment`, `partially_paid`, `paid`, `cancelled`.

Payment methods: `gateway` | `card_transfer` | `cash`.

Historical documents keep snapshots (invoice customer name/mobile/address; invoice item title/description/quantity/unit_price/total). Changing the live customer must not rewrite old invoices.

## Calendar

No `calendars` table. The calendar is derived from `service_assignments`.

## Auth

Internal guard vs `customer` guard (OTP). Policies + permissions.

## Storage paths (source)

```
storage/app/public/company/logo/
storage/app/public/service-reports/problems|before|after|damaged-parts/
storage/app/public/receptions/
```

## Production (hardening)

- `APP_ENV=production`. `APP_DEBUG` must be `false`. Debug exception dumps are not a production default; the application also forces `app.debug=false` when the environment is production.
- Secrets (`APP_KEY`, database password, `ZARINPAL_MERCHANT_ID`) stay in `.env` / a secret store. Never store card number, CVV, bank OTP, or customer login OTP in the database or logs.
- Gateway success is valid only after real gateway Verify. A user redirect is not proof of payment.
- Rate limits apply to internal login, customer OTP, and payment start/callback routes.

## Logging

- Operational logs: failed internal login (username only), failed OTP verify (`customer_id` only), gateway verify rejection/failure (`payment_id`, gateway code / HTTP status).
- Never log passwords, OTP codes, card numbers, CVV, merchant secrets, or raw gateway payloads that could contain them.
- Production: `LOG_CHANNEL=stack`, `LOG_STACK=daily`, `LOG_LEVEL=info` (or `warning`). Keep `storage/logs/` off the public web root (Laravel default).

## Backup (product-level, per independent install)

Each company has its own database and `storage/` tree. No third-party backup SaaS and no extra business table.

1. **MySQL** — daily `mysqldump` (or equivalent) of that install’s database, with `--single-transaction` and `--routines`. Keep at least 7 daily copies plus one weekly copy off-box. Restore is `mysql < dump.sql` onto an empty database for that company only.
2. **Files** — copy `storage/app/public/` (company logo, service-report images, reception PDFs, payment receipt images) with the same cadence. Paths above plus implemented `payment-receipts/` under `storage/app/public/`.
3. **Config** — back up `.env` separately from the dump (secrets). Do not commit it.
4. Test a restore on a staging copy of that install before relying on backups. Queue/cache tables (`jobs`, `cache`) may be omitted if you accept re-running queued SMS/email stubs after restore.
