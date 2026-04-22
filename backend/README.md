# Backend

Laravel API for the Promotional Materials Portal.

## Responsibilities
- authentication with Sanctum
- role-based authorization
- dashboard aggregation
- folder and file lifecycle
- request creation and request data
- client-to-production assignment data
- recycle-bin recovery
- activity logging

## Current flow notes
- public registration creates a default `client` account
- the first client request creates and assigns the client folder
- admin owns assignment, due dates, and role changes
- production owns uploads and assigned-client execution
- agents and clients can download files within their allowed scope
- core models use UUID-based keys

## Common commands
```bash
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan test
php artisan serve
```

## Primary guide
- [AGENTS.md](./AGENTS.md)
