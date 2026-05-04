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
  - Laravel Echo
  - Tailwind CSS
  - Vite
- Codebase is JavaScript-first, not TypeScript-first.
- Working roles in the app:
  - `admin`
  - `production`
  - `agent`
  - `client`
- Canonical role routes are:
  - `/admin`
  - `/agent`
  - `/production`
  - `/client`
- Compatibility redirects remain for:
  - `/admin-new`
  - `/agent-new`
- The router also contains the nested production shell:
  - `/production` shell with nested production folder workspace routes
- Current default redirects in the auth store:
  - `production` -> `/production/folders`
  - `agent` -> `/agent`
  - `admin` -> `/admin`
  - `client` -> `/client`
- Public entry at `/` shows the landing page for guests.
- Current client request UI targets the live `POST /requests` endpoint.
- In-app notifications are shown inside the admin, production, and client dashboards.
- Registration creates the client account immediately, and the first submitted request creates the assigned folder.
- Assignment chat is shown inside the client and production dashboards for active assignment threads.
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
- Keep Echo subscription lifecycle aligned with auth bootstrap, logout, and user switching.
- When adding or refactoring frontend features or functions, keep components and composables focused, reuse shared UI and logic where it is genuinely repeated, separate data access from presentation, and avoid speculative abstractions that make the code harder to follow without clear reuse or complexity reduction.
- When contracts change, update UI, services, and docs together.

## Current Feature Areas
- Landing page
- Login and registration
- Client dashboard
- Agent dashboard
- Admin management dashboard
- Production dashboard shell with nested folder browser/detail workspace
- Request submission UI for clients
- Realtime notification panels for admin, production, and client dashboards
- Assignment chat widget for client and production dashboards

## Live Route Notes
- Public:
  - `/`
  - `/login`
  - `/register`
- Live role routes:
  - `/client`
  - `/agent`
  - `/admin`
  - `/production`
  - `/production/folders`
  - `/production/folders/:folderId`
- Compatibility redirects:
  - `/agent-new`
  - `/admin-new`

## Styling Rules
- Reuse the current visual language first: rounded panels, soft shadows, slate/orange palette, Tailwind utilities.
- Preserve responsive behavior.
- Prefer consistent, intentional UI over one-off styling.
- For UI/UX design, redesign, polish, or art-direction prompts, read and apply `docs/ui-ux-design-policy.md` before editing the interface.
- Enforce that policy's anti-generic rules during implementation, especially card layering, border contrast, icon usage, color discipline, radius consistency, motion, and tokenized styling.

## Workflow
- Check the router, auth store, and relevant service modules before changing navigation or access behavior.
- Use `docs/frontend-routes.md`, `docs/system-flow.md`, and `docs/request-workflow.md` when route or onboarding intent is unclear.
- Use `docs/ui-ux-design-policy.md` whenever the request is about UI design, UX design, dashboard polish, layout refreshes, visual redesigns, or component styling.
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
- If assignment chat UI changes, verify:
  - dashboard widget rendering
  - unread count updates
  - message send/read flows
  - realtime subscription teardown on logout
- If notification UI changes, verify:
  - dashboard panel rendering
  - unread count updates
  - mark-as-read flows
  - realtime subscription teardown on logout

## Compounding Knowledge
- 2026-04-17: Frontend is JavaScript-first.
- 2026-04-17: Router auth bootstrap depends on `pm_token` in local storage.
- 2026-04-17: Shared dashboard UI is reused across role pages.
- 2026-04-17: Some screens still rely on compatibility payload fields during contract migration.
- 2026-04-21: Client request submission now targets the live `POST /requests` route.
- 2026-04-22: Registration creates the client account first, and the first submitted request creates the assigned folder.
- 2026-04-21: The production shell route remains nested at `/production`, while `/agent-new` and `/admin-new` are compatibility redirects to canonical role routes.
- 2026-04-22: Agreed UI role model is now admin management, production execution, agent download-only operational access, and client own-folder request and download access.
- 2026-04-23: The production shell now keeps sidebar/topbar stable while nested folder routes swap only the folder workspace section.
- 2026-04-24: `/admin` and `/agent` are the canonical frontend entry routes reflected in the live router and auth-store defaults.
- 2026-04-24: Admin, production, and client dashboards now include realtime in-app notification panels powered by Echo subscriptions to private user channels.
- 2026-05-04: Assignment chat is now a live dashboard feature for client and production users and should be treated as part of the current UI, not future work.

## Success Criteria
- Clear navigation
- Faithful backend integration
- Consistent shared UI
- Accurate current-vs-planned boundaries
