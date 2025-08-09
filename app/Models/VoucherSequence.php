<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherSequence extends Model
{
    protected $fillable = [
        'branch_id',
        'date',
        'last_number',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
