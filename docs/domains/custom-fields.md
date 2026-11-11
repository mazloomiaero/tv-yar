# Domain: Custom Fields

Industry differences must not become extra columns on `devices`.

## Tables

```
custom_fields
  ├── custom_field_options
  ├── device_type_custom_fields
  ├── service_type_custom_fields
  ├── device_custom_field_values
  └── service_request_custom_field_values
```

Two value tables (device vs request) **on purpose**: real foreign keys, not a polymorphic value table.

## Examples in source (illustrative, not required seed data)

- TV: Screen Size, Panel Type, Panel Code  
- Air conditioner: Capacity, Gas Type, Compressor Type  
- Mobile: IMEI, Storage, Color  

Suggestions for technicians use stored custom field values.

## Service

`CustomFieldService`. Unit test named in source: `CustomFieldServiceTest`.

## Gaps

Column types were not in the source dictionary. Identifier/value columns were added using the catalog name-column gap pattern (`docs/DATABASE.md`): `custom_fields.name`, `custom_field_options.name`, `device_custom_field_values.value`, `service_request_custom_field_values.value`. No type/required/sort_order/JSON columns.
