# Debug Session: sidebar-links-500-error

## Status: [OPEN]

## Session Information
- **Session ID**: sidebar-links-500-error
- **Created**: 2026-05-19
- **Bug Description**: Dashboard shows Axios 500 error when loading sidebar menu, and many sidebar links are not working

## Symptoms
- Console Error: `[Vue Error] AxiosError: Request failed with status code 500`
- Console Error: `runtime.lastError: The message port closed before a response was received.` (repeated 8 times)
- Error occurs in `mounted hook` in `main.js:12`
- Many sidebar links not working

## Root Cause Analysis ( hypotheses )

### Hypothesis 1: Database table `sidebar_items` missing or not migrated
- **Observation Point**: The SidebarService queries `SidebarItem` model which requires a database table
- **Falsifiable**: Check if `sidebar_items` table exists and has data

### Hypothesis 2: Route mismatch between sidebar config and Vue router
- **Observation Point**: The `config/sidebar.php` has routes like `/reports/metabase` but Vue router defines `/dashboard/metabase`
- **Falsifiable**: Compare all sidebar config routes with Vue router definitions

### Hypothesis 3: Missing API endpoints for sidebar menu items
- **Observation Point**: When clicking a sidebar link, the target Vue page might call an API that returns 500
- **Falsifiable**: Check which API endpoints are called when sidebar links are clicked

### Hypothesis 4: Permission system issue in SidebarService
- **Observation Point**: The SidebarService uses `hasPermissionTo()` method from HasRoles trait
- **Falsifiable**: Check if User model correctly implements the permission method

### Hypothesis 5: Vue pages missing or incorrectly imported
- **Observation Point**: The router imports Vue components that might not exist
- **Falsifiable**: Verify all imported Vue page components exist

## Evidence Collection

### Step 1: Check sidebar_items table
```sql
DESCRIBE sidebar_items;
SELECT COUNT(*) FROM sidebar_items;
```

### Step 2: Compare routes
- Sidebar config: `config/sidebar.php`
- Vue router: `resources/js/router/index.js`

### Step 3: Check missing Vue pages
Glob pattern: `resources/js/pages/**/*.vue`

## Instrumentation Plan
- Add debug logging to SidebarService
- Add error handling to API calls in Vue components

## Fix Log
*(To be filled as fixes are applied)*

## Cleanup
- [ ] Remove debug logging
- [ ] Confirm all sidebar links work
- [ ] Verify no 500 errors in console
