<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\LedgerController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Minimal JSON endpoints for Accounts and Transactions
Route::middleware(['auth'])->prefix('api')->group(function () {
    // Branches
    Route::get('branches', [BranchesController::class, 'index']);

    // Accounts
    Route::get('accounts', [AccountsController::class, 'index']);
    Route::post('accounts', [AccountsController::class, 'store']);
    Route::put('accounts/{account}', [AccountsController::class, 'update']);
    Route::patch('accounts/{account}/toggle', [AccountsController::class, 'toggle']);

    // Transactions
    Route::get('transactions', [TransactionsController::class, 'index']);
    Route::post('transactions', [TransactionsController::class, 'store']);

    // Ledger
    Route::get('ledger', [LedgerController::class, 'index']);
});

// Inertia pages (UI)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/accounts', function () {
        return Inertia::render('Accounts/Index');
    })->name('accounts.index');

    Route::get('/transactions', function () {
        return Inertia::render('Transactions/Index');
    })->name('transactions.index');

    Route::get('/transactions/create', function () {
        return Inertia::render('Transactions/Create');
    })->name('transactions.create');

    Route::get('/ledger', function () {
        return Inertia::render('Ledger/Index');
    })->name('ledger.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
