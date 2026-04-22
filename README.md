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
- Client request foundation and live submission path

## Live Roles
- `production`
- `agent`
- `client`

Note:
- `/admin` is still production-operated admin behavior.
- A first-class `admin` role is still planned, not fully live.

## Current Truth
- One client maps to one assigned folder.
- Local testing registration creates the client account and assigned folder immediately.
- Production manages uploads and current admin actions.
- Agents can browse and download across visible folders.
- Clients can access only their assigned folder and files.
- Backend foundations now exist for:
  - `client_requests`
  - `assigned_clients`
  - UUID-first naming such as `user_id`, `folder_id`, `file_id`, `folder_name`, `file_name`, `category`

## Planned But Incomplete
- True `admin` role separation
- Full request-management workflow
- Due-date management flow
- Full assignment-management UI/API behavior

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
- [docs/existing-features.md](./docs/existing-features.md)
- [docs/current-vs-planned.md](./docs/current-vs-planned.md)
- [docs/api-reference.md](./docs/api-reference.md)
- [docs/frontend-routes.md](./docs/frontend-routes.md)
- [docs/system-flow.md](./docs/system-flow.md)

Implementation guides:
- [AGENTS.md](./AGENTS.md)
- [backend/AGENTS.md](./backend/AGENTS.md)
- [frontend/AGENTS.md](./frontend/AGENTS.md)
