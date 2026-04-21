# PMS Delivery Matrix (Current Branch Snapshot)

This document captures implementation artifacts currently present in the repository for the PMS build-out plan.

## Sidebar config
- Source of truth: `resources/js/config/sidebarConfig.js`.
- Flattened route helper: `flattenSidebarRoutes()`.
- Dynamic page mapping helper: `pagePathFromRoute()`.

## Placeholder page matrix
- Empty-state page component: `resources/js/components/EmptyStatePage.vue`.
- Generated placeholder pages: `resources/js/Pages/**` (55 files currently).

## Route map
- API route declarations: `routes/api.php`.
- Web/public route declarations: `routes/web.php`.
- Front-end route registration and permission guard: `resources/js/router/index.js`.

## Seeders list
- Seeder directory: `database/seeders`.
- Current seeders include app bootstrap/database scaffolding and can be expanded per phase.

## Nova resources list
- Nova resources directory: `app/Nova`.
- Current resources are available and grouped by domain in the Nova app tree.

## Migration batch list
- Migration directory: `database/migrations`.
- Current migration count can be inspected with:

```bash
find database/migrations -type f | wc -l
```

## API flow smoke coverage (curl)
- 65 endpoint examples spanning login, dashboard, reservations, front-desk-adjacent unit flow,
  financial, reports, POS, settings, uploads, and public lead ingest.
- File: `tests/Feature/Api/curl-examples.sh`.

Run:

```bash
BASE_URL=http://127.0.0.1:8000 TOKEN=<sanctum_token> bash tests/Feature/Api/curl-examples.sh
```
