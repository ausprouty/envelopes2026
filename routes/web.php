<?php

use App\Http\Controllers\FinancialAccountController;
use App\Http\Controllers\FinancialTransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // User management routes
        Route::get('/users', [UserController::class, 'index'])
            ->name('users.index');

        Route::put('/users/{user}/household', [UserController::class, 'updateHousehold'])
            ->name('users.household.update');
    });

Route::middleware(['auth', 'verified'])
    ->group(function () {
        Route::get(
            '/households/{household}/accounts',
            [FinancialAccountController::class, 'index']
        )->name('households.accounts.index');

        Route::get(
            '/households/{household}/accounts/create',
            [FinancialAccountController::class, 'create']
        )->name('households.accounts.create');

        Route::post(
            '/households/{household}/accounts',
            [FinancialAccountController::class, 'store']
        )->name('households.accounts.store');

        Route::get(
            '/households/{household}/accounts/{financialAccount}/edit',
            [FinancialAccountController::class, 'edit']
        )->name('households.accounts.edit');

        Route::put(
            '/households/{household}/accounts/{financialAccount}',
            [FinancialAccountController::class, 'update']
        )->name('households.accounts.update');

        // Financial transactions routes
        Route::get(
            '/households/{household}/transactions',
            [FinancialTransactionController::class, 'index']
        )->name('households.transactions.index');

        // Category routes
        Route::get(
            '/households/{household}/categories',
            [CategoryController::class, 'index']
        )->name('households.categories.index');

        Route::get(
            '/households/{household}/categories/create',
            [CategoryController::class, 'create']
        )->name('households.categories.create');

        Route::post(
            '/households/{household}/categories',
            [CategoryController::class, 'store']
        )->name('households.categories.store');

        Route::get(
            '/households/{household}/categories/{category}/edit',
            [CategoryController::class, 'edit']
        )->name('households.categories.edit');

        Route::put(
            '/households/{household}/categories/{category}',
            [CategoryController::class, 'update']
        )->name('households.categories.update');
    });


require __DIR__ . '/settings.php';
