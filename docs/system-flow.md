# System Flow

This document describes the current onboarding, governance, request, and file-delivery flow across the portal.

## 1. Registration
1. A guest opens `/register`.
2. The user submits the registration form.
3. The backend creates a user with the `client` role.
4. The account can sign in immediately.
5. No folder is created during registration.

## 2. Login and role routing
1. A user submits login credentials.
2. The backend validates the account and returns a Sanctum token plus the user payload.
3. The frontend stores the token in local storage as `pm_token`.
4. The frontend routes the user by role:
   - `production` -> `/production/folders`
   - `agent` -> `/agent-new`
   - `admin` -> `/admin-new`
   - `client` -> `/client`
5. Guests opening `/` land on the public landing page.

## 3. Client request creation
1. A client signs in.
2. The client opens the dashboard and submits a request through `POST /requests`.
3. If the client does not have an assigned folder yet, the backend creates the folder and links it during this first request flow.
4. The request is stored against the client and the assigned folder.
5. New requests begin with `pending` status.

## 4. Admin governance
1. Admin opens `/admin-new`.
2. The dashboard loads governance data from:
   - `GET /dashboard`
   - `GET /admin/requests`
   - `GET /admin/activity-logs`
   - `GET /admin/assignments`
3. Admin reviews the request queue and identifies missing due dates or missing assignment coverage.
4. Admin sets or updates request due dates inline in the request queue through `PATCH /admin/requests/{clientRequest}`.
5. Admin creates or updates client-to-production assignments through `POST /admin/assignments`.
6. Admin can remove an assignment through `DELETE /admin/assignments/{assignment}`.
7. Admin can update user roles through `PATCH /admin/users/{user}`.

## 5. Production execution
1. Production opens `/production`.
2. The production shell redirects into `/production/folders`.
3. The nested workspace swaps between:
   - `/production/folders`
   - `/production/folders/:folderId`
4. Production sees requests for assigned clients through `GET /production/requests`.
5. Production updates operational request status through `PATCH /production/requests/{clientRequest}`.
6. Production uploads files into assigned client folders and manages recycle-bin recovery.

## 6. File access and delivery
1. Clients can view, preview, and download files from their assigned folder only.
2. Agents can browse and download files allowed by backend authorization.
3. Production can browse assigned-client folders and deliver files through the production workspace.
4. Admin governance is centered on requests, assignments, activity, and role oversight rather than direct file-portal operation.

## 7. Recycle bin
1. Production deletes a file.
2. The backend soft deletes the record and tracks deletion timing.
3. The file appears in the recycle bin list.
4. Production can restore the file.
5. Scheduled purge logic handles expired deleted files later.

## Role ownership
- `admin`
  - request oversight
  - request due dates
  - client-to-production assignments
  - user-role changes
- `production`
  - assigned-client requests
  - request status execution
  - assigned-client folders
  - file uploads and recovery
- `agent`
  - browse and download allowed files
- `client`
  - create requests
  - view own request history
  - access and download files from the assigned folder

## Guardrails
- Backend authorization is the source of truth.
- Agents do not enter request management.
- Clients do not set due dates.
- Production does not set admin due dates.
- Assignment ownership stays at the client level through `assigned_clients`.
