# Development Setup

This document summarizes the repo structure, stack, environment expectations, and useful commands for working in this project.

## Repo structure
- `backend`
  - Laravel API
  - Sanctum auth
  - business logic
  - storage integration
- `frontend`
  - Vue 3 app
  - Axios API access
  - Tailwind styling

## Backend stack
- PHP 8.2+
- Composer
- Laravel 12
- Laravel Sanctum
- MySQL/MariaDB
- PHPUnit

## Frontend stack
- Node.js 20+
- npm
- Vue 3
- Axios
- Pinia
- Vue Router
- Tailwind CSS
- Vite

## Common backend commands
Run from `backend`:

```bash
php artisan route:list
php artisan test
php artisan migrate
php artisan db:seed
php artisan serve
```

## Seeded demo data
- `php artisan db:seed` now calls:
  - `UserSeeder`
  - `FolderSeeder`
  - `MediaFileSeeder`
  - `AssignedClientSeeder`
  - `ClientRequestSeeder`
- Current seeded sample users include:
  - `admin@example.com`
  - `production@example.com`
  - `agent@example.com`
  - `client1@example.com`
  - `client2@example.com`
  - `pending@example.com`
- The current seeders provide demo folders, media records, assignment links, and request records for local development data, even though the live request-management routes are still incomplete.

## Common frontend commands
Run from `frontend`:

```bash
npm install
npm run dev
npm run build
```

## Environment notes
- Backend `.env` currently expects:
  - MySQL
  - `FILESYSTEM_DISK`
  - `FRONTEND_URL`
- Frontend `.env` expects:
  - `VITE_API_URL`

## Verification expectations
- Backend changes:
  - run `php artisan test`
- Frontend changes:
  - run `npm run build`
- If auth, file access, request workflow, or permissions are touched:
  - verify the full affected flow

## Documentation usage
- Use [existing-features.md](./existing-features.md) for implemented truth.
- Use [current-vs-planned.md](./current-vs-planned.md) when the intended system differs from the current code.
- Use [known-issues.md](./known-issues.md) before debugging auth and schema-related failures.
