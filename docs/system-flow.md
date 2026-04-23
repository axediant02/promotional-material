# System Flow

This document describes the agreed system flow for onboarding, governance, file delivery, and request handling.

## 1. Registration
1. User opens `/register`.
2. User submits registration form.
3. Backend creates the account with default role `client`.
4. User can sign in immediately.

## 2. Login and role routing
1. User submits login credentials.
2. Backend validates credentials.
3. Backend returns token and user payload.
4. Frontend stores the token in local storage.
5. Frontend redirects by role:
   - `production` -> `/production`
   - `agent` -> `/agent-new`
   - `admin` -> `/admin-new`
   - `client` -> `/client`

## 3. Request creation
1. Client signs in.
2. If the client has no assigned folder yet, the backend creates one and sets `assigned_folder_id`.
3. Client submits a request.
4. The request is linked to the client's assigned folder.
5. The request starts as `pending`.

## 4. Governance and assignment
1. Admin reviews all client requests.
2. Admin sets or updates `due_date` when needed.
3. Admin assigns clients to production through `assigned_clients`.
4. Admin manages user-role changes when access needs change.

## 5. Production execution
1. Production reviews assigned-client requests only.
2. Frontend opens the production shell at `/production`, which routes the folder workspace into `/production/folders`.
3. Production opens an assigned folder and the workspace route updates to `/production/folders/:folderId` without replacing the surrounding shell.
4. Production works requests through operational status updates.
5. Production uploads files to the assigned client folder.
6. Upload activity is logged.

## 6. File access and delivery
1. Client can view, preview, and download files from the assigned folder only.
2. Agent can browse and download allowed files only.
3. Production can access files needed for assigned-client execution through the nested folder workspace and selected-folder file view.
4. Admin does not use the file portal directly by default.

## 7. Recycle bin
1. Production deletes a file.
2. Backend sets `last_deleted_at` and soft deletes the record.
3. File appears in recycle bin.
4. Production can restore it.
5. Scheduled purge later removes expired deleted files.

## Role ownership
- `admin`
  - client-to-production assignment
  - due dates
  - user-role changes
  - request oversight
- `production`
  - file uploads
  - assigned-client folders
  - assigned-client requests
  - operational request execution
- `agent`
  - browse/download allowed files only
- `client`
  - create requests
  - view own request history
  - access and download own assigned-folder files

## Foundations already present
- the backend already contains `client_requests` and `assigned_clients`
- the backend schema uses target-style keys such as `user_id`, `folder_id`, `file_id`, `folder_name`, and `file_name`
- backend request-management routes are now present for client history, production status handling, and admin due-date management
- UI and assignment-management implementation are still being aligned fully to this role model

## Guardrails
- Agents do not enter the request workflow.
- Clients do not set due dates.
- Admin does not use the file portal directly by default.
- Authorization must always be enforced by the backend.
