# Existing Features

This document lists what is implemented in the codebase today.

## Implemented now

### Authentication
- Client self-registration
- Login and logout
- Token-based auth using Sanctum
- Route guards on the frontend

### Roles currently implemented in code
- `client`
- `agent`
- `production`

### Client management
- Pending client approval list
- Approve or reject client registration
- Automatic folder creation on approval when the client has no assigned folder

### Folder management
- Folder listing
- Folder creation
- Folder detail view
- Folder update
- Client folder scoping in the current implementation

### File management
- File upload by production
- File listing
- File detail view
- File update
- File soft delete
- File restore
- File preview
- File download

### Dashboards
- Client dashboard
- Agent workspace
- Production admin overview

### Activity tracking
- Upload action logging
- Delete action logging
- Restore action logging
- Activity log listing

### Recycle bin
- Soft delete files
- Recycle bin listing
- Restore deleted files
- Scheduled purge command exists in backend

## Partially implemented or mismatched
- Admin behavior is represented by production-only admin routes in the current code.
- Folder model currently still supports `parent_id`, but the agreed planned schema removed nested folders for now.
- File model currently uses:
  - `original_name`
  - `mime_type`
  - `size`
  instead of the later planned `file_name` and `category` target schema.

## Planned but not implemented yet
- Separate `admin` role
- `client_requests` feature
- `assigned_clients` production-to-client assignment model
- request `due_date`
- request statuses and request types
- request visibility rules for admin and production
- admin-managed client request workflow

## Recommendation for agents
- Treat this file as the "implemented truth".
- Treat [current-vs-planned.md](./current-vs-planned.md) as the guide for what the system is supposed to evolve into.
