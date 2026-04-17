# Roles And Access

This document defines current implemented access and the planned target access model.

## Current implemented roles in code
- `production`
- `agent`
- `client`

## Planned target roles
- `admin`
- `production`
- `agent`
- `client`

## Current implemented access matrix

| Capability | Production | Agent | Client |
|---|---|---|---|
| Login | Yes | Yes | Yes, if approved |
| View dashboard | Yes | Yes | Yes |
| Approve clients | Yes | No | No |
| Create agents | Yes | No | No |
| View activity logs | Yes | No | No |
| Create folders | Yes | No | No |
| Update folders | Yes | No | No |
| Upload files | Yes | No | No |
| Delete files | Yes | No | No |
| Restore files | Yes | No | No |
| View recycle bin | Yes | No | No |
| View all folders | Yes | Yes | No |
| View assigned folder only | No | No | Yes |
| Preview/download accessible files | Yes | Yes | Yes, own folder only |

## Planned target access matrix

| Capability | Admin | Production | Agent | Client |
|---|---|---|---|---|
| Manage system settings | Yes | No | No | No |
| Approve clients | Yes | Yes if retained in workflow | No | No |
| Assign production to clients | Yes | No | No | No |
| Upload files | No by default | Yes | No | No |
| Manage client requests | Yes | Yes | No | Own requests only |
| Set request due dates | Yes | No | No | No |
| View all client folders | Optional | Yes | Yes | No |
| View own folder only | No | No | No | Yes |

## Important notes for agents
- The current code has no `admin` role yet.
- Current `/admin` frontend and backend behavior is effectively "production admin".
- Planned role separation should be documented whenever debugging or planning new work, so agents do not confuse current implementation with target behavior.
