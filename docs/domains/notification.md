# Domain: Notification

## Owns

`notifications` table (columns not specified in source), `NotificationService`, queued jobs.

## Jobs

`SendSmsJob`, `SendEmailJob`, `SendServiceNotificationJob`.

## Notification classes

`ServiceRequestCreatedNotification`, `TechnicianAssignedNotification`, `ServiceStatusChangedNotification`, `PaymentConfirmedNotification`, `FeedbackRequestNotification`, `DeviceReadyNotification`.

## Rule

Notifications are fed by **domain events**. This domain must not reimplement Service Request or Payment logic.

## Event mapping (named classes only)

Listeners are queued and pass **entity id only**. `NotificationService` records `notifications.name` and dispatches `SendServiceNotificationJob`, `SendSmsJob`, and `SendEmailJob` (stub log/queue; no SMS vendor). Jobs do not choose recipients — the channel matrix is unspecified.

| Domain event | Notification class |
|---|---|
| `ServiceRequestCreated` | `ServiceRequestCreatedNotification` |
| `ServiceRequestAssigned` | `TechnicianAssignedNotification` |
| `ServiceStatusChanged` | `ServiceStatusChangedNotification` |
| `PaymentConfirmed` | `PaymentConfirmedNotification`, `FeedbackRequestNotification` |
| `DeviceDelivered` | `DeviceReadyNotification` |

`FeedbackRequestNotification` uses existing `PaymentConfirmed` (core process: Payment → Feedback). `DeviceReadyNotification` uses existing `DeviceDelivered`. No extra events.

## Gaps

Channel matrix (which role gets which SMS) and event payloads beyond entity id are not specified. `notifications` has only a name identifier — no recipient/channel/body columns.
