# Schemas And Relationships

This document separates the current implemented schema from the planned target schema discussed for the system.

## Current implemented schema

### `users`
- `id`
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

### `folders`
- `id`
- `name`
- `slug`
- `parent_id`
- `client_user_id`
- `created_by`
- `created_at`
- `updated_at`
- `deleted_at`

### `files`
- `id`
- `folder_id`
- `uploaded_by`
- `original_name`
- `storage_disk`
- `storage_path`
- `mime_type`
- `size`
- `last_deleted_at`
- `created_at`
- `updated_at`
- `deleted_at`

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

## Planned target schema

### `users`
- `id`
- `name`
- `email`
- `password`
- `role`
- `status`
- `assigned_folder_id`
- `created_at`
- `updated_at`

Planned role values:
- `admin`
- `production`
- `agent`
- `client`

Planned status values:
- `pending`
- `approved`
- `rejected`

### `folders`
- `id`
- `folder_name`
- `client_id`
- `created_by`
- `created_at`
- `updated_at`
- `deleted_at`

### `files`
- `id`
- `folder_id`
- `uploaded_by`
- `file_name`
- `storage_disk`
- `storage_path`
- `category`
- `created_at`
- `updated_at`
- `deleted_at`
- `last_deleted_at`

Planned category values:
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

Planned request types:
- `new_asset`
- `update_asset`

Planned request statuses:
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

## Current implemented relationships
- a `User` belongs to one assigned folder through `assigned_folder_id`
- a `Folder` belongs to one client user through `client_user_id`
- a `Folder` may belong to a parent folder through `parent_id`
- a `Folder` has many files
- a `MediaFile` belongs to one folder
- a `MediaFile` belongs to one uploader
- an `ActivityLog` belongs to one user

## Planned target relationships
- one client has one assigned folder
- one folder belongs to one client
- one folder has many files
- one file belongs to one folder
- one client has many requests
- one production user can be linked to many clients through `assigned_clients`
- one client should have one active production ownership record at a time

## Important schema drift to remember
- Current code still uses nested folders through `parent_id`.
- Current code still stores `original_name`, `mime_type`, and `size` instead of the later planned `file_name` and `category`.
- Current code does not yet include `client_requests` or `assigned_clients`.
