# Current Vs Planned

This document tracks the main differences between the current codebase and the agreed target system state.

## Roles

### Current
- live behavior and routes still center on:
  - `production`
  - `agent`
  - `client`
- backend schema now includes `admin` in the role enum

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
- `admin` becomes a first-class live role.
- Admin manages request workflow and production assignment to clients.

## Folder model

### Current
- backend schema now uses:
  - `folder_id`
  - `folder_name`
  - `client_id`
  - `created_by`
- one client-facing folder per client is now reflected in backend schema
- nested folder support is no longer part of the current backend model

### Planned
- keep one client-facing folder per client
- complete the rest of the stack around this simplified model

## File schema

### Current
- backend schema now uses:
  - `file_id`
  - `folder_id`
  - `uploaded_by`
  - `file_name`
  - `storage_disk`
  - `storage_path`
  - `category`
  - `last_deleted_at`
- older frontend consumers may still read compatibility fields while the contract transition is being completed

### Planned
- complete the contract migration across frontend payload handling and documentation
- support categories:
  - `image`
  - `video`
  - `pdf`

## Request workflow

### Current
- backend schema and models now include:
  - `client_requests`
  - `assigned_clients`
- full request routes, UI, and due-date workflow are still incomplete
- `request-workflow.md` should be read as target-state behavior layered on top of current backend foundations

### Planned
- expose full `client_requests` workflow in routes and UI
- add admin-managed due dates
- add request visibility and request handling workflows for the correct roles

## Production ownership

### Current
- backend schema now includes persistent client-to-production assignment through `assigned_clients`
- operational assignment-management flow is not fully exposed in the app yet

### Planned
- use `assigned_clients` as the live client-level ownership model
- complete management and visibility behavior in routes and UI

## Why this matters
- Agents should not assume every planned rule is already live in routes and UI just because the schema exists.
- When documenting, debugging, or planning changes:
  - use `existing-features.md` for current truth
  - use this file for target-state alignment
  - distinguish "backend foundation present" from "feature fully implemented"
