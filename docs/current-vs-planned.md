# Current Vs Planned

This document tracks the main differences between the current codebase and the agreed target system state.

## Roles

### Current
- `production`
- `agent`
- `client`

### Planned
- `admin`
- `production`
- `agent`
- `client`

## Admin behavior

### Current
- Production performs admin-only actions.
- Frontend `/admin` route is a production route.
- Backend `/api/admin/*` routes are guarded by `role:production`.

### Planned
- `admin` becomes a first-class role.
- Admin manages request workflow and production assignment to clients.

## Folder model

### Current
- `folders` supports `parent_id`
- nested folder behavior is still present in schema and controller

### Planned
- one client-facing folder per client
- no parent folder support for the MVP target

## File schema

### Current
- `original_name`
- `mime_type`
- `size`
- `storage_disk`
- `storage_path`

### Planned
- `file_name`
- `category`
- `storage_disk`
- `storage_path`
- support categories:
  - `image`
  - `video`
  - `pdf`

## Request workflow

### Current
- no request tables
- no request routes
- no request UI

### Planned
- `client_requests`
- `assigned_clients`
- admin-managed due dates
- client request types:
  - `new_asset`
  - `update_asset`

## Production ownership

### Current
- no persistent client-to-production assignment model

### Planned
- client-level production ownership through `assigned_clients`

## Why this matters
- Agents should not assume every planned rule is already live in code.
- When documenting, debugging, or planning changes:
  - use `existing-features.md` for current truth
  - use this file for target-state alignment
