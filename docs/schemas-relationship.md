# Schemas And Relationships

This document separates the current implemented backend schema foundation from the still-incomplete target behavior across the full system.

## Current implemented backend schema

### `users`
- `user_id`
- `name`
- `email`
- `email_verified_at`
- `password`
- `role`
- `status`
- `assigned_folder_id`
- `remember_token`
- `created_at`
- `updated_at`

Current role enum values in backend schema:
- `admin`
- `production`
- `agent`
- `client`

Current status enum values:
- `pending`
- `approved`
- `rejected`

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
- The backend schema and models have moved to the target relationship structure.
- The rest of the system is still catching up in routes, UI, and documentation.
- Do not assume every schema relationship is already fully exposed through APIs or screens.
- The backend role enum and request/assignment tables are ahead of the currently implemented production-operated UI behavior.
