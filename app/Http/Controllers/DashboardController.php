<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Household;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(
        Request $request,
        Household $household
    ): Response {
        $headings = Category::query()
            ->where('household_id', $household->id)
            ->where('category_type', 'heading')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->map(function (Category $heading) use ($household) {
                $balance = Category::query()
                    ->where('household_id', $household->id)
                    ->where('parent_category_id', $heading->id)
                    ->where('is_active', true)
                    ->where('tracks_balance', true)
                    ->sum('current_balance');

                return [
                    'id' => $heading->id,
                    'name' => $heading->name,
                    'icon' => $heading->icon,
                    'dashboard_image' => $heading->dashboard_image,
                    'balance' => (float) $balance,
                ];
            })
            ->values();

        $totalAvailable = Category::query()
            ->where('household_id', $household->id)
            ->where('is_active', true)
            ->where('tracks_balance', true)
            ->where('code', '!=', 'income_pool')
            ->sum('current_balance');

        $watchCategories = Category::query()
            ->where('household_id', $household->id)
            ->where('is_active', true)
            ->where('tracks_balance', true)
            ->where('category_type', '!=', 'heading')
            ->where(function ($query) {
                $query
                    ->where('needs_attention', true)
                    ->orWhere('current_balance', '<', 0);
            })
            ->orderByRaw('current_balance < 0 DESC')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'dashboard_image',
                'current_balance',
                'needs_attention',
            ]);

        return Inertia::render('households/dashboard/Index', [
            'household' => [
                'id' => $household->id,
                'household_name' => $household->household_name,
                'default_currency' => $household->default_currency,
            ],
            'headings' => $headings,
            'totalAvailable' => (float) $totalAvailable,
            'watchCategories' => $watchCategories,
        ]);
    }

    public function category(
        Request $request,
        Household $household,
        Category $category
    ): Response {
        abort_unless(
            $category->household_id === $household->id,
            404
        );

        abort_unless(
            $category->category_type === 'heading',
            404
        );

        $envelopes = Category::query()
            ->where('household_id', $household->id)
            ->where('parent_category_id', $category->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (Category $envelope) {
                return [
                    'id' => $envelope->id,
                    'name' => $envelope->name,
                    'dashboard_image' => $envelope->dashboard_image,
                    'balance' => (float) $envelope->current_balance,
                    'needs_attention' => (bool) $envelope->needs_attention,
                ];
            });

        return Inertia::render('households/dashboard/Category', [
            'household' => [
                'id' => $household->id,
                'household_name' => $household->household_name,
                'default_currency' => $household->default_currency,
            ],
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'dashboard_image' => $category->dashboard_image,
            ],
            'envelopes' => $envelopes,
        ]);
    }

    public function envelope(
    Household $household,
    Category $category
): Response {
    abort_unless(
        $category->household_id === $household->id,
        404
    );

    $transactions = Transaction::query()
        ->where('household_id', $household->id)
        ->where('category_id', $category->id)
        ->orderByDesc('transaction_date')
        ->orderByDesc('id')
        ->limit(20)
        ->get();

    $spentThisMonth = Transaction::query()
        ->where('household_id', $household->id)
        ->where('category_id', $category->id)
        ->whereBetween('transaction_date', [
            now()->startOfMonth()->toDateString(),
            now()->toDateString(),
        ])
        ->where('amount', '<', 0)
        ->sum('amount');

    return Inertia::render('households/dashboard/Envelope', [
        'household' => $household,

        'envelope' => [
            'id' => $category->id,
            'name' => $category->name,
            'image' => $category->dashboard_image,
            'current_balance' => (float) $category->current_balance,
            'spent_this_month' => abs((float) $spentThisMonth),
        ],

        'transactions' => $transactions,
    ]);
}

}
