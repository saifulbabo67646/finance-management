<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Get paginated list of transactions
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::with(['creator', 'approver', 'ledgerEntries.account'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Search by transaction number or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate($request->get('per_page', 15));

        return response()->json($transactions);
    }

    /**
     * Get single transaction with details
     */
    public function show(Transaction $transaction): JsonResponse
    {
        $transaction->load([
            'creator',
            'approver',
            'ledgerEntries.account'
        ]);

        return response()->json($transaction);
    }

    /**
     * Create new transaction with ledger entries
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'entries' => 'required|array|min:2',
            'entries.*.account_id' => 'required|exists:accounts,id',
            'entries.*.entry_type' => 'required|in:debit,credit',
            'entries.*.amount' => 'required|numeric|min:0.01',
            'entries.*.description' => 'nullable|string|max:255',
            'entries.*.notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate that debits equal credits
        $totalDebits = collect($request->entries)
            ->where('entry_type', 'debit')
            ->sum('amount');
        
        $totalCredits = collect($request->entries)
            ->where('entry_type', 'credit')
            ->sum('amount');

        if (abs($totalDebits - $totalCredits) > 0.01) {
            return response()->json([
                'message' => 'Transaction is not balanced. Total debits must equal total credits.',
                'errors' => [
                    'entries' => ['Total debits (' . $totalDebits . ') must equal total credits (' . $totalCredits . ')']
                ]
            ], 422);
        }

        try {
            $transactionData = [
                'transaction_date' => $request->transaction_date,
                'description' => $request->description,
                'notes' => $request->notes,
                'total_amount' => $totalDebits, // or $totalCredits, they should be equal
                'created_by' => Auth::id()
            ];

            $transaction = Transaction::createWithEntries($transactionData, $request->entries);

            $transaction->load([
                'creator',
                'ledgerEntries.account'
            ]);

            return response()->json([
                'message' => 'Transaction created successfully',
                'transaction' => $transaction
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update transaction (only if pending)
     */
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        if ($transaction->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending transactions can be updated'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'transaction_date' => 'sometimes|required|date',
            'description' => 'sometimes|required|string|max:255',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $transaction->update($request->only(['transaction_date', 'description', 'notes']));

        $transaction->load([
            'creator',
            'approver',
            'ledgerEntries.account'
        ]);

        return response()->json([
            'message' => 'Transaction updated successfully',
            'transaction' => $transaction
        ]);
    }

    /**
     * Approve transaction
     */
    public function approve(Transaction $transaction): JsonResponse
    {
        if ($transaction->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending transactions can be approved'
            ], 403);
        }

        $success = $transaction->approve(Auth::user());

        if (!$success) {
            return response()->json([
                'message' => 'Failed to approve transaction'
            ], 500);
        }

        $transaction->load([
            'creator',
            'approver',
            'ledgerEntries.account'
        ]);

        return response()->json([
            'message' => 'Transaction approved successfully',
            'transaction' => $transaction
        ]);
    }

    /**
     * Cancel transaction
     */
    public function cancel(Transaction $transaction): JsonResponse
    {
        if ($transaction->status === 'approved') {
            return response()->json([
                'message' => 'Approved transactions cannot be cancelled'
            ], 403);
        }

        $success = $transaction->cancel();

        if (!$success) {
            return response()->json([
                'message' => 'Failed to cancel transaction'
            ], 500);
        }

        $transaction->load([
            'creator',
            'approver',
            'ledgerEntries.account'
        ]);

        return response()->json([
            'message' => 'Transaction cancelled successfully',
            'transaction' => $transaction
        ]);
    }

    /**
     * Delete transaction (only if pending or cancelled)
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        if ($transaction->status === 'approved') {
            return response()->json([
                'message' => 'Approved transactions cannot be deleted'
            ], 403);
        }

        // Update account balances before deletion
        $accountIds = $transaction->ledgerEntries()->pluck('account_id')->unique();
        
        $transaction->delete();

        // Recalculate balances for affected accounts
        foreach ($accountIds as $accountId) {
            $account = Account::find($accountId);
            if ($account) {
                $account->updateBalance();
            }
        }

        return response()->json([
            'message' => 'Transaction deleted successfully'
        ]);
    }

    /**
     * Get recent transactions for dashboard
     */
    public function recent(): JsonResponse
    {
        $transactions = Transaction::with(['ledgerEntries.account'])
            ->approved()
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($transaction) {
                // Determine if this is income or expense based on cash accounts
                $cashEntry = $transaction->ledgerEntries->first(function ($entry) {
                    return $entry->account->isCashAccount();
                });

                return [
                    'id' => $transaction->id,
                    'description' => $transaction->description,
                    'date' => $transaction->transaction_date,
                    'amount' => $transaction->total_amount,
                    'type' => $cashEntry && $cashEntry->entry_type === 'debit' ? 'income' : 'expense'
                ];
            });

        return response()->json($transactions);
    }
}
