# AGENTS.md - Frontend Guidelines & Standards
This document serves as the primary source of truth for all AI agents and developers working inside the frontend application of the Promotional Materials Portal. Adherence to these rules is mandatory to keep the Vue client consistent, maintainable, and aligned with the V1 system requirements and the current frontend implementation.

## Project Overview
Project Name: Promotional Materials Portal Frontend

Frontend Purpose: A Vue-based client application for authentication, role-based routing, dashboards, file visibility, request workflow screens, and production/admin-facing operational views.

Frontend Scope:
- login and registration screens
- role-based route access
- client dashboard
- agent workspace
- production workspace
- future admin workspace
- request forms and request-management screens
- shared layout and UI presentation
- API consumption through Axios services

System Requirements To Implement:
- support four roles in the V1 target model:
  - `admin`
  - `production`
  - `agent`
  - `client`
- allow clients to self-register, with the current local-testing flow assigning the client folder immediately
- restrict file-upload actions to `production` in V1
- allow both `admin` and `production` to view and manage client requests in V1
- prevent `agent` access to request-management screens in V1
- ensure clients can access only their assigned files and request views
- reflect that `admin` owns due dates and production assignment decisions

Current Frontend Truth:
- The frontend is built with Vue 3, Vue Router, Pinia, Axios, Tailwind CSS, and Vite.
- The codebase is JavaScript-first today, not TypeScript-first.
- The current implemented live roles reflected in routing are `production`, `agent`, and `client`.
- The `/admin` route is still the production workspace, not a true dedicated `admin` role.
- The router also now contains temporary dashboard scaffolding routes for `production`, `agent`, and `admin` at `/production`, `/agent-new`, and `/admin-new`.
- The auth store's current default-route behavior points `production` to `/production`, `agent` to `/agent-new`, `admin` to `/admin-new`, and `client` to `/client`.
- The frontend still contains some live contract usage tied to older backend-compatible fields such as `original_name`, `mime_type`, and `size`, even though the backend has moved toward the target schema naming.
- The client dashboard now includes live request submission UI backed by `POST /requests`.

Target Direction:
- Support a true dedicated `admin` role when the backend introduces it in live routes and permissions.
- Add request workflow screens that reflect the approved V1 behavior:
  - client submits requests
  - admin manages lifecycle and due dates
  - production works assigned-client requests
  - agent has no request module access
- Keep the file portal stable while the request-management and admin-role screens are completed.

Core Frontend Rules:
- The frontend must reflect backend authorization, not redefine it.
- Route guards are for UX and navigation control, not for real security.
- All API communication must go through shared service modules.
- Shared visual patterns should be reused before creating new UI structures.
- Keep current implementation truth separate from planned target-state behavior.
- When V1 role workflows are only partially implemented, label them clearly in docs and code discussions instead of pretending they are already live.

## Coding Style & Best Practices
Magic Numbers And Strings:
- Avoid hardcoded role names, route names, request statuses, request types, storage keys, endpoint fragments, and UI labels when a shared constant or helper would reduce drift.
- Centralize values that repeat across routes, stores, or services.
- Example: `const TOKEN_STORAGE_KEY = 'pm_token'`

Naming Conventions:
- Use clear, descriptive names tied to business meaning.
- Prefer names like `pendingClients`, `recentFiles`, `fetchDashboard`, `performLogin`, `requestStatusLabel`, and `defaultRoute`.
- Name pages, services, and components after the feature or workflow they own.

Vue Patterns:
- Use the existing `script setup` Composition API style.
- Keep pages focused on orchestration, data loading, and page composition.
- Keep shared presentational UI in reusable components.
- Keep feature-specific UI inside its owning feature folder unless it is reused broadly.
- Prefer computed state and simple reactive primitives over unnecessary abstraction.

API And State Management:
- Use `frontend/src/services/api.js` as the shared Axios entry point.
- Keep endpoint wrappers in `frontend/src/services`.
- Use Pinia stores for shared application state such as authentication.
- Do not call Axios directly from unrelated components when a service module should own the request.
- Keep auth token handling consistent with the current `pm_token` local storage flow unless intentionally refactoring it.

