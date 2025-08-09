<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\TransactionLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LedgerController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $user = $request->user();

        $q = TransactionLine::query()
            ->with(['transaction', 'branch'])
            ->where('account_id', (int) $validated['account_id']);

        // Branch scoping
        if ($user && $user->role === 'branch_manager') {
            if ($user->branch_id) {
                $q->where('branch_id', $user->branch_id);
            } else {
                $q->whereRaw('1 = 0');
            }
        } elseif (!empty($validated['branch_id'])) {
            $q->where('branch_id', (int) $validated['branch_id']);
        }

        // Date filters via related transaction
        if (!empty($validated['date_from'])) {
            $q->whereHas('transaction', function ($tq) use ($validated) {
                $tq->whereDate('date', '>=', $validated['date_from']);
            });
        }
        if (!empty($validated['date_to'])) {
            $q->whereHas('transaction', function ($tq) use ($validated) {
                $tq->whereDate('date', '<=', $validated['date_to']);
            });
        }

        $q->join('transactions', 'transactions.id', '=', 'transaction_lines.transaction_id')
          ->orderBy('transactions.date')
          ->orderBy('transaction_lines.id')
          ->select('transaction_lines.*');

        $lines = $q->get();

        $balance = 0.0;
        $out = $lines->map(function (TransactionLine $line) use (&$balance) {
            $debit = (float) ($line->debit ?? 0);
            $credit = (float) ($line->credit ?? 0);
            $balance += ($debit - $credit);

            return [
                'id' => $line->id,
                'date' => optional($line->transaction)->date,
                'voucher_no' => optional($line->transaction)->voucher_no,
                'narration' => optional($line->transaction)->narration,
                'branch' => $line->branch?->code,
                'type' => optional($line->transaction)->type,
                'debit' => number_format($debit, 2, '.', ''),
                'credit' => number_format($credit, 2, '.', ''),
                'balance' => number_format($balance, 2, '.', ''),
            ];
        });

        return response()->json([
            'account' => Account::select('id', 'code', 'name')->find((int) $validated['account_id']),
            'lines' => $out,
        ]);
    }
}
