# Request Workflow

This document defines the agreed request workflow and role ownership for the portal.

## Purpose
Allow clients to request:
- new assets
- updates to existing assets

Requests are governed by admin and executed by production.

## Core Request Data

### `client_requests`
- `request_id`
- `client_id`
- `folder_id`
- `title`
- `description`
- `request_type`
- `status`
- `due_date`
- timestamps and soft-delete fields

### Expected values
- `request_type`
  - `new_asset`
  - `update_asset`
- `status`
  - `pending`
  - `in_progress`
  - `done`

## Ownership Model

### `assigned_clients`
- `id`
- `production_id`
- `client_id`
- `status`
- timestamps

Client ownership is intended to live at the client level, not through per-request assignment.

## Access Rules

### Client
- can create requests
- can view own requests
- cannot set due dates
- cannot view other clients' requests

### Admin
- can view all client requests
- can assign production ownership to clients
- can set `due_date`
- can manage user-role changes
- does not use the file portal directly by default

### Production
- can view requests for assigned clients
- can work requests through completion
- can upload files for client requests

### Agent
- no request module access in v1
- can browse and download allowed files only

## Current Workflow
1. User registers with default role `client`.
2. User can sign in immediately.
3. If the client has no assigned folder yet, the first submitted request creates and assigns it automatically.
4. Client submits a request.
5. Request is linked to the client and assigned folder.
6. Client can fetch request history through the client request listing route.
7. Admin can review all requests and set or update `due_date`.
8. Admin assigns the client to production when operational ownership is needed.
9. Production fetches requests for assigned clients only.
10. Production works the request through `pending`, `in_progress`, and `done`.

## Operational Notes
- Registration does not create the folder.
- The first request creates the assigned folder, and future requests reuse it.
- Production uploads files for assigned-client work.
- Agents can download allowed files for operational use.
- Clients can download files in their assigned folder only.

## TDD Rule
- New backend request-workflow changes should be written test-first.
- Once the expected behavior test is written and accepted, treat it as fixed acceptance criteria.
- If the test fails, adjust the implementation or fixtures instead of weakening the test to manufacture a pass.

## Implementation Note
- Backend schema and models for requests and assignments already exist.
- The backend route surface now covers client history, admin due-date management, and production status handling.
- Assignment-management routes and full frontend alignment are still incomplete.
- Read this file with:
  - [system-flow.md](./system-flow.md)
  - [api-reference.md](./api-reference.md)
  - [schemas-relationship.md](./schemas-relationship.md)
