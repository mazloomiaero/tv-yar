# PROJECT

Source of truth: architecture document (`index.html`). This file is a short agent-facing summary. It must not add requirements that are not in that document.

## What this product is

A **general** Field Service Management and repair platform. TV repair is only the first usage scenario. The same architecture is intended for appliances, air conditioners, mobiles, laptops, industrial equipment, installation, and other technical services.

It is **not** a TV-only product and **not** a warehouse, GPS, payroll, or multi-tenant SaaS product.

## Stack and deployment

- Laravel + PHP, MySQL, `utf8mb4`.
- **Independent installation per company**: shared Laravel core/codebase, separate domain and **separate database** per company.
- No `tenant_id`. No shared business data across companies. No cross-install data monitoring in the current plan.

## Numbers from the source

- **41** database tables (final).
- **6** main panels: Admin/Manager, Operator, Technical Supervisor, Accountant, Technician, Customer.
- **1** Laravel core.

## Core process

Customer → Device → Service Request → Assignment → Visit → Diagnosis → Repair → Report → Invoice → Payment → Feedback.

Request channels: Website, Phone, Walk-in, and future API.

Operator create-request steps: search customer by mobile → create customer if missing → select or create device → select service type → select address → record problem → set priority → preferred date/time window → assign technician if needed (manual or from suggestions) → save with initial status.

## In scope

Company and settings; users and roles; customers (multiple addresses); devices, device types, brands; service types; custom fields; service requests and status history; technician assignment and calendar-from-assignments; service reports (consumed parts + images); workshop device reception + receipt; invoices; online and manual payments; feedback; notifications; management dashboards/reports.

## Out of scope

Real multi-tenancy; warehouse/inventory; mobile app (Web first); GPS/maps; full accounting; payroll; independent calendar table; complex workflow engine; work orders / appointments / checklists as separate modules.

## Implementation phases (source)

1. Foundation — Laravel, auth, company, settings, users, roles, permissions  
2. Customer & Device  
3. Service configuration (types, custom fields, statuses)  
4. Service Request  
5. Technician (skills, assignment, scheduling, calendar)  
6. Service Report  
7. Workshop (reception + PDF receipt)  
8. Financial (invoice, items, payment, gateway transaction)  
9. Feedback & notification  
10. Dashboard & reports  
11. Hardening (security, authorization, testing, logging, performance, backup)

## Current repository

This repo is a Laravel skeleton. Domain schema and panels are **not** implemented yet. Next engineering work should follow the phases above, starting with Foundation **after** these docs (Architect phase 1).
