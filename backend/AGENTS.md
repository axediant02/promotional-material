# AGENTS.md - Backend Guidelines & Standards
This document serves as the primary source of truth for all AI agents and developers working inside the backend application of the Promotional Materials Portal. Adherence to these rules is mandatory to keep the Laravel API secure, maintainable, and aligned with the current backend implementation.

## Project Overview
Project Name: Promotional Materials Portal Backend

Backend Purpose: A Laravel API responsible for authentication, authorization, approvals, dashboard payloads, folder and file lifecycle management, recycle-bin recovery, activity logging, and the backend data foundations for request handling and production assignment.

Backend Scope:
- client registration and login
- Sanctum token authentication
- role-based API protection
- dashboard aggregation
- folder CRUD
- file upload, listing, preview, download, update, and soft delete
- recycle-bin listing and restore
- pending-client approval flow
- agent account creation
- activity log retrieval
- backend schema and model foundations for:
  - `client_requests`
  - `assigned_clients`

Current Backend Truth:
- The backend is built with Laravel 12, Laravel Sanctum, PHP 8.2+, and PHPUnit.
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
- Support a future first-class `admin` role when it is intentionally implemented in live routes and UI.
- Complete request-management routes, assignment flows, and due-date workflow on top of the backend foundations already added.
- Keep current file portal behavior stable while the remaining planned backend features are completed.

Core Backend Rules:
- Backend authorization is the source of truth for all access control.
- Clients must only access their assigned folder and files.
- Production owns uploads, recycle-bin operations, client approval, and agent creation in the current implementation.
- Agents have broad file visibility but no request-workflow access in v1.
- API responses should stay consistent with the current `message` and `data` structure unless a coordinated contract update is intentional.
- When backend naming changes move toward the target schema, keep compatibility risks explicit and update consumers deliberately rather than assuming frontend parity.

## Coding Style & Best Practices
Magic Numbers And Strings:
- Avoid hardcoding role names, statuses, action names, storage path fragments, and retention periods inline when a model constant, config value, or dedicated constant would reduce drift.
- Reuse existing model constants such as the role and status constants on `User`.
- Example: keep the recycle-bin retention window centralized when extracting or reusing purge behavior.

Naming Conventions:
- Use clear business-oriented names such as `assigned_folder_id`, `last_deleted_at`, `pendingClients`, and `file_uploaded`.
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
- Use explicit `abort_unless(...)` or focused authorization helpers when the current codebase pattern calls for it.

Controller And Service Boundaries:
- Keep controllers thin and predictable.
- Use dedicated private or protected helpers for repeated authorization checks.
- Extract shared business actions into services when logic is reused or likely to grow.
- Preserve and extend `ActivityLogService` instead of duplicating logging logic across controllers.
- Introduce additional services when business workflows become large enough to justify them.

Database And Model Safety:
- Respect current live schema names unless a task explicitly includes migration work.
- Distinguish carefully between:
  - backend schema/model foundations now present
  - API or UI behavior not yet fully exposed
- Treat UUID handling as a first-class concern in queries, migrations, route-model binding, and token integration.
- Preserve soft-delete behavior for files and folders when touching lifecycle code.
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

Current backend flow:
Form Request -> Controller -> Service or Model -> JSON response

### Backend Structure
Use the current folder layout as the architectural baseline:
- `app/Http/Controllers/Api/Auth`: auth endpoints
- `app/Http/Controllers/Api/Client`: dashboard, folders, files, recycle bin
- `app/Http/Controllers/Api/Admin`: current production-admin endpoints
- `app/Http/Requests`: validation rules
- `app/Http/Middleware`: role enforcement
- `app/Models`: data models and relationships
- `app/Services`: shared business actions such as activity logging
- `app/Console/Commands`: operational commands such as purge jobs
- `routes/api.php`: API surface
- `database/migrations`: schema history

### Current API Responsibilities
Auth:
- register pending client accounts
- log approved users in
- return authenticated user payloads
- revoke current access tokens

Dashboard:
- aggregate visible folders, files, and pending-client counts

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

Production-Admin Endpoints:
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
- Do not treat `/api/admin/*` as a true separate admin role yet; it is currently production-admin behavior.
- Do not document the request module as fully live unless routes and UI are actually implemented.
- Do not move authorization responsibility into the frontend.
- Do not bypass the activity log when changing upload, delete, restore, or approval flows that are already logged.
- Do not assume the full request-management feature exists just because the schema and models now exist.

## Workflow
Clarification:
- Ask for clarification only when access rules, schema intent, or current-vs-planned behavior is ambiguous.
- If planned documentation conflicts with live backend code, call out the difference and implement against the current backend unless the task is an intentional migration.

Planning:
- Check `routes/api.php`, the relevant Form Request, controller, model, and migration before changing backend behavior.
- Use `docs/existing-features.md` for implemented truth and `docs/current-vs-planned.md` for target-state gaps.
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
- If schema or Sanctum behavior changes, verify UUID compatibility with `personal_access_tokens`.

Documentation:
- Update this file when backend architecture, route behavior, or authorization patterns change.
- Keep backend guidance aligned with the actual codebase, not only with planned future architecture.
- Document planned-only areas clearly so agents do not mistake them for implemented APIs.

Compounding Knowledge:
- Record repeated backend mistakes, auth issues, schema drift, and lifecycle lessons in the section below with a date entry.

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
Success in this backend is measured by correct authorization, stable file lifecycle behavior, clear API contracts, and code that reflects current system truth without blurring it with planned features. Build endpoints that are easy to reason about, hard to misuse, and explicit about role and access boundaries.
