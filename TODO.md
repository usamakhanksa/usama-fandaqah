# TODO — Finance — Receipts CRUD (10 sections)

## 1) Migration (receipts table + soft deletes + tenant scoping + monthly counters)
- [ ] Create/adjust migration for `receipts` table
- [ ] Add `team_id`, `deleted_at`, `receipt_number`, `date`, `reason`, `amount`, `employee_id`, `payment_method`, `status`, cancellation fields, `note`, `attachment_path`
- [ ] Add monthly counter table for thread-safe numbering (per team per YYYY-MM)
- [ ] Add indexes & unique constraints

## 2) Receipt model (multi-tenant + scopes + formatted_amount + cancel())
- [ ] Update `app/Models/Receipt.php` to use SoftDeletes + tenant scoping
- [ ] Add relationships (employee)
- [ ] Add `generateReceiptNumberForDate()` with DB locking
- [ ] Add `formatted_amount` accessor
- [ ] Add `cancel()` (status + cancellation metadata + ledger compensation hooks)

## 3) Finance/ReceiptController (CRUD + cancel + RBAC)
- [ ] Create dedicated `app/Http/Controllers/Api/ReceiptController.php`
- [ ] Implement endpoints:
  - [ ] GET index
  - [ ] POST store (draft/confirm compatible)
  - [ ] GET show
  - [ ] PUT/PATCH update
  - [ ] POST cancel
- [ ] Implement spatie permission checks (Super Admin bypass)

## 4) ReceiptService (business logic + ledger correctness)
- [ ] Create `app/Services/ReceiptService.php`
- [ ] Encapsulate create/confirm/update/cancel
- [ ] Create/Reverse `FundMovement` records consistently

## 5) Vue pages (Index/Create/Edit/Show) + router wiring
- [ ] Update `resources/js/pages/FinancialManagementPage.vue` (receipts actions: view/edit/cancel)
- [ ] Add `resources/js/pages/FinancialReceiptShowPage.vue`
- [ ] Add `resources/js/pages/FinancialReceiptEditPage.vue`
- [ ] Update `resources/js/router/index.js` with show/edit routes
- [ ] Keep existing create wizard working; optionally redirect after confirm

## 6) Permission seeder entries (15 roles)
- [ ] Add receipt CRUD permissions:
  - [ ] view receipts
  - [ ] create receipts
  - [ ] edit receipts
  - [ ] cancel receipts
- [ ] Ensure they’re assigned to the correct roles (15 roles) via existing command/seeder

## 7) routes/web.php (SPA compatibility)
- [ ] Add/ensure server routes if needed for show/edit paths; otherwise rely on SPA catch-all

## 8) Sidebar update
- [ ] Update sidebar entry for receipts (and create if required) using `/api/sidebar` data or sidebar config

## 9) ReceiptSeeder (50 receipts across 3 teams)
- [ ] Create `database/seeders/ReceiptSeeder.php`
- [ ] Seed 50 receipts across 3 teams with numbering consistency
- [ ] Seed matching `FundMovement` (confirmed) and reversal/cancel ledger behavior (cancelled)

## 10) Nova/Receipt resource
- [ ] Create `app/Nova/Receipt.php`
- [ ] Configure columns, filters, actions (cancel)
- [ ] Ensure team scoping and soft deletes behavior
