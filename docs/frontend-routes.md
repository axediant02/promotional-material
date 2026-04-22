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
| `/production` | `production-dashboard` | `production` only | `ProductionDashboardPage.vue` | Temporary production dashboard route |
| `/agent-new` | `agent-dashboard` | `agent` only | `AgentDashboardPage.vue` | Temporary agent dashboard route |
| `/admin-new` | `admin-dashboard` | `admin` only | `AdminDashboardPage.vue` | Temporary admin dashboard scaffold |
| `/admin` | `admin-overview` | legacy | `AdminOverviewPage.vue` | Legacy transition route that should not be treated as the final admin surface |

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
- successful registration creates the client account only

### Client dashboard
- calls `GET /dashboard`
- calls `GET /files` for the media grid
- request submission calls `POST /requests`
- the first request creates and assigns the client's folder when needed

### Agent workspace
- calls `GET /dashboard`
- agent file access is browse/download only

### Legacy admin overview
- calls:
  - `GET /dashboard`
  - `GET /recycle-bin`
  - `GET /admin/activity-logs`

### Temporary dashboard scaffolding
- `/production`, `/agent-new`, and `/admin-new` currently exist in the router as temporary role-dashboard routes.
- These routes are the current UI transition layer while the backend route surface is being aligned to the agreed role model.

## Notes
- The agreed role model is:
  - `admin` for assignments, due dates, and user-role administration
  - `production` for uploads and assigned-client execution
  - `agent` for browse/download-only operational access
  - `client` for own requests and own-folder downloads
- Any remaining legacy `/admin` route usage should be treated as transition drift, not final architecture.