Routing And Access:
- Keep route metadata authoritative for frontend navigation behavior.
- Match role-based redirects with the auth store's `defaultRoute`.
- When route access changes, update both router guards and the consuming UI behavior.
- Do not create frontend routes for planned backend features unless the underlying API and permissions are available.
- When the V1 request workflow is introduced, enforce the target role expectations in navigation:
  - `client` can create and view own requests
  - `admin` can manage request lifecycle and assignment decisions
  - `production` can manage or work requests for assigned clients
  - `agent` has no request module access

Data Contracts:
- Respect current backend response shape:
  - `message`
  - `data`
  - optional `meta`
- Use current backend field names in the UI unless the backend contract changes.
- Do not rename live payload fields in the frontend just to match planned schema documents.
- When backend contract migration is in progress, update all affected screens, services, and docs together.

Styling:
- Reuse the current visual language: rounded panels, soft shadows, slate/orange palette, and Tailwind utilities.
- Favor consistency with `AppShell`, `StatsGrid`, `FolderList`, and `FileTable` before introducing a different visual pattern.
- Keep layout responsive for mobile and desktop.

Comments:
- Prefer self-documenting code.
- Add comments only for non-obvious route logic, auth bootstrap behavior, request-role constraints, or UI decisions driven by backend limitations.

## System Architecture
### Frontend Stack
Current stack:
- Vue 3
- Vue Router
- Pinia
- Axios
- Tailwind CSS
- Vite
- GitHub Actions for CI/CD workflow automation

Entry Points:
- `src/main.js` bootstraps Vue, Pinia, router, and global styles.
- `src/App.vue` renders the router view.
- `src/style.css` defines global styling and base visual direction.

### Frontend Structure
Use the current folder layout as the architectural baseline:
- `src/features`: feature-owned pages and UI
- `src/components`: shared layout and reusable UI
- `src/services`: API wrappers and request helpers
- `src/stores`: shared state
- `src/router`: route definitions and guards
- `src/utils`: generic helpers
- `src/assets`: static frontend assets

### Feature Responsibilities
Auth:
- login and registration pages
- auth store bootstrap and session flow
- guest-only and authenticated navigation behavior

Role Workspaces:
- `client` -> `/client`
- `agent` -> `/agent`
- `production` -> current `/admin`
- temporary scaffolding also exists for:
  - `production` -> `/production`
  - `agent` -> `/agent-new`
  - `admin` -> `/admin-new`
- `admin` -> future dedicated admin workspace when backend role separation is live

Shared UI:
- `AppShell` owns the common workspace frame and logout action
- `StatsGrid` renders summary cards
- `FolderList` renders visible folder summaries
- `FileTable` renders recent file listings

Request Workflow UI Goals:
- client request submission form
- client request history view
- admin request-management view
- production request-work view for assigned clients
- no request workspace for `agent` in V1

### API Integration
Frontend API rules:
- Use the shared Axios instance in `src/services/api.js`.
- Preserve bearer-token injection through the request interceptor.
- Read the API base URL from `VITE_API_URL`, with the existing local fallback only if intentionally preserved.
- Keep service modules thin and endpoint-focused.

Current service areas:
- `authService.js`
- `dashboardService.js`
- `approvalService.js`
- `activityLogService.js`
- `fileService.js`
- `folderService.js`
- `requestService.js` for in-progress request endpoint wrappers that still depend on future backend route exposure

Expected service growth for V1:
- assignment/admin service wrappers for `assigned_clients`
- dedicated role-aware request views when those APIs are truly available

### Frontend Boundaries
- Do not move permission or authorization logic into the frontend as the source of truth.
- Do not hand-edit `dist/`; it is build output.
- Do not build UI around request workflows as if they are already complete unless the underlying API and access rules are actually live.
- Treat `/admin` naming as current production-admin UI terminology until backend role separation is real.

