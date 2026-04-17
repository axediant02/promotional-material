# API Reference

This document describes the currently implemented backend API in `backend/routes/api.php`.

## Base URL
- Local API base: `http://127.0.0.1:8000/api`

## Auth model
- Token auth uses Laravel Sanctum.
- Protected routes require `Authorization: Bearer <token>`.
- Current implementation issues with Sanctum and UUID users are documented in [known-issues.md](./known-issues.md).

## Response shape
Most endpoints return:

```json
{
  "message": "Human-readable status",
  "data": {}
}
```

## Public auth routes

### `POST /auth/register`
- Purpose: create a pending client account
- Body:
  - `name`
  - `email`
  - `password`
  - `password_confirmation`
- Success:
  - creates a `client` user with `pending` status

### `POST /auth/login`
- Purpose: login for approved users
- Body:
  - `email`
  - `password`
- Success:
  - returns a token and user payload
- Notes:
  - returns `422` for invalid credentials
  - returns `403` when a client account is still pending approval

## Protected auth routes

### `GET /auth/me`
- Purpose: fetch the authenticated user
- Includes:
  - assigned folder relation

### `POST /auth/logout`
- Purpose: revoke the current access token

## Shared dashboard route

### `GET /dashboard`
- Purpose: fetch dashboard data for the current user
- Returns:
  - `user`
  - `stats`
  - `folders`
  - `recentFiles`
- Scope:
  - client sees assigned folder only
  - agent sees all accessible folders/files
  - production sees admin-oriented stats, including pending clients

## Folder routes

### `GET /folders`
- Purpose: list folders visible to the current user
- Query params:
  - `q` for name search

### `POST /folders`
- Purpose: create a folder
- Access:
  - production only
- Body:
  - `name`
  - `parent_id` nullable
  - `client_user_id` nullable

### `GET /folders/{folder}`
- Purpose: fetch one folder and its files/children
- Access:
  - client can only access assigned folder
  - other authenticated roles can access all folders in the current implementation

### `PUT|PATCH /folders/{folder}`
- Purpose: update a folder
- Access:
  - production only

## File routes

### `GET /files`
- Purpose: list files visible to the current user
- Query params:
  - `folder_id`
  - `q`

### `POST /files`
- Purpose: upload a file
- Access:
  - production only
- Body:
  - `folder_id`
  - `file` multipart upload

### `GET /files/{file}`
- Purpose: fetch one file record

### `PUT|PATCH /files/{file}`
- Purpose: update a file record
- Access:
  - production only

### `DELETE /files/{file}`
- Purpose: soft delete a file and move it to recycle bin
- Access:
  - production only

### `GET /files/{file}/download`
- Purpose: download a stored file

### `GET /files/{file}/preview`
- Purpose: stream a previewable file response

## Recycle bin routes

### `GET /recycle-bin`
- Purpose: list soft-deleted files
- Access:
  - production only

### `POST /files/{file}/restore`
- Purpose: restore a soft-deleted file
- Access:
  - production only

## Admin-prefixed routes

Important:
- These routes are currently protected by `role:production`.
- Planned system state introduces a separate `admin` role, but that role is not implemented in the current code yet.

### `GET /admin/pending-clients`
- Purpose: list pending client accounts

### `PATCH /admin/pending-clients/{user}`
- Purpose: approve or reject a client account
- Body:
  - `status`: `approved` or `rejected`

### `POST /admin/agents`
- Purpose: create an agent account
- Body:
  - `name`
  - `email`
  - `password`

### `GET /admin/activity-logs`
- Purpose: fetch recent activity logs

## Planned API areas not yet implemented
- `admin` role APIs
- `client_requests` CRUD
- `assigned_clients` management
- due date management
- request visibility for admin and production
