# Comprehensive Accounting System Database Schema Documentation

## Overview
This database schema is designed for a multi-company, multi-branch accounting system that supports double-entry bookkeeping, role-based access control, and comprehensive financial management. The system can handle complex organizational structures with parent-child company relationships and detailed transaction tracking.

---

## 1. Core User Management Tables

### 1.1 `users` Table
**Purpose**: Central user management for all system users across all companies and branches.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier for each user |
| `name` | VARCHAR | Full name of the user |
| `email` | VARCHAR | Unique email address for login |
| `password` | VARCHAR | Encrypted password hash |
| `role_id` | INTEGER (FK) | References roles.id - determines user permissions |
| `is_active` | BOOLEAN | Whether the user account is active |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**: 
- Each user has one role but can have access to multiple branches
- Email must be unique across the entire system
- Inactive users cannot log in but their historical data is preserved

### 1.2 `roles` Table
**Purpose**: Defines user roles and their associated permissions in the system.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier for each role |
| `name` | VARCHAR | Role name (super_admin, admin, company_manager, branch_manager) |
| `permissions` | JSON | Structured permissions data |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- **super_admin**: System-wide access, can manage all companies
- **admin**: Company-wide access, can manage all branches within their company
- **company_manager**: Full access to assigned company operations
- **branch_manager**: Limited to specific branch operations

### 1.3 `permissions` Table
**Purpose**: Granular permission definitions for fine-grained access control.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier for each permission |
| `name` | VARCHAR | Permission name (e.g., 'create_transaction', 'approve_payment') |
| `description` | TEXT | Detailed description of what this permission allows |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Permissions are atomic actions that can be combined into roles
- Examples: 'view_reports', 'create_users', 'approve_transactions', 'manage_branches'

### 1.4 `role_permissions` Table
**Purpose**: Many-to-many relationship between roles and permissions.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier |
| `role_id` | INTEGER (FK) | References roles.id |
| `permission_id` | INTEGER (FK) | References permissions.id |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Allows flexible permission assignment to roles
- Same permission can be assigned to multiple roles
- Enables easy role modification without changing user assignments

### 1.5 `user_branch_access` Table
**Purpose**: Controls which branches a user can access.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier |
| `user_id` | INTEGER (FK) | References users.id |
| `branch_id` | INTEGER (FK) | References branches.id |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Users can have access to multiple branches
- Branch managers typically have access to their assigned branch only
- Company managers may have access to all branches within their company

---

## 2. Organizational Structure Tables

### 2.1 `companies` Table
**Purpose**: Manages the company hierarchy with support for parent-child relationships.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier for each company |
| `name` | VARCHAR | Company name |
| `type` | VARCHAR | 'parent' or 'child' company type |
| `parent_id` | INTEGER (FK) | Self-referential to companies.id for child companies |
| `registration_number` | VARCHAR | Official business registration number |
| `tax_id` | VARCHAR | Tax identification number |
| `address` | TEXT | Complete company address |
| `contact_info` | JSON | Phone, email, website, etc. |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Parent companies can have multiple child companies
- Child companies inherit certain settings from parent companies
- Each company has separate fiscal years and chart of accounts
- Supports holding company structures and subsidiaries

### 2.2 `branches` Table
**Purpose**: Manages individual branch locations for each company.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier for each branch |
| `name` | VARCHAR | Branch name/identifier |
| `company_id` | INTEGER (FK) | References companies.id |
| `location` | VARCHAR | Branch physical location |
| `contact_info` | JSON | Branch-specific contact details |
| `is_active` | BOOLEAN | Whether the branch is operational |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Each branch belongs to exactly one company
- Branches can have separate cash flows and inventory
- Transactions are always associated with a specific branch
- Inactive branches retain historical data but cannot process new transactions

---

## 3. Chart of Accounts and Ledger Tables

