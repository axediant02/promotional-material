# AGENTS.md - Backend Guidelines & Standards
This document serves as the primary source of truth for all AI agents and developers working inside the backend application of the Promotional Materials Portal. Adherence to these rules is mandatory to keep the Laravel API secure, maintainable, and aligned with the V1 system requirements and the current backend implementation.

## Project Overview
Project Name: Promotional Materials Portal Backend

Backend Purpose: A Laravel API responsible for authentication, authorization, client approval, folder and file lifecycle management, request handling, production assignment, activity logging, and the core business rules that enforce secure role-based access.

Backend Scope:
- client self-registration and login
- Sanctum token authentication
- role-based API protection
- dashboard aggregation
- folder ownership and access control
- file upload, listing, preview, download, update, and soft delete
- recycle-bin listing and restore
- pending-client approval flow
- client request data management
- production-to-client assignment management
- activity log retrieval

System Requirements To Implement:
- support four roles in the V1 target model:
  - `admin`
  - `production`
  - `agent`
  - `client`
- require client self-registration to stay pending until approved
- restrict file uploads to `production` in V1
- allow both `admin` and `production` to view and manage client requests in V1
- prevent `agent` access to the request-management module in V1
- enforce one-client-one-folder ownership
- enforce client-level production assignment through `assigned_clients`
- keep due-date ownership with `admin`, not clients

Current Backend Truth:
- The backend is built with Laravel 12, Laravel Sanctum, PHP 8.2+, MySQL or MariaDB, and PHPUnit.
- The current implemented live routes and operational behavior still center on `production`, `agent`, and `client`.
- Admin-prefixed routes currently exist, but they are guarded by `role:production`.
- UUIDs are used for primary keys on core models.
- The backend schema and models now use target-style keys and names such as:
  - `user_id`
  - `folder_id`
  - `file_id`
  - `folder_name`
  - `file_name`
  - `category`
- The backend folder model is now the simplified one-client-one-folder shape:
  - `client_id`
  - `created_by`
  - no `slug`
  - no `parent_id`
- File records now persist category-driven metadata instead of the older `mime_type` and `size` contract in the database layer.
- `client_requests` and `assigned_clients` tables and models now exist in the backend codebase.

Target Direction:
- Move from production-operated admin behavior to a first-class `admin` role in live routes and permissions.
- Complete request-management routes and assignment workflows so they match the approved V1 proposal.
- Keep the current file portal stable while the remaining request and admin-role work is completed.

Core Backend Rules:
- Backend authorization is the source of truth for all access control.
- One client account maps to one assigned folder.
- Clients must be approved before they can fully use the portal.
- Clients must only access their assigned folder and files.
- Production owns file uploads in V1.
- Admin defines `due_date`; clients do not.
- Both `admin` and `production` should be able to view and manage client requests in the target V1 behavior.
- Agents may browse and download files according to their visibility rules, but they must not be given request-management access in V1.
- API responses should stay consistent with the current `message` and `data` structure unless a coordinated contract update is intentional.
- When backend naming changes move toward the target schema, keep compatibility risks explicit and update consumers deliberately rather than assuming frontend parity.

## Coding Style & Best Practices
Magic Numbers And Strings:
- Avoid hardcoding role names, statuses, request types, category values, action names, storage path fragments, and retention periods inline when a model constant, config value, or dedicated constant would reduce drift.
- Reuse existing model constants such as the role and status constants on `User`.
- Centralize enum-like values such as:
  - `pending`, `approved`, `rejected`
  - `new_asset`, `update_asset`
  - `pending`, `in_progress`, `done`
  - `image`, `video`, `pdf`

Naming Conventions:
- Use clear business-oriented names such as `assigned_folder_id`, `last_deleted_at`, `pendingClients`, `file_uploaded`, and `assignedClients`.
- Match controllers, requests, commands, and services to the workflow they own.
- Follow the new schema naming where the backend has already adopted it:
  - `user_id`
  - `folder_id`
  - `file_id`
  - `folder_name`
  - `file_name`
  - `category`

Validation And Authorization:
- Put request validation in Form Request classes whenever possible.
- Keep controllers responsible for orchestration, not validation sprawl.
- Enforce permissions in the backend even if the frontend already hides the action.
- Use explicit authorization for:
  - approval flows
  - file visibility
  - request visibility
  - due-date ownership
  - client-to-production assignment changes

