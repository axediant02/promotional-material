# Existing Features

This document lists what is implemented in the codebase today.

## Implemented now

### Authentication
- Client self-registration
- Login and logout
- Token-based auth using Sanctum
- Route guards on the frontend

### Roles currently implemented in live routes and UI
- `client`
- `agent`
- `production`

### Backend schema and model foundations now present
- `client_requests` table and model
- `assigned_clients` table and model
- `admin` included in the backend role enum
- target-style backend keys and names:
  - `user_id`
  - `folder_id`
  - `file_id`
  - `folder_name`
  - `file_name`
  - `category`
- simplified one-client-one-folder backend relationship model without `parent_id`

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
- Automatic assignment of the approved client to the created folder through `assigned_folder_id`

### File management
- File upload by production
- File listing
- File detail view
- File update
- File soft delete
- File restore
- File preview
- File download
- Category-based file metadata persisted in the backend

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
- Admin behavior is still represented by production-only admin routes in the current code.
- The backend schema has moved toward the target naming and relationship model, but the frontend and detailed docs are still catching up.
- `client_requests` and `assigned_clients` exist in backend schema/models, but request-management routes and UI are not complete yet.
- The current live app still behaves operationally like a production-admin portal even though the backend schema now includes `admin` in the role enum.
- Compatibility accessors still bridge some old frontend expectations, so schema naming and UI payload usage should be treated as an active migration area.

## Planned but not fully implemented yet
- Separate live `admin` role behavior across backend routes and frontend UI
- request CRUD and request visibility rules in live routes
- due-date management in the UI
- assignment-management workflow in the UI
- full admin-managed client request workflow

## Recommendation for agents
- Treat this file as the "implemented truth."
- Treat [current-vs-planned.md](./current-vs-planned.md) as the guide for what the system is still evolving toward.
