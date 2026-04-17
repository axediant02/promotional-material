# System Flow

This document describes the major flows in the system, separating current implemented flow from planned target flow when needed.

## Current implemented flow

### 1. Client registration and approval
1. Client opens `/register`.
2. Client submits registration form.
3. Backend creates a `client` user with `pending` status.
4. Production opens the admin overview.
5. Production reviews pending clients.
6. On approval:
   - client status becomes `approved`
   - a folder is created if one does not already exist
   - `assigned_folder_id` is set on the user

### 2. Login and role routing
1. User submits login credentials.
2. Backend validates credentials and status.
3. Backend returns token and user payload.
4. Frontend stores token in local storage.
5. Frontend redirects by role:
   - `production` -> `/admin`
   - `agent` -> `/agent`
   - `client` -> `/client`

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
- Only approved clients should access the file portal.
- Authorization should always be enforced by the backend.
