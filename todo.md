# Minimal Accounting UI — TODO Plan (Basic Schema)

This plan delivers a modern, intuitive accounting UI for a Bangladeshi enterprise with a very simple schema and implementation. It focuses on:
- Account management + categories (Asset, Liability, Equity, Income, Expense)
- Cashbook entry (From Account → To Account), with Cash/Bank type
- Auto-ledger (double-entry lines saved automatically)
- Unique voucher numbers
- Attribution of who made the entry (e.g., Cumilla/Chittagong branch manager, Head of Accountant, Admin, Super Admin)

We will NOT implement the complex `database_schema_documentation.md` structure. Instead, we’ll use a minimal schema and iterate.

---

## 1) Minimal Database Schema
Implement via new migrations in `database/migrations/`.

- accounts
  - id, code (unique), name, category (enum: asset, liability, equity, income, expense), is_bank (bool), is_active (bool), created_at, updated_at
- account_categories (optional, for UI-driven categories; otherwise use enum)
  - id, name (e.g., Asset, Liability, Equity, Income, Expense), created_at, updated_at
- branches
  - id, name (e.g., Cumilla, Chittagong, Head Office), code (unique), created_at, updated_at
- users (extend via migration; keep auth as-is)
  - add: role (enum: super_admin, admin, head_accountant, branch_manager), branch_id (nullable FK to branches)
- transactions
  - id, voucher_no (unique), date (date), type (enum: cash, bank, contra, journal), branch_id (FK), from_account_id (FK), to_account_id (FK), amount (decimal 14,2), narration (text), created_by (FK users), created_at, updated_at
  - optional bank fields (only when type=bank): bank_name, cheque_no, cheque_date (date)
  - voucher_image_path (nullable string; path to uploaded voucher image) (P1)
- transaction_lines (auto-ledger entries; always 2 lines per transaction)
  - id, transaction_id (FK), account_id (FK), debit (decimal 14,2 nullable), credit (decimal 14,2 nullable), branch_id (FK), created_at, updated_at

Notes
- `transaction_lines` are generated from `from_account_id`, `to_account_id`, and `amount`.
- Running balances can be computed from `transaction_lines`; we won’t persist balances initially.

---

## 2) Migrations & Seeders
- [x] Create migrations for: `branches`, `accounts`, `transactions`, `transaction_lines` (categories table optional – not included).
- [x] Create migration to add `role` and `branch_id` columns to `users`.
- [x] Add migration to add `voucher_image_path` to `transactions`; configure storage link (`php artisan storage:link`). (P1)
- [ ] Seed:
  - [x] Default branches: Head Office, Cumilla, Chittagong
  - [ ] Categories (if using table): Asset, Liability, Equity, Income, Expense
  - [x] A few starter accounts (Cash in Hand, Bank Account, Sales, Purchases, Expense)
  - [x] Admin user with role `super_admin`

---

## 3) Voucher Numbering (Unique)
- Rule: Voucher per branch per day, e.g. `BR-{BRANCHCODE}-{YYYYMMDD}-{NNNN}`
  - Example: `BR-CTG-20250809-0001`
- [x] Service: `App/Services/VoucherService.php`
  - [x] Generate on create (atomic; DB transaction; lock next sequence)
  - [ ] Validate uniqueness
  - [ ] Preview and lock on save

---

## 4) Backend Endpoints (Laravel + Inertia)
Controllers in `app/Http/Controllers/` and routes in `routes/web.php`.

- AccountsController
  - [x] index (list, filters by category, is_bank)
  - [x] create/store
  - [x] edit/update
  - [x] toggle active
- CategoriesController (if using table)
  - [ ] index/create/update/delete
- TransactionsController (Cashbook)
  - [x] index (filter: date range, branch, type, account)
  - [x] create/store: fields → date, branch, type (cash/bank), from_account, to_account, amount, narration, bank fields, auto voucher
  - [ ] show/print (voucher slip)
  - [x] upload voucher image (multipart) endpoint to attach voucher to a transaction, validate mime/size, store in `public/vouchers`, return URL. (P1)
  - [x] include `voucher_image_path` in transaction API responses. (P1)
