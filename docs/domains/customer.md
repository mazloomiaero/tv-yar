# Domain: Customer

## Owns

`customers`, `customer_addresses`. Optional link `customers.user_id` to a user account.

## Rules from source

- Multiple addresses per customer.
- `customer_code` is unique; `mobile` is **not** unique.
- Operator flow: search by mobile, create customer if missing.
- Soft-deletable master data.
- Customer portal: OTP login on guard `customer`; panel controllers under `Customer/` (profile, addresses, devices, requests, invoices, payments, feedback).

## Actions

`CreateCustomer`, `UpdateCustomer`.

## Does not own

Service request workflow, invoices (financial snapshots customer name at invoice time), warehouse.

## Gaps (not in source)

Full column list for `customer_addresses`. Address fields used on invoices are snapshotted as `customer_address` text on `invoices`.
