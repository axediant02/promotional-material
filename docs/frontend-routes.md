# Frontend Routes

This document describes the current Vue router configuration in `frontend/src/router/index.js`.

## Route table

| Path | Name | Access | Component | Notes |
|---|---|---|---|---|
| `/` | `landing` | guest only | `LandingPage.vue` | Public landing page |
| `/login` | `login` | guest only | `LoginPage.vue` | Login form |
| `/register` | `register` | guest only | `RegisterPage.vue` | Client registration form |
| `/client` | `client-dashboard` | `client` only | `ClientDashboardPage.vue` | Client dashboard |
| `/agent` | `agent-workspace` | `agent` only | `AgentWorkspacePage.vue` | Agent workspace |
| `/admin` | `admin-overview` | `production` only | `AdminOverviewPage.vue` | Legacy production-facing page kept in the router |
| `/production` | `production-dashboard` | `production` only | `ProductionDashboardPage.vue` | Production shell parent route |
| `/production/folders` | `production-folder-index` | `production` only | `ProductionFolderIndexPage.vue` | Assigned-folder browser inside the production shell |
| `/production/folders/:folderId` | `production-folder-detail` | `production` only | `ProductionFolderFilesPage.vue` | Selected-folder file view inside the production shell |
| `/agent-new` | `agent-dashboard` | `agent` only | `AgentDashboardPage.vue` | Current agent dashboard route |
| `/admin-new` | `admin-dashboard` | `admin` only | `AdminDashboardPage.vue` | Current admin governance dashboard |

## Guard behavior
- Auth-required routes redirect guests to `login`.
- Guest-only routes redirect authenticated users to their role default route.
- Role-locked routes redirect authenticated users with the wrong role to their default route.

## Default route logic
- `production` -> `production-folder-index` (`/production/folders`)
- `agent` -> `agent-dashboard` (`/agent-new`)
- `admin` -> `admin-dashboard` (`/admin-new`)
- any other authenticated user -> `client-dashboard` (`/client`)

## Production workspace routing
- `/production` is the production shell route.
- It redirects to `/production/folders`.
- The nested workspace swaps only the folder area between:
  - `/production/folders`
  - `/production/folders/:folderId`
- Query params preserve workspace state for:
  - `view`
  - `filter`
  - `sort`
  - `q`

## Current page coverage

### Public pages
- `LandingPage.vue`
- `LoginPage.vue`
- `RegisterPage.vue`

### Authenticated pages
- `ClientDashboardPage.vue`
- `AgentWorkspacePage.vue`
- `ProductionDashboardPage.vue`
- `ProductionFolderIndexPage.vue`
- `ProductionFolderFilesPage.vue`
- `AgentDashboardPage.vue`
- `AdminDashboardPage.vue`
- `AdminOverviewPage.vue`

## Current route-linked data usage

### Landing page
- public entry surface for guests

### Login page
- uses the auth store
- calls `POST /auth/login`

### Register page
- uses the auth store
- calls `POST /auth/register`

### Client dashboard
- calls `GET /dashboard`
- calls `GET /files`
- submits requests through `POST /requests`
- shows request history through the client request routes

### Agent workspace
- calls `GET /dashboard`
- supports browse/download file access only

### Production workspace
- calls `GET /dashboard`
- calls `GET /production/requests`
- uses nested folder routes for assigned-client file operations

### Admin dashboard
- calls `GET /dashboard`
- calls `GET /admin/requests`
- calls `PATCH /admin/requests/{clientRequest}` for due-date editing
- calls `GET /admin/assignments`
- calls `POST /admin/assignments`
- calls `DELETE /admin/assignments/{assignment}`
- calls `GET /admin/activity-logs`

## Notes
- `/admin-new` is the active admin route in the current frontend.
- `/admin` remains in the router, but it is configured for the `production` role and should be treated as a legacy compatibility page.
- The agreed role model in the frontend is:
  - `admin` for governance
  - `production` for execution
  - `agent` for browse/download access
  - `client` for requests and own-folder access
