# Domain: Technician

## Owns

`technicians`, `skills`, `technician_skills`, `service_assignments`.

## Assignment

- Always **manual**.
- Suggestions: service type, custom field values, skills, number of **open** assignments.
- `is_suggested` records whether the chosen technician was from the suggestion list (for later reporting).
- `branch_id` on assignment is nullable. Technicians relate to `branches`.

## Documented assignment columns

`scheduled_date`, `start_time`, `end_time`, `status`, `technician_response`, `accepted_at`, `started_at`, `completed_at`, `assigned_by`.

This is the **calendar source**. No independent calendar table.

## Actions

`SuggestTechnician`, `AssignTechnician`.

Technician panel: dashboard, assignments, related service requests, service reports.

## Gaps

No typed dictionary for `technicians`, `skills`, `technician_skills` (including how branch is stored on the technician).
