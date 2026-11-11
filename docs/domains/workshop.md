# Domain: Workshop

Not an independent product. The device **follows the same Service Request**; status changes.

## Flow

Transfer to Workshop → Device Received → In Workshop → Repairing → Ready for Delivery → Delivered.

## Owns

`device_receptions` (reception receipt). Actions: `ReceiveDevice`, `DeliverDevice`. Events: `DeviceReceived`, `DeviceDelivered`.

## Receipt content (narrative, untyped)

Receipt number, request, customer, device, receiving user, received date, appearance, accompanying items, reason, notes, delivery date.

PDF generation is in scope. Customer can see the receipt in the portal.

Numbering prefix `REC` via `document_sequences`.

## Out of scope here

Warehouse, stock, spare-parts inventory (catalog `parts` belongs to reporting/invoicing, not stock).
