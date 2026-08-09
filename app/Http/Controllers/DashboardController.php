<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Household;
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
                    'balance' => (float) $balance,
                ];
            })
            ->filter(
                fn ($heading) => $heading['balance'] != 0
            )
            ->values();

        $totalAvailable = Category::query()
            ->where('household_id', $household->id)
            ->where('is_active', true)
            ->where('tracks_balance', true)
            ->where('code', '!=', 'income_pool')
            ->sum('current_balance');

        return Inertia::render('Dashboard', [
            'household' => $household,
            'headings' => $headings,
            'totalAvailable' => (float) $totalAvailable,
        ]);
    }
}
