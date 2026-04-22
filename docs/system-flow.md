# System Flow

This document describes the implemented system flow for onboarding, file delivery, and request handling.

## System flow

### 1. Registration
1. User opens `/register`.
2. User submits registration form.
3. Backend creates the account with default role `client`.
4. User can sign in immediately.

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
   - the `admin` redirect is frontend scaffolding; dedicated live backend admin-role behavior is still incomplete.

### 3. File upload and delivery
1. Production uploads files to the assigned client folder.
2. Backend stores the file on the configured disk.
3. Backend saves file metadata in the database.
4. Upload is logged to `activity_logs`.
5. Client can view, preview, and download files from the assigned folder only.
6. Agent visibility continues to follow the current operational rules.

### 4. Client request flow
1. Client signs in.
2. If the client has no assigned folder, the backend creates one and sets `assigned_folder_id`.
3. Client submits a request.
4. Request is linked to the client's assigned folder.
5. Request starts as `pending`.

### 5. Recycle bin
1. Production deletes a file.
2. Backend sets `last_deleted_at` and soft deletes the record.
3. File appears in recycle bin.
4. Production can restore it.
5. Scheduled purge later removes expired deleted files.

## Backend foundations already present
- the backend already contains `client_requests` and `assigned_clients`
- the backend schema uses target-style keys and names such as `user_id`, `folder_id`, `file_id`, `folder_name`, and `file_name`
- these foundations do not by themselves mean the full request workflow or admin-role separation is live across the app

## Planned target flow

### 1. Role model
- `admin` manages the system and request workflow
- `production` handles client work and uploads
- `agent` handles cross-client file access only
- `client` accesses own files and creates requests

### 2. Client request flow
1. Client creates a request of type `new_asset` or `update_asset`.
2. The request is linked to the client and assigned folder.
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
- Clients should access only their assigned folder and files.
- Authorization should always be enforced by the backend.
- Schema foundations should be treated separately from fully live routes and UI.
