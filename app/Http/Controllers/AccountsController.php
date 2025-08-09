<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    public function index(Request $request)
    {
        $query = Account::query();

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }
        if ($request->filled('is_bank')) {
            $query->where('is_bank', (bool) $request->boolean('is_bank'));
        }
        if ($request->filled('active')) {
            $query->where('is_active', (bool) $request->boolean('active'));
        }

        return response()->json($query->orderBy('code')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:accounts,code'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:asset,liability,equity,income,expense'],
            'is_bank' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $account = Account::create([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'category' => $data['category'],
            'is_bank' => $data['is_bank'] ?? false,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json($account, 201);
    }

    public function update(Request $request, Account $account)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'in:asset,liability,equity,income,expense'],
            'is_bank' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $account->update($data);

        return response()->json($account);
    }

    public function toggle(Account $account)
    {
        $account->is_active = !$account->is_active;
        $account->save();

        return response()->json($account);
    }
}
