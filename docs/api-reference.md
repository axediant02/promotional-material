# API Reference

This document describes the current backend API in `backend/routes/api.php`.

## Base URL
- Local API base: `http://127.0.0.1:8000/api`

## Auth model
- Authentication uses Laravel Sanctum.
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
  - does not create a folder yet
  - returns the created user payload

### `POST /auth/login`
- Purpose: log in any registered user
- Body:
  - `email`
  - `password`
- Success:
  - returns a Sanctum token
  - returns the authenticated user payload
- Error:
  - returns `422` for invalid credentials

## Protected auth routes

### `GET /auth/currentUser`
- Purpose: fetch the authenticated user
- Returns:
  - `user`
- Includes:
  - `assignedFolder` when present

### `POST /auth/logout`
- Purpose: revoke the current access token

## Shared dashboard route

### `GET /dashboard`
- Purpose: fetch dashboard data for the authenticated user
- Returns:
  - `user`
  - `stats`
  - `folders`
  - `recentFiles`
- Scope:
  - client sees the assigned folder context
  - agent sees browse/download workspace context allowed by backend rules
  - production sees operational workspace data
  - admin uses the dashboard payload alongside dedicated admin governance endpoints

## Folder routes

### `GET /folders`
- Purpose: list folders visible to the authenticated user
- Query params:
  - `q`

### `POST /folders`
- Purpose: create a folder
- Access:
  - production only
- Body:
  - `folder_name`
  - `client_id`

### `GET /folders/{folder}`
- Purpose: fetch one folder and its files
- Access:
  - client is limited to the assigned folder
  - other roles follow backend authorization rules

### `PUT|PATCH /folders/{folder}`
- Purpose: update folder metadata
- Access:
  - production only
- Body may include:
  - `folder_name`
  - `client_id`

## File routes

### `GET /files`
- Purpose: list files visible to the authenticated user
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
- Purpose: update file metadata
- Access:
  - production only
- Body may include:
  - `folder_id`
  - `file_name`
  - `category`

### `DELETE /files/{file}`
- Purpose: soft delete a file
- Access:
  - production only

### `GET /files/{file}/download`
- Purpose: download a file

### `GET /files/{file}/preview`
- Purpose: preview a supported file

## Request routes

### `GET /requests`
- Purpose: fetch the authenticated client's request history
- Access:
  - client only
- Returns:
  - `requests`

### `POST /requests`
- Purpose: create a client request
- Access:
  - client only
- Body:
  - request creation fields used by the client request form
- Behavior:
  - creates and assigns the client folder if one does not exist yet
  - stores the request with `pending` status

### `GET /production/requests`
- Purpose: fetch requests for clients assigned to the authenticated production user
- Access:
  - production only
- Returns:
  - `requests`

### `PATCH /production/requests/{clientRequest}`
- Purpose: update operational request status
- Access:
  - production only
- Body:
  - `status`
- Allowed values:
  - `pending`
  - `in_progress`
  - `done`
- Notes:
  - `due_date` is prohibited on this route
  - production can update only requests for assigned clients

### `GET /admin/requests`
- Purpose: fetch all requests for admin governance
- Access:
  - admin only
- Returns:
  - `requests`

### `GET /admin/users`
- Purpose: fetch the complete backend-driven user list for admin governance
- Access:
  - admin only
- Returns:
  - `users`
- Notes:
  - each user record includes stable identifiers such as `user_id`
  - the admin users tab uses this route instead of deriving users from requests, assignments, or activity logs

### `PATCH /admin/requests/{clientRequest}`
- Purpose: set or update a request due date
- Access:
  - admin only
- Body:
  - `due_date`
- Notes:
  - `status` is prohibited on this route
  - the admin dashboard uses this route for inline due-date editing

## Admin governance routes

### `GET /admin/assignments`
- Purpose: fetch current client-to-production assignments and production user options
- Access:
  - admin only
- Returns:
  - `assignments`
  - `production_users`

### `POST /admin/assignments`
- Purpose: create or update the assignment for a client
- Access:
  - admin only
- Body:
  - `client_id`
  - `production_id`
  - `status`
- Notes:
  - the selected client must already have at least one request
  - returns `201` for a new assignment and `200` for an update

### `DELETE /admin/assignments/{assignment}`
- Purpose: remove a client assignment
- Access:
  - admin only

### `PATCH /admin/users/{user}`
- Purpose: update a user's role
- Access:
  - admin only
- Body:
  - `role`
- Notes:
  - the acting admin cannot change their own role

## Activity and agent routes

### `GET /admin/activity-logs`
- Purpose: fetch recent activity logs
- Access:
  - authenticated users can reach the route in the current code
- Returns:
  - `logs`

### `POST /admin/agents`
- Purpose: create an agent account
- Body:
  - `name`
  - `email`
  - `password`
- Returns:
  - `agent`

## Recycle bin routes

### `GET /recycle-bin`
- Purpose: list soft-deleted files
- Access:
  - production only

### `POST /files/{file}/restore`
- Purpose: restore a soft-deleted file
- Access:
  - production only

## Role ownership
- `admin`
  - request oversight
  - request due dates
  - client assignments
  - user-role changes
- `production`
  - assigned-client requests
  - operational status updates
  - file uploads
- `agent`
  - browse and download only
- `client`
  - request creation
  - request history
  - own-folder downloads