- LedgerController
  - [x] account ledger: lines and running balance (filters: date range, branch)

Policies/Scopes
- [x] Gate by role (branch_manager limited to their branch)
- [x] Super/admin/head_accountant can see multi-branch

---

## 5) Frontend (Vue 3 + Inertia + Tailwind 4)
Pages in `resources/js/pages/`; components in `resources/js/components/`; layouts in `resources/js/layouts/`.

- Layout & Nav
  - [ ] Authenticated layout with topbar: Branch selector (if role >= head_accountant), User menu, Quick actions
  - [ ] Sidebar: Dashboard, Accounts, Transactions, Ledger, Settings
- Accounts
  - [ ] List with category chips and is_bank badge; quick search
  - [ ] Create/Edit drawer modal (code, name, category, is_bank)
- Categories (optional)
  - [ ] Simple CRUD table
- Cashbook (Transactions)
  - [x] Form with: Date (default today), Branch (auto), Type (Cash/Bank), From Account, To Account, Amount, Narration
  - [x] If Type=Bank → show Bank Name, Cheque No, Cheque Date
  - [ ] Auto Voucher No (readonly; regenerate button)
  - [x] Submit → creates `transactions` + 2 `transaction_lines` (DR/CR)
  - [x] Voucher image upload (jpg/png/pdf up to 2MB) with preview; submit via multipart/form-data and attach to transaction. (P1)
  - [x] Show voucher image thumbnail/link in list or detail (icon linking to file). (P1)
- Transactions List
  - [x] Table with Voucher, Date, Branch, From, To, Amount, Type, Created By; filters (date range, branch, type)
  - [ ] Export CSV
- Ledger View
  - [x] Account picker; date range; list of lines with running balance (debit/credit columns)

UI/UX Principles
- [ ] Clean, responsive Tailwind 4 design, keyboard-friendly forms
- [ ] Selects with typeahead for account fields
- [ ] Validation messages inline; disabled states; loading states; toast notifications

---

## 6) Business Rules & Validation
- [x] Debit/Credit balance: always create two lines per transaction (sum debits = sum credits)
- [x] Prevent same From and To account
- [x] Require bank fields for bank type
- [x] Ensure user role/branch permissions on create/list
- [ ] Date within reasonable range (no future beyond policy)

---

## 7) Roles & Branch Attribution (Simple)
- [x] Add `role` enum on users: super_admin, admin, head_accountant, branch_manager
- [x] Add `branch_id` to users (nullable for super/admin)
- [x] Each transaction stores `created_by` and `branch_id`
- [ ] UI badges show who created it and for which branch

---

## 8) Minimal Reporting
- [ ] Trial Balance (computed from transaction_lines)
- [ ] Account Ledger export (CSV)
- [ ] Cashbook summary by date range and branch

---

## 9) Auditing & Logs (Lightweight)
- [ ] Model events: log user id and IP on transaction create
- [x] Store `created_by` on transactions (primary attribution)

---

## 10) Testing
- [x] Unit: VoucherService
- [ ] Unit: posting engine (DR/CR), category/account validation
- [ ] Feature: Accounts CRUD, create transaction (cash/bank), ledger view
- [ ] Factories: users (roles), branches, accounts

---

## 11) Milestones (Fast Path)
- M1: Schema + seed + Accounts CRUD + Branch/Role setup
- M2: Cashbook form + VoucherService + ledger lines generation
- M3: Transactions list + Ledger view + voucher image upload + minimal reports (P1)
- M4: UX polish (typeahead, toasts, exports) + tests

---

## 12) Nice-to-have (Later)
- [ ] Pinia state for selections
- [ ] PDF voucher print
- [ ] Bank statement import (CSV) for basic reconciliation
- [ ] Per-branch voucher sequences and configurable prefixes
