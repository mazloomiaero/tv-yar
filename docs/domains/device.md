# Domain: Device

## Owns

`devices`, `device_types`, `brands`, plus device-side custom field mappings/values.

## Rules from source

- A device belongs to a customer (`customer_id`).
- Type and brand are catalog entities (soft-deletable).
- Variable attributes (screen size, IMEI, gas type, …) are **custom fields**, not extra `devices` columns.
- Documented device columns: model, serial_number, title, purchase_date, warranty_status, warranty_until, notes.

## Actions

`CreateDevice`, `UpdateDevice`.

## Workshop

Receiving a device in workshop does **not** create a second subsystem. Status on the service request changes; `device_receptions` stores the receipt.

## Gaps

Typed columns for `device_custom_field_values` are not in the source dictionary. `value` VARCHAR(255) was added using the catalog name-column gap pattern (`docs/DATABASE.md`). Industry attributes stay on custom fields, not extra `devices` columns.

`device_types.name` and `brands.name` (VARCHAR 150) were added as catalog identifiers for Admin DeviceType/Brand controllers, citing source examples TV, Air Conditioner, Mobile. No other catalog columns.
