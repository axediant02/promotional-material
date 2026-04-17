# AGENTS.md - Frontend Guidelines & Standards
This document serves as the primary source of truth for all AI agents and developers working inside the frontend application of the Promotional Materials Portal. Adherence to these rules is mandatory to keep the Vue client consistent, maintainable, and aligned with the current system behavior.

## Project Overview
Project Name: Promotional Materials Portal Frontend

Frontend Purpose: A Vue-based client application for authentication, role-based routing, dashboards, file visibility, and production-facing operational views.

Frontend Scope:
- login and registration screens
- role-based route access
- client dashboard
- agent workspace
- production admin overview
- shared layout and UI presentation
- API consumption through Axios services

Current Frontend Truth:
- The frontend is built with Vue 3, Vue Router, Pinia, Axios, Tailwind CSS, and Vite.
- The codebase is JavaScript-first today, not TypeScript-first.
- The current implemented roles reflected in routing are `production`, `agent`, and `client`.
- The `/admin` route is still the production workspace, not a true dedicated `admin` role.
- The frontend consumes current backend schema names such as `original_name`, `mime_type`, and `size`.

Target Direction:
- Stay aligned with the planned backend evolution without pretending future features already exist.
- Support a future dedicated `admin` role when the backend truly introduces it.
- Support future request workflow screens only when the backend APIs and access rules are actually implemented.

Core Frontend Rules:
- The frontend must reflect backend authorization, not redefine it.
- Route guards are for UX and navigation control, not for real security.
- All API communication must go through shared service modules.
- Shared visual patterns should be reused before creating new UI structures.
- Keep current implementation truth separate from planned target-state behavior.

## Coding Style & Best Practices
Magic Numbers And Strings:
- Avoid hardcoded role names, route names, storage keys, endpoint fragments, and UI labels when a shared constant or helper would reduce drift.
- Centralize values that repeat across routes, stores, or services.
- Example: `const TOKEN_STORAGE_KEY = 'pm_token'`

Naming Conventions:
- Use clear, descriptive names tied to business meaning.
- Prefer names like `pendingClients`, `recentFiles`, `fetchDashboard`, `performLogin`, and `defaultRoute`.
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

Data Contracts:
- Respect current backend response shape:
  - `message`
  - `data`
  - optional `meta`
- Use current backend field names in the UI unless the backend contract changes.
- Do not rename live payload fields in the frontend just to match planned schema documents.

Styling:
- Reuse the current visual language: rounded panels, soft shadows, slate/orange palette, and Tailwind utilities.
- Favor consistency with `AppShell`, `StatsGrid`, `FolderList`, and `FileTable` before introducing a different visual pattern.
- Keep layout responsive for mobile and desktop.

Comments:
- Prefer self-documenting code.
- Add comments only for non-obvious route logic, auth bootstrap behavior, or UI decisions driven by backend constraints.

## System Architecture
### Frontend Stack
Current stack:
- Vue 3
- Vue Router
- Pinia
- Axios
- Tailwind CSS
- Vite

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
- `production` -> `/admin`

Shared UI:
- `AppShell` owns the common workspace frame and logout action
- `StatsGrid` renders summary cards
- `FolderList` renders visible folder summaries
- `FileTable` renders recent file listings

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

### Frontend Boundaries
- Do not move permission or authorization logic into the frontend as the source of truth.
- Do not hand-edit `dist/`; it is build output.
- Do not build UI around planned request workflows as if they already exist.
- Treat `/admin` naming as current production-admin UI terminology until backend role separation is real.

## Workflow
Clarification:
- Ask for clarification only when route behavior, access expectations, or API contracts are ambiguous.
- If current frontend behavior differs from planned documentation, document the difference and code against the live behavior unless the task is a migration.

Planning:
- Plan against actual frontend files first.
- Check the router, auth store, and service modules before changing navigation or access behavior.
- Use `docs/frontend-routes.md` and `docs/current-vs-planned.md` when route naming or target-state expectations are involved.

Implementation:
- Preserve the existing feature-based layout.
- Add new pages inside the owning feature folder.
- Add or update shared components only when reuse is real.
- Keep logout, auth bootstrap, and role redirect behavior consistent across screens.
- When adding API-driven UI, create or extend a service module instead of embedding raw requests in page files.
- Keep the UI aligned with the backend's current field names and role model.

Testing And Verification:
- Run frontend verification from `frontend`:
  - `npm run build`
- If routing or auth behavior changes, verify:
  - guest-only redirects
  - authenticated redirects
  - role mismatch redirects
  - session bootstrap behavior after refresh
- If API integration changes, verify the relevant service flow against the current backend contract.

Documentation:
- Update this file when frontend architecture, route behavior, or service conventions change.
- Keep frontend guidance aligned with the real app, not only planned backend ambitions.
- If future frontend request-workflow screens are introduced, document them only when they are actually supported.

Compounding Knowledge:
- Record repeated frontend mistakes, route drift, auth edge cases, and UI contract lessons in the section below with a date entry.

## Compounding Knowledge
2026-04-17 - Frontend Is JavaScript-First: The app currently uses `.vue` and `.js` files. Do not write guidance that assumes an existing TypeScript setup unless the frontend is intentionally migrated.

2026-04-17 - Auth Bootstrap Depends On Stored Token: The router relies on the auth store bootstrapping from `pm_token` in local storage before enforcing authenticated route behavior.

2026-04-17 - Default Route Logic Mirrors Current Roles: The auth store currently redirects `production` to `/admin`, `agent` to `/agent`, and all other authenticated users to `/client`.

2026-04-17 - Production Still Owns Admin UI: The current `/admin` page and related services are production-facing operational tools, not a separate implemented `admin` role.

2026-04-17 - Shared Dashboard UI Is Reused Across Roles: Client, agent, and production pages all compose `AppShell`, `StatsGrid`, `FolderList`, and `FileTable`, then layer role-specific data on top.

2026-04-17 - Current File Payload Names Are Live Contracts: Frontend tables and cards still rely on backend fields like `original_name`, `mime_type`, folder relations, and `size`.

## Final Note
Success in this frontend is measured by clear navigation, consistent shared UI, faithful backend integration, and code that makes role-based behavior obvious without duplicating backend authority. Build screens that are easy to extend, easy to debug, and honest about what the system supports today.
