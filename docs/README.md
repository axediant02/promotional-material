# Docs Index

This folder contains the maintained implementation references for the portal.

## Documents
- [system-flow.md](./system-flow.md)
- [request-workflow.md](./request-workflow.md)
- [api-reference.md](./api-reference.md)
- [frontend-routes.md](./frontend-routes.md)
- [schemas-relationship.md](./schemas-relationship.md)

## Reading order
1. Read `system-flow.md` for the current end-to-end flow.
2. Read `request-workflow.md` for request lifecycle and role ownership.
3. Use `api-reference.md` and `frontend-routes.md` when implementing or debugging route behavior.
4. Use `schemas-relationship.md` when checking table relationships and identifiers.

## Current implementation notes
- Guests land on `/`.
- The first client request creates the assigned folder.
- Admin governance runs through `/admin-new` and the `/admin/*` request, assignment, role, and activity endpoints.
- Production execution runs through the nested `/production` workspace.

## Testing rule
- The project uses TDD for new backend functionality.
- Once an approval test is accepted, keep it fixed as the acceptance target.
- If it fails, fix the implementation or setup instead of rewriting the test to force a pass.
