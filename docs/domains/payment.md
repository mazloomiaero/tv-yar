# Domain: Payment

## Owns

`payments`, `payment_transactions`. Services: `PaymentService`, `GatewayInterface`, `ZarinpalGateway`, other/future gateways.

## Actions

`CreatePayment`, `StartGatewayPayment`, `VerifyGatewayPayment`, `RecordManualPayment`.

## Events

`PaymentInitiated`, `PaymentConfirmed`.

Job: `VerifyPaymentJob`.

## Methods

| method | How | Extra |
|---|---|---|
| `gateway` | Online; row in `payment_transactions`; real Verify | Redirect is not success |
| `card_transfer` | Technician records; `receipt_image` **required**; `reference_number` | `pending_confirmation` until Accountant |
| `cash` | Technician records; receipt optional | `pending_confirmation` until Accountant |

`recorded_by` is the recording user (technician for manual methods).

## Forbidden

Storing card, CVV, OTP, or bank secrets. Inventing warehouse payments or a generic accounting module.

## Gaps

Exact VARCHAR values for payment `status` besides manual `pending_confirmation` and invoice `paid` after accountant confirm are not fully enumerated for gateway rows.

Implemented (not in the architecture dictionary): `initiated` (gateway start), `completed` (after real Verify or accountant confirm). `payment_transactions.status` uses `initiated` / `verified` / `failed` as row values. There is no `PaymentFailed` event.

Source storage list has no payments folder. Receipt images are stored under `storage/app/public/payment-receipts/` (path + metadata in `payments.receipt_image` only).

`payments.reference_number` is NOT NULL: gateway rows get a generated `GW-…` value until Verify may replace it with the gateway reference; cash without a user reference gets a generated `CASH-…` value.

Zarinpal integer amount is the invoice DECIMAL rounded to a whole unit (IRR conversion is not specified in the source). Merchant id lives in `.env` only and is not stored on `payment_transactions`.
