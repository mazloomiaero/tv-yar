# Domain: Invoice

## Owns

`invoices`, `invoice_items`. Action: `CreateInvoice`. Event: `InvoiceCreated`. Service: `InvoiceService`.

## Amount

After confirmation, sum of `service_report_parts` becomes `invoice_items`.

Technician may edit `unit_price` (prefilled from `parts.default_unit_price`) or enter a free part (`part_id` null).

## Snapshot (must persist on the document)

Invoice: `customer_name`, `customer_mobile`, `customer_address`.  
Item: `title`, `description`, `quantity`, `unit_price`, `total`.

## Statuses

`pending_payment`, `partially_paid`, `paid`, `cancelled`.

Also documented money fields: `subtotal`, `discount`, `tax`, `total`; `issued_at`, `paid_at`.

## Numbering

`INV` via `document_sequences`.

## Gaps

`customer_addresses` has no street/city (or other address text) columns. `invoices.customer_address` is still snapshotted at issue time and may be empty.

`invoice_items` snapshot columns are migrated (`title`, `description`, `quantity`, `unit_price`, `total`). `description` is empty until report parts gain a description field.

Discount and tax are accountant-provided amounts (default 0). There is no tax-rate engine.

## Does not own

Gateway verify (Payment domain). Full general ledger / payroll (out of product scope).