Controller And Service Boundaries:
- Keep controllers thin and predictable.
- Use dedicated private or protected helpers for repeated authorization checks.
- Extract shared business actions into services when logic is reused or likely to grow.
- Preserve and extend `ActivityLogService` instead of duplicating logging logic across controllers.
- Introduce dedicated services when request management, assignment logic, or approval flow becomes large enough to justify them.

Database And Model Safety:
- Respect the approved V1 schema direction unless a task explicitly includes migration work.
- Distinguish carefully between:
  - backend schema/model foundations now present
  - API or UI behavior not yet fully exposed
- Treat UUID handling as a first-class concern in queries, migrations, route-model binding, and token integration.
- Preserve soft-delete behavior for files, folders, and request records when lifecycle code is touched.
- Remember that the create-migration files now describe the target-style schema directly. Existing local databases may need a fresh rebuild to match the new structure exactly.

Comments:
- Prefer self-documenting code.
- Add comments only for non-obvious authorization rules, schema drift, UUID edge cases, or file storage behavior that would otherwise be easy to misread.

## System Architecture
### Backend Stack
Current stack:
- Laravel 12
- PHP 8.2+
- Laravel Sanctum
- MySQL or MariaDB
- PHPUnit
- GitHub Actions for CI/CD workflow automation

Current backend flow:
Form Request -> Controller -> Service or Model -> JSON response

### Backend Structure
Use the current folder layout as the architectural baseline:
- `app/Http/Controllers/Api/Auth`: auth endpoints
- `app/Http/Controllers/Api/Client`: dashboard, folders, files, recycle bin
- `app/Http/Controllers/Api/Admin`: current production-admin endpoints and future admin-owned request workflow
- `app/Http/Requests`: validation rules
- `app/Http/Middleware`: role enforcement
- `app/Models`: data models and relationships
- `app/Services`: shared business actions such as activity logging
- `app/Console/Commands`: operational commands such as purge jobs
- `routes/api.php`: API surface
- `database/migrations`: schema history

### Required Data Model
Main tables:
- `users`
- `folders`
- `files`
- `client_requests`
- `assigned_clients`

Required core fields:
- `users`
  - `user_id`
  - `name`
  - `email`
  - `password`
  - `role`
  - `status`
  - `assigned_folder_id`
- `folders`
  - `folder_id`
  - `folder_name`
  - `client_id`
  - `created_by`
- `files`
  - `file_id`
  - `folder_id`
  - `uploaded_by`
  - `file_name`
  - `storage_disk`
  - `storage_path`
  - `category`
  - `last_deleted_at`
- `client_requests`
  - `request_id`
  - `client_id`
  - `folder_id`
  - `title`
  - `description`
  - `request_type`
  - `status`
  - `due_date`
- `assigned_clients`
  - `id`
  - `production_id`
  - `client_id`
  - `status`

Allowed values:
- `users.role`
  - `admin`
  - `production`
  - `agent`
  - `client`
- `users.status`
  - `pending`
  - `approved`
  - `rejected`
- `files.category`
  - `image`
  - `video`
  - `pdf`
- `client_requests.request_type`
  - `new_asset`
  - `update_asset`
- `client_requests.status`
  - `pending`
  - `in_progress`
  - `done`
- `assigned_clients.status`
  - `pending`
  - `in_progress`
  - `done`

### Current API Responsibilities
Auth:
- register pending client accounts
- log approved users in
- return authenticated user payloads
- revoke current access tokens

Dashboard:
- aggregate visible folders, files, request-relevant counts, and pending-client counts

Folders:
- list accessible folders
- create folders
- show folder details
- update folders

Files:
- list accessible files
- upload production-managed files
- show file records
- update file metadata
- soft delete files
- preview files
- download files
- derive preview and download behavior from stored category and path metadata

Recycle Bin:
- list deleted files for production
- restore deleted files
- purge expired deleted files through the command layer

Approval And Admin Flow:
- list pending clients
- approve or reject client accounts
- auto-create assigned folders on approval when needed
- create agent accounts
- list activity logs

Backend Foundations Present But Not Fully Exposed:
- `client_requests` schema and model
- `assigned_clients` schema and model
- target-style folder and file naming
- `admin` in the role enum

### Backend Boundaries
- Do not treat `/api/admin/*` as a true separate admin role yet; it is currently production-admin behavior in the live code.
- Do not document the request module as fully live unless routes and UI are actually implemented.
- Do not move authorization responsibility into the frontend.
- Do not bypass the activity log when changing upload, delete, restore, approval, or future request lifecycle flows that are already logged or should be logged.
- Do not assume the full request-management feature exists just because the schema and models now exist.

