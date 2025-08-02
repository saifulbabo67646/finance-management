<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'category',
        'description',
        'opening_balance',
        'current_balance',
        'is_active'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Get all ledger entries for this account
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    /**
     * Get debit entries for this account
     */
    public function debitEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class)->where('entry_type', 'debit');
    }

    /**
     * Get credit entries for this account
     */
    public function creditEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class)->where('entry_type', 'credit');
    }

    /**
     * Calculate current balance based on account type and ledger entries
     */
    public function calculateBalance(): float
    {
        $debits = $this->debitEntries()->sum('amount');
        $credits = $this->creditEntries()->sum('amount');

        // For assets and expenses: Debit increases, Credit decreases
        // For liabilities, equity, and revenue: Credit increases, Debit decreases
        if (in_array($this->type, ['asset', 'expense'])) {
            return $this->opening_balance + $debits - $credits;
        } else {
            return $this->opening_balance + $credits - $debits;
        }
    }

    /**
     * Update current balance
     */
    public function updateBalance(): void
    {
        $this->current_balance = $this->calculateBalance();
        $this->save();
    }

    /**
     * Check if account is a cash/bank account
     */
    public function isCashAccount(): bool
    {
        return in_array($this->category, ['current_asset']) && 
               (str_contains(strtolower($this->name), 'cash') || 
                str_contains(strtolower($this->name), 'bank'));
    }

    /**
     * Scope for active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by account type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by account category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
