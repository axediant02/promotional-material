# Schemas And Relationships

This document describes the current schema foundation and the core relationships used by the implemented flow.

## Current implemented backend schema

### `users`
- `user_id`
- `name`
- `email`
- `email_verified_at`
- `password`
- `role`
- `assigned_folder_id`
- `remember_token`
- `created_at`
- `updated_at`

Current role enum values in backend schema:
- `admin`
- `production`
- `agent`
- `client`

### `folders`
- `folder_id`
- `folder_name`
- `client_id`
- `created_by`
- `created_at`
- `updated_at`
- `deleted_at`

### `files`
- `file_id`
- `folder_id`
- `uploaded_by`
- `file_name`
- `storage_disk`
- `storage_path`
- `category`
- `last_deleted_at`
- `created_at`
- `updated_at`
- `deleted_at`

Current category values:
- `image`
- `video`
- `pdf`

### `client_requests`
- `request_id`
- `client_id`
- `folder_id`
- `title`
- `description`
- `request_type`
- `status`
- `due_date`
- `created_at`
- `updated_at`
- `deleted_at`

Current request types in backend schema:
- `new_asset`
- `update_asset`

Current request statuses in backend schema:
- `pending`
- `in_progress`
- `done`

### `assigned_clients`
- `id`
- `production_id`
- `client_id`
- `status`
- `created_at`
- `updated_at`

Current assignment statuses in backend schema:
- `pending`
- `in_progress`
- `done`

### `activity_logs`
- `id`
- `user_id`
- `action`
- `subject_type`
- `subject_id`
- `description`
- `metadata`
- `created_at`
- `updated_at`

### support tables
- `personal_access_tokens`
- `password_reset_tokens`
- `sessions`
- `cache`
- `jobs`

## Current implemented backend relationships
- a `User` belongs to one assigned folder through `assigned_folder_id`
- a client `User` has one owned client folder through `folder`
- a `Folder` belongs to one client through `client_id`
- a `Folder` belongs to one creator through `created_by`
- a `Folder` has many files
- a `Folder` has many client requests
- a `MediaFile` belongs to one folder
- a `MediaFile` belongs to one uploader
- a `ClientRequest` belongs to one client
- a `ClientRequest` belongs to one folder
- an `AssignedClient` belongs to one production user
- an `AssignedClient` belongs to one client
- an `ActivityLog` belongs to one user

## Target full-system relationships
- one client has one assigned folder
- one folder belongs to one client
- one folder has many files
- one file belongs to one folder
- one client has many requests
- one production user can be linked to many clients through `assigned_clients`
- one client should have one active production ownership record at a time

## Important implementation notes
- registration creates the client account first.
- the first client request creates and assigns the folder.
- request linkage should stay aligned with [system-flow.md](./system-flow.md) and [request-workflow.md](./request-workflow.md).
- Do not assume every schema relationship is already fully exposed through APIs or screens.
