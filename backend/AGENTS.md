# AGENTS.md - Backend Guide

Primary backend guide for the Laravel API in `backend/`.

## Purpose
The backend owns authentication, authorization, file and folder lifecycle rules, request data, client assignment data, activity logging, and storage-backed delivery behavior.

## Live Backend Truth
- Stack:
  - Laravel 12
  - PHP 8.2+
  - Laravel Sanctum
  - MySQL or MariaDB
  - PHPUnit
- Live operational roles:
  - `production`
  - `agent`
  - `client`
- `admin` exists in schema direction, but live admin behavior is not fully separated yet.
- `/api/admin/*` is still guarded as production-admin behavior.
- UUIDs are used for core primary keys.
- Backend now uses target-style names such as:
  - `user_id`
  - `folder_id`
  - `file_id`
  - `folder_name`
  - `file_name`
  - `category`
- `client_requests` and `assigned_clients` tables/models exist.
- Registration creates the client account immediately.
- Registration also creates and assigns the client folder immediately.

## Core Rules
- Backend authorization is the source of truth.
- One client account maps to one assigned folder.
- Clients may access only their assigned folder and files.
- Production owns uploads in the current live flow.
- Agents may browse/download according to visibility rules but must not gain request-management access.
- Keep the current `message` + `data` response shape unless a coordinated contract change is intentional.
- Treat schema foundations as separate from fully live feature exposure.

## Code Rules
- Reuse model constants for roles, statuses, request types, and categories.
- Keep validation in Form Requests where practical.
- Keep controllers thin; extract shared logic into helpers or services as needed.
- Extend `ActivityLogService` instead of duplicating lifecycle logging.
- Be explicit about UUID compatibility in migrations, relations, route model binding, and Sanctum integration.
- Preserve soft-delete and recycle-bin behavior when touching file lifecycle code.

## Structure
- `app/Http/Controllers/Api/Auth`
- `app/Http/Controllers/Api/Client`
- `app/Http/Controllers/Api/Admin`
- `app/Http/Requests`
- `app/Models`
- `app/Services`
- `app/Console/Commands`
- `routes/api.php`
- `database/migrations`

## Current Feature Areas
- Auth:
  - register
  - login
  - `/auth/me`
  - logout
- Dashboard aggregation
- Folder list/create/show/update
- File list/upload/show/update/delete/restore/preview/download
- Recycle bin and purge command
- Agent creation
- Activity log listing
- Backend request and assignment foundations

## Planned Areas Still Incomplete
- True live `admin` role separation
- Full request-management route surface
- Full assignment-management workflow
- Final due-date ownership flow in live UI/API behavior

## Workflow
- Check `routes/api.php`, related Form Requests, controllers, models, and migrations before changing behavior.
- Use `docs/system-flow.md`, `docs/request-workflow.md`, and `docs/api-reference.md` as the current docs baseline.
- If docs and code disagree, code against the live backend unless the task is an intentional migration.
- Preserve current scoping rules for folders, files, previews, downloads, restore, and request ownership.

## Verification
- Run from `backend`:
  - `php artisan test`
- For auth changes, verify:
  - register
  - login
  - `/auth/me`
  - logout
- For access changes, verify:
  - production access
  - agent access
  - client assigned-folder scoping
  - preview/download authorization
  - recycle-bin restore flow
- For request/assignment changes, verify:
  - client request creation
  - production visibility rules
  - agent exclusion
  - due-date ownership
  - assignment linkage

## Compounding Knowledge
- 2026-04-17: `/api/admin/*` is still production-admin behavior.
- 2026-04-17: Core models use UUIDs, so auth and token schema compatibility need extra care.
- 2026-04-17: Sanctum token schema must stay UUID-compatible.
- 2026-04-20: Requests and assignments are present as backend foundations, not full live workflow.
- 2026-04-20: Naming has shifted toward `user_id`, `folder_id`, `file_id`, `folder_name`, `file_name`, `category`.
- 2026-04-20: Upload/delete/restore flows should continue using `ActivityLogService`.
- 2026-04-22: Registration currently creates both the client account and the assigned folder immediately.
- 2026-04-20: Recycle-bin behavior still depends on both soft deletes and storage cleanup.

## Success Criteria
- Correct authorization
- Stable API contracts
- Secure file delivery
- Clear current-vs-planned boundaries
