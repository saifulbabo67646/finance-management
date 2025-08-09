<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionLine;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TransactionService
{
    public function __construct(
        protected VoucherService $voucherService
    ) {}

    /**
     * Create a transaction and corresponding double-entry lines.
     *
     * @param array $data
     * @param User $creator
     * @return Transaction
     */
    public function create(array $data, User $creator): Transaction
    {
        // Basic validations
        if (($data['from_account_id'] ?? null) === ($data['to_account_id'] ?? null)) {
            throw new InvalidArgumentException('From and To accounts must be different');
        }
        if (($data['amount'] ?? 0) <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero');
        }
        if (($data['type'] ?? 'cash') === 'bank') {
            if (empty($data['bank_name']) || empty($data['cheque_no']) || empty($data['cheque_date'])) {
                throw new InvalidArgumentException('Bank entries require bank_name, cheque_no and cheque_date');
            }
        }

        return DB::transaction(function () use ($data, $creator) {
            $date = Carbon::parse($data['date'] ?? 'today')->toDateString();
            $branchId = (int) ($data['branch_id'] ?? $creator->branch_id);
            if (!$branchId) {
                throw new InvalidArgumentException('branch_id is required');
            }

            $voucher = $data['voucher_no'] ?? $this->voucherService->generate($branchId, $date);

            $tx = Transaction::create([
                'voucher_no'      => $voucher,
                'date'            => $date,
                'type'            => $data['type'] ?? 'cash',
                'branch_id'       => $branchId,
                'from_account_id' => (int) $data['from_account_id'],
                'to_account_id'   => (int) $data['to_account_id'],
                'amount'          => (string) $data['amount'],
                'narration'       => $data['narration'] ?? null,
                'voucher_image_path' => $data['voucher_image_path'] ?? null,
                'bank_name'       => $data['bank_name'] ?? null,
                'cheque_no'       => $data['cheque_no'] ?? null,
                'cheque_date'     => isset($data['cheque_date']) ? Carbon::parse($data['cheque_date'])->toDateString() : null,
                'created_by'      => $creator->id,
            ]);

            $amount = (string) $tx->amount;

            // Debit the destination account
            TransactionLine::create([
                'transaction_id' => $tx->id,
                'account_id'     => $tx->to_account_id,
                'branch_id'      => $branchId,
                'debit'          => $amount,
                'credit'         => null,
            ]);

            // Credit the source account
            TransactionLine::create([
                'transaction_id' => $tx->id,
                'account_id'     => $tx->from_account_id,
                'branch_id'      => $branchId,
                'debit'          => null,
                'credit'         => $amount,
            ]);

            return $tx->load(['fromAccount', 'toAccount', 'lines']);
        });
    }
}
