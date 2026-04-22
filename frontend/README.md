# Frontend

Vue client for the Promotional Materials Portal.

## Responsibilities
- landing page, login, and registration
- role-based routing
- client, agent, and production dashboards
- request submission UI
- API consumption through shared services

## Current flow notes
- public entry redirects `/` to `/login`
- sign-in is `/login`
- registration creates the account immediately, and the first request creates the assigned folder
- admin governs assignments and due dates
- production handles uploads and assigned-client execution
- agents and clients can download files within their allowed scope

## Common commands
```bash
npm install
npm run dev
npm run build
```

## Primary guide
- [AGENTS.md](./AGENTS.md)
