# AGENTS.md - System Guidelines & Standards
This document serves as the root coordination guide for all AI agents and developers working on the Promotional Materials Portal project. Use this file for project-wide rules, shared system intent, and cross-folder coordination. For implementation work inside a specific application folder, use that folder's `AGENTS.md` as the primary source of truth.

## Project Overview
Project Name: Promotional Materials Portal

System Purpose: A secure, responsive file delivery portal for internal production teams, agents, and approved clients.

Applications:
- `backend/` - Laravel API
- `frontend/` - Vue client

Primary Users:
- `production`
- `agent`
- `client`

Planned Future Role:
- `admin`

Current Truth:
- The live system currently implements `production`, `agent`, and `client`.
- Current `/admin` frontend behavior and `/api/admin/*` backend routes are still production-operated admin behavior.
- The system is focused on file delivery, folder access, approvals, recycle-bin recovery, and activity logging.
- The backend now contains schema and model foundations for `client_requests`, `assigned_clients`, and the target folder/file naming model.
- The backend schema now reflects one-client-one-folder ownership more directly through `assigned_folder_id`, `client_id`, and custom UUID primary keys such as `user_id`, `folder_id`, and `file_id`.

Target Direction:
- Introduce a first-class `admin` role.
- Complete the request management module and assignment workflows across routes and UI.
- Keep the current file portal stable while evolving toward the planned workflow.

Core Business Rules:
- One client account maps to one assigned folder.
- In the current local-testing implementation, client registration auto-assigns a folder immediately.
- Production handles uploads, folder management, approvals, recycle-bin actions, and operational oversight in the current implementation.
- Agents can browse and download across client folders for operational use.
- Clients can access only their own assigned folder and files.
- Deleted files stay recoverable through the recycle bin before final purge.
- Request workflow foundations now exist in backend schema and models, but the full feature is not yet fully live across the stack.

## Coding Style & Best Practices
Source Of Truth Discipline:
- Use this root file for project-wide rules and shared business constraints.
- Use `backend/AGENTS.md` as the primary guide for backend implementation details.
- Use `frontend/AGENTS.md` as the primary guide for frontend implementation details.
- Do not let the root file drift into a duplicate of the folder-specific guides.

Naming And Shared Terminology:
- Keep shared business language consistent across the repo.
- Use the same role names, status names, and workflow terms across backend code, frontend code, docs, and tests.
- If a shared contract changes, update both affected applications and the relevant documentation.

Current Vs Planned Discipline:
- Distinguish clearly between implemented behavior and planned target-state behavior.
- Do not code against planned roles, tables, fields, or screens as if they already exist.
- When a task intentionally moves planned behavior into implementation, update the folder-specific `AGENTS.md` files and supporting docs.
- If only the backend schema/model layer changed, document that as "backend foundation implemented" rather than "full feature live."

Authorization And Security:
- Backend authorization is the source of truth.
- Frontend route guards and UI restrictions are UX helpers only.
- Shared features such as login, folder access, preview, download, restore, and approval flows must stay aligned across both applications.

Comments:
- Prefer self-documenting code.
- Add comments only when shared business rules, current-vs-planned drift, or cross-application constraints would otherwise be easy to misread.

## System Architecture
### Application Boundaries
Backend:
- lives in `backend/`
- owns authentication, authorization, approvals, file and folder lifecycle rules, recycle-bin behavior, activity logging, storage integration, and the request/assignment data model
- primary implementation guide: `backend/AGENTS.md`

Frontend:
- lives in `frontend/`
- owns routing, auth bootstrap UX, dashboards, shared UI, and API consumption
- primary implementation guide: `frontend/AGENTS.md`

### Folder-Specific Agent Guides
Use the folder-specific guides as the first reference when your work is scoped to that application:
1. `backend/AGENTS.md` for Laravel API work
2. `frontend/AGENTS.md` for Vue client work

Use the root guide first when:
- the task affects both applications
- the task changes shared workflow or business rules
- the task changes current-vs-planned project guidance
- the task requires coordinating API contract changes across the stack