### 3.1 `accounts` Table
**Purpose**: Master chart of accounts defining the accounting structure.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier for each account |
| `code` | VARCHAR | Account code (e.g., '1001', '2001') |
| `name` | VARCHAR | Account name (e.g., 'Cash', 'Accounts Payable') |
| `type` | VARCHAR | Account type: asset, liability, equity, revenue, expense |
| `sub_type` | VARCHAR | Sub-classification within the type |
| `description` | TEXT | Detailed description of the account purpose |
| `is_active` | BOOLEAN | Whether the account is available for transactions |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Follows standard accounting principles (Assets = Liabilities + Equity)
- Account codes should follow a consistent numbering scheme
- Inactive accounts cannot receive new transactions but retain history
- Sub-types provide additional categorization (e.g., 'current_asset', 'fixed_asset')

### 3.2 `ledgers` Table
**Purpose**: Company and branch-specific instances of accounts with running balances.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier for each ledger |
| `account_id` | INTEGER (FK) | References accounts.id |
| `company_id` | INTEGER (FK) | References companies.id |
| `branch_id` | INTEGER (FK) | References branches.id |
| `name` | VARCHAR | Ledger-specific name if different from account |
| `opening_balance` | DECIMAL | Balance at the start of fiscal year |
| `current_balance` | DECIMAL | Current running balance |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Each company-branch combination has its own ledger for each account
- Current balance is updated with each transaction
- Opening balance is set at the beginning of each fiscal year
- Enables separate accounting for each branch while maintaining consolidated view

---

## 4. Financial Period Management

### 4.1 `fiscal_years` Table
**Purpose**: Manages accounting periods for each company.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier for each fiscal year |
| `name` | VARCHAR | Fiscal year name (e.g., 'FY 2024', '2024-25') |
| `start_date` | DATE | First day of the fiscal year |
| `end_date` | DATE | Last day of the fiscal year |
| `is_active` | BOOLEAN | Whether this is the current active fiscal year |
| `is_closed` | BOOLEAN | Whether the fiscal year has been closed |
| `company_id` | INTEGER (FK) | References companies.id |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Each company can have different fiscal year periods
- Only one fiscal year can be active per company at a time
- Closed fiscal years cannot receive new transactions
- Year-end closing processes update opening balances for the next year

---

## 5. Transaction Management Tables

### 5.1 `transactions` Table
**Purpose**: Main transaction header containing common transaction information.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier for each transaction |
| `transaction_date` | DATE | Date when the transaction occurred |
| `reference_no` | VARCHAR | External reference number (invoice, receipt, etc.) |
| `type` | VARCHAR | Transaction type (payment, receipt, journal, transfer, bank_deposit, etc.) |
| `amount` | DECIMAL | Total transaction amount |
| `description` | TEXT | Transaction description/memo |
| `fiscal_year_id` | INTEGER (FK) | References fiscal_years.id |
| `created_by` | INTEGER (FK) | References users.id - who created the transaction |
| `approved_by` | INTEGER (FK) | References users.id - who approved the transaction |
| `company_id` | INTEGER (FK) | References companies.id |
| `branch_id` | INTEGER (FK) | References branches.id |
| `company_bank_account_id` | INTEGER (FK) | References company_bank_accounts.id for bank transactions |
| `external_bank_account_id` | INTEGER (FK) | References external_company_bank_accounts.id |
| `cheque_number` | VARCHAR | Cheque number if applicable |
| `cheque_date` | DATE | Cheque date if applicable |
| `status` | VARCHAR | Transaction status (draft, approved, voided, cleared, bounced) |
| `cleared_date` | DATE | Date when bank transaction was cleared |
| `bank_charges` | DECIMAL | Bank charges associated with the transaction |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Each transaction must balance (total debits = total credits)
- Transactions can only be created in active fiscal years
- Draft transactions can be modified; approved transactions are immutable
- Bank-related transactions include additional banking information

### 5.2 `transaction_details` Table
**Purpose**: Individual debit and credit entries that make up each transaction (double-entry bookkeeping).

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier for each transaction line |
| `transaction_id` | INTEGER (FK) | References transactions.id |
| `ledger_id` | INTEGER (FK) | References ledgers.id |
| `debit_amount` | DECIMAL | Debit amount (null if credit entry) |
| `credit_amount` | DECIMAL | Credit amount (null if debit entry) |
| `description` | TEXT | Line-specific description |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Each transaction must have at least two transaction details (one debit, one credit)
- Either debit_amount or credit_amount is filled, never both
- Sum of all debits must equal sum of all credits for each transaction
- Updates the corresponding ledger's current_balance