## Workflow
Clarification:
- Ask for clarification only when route behavior, access expectations, request-role ownership, or API contracts are ambiguous.
- If current frontend behavior differs from planned documentation, document the difference and code against the live behavior unless the task is a migration.

Planning:
- Plan against actual frontend files first.
- Check the router, auth store, and service modules before changing navigation or access behavior.
- Use `docs/frontend-routes.md`, `docs/current-vs-planned.md`, `docs/request-workflow.md`, and `docs/roles-access.md` when route naming or target-state expectations are involved.

Implementation:
- Preserve the existing feature-based layout.
- Add new pages inside the owning feature folder.
- Add or update shared components only when reuse is real.
- Keep logout, auth bootstrap, and role redirect behavior consistent across screens.
- When adding API-driven UI, create or extend a service module instead of embedding raw requests in page files.
- Keep the UI aligned with the backend's current field names and role model.
- When request workflow screens are implemented, match the approved V1 behavior:
  - client can submit and view own requests
  - admin manages request lifecycle and due dates
  - production handles assigned-client requests
  - agent remains outside the request module

Testing And Verification:
- Run frontend verification from `frontend`:
  - `npm run build`
- If routing or auth behavior changes, verify:
  - guest-only redirects
  - authenticated redirects
  - role mismatch redirects
  - session bootstrap behavior after refresh
- If request workflow UI changes, verify:
  - client request submission path
  - request visibility per role
  - agent exclusion from request views
  - due-date display ownership
  - assignment-linked production views
- If API integration changes, verify the relevant service flow against the current backend contract.

Documentation:
- Update this file when frontend architecture, route behavior, role ownership, or service conventions change.
- Keep frontend guidance aligned with the actual app and the approved V1 requirements.
- If future frontend request-workflow screens are introduced, document them only when they are actually supported.

Compounding Knowledge:
- Record repeated frontend mistakes, route drift, auth edge cases, request-role UI drift, and contract lessons in the section below with a date entry.

## Compounding Knowledge
2026-04-17 - Frontend Is JavaScript-First: The app currently uses `.vue` and `.js` files. Do not write guidance that assumes an existing TypeScript setup unless the frontend is intentionally migrated.

2026-04-17 - Auth Bootstrap Depends On Stored Token: The router relies on the auth store bootstrapping from `pm_token` in local storage before enforcing authenticated route behavior.

2026-04-17 - Default Route Logic Mirrors Current Roles: The auth store currently redirects `production` to `/admin`, `agent` to `/agent`, and all other authenticated users to `/client`.

2026-04-17 - Production Still Owns Admin UI: The current `/admin` page and related services are production-facing operational tools, not a separate implemented `admin` role.

2026-04-17 - Shared Dashboard UI Is Reused Across Roles: Client, agent, and production pages all compose `AppShell`, `StatsGrid`, `FolderList`, and `FileTable`, then layer role-specific data on top.

2026-04-17 - Current File Payload Names Are Live Contracts: Frontend tables and cards still rely on backend fields like `original_name`, `mime_type`, folder relations, and `size`.

2026-04-21 - Client Request Submission Is Now Live: `frontend/src/services/requestService.js` and the client dashboard request UI now target the live `POST /requests` backend route.
2026-04-21 - Local Testing Registration Auto-Assigns Folders: The current frontend registration flow no longer waits for manual approval and should message immediate folder assignment instead.

2026-04-21 - Router Scaffolding Has Moved Ahead Of Live Role Support: `frontend/src/router/index.js` and `frontend/src/stores/auth.js` now include temporary `/production`, `/agent-new`, and `/admin-new` role dashboard routes and redirects, but backend live authorization and route support still center on `production`, `agent`, and `client`.

## Final Note
Success in this frontend is measured by clear navigation, consistent shared UI, faithful backend integration, request-role behavior that matches the approved V1 workflow, and code that makes role-based behavior obvious without duplicating backend authority.
