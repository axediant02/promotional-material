# AGENTS.md - Backend Guidelines & Standards
This document serves as the primary source of truth for all AI agents and developers working inside the backend application of the Promotional Materials Portal. Adherence to these rules is mandatory to keep the Laravel API secure, maintainable, and aligned with the current backend implementation.

## Project Overview
Project Name: Promotional Materials Portal Backend

Backend Purpose: A Laravel API responsible for authentication, authorization, approvals, dashboard payloads, folder and file lifecycle management, recycle-bin recovery, and activity logging.

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

Current Backend Truth:
- The backend is built with Laravel 12, Laravel Sanctum, PHP 8.2+, and PHPUnit.
- The current implemented roles are `production`, `agent`, and `client`.
- Admin-prefixed routes currently exist, but they are guarded by `role:production`.
- UUIDs are used for primary keys on core models such as `User`, `Folder`, `MediaFile`, and `ActivityLog`.
- The current live file schema still uses `original_name`, `mime_type`, `size`, `storage_disk`, and `storage_path`.
- The current folder schema still includes `parent_id`.

Target Direction:
- Support a future first-class `admin` role when it is intentionally implemented.
- Support future `client_requests`, `assigned_clients`, and due-date workflow only when the schema and routes actually exist.
- Keep current file portal behavior stable while planned backend features are still pending.

Core Backend Rules:
- Backend authorization is the source of truth for all access control.
- Clients must only access their assigned folder and files.
- Production owns uploads, recycle-bin operations, client approval, and agent creation in the current implementation.
- Agents have broad file visibility but no request-workflow access in v1.
- API responses should stay consistent with the current `message` and `data` structure.

## Coding Style & Best Practices
Magic Numbers And Strings:
- Avoid hardcoding role names, statuses, action names, storage path fragments, and retention periods inline when a model constant, config value, or dedicated constant would reduce drift.
- Reuse existing model constants such as the role and status constants on `User`.
- Example: keep the recycle-bin retention window centralized when extracting or reusing purge behavior.

Naming Conventions:
- Use clear business-oriented names such as `assigned_folder_id`, `last_deleted_at`, `pendingClients`, and `file_uploaded`.
- Match controllers, requests, commands, and services to the workflow they own.
- Keep model naming aligned with current database reality, including `MediaFile` mapping to the `files` table.

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
- Do not code against planned tables or columns as if they already exist.
- Treat UUID handling as a first-class concern in queries, migrations, route-model binding, and token integration.
- Preserve soft-delete behavior for files and folders when touching lifecycle code.

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

### Backend Boundaries
- Do not treat `/api/admin/*` as a true separate admin role yet; it is currently production-admin behavior.
- Do not build request-workflow endpoints against planned tables that do not exist.
- Do not move authorization responsibility into the frontend.
- Do not bypass the activity log when changing upload, delete, restore, or approval flows that are already logged.
- Do not assume nested folders are gone; `parent_id` still exists in the current backend.

## Workflow
Clarification:
- Ask for clarification only when access rules, schema intent, or current-vs-planned behavior is ambiguous.
- If planned documentation conflicts with live backend code, call out the difference and implement against the current backend unless the task is an intentional migration.

Planning:
- Check `routes/api.php`, the relevant Form Request, controller, model, and migration before changing backend behavior.
- Use `docs/existing-features.md` for implemented truth and `docs/current-vs-planned.md` for target-state gaps.
- Check `docs/known-issues.md` before assuming an auth or schema bug is application logic.

Implementation:
- Keep response payloads consistent with current API shape.
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

2026-04-17 - Users And Core Models Use UUIDs: `User`, `Folder`, `MediaFile`, and `ActivityLog` all use UUIDs, so auth, migrations, and foreign-key compatibility need extra care.

2026-04-17 - Sanctum Token Schema Needs UUID Compatibility: Login can fail after credential validation if `personal_access_tokens.tokenable_id` is not compatible with UUID-based users.

2026-04-17 - Current File Schema Uses Live Metadata Names: Backend file handling currently depends on `original_name`, `mime_type`, `size`, `storage_disk`, and `storage_path`, not the later planned `file_name` and `category`.

2026-04-17 - Folder Schema Still Supports Parent Relationships: The backend still stores `parent_id` and loads `children`, even though the planned MVP direction aims to simplify client-facing folders.

2026-04-17 - Activity Logging Is A Shared Backend Concern: Upload, delete, restore, and client-approval flows already depend on `ActivityLogService`, so new lifecycle behavior should extend that pattern instead of bypassing it.

2026-04-17 - Recycle Bin Behavior Depends On Both Soft Deletes And Storage Cleanup: The delete flow sets `last_deleted_at`, restore uses trashed records, and the purge command permanently deletes both the stored object and the database record after the retention window.

## Final Note
Success in this backend is measured by correct authorization, stable file lifecycle behavior, clear API contracts, and code that reflects current system truth without blurring it with planned features. Build endpoints that are easy to reason about, hard to misuse, and explicit about role and access boundaries.
