# Domain: Reporting

## Service

`ReportService`. Accountant panel includes `FinancialReportController`. Phase 10: management, financial, and technical reports/dashboards.

## Rule

Reporting must **not** reimplement domain logic (assignment rules, payment verify, custom field storage). Read committed data.

## Related

`is_suggested` on assignments exists so later reports can measure how often suggestions were used.

## Gaps

No KPI list, no report SQL, and no dashboard widgets are specified in the source. Do not invent metrics as requirements.