## Workflow
Clarification:
- Ask for clarification only when access rules, request ownership, due-date responsibility, or current-vs-planned behavior is ambiguous.
- If planned documentation conflicts with live backend code, call out the difference and implement against the current backend unless the task is an intentional migration.

Planning:
- Check `routes/api.php`, the relevant Form Request, controller, model, and migration before changing backend behavior.
- Use `docs/existing-features.md` for implemented truth and `docs/current-vs-planned.md` for target-state gaps.
- Check `docs/request-workflow.md`, `docs/system-flow.md`, and `docs/roles-access.md` when request ownership or role behavior is involved.
- Check `docs/known-issues.md` before assuming an auth or schema bug is application logic.

Implementation:
- Keep response payloads consistent with current API shape unless the contract change is intentional and coordinated.
- Preserve role checks and client scoping rules when touching folders, files, previews, or downloads.
- Keep login behavior aligned with client approval status checks.
- Preserve automatic assigned-folder creation when approving a client without a folder.
- Keep the recycle-bin flow consistent:
  - soft delete sets `last_deleted_at`
  - restore uses trashed records
  - purge removes expired storage objects and force-deletes records
- Implement request-management behavior so it matches the approved V1 ownership model:
  - client submits requests
  - admin reviews and manages lifecycle
  - admin owns due dates
  - production works requests for assigned clients
  - agents stay outside the request module
- Prefer extending current backend patterns over introducing a second competing pattern.

Testing And Verification:
- Run backend verification from `backend`:
  - `php artisan test`
- If auth behavior changes, verify:
  - registration
  - login
  - pending client rejection path
  - `/auth/me`
  - logout
- If file or folder access changes, verify:
  - production access
  - agent access
  - client assigned-folder scoping
  - preview and download authorization
  - recycle-bin restore flow
- If request or assignment behavior changes, verify:
  - client request creation rules
  - admin request visibility
  - production request visibility for assigned clients
  - agent exclusion from request-management access
  - due-date ownership
  - assignment linkage behavior
- If schema or Sanctum behavior changes, verify UUID compatibility with `personal_access_tokens`.

Documentation:
- Update this file when backend architecture, route behavior, schema expectations, or authorization patterns change.
- Keep backend guidance aligned with the actual codebase and the approved V1 requirements.
- Document planned-only areas clearly so agents do not mistake them for implemented APIs.

Compounding Knowledge:
- Record repeated backend mistakes, auth issues, schema drift, request ownership lessons, and lifecycle lessons in the section below with a date entry.

## Compounding Knowledge
2026-04-17 - Production Currently Owns Admin Routes: `/api/admin/*` is guarded by `role:production`, so admin-prefixed endpoints are still production-admin behavior in the live backend.

2026-04-17 - Users And Core Models Use UUIDs: Core models use UUIDs, so auth, migrations, and foreign-key compatibility need extra care.

2026-04-17 - Sanctum Token Schema Needs UUID Compatibility: Login can fail after credential validation if `personal_access_tokens.tokenable_id` is not compatible with UUID-based users.

2026-04-20 - Request And Assignment Foundations Are Now Present: `client_requests` and `assigned_clients` now exist as backend tables and models, but the full request-management routes and UI are still not complete.

2026-04-20 - Backend Naming Has Shifted Toward The Target Schema: The backend now uses target-style naming such as `user_id`, `folder_id`, `file_id`, `folder_name`, `file_name`, and `category`. Treat older docs or consumers that still expect `original_name`, `mime_type`, `size`, `slug`, or `parent_id` as migration drift to be resolved carefully.

2026-04-20 - Activity Logging Remains A Shared Backend Concern: Upload, delete, restore, and client-approval flows still depend on `ActivityLogService`, so new lifecycle behavior should extend that pattern instead of bypassing it.

2026-04-20 - Recycle Bin Behavior Still Depends On Both Soft Deletes And Storage Cleanup: The delete flow sets `last_deleted_at`, restore uses trashed records, and the purge command permanently deletes both the stored object and the database record after the retention window.

2026-04-20 - Schema Migration Strategy Now Matters More: Because the original create migrations were updated to the new schema shape, a previously migrated local database can drift from the codebase until it is rebuilt or freshly migrated.

## Final Note
Success in this backend is measured by correct authorization, secure file delivery, stable request ownership rules, clear API contracts, and code that reflects both the approved V1 requirements and the current live transition state without blurring them together.
