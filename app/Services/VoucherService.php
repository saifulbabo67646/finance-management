<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\VoucherSequence;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VoucherService
{
    /**
     * Generate a unique voucher number for a branch and date.
     * Format: BR-{BRANCHCODE}-{YYYYMMDD}-{NNNN}
     */
    public function generate(int $branchId, \DateTimeInterface|string $date = 'today'): string
    {
        $branch = Branch::find($branchId);
        if (!$branch) {
            throw new InvalidArgumentException('Invalid branch ID');
        }

        $day = Carbon::parse($date)->toDateString();
        $ymd = Carbon::parse($day)->format('Ymd');

        $next = DB::transaction(function () use ($branchId, $day) {
            // Locking is best-effort; in SQLite lockForUpdate is ignored but transaction is sufficient
            $seq = VoucherSequence::where('branch_id', $branchId)
                ->whereDate('date', $day)
                ->lockForUpdate()
                ->first();

            if (!$seq) {
                $seq = VoucherSequence::create([
                    'branch_id'   => $branchId,
                    'date'        => $day,
                    'last_number' => 0,
                ]);
            }

            $seq->last_number = $seq->last_number + 1;
            $seq->save();

            return $seq->last_number;
        });

        $nnnn = str_pad((string) $next, 4, '0', STR_PAD_LEFT);

        return sprintf('BR-%s-%s-%s', strtoupper($branch->code), $ymd, $nnnn);
    }
}
