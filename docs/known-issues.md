# Known Issues

This document tracks current known technical issues or mismatches that are useful during debugging.

## Sanctum token schema mismatch with UUID users

### Problem
- `users.user_id` uses UUID.
- `personal_access_tokens` must use a compatible tokenable key type.
- Sanctum token creation expects the tokenable column type to match the user primary key strategy.

### Symptom
- Login may fail during token creation with a MySQL error like data truncation on `tokenable_id`.

### Impact
- Auth appears to validate, but token creation fails after credential checks pass.

### Debugging hint
- Compare:
  - `users.user_id` type
  - `personal_access_tokens.tokenable_id` type

## Current admin route naming vs target role model

### Problem
- Current routes and frontend use "admin" naming for screens and endpoints.
- Current permission guard is still production-based.

### Impact
- Easy to confuse current production-admin behavior with the planned dedicated `admin` role.

## Schema migration drift after the target-style backend rename

### Problem
- The backend create migrations now describe the target-style schema with keys such as `user_id`, `folder_id`, `file_id`, `folder_name`, and `file_name`.
- A previously migrated local database may still reflect the older structure until it is rebuilt.

### Impact
- The codebase and the local database can disagree even when the migrations look correct in source control.

### Debugging hint
- If a local environment still behaves like the old schema, inspect the actual database tables instead of assuming they match the current migration files.

## Request workflow is only partially implemented

### Problem
- `client_requests` and `assigned_clients` now exist in backend schema and models.
- Full request routes, UI flows, due-date handling, and role-specific request management are still incomplete.

### Impact
- It is easy to over-assume that the whole request workflow is live just because the data structures already exist.
