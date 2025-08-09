<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['code' => 'CASH', 'name' => 'Cash in Hand', 'category' => 'asset', 'is_bank' => false, 'is_active' => true],
            ['code' => 'BANK', 'name' => 'Bank Account', 'category' => 'asset', 'is_bank' => true, 'is_active' => true],
            ['code' => 'SALES', 'name' => 'Sales', 'category' => 'income', 'is_bank' => false, 'is_active' => true],
            ['code' => 'PUR', 'name' => 'Purchases', 'category' => 'expense', 'is_bank' => false, 'is_active' => true],
            ['code' => 'EXP', 'name' => 'General Expense', 'category' => 'expense', 'is_bank' => false, 'is_active' => true],
            ['code' => 'CAP', 'name' => 'Capital', 'category' => 'equity', 'is_bank' => false, 'is_active' => true],
        ];

        foreach ($accounts as $a) {
            Account::updateOrCreate(['code' => $a['code']], $a);
        }
    }
}
