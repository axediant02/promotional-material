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

## Guard behavior
- If a route requires auth and there is no authenticated user, redirect to `login`.
- If a route is guest-only and a user is already logged in, redirect to the role default route.
- If a route requires a role and the user has a different role, redirect to the role default route.

## Default route logic
- `production` -> `/admin`
- `agent` -> `/agent`
- any other authenticated user -> `/client`

## Current frontend page coverage

### Auth pages
- `LoginPage.vue`
- `RegisterPage.vue`

### Role pages
- `ClientDashboardPage.vue`
- `AgentWorkspacePage.vue`
- `AdminOverviewPage.vue`

## Current data dependencies

### Login page
- uses auth store
- calls `POST /auth/login`

### Register page
- uses auth store
- calls `POST /auth/register`

### Client dashboard
- calls `GET /dashboard`

### Agent workspace
- calls `GET /dashboard`

### Admin overview
- calls:
  - `GET /dashboard`
  - `GET /admin/pending-clients`
  - `GET /recycle-bin`
  - `GET /admin/activity-logs`

## Planned frontend routes not yet implemented
- admin role-specific route separation
- request list screen
- request detail screen
- request creation form
- assigned-clients management screen
- due date management screen