### 5.3 `transaction_parties` Table
**Purpose**: Links transactions to external parties (customers, vendors).

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier |
| `transaction_id` | INTEGER (FK) | References transactions.id |
| `external_company_id` | INTEGER (FK) | References external_companies.id |
| `external_company_branch_id` | INTEGER (FK) | References external_company_branches.id - tracks which branch of the external company was involved |
| `party_type` | VARCHAR | 'from' or 'to' indicating transaction direction |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Enables tracking of receivables and payables
- Supports transactions with multiple parties
- 'from' indicates money received from this party
- 'to' indicates money paid to this party
- Provides branch-level tracking for external companies
- Enables comprehensive branch-to-branch transaction reporting

---

## 6. External Entity Management

### 6.1 `external_companies` Table
**Purpose**: Manages customers, vendors, and other external business entities.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier for each external company |
| `name` | VARCHAR | Company/individual name |
| `contact_person` | VARCHAR | Primary contact person |
| `email` | VARCHAR | Contact email address |
| `phone` | VARCHAR | Contact phone number |
| `address` | TEXT | Complete address |
| `tax_id` | VARCHAR | Tax identification number |
| `is_client` | BOOLEAN | Whether this entity is a customer |
| `is_vendor` | BOOLEAN | Whether this entity is a supplier |
| `is_active` | BOOLEAN | Whether the entity is active |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- An entity can be both client and vendor
- Inactive entities cannot be used in new transactions
- Supports both individual and company external parties
- Used for accounts receivable and payable tracking


### 6.2 `external_company_branches` Table
**Purpose**: Manages branch locations for external companies to enable branch-to-branch transaction tracking.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier for each external company branch |
| `external_company_id` | INTEGER (FK) | References external_companies.id |
| `name` | VARCHAR | Branch name (e.g., "Chittagong Branch", "Cumilla Branch") |
| `location` | VARCHAR | Physical location of the branch |
| `contact_info` | JSON | Branch-specific contact details |
| `is_active` | BOOLEAN | Whether the branch is operational |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Each branch belongs to exactly one external company
- Enables tracking of which specific branch of an external company was involved in a transaction
- Inactive branches cannot be used in new transactions but historical data is preserved
- Branch-specific contact information facilitates direct communication

---

## 7. Banking System Tables

### 7.1 `banks` Table
**Purpose**: Master data for all banks in the system.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier for each bank |
| `short_name` | VARCHAR | Bank abbreviation (e.g., 'ABC', 'XYZ Bank') |
| `full_name` | VARCHAR | Complete official bank name |
| `is_govt` | BOOLEAN | Whether it's a government or private bank |
| `head_office` | VARCHAR | Head office location |
| `head_office_address` | TEXT | Complete head office address |
| `contact_person` | VARCHAR | Primary contact person |
| `mobile` | VARCHAR | Contact mobile number |
| `email` | VARCHAR | Contact email address |
| `swift_code` | VARCHAR | International SWIFT code |
| `routing_number` | VARCHAR | Bank routing number |
| `is_active` | BOOLEAN | Whether the bank is operational |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Central repository for all banks used by the organization
- Government banks may have different processing rules
- SWIFT codes enable international transactions
- Inactive banks cannot be used for new accounts

### 7.2 `bank_branches` Table
**Purpose**: Individual branch locations for each bank.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier for each bank branch |
| `short_name` | VARCHAR | Branch abbreviation |
| `full_name` | VARCHAR | Complete branch name |
| `bank_id` | INTEGER (FK) | References banks.id |
| `branch_code` | VARCHAR | Bank-specific branch code |
| `manager` | VARCHAR | Branch manager name |
| `address` | TEXT | Branch address |
| `mobile` | VARCHAR | Branch contact number |
| `email` | VARCHAR | Branch email address |
| `is_active` | BOOLEAN | Whether the branch is operational |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Each branch belongs to exactly one bank
- Branch codes are used for check processing and transfers
- Inactive branches cannot be used for new accounts

