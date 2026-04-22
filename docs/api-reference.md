# API Reference

This document describes the currently implemented backend API in `backend/routes/api.php`.

## Base URL
- Local API base: `http://127.0.0.1:8000/api`

## Auth model
- Token auth uses Laravel Sanctum.
- Protected routes require `Authorization: Bearer <token>`.

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
- Purpose: create a client account
- Body:
  - `name`
  - `email`
  - `password`
  - `password_confirmation`
- Success:
  - creates a `client` user
  - does not create the folder yet
  - returns the created user payload

### `POST /auth/login`
- Purpose: login for registered users
- Body:
  - `email`
  - `password`
- Success:
  - returns a token and user payload
- Notes:
  - returns `422` for invalid credentials
  - returns `403` for `admin` users because dedicated live admin portal access is not enabled yet

## Protected auth routes

### `GET /auth/me`
- Purpose: fetch the authenticated user
- Includes:
  - assigned folder relation when present

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
  - client sees the assigned folder only
  - agent sees allowed folders/files for download work
  - production sees production-owned operational stats
  - admin should see governance-oriented stats once the dedicated surface is implemented
- Notes:
  - folder payloads now use backend naming such as `folder_id` and `folder_name`
  - file payloads are in a contract-transition period while frontend consumers catch up

## Folder routes

### `GET /folders`
- Purpose: list folders visible to the current user
- Query params:
  - `q` for folder-name search

### `POST /folders`
- Purpose: create a folder
- Access:
  - production only in the current codebase
- Body:
  - `folder_name`
  - `client_id`

### `GET /folders/{folder}`
- Purpose: fetch one folder and its files
- Access:
  - client can only access assigned folder
  - other authenticated roles follow the current backend authorization rules

### `PUT|PATCH /folders/{folder}`
- Purpose: update a folder
- Access:
  - production only
- Body may include:
  - `folder_name`
  - `client_id`

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
- Notes:
  - backend now categorizes files into `image`, `video`, or `pdf`

### `GET /files/{file}`
- Purpose: fetch one file record
- Notes:
  - backend records now persist `file_name`, `storage_disk`, `storage_path`, and `category`
  - compatibility fields may still appear while the frontend contract is being aligned

### `PUT|PATCH /files/{file}`
- Purpose: update a file record
- Access:
  - production only
- Body may include:
  - `folder_id`
  - `file_name`
  - `category`

### `DELETE /files/{file}`
- Purpose: soft delete a file and move it to recycle bin
- Access:
  - production only

### `GET /files/{file}/download`
- Purpose: download a stored file

### `GET /files/{file}/preview`
- Purpose: stream a previewable file response

## Request routes

### `POST /requests`
- Purpose: create a client request
- Access:
  - client
- Notes:
  - if the client has no assigned folder yet, request creation creates and assigns it first
  - the request is then stored against that folder with `pending` status

## Agreed Role Ownership

The role model this project is implementing is:
- `admin`
  - assigns clients to production
  - sets and updates request due dates
  - manages user-role changes
  - oversees request workflow
  - does not use the file portal directly by default
- `production`
  - uploads files
  - views assigned-client folders
  - views assigned-client requests
  - updates operational request status
- `agent`
  - browses and downloads allowed files only
  - does not participate in request management
- `client`
  - creates requests
  - views own requests
  - downloads files from own assigned folder only

## Recycle bin routes

### `GET /recycle-bin`
- Purpose: list soft-deleted files
- Access:
  - production only

### `POST /files/{file}/restore`
- Purpose: restore a soft-deleted file
- Access:
  - production only

## Legacy Admin-Prefixed Routes

Important:
- These routes exist in the current codebase, but their naming does not match the agreed role model.
- Treat them as legacy transition routes until dedicated `admin` and `production` route surfaces are implemented.

### `POST /admin/agents`
- Current codebase purpose: create an agent account
- Body:
  - `name`
  - `email`
  - `password`

### `GET /admin/activity-logs`
- Current codebase purpose: fetch recent activity logs

## Backend foundations present
- backend role enum now includes `admin`
- `client_requests` schema and model now exist in backend
- `assigned_clients` schema and model now exist in backend
- full request history, status-update, due-date, assignment-management, and role-management routes are still not complete
