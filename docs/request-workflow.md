# Request Workflow

This document describes the implemented client request workflow. Backend foundations already exist, but the full admin-managed workflow is still incomplete across live routes and UI.

## Purpose
Allow clients to request:
- new assets
- updates to existing assets

Requests are managed internally by admin and production users.

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
- can manage request state
- can assign production ownership to clients
- can set `due_date`

### Production
- can view requests for assigned clients
- can work requests through completion
- can upload files for client requests

### Agent
- no request module access in v1

## Current Workflow
1. User registers with default role `client`.
2. User can sign in immediately.
3. If the client has no assigned folder yet, the first submitted request creates and assigns it automatically.
4. Client submits a request.
5. Request is linked to the client and assigned folder.
6. Request is created with status `pending`.

## Operational Notes
- Registration does not create the folder.
- The first request creates the assigned folder, and future requests reuse it.
- Clients can access files in their assigned folder after production uploads them.

## Implementation Note
- Backend schema and models for requests and assignments already exist.
- Full end-to-end request handling is still not fully exposed across all live routes and screens.
- Read this file with:
  - [system-flow.md](./system-flow.md)
  - [api-reference.md](./api-reference.md)
  - [schemas-relationship.md](./schemas-relationship.md)
