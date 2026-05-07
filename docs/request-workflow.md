# Request Workflow

This document describes the current request lifecycle, ownership model, assignment chat, and route responsibilities.

## Status
- Current live request workflow unless a section is labeled planned or target.
- Requests and assignment chat are live backend features.
- If this doc and the code disagree, treat the live backend as the source of truth and mark the mismatch explicitly in future updates.

## Purpose
Clients can request:
- new assets
- updates to existing assets

Admin governs due dates and assignment context. Production executes the work.

## Core request data

### `client_requests`
- `request_id`
- `client_id`
- `folder_id`
- `title`
- `description`
- `request_type`
- `status`
- `due_date`
- timestamps
- soft-delete fields

### Supported values
- `request_type`
  - `new_asset`
  - `update_asset`
- `status`
  - `pending`
  - `in_progress`
  - `done`

## Ownership model

### `assigned_clients`
- `id`
- `production_id`
- `client_id`
- `status`
- timestamps

Assignments live at the client level, not the individual request level.

### `assignment_chat_threads`
- `thread_id`
- `assignment_id`
- `client_id`
- `production_id`
- `status`
- `started_at`
- `closed_at`
- `last_message_at`
- `last_message_by`
- `client_last_read_at`
- `production_last_read_at`
- timestamps

### `assignment_chat_messages`
- `message_id`
- `thread_id`
- `sender_user_id`
- `body`
- timestamps

## Access rules

### Client
- can create requests
- can view own request history
- cannot set due dates
- cannot view other clients' requests

### Admin
- can view all client requests
- can set and update `due_date`
- can assign a client to a production owner
- can remove a client assignment
- can update user roles

### Production
- can view requests for assigned clients
- can update request status
- can upload files for assigned-client work
- can use the assignment chat on active assignment threads

### Agent
- does not access request routes
- can browse and download files allowed by backend authorization

## Assignment chat
1. Saving an assignment creates or reuses the active chat thread for the client and production pair.
2. Client and production users can fetch the active thread, the full thread list, and message history through the chat routes.
3. Either side can post messages while the thread status is `active`.
4. When the assignment is changed, marked done, or removed, the active thread is archived and remains read-only.
5. Realtime delivery uses private `assignment-chat.{thread_id}` and `assignment-chat-user.{user_id}` channels.

## Current workflow
1. A user registers and receives the `client` role.
2. The client signs in.
3. The client submits a request through `POST /requests`.
4. If the client does not yet have an assigned folder, the backend creates and assigns it during that first request.
5. The request is stored with `pending` status.
6. The client can review request history through `GET /requests`.
7. Admin reviews the full queue through `GET /admin/requests`.
8. Admin sets or updates the due date through `PATCH /admin/requests/{clientRequest}`.
9. Admin links the client to production through `POST /admin/assignments`.
10. The backend creates or reuses the active assignment chat thread for that assignment.
11. Production fetches requests for assigned clients through `GET /production/requests`.
12. Production updates operational status through `PATCH /production/requests/{clientRequest}`.

## Admin management UI
- The admin dashboard at `/admin` shows admin stats, the request queue, assignments, activity logs, and user-role context.
- The request queue supports inline due-date editing.
- Saving a due date updates the request queue and admin counts from the live API response.
- Assignment management uses the live admin assignment routes instead of static placeholder data.
- `/admin-new` remains only as a compatibility redirect to `/admin`.

## Operational notes
- Registration does not create a folder.
- The first request creates the client's assigned folder.
- Future requests reuse the existing assigned folder.
- Production status updates and admin due-date updates are separate routes with separate validation rules.
- The admin due-date route prohibits `status`.
- The production status route prohibits `due_date`.
- Assignment chat becomes read-only when the assignment is archived or removed.
- Assignment chat is part of the current workflow, not future work.

## TDD rule
- New backend request-workflow changes should be written test-first.
- Once an approval test is accepted, treat it as fixed acceptance criteria.
- If that test fails, fix the implementation or setup instead of weakening the test.

## Related docs
- [system-flow.md](./system-flow.md)
- [api-reference.md](./api-reference.md)
- [schemas-relationship.md](./schemas-relationship.md)
