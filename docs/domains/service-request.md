# Domain: Service Request

Operational core of the system. Not a work order module.

## Owns

`service_types`, `service_type_device_types`, `service_statuses`, `service_type_statuses`, `service_requests`, `service_status_history`, request-side custom field values.

## Create (operator)

Search/create customer → select/create device → service type → address → problem → priority → preferred date/time → optional assignment → save with initial status.

Sources: `website`, `phone`, `walk-in`, future `api` (`source` VARCHAR(30)).

## Status

Configurable per service type. History in `service_status_history`. **No complex workflow engine.**

Sample main statuses and side statuses: see `docs/ARCHITECTURE.md`.

## Numbering

`request_number` via `document_sequences` (REQUEST / `SR`).

## Actions

`CreateServiceRequest`, `SuggestTechnician`, `AssignTechnician`, `ChangeServiceStatus`, `CloseServiceRequest`.

## Events

`ServiceRequestCreated`, `ServiceRequestAssigned`, `ServiceStatusChanged`, `ServiceRequestClosed`.

## Calendar

Scheduling lives on assignments, not on a calendar table.

## Gaps

Full columns for status tables, service types, and history rows are not in the source dictionary.