### 7.3 `company_bank_accounts` Table
**Purpose**: Bank accounts owned by the company and its branches.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier for each account |
| `company_id` | INTEGER (FK) | References companies.id |
| `branch_id` | INTEGER (FK) | References branches.id - which company branch owns this account |
| `bank_id` | INTEGER (FK) | References banks.id |
| `bank_branch_id` | INTEGER (FK) | References bank_branches.id |
| `account_number` | VARCHAR | Bank account number |
| `account_name` | VARCHAR | Account title/name |
| `account_type` | VARCHAR | Account type (current, savings, fixed_deposit) |
| `opening_balance` | DECIMAL | Balance when account was opened |
| `current_balance` | DECIMAL | Current account balance |
| `is_active` | BOOLEAN | Whether the account is operational |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Each account belongs to a specific company branch
- Current balance is updated with each transaction
- Different account types may have different rules and restrictions
- Inactive accounts cannot process new transactions

### 7.4 `external_company_bank_accounts` Table
**Purpose**: Bank account information for customers and vendors.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier |
| `external_company_id` | INTEGER (FK) | References external_companies.id |
| `bank_id` | INTEGER (FK) | References banks.id |
| `bank_branch_id` | INTEGER (FK) | References bank_branches.id |
| `account_number` | VARCHAR | Bank account number |
| `account_name` | VARCHAR | Account title/name |
| `account_type` | VARCHAR | Account type |
| `is_active` | BOOLEAN | Whether the account information is current |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Used for direct payments to vendors and receipts from customers
- Multiple bank accounts can be stored per external company
- Facilitates electronic fund transfers and payment processing

---

## 8. Bank Reconciliation Tables

### 8.1 `bank_reconciliation` Table
**Purpose**: Monthly bank statement reconciliation records.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier for each reconciliation |
| `company_bank_account_id` | INTEGER (FK) | References company_bank_accounts.id |
| `reconciliation_date` | DATE | Date of reconciliation (usually month-end) |
| `statement_balance` | DECIMAL | Balance according to bank statement |
| `book_balance` | DECIMAL | Balance according to company books |
| `reconciled_balance` | DECIMAL | Final reconciled balance |
| `status` | VARCHAR | Reconciliation status (pending, completed) |
| `reconciled_by` | INTEGER (FK) | References users.id - who performed reconciliation |
| `notes` | TEXT | Reconciliation notes and observations |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Performed monthly to ensure bank records match company records
- Identifies timing differences, bank charges, and errors
- Required for accurate financial reporting and cash management

### 8.2 `bank_reconciliation_items` Table
**Purpose**: Individual reconciling items identified during bank reconciliation.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier |
| `bank_reconciliation_id` | INTEGER (FK) | References bank_reconciliation.id |
| `transaction_id` | INTEGER (FK) | References transactions.id (if applicable) |
| `item_type` | VARCHAR | Type: outstanding_deposit, outstanding_withdrawal, bank_charge, interest |
| `amount` | DECIMAL | Reconciling item amount |
| `description` | TEXT | Description of the reconciling item |
| `is_reconciled` | BOOLEAN | Whether this item has been resolved |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Outstanding deposits: Deposits recorded but not yet cleared by bank
- Outstanding withdrawals: Checks issued but not yet cashed
- Bank charges: Fees charged by bank not yet recorded in books
- Interest: Interest earned but not yet recorded in books

---

## 9. Transfer Management Tables

### 9.1 `branch_transfers` Table
**Purpose**: Direct transfers between branches within the same company.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier |
| `from_branch_id` | INTEGER (FK) | References branches.id - source branch |
| `to_branch_id` | INTEGER (FK) | References branches.id - destination branch |
| `amount` | DECIMAL | Transfer amount |
| `transfer_date` | DATE | Date of transfer |
| `notes` | TEXT | Transfer purpose and notes |
| `created_by` | INTEGER (FK) | References users.id |
| `status` | VARCHAR | Transfer status (pending, completed, cancelled) |
| `transaction_id` | INTEGER (FK) | References transactions.id - underlying accounting transaction |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Facilitates cash management between branches
- Creates corresponding accounting entries in the general ledger
- Requires approval workflow for large amounts
- Both branches must belong to the same company

