<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinancialAccountController;
use App\Http\Controllers\FinancialTransactionController;
use App\Http\Controllers\IncomeAllocationController;
use App\Http\Controllers\TransactionImportController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');


Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // User management routes
        Route::get(
            '/users',
            [UserController::class, 'index']
        )->name('users.index');

        Route::put(
            '/users/{user}/household',
            [UserController::class, 'updateHousehold']
        )->name('users.household.update');
    });

Route::middleware([
    'auth',
    'verified',
    'household.access',
])
    ->prefix('households/{household}')
    ->name('households.')
    ->group(function () {
        // Account routes
        Route::get(
            '/accounts',
            [FinancialAccountController::class, 'index']
        )->name('accounts.index');

        Route::get(
            '/accounts/create',
            [FinancialAccountController::class, 'create']
        )->name('accounts.create');

        Route::post(
            '/accounts',
            [FinancialAccountController::class, 'store']
        )->name('accounts.store');

        Route::get(
            '/accounts/{financialAccount}/edit',
            [FinancialAccountController::class, 'edit']
        )->name('accounts.edit');

        Route::put(
            '/accounts/{financialAccount}',
            [FinancialAccountController::class, 'update']
        )->name('accounts.update');

        // Category routes
        Route::get(
            '/categories',
            [CategoryController::class, 'index']
        )->name('categories.index');

        Route::get(
            '/categories/create',
            [CategoryController::class, 'create']
        )->name('categories.create');

        Route::post(
            '/categories',
            [CategoryController::class, 'store']
        )->name('categories.store');

        Route::get(
            '/categories/{category}/edit',
            [CategoryController::class, 'edit']
        )->name('categories.edit');

        Route::put(
            '/categories/{category}',
            [CategoryController::class, 'update']
        )->name('categories.update');

        Route::get(
            '/dashboard',
            [DashboardController::class, 'index']
        )->name('dashboard');

        Route::get(
            '/dashboard/categories/{category}',
            [DashboardController::class, 'category']
        )->name('dashboard.category');

        Route::get(
            '/dashboard/envelopes/{category}',
            [DashboardController::class, 'envelope']
        )->name('dashboard.envelope');

        Route::post(
            '/income-allocations',
            [IncomeAllocationController::class, 'store']
        )->name('income-allocations.store');

        Route::get(
            '/income-allocations/create',
            [IncomeAllocationController::class, 'create']
        )->name('income-allocations.create');

        Route::post(
            '/income-allocations/defaults',
            [IncomeAllocationController::class, 'saveDefaults']
        )->name('income-allocations.defaults.store');

        // Financial transaction routes
        Route::get(
            '/transactions',
            [FinancialTransactionController::class, 'index']
        )->name('transactions.index');

        // Transaction import routes
        Route::get(
            '/transactions/import',
            [TransactionImportController::class, 'create']
        )->name('transactions.import');

        Route::post(
            '/transactions/import/check-duplicates',
            [TransactionImportController::class, 'checkDuplicates']
        )->name('transactions.import.check-duplicates');

        Route::post(
            '/transactions/import/store',
            [TransactionImportController::class, 'store']
        )->name('transactions.import.store');

        Route::put(
            '/transactions/{transaction}/category',
            [FinancialTransactionController::class, 'updateCategory']
        )->name('transactions.category.update');

        Route::get(
            '/transactions/review',
            [FinancialTransactionController::class, 'review']
        )->name('transactions.review');
    });

require __DIR__ . '/settings.php';
