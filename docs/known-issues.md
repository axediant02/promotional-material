# Known Issues

This document tracks current known technical issues or mismatches that are useful during debugging.

## Sanctum token schema mismatch with UUID users

### Problem
- `users.id` uses UUID.
- `personal_access_tokens` was created with `morphs('tokenable')`.
- Sanctum token creation expects the tokenable column type to be compatible with the user primary key.

### Symptom
- Login may fail during token creation with a MySQL error like data truncation on `tokenable_id`.

### Impact
- Auth appears to validate, but token creation fails after credential checks pass.

### Debugging hint
- Compare:
  - `users.id` type
  - `personal_access_tokens.tokenable_id` type

## Current admin route naming vs target role model

### Problem
- Current routes and frontend use "admin" naming for screens and endpoints.
- Current permission guard is still production-based.

### Impact
- Easy to confuse current production-admin behavior with the planned dedicated `admin` role.

## Folder schema drift

### Problem
- Current schema and controller still support nested folders through `parent_id`.
- Planned MVP target removed parent folders for simplicity.

### Impact
- Agents may accidentally extend nested folder behavior even though the agreed target says not to.

## Request workflow not implemented

### Problem
- `client_requests` and `assigned_clients` are documented and planned, but not present in the codebase.

### Impact
- Any request-related behavior in documents should be treated as target-state guidance, not implemented capability.
