<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Transaction;
use App\Models\TransactionLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_cash_transaction_creates_double_entry_lines(): void
    {
        $this->seed();

        $user = User::factory()->create([
            'role' => 'super_admin',
            'branch_id' => null,
        ]);
        $this->actingAs($user);

        $branch = Branch::where('code', 'HO')->firstOrFail();
        $cash = Account::where('code', 'CASH')->firstOrFail();
        $sales = Account::where('code', 'SALES')->firstOrFail();

        $payload = [
            'date' => '2025-01-05',
            'branch_id' => $branch->id,
            'type' => 'cash',
            'from_account_id' => $sales->id,   // credit sales
            'to_account_id' => $cash->id,      // debit cash
            'amount' => 500,
            'narration' => 'Test sale receipt',
        ];

        $res = $this->postJson('/api/transactions', $payload)
            ->assertCreated()
            ->assertJsonStructure(['id', 'voucher_no', 'lines']);

        $tx = Transaction::first();
        $this->assertNotNull($tx);
        $this->assertSame('2025-01-05', $tx->date->toDateString());
        $this->assertSame($user->id, $tx->created_by);
        $this->assertSame($branch->id, $tx->branch_id);

        // Voucher format
        $this->assertMatchesRegularExpression('/^BR-HO-20250105-\d{4}$/', $tx->voucher_no);

        // Two lines: debit CASH 500, credit SALES 500
        $this->assertDatabaseHas('transaction_lines', [
            'transaction_id' => $tx->id,
            'account_id' => $cash->id,
            'debit' => '500.00',
            'credit' => null,
        ]);
        $this->assertDatabaseHas('transaction_lines', [
            'transaction_id' => $tx->id,
            'account_id' => $sales->id,
            'debit' => null,
            'credit' => '500.00',
        ]);

        // Ensure only 2 lines created
        $this->assertSame(2, TransactionLine::where('transaction_id', $tx->id)->count());
    }
}
