<?php

use App\Http\Controllers\FinancialAccountController;
use App\Http\Controllers\Admin\UserController;
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
    });

require __DIR__.'/settings.php';
