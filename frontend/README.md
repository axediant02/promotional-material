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
- `/admin` still represents the production-operated admin workspace

## Common commands
```bash
npm install
npm run dev
npm run build
```

## Primary guide
- [AGENTS.md](./AGENTS.md)
