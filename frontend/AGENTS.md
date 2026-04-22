# AGENTS.md - Frontend Guide

Primary frontend guide for the Vue app in `frontend/`.

## Purpose
The frontend owns public entry screens, auth UX, routing, role-based dashboards, shared UI, and API consumption.

## Live Frontend Truth
- Stack:
  - Vue 3
  - Vue Router
  - Pinia
  - Axios
  - Tailwind CSS
  - Vite
- Codebase is JavaScript-first, not TypeScript-first.
- Working roles in the app:
  - `admin`
  - `production`
  - `agent`
  - `client`
- The router also contains temporary dashboard scaffolding:
  - `/production`
  - `/agent-new`
  - `/admin-new`
- Current default redirects in the auth store:
  - `production` -> `/production`
  - `agent` -> `/agent-new`
  - `admin` -> `/admin-new`
  - `client` -> `/client`
- Public entry currently redirects `/` to `/login`.
- Current client request UI targets the live `POST /requests` endpoint.
- Registration creates the client account immediately, and the first submitted request creates the assigned folder.
- Role ownership for ongoing UI work:
  - `admin` manages assignments, due dates, and user-role administration
  - `production` manages assigned-client work and uploads
  - `agent` can browse and download allowed files only
  - `client` can create requests and download own files only

## Core Rules
- Backend authorization is the source of truth.
- Route guards are UX only.
- All API calls should go through shared service modules.
- Keep current implementation truth separate from planned target behavior.
- Do not build UI as if unfinished backend features are complete.

## Structure
- `src/features` feature-owned pages and UI
- `src/components` shared reusable UI
- `src/services` API wrappers
- `src/stores` shared state
- `src/router` route definitions and guards
- `src/utils` generic helpers

## Implementation Rules
- Use `script setup`.
- Keep pages focused on orchestration and composition.
- Keep shared presentational UI in reusable components.
- Keep feature-specific UI inside its feature folder unless reuse is real.
- Use `src/services/api.js` as the shared Axios entry point.
- Keep bearer-token handling aligned with `pm_token` unless refactoring intentionally.
- Keep route metadata aligned with auth-store redirects.
- When contracts change, update UI, services, and docs together.

## Current Feature Areas
- Landing page
- Login and registration
- Client dashboard
- Agent workspace
- Admin and production dashboard scaffolding
- Temporary role dashboard scaffolding
- Request submission UI for clients

## Live Route Notes
- Public:
  - `/`
  - `/login`
  - `/register`
- Live role routes:
  - `/client`
  - `/agent`
  - `/admin-new`
  - `/production`
- Temporary scaffolding:
  - `/agent-new`
  - legacy `/admin`

## Styling Rules
- Reuse the current visual language first: rounded panels, soft shadows, slate/orange palette, Tailwind utilities.
- Preserve responsive behavior.
- Prefer consistent, intentional UI over one-off styling.

## Workflow
- Check the router, auth store, and relevant service modules before changing navigation or access behavior.
- Use `docs/frontend-routes.md`, `docs/system-flow.md`, and `docs/request-workflow.md` when route or onboarding intent is unclear.
- Preserve feature-based layout.
- Add new pages inside the owning feature folder.
- Add shared components only when reuse is real.
- Keep logout and auth bootstrap behavior consistent across public and authenticated screens.

## Verification
- Run from `frontend`:
  - `npm run build`
- If routing or auth changes, verify:
  - guest-only redirects
  - authenticated redirects
  - role mismatch redirects
  - session bootstrap after refresh
- If request UI changes, verify:
  - client request submission
  - role visibility
  - agent exclusion

## Compounding Knowledge
- 2026-04-17: Frontend is JavaScript-first.
- 2026-04-17: Router auth bootstrap depends on `pm_token` in local storage.
- 2026-04-17: Shared dashboard UI is reused across role pages.
- 2026-04-17: Some screens still rely on compatibility payload fields during contract migration.
- 2026-04-21: Client request submission now targets the live `POST /requests` route.
- 2026-04-22: Registration creates the client account first, and the first submitted request creates the assigned folder.
- 2026-04-21: Temporary `/production`, `/agent-new`, and `/admin-new` routes exist ahead of full backend role separation.
- 2026-04-22: Agreed UI role model is now admin governance, production execution, agent download-only operational access, and client own-folder request and download access.

## Success Criteria
- Clear navigation
- Faithful backend integration
- Consistent shared UI
- Accurate current-vs-planned boundaries
