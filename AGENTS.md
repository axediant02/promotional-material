# AGENTS.md - Project Guide

Root coordination guide for the Promotional Materials Portal. Use this file for shared business rules and cross-stack changes. For implementation details, use:
- [backend/AGENTS.md](./backend/AGENTS.md)
- [frontend/AGENTS.md](./frontend/AGENTS.md)

## Project Summary
- Purpose: secure file delivery portal for production teams, agents, and clients.
- Apps:
  - `backend/` Laravel API
  - `frontend/` Vue client
- Live roles:
  - `production`
  - `agent`
  - `client`
- Planned role:
  - `admin`

## Current Truth
- Current `/admin` UI and `/api/admin/*` routes are still production-operated admin behavior.
- Core live flows are file delivery, folder access, downloads, recycle-bin recovery, and activity logging.
- Backend foundations now exist for:
  - `client_requests`
  - `assigned_clients`
  - UUID-first schema naming such as `user_id`, `folder_id`, `file_id`, `folder_name`, `file_name`, `category`
- One client maps to one assigned folder.
- Registration creates a default `client` account immediately.
- The client's folder is created and assigned when the first request is submitted.

## Target Direction
- Introduce a first-class live `admin` role.
- Complete request-management and client-assignment workflows across backend and frontend.
- Keep the current file portal stable while the request/admin split is completed.

## Shared Rules
- Backend authorization is the source of truth.
- Frontend guards and hidden UI are UX only.
- Keep terminology aligned across backend, frontend, tests, and docs.
- Distinguish clearly between:
  - implemented behavior
  - backend/schema foundations
  - planned target state
- If a shared API contract changes, update both apps and the relevant docs in the same pass.

## Scope Rules
- Use this root guide first when:
  - the task affects both apps
  - the task changes shared workflow or business rules
  - the task changes current-vs-planned guidance
- Use folder guides first when:
  - the task is backend-only
  - the task is frontend-only

## Documentation Order
1. `AGENTS.md` at repo root
2. `backend/AGENTS.md`
3. `frontend/AGENTS.md`
4. `docs/system-flow.md`
5. `docs/request-workflow.md`
6. remaining references in `docs/`

## Workflow
- Clarify only when access rules, contracts, or docs conflict in a risky way.
- For cross-stack changes, keep backend and frontend behavior aligned.
- Preserve current production-admin behavior unless the task explicitly introduces the admin split.
- Preserve one-client-one-folder access unless requirements change.
- Verify:
  - backend changes: `cd backend && php artisan test`
  - frontend changes: `cd frontend && npm run build`
  - cross-stack changes: run both when relevant

## Compounding Knowledge
- 2026-04-17: `backend/AGENTS.md` and `frontend/AGENTS.md` are the primary implementation guides for their folders.
- 2026-04-17: Production still acts as admin across the live stack.
- 2026-04-20: Requests and assignments now exist as backend foundations, but the full workflow is still incomplete.
- 2026-04-20: Backend naming is actively migrating toward `user_id` / `folder_id` / `file_id` / `folder_name` / `file_name` / `category`.
- 2026-04-20: Docs must distinguish schema readiness from product readiness.

## Success Criteria
- Secure file delivery
- Clear role boundaries
- Cross-stack alignment
- Docs that reflect the real codebase without overstating planned features
