# CONTRACTS

Shared language between agents. Details must match the architecture document. **No REST JSON schemas are defined in the source** — do not invent `/api/v1` payloads.

## Core process contract

Customer → Device → Service Request → Assignment → Visit → Diagnosis → Repair → Report → Invoice → Payment → Feedback.

## Actions (inputs implied by names + documented fields)

| Action | Domain notes |
|---|---|
| `CreateCustomer` / `UpdateCustomer` | `mobile` is not unique. `customer_code` is unique. Optional `user_id`. |
| `CreateDevice` / `UpdateDevice` | Belongs to customer; type + brand; variable attributes via custom fields, not extra device columns. |
| `CreateServiceRequest` | Channels: website, phone, walk-in, future API. Initial status stored. Number from `document_sequences`. |
| `SuggestTechnician` | Based on service type, custom field values, `technician_skills`, count of open assignments. Display only; user still chooses. |
| `AssignTechnician` | Always manual. Set `is_suggested` if the pick came from suggestions. Optional `branch_id`. |
| `ChangeServiceStatus` | Allowed statuses from `service_type_statuses`. Write `service_status_history`. Not a workflow engine. |
| `CloseServiceRequest` | Sets `closed_at`. |
| `CreateServiceReport` | Parts from catalog or free text; images before/after. Confirmed parts feed invoice lines. |
| `ReceiveDevice` / `DeliverDevice` | Workshop continuation of the same request; `device_receptions` receipt. |
| `CreateInvoice` | Lines from confirmed `service_report_parts`. Snapshot customer name/mobile/address. |
| `CreatePayment` / `StartGatewayPayment` / `VerifyGatewayPayment` | Verify with gateway; never trust redirect alone. |
| `RecordManualPayment` | `card_transfer` (receipt required) or `cash` (receipt optional). Status `pending_confirmation` until Accountant confirms. |
| `CreateFeedback` | Customer satisfaction after service. |

## Events

`ServiceRequestCreated`, `ServiceRequestAssigned`, `ServiceStatusChanged`, `ServiceRequestClosed`, `ServiceReportCreated`, `DeviceReceived`, `DeviceDelivered`, `InvoiceCreated`, `PaymentInitiated`, `PaymentConfirmed`, `FeedbackSubmitted`.

Payload fields are **not** specified in the source. Listeners must not invent a required payload contract beyond the entity ids implied by the event name.

## Jobs (source)

`SendSmsJob`, `SendEmailJob`, `SendServiceNotificationJob`, `VerifyPaymentJob`, `GenerateDocumentJob`.

## Notifications (source)

`ServiceRequestCreatedNotification`, `TechnicianAssignedNotification`, `ServiceStatusChangedNotification`, `PaymentConfirmedNotification`, `FeedbackRequestNotification`, `DeviceReadyNotification`.

## Permissions (samples from source)

```
customers.view | create | edit | delete
devices.view | create | edit
requests.view | create | edit | assign | change_status | close
reports.view | create | edit
invoices.view | create | edit
payments.view | create | verify
feedback.view
```

Roles: Company Admin/Manager, Operator, Technical Supervisor, Accountant, Technician, Customer.

## Financial contract

- Amount source: confirmed `service_report_parts` (`unit_price` may be edited from `parts.default_unit_price`; `part_id` nullable).
- Invoice status: `pending_payment` | `partially_paid` | `paid` | `cancelled`.
- Payment method: `gateway` | `card_transfer` | `cash`.
- Gateway rows go to `payment_transactions`. Manual payments do not use that table for verify.
- Do not store card/CVV/OTP.

## Numbering contract

Atomic increment of `document_sequences.current_number` per `document_type` + `year` with `lockForUpdate`.

## Handoff between agents

Database (schema) → Operations (requests, assignments, reports, workshop) → Financial (invoice/payment) → Support (events, notifications, custom fields, reports) → QA.

Example: Support does not reimplement Service Request; it consumes `ServiceRequestClosed` / related events.
