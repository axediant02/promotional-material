# Promotional Materials Portal

Secure file delivery portal for production teams, agents, and clients.

## Stack
- `backend/` Laravel 12 API
- `frontend/` Vue 3 client

## Live Scope
- Auth with Sanctum
- Role-based routing and access
- Client file delivery
- Folder and file management
- Recycle-bin recovery
- Activity logging
- Client request creation
- Backend foundations for assignments and request management

## Live Roles
- `admin`
- `production`
- `agent`
- `client`

## Current Truth
- One client maps to one assigned folder.
- Registration creates the client account immediately.
- The client folder is created and assigned when the first request is submitted.
- Admin governs assignments, due dates, and user-role changes.
- Production uploads files and works assigned-client folders and requests.
- Agents can browse and download allowed files.
- Clients can create requests and access only their assigned folder and files.
- Backend foundations now exist for:
  - `client_requests`
  - `assigned_clients`
  - UUID-first naming such as `user_id`, `folder_id`, `file_id`, `folder_name`, `file_name`, `category`

## Planned But Incomplete
- Full request-management route surface
- Full assignment-management UI/API behavior
- Admin user-management and role-management UI/API behavior
- Cleanup of legacy route and UI naming that still blurs `admin` and `production`

## Repository Structure
```text
promotional-materials/
|- backend/
|- frontend/
|- docs/
```

## Local Setup

### Backend
```bash
cd backend
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

### Frontend
```bash
cd frontend
npm install
copy .env.example .env
npm run dev
```

## Verification
- Backend:
  - `cd backend && php artisan test`
- Frontend:
  - `cd frontend && npm run build`

## CI/CD
- [ci.yml](./.github/workflows/ci.yml)
  - runs backend tests and frontend build on PRs to `main` and pushes to `main`
- [cd.yml](./.github/workflows/cd.yml)
  - manual validation and packaging workflow

## Docs
- [docs/api-reference.md](./docs/api-reference.md)
- [docs/frontend-routes.md](./docs/frontend-routes.md)
- [docs/request-workflow.md](./docs/request-workflow.md)
- [docs/schemas-relationship.md](./docs/schemas-relationship.md)
- [docs/system-flow.md](./docs/system-flow.md)

Implementation guides:
- [AGENTS.md](./AGENTS.md)
- [backend/AGENTS.md](./backend/AGENTS.md)
- [frontend/AGENTS.md](./frontend/AGENTS.md)