### 9.2 `inter_company_transactions` Table
**Purpose**: Transactions between different companies (parent-child or related entities).

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier |
| `from_company_id` | INTEGER (FK) | References companies.id - source company |
| `to_company_id` | INTEGER (FK) | References companies.id - destination company |
| `from_branch_id` | INTEGER (FK) | References branches.id - source branch |
| `to_branch_id` | INTEGER (FK) | References branches.id - destination branch |
| `amount` | DECIMAL | Transaction amount |
| `transaction_date` | DATE | Date of transaction |
| `description` | TEXT | Transaction description |
| `created_by` | INTEGER (FK) | References users.id |
| `approved_by` | INTEGER (FK) | References users.id |
| `status` | VARCHAR | Transaction status (pending, approved, completed) |
| `transaction_id` | INTEGER (FK) | References transactions.id |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Handles loans, investments, and service charges between related companies
- Requires higher-level approval due to inter-company nature
- Creates entries in both companies' books
- Essential for consolidated financial reporting

---

## 10. System Administration Tables

### 10.1 `audit_logs` Table
**Purpose**: Comprehensive audit trail of all system activities.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier |
| `user_id` | INTEGER (FK) | References users.id - who performed the action |
| `action` | VARCHAR | Action performed (create, update, delete, approve, etc.) |
| `entity_type` | VARCHAR | Type of entity affected (transaction, user, company, etc.) |
| `entity_id` | INTEGER | ID of the affected entity |
| `old_values` | JSON | Previous values before change |
| `new_values` | JSON | New values after change |
| `ip_address` | VARCHAR | IP address of the user |
| `created_at` | TIMESTAMP | When the action occurred |

**Business Logic**:
- Immutable log of all system changes
- Required for compliance and security auditing
- Enables investigation of unauthorized changes
- Supports rollback scenarios and forensic analysis

### 10.2 `reports` Table
**Purpose**: Saved report definitions and custom report templates.

| Field | Type | Description |
|-------|------|-------------|
| `id` | INTEGER (PK) | Unique identifier |
| `name` | VARCHAR | Report name |
| `type` | VARCHAR | Report type (balance_sheet, income_statement, cash_flow, custom) |
| `query_params` | JSON | Report parameters and filters |
| `created_by` | INTEGER (FK) | References users.id |
| `is_public` | BOOLEAN | Whether other users can access this report |
| `created_at` | TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | Last modification timestamp |

**Business Logic**:
- Enables saving of frequently used report configurations
- Public reports can be shared across users
- Custom reports allow flexible data analysis
- Query parameters stored in JSON format for flexibility

---

## Database Relationships and Constraints

### Key Relationships:
1. **Hierarchical**: Companies can have parent-child relationships
2. **One-to-Many**: Companies have many branches, branches have many transactions
3. **Many-to-Many**: Users can access multiple branches, roles can have multiple permissions
4. **Self-Referential**: Companies.parent_id references Companies.id

### Data Integrity Rules:
1. **Referential Integrity**: All foreign keys must reference valid records
2. **Transaction Balance**: Sum of debits must equal sum of credits
3. **Fiscal Year Constraints**: Transactions can only be created in active fiscal years
4. **User Access Control**: Users can only access data within their permitted branches
5. **Audit Trail**: All changes must be logged in audit_logs table

### Performance Considerations:
1. **Indexing**: Primary keys, foreign keys, and frequently queried fields should be indexed
2. **Partitioning**: Large tables like transactions and audit_logs may benefit from date-based partitioning
3. **Archiving**: Historical data older than regulatory requirements can be archived
4. **Caching**: Frequently accessed master data (accounts, companies) should be cached

This comprehensive schema provides a robust foundation for enterprise-level accounting operations while maintaining flexibility for various business models and compliance requirements.