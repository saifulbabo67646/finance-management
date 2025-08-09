<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'is_bank',
        'is_active',
    ];

    protected $casts = [
        'is_bank' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function lines()
    {
        return $this->hasMany(TransactionLine::class);
    }

    public function transactionsFrom()
    {
        return $this->hasMany(Transaction::class, 'from_account_id');
    }

    public function transactionsTo()
    {
        return $this->hasMany(Transaction::class, 'to_account_id');
    }
}
