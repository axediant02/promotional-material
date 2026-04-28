---
project_name: 'promotional-materials'
user_name: 'Ian'
date: '2026-04-28'
sections_completed: ['technology_stack', 'language_rules', 'framework_rules', 'testing_rules', 'code_quality_rules', 'workflow_rules', 'critical_rules']
existing_patterns_found: 12
---

# Project Context for AI Agents

_This file contains critical rules and patterns that AI agents must follow when implementing code in this project. Focus on unobvious details that agents might otherwise miss._

---

## Technology Stack & Versions

### Backend
- **Framework:** Laravel 12.x
- **PHP:** ^8.2
- **Auth:** Laravel Sanctum
- **Realtime:** Laravel Reverb 1.10+
- **Broadcasting:** Pusher PHP Server
- **Testing:** PHPUnit 11.5.50
- **Linting:** Laravel Pint 1.24

### Frontend
- **Framework:** Vue 3.5.32
- **Router:** Vue Router 5.0.4
- **State:** Pinia 3.0.4
- **HTTP:** Axios 1.15.0
- **Broadcasting:** Laravel Echo 2.3.4, Pusher JS 8.5.0
- **Build:** Vite 5.4.19
- **CSS:** Tailwind CSS 3.4.17, PostCSS 8.5.10

### Shared
- **Database:** MySQL/MariaDB (UUID-first schema)
- **API:** REST with Laravel API routes

---

## Critical Implementation Rules

### Backend Authorization
- **Backend authorization is the source of truth.** Frontend guards are UX only.
- One client account maps to one assigned folder.
- Clients may access only their assigned folder and files.

### Role Ownership
| Role | Responsibilities |
|------|------------------|
| `admin` | Client assignment, due dates, user-role changes, request intake |
| `production` | File uploads, assigned-client execution, status updates |
| `agent` | Browse and download allowed files only |
| `client` | Create requests, download files from own folder only |

### Naming Conventions (Backend)
- Use target-style names: `user_id`, `folder_id`, `file_id`, `folder_name`, `file_name`, `category`
- UUIDs for core primary keys
- Response shape: `{ "message": "...", "data": {...} }`

### Frontend Conventions
- **JavaScript-first**, not TypeScript-first
- Use `<script setup>` for Vue components
- Routes under `/admin`, `/agent`, `/production`, `/client` (canonical)
- Compatibility redirects: `/admin-new`, `/agent-new` → canonical routes
- Bearer token stored as `pm_token` in localStorage

---

## Language-Specific Rules

### JavaScript/Vue Rules
- **JavaScript-first**, not TypeScript-first
- Use `<script setup>` composition API for Vue components
- Named exports from service modules (`export const api = ...`)
- Default exports for page components
- Axios interceptors for auth errors (401 → redirect to login)
- Try/catch in async operations

### PHP/Laravel Rules
- PSR-4 autoloading
- Use Form Requests for validation
- Keep controllers thin; extract logic to Services
- UUID compatibility in migrations, relations, and route model binding
- Sanctum token schema must stay UUID-compatible

---

## Framework-Specific Rules

### Laravel Rules
- Backend authorization is the source of truth
- Response shape: `{ "message": "...", "data": {...} }`
- Use target-style names: `user_id`, `folder_id`, `file_id`, `folder_name`, `file_name`, `category`
- Extend `ActivityLogService` instead of duplicating lifecycle logging
- Preserve soft-delete and recycle-bin behavior when touching file lifecycle

### Vue Router Rules
- Routes under `/admin`, `/agent`, `/production`, `/client` (canonical)
- Compatibility redirects: `/admin-new`, `/agent-new` → canonical routes
- Production shell route nested at `/production` with children for folders
- Route guards are UX only (backend auth is authoritative)
- Use `beforeEach` for auth bootstrap and role checks

### Pinia Store Rules
- Auth store manages `pm_token` in localStorage
- Bootstrap current user on auth-required routes
- Default route redirect based on user role

### Echo/Reverb Rules
- Private user channels for notifications
- Subscription lifecycle aligned with auth bootstrap and logout
- Channels: `private-user.{userId}` for admin, production, client

---

## Testing Rules

### Backend Testing
- PHPUnit for unit and feature tests
- Run: `cd backend && php artisan test`
- TDD rule: Keep newly written tests stable; adapt implementation to satisfy tests
- Don't weaken or rewrite a failing newly added test

### Frontend Testing
- Vite build verification
- Run: `cd frontend && npm run build`

### Cross-Stack Verification
- Backend changes: run `php artisan test`
- Frontend changes: run `npm run build`
- Cross-stack changes: run both

---

## Code Quality & Style Rules

### Code Organization

#### Backend (`backend/app/`)
```
Http/Controllers/Api/
  ├── Auth/          # register, login, logout, currentUser
  ├── Admin/         # admin-only operations
  ├── Client/        # client dashboard, requests
  ├── Production/    # production folder/file management
  └── NotificationController.php
Services/
  └── ActivityLogService.php  # preferred for lifecycle logging
```

#### Frontend (`frontend/src/`)
```
features/           # feature-owned pages and UI
components/        # shared reusable UI
services/          # API wrappers (api.js)
stores/            # shared state (auth.js)
router/            # route definitions and guards
```

### API Response Patterns
- Backend returns `{ "message": "...", "data": {...} }`
- Frontend services consume via Axios
- Auth token managed through `pm_token` in localStorage

---

## Development Workflow Rules

### File & Folder Structure

#### Backend Key Paths
- Routes: `routes/api.php`
- Models: `app/Models/`
- Migrations: `database/migrations/`
- Form Requests: `app/Http/Requests/`

#### Frontend Key Paths
- Router: `src/router/index.js`
- Auth Store: `src/stores/auth.js`
- API Service: `src/services/api.js`
- Feature Pages: `src/features/{role}-dashboard/pages/`

### AGENTS.md Hierarchy
1. Root `AGENTS.md` — cross-stack/shared rules
2. `backend/AGENTS.md` — backend implementation guide
3. `frontend/AGENTS.md` — frontend implementation guide
4. `docs/ui-ux-design-policy.md` — UI/UX design work
5. `docs/system-flow.md` — system flows
6. `docs/request-workflow.md` — request workflows

---

## Critical Don't-Miss Rules

### Anti-Patterns to Avoid
- **Don't bypass backend authorization** — frontend guards are UX only
- **Don't create multiple folders per client** — one client = one folder
- **Don't blur role boundaries** — agents stay outside request workflow
- **Don't use legacy route names** — use canonical `/admin`, `/agent`, `/production`, `/client`

### Edge Cases
- UUID compatibility in all database operations
- Token schema compatibility with Sanctum
- Echo subscription cleanup on logout
- Soft-delete cleanup via `php artisan files:purge-deleted`

### Security Rules
- Backend authorization is authoritative
- Clients can only access assigned folder
- Agents cannot manage requests
- Production can only work on assigned clients
