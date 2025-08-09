<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    public function __construct(private TransactionService $service)
    {
    }

    public function index(Request $request)
    {
        $q = Transaction::query()->with(['fromAccount', 'toAccount', 'branch', 'creator']);
        $user = $request->user();

        // Branch scoping
        if ($user && $user->role === 'branch_manager') {
            // Force to user's branch
            if ($user->branch_id) {
                $q->where('branch_id', $user->branch_id);
            } else {
                // No branch assigned -> return empty set
                $q->whereRaw('1 = 0');
            }
        } elseif ($request->filled('branch_id')) {
            $q->where('branch_id', (int) $request->branch_id);
        }
        if ($request->filled('type')) {
            $q->where('type', $request->string('type'));
        }
        if ($request->filled('date_from')) {
            $q->whereDate('date', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $q->whereDate('date', '<=', $request->date('date_to'));
        }
        if ($request->filled('account_id')) {
            $accountId = (int) $request->account_id;
            $q->where(function ($qr) use ($accountId) {
                $qr->where('from_account_id', $accountId)
                   ->orWhere('to_account_id', $accountId);
            });
        }

        return response()->json($q->orderByDesc('date')->orderByDesc('id')->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'type' => ['required', 'in:cash,bank,contra,journal'],
            'from_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'to_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'narration' => ['nullable', 'string'],
            // Optional voucher image (jpg/png/pdf up to 2MB)
            'voucher_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'bank_name' => ['nullable', 'string'],
            'cheque_no' => ['nullable', 'string'],
            'cheque_date' => ['nullable', 'date'],
        ]);

        if ($validated['type'] === 'bank') {
            $request->validate([
                'bank_name' => ['required', 'string'],
                'cheque_no' => ['required', 'string'],
                'cheque_date' => ['required', 'date'],
            ]);
        }

        // Branch scoping for branch_manager
        $user = $request->user();
        if ($user && $user->role === 'branch_manager') {
            if (!$user->branch_id || (int) $validated['branch_id'] !== (int) $user->branch_id) {
                abort(403, 'You can only create transactions for your branch');
            }
        }

        // Handle optional voucher image upload
        if ($request->hasFile('voucher_image')) {
            $path = $request->file('voucher_image')->store('vouchers', 'public');
            $validated['voucher_image_path'] = $path;
        }

        $tx = $this->service->create($validated, $user);

        return response()->json($tx->load(['lines']), 201);
    }
}
