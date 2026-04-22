# Frontend Routes

This document describes the current Vue frontend routing in `frontend/src/router/index.js`.

## Route table

| Path | Name | Access | Component | Notes |
|---|---|---|---|---|
| `/` | redirect | public | redirect to `/login` | Default entry path |
| `/login` | `login` | guest only | `LoginPage.vue` | Login form |
| `/register` | `register` | guest only | `RegisterPage.vue` | Client registration form |
| `/client` | `client-dashboard` | `client` only | `ClientDashboardPage.vue` | Client dashboard |
| `/agent` | `agent-workspace` | `agent` only | `AgentWorkspacePage.vue` | Agent dashboard |
| `/admin` | `admin-overview` | `production` only | `AdminOverviewPage.vue` | Production admin area |
| `/production` | `production-dashboard` | `production` only | `ProductionDashboardPage.vue` | Temporary production dashboard route |
| `/agent-new` | `agent-dashboard` | `agent` only | `AgentDashboardPage.vue` | Temporary agent dashboard route |
| `/admin-new` | `admin-dashboard` | `admin` only | `AdminDashboardPage.vue` | Temporary admin dashboard scaffold |

## Guard behavior
- If a route requires auth and there is no authenticated user, redirect to `login`.
- If a route is guest-only and a user is already logged in, redirect to the role default route.
- If a route requires a role and the user has a different role, redirect to the role default route.

## Default route logic
- `production` -> `production-dashboard` (`/production`)
- `agent` -> `agent-dashboard` (`/agent-new`)
- `admin` -> `admin-dashboard` (`/admin-new`)
- any other authenticated user -> `client-dashboard` (`/client`)

## Current frontend page coverage

### Auth pages
- `LoginPage.vue`
- `RegisterPage.vue`

### Role pages
- `ClientDashboardPage.vue`
- `AgentWorkspacePage.vue`
- `AdminOverviewPage.vue`
- `ProductionDashboardPage.vue`
- `AgentDashboardPage.vue`
- `AdminDashboardPage.vue`

## Current data dependencies

### Login page
- uses auth store
- calls `POST /auth/login`

### Register page
- uses auth store
- calls `POST /auth/register`
- successful registration creates and assigns the client folder

### Client dashboard
- calls `GET /dashboard`
- calls `GET /files` for the media grid
- request submission calls `POST /requests`
- requests use the client's existing assigned folder

### Agent workspace
- calls `GET /dashboard`

### Admin overview
- calls:
  - `GET /dashboard`
  - `GET /recycle-bin`
  - `GET /admin/activity-logs`

### Temporary dashboard scaffolding
- `/production`, `/agent-new`, and `/admin-new` currently exist in the router as temporary role-dashboard routes.
- The `admin` route scaffold does not mean the backend has live dedicated `admin` authorization yet.
- Treat these routes as frontend scaffolding until backend role separation and API support are actually implemented.

## Notes
- `/admin` is still the production-operated admin workspace.
- `/production`, `/agent-new`, and `/admin-new` are temporary role-dashboard routes ahead of full backend role separation.
