<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'voucher_no',
        'date',
        'type',
        'branch_id',
        'from_account_id',
        'to_account_id',
        'amount',
        'narration',
        'voucher_image_path',
        'bank_name',
        'cheque_no',
        'cheque_date',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'cheque_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected $appends = ['voucher_image_url'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function lines()
    {
        return $this->hasMany(TransactionLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getVoucherImageUrlAttribute()
    {
        return $this->voucher_image_path ? asset('storage/' . $this->voucher_image_path) : null;
    }
}
