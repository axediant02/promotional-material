# System Flow

This document describes the major flows in the system, separating current implemented flow from planned target flow when needed.

## Current implemented flow

### 1. Client registration and folder assignment
1. Client opens `/register`.
2. Client submits registration form.
3. Backend creates a `client` user.
4. Backend creates the client's folder immediately.
5. Backend sets `assigned_folder_id` on the user.

### 2. Login and role routing
1. User submits login credentials.
2. Backend validates credentials.
3. Backend returns token and user payload.
4. Frontend stores token in local storage.
5. Frontend redirects by role:
   - `production` -> `/production`
   - `agent` -> `/agent-new`
   - `admin` -> `/admin-new`
   - `client` -> `/client`
6. Notes:
   - `/admin` still exists as the current production-operated admin workspace.
   - `/production`, `/agent-new`, and `/admin-new` are the current auth-store default routes.
   - the `admin` redirect exists in frontend scaffolding, but dedicated live backend admin-role behavior is still not fully implemented.

### 2.5 Backend data foundations already added
- the backend already contains `client_requests` and `assigned_clients`
- the backend schema now uses target-style keys and names such as `user_id`, `folder_id`, `file_id`, `folder_name`, and `file_name`
- these foundations do not yet mean the full request workflow is live in routes and UI

### 3. File upload and delivery
1. Production uploads a file to a folder.
2. Backend stores the file on the configured disk.
3. Backend saves file metadata in the database.
4. Upload is logged to `activity_logs`.
5. Clients and agents can list or preview files based on access.

### 4. Recycle bin
1. Production deletes a file.
2. Backend sets `last_deleted_at` and soft deletes the record.
3. File appears in recycle bin.
4. Production can restore it.
5. Scheduled purge later removes expired deleted files.

## Planned target flow

### 1. Role model
- `admin` manages the system and request workflow
- `production` handles client work and uploads
- `agent` handles cross-client file access only
- `client` accesses own files and creates requests

### 2. Client request flow
1. Client creates a request of type `new_asset` or `update_asset`.
2. The request is linked to the client and folder.
3. Admin reviews the request.
4. Admin sets `due_date`.
5. Production handles the request based on client-level assignment.
6. Request moves through:
   - `pending`
   - `in_progress`
   - `done`

### 3. Client-to-production assignment
1. Admin assigns a production user to a client through `assigned_clients`.
2. Production becomes responsible for that client's request flow.
3. Request ownership is determined by the client assignment, not by per-request manual assignment.

## Flow boundaries
- Agents should not enter the request workflow in v1.
- Clients should not set due dates.
- Clients should not access files or request flows without an assigned folder.
- Authorization should always be enforced by the backend.
- The existence of request and assignment tables should be treated as backend groundwork until route and UI support is completed.
