# Request Workflow

This document describes the target client request system. The backend schema and models now exist, but the full workflow is not implemented across the live routes and UI yet.

## Purpose
Allow clients to request:
- new assets
- updates to existing assets

Requests are managed internally by admin and production users.

## Planned core table

### `client_requests`
- `request_id`
- `client_id`
- `folder_id`
- `title`
- `description`
- `request_type`
- `status`
- `due_date`
- `created_at`
- `updated_at`
- `deleted_at`

### Planned values
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
- `created_at`
- `updated_at`

## Planned access rules

### Client
- can create requests
- can view own requests
- cannot set due dates
- cannot view other clients' requests

### Admin
- can view all client requests
- can manage request state
- can assign production ownership to clients
- can set `due_date`

### Production
- can view client requests for assigned clients
- can work requests through operational completion

### Agent
- no request module access in v1

## Planned workflow
1. Client creates request.
2. Request is linked to client and folder.
3. Admin reviews request.
4. Admin sets `due_date`.
5. Assigned production user handles the request.
6. Request status progresses from `pending` to `in_progress` to `done`.

## Implementation note
- The agreed direction is client-level production assignment through `assigned_clients`.
- Per-request `assigned_to` is not the preferred target model.
- Read this file together with [existing-features.md](./existing-features.md) and [current-vs-planned.md](./current-vs-planned.md): the data foundation exists in backend, but the operational feature is still incomplete.
