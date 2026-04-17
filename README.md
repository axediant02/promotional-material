# Promotional Materials Portal

A secure file delivery portal for production teams, agents, and approved clients.

This project is a split Laravel + Vue application built to centralize client file access, reduce manual file sharing, and enforce role-based visibility across internal and client-facing users.

## Overview

The Promotional Materials Portal is designed for teams that need a controlled way to upload, organize, preview, and deliver media files to clients.

Current system goals:
- give each approved client access only to their own assigned folder
- let agents browse files across client folders for operational work
- let production manage uploads, approvals, recycle-bin recovery, and activity tracking
- provide a cleaner, more secure alternative to scattered manual file sharing

## Current Features

Implemented today:
- client self-registration
- login and logout with Laravel Sanctum
- role-based access for `client`, `agent`, and `production`
- pending client approval flow
- automatic folder creation on client approval when needed
- folder listing, creation, detail view, and updates
- file upload, listing, detail view, update, preview, download, soft delete, and restore
- recycle bin with scheduled purge command
- client dashboard
- agent workspace
- production admin overview
- activity logs for upload, delete, and restore actions

Planned but not fully implemented yet:
- dedicated `admin` role
- client request workflow
- production-to-client assignment model
- due-date management for requests

## Roles

- `production`
  Full operational access in the current implementation, including approvals, uploads, recycle bin, and activity logs.
- `agent`
  Cross-client file visibility for browsing, previewing, and downloading accessible files.
- `client`
  Access only to the assigned folder and its files after approval.

Note:
Current `/admin` behavior is still production-operated admin behavior. A separate first-class `admin` role is planned, but not yet implemented.

## Tech Stack

Backend:
- Laravel 12
- PHP 8.2+
- Laravel Sanctum
- MySQL or MariaDB
- PHPUnit

Frontend:
- Vue 3
- Vue Router
- Pinia
- Axios
- Tailwind CSS
- Vite

## Repository Structure

```text
promotional-materials/
|- backend/    Laravel API
|- frontend/   Vue client
|- docs/       Internal project and architecture references
|- README.md   Repository overview
```

## How It Works

1. A client registers for access.
2. Production reviews and approves or rejects the account.
3. On approval, the client is linked to a folder if one is not already assigned.
4. Production uploads and manages files.
5. Agents can browse across client folders.
6. Clients can access only their own approved folder and files.
7. Deleted files move to the recycle bin and can later be restored or purged.

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 20+
- npm
- MySQL or MariaDB

### 1. Clone the project

```bash
git clone <your-repository-url>
cd promotional-materials
```

### 2. Set up the backend

```bash
cd backend
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

Backend API default local URL:

```text
http://127.0.0.1:8000/api
```

### 3. Set up the frontend

Open a second terminal:

```bash
cd frontend
npm install
copy .env.example .env
npm run dev
```

Frontend default local URL:

```text
http://localhost:5173
```

## Environment Notes

Backend environment expects values such as:
- database connection settings
- `FILESYSTEM_DISK`
- `FRONTEND_URL`

Frontend environment expects:
- `VITE_API_URL`

## Common Commands

Backend:

```bash
cd backend
php artisan route:list
php artisan test
php artisan migrate
php artisan db:seed
php artisan serve
```

Frontend:

```bash
cd frontend
npm run dev
npm run build
```

## Verification

When making changes:

- backend changes: run `php artisan test`
- frontend changes: run `npm run build`
- auth, file access, or permission changes: verify the full affected flow, not just one screen or endpoint

## Documentation

Useful project references:
- `docs/existing-features.md` for implemented behavior
- `docs/current-vs-planned.md` for target-state gaps
- `docs/api-reference.md` for backend endpoints
- `docs/frontend-routes.md` for current route behavior
- `docs/system-flow.md` for end-to-end flows

Implementation guides:
- `backend/AGENTS.md` for backend-specific working rules
- `frontend/AGENTS.md` for frontend-specific working rules
- `AGENTS.md` at the repo root for cross-project coordination

## Current Notes

- the backend currently uses UUID-based core models
- current file payloads still use fields such as `original_name`, `mime_type`, and `size`
- the folder schema still includes `parent_id`
- request workflow features are documented but not yet implemented

## License

This repository currently does not define a project-specific license in the root. Add one if you plan to distribute the code publicly.
