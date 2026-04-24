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
- Working roles:
  - `admin`
  - `production`
  - `agent`
  - `client`
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
- The first client request creates and assigns the client's folder.
- Role ownership:
  - `admin` manages client assignment, due dates, and user roles
  - `production` uploads files and executes work for assigned clients
  - `agent` can browse and download allowed files only
  - `client` creates requests and downloads files from the assigned folder only

## Core Rules
- Backend authorization is the source of truth.
- One client account maps to one assigned folder.
- Clients may access only their assigned folder and files.
- Admin must not be treated as a general file-portal operator by default.
- Production owns uploads and assigned-client request execution.
- Agents may browse/download according to visibility rules but must not gain request-management access.
- Keep the current `message` + `data` response shape unless a coordinated contract change is intentional.
- Treat schema foundations as separate from fully live feature exposure.
- For TDD work, do not weaken or rewrite a failing newly added test just to make it pass; fix the backend behavior, fixtures, or setup instead.

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
  - `/auth/currentUser`
  - logout
- Dashboard aggregation
- Folder list/create/show/update
- File list/upload/show/update/delete/restore/preview/download
- Recycle bin and purge command
- Agent creation
- Activity log listing
- Client request creation and request history
- Production request listing for assigned clients
- Production request status updates
- Admin request listing and due-date updates
- Backend assignment foundations

## Planned Areas Still Incomplete
- Full assignment-management workflow
- End-to-end assignment workflow polish across governance, execution, and docs
- Compatibility cleanup for legacy route references that previously blurred `admin` and `production`

## Workflow
- Check `routes/api.php`, related Form Requests, controllers, models, and migrations before changing behavior.
- Use `docs/system-flow.md`, `docs/request-workflow.md`, and `docs/api-reference.md` as the current docs baseline.
- If docs and code disagree, code against the live backend unless the task is an intentional migration.
- Preserve role boundaries for uploads, downloads, request visibility, due-date ownership, and assignment ownership.
- When writing tests first, treat the initial approved test as the acceptance target and adapt implementation to satisfy it unless the requirement itself is corrected explicitly.

## Verification
- Run from `backend`:
  - `php artisan test`
- For auth changes, verify:
  - register
  - login
  - `/auth/currentUser`
  - logout
- For access changes, verify:
  - production access
  - agent access
  - client assigned-folder scoping
  - preview/download authorization
  - recycle-bin restore flow
- For request/assignment changes, verify:
  - client request creation
  - admin visibility and due-date rules
  - production visibility rules for assigned clients
  - agent exclusion
  - client download scoping
  - agent download access
  - assignment linkage

## Compounding Knowledge
- 2026-04-17: Core models use UUIDs, so auth and token schema compatibility need extra care.
- 2026-04-17: Sanctum token schema must stay UUID-compatible.
- 2026-04-20: Requests and assignments are present as backend foundations, not full live workflow.
- 2026-04-20: Naming has shifted toward `user_id`, `folder_id`, `file_id`, `folder_name`, `file_name`, `category`.
- 2026-04-20: Upload/delete/restore flows should continue using `ActivityLogService`.
- 2026-04-22: Registration creates the client account first, and the first client request creates and assigns the folder.
- 2026-04-20: Recycle-bin behavior still depends on both soft deletes and storage cleanup.
- 2026-04-22: Admin owns assignment, due dates, and role changes. Production owns uploads and assigned-client execution. Agents and clients can download files, but agents stay outside the request workflow.
- 2026-04-23: Backend TDD work keeps newly written failing tests fixed as acceptance criteria; implementation must move to the test, not the other way around.
- 2026-04-24: Admin user listing and role update routes are part of the live backend surface; remaining incompleteness is centered on broader assignment workflow fit-and-finish rather than missing admin role-management endpoints.

## Success Criteria
- Correct authorization
- Stable API contracts
- Secure file delivery
- Clear current-vs-planned boundaries
