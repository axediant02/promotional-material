# Docs Index

This folder contains the current working docs for the portal.

## Current docs
- [api-reference.md](./api-reference.md)
- [frontend-routes.md](./frontend-routes.md)
- [request-workflow.md](./request-workflow.md)
- [schemas-relationship.md](./schemas-relationship.md)
- [system-flow.md](./system-flow.md)

## Reading order
1. Read `system-flow.md` for the implemented onboarding and delivery flow.
2. Read `request-workflow.md` for request ownership and lifecycle.
3. Use `api-reference.md`, `frontend-routes.md`, and `schemas-relationship.md` while implementing or debugging.

## Testing rule
- The project uses TDD for new backend functionality.
- After a test is written and approved as the target behavior, do not rewrite that test only to force a pass.
- Fix the implementation, supporting setup, or incorrect assumptions instead unless the requirement itself is intentionally changed.
