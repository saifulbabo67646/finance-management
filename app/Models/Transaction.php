<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'transaction_date',
        'description',
        'notes',
        'total_amount',
        'status',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime'
    ];

    /**
     * Get the user who created this transaction
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this transaction
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get all ledger entries for this transaction
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    /**
     * Get debit entries for this transaction
     */
    public function debitEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class)->where('entry_type', 'debit');
    }

    /**
     * Get credit entries for this transaction
     */
    public function creditEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class)->where('entry_type', 'credit');
    }

    /**
     * Check if transaction is balanced (total debits = total credits)
     */
    public function isBalanced(): bool
    {
        $totalDebits = $this->debitEntries()->sum('amount');
        $totalCredits = $this->creditEntries()->sum('amount');
        
        return abs($totalDebits - $totalCredits) < 0.01; // Allow for minor floating point differences
    }

    /**
     * Generate unique transaction number
     */
    public static function generateTransactionNumber(): string
    {
        $date = now()->format('Ymd');
        $lastTransaction = self::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastTransaction ? 
            (int) substr($lastTransaction->transaction_number, -4) + 1 : 1;
        
        return 'TXN' . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create transaction with ledger entries
     */
    public static function createWithEntries(array $transactionData, array $entries): self
    {
        return DB::transaction(function () use ($transactionData, $entries) {
            // Generate transaction number if not provided
            if (!isset($transactionData['transaction_number'])) {
                $transactionData['transaction_number'] = self::generateTransactionNumber();
            }

            // Create the transaction
            $transaction = self::create($transactionData);

            // Create ledger entries
            foreach ($entries as $entry) {
                $transaction->ledgerEntries()->create([
                    'account_id' => $entry['account_id'],
                    'entry_type' => $entry['entry_type'],
                    'amount' => $entry['amount'],
                    'description' => $entry['description'] ?? $transactionData['description'],
                    'notes' => $entry['notes'] ?? null
                ]);
            }

            // Verify transaction is balanced
            if (!$transaction->isBalanced()) {
                throw new \Exception('Transaction is not balanced. Total debits must equal total credits.');
            }

            // Update account balances
            $transaction->updateAccountBalances();

            return $transaction;
        });
    }

    /**
     * Update account balances for all affected accounts
     */
    public function updateAccountBalances(): void
    {
        $accountIds = $this->ledgerEntries()->pluck('account_id')->unique();
        
        foreach ($accountIds as $accountId) {
            $account = Account::find($accountId);
            if ($account) {
                $account->updateBalance();
            }
        }
    }

    /**
     * Approve transaction
     */
    public function approve(User $approver): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now()
        ]);

        return true;
    }

    /**
     * Cancel transaction
     */
    public function cancel(): bool
    {
        if ($this->status === 'approved') {
            return false; // Cannot cancel approved transactions
        }

        $this->update(['status' => 'cancelled']);
        
        return true;
    }

    /**
     * Scope for pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved transactions
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for transactions by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }
}
