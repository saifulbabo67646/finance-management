# Nabisco Financial Management System

## Database Schema

Goto this [file](database_schema_documentation.md) for the database schema documentation.

### Database Schema Visualization

The database schema is visualized using [DBDiagram.io](https://dbdiagram.io/). You can view the schema by visiting this [link](https://dbdiagram.io/d/nabisco_accounts_financial_management-683ac3bfbd74709cb76aeb32).

## System Overview

The Nabisco Financial Management System is a comprehensive financial and accounting platform designed to handle complex organizational structures with parent-child company relationships, multiple branches, and detailed transaction tracking. The system automates accounting tasks such as ledger management, voucher entry, and financial statement generation while providing powerful reporting capabilities.

## Technology Stack

- **Frontend**: Vue.js Single Page Application
- **Backend**: Laravel API
- **Database**: PostgreSQL
- **Authentication**: Laravel Sanctum
- **Reporting**: PDF generation with server-side rendering

## System Architecture

The application follows a modern architecture with clear separation of concerns:

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   Vue.js SPA    │◄───►│  Laravel API    │◄───►│   PostgreSQL    │
│   (Frontend)    │     │   (Backend)     │     │   (Database)    │
└─────────────────┘     └─────────────────┘     └─────────────────┘
```

### Key Components

1. **User Management & Access Control**
   - Role-based access (Super Admin, Admin, Company Manager, Branch Manager)
   - Company and branch-specific access restrictions
   - Audit logging for all critical actions

2. **Company Hierarchy Management**
   - Parent-child company relationships
   - Branch management for each company
   - External company tracking with branch-level detail

3. **Financial Core**
   - Double-entry accounting system
   - Chart of accounts with customizable structure
   - Fiscal year management
   - Multi-currency support

4. **Transaction Processing**
   - Comprehensive voucher system
   - Bank reconciliation
   - Approval workflows

5. **Reporting & Analytics**
   - Standard financial statements
   - Custom report builder
   - Graphical dashboards

## Transaction Management Scenarios

### 1. Internal Branch Transactions

**Scenario**: Nabisco Head Office transfers ৳500,000 to Nabisco Chittagong Branch

**Process Flow**:
1. Branch Manager initiates an internal transfer voucher
2. System creates a transaction record with:
   - `company_id` = Nabisco
   - `branch_id` = Head Office
   - `type` = "transfer"
   - `amount` = ৳500,000
3. Transaction details record:
   - Debit entry to "Intra-company Transfers - Chittagong" ledger
   - Credit entry from "Cash/Bank" ledger
4. Both branches' ledger balances are updated automatically
5. Reports show the transfer in both branch accounts

### 2. Parent-Child Company Transactions

**Scenario**: Nabisco invests ৳10,000,000 in its IT Subsidiary

**Process Flow**:
1. Company Admin creates an investment voucher
2. System creates a transaction with:
   - `company_id` = Nabisco
   - `branch_id` = Head Office
   - `type` = "payment"
   - `amount` = ৳10,000,000
3. Transaction details record:
   - Debit to "Investments in Subsidiaries" ledger
   - Credit from "Bank Account" ledger
4. External party record links to the IT subsidiary
5. In the IT subsidiary's books (separate company record):
   - Debit to "Bank Account" ledger
   - Credit to "Share Capital" ledger
6. Consolidated reports can show the investment while eliminating inter-company balances

### 3. External Company Transactions

**Scenario**: Nabisco Cumilla Branch purchases equipment worth ৳75,000 from Walton Chittagong Branch

**Process Flow**:
1. Branch Manager creates a purchase voucher
2. System creates a transaction with:
   - `company_id` = Nabisco
   - `branch_id` = Cumilla
   - `type` = "payment"
   - `amount` = ৳75,000
3. Transaction details record:
   - Debit to "Equipment/Fixed Assets" ledger
   - Credit from "Bank Account" ledger
4. Transaction party record links to:
   - `external_company_id` = Walton
   - `external_company_branch_id` = Walton Chittagong Branch
   - `party_type` = "to"
5. Reports can show all transactions with Walton or specifically with their Chittagong branch

### 4. Complex Multi-Branch Transaction

**Scenario**: Nabisco's Electronics subsidiary Dhaka branch sells ৳250,000 worth of products to an external client, payment received by Nabisco Head Office

**Process Flow**:
1. Two coordinated transactions are created:
   - Sales transaction in Electronics subsidiary
   - Receipt transaction in Nabisco Head Office
2. In Electronics subsidiary books:
   - Debit to "Accounts Receivable - Nabisco" ledger
   - Credit to "Sales Revenue" ledger
3. In Nabisco Head Office books:
   - Debit to "Bank Account" ledger
   - Credit to "Accounts Payable - Electronics subsidiary" ledger
4. Inter-company balances track the internal obligation
5. Reports can show the revenue in the subsidiary and the collection at the parent level

## Key Financial Management Features

### 1. Chart of Accounts Management

- Hierarchical account structure
- Standard accounting categories (Assets, Liabilities, Equity, Revenue, Expenses)
- Custom account creation with validation rules
- Account status management (active/inactive)

### 2. Ledger Management

- Automated posting to ledgers from transactions
- Real-time balance calculations
- Historical balance viewing
- Drill-down capabilities from ledger to source transactions

### 3. Bank Account Management

- Multiple bank accounts per company/branch
- Automated bank reconciliation
- Cheque management and tracking
- Bank statement import

### 4. Financial Reporting

- Balance Sheet
- Profit & Loss Statement
- Cash Flow Statement
- Trial Balance
- General Ledger
- Accounts Receivable/Payable Aging
- Branch-wise performance reports
- Inter-company transaction reports
- Custom report builder

### 5. Dashboard & Analytics

- Executive dashboard with key financial metrics
- Graphical representations of financial data
- Trend analysis
- Branch comparison
- Performance indicators

## Access Control & Security

### User Roles

1. **Super Admin**
   - System-wide access
   - Can manage all companies, branches, and users
   - Full access to all financial data and reports

2. **Admin**
   - Company-wide access
   - Can manage branches within their assigned company
   - Full access to company financial data

3. **Company Manager**
   - Access to assigned company
   - Can view and manage company operations
   - Limited ability to create users

4. **Branch Manager**
   - Access to assigned branch only
   - Can create and manage transactions for their branch
   - Can view branch-specific reports

### Security Features

- Encrypted data transmission
- Password hashing
- Audit trails for all financial transactions
- IP-based access restrictions
- Two-factor authentication for sensitive operations

## System Benefits

1. **Centralized Financial Control**
   - Single source of truth for all financial data
   - Consistent accounting policies across the organization
   - Real-time financial visibility

2. **Streamlined Operations**
   - Automated double-entry accounting
   - Reduced manual errors
   - Efficient approval workflows

3. **Enhanced Reporting**
   - Consolidated and entity-specific views
   - Custom report generation
   - Data-driven decision making

4. **Scalability**
   - Supports growth in company structure
   - Easily add new branches or subsidiaries
   - Handles increasing transaction volumes

5. **Compliance & Audit Support**
   - Detailed audit trails
   - Standard-compliant financial statements
   - Data retention policies

## Implementation Approach

The system will be implemented in phases:

1. **Phase 1**: Core accounting framework and user management
2. **Phase 2**: Transaction processing and basic reporting
3. **Phase 3**: Advanced reporting and dashboards
4. **Phase 4**: Bank reconciliation and additional features

Each phase will include testing, user training, and documentation updates.
