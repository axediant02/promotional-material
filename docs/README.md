# Docs Index

This folder contains the maintained implementation references for the portal.

## Source Of Truth
- These docs describe current live behavior unless a section is explicitly labeled `planned`, `target`, or `historical`.
- Use the root `AGENTS.md` files for workflow rules first, then use these docs for implementation detail.
- Treat proposal files and scratch notes as non-authoritative unless they are being updated to match the live system.

## Documents
| File | Use |
|---|---|
| [system-flow.md](./system-flow.md) | End-to-end onboarding, request, assignment, and delivery flow |
| [request-workflow.md](./request-workflow.md) | Request lifecycle, ownership, and chat behavior |
| [api-reference.md](./api-reference.md) | Live backend route groups and access notes |
| [frontend-routes.md](./frontend-routes.md) | Current Vue route map and dashboard linkage |
| [schemas-relationship.md](./schemas-relationship.md) | Current schema foundation and relationships |

## Reading Order
1. Read `system-flow.md` for the current end-to-end flow.
2. Read `request-workflow.md` for request lifecycle, ownership, and assignment chat.
3. Use `api-reference.md` and `frontend-routes.md` when implementing or debugging route behavior.
4. Use `schemas-relationship.md` when checking table relationships and identifiers.

## Current Implementation Notes
- Guests land on `/`.
- Registration creates a `client` account immediately.
- The first client request creates the assigned folder if it does not already exist.
- Admin management runs through `/admin` and the `/admin/*` request, assignment, role, and activity endpoints.
- `/admin-new` and `/agent-new` remain documented only as compatibility redirects to the canonical `/admin` and `/agent` routes.
- Production execution runs through the nested `/production` workspace.
- Assignment chat is part of the live client and production dashboard experience for assignment-linked threads.

## Testing Rule
- The project uses TDD for new backend functionality.
- Once an approval test is accepted, keep it fixed as the acceptance target.
- If it fails, fix the implementation or setup instead of rewriting the test to force a pass.
