<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            // Assets - Current Assets
            [
                'code' => '1001',
                'name' => 'Cash in Hand',
                'type' => 'asset',
                'category' => 'current_asset',
                'description' => 'Physical cash available in office',
                'opening_balance' => 50000.00,
                'current_balance' => 50000.00,
                'is_active' => true
            ],
            [
                'code' => '1002',
                'name' => 'Bank Account - Main',
                'type' => 'asset',
                'category' => 'current_asset',
                'description' => 'Primary business bank account',
                'opening_balance' => 200000.00,
                'current_balance' => 200000.00,
                'is_active' => true
            ],
            [
                'code' => '1003',
                'name' => 'Accounts Receivable',
                'type' => 'asset',
                'category' => 'current_asset',
                'description' => 'Money owed by customers',
                'opening_balance' => 75000.00,
                'current_balance' => 75000.00,
                'is_active' => true
            ],
            [
                'code' => '1004',
                'name' => 'Inventory',
                'type' => 'asset',
                'category' => 'current_asset',
                'description' => 'Stock of goods for sale',
                'opening_balance' => 150000.00,
                'current_balance' => 150000.00,
                'is_active' => true
            ],
            [
                'code' => '1005',
                'name' => 'Prepaid Expenses',
                'type' => 'asset',
                'category' => 'current_asset',
                'description' => 'Expenses paid in advance',
                'opening_balance' => 25000.00,
                'current_balance' => 25000.00,
                'is_active' => true
            ],

            // Assets - Fixed Assets
            [
                'code' => '1101',
                'name' => 'Office Equipment',
                'type' => 'asset',
                'category' => 'fixed_asset',
                'description' => 'Computers, furniture, and office equipment',
                'opening_balance' => 300000.00,
                'current_balance' => 300000.00,
                'is_active' => true
            ],
            [
                'code' => '1102',
                'name' => 'Vehicles',
                'type' => 'asset',
                'category' => 'fixed_asset',
                'description' => 'Company vehicles',
                'opening_balance' => 500000.00,
                'current_balance' => 500000.00,
                'is_active' => true
            ],
            [
                'code' => '1103',
                'name' => 'Building',
                'type' => 'asset',
                'category' => 'fixed_asset',
                'description' => 'Office building or property',
                'opening_balance' => 2000000.00,
                'current_balance' => 2000000.00,
                'is_active' => true
            ],

            // Liabilities - Current Liabilities
            [
                'code' => '2001',
                'name' => 'Accounts Payable',
                'type' => 'liability',
                'category' => 'current_liability',
                'description' => 'Money owed to suppliers',
                'opening_balance' => 80000.00,
                'current_balance' => 80000.00,
                'is_active' => true
            ],
            [
                'code' => '2002',
                'name' => 'Accrued Expenses',
                'type' => 'liability',
                'category' => 'current_liability',
                'description' => 'Expenses incurred but not yet paid',
                'opening_balance' => 30000.00,
                'current_balance' => 30000.00,
                'is_active' => true
            ],
            [
                'code' => '2003',
                'name' => 'Short-term Loan',
                'type' => 'liability',
                'category' => 'current_liability',
                'description' => 'Bank loan payable within one year',
                'opening_balance' => 100000.00,
                'current_balance' => 100000.00,
                'is_active' => true
            ],

            // Liabilities - Long-term Liabilities
            [
                'code' => '2101',
                'name' => 'Long-term Bank Loan',
                'type' => 'liability',
                'category' => 'long_term_liability',
                'description' => 'Bank loan payable over multiple years',
                'opening_balance' => 800000.00,
                'current_balance' => 800000.00,
                'is_active' => true
            ],
            [
                'code' => '2102',
                'name' => 'Mortgage Payable',
                'type' => 'liability',
                'category' => 'long_term_liability',
                'description' => 'Mortgage on office building',
                'opening_balance' => 1500000.00,
                'current_balance' => 1500000.00,
                'is_active' => true
            ],

            // Equity
            [
                'code' => '3001',
                'name' => 'Owner\'s Capital',
                'type' => 'equity',
                'category' => 'owner_equity',
                'description' => 'Owner\'s investment in the business',
                'opening_balance' => 1000000.00,
                'current_balance' => 1000000.00,
                'is_active' => true
            ],
            [
                'code' => '3002',
                'name' => 'Retained Earnings',
                'type' => 'equity',
                'category' => 'owner_equity',
                'description' => 'Accumulated profits retained in business',
                'opening_balance' => 285000.00,
                'current_balance' => 285000.00,
                'is_active' => true
            ],

            // Revenue - Operating Revenue
            [
                'code' => '4001',
                'name' => 'Sales Revenue',
                'type' => 'revenue',
                'category' => 'operating_revenue',
                'description' => 'Revenue from primary business activities',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
                'is_active' => true
            ],
            [
                'code' => '4002',
                'name' => 'Service Revenue',
                'type' => 'revenue',
                'category' => 'operating_revenue',
                'description' => 'Revenue from services provided',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
                'is_active' => true
            ],

            // Revenue - Other Revenue
            [
                'code' => '4101',
                'name' => 'Interest Income',
                'type' => 'revenue',
                'category' => 'other_revenue',
                'description' => 'Interest earned on bank deposits',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
                'is_active' => true
            ],
            [
                'code' => '4102',
                'name' => 'Rental Income',
                'type' => 'revenue',
                'category' => 'other_revenue',
                'description' => 'Income from property rentals',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
                'is_active' => true
            ],

            // Expenses - Operating Expenses
            [
                'code' => '5001',
                'name' => 'Cost of Goods Sold',
                'type' => 'expense',
                'category' => 'operating_expense',
                'description' => 'Direct cost of products sold',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
                'is_active' => true
            ],
            [
                'code' => '5002',
                'name' => 'Salaries and Wages',
                'type' => 'expense',
                'category' => 'operating_expense',
                'description' => 'Employee compensation',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
                'is_active' => true
            ],
            [
                'code' => '5003',
                'name' => 'Office Rent',
                'type' => 'expense',
                'category' => 'operating_expense',
                'description' => 'Monthly office rental expense',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
                'is_active' => true
            ],
            [
                'code' => '5004',
                'name' => 'Utilities Expense',
                'type' => 'expense',
                'category' => 'operating_expense',
                'description' => 'Electricity, water, internet expenses',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
                'is_active' => true
            ],
            [
                'code' => '5005',
                'name' => 'Office Supplies',
                'type' => 'expense',
                'category' => 'operating_expense',
                'description' => 'Stationery and office supplies',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
                'is_active' => true
            ],
            [
                'code' => '5006',
                'name' => 'Marketing Expense',
                'type' => 'expense',
                'category' => 'operating_expense',
                'description' => 'Advertising and marketing costs',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
                'is_active' => true
            ],
            [
                'code' => '5007',
                'name' => 'Travel Expense',
                'type' => 'expense',
                'category' => 'operating_expense',
                'description' => 'Business travel and transportation',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
                'is_active' => true
            ],

            // Expenses - Other Expenses
            [
                'code' => '5101',
                'name' => 'Interest Expense',
                'type' => 'expense',
                'category' => 'other_expense',
                'description' => 'Interest paid on loans',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
                'is_active' => true
            ],
            [
                'code' => '5102',
                'name' => 'Bank Charges',
                'type' => 'expense',
                'category' => 'other_expense',
                'description' => 'Bank fees and charges',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
                'is_active' => true
            ],
            [
                'code' => '5103',
                'name' => 'Depreciation Expense',
                'type' => 'expense',
                'category' => 'other_expense',
                'description' => 'Depreciation of fixed assets',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
                'is_active' => true
            ]
        ];

        foreach ($accounts as $accountData) {
            Account::create($accountData);
        }

        $this->command->info('Sample chart of accounts created successfully!');
    }
}