### Documentation Source Of Truth
Use project documents in this order:
1. `AGENTS.md` at the repo root for cross-project coordination.
2. `backend/AGENTS.md` for backend implementation rules.
3. `frontend/AGENTS.md` for frontend implementation rules.
4. `docs/existing-features.md` for implemented truth.
5. `docs/current-vs-planned.md` for target-state gaps.
6. `docs/api-reference.md`, `docs/frontend-routes.md`, `docs/system-flow.md`, and `docs/schemas-relationship.md` for implementation planning and debugging.
7. `docs/roles-access.md`, `docs/request-workflow.md`, and `docs/known-issues.md` for permission, workflow, and auth/schema edge cases.

## Workflow
Clarification:
- Ask for clarification only when requirements conflict, docs disagree, or a change could alter access control or cross-application contracts in a risky way.
- If current behavior and planned behavior differ, call out both explicitly before changing the system.

Planning:
- Determine scope first: `backend`, `frontend`, or cross-stack.
- If the task is backend-only, follow `backend/AGENTS.md` as the implementation guide.
- If the task is frontend-only, follow `frontend/AGENTS.md` as the implementation guide.
- If the task is cross-stack, start from this root file and then use both folder-specific guides.
- Treat planned features as target-state guidance unless the task is explicitly to build them.

Implementation:
- Keep backend and frontend behavior aligned around the same business rules.
- When API contracts change, update the backend implementation, frontend consumer, and relevant docs together.
- Preserve production-admin behavior unless the task explicitly introduces the admin-role split.
- Preserve one-client-one-folder access control unless a deliberate requirements change says otherwise.
- Keep file upload, delete, restore, preview, and download rules aligned across the stack.
- Keep the current local-testing onboarding flow aligned across backend and frontend: registration creates the client account and assigns a folder immediately.

Testing:
- For backend changes, run verification from `backend`:
  - `php artisan test`
- For frontend changes, run verification from `frontend`:
  - `npm run build`
- For cross-stack changes, run both verifications when relevant.
- If auth, approvals, permissions, files, or storage behavior changes, verify the full affected flow rather than a single endpoint or screen.

Documentation:
- Update the folder-specific `AGENTS.md` when the implementation rules for that folder change.
- Update the root `AGENTS.md` when shared system rules, application boundaries, or cross-stack workflow guidance changes.
- Keep documentation aligned with the real codebase, not only with planned architecture.
- Clearly label anything that is target-state only.

Compounding Knowledge:
- Record project-wide mistakes, coordination lessons, and shared system drift in the section below with a date entry.
- Put application-specific lessons in the relevant folder-specific `AGENTS.md` whenever possible.

## Compounding Knowledge
2026-04-17 - Folder-Specific Agent Guides Now Lead Implementation: `backend/AGENTS.md` and `frontend/AGENTS.md` are the primary references for work inside their respective folders, while the root `AGENTS.md` is now the cross-project coordination guide.

2026-04-17 - Production Is Acting Admin Across The Stack: Current `/admin` frontend behavior and `/api/admin/*` backend routes are still production-admin behavior until a true `admin` role is implemented.

2026-04-20 - Request And Assignment Foundations Now Exist In Backend: `client_requests` and `assigned_clients` are no longer planned-only schema ideas. The backend now includes table and model foundations, but full API and UI workflows are still incomplete.

2026-04-20 - Shared Contracts Are In Transition: The backend has moved toward target naming such as `user_id`, `folder_id`, `file_id`, `folder_name`, `file_name`, and `category`. Treat this as an active contract transition and keep backend, frontend, and docs aligned when continuing the migration.

2026-04-20 - Current Docs Must Distinguish Schema Readiness From Product Readiness: The presence of backend tables, models, and relationships for requests and assignments does not mean the request workflow is fully exposed in routes, screens, or role handling yet.

## Final Note
Success in this project is measured by secure file delivery, clear role boundaries, clean coordination between frontend and backend, and documentation that makes it obvious which guide governs which part of the system. Use the root guide for shared direction and the folder-specific guides for implementation truth.
