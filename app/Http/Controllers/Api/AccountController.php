<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    /**
     * Get paginated list of accounts
     */
    public function index(Request $request): JsonResponse
    {
        $query = Account::query();

        // Filter by type
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        // Filter by category
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        // Filter by active status
        if ($request->has('active')) {
            if ($request->boolean('active')) {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        // Search by name or code
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $accounts = $query->orderBy('code')
            ->paginate($request->get('per_page', 15));

        return response()->json($accounts);
    }

    /**
     * Get all active accounts for dropdowns
     */
    public function active(): JsonResponse
    {
        $accounts = Account::active()
            ->orderBy('type')
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type', 'category', 'current_balance']);

        return response()->json($accounts);
    }

    /**
     * Get cash/bank accounts for cashbook
     */
    public function cashAccounts(): JsonResponse
    {
        $accounts = Account::active()
            ->byType('asset')
            ->byCategory('current_asset')
            ->where(function ($query) {
                $query->where('name', 'like', '%cash%')
                      ->orWhere('name', 'like', '%bank%');
            })
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'current_balance']);

        return response()->json($accounts);
    }

    /**
     * Get single account with details
     */
    public function show(Account $account): JsonResponse
    {
        return response()->json($account);
    }

    /**
     * Create new account
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:20|unique:accounts,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'category' => 'required|in:current_asset,fixed_asset,current_liability,long_term_liability,owner_equity,operating_revenue,other_revenue,operating_expense,other_expense',
            'description' => 'nullable|string',
            'opening_balance' => 'nullable|numeric',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $account = Account::create([
            'code' => $request->code,
            'name' => $request->name,
            'type' => $request->type,
            'category' => $request->category,
            'description' => $request->description,
            'opening_balance' => $request->opening_balance ?? 0,
            'current_balance' => $request->opening_balance ?? 0,
            'is_active' => $request->boolean('is_active', true)
        ]);

        return response()->json([
            'message' => 'Account created successfully',
            'account' => $account
        ], 201);
    }

    /**
     * Update account
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'sometimes|required|string|max:20|unique:accounts,code,' . $account->id,
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:asset,liability,equity,revenue,expense',
            'category' => 'sometimes|required|in:current_asset,fixed_asset,current_liability,long_term_liability,owner_equity,operating_revenue,other_revenue,operating_expense,other_expense',
            'description' => 'nullable|string',
            'opening_balance' => 'sometimes|nullable|numeric',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $account->update($request->only([
            'code', 'name', 'type', 'category', 'description', 'opening_balance', 'is_active'
        ]));

        // Recalculate current balance if opening balance changed
        if ($request->has('opening_balance')) {
            $account->updateBalance();
        }

        return response()->json([
            'message' => 'Account updated successfully',
            'account' => $account
        ]);
    }

    /**
     * Delete account (only if no transactions)
     */
    public function destroy(Account $account): JsonResponse
    {
        // Check if account has any ledger entries
        if ($account->ledgerEntries()->exists()) {
            return response()->json([
                'message' => 'Cannot delete account with existing transactions'
            ], 403);
        }

        $account->delete();

        return response()->json([
            'message' => 'Account deleted successfully'
        ]);
    }

    /**
     * Get account balance history
     */
    public function balanceHistory(Account $account, Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $entries = $account->ledgerEntries()
            ->with('transaction')
            ->dateRange($startDate, $endDate)
            ->orderBy('created_at')
            ->get();

        $balance = $account->opening_balance;
        $history = [];

        foreach ($entries as $entry) {
            if (in_array($account->type, ['asset', 'expense'])) {
                $balance += ($entry->entry_type === 'debit') ? $entry->amount : -$entry->amount;
            } else {
                $balance += ($entry->entry_type === 'credit') ? $entry->amount : -$entry->amount;
            }

            $history[] = [
                'date' => $entry->transaction->transaction_date,
                'description' => $entry->description,
                'debit' => $entry->entry_type === 'debit' ? $entry->amount : 0,
                'credit' => $entry->entry_type === 'credit' ? $entry->amount : 0,
                'balance' => $balance,
                'transaction_number' => $entry->transaction->transaction_number
            ];
        }

        return response()->json([
            'account' => $account,
            'opening_balance' => $account->opening_balance,
            'closing_balance' => $balance,
            'history' => $history
        ]);
    }

    /**
     * Get chart of accounts grouped by type
     */
    public function chartOfAccounts(): JsonResponse
    {
        $accounts = Account::active()
            ->orderBy('type')
            ->orderBy('code')
            ->get()
            ->groupBy('type');

        $chartOfAccounts = [];
        foreach ($accounts as $type => $typeAccounts) {
            $chartOfAccounts[$type] = [
                'type' => $type,
                'accounts' => $typeAccounts->groupBy('category')
            ];
        }

        return response()->json($chartOfAccounts);
    }
}
