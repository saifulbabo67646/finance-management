<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user()->load('role');
})->middleware('auth:sanctum');

// User Management Routes (Protected by permissions)
Route::middleware(['auth:sanctum'])->group(function () {
    // User management - only for users with manage_users permission
    Route::middleware(['permission:manage_users'])->group(function () {
        Route::apiResource('users', UserController::class);
        Route::get('roles/active', [UserController::class, 'activeRoles']);
    });

    // Account management routes
    Route::middleware(['permission:manage_accounts'])->group(function () {
        Route::apiResource('accounts', AccountController::class);
        Route::get('accounts/{account}/balance-history', [AccountController::class, 'balanceHistory']);
    });

    // Read-only account routes for users with view permissions
    Route::middleware(['permission:view_accounts'])->group(function () {
        Route::get('accounts-active', [AccountController::class, 'active']);
        Route::get('accounts-cash', [AccountController::class, 'cashAccounts']);
        Route::get('chart-of-accounts', [AccountController::class, 'chartOfAccounts']);
    });

    // Transaction management routes
    Route::middleware(['permission:create_transactions'])->group(function () {
        Route::post('transactions', [TransactionController::class, 'store']);
    });

    Route::middleware(['permission:view_transactions'])->group(function () {
        Route::get('transactions', [TransactionController::class, 'index']);
        Route::get('transactions/{transaction}', [TransactionController::class, 'show']);
    });

    Route::middleware(['permission:edit_transactions'])->group(function () {
        Route::put('transactions/{transaction}', [TransactionController::class, 'update']);
    });

    Route::middleware(['permission:approve_transactions'])->group(function () {
        Route::post('transactions/{transaction}/approve', [TransactionController::class, 'approve']);
        Route::post('transactions/{transaction}/cancel', [TransactionController::class, 'cancel']);
    });

    Route::middleware(['permission:delete_transactions'])->group(function () {
        Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy']);
    });

    // Dashboard routes
    Route::middleware(['permission:view_dashboard'])->group(function () {
        Route::get('dashboard/recent-transactions', [TransactionController::class, 'recent']);
    });
});
