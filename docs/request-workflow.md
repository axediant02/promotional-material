# Request Workflow

This document describes the current request lifecycle, ownership model, and route responsibilities.

## Purpose
Clients can request:
- new assets
- updates to existing assets

Admin governs due dates and assignment context. Production executes the work.

## Core request data

### `client_requests`
- `request_id`
- `client_id`
- `folder_id`
- `title`
- `description`
- `request_type`
- `status`
- `due_date`
- timestamps
- soft-delete fields

### Supported values
- `request_type`
  - `new_asset`
  - `update_asset`
- `status`
  - `pending`
  - `in_progress`
  - `done`

## Ownership model

### `assigned_clients`
- `id`
- `production_id`
- `client_id`
- `status`
- timestamps

Assignments live at the client level, not the individual request level.

## Access rules

### Client
- can create requests
- can view own request history
- cannot set due dates
- cannot view other clients' requests

### Admin
- can view all client requests
- can set and update `due_date`
- can assign a client to a production owner
- can remove a client assignment
- can update user roles

### Production
- can view requests for assigned clients
- can update request status
- can upload files for assigned-client work

### Agent
- does not access request routes
- can browse and download files allowed by backend authorization

## Current workflow
1. A user registers and receives the `client` role.
2. The client signs in.
3. The client submits a request through `POST /requests`.
4. If the client does not yet have an assigned folder, the backend creates and assigns it during that first request.
5. The request is stored with `pending` status.
6. The client can review request history through `GET /requests`.
7. Admin reviews the full queue through `GET /admin/requests`.
8. Admin sets or updates the due date through `PATCH /admin/requests/{clientRequest}`.
9. Admin links the client to production through `POST /admin/assignments`.
10. Production fetches requests for assigned clients through `GET /production/requests`.
11. Production updates operational status through `PATCH /production/requests/{clientRequest}`.

## Admin management UI
- The admin dashboard at `/admin` shows admin stats, the request queue, assignments, activity logs, and user-role context.
- The request queue supports inline due-date editing.
- Saving a due date updates the request queue and admin counts from the live API response.
- Assignment management uses the live admin assignment routes instead of static placeholder data.
- `/admin-new` remains only as a compatibility redirect to `/admin`.

## Operational notes
- Registration does not create a folder.
- The first request creates the client's assigned folder.
- Future requests reuse the existing assigned folder.
- Production status updates and admin due-date updates are separate routes with separate validation rules.
- The admin due-date route prohibits `status`.
- The production status route prohibits `due_date`.

## TDD rule
- New backend request-workflow changes should be written test-first.
- Once an approval test is accepted, treat it as fixed acceptance criteria.
- If that test fails, fix the implementation or setup instead of weakening the test.

## Related docs
- [system-flow.md](./system-flow.md)
- [api-reference.md](./api-reference.md)
- [schemas-relationship.md](./schemas-relationship.md)
